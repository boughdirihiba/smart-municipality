<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/../../controllers/EvenementC.php';
require_once __DIR__ . '/../../controllers/ParticipationC.php';

$isLoggedIn = isset($_SESSION['user_id']);
$isAdmin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
$userName = isset($_SESSION['prenom']) ? $_SESSION['prenom'] . ' ' . $_SESSION['nom'] : 'Invité';

$evenementC = new EvenementC();
$participationC = new ParticipationC();
$evenements = $evenementC->afficherEvenements();
?>

<link href='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css' rel='stylesheet' />
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js'></script>
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/locales/fr.js'></script>

<style>
    .calendar-container {
        background: white;
        border-radius: 20px;
        padding: 20px;
        margin: 30px auto;
        max-width: 1300px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.05);
    }
    .fc-event { background: #1a5e2a; border: none; cursor: pointer; }
    .fc-day-today { background: #e8f5e9 !important; }
    @media (max-width: 768px) { .calendar-container { margin: 15px; padding: 10px; } }
</style>

<div class="container">
    <div class="calendar-container">
        <div id="calendar"></div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        var events = <?php
            $eventsArray = [];
            foreach($evenements as $e) {
                $url = $isAdmin
                    ? BASE_URL . '/index.php?action=evenements&id=' . $e['id']
                    : BASE_URL . '/index.php';
                $eventsArray[] = [
                    'id'    => $e['id'],
                    'title' => $e['titre'],
                    'start' => $e['date_evenement'],
                    'color' => '#1a5e2a',
                    'url'   => $url
                ];
            }
            echo json_encode($eventsArray);
        ?>;

        var calendar = new FullCalendar.Calendar(document.getElementById('calendar'), {
            locale: 'fr',
            initialView: 'dayGridMonth',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,listWeek'
            },
            buttonText: {
                today: "Aujourd'hui",
                month: 'Mois',
                week: 'Semaine',
                list: 'Liste'
            },
            events: events,
            eventClick: function(info) {
                if (info.event.url) {
                    info.jsEvent.preventDefault();
                    window.location.href = info.event.url;
                }
            },
            height: 'auto',
            weekNumbers: true,
            dayMaxEvents: true
        });
        calendar.render();
    });
</script>
