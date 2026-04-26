<?php
session_start();
require_once __DIR__ . '/../../controller/EvenementC.php';
require_once __DIR__ . '/../../controller/CategorieEvenementC.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../../index.php');
    exit();
}

$categorieC = new CategorieEvenementC();
$categories = $categorieC->afficherCategories();

$errors = [];
$old = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupération et nettoyage
    $old['titre'] = trim($_POST['titre'] ?? '');
    $old['description'] = trim($_POST['description'] ?? '');
    $old['max_participants'] = intval($_POST['max_participants'] ?? 50);
    $old['lieu'] = trim($_POST['lieu'] ?? '');
    $old['date_evenement'] = $_POST['date_evenement'] ?? '';
    $old['heure'] = $_POST['heure'] ?? '';
    $old['categorie_id'] = intval($_POST['categorie_id'] ?? 0);
    
    // ========== VALIDATION TITRE ==========
    if (empty($old['titre'])) {
        $errors['titre'] = 'Le titre est obligatoire';
    } elseif (strlen($old['titre']) < 3) {
        $errors['titre'] = 'Le titre doit contenir au moins 3 caractères';
    } elseif (strlen($old['titre']) > 255) {
        $errors['titre'] = 'Le titre ne peut pas dépasser 255 caractères';
    }
    
    // ========== VALIDATION DESCRIPTION ==========
    if (empty($old['description'])) {
        $errors['description'] = 'La description est obligatoire';
    } elseif (strlen($old['description']) < 10) {
        $errors['description'] = 'La description doit contenir au moins 10 caractères';
    } elseif (strlen($old['description']) > 2000) {
        $errors['description'] = 'La description ne peut pas dépasser 2000 caractères';
    }
    
    // ========== VALIDATION NOMBRE DE PARTICIPANTS ==========
    if ($old['max_participants'] < 1) {
        $errors['max_participants'] = 'Le nombre de participants doit être au moins 1';
    } elseif ($old['max_participants'] > 1000) {
        $errors['max_participants'] = 'Le nombre de participants ne peut pas dépasser 1000';
    }
    
    // ========== VALIDATION LIEU ==========
    if (empty($old['lieu'])) {
        $errors['lieu'] = 'Le lieu est obligatoire';
    } elseif (strlen($old['lieu']) < 3) {
        $errors['lieu'] = 'Le lieu doit contenir au moins 3 caractères';
    }
    
    // ========== VALIDATION DATE (doit être >= aujourd'hui) ==========
    if (empty($old['date_evenement'])) {
        $errors['date_evenement'] = 'La date est obligatoire';
    } else {
        $date = DateTime::createFromFormat('Y-m-d', $old['date_evenement']);
        $aujourdhui = new DateTime();
        $aujourdhui->setTime(0, 0, 0);
        
        if (!$date || $date->format('Y-m-d') !== $old['date_evenement']) {
            $errors['date_evenement'] = 'Format de date invalide (AAAA-MM-JJ)';
        } elseif ($date < $aujourdhui) {
            $errors['date_evenement'] = 'La date doit être aujourd\'hui ou dans le futur';
        }
    }
    
    // ========== VALIDATION HEURE (00:00 à 23:59) ==========
    if (empty($old['heure'])) {
        $errors['heure'] = 'L\'heure est obligatoire';
    } elseif (!preg_match('/^([0-1][0-9]|2[0-3]):[0-5][0-9]$/', $old['heure'])) {
        $errors['heure'] = 'Format d\'heure invalide (HH:MM) de 00:00 à 23:59';
    } else {
        $heures = explode(':', $old['heure']);
        $h = intval($heures[0]);
        $m = intval($heures[1]);
        
        if ($h < 0 || $h > 23) {
            $errors['heure'] = 'Les heures doivent être entre 00 et 23';
        }
        if ($m < 0 || $m > 59) {
            $errors['heure'] = 'Les minutes doivent être entre 00 et 59';
        }
    }
    
    // ========== VALIDATION CATEGORIE ==========
    if ($old['categorie_id'] <= 0) {
        $errors['categorie_id'] = 'Veuillez sélectionner une catégorie';
    } else {
        $catExists = false;
        foreach ($categories as $cat) {
            if ($cat['id'] == $old['categorie_id']) {
                $catExists = true;
                break;
            }
        }
        if (!$catExists) {
            $errors['categorie_id'] = 'Catégorie invalide';
        }
    }
    
    // ========== INSERTION ==========
    if (empty($errors)) {
        require_once __DIR__ . '/../../model/Evenement.php';
        $evenement = new Evenement(
            $old['titre'],
            $old['description'],
            $old['lieu'],
            $old['date_evenement'],
            $old['heure'],
            $old['categorie_id'],
            $old['max_participants']
        );
        
        $evenementC = new EvenementC();
        if ($evenementC->ajouterEvenement($evenement)) {
            $_SESSION['success'] = 'Événement ajouté avec succès';
            header('Location: liste.php');
            exit();
        } else {
            $errors['global'] = 'Erreur lors de l\'ajout en base de données';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter un événement - Smart Municipality</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root { --primary: #1a5e2a; --primary-dark: #0d3b1a; --gradient: linear-gradient(135deg, #1a5e2a, #4caf50); }
        body { font-family: 'Inter', sans-serif; background: linear-gradient(135deg, #e8f5e9, #f0f4f0); min-height: 100vh; padding: 40px 0; }
        .form-card { max-width: 750px; margin: 0 auto; background: white; border-radius: 28px; box-shadow: 0 20px 40px rgba(0,0,0,0.1); overflow: hidden; }
        .form-header { background: var(--gradient); padding: 30px; text-align: center; color: white; }
        .form-header h2 { font-weight: 700; margin: 0; }
        .form-body { padding: 35px; }
        .form-label { font-weight: 600; color: var(--primary); margin-bottom: 8px; }
        .form-control, .form-select { border: 2px solid #e9ecef; border-radius: 12px; padding: 12px 16px; transition: all 0.2s; }
        .form-control:focus, .form-select:focus { border-color: var(--primary); box-shadow: 0 0 0 3px rgba(26,94,42,0.1); }
        .is-invalid { border-color: #dc3545; }
        .invalid-feedback { font-size: 0.75rem; margin-top: 5px; color: #dc3545; }
        .btn-primary-custom { background: var(--gradient); border: none; padding: 12px; border-radius: 12px; font-weight: 600; transition: all 0.2s; }
        .btn-primary-custom:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(26,94,42,0.3); }
        .required:after { content: " *"; color: #dc3545; }
    </style>
</head>
<body>
    <div class="container">
        <div class="form-card">
            <div class="form-header">
                <h2><i class="fas fa-plus-circle me-2"></i>Ajouter un événement</h2>
                <p class="mb-0">Remplissez toutes les informations</p>
            </div>
            <div class="form-body">
                <?php if (isset($errors['global'])): ?>
                    <div class="alert alert-danger"><?php echo $errors['global']; ?></div>
                <?php endif; ?>
                
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label required">Titre</label>
                        <input type="text" name="titre" class="form-control <?php echo isset($errors['titre']) ? 'is-invalid' : ''; ?>" 
                               value="<?php echo htmlspecialchars($old['titre'] ?? ''); ?>">
                        <?php if (isset($errors['titre'])): ?>
                            <div class="invalid-feedback"><?php echo $errors['titre']; ?></div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label required">Description</label>
                        <textarea name="description" rows="4" class="form-control <?php echo isset($errors['description']) ? 'is-invalid' : ''; ?>"><?php echo htmlspecialchars($old['description'] ?? ''); ?></textarea>
                        <?php if (isset($errors['description'])): ?>
                            <div class="invalid-feedback"><?php echo $errors['description']; ?></div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label required">Participants max</label>
                            <input type="number" name="max_participants" class="form-control <?php echo isset($errors['max_participants']) ? 'is-invalid' : ''; ?>" 
                                   value="<?php echo htmlspecialchars($old['max_participants'] ?? 50); ?>">
                            <?php if (isset($errors['max_participants'])): ?>
                                <div class="invalid-feedback"><?php echo $errors['max_participants']; ?></div>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label required">Lieu</label>
                            <input type="text" name="lieu" class="form-control <?php echo isset($errors['lieu']) ? 'is-invalid' : ''; ?>" 
                                   value="<?php echo htmlspecialchars($old['lieu'] ?? ''); ?>">
                            <?php if (isset($errors['lieu'])): ?>
                                <div class="invalid-feedback"><?php echo $errors['lieu']; ?></div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label required">Date</label>
                            <input type="date" name="date_evenement" class="form-control <?php echo isset($errors['date_evenement']) ? 'is-invalid' : ''; ?>" 
                                   value="<?php echo htmlspecialchars($old['date_evenement'] ?? ''); ?>" min="<?php echo date('Y-m-d'); ?>">
                            <?php if (isset($errors['date_evenement'])): ?>
                                <div class="invalid-feedback"><?php echo $errors['date_evenement']; ?></div>
                            <?php endif; ?>
                            <small class="text-muted">La date doit être aujourd'hui ou dans le futur</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label required">Heure</label>
                            <input type="time" name="heure" class="form-control <?php echo isset($errors['heure']) ? 'is-invalid' : ''; ?>" 
                                   value="<?php echo htmlspecialchars($old['heure'] ?? ''); ?>" step="60">
                            <?php if (isset($errors['heure'])): ?>
                                <div class="invalid-feedback"><?php echo $errors['heure']; ?></div>
                            <?php endif; ?>
                            <small class="text-muted">Format HH:MM (de 00:00 à 23:59)</small>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <label class="form-label required">Catégorie</label>
                        <select name="categorie_id" class="form-select <?php echo isset($errors['categorie_id']) ? 'is-invalid' : ''; ?>">
                            <option value="">-- Sélectionner une catégorie --</option>
                            <?php foreach($categories as $cat): ?>
                            <option value="<?php echo $cat['id']; ?>" <?php echo (($old['categorie_id'] ?? '') == $cat['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cat['nom']); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                        <?php if (isset($errors['categorie_id'])): ?>
                            <div class="invalid-feedback"><?php echo $errors['categorie_id']; ?></div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="d-flex gap-3">
                        <a href="liste.php" class="btn btn-secondary flex-grow-1"><i class="fas fa-arrow-left me-2"></i>Annuler</a>
                        <button type="submit" class="btn btn-primary-custom flex-grow-1"><i class="fas fa-save me-2"></i>Ajouter</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>