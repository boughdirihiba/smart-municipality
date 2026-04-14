<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Smart Municipality - Modifier un événement</title>
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
        
        /* Back Button */
        .back-btn-container {
            margin-bottom: 25px;
        }
        
        .btn-back {
            background: #95a5a6;
            color: white;
            padding: 10px 20px;
            border-radius: 8px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s;
        }
        
        .btn-back:hover {
            background: #7f8c8d;
            transform: translateY(-2px);
        }
        
        /* Form Container */
        .form-container {
            max-width: 700px;
            background: white;
            padding: 30px;
            border-radius: 16px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            margin: 0 auto;
        }
        
        .form-container h1 {
            color: #1e8449;
            margin-bottom: 25px;
            font-family: 'Poppins', sans-serif;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #333;
        }
        
        .form-control {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 14px;
            transition: border-color 0.3s;
        }
        
        .form-control:focus {
            outline: none;
            border-color: #2ecc71;
            box-shadow: 0 0 0 3px rgba(46,204,113,0.1);
        }
        
        textarea.form-control {
            resize: vertical;
            min-height: 100px;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }
        
        /* Boutons */
        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #2ecc71 0%, #27ae60 100%);
            color: white;
        }
        
        .btn-primary:hover {
            background: linear-gradient(135deg, #27ae60 0%, #1e8449 100%);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(46,204,113,0.3);
        }
        
        .btn-secondary {
            background: #95a5a6;
            color: white;
            margin-left: 10px;
        }
        
        .btn-secondary:hover {
            background: #7f8c8d;
            transform: translateY(-2px);
        }
        
        /* Alert */
        .alert {
            padding: 14px 20px;
            border-radius: 10px;
            margin-bottom: 20px;
        }
        
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border-left: 4px solid #e74c3c;
        }
        
        .alert ul {
            margin-left: 20px;
        }
        
        /* Current Image */
        .current-image {
            margin-top: 10px;
        }
        
        .current-image img {
            max-width: 150px;
            border-radius: 8px;
            border: 1px solid #ddd;
        }
        
        .error-message {
            color: #e74c3c;
            font-size: 12px;
            margin-top: 5px;
            display: none;
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
            .form-row {
                grid-template-columns: 1fr;
            }
            .form-container {
                margin: 0;
                padding: 20px;
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
    
    <a href="profile.php" class="nav-item">
        <i class="fas fa-user-circle"></i>
        <span>Profile</span>
    </a>
    
    <a href="index.php?action=manage" class="nav-item active">
        <i class="fas fa-calendar-alt"></i>
        <span>Événements</span>
    </a>
    
    
    <a href="carte.php" class="nav-item">
        <i class="fas fa-map-marked-alt"></i>
        <span>Carte intelligente</span>
    </a>
    
    <a href="blog.php" class="nav-item">
        <i class="fas fa-blog"></i>
        <span>Blog</span>
    </a>
    
    <a href="services.php" class="nav-item">
        <i class="fas fa-concierge-bell"></i>
        <span>Services en ligne</span>
    </a>
    
    <a href="rdv.php" class="nav-item">
        <i class="fas fa-calendar-check"></i>
        <span>Rendez-vous</span>
    </a>
</div>

<!-- Main Content -->
<div class="main-content">
    <div class="back-btn-container">
        <a href="index.php?action=manage" class="btn-back">
            <i class="fas fa-arrow-left"></i> Retour à la gestion
        </a>
    </div>

    <?php if(!empty($errors)): ?>
        <div class="alert alert-error">
            <ul>
                <?php foreach($errors as $error): ?>
                    <li><?php echo $error; ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <div class="form-container">
        <h1><i class="fas fa-edit"></i> Modifier l'événement</h1>
        
        <form method="POST" action="" enctype="multipart/form-data" id="eventForm">
            <div class="form-group">
                <label>Titre * (5-100 caractères)</label>
                <input type="text" name="titre" class="form-control" value="<?php echo htmlspecialchars($this->event->titre); ?>" required>
                <small class="error-message" id="titreError">Le titre doit contenir au moins 5 caractères.</small>
            </div>

            <div class="form-group">
                <label>Description * (10-500 caractères)</label>
                <textarea name="description" class="form-control" rows="5" required><?php echo htmlspecialchars($this->event->description); ?></textarea>
                <small class="error-message" id="descriptionError">La description doit contenir au moins 10 caractères.</small>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Lieu *</label>
                    <input type="text" name="lieu" class="form-control" value="<?php echo htmlspecialchars($this->event->lieu); ?>" required>
                    <small class="error-message" id="lieuError">Le lieu doit contenir au moins 3 caractères.</small>
                </div>
                <div class="form-group">
                    <label>Catégorie *</label>
                    <select name="categorie" class="form-control" required>
                        <option value="Culture" <?php echo ($this->event->categorie == 'Culture') ? 'selected' : ''; ?>>Culture</option>
                        <option value="Sport" <?php echo ($this->event->categorie == 'Sport') ? 'selected' : ''; ?>>Sport</option>
                        <option value="Environnement" <?php echo ($this->event->categorie == 'Environnement') ? 'selected' : ''; ?>>Environnement</option>
                        <option value="Social" <?php echo ($this->event->categorie == 'Social') ? 'selected' : ''; ?>>Social</option>
                        <option value="Education" <?php echo ($this->event->categorie == 'Education') ? 'selected' : ''; ?>>Éducation</option>
                    </select>
                    <small class="error-message" id="categorieError">Veuillez sélectionner une catégorie.</small>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Date * (Doit être après aujourd'hui)</label>
                    <input type="date" name="date_evenement" class="form-control" id="dateEvent" value="<?php echo $this->event->date_evenement; ?>" required>
                    <small class="error-message" id="dateError">La date doit être supérieure à aujourd'hui.</small>
                </div>
                <div class="form-group">
                    <label>Heure * (HH:MM)</label>
                    <input type="time" name="heure" class="form-control" value="<?php echo $this->event->heure; ?>" required>
                    <small class="error-message" id="heureError">Format d'heure invalide (HH:MM).</small>
                </div>
            </div>

            <?php if(!empty($this->event->image_url) && file_exists($this->event->image_url)): ?>
                <div class="form-group">
                    <label>Image actuelle</label>
                    <div class="current-image">
                        <img src="<?php echo $this->event->image_url; ?>" alt="Image actuelle">
                    </div>
                </div>
            <?php endif; ?>

            <div class="form-group">
                <label>Nouvelle image (optionnel)</label>
                <input type="file" name="image" class="form-control" accept="image/*">
                <small>Laissez vide pour conserver l'image actuelle</small>
            </div>

            <div style="margin-top: 25px;">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Mettre à jour
                </button>
                <a href="index.php?action=manage" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Annuler
                </a>
            </div>
        </form>
    </div>
</div>

<script>
// Désactiver les dates passées dans le sélecteur
const dateInput = document.getElementById('dateEvent');
const today = new Date().toISOString().split('T')[0];
dateInput.setAttribute('min', today);

// Validation de la date
function validateDate() {
    const date = dateInput.value;
    const errorSpan = document.getElementById('dateError');
    
    if(date === '') {
        errorSpan.textContent = 'La date est requise.';
        errorSpan.style.display = 'block';
        return false;
    }
    
    const selectedDate = new Date(date);
    const todayDate = new Date();
    todayDate.setHours(0, 0, 0, 0);
    
    if(selectedDate < todayDate) {
        errorSpan.textContent = 'La date doit être supérieure à aujourd\'hui.';
        errorSpan.style.display = 'block';
        return false;
    }
    
    errorSpan.style.display = 'none';
    return true;
}

function validateTitre() {
    const titre = document.querySelector('input[name="titre"]').value.trim();
    const errorSpan = document.getElementById('titreError');
    if(titre.length < 5) {
        errorSpan.style.display = 'block';
        return false;
    }
    errorSpan.style.display = 'none';
    return true;
}

function validateDescription() {
    const description = document.querySelector('textarea[name="description"]').value.trim();
    const errorSpan = document.getElementById('descriptionError');
    if(description.length < 10) {
        errorSpan.style.display = 'block';
        return false;
    }
    errorSpan.style.display = 'none';
    return true;
}

function validateLieu() {
    const lieu = document.querySelector('input[name="lieu"]').value.trim();
    const errorSpan = document.getElementById('lieuError');
    if(lieu.length < 3) {
        errorSpan.style.display = 'block';
        return false;
    }
    errorSpan.style.display = 'none';
    return true;
}

function validateCategorie() {
    const categorie = document.querySelector('select[name="categorie"]').value;
    const errorSpan = document.getElementById('categorieError');
    if(categorie === '') {
        errorSpan.style.display = 'block';
        return false;
    }
    errorSpan.style.display = 'none';
    return true;
}

function validateHeure() {
    const heure = document.querySelector('input[name="heure"]').value;
    const errorSpan = document.getElementById('heureError');
    const heureRegex = /^([0-1]?[0-9]|2[0-3]):[0-5][0-9]$/;
    if(heure === '' || !heureRegex.test(heure)) {
        errorSpan.style.display = 'block';
        return false;
    }
    errorSpan.style.display = 'none';
    return true;
}

// Événements en temps réel
document.querySelector('input[name="titre"]').addEventListener('input', validateTitre);
document.querySelector('textarea[name="description"]').addEventListener('input', validateDescription);
document.querySelector('input[name="lieu"]').addEventListener('input', validateLieu);
dateInput.addEventListener('change', validateDate);
document.querySelector('input[name="heure"]').addEventListener('change', validateHeure);
document.querySelector('select[name="categorie"]').addEventListener('change', validateCategorie);

// Validation avant soumission
document.getElementById('eventForm').addEventListener('submit', function(e) {
    const isTitreValid = validateTitre();
    const isDescriptionValid = validateDescription();
    const isLieuValid = validateLieu();
    const isDateValid = validateDate();
    const isHeureValid = validateHeure();
    const isCategorieValid = validateCategorie();
    
    if(!isTitreValid || !isDescriptionValid || !isLieuValid || !isDateValid || !isHeureValid || !isCategorieValid) {
        e.preventDefault();
        const firstError = document.querySelector('.error-message[style*="display: block"]');
        if(firstError) {
            firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    }
});
</script>

</body>
</html>