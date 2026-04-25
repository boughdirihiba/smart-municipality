<?php
// views/services/list.php - Page to list all services
$active_page = 'services';

// Nettoyage et sécurisation de la recherche
$raw_search = isset($_GET['search']) ? trim($_GET['search']) : '';
$current_search = htmlspecialchars($raw_search);

// Connexion à la base de données pour récupérer les services
require_once "config/database.php";
$database = new Database();
$db = $database->connect();

// Configuration de la collation pour une recherche insensible aux accents
$db->exec("SET NAMES 'utf8mb4'");
$db->exec("SET CHARACTER SET utf8mb4");

// Déterminer si la recherche est un ID numérique
$is_id_search = !empty($raw_search) && is_numeric($raw_search);

// Récupérer les services avec recherche améliorée (nom + ID)
if(!empty($raw_search)) {
    if($is_id_search) {
        // Recherche par ID exact
        $sql = "SELECT * FROM services WHERE id = :id ORDER BY id DESC";
        $stmt = $db->prepare($sql);
        $stmt->bindParam(":id", $raw_search, PDO::PARAM_INT);
        $stmt->execute();
        $services = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } else {
        // Recherche par nom (insensible aux accents)
        $sql = "SELECT * FROM services WHERE nom LIKE :search ORDER BY id DESC";
        $stmt = $db->prepare($sql);
        $searchTerm = "%$raw_search%";
        $stmt->bindParam(":search", $searchTerm);
        $stmt->execute();
        $services = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if(count($services) == 0) {
            $sql = "SELECT * FROM services 
                    WHERE nom COLLATE utf8mb4_unicode_ci LIKE :search 
                    ORDER BY id DESC";
            $stmt = $db->prepare($sql);
            $searchTerm = "%$raw_search%";
            $stmt->bindParam(":search", $searchTerm);
            $stmt->execute();
            $services = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        
        if(count($services) == 0) {
            try {
                $sql = "SELECT * FROM services 
                        WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(
                        LOWER(nom), 
                        'à','a'), 'á','a'), 'â','a'), 'ã','a'), 'ä','a'), 
                        'é','e'), 'è','e'), 'ê','e'), 'ë','e'), 
                        'ç','c') LIKE :search 
                        ORDER BY id DESC";
                $stmt = $db->prepare($sql);
                $searchTermNoAccent = "%" . strtr(strtolower($raw_search), 
                    ['à'=>'a','á'=>'a','â'=>'a','ã'=>'a','ä'=>'a',
                     'é'=>'e','è'=>'e','ê'=>'e','ë'=>'e',
                     'î'=>'i','ï'=>'i','ô'=>'o','ö'=>'o','ù'=>'u','ü'=>'u',
                     'ç'=>'c', 'ÿ'=>'y']) . "%";
                $stmt->bindParam(":search", $searchTermNoAccent);
                $stmt->execute();
                $services = $stmt->fetchAll(PDO::FETCH_ASSOC);
            } catch(Exception $e) {
                // Si la requête échoue, on garde le résultat précédent
            }
        }
    }
} else {
    $sql = "SELECT * FROM services ORDER BY id DESC";
    $stmt = $db->prepare($sql);
    $stmt->execute();
    $services = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestion des services - Smart Municipality</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: #f5f7fb;
            color: #1e293b;
            min-height: 100vh;
            transition: all 0.3s ease;
        }

        /* ========== MODE SOMBRE ========== */
        body.dark-mode {
            background: #0f172a;
            color: #e2e8f0;
        }

        body.dark-mode .sidebar {
            background: #052E16;
        }

        body.dark-mode .header,
        body.dark-mode .search-container,
        body.dark-mode .table-container {
            background: #1e293b;
            border-color: #334155;
        }

        body.dark-mode .header h1,
        body.dark-mode .header p,
        body.dark-mode th {
            color: #e2e8f0;
        }

        body.dark-mode td {
            color: #cbd5e1;
        }

        body.dark-mode tr:hover {
            background: #334155;
        }

        body.dark-mode .admin-info {
            background: #334155;
        }

        body.dark-mode .search-input {
            background: #334155;
            border-color: #475569;
            color: #e2e8f0;
        }

        body.dark-mode .search-info {
            background: #334155;
            color: #e2e8f0;
        }

        body.dark-mode .btn-reset {
            background: #334155;
            color: #e2e8f0;
        }

        body.dark-mode .btn-reset:hover {
            background: #475569;
        }

        .app-container {
            display: flex;
            min-height: 100vh;
        }

        /* ========== SIDEBAR VERT FONCÉ #052E16 ========== */
        .sidebar {
            width: 280px;
            background: #052E16;
            color: white;
            height: 100vh;
            padding: 1.5rem 1rem;
            position: fixed;
            left: 0;
            top: 0;
            overflow-y: auto;
            box-shadow: 8px 0 32px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
        }

        .logo-container {
            text-align: center;
            margin-bottom: 2rem;
            padding: 0.5rem;
        }

        .logo-container img {
            max-width: 150px;
            height: auto;
            background: transparent;
        }

        .sidebar h2 {
            margin-bottom: 2rem;
            font-size: 1.2rem;
            text-align: center;
            padding-bottom: 1rem;
            border-bottom: 2px solid rgba(255, 255, 255, 0.2);
        }

        .sidebar h2 i {
            margin-right: 8px;
            color: #6ee7b7;
        }

        .sidebar ul {
            list-style: none;
            padding: 0;
        }

        .sidebar li {
            padding: 12px;
            margin: 8px 0;
            border-radius: 12px;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .sidebar li a {
            color: white;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 12px;
            width: 100%;
        }

        .sidebar li:hover {
            background: rgba(46, 204, 113, 0.3);
            transform: translateX(5px);
        }

        .sidebar li.active {
            background: #2ecc71;
            box-shadow: 0 4px 12px rgba(46, 204, 113, 0.3);
        }

        .sidebar li i {
            width: 22px;
            font-size: 1rem;
        }

        /* MAIN CONTENT */
        .main-content {
            flex: 1;
            margin-left: 280px;
            padding: 2rem;
            transition: all 0.3s;
        }

        /* HEADER */
        .header {
            background: white;
            padding: 1.5rem 2rem;
            border-radius: 20px;
            margin-bottom: 2rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
            border: 1px solid #e2e8f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 20px;
            transition: all 0.3s ease;
        }

        .header h1 {
            color: #1a1f36;
            font-size: 1.5rem;
            font-weight: 700;
        }

        .header h1 i {
            color: #052E16;
            margin-right: 10px;
        }

        .header p {
            color: #64748b;
            margin-top: 0.25rem;
            font-size: 0.875rem;
        }

        .header-buttons {
            display: flex;
            gap: 15px;
            align-items: center;
            flex-wrap: wrap;
        }

        .btn-darkmode {
            background: #f1f5f9;
            border: none;
            padding: 10px 18px;
            border-radius: 40px;
            font-size: 0.875rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: #475569;
        }

        .btn-darkmode:hover {
            background: #e2e8f0;
            transform: translateY(-2px);
        }

        body.dark-mode .btn-darkmode {
            background: #334155;
            color: #e2e8f0;
        }

        body.dark-mode .btn-darkmode:hover {
            background: #10b981;
            color: white;
        }

        .btn-add {
            background: linear-gradient(135deg, #052E16, #0a4a22);
            color: white;
            padding: 12px 24px;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            transition: all 0.3s;
        }

        .btn-add:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(5, 46, 22, 0.3);
            color: white;
        }

        .admin-info {
            display: flex;
            align-items: center;
            gap: 16px;
            background: #f8fafc;
            padding: 0.5rem 1rem;
            border-radius: 50px;
            transition: all 0.3s ease;
        }

        .admin-avatar {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, #052E16, #0a4a22);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
        }

        .admin-name {
            font-weight: 600;
            font-size: 0.875rem;
        }

        .admin-role {
            font-size: 0.75rem;
            color: #64748b;
        }

        /* SEARCH BAR */
        .search-container {
            background: white;
            border-radius: 20px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
            border: 1px solid #e2e8f0;
            transition: all 0.3s ease;
        }

        .search-form {
            display: flex;
            gap: 15px;
            align-items: center;
            flex-wrap: wrap;
        }

        .search-input-group {
            flex: 1;
            position: relative;
        }

        .search-input-group i {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
        }

        .search-input {
            width: 100%;
            padding: 14px 16px 14px 45px;
            border: 2px solid #e2e8f0;
            border-radius: 16px;
            font-size: 0.9rem;
            font-family: 'Inter', sans-serif;
            transition: all 0.3s;
        }

        .search-input:focus {
            outline: none;
            border-color: #052E16;
            box-shadow: 0 0 0 3px rgba(5, 46, 22, 0.1);
        }

        .btn-search {
            background: linear-gradient(135deg, #052E16, #0a4a22);
            color: white;
            border: none;
            padding: 14px 28px;
            border-radius: 16px;
            font-size: 0.9rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 10px;
        }

        .btn-search:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(5, 46, 22, 0.3);
        }

        .btn-reset {
            background: #f1f5f9;
            color: #475569;
            border: none;
            padding: 14px 28px;
            border-radius: 16px;
            font-size: 0.9rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
        }

        .btn-reset:hover {
            background: #e2e8f0;
            transform: translateY(-2px);
        }

        .search-info {
            margin-top: 15px;
            padding: 10px;
            background: #e8f3e8;
            border-radius: 12px;
            color: #052E16;
            font-size: 0.85rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 10px;
        }

        .search-badge {
            background: #052E16;
            color: white;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        /* TABLE */
        .table-container {
            background: white;
            border-radius: 28px;
            padding: 1.5rem;
            box-shadow: 0 8px 32px rgba(0,0,0,0.04);
            border: 1px solid #e2e8f0;
            overflow-x: auto;
            transition: all 0.3s ease;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            text-align: left;
            padding: 1rem;
            background: #f8fafc;
            color: #1e293b;
            font-weight: 600;
            font-size: 0.85rem;
            border-bottom: 2px solid #e2e8f0;
        }

        td {
            padding: 1rem;
            border-bottom: 1px solid #e2e8f0;
            color: #334155;
            font-size: 0.875rem;
            vertical-align: middle;
        }

        tr:hover {
            background: #f8fafc;
        }

        .service-icon {
            font-size: 1.8rem;
            color: #052E16;
            text-align: center;
        }

        .actions {
            display: flex;
            gap: 10px;
        }

        .btn-edit, .btn-delete {
            padding: 6px 12px;
            border-radius: 10px;
            text-decoration: none;
            font-size: 0.75rem;
            font-weight: 600;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .btn-edit {
            background: #e0e7ff;
            color: #4f46e5;
        }

        .btn-edit:hover {
            background: #c7d2fe;
            transform: translateY(-2px);
        }

        .btn-delete {
            background: #fee2e2;
            color: #dc2626;
        }

        .btn-delete:hover {
            background: #fecaca;
            transform: translateY(-2px);
        }

        .alert {
            padding: 1rem;
            border-radius: 12px;
            margin-bottom: 1.5rem;
            font-weight: 500;
        }

        .alert-success {
            background: #d1fae5;
            color: #059669;
            border-left: 4px solid #059669;
        }

        .alert-error {
            background: #fee2e2;
            color: #dc2626;
            border-left: 4px solid #dc2626;
        }

        .empty-state {
            text-align: center;
            padding: 3rem;
        }

        .empty-state i {
            font-size: 4rem;
            color: #cbd5e1;
            margin-bottom: 1rem;
        }

        .empty-state p {
            color: #64748b;
            margin-bottom: 1.5rem;
        }

        .highlight {
            background-color: #fef3c7;
            font-weight: 600;
            padding: 2px 4px;
            border-radius: 4px;
        }

        .id-highlight {
            background-color: #dbeafe;
            font-weight: 700;
            padding: 2px 6px;
            border-radius: 8px;
            display: inline-block;
        }

        .suggestion-box {
            background: white;
            border: 1px solid #e2e8f0;
            border-top: none;
            border-radius: 0 0 16px 16px;
            max-height: 200px;
            overflow-y: auto;
            display: none;
            position: absolute;
            width: 100%;
            z-index: 1000;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }

        .suggestion-item {
            padding: 10px 16px;
            cursor: pointer;
            transition: background 0.2s;
        }

        .suggestion-item:hover {
            background: #f1f5f9;
        }

        @media (max-width: 768px) {
            .sidebar {
                width: 70px;
                padding: 15px 10px;
            }
            
            .logo-container img {
                max-width: 40px;
            }
            
            .sidebar h2 {
                font-size: 0;
            }
            
            .sidebar h2 i {
                font-size: 20px;
            }
            
            .sidebar li span {
                display: none;
            }
            
            .sidebar li i {
                margin-right: 0;
                font-size: 18px;
            }
            
            .main-content {
                margin-left: 90px;
                padding: 1rem;
            }
            
            .table-container {
                padding: 1rem;
            }
            
            th, td {
                padding: 0.75rem;
            }
            
            .actions {
                flex-direction: column;
            }
            
            .btn-edit, .btn-delete {
                justify-content: center;
            }
            
            .search-form {
                flex-direction: column;
            }
            
            .btn-search, .btn-reset {
                width: 100%;
                justify-content: center;
            }
        }

        ::-webkit-scrollbar {
            width: 8px;
        }

        ::-webkit-scrollbar-track {
            background: #e2e8f0;
            border-radius: 10px;
        }

        ::-webkit-scrollbar-thumb {
            background: linear-gradient(135deg, #052E16, #0a4a22);
            border-radius: 10px;
        }
    </style>
</head>
<body>
    <div class="app-container">
        <!-- SIDEBAR VERT FONCÉ #052E16 -->
        <div class="sidebar">
            <div class="logo-container">
                <img src="assets/images/logo.png" alt="Smart Municipality Logo">
            </div>
            <h2><i class="fas fa-city"></i> Smart Municipality</h2>
            <ul>
                <li>
                    <a href="index.php?action=dashboard">
                        <i class="fas fa-tachometer-alt"></i> <span>Dashboard</span>
                    </a>
                </li>
                <li>
                    <a href="index.php?action=profil">
                        <i class="fas fa-id-card"></i> <span>Profil</span>
                    </a>
                </li>
                <li>
                    <a href="index.php?action=evenements">
                        <i class="fas fa-calendar-alt"></i> <span>Événements</span>
                    </a>
                </li>
                <li>
                    <a href="index.php?action=carte_intelligente">
                        <i class="fas fa-brain"></i> <span>Carte intelligente</span>
                    </a>
                </li>
                <li>
                    <a href="index.php?action=blog">
                        <i class="fas fa-newspaper"></i> <span>Blog</span>
                    </a>
                </li>
                <li class="active">
                    <a href="index.php?action=list_services">
                        <i class="fas fa-concierge-bell"></i> <span>Services en ligne</span>
                    </a>
                </li>
                <li>
                    <a href="index.php?action=rendez_vous">
                        <i class="fas fa-calendar-check"></i> <span>Rendez-vous</span>
                    </a>
                </li>
            </ul>
        </div>

        <div class="main-content">
            <div class="header">
                <div>
                    <h1><i class="fas fa-concierge-bell"></i> Gestion des services</h1>
                    <p>Gérez les services proposés aux citoyens</p>
                </div>
                <div class="header-buttons">
                    <button id="darkModeToggle" class="btn-darkmode">
                        <i class="fas fa-moon"></i> <span id="darkModeText">Sombre</span>
                    </button>
                    <a href="index.php?action=create_service" class="btn-add">
                        <i class="fas fa-plus"></i> Nouveau service
                    </a>
                    <div class="admin-info">
                        <div class="admin-avatar">
                            <i class="fas fa-user"></i>
                        </div>
                        <div>
                            <div class="admin-name">Admin Système</div>
                            <div class="admin-role">Administrateur</div>
                        </div>
                    </div>
                </div>
            </div>

            <?php if(isset($_GET['success'])): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> Opération effectuée avec succès !
                </div>
            <?php endif; ?>

            <?php if(isset($_GET['deleted'])): ?>
                <div class="alert alert-success">
                    <i class="fas fa-trash-alt"></i> Service supprimé avec succès !
                </div>
            <?php endif; ?>

            <?php if(isset($_GET['error'])): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($_GET['error']); ?>
                </div>
            <?php endif; ?>

            <!-- BARRE DE RECHERCHE -->
            <div class="search-container">
                <form method="GET" action="index.php" class="search-form" id="searchForm">
                    <input type="hidden" name="action" value="list_services">
                    <div class="search-input-group" style="position: relative;">
                        <i class="fas fa-search"></i>
                        <input type="text" 
                               name="search" 
                               id="searchInput"
                               class="search-input" 
                               placeholder="Rechercher par ID (ex: 5) ou par nom du service..." 
                               value="<?php echo $current_search; ?>"
                               autocomplete="off">
                        <div id="suggestions" class="suggestion-box"></div>
                    </div>
                    <button type="submit" class="btn-search">
                        <i class="fas fa-search"></i> Rechercher
                    </button>
                    <?php if(!empty($current_search)): ?>
                        <a href="index.php?action=list_services" class="btn-reset">
                            <i class="fas fa-times"></i> Réinitialiser
                        </a>
                    <?php endif; ?>
                </form>
                
                <?php if(!empty($current_search)): ?>
                    <div class="search-info">
                        <div>
                            <i class="fas fa-chart-line"></i> 
                            <strong><?php echo count($services); ?></strong> résultat(s) trouvé(s) pour 
                            "<strong><?php echo $current_search; ?></strong>"
                            <?php if($is_id_search && !empty($services)): ?>
                                <span class="search-badge">
                                    <i class="fas fa-hashtag"></i> Recherche par ID
                                </span>
                            <?php elseif(!empty($raw_search) && !$is_id_search): ?>
                                <span class="search-badge">
                                    <i class="fas fa-font"></i> Recherche par nom
                                </span>
                            <?php endif; ?>
                        </div>
                        <div>
                            <i class="fas fa-info-circle"></i> 
                            Vous pouvez rechercher par ID numérique ou par nom (insensible aux accents)
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <div class="table-container">
                 <table>
                    <thead>
                        <tr>
                            <th width="5%">#ID</th>
                            <th width="8%">Icône</th>
                            <th width="25%">Nom du service</th>
                            <th width="40%">Description</th>
                            <th width="12%">Date création</th>
                            <th width="10%">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(!empty($services) && count($services) > 0): ?>
                            <?php foreach($services as $service): ?>
                            <tr>
                                <td>
                                    <?php 
                                    $id_display = $service['id'];
                                    if($is_id_search && $raw_search == $service['id']) {
                                        $id_display = '<span class="id-highlight"><i class="fas fa-hashtag"></i> ' . $service['id'] . '</span>';
                                    }
                                    echo $id_display;
                                    ?>
                                </td>
                                <td class="service-icon">
                                    <i class="<?php echo htmlspecialchars($service['icone']); ?>"></i>
                                </td>
                                <td>
                                    <strong>
                                        <?php 
                                        $nom = htmlspecialchars($service['nom']);
                                        if(!empty($raw_search) && !$is_id_search) {
                                            $pattern = '/(' . preg_quote($raw_search, '/') . ')/i';
                                            $nom = preg_replace($pattern, '<span class="highlight">$1</span>', $nom);
                                        }
                                        echo $nom;
                                        ?>
                                    </strong>
                                </td>
                                <td>
                                    <?php 
                                    $description = htmlspecialchars($service['description']);
                                    echo strlen($description) > 80 ? substr($description, 0, 80) . '...' : $description;
                                    ?>
                                </td>
                                <td>
                                    <i class="fas fa-calendar-alt" style="color: #94a3b8; font-size: 0.7rem;"></i>
                                    <?php echo date('d/m/Y', strtotime($service['date_creation'])); ?>
                                </td>
                                <td class="actions">
                                    <a href="index.php?action=edit_service&id=<?php echo $service['id']; ?>" class="btn-edit" title="Modifier">
                                        <i class="fas fa-edit"></i> Modifier
                                    </a>
                                    <a href="index.php?action=delete_service&id=<?php echo $service['id']; ?>" 
                                       class="btn-delete" 
                                       title="Supprimer"
                                       onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce service ?')">
                                        <i class="fas fa-trash-alt"></i> Supprimer
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6">
                                    <div class="empty-state">
                                        <?php if(!empty($raw_search)): ?>
                                            <i class="fas fa-search"></i>
                                            <?php if($is_id_search): ?>
                                                <p>Aucun service avec l'ID "<strong><?php echo $current_search; ?></strong>" n'a été trouvé.</p>
                                                <p style="font-size: 0.85rem; margin-top: 10px;">
                                                    <i class="fas fa-lightbulb"></i> Astuces :<br>
                                                    - Vérifiez que l'ID existe<br>
                                                    - Essayez un autre ID numérique<br>
                                                    - Utilisez la recherche par nom
                                                </p>
                                            <?php else: ?>
                                                <p>Aucun service ne correspond à "<strong><?php echo $current_search; ?></strong>"</p>
                                                <p style="font-size: 0.85rem; margin-top: 10px;">
                                                    <i class="fas fa-lightbulb"></i> Suggestions :<br>
                                                    - Vérifiez l'orthographe<br>
                                                    - Essayez des mots-clés plus génériques<br>
                                                    - Recherche insensible aux accents
                                                </p>
                                            <?php endif; ?>
                                            <a href="index.php?action=list_services" class="btn-add" style="margin-top: 20px;">
                                                <i class="fas fa-arrow-left"></i> Voir tous les services
                                            </a>
                                        <?php else: ?>
                                            <i class="fas fa-inbox"></i>
                                            <p>Aucun service n'a été trouvé.</p>
                                            <a href="index.php?action=create_service" class="btn-add">
                                                <i class="fas fa-plus"></i> Ajouter votre premier service
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        // ========== MODE SOMBRE ==========
        function initDarkMode() {
            const darkMode = localStorage.getItem('darkMode');
            const darkModeToggle = document.getElementById('darkModeToggle');
            const darkModeText = document.getElementById('darkModeText');
            
            if (darkMode === 'enabled') {
                document.body.classList.add('dark-mode');
                if (darkModeText) darkModeText.textContent = 'Clair';
                if (darkModeToggle) darkModeToggle.innerHTML = '<i class="fas fa-sun"></i> Clair';
            }
            
            if (darkModeToggle) {
                darkModeToggle.addEventListener('click', () => {
                    document.body.classList.toggle('dark-mode');
                    
                    if (document.body.classList.contains('dark-mode')) {
                        localStorage.setItem('darkMode', 'enabled');
                        darkModeToggle.innerHTML = '<i class="fas fa-sun"></i> Clair';
                        if (darkModeText) darkModeText.textContent = 'Clair';
                    } else {
                        localStorage.setItem('darkMode', 'disabled');
                        darkModeToggle.innerHTML = '<i class="fas fa-moon"></i> Sombre';
                        if (darkModeText) darkModeText.textContent = 'Sombre';
                    }
                });
            }
        }

        // Détection automatique du type de recherche
        const searchInput = document.getElementById('searchInput');
        const suggestionsBox = document.getElementById('suggestions');
        let searchTimeout;
        
        function isNumericSearch(value) {
            return /^\d+$/.test(value.trim());
        }
        
        if(searchInput) {
            searchInput.addEventListener('input', function() {
                const value = this.value.trim();
                clearTimeout(searchTimeout);
                const query = this.value.trim();
                
                if(query.length < 2 || isNumericSearch(query)) {
                    suggestionsBox.style.display = 'none';
                    return;
                }
                
                searchTimeout = setTimeout(() => {
                    fetch(`index.php?action=ajax_search_services&q=${encodeURIComponent(query)}`)
                        .then(response => response.json())
                        .then(data => {
                            if(data.length > 0) {
                                suggestionsBox.innerHTML = data.map(item => 
                                    `<div class="suggestion-item" onclick="selectSuggestion('${item.nom.replace(/'/g, "\\'")}')">
                                        <i class="fas fa-concierge-bell" style="color: #059669; margin-right: 10px;"></i>
                                        ${item.nom}
                                     </div>`
                                ).join('');
                                suggestionsBox.style.display = 'block';
                            } else {
                                suggestionsBox.style.display = 'none';
                            }
                        })
                        .catch(() => {
                            suggestionsBox.style.display = 'none';
                        });
                }, 300);
            });
            
            document.addEventListener('click', function(e) {
                if(searchInput && !searchInput.contains(e.target) && suggestionsBox && !suggestionsBox.contains(e.target)) {
                    suggestionsBox.style.display = 'none';
                }
            });
            
            searchInput.addEventListener('keypress', function(e) {
                if(e.key === 'Enter') {
                    e.preventDefault();
                    document.getElementById('searchForm').submit();
                }
            });
        }
        
        function selectSuggestion(value) {
            searchInput.value = value;
            suggestionsBox.style.display = 'none';
            document.getElementById('searchForm').submit();
        }
        
        // Initialiser le mode sombre
        initDarkMode();
    </script>
</body>
</html>