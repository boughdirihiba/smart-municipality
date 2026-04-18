<?php

declare(strict_types=1);

namespace Models;

interface UserRepository
{
    public function findByMail(string $mail): ?User;
    public function findById(int $id): ?User;

    public function countUsers(): int;

    /** @return array<int, array{id:int, nom:string, prenom:string, mail:string, telephone?:string}> */
    public function listUsers(int $limit = 200, int $offset = 0): array;

    /** @param array{nom:string, prenom:string, mail:string, telephone?:string} $data */
    public function updateUser(int $id, array $data): void;
    public function deleteUser(int $id): void;

    public function mailExistsForOtherId(string $mail, int $id): bool;
    public function mailExists(string $mail): bool;

    public function createUser(string $nom, string $prenom, string $mail, string $passwordHash): int;
    public function updatePassword(int $id, string $newHash): void;

    /** @param array{nom:string, prenom:string, mail:string, telephone?:string} $data */
    public function updateProfile(int $id, array $data): void;
}
