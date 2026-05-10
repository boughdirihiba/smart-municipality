<div class="container-fluid mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1><?= htmlspecialchars($rapport['titre']) ?></h1>
            <p class="text-muted">
                <?= htmlspecialchars(ucfirst($rapport['type'])) ?> 
                • <?= htmlspecialchars($rapport['periode_debut']) ?> à <?= htmlspecialchars($rapport['periode_fin']) ?>
            </p>
        </div>
        <div>
            <span class="badge bg-<?= $rapport['status'] === 'termine' ? 'success' : 'warning' ?>">
                <?= htmlspecialchars($rapport['status']) ?>
            </span>
            <?php if ($rapport['fichier_pdf']): ?>
                <a href="/index.php?route=rapport/downloadPdf&id=<?= $rapport['id'] ?>" class="btn btn-primary ms-2">
                    📥 Télécharger PDF
                </a>
            <?php endif; ?>
            <a href="<?php echo BASE_URL; ?>/index.php?route=rapport/list" class="btn btn-outline-secondary ms-2">
                ← Retour
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <?php if ($rapport['contenu']): ?>
                <div class="card">
                    <div class="card-body">
                        <div style="border: 1px solid #ddd; padding: 20px; background: #fafafa; border-radius: 8px;">
                            <?= $rapport['contenu'] ?>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="alert alert-info">
                    Le rapport est en cours de génération...
                </div>
            <?php endif; ?>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">📊 Résumé Exécutif</h5>
                </div>
                <div class="card-body">
                    <?php 
                        $metrics = is_string($rapport['metriques']) 
                            ? json_decode($rapport['metriques'], true) 
                            : $rapport['metriques'] ?? [];
                    ?>

                    <?php if (!empty($metrics)): ?>
                        <div class="mb-3">
                            <h6>📋 Signalements</h6>
                            <div class="small">
                                <div>Total: <strong><?= $metrics['signalements']['total'] ?? 0 ?></strong></div>
                                <div>Résolus: <strong><?= $metrics['signalements']['resolus'] ?? 0 ?></strong></div>
                                <div>Taux: <strong><?= ($metrics['signalements']['taux_resolution'] ?? 0) ?>%</strong></div>
                            </div>
                        </div>

                        <hr>

                        <div class="mb-3">
                            <h6>🔧 Interventions</h6>
                            <div class="small">
                                <div>Total: <strong><?= $metrics['interventions']['total'] ?? 0 ?></strong></div>
                                <div>Terminées: <strong><?= $metrics['interventions']['terminees'] ?? 0 ?></strong></div>
                                <div>Progression: <strong><?= ($metrics['interventions']['progression_moyenne'] ?? 0) ?>%</strong></div>
                            </div>
                        </div>

                        <hr>

                        <div class="mb-3">
                            <h6>💰 Coûts</h6>
                            <div class="small">
                                <div>Total: <strong><?= number_format($metrics['couts']['cout_total'] ?? 0, 2) ?> TND</strong></div>
                                <div>Moyen: <strong><?= number_format($metrics['couts']['cout_moyen'] ?? 0, 2) ?> TND</strong></div>
                                <div>Min/Max: <strong><?= number_format($metrics['couts']['cout_min'] ?? 0, 2) ?> / <?= number_format($metrics['couts']['cout_max'] ?? 0, 2) ?> TND</strong></div>
                            </div>
                        </div>

                        <hr>

                        <div class="mb-3">
                            <h6>⏱️ Temps Équipes</h6>
                            <div class="small">
                                <div>Total: <strong><?= $metrics['time_tracking']['total_sessions'] ?? 0 ?></strong> sessions</div>
                                <div>Heures: <strong><?= $metrics['time_tracking']['total_heures'] ?? 0 ?></strong> h</div>
                                <div>Moyenne: <strong><?= $metrics['time_tracking']['moyenne_minutes'] ?? 0 ?></strong> min</div>
                            </div>
                        </div>
                    <?php else: ?>
                        <p class="text-muted">Métriques indisponibles</p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header">
                    <h5 class="mb-0">ℹ️ Informations</h5>
                </div>
                <div class="card-body small">
                    <div class="mb-2">
                        <strong>Créé:</strong><br>
                        <?= date('d/m/Y H:i', strtotime($rapport['created_at'])) ?>
                    </div>
                    <div class="mb-2">
                        <strong>Modifié:</strong><br>
                        <?= date('d/m/Y H:i', strtotime($rapport['updated_at'])) ?>
                    </div>
                    <div class="mb-2">
                        <strong>ID Rapport:</strong><br>
                        <code>#<?= $rapport['id'] ?></code>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .card {
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        border: 1px solid #e0e0e0;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        margin: 10px 0;
    }

    table, th, td {
        border: 1px solid #ddd;
    }

    th {
        background-color: #f2f2f2;
        padding: 10px;
        text-align: left;
    }

    td {
        padding: 8px;
    }

    tr:nth-child(even) {
        background-color: #f9f9f9;
    }
</style>
