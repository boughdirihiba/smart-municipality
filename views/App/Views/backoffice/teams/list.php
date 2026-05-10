<div class="container-fluid mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1>👥 Gestion des Équipes</h1>
            <p class="text-muted">Gestion centralisée des équipes d'intervention</p>
        </div>
        <a href="<?php echo BASE_URL; ?>/index.php?route=tracking/createTeam" class="btn btn-primary btn-lg">
            ➕ Nouvelle Équipe
        </a>
    </div>

    <?php if (isset($_SESSION['flash'])): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <?= htmlspecialchars($_SESSION['flash']['message']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['flash']); ?>
    <?php endif; ?>

    <div class="row">
        <?php if (empty($teams)): ?>
            <div class="col-12">
                <div class="alert alert-info">
                    Aucune équipe créée. <a href="<?php echo BASE_URL; ?>/index.php?route=tracking/createTeam">Créer une équipe</a>
                </div>
            </div>
        <?php else: ?>
            <?php foreach ($teams as $team): ?>
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card h-100" style="border-left: 4px solid #007bff;">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <h5 class="card-title mb-0"><?= htmlspecialchars($team['nom']) ?></h5>
                                <span class="badge bg-<?= $team['statut'] === 'disponible' ? 'success' : ($team['statut'] === 'en_mission' ? 'warning' : 'secondary') ?>">
                                    <?= htmlspecialchars($team['statut']) ?>
                                </span>
                            </div>

                            <p class="card-text text-muted small mb-3">
                                <?php if ($team['description']): ?>
                                    <?= htmlspecialchars(substr($team['description'], 0, 80)) ?>...
                                <?php endif; ?>
                            </p>

                            <div class="mb-3">
                                <strong>🏷️ Type:</strong> <code><?= htmlspecialchars($team['type_intervention']) ?></code>
                            </div>

                            <?php if (!empty($team['agents'])): ?>
                                <div class="mb-3">
                                    <strong>👤 Agents (<?= count($team['agents']) ?>):</strong>
                                    <ul class="small list-unstyled mt-2">
                                        <?php foreach ($team['agents'] as $agent): ?>
                                            <li>
                                                <span class="badge bg-info"><?= htmlspecialchars($agent['role'] ?? 'membre') ?></span>
                                                <?= htmlspecialchars($agent['prenom'] . ' ' . $agent['nom']) ?>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            <?php else: ?>
                                <div class="alert alert-sm alert-warning mb-3">
                                    Aucun agent assigné
                                </div>
                            <?php endif; ?>

                            <?php if ($team['derniere_position']): ?>
                                <div class="mb-3 p-2 bg-light rounded small">
                                    <strong>📍 Dernière position:</strong><br>
                                    Lat: <?= number_format($team['derniere_position']['latitude'], 6) ?><br>
                                    Lon: <?= number_format($team['derniere_position']['longitude'], 6) ?><br>
                                    <em><?= date('d/m/Y H:i', strtotime($team['derniere_position']['created_at'])) ?></em>
                                </div>
                            <?php else: ?>
                                <div class="alert alert-sm alert-secondary mb-3">
                                    Aucune position GPS enregistrée
                                </div>
                            <?php endif; ?>

                            <div class="d-grid gap-2">
                                <a href="/index.php?route=tracking/editTeam&id=<?= $team['id'] ?>" class="btn btn-sm btn-outline-primary">
                                    ✏️ Modifier
                                </a>
                                <button class="btn btn-sm btn-outline-danger" onclick="if(confirm('Supprimer cette équipe ?')) window.location='/index.php?route=tracking/deleteTeam&id=<?= $team['id'] ?>'">
                                    🗑️ Supprimer
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <hr class="my-5">

    <div class="card">
        <div class="card-header bg-light">
            <h5 class="mb-0">📊 Statistiques Globales</h5>
        </div>
        <div class="card-body">
            <div class="row text-center">
                <div class="col-md-3">
                    <h3 class="text-primary"><?= count($teams) ?></h3>
                    <p class="text-muted">Total équipes</p>
                </div>
                <div class="col-md-3">
                    <h3 class="text-success">
                        <?= count(array_filter($teams, fn($t) => $t['statut'] === 'disponible')) ?>
                    </h3>
                    <p class="text-muted">Disponibles</p>
                </div>
                <div class="col-md-3">
                    <h3 class="text-warning">
                        <?= count(array_filter($teams, fn($t) => $t['statut'] === 'en_mission')) ?>
                    </h3>
                    <p class="text-muted">En mission</p>
                </div>
                <div class="col-md-3">
                    <h3 class="text-info">
                        <?= array_sum(array_map(fn($t) => count($t['agents'] ?? []), $teams)) ?>
                    </h3>
                    <p class="text-muted">Total agents</p>
                </div>
            </div>
        </div>
    </div>
</div>
