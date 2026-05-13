<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once BASE_PATH . '/controllers/ParticipationC.php';

$userId   = $_SESSION['user']['id']   ?? $_SESSION['user_id']   ?? null;
$userName = $_SESSION['user']['prenom'] ?? $_SESSION['prenom'] ?? 'Utilisateur';

if (!$userId) {
    header('Location: ' . BASE_URL . '/index.php?route=login');
    exit();
}

$participationC = new ParticipationC();
$participations = $participationC->afficherParticipationsParUser($userId);

$message     = '';
$messageType = '';
if (isset($_GET['annule']) && $_GET['annule'] === 'success') {
    $message     = 'Participation annulée avec succès.';
    $messageType = 'success';
}
if (isset($_GET['success']) && $_GET['success'] === 'inscrit') {
    $message     = 'Inscription envoyée ! En attente de validation.';
    $messageType = 'success';
}
if (isset($_GET['error'])) {
    $message     = htmlspecialchars($_GET['error'], ENT_QUOTES, 'UTF-8');
    $messageType = 'danger';
}

$totalInscrits = count($participations);
$valides       = count(array_filter($participations, fn($p) => $p['statut_validation'] === 'valide'));
$enAttente     = count(array_filter($participations, fn($p) => $p['statut_validation'] === 'en_attente'));
$refuses       = count(array_filter($participations, fn($p) => $p['statut_validation'] === 'refuse'));
?>

