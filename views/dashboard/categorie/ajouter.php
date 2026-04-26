<?php
session_start();
require_once __DIR__ . '/../../../controller/CategorieEvenementC.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../../../index.php');
    exit();
}

$errors = [];
$old = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $old['nom'] = trim($_POST['nom'] ?? '');
    $old['description'] = trim($_POST['description'] ?? '');
    $old['image_url'] = trim($_POST['image_url'] ?? '');
    
    // ========== VALIDATION NOM ==========
    if (empty($old['nom'])) {
        $errors['nom'] = 'Le nom est obligatoire';
    } elseif (strlen($old['nom']) < 2) {
        $errors['nom'] = 'Le nom doit contenir au moins 2 caractères';
    } elseif (strlen($old['nom']) > 100) {
        $errors['nom'] = 'Le nom ne peut pas dépasser 100 caractères';
    } elseif (!preg_match('/^[a-zA-Z0-9\s\-\'éèêëàâôûç]+$/', $old['nom'])) {
        $errors['nom'] = 'Le nom contient des caractères non autorisés';
    }
    
    // ========== VALIDATION DESCRIPTION ==========
    if (empty($old['description'])) {
        $errors['description'] = 'La description est obligatoire';
    } elseif (strlen($old['description']) < 10) {
        $errors['description'] = 'La description doit contenir au moins 10 caractères';
    } elseif (strlen($old['description']) > 500) {
        $errors['description'] = 'La description ne peut pas dépasser 500 caractères';
    }
    
    // ========== VALIDATION IMAGE URL ==========
    if (!empty($old['image_url'])) {
        if (!filter_var($old['image_url'], FILTER_VALIDATE_URL)) {
            $errors['image_url'] = 'L\'URL de l\'image n\'est pas valide';
        } elseif (!preg_match('/\.(jpg|jpeg|png|gif|webp)$/i', $old['image_url'])) {
            $errors['image_url'] = 'L\'URL doit pointer vers une image (jpg, png, gif, webp)';
        }
    }
    
    // ========== INSERTION ==========
    if (empty($errors)) {
        require_once __DIR__ . '/../../../model/CategorieEvenement.php';
        $categorie = new CategorieEvenement($old['nom'], $old['description'], $old['image_url']);
        
        $categorieC = new CategorieEvenementC();
        if ($categorieC->ajouterCategorie($categorie)) {
            $_SESSION['success'] = 'Catégorie ajoutée avec succès';
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
    <title>Ajouter une catégorie - Smart Municipality</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root { --primary: #1a5e2a; --gradient: linear-gradient(135deg, #1a5e2a, #4caf50); }
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #e8f5e9, #f0f4f0);
            min-height: 100vh;
            padding: 40px 0;
        }
        .form-card {
            max-width: 650px;
            margin: 0 auto;
            background: white;
            border-radius: 28px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .form-header { background: var(--gradient); padding: 30px; text-align: center; color: white; }
        .form-header h2 { font-weight: 700; margin: 0; }
        .form-body { padding: 35px; }
        .form-label { font-weight: 600; color: var(--primary); margin-bottom: 8px; }
        .form-control, .form-select {
            border: 2px solid #e9ecef;
            border-radius: 12px;
            padding: 12px 16px;
        }
        .form-control:focus, .form-select:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(26,94,42,0.1);
        }
        .is-invalid { border-color: #dc3545; }
        .invalid-feedback { font-size: 0.75rem; margin-top: 5px; color: #dc3545; }
        .btn-primary-custom {
            background: var(--gradient);
            border: none;
            padding: 12px;
            border-radius: 12px;
            font-weight: 600;
        }
        .btn-primary-custom:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(26,94,42,0.3); }
        .required:after { content: " *"; color: #dc3545; }
    </style>
</head>
<body>
    <div class="container">
        <div class="form-card">
            <div class="form-header">
                <h2><i class="fas fa-tag me-2"></i>Ajouter une catégorie</h2>
                <p class="mb-0">Créez une nouvelle catégorie d'événements</p>
            </div>
            <div class="form-body">
                <?php if (isset($errors['global'])): ?>
                    <div class="alert alert-danger"><?php echo $errors['global']; ?></div>
                <?php endif; ?>
                
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label required">Nom de la catégorie</label>
                        <input type="text" name="nom" class="form-control <?php echo isset($errors['nom']) ? 'is-invalid' : ''; ?>" 
                               value="<?php echo htmlspecialchars($old['nom'] ?? ''); ?>">
                        <?php if (isset($errors['nom'])): ?>
                            <div class="invalid-feedback"><?php echo $errors['nom']; ?></div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label required">Description</label>
                        <textarea name="description" rows="4" class="form-control <?php echo isset($errors['description']) ? 'is-invalid' : ''; ?>"><?php echo htmlspecialchars($old['description'] ?? ''); ?></textarea>
                        <?php if (isset($errors['description'])): ?>
                            <div class="invalid-feedback"><?php echo $errors['description']; ?></div>
                        <?php endif; ?>
                        <small class="text-muted">Description détaillée de la catégorie</small>
                    </div>
                    
                    <div class="mb-4">
                        <label class="form-label">URL de l'image</label>
                        <input type="text" name="image_url" class="form-control <?php echo isset($errors['image_url']) ? 'is-invalid' : ''; ?>" 
                               value="<?php echo htmlspecialchars($old['image_url'] ?? ''); ?>" placeholder="https://exemple.com/image.jpg">
                        <?php if (isset($errors['image_url'])): ?>
                            <div class="invalid-feedback"><?php echo $errors['image_url']; ?></div>
                        <?php endif; ?>
                        <small class="text-muted">Optionnel. Formats acceptés : JPG, PNG, GIF, WEBP</small>
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