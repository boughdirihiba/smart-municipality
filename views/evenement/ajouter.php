<?php
session_start();
require_once __DIR__ . '/../../controller/EvenementC.php';
require_once __DIR__ . '/../../controller/CategorieEvenementC.php';

// Vérifier si l'utilisateur est admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../../index.php');
    exit();
}

$categorieC = new CategorieEvenementC();
$categories = $categorieC->afficherCategories();

$errors = [];
$formData = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupération et nettoyage des données
    $formData['titre'] = trim($_POST['titre'] ?? '');
    $formData['description'] = trim($_POST['description'] ?? '');
    $formData['lieu'] = trim($_POST['lieu'] ?? '');
    $formData['date_evenement'] = $_POST['date_evenement'] ?? '';
    $formData['heure'] = $_POST['heure'] ?? '';
    $formData['categorie_id'] = $_POST['categorie_id'] ?? '';
    
    // Validation
    if (empty($formData['titre'])) {
        $errors['titre'] = 'Le titre est obligatoire';
    } elseif (strlen($formData['titre']) < 3) {
        $errors['titre'] = 'Le titre doit contenir au moins 3 caractères';
    }
    
    if (empty($formData['description'])) {
        $errors['description'] = 'La description est obligatoire';
    } elseif (strlen($formData['description']) < 10) {
        $errors['description'] = 'La description doit contenir au moins 10 caractères';
    }
    
    if (empty($formData['lieu'])) {
        $errors['lieu'] = 'Le lieu est obligatoire';
    }
    
    if (empty($formData['date_evenement'])) {
        $errors['date_evenement'] = 'La date est obligatoire';
    } elseif (strtotime($formData['date_evenement']) < strtotime(date('Y-m-d'))) {
        $errors['date_evenement'] = 'La date doit être dans le futur';
    }
    
    if (empty($formData['heure'])) {
        $errors['heure'] = 'L\'heure est obligatoire';
    }
    
    if (empty($formData['categorie_id'])) {
        $errors['categorie_id'] = 'Veuillez sélectionner une catégorie';
    }
    
    if (empty($errors)) {
        require_once __DIR__ . '/../../model/Evenement.php';
        $evenement = new Evenement(
            $formData['titre'],
            $formData['description'],
            $formData['lieu'],
            $formData['date_evenement'],
            $formData['heure'],
            $formData['categorie_id']
        );
        
        $evenementC = new EvenementC();
        if ($evenementC->ajouterEvenement($evenement)) {
            header('Location: ../../index.php?success=ajout');
            exit();
        } else {
            $errors['global'] = 'Erreur lors de l\'ajout de l\'événement';
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
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 50px 0;
        }
        .form-container {
            background: white;
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.2);
            max-width: 700px;
            margin: 0 auto;
        }
        .form-container h2 {
            color: #2d3748;
            margin-bottom: 30px;
            text-align: center;
        }
        .form-label {
            font-weight: 600;
            color: #4a5568;
        }
        .required:after {
            content: " *";
            color: red;
        }
        .btn-submit {
            background: linear-gradient(135deg, #667eea, #764ba2);
            border: none;
            padding: 12px;
            font-weight: 600;
        }
        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102,126,234,0.4);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="form-container">
            <h2><i class="fas fa-plus-circle me-2 text-primary"></i>Ajouter un événement</h2>
            
            <?php if (isset($errors['global'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i><?php echo $errors['global']; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="mb-3">
                    <label class="form-label required">Titre</label>
                    <input type="text" name="titre" class="form-control <?php echo isset($errors['titre']) ? 'is-invalid' : ''; ?>" 
                           value="<?php echo htmlspecialchars($formData['titre'] ?? ''); ?>">
                    <?php if (isset($errors['titre'])): ?>
                    <div class="invalid-feedback"><?php echo $errors['titre']; ?></div>
                    <?php endif; ?>
                </div>
                
                <div class="mb-3">
                    <label class="form-label required">Description</label>
                    <textarea name="description" class="form-control <?php echo isset($errors['description']) ? 'is-invalid' : ''; ?>" 
                              rows="4"><?php echo htmlspecialchars($formData['description'] ?? ''); ?></textarea>
                    <?php if (isset($errors['description'])): ?>
                    <div class="invalid-feedback"><?php echo $errors['description']; ?></div>
                    <?php endif; ?>
                </div>
                
                <div class="mb-3">
                    <label class="form-label required">Lieu</label>
                    <input type="text" name="lieu" class="form-control <?php echo isset($errors['lieu']) ? 'is-invalid' : ''; ?>" 
                           value="<?php echo htmlspecialchars($formData['lieu'] ?? ''); ?>">
                    <?php if (isset($errors['lieu'])): ?>
                    <div class="invalid-feedback"><?php echo $errors['lieu']; ?></div>
                    <?php endif; ?>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label required">Date</label>
                        <input type="date" name="date_evenement" class="form-control <?php echo isset($errors['date_evenement']) ? 'is-invalid' : ''; ?>" 
                               value="<?php echo htmlspecialchars($formData['date_evenement'] ?? ''); ?>">
                        <?php if (isset($errors['date_evenement'])): ?>
                        <div class="invalid-feedback"><?php echo $errors['date_evenement']; ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label required">Heure</label>
                        <input type="time" name="heure" class="form-control <?php echo isset($errors['heure']) ? 'is-invalid' : ''; ?>" 
                               value="<?php echo htmlspecialchars($formData['heure'] ?? ''); ?>">
                        <?php if (isset($errors['heure'])): ?>
                        <div class="invalid-feedback"><?php echo $errors['heure']; ?></div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="mb-4">
                    <label class="form-label required">Catégorie</label>
                    <select name="categorie_id" class="form-select <?php echo isset($errors['categorie_id']) ? 'is-invalid' : ''; ?>">
                        <option value="">Sélectionner une catégorie</option>
                        <?php foreach($categories as $cat): ?>
                        <option value="<?php echo $cat['id']; ?>" <?php echo (($formData['categorie_id'] ?? '') == $cat['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($cat['nom']); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                    <?php if (isset($errors['categorie_id'])): ?>
                    <div class="invalid-feedback"><?php echo $errors['categorie_id']; ?></div>
                    <?php endif; ?>
                </div>
                
                <div class="d-flex gap-3">
                    <a href="../../index.php" class="btn btn-secondary flex-grow-1">
                        <i class="fas fa-arrow-left me-2"></i>Annuler
                    </a>
                    <button type="submit" class="btn btn-primary flex-grow-1 btn-submit">
                        <i class="fas fa-save me-2"></i>Ajouter
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>