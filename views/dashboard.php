<?php

declare(strict_types=1);

$stats = isset($stats) && is_array($stats) ? $stats : [];

$totalPosts = (int)($stats['total_posts'] ?? 0);
$activeUsers = (int)($stats['active_users'] ?? 0);
$comments = (int)($stats['comments'] ?? 0);
$reactions = (int)($stats['reactions'] ?? 0);

$flash = isset($flash) && is_array($flash) ? $flash : null;
$errors = is_array($flash) && isset($flash['errors']) && is_array($flash['errors']) ? $flash['errors'] : [];
$old = is_array($flash) && isset($flash['old']) && is_array($flash['old']) ? $flash['old'] : [];
$success = is_array($flash) ? (string)($flash['success'] ?? '') : '';

$users = isset($users) && is_array($users) ? $users : [];
$editUser = isset($editUser) && is_array($editUser) ? $editUser : null;

$h = static function (string $v): string {
  return htmlspecialchars($v, ENT_QUOTES, 'UTF-8');
};

$getOld = static function (string $key, string $fallback = '') use ($old): string {
  $v = $old[$key] ?? $fallback;
  return is_string($v) ? $v : $fallback;
};

$err = static function (string $key) use ($errors): string {
  $v = $errors[$key] ?? '';
  return is_string($v) ? $v : '';
};

?>

<section class="stats">
  <div class="card stat-card">
    <div class="stat-ico" aria-hidden="true">
      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5A2.5 2.5 0 0 0 6.5 22H20"/><path d="M20 2H6.5A2.5 2.5 0 0 0 4 4.5v15"/><path d="M8 6h8"/><path d="M8 10h10"/><path d="M8 14h6"/></svg>
    </div>
    <div>
      <div class="stat-k">Total des posts</div>
      <div class="stat-v"><?= htmlspecialchars((string)$totalPosts, ENT_QUOTES, 'UTF-8') ?></div>
    </div>
  </div>
  <div class="card stat-card">
    <div class="stat-ico" aria-hidden="true">
      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21a8 8 0 0 0-16 0"/><circle cx="12" cy="7" r="4"/></svg>
    </div>
    <div>
      <div class="stat-k">Utilisateurs actifs</div>
      <div class="stat-v"><?= htmlspecialchars((string)$activeUsers, ENT_QUOTES, 'UTF-8') ?></div>
    </div>
  </div>
  <div class="card stat-card">
    <div class="stat-ico" aria-hidden="true">
      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a4 4 0 0 1-4 4H7l-4 3V7a4 4 0 0 1 4-4h10a4 4 0 0 1 4 4z"/></svg>
    </div>
    <div>
      <div class="stat-k">Commentaires</div>
      <div class="stat-v"><?= htmlspecialchars((string)$comments, ENT_QUOTES, 'UTF-8') ?></div>
    </div>
  </div>
  <div class="card stat-card">
    <div class="stat-ico" aria-hidden="true">
      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M7 22H4a2 2 0 0 1-2-2v-7a2 2 0 0 1 2-2h3"/><path d="M22 12v8a2 2 0 0 1-2 2h-7"/><path d="M14 2l-3 9H3l3 11 8-6h8l-3-14z"/></svg>
    </div>
    <div>
      <div class="stat-k">Réactions</div>
      <div class="stat-v"><?= htmlspecialchars((string)$reactions, ENT_QUOTES, 'UTF-8') ?></div>
    </div>
  </div>
</section>

<section class="charts">
  <div class="card" style="padding:14px;">
    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:10px;">
      <div style="font-weight:950;">Posts par jour</div>
      <div class="muted" style="font-weight:800; font-size:12px;">7 jours</div>
    </div>
    <canvas id="lineChart" height="140"></canvas>
  </div>

  <div class="card" style="padding:14px;">
    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:10px;">
      <div style="font-weight:950;">Distribution du contenu</div>
      <div class="muted" style="font-weight:800; font-size:12px;">Résumé</div>
    </div>
    <canvas id="pieChart" height="140"></canvas>
  </div>
</section>

<section class="activity">
  <div class="card">
    <div class="activity-title">Activité dans le temps</div>
    <div class="activity-chart" aria-hidden="true">
      <div class="muted" style="font-weight:800;">Chart / timeline (placeholder)</div>
    </div>
  </div>
</section>

