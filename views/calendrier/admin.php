<?php
session_start();
require_once __DIR__ . '/../../controller/EvenementC.php';
require_once __DIR__ . '/../../controller/ParticipationC.php';
require_once __DIR__ . '/../../controller/CategorieEvenementC.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../../index.php');
    exit();
}

$evenementC = new EvenementC();
$participationC = new ParticipationC();
$categorieC = new CategorieEvenementC();

$evenements = $evenementC->afficherEvenements();
$categories = $categorieC->afficherCategories();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Calendrier Admin - Smart Municipality</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css' rel='stylesheet' />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #1a5e2a;
            --gradient: linear-gradient(135deg, #1a5e2a, #4caf50);
            --shadow: 0 5px 15px rgba(0,0,0,0.05);
            --radius: 16px;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #e8f5e9 0%, #f0f4f0 100%);
        }
        .sidebar {
            min-height: 100vh;
            background: linear-gradient(180deg, #0d3b1a 0%, #1a5e2a 100%);
            position: fixed;
            width: 280px;
        }
        .sidebar-header { padding: 25px; text-align: center; border-bottom: 1px solid rgba(255,255,255,0.1); }
        .sidebar-header img { border-radius: 15px; margin-bottom: 10px; }
        .sidebar-header h3 { color: white; font-size: 1.2rem; }
        .sidebar-nav a {
            display: flex;
            align-items: center;
            padding: 12px 25px;
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            transition: 0.3s;
            margin: 5px 10px;
            border-radius: 12px;
        }
        .sidebar-nav a i { width: 28px; margin-right: 12px; }
        .sidebar-nav a:hover, .sidebar-nav a.active { background: rgba(255,255,255,0.15); color: white; transform: translateX(5px); }
        .main-content { margin-left: 280px; padding: 25px; }
        .btn-primary-custom { background: var(--gradient); border: none; padding: 8px 16px; border-radius: 10px; font-weight: 600; color: white; }
        .calendar-container { background: white; border-radius: var(--radius); padding: 20px; box-shadow: var(--shadow); }
        .fc-event-custom { background: var(--primary); border: none; cursor: pointer; }
        hr { border-color: rgba(255,255,255,0.1); margin: 15px; }
        @media (max-width: 768px) { .sidebar { width: 80px; } .sidebar-header h3, .sidebar-nav a span { display: none; } .sidebar-nav a { justify-content: center; } .sidebar-nav a i { margin-right: 0; } .main-content { margin-left: 80px; } }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="sidebar-header">
            <img src="../../logo.jpeg" alt="Logo" height="45">
            <h3>Smart Municipality</h3>
        </div>
        <div class="sidebar-nav">
            <a href="../dashboard/admin.php"><i class="fas fa-tachometer-alt"></i><span>Dashboard</span></a>
            <a href="../evenement/liste.php"><i class="fas fa-calendar-alt"></i><span>Événements</span></a>
            <a href="../dashboard/categorie/liste.php"><i class="fas fa-tags"></i><span>Catégories</span></a>
            <a href="admin.php" class="active"><i class="fas fa-calendar-week"></i><span>Calendrier</span></a>
            <hr>
            <a href="../../index.php"><i class="fas fa-home"></i><span>Accueil</span></a>
            <a href="../../logout.php"><i class="fas fa-sign-out-alt"></i><span>Déconnexion</span></a>
        </div>
    </div>

    <div class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-calendar-week me-2" style="color: var(--primary);"></i>Calendrier Admin</h2>
            <button class="btn btn-primary-custom" data-bs-toggle="modal" data-bs-target="#addEventModal">
                <i class="fas fa-plus me-2"></i>Ajouter un événement
            </button>
        </div>

        <div class="calendar-container">
            <div id="calendar"></div>
        </div>
    </div>

    <!-- Modal Ajout Événement -->
    <div class="modal fade" id="addEventModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header" style="background: var(--gradient); color: white;">
                    <h5 class="modal-title"><i class="fas fa-plus-circle me-2"></i>Ajouter un événement</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="../evenement/ajouter.php" id="addEventForm">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Titre *</label>
                                <input type="text" name="titre" class="form-control" required>
                            </div>
                            <div class="col-12 mb-3">
                                <label class="form-label">Description *</label>
                                <textarea name="description" rows="3" class="form-control" required></textarea>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Date *</label>
                                <input type="date" name="date_evenement" id="eventDate" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Heure *</label>
                                <input type="time" name="heure" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Lieu *</label>
                                <input type="text" name="lieu" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Nombre max participants</label>
                                <input type="number" name="max_participants" class="form-control" value="50" min="1">
                            </div>
                            <div class="col-12 mb-3">
                                <label class="form-label">Catégorie *</label>
                                <select name="categorie_id" class="form-select" required>
                                    <option value="">-- Sélectionner --</option>
                                    <?php foreach($categories as $cat): ?>
                                    <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['nom']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-primary-custom">Ajouter</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js'></script>
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/locales/fr.js'></script>
    <script>
        // Initialiser la date du jour dans le formulaire
        const today = new Date().toISOString().split('T')[0];
        document.getElementById('eventDate').value = today;
        
        const events = <?php 
            $eventsArray = [];
            foreach($evenements as $e) {
                $eventsArray[] = [
                    'id' => $e['id'],
                    'title' => $e['titre'],
                    'start' => $e['date_evenement'],
                    'lieu' => $e['lieu'],
                    'color' => '#1a5e2a',
                    'url' => '../evenement/modifier.php?id=' . $e['id']
                ];
            }
            echo json_encode($eventsArray);
        ?>;
        
        const calendarEl = document.getElementById('calendar');
        const calendar = new FullCalendar.Calendar(calendarEl, {
            locale: 'fr',
            initialView: 'dayGridMonth',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,listWeek'
            },
            buttonText: { today: "Aujourd'hui", month: 'Mois', week: 'Semaine', list: 'Liste' },
            events: events,
            eventClick: function(info) {
                if (info.event.url) {
                    window.location.href = info.event.url;
                }
            },
            dateClick: function(info) {
                document.getElementById('eventDate').value = info.dateStr;
                new bootstrap.Modal(document.getElementById('addEventModal')).show();
            }
        });
        calendar.render();
    </script>
</body>
</html>