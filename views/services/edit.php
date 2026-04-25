<?php
require_once "controllers/ServiceController.php";
$serviceController = new ServiceController();
$service = $serviceController->getServiceById($_GET['id']);
if(!$service) {
    header("Location: index.php?action=list_services");
    exit;
}
$active_page = 'services';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modifier un service - Smart Municipality</title>
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
        body.dark-mode .form-container {
            background: #1e293b;
            border-color: #334155;
        }

        body.dark-mode .header h1,
        body.dark-mode .form-container h2 {
            color: #e2e8f0;
        }

        body.dark-mode .admin-info {
            background: #334155;
        }

        body.dark-mode .admin-name,
        body.dark-mode .admin-role {
            color: #e2e8f0;
        }

        body.dark-mode input,
        body.dark-mode select,
        body.dark-mode textarea {
            background: #334155;
            border-color: #475569;
            color: #e2e8f0;
        }

        body.dark-mode .icon-preview {
            background: #334155;
        }

        body.dark-mode .btn-back {
            color: #94a3b8;
        }

        body.dark-mode .btn-back:hover {
            color: #10b981;
        }

        body.dark-mode label {
            color: #cbd5e1;
        }

        /* Bouton mode sombre */
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

        .header p {
            color: #64748b;
            margin-top: 0.25rem;
            font-size: 0.875rem;
        }

        .header-buttons {
            display: flex;
            gap: 16px;
            align-items: center;
            flex-wrap: wrap;
        }

        .btn-dashboard {
            background: linear-gradient(135deg, #052E16, #0a4a22);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 40px;
            font-size: 0.875rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
        }

        .btn-dashboard:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(5, 46, 22, 0.3);
        }

        .admin-info {
            display: flex;
            align-items: center;
            gap: 16px;
            background: #f1f5f9;
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

        /* FORMULAIRE */
        .form-container {
            background: white;
            border-radius: 28px;
            padding: 2rem;
            box-shadow: 0 8px 32px rgba(0,0,0,0.04);
            border: 1px solid #e2e8f0;
            max-width: 700px;
            margin: 0 auto;
            transition: all 0.3s ease;
        }

        .form-container h2 {
            color: #052E16;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid #052E16;
            font-weight: 700;
            font-size: 1.3rem;
            display: inline-block;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        label {
            display: block;
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: #334155;
            font-size: 0.875rem;
        }

        label i {
            color: #052E16;
            margin-right: 8px;
        }

        input, select, textarea {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #e2e8f0;
            border-radius: 16px;
            font-size: 0.875rem;
            font-family: 'Inter', sans-serif;
            transition: all 0.3s;
        }

        input:focus, select:focus, textarea:focus {
            outline: none;
            border-color: #052E16;
            box-shadow: 0 0 0 3px rgba(5, 46, 22, 0.1);
        }

        .icon-preview {
            text-align: center;
            font-size: 3rem;
            margin: 1rem 0;
            padding: 1rem;
            background: #f0fdf4;
            border-radius: 20px;
            color: #052E16;
            transition: all 0.3s ease;
        }

        .btn-submit {
            background: linear-gradient(135deg, #052E16, #0a4a22);
            color: white;
            border: none;
            padding: 14px 28px;
            border-radius: 50px;
            font-size: 0.9rem;
            font-weight: 600;
            cursor: pointer;
            width: 100%;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(5, 46, 22, 0.3);
        }

        .btn-back {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-top: 1.5rem;
            color: #64748b;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s;
        }

        .btn-back:hover {
            color: #052E16;
            transform: translateX(-4px);
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
            
            .form-container {
                padding: 1.5rem;
            }

            .header-buttons {
                width: 100%;
                justify-content: space-between;
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
                    <h1>Modifier un service</h1>
                    <p>Modifiez les informations du service</p>
                </div>
                <div class="header-buttons">
                    <button id="darkModeToggle" class="btn-darkmode">
                        <i class="fas fa-moon"></i> <span id="darkModeText">Sombre</span>
                    </button>
                    <a href="index.php?action=dashboard" class="btn-dashboard">
                        <i class="fas fa-chart-line"></i> Dashboard
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

            <div class="form-container">
                <h2><i class="fas fa-edit"></i> Formulaire de modification</h2>
                
                <form action="index.php?action=update_service" method="POST">
                    <input type="hidden" name="id" value="<?php echo $service['id']; ?>">
                    
                    <div class="form-group">
                        <label><i class="fas fa-tag"></i> Nom du service</label>
                        <input type="text" name="nom" value="<?php echo htmlspecialchars($service['nom']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label><i class="fas fa-align-left"></i> Description</label>
                        <textarea name="description" rows="4" required><?php echo htmlspecialchars($service['description']); ?></textarea>
                    </div>

                    <div class="form-group">
                        <label><i class="fas fa-icons"></i> Icône Font Awesome</label>
                        <select name="icone">
                            <option value="fas fa-gavel" <?php echo $service['icone'] == 'fas fa-gavel' ? 'selected' : ''; ?>>🔨 Gavel (Légalisation)</option>
                            <option value="fas fa-baby-carriage" <?php echo $service['icone'] == 'fas fa-baby-carriage' ? 'selected' : ''; ?>>👶 Baby Carriage (Naissance)</option>
                            <option value="fas fa-coins" <?php echo $service['icone'] == 'fas fa-coins' ? 'selected' : ''; ?>>💰 Coins (Taxes)</option>
                            <option value="fas fa-folder-open" <?php echo $service['icone'] == 'fas fa-folder-open' ? 'selected' : ''; ?>>📁 Folder (Dossier)</option>
                            <option value="fas fa-file-alt" <?php echo $service['icone'] == 'fas fa-file-alt' ? 'selected' : ''; ?>>📄 File Alt</option>
                            <option value="fas fa-building" <?php echo $service['icone'] == 'fas fa-building' ? 'selected' : ''; ?>>🏢 Building</option>
                            <option value="fas fa-handshake" <?php echo $service['icone'] == 'fas fa-handshake' ? 'selected' : ''; ?>>🤝 Handshake</option>
                            <option value="fas fa-id-card" <?php echo $service['icone'] == 'fas fa-id-card' ? 'selected' : ''; ?>>🪪 ID Card</option>
                        </select>
                    </div>

                    <div class="icon-preview" id="iconPreview">
                        <i class="<?php echo $service['icone']; ?>"></i>
                    </div>

                    <button type="submit" class="btn-submit">
                        <i class="fas fa-save"></i> Enregistrer les modifications
                    </button>
                </form>

                <a href="index.php?action=list_services" class="btn-back">
                    <i class="fas fa-arrow-left"></i> Retour à la liste des services
                </a>
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

        // Preview icône
        const selectIcone = document.querySelector('select[name="icone"]');
        const iconPreview = document.getElementById('iconPreview');
        
        if (selectIcone) {
            selectIcone.addEventListener('change', function() {
                iconPreview.innerHTML = '<i class="' + this.value + '"></i>';
            });
        }

        // Initialiser le mode sombre
        initDarkMode();
    </script>
</body>
</html>