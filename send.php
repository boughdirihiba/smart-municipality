<?php
// send.php - Envoyer une notification
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once "config/database.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: index.php?action=dashboard");
    exit();
}

if (!isset($_POST['demande_id']) || empty($_POST['demande_id'])) {
    $_SESSION['error_message'] = "Veuillez sélectionner une demande";
    header("Location: index.php?action=dashboard");
    exit();
}

if (!isset($_POST['message']) || empty(trim($_POST['message']))) {
    $_SESSION['error_message'] = "Veuillez saisir un message";
    header("Location: index.php?action=dashboard");
    exit();
}

$demande_id = intval($_POST['demande_id']);
$message = trim($_POST['message']);

$database = new Database();
$db = $database->connect();

// Récupérer le user_id (l'ID du citoyen qui a fait la demande)
$sqlUser = "SELECT user_id, nom FROM demandes WHERE id = :id";
$stmtUser = $db->prepare($sqlUser);
$stmtUser->bindParam(":id", $demande_id);
$stmtUser->execute();
$demande = $stmtUser->fetch(PDO::FETCH_ASSOC);

if (!$demande) {
    $_SESSION['error_message'] = "Demande non trouvée";
    header("Location: index.php?action=dashboard");
    exit();
}

// Si la demande n'a pas de user_id, on met 1 par défaut
$user_id = !empty($demande['user_id']) ? $demande['user_id'] : 1;

try {
    // Requête sans 'id' car AUTO_INCREMENT
    $sql = "INSERT INTO notifications (user_id, message, statut, date_creation) 
            VALUES (:user_id, :message, 'non_lu', NOW())";
    $stmt = $db->prepare($sql);
    $stmt->bindParam(":user_id", $user_id);
    $stmt->bindParam(":message", $message);
    
    if ($stmt->execute()) {
        $_SESSION['success_message'] = "✅ Notification envoyée avec succès à " . htmlspecialchars($demande['nom']);
    } else {
        $_SESSION['error_message'] = "❌ Erreur lors de l'envoi";
    }
} catch(PDOException $e) {
    $_SESSION['error_message'] = "❌ Erreur: " . $e->getMessage();
}

// Rediriger vers le dashboard
header("Location: index.php?action=dashboard");
exit();
?>