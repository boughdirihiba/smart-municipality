<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h3 class="mb-0">➕ Créer une Équipe</h3>
                </div>

                <form method="POST" class="card-body">
                    <div class="mb-3">
                        <label class="form-label" for="nom"><strong>Nom de l'équipe *</strong></label>
                        <input type="text" class="form-control" id="nom" name="nom" required placeholder="Ex: Équipe Route Nord">
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="description"><strong>Description</strong></label>
                        <textarea class="form-control" id="description" name="description" rows="3" placeholder="Description de l'équipe..."></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="type"><strong>Type d'intervention *</strong></label>
                        <select class="form-select" id="type" name="type_intervention" required>
                            <option value="">Sélectionner un type</option>
                            <option value="route">🛣️ Route</option>
                            <option value="eclairage">💡 Éclairage</option>
                            <option value="eau">💧 Eau</option>
                            <option value="transport">🚌 Transport</option>
                            <option value="ordures">🗑️ Ordures</option>
                            <option value="autre">📌 Autre</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="agents"><strong>Nombre d'agents</strong></label>
                        <input type="number" class="form-control" id="agents" name="nombre_agents" value="1" min="1" max="20">
                    </div>

                    <div class="alert alert-info">
                        <strong>ℹ️ Note:</strong> Vous pourrez ajouter les agents à cette équipe après sa création.
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary btn-lg">
                            ✅ Créer l'Équipe
                        </button>
                        <a href="/index.php?route=tracking/teams" class="btn btn-outline-secondary">
                            Annuler
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
