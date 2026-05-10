<?php
$demande_id = $doc['demande_id'];
?>

<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
<style>
    .main-container { max-width: 600px; margin: 0 auto; padding: 40px; }
    .edit-container { background: white; border-radius: 32px; padding: 40px; box-shadow: 0 20px 35px -12px rgba(0,0,0,0.1); border: 1px solid #eef2ff; }
    .edit-header { text-align: center; margin-bottom: 30px; }
    .edit-header i { font-size: 55px; background: linear-gradient(135deg,#059669,#10b981); -webkit-background-clip: text; -webkit-text-fill-color: transparent; margin-bottom: 15px; }
    .edit-header h2 { font-size: 24px; color: #0f172a; margin-bottom: 8px; font-weight: 700; }
    .edit-header p { color: #64748b; font-size: 13px; }
    .badge-doc { display: inline-block; background: #ecfdf5; color: #059669; padding: 5px 14px; border-radius: 30px; font-size: 11px; font-weight: 600; margin-top: 12px; }
    .form-group { margin-bottom: 22px; }
    label { display: block; font-weight: 600; margin-bottom: 8px; color: #334155; font-size: 13px; }
    label i { color: #059669; margin-right: 6px; }
    input { width: 100%; padding: 12px 16px; border: 2px solid #e2e8f0; border-radius: 14px; font-size: 14px; font-family: 'Inter', sans-serif; transition: all 0.3s; }
    input:focus { outline: none; border-color: #059669; box-shadow: 0 0 0 3px rgba(5,150,105,0.1); }
    .input-info { font-size: 11px; color: #94a3b8; margin-top: 6px; display: flex; align-items: center; gap: 5px; }
    .info-box { background: #f8fafc; border-radius: 16px; padding: 15px; margin-bottom: 22px; border: 1px solid #e2e8f0; }
    .info-item { display: flex; justify-content: space-between; margin-bottom: 10px; font-size: 12px; }
    .info-item:last-child { margin-bottom: 0; }
    .info-label { color: #64748b; font-weight: 500; }
    .info-value { color: #0f172a; font-weight: 600; }
    .btn-group { display: flex; gap: 12px; margin-top: 25px; }
    .btn-save { background: linear-gradient(135deg,#059669,#10b981); color: white; border: none; padding: 12px 20px; border-radius: 40px; cursor: pointer; flex: 1; font-weight: 600; font-size: 13px; display: flex; align-items: center; justify-content: center; gap: 8px; transition: all 0.3s; }
    .btn-save:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(5,150,105,0.3); }
    .btn-cancel { background: #f1f5f9; color: #475569; border: none; padding: 12px 20px; border-radius: 40px; cursor: pointer; flex: 1; text-align: center; text-decoration: none; font-weight: 600; font-size: 13px; display: flex; align-items: center; justify-content: center; gap: 8px; transition: all 0.3s; }
    .btn-cancel:hover { background: #fee2e2; color: #dc2626; }
    .filename-wrapper { display: flex; align-items: center; gap: 8px; }
    .filename-wrapper input { flex: 1; }
    .ext-badge { background: #f1f5f9; padding: 8px 14px; border-radius: 12px; font-size: 12px; font-weight: 600; color: #059669; border: 1px solid #e2e8f0; }
    @media (max-width: 650px) { .main-container { padding: 20px; } .edit-container { padding: 25px; } .btn-group { flex-direction: column; } }
</style>

<main class="main-container">
    <div class="edit-container">
        <div class="edit-header">
            <i class="fas fa-file-alt"></i>
            <h2>Modification du fichier</h2>
            <p>Choisissez un nouveau nom pour votre document</p>
            <span class="badge-doc">
                <i class="fas fa-folder-open"></i> Document #<?php echo $doc['id']; ?>
            </span>
        </div>

        <form action="index.php?action=update_document" method="POST">
            <input type="hidden" name="id" value="<?php echo $doc['id']; ?>">
            <input type="hidden" name="demande_id" value="<?php echo $doc['demande_id']; ?>">

            <div class="form-group">
                <label><i class="fas fa-tag"></i> Nom du fichier</label>
                <div class="filename-wrapper">
                    <input type="text" name="nom_fichier" value="<?php echo htmlspecialchars(pathinfo($doc['nom_fichier'], PATHINFO_FILENAME)); ?>" required placeholder="Nouveau nom du fichier">
                    <span class="ext-badge">.<?php echo pathinfo($doc['nom_fichier'], PATHINFO_EXTENSION); ?></span>
                </div>
                <div class="input-info">
                    <i class="fas fa-info-circle"></i> Modifiez uniquement le nom, l'extension est conservée automatiquement
                </div>
            </div>

            <div class="info-box">
                <div class="info-item">
                    <span class="info-label"><i class="fas fa-file"></i> Fichier actuel :</span>
                    <span class="info-value"><?php echo htmlspecialchars($doc['nom_fichier']); ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label"><i class="fas fa-file-alt"></i> Type :</span>
                    <span class="info-value"><?php echo strtoupper(pathinfo($doc['nom_fichier'], PATHINFO_EXTENSION)); ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label"><i class="fas fa-weight-hanging"></i> Taille :</span>
                    <span class="info-value"><?php echo round($doc['taille']/1024, 2); ?> KB</span>
                </div>
                <div class="info-item">
                    <span class="info-label"><i class="fas fa-calendar-alt"></i> Date d'upload :</span>
                    <span class="info-value"><?php echo date('d/m/Y H:i', strtotime($doc['uploaded_at'])); ?></span>
                </div>
            </div>

            <div class="btn-group">
                <button type="submit" class="btn-save">
                    <i class="fas fa-save"></i> Enregistrer
                </button>
                <a href="index.php?action=manage" class="btn-cancel">
                    <i class="fas fa-times"></i> Annuler
                </a>
            </div>
        </form>
    </div>
</main>
