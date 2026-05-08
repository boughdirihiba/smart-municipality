<?php
session_start();
require_once __DIR__ . '/../../controller/ParticipationC.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../index.php');
    exit();
}

$ticket_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$participationC = new ParticipationC();
$ticket = $participationC->getParticipationById($ticket_id);

if (!$ticket || $ticket['user_id'] != $_SESSION['user_id']) {
    header('Location: ../../index.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon ticket - Smart Municipality</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { background: linear-gradient(135deg, #e8f5e9, #f0f4f0); font-family: 'Inter', sans-serif; padding: 40px; }
        .ticket { max-width: 550px; margin: 0 auto; background: white; border-radius: 28px; box-shadow: 0 20px 40px rgba(0,0,0,0.15); overflow: hidden; }
        .ticket-header { background: linear-gradient(135deg, #1a5e2a, #4caf50); color: white; padding: 25px; text-align: center; }
        .ticket-header i { font-size: 2.5rem; }
        .ticket-body { padding: 25px; }
        .info-row { display: flex; justify-content: space-between; padding: 12px 0; border-bottom: 1px solid #f0f0f0; }
        .info-label { font-weight: 600; color: #555; }
        .info-value { color: #333; font-weight: 500; }
        .badge { padding: 5px 12px; border-radius: 20px; font-size: 0.75rem; font-weight: 600; }
        .btn { font-family: 'Inter', sans-serif; font-weight: 500; font-size: 0.8rem; padding: 10px; border-radius: 12px; transition: all 0.2s; display: inline-flex; align-items: center; justify-content: center; gap: 8px; border: none; cursor: pointer; width: 100%; margin-top: 15px; }
        .btn-primary { background: linear-gradient(135deg, #1a5e2a, #4caf50); color: white; }
        .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(26,94,42,0.3); }
        .btn-secondary { background: #6c757d; color: white; }
        .footer-note { background: #e8f5e9; padding: 12px 20px; text-align: center; font-size: 0.7rem; color: #1a5e2a; border-top: 1px solid rgba(76,175,80,0.2); }
        @media print { body { background: white; padding: 0; } .btn, .btn-primary { display: none; } .ticket { box-shadow: none; } }
    </style>
</head>
<body>
    <div class="ticket">
        <div class="ticket-header"><i class="fas fa-ticket-alt"></i><h2 class="mt-2">Ticket d'entrée</h2><p>Smart Municipality</p></div>
        <div class="ticket-body">
            <div class="info-row"><span class="info-label"><i class="fas fa-hashtag me-2 text-success"></i>Ticket N°</span><span class="info-value">#<?php echo str_pad($ticket['id'], 8, '0', STR_PAD_LEFT); ?></span></div>
            <div class="info-row"><span class="info-label"><i class="fas fa-user me-2 text-success"></i>Participant</span><span class="info-value"><?php echo htmlspecialchars($ticket['prenom'] . ' ' . $ticket['nom']); ?></span></div>
            <div class="info-row"><span class="info-label"><i class="fas fa-envelope me-2 text-success"></i>Email</span><span class="info-value"><?php echo htmlspecialchars($ticket['email']); ?></span></div>
            <div class="info-row"><span class="info-label"><i class="fas fa-calendar-alt me-2 text-success"></i>Événement</span><span class="info-value"><?php echo htmlspecialchars($ticket['titre']); ?></span></div>
            <div class="info-row"><span class="info-label"><i class="fas fa-map-marker-alt me-2 text-success"></i>Lieu</span><span class="info-value"><?php echo htmlspecialchars($ticket['lieu']); ?></span></div>
            <div class="info-row"><span class="info-label"><i class="fas fa-calendar-day me-2 text-success"></i>Date</span><span class="info-value"><?php echo date('d/m/Y', strtotime($ticket['date_evenement'])); ?></span></div>
            <div class="info-row"><span class="info-label"><i class="fas fa-clock me-2 text-success"></i>Heure</span><span class="info-value"><?php echo $ticket['heure']; ?></span></div>
            <div class="info-row"><span class="info-label"><i class="fas fa-users me-2 text-success"></i>Personnes</span><span class="info-value"><?php echo $ticket['nombre_participants']; ?> personne(s)</span></div>
            <div class="info-row"><span class="info-label"><i class="fas fa-check-circle me-2 text-success"></i>Statut</span><span class="info-value"><span class="badge bg-success"><?php echo $ticket['statut_validation'] == 'valide' ? 'Validé ✅' : 'En attente'; ?></span></span></div>
            <button class="btn btn-primary" onclick="window.print()"><i class="fas fa-print me-2"></i>Imprimer / Sauvegarder PDF</button>
            <a href="../../index.php" class="btn btn-secondary" style="text-decoration: none;"><i class="fas fa-home me-2"></i>Retour à l'accueil</a>
        </div>
        <div class="footer-note"><i class="fas fa-info-circle me-1"></i>Ce ticket est nominatif. Présentez-le à l'entrée avec une pièce d'identité.</div>
    </div>
</body>
</html>