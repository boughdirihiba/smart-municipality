<?php
session_start();
require_once __DIR__ . '/../../../controllers/CategorieEvenementC.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../../../index.php');
    exit();
}

$categorieC = new CategorieEvenementC();
$categories = $categorieC->afficherCategories();
$displayName = $_SESSION['prenom'] . ' ' . $_SESSION['nom'];
$userRole = $_SESSION['role'];
$avatarName = 'sidebar-photo.svg';
$currentRoute = 'categories';
$baseUrl = '../../../';

$message = '';
$messageType = '';
if (isset($_GET['success'])) {
    $message = 'Opération effectuée avec succès !';
    $messageType = 'success';
}
if (isset($_GET['error'])) {
    $message = htmlspecialchars($_GET['error']);
    $messageType = 'danger';
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des catégories - Smart Municipality</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../../../public/css/admin-sidebar.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Inter', sans-serif;
            background: #e8f5e9;
        }
        /* SIDEBAR */
        .sidebar {
            min-height: 100vh;
            background: linear-gradient(180deg, #0d3b1a 0%, #1a5e2a 100%);
            position: fixed;
            width: 280px;
            transition: all 0.3s;
            z-index: 1000;
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
        }
        .sidebar-header { padding: 25px; text-align: center; border-bottom: 1px solid rgba(255,255,255,0.1); }
        .sidebar-header img { border-radius: 15px; margin-bottom: 10px; border: 2px solid rgba(255,255,255,0.2); }
        .sidebar-header h3 { color: white; font-size: 1.2rem; margin: 0; }
        .sidebar-nav { padding: 20px 0; }
        .sidebar-nav a {
            display: flex;
            align-items: center;
            padding: 12px 25px;
            color: rgba(255,255,255,0.85);
            text-decoration: none;
            transition: all 0.3s;
            margin: 5px 10px;
            border-radius: 12px;
            font-weight: 500;
            font-size: 0.9rem;
        }
        .sidebar-nav a i { width: 28px; margin-right: 12px; font-size: 1.1rem; text-align: center; }
        .sidebar-nav a:hover, .sidebar-nav a.active { background: rgba(255,255,255,0.15); color: white; transform: translateX(5px); }
        .sidebar-nav hr { border-color: rgba(255,255,255,0.1); margin: 15px; }
        /* MAIN CONTENT */
        .main-content { margin-left: 280px; padding: 25px; }
        /* BOUTONS */
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
        .btn-primary { background: linear-gradient(135deg, #1a5e2a, #4caf50); color: white; }
        .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(26,94,42,0.3); }
        .btn-warning { background: #f59e0b; color: white; }
        .btn-danger { background: #dc2626; color: white; }
        .btn-success { background: #2e7d32; color: white; }
        /* TABLEAU */
        .table-wrapper { background: white; border-radius: 16px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.05); }
        .table-pro { width: 100%; border-collapse: collapse; font-size: 0.8rem; }
        .table-pro thead th { background: linear-gradient(135deg, #1a5e2a, #4caf50); color: white; padding: 12px 15px; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px; }
        .table-pro tbody td { padding: 12px 15px; border-bottom: 1px solid #e8f5e9; vertical-align: middle; }
        .table-pro tbody tr:hover { background: #e8f5e9; }
        .categorie-image { width: 45px; height: 45px; object-fit: cover; border-radius: 10px; border: 2px solid #e8f5e9; }
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
    <?php require_once __DIR__ . '/../../partials/admin_sidebar.php'; ?>

    <!-- Main Content -->
    <div class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-tags me-2" style="color: #1a5e2a;"></i>Gestion des catégories</h2>
            <a href="ajouter.php" class="btn btn-primary"><i class="fas fa-plus me-2"></i>Ajouter une catégorie</a>
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
                        <th>Image</th>
                        <th>Nom</th>
                        <th>Description</th>
                        <th>Événements</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($categories as $cat): ?>
                    <tr>
                        <td>#<?php echo $cat['id']; ?></td>
                        <td>
                            <?php if($cat['image_url'] && file_exists('../../../' . $cat['image_url'])): ?>
                                <img src="../../../<?php echo $cat['image_url']; ?>" class="categorie-image" alt="<?php echo htmlspecialchars($cat['nom']); ?>">
                            <?php else: ?>
                                <div style="width:45px;height:45px;background:#e8f5e9;border-radius:10px;display:flex;align-items:center;justify-content:center;color:#1a5e2a;">
                                    <i class="fas fa-image"></i>
                                </div>
                            <?php endif; ?>
                        </td>
                        <td><strong><?php echo htmlspecialchars($cat['nom']); ?></strong></td>
                        <td><?php echo substr(htmlspecialchars($cat['description']), 0, 80); ?>...</td>
                        <td><span class="badge" style="background: #e8f5e9; color: #1a5e2a; padding: 5px 10px;"><?php echo $categorieC->compterEvenementsParCategorie($cat['id']); ?> événements</span></td>
                        <td>
                            <a href="modifier.php?id=<?php echo $cat['id']; ?>" class="btn btn-warning btn-sm"><i class="fas fa-edit"></i></a>
                            <a href="supprimer.php?id=<?php echo $cat['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Supprimer cette catégorie ?')"><i class="fas fa-trash"></i></a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
