<?php
// views/notifications/modal.php
// Modal form for sending a notification to a citizen
if (!isset($demandes)) { $demandes = []; }
?>
<div class="modal fade" id="sendNotificationModal" tabindex="-1" aria-labelledby="sendNotifLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title" id="sendNotifLabel">
          <i class="bi bi-bell me-2"></i>Envoyer une notification
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fermer"></button>
      </div>
      <form method="POST" action="index.php?action=notification_send" id="notifForm">
        <div class="modal-body">
          <div class="mb-3">
            <label for="notif_demande_id" class="form-label fw-semibold">Demande concernée</label>
            <select class="form-select" name="demande_id" id="notif_demande_id" required>
              <option value="">— Sélectionner une demande —</option>
              <?php foreach ($demandes as $d): ?>
                <option value="<?= htmlspecialchars((string)$d['id']) ?>">
                  #<?= htmlspecialchars((string)$d['id']) ?> — <?= htmlspecialchars($d['nom'] ?? '') ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="mb-3">
            <label for="notif_message" class="form-label fw-semibold">Message</label>
            <textarea class="form-control" name="message" id="notif_message" rows="4"
                      placeholder="Saisissez votre message ici…" required maxlength="1000"></textarea>
            <div class="form-text text-end"><span id="notifCharCount">0</span>/1000</div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
          <button type="submit" class="btn btn-primary">
            <i class="bi bi-send me-1"></i>Envoyer
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
<script>
(function(){
  var ta = document.getElementById('notif_message');
  var counter = document.getElementById('notifCharCount');
  if(ta && counter){
    ta.addEventListener('input', function(){ counter.textContent = ta.value.length; });
  }
})();
</script>
