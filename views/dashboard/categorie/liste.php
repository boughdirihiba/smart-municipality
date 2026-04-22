<?php
session_start();
require_once __DIR__ . '/../../../controller/CategorieEvenementC.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../../../index.php');
    exit();
}

$categorieC = new CategorieEvenementC();
$categories = $categorieC->afficherCategories();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des catégories - Smart Municipality</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-dark: #1a5e2a;
            --primary-dark-hover: #0d3b1a;
            --secondary-dark: #2e7d32;
            --light-dark: #d4e6d4;
            --bg-dark: #e8f3e8;
        }
        body {
            background: var(--bg-dark);
            font-family: 'Segoe UI', sans-serif;
        }
        .sidebar {
            min-height: 100vh;
            background: linear-gradient(180deg, var(--primary-dark) 0%, var(--secondary-dark) 100%);
            position: fixed;
            width: 280px;
        }
        .sidebar-brand {
            padding: 30px 25px;
            text-align: center;
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
        .btn-dark-green {
            background: var(--primary-dark);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 12px;
            transition: all 0.3s;
        }
        .btn-dark-green:hover {
            background: var(--primary-dark-hover);
            transform: translateY(-2px);
        }
        .table-custom th {
            background: var(--primary-dark);
            color: white;
            border: none;
            padding: 15px;
        }
        .card-custom {
            background: white;
            border-radius: 20px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
            overflow: hidden;
        }
        .categorie-image {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 12px;
            border: 2px solid var(--primary-dark);
        }
        .badge-categorie {
            background: var(--primary-dark);
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
            <a href="../admin.php">
                <i class="fas fa-tachometer-alt"></i> Dashboard
            </a>
            <a href="../../evenement/liste.php">
                <i class="fas fa-calendar-alt"></i> Événements
            </a>
            <a href="liste.php" class="active">
                <i class="fas fa-tags"></i> Catégories
            </a>
            <a href="../../participation/mes_participations.php">
                <i class="fas fa-users"></i> Participations
            </a>
            <hr style="border-color: rgba(255,255,255,0.1);">
            <a href="../../../index.php">
                <i class="fas fa-home"></i> Accueil
            </a>
            <a href="../../../logout.php">
                <i class="fas fa-sign-out-alt"></i> Déconnexion
            </a>
        </div>
    </div>

    <div class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 style="color: var(--primary-dark);">
                <i class="fas fa-tags me-2"></i>
                Gestion des catégories
            </h2>
            <a href="ajouter.php" class="btn btn-dark-green">
                <i class="fas fa-plus me-2"></i>Ajouter une catégorie
            </a>
        </div>

        <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>Opération effectuée avec succès !
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <div class="card-custom">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-custom">
                        <tr>
                            <th>ID</th>
                            <th>Image</th>
                            <th>Nom</th>
                            <th>Description</th>
                            <th>Événements</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($categories as $cat): ?>
                        <tr>
                            <td><span class="badge bg-secondary">#<?php echo $cat['id']; ?></span></td>
                            <td>
                                <?php if($cat['image_url'] && file_exists('../../../' . $cat['image_url'])): ?>
                                <img src="../../../<?php echo $cat['image_url']; ?>" class="categorie-image" alt="<?php echo htmlspecialchars($cat['nom']); ?>">
                                <?php else: ?>
                                <div class="categorie-image-placeholder" style="width:50px;height:50px;background:var(--light-dark);border-radius:12px;display:flex;align-items:center;justify-content:center;color:var(--primary-dark);">
                                    <i class="fas fa-image"></i>
                                </div>
                                <?php endif; ?>
                             </
                            <td><strong><?php echo htmlspecialchars($cat['nom']); ?></strong></td>
                            <td><?php echo substr(htmlspecialchars($cat['description']), 0, 80); ?>...</
                            <td><span class="badge badge-categorie"><?php echo $categorieC->compterEvenementsParCategorie($cat['id']); ?> événements</span></td>
                            <td class="text-center">
                                <a href="modifier.php?id=<?php echo $cat['id']; ?>" class="btn btn-sm btn-warning me-1">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="supprimer.php?id=<?php echo $cat['id']; ?>" class="btn btn-sm btn-danger" 
                                   onclick="return confirm('Supprimer cette catégorie ?')">
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>