<?php
session_start();
require_once __DIR__ . '/../../../controllers/CategorieEvenementC.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../../../index.php');
    exit();
}

$errors = [];
$old = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $old['nom'] = trim($_POST['nom'] ?? '');
    $old['description'] = trim($_POST['description'] ?? '');
    
    // Validation du nom
    if (empty($old['nom'])) {
        $errors['nom'] = 'Le nom est obligatoire';
    } elseif (strlen($old['nom']) < 2) {
        $errors['nom'] = 'Le nom doit contenir au moins 2 caractères';
    } elseif (strlen($old['nom']) > 100) {
        $errors['nom'] = 'Le nom ne peut pas dépasser 100 caractères';
    }
    
    // Validation de la description
    if (empty($old['description'])) {
        $errors['description'] = 'La description est obligatoire';
    } elseif (strlen($old['description']) < 10) {
        $errors['description'] = 'La description doit contenir au moins 10 caractères';
    } elseif (strlen($old['description']) > 500) {
        $errors['description'] = 'La description ne peut pas dépasser 500 caractères';
    }
    
    // Upload de l'image
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
        require_once __DIR__ . '/../../../models/CategorieEvenement.php';
        $categorie = new CategorieEvenement();
        $categorie->setNom($old['nom']);
        $categorie->setDescription($old['description']);
        $categorie->setImageUrl($image_url);
        
        $categorieC = new CategorieEvenementC();
        if ($categorieC->ajouterCategorie($categorie)) {
            header('Location: liste.php?success=1');
            exit();
        } else {
            $errors['global'] = 'Erreur lors de l\'ajout en base de données';
        }
    }
}

