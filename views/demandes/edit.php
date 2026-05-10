<?php
if(!isset($demande)) {
    header("Location: index.php?action=manage");
    exit;
}

require_once "controllers/ServiceController.php";
$serviceController = new ServiceController();
$allServices = $serviceController->getServicesFront();
?>

<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
<style>
    .main-container { max-width: 800px; margin: 0 auto; padding: 40px 60px; }
    .form-card { background: white; border-radius: 32px; padding: 40px; box-shadow: 0 20px 35px -12px rgba(0,0,0,0.1); margin-top: 20px; }
    .form-header { text-align: center; margin-bottom: 35px; }
    .form-header h2 { font-size: 28px; font-weight: 700; color: #0f172a; margin-bottom: 10px; }
    .form-header p { color: #64748b; font-size: 14px; }
    .form-header i { background: #d1fae5; padding: 15px; border-radius: 50%; color: #059669; font-size: 24px; margin-bottom: 15px; display: inline-block; }
    .form-group { margin-bottom: 24px; }
    .form-group label { display: block; font-weight: 600; font-size: 14px; color: #334155; margin-bottom: 8px; }
    .form-group label i { color: #10b981; margin-right: 8px; }
    .form-group input, .form-group select, .form-group textarea {
        width: 100%; padding: 14px 18px; border: 2px solid #e2e8f0; border-radius: 16px;
        font-size: 14px; font-family: 'Inter', sans-serif; transition: all 0.3s; background: #fafbfc;
    }
    .form-group input:focus, .form-group select:focus, .form-group textarea:focus {
        outline: none; border-color: #10b981; background: white; box-shadow: 0 0 0 3px rgba(16,185,129,0.1);
    }
    .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
    .badge-id { background: linear-gradient(135deg, #10b981, #059669); color: white; padding: 8px 16px; border-radius: 30px; font-size: 14px; font-weight: 600; display: inline-block; margin-bottom: 20px; }
    .btn-group { display: flex; gap: 15px; margin-top: 16px; }
    .btn-save {
        background: linear-gradient(135deg, #10b981, #059669); color: white; border: none;
        padding: 16px 32px; border-radius: 40px; font-size: 16px; font-weight: 700;
        cursor: pointer; flex: 1; transition: all 0.3s; font-family: 'Inter', sans-serif;
    }
    .btn-save:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(16,185,129,0.3); }
    .btn-cancel {
        background: #f1f5f9; color: #475569; border: 2px solid #e2e8f0;
        padding: 16px 32px; border-radius: 40px; font-size: 16px; font-weight: 600;
        cursor: pointer; flex: 1; transition: all 0.3s; font-family: 'Inter', sans-serif;
        text-align: center; text-decoration: none; display: inline-block;
    }
    .btn-cancel:hover { background: #fee2e2; border-color: #dc2626; color: #dc2626; }
    @media (max-width: 768px) {
        .main-container { padding: 20px; }
        .form-row { grid-template-columns: 1fr; }
        .form-card { padding: 24px; }
        .btn-group { flex-direction: column; }
    }
</style>

<div class="main-container">
    <div class="form-card">
        <div class="form-header">
            <i class="fas fa-edit"></i>
            <h2>Modifier la demande</h2>
            <p>Modifiez les informations de votre demande ci-dessous</p>
        </div>

        <div style="text-align: center; margin-bottom: 25px;">
            <span class="badge-id">
                <i class="fas fa-hashtag"></i> Demande #<?php echo $demande['id']; ?>
            </span>
        </div>

        <form action="index.php?action=update" method="POST">
            <input type="hidden" name="id" value="<?php echo $demande['id']; ?>">

            <div class="form-group">
                <label><i class="fas fa-user"></i> Nom complet *</label>
                <input type="text" name="nom" value="<?php echo htmlspecialchars($demande['nom']); ?>" required placeholder="Votre nom et prénom">
            </div>

            <div class="form-group">
                <label><i class="fas fa-concierge-bell"></i> Type de service *</label>
                <select name="type_service" required>
                    <option value="">-- Sélectionnez un service --</option>
                    <?php foreach($allServices as $service): ?>
                        <option value="<?php echo htmlspecialchars($service['nom']); ?>"
                            <?php echo ($demande['type_service'] == $service['nom']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($service['nom']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label><i class="fas fa-file-alt"></i> Documents requis *</label>
                <textarea name="documents" rows="3" required placeholder="Liste des documents fournis"><?php echo htmlspecialchars($demande['documents']); ?></textarea>
            </div>

            <div class="form-group">
                <label><i class="fas fa-calendar-alt"></i> Date de création *</label>
                <input type="date" name="date_creation" value="<?php echo $demande['date_creation']; ?>" required>
            </div>

            <div class="btn-group">
                <button type="submit" class="btn-save">
                    <i class="fas fa-save"></i> Enregistrer les modifications
                </button>
                <a href="index.php?action=manage" class="btn-cancel">
                    <i class="fas fa-times"></i> Annuler
                </a>
            </div>
        </form>
    </div>
</div>
