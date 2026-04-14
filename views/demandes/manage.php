<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Smart Municipality</title>
<link rel="stylesheet" href="assets/style.css">
<style>
    /* Styles supplémentaires pour le tableau des demandes */
    .demandes-list {
        background: white;
        border-radius: 15px;
        padding: 20px;
        margin-top: 30px;
    }
    .demandes-list h3 {
        color: #0f3b2c;
        margin-bottom: 15px;
        padding-bottom: 10px;
        border-bottom: 2px solid #2FA084;
    }
    .demandes-table {
        width: 100%;
        border-collapse: collapse;
    }
    .demandes-table th,
    .demandes-table td {
        padding: 10px;
        text-align: left;
        border-bottom: 1px solid #ddd;
    }
    .demandes-table th {
        background: linear-gradient(135deg, #2FA084, #0f3b2c);
        color: white;
    }
    .demandes-table tr:hover {
        background: #f5f5f5;
    }
    .no-demandes {
        text-align: center;
        padding: 20px;
        color: #999;
    }
    .btn-edit {
        background: #ffc107;
        color: #333;
        border: none;
        padding: 6px 12px;
        border-radius: 15px;
        cursor: pointer;
        margin-right: 5px;
        font-size: 12px;
    }
    .btn-edit:hover {
        background: #e0a800;
    }
    .btn-delete {
        background: #dc3545;
        color: white;
        border: none;
        padding: 6px 12px;
        border-radius: 15px;
        cursor: pointer;
        font-size: 12px;
    }
    .btn-delete:hover {
        background: #c82333;
    }
    .action-buttons {
        display: flex;
        gap: 5px;
    }
    .sidebar li a {
        color: white;
        text-decoration: none;
        display: block;
    }

    /* SIDEBAR - VERT FONCE */
    .sidebar {
        background: linear-gradient(135deg, #2FA084, #0f3b2c);
    }

    /* BOUTONS DU SIDEBAR - ESPACE MODERE */
    .sidebar-btn {
        background: transparent;
        border: none;
        color: white;
        width: 100%;
        text-align: left;
        padding: 10px 12px;
        margin: 5px 0;
        border-radius: 8px;
        cursor: pointer;
        font-size: 14px;
        font-family: Arial;
        transition: all 0.3s;
    }
    .sidebar-btn:hover {
        background: rgba(255, 255, 255, 0.15);
    }
    .sidebar-btn.active {
        background: rgba(255, 255, 255, 0.2);
    }

    /* SUPPRIMER MARGES PAR DEFAUT */
    .sidebar ul {
        margin: 0;
        padding: 0;
    }
    .sidebar li {
        margin: 0;
        padding: 0;
    }

    /* CARTES - VERT FONCE */
    .card button {
        background: linear-gradient(135deg, #2FA084, #0f3b2c);
        color: white;
        border: none;
        padding: 10px 20px;
        border-radius: 20px;
        cursor: pointer;
    }
    .card button:hover {
        background: linear-gradient(135deg, #0f3b2c, #2FA084);
    }

    /* STEPS ACTIVE - VERT FONCE */
    .steps .active {
        background: linear-gradient(135deg, #2FA084, #0f3b2c);
        color: white;
    }

    /* TOPBAR USER - VERT FONCE */
    .user {
        background: linear-gradient(135deg, #2FA084, #0f3b2c);
        color: white;
        padding: 10px 15px;
        border-radius: 20px;
    }

    /* RIGHT PANEL SEND BUTTON */
    .send {
        background: linear-gradient(135deg, #2FA084, #0f3b2c);
        color: white;
        border: none;
        padding: 10px;
        width: 100%;
        border-radius: 20px;
        margin-top: 20px;
        cursor: pointer;
    }
    .send:hover {
        background: linear-gradient(135deg, #0f3b2c, #2FA084);
    }

    /* LIENS DANS SIDEBAR */
    .sidebar h2 {
        color: white;
    }
</style>
</head>

<body>

<div class="container">

    <!-- SIDEBAR -->
    <div class="sidebar">
        <h2>Smart Municipality</h2>
        <ul style="list-style: none; padding: 0; margin: 0;">
            <li style="margin: 0; padding: 0;"><button class="sidebar-btn active" onclick="window.location.href='index.php?action=manage'">🏠 Services en ligne</button></li>
            <li style="margin: 0; padding: 0;"><button class="sidebar-btn" onclick="window.location.href='#'">👤 Profil</button></li>
            <li style="margin: 0; padding: 0;"><button class="sidebar-btn" onclick="window.location.href='#'">⚠️ Signalement</button></li>
            <li style="margin: 0; padding: 0;"><button class="sidebar-btn" onclick="window.location.href='#'">📝 Blog</button></li>
            <li style="margin: 0; padding: 0;"><button class="sidebar-btn" onclick="window.location.href='index.php?action=manage'">🛠️ Service en ligne</button></li>
            <li style="margin: 0; padding: 0;"><button class="sidebar-btn" onclick="window.location.href='#'">🎉 Événement</button></li>
            <li style="margin: 0; padding: 0;"><button class="sidebar-btn" onclick="window.location.href='#'">📅 RDV</button></li>
            <li style="margin: 0; padding: 0;"><button class="sidebar-btn" onclick="window.location.href='index.php?action=dashboard'">📊 Accéder au BackOffice</button></li>
        </ul>
    </div>

    <!-- MAIN -->
    <div class="main">

        <!-- TOP BAR -->
        <div class="topbar">
            <input type="text" placeholder="Rechercher un service...">
            <div class="user">Eliza Thorne</div>
        </div>

        <h1>VOTRE COMPTE - SERVICES EN LIGNE</h1>

        <!-- STEPS -->
        <div class="steps">
            <span class="active">1 Choix du Service</span>
            <span>2 Documents requis</span>
            <span>3 Téléversement</span>
            <span>4 Soumission</span>
        </div>

        <!-- SERVICES -->
        <div class="cards">

            <div class="card">
                <h3>Légalisation de documents</h3>
                <p>Authentifiez vos documents officiels.</p>
                <a href="index.php?action=create">
                    <button>Accéder</button>
                </a>
            </div>

            <div class="card">
                <h3>Extrait de naissance</h3>
                <p>Naissance, mariage, décès.</p>
                <a href="index.php?action=create">
                    <button>Accéder</button>
                </a>
            </div>

            <div class="card">
                <h3>Paiement taxes</h3>
                <p>Impôts locaux, taxes foncières.</p>
                <a href="index.php?action=create">
                    <button>Accéder</button>
                </a>
            </div>

            <div class="card">
                <h3>Dépôt de dossier</h3>
                <p>Urbanisme, permis, demandes.</p>
                <a href="index.php?action=create">
                    <button>Accéder</button>
                </a>
            </div>

        </div>

        <!-- AFFICHAGE DES DEMANDES AJOUTEES -->
        <div class="demandes-list">
            <h3>📋 Mes demandes soumises</h3>
            <table class="demandes-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nom</th>
                        <th>Type de service</th>
                        <th>Documents</th>
                        <th>Date de création</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(isset($demandes) && $demandes && $demandes->rowCount() > 0): ?>
                        <?php while($row = $demandes->fetch(PDO::FETCH_ASSOC)): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['id']); ?></td>
                                <td><?php echo htmlspecialchars($row['nom']); ?></td>
                                <td><?php echo htmlspecialchars($row['type_service']); ?></td>
                                <td><?php echo htmlspecialchars(substr($row['documents'], 0, 40)) . (strlen($row['documents']) > 40 ? '...' : ''); ?></td>
                                <td><?php echo date('d/m/Y', strtotime($row['date_creation'])); ?></td>
                                <td class="action-buttons">
                                    <a href="index.php?action=edit&id=<?php echo $row['id']; ?>">
                                        <button class="btn-edit">✏️ Modifier</button>
                                    </a>
                                    <a href="index.php?action=delete&id=<?php echo $row['id']; ?>" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette demande ?')">
                                        <button class="btn-delete">🗑️ Supprimer</button>
                                    </a>
                                </a>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="no-demandes">Aucune demande pour le moment. Cliquez sur "Accéder" pour créer votre première demande.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    </div>

    <!-- RIGHT PANEL -->
    <div class="right-panel">
        <h3>RÉSUMÉ DU SERVICE</h3>
        <p><b>Service sélectionné :</b><br> Aucun service choisi</p>
        <p><b>Progression :</b><br>
        1. Service choisi<br>
        2. Documents requis<br>
        3. Téléversement<br>
        4. Soumission</p>
        <a href="index.php?action=dashboard">
            <button class="send">Envoyer la demande</button>
        </a>
    </div>

</div>

</body>
</html>