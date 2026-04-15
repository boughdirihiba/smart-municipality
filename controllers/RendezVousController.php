<?php

session_start();

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/RendezVous.php';
require_once __DIR__ . '/../PHPMailer/PHPMailer.php';
require_once __DIR__ . '/../PHPMailer/SMTP.php';
require_once __DIR__ . '/../PHPMailer/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

$db = new Database();
$conn = $db->getConnection();
$rdv = new RendezVous($conn);

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$base = '/smart-municipality';

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
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'replay.smartmunicipality@gmail.com';
        $mail->Password   = 'oxmu xojt xnew okbx';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;
        $mail->CharSet    = 'UTF-8';

        $mail->setFrom('replay.smartmunicipality@gmail.com', 'Smart Municipality');
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
        return false;
    }
}

switch ($action) {

    case 'create':

        $categorie_id = $_POST['categorie_id'] ?? '';
        $date_rdv = $_POST['date_rdv'] ?? '';
        $heure = $_POST['heure'] ?? '';

        if (empty($categorie_id) || empty($date_rdv) || empty($heure)) {
            $_SESSION['error'] = "Veuillez remplir tous les champs.";
            header("Location: $base/views/frontoffice/rendez-vous.php");
            exit;
        }

        if ($rdv->isSlotTaken($categorie_id, $date_rdv, $heure)) {
            $_SESSION['error'] = "Ce créneau est déjà réservé.";
            header("Location: $base/views/frontoffice/rendez-vous.php?categorie_id=$categorie_id&date=$date_rdv");
            exit;
        }

        $rdv->setUserId(1);
        $rdv->setCategorieId($categorie_id);
        $rdv->setDateRdv($date_rdv);
        $rdv->setHeure($heure);
        $rdv->setStatut('en_attente');

        if ($rdv->create()) {
            $_SESSION['success'] = "Votre rendez-vous a été enregistré avec succès.";
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

        $row = $rdv->readOne($id);
        if ($row) {
            $rdv->setStatut('confirme');
            if ($rdv->update()) {
                $_SESSION['success'] = "Rendez-vous #$id confirmé.";

                $user = getUserInfo($conn, $row['user_id']);
                if ($user && $user['email']) {
                    $emailSent = sendConfirmationEmail(
                        $user['email'],
                        $user['prenom'],
                        $row['service_nom'],
                        $row['date_rdv'],
                        $row['heure']
                    );
                    if ($emailSent) {
                        $_SESSION['success'] .= " Email de confirmation envoyé à " . $user['email'];
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

        $row = $rdv->readOne($id);
        if ($row) {
            $rdv->setStatut('annule');
            if ($rdv->update()) {
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

        if ($rdv->delete($id)) {
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

        if ($rdv->update()) {
            $_SESSION['success'] = "Rendez-vous modifié.";
        } else {
            $_SESSION['error'] = "Erreur lors de la modification.";
        }

        header("Location: $base/views/backoffice/rendez-vous.php");
        exit;
        break;

    default:
        header("Location: $base/views/frontoffice/rendez-vous.php");
        exit;
        break;

}

?>