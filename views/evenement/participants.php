<?php
session_start();
require_once __DIR__ . '/../../controller/EvenementC.php';
require_once __DIR__ . '/../../controller/ParticipationC.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../../index.php');
    exit();
}

$event_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($event_id <= 0) {
    header('Location: liste.php');
    exit();
}

$evenementC = new EvenementC();
$participationC = new ParticipationC();

$evenement = $evenementC->afficherEvenementParId($event_id);
if (!$evenement) {
    header('Location: liste.php');
    exit();
}

// Traitement des actions
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && isset($_POST['participation_id'])) {
        $participation_id = intval($_POST['participation_id']);
        
        if ($_POST['action'] === 'valider') {
            $result = $participationC->validerParticipation($participation_id);
            if ($result['success']) {
                $message = '✅ Participation validée avec succès !';
                $messageType = 'success';
            } else {
                $message = '❌ Erreur: ' . $result['message'];
                $messageType = 'danger';
            }
        } elseif ($_POST['action'] === 'refuser') {
            $commentaire = isset($_POST['commentaire']) ? trim($_POST['commentaire']) : null;
            $result = $participationC->refuserParticipation($participation_id, $commentaire);
            if ($result['success']) {
                $message = '⚠️ Participation refusée';
                $messageType = 'warning';
            } else {
                $message = '❌ Erreur: ' . $result['message'];
                $messageType = 'danger';
            }
        }
        
        header("Location: participants.php?id=$event_id&msg=" . urlencode($message) . "&type=$messageType");
        exit();
    }
}

if (isset($_GET['msg'])) {
    $message = htmlspecialchars($_GET['msg']);
    $messageType = isset($_GET['type']) ? $_GET['type'] : 'info';
}

