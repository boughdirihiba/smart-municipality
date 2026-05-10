<?php
// Variables provided by legacy_router: $categories, $ev_errors, $ev_old, $ev_id, $ev_evenement
$ev_errors = $ev_errors ?? [];
$ev_old    = $ev_old    ?? [];
?>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<style>
    .form-container { max-width: 800px; margin: 0 auto; background: white; border-radius: 20px; overflow: hidden; box-shadow: 0 5px 20px rgba(0,0,0,0.05); }
    .form-header { background: linear-gradient(135deg, #1a5e2a, #4caf50); padding: 20px; color: white; text-align: center; }
    .form-header h2 { margin: 0; font-size: 1.3rem; }
    .form-header p  { margin: 5px 0 0; opacity: 0.9; font-size: 0.8rem; }
    .form-body { padding: 25px; }
    .ev-label { font-weight: 600; color: #1a5e2a; font-size: 0.85rem; margin-bottom: 8px; display: block; }
    .ev-label.required::after { content: " *"; color: #dc3545; }
    .ev-input, .ev-select, .ev-textarea {
        width: 100%; border: 2px solid #e9ecef; border-radius: 10px;
        padding: 10px 15px; font-size: 0.85rem; font-family: inherit;
        transition: border-color 0.2s;
    }
    .ev-input:focus, .ev-select:focus, .ev-textarea:focus { border-color: #1a5e2a; outline: none; box-shadow: 0 0 0 3px rgba(26,94,42,0.1); }
    .is-invalid { border-color: #dc3545 !important; }
    .ev-error { color: #dc3545; font-size: 0.75rem; margin-top: 5px; }
    .ev-row { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
    .ev-btn-group { display: flex; gap: 12px; margin-top: 8px; }
    .ev-btn-save { background: linear-gradient(135deg, #1a5e2a, #4caf50); border: none; padding: 10px 20px; border-radius: 10px; font-weight: 600; color: white; cursor: pointer; flex: 1; }
    .ev-btn-save:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(26,94,42,0.3); }
    .ev-btn-cancel { background: #6c757d; border: none; padding: 10px 20px; border-radius: 10px; font-weight: 600; color: white; text-decoration: none; text-align: center; flex: 1; }
    .ev-btn-cancel:hover { background: #5a6268; color: white; }
    @media (max-width: 600px) { .ev-row { grid-template-columns: 1fr; } }
</style>

<div class="form-container">
    <div class="form-header">
        <h2><i class="fas fa-edit me-2"></i>Modifier l'événement</h2>
        <p>Modifiez les informations de l'événement</p>
    </div>
    <div class="form-body">
        <?php if (!empty($ev_errors['global'])): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($ev_errors['global']); ?></div>
        <?php endif; ?>

        <form method="POST" action="<?php echo BASE_URL; ?>/index.php?action=modifier_evenement&id=<?php echo $ev_id; ?>">
            <div class="mb-3">
                <label class="ev-label required">Titre</label>
                <input type="text" name="titre" class="ev-input <?php echo isset($ev_errors['titre']) ? 'is-invalid' : ''; ?>"
                       value="<?php echo htmlspecialchars($ev_old['titre'] ?? ''); ?>">
                <?php if (isset($ev_errors['titre'])): ?><div class="ev-error"><?php echo $ev_errors['titre']; ?></div><?php endif; ?>
            </div>

            <div class="mb-3">
                <label class="ev-label required">Description</label>
                <textarea name="description" rows="4" class="ev-textarea <?php echo isset($ev_errors['description']) ? 'is-invalid' : ''; ?>"><?php echo htmlspecialchars($ev_old['description'] ?? ''); ?></textarea>
                <?php if (isset($ev_errors['description'])): ?><div class="ev-error"><?php echo $ev_errors['description']; ?></div><?php endif; ?>
            </div>

            <div class="ev-row mb-3">
                <div>
                    <label class="ev-label required">Participants max</label>
                    <input type="number" name="max_participants" class="ev-input <?php echo isset($ev_errors['max_participants']) ? 'is-invalid' : ''; ?>"
                           value="<?php echo htmlspecialchars($ev_old['max_participants'] ?? 50); ?>">
                    <?php if (isset($ev_errors['max_participants'])): ?><div class="ev-error"><?php echo $ev_errors['max_participants']; ?></div><?php endif; ?>
                </div>
                <div>
                    <label class="ev-label required">Lieu</label>
                    <input type="text" name="lieu" class="ev-input <?php echo isset($ev_errors['lieu']) ? 'is-invalid' : ''; ?>"
                           value="<?php echo htmlspecialchars($ev_old['lieu'] ?? ''); ?>">
                    <?php if (isset($ev_errors['lieu'])): ?><div class="ev-error"><?php echo $ev_errors['lieu']; ?></div><?php endif; ?>
                </div>
            </div>

            <div class="ev-row mb-3">
                <div>
                    <label class="ev-label required">Date</label>
                    <input type="date" name="date_evenement" class="ev-input <?php echo isset($ev_errors['date_evenement']) ? 'is-invalid' : ''; ?>"
                           value="<?php echo htmlspecialchars($ev_old['date_evenement'] ?? ''); ?>">
                    <?php if (isset($ev_errors['date_evenement'])): ?><div class="ev-error"><?php echo $ev_errors['date_evenement']; ?></div><?php endif; ?>
                </div>
                <div>
                    <label class="ev-label required">Heure</label>
                    <input type="time" name="heure" class="ev-input <?php echo isset($ev_errors['heure']) ? 'is-invalid' : ''; ?>"
                           value="<?php echo htmlspecialchars($ev_old['heure'] ?? ''); ?>">
                    <?php if (isset($ev_errors['heure'])): ?><div class="ev-error"><?php echo $ev_errors['heure']; ?></div><?php endif; ?>
                </div>
            </div>

            <div class="mb-4">
                <label class="ev-label required">Catégorie</label>
                <select name="categorie_id" class="ev-select <?php echo isset($ev_errors['categorie_id']) ? 'is-invalid' : ''; ?>">
                    <option value="">-- Sélectionner une catégorie --</option>
                    <?php foreach ($categories as $cat): ?>
                    <option value="<?php echo $cat['id']; ?>" <?php echo ($ev_old['categorie_id'] ?? 0) == $cat['id'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($cat['nom']); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
                <?php if (isset($ev_errors['categorie_id'])): ?><div class="ev-error"><?php echo $ev_errors['categorie_id']; ?></div><?php endif; ?>
            </div>

            <div class="ev-btn-group">
                <a href="<?php echo BASE_URL; ?>/index.php?action=evenements" class="ev-btn-cancel">
                    <i class="fas fa-arrow-left me-1"></i> Annuler
                </a>
                <button type="submit" class="ev-btn-save">
                    <i class="fas fa-save me-1"></i> Modifier
                </button>
            </div>
        </form>
    </div>
</div>
