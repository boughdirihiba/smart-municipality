<?php
// Variables provided by legacy_router: $rdv_data (array), $categories_edit (array), $title
$rdv = $rdv_data ?? [];
$cats = $categories_edit ?? [];
?>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<style>
    :root { --forest: #0B4F30; --emerald: #1A7A4E; --mint: #3DDC84; --sage: #E8F5E9; }
    .edit-card {
        max-width: 560px;
        margin: 40px auto;
        background: white;
        border-radius: 20px;
        box-shadow: 0 4px 24px rgba(11,79,48,0.10);
        overflow: hidden;
    }
    .edit-header {
        background: linear-gradient(135deg, var(--forest), var(--emerald));
        color: white;
        padding: 28px 32px 20px;
    }
    .edit-header h2 { font-size: 1.25rem; font-weight: 700; margin: 0 0 4px; }
    .edit-header p  { font-size: 0.85rem; opacity: 0.8; margin: 0; }
    .edit-body { padding: 28px 32px 32px; }
    .form-label { font-size: 0.82rem; font-weight: 600; color: #374151; margin-bottom: 6px; }
    .form-control, .form-select {
        border: 1.5px solid #d1fae5;
        border-radius: 10px;
        font-size: 0.88rem;
        padding: 10px 14px;
        transition: border-color 0.2s;
    }
    .form-control:focus, .form-select:focus {
        border-color: var(--emerald);
        box-shadow: 0 0 0 3px rgba(26,122,78,0.12);
    }
    .mb-group { margin-bottom: 20px; }
    .btn-save {
        background: linear-gradient(135deg, var(--forest), var(--emerald));
        color: white;
        border: none;
        border-radius: 40px;
        padding: 11px 32px;
        font-size: 0.9rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
    }
    .btn-save:hover { transform: translateY(-2px); box-shadow: 0 6px 18px rgba(11,79,48,0.25); }
    .btn-back {
        color: var(--forest);
        text-decoration: none;
        font-size: 0.85rem;
        font-weight: 500;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }
    .btn-back:hover { color: var(--emerald); }
    .status-badge {
        display: inline-block;
        background: var(--sage);
        color: var(--forest);
        font-size: 0.75rem;
        font-weight: 600;
        padding: 4px 12px;
        border-radius: 20px;
        margin-top: 6px;
    }
</style>

<?php if (!empty($flash)): ?>
<div style="max-width:560px;margin:20px auto 0;">
    <div class="alert alert-<?php echo htmlspecialchars($flash['type'] === 'error' ? 'danger' : $flash['type']); ?> alert-dismissible fade show">
        <?php echo htmlspecialchars($flash['message']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
</div>
<?php endif; ?>

<div class="edit-card">
    <div class="edit-header">
        <h2><i class="fas fa-calendar-edit me-2"></i>Modifier le rendez-vous</h2>
        <p>Les modifications remettent le statut à "En attente de confirmation"</p>
    </div>
    <div class="edit-body">
        <form method="POST" action="<?php echo BASE_URL; ?>/index.php?action=rdv_edit&id=<?php echo (int)$rdv['id']; ?>">

            <div class="mb-group">
                <label class="form-label"><i class="fas fa-concierge-bell me-1"></i>Service</label>
                <select name="categorie_id" class="form-select" required>
                    <option value="">-- Choisir un service --</option>
                    <?php foreach ($cats as $cat): ?>
                    <option value="<?php echo (int)$cat['id']; ?>"
                        <?php echo ((int)$rdv['categorie_id'] === (int)$cat['id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($cat['nom']); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="mb-group">
                <label class="form-label"><i class="fas fa-calendar-day me-1"></i>Date</label>
                <input type="date" name="date_rdv" class="form-control"
                       value="<?php echo htmlspecialchars($rdv['date_rdv'] ?? ''); ?>"
                       min="<?php echo date('Y-m-d'); ?>" required>
            </div>

            <div class="mb-group">
                <label class="form-label"><i class="fas fa-clock me-1"></i>Heure</label>
                <input type="time" name="heure" class="form-control"
                       value="<?php echo htmlspecialchars(substr($rdv['heure'] ?? '', 0, 5)); ?>"
                       min="08:00" max="17:00" required>
            </div>

            <div style="margin-bottom:24px;">
                <span class="status-badge"><i class="fas fa-info-circle me-1"></i>Statut actuel : <?php echo ucfirst(str_replace('_', ' ', $rdv['statut'] ?? '')); ?></span>
            </div>

            <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;">
                <a href="<?php echo BASE_URL; ?>/index.php?action=rendez_vous" class="btn-back">
                    <i class="fas fa-arrow-left"></i> Retour à mes rendez-vous
                </a>
                <button type="submit" class="btn-save">
                    <i class="fas fa-save me-1"></i> Enregistrer les modifications
                </button>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
