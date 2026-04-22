<?php
session_start();
require_once __DIR__ . '/../../../controller/CategorieEvenementC.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../../../index.php');
    exit();
}

$errors = [];
$formData = [];
$uploadError = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $formData['nom'] = trim($_POST['nom'] ?? '');
    $formData['description'] = trim($_POST['description'] ?? '');
    
    // Validation
    if (empty($formData['nom'])) {
        $errors['nom'] = 'Le nom est obligatoire';
    } elseif (strlen($formData['nom']) < 2) {
        $errors['nom'] = 'Le nom doit contenir au moins 2 caractères';
    }
    
    if (empty($formData['description'])) {
        $errors['description'] = 'La description est obligatoire';
    } elseif (strlen($formData['description']) < 10) {
        $errors['description'] = 'La description doit contenir au moins 10 caractères';
    }
    
    // Gestion de l'upload d'image
    $image_url = null;
    
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = __DIR__ . '/../../../uploads/categories/';
        
        // Créer le dossier s'il n'existe pas
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        $fileInfo = pathinfo($_FILES['image']['name']);
        $extension = strtolower($fileInfo['extension']);
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        
        if (!in_array($extension, $allowedExtensions)) {
            $errors['image'] = 'Format non supporté. Utilisez JPG, PNG, GIF ou WEBP';
        } elseif ($_FILES['image']['size'] > 5 * 1024 * 1024) {
            $errors['image'] = 'L\'image ne doit pas dépasser 5Mo';
        } else {
            // Générer un nom unique
            $newFileName = uniqid('categorie_') . '.' . $extension;
            $uploadPath = $uploadDir . $newFileName;
            
            if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadPath)) {
                $image_url = 'uploads/categories/' . $newFileName;
            } else {
                $errors['image'] = 'Erreur lors de l\'upload de l\'image';
            }
        }
    }
    
    if (empty($errors)) {
        require_once __DIR__ . '/../../../model/CategorieEvenement.php';
        $categorie = new CategorieEvenement($formData['nom'], $formData['description'], $image_url);
        
        $categorieC = new CategorieEvenementC();
        if ($categorieC->ajouterCategorie($categorie)) {
            header('Location: liste.php?success=1');
            exit();
        } else {
            $errors['global'] = 'Erreur lors de l\'ajout de la catégorie';
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
    <style>
        :root {
            --primary-green: #2e7d32;
            --secondary-green: #4caf50;
            --light-green: #e8f5e9;
        }
        body {
            background: linear-gradient(135deg, var(--primary-green), var(--secondary-green));
            min-height: 100vh;
            padding: 50px 0;
            font-family: 'Segoe UI', sans-serif;
        }
        .form-container {
            background: white;
            border-radius: 24px;
            padding: 40px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.2);
            max-width: 700px;
            margin: 0 auto;
        }
        .form-container h2 {
            color: var(--primary-green);
            margin-bottom: 30px;
            text-align: center;
            font-weight: bold;
        }
        .form-label {
            font-weight: 600;
            color: #2d3748;
        }
        .required:after {
            content: " *";
            color: #dc2626;
        }
        .btn-submit {
            background: linear-gradient(135deg, var(--primary-green), var(--secondary-green));
            border: none;
            padding: 12px;
            font-weight: 600;
            transition: all 0.3s;
        }
        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(46,125,50,0.4);
        }
        .image-preview {
            margin-top: 15px;
            text-align: center;
        }
        .image-preview img {
            max-width: 200px;
            max-height: 150px;
            border-radius: 12px;
            border: 2px solid var(--secondary-green);
            padding: 5px;
            background: var(--light-green);
        }
        .btn-remove-image {
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="form-container">
            <h2>
                <i class="fas fa-tag me-2" style="color: var(--primary-green);"></i>
                Ajouter une catégorie
            </h2>
            
            <?php if (isset($errors['global'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i><?php echo $errors['global']; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php endif; ?>
            
            <form method="POST" enctype="multipart/form-data">
                <div class="mb-3">
                    <label class="form-label required">Nom de la catégorie</label>
                    <input type="text" name="nom" class="form-control <?php echo isset($errors['nom']) ? 'is-invalid' : ''; ?>" 
                           value="<?php echo htmlspecialchars($formData['nom'] ?? ''); ?>" placeholder="Ex: Culture, Sport, Environnement...">
                    <?php if (isset($errors['nom'])): ?>
                    <div class="invalid-feedback"><?php echo $errors['nom']; ?></div>
                    <?php endif; ?>
                </div>
                
                <div class="mb-3">
                    <label class="form-label required">Description</label>
                    <textarea name="description" class="form-control <?php echo isset($errors['description']) ? 'is-invalid' : ''; ?>" 
                              rows="4" placeholder="Décrivez cette catégorie..."><?php echo htmlspecialchars($formData['description'] ?? ''); ?></textarea>
                    <?php if (isset($errors['description'])): ?>
                    <div class="invalid-feedback"><?php echo $errors['description']; ?></div>
                    <?php endif; ?>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Image de la catégorie</label>
                    <input type="file" name="image" id="imageInput" class="form-control <?php echo isset($errors['image']) ? 'is-invalid' : ''; ?>" 
                           accept="image/jpeg,image/png,image/gif,image/webp">
                    <?php if (isset($errors['image'])): ?>
                    <div class="invalid-feedback"><?php echo $errors['image']; ?></div>
                    <?php endif; ?>
                    <small class="text-muted">
                        <i class="fas fa-info-circle me-1"></i> 
                        Formats acceptés : JPG, PNG, GIF, WEBP. Taille max : 5Mo
                    </small>
                    
                    <!-- Aperçu de l'image -->
                    <div class="image-preview" id="imagePreview" style="display: none;">
                        <img id="previewImg" src="#" alt="Aperçu">
                        <br>
                        <button type="button" class="btn btn-sm btn-danger btn-remove-image" onclick="removeImage()">
                            <i class="fas fa-trash me-1"></i> Supprimer l'image
                        </button>
                    </div>
                </div>
                
                <div class="d-flex gap-3 mt-4">
                    <a href="liste.php" class="btn btn-secondary flex-grow-1">
                        <i class="fas fa-arrow-left me-2"></i>Annuler
                    </a>
                    <button type="submit" class="btn btn-primary flex-grow-1 btn-submit">
                        <i class="fas fa-save me-2"></i>Ajouter la catégorie
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Aperçu de l'image avant upload
        document.getElementById('imageInput').addEventListener('change', function(e) {
            const file = e.target.files[0];
            const preview = document.getElementById('imagePreview');
            const previewImg = document.getElementById('previewImg');
            
            if (file) {
                const reader = new FileReader();
                reader.onload = function(event) {
                    previewImg.src = event.target.result;
                    preview.style.display = 'block';
                }
                reader.readAsDataURL(file);
            } else {
                preview.style.display = 'none';
            }
        });
        
        function removeImage() {
            document.getElementById('imageInput').value = '';
            document.getElementById('imagePreview').style.display = 'none';
        }
    </script>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>