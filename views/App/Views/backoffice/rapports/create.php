<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h3 class="mb-0">📝 Créer un Rapport</h3>
                </div>

                <form method="POST" class="card-body">
                    <div class="mb-3">
                        <label class="form-label"><strong>Type de rapport</strong></label>
                        <div class="d-flex gap-2">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="type" value="mensuel" id="typeMensuel" checked>
                                <label class="form-check-label" for="typeMensuel">
                                    📅 Mensuel
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="type" value="trimestriel" id="typeTrimestriel">
                                <label class="form-check-label" for="typeTrimestriel">
                                    📊 Trimestriel
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="type" value="annuel" id="typeAnnuel">
                                <label class="form-check-label" for="typeAnnuel">
                                    📈 Annuel
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="dateDebut"><strong>Date de début</strong></label>
                        <input type="date" class="form-control" id="dateDebut" name="periode_debut" required
                               value="<?= date('Y-m-01') ?>">
                        <small class="text-muted">Sélectionnez le 1er jour de la période</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="dateFin"><strong>Date de fin</strong></label>
                        <input type="date" class="form-control" id="dateFin" name="periode_fin" required
                               value="<?= date('Y-m-t') ?>">
                        <small class="text-muted">Sélectionnez le dernier jour de la période</small>
                    </div>

                    <div class="alert alert-info">
                        <strong>ℹ️ Le rapport comprendra:</strong>
                        <ul class="mb-0">
                            <li>📋 Statistiques des signalements</li>
                            <li>🔧 Statistiques des interventions</li>
                            <li>💰 Analyse des coûts</li>
                            <li>⏱️ Durée des interventions</li>
                            <li>📊 Breakdown par catégorie</li>
                        </ul>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary btn-lg">
                            ✨ Générer le Rapport
                        </button>
                        <a href="<?php echo BASE_URL; ?>/index.php?route=rapport/list" class="btn btn-outline-secondary">
                            Annuler
                        </a>
                    </div>
                </form>
            </div>

            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="mb-0">💡 Conseils</h5>
                </div>
                <div class="card-body small">
                    <p>
                        <strong>Rapports mensuels:</strong> Utiles pour le suivi mensuel de l'activité et l'identification des tendances.
                    </p>
                    <p>
                        <strong>Rapports trimestriels:</strong> Permettent une vue consolidée sur 3 mois pour évaluer la progression.
                    </p>
                    <p>
                        <strong>Rapports annuels:</strong> Rapport complet de l'année pour analyses stratégiques et archivage.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Auto-remplir la date de fin en fonction du type de rapport
    document.querySelectorAll('input[name="type"]').forEach(radio => {
        radio.addEventListener('change', () => {
            const type = radio.value;
            const debut = new Date(document.getElementById('dateDebut').value);
            const fin = new Date(debut);

            if (type === 'mensuel') {
                fin.setMonth(fin.getMonth() + 1);
                fin.setDate(0);
            } else if (type === 'trimestriel') {
                fin.setMonth(fin.getMonth() + 3);
                fin.setDate(0);
            } else if (type === 'annuel') {
                fin.setFullYear(fin.getFullYear() + 1);
                fin.setDate(0);
            }

            document.getElementById('dateFin').value = fin.toISOString().split('T')[0];
        });
    });
</script>
