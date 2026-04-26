<?php

declare(strict_types=1);

namespace Controles;

use Config\Auth;
use Config\Database;
use Config\Flash;
use Config\Validation;
use Config\View;
use Models\PdoUserRepository;
use Models\User;
use Models\UserRepository;
use PDO;
use Throwable;

final class AuthController
{
    private ?PDO $pdo = null;
    private ?UserRepository $repo = null;

    public function __construct()
    {
    }

    private function repo(): UserRepository
    {
        if ($this->repo instanceof UserRepository) {
            return $this->repo;
        }

        $this->pdo = (new Database())->getConnection();
        $this->repo = new PdoUserRepository($this->pdo);
        return $this->repo;
    }

    public function showLogin(): void
    {
        $flash = $this->consumeFlash();
        View::render('pages.php', ['page' => 'login', 'flash' => $flash]);
    }

    public function showSignup(): void
    {
        $flash = $this->consumeFlash();
        View::render('pages.php', ['page' => 'signup', 'flash' => $flash]);
    }

    public function login(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo 'Method Not Allowed';
            return;
        }

        $mail = trim((string)($_POST['mail'] ?? ''));
        $motdepasse = (string)($_POST['motdepasse'] ?? '');

        $errors = Validation::login($mail, $motdepasse);

        if ($errors !== []) {
            $this->setFlashErrors($errors, ['mail' => $mail]);
            $this->redirect('index.php?route=login');
            return;
        }

        try {
            $repo = new PdoUserRepository((new Database())->getConnection());
            $user = $repo->findByMail($mail);
            if (!$user || $user->getMdp() === '') {
                $this->setFlashErrors(['motdepasse' => 'Email ou mot de passe incorrect.'], ['mail' => $mail]);
                $this->redirect('index.php?route=login');
                return;
            }

            $storedPassword = $user->getMdp();

            // If the DB schema truncates hashes (e.g., mdp VARCHAR(20/50)), login will always fail.
            // Bcrypt hashes are typically 60 chars; Argon2 can be longer.
            $looksHashed = str_starts_with($storedPassword, '$2y$')
                || str_starts_with($storedPassword, '$2a$')
                || str_starts_with($storedPassword, '$argon2');
            if ($looksHashed && strlen($storedPassword) < 55) {
                $this->setFlashErrors([
                    'motdepasse' => "Compte non connectable: le hash du mot de passe a été tronqué (ancien schéma BD). Mets 'utilisateur.mdp' en VARCHAR(255) puis recrée le compte ou réinitialise le mot de passe.",
                ], ['mail' => $mail]);
                $this->redirect('index.php?route=login');
                return;
            }

            $isOk = password_verify($motdepasse, $storedPassword);

            // Compat: si mdp est encore en clair, on upgrade.
            if (!$isOk && hash_equals($storedPassword, $motdepasse)) {
                $isOk = true;
                $repo->updatePassword($user->getId(), password_hash($motdepasse, PASSWORD_DEFAULT));
            }

            if (!$isOk) {
                $this->setFlashErrors(['motdepasse' => 'Email ou mot de passe incorrect.'], ['mail' => $mail]);
                $this->redirect('index.php?route=login');
                return;
            }

            Auth::login($user);
            if (Auth::isAdmin()) {
                $this->redirect('index.php?route=dashboard');
            }
            $this->redirect('index.php?route=profile');
        } catch (Throwable $e) {
            error_log('[AuthController::login] ' . get_class($e) . ': ' . $e->getMessage());

            $this->setFlashErrors(['motdepasse' => 'Une erreur est survenue. Réessaie plus tard.'], ['mail' => $mail]);
            $this->redirect('index.php?route=login');
        }
    }

    public function signup(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo 'Method Not Allowed';
            return;
        }

        $prenom = trim((string)($_POST['prenom'] ?? ''));
        $nom = trim((string)($_POST['nom'] ?? ''));
        $mail = trim((string)($_POST['email'] ?? ''));
        $motdepasse = (string)($_POST['motdepasse'] ?? '');
        $confirm = (string)($_POST['confirmMotdepasse'] ?? '');

        $old = [
            'prenom' => $prenom,
            'nom' => $nom,
            'email' => $mail,
        ];

        $errors = Validation::signup($prenom, $nom, $mail, $motdepasse, $confirm);

        if ($errors !== []) {
            $this->setFlashErrors($errors, $old);
            $this->redirect('index.php?route=signup');
            return;
        }

        try {
            $repo = new PdoUserRepository((new Database())->getConnection());
            if ($repo->mailExists($mail)) {
                $this->setFlashErrors(['email' => 'Cet email est déjà utilisé.'], $old);
                $this->redirect('index.php?route=signup');
                return;
            }

            $hash = password_hash($motdepasse, PASSWORD_DEFAULT);
            $repo->createUser($nom, $prenom, $mail, $hash);

            // Safety: if the DB truncates the hash, we block completion and show a clear fix.
            $created = $repo->findByMail($mail);
            if ($created && $created->getMdp() !== '' && !hash_equals($created->getMdp(), $hash)) {
                $this->setFlashErrors([
                    'motdepasse' => "Configuration BD: le hash du mot de passe est tronqué. Modifie 'utilisateur.mdp' en VARCHAR(255).",
                ], $old);
                $this->redirect('index.php?route=signup');
                return;
            }

            $this->redirect('index.php?route=login');
        } catch (Throwable $e) {
            error_log('[AuthController::signup] ' . get_class($e) . ': ' . $e->getMessage());

            $this->setFlashErrors(['email' => 'Une erreur est survenue. Réessaie plus tard.'], $old);
            $this->redirect('index.php?route=signup');
        }
    }

    public function logout(): void
    {
        Auth::logout();
        $this->redirect('index.php?route=login');
    }

    private function redirect(string $to): void
    {
        header('Location: ' . $to);
        exit;
    }

    /**
     * @param array<string, string> $errors
     * @param array<string, mixed> $old
     */
    private function setFlashErrors(array $errors, array $old = []): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        $_SESSION['_flash'] = [
            'errors' => $errors,
            'old' => $old,
        ];
    }

    /**
     * @return array{errors?:array<string,string>, old?:array<string,mixed>}|null
     */
    private function consumeFlash(): ?array
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            @session_start();
        }

        $flash = $_SESSION['_flash'] ?? null;
        unset($_SESSION['_flash']);

        return is_array($flash) ? $flash : null;
    }
}

