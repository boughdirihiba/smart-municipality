<?php

require_once __DIR__ . '/../models/RendezVous.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class RendezVousController {

    public static function create(RendezVous $rdv) {
        $query = "INSERT INTO " . $rdv->getTable() . " (user_id, categorie_id, date_rdv, heure, statut) 
                  VALUES (:user_id, :categorie_id, :date_rdv, :heure, :statut)";

        $stmt = $rdv->getConn()->prepare($query);
        $stmt->bindParam(':user_id', $rdv->getUserId());
        $stmt->bindParam(':categorie_id', $rdv->getCategorieId());
        $stmt->bindParam(':date_rdv', $rdv->getDateRdv());
        $stmt->bindParam(':heure', $rdv->getHeure());
        $stmt->bindParam(':statut', $rdv->getStatut());

        if ($stmt->execute()) {
            $rdv->setId($rdv->getConn()->lastInsertId());
            return true;
        }

        return false;
    }

    public static function readAll(RendezVous $rdv) {
        $query = "SELECT r.*, c.nom AS service_nom, u.nom AS user_nom, u.prenom AS user_prenom, u.email AS user_email 
                  FROM " . $rdv->getTable() . " r 
                  JOIN categories c ON r.categorie_id = c.id 
                  JOIN users u ON r.user_id = u.id 
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
                  SET categorie_id = :categorie_id, date_rdv = :date_rdv, heure = :heure, statut = :statut 
                  WHERE id = :id";

        $stmt = $rdv->getConn()->prepare($query);
        $stmt->bindParam(':categorie_id', $rdv->getCategorieId());
        $stmt->bindParam(':date_rdv', $rdv->getDateRdv());
        $stmt->bindParam(':heure', $rdv->getHeure());
        $stmt->bindParam(':statut', $rdv->getStatut());
        $stmt->bindParam(':id', $rdv->getId());

        return $stmt->execute();
    }

    public static function delete(RendezVous $rdv, $id) {
        $query = "DELETE FROM " . $rdv->getTable() . " WHERE id = :id";

        $stmt = $rdv->getConn()->prepare($query);
        $stmt->bindParam(':id', $id);

        return $stmt->execute();
    }

    public static function getAvailableSlots(RendezVous $rdv, $categorie_id, $date_rdv) {
        $all_slots = ['09:00', '10:00', '11:00', '14:00', '15:00', '16:00'];

        $query = "SELECT heure FROM " . $rdv->getTable() . " 
                  WHERE categorie_id = :categorie_id AND date_rdv = :date_rdv AND statut != 'annule'";

        $stmt = $rdv->getConn()->prepare($query);
        $stmt->bindParam(':categorie_id', $categorie_id);
        $stmt->bindParam(':date_rdv', $date_rdv);
        $stmt->execute();

        $booked = $stmt->fetchAll(PDO::FETCH_COLUMN);
        $available = array_diff($all_slots, $booked);

        return array_values($available);
    }

    public static function isSlotTaken(RendezVous $rdv, $categorie_id, $date_rdv, $heure) {
        $query = "SELECT COUNT(*) FROM " . $rdv->getTable() . " 
                  WHERE categorie_id = :categorie_id AND date_rdv = :date_rdv AND heure = :heure AND statut != 'annule'";

        $stmt = $rdv->getConn()->prepare($query);
        $stmt->bindParam(':categorie_id', $categorie_id);
        $stmt->bindParam(':date_rdv', $date_rdv);
        $stmt->bindParam(':heure', $heure);
        $stmt->execute();

        return $stmt->fetchColumn() > 0;
    }

    public static function getAllCategories(RendezVous $rdv) {
        $query = "SELECT * FROM categories ORDER BY id";
        $stmt = $rdv->getConn()->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getCategoryById(RendezVous $rdv, $id) {
        $query = "SELECT * FROM categories WHERE id = :id";
        $stmt = $rdv->getConn()->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public static function createCategory(RendezVous $rdv, $nom, $description, $icone) {
        $query = "INSERT INTO categories (nom, description, icone) VALUES (:nom, :description, :icone)";
        $stmt = $rdv->getConn()->prepare($query);
        $stmt->bindParam(':nom', $nom);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':icone', $icone);
        if ($stmt->execute()) {
            return $rdv->getConn()->lastInsertId();
        }
        return false;
    }

    public static function updateCategory(RendezVous $rdv, $id, $nom, $description, $icone) {
        $query = "UPDATE categories SET nom = :nom, description = :description, icone = :icone WHERE id = :id";
        $stmt = $rdv->getConn()->prepare($query);
        $stmt->bindParam(':nom', $nom);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':icone', $icone);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    public static function deleteCategory(RendezVous $rdv, $id) {
        $query = "DELETE FROM categories WHERE id = :id";
        $stmt = $rdv->getConn()->prepare($query);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    // ============================================================
    // SUGGESTION DE CATÉGORIE — métier avancé
    // Retourne les 3 meilleures catégories pour une description
    // libre du citoyen. 3 couches : mots-clés + Levenshtein + desc.
    // ============================================================
    public static function getSuggestions(RendezVous $rdv, $query) {

        $categories = self::getAllCategories($rdv);
        if (empty($categories)) return [];

        // Dictionnaire de mots-clés par service
        // Clé = nom de catégorie (lowercase), valeur = liste de mots-clés
        $keywords = [
            'état civil'              => ['naissance','acte','mariage','décès','divorce','filiation','extrait','enfant','naissance','civil'],
            'légalisation'            => ['légaliser','légalisation','document','copie','authentifier','certifier','légal','notaire','signature','tampon'],
            'paiement taxes'          => ['taxe','impôt','amende','payer','redevance','fiscalité','trésor','contribution','paiement','facture'],
            'casier judiciaire'       => ['casier','judiciaire','extrait','condamnation','moralité','justice','bulletin'],
            'permis de construire'    => ['construire','construction','permis','bâtir','travaux','bâtiment','architecte','plan','immeuble'],
            'carte d\'identité'       => ['cin','carte identité','identité','identifiant','renouveler','pièce','id'],
            'passeport'               => ['passeport','voyage','étranger','visa','international','aéroport'],
            'carte de séjour'         => ['séjour','résidence','étranger','immigré','permis séjour'],
            'urbanisme'               => ['urbanisme','plan','zone','terrain','cadastre','lotissement','immeuble','quartier'],
            'aide sociale'            => ['aide','social','allocations','pauvreté','assistance','bourse','famille','handicap'],
        ];

        $queryLower = mb_strtolower(trim($query), 'UTF-8');
        $queryWords = array_filter(explode(' ', $queryLower), fn($w) => strlen($w) > 2);

        $scores = [];

        foreach ($categories as $cat) {
            $score      = 0;
            $catNameLow = mb_strtolower($cat['nom'], 'UTF-8');
            $catDescLow = mb_strtolower($cat['description'] ?? '', 'UTF-8');

            // --- Layer 1: exact category name match ---
            if (str_contains($catNameLow, $queryLower) || str_contains($queryLower, $catNameLow)) {
                $score += 60;
            }

            // --- Layer 2: keyword dictionary ---
            foreach ($keywords as $keyName => $keyList) {
                if (str_contains($catNameLow, $keyName) || str_contains($keyName, $catNameLow)) {
                    foreach ($keyList as $kw) {
                        foreach ($queryWords as $qw) {
                            if (str_contains($qw, $kw) || str_contains($kw, $qw)) {
                                $score += 20;
                            }
                            // Levenshtein — typo tolerance (distance ≤ 2)
                            if (levenshtein($qw, $kw) <= 2 && strlen($kw) > 3) {
                                $score += 10;
                            }
                        }
                    }
                }
            }

            // --- Layer 3: description match ---
            foreach ($queryWords as $qw) {
                if (str_contains($catDescLow, $qw)) {
                    $score += 8;
                }
                // Levenshtein on category name words
                foreach (explode(' ', $catNameLow) as $nameWord) {
                    if (strlen($nameWord) > 3 && levenshtein($qw, $nameWord) <= 2) {
                        $score += 15;
                    }
                }
            }

            if ($score > 0) {
                $scores[] = [
                    'id'          => $cat['id'],
                    'nom'         => $cat['nom'],
                    'description' => $cat['description'] ?? '',
                    'icone'       => $cat['icone'] ?? 'rdv.svg',
                    'score'       => $score,
                    'confidence'  => min(100, $score), // cap at 100%
                ];
            }
        }

        // Sort by score descending, return top 3
        usort($scores, fn($a, $b) => $b['score'] - $a['score']);
        return array_slice($scores, 0, 3);
    }

    // ============================================================
    // MULTI-SERVICE AVAILABILITY — Option A & B
    // ============================================================

    /**
     * Is a single slot free?
     * Free = no existing appointment for that service within 20 min of $time.
     */
    public static function isSlotFree(RendezVous $rdv, $catId, $date, $time) {
        $stmt = $rdv->getConn()->prepare(
            "SELECT COUNT(*) FROM rendez_vous
             WHERE categorie_id = :cat_id
               AND date_rdv     = :date
               AND statut      != 'annule'
               AND ABS(TIMESTAMPDIFF(MINUTE, heure, :heure)) < 20"
        );
        $stmt->execute([':cat_id' => $catId, ':date' => $date, ':heure' => $time]);
        return $stmt->fetchColumn() == 0;
    }

    /**
     * Is the full chain free?
     * Service[0] at startTime, Service[1] at +20min, Service[2] at +40min…
     */
    public static function isChainFree(RendezVous $rdv, array $catIds, $date, $startTime) {
        list($h, $m) = array_map('intval', explode(':', $startTime));
        foreach ($catIds as $i => $catId) {
            $totalMin = $h * 60 + $m + ($i * 20);
            if ($totalMin >= 17 * 60) return false;
            $slotTime = sprintf('%02d:%02d:00', intdiv($totalMin, 60), $totalMin % 60);
            if (!self::isSlotFree($rdv, $catId, $date, $slotTime)) return false;
        }
        return true;
    }

    /**
     * OPTION A — Given a start time, return dates (next $daysAhead days)
     * where the full chain fits.
     */
    public static function getAvailableDates(RendezVous $rdv, array $catIds, $startTime, $daysAhead = 60) {
        $available = [];
        $today     = new DateTime();
        for ($d = 1; $d <= $daysAhead; $d++) {
            $date = (clone $today)->modify("+$d days")->format('Y-m-d');
            if (self::isChainFree($rdv, $catIds, $date, $startTime)) {
                $available[] = $date;
            }
        }
        return $available;
    }

    /**
     * OPTION B — Given a date, return all valid start times (every 5 min)
     * where the full chain fits.
     */
    public static function getAvailableTimes(RendezVous $rdv, array $catIds, $date) {
        $available = [];
        $n         = count($catIds);
        $startMin  = 8  * 60;
        $endMin    = 17 * 60 - ($n - 1) * 20;

        for ($t = $startMin; $t <= $endMin; $t += 5) {
            $timeStr = sprintf('%02d:%02d:00', intdiv($t, 60), $t % 60);
            if (self::isChainFree($rdv, $catIds, $date, $timeStr)) {
                $available[] = sprintf('%02d:%02d', intdiv($t, 60), $t % 60);
            }
        }
        return $available;
    }

}

function getUserInfo($conn, $user_id) {
    $stmt = $conn->prepare("SELECT nom, prenom, email FROM users WHERE id = :id");
    $stmt->bindParam(':id', $user_id);
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function sendConfirmationEmail($toEmail, $prenom, $service, $date, $heure) {

    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp-relay.brevo.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = getenv('BREVO_SMTP_USERNAME') ?: 'aa60df001@smtp-brevo.com';
        $mail->Password   = getenv('BREVO_SMTP_PASSWORD') ?: '';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;
        $mail->CharSet    = 'UTF-8';

        $mail->setFrom('aa60df001@smtp-brevo.com', 'Smart Municipality');
        $mail->addAddress($toEmail);

        $mail->isHTML(true);
        $mail->Subject = 'Smart Municipality - Rendez-vous confirmé !';
        $mail->Body = "
        <html>
        <body style='font-family: Segoe UI, Tahoma, sans-serif; background-color: #f0f0f0; padding: 20px; margin: 0;'>
            <div style='max-width: 500px; margin: 0 auto; background: white; border-radius: 12px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.1);'>
                
                <div style='background-color: #1a5c2a; color: white; padding: 25px; text-align: center;'>
                    <h1 style='font-size: 20px; margin: 0;'>&#127963; Smart Municipality</h1>
                    <p style='margin: 5px 0 0 0; font-size: 13px; opacity: 0.8;'>Confirmation de rendez-vous</p>
                </div>
                
                <div style='padding: 25px;'>
                    <p style='font-size: 15px; color: #333;'>Bonjour <strong>$prenom</strong>,</p>
                    <p style='font-size: 14px; color: #555;'>Bonne nouvelle ! Votre rendez-vous a été <strong style=\"color: #27ae60;\">confirmé</strong> par l'administration.</p>
                    
                    <div style='background-color: #d4edda; border-radius: 8px; padding: 10px 15px; display: inline-block; margin: 10px 0;'>
                        <span style='color: #155724; font-weight: bold; font-size: 13px;'>&#10004; Confirmé</span>
                    </div>
                    
                    <div style='background-color: #f8f9fa; border-radius: 10px; padding: 20px; margin: 20px 0;'>
                        <table style='width: 100%;'>
                            <tr>
                                <td style='padding: 8px 0; font-size: 13px;'>
                                    <strong style='color: #333;'>&#128197; Service</strong><br>
                                    <span style='color: #666;'>$service</span>
                                </td>
                            </tr>
                            <tr>
                                <td style='padding: 8px 0; font-size: 13px; border-top: 1px solid #eee;'>
                                    <strong style='color: #333;'>&#128198; Date</strong><br>
                                    <span style='color: #666;'>$date</span>
                                </td>
                            </tr>
                            <tr>
                                <td style='padding: 8px 0; font-size: 13px; border-top: 1px solid #eee;'>
                                    <strong style='color: #333;'>&#9200; Heure</strong><br>
                                    <span style='color: #666;'>$heure</span>
                                </td>
                            </tr>
                        </table>
                    </div>
                    
                    <p style='font-size: 13px; color: #888;'>Veuillez vous présenter à l'heure indiquée avec vos documents nécessaires.</p>
                </div>
                
                <div style='background-color: #f8f8f8; padding: 15px; text-align: center; font-size: 11px; color: #999;'>
                    Smart Municipality &copy; 2026 - Ne pas répondre à cet email
                </div>
            </div>
        </body>
        </html>";

        $mail->send();
        return true;

    } catch (Exception $e) {
        // Store error in session so admin can see what went wrong
        $_SESSION['email_error'] = 'Erreur email : ' . $mail->ErrorInfo;
        return false;
    }
}
// MULTI-SERVICE CONFIRMATION EMAIL
// Sends one email listing all chained appointments
// ---------------------------------------------------------------
function sendMultiConfirmationEmail($toEmail, $prenom, array $slots, $date) {

    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp-relay.brevo.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = getenv('BREVO_SMTP_USERNAME') ?: 'aa60df001@smtp-brevo.com';
        $mail->Password   = getenv('BREVO_SMTP_PASSWORD') ?: '';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;
        $mail->CharSet    = 'UTF-8';
        $mail->SMTPDebug  = 0; // Set to 2 to see full SMTP log in browser

        $mail->setFrom('aa60df001@smtp-brevo.com', 'Smart Municipality');
        $mail->addAddress($toEmail);
        $mail->isHTML(true);

        $count = count($slots);
        $mail->Subject = "Smart Municipality - $count rendez-vous enregistré(s) !";

        // Build the chained slots table
        $slotsHtml = '';
        foreach ($slots as $i => $slot) {
            $num      = $i + 1;
            $service  = htmlspecialchars($slot['service']);
            $heure    = htmlspecialchars(substr($slot['heure'], 0, 5));
            $slotsHtml .= "
            <tr>
                <td style='padding:10px 0;font-size:13px;" . ($i > 0 ? "border-top:1px solid #eee;" : "") . "'>
                    <strong style='color:#1a5c2a;font-size:12px;'>RDV $num</strong><br>
                    <strong style='color:#333;'>&#128203; $service</strong><br>
                    <span style='color:#666;font-size:13px;'>&#9200; $heure</span>
                </td>
            </tr>";
        }

        $dateFormatted = date('d/m/Y', strtotime($date));

        $mail->Body = "
        <html>
        <body style='font-family:Segoe UI,Tahoma,sans-serif;background-color:#f0f0f0;padding:20px;margin:0;'>
            <div style='max-width:520px;margin:0 auto;background:white;border-radius:12px;overflow:hidden;box-shadow:0 2px 10px rgba(0,0,0,0.1);'>

                <div style='background:linear-gradient(135deg,#1a5c2a,#2FA084);color:white;padding:28px;text-align:center;'>
                    <h1 style='font-size:20px;margin:0;'>&#127963; Smart Municipality</h1>
                    <p style='margin:6px 0 0;font-size:13px;opacity:0.85;'>Votre visite multi-services est confirmée</p>
                </div>

                <div style='padding:26px;'>
                    <p style='font-size:15px;color:#333;margin-bottom:6px;'>Bonjour <strong>$prenom</strong>,</p>
                    <p style='font-size:14px;color:#555;'>
                        Votre demande a bien été <strong style='color:#27ae60;'>enregistrée</strong>.
                        Voici le récapitulatif de votre visite du <strong>$dateFormatted</strong> :
                    </p>

                    <div style='background:#f8f9fa;border-radius:10px;padding:18px 20px;margin:20px 0;'>
                        <div style='background:#e8f5e9;border-radius:6px;padding:7px 12px;display:inline-block;margin-bottom:14px;'>
                            <span style='color:#155724;font-weight:bold;font-size:12px;'>
                                &#128197; $dateFormatted &nbsp;·&nbsp; $count service(s)
                            </span>
                        </div>
                        <table style='width:100%;'>
                            $slotsHtml
                        </table>
                    </div>

                    <div style='background:#fff3cd;border-radius:8px;padding:12px 16px;font-size:12.5px;color:#856404;'>
                        &#9888; Chaque rendez-vous dure <strong>20 minutes</strong>.
                        Présentez-vous à l'heure indiquée avec vos documents nécessaires.
                    </div>
                </div>

                <div style='background:#f8f8f8;padding:14px;text-align:center;font-size:11px;color:#999;'>
                    Smart Municipality &copy; 2026 — Ne pas répondre à cet email
                </div>
            </div>
        </body>
        </html>";

        $mail->send();
        return true;

    } catch (Exception $e) {
        return false;
    }
}

