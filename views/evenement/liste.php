<?php
session_start();
require_once __DIR__ . '/../../controller/EvenementC.php';
require_once __DIR__ . '/../../controller/ParticipationC.php';
require_once __DIR__ . '/../../controller/CategorieEvenementC.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../../index.php');
    exit();
}

$evenementC = new EvenementC();
$participationC = new ParticipationC();
$categorieC = new CategorieEvenementC();

$evenements = $evenementC->afficherEvenements();
$categories = $categorieC->afficherCategories();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des événements - Smart Municipality</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-green: #2e7d32;
            --secondary-green: #4caf50;
            --light-green: #e8f5e9;
        }
        body {
            background: var(--light-green);
        }
        .sidebar {
            min-height: 100vh;
            background: linear-gradient(180deg, #1b5e20 0%, var(--primary-green) 100%);
            position: fixed;
            width: 280px;
        }
        .sidebar-brand {
            padding: 30px 25px;
            text-align: center;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        .sidebar-brand h3 {
            color: white;
            margin: 10px 0 0;
        }
        .sidebar-nav a {
            display: flex;
            align-items: center;
            padding: 12px 25px;
            color: rgba(255,255,255,0.85);
            text-decoration: none;
            transition: all 0.3s;
        }
        .sidebar-nav a:hover, .sidebar-nav a.active {
            background: rgba(255,255,255,0.15);
            color: white;
            border-left: 4px solid white;
        }
        .sidebar-nav a i {
            width: 28px;
            margin-right: 12px;
        }
        .main-content {
            margin-left: 280px;
            padding: 25px;
        }
        .card-custom {
            background: white;
            border-radius: 20px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
            border: none;
        }
        .btn-green {
            background: var(--primary-green);
            color: white;
            border: none;
        }
        .btn-green:hover {
            background: #1b5e20;
        }
        .table-custom th {
            background: var(--primary-green);
            color: white;
            border: none;
            padding: 15px;
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="sidebar-brand">
            <i class="fas fa-city fa-2x text-white"></i>
            <h3>Smart Municipality</h3>
        </div>
        <div class="sidebar-nav">
            <a href="../dashboard/admin.php">
                <i class="fas fa-tachometer-alt"></i> Dashboard
            </a>
            <a href="liste.php" class="active">
                <i class="fas fa-calendar-alt"></i> Événements
            </a>
            <a href="../dashboard/categorie/liste.php">
                <i class="fas fa-tags"></i> Catégories
            </a>
            <a href="../participation/mes_participations.php">
                <i class="fas fa-users"></i> Participations
            </a>
            <hr style="border-color: rgba(255,255,255,0.1);">
            <a href="../../index.php">
                <i class="fas fa-home"></i> Accueil
            </a>
            <a href="../../logout.php">
                <i class="fas fa-sign-out-alt"></i> Déconnexion
            </a>
        </div>
    </div>

    <div class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-calendar-alt me-2 text-success"></i>Gestion des événements</h2>
            <a href="ajouter.php" class="btn btn-green">
                <i class="fas fa-plus me-2"></i>Ajouter un événement
            </a>
        </div>

        <div class="card-custom">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-custom">
                        <tr>
                            <th>ID</th>
                            <th>Titre</th>
                            <th>Lieu</th>
                            <th>Date</th>
                            <th>Heure</th>
                            <th>Catégorie</th>
                            <th>Participants</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($evenements as $event): ?>
                        <tr>
                            <td>#<?php echo $event['id']; ?></td>
                            <td><strong><?php echo htmlspecialchars($event['titre']); ?></strong></td>
                            <td><?php echo htmlspecialchars($event['lieu']); ?></td>
                            <td><?php echo date('d/m/Y', strtotime($event['date_evenement'])); ?></
                            <td><?php echo $event['heure']; ?></td>
                            <td><span class="badge" style="background: var(--secondary-green);"><?php echo htmlspecialchars($event['categorie_nom'] ?? 'N/A'); ?></span></td>
                            <td>
                                <?php 
                                $nb = $participationC->compterParticipationsParEvenement($event['id']);
                                ?>
                                <span class="badge bg-info"><?php echo $nb; ?> inscrits</span>
                             </
                            <td>
                                <a href="modifier.php?id=<?php echo $event['id']; ?>" class="btn btn-sm btn-warning">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="supprimer.php?id=<?php echo $event['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Supprimer cet événement ?')">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>