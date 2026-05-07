<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\User;

class LoginController extends Controller
{
    private ?User $user = null;

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Lazy load User model
     */
    private function getUser(): User
    {
        if ($this->user === null) {
            $this->user = new User();
        }
        return $this->user;
    }

    /**
     * Show login form
     */
    public function index()
    {
        // If already logged in, redirect to home
        if (isset($_SESSION['user'])) {
            header('Location: ' . BASE_URL . '/index.php?route=home/index');
            exit;
        }

        $error = $_SESSION['error'] ?? null;
        unset($_SESSION['error']);

        $this->render('auth/login', [
            'pageTitle' => 'Connexion',
            'error' => $error
        ]);
    }

    /**
     * Handle login form submission
     */
    public function login()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo 'Method not allowed.';
            return;
        }

        // If already logged in, redirect
        if (isset($_SESSION['user'])) {
            header('Location: ' . BASE_URL . '/index.php?route=home/index');
            exit;
        }

        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $error = null;

        // Validation
        if (empty($email)) {
            $error = 'Veuillez saisir votre email';
        } elseif (empty($password)) {
            $error = 'Veuillez saisir votre mot de passe';
        } else {
            try {
                // Find user by email
                $userData = $this->getUser()->findByEmail($email);

                if ($userData) {
                    // Verify password
                    if ($this->getUser()->verifyPassword($password, $userData['mot_de_passe'])) {
                        // Set session
                        $_SESSION['user'] = [
                            'id' => $userData['id'],
                            'nom' => $userData['nom'],
                            'prenom' => $userData['prenom'],
                            'email' => $userData['email'],
                            'role' => $userData['role'],
                            'telephone' => $userData['telephone'] ?? '',
                            'adresse' => $userData['adresse'] ?? '',
                            'avatar' => $userData['avatar'] ?? 'sidebar-photo.svg'
                        ];

                        // Redirect based on role
                        if ($userData['role'] === 'admin') {
                            header('Location: ' . BASE_URL . '/index.php?route=admin/list');
                        } else {
                            header('Location: ' . BASE_URL . '/index.php?route=home/index');
                        }
                        exit;
                    } else {
                        $error = 'Mot de passe incorrect';
                    }
                } else {
                    $error = 'Aucun compte trouvé avec cet email';
                }
            } catch (\Exception $e) {
                $error = 'Erreur de connexion. Veuillez réessayer.';
            }
        }

        // Re-render with error
        $_SESSION['error'] = $error;
        header('Location: ' . BASE_URL . '/index.php?route=login/index');
        exit;
    }

    /**
     * Logout
     */
    public function logout()
    {
        // Clear session data
        $_SESSION = [];
        
        // Destroy session
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }
        
        // Delete session cookie
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params["path"],
                $params["domain"],
                $params["secure"],
                $params["httponly"]
            );
        }
        
        header('Location: ' . BASE_URL . '/index.php?route=login/index');
        exit;
    }
}
