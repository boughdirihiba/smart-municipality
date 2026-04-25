<?php
// Ce fichier est pour modifier une DEMANDE, pas un document
// La variable $demande est passée par DemandeController
if(!isset($demande)) {
    header("Location: index.php?action=manage");
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Smart Municipality - Modifier une demande</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', 'Segoe UI', Arial, sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #eef2f7 100%);
            min-height: 100vh;
        }

        .navbar {
            background: white;
            padding: 0 60px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .nav-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 1400px;
            margin: 0 auto;
            padding: 12px 0;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
        }

        .logo img {
            height: 45px;
            width: auto;
            object-fit: contain;
        }

        .logo .smart {
            font-size: 22px;
            font-weight: 800;
            background: linear-gradient(135deg, #10b981, #059669);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .logo .municipality {
            font-size: 18px;
            font-weight: 600;
            color: #1e293b;
        }

        .nav-links {
            display: flex;
            gap: 25px;
            align-items: center;
            flex-wrap: wrap;
        }

        .nav-links a {
            text-decoration: none;
            color: #475569;
            font-weight: 500;
            transition: all 0.3s;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .nav-links a:hover {
            color: #10b981;
        }

        .btn-backoffice {
            background: linear-gradient(135deg, #10b981, #059669);
            color: white !important;
            padding: 8px 20px;
            border-radius: 40px;
            font-weight: 600;
        }

        .main-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 40px 60px;
        }

        .form-card {
            background: white;
            border-radius: 32px;
            padding: 40px;
            box-shadow: 0 20px 35px -12px rgba(0,0,0,0.1);
            margin-top: 20px;
        }

        .form-header {
            text-align: center;
            margin-bottom: 35px;
        }

        .form-header h2 {
            font-size: 28px;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 10px;
        }

        .form-header p {
            color: #64748b;
            font-size: 14px;
        }

        .form-header i {
            background: #d1fae5;
            padding: 15px;
            border-radius: 50%;
            color: #059669;
            font-size: 24px;
            margin-bottom: 15px;
            display: inline-block;
        }

        .form-group {
            margin-bottom: 24px;
        }

        .form-group label {
            display: block;
            font-weight: 600;
            font-size: 14px;
            color: #334155;
            margin-bottom: 8px;
        }

        .form-group label i {
            color: #10b981;
            margin-right: 8px;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 14px 18px;
            border: 2px solid #e2e8f0;
            border-radius: 16px;
            font-size: 14px;
            font-family: 'Inter', sans-serif;
            transition: all 0.3s;
            background: #fafbfc;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #10b981;
            background: white;
            box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1);
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .badge-id {
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
            padding: 8px 16px;
            border-radius: 30px;
            font-size: 14px;
            font-weight: 600;
            display: inline-block;
            margin-bottom: 20px;
        }

        .btn-group {
            display: flex;
            gap: 15px;
            margin-top: 16px;
        }

        .btn-save {
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
            border: none;
            padding: 16px 32px;
            border-radius: 40px;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            flex: 1;
            transition: all 0.3s;
            font-family: 'Inter', sans-serif;
        }

        .btn-save:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(16, 185, 129, 0.3);
        }

        .btn-cancel {
            background: #f1f5f9;
            color: #475569;
            border: 2px solid #e2e8f0;
            padding: 16px 32px;
            border-radius: 40px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            flex: 1;
            transition: all 0.3s;
            font-family: 'Inter', sans-serif;
            text-align: center;
            text-decoration: none;
            display: inline-block;
        }

        .btn-cancel:hover {
            background: #fee2e2;
            border-color: #dc2626;
            color: #dc2626;
        }

        .footer {
            background: #0f172a;
            color: white;
            padding: 40px 60px 24px;
            margin-top: 60px;
        }

        .footer-content {
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 40px;
        }

        .footer-section h4 {
            font-size: 18px;
            margin-bottom: 20px;
        }

        .footer-section p, .footer-section a {
            color: #94a3b8;
            line-height: 1.6;
            text-decoration: none;
            display: block;
            margin-bottom: 10px;
        }

        .footer-section a:hover {
            color: #10b981;
        }

        .social-links {
            display: flex;
            gap: 16px;
        }

        .social-links a {
            width: 40px;
            height: 40px;
            background: rgba(255,255,255,0.1);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .footer-bottom {
            text-align: center;
            padding-top: 32px;
            margin-top: 32px;
            border-top: 1px solid #1e293b;
            color: #64748b;
            font-size: 13px;
        }

        @media (max-width: 768px) {
            .navbar {
                padding: 0 20px;
            }
            .nav-container {
                flex-direction: column;
                gap: 15px;
            }
            .main-container {
                padding: 20px;
            }
            .form-row {
                grid-template-columns: 1fr;
            }
            .form-card {
                padding: 24px;
            }
            .btn-group {
                flex-direction: column;
            }
            .footer {
                padding: 40px 20px 20px;
            }
        }
    </style>
</head>
<body>

<nav class="navbar">
    <div class="nav-container">
        <a href="index.php?action=manage" class="logo">
            <img src="assets/images/logo.png" alt="Logo Smart Municipality">
            <div>
                <span class="smart">Smart</span>
                <span class="municipality">Municipality</span>
            </div>
        </a>
        <div class="nav-links">
            <a href="#"><i class="fas fa-user-circle"></i> Profil</a>
            <a href="#"><i class="fas fa-calendar-alt"></i> Événement</a>
            <a href="#"><i class="fas fa-map"></i> Carte</a>
            <a href="#"><i class="fas fa-blog"></i> Blog</a>
            <a href="#"><i class="fas fa-concierge-bell"></i> Services</a>
            <a href="#"><i class="fas fa-calendar-check"></i> Rendez-vous</a>
            <a href="index.php?action=dashboard" class="btn-backoffice"><i class="fas fa-chart-line"></i> BackOffice</a>
        </div>
    </div>
</nav>

<main class="main-container">
    <div class="form-card">
        <div class="form-header">
            <i class="fas fa-edit"></i>
            <h2>Modifier la demande</h2>
            <p>Modifiez les informations de votre demande ci-dessous</p>
        </div>

        <div style="text-align: center; margin-bottom: 25px;">
            <span class="badge-id">
                <i class="fas fa-hashtag"></i> Demande #<?php echo $demande['id']; ?>
            </span>
        </div>

        <form action="index.php?action=update" method="POST">
            <input type="hidden" name="id" value="<?php echo $demande['id']; ?>">

            <div class="form-group">
                <label><i class="fas fa-user"></i> Nom complet *</label>
                <input type="text" name="nom" value="<?php echo htmlspecialchars($demande['nom']); ?>" required placeholder="Votre nom et prénom">
            </div>

            <div class="form-group">
                <label><i class="fas fa-concierge-bell"></i> Type de service *</label>
                <select name="type_service" required>
                    <option value="">-- Sélectionnez un service --</option>
                    <option value="Légalisation de documents" <?php echo ($demande['type_service'] == 'Légalisation de documents') ? 'selected' : ''; ?>>📜 Légalisation de documents</option>
                    <option value="Extrait de naissance" <?php echo ($demande['type_service'] == 'Extrait de naissance') ? 'selected' : ''; ?>>👶 Extrait de naissance</option>
                    <option value="Acte de mariage" <?php echo ($demande['type_service'] == 'Acte de mariage') ? 'selected' : ''; ?>>💍 Acte de mariage</option>
                    <option value="Paiement taxes" <?php echo ($demande['type_service'] == 'Paiement taxes') ? 'selected' : ''; ?>>💰 Paiement taxes</option>
                    <option value="Dépôt de dossier" <?php echo ($demande['type_service'] == 'Dépôt de dossier') ? 'selected' : ''; ?>>📁 Dépôt de dossier</option>
                    <option value="Réclamation citoyenne" <?php echo ($demande['type_service'] == 'Réclamation citoyenne') ? 'selected' : ''; ?>>📢 Réclamation citoyenne</option>
                </select>
            </div>

            <div class="form-group">
                <label><i class="fas fa-file-alt"></i> Documents requis *</label>
                <textarea name="documents" rows="3" required placeholder="Liste des documents fournis"><?php echo htmlspecialchars($demande['documents']); ?></textarea>
            </div>

            <div class="form-group">
                <label><i class="fas fa-calendar-alt"></i> Date de création *</label>
                <input type="date" name="date_creation" value="<?php echo $demande['date_creation']; ?>" required>
            </div>

            <div class="btn-group">
                <button type="submit" class="btn-save">
                    <i class="fas fa-save"></i> Enregistrer les modifications
                </button>
                <a href="index.php?action=manage" class="btn-cancel">
                    <i class="fas fa-times"></i> Annuler
                </a>
            </div>
        </form>
    </div>
</main>

<footer class="footer">
    <div class="footer-content">
        <div class="footer-section">
            <h4>Smart Municipality</h4>
            <p>Simplifiez vos démarches administratives avec notre plateforme digitale intelligente.</p>
        </div>
        <div class="footer-section">
            <h4>Liens rapides</h4>
            <a href="index.php?action=manage">Accueil</a>
            <a href="#">Services en ligne</a>
            <a href="#">Contact</a>
            <a href="#">FAQ</a>
        </div>
        <div class="footer-section">
            <h4>Nous contacter</h4>
            <a href="mailto:contact@smartmunicipality.com"><i class="fas fa-envelope"></i> contact@smartmunicipality.com</a>
            <a href="tel:+33123456789"><i class="fas fa-phone"></i> +33 1 23 45 67 89</a>
        </div>
        <div class="footer-section">
            <h4>Suivez-nous</h4>
            <div class="social-links">
                <a href="#"><i class="fab fa-facebook-f"></i></a>
                <a href="#"><i class="fab fa-twitter"></i></a>
                <a href="#"><i class="fab fa-linkedin-in"></i></a>
                <a href="#"><i class="fab fa-instagram"></i></a>
            </div>
        </div>
    </div>
    <div class="footer-bottom">
        &copy; 2026 Smart Municipality - Tous droits réservés
    </div>
</footer>

</body>
</html>