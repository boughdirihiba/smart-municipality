<?php
require_once BASE_PATH . '/controllers/ParticipationC.php';

$participationC  = new ParticipationC();
$participations  = $participationC->afficherToutesParticipations();

$filterStatut = $_GET['statut'] ?? '';
if ($filterStatut !== '') {
    $participations = array_filter($participations, fn($p) => $p['statut_validation'] === $filterStatut);
}

$total     = count($participationC->afficherToutesParticipations());
$valides   = count(array_filter($participationC->afficherToutesParticipations(), fn($p) => $p['statut_validation'] === 'valide'));
$enAttente = count(array_filter($participationC->afficherToutesParticipations(), fn($p) => $p['statut_validation'] === 'en_attente'));
$refuses   = count(array_filter($participationC->afficherToutesParticipations(), fn($p) => $p['statut_validation'] === 'refuse'));

$msgFlash = '';
$msgType  = '';
if (isset($_GET['success'])) {
    $msgFlash = match($_GET['success']) {
        'valide'  => 'Participation validée avec succès.',
        'refuse'  => 'Participation refusée.',
        'supprime'=> 'Participation supprimée.',
        default   => ''
    };
    $msgType = 'success';
}
if (isset($_GET['error'])) {
    $msgFlash = htmlspecialchars($_GET['error'], ENT_QUOTES, 'UTF-8');
    $msgType  = 'danger';
}
?>

<style>
.pa-wrap { padding: 28px 32px; max-width: 1300px; }
.pa-stats { display: grid; grid-template-columns: repeat(4,1fr); gap: 16px; margin-bottom: 28px; }
.pa-stat  { background: #fff; border-radius: 14px; padding: 20px 16px; text-align: center;
            box-shadow: 0 2px 8px rgba(0,0,0,.06); }
