<?php
// Variables provided by legacy_router:
// $evenement, $event_id, $participations, $message, $messageType, $generatedTicket
// $totalInscrits, $totalValides, $totalAttente, $totalRefuses
?>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<style>
    :root { --primary: #1a5e2a; --gradient: linear-gradient(135deg, #1a5e2a, #4caf50); }
    .stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; margin-bottom: 24px; }
    .stat-card { background: white; border-radius: 14px; padding: 18px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 2px 8px rgba(0,0,0,0.05); border-left: 4px solid var(--primary); }
    .stat-info h3 { font-size: 0.65rem; text-transform: uppercase; color: #666; letter-spacing: 0.5px; margin-bottom: 4px; }
    .stat-info h2 { font-size: 1.4rem; font-weight: 700; color: var(--primary); margin: 0; }
    .stat-icon { width: 42px; height: 42px; background: #e8f5e9; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.1rem; color: var(--primary); }
    .table-wrapper { background: white; border-radius: 14px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.05); }
    .table-custom { width: 100%; border-collapse: collapse; font-size: 0.82rem; }
    .table-custom thead th { background: var(--gradient); color: white; padding: 11px 14px; font-size: 0.72rem; text-transform: uppercase; letter-spacing: 0.5px; }
    .table-custom tbody td { padding: 11px 14px; border-bottom: 1px solid #e8f5e9; vertical-align: middle; }
    .table-custom tbody tr:hover { background: #f0fdf4; }
    .badge-en-attente { background: #ff9800; color: white; padding: 4px 9px; border-radius: 20px; font-size: 0.68rem; font-weight: 600; }
    .badge-valide     { background: #4caf50; color: white; padding: 4px 9px; border-radius: 20px; font-size: 0.68rem; font-weight: 600; }
    .badge-refuse     { background: #f44336; color: white; padding: 4px 9px; border-radius: 20px; font-size: 0.68rem; font-weight: 600; }
    .p-btn { font-family: inherit; font-weight: 500; font-size: 0.7rem; padding: 4px 10px; border-radius: 7px; border: none; cursor: pointer; display: inline-flex; align-items: center; gap: 4px; }
    .p-btn-success { background: #2e7d32; color: white; }
    .p-btn-danger  { background: #dc2626; color: white; }
    .p-btn-secondary { background: #6c757d; color: white; text-decoration: none; }
    .modal-content { border-radius: 14px; }
    .modal-header  { background: var(--gradient); color: white; border: none; }
    .modal-header .btn-close { filter: brightness(0) invert(1); }
    .ev-page-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 20px; }
    .ev-page-header h2 { font-size: 1.3rem; font-weight: 700; color: var(--primary); margin: 0 0 4px; }
    @media (max-width: 768px) { .stats-grid { grid-template-columns: repeat(2, 1fr); } }
</style>

<div class="ev-page-header">
    <div>
        <h2><i class="fas fa-users me-2"></i>Gestion des participants</h2>
        <p class="text-muted mb-0">
            Événement : <strong><?php echo htmlspecialchars($evenement['titre']); ?></strong><br>
            <small><?php echo htmlspecialchars($evenement['lieu']); ?> &mdash; <?php echo date('d/m/Y', strtotime($evenement['date_evenement'])); ?> à <?php echo htmlspecialchars($evenement['heure']); ?></small>
        </p>
    </div>
    <a href="<?php echo BASE_URL; ?>/index.php?action=evenements" class="p-btn p-btn-secondary">
        <i class="fas fa-arrow-left"></i> Retour
    </a>
</div>

<?php if ($message): ?>
<div class="alert alert-<?php echo htmlspecialchars($messageType); ?> alert-dismissible fade show" role="alert">
    <?php echo $message; ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<?php if ($generatedTicket && isset($generatedTicket['qr_file'])): ?>
<div class="alert alert-success">
    <i class="fas fa-ticket-alt me-2"></i> <strong>Ticket généré !</strong>
    <a href="<?php echo BASE_URL; ?>/uploads/qrcodes/<?php echo htmlspecialchars($generatedTicket['qr_file']); ?>" class="btn btn-sm btn-success ms-2" target="_blank">
        <i class="fas fa-qrcode"></i> Voir le ticket
    </a>
</div>
<?php endif; ?>

<div class="stats-grid">
    <div class="stat-card"><div class="stat-info"><h3>Inscriptions</h3><h2><?php echo $totalInscrits; ?></h2></div><div class="stat-icon"><i class="fas fa-calendar-check"></i></div></div>
    <div class="stat-card"><div class="stat-info"><h3>Validés</h3><h2><?php echo $totalValides; ?></h2></div><div class="stat-icon"><i class="fas fa-check-circle"></i></div></div>
    <div class="stat-card"><div class="stat-info"><h3>En attente</h3><h2><?php echo $totalAttente; ?></h2></div><div class="stat-icon"><i class="fas fa-clock"></i></div></div>
    <div class="stat-card"><div class="stat-info"><h3>Refusés</h3><h2><?php echo $totalRefuses; ?></h2></div><div class="stat-icon"><i class="fas fa-times-circle"></i></div></div>
</div>

<div class="table-wrapper">
    <table class="table-custom">
        <thead><tr><th>ID</th><th>Participant</th><th>Contact</th><th>Personnes</th><th>Date inscription</th><th>Statut</th><th>Actions</th></tr></thead>
        <tbody>
            <?php if (empty($participations)): ?>
            <tr><td colspan="7" class="text-center py-4 text-muted"><i class="fas fa-users fa-2x d-block mb-2"></i>Aucune inscription pour cet événement</td></tr>
            <?php else: ?>
                <?php foreach ($participations as $p): ?>
                <tr>
                    <td>#<?php echo $p['id']; ?></td>
                    <td><strong><?php echo htmlspecialchars($p['prenom'] . ' ' . $p['nom']); ?></strong></td>
                    <td>
                        <i class="fas fa-envelope me-1 text-muted"></i><?php echo htmlspecialchars($p['email']); ?>
                        <?php if (!empty($p['telephone'])): ?><br><i class="fas fa-phone me-1 text-muted"></i><?php echo htmlspecialchars($p['telephone']); ?><?php endif; ?>
                    </td>
                    <td><?php echo $p['nombre_participants']; ?> personne(s)</td>
                    <td><?php echo date('d/m/Y H:i', strtotime($p['date_participation'])); ?></td>
                    <td>
                        <?php if ($p['statut_validation'] == 'en_attente'): ?>
                            <span class="badge-en-attente"><i class="fas fa-clock me-1"></i>En attente</span>
                        <?php elseif ($p['statut_validation'] == 'valide'): ?>
                            <span class="badge-valide"><i class="fas fa-check-circle me-1"></i>Validé</span>
                        <?php else: ?>
                            <span class="badge-refuse"><i class="fas fa-times-circle me-1"></i>Refusé</span>
                        <?php endif; ?>
                    </td>
                    <td class="text-nowrap">
                        <?php if ($p['statut_validation'] == 'en_attente'): ?>
                            <form method="POST" action="<?php echo BASE_URL; ?>/index.php?action=participants_evenement&id=<?php echo $event_id; ?>" class="d-inline">
                                <input type="hidden" name="participation_id" value="<?php echo $p['id']; ?>">
                                <button type="submit" name="action" value="valider" class="p-btn p-btn-success"><i class="fas fa-check"></i></button>
                            </form>
                            <button type="button" class="p-btn p-btn-danger ms-1" data-bs-toggle="modal" data-bs-target="#refuseModal<?php echo $p['id']; ?>"><i class="fas fa-times"></i></button>
                        <?php elseif ($p['statut_validation'] == 'valide'): ?>
                            <span class="text-success"><i class="fas fa-check-circle"></i></span>
                        <?php else: ?>
                            <span class="text-danger"><i class="fas fa-times-circle"></i></span>
                        <?php endif; ?>
                    </td>
                </tr>

                <div class="modal fade" id="refuseModal<?php echo $p['id']; ?>" tabindex="-1">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <form method="POST" action="<?php echo BASE_URL; ?>/index.php?action=participants_evenement&id=<?php echo $event_id; ?>">
                                <input type="hidden" name="participation_id" value="<?php echo $p['id']; ?>">
                                <div class="modal-header">
                                    <h5 class="modal-title"><i class="fas fa-times-circle me-2"></i>Refuser l'inscription</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <p>Refuser l'inscription de <strong><?php echo htmlspecialchars($p['prenom'] . ' ' . $p['nom']); ?></strong> (<?php echo $p['nombre_participants']; ?> personne(s)) ?</p>
                                    <div class="mb-3">
                                        <label class="form-label">Motif du refus (optionnel)</label>
                                        <textarea name="commentaire" class="form-control" rows="3" placeholder="Expliquez la raison du refus..."></textarea>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                                    <button type="submit" name="action" value="refuser" class="btn btn-danger">Confirmer le refus</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
