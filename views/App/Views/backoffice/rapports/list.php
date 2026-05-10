<div class="container-fluid mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1>📊 Rapports</h1>
            <p class="text-muted">Rapports mensuels, trimestriels et annuels</p>
        </div>
        <a href="<?php echo BASE_URL; ?>/index.php?route=rapport/create" class="btn btn-primary btn-lg">
            ➕ Nouveau Rapport
        </a>
    </div>

    <?php if ($type): ?>
        <div class="alert alert-info">
            Filtre: <strong><?= htmlspecialchars($type) ?></strong>
            <a href="<?php echo BASE_URL; ?>/index.php?route=rapport/list" class="ms-2">Réinitialiser</a>
        </div>
    <?php endif; ?>

    <div class="mb-3">
        <div class="btn-group" role="group">
            <a href="<?php echo BASE_URL; ?>/index.php?route=rapport/list&type=mensuel" class="btn btn-outline-secondary <?= $type === 'mensuel' ? 'active' : '' ?>">Mensuel</a>
            <a href="<?php echo BASE_URL; ?>/index.php?route=rapport/list&type=trimestriel" class="btn btn-outline-secondary <?= $type === 'trimestriel' ? 'active' : '' ?>">Trimestriel</a>
            <a href="<?php echo BASE_URL; ?>/index.php?route=rapport/list&type=annuel" class="btn btn-outline-secondary <?= $type === 'annuel' ? 'active' : '' ?>">Annuel</a>
            <a href="<?php echo BASE_URL; ?>/index.php?route=rapport/list" class="btn btn-outline-secondary <?= empty($type) ? 'active' : '' ?>">Tous</a>
        </div>
    </div>

    <?php if (empty($rapports)): ?>
        <div class="alert alert-info">
            Aucun rapport trouvé. <a href="<?php echo BASE_URL; ?>/index.php?route=rapport/create">Créer un rapport</a>
        </div>
    <?php else: ?>
        <div class="row">
            <?php foreach ($rapports as $rapport): ?>
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h5 class="card-title mb-0"><?= htmlspecialchars($rapport['titre']) ?></h5>
                                <span class="badge bg-<?= 
                                    $rapport['status'] === 'termine' ? 'success' : 
                                    ($rapport['status'] === 'en_generation' ? 'warning' : 'danger')
                                ?>">
                                    <?= htmlspecialchars($rapport['status']) ?>
                                </span>
                            </div>

                            <div class="small text-muted mb-3">
                                <strong>Type:</strong> <?= htmlspecialchars(ucfirst($rapport['type'])) ?><br>
                                <strong>Période:</strong> <?= htmlspecialchars($rapport['periode_debut']) ?> → <?= htmlspecialchars($rapport['periode_fin']) ?><br>
                                <strong>Créé:</strong> <?= date('d/m/Y H:i', strtotime($rapport['created_at'])) ?>
                            </div>

                            <?php if ($rapport['metriques']): ?>
                                <?php $metrics = json_decode($rapport['metriques'], true) ?? []; ?>
                                <div class="row small text-center mb-3">
                                    <div class="col-4">
                                        <div class="text-primary"><strong><?= $metrics['signalements']['total'] ?? 0 ?></strong></div>
                                        <div class="text-muted">Signalements</div>
                                    </div>
                                    <div class="col-4">
                                        <div class="text-warning"><strong><?= $metrics['interventions']['total'] ?? 0 ?></strong></div>
                                        <div class="text-muted">Interventions</div>
                                    </div>
                                    <div class="col-4">
                                        <div class="text-success"><strong><?= number_format($metrics['couts']['cout_total'] ?? 0, 0) ?></strong></div>
                                        <div class="text-muted">TND</div>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <div class="d-grid gap-2">
                                <a href="/index.php?route=rapport/view&id=<?= $rapport['id'] ?>" class="btn btn-sm btn-primary">
                                    👁️ Consulter
                                </a>
                                <?php if ($rapport['fichier_pdf']): ?>
                                    <a href="/index.php?route=rapport/downloadPdf&id=<?= $rapport['id'] ?>" class="btn btn-sm btn-outline-secondary">
                                        📥 Télécharger PDF
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
