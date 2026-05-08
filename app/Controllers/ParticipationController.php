<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Participation;
use App\Models\Evenement;

class ParticipationController extends Controller
{
    private Participation $participation;
    private Evenement $evenement;

    public function __construct()
    {
        parent::__construct();
        $this->participation = new Participation();
        $this->evenement = new Evenement();
    }

    /**
     * User registers for an event
     */
    public function register()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(400);
            echo 'Invalid request method.';
            return;
        }

        if (!isset($_SESSION['user'])) {
            $_SESSION['error'] = 'Vous devez être connecté pour vous inscrire';
            header('Location: ' . $GLOBALS['baseUrl'] . '?route=login');
            exit;
        }

        $data = [
            'user_id' => $_SESSION['user']['id'],
            'event_id' => $_POST['event_id'] ?? null,
            'nombre_participants' => $_POST['nombre_participants'] ?? 1
        ];

        $result = $this->participation->create($data);
        $_SESSION[($result['success'] ?? false) ? 'success' : 'error'] = $result['message'] ?? 'Unknown error';

        $referrer = $_SERVER['HTTP_REFERER'] ?? $GLOBALS['baseUrl'] . '?route=event/index';
        header('Location: ' . $referrer);
        exit;
    }

    /**
     * User cancels their participation
     */
    public function cancel()
    {
        if (!isset($_SESSION['user'])) {
            $_SESSION['error'] = 'Vous devez être connecté';
            header('Location: ' . $GLOBALS['baseUrl'] . '?route=login');
            exit;
        }

        $userId = $_SESSION['user']['id'];
        $eventId = (int)($_GET['event_id'] ?? 0);

        if (!$eventId) {
            http_response_code(400);
            echo 'Invalid event ID.';
            return;
        }

        $result = $this->participation->cancel($userId, $eventId);
        $_SESSION[($result['success'] ?? false) ? 'success' : 'error'] = $result['message'] ?? 'Unknown error';

        header('Location: ' . $GLOBALS['baseUrl'] . '?route=participation/myEvents');
        exit;
    }

    /**
     * Display user's registered events
     */
    public function myEvents()
    {
        if (!isset($_SESSION['user'])) {
            $_SESSION['error'] = 'Vous devez être connecté';
            header('Location: ' . $GLOBALS['baseUrl'] . '?route=login');
            exit;
        }

        $participations = $this->participation->getByUser($_SESSION['user']['id']);
        $this->render('participation/myEvents', [
            'participations' => $participations,
            'pageTitle' => 'Mes participations'
        ]);
    }

    /**
     * List all participations for an event (admin)
     */
    public function byEvent()
    {
        // Admin only - add auth check if needed
        $eventId = (int)($_GET['event_id'] ?? 0);
        if (!$eventId) {
            http_response_code(400);
            echo 'Invalid event ID.';
            return;
        }

        $event = $this->evenement->find($eventId);
        if (!$event) {
            http_response_code(404);
            echo 'Event not found.';
            return;
        }

        $participations = $this->participation->getByEvent($eventId);
        $this->render('participation/byEvent', [
            'event' => $event,
            'participations' => $participations,
            'pageTitle' => 'Participations: ' . ($event['titre'] ?? 'Event')
        ]);
    }

    /**
     * Validate a participation (admin)
     */
    public function validate()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(400);
            echo 'Invalid request method.';
            return;
        }

        $participationId = (int)($_POST['participation_id'] ?? 0);
        if (!$participationId) {
            http_response_code(400);
            echo 'Invalid participation ID.';
            return;
        }

        $result = $this->participation->validate($participationId);
        $_SESSION[($result['success'] ?? false) ? 'success' : 'error'] = $result['message'] ?? 'Unknown error';

        $referrer = $_SERVER['HTTP_REFERER'] ?? $GLOBALS['baseUrl'];
        header('Location: ' . $referrer);
        exit;
    }

    /**
     * Reject a participation (admin)
     */
    public function reject()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(400);
            echo 'Invalid request method.';
            return;
        }

        $participationId = (int)($_POST['participation_id'] ?? 0);
        $comment = $_POST['comment'] ?? null;

        if (!$participationId) {
            http_response_code(400);
            echo 'Invalid participation ID.';
            return;
        }

        $result = $this->participation->reject($participationId, $comment);
        $_SESSION[($result['success'] ?? false) ? 'success' : 'error'] = $result['message'] ?? 'Unknown error';

        $referrer = $_SERVER['HTTP_REFERER'] ?? $GLOBALS['baseUrl'];
        header('Location: ' . $referrer);
        exit;
    }

    /**
     * Add participation directly (admin)
     */
    public function addDirect()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(400);
            echo 'Invalid request method.';
            return;
        }

        $userId = (int)($_POST['user_id'] ?? 0);
        $eventId = (int)($_POST['event_id'] ?? 0);
        $nbParticipants = (int)($_POST['nombre_participants'] ?? 1);

        if (!$userId || !$eventId) {
            http_response_code(400);
            echo 'Invalid user or event ID.';
            return;
        }

        $result = $this->participation->createDirect($userId, $eventId, $nbParticipants);
        $_SESSION[($result['success'] ?? false) ? 'success' : 'error'] = $result['message'] ?? 'Unknown error';

        $referrer = $_SERVER['HTTP_REFERER'] ?? $GLOBALS['baseUrl'];
        header('Location: ' . $referrer);
        exit;
    }
}
?>
