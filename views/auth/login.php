<?php
session_start();
require_once __DIR__ . '/../../config.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = md5($_POST['password']);
    
    $db = config::getConnexion();
    $query = $db->prepare('SELECT * FROM users WHERE email = :email AND mot_de_passe = :password');
    $query->execute(['email' => $email, 'password' => $password]);
    $user = $query->fetch();
    
    if ($user) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['nom'] = $user['nom'];
        $_SESSION['prenom'] = $user['prenom'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['role'] = $user['role'];
        header('Location: ../../index.php');
        exit();
    } else {
        $error = 'Email ou mot de passe incorrect';
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Connexion - Smart Municipality</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-dark: #1a5e2a;
            --primary-dark-hover: #0d3b1a;
            --secondary-dark: #2e7d32;
            --bg-dark: #e8f3e8;
        }
        body { 
            background: linear-gradient(135deg, var(--primary-dark), var(--secondary-dark)); 
            min-height: 100vh; 
            font-family: 'Segoe UI', sans-serif;
        }
        .login-container { 
            max-width: 400px; 
            margin: 100px auto; 
        }
        .card { 
            border-radius: 24px; 
            box-shadow: 0 20px 60px rgba(0,0,0,0.2); 
            border: none;
        }
        .btn-dark-green { 
            background: var(--primary-dark); 
            color: white; 
            border: none;
            padding: 12px;
            font-weight: 600;
            transition: all 0.3s;
        }
        .btn-dark-green:hover { 
            background: var(--primary-dark-hover); 
            transform: translateY(-2px);
        }
        .form-control:focus {
            border-color: var(--primary-dark);
            box-shadow: 0 0 0 0.2rem rgba(26, 94, 42, 0.25);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="login-container">
            <div class="card">
                <div class="card-body p-4">
 <!-- Dans le formulaire de connexion -->
                    <div class="text-center mb-4">
                        <img src="../../logo.jpeg" alt="Smart Municipality" height="60" style="border-radius: 15px; margin-bottom: 10px;">
                        <h3 class="mt-2" style="color: var(--primary-dark);">Smart Municipality</h3>
                        <p class="text-muted">Connectez-vous à votre compte</p>
                    </div>
                    <?php if($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>
                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Mot de passe</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>
                        <button type="submit" class="btn btn-dark-green w-100">
                            <i class="fas fa-sign-in-alt me-2"></i>Se connecter
                        </button>
                    </form>
                    <hr class="my-4">
                    <p class="text-center mb-0">
                        <a href="register.php" style="color: var(--primary-dark);">Créer un compte</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>