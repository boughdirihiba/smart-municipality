<?php
// Variables provided by legacy_router (evenements_categorie case):
// $categorie, $categorie_id, $evenements, $recherche, $sort_by, $sort_order
// $isLoggedIn, $userId_ec, $isAdmin_ec
// $message_ec, $messageType_ec
// $nbEvenements, $totalPlaces, $calendarEvents, $participationC_ec
?>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js'></script>
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/locales/fr.js'></script>
<link href='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css' rel='stylesheet' />
<style>
    :root { --primary: #1a5e2a; --gradient: linear-gradient(135deg, #1a5e2a, #4caf50); --radius: 12px; --radius-lg: 20px; }

    .hero-categorie { background: var(--gradient); padding: 36px 0; text-align: center; color: white; border-radius: 20px; margin-bottom: 24px; }
    .hero-categorie h1 { font-size: 1.6rem; font-weight: 700; margin-bottom: 8px; }
    .hero-categorie p  { font-size: 0.85rem; opacity: 0.9; margin: 0; }
    .hero-stats { display: flex; justify-content: center; gap: 16px; margin-top: 14px; flex-wrap: wrap; }
    .hero-stat { background: rgba(255,255,255,0.18); backdrop-filter: blur(8px); padding: 5px 14px; border-radius: 30px; display: inline-flex; align-items: center; gap: 8px; font-size: 0.75rem; }
    .back-link { display: inline-flex; align-items: center; gap: 6px; color: white; text-decoration: none; margin-bottom: 16px; background: rgba(255,255,255,0.15); padding: 5px 14px; border-radius: 30px; font-size: 0.75rem; transition: all 0.2s; }
    .back-link:hover { background: rgba(255,255,255,0.28); transform: translateX(-3px); color: white; }

    .filter-bar { background: white; border-radius: var(--radius-lg); padding: 1rem; margin-bottom: 1rem; box-shadow: 0 2px 8px rgba(0,0,0,0.05); }
    .sort-buttons { display: flex; flex-wrap: wrap; gap: 10px; align-items: center; background: white; padding: 0.8rem 1rem; border-radius: var(--radius-lg); box-shadow: 0 2px 8px rgba(0,0,0,0.05); margin-bottom: 1rem; }
    .sort-label { font-size: 0.75rem; font-weight: 600; color: var(--primary); margin-right: 6px; }
    .sort-btn { background: #f0f4f0; border: none; padding: 6px 14px; border-radius: 20px; font-size: 0.7rem; font-weight: 500; color: #555; cursor: pointer; text-decoration: none; }
    .sort-btn:hover, .sort-btn.active { background: var(--primary); color: white; }
    .btn-add-event { background: var(--gradient); border: none; padding: 8px 20px; border-radius: 30px; font-weight: 600; font-size: 0.75rem; color: white; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; margin-bottom: 1rem; }
    .btn-add-event:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(26,94,42,0.3); color: white; }
    .btn-primary-custom { background: var(--gradient); border: none; padding: 6px 12px; border-radius: 8px; font-weight: 600; font-size: 0.7rem; color: white; }

    .event-card { background: white; border-radius: var(--radius-lg); box-shadow: 0 2px 8px rgba(0,0,0,0.05); transition: all 0.3s; margin-bottom: 1.25rem; border: 1px solid rgba(0,0,0,0.03); overflow: hidden; }
    .event-card:hover { transform: translateY(-3px); box-shadow: 0 10px 25px rgba(0,0,0,0.1); }
    .event-card-inner { padding: 1.25rem; }
    .event-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 12px; }
    .event-title { font-weight: 700; font-size: 1rem; color: #0d3b1a; margin: 0; }
    .event-category { background: #e8f5e9; color: var(--primary); padding: 3px 10px; border-radius: 20px; font-size: 0.65rem; font-weight: 600; white-space: nowrap; }
    .event-details { display: flex; flex-wrap: wrap; gap: 12px; margin-bottom: 12px; }
    .event-detail { display: flex; align-items: center; gap: 5px; font-size: 0.7rem; color: #666; }
    .event-detail i { width: 18px; color: var(--primary); }
    .event-description { font-size: 0.7rem; color: #777; line-height: 1.4; margin-bottom: 12px; }
    .progress-section { margin: 12px 0; }
    .progress-stats { display: flex; justify-content: space-between; font-size: 0.65rem; color: #888; margin-bottom: 5px; }
    .progress-bar-custom { height: 4px; background: #e8ece8; border-radius: 4px; overflow: hidden; }
    .progress-fill { height: 100%; background: var(--primary); border-radius: 4px; }
    .btn-subscribe { width: 100%; background: white; border: 1.5px solid var(--primary); color: var(--primary); padding: 8px 12px; border-radius: 10px; font-weight: 600; font-size: 0.7rem; transition: all 0.2s; cursor: pointer; }
    .btn-subscribe:hover { background: var(--primary); color: white; transform: translateY(-2px); }
    .btn-share { background: #1877f2; color: white; border: none; padding: 8px 12px; border-radius: 10px; font-size: 0.7rem; font-weight: 600; cursor: pointer; transition: all 0.2s; display: inline-flex; align-items: center; justify-content: center; gap: 8px; width: 100%; margin-top: 8px; }
    .btn-share:hover { background: #0d5bd9; transform: translateY(-2px); color: white; }
    .status-badge { display: inline-flex; align-items: center; gap: 4px; padding: 6px 12px; border-radius: 10px; font-size: 0.7rem; font-weight: 600; width: 100%; justify-content: center; }
    .status-pending   { background: #ff9800; color: white; }
    .status-confirmed { background: #4caf50; color: white; }
    .status-refused   { background: #f44336; color: white; }
    .status-full      { background: #9e9e9e; color: white; }
    .admin-buttons { display: flex; gap: 8px; margin-top: 10px; }
    .btn-admin  { padding: 5px 10px; border-radius: 8px; font-size: 0.65rem; font-weight: 500; text-decoration: none; }
    .btn-edit   { background: #f59e0b; color: white; }
    .btn-users  { background: #0891b2; color: white; }
    .btn-delete { background: #dc2626; color: white; }

    .calendar-container { background: white; border-radius: var(--radius-lg); padding: 20px; margin-top: 24px; margin-bottom: 24px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); }
    .fc { font-family: inherit; }
    .fc-event { cursor: pointer; border-radius: 6px; font-size: 0.7rem; padding: 4px 6px; }
    .fc-col-header-cell-cushion { font-weight: 600; color: var(--primary); text-transform: uppercase; font-size: 0.7rem; }
    .fc-toolbar-title { font-size: 1rem !important; font-weight: 700 !important; color: var(--primary) !important; }
    .fc-button { background: var(--primary) !important; border: none !important; border-radius: 8px !important; padding: 4px 10px !important; font-size: 0.7rem !important; }
    .fc-button:hover { background: #0d3b1a !important; }
    .fc-day-today { background: #e8f5e9 !important; }

    .modal-overlay { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); backdrop-filter: blur(4px); z-index: 10000; align-items: center; justify-content: center; }
    .modal-container { background: white; border-radius: 24px; max-width: 420px; width: 90%; overflow: hidden; animation: modalSlideIn 0.2s ease; }
    @keyframes modalSlideIn { from { transform: translateY(30px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
    .modal-header-custom { background: var(--gradient); padding: 16px; text-align: center; color: white; }
    .modal-header-custom i { font-size: 2rem; margin-bottom: 5px; display: block; }
    .modal-header-custom h3 { margin: 0; font-size: 1.1rem; }
    .modal-body-custom { padding: 20px; }
    .modal-footer-custom { padding: 12px 20px; display: flex; gap: 10px; justify-content: flex-end; border-top: 1px solid #e9ecef; }
    .event-details-modal { background: #e8f5e9; border-radius: 12px; padding: 12px; margin: 14px 0; font-size: 0.8rem; }

    .toast-message { position: fixed; top: 80px; right: 20px; z-index: 10001; animation: slideInRight 0.3s ease; }
    @keyframes slideInRight { from { transform: translateX(100%); opacity: 0; } to { transform: translateX(0); opacity: 1; } }
    .empty-state { text-align: center; padding: 40px; background: white; border-radius: var(--radius-lg); }
    .empty-state i { font-size: 2.5rem; color: #4caf50; margin-bottom: 1rem; }

    @media (max-width: 768px) {
        .hero-categorie h1 { font-size: 1.2rem; }
        .hero-stats { flex-direction: column; align-items: center; gap: 8px; }
        .calendar-container { padding: 10px; }
    }
</style>

<?php if ($message_ec): ?>
<div class="toast-message">
    <div class="alert alert-<?php echo htmlspecialchars($messageType_ec); ?> shadow rounded-3 border-0 py-2 px-3">
        <i class="fas fa-<?php echo $messageType_ec === 'success' ? 'check-circle' : 'exclamation-triangle'; ?> me-2"></i>
        <?php echo $message_ec; ?>
        <button type="button" class="btn-close ms-2" data-bs-dismiss="alert"></button>
    </div>
</div>
<?php endif; ?>

<!-- Modal d'inscription -->
<div id="inscriptionModal" class="modal-overlay">
    <div class="modal-container">
        <div class="modal-header-custom">
            <i class="fas fa-ticket-alt"></i>
            <h3>Confirmer l'inscription</h3>
        </div>
        <div class="modal-body-custom">
            <div class="event-details-modal">
                <p><i class="fas fa-calendar-alt me-1"></i> <strong id="modalTitle">-</strong></p>
                <p><i class="fas fa-map-marker-alt me-1"></i> Lieu : <span id="modalLieu">-</span></p>
                <p><i class="fas fa-calendar-day me-1"></i> Date : <span id="modalDate">-</span> à <span id="modalHeure">-</span></p>
            </div>
            <div class="mb-2">
                <label class="form-label small fw-bold">Nombre de participants</label>
                <input type="number" id="nbParticipants" class="form-control form-control-sm" min="1" max="10" value="1">
                <small class="text-muted">Maximum 10 personnes</small>
            </div>
            <div class="alert alert-warning py-1 px-2 mb-0" style="font-size:0.75rem;">
                <i class="fas fa-info-circle me-1"></i> Places restantes : <strong id="placesRestantes">-</strong>
            </div>
        </div>
        <div class="modal-footer-custom">
            <button type="button" class="btn btn-secondary btn-sm" id="closeModalBtn">Annuler</button>
            <button type="button" class="btn btn-primary-custom btn-sm" id="confirmModalBtn">Confirmer</button>
        </div>
    </div>
</div>

<!-- Hero -->
<section class="hero-categorie">
    <div class="container">
        <a href="<?php echo BASE_URL; ?>/index.php?action=evenements" class="back-link">
            <i class="fas fa-arrow-left"></i> Retour aux catégories
        </a>
        <h1>
            <i class="fas <?php
                $nom = strtolower($categorie['nom']);
                if (strpos($nom, 'culture') !== false) echo 'fa-music';
                elseif (strpos($nom, 'sport') !== false) echo 'fa-futbol';
                elseif (strpos($nom, 'environnement') !== false) echo 'fa-leaf';
                elseif (strpos($nom, 'social') !== false) echo 'fa-handshake';
                elseif (strpos($nom, 'tech') !== false) echo 'fa-microchip';
                else echo 'fa-tag';
            ?> me-2"></i><?php echo htmlspecialchars($categorie['nom']); ?>
        </h1>
        <p><?php echo htmlspecialchars($categorie['description']); ?></p>
        <div class="hero-stats">
            <div class="hero-stat"><i class="fas fa-calendar-alt"></i> <?php echo $nbEvenements; ?> événement(s)</div>
            <div class="hero-stat"><i class="fas fa-users"></i> <?php echo $totalPlaces; ?> places totales</div>
        </div>
    </div>
</section>

<div class="container-fluid px-0">
    <!-- Filtre recherche -->
    <div class="filter-bar">
        <form method="GET" action="<?php echo BASE_URL; ?>/index.php">
            <input type="hidden" name="action" value="evenements_categorie">
            <input type="hidden" name="id" value="<?php echo $categorie_id; ?>">
            <div class="row g-2">
                <div class="col-md-10">
                    <div class="input-group">
                        <span class="input-group-text bg-white"><i class="fas fa-search text-success"></i></span>
                        <input type="text" name="search" class="form-control" placeholder="Rechercher un événement..." value="<?php echo htmlspecialchars($recherche); ?>">
                    </div>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary-custom w-100"><i class="fas fa-search me-1"></i> Rechercher</button>
                </div>
            </div>
        </form>
    </div>

    <!-- Tris -->
    <?php
    $baseSort = BASE_URL . '/index.php?action=evenements_categorie&id=' . $categorie_id . ($recherche ? '&search=' . urlencode($recherche) : '');
    ?>
    <div class="sort-buttons">
        <span class="sort-label"><i class="fas fa-sort me-1"></i> Trier par :</span>
        <a href="<?php echo $baseSort; ?>&sort_by=date&sort_order=<?php echo ($sort_by === 'date' && $sort_order === 'asc') ? 'desc' : 'asc'; ?>"
           class="sort-btn <?php echo $sort_by === 'date' ? 'active' : ''; ?>">
            Date <?php if ($sort_by === 'date') echo $sort_order === 'asc' ? '↑' : '↓'; ?>
        </a>
        <a href="<?php echo $baseSort; ?>&sort_by=titre&sort_order=<?php echo ($sort_by === 'titre' && $sort_order === 'asc') ? 'desc' : 'asc'; ?>"
           class="sort-btn <?php echo $sort_by === 'titre' ? 'active' : ''; ?>">
            Titre <?php if ($sort_by === 'titre') echo $sort_order === 'asc' ? '↑' : '↓'; ?>
        </a>
        <a href="<?php echo $baseSort; ?>&sort_by=lieu&sort_order=<?php echo ($sort_by === 'lieu' && $sort_order === 'asc') ? 'desc' : 'asc'; ?>"
           class="sort-btn <?php echo $sort_by === 'lieu' ? 'active' : ''; ?>">
            Lieu <?php if ($sort_by === 'lieu') echo $sort_order === 'asc' ? '↑' : '↓'; ?>
        </a>
    </div>

    <?php if ($isAdmin_ec): ?>
    <div class="text-end mb-3">
        <a href="<?php echo BASE_URL; ?>/index.php?action=ajouter_evenement&categorie=<?php echo $categorie_id; ?>" class="btn-add-event">
            <i class="fas fa-plus-circle"></i> Ajouter un événement
        </a>
    </div>
    <?php endif; ?>

    <!-- Liste des événements -->
    <?php if (empty($evenements)): ?>
    <div class="empty-state">
        <i class="fas fa-calendar-times"></i>
        <h5>Aucun événement trouvé</h5>
        <p class="text-muted">Aucun événement à venir dans la catégorie "<?php echo htmlspecialchars($categorie['nom']); ?>"</p>
        <a href="<?php echo BASE_URL; ?>/index.php?action=evenements_categorie&id=<?php echo $categorie_id; ?>" class="btn btn-primary-custom btn-sm mt-2">
            Réinitialiser
        </a>
    </div>
    <?php else: ?>
        <?php foreach ($evenements as $event):
            $placesTotal    = $event['max_participants'];
            $placesValidees = $participationC_ec->compterParticipationsValidees($event['id']);
            $placesRestantes = $placesTotal - $placesValidees;
            $pourcentage    = $placesTotal > 0 ? round(($placesValidees / $placesTotal) * 100) : 0;
            $estComplet     = $placesRestantes <= 0;
            $estInscrit     = false;
            $statutValidation = null;
            if ($isLoggedIn) {
                $estInscrit       = $participationC_ec->estInscrit($userId_ec, $event['id']);
                $statutValidation = $participationC_ec->getStatutValidation($userId_ec, $event['id']);
            }
        ?>
        <div class="event-card event-card-wrapper"
             data-id="<?php echo $event['id']; ?>"
             data-title="<?php echo htmlspecialchars($event['titre']); ?>"
             data-lieu="<?php echo htmlspecialchars($event['lieu']); ?>"
             data-date="<?php echo date('d/m/Y', strtotime($event['date_evenement'])); ?>"
             data-heure="<?php echo htmlspecialchars($event['heure']); ?>"
             data-places="<?php echo $placesRestantes; ?>">
            <div class="event-card-inner">
                <div class="event-header">
                    <h5 class="event-title"><?php echo htmlspecialchars($event['titre']); ?></h5>
                    <span class="event-category"><?php echo htmlspecialchars($event['categorie_nom'] ?? $categorie['nom']); ?></span>
                </div>
                <div class="event-details">
                    <span class="event-detail"><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($event['lieu']); ?></span>
                    <span class="event-detail"><i class="fas fa-calendar-day"></i> <?php echo date('d/m/Y', strtotime($event['date_evenement'])); ?></span>
                    <span class="event-detail"><i class="fas fa-clock"></i> <?php echo htmlspecialchars($event['heure']); ?></span>
                </div>
                <p class="event-description"><?php echo htmlspecialchars(substr($event['description'], 0, 120)); ?>...</p>
                <div class="progress-section">
                    <div class="progress-stats">
                        <span><i class="fas fa-users"></i> <?php echo $placesValidees; ?>/<?php echo $placesTotal; ?> inscrits</span>
                        <span><?php echo $placesRestantes; ?> places restantes</span>
                    </div>
                    <div class="progress-bar-custom"><div class="progress-fill" style="width:<?php echo $pourcentage; ?>%;"></div></div>
                </div>
                <div class="action-buttons">
                    <?php if ($estInscrit): ?>
                        <?php if ($statutValidation === 'en_attente'): ?>
                            <div class="status-badge status-pending"><i class="fas fa-clock"></i> En attente</div>
                        <?php elseif ($statutValidation === 'valide'): ?>
                            <div class="status-badge status-confirmed"><i class="fas fa-check-circle"></i> Inscrit</div>
                        <?php else: ?>
                            <div class="status-badge status-refused"><i class="fas fa-times-circle"></i> Refusé</div>
                        <?php endif; ?>
                    <?php elseif ($estComplet): ?>
                        <div class="status-badge status-full"><i class="fas fa-ban"></i> Complet</div>
                    <?php else: ?>
                        <button class="btn-subscribe btn-inscrire"><i class="fas fa-ticket-alt"></i> S'inscrire</button>
                    <?php endif; ?>

                    <button onclick="partagerFacebook('<?php echo htmlspecialchars(addslashes($event['titre'])); ?>', '<?php echo htmlspecialchars(addslashes($event['lieu'])); ?>', '<?php echo date('d/m/Y', strtotime($event['date_evenement'])); ?>')" class="btn-share">
                        <i class="fab fa-facebook-f"></i> Partager sur Facebook
                    </button>

                    <?php if ($isAdmin_ec): ?>
                    <div class="admin-buttons">
                        <a href="<?php echo BASE_URL; ?>/index.php?action=modifier_evenement&id=<?php echo $event['id']; ?>" class="btn-admin btn-edit"><i class="fas fa-edit"></i></a>
                        <a href="<?php echo BASE_URL; ?>/index.php?action=participants_evenement&id=<?php echo $event['id']; ?>" class="btn-admin btn-users"><i class="fas fa-users"></i></a>
                        <a href="<?php echo BASE_URL; ?>/index.php?action=supprimer_evenement&id=<?php echo $event['id']; ?>" class="btn-admin btn-delete" onclick="return confirm('Supprimer cet événement ?')"><i class="fas fa-trash"></i></a>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <!-- Calendrier -->
    <div class="calendar-container">
        <div id="calendar"></div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
const BASE_URL = '<?php echo BASE_URL; ?>';
const CATEGORIE_ID = <?php echo $categorie_id; ?>;

// ── Modal ──────────────────────────────────────────────────────────────
const modal      = document.getElementById('inscriptionModal');
const closeBtn   = document.getElementById('closeModalBtn');
const confirmBtn = document.getElementById('confirmModalBtn');
const nbInput    = document.getElementById('nbParticipants');
let currentEventId = null, currentPlaces = 0;

function openModal(eventId, title, lieu, date, heure, places) {
    currentEventId = eventId;
    currentPlaces  = places;
    document.getElementById('modalTitle').textContent   = title;
    document.getElementById('modalLieu').textContent    = lieu;
    document.getElementById('modalDate').textContent    = date;
    document.getElementById('modalHeure').textContent   = heure;
    document.getElementById('placesRestantes').textContent = places;
    nbInput.max   = Math.min(10, places);
    nbInput.value = 1;
    modal.style.display = 'flex';
}
function closeModal() { modal.style.display = 'none'; currentEventId = null; }

function confirmInscription() {
    if (!currentEventId) return;
    let nb = parseInt(nbInput.value);
    if (isNaN(nb) || nb < 1) nb = 1;
    if (nb > currentPlaces) { alert('⚠️ Il ne reste que ' + currentPlaces + ' place(s) disponible(s).'); return; }
    window.location.href = BASE_URL + '/index.php?action=inscrire_evenement&event_id=' + currentEventId + '&nb_participants=' + nb + '&categorie_id=' + CATEGORIE_ID;
}

closeBtn.onclick  = closeModal;
confirmBtn.onclick = confirmInscription;
modal.addEventListener('click', e => { if (e.target === modal) closeModal(); });
document.addEventListener('keydown', e => { if (e.key === 'Escape' && modal.style.display === 'flex') closeModal(); });

document.querySelectorAll('.btn-inscrire').forEach(btn => {
    btn.addEventListener('click', function() {
        const w = this.closest('.event-card-wrapper');
        if (w) openModal(w.dataset.id, w.dataset.title, w.dataset.lieu, w.dataset.date, w.dataset.heure, parseInt(w.dataset.places));
    });
});

// ── FullCalendar ────────────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', function() {
    var events = <?php echo json_encode($calendarEvents); ?>;
    var calEl  = document.getElementById('calendar');
    if (!calEl) return;

    var calendar = new FullCalendar.Calendar(calEl, {
        locale: 'fr',
        initialView: 'dayGridMonth',
        headerToolbar: { left: 'prev,next today', center: 'title', right: 'dayGridMonth,timeGridWeek,listWeek' },
        buttonText: { today: "Aujourd'hui", month: 'Mois', week: 'Semaine', list: 'Liste' },
        events: events,
        height: 480,
        contentHeight: 'auto',
        firstDay: 1,
        dayMaxEvents: true,
        eventClick: function(info) {
            const ev = info.event, props = ev.extendedProps;
            <?php if ($isAdmin_ec): ?>
                window.location.href = BASE_URL + '/index.php?action=modifier_evenement&id=' + ev.id;
            <?php elseif ($isLoggedIn): ?>
                if (props.est_inscrit) { alert('✅ Vous êtes déjà inscrit à cet événement !'); return; }
                if (props.est_complet) { alert('❌ Désolé, cet événement est complet !'); return; }
                openModal(ev.id, ev.title, props.lieu, ev.startStr, props.heure, props.places_restantes);
            <?php else: ?>
                alert('🔐 Veuillez vous connecter pour vous inscrire.');
                window.location.href = BASE_URL + '/index.php?route=auth/login';
            <?php endif; ?>
        },
        dateClick: function(info) {
            <?php if ($isAdmin_ec): ?>
                window.location.href = BASE_URL + '/index.php?action=ajouter_evenement&categorie=' + CATEGORIE_ID + '&date=' + info.dateStr;
            <?php endif; ?>
        },
        eventDidMount: function(info) {
            const props = info.event.extendedProps;
            let tip = info.event.title + '\n📍 ' + props.lieu + '\n🕐 ' + props.heure + '\n👥 ' + props.places_restantes + '/' + props.places_total + ' places';
            if (props.est_complet) tip += '\n❌ COMPLET';
            if (props.est_inscrit) tip += '\n✅ Vous êtes inscrit';
            info.el.setAttribute('title', tip);
        }
    });
    calendar.render();
});

// ── Facebook share ──────────────────────────────────────────────────────
function partagerFacebook(titre, lieu, date) {
    var url = window.location.href;
    var shareUrl = 'https://www.facebook.com/sharer/sharer.php?u=' + encodeURIComponent(url);
    window.open(shareUrl, 'facebook-share', 'width=600,height=400');
}

// ── Auto-hide toast ─────────────────────────────────────────────────────
setTimeout(function() {
    var t = document.querySelector('.toast-message');
    if (t) { t.style.opacity = '0'; setTimeout(function() { t.remove(); }, 300); }
}, 5000);
</script>