final class AdminUsersController
{
    public function index(): void
    {
        Auth::requireAdmin();
        header('Location: index.php?route=dashboard');
        exit;
    }

    public function create(): void
    {
        Auth::requireAdmin();

        if (strtoupper((string)($_SERVER['REQUEST_METHOD'] ?? 'GET')) !== 'POST') {
            http_response_code(405);
            echo 'Method Not Allowed';
            return;
        }

        $prenom = trim((string)($_POST['prenom'] ?? ''));
        $nom = trim((string)($_POST['nom'] ?? ''));
        $mail = trim((string)($_POST['mail'] ?? ''));
        $telephone = trim((string)($_POST['telephone'] ?? ''));
        $password = (string)($_POST['password'] ?? '');
        $confirm = (string)($_POST['confirm_password'] ?? '');

        $old = [
            'prenom' => $prenom,
            'nom' => $nom,
            'mail' => $mail,
            'telephone' => $telephone,
        ];

        $errors = Validation::adminUserCreate($prenom, $nom, $mail, $telephone, $password, $confirm);

        if ($errors !== []) {
            Flash::setErrors($errors, $old);
            header('Location: index.php?route=dashboard#members');
            exit;
        }

        try {
            $repo = new PdoUserRepository((new Database())->getConnection());
            if ($repo->mailExists($mail)) {
                Flash::setErrors(['mail' => 'Cet email est déjà utilisé.'], $old);
                header('Location: index.php?route=dashboard#members');
                exit;
            }

            $hash = password_hash($password, PASSWORD_DEFAULT);
            $id = $repo->createUser($nom, $prenom, $mail, $hash);

            $repo->updateUser($id, [
                'nom' => $nom,
                'prenom' => $prenom,
                'mail' => $mail,
                'telephone' => $telephone,
            ]);

            Flash::success('Membre créé avec succès.');
            header('Location: index.php?route=dashboard#members');
            exit;
        } catch (Throwable $e) {
            Flash::setErrors(['mail' => 'Une erreur est survenue. Réessaie plus tard.'], $old);
            header('Location: index.php?route=dashboard#members');
            exit;
        }
    }

