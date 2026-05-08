<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Rapport;

class RapportController extends Controller
{
    private Rapport $rapportModel;

    public function __construct()
    {
        $this->rapportModel = new Rapport();
    }

    private function ensureAdmin(): void
    {
        if (($_SESSION['user']['role'] ?? 'citoyen') !== 'admin') {
            set_flash('error', 'Accès réservé aux administrateurs.');
            redirect('home/index');
        }
    }

    /**
     * Liste des rapports
     */
    public function list(): void
    {
        $this->ensureAdmin();

        $type = trim((string)($_GET['type'] ?? ''));
        $rapports = $this->rapportModel->all($type ?: null);

        $this->render('backoffice/rapports/list', [
            'title' => 'Rapports',
            'rapports' => $rapports,
            'type' => $type,
        ]);
    }

    /**
     * Créer un nouveau rapport
     */
    public function create(): void
    {
        $this->ensureAdmin();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $type = trim((string)($_POST['type'] ?? 'mensuel'));
            $periodDebut = trim((string)($_POST['periode_debut'] ?? ''));
            $periodFin = trim((string)($_POST['periode_fin'] ?? ''));

            if (empty($periodDebut) || empty($periodFin)) {
                set_flash('error', 'Les périodes sont requises.');
                redirect('rapport/create');
                return;
            }

            // Créer le rapport
            $rapportId = $this->rapportModel->create([
                'titre' => 'Rapport ' . ucfirst($type) . ' - ' . $periodDebut . ' à ' . $periodFin,
                'type' => $type,
                'periode_debut' => $periodDebut,
                'periode_fin' => $periodFin,
            ]);

            // Générer les métriques
            $metrics = $this->rapportModel->generateMetrics($rapportId, $periodDebut, $periodFin);

            // Générer le contenu HTML
            $html = $this->rapportModel->generateHtmlContent(
                $metrics,
                'Rapport ' . ucfirst($type),
                $periodDebut,
                $periodFin
            );

            // Générer le PDF
            $pdfFile = $this->generatePdf($rapportId, $html);

            // Mettre à jour le rapport
            $this->rapportModel->update($rapportId, [
                'contenu' => $html,
                'fichier_pdf' => $pdfFile,
                'metriques' => $metrics,
                'status' => 'termine',
            ]);

            set_flash('success', 'Rapport généré avec succès.');
            redirect('rapport/list');
        }

        $this->render('backoffice/rapports/create', [
            'title' => 'Créer un rapport',
        ]);
    }

    /**
     * Voir un rapport
     */
    public function view(): void
    {
        $this->ensureAdmin();

        $id = (int)($_GET['id'] ?? 0);
        $rapport = $this->rapportModel->find($id);

        if (!$rapport) {
            set_flash('error', 'Rapport introuvable.');
            redirect('rapport/list');
        }

        $this->render('backoffice/rapports/view', [
            'title' => $rapport['titre'],
            'rapport' => $rapport,
        ]);
    }

    /**
     * Télécharger le PDF
     */
    public function downloadPdf(): void
    {
        $this->ensureAdmin();

        $id = (int)($_GET['id'] ?? 0);
        $rapport = $this->rapportModel->find($id);

        if (!$rapport || empty($rapport['fichier_pdf'])) {
            http_response_code(404);
            echo 'Fichier introuvable.';
            return;
        }

        $filePath = BASE_PATH . '/public/rapports/' . basename($rapport['fichier_pdf']);

        if (!file_exists($filePath)) {
            http_response_code(404);
            echo 'Fichier non trouvé.';
            return;
        }

        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . basename($filePath) . '"');
        header('Content-Length: ' . filesize($filePath));

        readfile($filePath);
        exit;
    }

    /**
     * Générer un PDF via HTML2PDF (simple)
     */
    private function generatePdf(int $rapportId, string $html): ?string
    {
        try {
            $pdfDir = BASE_PATH . '/public/rapports';
            if (!is_dir($pdfDir)) {
                mkdir($pdfDir, 0755, true);
            }

            // Utiliser mPDF si disponible, sinon générer un HTML simple
            $fileName = 'rapport_' . $rapportId . '_' . date('Y-m-d_His') . '.pdf';
            $filePath = $pdfDir . '/' . $fileName;

            // Vérifier si mpdf est installé via composer
            if (class_exists('\Mpdf\Mpdf')) {
                $mpdf = new \Mpdf\Mpdf([
                    'mode' => 'utf-8',
                    'format' => 'A4',
                    'orientation' => 'P',
                ]);
                $mpdf->WriteHTML($html);
                $mpdf->Output($filePath, \Mpdf\Output\OutputDestination::FILE);
                return $fileName;
            }

            // Fallback: créer un HTML simple convertible
            $htmlContent = '<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Rapport</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        h1 { color: #333; border-bottom: 2px solid #007bff; padding-bottom: 10px; }
        h2 { color: #555; margin-top: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        table, th, td { border: 1px solid #ddd; }
        th { background-color: #f2f2f2; padding: 8px; }
        td { padding: 8px; }
        .footer { margin-top: 30px; font-size: 12px; color: #999; }
    </style>
</head>
<body>
' . $html . '
<div class="footer">
    <p>Rapport généré le ' . date('d/m/Y H:i') . '</p>
</div>
</body>
</html>';

            file_put_contents($filePath . '.html', $htmlContent);

            // Écrire aussi le HTML pour consultation directe
            return $fileName . '.html';
        } catch (\Throwable $e) {
            error_log('PDF generation error: ' . $e->getMessage());
            return null;
        }
    }
}