$participations = $participationC->getParticipationsByEvent($event_id);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des participants - Smart Municipality</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Inter', sans-serif;
            background: #e8f5e9;
        }
        .sidebar {
            min-height: 100vh;
            background: linear-gradient(180deg, #0d3b1a 0%, #1a5e2a 100%);
            position: fixed;
            width: 280px;
        }
        .sidebar-header { padding: 25px; text-align: center; border-bottom: 1px solid rgba(255,255,255,0.1); }
        .sidebar-header img { border-radius: 15px; margin-bottom: 10px; }
        .sidebar-header h3 { color: white; font-size: 1.2rem; }
        .sidebar-nav a {
            display: flex;
            align-items: center;
            padding: 12px 25px;
            color: rgba(255,255,255,0.85);
            text-decoration: none;
            transition: all 0.3s;
            margin: 5px 10px;
            border-radius: 12px;
        }
        .sidebar-nav a i { width: 28px; margin-right: 12px; font-size: 1.1rem; }
        .sidebar-nav a:hover, .sidebar-nav a.active { background: rgba(255,255,255,0.15); color: white; transform: translateX(5px); }
        .main-content { margin-left: 280px; padding: 25px; }
        hr { border-color: rgba(255,255,255,0.1); margin: 15px; }
        
        .btn {
            font-family: 'Inter', sans-serif;
            font-weight: 500;
            font-size: 0.8rem;
            padding: 8px 16px;
            border-radius: 10px;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            border: none;
            cursor: pointer;
        }
        .btn-sm { font-size: 0.7rem; padding: 5px 12px; gap: 5px; }
        .btn-success { background: #2e7d32; color: white; }
        .btn-success:hover { background: #1b5e20; transform: translateY(-2px); }
        .btn-danger { background: #dc2626; color: white; }
        .btn-danger:hover { background: #b91c1c; transform: translateY(-2px); }
        .btn-warning { background: #f59e0b; color: white; }
        .btn-warning:hover { background: #d97706; transform: translateY(-2px); }
        .btn-info { background: #0891b2; color: white; }
        .btn-info:hover { background: #0e7490; transform: translateY(-2px); }
        .btn-secondary { background: #6c757d; color: white; }
        .btn-secondary:hover { background: #5a6268; transform: translateY(-2px); }
        
        .table-wrapper { background: white; border-radius: 16px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.05); }
        .table-pro { width: 100%; border-collapse: collapse; font-size: 0.8rem; }
        .table-pro thead th { background: linear-gradient(135deg, #1a5e2a, #4caf50); color: white; padding: 12px 15px; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px; }
        .table-pro tbody td { padding: 12px 15px; border-bottom: 1px solid #e8f5e9; vertical-align: middle; }
        .table-pro tbody tr:hover { background: #e8f5e9; }
        .badge-en-attente { background: #ff9800; color: white; padding: 5px 10px; border-radius: 20px; font-size: 0.7rem; font-weight: 600; display: inline-flex; align-items: center; gap: 5px; }
        .badge-valide { background: #4caf50; color: white; padding: 5px 10px; border-radius: 20px; font-size: 0.7rem; font-weight: 600; display: inline-flex; align-items: center; gap: 5px; }
        .badge-refuse { background: #f44336; color: white; padding: 5px 10px; border-radius: 20px; font-size: 0.7rem; font-weight: 600; display: inline-flex; align-items: center; gap: 5px; }
        
        @media (max-width: 768px) {
            .sidebar { width: 80px; }
            .sidebar-header h3, .sidebar-nav a span { display: none; }
            .sidebar-nav a { justify-content: center; padding: 12px; }
            .sidebar-nav a i { margin-right: 0; }
            .main-content { margin-left: 80px; }
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="sidebar-header">
            <img src="../../logo.jpeg" alt="Logo" height="45">
            <h3>Smart Municipality</h3>
        </div>
        <div class="sidebar-nav">
            <a href="../dashboard/admin.php"><i class="fas fa-tachometer-alt"></i><span>Dashboard</span></a>
            <a href="../evenement/liste.php"><i class="fas fa-calendar-alt"></i><span>Événements</span></a>
            <a href="#"><i class="fas fa-users"></i><span>Utilisateurs</span></a>
            <a href="#"><i class="fas fa-calendar-check"></i><span>Rendez-vous</span></a>
            <a href="#"><i class="fas fa-blog"></i><span>Blog</span></a>
            <a href="#"><i class="fas fa-exclamation-triangle"></i><span>Signalements</span></a>
            <a href="#"><i class="fas fa-headset"></i><span>Services</span></a>
            <hr>
            <a href="../../index.php"><i class="fas fa-home"></i><span>Accueil</span></a>
            <a href="../../logout.php"><i class="fas fa-sign-out-alt"></i><span>Déconnexion</span></a>
        </div>
    </div>

    <div class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2><i class="fas fa-users me-2" style="color: #1a5e2a;"></i>Gestion des participants</h2>
                <p class="text-muted mb-0">Événement : <strong><?php echo htmlspecialchars($evenement['titre']); ?></strong></p>
            </div>
            <a href="liste.php" class="btn btn-secondary"><i class="fas fa-arrow-left me-2"></i>Retour</a>
        </div>

        <?php if ($message): ?>
        <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
            <i class="fas fa-<?php echo $messageType == 'success' ? 'check-circle' : 'exclamation-triangle'; ?> me-2"></i>
            <?php echo $message; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <div class="table-wrapper">
            <table class="table-pro">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Participant</th>
                        <th>Contact</th>
                        <th>Personnes</th>
                        <th>Date inscription</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($participations as $p): ?>
                    <tr>
                        <td>#<?php echo $p['id']; ?></td>
                        <td><strong><?php echo htmlspecialchars($p['prenom'] . ' ' . $p['nom']); ?></strong></td>
                        <td>
                            <i class="fas fa-envelope me-1 text-muted"></i> <?php echo htmlspecialchars($p['email']); ?><br>
                            <?php if (!empty($p['telephone'])): ?>
                            <i class="fas fa-phone me-1 text-muted"></i> <?php echo htmlspecialchars($p['telephone']); ?>
                            <?php endif; ?>
                        </td>
                        <td><?php echo $p['nombre_participants']; ?></td>
                        <td><?php echo date('d/m/Y', strtotime($p['date_participation'])); ?></td>
                        <td>
                            <?php if ($p['statut_validation'] == 'en_attente'): ?>
                                <span class="badge-en-attente"><i class="fas fa-clock me-1"></i> En attente</span>
                            <?php elseif ($p['statut_validation'] == 'valide'): ?>
                                <span class="badge-valide"><i class="fas fa-check-circle me-1"></i> Validé</span>
                            <?php else: ?>
                                <span class="badge-refuse"><i class="fas fa-times-circle me-1"></i> Refusé</span>
                            <?php endif; ?>
                            <?php if (!empty($p['commentaire_refus'])): ?>
                                <i class="fas fa-comment text-muted ms-1" title="<?php echo htmlspecialchars($p['commentaire_refus']); ?>"></i>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($p['statut_validation'] == 'en_attente'): ?>
                                <div class="btn-group" role="group">
                                    <form method="POST" class="d-inline">
                                        <input type="hidden" name="participation_id" value="<?php echo $p['id']; ?>">
                                        <button type="submit" name="action" value="valider" class="btn btn-success btn-sm" title="Valider">
                                            <i class="fas fa-check"></i> Valider
                                        </button>
                                    </form>
                                    <button type="button" class="btn btn-danger btn-sm" title="Refuser" data-bs-toggle="modal" data-bs-target="#refuseModal<?php echo $p['id']; ?>">
                                        <i class="fas fa-times"></i> Refuser
                                    </button>
                                </div>
                            <?php elseif ($p['statut_validation'] == 'valide'): ?>
                                <span class="text-success"><i class="fas fa-check-circle me-1"></i> Déjà validé</span>
                            <?php else: ?>
                                <span class="text-danger"><i class="fas fa-times-circle me-1"></i> Refusé</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    
                    <!-- Modal Refus -->
                    <div class="modal fade" id="refuseModal<?php echo $p['id']; ?>" tabindex="-1">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <form method="POST">
                                    <input type="hidden" name="participation_id" value="<?php echo $p['id']; ?>">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Refuser l'inscription</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <p>Refuser l'inscription de :</p>
                                        <div class="alert alert-light">
                                            <strong><?php echo htmlspecialchars($p['prenom'] . ' ' . $p['nom']); ?></strong><br>
                                            <small><?php echo $p['nombre_participants']; ?> personne(s) - <?php echo $p['email']; ?></small>
                                        </div>
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
                </tbody>
            </table>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>