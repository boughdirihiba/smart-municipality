<?php

declare(strict_types=1);

namespace Config;

final class Captcha
{
    private const VERIFY_URL = 'https://challenges.cloudflare.com/turnstile/v0/siteverify';

    public static function siteKey(): string
    {
        return trim((string)(getenv('TURNSTILE_SITEKEY') ?: ''));
    }

    public static function secretKey(): string
    {
        return trim((string)(getenv('TURNSTILE_SECRETKEY') ?: ''));
    }

    public static function isConfigured(): bool
    {
        return self::siteKey() !== '' && self::secretKey() !== '';
    }

    public static function verifyTurnstile(string $token, ?string $remoteIp = null): bool
    {
        $token = trim($token);
        if ($token === '') {
            return false;
        }

        $secret = self::secretKey();
        if ($secret === '') {
            return false;
        }

        $payload = [
            'secret' => $secret,
            'response' => $token,
        ];

        $remoteIp = $remoteIp !== null ? trim($remoteIp) : '';
        if ($remoteIp !== '') {
            $payload['remoteip'] = $remoteIp;
        }

        $body = http_build_query($payload);
        $context = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => "Content-Type: application/x-www-form-urlencoded\r\n" .
                    'Content-Length: ' . strlen($body) . "\r\n",
                'content' => $body,
                'timeout' => 6,
            ],
        ]);

        $raw = @file_get_contents(self::VERIFY_URL, false, $context);
        if ($raw === false || $raw === '') {
            error_log('[Captcha] Turnstile verify: no response');
            return false;
        }

        $json = json_decode($raw, true);
        if (!is_array($json)) {
            error_log('[Captcha] Turnstile verify: invalid JSON');
            return false;
        }

        return (bool)($json['success'] ?? false);
    }
}