    public function update(): void
    {
        Auth::requireAdmin();

        if (strtoupper((string)($_SERVER['REQUEST_METHOD'] ?? 'GET')) !== 'POST') {
            http_response_code(405);
            echo 'Method Not Allowed';
            return;
        }

        $id = (int)($_GET['id'] ?? 0);
        if ($id <= 0) {
            Flash::setErrors(['id' => 'ID invalide.']);
            header('Location: index.php?route=dashboard#members');
            exit;
        }

        $prenom = trim((string)($_POST['prenom'] ?? ''));
        $nom = trim((string)($_POST['nom'] ?? ''));
        $mail = trim((string)($_POST['mail'] ?? ''));
        $telephone = trim((string)($_POST['telephone'] ?? ''));
        $password = (string)($_POST['password'] ?? '');
        $confirm = (string)($_POST['confirm_password'] ?? '');

        $errors = Validation::adminUserUpdate($prenom, $nom, $mail, $telephone, $password, $confirm);

        if ($errors !== []) {
            Flash::setErrors($errors, [
                'prenom' => $prenom,
                'nom' => $nom,
                'mail' => $mail,
                'telephone' => $telephone,
            ]);
            header('Location: index.php?route=dashboard&edit=' . $id . '#members');
            exit;
        }

        try {
            $repo = new PdoUserRepository((new Database())->getConnection());

            if ($repo->mailExistsForOtherId($mail, $id)) {
                Flash::setErrors(['mail' => 'Cet email est déjà utilisé.'], [
                    'prenom' => $prenom,
                    'nom' => $nom,
                    'mail' => $mail,
                    'telephone' => $telephone,
                ]);
                header('Location: index.php?route=dashboard&edit=' . $id . '#members');
                exit;
            }

            $repo->updateUser($id, [
                'nom' => $nom,
                'prenom' => $prenom,
                'mail' => $mail,
                'telephone' => $telephone,
            ]);

            if ($password !== '') {
                $repo->updatePassword($id, password_hash($password, PASSWORD_DEFAULT));
            }

            Flash::success('Membre mis à jour.');
            header('Location: index.php?route=dashboard#members');
            exit;
        } catch (Throwable $e) {
            Flash::setErrors(['mail' => 'Une erreur est survenue. Réessaie plus tard.']);
            header('Location: index.php?route=dashboard&edit=' . $id . '#members');
            exit;
        }
    }

    public function delete(): void
    {
        Auth::requireAdmin();

        if (strtoupper((string)($_SERVER['REQUEST_METHOD'] ?? 'GET')) !== 'POST') {
            http_response_code(405);
            echo 'Method Not Allowed';
            return;
        }

        $id = (int)($_GET['id'] ?? 0);
        if ($id <= 0) {
            Flash::setErrors(['id' => 'ID invalide.']);
            header('Location: index.php?route=dashboard#members');
            exit;
        }

        if ($id === Auth::id()) {
            Flash::setErrors(['id' => 'Impossible de supprimer votre propre compte admin.']);
            header('Location: index.php?route=dashboard#members');
            exit;
        }

        try {
            $repo = new PdoUserRepository((new Database())->getConnection());
            $repo->deleteUser($id);
            Flash::success('Membre supprimé.');
            header('Location: index.php?route=dashboard#members');
            exit;
        } catch (Throwable $e) {
            Flash::setErrors(['id' => 'Une erreur est survenue.']);
            header('Location: index.php?route=dashboard#members');
            exit;
        }
    }
}

final class DashboardController
{
    public function dashboard(): void
    {
        Auth::requireAdmin();

        $flash = Flash::consume();

        $repo = new PdoUserRepository((new Database())->getConnection());
        $activeUsers = $repo->countUsers();

        $users = $repo->listUsers(200, 0);
        $editId = (int)($_GET['edit'] ?? 0);
        $editUser = null;
        if ($editId > 0) {
            foreach ($users as $u) {
                if ((int)($u['id'] ?? 0) === $editId) {
                    $editUser = $u;
                    break;
                }
            }
        }

        View::render('layout/admin.php', [
            'title' => 'Dashboard',
            'active' => 'dashboard',
            'contentView' => 'dashboard.php',
            'flash' => $flash,
            'stats' => [
                'total_posts' => 24,
                'active_users' => $activeUsers,
                'comments' => 71,
                'reactions' => 312,
            ],
            'users' => $users,
            'editUser' => $editUser,
        ]);
    }

