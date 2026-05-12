<?php

declare(strict_types=1);

use Config\Auth;
use Models\User;

$page = isset($page) ? (string)$page : '';

switch ($page) {
    case 'events':
    case 'map':
    case 'blog':
    case 'services':
    case 'rdv':
        // Intentionally empty: only the profile page contains content.
        break;

    case 'profile':
        $flash = isset($flash) && is_array($flash) ? $flash : null;
        $errors = is_array($flash) && isset($flash['errors']) && is_array($flash['errors']) ? $flash['errors'] : [];
        $old = is_array($flash) && isset($flash['old']) && is_array($flash['old']) ? $flash['old'] : [];
        $success = is_array($flash) && isset($flash['success']) ? (string)$flash['success'] : '';

      $hasFaceId = isset($hasFaceId) ? (bool)$hasFaceId : false;

      $userModel = (isset($user) && $user instanceof User)
        ? $user
        : ((isset($userModel) && $userModel instanceof User) ? $userModel : null);

        $prenom = (string)($old['prenom'] ?? ($userModel?->getPrenom() ?? ''));
        $nom = (string)($old['nom'] ?? ($userModel?->getNom() ?? ''));
        $mail = (string)($old['mail'] ?? ($userModel?->getMail() ?? ''));
        $telephone = (string)($old['telephone'] ?? ($userModel?->getTelephone() ?? ''));

        Auth::startSession();
        $settings = isset($_SESSION['settings']) && is_array($_SESSION['settings']) ? $_SESSION['settings'] : ['notifications' => true, 'dark_mode' => false];
        $notifications = (bool)($settings['notifications'] ?? true);
        $darkMode = (bool)($settings['dark_mode'] ?? false);

        $name = trim($prenom . ' ' . $nom);
        $initialSource = $name !== '' ? $name : 'U';
        $initial = function_exists('mb_substr')
          ? strtoupper((string) mb_substr($initialSource, 0, 1))
          : strtoupper(substr($initialSource, 0, 1));

        ?>

        <?php if ($success !== ''): ?>
          <div class="card profile-card profile-success" style="margin-bottom:16px;">
            <div class="profile-success-text">✓ <?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?></div>
          </div>
        <?php endif; ?>

        <div class="profile-grid">
          <section>
            <div class="card profile-card profile-header" style="margin-bottom:16px;">
              <div class="profile-header-main">
                <div class="avatar profile-avatar-initial"><?= htmlspecialchars($initial, ENT_QUOTES, 'UTF-8') ?></div>
                <div>
                  <div class="profile-name">
                    <?= htmlspecialchars($name !== '' ? $name : 'Utilisateur', ENT_QUOTES, 'UTF-8') ?>
                  </div>
                  <div class="profile-email">
                    <?= htmlspecialchars($mail, ENT_QUOTES, 'UTF-8') ?>
                  </div>
                  <div style="margin-top:10px;">
                    <span class="badge badge-success">Actif</span>
                  </div>
                </div>
              </div>

              <a class="btn btn-ghost" href="#info">Modifier</a>
            </div>

            <div id="info" class="card profile-card" style="margin-bottom:16px;">
              <h2 class="profile-section-title">Informations personnelles</h2>

              <form class="profile-form" method="post" action="index.php?route=profile">
                <input type="hidden" name="action" value="info">

                <div class="form-row-2">
                  <div>
                    <label class="profile-label">Prénom</label>
                    <input class="input" name="prenom" value="<?= htmlspecialchars($prenom, ENT_QUOTES, 'UTF-8') ?>">
                    <?php if (isset($errors['prenom'])): ?><div class="muted" style="color:#dc2626; font-weight:700; margin-top:6px; font-size:12px;"><?= htmlspecialchars((string)$errors['prenom'], ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
                  </div>
                  <div>
                    <label class="profile-label">Nom</label>
                    <input class="input" name="nom" value="<?= htmlspecialchars($nom, ENT_QUOTES, 'UTF-8') ?>">
                    <?php if (isset($errors['nom'])): ?><div class="muted" style="color:#dc2626; font-weight:700; margin-top:6px; font-size:12px;"><?= htmlspecialchars((string)$errors['nom'], ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
                  </div>
                </div>

                <div>
                  <label class="profile-label">Email</label>
                  <input class="input" name="mail" value="<?= htmlspecialchars($mail, ENT_QUOTES, 'UTF-8') ?>">
                  <?php if (isset($errors['mail'])): ?><div class="muted" style="color:#dc2626; font-weight:700; margin-top:6px; font-size:12px;"><?= htmlspecialchars((string)$errors['mail'], ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
                </div>

                <div>
                  <label class="profile-label">Téléphone</label>
                  <input class="input" name="telephone" value="<?= htmlspecialchars($telephone, ENT_QUOTES, 'UTF-8') ?>" placeholder="+212 ...">
                  <?php if (isset($errors['telephone'])): ?><div class="muted" style="color:#dc2626; font-weight:700; margin-top:6px; font-size:12px;"><?= htmlspecialchars((string)$errors['telephone'], ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
                </div>

                <div style="display:flex; gap:10px;">
                  <button class="btn btn-primary" type="submit">Enregistrer</button>
                </div>
              </form>
            </div>

            <div class="card profile-card">
              <h2 class="profile-section-title">Sécurité</h2>

              <div class="card profile-faceid">
                <div class="profile-faceid-row">
                  <div>
                    <div class="profile-faceid-title">Face ID</div>
                    <div class="profile-faceid-subtitle">
                      <?= $hasFaceId ? 'Enregistré sur ce compte.' : 'Aucun Face ID enregistré.' ?>
                    </div>
                  </div>
                  <div class="profile-faceid-actions">
                    <?php if ($hasFaceId): ?>
                      <span class="badge badge-success">Activé</span>
                    <?php endif; ?>
                    <button class="btn btn-ghost" type="button" data-faceid-enroll-btn>
                      <?= $hasFaceId ? 'Mettre à jour Face ID' : 'Enregistrer Face ID' ?>
                    </button>
                  </div>
                </div>
              </div>

              <form class="profile-form" method="post" action="index.php?route=profile">
                <input type="hidden" name="action" value="password">

                <div>
                  <label class="profile-label">Mot de passe actuel</label>
                  <input class="input" type="password" name="current_password">
                  <?php if (isset($errors['current_password'])): ?><div class="muted" style="color:#dc2626; font-weight:700; margin-top:6px; font-size:12px;"><?= htmlspecialchars((string)$errors['current_password'], ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
                </div>
                <div class="form-row-2">
                  <div>
                    <label class="profile-label">Nouveau mot de passe</label>
                    <input class="input" type="password" name="new_password">
                    <?php if (isset($errors['new_password'])): ?><div class="muted" style="color:#dc2626; font-weight:700; margin-top:6px; font-size:12px;"><?= htmlspecialchars((string)$errors['new_password'], ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
                  </div>
                  <div>
                    <label class="profile-label">Confirmer</label>
                    <input class="input" type="password" name="confirm_password">
                    <?php if (isset($errors['confirm_password'])): ?><div class="muted" style="color:#dc2626; font-weight:700; margin-top:6px; font-size:12px;"><?= htmlspecialchars((string)$errors['confirm_password'], ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
                  </div>
                </div>

                <button class="btn btn-primary" type="submit">Changer le mot de passe</button>
              </form>
            </div>
          </section>

          <aside class="profile-aside">
            <div class="card profile-card" style="margin-bottom:16px;">
              <h2 class="profile-section-title">Aperçu d’activité</h2>
              <div class="stats profile-stats">
                <div class="card stat-card" style="box-shadow:none;">
                  <div class="stat-k">Rendez-vous</div>
                  <div class="stat-v">3</div>
                </div>
                <div class="card stat-card" style="box-shadow:none;">
                  <div class="stat-k">Activité</div>
                  <div class="stat-v">12</div>
                </div>
                <div class="card stat-card" style="box-shadow:none;">
                  <div class="stat-k">Notifications</div>
                  <div class="stat-v">5</div>
                </div>
                <div class="card stat-card" style="box-shadow:none;">
                  <div class="stat-k">Services</div>
                  <div class="stat-v">4</div>
                </div>
              </div>
            </div>

            <div class="card profile-card">
              <h2 class="profile-section-title">Paramètres</h2>
              <form class="profile-form" method="post" action="index.php?route=profile">
                <input type="hidden" name="action" value="settings">

                <label class="profile-setting-row">
                  <span>Notifications</span>
                  <input type="checkbox" name="notifications" <?= $notifications ? 'checked' : '' ?>>
                </label>

                <label class="profile-setting-row">
                  <span>Mode sombre</span>
                  <input type="checkbox" name="dark_mode" <?= $darkMode ? 'checked' : '' ?>>
                </label>

                <button class="btn btn-primary" type="submit">Enregistrer</button>
                <a class="btn btn-ghost" href="<?php echo BASE_URL; ?>/index.php?route=logout" style="justify-content:center;">Déconnexion</a>
              </form>
            </div>
          </aside>
        </div>

        <div id="faceIdEnrollModal" class="faceid-modal" aria-hidden="true">
          <div class="faceid-backdrop"></div>
          <div class="faceid-dialog" role="dialog" aria-modal="true" aria-label="Enregistrer Face ID">
            <div class="faceid-head">
              <div class="faceid-title">Enregistrer Face ID</div>
              <button class="faceid-close" type="button" data-faceid-close>Fermer</button>
            </div>
            <div class="faceid-body">
              <div class="faceid-videoWrap">
                <video class="faceid-video" playsinline autoplay muted></video>
              </div>
              <div class="faceid-msg" data-faceid-msg></div>
              <div class="faceid-actions">
                <button class="btn btn-ghost" type="button" data-faceid-start>Ouvrir la caméra</button>
                <button class="btn btn-primary" type="button" data-faceid-save>Enregistrer</button>
              </div>
              <div class="muted" style="font-size:12px; font-weight:700;">
                Conseil: placez votre visage au centre et assurez-vous d'avoir une bonne lumière.
              </div>
            </div>
          </div>
        </div>

        <?php if (Auth::isAdmin()): ?>
          <a
            class="btn btn-primary"
            href="<?php echo BASE_URL; ?>/index.php?route=dashboard"
            style="position:fixed; right:22px; bottom:22px; z-index:60; box-shadow: var(--shadow);"
          >Dashboard</a>
        <?php endif; ?>

        <?php
        break;

    default:
        ?>
        <div class="card" style="padding:16px;">
          <div style="font-weight:950;">Page introuvable.</div>
        </div>
        <?php
        break;
}
