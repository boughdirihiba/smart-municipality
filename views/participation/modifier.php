<?php
session_start();
include_once '../../controller/ParticipationC.php';
include_once '../../model/Participation.php';

$participationC = new ParticipationC();
$id = $_GET['id'] ?? null;
if (!$id) header('Location: ../dashboard/admin.php?tab=participations');

$participation = $participationC->afficherParticipationParId($id);
if (!$participation) header('Location: ../dashboard/admin.php?tab=participations');

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre_places = intval($_POST['nombre_places']);
    $commentaire = !empty($_POST['commentaire']) ? $_POST['commentaire'] : null;
    $statut = $_POST['statut'];
    
    $errors = [];
    if ($nombre_places < 1) $errors[] = "Le nombre de places doit être au moins 1.";
    
    if (empty($errors)) {
        $participationObj = new Participation(
            $participation['user_id'],
            $participation['event_id'],
            $statut,
            $nombre_places,
            $commentaire
        );
        $participationC->modifierParticipation($participationObj, $id);
        header('Location: ../dashboard/admin.php?tab=participations&message=participation_modifiee');
        exit();
    } else {
        $error = implode('<br>', $errors);
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier Participation</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background: #f5f5f5; }
        .container { max-width: 800px; margin: 48px auto; padding: 0 24px; }
        .card { background: white; border-radius: 16px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); overflow: hidden; }
        .card-header { padding: 24px; background: linear-gradient(135deg, #17a2b8, #0d47a1); color: white; }
        .card-header h1 { font-size: 24px; margin-bottom: 8px; }
        .card-body { padding: 24px; }
        .form-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 8px; font-weight: 500; }
        input, select, textarea { width: 100%; padding: 12px; border: 1px solid #dadce0; border-radius: 8px; font-family: inherit; }
        input:focus, select:focus, textarea:focus { outline: none; border-color: #1a73e8; }
        .error-message { background: #fce8e6; color: #ea4335; padding: 12px; border-radius: 8px; margin-bottom: 20px; }
        .form-actions { display: flex; gap: 12px; justify-content: flex-end; margin-top: 24px; }
        .btn-primary { background: #1a73e8; color: white; border: none; padding: 12px 24px; border-radius: 24px; cursor: pointer; }
        .btn-secondary { background: transparent; border: 1px solid #dadce0; padding: 12px 24px; border-radius: 24px; cursor: pointer; text-decoration: none; color: #5f6368; }
        .info-box { background: #f8f9fa; padding: 16px; border-radius: 8px; margin-bottom: 24px; }
        .info-box p { margin: 5px 0; }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <div class="card-header">
                <h1><i class="fas fa-edit"></i> Modifier la participation</h1>
                <p>Modifiez les informations de la participation</p>
            </div>
            <div class="card-body">
                <div class="info-box">
                    <p><strong>Utilisateur :</strong> <?php echo htmlspecialchars($participation['user_prenom'] . ' ' . $participation['user_nom']); ?></p>
                    <p><strong>Événement :</strong> <?php echo htmlspecialchars($participation['event_titre']); ?></p>
                    <p><strong>Date événement :</strong> <?php echo date('d/m/Y', strtotime($participation['date_evenement'])); ?></p>
                </div>

                <?php if($error): ?>
                    <div class="error-message"><i class="fas fa-exclamation-circle"></i> <?php echo $error; ?></div>
                <?php endif; ?>

                <form method="POST">
                    <div class="form-group">
                        <label>Nombre de places</label>
                        <input type="number" name="nombre_places" min="1" max="10" required value="<?php echo $participation['nombre_places']; ?>">
                    </div>
                    <div class="form-group">
                        <label>Statut</label>
                        <select name="statut">
                            <option value="inscrit" <?php echo $participation['statut'] == 'inscrit' ? 'selected' : ''; ?>>Inscrit</option>
                            <option value="present" <?php echo $participation['statut'] == 'present' ? 'selected' : ''; ?>>Présent</option>
                            <option value="absent" <?php echo $participation['statut'] == 'absent' ? 'selected' : ''; ?>>Absent</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Commentaire</label>
                        <textarea name="commentaire"><?php echo htmlspecialchars($participation['commentaire'] ?? ''); ?></textarea>
                    </div>
                    <div class="form-actions">
                        <a href="../dashboard/admin.php?tab=participations" class="btn-secondary">Annuler</a>
                        <button type="submit" class="btn-primary"><i class="fas fa-save"></i> Enregistrer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>