if (realpath($_SERVER['SCRIPT_FILENAME']) === realpath(__FILE__)) {
    session_start();

    require_once __DIR__ . '/../config/database.php';
    require_once __DIR__ . '/../PHPMailer/PHPMailer.php';
    require_once __DIR__ . '/../PHPMailer/SMTP.php';
    require_once __DIR__ . '/../PHPMailer/Exception.php';

    $db = new Database();
    $conn = $db->getConnection();
    $rdv = new RendezVous($conn);

    $action = $_POST['action'] ?? $_GET['action'] ?? '';
    $base = '/smart-municipality';

    switch ($action) {

    case 'create_multi':

        $date_rdv = $_POST['date_rdv'] ?? '';
        $slots    = $_POST['slots']    ?? [];

        if (empty($date_rdv) || empty($slots)) {
            $_SESSION['error'] = "Données manquantes.";
            header("Location: $base/views/frontoffice/rendez-vous.php");
            exit;
        }

        $created      = 0;
        $errors       = 0;
        $createdSlots = []; // for email

        foreach ($slots as $slotStr) {
            [$cat_id, $heure] = explode('|', $slotStr, 2);
            $cat_id = (int)$cat_id;

            if (RendezVousController::isSlotTaken($rdv, $cat_id, $date_rdv, $heure)) {
                $errors++;
                continue;
            }

            $rdv->setUserId(1);
            $rdv->setCategorieId($cat_id);
            $rdv->setDateRdv($date_rdv);
            $rdv->setHeure($heure);
            $rdv->setStatut('en_attente');

            if (RendezVousController::create($rdv)) {
                $created++;
                // Get service name for email
                $stmtCat = $conn->prepare("SELECT nom FROM categories WHERE id = :id");
                $stmtCat->execute([':id' => $cat_id]);
                $catRow = $stmtCat->fetch(PDO::FETCH_ASSOC);
                $createdSlots[] = [
                    'service' => $catRow['nom'] ?? 'Service',
                    'heure'   => $heure
                ];
            } else {
                $errors++;
            }
        }

        if ($created > 0) {
            if ($errors === 0) {
                $_SESSION['success'] = "$created rendez-vous enregistré(s) avec succès. En attente de confirmation par l'administration.";
            } else {
                $_SESSION['success'] = "$created rendez-vous enregistré(s). $errors créneau(x) déjà pris — ignoré(s).";
            }
        } else {
            $_SESSION['error'] = "Tous les créneaux sont déjà pris. Veuillez choisir un autre horaire.";
        }

        header("Location: $base/views/frontoffice/rendez-vous.php");
        exit;
        break;

    case 'create':

        $categorie_id = $_POST['categorie_id'] ?? '';
        $date_rdv = $_POST['date_rdv'] ?? '';
        $heure = $_POST['heure'] ?? '';

        if (empty($categorie_id) || empty($date_rdv) || empty($heure)) {
            $_SESSION['error'] = "Veuillez remplir tous les champs.";
            header("Location: $base/views/frontoffice/rendez-vous.php");
            exit;
        }

        if (RendezVousController::isSlotTaken($rdv, $categorie_id, $date_rdv, $heure)) {
            $_SESSION['error'] = "Ce créneau est déjà réservé.";
            header("Location: $base/views/frontoffice/rendez-vous.php?categorie_id=$categorie_id&date=$date_rdv");
            exit;
        }

        $rdv->setUserId(1);
        $rdv->setCategorieId($categorie_id);
        $rdv->setDateRdv($date_rdv);
        $rdv->setHeure($heure);
        $rdv->setStatut('en_attente');

        if (RendezVousController::create($rdv)) {
            $_SESSION['success'] = "Votre rendez-vous a été enregistré avec succès. En attente de confirmation par l'administration.";
        } else {
            $_SESSION['error'] = "Une erreur est survenue.";
        }

        header("Location: $base/views/frontoffice/rendez-vous.php");
        exit;
        break;

    case 'confirm':

        $id = $_GET['id'] ?? '';

        if (empty($id)) {
            $_SESSION['error'] = "Rendez-vous introuvable.";
            header("Location: $base/views/backoffice/rendez-vous.php");
            exit;
        }

        $row = RendezVousController::readOne($rdv, $id);
        if ($row) {
            $rdv->setStatut('confirme');
            if (RendezVousController::update($rdv)) {
                $_SESSION['success'] = "Rendez-vous #$id confirmé.";

                $user = getUserInfo($conn, $row['user_id']);
                if ($user && !empty($user['email'])) {
                    $emailSent = sendMultiConfirmationEmail(
                        $user['email'],
                        $user['prenom'] ?? $user['nom'] ?? 'Citoyen',
                        [[
                            'service' => $row['service_nom'],
                            'heure'   => $row['heure']
                        ]],
                        $row['date_rdv']
                    );
                    if ($emailSent) {
                        $_SESSION['success'] .= " — Email envoyé à " . $user['email'];
                    } else {
                        $errDetail = $_SESSION['email_error'] ?? 'Erreur inconnue';
                        unset($_SESSION['email_error']);
                        $_SESSION['success'] .= " — ⚠️ Email non envoyé : $errDetail";
                    }
                }
            } else {
                $_SESSION['error'] = "Erreur lors de la confirmation.";
            }
        }

        header("Location: $base/views/backoffice/rendez-vous.php");
        exit;
        break;

    case 'cancel':

        $id = $_GET['id'] ?? '';

        if (empty($id)) {
            $_SESSION['error'] = "Rendez-vous introuvable.";
            header("Location: $base/views/backoffice/rendez-vous.php");
            exit;
        }

        $row = RendezVousController::readOne($rdv, $id);
        if ($row) {
            $rdv->setStatut('annule');
            if (RendezVousController::update($rdv)) {
                $_SESSION['success'] = "Rendez-vous #$id annulé.";
            } else {
                $_SESSION['error'] = "Erreur lors de l'annulation.";
            }
        }

        header("Location: $base/views/backoffice/rendez-vous.php");
        exit;
        break;

    case 'delete':

        $id = $_GET['id'] ?? $_POST['id'] ?? '';
        $from = $_GET['from'] ?? $_POST['from'] ?? 'back';

        if (empty($id)) {
            $_SESSION['error'] = "Rendez-vous introuvable.";
            if ($from == 'front') {
                header("Location: $base/views/frontoffice/rendez-vous.php");
            } else {
                header("Location: $base/views/backoffice/rendez-vous.php");
            }
            exit;
        }

        if (RendezVousController::delete($rdv, $id)) {
            $_SESSION['success'] = "Rendez-vous supprimé.";
        } else {
            $_SESSION['error'] = "Erreur lors de la suppression.";
        }

        if ($from == 'front') {
            header("Location: $base/views/frontoffice/rendez-vous.php");
        } else {
            header("Location: $base/views/backoffice/rendez-vous.php");
        }
        exit;
        break;

    case 'update':

        $id = $_POST['id'] ?? '';
        $categorie_id = $_POST['categorie_id'] ?? '';
        $date_rdv = $_POST['date_rdv'] ?? '';
        $heure = $_POST['heure'] ?? '';
        $statut = $_POST['statut'] ?? 'en_attente';

        if (empty($id) || empty($categorie_id) || empty($date_rdv) || empty($heure)) {
            $_SESSION['error'] = "Veuillez remplir tous les champs.";
            header("Location: $base/views/backoffice/rendez-vous.php");
            exit;
        }

        $rdv->setId($id);
        $rdv->setCategorieId($categorie_id);
        $rdv->setDateRdv($date_rdv);
        $rdv->setHeure($heure);
        $rdv->setStatut($statut);

        if (RendezVousController::update($rdv)) {
            $_SESSION['success'] = "Rendez-vous modifié.";
        } else {
            $_SESSION['error'] = "Erreur lors de la modification.";
        }

        header("Location: $base/views/backoffice/rendez-vous.php");
        exit;
        break;

    case 'create_category':

        $nom = trim($_POST['nom'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $icone = trim($_POST['icone'] ?? 'rdv.svg');

        if (empty($nom)) {
            $_SESSION['error'] = "Le nom de la catégorie est requis.";
            header("Location: $base/views/backoffice/categories.php?new=1");
            exit;
        }

        $newId = RendezVousController::createCategory($rdv, $nom, $description, $icone);
        if ($newId) {
            $_SESSION['success'] = 'Catégorie "' . $nom . '" créée avec succès.';
            header("Location: $base/views/backoffice/categories.php?edit=" . $newId);
        } else {
            $_SESSION['error'] = "Erreur lors de la création.";
            header("Location: $base/views/backoffice/categories.php?new=1");
        }
        exit;
        break;

    case 'update_category':

        $id = (int)($_POST['id'] ?? 0);
        $nom = trim($_POST['nom'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $icone = trim($_POST['icone'] ?? 'rdv.svg');

        if ($id <= 0 || empty($nom)) {
            $_SESSION['error'] = "Données invalides.";
            header("Location: $base/views/backoffice/categories.php");
            exit;
        }

        if (RendezVousController::updateCategory($rdv, $id, $nom, $description, $icone)) {
            $_SESSION['success'] = "Catégorie mise à jour.";
        } else {
            $_SESSION['error'] = "Erreur de mise à jour.";
        }
        header("Location: $base/views/backoffice/categories.php?edit=" . $id);
        exit;
        break;

    case 'delete_category':

        $id = (int)($_GET['id'] ?? 0);

        if ($id <= 0) {
            $_SESSION['error'] = "Catégorie introuvable.";
            header("Location: $base/views/backoffice/categories.php");
            exit;
        }

        try {
            if (RendezVousController::deleteCategory($rdv, $id)) {
                $_SESSION['success'] = "Catégorie supprimée.";
            } else {
                $_SESSION['error'] = "Erreur lors de la suppression.";
            }
        } catch (PDOException $e) {
            if (strpos($e->getMessage(), '1451') !== false || stripos($e->getMessage(), 'foreign key') !== false) {
                $_SESSION['error'] = "Impossible de supprimer : des rendez-vous sont encore liés à cette catégorie.";
            } else {
                $_SESSION['error'] = "Erreur de suppression : " . $e->getMessage();
            }
        }
        header("Location: $base/views/backoffice/categories.php");
        exit;
        break;

    default:
        header("Location: $base/views/frontoffice/rendez-vous.php");
        exit;
        break;

    }
}

?>