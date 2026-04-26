<?php
session_start();
require_once __DIR__ . '/../../controller/EvenementC.php';
require_once __DIR__ . '/../../controller/ParticipationC.php';

$isLoggedIn = isset($_SESSION['user_id']);
$isAdmin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
$userName = isset($_SESSION['prenom']) ? $_SESSION['prenom'] . ' ' . $_SESSION['nom'] : 'Invité';

$evenementC = new EvenementC();
$participationC = new ParticipationC();
$evenements = $evenementC->afficherEvenements();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Calendrier - Smart Municipality</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js'></script>
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/locales/fr.js'></script>
    <link href='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css' rel='stylesheet' />
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Inter', sans-serif;
            background: #e8f5e9;
        }
        .navbar {
            background: white;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            padding: 15px 30px;
        }
        .navbar-brand {
            font-weight: 700;
            color: #1a5e2a !important;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .navbar-brand img { border-radius: 10px; }
        .btn-primary {
            background: linear-gradient(135deg, #1a5e2a, #4caf50);
            border: none;
            padding: 8px 16px;
            border-radius: 10px;
            font-weight: 500;
            color: white;
            text-decoration: none;
        }
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
</head>
<body>
    <nav class="navbar">
        <div class="container">
            <a class="navbar-brand" href="../../index.php"><img src="../../logo.jpeg" alt="Logo" height="35">Smart Municipality</a>
            <div>
                <span class="text-muted me-3"><i class="fas fa-user-circle me-1"></i><?php echo htmlspecialchars($userName); ?></span>
                <a href="../../index.php" class="btn btn-primary btn-sm"><i class="fas fa-arrow-left me-1"></i>Retour</a>
            </div>
        </div>
    </nav>

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
                    $url = $isAdmin ? '../evenement/modifier.php?id=' . $e['id'] : '../../index.php';
                    $eventsArray[] = [
                        'id' => $e['id'],
                        'title' => $e['titre'],
                        'start' => $e['date_evenement'],
                        'color' => '#1a5e2a',
                        'url' => $url
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
</body>
</html>