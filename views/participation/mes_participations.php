<?php
session_start();
require_once __DIR__ . '/../../controller/ParticipationC.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../index.php');
    exit();
}

$participationC = new ParticipationC();
$participations = $participationC->afficherParticipationsParUser($_SESSION['user_id']);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mes participations - Smart Municipality</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background: #e8f5e9; }
        .navbar { background: white; box-shadow: 0 2px 8px rgba(0,0,0,0.05); padding: 15px 30px; }
        .navbar-brand { font-weight: 700; color: #1a5e2a !important; display: flex; align-items: center; gap: 10px; }
        .navbar-brand img { border-radius: 10px; }
        .content-container { background: white; border-radius: 20px; padding: 30px; margin: 30px auto; box-shadow: 0 5px 15px rgba(0,0,0,0.05); max-width: 1200px; }
        .btn { font-family: 'Inter', sans-serif; font-weight: 500; font-size: 0.8rem; padding: 8px 16px; border-radius: 10px; transition: all 0.2s; display: inline-flex; align-items: center; gap: 8px; border: none; cursor: pointer; }
        .btn-sm { font-size: 0.7rem; padding: 5px 12px; gap: 5px; }
        .btn-primary { background: linear-gradient(135deg, #1a5e2a, #4caf50); color: white; }
        .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(26,94,42,0.3); }
        .btn-danger { background: #dc2626; color: white; }
        .btn-danger:hover { background: #b91c1c; transform: translateY(-2px); }
        .btn-info { background: #0891b2; color: white; }
        .btn-info:hover { background: #0e7490; transform: translateY(-2px); }
        .table-wrapper { background: white; border-radius: 16px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.05); }
        .table-pro { width: 100%; border-collapse: collapse; font-size: 0.8rem; }
        .table-pro thead th { background: linear-gradient(135deg, #1a5e2a, #4caf50); color: white; padding: 12px 15px; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px; }
        .table-pro tbody td { padding: 12px 15px; border-bottom: 1px solid #e8f5e9; vertical-align: middle; }
        .table-pro tbody tr:hover { background: #e8f5e9; }
        .badge { padding: 5px 10px; border-radius: 20px; font-size: 0.7rem; font-weight: 600; }
        .empty-state { text-align: center; padding: 50px; }
        .empty-state i { font-size: 3rem; color: #2e7d32; margin-bottom: 15px; }
        @media (max-width: 768px) { .content-container { padding: 15px; } }
    </style>
</head>
<body>
    <nav class="navbar"><div class="container"><a class="navbar-brand" href="../../index.php"><img src="../../logo.jpeg" alt="Logo" height="35">Smart Municipality</a><a href="../../index.php" class="btn btn-primary btn-sm"><i class="fas fa-arrow-left me-1"></i>Retour</a></div></nav>
    <div class="container"><div class="content-container"><h2 class="mb-4"><i class="fas fa-ticket-alt me-2" style="color: #1a5e2a;"></i>Mes participations</h2>
    <?php if (empty($participations)): ?><div class="empty-state"><i class="fas fa-calendar-times"></i><h3>Aucune participation</h3><a href="../../index.php" class="btn btn-primary mt-3">Découvrir</a></div>
    <?php else: ?><div class="table-wrapper"><table class="table-pro"><thead><tr><th>Événement</th><th>Catégorie</th><th>Date</th><th>Heure</th><th>Lieu</th><th>Participants</th><th>Statut</th><th>Actions</th></tr></thead>
    <tbody><?php foreach($participations as $p): ?><tr><td><strong><?php echo htmlspecialchars($p['titre']); ?></strong></td><td><?php echo htmlspecialchars($p['categorie_nom'] ?? 'Non catégorisé'); ?></td><td><?php echo date('d/m/Y', strtotime($p['date_evenement'])); ?></td><td><?php echo $p['heure']; ?></td><td><?php echo htmlspecialchars($p['lieu']); ?></td><td><?php echo $p['nombre_participants']; ?></td><td><?php if($p['statut_validation'] == 'en_attente'): ?><span class="badge bg-warning">En attente</span><?php elseif($p['statut_validation'] == 'valide'): ?><span class="badge bg-success">Validé</span><?php else: ?><span class="badge bg-danger">Refusé</span><?php endif; ?></td><td class="text-nowrap"><?php if($p['statut_validation'] == 'valide'): ?><a href="ticket.php?id=<?php echo $p['id']; ?>" class="btn btn-info btn-sm"><i class="fas fa-ticket-alt"></i> Ticket</a><?php endif; ?><a href="annuler.php?event_id=<?php echo $p['event_id']; ?>" class="btn btn-danger btn-sm ms-1" onclick="return confirm('Annuler ?')"><i class="fas fa-times"></i> Annuler</a></td></tr><?php endforeach; ?></tbody></table></div><?php endif; ?></div></div>
</body>
</html>