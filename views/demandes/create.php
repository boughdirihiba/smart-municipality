<?php
// $service_prefill, $errors, $old_input are set by DemandeController::create()
require_once "config/database.php";
require_once "controllers/ServiceController.php";
$serviceController = new ServiceController();
$allServices = $serviceController->getServicesFront();
$service_prefill = $service_prefill ?? (isset($_GET['service']) ? $_GET['service'] : '');
$errors        = $errors ?? [];
$old_input     = $old_input ?? [];
?>

<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
<style>
    .main-container { max-width: 1200px; margin: 0 auto; padding: 40px 60px; }
    .form-card { background: white; border-radius: 32px; padding: 40px; box-shadow: 0 20px 35px -12px rgba(0,0,0,0.1); margin-top: 20px; }
    .form-header { text-align: center; margin-bottom: 35px; }
    .form-header h2 { font-size: 28px; font-weight: 700; color: #0f172a; margin-bottom: 10px; }
    .form-header p { color: #64748b; font-size: 14px; }
    .form-group { margin-bottom: 24px; }
    .form-group label { display: block; font-weight: 600; font-size: 14px; color: #334155; margin-bottom: 8px; }
    .form-group label i { color: #10b981; margin-right: 8px; }
    .form-group label .required { color: #ef4444; margin-left: 4px; }
    .form-group input, .form-group select, .form-group textarea {
        width: 100%; padding: 14px 18px; border: 2px solid #e2e8f0; border-radius: 16px;
        font-size: 14px; font-family: 'Inter', sans-serif; transition: all 0.3s; background: #fafbfc;
    }
    .form-group input:focus, .form-group select:focus, .form-group textarea:focus {
        outline: none; border-color: #10b981; background: white; box-shadow: 0 0 0 3px rgba(16,185,129,0.1);
    }
    .form-group input.error, .form-group select.error, .form-group textarea.error {
        border-color: #ef4444; background: #fef2f2;
    }
    .error-message { color: #ef4444; font-size: 12px; margin-top: 6px; display: flex; align-items: center; gap: 6px; }
    .success-message { color: #10b981; font-size: 12px; margin-top: 6px; display: flex; align-items: center; gap: 6px; }
    .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
    .btn-submit {
        background: linear-gradient(135deg, #10b981, #059669); color: white; border: none;
        padding: 16px 32px; border-radius: 40px; font-size: 16px; font-weight: 700;
        cursor: pointer; width: 100%; transition: all 0.3s; margin-top: 16px;
    }
    .btn-submit:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(16,185,129,0.3); }
    .btn-submit:disabled { opacity: 0.6; cursor: not-allowed; transform: none; }
    .btn-back {
        background: #f1f5f9; color: #475569; border: none; padding: 12px 24px; border-radius: 40px;
        font-size: 14px; font-weight: 600; cursor: pointer; text-decoration: none; display: inline-block; margin-top: 20px;
    }
    .btn-back:hover { background: #e2e8f0; }
    .alert { padding: 14px 18px; border-radius: 16px; margin-bottom: 24px; display: flex; align-items: center; gap: 12px; font-size: 14px; }
    .alert-error { background: #fef2f2; border: 1px solid #fecaca; color: #dc2626; }
    .alert-success { background: #f0fdf4; border: 1px solid #bbf7d0; color: #059669; }
    .alert ul { margin: 0; padding-left: 20px; }
    @media (max-width: 768px) {
        .main-container { padding: 20px; }
        .form-row { grid-template-columns: 1fr; }
        .form-card { padding: 24px; }
    }
</style>

<div class="main-container">
    <div class="form-card">
        <div class="form-header">
            <h2>Ajouter une demande</h2>
            <p>Tous les champs marqués d'un * sont obligatoires</p>
        </div>

        <?php if(!empty($errors)): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i>
                <ul style="margin: 0; padding-left: 20px;">
                    <?php foreach($errors as $error): ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form id="demandeForm" action="index.php?action=store" method="POST" novalidate>
            <input type="hidden" name="service_id" id="service_id" value="">

            <div class="form-row">
                <div class="form-group">
                    <label><i class="fas fa-hashtag"></i> ID <span class="required">*</span></label>
                    <input type="number" id="id" name="id" placeholder="Numéro d'identification"
                           value="<?php echo isset($old_input['id']) ? htmlspecialchars($old_input['id']) : ''; ?>">
                    <div class="error-message" id="idError"></div>
                </div>

                <div class="form-group">
                    <label><i class="fas fa-user"></i> Nom complet <span class="required">*</span></label>
                    <input type="text" id="nom" name="nom" placeholder="Votre nom et prénom"
                           value="<?php echo isset($old_input['nom']) ? htmlspecialchars($old_input['nom']) : ''; ?>">
                    <div class="error-message" id="nomError"></div>
                    <div class="success-message" id="nomCounter">0 / 50 caractères</div>
                </div>
            </div>

            <div class="form-group">
                <label><i class="fas fa-concierge-bell"></i> Type de service <span class="required">*</span></label>
                <select id="type_service" name="type_service">
                    <option value="">-- Sélectionnez un service --</option>
                    <?php foreach($allServices as $service): ?>
                        <option value="<?php echo htmlspecialchars($service['nom']); ?>"
                            <?php echo ($service_prefill == $service['nom'] ||
                                     (isset($old_input['type_service']) && $old_input['type_service'] == $service['nom']))
                                     ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($service['nom']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <div class="error-message" id="serviceError"></div>
            </div>

            <div class="form-group">
                <label><i class="fas fa-file-alt"></i> Documents requis <span class="required">*</span></label>
                <textarea id="documents" name="documents" placeholder="Liste des documents fournis (ex: CNI, justificatif de domicile, ...)" rows="3" maxlength="40"><?php echo isset($old_input['documents']) ? htmlspecialchars($old_input['documents']) : ''; ?></textarea>
                <div class="error-message" id="documentsError"></div>
                <div class="success-message" id="documentsCounter">0 / 40 caractères</div>
            </div>

            <div class="form-group">
                <label><i class="fas fa-calendar-alt"></i> Date de création</label>
                <input type="date" id="date_creation" name="date_creation"
                       value="<?php echo isset($old_input['date_creation']) ? htmlspecialchars($old_input['date_creation']) : date('Y-m-d'); ?>">
            </div>

            <button type="submit" class="btn-submit" id="submitBtn">
                <i class="fas fa-paper-plane"></i> Envoyer la demande
            </button>
        </form>

        <div style="text-align: center; margin-top: 20px;">
            <a href="index.php?action=manage" class="btn-back">
                <i class="fas fa-arrow-left"></i> Retour à l'accueil
            </a>
        </div>
    </div>
</div>

<script>
    const form = document.getElementById('demandeForm');
    const idInput = document.getElementById('id');
    const nomInput = document.getElementById('nom');
    const serviceSelect = document.getElementById('type_service');
    const documentsInput = document.getElementById('documents');
    const submitBtn = document.getElementById('submitBtn');

    const idError = document.getElementById('idError');
    const nomError = document.getElementById('nomError');
    const serviceError = document.getElementById('serviceError');
    const documentsError = document.getElementById('documentsError');
    const nomCounter = document.getElementById('nomCounter');
    const documentsCounter = document.getElementById('documentsCounter');

    idInput.classList.remove('error');
    nomInput.classList.remove('error');
    serviceSelect.classList.remove('error');
    documentsInput.classList.remove('error');

    function validateId() {
        const id = idInput.value.trim();
        if(id === '') { idError.innerHTML = '<i class="fas fa-exclamation-circle"></i> L\'ID est obligatoire'; idInput.classList.add('error'); return false; }
        if(isNaN(id) || id <= 0) { idError.innerHTML = '<i class="fas fa-exclamation-circle"></i> L\'ID doit être un nombre positif (1, 2, 3, ...)'; idInput.classList.add('error'); return false; }
        idError.innerHTML = ''; idInput.classList.remove('error'); return true;
    }

    function validateNom() {
        const nom = nomInput.value.trim();
        if(nom === '') { nomError.innerHTML = '<i class="fas fa-exclamation-circle"></i> Le nom est obligatoire'; nomInput.classList.add('error'); return false; }
        if(nom.length > 50) { nomError.innerHTML = '<i class="fas fa-exclamation-circle"></i> Le nom ne doit pas dépasser 50 caractères'; nomInput.classList.add('error'); return false; }
        nomError.innerHTML = ''; nomInput.classList.remove('error'); return true;
    }

    function validateService() {
        const service = serviceSelect.value;
        if(service === '') { serviceError.innerHTML = '<i class="fas fa-exclamation-circle"></i> Veuillez sélectionner un service'; serviceSelect.classList.add('error'); return false; }
        serviceError.innerHTML = ''; serviceSelect.classList.remove('error'); return true;
    }

    function validateDocuments() {
        const documents = documentsInput.value.trim();
        if(documents === '') { documentsError.innerHTML = '<i class="fas fa-exclamation-circle"></i> La liste des documents est obligatoire'; documentsInput.classList.add('error'); return false; }
        if(documents.length > 40) { documentsError.innerHTML = '<i class="fas fa-exclamation-circle"></i> Maximum 40 caractères'; documentsInput.classList.add('error'); return false; }
        documentsError.innerHTML = ''; documentsInput.classList.remove('error'); return true;
    }

    function updateNomCounter() {
        const length = nomInput.value.length;
        nomCounter.innerHTML = `${length} / 50 caractères`;
        nomCounter.style.color = length > 50 ? '#ef4444' : length > 40 ? '#f59e0b' : '#10b981';
    }

    function updateDocumentsCounter() {
        const length = documentsInput.value.length;
        documentsCounter.innerHTML = `${length} / 40 caractères`;
        documentsCounter.style.color = length > 40 ? '#ef4444' : length > 30 ? '#f59e0b' : '#10b981';
    }

    function validateForm() {
        const valid = validateId() & validateNom() & validateService() & validateDocuments();
        submitBtn.disabled = !valid;
        return !!valid;
    }

    idInput.addEventListener('input', () => { validateId(); validateForm(); });
    nomInput.addEventListener('input', () => { validateNom(); updateNomCounter(); validateForm(); });
    serviceSelect.addEventListener('change', () => { validateService(); validateForm(); });
    documentsInput.addEventListener('input', () => { validateDocuments(); updateDocumentsCounter(); validateForm(); });

    idInput.addEventListener('blur', validateId);
    nomInput.addEventListener('blur', validateNom);
    serviceSelect.addEventListener('blur', validateService);
    documentsInput.addEventListener('blur', validateDocuments);

    updateNomCounter();
    updateDocumentsCounter();
    submitBtn.disabled = true;

    let isSubmitting = false;
    form.addEventListener('submit', function(e) {
        if(!validateForm()) {
            e.preventDefault();
            const firstError = document.querySelector('.error');
            if(firstError) firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
            return false;
        }
        if(isSubmitting) { e.preventDefault(); return false; }
        isSubmitting = true;
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Envoi en cours...';
    });
</script>
