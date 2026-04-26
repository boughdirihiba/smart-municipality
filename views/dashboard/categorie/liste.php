<?php
session_start();
require_once __DIR__ . '/../../../controller/CategorieEvenementC.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../../../index.php');
    exit();
}

$categorieC = new CategorieEvenementC();
$categories = $categorieC->afficherCategories();

// Recherche et tri
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$order_by = isset($_GET['order_by']) ? $_GET['order_by'] : 'id';
$order_dir = isset($_GET['order_dir']) && $_GET['order_dir'] === 'asc' ? 'asc' : 'desc';

if (!empty($search)) {
    $categories = array_filter($categories, function($c) use ($search) {
        return stripos($c['nom'], $search) !== false || stripos($c['description'], $search) !== false;
    });
}

usort($categories, function($a, $b) use ($order_by, $order_dir) {
    $val1 = $a[$order_by] ?? '';
    $val2 = $b[$order_by] ?? '';
    return $order_dir === 'asc' ? $val1 <=> $val2 : $val2 <=> $val1;
});
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des catégories - Smart Municipality</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-50: #e8f5e9;
            --primary-500: #4caf50;
            --primary-600: #43a047;
            --primary-700: #388e3c;
            --primary-800: #2e7d32;
            --gradient-primary: linear-gradient(135deg, #2e7d32, #4caf50);
            --gradient-sidebar: linear-gradient(180deg, #1b5e20 0%, #2e7d32 100%);
        }
        body { font-family: 'Inter', sans-serif; background: var(--primary-50); }
        .sidebar { min-height: 100vh; background: var(--gradient-sidebar); position: fixed; width: 280px; }
        .sidebar-header { padding: 1.5rem; text-align: center; border-bottom: 1px solid rgba(255,255,255,0.1); }
        .sidebar-header img { border-radius: 12px; margin-bottom: 0.75rem; }
        .sidebar-header h3 { color: white; font-size: 1.2rem; }
        .sidebar-nav a { display: flex; align-items: center; padding: 0.75rem 1.5rem; color: rgba(255,255,255,0.8); text-decoration: none; transition: 0.2s; }
        .sidebar-nav a i { width: 28px; margin-right: 12px; }
        .sidebar-nav a:hover, .sidebar-nav a.active { background: rgba(255,255,255,0.12); color: white; border-left: 3px solid var(--primary-500); }
        .main-content { margin-left: 280px; padding: 1.5rem; }
        .btn-primary-custom { background: var(--gradient-primary); border: none; }
        .btn-primary-custom:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(46,125,50,0.3); }
        .table-container { background: white; border-radius: 16px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.04); }
        .table-modern thead th { background: var(--gradient-primary); color: white; padding: 1rem; font-weight: 600; }
        .table-modern tbody td { padding: 1rem; vertical-align: middle; }
        .table-modern tbody tr:hover { background: var(--primary-50); }
        .sort-link { color: white; text-decoration: none; display: inline-flex; align-items: center; gap: 5px; }
        .sort-link:hover { color: #ddd; }
        .filter-bar { background: white; border-radius: 16px; padding: 1rem; margin-bottom: 1.5rem; display: flex; gap: 1rem; align-items: center; flex-wrap: wrap; }
        .categorie-image { width: 45px; height: 45px; object-fit: cover; border-radius: 10px; border: 2px solid var(--primary-200); }
        @media (max-width: 768px) { .sidebar { width: 80px; } .sidebar-header h3, .sidebar-nav a span { display: none; } .sidebar-nav a { justify-content: center; } .sidebar-nav a i { margin-right: 0; } .main-content { margin-left: 80px; } }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="sidebar-header"><img src="../../../logo.jpeg" alt="Logo" height="45"><h3>Smart Municipality</h3></div>
        <div class="sidebar-nav">
            <a href="../admin.php"><i class="fas fa-tachometer-alt"></i><span>Dashboard</span></a>
            <a href="../../evenement/liste.php"><i class="fas fa-calendar-alt"></i><span>Événements</span></a>
            <a href="liste.php" class="active"><i class="fas fa-tags"></i><span>Catégories</span></a>
            <a href="../../participation/mes_participations.php"><i class="fas fa-users"></i><span>Participations</span></a>
            <hr style="border-color: rgba(255,255,255,0.1);">
            <a href="../../../index.php"><i class="fas fa-home"></i><span>Accueil</span></a>
            <a href="../../../logout.php"><i class="fas fa-sign-out-alt"></i><span>Déconnexion</span></a>
        </div>
    </div>

    <div class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-4"><h2><i class="fas fa-tags me-2" style="color: var(--primary-700);"></i>Gestion des catégories</h2><a href="ajouter.php" class="btn btn-primary-custom"><i class="fas fa-plus me-2"></i>Ajouter</a></div>

        <div class="filter-bar">
            <form method="GET" class="d-flex gap-2 flex-grow-1">
                <div class="input-group"><span class="input-group-text bg-white"><i class="fas fa-search text-success"></i></span><input type="text" name="search" class="form-control" placeholder="Rechercher..." value="<?php echo htmlspecialchars($search); ?>"></div>
                <button type="submit" class="btn btn-primary-custom"><i class="fas fa-search me-1"></i> Chercher</button>
                <?php if ($search): ?><a href="liste.php" class="btn btn-outline-secondary"><i class="fas fa-times me-1"></i> Réinitialiser</a><?php endif; ?>
            </form>
        </div>

        <div class="table-container">
            <table class="table table-modern mb-0">
                <thead><tr>
                    <th><a href="?order_by=id&order_dir=<?php echo $order_by == 'id' && $order_dir == 'asc' ? 'desc' : 'asc'; ?>" class="sort-link">ID <i class="fas fa-sort"></i></a></th>
                    <th>Image</th>
                    <th><a href="?order_by=nom&order_dir=<?php echo $order_by == 'nom' && $order_dir == 'asc' ? 'desc' : 'asc'; ?>" class="sort-link">Nom <i class="fas fa-sort"></i></a></th>
                    <th>Description</th>
                    <th><a href="?order_by=created_at&order_dir=<?php echo $order_by == 'created_at' && $order_dir == 'asc' ? 'desc' : 'asc'; ?>" class="sort-link">Créée le <i class="fas fa-sort"></i></a></th>
                    <th>Événements</th>
                    <th>Actions</th>
                </tr></thead>
                <tbody><?php foreach($categories as $c): ?><tr>
                    <td>#<?php echo $c['id']; ?></td>
                    <td><?php if($c['image_url'] && file_exists('../../../' . $c['image_url'])): ?><img src="../../../<?php echo $c['image_url']; ?>" class="categorie-image"><?php else: ?><i class="fas fa-image fa-2x text-muted"></i><?php endif; ?></td>
                    <td><strong><?php echo htmlspecialchars($c['nom']); ?></strong></td>
                    <td><?php echo substr(htmlspecialchars($c['description']), 0, 60); ?>...</td>
                    <td><?php echo date('d/m/Y', strtotime($c['created_at'])); ?></td>
                    <td><span class="badge" style="background: var(--primary-600); color: white;"><?php echo $categorieC->compterEvenementsParCategorie($c['id']); ?> événements</span></td>
                    <td><a href="modifier.php?id=<?php echo $c['id']; ?>" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i></a> <a href="supprimer.php?id=<?php echo $c['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Supprimer ?')"><i class="fas fa-trash"></i></a></td>
                </tr><?php endforeach; ?></tbody>
            </table>
        </div>
    </div>
</body>
</html>