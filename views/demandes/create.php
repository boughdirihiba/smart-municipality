<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Smart Municipality - Nouvelle demande</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background: #f4f6f5;
        }

        .container {
            display: flex;
            min-height: 100vh;
        }

        /* SIDEBAR */
        .sidebar {
            width: 220px;
            background: #1f5f3a;
            color: white;
            padding: 20px;
        }

        .sidebar h2 {
            margin-bottom: 30px;
        }

        .sidebar ul {
            list-style: none;
            padding: 0;
        }

        .sidebar li {
            padding: 12px;
            margin: 10px 0;
            border-radius: 8px;
            cursor: pointer;
        }

        .sidebar li.active {
            background: #2ecc71;
        }

        /* MAIN */
        .main {
            flex: 1;
            padding: 20px;
        }

        /* TOPBAR */
        .topbar {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
        }

        .topbar input {
            width: 60%;
            padding: 10px;
            border-radius: 20px;
            border: none;
            background: #e0f2e9;
        }

        .user {
            background: #1f5f3a;
            color: white;
            padding: 10px 15px;
            border-radius: 20px;
        }

        /* STEPS */
        .steps span {
            margin-right: 10px;
            padding: 8px 12px;
            background: #ddd;
            border-radius: 20px;
        }

        .steps .active {
            background: #2ecc71;
            color: white;
        }

        /* RIGHT PANEL */
        .right-panel {
            width: 250px;
            background: #fff;
            padding: 20px;
        }

        .send {
            background: orange;
            color: white;
            border: none;
            padding: 10px;
            width: 100%;
            border-radius: 20px;
            margin-top: 20px;
            cursor: pointer;
        }

        /* FORMULAIRE VERT FONCÉ */
        .form-box {
            background: linear-gradient(135deg, #1a4731 0%, #0f2e1f 100%);
            border-radius: 25px;
            padding: 35px;
            margin-top: 25px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.2);
        }

        .title {
            color: #1a4731;
            font-size: 28px;
            text-align: center;
            margin-bottom: 20px;
        }

        .form-box input, .form-box select, .form-box textarea {
            width: 100%;
            padding: 14px 18px;
            margin-bottom: 15px;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            background: #f0f7f3;
            border: 2px solid transparent;
        }

        .form-box input:focus, .form-box select:focus, .form-box textarea:focus {
            outline: none;
            border-color: #2ecc71;
            background: #ffffff;
        }

        .form-box input.error, .form-box select.error, .form-box textarea.error {
            border-color: #ff6b6b;
            background: #fff5f5;
        }

        .error-message {
            color: #ffaaaa;
            font-size: 13px;
            margin-top: -10px;
            margin-bottom: 10px;
            display: block;
        }

        .form-box button[type="submit"] {
            background: #2ecc71;
            color: #1a4731;
            border: none;
            padding: 14px 35px;
            border-radius: 40px;
            font-size: 18px;
            font-weight: bold;
            cursor: pointer;
            width: 100%;
            transition: all 0.3s ease;
        }

        .form-box button[type="submit"]:hover {
            background: #27ae60;
            color: white;
        }

        .dash-btn {
            background: #1f5f3a;
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 25px;
            cursor: pointer;
            margin-top: 15px;
        }

        .dash-btn:hover {
            background: #2ecc71;
        }
    </style>
</head>
<body>