$displayName = $_SESSION['prenom'] . ' ' . $_SESSION['nom'];
$userRole = $_SESSION['role'];
$avatarName = 'sidebar-photo.svg';
$currentRoute = 'categories';
$baseUrl = '../../../';
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter une catégorie - Smart Municipality</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/../../../public/css/admin-sidebar.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Inter', sans-serif;
            background: #f0f4f0;
            display: flex;
        }
        .sidebar {
            width: 280px;
            background: linear-gradient(180deg, #0d3b1a 0%, #1a5e2a 100%);
            color: white;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            position: sticky;
            top: 0;
        }
        .sidebar-logo { padding: 25px; text-align: center; border-bottom: 1px solid rgba(255,255,255,0.1); }
        .sidebar-logo img { width: 60px; height: 60px; border-radius: 15px; margin-bottom: 10px; }
        .sidebar-logo h2 { font-size: 1.2rem; font-weight: 600; }
        .sidebar-user { display: flex; align-items: center; gap: 12px; padding: 20px; background: rgba(255,255,255,0.05); margin: 15px; border-radius: 12px; }
        .sidebar-user img { width: 45px; height: 45px; border-radius: 50%; }
        .sidebar-user strong { display: block; font-size: 0.85rem; }
        .sidebar-user span { font-size: 0.7rem; opacity: 0.7; }
        .sidebar-nav { flex: 1; padding: 10px 0; }
        .sidebar-link {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 20px;
            color: rgba(255,255,255,0.85);
            text-decoration: none;
            transition: all 0.3s;
            margin: 5px 10px;
            border-radius: 12px;
            font-weight: 500;
            font-size: 0.85rem;
        }
        .sidebar-link:hover, .sidebar-link.active { background: rgba(255,255,255,0.15); color: white; transform: translateX(5px); }
        .sidebar-link .icon { font-size: 1.1rem; width: 24px; text-align: center; }
        .sidebar-footer { padding: 15px; border-top: 1px solid rgba(255,255,255,0.1); display: flex; justify-content: space-around; }
        .main-content { flex: 1; padding: 25px; }
        
        .form-container {
            max-width: 700px;
            margin: 0 auto;
            background: white;
            border-radius: 20px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.05);
            overflow: hidden;
        }
        .form-header {
            background: linear-gradient(135deg, #1a5e2a, #4caf50);
            padding: 20px;
            color: white;
            text-align: center;
        }
        .form-header h2 { margin: 0; font-size: 1.3rem; }
        .form-header p { margin: 5px 0 0; opacity: 0.9; font-size: 0.75rem; }
        .form-body { padding: 25px; }
        .form-label { font-weight: 600; color: #1a5e2a; font-size: 0.85rem; margin-bottom: 8px; display: block; }
        .form-control, .form-select {
            width: 100%;
            border: 2px solid #e9ecef;
            border-radius: 10px;
            padding: 10px 15px;
            font-size: 0.85rem;
            transition: all 0.2s;
        }
        .form-control:focus, .form-select:focus {
            border-color: #1a5e2a;
            box-shadow: 0 0 0 3px rgba(26,94,42,0.1);
            outline: none;
        }
        .is-invalid { border-color: #dc3545; }
        .invalid-feedback { color: #dc3545; font-size: 0.7rem; margin-top: 5px; }
        .btn-primary {
            background: linear-gradient(135deg, #1a5e2a, #4caf50);
            border: none;
            padding: 10px 20px;
            border-radius: 10px;
            font-weight: 600;
            color: white;
            width: 100%;
        }
        .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(26,94,42,0.3); }
        .btn-secondary {
            background: #6c757d;
            border: none;
            padding: 10px 20px;
            border-radius: 10px;
            font-weight: 600;
            color: white;
            text-decoration: none;
            display: inline-block;
            text-align: center;
        }
        .image-preview {
            margin-top: 15px;
            text-align: center;
        }
        .image-preview img {
            max-width: 150px;
            max-height: 120px;
            border-radius: 10px;
            border: 2px solid #e8f5e9;
            padding: 5px;
        }
        @media (max-width: 992px) {
            .sidebar { width: 80px; }
            .sidebar-logo h2, .sidebar-logo p, .sidebar-user div, .sidebar-link .label { display: none; }
            .sidebar-user { justify-content: center; }
            .sidebar-link { justify-content: center; padding: 12px; }
            .main-content { padding: 15px; }
            .form-container { margin: 0 10px; }
        }
    </style>
</head>
<body>
    <?php require_once __DIR__ . '/../../partials/admin_sidebar.php'; ?>

    <div class="main-content">
        <div class="form-container">
            <div class="form-header">
                <h2><i class="fas fa-tag me-2"></i>Ajouter une catégorie</h2>
                <p>Créez une nouvelle catégorie d'événements</p>
            </div>
            <div class="form-body">
                <?php if (isset($errors['global'])): ?>
                    <div class="alert alert-danger"><?php echo $errors['global']; ?></div>
                <?php endif; ?>
                
                <form method="POST" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label class="form-label">Nom de la catégorie *</label>
                        <input type="text" name="nom" class="form-control <?php echo isset($errors['nom']) ? 'is-invalid' : ''; ?>" 
                               value="<?php echo htmlspecialchars($old['nom'] ?? ''); ?>">
                        <?php if (isset($errors['nom'])): ?>
                            <div class="invalid-feedback"><?php echo $errors['nom']; ?></div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Description *</label>
                        <textarea name="description" rows="4" class="form-control <?php echo isset($errors['description']) ? 'is-invalid' : ''; ?>"
                                  placeholder="Décrivez cette catégorie..."><?php echo htmlspecialchars($old['description'] ?? ''); ?></textarea>
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
                        <small class="text-muted">Formats acceptés : JPG, PNG, GIF, WEBP. Taille max : 5Mo</small>
                        
                        <div class="image-preview" id="imagePreview" style="display: none;">
                            <img id="previewImg" src="#" alt="Aperçu">
                            <br>
                            <button type="button" class="btn btn-sm btn-danger mt-2" onclick="removeImage()">
                                <i class="fas fa-trash me-1"></i> Supprimer
                            </button>
                        </div>
                    </div>
                    
                    <div class="d-flex gap-3 mt-4">
                        <a href="<?php echo BASE_URL; ?>/liste.php" class="btn btn-secondary flex-grow-1"><i class="fas fa-arrow-left me-2"></i>Annuler</a>
                        <button type="submit" class="btn btn-primary flex-grow-1"><i class="fas fa-save me-2"></i>Ajouter</button>
                    </div>
                </form>
            </div>
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
</body>
</html>
