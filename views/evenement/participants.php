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

// Traitement des actions (valider/refuser)
$message = '';
$messageType = '';
$generatedTicket = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && isset($_POST['participation_id'])) {
        $participation_id = intval($_POST['participation_id']);
        
        if ($_POST['action'] === 'valider') {
            $result = $participationC->validerParticipation($participation_id);
            if ($result['success']) {
                $message = '✅ Participation validée avec succès !';
                $messageType = 'success';
                $generatedTicket = $result;
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
        } elseif ($_POST['action'] === 'supprimer') {
            // Récupérer user_id et event_id depuis la participation
            $participations = $participationC->getParticipationsByEvent($event_id);
            foreach ($participations as $p) {
                if ($p['id'] == $participation_id) {
                    $result = $participationC->annulerParticipation($p['user_id'], $p['event_id']);
                    break;
                }
            }
            if ($result['success']) {
                $message = '🗑️ Participation supprimée';
                $messageType = 'info';
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

$isLoggedIn = isset($_SESSION['user_id']);
$isAdmin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
$userName = isset($_SESSION['prenom']) ? $_SESSION['prenom'] . ' ' . $_SESSION['nom'] : 'Invité';

// Statistiques
$totalInscrits = array_sum(array_column($participations, 'nombre_participants'));
$totalValides = $participationC->compterParticipationsValidees($event_id);
$totalAttente = $participationC->compterParticipationsEnAttente($event_id);
$totalRefuses = 0;
foreach ($participations as $p) {
    if ($p['statut_validation'] == 'refuse') {
        $totalRefuses += $p['nombre_participants'];
    }
}
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
        :root {
            --primary: #1a5e2a;
            --primary-light: #2e7d32;
            --gradient: linear-gradient(135deg, #1a5e2a, #4caf50);
            --shadow: 0 5px 15px rgba(0,0,0,0.05);
            --radius: 16px;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #e8f5e9 0%, #f0f4f0 100%);
            min-height: 100vh;
        }
        /* Navbar */
        .navbar {
            background: white;
            box-shadow: var(--shadow);
            padding: 0.75rem 2rem;
            position: sticky;
            top: 0;
            z-index: 1000;
        }
        .navbar-brand {
            font-weight: 700;
            font-size: 1.35rem;
            color: var(--primary) !important;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .navbar-brand img { border-radius: 10px; }
        .nav-link {
            font-weight: 500;
            color: #4a5568;
            transition: all 0.2s;
            padding: 0.5rem 1rem;
            border-radius: 8px;
        }
        .nav-link:hover, .nav-link.active {
            color: var(--primary);
            background: #e8f5e9;
        }
        .btn-outline-custom {
            border: 2px solid var(--primary);
            color: var(--primary);
            background: transparent;
            padding: 5px 12px;
            border-radius: 8px;
            font-weight: 500;
            font-size: 0.8rem;
        }
        .btn-outline-custom:hover { background: var(--primary); color: white; }
        /* Stats Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-bottom: 25px;
        }
        .stat-card {
            background: white;
            border-radius: 16px;
            padding: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: var(--shadow);
            transition: all 0.3s;
            border-left: 4px solid var(--primary);
        }
        .stat-card:hover { transform: translateY(-3px); box-shadow: 0 8px 20px rgba(0,0,0,0.1); }
        .stat-info h3 { font-size: 0.7rem; text-transform: uppercase; color: #666; letter-spacing: 0.5px; margin-bottom: 5px; }
        .stat-info h2 { font-size: 1.5rem; font-weight: 700; color: var(--primary); margin: 0; }
        .stat-icon { width: 45px; height: 45px; background: #e8f5e9; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; color: var(--primary); }
        /* Table */
        .table-wrapper {
            background: white;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: var(--shadow);
        }
        .table-custom {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.8rem;
        }
        .table-custom thead th {
            background: var(--gradient);
            color: white;
            padding: 12px 15px;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .table-custom tbody td {
            padding: 12px 15px;
            border-bottom: 1px solid #e8f5e9;
            vertical-align: middle;
        }
        .table-custom tbody tr:hover { background: #e8f5e9; }
        /* Badges */
        .badge-en-attente { background: #ff9800; color: white; padding: 5px 10px; border-radius: 20px; font-size: 0.7rem; font-weight: 600; display: inline-flex; align-items: center; gap: 5px; }
        .badge-valide { background: #4caf50; color: white; padding: 5px 10px; border-radius: 20px; font-size: 0.7rem; font-weight: 600; display: inline-flex; align-items: center; gap: 5px; }
        .badge-refuse { background: #f44336; color: white; padding: 5px 10px; border-radius: 20px; font-size: 0.7rem; font-weight: 600; display: inline-flex; align-items: center; gap: 5px; }
        /* Buttons */
        .btn {
            font-family: 'Inter', sans-serif;
            font-weight: 500;
            font-size: 0.7rem;
            padding: 5px 12px;
            border-radius: 8px;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            border: none;
            cursor: pointer;
        }
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
        .btn-primary {
            background: var(--gradient);
            color: white;
        }
        .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(26,94,42,0.3); }
        /* Modal */
        .modal-content { border-radius: 16px; }
        .modal-header { background: var(--gradient); color: white; border: none; }
        .modal-header .btn-close { filter: brightness(0) invert(1); }
        /* Footer */
        .footer {
            background: white;
            text-align: center;
            padding: 1.5rem;
            margin-top: 2rem;
            color: #666;
            font-size: 0.8rem;
            border-top: 1px solid rgba(0,0,0,0.05);
        }
        @media (max-width: 768px) {
            .stats-grid { grid-template-columns: repeat(2, 1fr); gap: 15px; }
            .table-wrapper { overflow-x: auto; }
        }
    </style>
</head>
<body>

    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg">
        <div class="container">
            <a class="navbar-brand" href="../../index.php">
                <img src="../../logo.jpeg" alt="Logo" height="35">
                Smart Municipality
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav mx-auto">
                    <li class="nav-item"><a class="nav-link" href="../../index.php"><i class="fas fa-th-large me-1"></i> Catégories</a></li>
                    <li class="nav-item"><a class="nav-link" href="#"><i class="fas fa-users me-1"></i> Utilisateurs</a></li>
                    <li class="nav-item"><a class="nav-link" href="#"><i class="fas fa-calendar-check me-1"></i> Rendez-vous</a></li>
                    <li class="nav-item"><a class="nav-link" href="#"><i class="fas fa-blog me-1"></i> Blog</a></li>
                    <li class="nav-item"><a class="nav-link" href="#"><i class="fas fa-exclamation-triangle me-1"></i> Signalements</a></li>
                    <li class="nav-item"><a class="nav-link" href="#"><i class="fas fa-headset me-1"></i> Services</a></li>
                    <?php if ($isLoggedIn): ?>
                    <li class="nav-item"><a class="nav-link" href="../participation/mes_participations.php"><i class="fas fa-ticket-alt me-1"></i> Mes inscriptions</a></li>
                    <?php endif; ?>
                </ul>
                <div class="dropdown">
                    <button class="btn btn-outline-custom dropdown-toggle" data-bs-toggle="dropdown" style="font-size: 0.8rem; padding: 5px 12px;">
                        <i class="fas fa-user-circle me-1"></i> <?php echo htmlspecialchars($userName); ?>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <?php if ($isAdmin): ?>
                        <li><a class="dropdown-item" href="../dashboard/admin.php"><i class="fas fa-chart-line me-2"></i>Dashboard</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <?php endif; ?>
                        <li><a class="dropdown-item text-danger" href="../../logout.php"><i class="fas fa-sign-out-alt me-2"></i>Déconnexion</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <!-- En-tête -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2><i class="fas fa-users me-2" style="color: var(--primary);"></i>Gestion des participants</h2>
                <p class="text-muted mb-0">
                    Événement : <strong><?php echo htmlspecialchars($evenement['titre']); ?></strong><br>
                    <small><?php echo htmlspecialchars($evenement['lieu']); ?> - <?php echo date('d/m/Y', strtotime($evenement['date_evenement'])); ?> à <?php echo $evenement['heure']; ?></small>
                </p>
            </div>
            <a href="liste.php" class="btn btn-secondary"><i class="fas fa-arrow-left me-2"></i>Retour</a>
        </div>

        <!-- Message -->
        <?php if ($message): ?>
        <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
            <i class="fas fa-<?php echo $messageType == 'success' ? 'check-circle' : ($messageType == 'warning' ? 'exclamation-triangle' : 'info-circle'); ?> me-2"></i>
            <?php echo $message; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <!-- Ticket généré -->
        <?php if ($generatedTicket && isset($generatedTicket['qr_file'])): ?>
        <div class="alert alert-success">
            <i class="fas fa-ticket-alt me-2"></i>
            <strong>Ticket généré !</strong>
            <a href="../../uploads/qrcodes/<?php echo $generatedTicket['qr_file']; ?>" class="btn btn-sm btn-success ms-2" target="_blank">
                <i class="fas fa-qrcode"></i> Voir le ticket
            </a>
        </div>
        <?php endif; ?>

        <!-- Statistiques -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-info">
                    <h3>Inscriptions</h3>
                    <h2><?php echo $totalInscrits; ?></h2>
                    <small>personnes inscrites</small>
                </div>
                <div class="stat-icon"><i class="fas fa-calendar-check"></i></div>
            </div>
            <div class="stat-card">
                <div class="stat-info">
                    <h3>Validés</h3>
                    <h2><?php echo $totalValides; ?></h2>
                    <small>acceptés</small>
                </div>
                <div class="stat-icon"><i class="fas fa-check-circle"></i></div>
            </div>
            <div class="stat-card">
                <div class="stat-info">
                    <h3>En attente</h3>
                    <h2><?php echo $totalAttente; ?></h2>
                    <small>à valider</small>
                </div>
                <div class="stat-icon"><i class="fas fa-clock"></i></div>
            </div>
            <div class="stat-card">
                <div class="stat-info">
                    <h3>Refusés</h3>
                    <h2><?php echo $totalRefuses; ?></h2>
                    <small>non acceptés</small>
                </div>
                <div class="stat-icon"><i class="fas fa-times-circle"></i></div>
            </div>
        </div>

        <!-- Tableau des participants -->
        <div class="table-wrapper">
            <table class="table-custom">
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
                    <?php if (empty($participations)): ?>
                    <tr>
                        <td colspan="7" class="text-center py-4">
                            <i class="fas fa-users fa-2x text-muted mb-2 d-block"></i>
                            Aucune inscription pour cet événement
                        </td>
                    </tr>
                    <?php else: ?>
                        <?php foreach($participations as $p): ?>
                        <tr>
                            <td>#<?php echo $p['id']; ?></td>
                            <td><strong><?php echo htmlspecialchars($p['prenom'] . ' ' . $p['nom']); ?></strong></td>
                            <td>
                                <i class="fas fa-envelope me-1 text-muted"></i> <?php echo htmlspecialchars($p['email']); ?><br>
                                <?php if (!empty($p['telephone'])): ?>
                                <i class="fas fa-phone me-1 text-muted"></i> <?php echo htmlspecialchars($p['telephone']); ?>
                                <?php endif; ?>
                            
                            <td><?php echo $p['nombre_participants']; ?> personne(s)</td>
                            <td><?php echo date('d/m/Y H:i', strtotime($p['date_participation'])); ?></td>
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
                             
                            <td class="text-nowrap">
                                <?php if ($p['statut_validation'] == 'en_attente'): ?>
                                    <form method="POST" class="d-inline">
                                        <input type="hidden" name="participation_id" value="<?php echo $p['id']; ?>">
                                        <button type="submit" name="action" value="valider" class="btn btn-success btn-sm" title="Valider">
                                            <i class="fas fa-check"></i>
                                        </button>
                                    </form>
                                    <button type="button" class="btn btn-danger btn-sm" title="Refuser" data-bs-toggle="modal" data-bs-target="#refuseModal<?php echo $p['id']; ?>">
                                        <i class="fas fa-times"></i>
                                    </button>
                                <?php elseif ($p['statut_validation'] == 'valide'): ?>
                                    <span class="text-success"><i class="fas fa-check-circle me-1"></i> Validé</span>
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
                                            <h5 class="modal-title"><i class="fas fa-times-circle me-2"></i>Refuser l'inscription</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <p>Refuser l'inscription de :</p>
                                            <div class="alert alert-light border">
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
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <p class="mb-0">&copy; 2024 Smart Municipality - Tous droits réservés</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>