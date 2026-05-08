<?php
// RendezVous - create new
$pageTitle = $pageTitle ?? 'Prendre rendez-vous';
?>

<div class="container mt-5">
    <div class="row">
        <div class="col-md-6 offset-md-3">
            <a href="<?php echo BASE_URL; ?>/index.php?route=rendez_vous/myAppointments" class="btn btn-secondary mb-3">
                <i class="fas fa-arrow-left"></i> Retour
            </a>

            <h1><?php echo htmlspecialchars($pageTitle); ?></h1>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
                </div>
            <?php endif; ?>

            <form method="POST" class="card p-4">
                <div class="mb-3">
                    <label for="categorie_id" class="form-label">Service</label>
                    <select class="form-select" id="categorie_id" name="categorie_id" required onchange="loadAvailableSlots()">
                        <option value="">-- Sélectionner un service --</option>
                        <!-- Options will be populated by JavaScript or from controller -->
                    </select>
                </div>

                <div class="mb-3">
                    <label for="date_rdv" class="form-label">Date</label>
                    <input type="date" class="form-control" id="date_rdv" name="date_rdv" required onchange="loadAvailableSlots()">
                </div>

                <div class="mb-3">
                    <label for="heure" class="form-label">Heure</label>
                    <select class="form-select" id="heure" name="heure" required>
                        <option value="">-- Sélectionner une heure --</option>
                        <option value="09:00">09:00</option>
                        <option value="10:00">10:00</option>
                        <option value="11:00">11:00</option>
                        <option value="14:00">14:00</option>
                        <option value="15:00">15:00</option>
                        <option value="16:00">16:00</option>
                    </select>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-calendar"></i> Réserver
                    </button>
                    <a href="<?php echo BASE_URL; ?>/index.php?route=rendez_vous/myAppointments" class="btn btn-secondary">
                        Annuler
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function loadAvailableSlots() {
    const categoryId = document.getElementById('categorie_id').value;
    const date = document.getElementById('date_rdv').value;
    
    if (categoryId && date) {
        fetch('<?php echo BASE_URL; ?>/index.php?route=rendez_vous/getSlots&category_id=' + categoryId + '&date=' + date)
            .then(response => response.json())
            .then(data => {
                const select = document.getElementById('heure');
                const currentValue = select.value;
                select.innerHTML = '<option value="">-- Sélectionner une heure --</option>';
                
                if (data.slots && data.slots.length > 0) {
                    data.slots.forEach(slot => {
                        const option = document.createElement('option');
                        option.value = slot;
                        option.textContent = slot;
                        select.appendChild(option);
                    });
                    if (currentValue && data.slots.includes(currentValue)) {
                        select.value = currentValue;
                    }
                }
            });
    }
}
</script>
