<?php
session_start();
require_once __DIR__ . '/../../config.php';

$error = '';

// Vérifier si l'utilisateur est déjà connecté
if (isset($_SESSION['user_id'])) {
    header('Location: ../../index.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    // Validation
    if (empty($email)) {
        $error = 'Veuillez saisir votre email';
    } elseif (empty($password)) {
        $error = 'Veuillez saisir votre mot de passe';
    } else {
        $db = config::getConnexion();
        
        // Vérifier si l'utilisateur existe
        $query = $db->prepare('SELECT * FROM users WHERE email = :email');
        $query->execute(['email' => $email]);
        $user = $query->fetch(PDO::FETCH_ASSOC);
        
        if ($user) {
            // Vérifier le mot de passe (password_hash ou MD5)
            $passwordValid = false;
            
            if (password_verify($password, $user['mot_de_passe'])) {
                $passwordValid = true;
            } elseif (md5($password) === $user['mot_de_passe']) {
                $passwordValid = true;
                // Mettre à jour vers password_hash
                $update = $db->prepare('UPDATE users SET mot_de_passe = :new_password WHERE id = :id');
                $update->execute([
                    'new_password' => password_hash($password, PASSWORD_DEFAULT),
                    'id' => $user['id']
                ]);
            }
            
            if ($passwordValid) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['nom'] = $user['nom'];
                $_SESSION['prenom'] = $user['prenom'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['telephone'] = $user['telephone'] ?? '';
                $_SESSION['adresse'] = $user['adresse'] ?? '';
                
                // Redirection selon le rôle
                if ($user['role'] === 'admin') {
                    header('Location: ../../views/dashboard/admin.php');
                } else {
                    header('Location: ../../index.php');
                }
                exit();
            } else {
                $error = 'Mot de passe incorrect';
            }
        } else {
            $error = 'Aucun compte trouvé avec cet email';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - Smart Municipality</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #1a5e2a;
            --primary-dark: #0d3b1a;
            --primary-light: #2e7d32;
            --secondary: #4caf50;
            --gradient: linear-gradient(135deg, #1a5e2a, #4caf50);
            --gradient-hover: linear-gradient(135deg, #0d3b1a, #2e7d32);
            --shadow: 0 10px 25px rgba(0,0,0,0.08);
            --shadow-lg: 0 20px 40px rgba(0,0,0,0.12);
            --radius: 16px;
            --radius-lg: 24px;
        }
        
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #e8f5e9 0%, #f0f4f0 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .login-container {
            max-width: 450px;
            width: 100%;
            margin: 0 auto;
        }
        
        .login-card {
            background: white;
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-lg);
            overflow: hidden;
            animation: fadeInUp 0.5s ease;
        }
        
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .login-header {
            background: var(--gradient);
            padding: 35px 30px;
            text-align: center;
            color: white;
            position: relative;
            overflow: hidden;
        }
        
        .login-header::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
            pointer-events: none;
        }
        
        .login-header img {
            border-radius: 20px;
            margin-bottom: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
            position: relative;
            z-index: 1;
        }
        
        .login-header h2 {
            font-weight: 700;
            font-size: 1.8rem;
            margin-bottom: 5px;
            position: relative;
            z-index: 1;
        }
        
        .login-header p {
            opacity: 0.9;
            font-size: 0.9rem;
            position: relative;
            z-index: 1;
        }
        
        .login-body {
            padding: 40px;
        }
        
        .form-group {
            margin-bottom: 25px;
        }
        
        .form-label {
            font-weight: 600;
            color: var(--primary-dark);
            margin-bottom: 8px;
            display: block;
            font-size: 0.85rem;
        }
        
        .input-group {
            border-radius: 12px;
            overflow: hidden;
            transition: all 0.3s ease;
            border: 2px solid #e2e8f0;
            background: white;
        }
        
        .input-group:focus-within {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(26,94,42,0.15);
        }
        
        .input-group-text {
            background: white;
            border: none;
            color: var(--primary);
            padding: 12px 16px;
        }
        
        .form-control {
            border: none;
            padding: 12px 16px 12px 0;
            font-size: 0.95rem;
            transition: all 0.3s ease;
            background: white;
        }
        
        .form-control:focus {
            box-shadow: none;
            outline: none;
        }
        
        .btn-login {
            background: var(--gradient);
            border: none;
            padding: 14px;
            font-weight: 600;
            font-size: 1rem;
            border-radius: 12px;
            width: 100%;
            transition: all 0.3s ease;
            color: white;
            cursor: pointer;
            margin-top: 10px;
        }
        
        .btn-login:hover {
            background: var(--gradient-hover);
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(26,94,42,0.3);
        }
        
        .btn-login:active {
            transform: translateY(0);
        }
        
        .alert-danger {
            background: linear-gradient(135deg, #fee2e2, #ffebeb);
            border: none;
            color: #991b1b;
            border-radius: 12px;
            padding: 12px 16px;
            margin-bottom: 25px;
            font-size: 0.85rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .forgot-password {
            text-align: right;
            margin-top: 10px;
        }
        
        .forgot-password a {
            color: #888;
            text-decoration: none;
            font-size: 0.75rem;
            transition: all 0.3s;
        }
        
        .forgot-password a:hover {
            color: var(--primary);
        }
        
        .register-link {
            text-align: center;
            margin-top: 25px;
            padding-top: 20px;
            border-top: 1px solid #e2e8f0;
        }
        
        .register-link p {
            margin: 0;
            font-size: 0.85rem;
            color: #666;
        }
        
        .register-link a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .register-link a:hover {
            text-decoration: underline;
        }
        
        .btn-show-password {
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .btn-show-password:hover {
            color: var(--primary);
        }
        
        @media (max-width: 480px) {
            .login-body {
                padding: 25px;
            }
            .login-header {
                padding: 25px 20px;
            }
            .login-header h2 {
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <img src="../../logo.jpeg" alt="Smart Municipality" height="60">
                <h2><i class="fas fa-city me-2"></i>Smart Municipality</h2>
                <p>Plateforme citoyenne intelligente</p>
            </div>
            <div class="login-body">
                <?php if ($error): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle"></i>
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>

                <form method="POST" id="loginForm">
                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-envelope me-1" style="color: var(--primary);"></i> Adresse email
                        </label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                            <input type="email" name="email" class="form-control" 
                                   placeholder="exemple@email.com" 
                                   value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                                   required autofocus>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-lock me-1" style="color: var(--primary);"></i> Mot de passe
                        </label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-lock"></i></span>
                            <input type="password" name="password" id="password" class="form-control" 
                                   placeholder="••••••••" required>
                            <span class="input-group-text btn-show-password" onclick="togglePassword()">
                                <i class="fas fa-eye" id="eyeIcon"></i>
                            </span>
                        </div>
                        <div class="forgot-password">
                            <a href="forgot_password.php"><i class="fas fa-question-circle me-1"></i>Mot de passe oublié ?</a>
                        </div>
                    </div>

                    <button type="submit" class="btn-login" id="loginBtn">
                        <i class="fas fa-sign-in-alt me-2"></i>Se connecter
                    </button>
                </form>

                <div class="register-link">
                    <p><i class="fas fa-user-plus me-1"></i> Pas encore de compte ? <a href="register.php">Créer un compte</a></p>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Animation de chargement lors de la soumission
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            const btn = document.getElementById('loginBtn');
            btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Connexion en cours...';
            btn.disabled = true;
        });

        // Afficher/masquer le mot de passe
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const eyeIcon = document.getElementById('eyeIcon');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                eyeIcon.classList.remove('fa-eye');
                eyeIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                eyeIcon.classList.remove('fa-eye-slash');
                eyeIcon.classList.add('fa-eye');
            }
        }
    </script>
</body>
</html>