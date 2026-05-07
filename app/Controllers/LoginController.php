<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\User;

class LoginController extends Controller
{
    private User $user;

    public function __construct()
    {
        parent::__construct();
        $this->user = new User();
    }

    /**
     * Show login form
     */
    public function index()
    {
        // If already logged in, redirect to home
        if (isset($_SESSION['user'])) {
            header('Location: ' . $GLOBALS['baseUrl'] . '?route=home/index');
            exit;
        }

        $this->render('auth/login', [
            'pageTitle' => 'Connexion'
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
            header('Location: ' . $GLOBALS['baseUrl'] . '?route=home/index');
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
            // Find user by email
            $userData = $this->user->findByEmail($email);

            if ($userData) {
                // Verify password
                if ($this->user->verifyPassword($password, $userData['mot_de_passe'])) {
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

                    // Update MD5 password to bcrypt if needed
                    if (!password_needs_rehash($userData['mot_de_passe'], PASSWORD_DEFAULT)) {
                        // Already using bcrypt, no need to update
                    } else {
                        // Update to bcrypt
                        $updateStmt = $GLOBALS['pdo']->prepare('UPDATE users SET mot_de_passe = :pwd WHERE id = :id');
                        $updateStmt->execute([
                            ':pwd' => password_hash($password, PASSWORD_DEFAULT),
                            ':id' => $userData['id']
                        ]);
                    }

                    // Redirect based on role
                    if ($userData['role'] === 'admin') {
                        header('Location: ' . $GLOBALS['baseUrl'] . '?route=admin/list');
                    } else {
                        header('Location: ' . $GLOBALS['baseUrl'] . '?route=home/index');
                    }
                    exit;
                } else {
                    $error = 'Mot de passe incorrect';
                }
            } else {
                $error = 'Aucun compte trouvé avec cet email';
            }
        }

        // Re-render with error
        $_SESSION['error'] = $error;
        header('Location: ' . $GLOBALS['baseUrl'] . '?route=login/index');
        exit;
    }

    /**
     * Logout
     */
    public function logout()
    {
        session_destroy();
        header('Location: ' . $GLOBALS['baseUrl'] . '?route=login/index');
        exit;
    }
}
?>