    public function section(string $key): void
    {
        Auth::requireAdmin();

        $titles = [
            'blog' => 'Blog',
            'signalement' => 'Signalement',
            'events' => 'Événements',
            'map' => 'Carte intelligente',
            'services' => 'Services en ligne',
            'rdv' => 'Rendez-vous',
        ];

        $title = $titles[$key] ?? 'Section';

        View::render('layout/admin.php', [
            'title' => $title,
            'active' => 'admin-' . $key,
            'contentView' => 'admin_section.php',
            'sectionTitle' => $title,
        ]);
    }
}

final class ProfileController
{
    public function show(): void
    {
        Auth::requireLogin();
        $flash = Flash::consume();

        $repo = new PdoUserRepository((new Database())->getConnection());
        $user = $repo->findById(Auth::id());

        if (!$user) {
            Auth::logout();
            header('Location: index.php?route=login');
            exit;
        }

        View::render('layout/app.php', [
            'title' => 'Profil',
            'active' => 'profile',
            'contentView' => 'site.php',
            'page' => 'profile',
            'flash' => $flash,
            'user' => $user,
        ]);
    }

    public function update(): void
    {
        Auth::requireLogin();

        if (strtoupper((string)($_SERVER['REQUEST_METHOD'] ?? 'GET')) !== 'POST') {
            http_response_code(405);
            echo 'Method Not Allowed';
            return;
        }

        $action = (string)($_POST['action'] ?? 'info');

        if ($action === 'password') {
            $this->changePassword();
            return;
        }

        if ($action === 'settings') {
            $this->saveSettings();
            return;
        }

        $this->saveInfo();
    }

    private function saveInfo(): void
    {
        $prenom = trim((string)($_POST['prenom'] ?? ''));
        $nom = trim((string)($_POST['nom'] ?? ''));
        $mail = trim((string)($_POST['mail'] ?? ''));
        $telephone = trim((string)($_POST['telephone'] ?? ''));

        $errors = Validation::profileInfo($prenom, $nom, $mail, $telephone);

        if ($errors !== []) {
            Flash::setErrors($errors, [
                'prenom' => $prenom,
                'nom' => $nom,
                'mail' => $mail,
                'telephone' => $telephone,
            ]);
            header('Location: index.php?route=profile');
            exit;
        }

        try {
            $repo = new PdoUserRepository((new Database())->getConnection());
            $repo->updateProfile(Auth::id(), [
                'prenom' => $prenom,
                'nom' => $nom,
                'mail' => $mail,
                'telephone' => $telephone,
            ]);

            Auth::startSession();
            $_SESSION['user']['prenom'] = $prenom;
            $_SESSION['user']['nom'] = $nom;
            $_SESSION['user']['mail'] = $mail;
            $_SESSION['user']['telephone'] = $telephone;

            Flash::success('Profil mis à jour.');
            header('Location: index.php?route=profile');
            exit;
        } catch (Throwable $e) {
            Flash::setErrors(['mail' => 'Une erreur est survenue. Réessaie plus tard.'], [
                'prenom' => $prenom,
                'nom' => $nom,
                'mail' => $mail,
                'telephone' => $telephone,
            ]);
            header('Location: index.php?route=profile');
            exit;
        }
    }

    private function changePassword(): void
    {
        $current = (string)($_POST['current_password'] ?? '');
        $new = (string)($_POST['new_password'] ?? '');
        $confirm = (string)($_POST['confirm_password'] ?? '');

        $errors = Validation::profilePassword($current, $new, $confirm);

        if ($errors !== []) {
            Flash::setErrors($errors);
            header('Location: index.php?route=profile');
            exit;
        }

        try {
            $repo = new PdoUserRepository((new Database())->getConnection());
            $user = $repo->findById(Auth::id());

            if (!$user || !password_verify($current, $user->getMdp())) {
                Flash::setErrors(['current_password' => 'Mot de passe actuel incorrect.']);
                header('Location: index.php?route=profile');
                exit;
            }

            $repo->updatePassword($user->getId(), password_hash($new, PASSWORD_DEFAULT));
            Flash::success('Mot de passe mis à jour.');
            header('Location: index.php?route=profile');
            exit;
        } catch (Throwable $e) {
            Flash::setErrors(['new_password' => 'Une erreur est survenue. Réessaie plus tard.']);
            header('Location: index.php?route=profile');
            exit;
        }
    }

