<?php
$demande_id = $doc['demande_id'];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modifier document - Smart Municipality</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;14..32,400;14..32,500;14..32,600;14..32,700;14..32,800&display=swap" rel="stylesheet">
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
            min-height: 100vh;
        }

        /* ==================== NAVBAR MODERNE ==================== */
        .navbar {
            background: white;
            padding: 0 40px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
            position: sticky;
            top: 0;
            z-index: 1000;
            border-bottom: 1px solid #eef2ff;
        }

        .nav-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 1400px;
            margin: 0 auto;
            padding: 14px 0;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
        }

        .logo-icon {
            width: 38px;
            height: 38px;
            background: linear-gradient(135deg, #059669, #10b981);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .logo-icon i {
            font-size: 20px;
            color: white;
        }

        .logo-text .smart {
            font-size: 20px;
            font-weight: 800;
            background: linear-gradient(135deg, #059669, #10b981);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .logo-text .municipality {
            font-size: 12px;
            font-weight: 500;
            color: #64748b;
        }

        .nav-links {
            display: flex;
            gap: 24px;
            align-items: center;
        }

        .nav-links a {
            text-decoration: none;
            color: #475569;
            font-weight: 500;
            font-size: 14px;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .nav-links a:hover {
            color: #059669;
        }

        .btn-backoffice {
            background: #059669;
            color: white !important;
            padding: 8px 20px;
            border-radius: 30px;
        }

        /* ==================== HERO ==================== */
        .hero {
            background: linear-gradient(135deg, #064e3b, #059669);
            padding: 40px 40px;
            text-align: center;
            color: white;
        }

        .hero h1 {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 8px;
        }

        .hero p {
            font-size: 14px;
            opacity: 0.9;
        }

        /* ==================== CONTENEUR PRINCIPAL ==================== */
        .main-container {
            max-width: 600px;
            margin: 0 auto;
            padding: 40px;
        }
        
        .edit-container {
            background: white;
            border-radius: 32px;
            padding: 40px;
            box-shadow: 0 20px 35px -12px rgba(0, 0, 0, 0.1);
            border: 1px solid #eef2ff;
        }
        
        .edit-header { 
            text-align: center; 
            margin-bottom: 30px; 
        }
        
        .edit-header i { 
            font-size: 55px; 
            background: linear-gradient(135deg, #059669, #10b981);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 15px;
        }
        
        .edit-header h2 { 
            font-size: 24px; 
            color: #0f172a; 
            margin-bottom: 8px;
            font-weight: 700;
        }
        
        .edit-header p {
            color: #64748b;
            font-size: 13px;
        }
        
        .badge-doc {
            display: inline-block;
            background: #ecfdf5;
            color: #059669;
            padding: 5px 14px;
            border-radius: 30px;
            font-size: 11px;
            font-weight: 600;
            margin-top: 12px;
        }
        
        .form-group { 
            margin-bottom: 22px; 
        }
        
        label { 
            display: block; 
            font-weight: 600; 
            margin-bottom: 8px; 
            color: #334155;
            font-size: 13px;
        }
        
        label i {
            color: #059669;
            margin-right: 6px;
        }
        
        input { 
            width: 100%; 
            padding: 12px 16px; 
            border: 2px solid #e2e8f0; 
            border-radius: 14px; 
            font-size: 14px;
            font-family: 'Inter', sans-serif;
            transition: all 0.3s;
        }
        
        input:focus { 
            outline: none; 
            border-color: #059669; 
            box-shadow: 0 0 0 3px rgba(5, 150, 105, 0.1);
        }
        
        .input-info {
            font-size: 11px;
            color: #94a3b8;
            margin-top: 6px;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .info-box {
            background: #f8fafc;
            border-radius: 16px;
            padding: 15px;
            margin-bottom: 22px;
            border: 1px solid #e2e8f0;
        }
        
        .info-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            font-size: 12px;
        }
        
        .info-item:last-child {
            margin-bottom: 0;
        }
        
        .info-label {
            color: #64748b;
            font-weight: 500;
        }
        
        .info-value {
            color: #0f172a;
            font-weight: 600;
        }
        
        .btn-group { 
            display: flex; 
            gap: 12px; 
            margin-top: 25px; 
        }
        
        .btn-save { 
            background: linear-gradient(135deg, #059669, #10b981);
            color: white; 
            border: none; 
            padding: 12px 20px; 
            border-radius: 40px; 
            cursor: pointer; 
            flex: 1; 
            font-weight: 600;
            font-size: 13px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: all 0.3s;
        }
        
        .btn-save:hover { 
            background: linear-gradient(135deg, #047857, #059669);
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(5, 150, 105, 0.3);
        }
        
        .btn-cancel { 
            background: #f1f5f9; 
            color: #475569; 
            border: none; 
            padding: 12px 20px; 
            border-radius: 40px; 
            cursor: pointer; 
            flex: 1; 
            text-align: center; 
            text-decoration: none; 
            font-weight: 600;
            font-size: 13px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: all 0.3s;
        }
        
        .btn-cancel:hover { 
            background: #fee2e2; 
            color: #dc2626;
            transform: translateY(-2px);
        }
        
        /* Extension du fichier */
        .filename-wrapper {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .filename-wrapper input {
            flex: 1;
        }
        
        .ext-badge {
            background: #f1f5f9;
            padding: 8px 14px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
            color: #059669;
            border: 1px solid #e2e8f0;
        }

        /* ==================== FOOTER ==================== */
        .footer {
            background: #0f172a;
            color: white;
            padding: 35px 40px 20px;
            margin-top: 50px;
        }

        .footer-content {
            max-width: 1400px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 30px;
        }

        .footer-section h4 {
            font-size: 14px;
            margin-bottom: 12px;
        }

        .footer-section p,
        .footer-section a {
            color: #94a3b8;
            font-size: 12px;
            text-decoration: none;
            display: block;
            margin-bottom: 6px;
        }

        .footer-section a:hover {
            color: #10b981;
        }

        .footer-bottom {
            text-align: center;
            padding-top: 20px;
            margin-top: 20px;
            border-top: 1px solid #1e293b;
            font-size: 11px;
            color: #64748b;
        }
        
        @media (max-width: 650px) {
            .navbar { padding: 0 20px; }
            .hero { padding: 30px 20px; }
            .hero h1 { font-size: 22px; }
            .main-container { padding: 20px; }
            .edit-container { padding: 25px; }
            .btn-group { flex-direction: column; }
            .footer { padding: 30px 20px 15px; }
        }
    </style>
</head>
<body>

<!-- ==================== NAVBAR MODERNE ==================== -->
<nav class="navbar">
    <div class="nav-container">
        <a href="index.php?action=manage" class="logo">
            <div class="logo-icon">
                <i class="fas fa-city"></i>
            </div>
            <div class="logo-text">
                <div class="smart">Smart Municipality</div>
                <div class="municipality">Services en ligne</div>
            </div>
        </a>
        <div class="nav-links">
            <a href="index.php?action=manage"><i class="fas fa-home"></i> Accueil</a>
            <a href="#"><i class="fas fa-user-circle"></i> Mon compte</a>
            <a href="index.php?action=dashboard" class="btn-backoffice"><i class="fas fa-chart-line"></i> Administration</a>
        </div>
    </div>
</nav>

<!-- ==================== HERO ==================== -->
<section class="hero">
    <h1>Modifier le document</h1>
    <p>Modifiez le nom du fichier ci-dessous</p>
</section>

<!-- ==================== MAIN CONTENT ==================== -->
<main class="main-container">
    <div class="edit-container">
        <div class="edit-header">
            <i class="fas fa-file-alt"></i>
            <h2>Modification du fichier</h2>
            <p>Choisissez un nouveau nom pour votre document</p>
            <span class="badge-doc">
                <i class="fas fa-folder-open"></i> Document #<?php echo $doc['id']; ?>
            </span>
        </div>

        <form action="index.php?action=update_document" method="POST">
            <input type="hidden" name="id" value="<?php echo $doc['id']; ?>">
            <input type="hidden" name="demande_id" value="<?php echo $doc['demande_id']; ?>">
            
            <div class="form-group">
                <label><i class="fas fa-tag"></i> Nom du fichier</label>
                <div class="filename-wrapper">
                    <input type="text" name="nom_fichier" value="<?php echo htmlspecialchars(pathinfo($doc['nom_fichier'], PATHINFO_FILENAME)); ?>" required placeholder="Nouveau nom du fichier">
                    <span class="ext-badge">.<?php echo pathinfo($doc['nom_fichier'], PATHINFO_EXTENSION); ?></span>
                </div>
                <div class="input-info">
                    <i class="fas fa-info-circle"></i> Modifiez uniquement le nom, l'extension est conservée automatiquement
                </div>
            </div>

            <div class="info-box">
                <div class="info-item">
                    <span class="info-label"><i class="fas fa-file"></i> Fichier actuel :</span>
                    <span class="info-value"><?php echo htmlspecialchars($doc['nom_fichier']); ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label"><i class="fas fa-file-alt"></i> Type :</span>
                    <span class="info-value"><?php echo strtoupper(pathinfo($doc['nom_fichier'], PATHINFO_EXTENSION)); ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label"><i class="fas fa-weight-hanging"></i> Taille :</span>
                    <span class="info-value"><?php echo round($doc['taille']/1024, 2); ?> KB</span>
                </div>
                <div class="info-item">
                    <span class="info-label"><i class="fas fa-calendar-alt"></i> Date d'upload :</span>
                    <span class="info-value"><?php echo date('d/m/Y H:i', strtotime($doc['uploaded_at'])); ?></span>
                </div>
            </div>

            <div class="btn-group">
                <button type="submit" class="btn-save">
                    <i class="fas fa-save"></i> Enregistrer
                </button>
                <a href="index.php?action=manage" class="btn-cancel">
                    <i class="fas fa-times"></i> Annuler
                </a>
            </div>
        </form>
    </div>
</main>

<!-- ==================== FOOTER ==================== -->
<footer class="footer">
    <div class="footer-content">
        <div class="footer-section">
            <h4>Smart Municipality</h4>
            <p>Simplifiez vos démarches administratives</p>
        </div>
        <div class="footer-section">
            <h4>Liens rapides</h4>
            <a href="index.php?action=manage">Accueil</a>
            <a href="#">Services en ligne</a>
            <a href="#">Contact</a>
            <a href="#">FAQ</a>
        </div>
        <div class="footer-section">
            <h4>Contact</h4>
            <a href="mailto:contact@smartmunicipality.com">contact@smartmunicipality.com</a>
            <a href="tel:+33123456789">+33 1 23 45 67 89</a>
        </div>
    </div>
    <div class="footer-bottom">
        &copy; 2026 Smart Municipality - Tous droits réservés
    </div>
</footer>

</body>
</html>