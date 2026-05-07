<?php

declare(strict_types=1);

$sectionTitle = isset($sectionTitle) ? (string)$sectionTitle : 'Section';

$h = static function (string $v): string {
    return htmlspecialchars($v, ENT_QUOTES, 'UTF-8');
};

?>

<section>
  <div class="card" style="padding:16px;">
    <div style="font-weight:950; font-size:18px; margin-bottom:6px;"><?= $h($sectionTitle) ?></div>
    <div class="muted" style="font-weight:700;">Contenu à venir (placeholder).</div>
    <div class="admin-placeholder" aria-hidden="true">
      <div class="admin-placeholder-inner">Placeholder</div>
    </div>
  </div>
</section>