.pa-stat .n { font-size: 2rem; font-weight: 700; }
.pa-stat .l { font-size: .78rem; color: #666; margin-top: 4px; }
.pa-card { background: #fff; border-radius: 14px; box-shadow: 0 2px 8px rgba(0,0,0,.06); overflow: hidden; }
.pa-toolbar { display: flex; align-items: center; gap: 12px; padding: 16px 20px;
              border-bottom: 1px solid #f0f0f0; flex-wrap: wrap; }
.pa-toolbar h3 { font-size: 1rem; font-weight: 700; flex: 1; }
.filter-btn { padding: 6px 16px; border-radius: 20px; border: 1px solid #ddd;
              background: #fff; font-size: .78rem; cursor: pointer; text-decoration: none;
              color: #333; transition: all .15s; }
.filter-btn.active, .filter-btn:hover { background: #135D36; color: #fff; border-color: #135D36; }
.pa-table { width: 100%; border-collapse: collapse; font-size: .83rem; }
.pa-table thead th { background: linear-gradient(135deg,#135D36,#2FA084); color: #fff;
                     padding: 12px 14px; text-align: left; font-size: .75rem;
                     text-transform: uppercase; letter-spacing: .4px; }
.pa-table tbody td { padding: 12px 14px; border-bottom: 1px solid #f5f5f5; vertical-align: middle; }
.pa-table tbody tr:last-child td { border-bottom: none; }
.pa-table tbody tr:hover { background: #f8fdf9; }
.badge-s { display: inline-flex; align-items: center; gap: 4px;
           padding: 3px 10px; border-radius: 20px; font-size: .7rem; font-weight: 600; }
.bs-attente { background: #fef3c7; color: #b45309; }
.bs-valide  { background: #dcfce7; color: #16a34a; }
.bs-refuse  { background: #fee2e2; color: #dc2626; }
.act-btn { display: inline-flex; align-items: center; gap: 4px; padding: 4px 10px;
           border-radius: 7px; font-size: .72rem; font-weight: 600;
           text-decoration: none; border: none; cursor: pointer; margin: 2px; }
.act-valider  { background: #dcfce7; color: #16a34a; }
.act-valider:hover  { background: #16a34a; color: #fff; }
.act-refuser  { background: #fee2e2; color: #dc2626; }
.act-refuser:hover  { background: #dc2626; color: #fff; }
.act-suppr    { background: #f1f5f9; color: #64748b; }
.act-suppr:hover    { background: #64748b; color: #fff; }
.alert-msg { padding: 12px 18px; border-radius: 10px; margin-bottom: 20px; font-size: .88rem; }
.alert-success { background: #dcfce7; color: #16a34a; border: 1px solid #bbf7d0; }
.alert-danger  { background: #fee2e2; color: #dc2626; border: 1px solid #fecaca; }
.empty-pa { text-align: center; padding: 50px; color: #999; }
.empty-pa i { font-size: 2.5rem; margin-bottom: 12px; display: block; }
</style>

<div class="pa-wrap">
  <h2 style="font-size:1.3rem;font-weight:700;margin-bottom:22px;color:#135D36;">
    <i class="fas fa-users" style="margin-right:8px;"></i>Gestion des participations
  </h2>

  <?php if ($msgFlash): ?>
    <div class="alert-msg alert-<?= $msgType ?>"><?= $msgFlash ?></div>
  <?php endif; ?>

  <div class="pa-stats">
    <div class="pa-stat">
      <div class="n" style="color:#135D36;"><?= $total ?></div>
      <div class="l">Total</div>
    </div>
    <div class="pa-stat">
      <div class="n" style="color:#16a34a;"><?= $valides ?></div>
      <div class="l">Validées</div>
    </div>
    <div class="pa-stat">
      <div class="n" style="color:#b45309;"><?= $enAttente ?></div>
      <div class="l">En attente</div>
    </div>
    <div class="pa-stat">
      <div class="n" style="color:#dc2626;"><?= $refuses ?></div>
      <div class="l">Refusées</div>
    </div>
  </div>

  <div class="pa-card">
    <div class="pa-toolbar">
      <h3><i class="fas fa-list" style="margin-right:6px;"></i>Liste des inscriptions</h3>
      <a href="?action=participations_admin" class="filter-btn <?= $filterStatut === '' ? 'active' : '' ?>">Toutes</a>
      <a href="?action=participations_admin&statut=en_attente" class="filter-btn <?= $filterStatut === 'en_attente' ? 'active' : '' ?>">En attente</a>
      <a href="?action=participations_admin&statut=valide" class="filter-btn <?= $filterStatut === 'valide' ? 'active' : '' ?>">Validées</a>
      <a href="?action=participations_admin&statut=refuse" class="filter-btn <?= $filterStatut === 'refuse' ? 'active' : '' ?>">Refusées</a>
    </div>

    <?php if (empty($participations)): ?>
      <div class="empty-pa">
        <i class="fas fa-inbox"></i>
        <p>Aucune participation trouvée.</p>
      </div>
    <?php else: ?>
      <div style="overflow-x:auto;">
        <table class="pa-table">
          <thead>
            <tr>
              <th>#</th>
              <th>Citoyen</th>
              <th>Email</th>
              <th>Événement</th>
              <th>Catégorie</th>
              <th>Date évén.</th>
              <th>Nb</th>
              <th>Inscrit le</th>
              <th>Statut</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($participations as $p): ?>
            <tr>
              <td style="color:#999;"><?= (int)$p['id'] ?></td>
              <td><strong><?= htmlspecialchars($p['prenom'] . ' ' . $p['nom'], ENT_QUOTES, 'UTF-8') ?></strong></td>
              <td style="color:#666;font-size:.75rem;"><?= htmlspecialchars($p['email'], ENT_QUOTES, 'UTF-8') ?></td>
              <td><?= htmlspecialchars($p['titre'], ENT_QUOTES, 'UTF-8') ?></td>
              <td><?= htmlspecialchars($p['categorie_nom'] ?? '—', ENT_QUOTES, 'UTF-8') ?></td>
              <td><?= date('d/m/Y', strtotime($p['date_evenement'])) ?></td>
              <td style="text-align:center;"><?= (int)$p['nombre_participants'] ?></td>
              <td style="font-size:.75rem;color:#666;"><?= date('d/m/Y H:i', strtotime($p['date_participation'])) ?></td>
              <td>
                <?php if ($p['statut_validation'] === 'en_attente'): ?>
                  <span class="badge-s bs-attente"><i class="fas fa-clock"></i> En attente</span>
                <?php elseif ($p['statut_validation'] === 'valide'): ?>
                  <span class="badge-s bs-valide"><i class="fas fa-check"></i> Validé</span>
                <?php else: ?>
                  <span class="badge-s bs-refuse"><i class="fas fa-times"></i> Refusé</span>
                <?php endif; ?>
              </td>
              <td style="white-space:nowrap;">
                <?php if ($p['statut_validation'] !== 'valide'): ?>
                  <a href="<?= BASE_URL ?>/index.php?action=participation_valider&id=<?= (int)$p['id'] ?>"
                     class="act-btn act-valider"
                     onclick="return confirm('Valider cette participation ?')">
                    <i class="fas fa-check"></i> Valider
                  </a>
                <?php endif; ?>
                <?php if ($p['statut_validation'] !== 'refuse'): ?>
                  <a href="<?= BASE_URL ?>/index.php?action=participation_refuser&id=<?= (int)$p['id'] ?>"
                     class="act-btn act-refuser"
                     onclick="return confirm('Refuser cette participation ?')">
                    <i class="fas fa-times"></i> Refuser
                  </a>
                <?php endif; ?>
                <a href="<?= BASE_URL ?>/index.php?action=participation_supprimer&id=<?= (int)$p['id'] ?>"
                   class="act-btn act-suppr"
                   onclick="return confirm('Supprimer définitivement cette participation ?')">
                  <i class="fas fa-trash"></i>
                </a>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>
</div>
