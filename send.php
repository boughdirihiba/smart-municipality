<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once "config/database.php";
require_once "config/Language.php";  // ← AJOUTER CETTE LIGNE

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
$document_id = isset($_POST['document_id']) && !empty($_POST['document_id']) ? intval($_POST['document_id']) : null;

$database = new Database();
$db = $database->connect();

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

$user_id = !empty($demande['user_id']) ? $demande['user_id'] : 1;

try {
    $sql = "INSERT INTO notifications (user_id, message, statut, date_creation, demande_id, document_id) 
            VALUES (:user_id, :message, 'non_lu', NOW(), :demande_id, :document_id)";
    $stmt = $db->prepare($sql);
    $stmt->bindParam(":user_id", $user_id);
    $stmt->bindParam(":message", $message);
    $stmt->bindParam(":demande_id", $demande_id);
    $stmt->bindParam(":document_id", $document_id);
    
    if ($stmt->execute()) {
        $_SESSION['success_message'] = "✅ " . __('success_created');
    } else {
        $_SESSION['error_message'] = "❌ " . __('error_occurred');
    }
} catch(PDOException $e) {
    $_SESSION['error_message'] = "❌ Erreur: " . $e->getMessage();
}

header("Location: index.php?action=dashboard");
exit();
?>