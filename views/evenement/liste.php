<?php
require_once __DIR__ . '/../../controllers/EvenementC.php';
require_once __DIR__ . '/../../controllers/ParticipationC.php';

$evenementC   = new EvenementC();
$participationC = new ParticipationC();
$evenements   = $evenementC->afficherEvenements();
$userRole     = $_SESSION['user']['role'] ?? 'citoyen';
?>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<style>
    .ev-btn { font-family: inherit; font-weight: 500; font-size: 0.8rem; padding: 8px 16px; border-radius: 10px; transition: all 0.2s; display: inline-flex; align-items: center; gap: 8px; border: none; cursor: pointer; text-decoration: none; }
    .ev-btn-sm { font-size: 0.7rem; padding: 5px 12px; }
    .ev-btn-primary { background: linear-gradient(135deg, #1a5e2a, #4caf50); color: white; }
    .ev-btn-primary:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(26,94,42,0.3); color: white; }
    .ev-btn-warning { background: #f59e0b; color: white; }
    .ev-btn-warning:hover { background: #d97706; transform: translateY(-2px); color: white; }
    .ev-btn-danger  { background: #dc2626; color: white; }
    .ev-btn-danger:hover  { background: #b91c1c; transform: translateY(-2px); color: white; }
    .ev-btn-info   { background: #0891b2; color: white; }
    .ev-btn-info:hover   { background: #0e7490; transform: translateY(-2px); color: white; }
    .table-wrapper { background: white; border-radius: 16px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.05); }
    .table-pro { width: 100%; border-collapse: collapse; font-size: 0.85rem; }
    .table-pro thead th { background: linear-gradient(135deg, #1a5e2a, #4caf50); color: white; padding: 12px 15px; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px; }
    .table-pro tbody td { padding: 12px 15px; border-bottom: 1px solid #e8f5e9; vertical-align: middle; }
    .table-pro tbody tr:hover { background: #f0fdf4; }
    .ev-badge { padding: 4px 10px; border-radius: 20px; font-size: 0.7rem; font-weight: 600; }
    .filter-bar { background: white; border-radius: 16px; padding: 15px; margin-bottom: 20px; display: flex; gap: 15px; align-items: center; flex-wrap: wrap; box-shadow: 0 2px 8px rgba(0,0,0,0.04); }
    .ev-page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
    .ev-page-header h2 { font-size: 1.4rem; font-weight: 700; color: #0f3b2c; margin: 0; }
</style>

<div class="ev-page-header">
    <h2><i class="fas fa-calendar-alt me-2" style="color:#1a5e2a;"></i>Événements</h2>
    <?php if ($userRole === 'admin'): ?>
        <a href="<?php echo BASE_URL; ?>/index.php?action=ajouter_evenement" class="ev-btn ev-btn-primary">
            <i class="fas fa-plus"></i> Ajouter
        </a>
    <?php endif; ?>
</div>

<div class="filter-bar">
    <form method="GET" action="<?php echo BASE_URL; ?>/index.php" class="d-flex gap-2 flex-grow-1">
        <input type="hidden" name="action" value="evenements">
        <div class="input-group">
            <span class="input-group-text bg-white"><i class="fas fa-search text-success"></i></span>
            <input type="text" name="search" class="form-control" placeholder="Rechercher un événement..."
                   value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>">
        </div>
        <button type="submit" class="ev-btn ev-btn-primary"><i class="fas fa-search"></i> Chercher</button>
    </form>
</div>

<div class="table-wrapper">
    <table class="table-pro">
        <thead>
            <tr>
                <th>ID</th><th>Titre</th><th>Lieu</th><th>Date</th><th>Heure</th>
                <th>Catégorie</th><th>Participants</th>
                <?php if ($userRole === 'admin'): ?><th>Actions</th><?php endif; ?>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($evenements as $e): ?>
            <tr>
                <td>#<?php echo $e['id']; ?></td>
                <td><strong><?php echo htmlspecialchars($e['titre']); ?></strong></td>
                <td><?php echo htmlspecialchars($e['lieu']); ?></td>
                <td><?php echo date('d/m/Y', strtotime($e['date_evenement'])); ?></td>
                <td><?php echo htmlspecialchars($e['heure']); ?></td>
                <td><span class="ev-badge" style="background:#e8f5e9;color:#1a5e2a;"><?php echo htmlspecialchars($e['categorie_nom'] ?? 'N/A'); ?></span></td>
                <td><span class="ev-badge bg-info text-white"><?php echo $participationC->compterParticipationsParEvenement($e['id']); ?> inscrits</span></td>
                <?php if ($userRole === 'admin'): ?>
                <td>
                    <a href="<?php echo BASE_URL; ?>/index.php?action=modifier_evenement&id=<?php echo $e['id']; ?>" class="ev-btn ev-btn-warning ev-btn-sm"><i class="fas fa-edit"></i></a>
                    <a href="<?php echo BASE_URL; ?>/index.php?action=participants_evenement&id=<?php echo $e['id']; ?>" class="ev-btn ev-btn-info ev-btn-sm ms-1"><i class="fas fa-users"></i></a>
                    <a href="<?php echo BASE_URL; ?>/index.php?action=supprimer_evenement&id=<?php echo $e['id']; ?>" class="ev-btn ev-btn-danger ev-btn-sm ms-1" onclick="return confirm('Supprimer cet événement ?')"><i class="fas fa-trash"></i></a>
                </td>
                <?php endif; ?>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($evenements)): ?>
            <tr><td colspan="<?php echo $userRole === 'admin' ? 8 : 7; ?>" class="text-center py-4 text-muted">Aucun événement trouvé.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
