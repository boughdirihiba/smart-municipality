<?php
session_start();
require_once __DIR__ . '/../../../controller/CategorieEvenementC.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../../../index.php');
    exit();
}

$id = $_GET['id'] ?? 0;
$categorieC = new CategorieEvenementC();
$categorie = $categorieC->afficherCategorieParId($id);

if (!$categorie) {
    header('Location: liste.php');
    exit();
}

$errors = [];
$formData = [
    'nom' => $categorie['nom'],
    'description' => $categorie['description'],
    'image_url' => $categorie['image_url']
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $formData['nom'] = trim($_POST['nom'] ?? '');
    $formData['description'] = trim($_POST['description'] ?? '');
    
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
    $image_url = $formData['image_url']; // Garder l'ancienne image par défaut
    
    // Vérifier si l'utilisateur veut supprimer l'image
    if (isset($_POST['remove_image']) && $_POST['remove_image'] == '1') {
        // Supprimer l'ancien fichier
        if ($image_url && file_exists('../../../' . $image_url)) {
            unlink('../../../' . $image_url);
        }
        $image_url = null;
    }
    
    // Vérifier si une nouvelle image est uploadée
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = __DIR__ . '/../../../uploads/categories/';
        
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        $fileInfo = pathinfo($_FILES['image']['name']);
        $extension = strtolower($fileInfo['extension']);
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        
        if (!in_array($extension, $allowedExtensions)) {
            $errors['image'] = 'Format non supporté';
        } elseif ($_FILES['image']['size'] > 5 * 1024 * 1024) {
            $errors['image'] = 'L\'image ne doit pas dépasser 5Mo';
        } else {
            // Supprimer l'ancienne image si elle existe
            if ($image_url && file_exists('../../../' . $image_url)) {
                unlink('../../../' . $image_url);
            }
            
            $newFileName = uniqid('categorie_') . '.' . $extension;
            $uploadPath = $uploadDir . $newFileName;
            
            if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadPath)) {
                $image_url = 'uploads/categories/' . $newFileName;
            } else {
                $errors['image'] = 'Erreur lors de l\'upload';
            }
        }
    }
    
    if (empty($errors)) {
        require_once __DIR__ . '/../../../model/CategorieEvenement.php';
        $categorieObj = new CategorieEvenement($formData['nom'], $formData['description'], $image_url);
        
        if ($categorieC->modifierCategorie($categorieObj, $id)) {
            header('Location: liste.php?success=1');
            exit();
        } else {
            $errors['global'] = 'Erreur lors de la modification';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier une catégorie - Smart Municipality</title>
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
        }
        .current-image {
            text-align: center;
            margin-bottom: 20px;
        }
        .current-image img {
            max-width: 150px;
            max-height: 150px;
            border-radius: 12px;
            border: 3px solid var(--secondary-green);
            padding: 5px;
            background: var(--light-green);
        }
        .btn-submit {
            background: linear-gradient(135deg, #f59e0b, #d97706);
            border: none;
            padding: 12px;
            font-weight: 600;
        }
        .image-preview {
            margin-top: 15px;
            text-align: center;
        }
        .image-preview img {
            max-width: 200px;
            max-height: 150px;
            border-radius: 12px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="form-container">
            <h2>
                <i class="fas fa-edit me-2" style="color: #f59e0b;"></i>
                Modifier la catégorie
            </h2>
            
            <?php if (isset($errors['global'])): ?>
            <div class="alert alert-danger"><?php echo $errors['global']; ?></div>
            <?php endif; ?>
            
            <!-- Image actuelle -->
            <?php if($formData['image_url'] && file_exists('../../../' . $formData['image_url'])): ?>
            <div class="current-image">
                <label class="form-label">Image actuelle</label>
                <div>
                    <img src="../../../<?php echo $formData['image_url']; ?>" alt="Image actuelle">
                </div>
                <div class="form-check mt-2">
                    <input class="form-check-input" type="checkbox" name="remove_image" value="1" id="removeImage">
                    <label class="form-check-label text-danger" for="removeImage">
                        <i class="fas fa-trash me-1"></i> Supprimer cette image
                    </label>
                </div>
            </div>
            <?php endif; ?>
            
            <form method="POST" enctype="multipart/form-data">
                <div class="mb-3">
                    <label class="form-label">Nom</label>
                    <input type="text" name="nom" class="form-control <?php echo isset($errors['nom']) ? 'is-invalid' : ''; ?>" 
                           value="<?php echo htmlspecialchars($formData['nom']); ?>">
                    <?php if (isset($errors['nom'])): ?>
                    <div class="invalid-feedback"><?php echo $errors['nom']; ?></div>
                    <?php endif; ?>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control <?php echo isset($errors['description']) ? 'is-invalid' : ''; ?>" 
                              rows="4"><?php echo htmlspecialchars($formData['description']); ?></textarea>
                    <?php if (isset($errors['description'])): ?>
                    <div class="invalid-feedback"><?php echo $errors['description']; ?></div>
                    <?php endif; ?>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Nouvelle image</label>
                    <input type="file" name="image" id="imageInput" class="form-control <?php echo isset($errors['image']) ? 'is-invalid' : ''; ?>" 
                           accept="image/jpeg,image/png,image/gif,image/webp">
                    <?php if (isset($errors['image'])): ?>
                    <div class="invalid-feedback"><?php echo $errors['image']; ?></div>
                    <?php endif; ?>
                    <small class="text-muted">Laissez vide pour garder l'image actuelle</small>
                    
                    <div class="image-preview" id="imagePreview" style="display: none;">
                        <img id="previewImg" src="#" alt="Aperçu">
                    </div>
                </div>
                
                <div class="d-flex gap-3">
                    <a href="liste.php" class="btn btn-secondary flex-grow-1">Annuler</a>
                    <button type="submit" class="btn btn-warning flex-grow-1 btn-submit">
                        <i class="fas fa-save me-2"></i>Modifier
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
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
    </script>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>