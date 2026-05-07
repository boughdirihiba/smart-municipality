<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\RendezVous;

class RendezVousController extends Controller
{
    private RendezVous $rdv;

    public function __construct()
    {
        parent::__construct();
        $this->rdv = new RendezVous();
    }

    /**
     * List all appointments (admin)
     */
    public function index()
    {
        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
            http_response_code(403);
            echo 'Unauthorized.';
            return;
        }

        $appointments = $this->rdv->all();
        $this->render('rendez_vous/index', [
            'appointments' => $appointments,
            'pageTitle' => 'Tous les rendez-vous'
        ]);
    }

    /**
     * Display user's appointments
     */
    public function myAppointments()
    {
        if (!isset($_SESSION['user'])) {
            $_SESSION['error'] = 'Vous devez être connecté';
            header('Location: ' . $GLOBALS['baseUrl'] . '?route=login');
            exit;
        }

        $appointments = $this->rdv->getByUser($_SESSION['user']['id']);
        $this->render('rendez_vous/myAppointments', [
            'appointments' => $appointments,
            'pageTitle' => 'Mes rendez-vous'
        ]);
    }

    /**
     * Show appointment details
     */
    public function detail()
    {
        $id = (int)($_GET['id'] ?? 0);
        if (!$id) {
            http_response_code(404);
            echo 'Appointment not found.';
            return;
        }

        $appointment = $this->rdv->find($id);
        if (!$appointment) {
            http_response_code(404);
            echo 'Appointment not found.';
            return;
        }

        $this->render('rendez_vous/detail', [
            'appointment' => $appointment,
            'pageTitle' => 'Détail rendez-vous'
        ]);
    }

    /**
     * Create new appointment form
     */
    public function create()
    {
        if (!isset($_SESSION['user'])) {
            $_SESSION['error'] = 'Vous devez être connecté';
            header('Location: ' . $GLOBALS['baseUrl'] . '?route=login');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $categoryId = (int)($_POST['categorie_id'] ?? 0);
            $date = $_POST['date_rdv'] ?? '';
            $time = $_POST['heure'] ?? '';

            // Check if slot is available
            if ($this->rdv->isSlotTaken($categoryId, $date, $time)) {
                $_SESSION['error'] = 'Créneau horaire déjà réservé';
            } else {
                $data = [
                    'user_id' => $_SESSION['user']['id'],
                    'categorie_id' => $categoryId,
                    'date_rdv' => $date,
                    'heure' => $time,
                    'statut' => 'confirme'
                ];

                if ($this->rdv->create($data)) {
                    $_SESSION['success'] = 'Rendez-vous créé avec succès';
                    header('Location: ' . $GLOBALS['baseUrl'] . '?route=rendez_vous/myAppointments');
                    exit;
                } else {
                    $_SESSION['error'] = 'Erreur lors de la création';
                }
            }
        }

        $this->render('rendez_vous/create', [
            'pageTitle' => 'Prendre rendez-vous'
        ]);
    }

    /**
     * Get available slots (AJAX endpoint)
     */
    public function getSlots()
    {
        header('Content-Type: application/json');

        $categoryId = (int)($_GET['category_id'] ?? 0);
        $date = $_GET['date'] ?? '';

        if (!$categoryId || !$date) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid parameters']);
            return;
        }

        $slots = $this->rdv->getAvailableSlots($categoryId, $date);
        echo json_encode(['slots' => $slots]);
    }

    /**
     * Edit appointment
     */
    public function edit()
    {
        $id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
        if (!$id) {
            http_response_code(404);
            echo 'Appointment not found.';
            return;
        }

        $appointment = $this->rdv->find($id);
        if (!$appointment) {
            http_response_code(404);
            echo 'Appointment not found.';
            return;
        }

        if (!isset($_SESSION['user']) || ($_SESSION['user']['role'] !== 'admin' && $_SESSION['user']['id'] != $appointment['user_id'])) {
            http_response_code(403);
            echo 'Unauthorized.';
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'categorie_id' => $_POST['categorie_id'] ?? $appointment['categorie_id'],
                'date_rdv' => $_POST['date_rdv'] ?? $appointment['date_rdv'],
                'heure' => $_POST['heure'] ?? $appointment['heure'],
                'statut' => $_POST['statut'] ?? $appointment['statut']
            ];

            if ($this->rdv->update($id, $data)) {
                $_SESSION['success'] = 'Rendez-vous mis à jour avec succès';
                header('Location: ' . $GLOBALS['baseUrl'] . '?route=rendez_vous/detail&id=' . $id);
                exit;
            } else {
                $_SESSION['error'] = 'Erreur lors de la mise à jour';
            }
        }

        $this->render('rendez_vous/edit', [
            'appointment' => $appointment,
            'pageTitle' => 'Éditer rendez-vous'
        ]);
    }

    /**
     * Cancel appointment
     */
    public function cancel()
    {
        $id = (int)($_GET['id'] ?? 0);
        if (!$id) {
            http_response_code(400);
            echo 'Invalid ID.';
            return;
        }

        $appointment = $this->rdv->find($id);
        if (!$appointment) {
            http_response_code(404);
            echo 'Appointment not found.';
            return;
        }

        if (!isset($_SESSION['user']) || ($_SESSION['user']['role'] !== 'admin' && $_SESSION['user']['id'] != $appointment['user_id'])) {
            http_response_code(403);
            echo 'Unauthorized.';
            return;
        }

        if ($this->rdv->update($id, ['statut' => 'annule'])) {
            $_SESSION['success'] = 'Rendez-vous annulé';
        } else {
            $_SESSION['error'] = 'Erreur lors de l\'annulation';
        }

        header('Location: ' . $GLOBALS['baseUrl'] . '?route=rendez_vous/myAppointments');
        exit;
    }

    /**
     * Delete appointment (admin only)
     */
    public function delete()
    {
        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
            http_response_code(403);
            echo 'Unauthorized.';
            return;
        }

        $id = (int)($_GET['id'] ?? 0);
        if (!$id) {
            http_response_code(400);
            echo 'Invalid ID.';
            return;
        }

        if ($this->rdv->delete($id)) {
            $_SESSION['success'] = 'Rendez-vous supprimé';
        } else {
            $_SESSION['error'] = 'Erreur lors de la suppression';
        }

        header('Location: ' . $GLOBALS['baseUrl'] . '?route=rendez_vous/index');
        exit;
    }
}
?>