    private function saveSettings(): void
    {
        Auth::startSession();

        $notifications = isset($_POST['notifications']);
        $darkMode = isset($_POST['dark_mode']);

        $_SESSION['settings'] = [
            'notifications' => $notifications,
            'dark_mode' => $darkMode,
        ];

        Flash::success('Paramètres enregistrés.');
        header('Location: index.php?route=profile');
        exit;
    }
}

final class PublicController
{
    public function events(): void
    {
        Auth::requireLogin();
        View::render('layout/app.php', [
            'title' => 'Événements',
            'active' => 'events',
            'contentView' => 'site.php',
            'page' => 'events',
        ]);
    }

    public function map(): void
    {
        Auth::requireLogin();
        View::render('layout/app.php', [
            'title' => 'Carte',
            'active' => 'map',
            'contentView' => 'site.php',
            'page' => 'map',
        ]);
    }

    public function blog(): void
    {
        Auth::requireLogin();
        View::render('layout/app.php', [
            'title' => 'Blog',
            'active' => 'blog',
            'contentView' => 'site.php',
            'page' => 'blog',
        ]);
    }

    public function services(): void
    {
        Auth::requireLogin();
        View::render('layout/app.php', [
            'title' => 'Services',
            'active' => 'services',
            'contentView' => 'site.php',
            'page' => 'services',
        ]);
    }

    public function rdv(): void
    {
        Auth::requireLogin();
        View::render('layout/app.php', [
            'title' => 'Rendez-vous',
            'active' => 'rdv',
            'contentView' => 'site.php',
            'page' => 'rdv',
        ]);
    }
}

final class PdoUserRepositoryController
{
    public static function findByMail(PdoUserRepository $repo, string $mail): ?User
    {
        $cols = self::selectColumns($repo);
        $stmt = $repo->getPdo()->prepare('SELECT ' . $cols . ' FROM utilisateur WHERE mail = :mail LIMIT 1');
        $stmt->execute(['mail' => $mail]);
        $row = $stmt->fetch();
        return self::hydrate($row);
    }

