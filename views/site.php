<?php

declare(strict_types=1);

use Config\Auth;
use Models\User;

$page = isset($page) ? (string)$page : '';

switch ($page) {
    case 'events':
        $events = [
            ['title' => 'Journée de nettoyage citoyen', 'date' => '2026-04-22', 'location' => 'Place centrale', 'category' => 'Citoyenneté'],
            ['title' => 'Forum des services municipaux', 'date' => '2026-05-02', 'location' => 'Maison de la culture', 'category' => 'Services'],
            ['title' => 'Marathon vert', 'date' => '2026-05-10', 'location' => 'Parc municipal', 'category' => 'Sport'],
        ];
        ?>

        <div class="card" style="padding:16px; margin-bottom:16px;">
          <div style="display:flex; align-items:center; justify-content:space-between; gap:12px; flex-wrap:wrap;">
            <h1 style="margin:0; font-size:20px; font-weight:950;">Événements</h1>
            <div style="display:flex; gap:10px; align-items:center;">
              <input class="input" type="date" aria-label="Filtrer par date">
              <select class="input" aria-label="Filtrer par catégorie" style="width:220px;">
                <option value="">Toutes catégories</option>
                <option>Citoyenneté</option>
                <option>Services</option>
                <option>Sport</option>
              </select>
            </div>
          </div>
        </div>

        <div style="display:grid; grid-template-columns:repeat(3, 1fr); gap:14px;">
          <?php foreach ($events as $e): ?>
            <div class="card" style="padding:16px;">
              <div style="font-weight:950; font-size:16px; margin-bottom:10px;">
                <?= htmlspecialchars($e['title'], ENT_QUOTES, 'UTF-8') ?>
              </div>
              <div class="muted" style="font-weight:800;">
                <?= htmlspecialchars($e['date'], ENT_QUOTES, 'UTF-8') ?> • <?= htmlspecialchars($e['location'], ENT_QUOTES, 'UTF-8') ?>
              </div>
              <div style="margin-top:12px;">
                <span class="badge badge-success"><?= htmlspecialchars($e['category'], ENT_QUOTES, 'UTF-8') ?></span>
              </div>
            </div>
          <?php endforeach; ?>
        </div>

        <style>
          @media (max-width: 980px){
            div[style*="grid-template-columns:repeat(3"]{grid-template-columns:1fr;}
          }
        </style>

        <?php
        break;

    case 'map':
        ?>

        <div class="card" style="padding:16px; margin-bottom:16px; display:flex; align-items:center; justify-content:space-between; gap:12px; flex-wrap:wrap;">
          <div>
            <h1 style="margin:0; font-size:20px; font-weight:950;">Carte</h1>
            <div class="muted" style="font-weight:700; margin-top:4px;">Carte interactive (OpenStreetMap / Leaflet).</div>
          </div>
          <div style="display:flex; gap:10px;">
            <button class="btn btn-ghost" type="button" onclick="focusCity()">Centrer</button>
          </div>
        </div>

        <div style="display:grid; grid-template-columns:1fr 340px; gap:16px;">
          <div class="card" style="overflow:hidden;">
            <div id="map" style="height:520px;"></div>
          </div>

          <div class="card" style="padding:14px;">
            <div style="font-weight:950; margin-bottom:10px;">Points d’intérêt</div>
            <div class="muted" style="font-weight:700; margin-bottom:12px;">Clique un point sur la carte.</div>

            <div id="poi" style="display:grid; gap:10px;">
              <div class="card" style="padding:12px; box-shadow:none;">
                <div style="font-weight:900;">Mairie</div>
                <div class="muted" style="font-weight:700;">Centre-ville</div>
              </div>
              <div class="card" style="padding:12px; box-shadow:none;">
                <div style="font-weight:900;">Parc municipal</div>
                <div class="muted" style="font-weight:700;">Zone verte</div>
              </div>
              <div class="card" style="padding:12px; box-shadow:none;">
                <div style="font-weight:900;">Centre de services</div>
                <div class="muted" style="font-weight:700;">Guichet unique</div>
              </div>
            </div>
          </div>
        </div>

        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

        <script>
          const city = [33.5731, -7.5898]; // Casablanca (démo)
          const map = L.map('map').setView(city, 12);

          L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; OpenStreetMap'
          }).addTo(map);

          const points = [
            {name:'Mairie', desc:'Centre-ville', lat:33.590, lng:-7.603},
            {name:'Parc municipal', desc:'Zone verte', lat:33.560, lng:-7.616},
            {name:'Centre de services', desc:'Guichet unique', lat:33.575, lng:-7.550},
          ];

          points.forEach(p => {
            const m = L.marker([p.lat, p.lng]).addTo(map);
            m.bindPopup(`<b>${p.name}</b><br/>${p.desc}`);
          });

          window.focusCity = () => map.setView(city, 12);
        </script>

        <style>
          @media (max-width: 980px){
            div[style*="grid-template-columns:1fr 340px"]{grid-template-columns:1fr;}
          }
        </style>

        <?php
        break;

    case 'blog':
        $articles = [
            ['title' => 'Modernisation des services en ligne', 'desc' => 'Nouveaux parcours, démarches simplifiées.', 'img' => 'https://images.unsplash.com/photo-1521737604893-d14cc237f11d?w=1200&auto=format&fit=crop'],
            ['title' => 'Mobilité urbaine : plan 2026', 'desc' => 'Priorité aux transports durables.', 'img' => 'https://images.unsplash.com/photo-1502877338535-766e1452684a?w=1200&auto=format&fit=crop'],
            ['title' => 'Espaces verts : objectifs', 'desc' => 'Plus d’arbres, plus d’ombre, plus de vie.', 'img' => 'https://images.unsplash.com/photo-1441974231531-c6227db76b6e?w=1200&auto=format&fit=crop'],
            ['title' => 'Signalements citoyens', 'desc' => 'Suivi rapide et transparence.', 'img' => 'https://images.unsplash.com/photo-1520607162513-77705c0f0d4a?w=1200&auto=format&fit=crop'],
            ['title' => 'Événements communautaires', 'desc' => 'Calendrier et inscriptions en ligne.', 'img' => 'https://images.unsplash.com/photo-1500530855697-b586d89ba3ee?w=1200&auto=format&fit=crop'],
            ['title' => 'Carte intelligente', 'desc' => 'POI, alertes et accès aux infos.', 'img' => 'https://images.unsplash.com/photo-1528909514045-2fa4ac7a08ba?w=1200&auto=format&fit=crop'],
        ];
        ?>

        <div class="card" style="padding:16px; margin-bottom:16px; display:flex; align-items:center; justify-content:space-between; gap:12px; flex-wrap:wrap;">
          <div>
            <h1 style="margin:0; font-size:20px; font-weight:950;">Blog</h1>
            <div class="muted" style="font-weight:700; margin-top:4px;">Articles et actualités.</div>
          </div>
          <div class="muted" style="font-weight:800;">Page 1 / 3</div>
        </div>

        <div style="display:grid; grid-template-columns:repeat(3, 1fr); gap:14px;">
          <?php foreach ($articles as $a): ?>
            <article class="card" style="overflow:hidden;">
              <div style="height:140px; background:url('<?= htmlspecialchars($a['img'], ENT_QUOTES, 'UTF-8') ?>') center/cover no-repeat;"></div>
              <div style="padding:14px;">
                <div style="font-weight:950; margin-bottom:8px;">
                  <?= htmlspecialchars($a['title'], ENT_QUOTES, 'UTF-8') ?>
                </div>
                <div class="muted" style="font-weight:700; line-height:1.5;">
                  <?= htmlspecialchars($a['desc'], ENT_QUOTES, 'UTF-8') ?>
                </div>
                <div style="margin-top:12px;">
                  <a class="btn btn-ghost" href="#">Lire</a>
                </div>
              </div>
            </article>
          <?php endforeach; ?>
        </div>

        <div style="display:flex; justify-content:center; gap:10px; margin-top:16px;">
          <button class="btn btn-ghost" type="button" disabled>Précédent</button>
          <button class="btn btn-primary" type="button">Suivant</button>
        </div>

        <style>
          @media (max-width: 980px){
            div[style*="grid-template-columns:repeat(3"]{grid-template-columns:1fr;}
          }
        </style>

        <?php
        break;

    case 'services':
        $services = [
            ['title' => 'Demande d’acte', 'desc' => 'Obtenir un document officiel en ligne.', 'action' => 'Demander'],
            ['title' => 'Signalement', 'desc' => 'Déclarer un problème dans votre quartier.', 'action' => 'Signaler'],
            ['title' => 'Paiement', 'desc' => 'Règler taxes et frais municipaux.', 'action' => 'Payer'],
            ['title' => 'Inscription événement', 'desc' => 'Participer aux événements de la commune.', 'action' => 'S’inscrire'],
            ['title' => 'Contact service', 'desc' => 'Contacter un service municipal.', 'action' => 'Contacter'],
            ['title' => 'Suivi de demande', 'desc' => 'Consulter l’avancement de vos demandes.', 'action' => 'Suivre'],
        ];
        ?>

        <div class="card" style="padding:16px; margin-bottom:16px;">
          <h1 style="margin:0; font-size:20px; font-weight:950;">Services</h1>
          <div class="muted" style="font-weight:700; margin-top:4px;">Services municipaux en ligne.</div>
        </div>

        <div style="display:grid; grid-template-columns:repeat(3, 1fr); gap:14px;">
          <?php foreach ($services as $s): ?>
            <div class="card" style="padding:16px;">
              <div style="display:flex; align-items:flex-start; gap:12px;">
                <div style="width:42px; height:42px; border-radius:16px; background:rgba(34,197,94,.14); display:grid; place-items:center; font-weight:950; color:var(--green-700);">✓</div>
                <div style="flex:1;">
                  <div style="font-weight:950; margin-bottom:6px;">
                    <?= htmlspecialchars($s['title'], ENT_QUOTES, 'UTF-8') ?>
                  </div>
                  <div class="muted" style="font-weight:700; line-height:1.5;">
                    <?= htmlspecialchars($s['desc'], ENT_QUOTES, 'UTF-8') ?>
                  </div>
                  <div style="margin-top:12px;">
                    <button class="btn btn-primary" type="button"><?= htmlspecialchars($s['action'], ENT_QUOTES, 'UTF-8') ?></button>
                  </div>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>

        <style>
          @media (max-width: 980px){
            div[style*="grid-template-columns:repeat(3"]{grid-template-columns:1fr;}
          }
        </style>

        <?php
        break;

    case 'rdv':
        ?>

        <div class="card" style="padding:16px;">
          <h1 style="margin:0; font-size:20px; font-weight:950;">Rendez-vous</h1>
          <div class="muted" style="font-weight:700; margin-top:6px;">
            Contenu à intégrer (spécification demandée : exclure le contenu RDV).
          </div>
        </div>

        <?php
        break;

    case 'profile':
        $flash = isset($flash) && is_array($flash) ? $flash : null;
        $errors = is_array($flash) && isset($flash['errors']) && is_array($flash['errors']) ? $flash['errors'] : [];
        $old = is_array($flash) && isset($flash['old']) && is_array($flash['old']) ? $flash['old'] : [];
        $success = is_array($flash) && isset($flash['success']) ? (string)$flash['success'] : '';

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
          <div class="card" style="padding:14px; border-color: rgba(34,197,94,.35); margin-bottom:16px;">
            <div style="font-weight:900; color: var(--green-700);">✓ <?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?></div>
          </div>
        <?php endif; ?>

        <div class="grid-2">
          <section>
            <div class="card" style="padding:16px; margin-bottom:16px; display:flex; align-items:center; justify-content:space-between; gap:16px;">
              <div style="display:flex; align-items:center; gap:14px;">
                <div class="avatar" style="width:56px; height:56px; border-radius:18px;"><?= htmlspecialchars($initial, ENT_QUOTES, 'UTF-8') ?></div>
                <div>
                  <div style="font-weight:950; font-size:18px;">
                    <?= htmlspecialchars($name !== '' ? $name : 'Utilisateur', ENT_QUOTES, 'UTF-8') ?>
                  </div>
                  <div class="muted" style="font-weight:700; margin-top:2px;">
                    <?= htmlspecialchars($mail, ENT_QUOTES, 'UTF-8') ?>
                  </div>
                  <div style="margin-top:10px;">
                    <span class="badge badge-success">Actif</span>
                  </div>
                </div>
              </div>

              <a class="btn btn-ghost" href="#info">Modifier</a>
            </div>

            <div id="info" class="card" style="padding:16px; margin-bottom:16px;">
              <h2 class="section-title">Informations personnelles</h2>

              <form method="post" action="index.php?route=profile" style="display:grid; gap:12px;">
                <input type="hidden" name="action" value="info">

                <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                  <div>
                    <div class="muted" style="font-weight:800; font-size:12px; margin-bottom:6px;">Prénom</div>
                    <input class="input" name="prenom" value="<?= htmlspecialchars($prenom, ENT_QUOTES, 'UTF-8') ?>">
                    <?php if (isset($errors['prenom'])): ?><div class="muted" style="color:#dc2626; font-weight:700; margin-top:6px; font-size:12px;"><?= htmlspecialchars((string)$errors['prenom'], ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
                  </div>
                  <div>
                    <div class="muted" style="font-weight:800; font-size:12px; margin-bottom:6px;">Nom</div>
                    <input class="input" name="nom" value="<?= htmlspecialchars($nom, ENT_QUOTES, 'UTF-8') ?>">
                    <?php if (isset($errors['nom'])): ?><div class="muted" style="color:#dc2626; font-weight:700; margin-top:6px; font-size:12px;"><?= htmlspecialchars((string)$errors['nom'], ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
                  </div>
                </div>

                <div>
                  <div class="muted" style="font-weight:800; font-size:12px; margin-bottom:6px;">Email</div>
                  <input class="input" name="mail" value="<?= htmlspecialchars($mail, ENT_QUOTES, 'UTF-8') ?>">
                  <?php if (isset($errors['mail'])): ?><div class="muted" style="color:#dc2626; font-weight:700; margin-top:6px; font-size:12px;"><?= htmlspecialchars((string)$errors['mail'], ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
                </div>

                <div>
                  <div class="muted" style="font-weight:800; font-size:12px; margin-bottom:6px;">Téléphone</div>
                  <input class="input" name="telephone" value="<?= htmlspecialchars($telephone, ENT_QUOTES, 'UTF-8') ?>" placeholder="+212 ...">
                  <?php if (isset($errors['telephone'])): ?><div class="muted" style="color:#dc2626; font-weight:700; margin-top:6px; font-size:12px;"><?= htmlspecialchars((string)$errors['telephone'], ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
                </div>

                <div style="display:flex; gap:10px;">
                  <button class="btn btn-primary" type="submit">Enregistrer</button>
                </div>
              </form>
            </div>

            <div class="card" style="padding:16px;">
              <h2 class="section-title">Sécurité</h2>

              <form method="post" action="index.php?route=profile" style="display:grid; gap:12px;">
                <input type="hidden" name="action" value="password">

                <div>
                  <div class="muted" style="font-weight:800; font-size:12px; margin-bottom:6px;">Mot de passe actuel</div>
                  <input class="input" type="password" name="current_password">
                  <?php if (isset($errors['current_password'])): ?><div class="muted" style="color:#dc2626; font-weight:700; margin-top:6px; font-size:12px;"><?= htmlspecialchars((string)$errors['current_password'], ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
                </div>
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                  <div>
                    <div class="muted" style="font-weight:800; font-size:12px; margin-bottom:6px;">Nouveau mot de passe</div>
                    <input class="input" type="password" name="new_password">
                    <?php if (isset($errors['new_password'])): ?><div class="muted" style="color:#dc2626; font-weight:700; margin-top:6px; font-size:12px;"><?= htmlspecialchars((string)$errors['new_password'], ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
                  </div>
                  <div>
                    <div class="muted" style="font-weight:800; font-size:12px; margin-bottom:6px;">Confirmer</div>
                    <input class="input" type="password" name="confirm_password">
                    <?php if (isset($errors['confirm_password'])): ?><div class="muted" style="color:#dc2626; font-weight:700; margin-top:6px; font-size:12px;"><?= htmlspecialchars((string)$errors['confirm_password'], ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
                  </div>
                </div>

                <button class="btn btn-primary" type="submit">Changer le mot de passe</button>
              </form>
            </div>
          </section>

          <aside>
            <div class="card" style="padding:16px; margin-bottom:16px;">
              <h2 class="section-title">Aperçu d’activité</h2>
              <div class="stats" style="grid-template-columns:repeat(2,1fr);">
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

            <div class="card" style="padding:16px;">
              <h2 class="section-title">Paramètres</h2>
              <form method="post" action="index.php?route=profile" style="display:grid; gap:12px;">
                <input type="hidden" name="action" value="settings">

                <label style="display:flex; align-items:center; justify-content:space-between; gap:10px; padding:12px; border:1px solid var(--border); border-radius:16px;">
                  <span style="font-weight:900;">Notifications</span>
                  <input type="checkbox" name="notifications" <?= $notifications ? 'checked' : '' ?>>
                </label>

                <label style="display:flex; align-items:center; justify-content:space-between; gap:10px; padding:12px; border:1px solid var(--border); border-radius:16px;">
                  <span style="font-weight:900;">Mode sombre</span>
                  <input type="checkbox" name="dark_mode" <?= $darkMode ? 'checked' : '' ?>>
                </label>

                <button class="btn btn-primary" type="submit">Enregistrer</button>
                <a class="btn btn-ghost" href="index.php?route=logout" style="justify-content:center;">Déconnexion</a>
              </form>
            </div>
          </aside>
        </div>

        <?php if (Auth::isAdmin()): ?>
          <a
            class="btn btn-primary"
            href="index.php?route=dashboard"
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
