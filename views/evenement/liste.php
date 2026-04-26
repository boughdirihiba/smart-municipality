<?php
session_start();
require_once __DIR__ . '/../../controller/EvenementC.php';
require_once __DIR__ . '/../../controller/ParticipationC.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../../index.php');
    exit();
}

$evenementC = new EvenementC();
$participationC = new ParticipationC();

$evenements = $evenementC->afficherEvenements();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des événements - Smart Municipality</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background: #e8f5e9; }
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
        .btn { font-family: 'Inter', sans-serif; font-weight: 500; font-size: 0.8rem; padding: 8px 16px; border-radius: 10px; transition: all 0.2s; display: inline-flex; align-items: center; gap: 8px; border: none; cursor: pointer; }
        .btn-sm { font-size: 0.7rem; padding: 5px 12px; gap: 5px; }
        .btn-primary { background: linear-gradient(135deg, #1a5e2a, #4caf50); color: white; }
        .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(26,94,42,0.3); }
        .btn-warning { background: #f59e0b; color: white; }
        .btn-warning:hover { background: #d97706; transform: translateY(-2px); }
        .btn-danger { background: #dc2626; color: white; }
        .btn-danger:hover { background: #b91c1c; transform: translateY(-2px); }
        .btn-info { background: #0891b2; color: white; }
        .btn-info:hover { background: #0e7490; transform: translateY(-2px); }
        .table-wrapper { background: white; border-radius: 16px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.05); }
        .table-pro { width: 100%; border-collapse: collapse; font-size: 0.8rem; }
        .table-pro thead th { background: linear-gradient(135deg, #1a5e2a, #4caf50); color: white; padding: 12px 15px; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px; }
        .table-pro tbody td { padding: 12px 15px; border-bottom: 1px solid #e8f5e9; vertical-align: middle; }
        .table-pro tbody tr:hover { background: #e8f5e9; }
        .badge { padding: 5px 10px; border-radius: 20px; font-size: 0.7rem; font-weight: 600; }
        .filter-bar { background: white; border-radius: 16px; padding: 15px; margin-bottom: 20px; display: flex; gap: 15px; align-items: center; flex-wrap: wrap; }
        @media (max-width: 768px) { .sidebar { width: 80px; } .sidebar-header h3, .sidebar-nav a span { display: none; } .sidebar-nav a { justify-content: center; } .sidebar-nav a i { margin-right: 0; } .main-content { margin-left: 80px; } }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="sidebar-header"><img src="../../logo.jpeg" alt="Logo" height="45"><h3>Smart Municipality</h3></div>
        <div class="sidebar-nav">
            <a href="../dashboard/admin.php"><i class="fas fa-tachometer-alt"></i><span>Dashboard</span></a>
            <a href="liste.php" class="active"><i class="fas fa-calendar-alt"></i><span>Événements</span></a>
                <li class="nav-item"><a class="nav-link" href="views/calendrier/index.php"><i class="fas fa-calendar-week me-1"></i> Calendrier</a></li>  
            <a href="#"><i class="fas fa-users"></i><span>Utilisateurs</span></a>
            <a href="#"><i class="fas fa-calendar-check"></i><span>Rendez-vous</span></a>
            <a href="#"><i class="fas fa-blog"></i><span>Blog</span></a>
            <a href="#"><i class="fas fa-exclamation-triangle"></i><span>Signalements</span></a>
            <a href="#"><i class="fas fa-headset"></i><span>Services</span></a>
            <hr><a href="../../index.php"><i class="fas fa-home"></i><span>Accueil</span></a><a href="../../logout.php"><i class="fas fa-sign-out-alt"></i><span>Déconnexion</span></a>
        </div>
    </div>

    <div class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-4"><h2><i class="fas fa-calendar-alt me-2" style="color: #1a5e2a;"></i>Gestion des événements</h2><a href="ajouter.php" class="btn btn-primary"><i class="fas fa-plus me-2"></i>Ajouter</a></div>

        <div class="filter-bar"><form method="GET" class="d-flex gap-2 flex-grow-1"><div class="input-group"><span class="input-group-text bg-white"><i class="fas fa-search text-success"></i></span><input type="text" name="search" class="form-control" placeholder="Rechercher..."></div><button type="submit" class="btn btn-primary"><i class="fas fa-search me-1"></i>Chercher</button></form></div>

        <div class="table-wrapper">
            <table class="table-pro">
                <thead><tr><th>ID</th><th>Titre</th><th>Lieu</th><th>Date</th><th>Heure</th><th>Catégorie</th><th>Participants</th><th>Actions</th></tr></thead>
                <tbody><?php foreach($evenements as $e): ?><tr><td>#<?php echo $e['id']; ?></td><td><strong><?php echo htmlspecialchars($e['titre']); ?></strong></td><td><?php echo htmlspecialchars($e['lieu']); ?></td><td><?php echo date('d/m/Y', strtotime($e['date_evenement'])); ?></td><td><?php echo $e['heure']; ?></td><td><span class="badge" style="background: #e8f5e9; color: #1a5e2a;"><?php echo htmlspecialchars($e['categorie_nom'] ?? 'N/A'); ?></span></td><td><span class="badge bg-info"><?php echo $participationC->compterParticipationsParEvenement($e['id']); ?> inscrits</span></td><td><a href="modifier.php?id=<?php echo $e['id']; ?>" class="btn btn-warning btn-sm"><i class="fas fa-edit"></i></a><a href="participants.php?id=<?php echo $e['id']; ?>" class="btn btn-info btn-sm ms-1"><i class="fas fa-users"></i></a><a href="supprimer.php?id=<?php echo $e['id']; ?>" class="btn btn-danger btn-sm ms-1" onclick="return confirm('Supprimer ?')"><i class="fas fa-trash"></i></a></td></tr><?php endforeach; ?></tbody>
            </table>
        </div>
    </div>
</body>
</html>