<div class="container">

    <!-- SIDEBAR -->
    <div class="sidebar">
        <h2>Smart Municipality</h2>
        <ul>
            <li class="active">Services en ligne</li>
            <li>Accéder au BackOffice</li>
            <li>Rendez-vous</li>
            <li>Paramètres</li>
            <li>Déconnexion</li>
        </ul>
    </div>

    <!-- MAIN -->
    <div class="main">

        <div class="topbar">
            <input type="text" placeholder="Rechercher un service...">
            <div class="user">Eliza Thorne</div>
        </div>

        <h1>VOTRE COMPTE - NOUVELLE DEMANDE</h1>

        <div class="steps">
            <span class="active">1 Choix du Service</span>
            <span>2 Documents requis</span>
            <span>3 Téléversement</span>
            <span>4 Soumission</span>
        </div>

        <h2 class="title">Ajouter une demande</h2>

        <div class="form-box">
            <form action="index.php?action=store" method="POST" onsubmit="return validateForm()">
                <input type="text" id="id" name="id" placeholder="ID *">
                <span class="error-message" id="idError"></span>

                <input type="text" id="nom" name="nom" placeholder="Nom complet *">
                <span class="error-message" id="nomError"></span>

                <select id="type_service" name="type_service">
                    <option value="">-- Type de service * --</option>
                    <option value="Légalisation de documents">Légalisation de documents</option>
                    <option value="Extrait de naissance">Extrait de naissance</option>
                    <option value="Paiement taxes">Paiement taxes</option>
                    <option value="Dépôt de dossier">Dépôt de dossier</option>
                </select>
                <span class="error-message" id="serviceError"></span>

                <textarea id="documents" name="documents" placeholder="Documents requis * (liste des documents fournis)" rows="3"></textarea>
                <span class="error-message" id="documentsError"></span>

                <input type="date" id="date_creation" name="date_creation">
                <span class="error-message" id="dateError"></span>

                <button type="submit">Envoyer la demande</button>
            </form>
        </div>

        <br>
        <a href="index.php?action=dashboard">
            <button class="dash-btn">Dashboard</button>
        </a>
    </div>

    <!-- RIGHT PANEL -->
    <div class="right-panel">
        <h3>RÉSUMÉ DU SERVICE</h3>
        <p><b>Service sélectionné :</b><br> <span id="selectedService">Aucun service choisi</span></p>
        <p><b>Progression :</b><br>1. Service choisi<br>2. Documents requis<br>3. Téléversement<br>4. Soumission</p>
        <button class="send" id="quickSendBtn">Envoyer la demande</button>
    </div>

</div>

<script>
    // Mise à jour du résumé service
    const serviceSelect = document.getElementById('type_service');
    const selectedServiceSpan = document.getElementById('selectedService');
    if(serviceSelect) {
        serviceSelect.addEventListener('change', function() {
            selectedServiceSpan.textContent = this.value || "Aucun service choisi";
        });
    }

    function validateForm() {
        let isValid = true;

        const idField = document.getElementById('id');
        const nomField = document.getElementById('nom');
        const serviceField = document.getElementById('type_service');
        const documentsField = document.getElementById('documents');
        const dateField = document.getElementById('date_creation');

        // Reset erreurs
        document.querySelectorAll('.error-message').forEach(el => el.innerHTML = '');
        document.querySelectorAll('.form-box input, .form-box select, .form-box textarea').forEach(el => el.classList.remove('error'));

        // Validation ID
        if(!idField.value.trim()) {
            document.getElementById('idError').innerHTML = 'ID obligatoire';
            idField.classList.add('error');
            isValid = false;
        } else if(isNaN(idField.value.trim()) || parseInt(idField.value.trim()) <= 0) {
            document.getElementById('idError').innerHTML = 'ID doit être un nombre positif';
            idField.classList.add('error');
            isValid = false;
        }

        // Validation Nom
        if(!nomField.value.trim()) {
            document.getElementById('nomError').innerHTML = 'Nom obligatoire';
            nomField.classList.add('error');
            isValid = false;
        } else if(nomField.value.trim().length < 3) {
            document.getElementById('nomError').innerHTML = 'Nom doit contenir au moins 3 caractères';
            nomField.classList.add('error');
            isValid = false;
        }

        // Validation Service
        if(!serviceField.value) {
            document.getElementById('serviceError').innerHTML = 'Veuillez sélectionner un service';
            serviceField.classList.add('error');
            isValid = false;
        }

        // Validation Documents
        if(!documentsField.value.trim()) {
            document.getElementById('documentsError').innerHTML = 'Documents requis obligatoire';
            documentsField.classList.add('error');
            isValid = false;
        } else if(documentsField.value.trim().length < 10) {
            document.getElementById('documentsError').innerHTML = 'Décrivez les documents (min 10 caractères)';
            documentsField.classList.add('error');
            isValid = false;
        }

        // Validation Date
        if(!dateField.value) {
            document.getElementById('dateError').innerHTML = 'Date obligatoire';
            dateField.classList.add('error');
            isValid = false;
        }

        if(!isValid) {
            alert('Veuillez corriger les erreurs dans le formulaire');
        } else {
            alert('Formulaire validé ! Envoi en cours...');
        }

        return isValid;
    }

    // Bouton rapide
    const quickBtn = document.getElementById('quickSendBtn');
    if(quickBtn) {
        quickBtn.addEventListener('click', function() {
            const form = document.querySelector('form');
            if(form) {
                if(validateForm()) {
                    form.submit();
                }
            }
        });
    }
</script>

</body>
</html>