    public static function findById(PdoUserRepository $repo, int $id): ?User
    {
        $cols = self::selectColumns($repo);
        $stmt = $repo->getPdo()->prepare('SELECT ' . $cols . ' FROM utilisateur WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return self::hydrate($row);
    }

    public static function countUsers(PdoUserRepository $repo): int
    {
        $stmt = $repo->getPdo()->query('SELECT COUNT(*) AS c FROM utilisateur');
        $row = $stmt->fetch();
        return (int)($row['c'] ?? 0);
    }

    /** @return array<int, array{id:int, nom:string, prenom:string, mail:string, telephone?:string}> */
    public static function listUsers(PdoUserRepository $repo, int $limit = 200, int $offset = 0): array
    {
        $limit = max(1, min(500, $limit));
        $offset = max(0, $offset);

        $hasTelephone = self::hasTelephone($repo);
        $cols = $hasTelephone ? 'id, nom, prenom, mail, telephone' : 'id, nom, prenom, mail';

        $sql = 'SELECT ' . $cols . ' FROM utilisateur ORDER BY id DESC LIMIT :limit OFFSET :offset';
        $stmt = $repo->getPdo()->prepare($sql);
        $stmt->bindValue('limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue('offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        $rows = $stmt->fetchAll();
        if (!is_array($rows)) {
            return [];
        }

        $out = [];
        foreach ($rows as $row) {
            if (!is_array($row)) {
                continue;
            }

            $item = [
                'id' => (int)($row['id'] ?? 0),
                'nom' => (string)($row['nom'] ?? ''),
                'prenom' => (string)($row['prenom'] ?? ''),
                'mail' => (string)($row['mail'] ?? ''),
            ];

            if ($hasTelephone) {
                $item['telephone'] = (string)($row['telephone'] ?? '');
            }

            if ($item['id'] > 0) {
                $out[] = $item;
            }
        }

        return $out;
    }

    /** @param array{nom:string, prenom:string, mail:string, telephone?:string} $data */
    public static function updateUser(PdoUserRepository $repo, int $id, array $data): void
    {
        self::updateProfile($repo, $id, $data);
    }

    public static function deleteUser(PdoUserRepository $repo, int $id): void
    {
        $stmt = $repo->getPdo()->prepare('DELETE FROM utilisateur WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }

    public static function mailExistsForOtherId(PdoUserRepository $repo, string $mail, int $id): bool
    {
        $stmt = $repo->getPdo()->prepare('SELECT id FROM utilisateur WHERE mail = :mail AND id <> :id LIMIT 1');
        $stmt->execute(['mail' => $mail, 'id' => $id]);
        return (bool)$stmt->fetch();
    }

    public static function mailExists(PdoUserRepository $repo, string $mail): bool
    {
        $stmt = $repo->getPdo()->prepare('SELECT id FROM utilisateur WHERE mail = :mail LIMIT 1');
        $stmt->execute(['mail' => $mail]);
        return (bool)$stmt->fetch();
    }

    public static function createUser(
        PdoUserRepository $repo,
        string $nom,
        string $prenom,
        string $mail,
        string $passwordHash
    ): int {
        $stmt = $repo->getPdo()->prepare(
            'INSERT INTO utilisateur (nom, prenom, mail, mdp) VALUES (:nom, :prenom, :mail, :mdp)'
        );
        $stmt->execute([
            'nom' => $nom,
            'prenom' => $prenom,
            'mail' => $mail,
            'mdp' => $passwordHash,
        ]);

        return (int)$repo->getPdo()->lastInsertId();
    }

    public static function updatePassword(PdoUserRepository $repo, int $id, string $newHash): void
    {
        $stmt = $repo->getPdo()->prepare('UPDATE utilisateur SET mdp = :mdp WHERE id = :id');
        $stmt->execute(['mdp' => $newHash, 'id' => $id]);
    }

    /** @param array{nom:string, prenom:string, mail:string, telephone?:string} $data */
    public static function updateProfile(PdoUserRepository $repo, int $id, array $data): void
    {
        $fields = ['nom = :nom', 'prenom = :prenom', 'mail = :mail'];
        $params = [
            'id' => $id,
            'nom' => $data['nom'],
            'prenom' => $data['prenom'],
            'mail' => $data['mail'],
        ];

        if (self::hasTelephone($repo)) {
            $fields[] = 'telephone = :telephone';
            $params['telephone'] = $data['telephone'] ?? '';
        }

        $sql = 'UPDATE utilisateur SET ' . implode(', ', $fields) . ' WHERE id = :id';
        $stmt = $repo->getPdo()->prepare($sql);
        $stmt->execute($params);
    }

    private static function hasTelephone(PdoUserRepository $repo): bool
    {
        $cached = $repo->getHasTelephoneColumn();
        if ($cached !== null) {
            return $cached;
        }

        try {
            $stmt = $repo->getPdo()->query("SHOW COLUMNS FROM utilisateur LIKE 'telephone'");
            $row = $stmt->fetch();
            $repo->setHasTelephoneColumn((bool)$row);
        } catch (\Throwable $e) {
            $repo->setHasTelephoneColumn(false);
        }

        return (bool)$repo->getHasTelephoneColumn();
    }

    private static function selectColumns(PdoUserRepository $repo): string
    {
        $base = 'id, nom, prenom, mail, mdp';
        return self::hasTelephone($repo) ? ($base . ', telephone') : $base;
    }

    /** @param mixed $row */
    private static function hydrate($row): ?User
    {
        if (!$row || !is_array($row)) {
            return null;
        }

        $user = new User();
        $user->setId((int)($row['id'] ?? 0));
        $user->setNom((string)($row['nom'] ?? ''));
        $user->setPrenom((string)($row['prenom'] ?? ''));
        $user->setMail((string)($row['mail'] ?? ''));
        $user->setMdp((string)($row['mdp'] ?? ''));
        $user->setTelephone((string)($row['telephone'] ?? ''));

        return $user;
    }
}
