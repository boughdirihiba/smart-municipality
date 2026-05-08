<?php
// Modern login view
$error = $_SESSION['error'] ?? null;
if ($error) {
    unset($_SESSION['error']);
}
?>

<div class="auth-container">
    <div class="auth-wrapper">
        <div class="auth-card">
            <div class="auth-header">
                <img src="<?php echo BASE_URL; ?>/public/uploads/sidebar-photo.svg" alt="Logo" class="auth-logo">
                <h1>Smart Municipality</h1>
                <p>Connectez-vous à votre compte</p>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <strong>Erreur:</strong> <?php echo htmlspecialchars($error); ?>
                    <button type="button" class="btn-close"></button>
                </div>
            <?php endif; ?>

            <form method="POST" action="<?php echo BASE_URL; ?>/index.php?route=login/login" class="auth-form">
                <div class="form-group">
                    <label for="email" class="form-label">Email</label>
                    <input 
                        type="email" 
                        class="form-control" 
                        id="email" 
                        name="email" 
                        placeholder="votre.email@example.com"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="password" class="form-label">Mot de passe</label>
                    <input 
                        type="password" 
                        class="form-control" 
                        id="password" 
                        name="password" 
                        placeholder="••••••••"
                        required
                    >
                </div>

                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" id="remember" name="remember">
                    <label class="form-check-label" for="remember">
                        Se souvenir de moi
                    </label>
                </div>

                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-sign-in-alt"></i> Se connecter
                </button>
            </form>

            <div class="auth-footer">
                <p>Vous n'avez pas de compte? <a href="#signup">S'inscrire</a></p>
            </div>
        </div>
    </div>
</div>

<style>
.auth-container {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, #e8f5e9 0%, #f0f4f0 100%);
    padding: 20px;
}

.auth-wrapper {
    width: 100%;
    max-width: 420px;
}

.auth-card {
    background: white;
    border-radius: 16px;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
    padding: 40px;
}

.auth-header {
    text-align: center;
    margin-bottom: 30px;
}

.auth-logo {
    width: 80px;
    height: 80px;
    margin-bottom: 15px;
    border-radius: 12px;
}

.auth-header h1 {
    font-size: 28px;
    font-weight: 700;
    color: #1a5e2a;
    margin-bottom: 8px;
}

.auth-header p {
    color: #666;
    font-size: 14px;
}

.form-group {
    margin-bottom: 20px;
}

.form-label {
    display: block;
    font-weight: 600;
    color: #333;
    margin-bottom: 8px;
    font-size: 14px;
}

.form-control {
    width: 100%;
    padding: 12px 14px;
    border: 2px solid #e0e0e0;
    border-radius: 8px;
    font-size: 14px;
    transition: border-color 0.3s;
}

.form-control:focus {
    outline: none;
    border-color: #1a5e2a;
    box-shadow: 0 0 0 3px rgba(26, 94, 42, 0.1);
}

.btn {
    padding: 12px 20px;
    border: none;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    font-size: 14px;
    transition: all 0.3s;
}

.btn-primary {
    background: linear-gradient(135deg, #1a5e2a, #2e7d32);
    color: white;
}

.btn-primary:hover {
    background: linear-gradient(135deg, #0d3b1a, #1a5e2a);
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(26, 94, 42, 0.3);
}

.alert {
    padding: 12px 16px;
    border-radius: 8px;
    margin-bottom: 20px;
    font-size: 14px;
}

.alert-danger {
    background-color: #ffebee;
    color: #c62828;
    border: 1px solid #ef5350;
}

.auth-footer {
    text-align: center;
    margin-top: 25px;
    padding-top: 20px;
    border-top: 1px solid #e0e0e0;
}

.auth-footer p {
    color: #666;
    font-size: 14px;
}

.auth-footer a {
    color: #1a5e2a;
    text-decoration: none;
    font-weight: 600;
}

.auth-footer a:hover {
    text-decoration: underline;
}

@media (max-width: 480px) {
    .auth-card {
        padding: 25px;
    }
    
    .auth-header h1 {
        font-size: 24px;
    }
}
</style>
