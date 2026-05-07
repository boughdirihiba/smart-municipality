<?php
session_start();
require_once __DIR__ . '/../../controller/EvenementC.php';
require_once __DIR__ . '/../../controller/CategorieEvenementC.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../../index.php');
    exit();
}

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id <= 0) {
    header('Location: liste.php');
    exit();
}

$evenementC = new EvenementC();
$categorieC = new CategorieEvenementC();

$evenement = $evenementC->afficherEvenementParId($id);
if (!$evenement) {
    header('Location: liste.php');
    exit();
}

$categories = $categorieC->afficherCategories();
$errors = [];
$old = [
    'titre' => $evenement['titre'],
    'description' => $evenement['description'],
    'max_participants' => $evenement['max_participants'],
    'lieu' => $evenement['lieu'],
    'date_evenement' => $evenement['date_evenement'],
    'heure' => $evenement['heure'],
    'categorie_id' => $evenement['categorie_id']
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $old['titre'] = trim($_POST['titre'] ?? '');
    $old['description'] = trim($_POST['description'] ?? '');
    $old['max_participants'] = intval($_POST['max_participants'] ?? 50);
    $old['lieu'] = trim($_POST['lieu'] ?? '');
    $old['date_evenement'] = $_POST['date_evenement'] ?? '';
    $old['heure'] = $_POST['heure'] ?? '';
    $old['categorie_id'] = intval($_POST['categorie_id'] ?? 0);
    
    // Validations
    if (empty($old['titre'])) {
        $errors['titre'] = 'Le titre est obligatoire';
    } elseif (strlen($old['titre']) < 3) {
        $errors['titre'] = 'Le titre doit contenir au moins 3 caractères';
    }
    
    if (empty($old['description'])) {
        $errors['description'] = 'La description est obligatoire';
    } elseif (strlen($old['description']) < 10) {
        $errors['description'] = 'La description doit contenir au moins 10 caractères';
    }
    
    if ($old['max_participants'] < 1) {
        $errors['max_participants'] = 'Le nombre de participants doit être au moins 1';
    }
    
    if (empty($old['lieu'])) {
        $errors['lieu'] = 'Le lieu est obligatoire';
    }
    
    if (empty($old['date_evenement'])) {
        $errors['date_evenement'] = 'La date est obligatoire';
    } else {
        $date = DateTime::createFromFormat('Y-m-d', $old['date_evenement']);
        if (!$date || $date->format('Y-m-d') !== $old['date_evenement']) {
            $errors['date_evenement'] = 'Format de date invalide';
        }
    }
    
    if (empty($old['heure'])) {
        $errors['heure'] = 'L\'heure est obligatoire';
    } elseif (!preg_match('/^([0-1][0-9]|2[0-3]):[0-5][0-9]$/', $old['heure'])) {
        $errors['heure'] = 'Format d\'heure invalide (HH:MM)';
    }
    
    if ($old['categorie_id'] <= 0) {
        $errors['categorie_id'] = 'Veuillez sélectionner une catégorie';
    }
    
    if (empty($errors)) {
        require_once __DIR__ . '/../../model/Evenement.php';
        
        // CORRECTION ICI : Ajouter tous les paramètres requis par le constructeur
        $evenementObj = new Evenement(
            $old['titre'],
            $old['description'],
            $old['lieu'],
            $old['date_evenement'],
            $old['heure'],
            $old['categorie_id'],
            $old['max_participants']
        );
        
        if ($evenementC->modifierEvenement($evenementObj, $id)) {
            header('Location: liste.php?success=1');
            exit();
        } else {
            $errors['global'] = 'Erreur lors de la modification';
        }
    }
}

