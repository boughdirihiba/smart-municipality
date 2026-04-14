<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Smart Municipality - Gestion des Événements</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&family=Roboto:wght@300;400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #f0f4f8;
        }
        
        /* ========== SIDEBAR VERT DÉGRADÉ ========== */
        .sidebar {
            width: 280px;
            background: linear-gradient(135deg, #0a2e1f 0%, #1a5a3a 50%, #0d3d26 100%);
            min-height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            box-shadow: 2px 0 15px rgba(0,0,0,0.15);
            overflow-y: auto;
            transition: all 0.3s ease;
        }
        
        .sidebar::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, #2ecc71, #27ae60, #2ecc71);
        }
        
        .sidebar::-webkit-scrollbar {
            width: 5px;
        }
        
        .sidebar::-webkit-scrollbar-track {
            background: rgba(255,255,255,0.1);
            border-radius: 10px;
        }
        
        .sidebar::-webkit-scrollbar-thumb {
            background: #2ecc71;
            border-radius: 10px;
        }
        
        .logo {
            padding: 25px 24px 30px;
            border-bottom: 1px solid rgba(255,255,255,0.15);
            margin-bottom: 20px;
            background: linear-gradient(90deg, rgba(255,255,255,0.05), transparent);
        }
        
        .logo h2 {
            color: white;
            font-family: 'Poppins', sans-serif;
            font-size: 18px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .logo h2 i {
            font-size: 28px;
            color: #2ecc71;
        }
        
        .nav-item {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 14px 24px;
            margin: 5px 12px;
            text-decoration: none;
            color: rgba(255,255,255,0.8);
            border-radius: 12px;
            transition: all 0.3s ease;
            font-size: 14px;
            font-weight: 500;
            position: relative;
            overflow: hidden;
        }
        
        .nav-item::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(46,204,113,0.2), transparent);
            transition: left 0.5s;
        }
        
        .nav-item:hover::before {
            left: 100%;
        }
        
        .nav-item:hover {
            background: linear-gradient(90deg, rgba(46,204,113,0.2), rgba(46,204,113,0.05));
            color: white;
            transform: translateX(5px);
        }
        
        .nav-item.active {
            background: linear-gradient(90deg, #27ae60, #1e8449);
            color: white;
            box-shadow: 0 2px 8px rgba(39,174,96,0.3);
        }
        
        .nav-item i {
            width: 20px;
            font-size: 16px;
        }
        
        /* Main Content */
        .main-content {
            margin-left: 280px;
            padding: 25px 35px;
        }
        
        /* Bouton Dashboard */
        .dashboard-btn-container {
            margin-bottom: 25px;
        }
        
        .btn-dashboard {
            background: linear-gradient(135deg, #2ecc71 0%, #27ae60 100%);
            color: white;
            padding: 12px 25px;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            font-size: 15px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            transition: all 0.3s;
            text-decoration: none;
        }
        
        .btn-dashboard:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(46,204,113,0.3);
        }
        
        /* Header */
        .manage-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            flex-wrap: wrap;
            gap: 15px;
        }
        
        .manage-header h1 {
            color: #1e8449;
            font-family: 'Poppins', sans-serif;
            font-size: 24px;
        }
        
        /* Bouton Ajouter vert clair */
        .btn-add {
            background: linear-gradient(135deg, #2ecc71 0%, #27ae60 100%);
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s;
        }
        
        .btn-add:hover {
            background: linear-gradient(135deg, #27ae60 0%, #1e8449 100%);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(46,204,113,0.3);
        }
        
        /* Filters */
        .filters {
            background: white;
            padding: 15px 20px;
            border-radius: 12px;
            margin-bottom: 25px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.08);
        }
        
        .filter-group {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .filter-group label {
            font-weight: 500;
            color: #333;
        }
        
        .filter-group select {
            padding: 8px 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 14px;
            background: white;
            cursor: pointer;
        }
        
        /* Table */
        .table-container {
            background: white;
            border-radius: 16px;
            overflow-x: auto;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }
        
        .data-table {
            width: 100%;
            border-collapse: collapse;
            min-width: 800px;
        }
        
        .data-table th {
            background: #1e8449;
            color: white;
            padding: 15px 18px;
            text-align: left;
            font-weight: 600;
            font-size: 13px;
        }
        
        .data-table td {
            padding: 14px 18px;
            border-bottom: 1px solid #eef2f6;
            font-size: 14px;
            vertical-align: middle;
        }
        
        .data-table tr:hover {
            background: #f8f9fa;
        }
        
        /* Image dans le tableau */
        .event-thumb {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 8px;
            background: #f0f0f0;
        }
        
        .no-image {
            width: 50px;
            height: 50px;
            background: #e9ecef;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #6c757d;
            font-size: 20px;
        }
        
        .badge {
            display: inline-block;
            padding: 5px 12px;
            background: #d5f5e3;
            color: #1e8449;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        
        /* Boutons Modifier et Supprimer */
        .action-buttons {
            display: flex;
            gap: 10px;
        }
        
        .btn-edit {
            background: #2ecc71;
            color: white;
            padding: 8px 16px;
            border-radius: 8px;
            text-decoration: none;
            font-size: 12px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: all 0.2s;
            border: none;
            cursor: pointer;
        }
        
        .btn-edit:hover {
            background: #27ae60;
            transform: translateY(-2px);
        }
        
        .btn-delete {
            background: #e74c3c;
            color: white;
            padding: 8px 16px;
            border-radius: 8px;
            text-decoration: none;
            font-size: 12px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: all 0.2s;
            border: none;
            cursor: pointer;
        }
        
        .btn-delete:hover {
            background: #c0392b;
            transform: translateY(-2px);
        }
        
        /* Alertes */
        .alert {
            padding: 14px 20px;
            border-radius: 10px;
            margin-bottom: 20px;
        }
        
        .alert-success {
            background: #d5f5e3;
            color: #1e8449;
            border-left: 4px solid #2ecc71;
        }
        
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border-left: 4px solid #e74c3c;
        }
        
        .no-events {
            text-align: center;
            padding: 50px;
            color: #6c757d;
        }
        
        /* Responsive */
        @media (max-width: 900px) {
            .sidebar {
                width: 80px;
            }
            .sidebar .logo h2 span:last-child,
            .sidebar .nav-item span:last-child {
                display: none;
            }
            .main-content {
                margin-left: 80px;
            }
        }
        
        @media (max-width: 600px) {
            .main-content {
                padding: 15px;
            }
            .action-buttons {
                flex-direction: column;
                gap: 5px;
            }
            .btn-edit, .btn-delete {
                padding: 6px 12px;
                font-size: 10px;
            }
        }
        .logo {
    padding: 20px 24px 25px;
    border-bottom: 1px solid rgba(255,255,255,0.15);
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 12px;
}

.logo-img {
    width: 45px;
    height: 45px;
    object-fit: cover;
    border-radius: 12px;
    background: white;
    padding: 5px;
}

.logo-text {
    display: flex;
    flex-direction: column;
}

.logo-title {
    color: white;
    font-family: 'Poppins', sans-serif;
    font-size: 18px;
    font-weight: 700;
    line-height: 1.2;
}
    </style>
</head>
<body>

<!-- Sidebar Vert -->
<div class="sidebar">
    <div class="logo">
    <img src="assets/logo.jpeg" alt="Smart Municipality" class="logo-img">
    <div class="logo-text">
        <span class="logo-title">Smart Municipality</span>
        
    </div>
</div>
    
    <!-- Profile -->
    <a href="profile.php" class="nav-item">
        <i class="fas fa-user-circle"></i>
        <span>Profile</span>
    </a>
    
    <!-- Événements (actif) -->
    <a href="index.php?action=manage" class="nav-item active">
        <i class="fas fa-calendar-alt"></i>
        <span>Événements</span>
    </a>
    
    <!-- Dashboard -->

    
    <!-- Carte intelligente -->
    <a href="carte.php" class="nav-item">
        <i class="fas fa-map-marked-alt"></i>
        <span>Carte intelligente</span>
    </a>
    
    <!-- Blog -->
    <a href="blog.php" class="nav-item">
        <i class="fas fa-blog"></i>
        <span>Blog</span>
    </a>
    
    <!-- Services en ligne -->
    <a href="services.php" class="nav-item">
        <i class="fas fa-concierge-bell"></i>
        <span>Services en ligne</span>
    </a>
    
    <!-- Rendez-vous -->
    <a href="rdv.php" class="nav-item">
        <i class="fas fa-calendar-check"></i>
        <span>Rendez-vous</span>
    </a>
</div>

<!-- Main Content -->
<div class="main-content">
    <?php if(isset($_SESSION['success'])): ?>
        <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
    <?php endif; ?>
    <?php if(isset($_SESSION['error'])): ?>
        <div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
    <?php endif; ?>

    <!-- Bouton Dashboard -->
    <div class="dashboard-btn-container">
        <a href="index.php?action=dashboard" class="btn-dashboard">
            <i class="fas fa-chart-line"></i> 📊 Voir le Dashboard des statistiques
        </a>
    </div>

    <!-- Header Gestion -->
    <div class="manage-header">
        <h1><i class="fas fa-calendar-alt"></i> Gestion des Événements</h1>
        <a href="index.php?action=create" class="btn-add">
            <i class="fas fa-plus"></i> Ajouter un événement
        </a>
    </div>

    <!-- Filters -->
    <div class="filters">
        <div class="filter-group">
            <label><i class="fas fa-filter"></i> Filtrer par catégorie :</label>
            <select name="categorie" onchange="window.location.href='?action=manage&categorie='+this.value">
                <option value="all" <?php echo (!isset($_GET['categorie']) || $_GET['categorie'] == 'all') ? 'selected' : ''; ?>>Toutes</option>
                <option value="Culture" <?php echo (isset($_GET['categorie']) && $_GET['categorie'] == 'Culture') ? 'selected' : ''; ?>>Culture</option>
                <option value="Sport" <?php echo (isset($_GET['categorie']) && $_GET['categorie'] == 'Sport') ? 'selected' : ''; ?>>Sport</option>
                <option value="Environnement" <?php echo (isset($_GET['categorie']) && $_GET['categorie'] == 'Environnement') ? 'selected' : ''; ?>>Environnement</option>
                <option value="Social" <?php echo (isset($_GET['categorie']) && $_GET['categorie'] == 'Social') ? 'selected' : ''; ?>>Social</option>
                <option value="Education" <?php echo (isset($_GET['categorie']) && $_GET['categorie'] == 'Education') ? 'selected' : ''; ?>>Éducation</option>
            </select>
        </div>
    </div>

    <!-- Tableau des événements -->
    <div class="table-container">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Image</th>
                    <th>Titre</th>
                    <th>Lieu</th>
                    <th>Date</th>
                    <th>Heure</th>
                    <th>Catégorie</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if(count($events) > 0): ?>
                    <?php foreach($events as $event): ?>
                        <tr>
                            <td>
                                <?php if(!empty($event['image_url']) && file_exists($event['image_url'])): ?>
                                    <img src="<?php echo $event['image_url']; ?>" class="event-thumb" alt="Image">
                                <?php else: ?>
                                    <div class="no-image">
                                        <i class="fas fa-image"></i>
                                    </div>
                                <?php endif; ?>
                            </a>
                            <td><strong><?php echo htmlspecialchars($event['titre']); ?></strong></a></td>
                            <td><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($event['lieu']); ?></a></td>
                            <td><i class="fas fa-calendar"></i> <?php echo date('d/m/Y', strtotime($event['date_evenement'])); ?></a></a></td>
                            <td><i class="fas fa-clock"></i> <?php echo $event['heure']; ?></a></a></td>
                            <td><span class="badge"><?php echo $event['categorie']; ?></span></a></a></td>
                            <td class="action-buttons">
                                <a href="index.php?action=edit&id=<?php echo $event['id']; ?>" class="btn-edit">
                                    <i class="fas fa-edit"></i> Modifier
                                </a>
                                <a href="index.php?action=delete&id=<?php echo $event['id']; ?>" class="btn-delete" onclick="return confirm('Supprimer cet événement ?')">
                                    <i class="fas fa-trash-alt"></i> Supprimer
                                </a>
                            </a>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="no-events">
                            <i class="fas fa-calendar-times"></i> Aucun événement trouvé.<br>
                            <a href="index.php?action=create" style="color: #2ecc71; margin-top: 10px; display: inline-block;">
                                <i class="fas fa-plus"></i> Cliquez ici pour ajouter un événement
                            </a>
                        </a>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>