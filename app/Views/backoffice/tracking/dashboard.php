<div class="container-fluid mt-4">
    <h1>🗺️ Tableau de Bord Tracking</h1>
    <p class="text-muted">Positions en temps réel des équipes en intervention</p>

    <div class="row">
        <div class="col-md-8">
            <div id="trackingMap" style="height: 600px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);"></div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">📍 Équipes Actives</h5>
                </div>
                <div class="card-body" style="max-height: 600px; overflow-y: auto;">
                    <?php if (empty($positions)): ?>
                        <p class="text-muted">Aucune équipe en mission actuellement.</p>
                    <?php else: ?>
                        <?php foreach ($positions as $pos): ?>
                            <div class="mb-3 p-2 border rounded" style="cursor: pointer;" onclick="flyToTeam(<?= $pos['latitude'] ?>, <?= $pos['longitude'] ?>);">
                                <h6 class="mb-1">
                                    <span class="badge bg-success">●</span>
                                    <?= htmlspecialchars($pos['nom_equipe'] ?? 'Équipe ' . $pos['equipe_id']) ?>
                                </h6>
                                <small class="d-block text-muted">
                                    <strong>Lat:</strong> <?= number_format($pos['latitude'], 6) ?><br>
                                    <strong>Lon:</strong> <?= number_format($pos['longitude'], 6) ?><br>
                                    <strong>Précision:</strong> <?= $pos['precision'] ? number_format($pos['precision'], 1) . 'm' : 'N/A' ?><br>
                                    <strong>Vitesse:</strong> <?= $pos['vitesse'] ? number_format($pos['vitesse'], 1) . ' km/h' : 'N/A' ?><br>
                                    <strong>À:</strong> <?= date('H:i:s', strtotime($pos['created_at'])) ?>
                                </small>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header">
                    <h5 class="mb-0">⚙️ Contrôles</h5>
                </div>
                <div class="card-body">
                    <button class="btn btn-sm btn-primary w-100 mb-2" onclick="refreshPositions()">
                        🔄 Rafraîchir
                    </button>
                    <button class="btn btn-sm btn-secondary w-100" onclick="resetMap()">
                        🏠 Réinitialiser vue
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<link rel="stylesheet" href="https://unpkg.com/maplibre-gl@4.7.1/dist/maplibre-gl.css" />
<script src="https://unpkg.com/maplibre-gl@4.7.1/dist/maplibre-gl.js"></script>

<script>
    // Initialiser MapLibre GL
    const map = new maplibregl.Map({
        container: 'trackingMap',
        style: 'https://demotiles.maplibre.org/style.json',
        center: [10.1615, 36.8065],
        zoom: 13
    });

    const markers = {};

    // Ajouter les marqueurs
    function addMarkers() {
        <?php foreach ($positions as $pos): ?>
            const teamId = <?= (int)$pos['equipe_id'] ?>;
            const lat = <?= (float)$pos['latitude'] ?>;
            const lon = <?= (float)$pos['longitude'] ?>;
            const nom = "<?= addslashes($pos['nom_equipe'] ?? 'Équipe ' . $pos['equipe_id']) ?>";

            const el = document.createElement('div');
            el.className = 'marker-team';
            el.style.cssText = `
                width: 40px;
                height: 40px;
                background: linear-gradient(135deg, #007bff, #0056b3);
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                color: white;
                font-weight: bold;
                font-size: 14px;
                box-shadow: 0 2px 8px rgba(0,0,0,0.3);
                cursor: pointer;
                border: 3px solid white;
            `;
            el.textContent = teamId;
            el.title = nom;

            el.addEventListener('click', () => {
                alert(`Équipe: ${nom}\nLat: ${lat.toFixed(6)}\nLon: ${lon.toFixed(6)}`);
            });

            const marker = new maplibregl.Marker({element: el})
                .setLngLat([lon, lat])
                .addTo(map);

            markers[teamId] = marker;
        <?php endforeach; ?>
    }

    function flyToTeam(lat, lon) {
        map.flyTo({center: [lon, lat], zoom: 15, duration: 1000});
    }

    function refreshPositions() {
        location.reload();
    }

    function resetMap() {
        map.flyTo({center: [10.1615, 36.8065], zoom: 13});
    }

    // Initialiser
    map.on('load', addMarkers);

    // Auto-refresh toutes les 30s
    setInterval(() => {
        fetch('/index.php?route=api/getPositions')
            .then(r => r.json())
            .then(positions => {
                positions.forEach(pos => {
                    const teamId = pos.equipe_id;
                    if (markers[teamId]) {
                        markers[teamId].setLngLat([pos.longitude, pos.latitude]);
                    }
                });
            })
            .catch(e => console.error('Position fetch error:', e));
    }, 30000);
</script>

<style>
    .marker-team {
        animation: pulse 2s infinite;
    }

    @keyframes pulse {
        0% { transform: scale(1); opacity: 1; }
        50% { transform: scale(1.1); opacity: 0.9; }
        100% { transform: scale(1); opacity: 1; }
    }

    #trackingMap {
        position: relative;
    }
</style>