$displayName = $_SESSION['prenom'] . ' ' . $_SESSION['nom'];
$userRole = $_SESSION['role'];
$avatarName = 'sidebar-photo.svg';
$currentRoute = 'evenements';
$baseUrl = '../../';
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier un événement - Smart Municipality</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../public/css/admin-sidebar.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Inter', sans-serif;
            background: #e8f5e9;
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
        .sidebar-logo p { font-size: 0.7rem; opacity: 0.7; margin-top: 5px; }
        .sidebar-user { display: flex; align-items: center; gap: 12px; padding: 20px; background: rgba(255,255,255,0.05); margin: 15px; border-radius: 12px; }
        .sidebar-user img { width: 45px; height: 45px; border-radius: 50%; object-fit: cover; border: 2px solid rgba(255,255,255,0.3); }
        .sidebar-user strong { display: block; font-size: 0.85rem; }
        .sidebar-user span { font-size: 0.7rem; opacity: 0.7; }
        .sidebar-nav { flex: 1; padding: 10px 0; }
        .sidebar-link {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 25px;
            color: rgba(255,255,255,0.85);
            text-decoration: none;
            transition: all 0.3s;
            margin: 5px 10px;
            border-radius: 12px;
            font-weight: 500;
            font-size: 0.85rem;
        }
        .sidebar-link .icon { font-size: 1.1rem; width: 24px; text-align: center; }
        .sidebar-link .label { flex: 1; }
        .sidebar-link:hover, .sidebar-link.active { background: rgba(255,255,255,0.15); color: white; transform: translateX(5px); }
        .sidebar-footer-links { display: flex; justify-content: space-around; padding: 15px; border-top: 1px solid rgba(255,255,255,0.1); }
        .sidebar-footer-links .sidebar-link { padding: 8px; margin: 0; }
        .sidebar-toggle { position: absolute; top: 20px; right: -12px; background: #1a5e2a; border: none; color: white; width: 24px; height: 24px; border-radius: 50%; cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: 0.7rem; }
        
        .main-content { flex: 1; padding: 25px; }
        .form-container { max-width: 800px; margin: 0 auto; background: white; border-radius: 20px; overflow: hidden; box-shadow: 0 5px 20px rgba(0,0,0,0.05); }
        .form-header { background: linear-gradient(135deg, #1a5e2a, #4caf50); padding: 20px; color: white; text-align: center; }
        .form-header h2 { margin: 0; font-size: 1.3rem; }
        .form-header p { margin: 5px 0 0; opacity: 0.9; font-size: 0.8rem; }
        .form-body { padding: 25px; }
        .form-label { font-weight: 600; color: #1a5e2a; font-size: 0.85rem; margin-bottom: 8px; display: block; }
        .form-control, .form-select { width: 100%; border: 2px solid #e9ecef; border-radius: 10px; padding: 10px 15px; font-size: 0.85rem; }
        .form-control:focus, .form-select:focus { border-color: #1a5e2a; box-shadow: 0 0 0 3px rgba(26,94,42,0.1); outline: none; }
        .is-invalid { border-color: #dc3545; }
        .invalid-feedback { color: #dc3545; font-size: 0.7rem; margin-top: 5px; }
        .btn-primary { background: linear-gradient(135deg, #1a5e2a, #4caf50); border: none; padding: 10px 20px; border-radius: 10px; font-weight: 600; color: white; width: 100%; }
        .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(26,94,42,0.3); }
        .btn-secondary { background: #6c757d; border: none; padding: 10px 20px; border-radius: 10px; font-weight: 600; color: white; text-decoration: none; display: inline-block; text-align: center; }
        .required:after { content: " *"; color: #dc3545; }
        @media (max-width: 992px) {
            .sidebar { width: 80px; }
            .sidebar-logo h2, .sidebar-logo p, .sidebar-user div, .sidebar-link .label { display: none; }
            .sidebar-user { justify-content: center; }
            .sidebar-link { justify-content: center; padding: 12px; }
            .sidebar-footer-links .sidebar-link { padding: 5px; }
        }
    </style>
</head>
<body>
    <?php require_once __DIR__ . '/../partials/admin_sidebar.php'; ?>

    <div class="main-content">
        <div class="form-container">
            <div class="form-header">
                <h2><i class="fas fa-edit me-2"></i>Modifier l'événement</h2>
                <p>Modifiez les informations de l'événement</p>
            </div>
            <div class="form-body">
                <?php if (isset($errors['global'])): ?>
                    <div class="alert alert-danger"><?php echo $errors['global']; ?></div>
                <?php endif; ?>
                
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label required">Titre</label>
                        <input type="text" name="titre" class="form-control <?php echo isset($errors['titre']) ? 'is-invalid' : ''; ?>" 
                               value="<?php echo htmlspecialchars($old['titre']); ?>">
                        <?php if (isset($errors['titre'])): ?>
                            <div class="invalid-feedback"><?php echo $errors['titre']; ?></div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label required">Description</label>
                        <textarea name="description" rows="4" class="form-control <?php echo isset($errors['description']) ? 'is-invalid' : ''; ?>"><?php echo htmlspecialchars($old['description']); ?></textarea>
                        <?php if (isset($errors['description'])): ?>
                            <div class="invalid-feedback"><?php echo $errors['description']; ?></div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label required">Participants max</label>
                            <input type="number" name="max_participants" class="form-control <?php echo isset($errors['max_participants']) ? 'is-invalid' : ''; ?>" 
                                   value="<?php echo htmlspecialchars($old['max_participants']); ?>">
                            <?php if (isset($errors['max_participants'])): ?>
                                <div class="invalid-feedback"><?php echo $errors['max_participants']; ?></div>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label required">Lieu</label>
                            <input type="text" name="lieu" class="form-control <?php echo isset($errors['lieu']) ? 'is-invalid' : ''; ?>" 
                                   value="<?php echo htmlspecialchars($old['lieu']); ?>">
                            <?php if (isset($errors['lieu'])): ?>
                                <div class="invalid-feedback"><?php echo $errors['lieu']; ?></div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label required">Date</label>
                            <input type="date" name="date_evenement" class="form-control <?php echo isset($errors['date_evenement']) ? 'is-invalid' : ''; ?>" 
                                   value="<?php echo htmlspecialchars($old['date_evenement']); ?>">
                            <?php if (isset($errors['date_evenement'])): ?>
                                <div class="invalid-feedback"><?php echo $errors['date_evenement']; ?></div>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label required">Heure</label>
                            <input type="time" name="heure" class="form-control <?php echo isset($errors['heure']) ? 'is-invalid' : ''; ?>" 
                                   value="<?php echo htmlspecialchars($old['heure']); ?>">
                            <?php if (isset($errors['heure'])): ?>
                                <div class="invalid-feedback"><?php echo $errors['heure']; ?></div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <label class="form-label required">Catégorie</label>
                        <select name="categorie_id" class="form-select <?php echo isset($errors['categorie_id']) ? 'is-invalid' : ''; ?>">
                            <option value="">-- Sélectionner une catégorie --</option>
                            <?php foreach($categories as $cat): ?>
                            <option value="<?php echo $cat['id']; ?>" <?php echo $old['categorie_id'] == $cat['id'] ? 'selected' : ''; ?>>
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
                        <button type="submit" class="btn btn-primary flex-grow-1"><i class="fas fa-save me-2"></i>Modifier</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        const sidebar = document.querySelector('.sidebar');
        const toggleBtn = document.getElementById('sidebarToggle');
        if (toggleBtn) {
            toggleBtn.addEventListener('click', function() {
                sidebar.classList.toggle('collapsed');
                this.textContent = sidebar.classList.contains('collapsed') ? '❯' : '❮';
            });
        }
    </script>
</body>
</html>