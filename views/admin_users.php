<?php

declare(strict_types=1);

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

<?php if ($success !== ''): ?>
  <div class="card" style="padding:14px; border-color: rgba(34,197,94,.35); margin-bottom:16px;">
    <div style="font-weight:900; color: var(--green-700);"><?= $h($success) ?></div>
  </div>
<?php endif; ?>

<?php if ($errors !== []): ?>
  <div class="card" style="padding:14px; border-color: rgba(239,68,68,.35); margin-bottom:16px;">
    <div style="font-weight:900;">Erreurs</div>
    <div class="muted" style="margin-top:6px; font-weight:700;">Corrige les champs indiqués.</div>
  </div>
<?php endif; ?>

<div style="display:grid; grid-template-columns: 1fr; gap:16px;">

  <div class="card" style="padding:16px;">
    <div style="display:flex; align-items:center; justify-content:space-between; gap:12px;">
      <div>
        <div style="font-weight:950; font-size:18px;">Créer un membre</div>
        <div class="muted" style="font-weight:700;">Ajoute un utilisateur au site web.</div>
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
        <a class="btn btn-ghost" href="index.php?route=admin-users">Réinitialiser</a>
      </div>
    </form>
  </div>

  <?php if ($editUser !== null): ?>
    <div class="card" style="padding:16px;">
      <div style="display:flex; align-items:center; justify-content:space-between; gap:12px;">
        <div>
          <div style="font-weight:950; font-size:18px;">Modifier le membre #<?= $h((string)($editUser['id'] ?? '')) ?></div>
          <div class="muted" style="font-weight:700;">Modifie les informations, ou réinitialise le mot de passe.</div>
        </div>
        <a class="btn btn-ghost" href="index.php?route=admin-users">Fermer</a>
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
          <a class="btn btn-ghost" href="index.php?route=admin-users">Annuler</a>
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
                <a class="btn btn-ghost" style="padding:8px 10px; border-radius:12px;" href="index.php?route=admin-users&edit=<?= $h((string)($u['id'] ?? 0)) ?>">Modifier</a>
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

</div>