<style>
.part-wrap { max-width: 1100px; margin: 32px auto; padding: 0 24px; }
.part-stats { display: grid; grid-template-columns: repeat(4,1fr); gap: 16px; margin-bottom: 28px; }
.part-stat  { background: #fff; border-radius: 14px; padding: 20px; text-align: center;
              box-shadow: 0 2px 8px rgba(0,0,0,.06); border-top: 4px solid #135D36; }
.part-stat .num   { font-size: 2rem; font-weight: 700; color: #135D36; }
.part-stat .label { font-size: .8rem; color: #666; margin-top: 4px; }
.part-table-wrap { background: #fff; border-radius: 14px; box-shadow: 0 2px 8px rgba(0,0,0,.06); overflow: hidden; }
.part-table { width: 100%; border-collapse: collapse; font-size: .85rem; }
.part-table thead th { background: linear-gradient(135deg, #135D36, #2FA084); color: #fff;
                       padding: 14px 16px; text-align: left; font-weight: 600; font-size: .78rem;
                       text-transform: uppercase; letter-spacing: .4px; }
.part-table tbody td { padding: 13px 16px; border-bottom: 1px solid #f0f0f0; vertical-align: middle; }
.part-table tbody tr:last-child td { border-bottom: none; }
.part-table tbody tr:hover { background: #f8fdf9; }
.badge-status { display: inline-flex; align-items: center; gap: 5px;
                padding: 4px 12px; border-radius: 20px; font-size: .72rem; font-weight: 600; }
.badge-attente  { background: #fef3c7; color: #b45309; }
.badge-valide   { background: #dcfce7; color: #16a34a; }
.badge-refuse   { background: #fee2e2; color: #dc2626; }
.btn-action { display: inline-flex; align-items: center; gap: 5px;
              padding: 5px 12px; border-radius: 8px; font-size: .75rem;
              font-weight: 500; text-decoration: none; border: none; cursor: pointer; }
.btn-danger-sm  { background: #fee2e2; color: #dc2626; }
.btn-danger-sm:hover { background: #dc2626; color: #fff; }
.btn-info-sm    { background: #dbeafe; color: #2563eb; }
.btn-info-sm:hover { background: #2563eb; color: #fff; }
.empty-part { text-align: center; padding: 60px 24px; color: #666; }
.empty-part i { font-size: 3rem; color: #ccc; margin-bottom: 12px; display: block; }
.alert-msg { padding: 12px 18px; border-radius: 10px; margin-bottom: 20px; font-size: .88rem; }
.alert-success { background: #dcfce7; color: #16a34a; border: 1px solid #bbf7d0; }
.alert-danger   { background: #fee2e2; color: #dc2626; border: 1px solid #fecaca; }
@media(max-width:768px){ .part-stats{ grid-template-columns:1fr 1fr; } .part-table-wrap{ overflow-x:auto; } }
</style>

<div class="part-wrap">
  <h2 style="font-size:1.4rem;font-weight:700;margin-bottom:20px;color:#135D36;">
    <i class="fas fa-ticket-alt" style="margin-right:8px;"></i>Mes participations
  </h2>

  <?php if ($message): ?>
    <div class="alert-msg alert-<?= $messageType ?>"><?= $message ?></div>
  <?php endif; ?>

  <div class="part-stats">
    <div class="part-stat"><div class="num"><?= $totalInscrits ?></div><div class="label">Total</div></div>
    <div class="part-stat"><div class="num" style="color:#16a34a;"><?= $valides ?></div><div class="label">Validées</div></div>
    <div class="part-stat"><div class="num" style="color:#b45309;"><?= $enAttente ?></div><div class="label">En attente</div></div>
    <div class="part-stat"><div class="num" style="color:#dc2626;"><?= $refuses ?></div><div class="label">Refusées</div></div>
  </div>

  <?php if (empty($participations)): ?>
    <div class="part-table-wrap">
      <div class="empty-part">
        <i class="fas fa-calendar-times"></i>
        <p style="font-size:1rem;font-weight:600;margin-bottom:8px;">Aucune participation</p>
        <p>Vous n'êtes inscrit à aucun événement pour le moment.</p>
        <a href="<?= BASE_URL ?>/index.php?action=evenements" style="display:inline-block;margin-top:16px;padding:10px 22px;background:#135D36;color:#fff;border-radius:10px;text-decoration:none;font-weight:600;">
          Découvrir les événements
        </a>
      </div>
    </div>
  <?php else: ?>
    <div class="part-table-wrap">
      <table class="part-table">
        <thead>
          <tr>
            <th>Événement</th>
            <th>Catégorie</th>
            <th>Date</th>
            <th>Heure</th>
            <th>Lieu</th>
            <th>Participants</th>
            <th>Statut</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($participations as $p): ?>
          <tr>
            <td><strong><?= htmlspecialchars($p['titre'], ENT_QUOTES, 'UTF-8') ?></strong></td>
            <td><?= htmlspecialchars($p['categorie_nom'] ?? 'Non catégorisé', ENT_QUOTES, 'UTF-8') ?></td>
            <td><?= date('d/m/Y', strtotime($p['date_evenement'])) ?></td>
            <td><?= htmlspecialchars($p['heure'] ?? '—', ENT_QUOTES, 'UTF-8') ?></td>
            <td><?= htmlspecialchars($p['lieu'], ENT_QUOTES, 'UTF-8') ?></td>
            <td style="text-align:center;"><?= (int)$p['nombre_participants'] ?></td>
            <td>
              <?php if ($p['statut_validation'] === 'en_attente'): ?>
                <span class="badge-status badge-attente"><i class="fas fa-clock"></i> En attente</span>
              <?php elseif ($p['statut_validation'] === 'valide'): ?>
                <span class="badge-status badge-valide"><i class="fas fa-check-circle"></i> Validé</span>
              <?php else: ?>
                <span class="badge-status badge-refuse"><i class="fas fa-times-circle"></i> Refusé</span>
              <?php endif; ?>
            </td>
            <td style="white-space:nowrap;">
              <a href="<?= BASE_URL ?>/index.php?action=participation_annuler&event_id=<?= (int)$p['event_id'] ?>"
                 class="btn-action btn-danger-sm"
                 onclick="return confirm('Annuler votre participation à cet événement ?')">
                <i class="fas fa-times"></i> Annuler
              </a>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <p style="margin-top:12px;font-size:.78rem;color:#888;">
      <i class="fas fa-info-circle"></i> Les inscriptions sont validées par l'administrateur.
    </p>
  <?php endif; ?>
</div>
