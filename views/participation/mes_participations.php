<?php
session_start();
require_once __DIR__ . '/../../controllers/ParticipationC.php';

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../views/auth/login.php');
    exit();
}

$userId = $_SESSION['user_id'];
$isAdmin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
$userName = isset($_SESSION['prenom']) ? $_SESSION['prenom'] . ' ' . $_SESSION['nom'] : 'Utilisateur';

$participationC = new ParticipationC();
$participations = $participationC->afficherParticipationsParUser($userId);

// Message de confirmation pour l'annulation
$message = '';
$messageType = '';
if (isset($_GET['annule']) && $_GET['annule'] == 'success') {
    $message = '✅ Participation annulée avec succès.';
    $messageType = 'success';
}
if (isset($_GET['error'])) {
    $message = '❌ ' . htmlspecialchars($_GET['error']);
    $messageType = 'danger';
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mes participations - Smart Municipality</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background: #e8f5e9; }
        .navbar { background: white; box-shadow: 0 2px 8px rgba(0,0,0,0.05); padding: 15px 30px; }
        .navbar-brand { font-weight: 700; color: #1a5e2a !important; display: flex; align-items: center; gap: 10px; }
        .navbar-brand img { border-radius: 10px; }
        .admin-badge { background: #f59e0b; color: white; padding: 2px 8px; border-radius: 20px; font-size: 0.6rem; font-weight: 600; margin-left: 10px; }
        .content-container { background: white; border-radius: 20px; padding: 30px; margin: 30px auto; box-shadow: 0 5px 15px rgba(0,0,0,0.05); max-width: 1200px; }
        .btn { font-family: 'Inter', sans-serif; font-weight: 500; font-size: 0.8rem; padding: 8px 16px; border-radius: 10px; transition: all 0.2s; display: inline-flex; align-items: center; gap: 8px; border: none; cursor: pointer; text-decoration: none; }
        .btn-sm { font-size: 0.7rem; padding: 5px 12px; gap: 5px; }
        .btn-primary { background: linear-gradient(135deg, #1a5e2a, #4caf50); color: white; }
        .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(26,94,42,0.3); color: white; }
        .btn-danger { background: #dc2626; color: white; }
        .btn-danger:hover { background: #b91c1c; transform: translateY(-2px); color: white; }
        .btn-info { background: #0891b2; color: white; }
        .btn-info:hover { background: #0e7490; transform: translateY(-2px); color: white; }
        .btn-secondary { background: #6c757d; color: white; }
        .btn-secondary:hover { background: #5a6268; transform: translateY(-2px); color: white; }
        .table-wrapper { background: white; border-radius: 16px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.05); }
        .table-pro { width: 100%; border-collapse: collapse; font-size: 0.8rem; }
        .table-pro thead th { background: linear-gradient(135deg, #1a5e2a, #4caf50); color: white; padding: 12px 15px; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px; }
        .table-pro tbody td { padding: 12px 15px; border-bottom: 1px solid #e8f5e9; vertical-align: middle; }
        .table-pro tbody tr:hover { background: #e8f5e9; }
        .badge { padding: 5px 10px; border-radius: 20px; font-size: 0.7rem; font-weight: 600; display: inline-block; }
        .empty-state { text-align: center; padding: 50px; }
        .empty-state i { font-size: 3rem; color: #2e7d32; margin-bottom: 15px; }
        .stats-card { background: linear-gradient(135deg, #1a5e2a, #4caf50); border-radius: 15px; padding: 15px; margin-bottom: 20px; color: white; display: flex; justify-content: space-around; text-align: center; }
        .stats-card .stat { text-align: center; }
        .stats-card .stat-number { font-size: 1.5rem; font-weight: 700; }
        .stats-card .stat-label { font-size: 0.7rem; opacity: 0.9; }
        .toast-message { position: fixed; top: 20px; right: 20px; z-index: 1000; animation: slideInRight 0.3s ease; }
        @keyframes slideInRight { from { transform: translateX(100%); opacity: 0; } to { transform: translateX(0); opacity: 1; } }
        @media (max-width: 768px) { .content-container { padding: 15px; margin: 15px; } .table-wrapper { overflow-x: auto; } .table-pro { min-width: 700px; } }
    </style>
</head>
<body>
    <!-- Toast Notification -->
    <?php if ($message): ?>
    <div class="toast-message">
        <div class="alert alert-<?php echo $messageType; ?> shadow rounded-3 border-0 py-2 px-3">
            <i class="fas fa-<?php echo $messageType == 'success' ? 'check-circle' : 'exclamation-triangle'; ?> me-2"></i>
            <?php echo $message; ?>
            <button type="button" class="btn-close ms-2" data-bs-dismiss="alert"></button>
        </div>
    </div>
    <?php endif; ?>

    <nav class="navbar">
        <div class="container">
            <a class="navbar-brand" href="<?php echo BASE_URL; ?>/../../index.php">
                <img src="<?php echo BASE_URL; ?>/../../logo.jpeg" alt="Logo" height="35">
                Smart Municipality
                <?php if ($isAdmin): ?>
                    <span class="admin-badge">Admin</span>
                <?php endif; ?>
            </a>
            <div>
                <span class="text-muted me-3" style="font-size: 0.8rem;">
                    <i class="fas fa-user me-1"></i><?php echo htmlspecialchars($userName); ?>
                </span>
                <a href="<?php echo BASE_URL; ?>/../../index.php" class="btn btn-primary btn-sm">
                    <i class="fas fa-arrow-left me-1"></i>Accueil
                </a>
            </div>
        </div>
    </nav>
    
    <div class="container">
        <div class="content-container">
            <h2 class="mb-4">
                <i class="fas fa-ticket-alt me-2" style="color: #1a5e2a;"></i>
                Mes participations
            </h2>
            
            <!-- Statistiques -->
            <?php 
            $totalInscrits = count($participations);
            $valides = count(array_filter($participations, function($p) { return $p['statut_validation'] == 'valide'; }));
            $enAttente = count(array_filter($participations, function($p) { return $p['statut_validation'] == 'en_attente'; }));
            $refuses = count(array_filter($participations, function($p) { return $p['statut_validation'] == 'refuse'; }));
            ?>
            <div class="stats-card">
                <div class="stat">
                    <div class="stat-number"><?php echo $totalInscrits; ?></div>
                    <div class="stat-label">Total inscriptions</div>
                </div>
                <div class="stat">
                    <div class="stat-number"><?php echo $valides; ?></div>
                    <div class="stat-label">Validées</div>
                </div>
                <div class="stat">
                    <div class="stat-number"><?php echo $enAttente; ?></div>
                    <div class="stat-label">En attente</div>
                </div>
                <div class="stat">
                    <div class="stat-number"><?php echo $refuses; ?></div>
                    <div class="stat-label">Refusées</div>
                </div>
            </div>
            
            <?php if (empty($participations)): ?>
                <div class="empty-state">
                    <i class="fas fa-calendar-times"></i>
                    <h3>Aucune participation</h3>
                    <p class="text-muted">Vous n'êtes inscrit à aucun événement pour le moment.</p>
                    <a href="<?php echo BASE_URL; ?>/../../index.php" class="btn btn-primary mt-3">
                        <i class="fas fa-calendar-alt me-1"></i>Découvrir les événements
                    </a>
                </div>
            <?php else: ?>
                <div class="table-wrapper">
                    <table class="table-pro">
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
                            <?php foreach($participations as $p): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($p['titre']); ?></strong></td>
                                <td><?php echo htmlspecialchars($p['categorie_nom'] ?? 'Non catégorisé'); ?></td>
                                <td><?php echo date('d/m/Y', strtotime($p['date_evenement'])); ?></td>
                                <td><?php echo $p['heure']; ?></td>
                                <td><?php echo htmlspecialchars($p['lieu']); ?></td>
                                <td><?php echo $p['nombre_participants']; ?></td>
                                <td>
                                    <?php if($p['statut_validation'] == 'en_attente'): ?>
                                        <span class="badge bg-warning"><i class="fas fa-clock me-1"></i>En attente</span>
                                    <?php elseif($p['statut_validation'] == 'valide'): ?>
                                        <span class="badge bg-success"><i class="fas fa-check-circle me-1"></i>Validé</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger"><i class="fas fa-times-circle me-1"></i>Refusé</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-nowrap">
                                    <?php if($p['statut_validation'] == 'valide'): ?>
                                        <a href="ticket.php?id=<?php echo $p['id']; ?>" class="btn btn-info btn-sm">
                                            <i class="fas fa-ticket-alt"></i> Ticket
                                        </a>
                                    <?php endif; ?>
                                    <a href="annuler.php?event_id=<?php echo $p['event_id']; ?>" class="btn btn-danger btn-sm ms-1" onclick="return confirm('Êtes-vous sûr de vouloir annuler votre participation à cet événement ?')">
                                        <i class="fas fa-times"></i> Annuler
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div class="mt-3 text-muted small">
                    <i class="fas fa-info-circle me-1"></i>
                    Les inscriptions sont en attente de validation par l'administrateur.
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Auto-hide toast after 5 seconds
        setTimeout(() => {
            const toast = document.querySelector('.toast-message');
            if (toast) {
                toast.style.opacity = '0';
                setTimeout(() => toast.remove(), 300);
            }
        }, 5000);
    </script>
</body>
</html>
