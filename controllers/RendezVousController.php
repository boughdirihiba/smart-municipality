<?php

require_once __DIR__ . '/../models/RendezVous.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class RendezVousController {

    // ============================================================
    // CRUD — Static methods used by views and dispatch()
    // ============================================================

    public static function create(RendezVous $rdv) {
        $query = "INSERT INTO " . $rdv->getTable() . " (user_id, categorie_id, date_rdv, heure, statut)
                  VALUES (:user_id, :categorie_id, :date_rdv, :heure, :statut)";
        $stmt = $rdv->getConn()->prepare($query);
        $stmt->bindParam(':user_id',      $rdv->getUserId());
        $stmt->bindParam(':categorie_id', $rdv->getCategorieId());
        $stmt->bindParam(':date_rdv',     $rdv->getDateRdv());
        $stmt->bindParam(':heure',        $rdv->getHeure());
        $stmt->bindParam(':statut',       $rdv->getStatut());
        if ($stmt->execute()) { $rdv->setId($rdv->getConn()->lastInsertId()); return true; }
        return false;
    }

    public static function readAll(RendezVous $rdv) {
        // FIX: schema uses `utilisateurs`, not `users`
        $query = "SELECT r.*, c.nom AS service_nom,
                         u.nom AS user_nom, u.prenom AS user_prenom, u.email AS user_email
                  FROM " . $rdv->getTable() . " r
                  JOIN categories c ON r.categorie_id = c.id
                  JOIN utilisateurs u ON r.user_id = u.id
                  ORDER BY r.date_rdv DESC, r.heure ASC";
        $stmt = $rdv->getConn()->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function readByUser(RendezVous $rdv, $user_id) {
        $query = "SELECT r.*, c.nom AS service_nom
                  FROM " . $rdv->getTable() . " r
                  JOIN categories c ON r.categorie_id = c.id
                  WHERE r.user_id = :user_id
                  ORDER BY r.date_rdv DESC, r.heure ASC";
        $stmt = $rdv->getConn()->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function readOne(RendezVous $rdv, $id) {
        $query = "SELECT r.*, c.nom AS service_nom
                  FROM " . $rdv->getTable() . " r
                  JOIN categories c ON r.categorie_id = c.id
                  WHERE r.id = :id";
        $stmt = $rdv->getConn()->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            $rdv->setId($row['id']);
            $rdv->setUserId($row['user_id']);
            $rdv->setCategorieId($row['categorie_id']);
            $rdv->setDateRdv($row['date_rdv']);
            $rdv->setHeure($row['heure']);
            $rdv->setStatut($row['statut']);
            return $row;
        }
        return false;
    }

    public static function update(RendezVous $rdv) {
        $query = "UPDATE " . $rdv->getTable() . "
                  SET categorie_id = :categorie_id, date_rdv = :date_rdv,
                      heure = :heure, statut = :statut
                  WHERE id = :id";
        $stmt = $rdv->getConn()->prepare($query);
        $stmt->bindParam(':categorie_id', $rdv->getCategorieId());
        $stmt->bindParam(':date_rdv',     $rdv->getDateRdv());
        $stmt->bindParam(':heure',        $rdv->getHeure());
        $stmt->bindParam(':statut',       $rdv->getStatut());
        $stmt->bindParam(':id',           $rdv->getId());
        return $stmt->execute();
    }

    public static function delete(RendezVous $rdv, $id) {
        $query = "DELETE FROM " . $rdv->getTable() . " WHERE id = :id";
        $stmt = $rdv->getConn()->prepare($query);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    // ============================================================
    // SLOTS
    // ============================================================

    public static function getAvailableSlots(RendezVous $rdv, $categorie_id, $date_rdv) {
        $all_slots = ['09:00','10:00','11:00','14:00','15:00','16:00'];
        $query = "SELECT heure FROM " . $rdv->getTable() . "
                  WHERE categorie_id = :categorie_id AND date_rdv = :date_rdv AND statut != 'annule'";
        $stmt = $rdv->getConn()->prepare($query);
        $stmt->bindParam(':categorie_id', $categorie_id);
        $stmt->bindParam(':date_rdv', $date_rdv);
        $stmt->execute();
        $booked = $stmt->fetchAll(PDO::FETCH_COLUMN);
        return array_values(array_diff($all_slots, $booked));
    }

    public static function isSlotTaken(RendezVous $rdv, $categorie_id, $date_rdv, $heure) {
        $query = "SELECT COUNT(*) FROM " . $rdv->getTable() . "
                  WHERE categorie_id = :categorie_id AND date_rdv = :date_rdv
                    AND heure = :heure AND statut != 'annule'";
        $stmt = $rdv->getConn()->prepare($query);
        $stmt->bindParam(':categorie_id', $categorie_id);
        $stmt->bindParam(':date_rdv', $date_rdv);
        $stmt->bindParam(':heure', $heure);
        $stmt->execute();
        return $stmt->fetchColumn() > 0;
    }

    // ============================================================
    // CATEGORIES
    // ============================================================

    public static function getAllCategories(RendezVous $rdv) {
        $stmt = $rdv->getConn()->prepare("SELECT * FROM categories ORDER BY id");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getCategoryById(RendezVous $rdv, $id) {
        $stmt = $rdv->getConn()->prepare("SELECT * FROM categories WHERE id = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public static function createCategory(RendezVous $rdv, $nom, $description, $icone) {
        $stmt = $rdv->getConn()->prepare(
            "INSERT INTO categories (nom, description, icone) VALUES (:nom, :description, :icone)"
        );
        $stmt->bindParam(':nom', $nom);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':icone', $icone);
        return $stmt->execute() ? $rdv->getConn()->lastInsertId() : false;
    }

    public static function updateCategory(RendezVous $rdv, $id, $nom, $description, $icone) {
        $stmt = $rdv->getConn()->prepare(
            "UPDATE categories SET nom = :nom, description = :description, icone = :icone WHERE id = :id"
        );
        $stmt->bindParam(':nom', $nom);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':icone', $icone);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    public static function deleteCategory(RendezVous $rdv, $id) {
        $stmt = $rdv->getConn()->prepare("DELETE FROM categories WHERE id = :id");
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    // ============================================================
    // SUGGESTIONS — AI-based category suggestion
    // ============================================================

    public static function getSuggestions(RendezVous $rdv, $query) {
        $categories = self::getAllCategories($rdv);
        if (empty($categories)) return [];

        $keywords = [
            'état civil'           => ['naissance','acte','mariage','décès','divorce','extrait','enfant','civil'],
            'légalisation'         => ['légaliser','légalisation','document','copie','authentifier','certifier','tampon'],
            'paiement taxes'       => ['taxe','impôt','amende','payer','redevance','fiscalité','contribution','paiement'],
            'casier judiciaire'    => ['casier','judiciaire','extrait','condamnation','moralité','bulletin'],
            'permis de construire' => ['construire','construction','permis','bâtir','travaux','architecte','plan'],
            "carte d'identité"     => ['cin','carte identité','identité','renouveler','pièce'],
            'passeport'            => ['passeport','voyage','étranger','visa','international'],
            'carte de séjour'      => ['séjour','résidence','étranger','immigré'],
            'urbanisme'            => ['urbanisme','plan','zone','terrain','cadastre','lotissement'],
            'aide sociale'         => ['aide','social','allocations','assistance','bourse','handicap'],
        ];

        $queryLower = mb_strtolower(trim($query), 'UTF-8');
        $queryWords = array_filter(explode(' ', $queryLower), fn($w) => strlen($w) > 2);
        $scores = [];

        foreach ($categories as $cat) {
            $score      = 0;
            $catNameLow = mb_strtolower($cat['nom'], 'UTF-8');
            $catDescLow = mb_strtolower($cat['description'] ?? '', 'UTF-8');

            if (str_contains($catNameLow, $queryLower) || str_contains($queryLower, $catNameLow))
                $score += 60;

            foreach ($keywords as $keyName => $keyList) {
                if (str_contains($catNameLow, $keyName) || str_contains($keyName, $catNameLow)) {
                    foreach ($keyList as $kw) {
                        foreach ($queryWords as $qw) {
                            if (str_contains($qw, $kw) || str_contains($kw, $qw)) $score += 20;
                            if (levenshtein($qw, $kw) <= 2 && strlen($kw) > 3)    $score += 10;
                        }
                    }
                }
            }

            foreach ($queryWords as $qw) {
                if (str_contains($catDescLow, $qw)) $score += 8;
                foreach (explode(' ', $catNameLow) as $nameWord)
                    if (strlen($nameWord) > 3 && levenshtein($qw, $nameWord) <= 2) $score += 15;
            }

            if ($score > 0)
                $scores[] = ['id' => $cat['id'], 'nom' => $cat['nom'],
                    'description' => $cat['description'] ?? '', 'icone' => $cat['icone'] ?? 'rdv.svg',
                    'score' => $score, 'confidence' => min(100, $score)];
        }

        usort($scores, fn($a, $b) => $b['score'] - $a['score']);
        return array_slice($scores, 0, 3);
    }

    // ============================================================
    // MULTI-SERVICE AVAILABILITY
    // ============================================================

    public static function isSlotFree(RendezVous $rdv, $catId, $date, $time) {
        $stmt = $rdv->getConn()->prepare(
            "SELECT COUNT(*) FROM rendez_vous
             WHERE categorie_id = :cat_id AND date_rdv = :date
               AND statut != 'annule'
               AND ABS(TIMESTAMPDIFF(MINUTE, heure, :heure)) < 20"
        );
        $stmt->execute([':cat_id' => $catId, ':date' => $date, ':heure' => $time]);
        return $stmt->fetchColumn() == 0;
    }

    public static function isChainFree(RendezVous $rdv, array $catIds, $date, $startTime) {
        [$h, $m] = array_map('intval', explode(':', $startTime));
        foreach ($catIds as $i => $catId) {
            $totalMin = $h * 60 + $m + ($i * 20);
            if ($totalMin >= 17 * 60) return false;
            $slotTime = sprintf('%02d:%02d:00', intdiv($totalMin, 60), $totalMin % 60);
            if (!self::isSlotFree($rdv, $catId, $date, $slotTime)) return false;
        }
        return true;
    }

    public static function getAvailableDates(RendezVous $rdv, array $catIds, $startTime, $daysAhead = 60) {
        $available = [];
        $today = new DateTime();
        for ($d = 1; $d <= $daysAhead; $d++) {
            $date = (clone $today)->modify("+$d days")->format('Y-m-d');
            if (self::isChainFree($rdv, $catIds, $date, $startTime)) $available[] = $date;
        }
        return $available;
    }

    public static function getAvailableTimes(RendezVous $rdv, array $catIds, $date) {
        $available = [];
        $n        = count($catIds);
        $startMin = 8 * 60;
        $endMin   = 17 * 60 - ($n - 1) * 20;
        for ($t = $startMin; $t <= $endMin; $t += 5) {
            $timeStr = sprintf('%02d:%02d:00', intdiv($t, 60), $t % 60);
            if (self::isChainFree($rdv, $catIds, $date, $timeStr))
                $available[] = sprintf('%02d:%02d', intdiv($t, 60), $t % 60);
        }
        return $available;
    }

    // ============================================================
    // DISPATCH — Called by legacy_router.php for all rdv_ actions
    // FIX: moved out of if(realpath...) so it works via index.php
    // ============================================================

    public static function dispatch($conn, RendezVous $rdv) {
        if (session_status() === PHP_SESSION_NONE) session_start();

        // Buffer all output so stray warnings don't corrupt JSON responses
        ob_start();

        // FIX: use BASE_URL constant instead of hardcoded '/smart-municipality'
        $base      = defined('BASE_URL') ? BASE_URL : '';
        $frontUrl  = $base . '/index.php?action=rendez_vous';
        $backUrl   = $base . '/index.php?action=rdv_backoffice';
        $catsUrl   = $base . '/index.php?action=rdv_categories';

        // Determine action — strip the rdv_ prefix so old switch cases still work
        $rawAction = $_POST['action'] ?? $_GET['action'] ?? '';
        $action    = preg_replace('/^rdv_/', '', $rawAction); // rdv_delete → delete

        switch ($action) {

            // ── CREATE MULTI ──────────────────────────────────────────────
            case 'create_multi':
                $isAjax   = isset($_SERVER['HTTP_X_REQUESTED_WITH'])
                            && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
                $date_rdv = $_POST['date_rdv'] ?? '';
                $slots    = $_POST['slots']    ?? [];

                if (empty($date_rdv) || empty($slots)) {
                    if ($isAjax) {
                        ob_clean();
                        header('Content-Type: application/json');
                        echo json_encode(['success' => false, 'message' => 'Données manquantes.']);
                        exit;
                    }
                    $_SESSION['error'] = "Données manquantes.";
                    ob_end_clean();
                header("Location: $frontUrl"); exit;
                }

                $created = $errors = 0;
                $createdSlots = [];

                foreach ($slots as $slotStr) {
                    [$cat_id, $heure] = explode('|', $slotStr, 2);
                    $cat_id = (int)$cat_id;
                    if (self::isSlotTaken($rdv, $cat_id, $date_rdv, $heure)) { $errors++; continue; }

                    $rdv->setUserId($_SESSION['user']['id'] ?? $_SESSION['user_id'] ?? 1);
                    $rdv->setCategorieId($cat_id);
                    $rdv->setDateRdv($date_rdv);
                    $rdv->setHeure($heure);
                    $rdv->setStatut('en_attente');

                    if (self::create($rdv)) {
                        $created++;
                        $stmtCat = $conn->prepare("SELECT nom FROM categories WHERE id = :id");
                        $stmtCat->execute([':id' => $cat_id]);
                        $catRow = $stmtCat->fetch(PDO::FETCH_ASSOC);
                        $createdSlots[] = ['service' => $catRow['nom'] ?? 'Service', 'heure' => $heure];
                    } else { $errors++; }
                }

                if ($created > 0) {
                    $msg = $errors === 0
                        ? "$created rendez-vous enregistré(s) avec succès."
                        : "$created rendez-vous enregistré(s). $errors créneau(x) ignoré(s).";
                    if ($isAjax) {
                        ob_clean();
                        header('Content-Type: application/json');
                        echo json_encode(['success' => true, 'message' => $msg, 'created' => $created]);
                        exit;
                    }
                    $_SESSION['success'] = $msg;
                } else {
                    $msg = "Tous les créneaux sont déjà pris.";
                    if ($isAjax) {
                        ob_clean();
                        header('Content-Type: application/json');
                        echo json_encode(['success' => false, 'message' => $msg]);
                        exit;
                    }
                    $_SESSION['error'] = $msg;
                }
                ob_end_clean();
                header("Location: $frontUrl"); exit;

            // ── CREATE SINGLE ─────────────────────────────────────────────
            case 'create':
                $categorie_id = $_POST['categorie_id'] ?? '';
                $date_rdv     = $_POST['date_rdv']     ?? '';
                $heure        = $_POST['heure']        ?? '';

                if (empty($categorie_id) || empty($date_rdv) || empty($heure)) {
                    $_SESSION['error'] = "Veuillez remplir tous les champs.";
                    ob_end_clean();
                header("Location: $frontUrl"); exit;
                }

                if (self::isSlotTaken($rdv, $categorie_id, $date_rdv, $heure)) {
                    $_SESSION['error'] = "Ce créneau est déjà réservé.";
                    header("Location: $frontUrl?categorie_id=$categorie_id&date=$date_rdv"); exit;
                }

                $rdv->setUserId($_SESSION['user']['id'] ?? 1);
                $rdv->setCategorieId($categorie_id);
                $rdv->setDateRdv($date_rdv);
                $rdv->setHeure($heure);
                $rdv->setStatut('en_attente');
                $_SESSION[self::create($rdv) ? 'success' : 'error'] =
                    self::create($rdv) ? "Rendez-vous enregistré." : "Erreur lors de l'enregistrement.";
                ob_end_clean();
                header("Location: $frontUrl"); exit;

            // ── CONFIRM ───────────────────────────────────────────────────
            case 'confirm':
                $id = $_GET['id'] ?? '';
                if (empty($id)) { $_SESSION['error'] = "Introuvable."; header("Location: $backUrl"); exit; }

                $row = self::readOne($rdv, $id);
                if ($row) {
                    $rdv->setStatut('confirme');
                    if (self::update($rdv)) {
                        $_SESSION['success'] = "Rendez-vous #$id confirmé.";
                        // Email notification
                        self::trySendConfirmEmail($conn, $row);
                    } else {
                        $_SESSION['error'] = "Erreur lors de la confirmation.";
                    }
                }
                header("Location: $backUrl"); exit;

            // ── CANCEL (admin) ────────────────────────────────────────────
            case 'cancel':
                $id = $_GET['id'] ?? '';
                if (empty($id)) { $_SESSION['error'] = "Introuvable."; header("Location: $backUrl"); exit; }
                $row = self::readOne($rdv, $id);
                if ($row) {
                    $rdv->setStatut('annule');
                    $_SESSION[self::update($rdv) ? 'success' : 'error'] =
                        self::update($rdv) ? "Rendez-vous #$id annulé." : "Erreur lors de l'annulation.";
                }
                header("Location: $backUrl"); exit;

            // ── DELETE ────────────────────────────────────────────────────
            case 'delete':
                $id   = $_GET['id']   ?? $_POST['id']   ?? '';
                $from = $_GET['from'] ?? $_POST['from'] ?? 'back';
                if (empty($id)) {
                    $_SESSION['error'] = "Introuvable.";
                    header("Location: " . ($from === 'front' ? $frontUrl : $backUrl)); exit;
                }
                $_SESSION[self::delete($rdv, $id) ? 'success' : 'error'] =
                    self::delete($rdv, $id) ? "Rendez-vous supprimé." : "Erreur lors de la suppression.";
                header("Location: " . ($from === 'front' ? $frontUrl : $backUrl)); exit;

            // ── EDIT (show edit form) ─────────────────────────────────────
            case 'edit':
                $id = $_GET['id'] ?? $_POST['id'] ?? '';
                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    // Save changes
                    $categorie_id = $_POST['categorie_id'] ?? '';
                    $date_rdv     = $_POST['date_rdv']     ?? '';
                    $heure        = $_POST['heure']        ?? '';
                    if (empty($id) || empty($categorie_id) || empty($date_rdv) || empty($heure)) {
                        $_SESSION['error'] = "Veuillez remplir tous les champs.";
                        ob_end_clean();
                header("Location: $frontUrl"); exit;
                    }
                    $rdv->setId($id);
                    $rdv->setCategorieId($categorie_id);
                    $rdv->setDateRdv($date_rdv);
                    $rdv->setHeure($heure);
                    $rdv->setStatut('en_attente');
                    $_SESSION[self::update($rdv) ? 'success' : 'error'] =
                        self::update($rdv) ? "Rendez-vous modifié." : "Erreur de modification.";
                    ob_end_clean();
                header("Location: $frontUrl"); exit;
                } else {
                    // Show edit form
                    if (empty($id)) { header("Location: $frontUrl"); exit; }
                    $rdv_data        = self::readOne($rdv, $id);
                    $categories_edit = self::getAllCategories($rdv);
                    include __DIR__ . '/../views/frontoffice/rdv-edit.php';
                    exit;
                }

            // ── UPDATE (backoffice) ───────────────────────────────────────
            case 'update':
                $id           = $_POST['id']           ?? '';
                $categorie_id = $_POST['categorie_id'] ?? '';
                $date_rdv     = $_POST['date_rdv']     ?? '';
                $heure        = $_POST['heure']        ?? '';
                $statut       = $_POST['statut']       ?? 'en_attente';

                if (empty($id) || empty($categorie_id) || empty($date_rdv) || empty($heure)) {
                    $_SESSION['error'] = "Données invalides.";
                    header("Location: $backUrl"); exit;
                }

                $rdv->setId($id);
                $rdv->setCategorieId($categorie_id);
                $rdv->setDateRdv($date_rdv);
                $rdv->setHeure($heure);
                $rdv->setStatut($statut);
                $_SESSION[self::update($rdv) ? 'success' : 'error'] =
                    self::update($rdv) ? "Rendez-vous modifié." : "Erreur de modification.";
                header("Location: $backUrl"); exit;

            // ── CATEGORY CRUD ─────────────────────────────────────────────
            case 'create_category':
                $nom         = trim($_POST['nom']         ?? '');
                $description = trim($_POST['description'] ?? '');
                $icone       = trim($_POST['icone']       ?? 'rdv.svg');
                if (empty($nom)) { $_SESSION['error'] = "Nom requis."; header("Location: $catsUrl?new=1"); exit; }
                $newId = self::createCategory($rdv, $nom, $description, $icone);
                $_SESSION[$newId ? 'success' : 'error'] =
                    $newId ? "Catégorie \"$nom\" créée." : "Erreur lors de la création.";
                header("Location: " . ($newId ? "$catsUrl?edit=$newId" : "$catsUrl?new=1")); exit;

            case 'update_category':
                $id          = (int)($_POST['id']          ?? 0);
                $nom         = trim($_POST['nom']          ?? '');
                $description = trim($_POST['description']  ?? '');
                $icone       = trim($_POST['icone']        ?? 'rdv.svg');
                if ($id <= 0 || empty($nom)) { $_SESSION['error'] = "Données invalides."; header("Location: $catsUrl"); exit; }
                $_SESSION[self::updateCategory($rdv, $id, $nom, $description, $icone) ? 'success' : 'error'] =
                    self::updateCategory($rdv, $id, $nom, $description, $icone) ? "Catégorie mise à jour." : "Erreur.";
                header("Location: $catsUrl?edit=$id"); exit;

            case 'delete_category':
                $id = (int)($_GET['id'] ?? 0);
                if ($id <= 0) { $_SESSION['error'] = "Introuvable."; header("Location: $catsUrl"); exit; }
                try {
                    $_SESSION[self::deleteCategory($rdv, $id) ? 'success' : 'error'] =
                        self::deleteCategory($rdv, $id) ? "Catégorie supprimée." : "Erreur.";
                } catch (PDOException $e) {
                    $_SESSION['error'] = strpos($e->getMessage(), '1451') !== false
                        ? "Impossible : des rendez-vous sont liés à cette catégorie."
                        : "Erreur: " . $e->getMessage();
                }
                header("Location: $catsUrl"); exit;

            default:
                ob_end_clean();
                header("Location: $frontUrl"); exit;
        }
    }

    // ============================================================
    // EMAIL HELPER
    // ============================================================

    private static function trySendConfirmEmail($conn, $row) {
        // FIX: uses `utilisateurs` table
        $stmt = $conn->prepare("SELECT nom, prenom, email FROM utilisateurs WHERE id = :id");
        $stmt->bindParam(':id', $row['user_id']);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$user || empty($user['email'])) return;

        $phpMailerPath = __DIR__ . '/../PHPMailer/';
        if (!file_exists($phpMailerPath . 'PHPMailer.php')) return;

        require_once $phpMailerPath . 'PHPMailer.php';
        require_once $phpMailerPath . 'SMTP.php';
        require_once $phpMailerPath . 'Exception.php';

        try {
            $mail = new PHPMailer(true);
            $mail->isSMTP();
            $mail->Host       = 'smtp-relay.brevo.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = getenv('BREVO_SMTP_USERNAME') ?: 'aa60df001@smtp-brevo.com';
            $mail->Password   = getenv('BREVO_SMTP_PASSWORD') ?: '';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;
            $mail->CharSet    = 'UTF-8';
            $mail->setFrom('aa60df001@smtp-brevo.com', 'Smart Municipality');
            $mail->addAddress($user['email']);
            $mail->isHTML(true);
            $mail->Subject = 'Smart Municipality — Rendez-vous confirmé';
            $prenom  = htmlspecialchars($user['prenom'] ?? $user['nom'] ?? 'Citoyen');
            $service = htmlspecialchars($row['service_nom'] ?? '');
            $date    = htmlspecialchars(date('d/m/Y', strtotime($row['date_rdv'])));
            $heure   = htmlspecialchars(substr($row['heure'], 0, 5));
            $mail->Body = "<p>Bonjour <strong>$prenom</strong>,</p>
                <p>Votre rendez-vous <strong>$service</strong> du <strong>$date à $heure</strong> a été <strong style='color:#27ae60;'>confirmé</strong>.</p>
                <p>Smart Municipality</p>";
            $mail->send();
            $_SESSION['success'] .= " — Email envoyé à {$user['email']}.";
        } catch (\Exception $e) {
            $_SESSION['success'] .= " — Email non envoyé: {$e->getMessage()}.";
        }
    }
}