<section id="members" style="margin-top:16px;">
  <?php if ($success !== ''): ?>
    <div class="card" style="padding:14px; border-color: rgba(34,197,94,.35); margin-bottom:16px;">
      <div style="font-weight:900; color: var(--green-700);">✓ <?= $h($success) ?></div>
    </div>
  <?php endif; ?>

  <div class="card" style="padding:16px; margin-bottom:16px;">
    <div style="display:flex; align-items:center; justify-content:space-between; gap:12px;">
      <div>
        <div style="font-weight:950; font-size:18px;">Membres</div>
        <div class="muted" style="font-weight:700;">Gestion des utilisateurs du site (CRUD).</div>
      </div>
    </div>

    <form method="post" action="index.php?route=admin-users-create" style="margin-top:12px;">
      <div style="display:grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap:12px;">
        <div>
          <label style="font-weight:800; font-size:12px;" for="create_prenom">Prénom</label>
          <input class="input" id="create_prenom" name="prenom" value="<?= $h($getOld('prenom')) ?>" />
          <?php if ($err('prenom') !== ''): ?><div class="muted" style="color:#ef4444; font-weight:800; margin-top:6px;"><?= $h($err('prenom')) ?></div><?php endif; ?>
        </div>
        <div>
          <label style="font-weight:800; font-size:12px;" for="create_nom">Nom</label>
          <input class="input" id="create_nom" name="nom" value="<?= $h($getOld('nom')) ?>" />
          <?php if ($err('nom') !== ''): ?><div class="muted" style="color:#ef4444; font-weight:800; margin-top:6px;"><?= $h($err('nom')) ?></div><?php endif; ?>
        </div>
        <div>
          <label style="font-weight:800; font-size:12px;" for="create_mail">Email</label>
          <input class="input" id="create_mail" name="mail" value="<?= $h($getOld('mail')) ?>" />
          <?php if ($err('mail') !== ''): ?><div class="muted" style="color:#ef4444; font-weight:800; margin-top:6px;"><?= $h($err('mail')) ?></div><?php endif; ?>
        </div>
        <div>
          <label style="font-weight:800; font-size:12px;" for="create_tel">Téléphone (optionnel)</label>
          <input class="input" id="create_tel" name="telephone" value="<?= $h($getOld('telephone')) ?>" />
          <?php if ($err('telephone') !== ''): ?><div class="muted" style="color:#ef4444; font-weight:800; margin-top:6px;"><?= $h($err('telephone')) ?></div><?php endif; ?>
        </div>
        <div>
          <label style="font-weight:800; font-size:12px;" for="create_pwd">Mot de passe</label>
          <input class="input" id="create_pwd" type="password" name="password" />
          <?php if ($err('password') !== ''): ?><div class="muted" style="color:#ef4444; font-weight:800; margin-top:6px;"><?= $h($err('password')) ?></div><?php endif; ?>
        </div>
        <div>
          <label style="font-weight:800; font-size:12px;" for="create_pwd2">Confirmer</label>
          <input class="input" id="create_pwd2" type="password" name="confirm_password" />
          <?php if ($err('confirm_password') !== ''): ?><div class="muted" style="color:#ef4444; font-weight:800; margin-top:6px;"><?= $h($err('confirm_password')) ?></div><?php endif; ?>
        </div>
      </div>

      <div style="margin-top:12px; display:flex; gap:10px;">
        <button class="btn btn-primary" type="submit">Créer</button>
        <?php if ($editUser !== null): ?>
          <a class="btn btn-ghost" href="index.php?route=dashboard#members">Fermer l’édition</a>
        <?php endif; ?>
      </div>
    </form>
  </div>

  <?php if ($editUser !== null): ?>
    <div class="card" style="padding:16px; margin-bottom:16px;">
      <div style="display:flex; align-items:center; justify-content:space-between; gap:12px;">
        <div>
          <div style="font-weight:950; font-size:18px;">Modifier le membre #<?= $h((string)($editUser['id'] ?? '')) ?></div>
          <div class="muted" style="font-weight:700;">Modifie les informations, ou réinitialise le mot de passe.</div>
        </div>
      </div>

      <form method="post" action="index.php?route=admin-users-update&id=<?= $h((string)($editUser['id'] ?? 0)) ?>" style="margin-top:12px;">
        <div style="display:grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap:12px;">
          <div>
            <label style="font-weight:800; font-size:12px;" for="edit_prenom">Prénom</label>
            <input class="input" id="edit_prenom" name="prenom" value="<?= $h((string)($editUser['prenom'] ?? '')) ?>" />
          </div>
          <div>
            <label style="font-weight:800; font-size:12px;" for="edit_nom">Nom</label>
            <input class="input" id="edit_nom" name="nom" value="<?= $h((string)($editUser['nom'] ?? '')) ?>" />
          </div>
          <div>
            <label style="font-weight:800; font-size:12px;" for="edit_mail">Email</label>
            <input class="input" id="edit_mail" name="mail" value="<?= $h((string)($editUser['mail'] ?? '')) ?>" />
          </div>
          <div>
            <label style="font-weight:800; font-size:12px;" for="edit_tel">Téléphone</label>
            <input class="input" id="edit_tel" name="telephone" value="<?= $h((string)($editUser['telephone'] ?? '')) ?>" />
          </div>
          <div>
            <label style="font-weight:800; font-size:12px;" for="edit_pwd">Nouveau mot de passe (optionnel)</label>
            <input class="input" id="edit_pwd" type="password" name="password" />
          </div>
          <div>
            <label style="font-weight:800; font-size:12px;" for="edit_pwd2">Confirmer</label>
            <input class="input" id="edit_pwd2" type="password" name="confirm_password" />
          </div>
        </div>

        <div style="margin-top:12px; display:flex; gap:10px;">
          <button class="btn btn-primary" type="submit">Enregistrer</button>
          <a class="btn btn-ghost" href="index.php?route=dashboard#members">Annuler</a>
        </div>
      </form>
    </div>
  <?php endif; ?>

  <div class="card" style="padding:16px;">
    <div style="display:flex; align-items:center; justify-content:space-between; gap:12px;">
      <div>
        <div style="font-weight:950; font-size:18px;">Tous les membres</div>
        <div class="muted" style="font-weight:700;">Liste des utilisateurs du site.</div>
      </div>
    </div>

    <div style="overflow:auto; margin-top:12px;">
      <table class="table" aria-label="Membres">
        <thead>
          <tr>
            <th>ID</th>
            <th>Prénom</th>
            <th>Nom</th>
            <th>Email</th>
            <th>Téléphone</th>
            <th style="width:210px;">Actions</th>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($users as $u): ?>
          <tr>
            <td class="muted" style="font-weight:800;">#<?= $h((string)($u['id'] ?? 0)) ?></td>
            <td><?= $h((string)($u['prenom'] ?? '')) ?></td>
            <td><?= $h((string)($u['nom'] ?? '')) ?></td>
            <td class="muted"><?= $h((string)($u['mail'] ?? '')) ?></td>
            <td class="muted"><?= $h((string)($u['telephone'] ?? '')) ?></td>
            <td>
              <div style="display:flex; gap:8px;">
                <a class="btn btn-ghost" style="padding:8px 10px; border-radius:12px;" href="index.php?route=dashboard&edit=<?= $h((string)($u['id'] ?? 0)) ?>#members">Modifier</a>
                <form method="post" action="index.php?route=admin-users-delete&id=<?= $h((string)($u['id'] ?? 0)) ?>" onsubmit="return confirm('Supprimer ce membre ?');">
                  <button class="btn btn-ghost" style="padding:8px 10px; border-radius:12px; border-color: rgba(239,68,68,.35);" type="submit">Supprimer</button>
                </form>
              </div>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</section>

<script>
  document.addEventListener('DOMContentLoaded', () => {
    const lineEl = document.getElementById('lineChart');
    const pieEl = document.getElementById('pieChart');

    if (window.Chart && lineEl) {
      new Chart(lineEl, {
        type: 'line',
        data: {
          labels: ['Lun','Mar','Mer','Jeu','Ven','Sam','Dim'],
          datasets: [{
            label: 'Posts',
            data: [2, 4, 3, 6, 5, 2, 4],
            tension: 0.35,
            borderColor: '#16a34a',
            backgroundColor: 'rgba(34, 197, 94, 0.18)',
            fill: true,
            pointRadius: 4,
          }]
        },
        options: {
          plugins: { legend: { display: false } },
          scales: { y: { beginAtZero: true } },
        }
      });
    }

    if (window.Chart && pieEl) {
      new Chart(pieEl, {
        type: 'pie',
        data: {
          labels: ['Blog','Événements','Services'],
          datasets: [{
            data: [55, 25, 20],
            backgroundColor: ['#16a34a', '#22c55e', '#166534'],
          }]
        },
        options: { plugins: { legend: { position: 'bottom' } } }
      });
    }
  });
</script>
