<div class="card">
    <h1>Créer un signalement</h1>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-error">
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?php echo e($error); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form id="signalementForm" method="post" action="<?php echo BASE_URL; ?>/index.php?route=signalements/store" enctype="multipart/form-data" novalidate>
        <div class="grid grid-2">
            <div>
                <label for="titre">Titre</label>
                <input id="titre" name="titre" type="text" value="<?php echo e($old['titre'] ?? ''); ?>" placeholder="Ex: Nid de poule dangereux" required minlength="5" maxlength="255">
            </div>
            <div>
                <label for="categorie">Catégorie</label>
                <select id="categorie" name="categorie" required>
                    <option value="">Choisir</option>
                    <?php foreach (['route', 'eclairage', 'eau', 'transport', 'ordures', 'autre'] as $cat): ?>
                        <option value="<?php echo $cat; ?>" <?php echo (($old['categorie'] ?? '') === $cat) ? 'selected' : ''; ?>><?php echo ucfirst($cat); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <label for="description">Description</label>
        <textarea id="description" name="description" placeholder="Décrire le problème en détail" required minlength="10" maxlength="2000"><?php echo e($old['description'] ?? ''); ?></textarea>

        <div class="ai-assistant-box">
            <div class="ai-assistant-head">
                <strong>Assistant IA citoyen</strong>
                <button id="aiAssistBtn" type="button" class="btn-secondaire">Analyser mon brouillon</button>
            </div>
            <p class="ai-assistant-sub">Obtenez une catégorie suggérée, une priorité, un titre amélioré et les éléments manquants.</p>
            <div id="aiAssistResult" class="ai-assistant-result" style="display:none;"></div>
        </div>

        <div class="grid grid-2">
            <div>
                <label for="latitude">Latitude</label>
                <input id="latitude" name="latitude" type="text" value="<?php echo e($old['latitude'] ?? ''); ?>" placeholder="36.80650000" required>
            </div>
            <div>
                <label for="longitude">Longitude</label>
                <input id="longitude" name="longitude" type="text" value="<?php echo e($old['longitude'] ?? ''); ?>" placeholder="10.18150000" required>
            </div>
        </div>

        <div class="grid grid-2">
            <div>
                <label for="adresse">Adresse</label>
                <input id="adresse" name="adresse" type="text" value="<?php echo e($old['adresse'] ?? ''); ?>" placeholder="Ex: Rue de la Liberté, Tunis" required minlength="5" maxlength="255">
            </div>
            <div>
                <label for="quartier">Quartier</label>
                <input id="quartier" name="quartier" type="text" value="<?php echo e($old['quartier'] ?? ''); ?>" placeholder="Ex: Centre-ville" maxlength="120">
            </div>
        </div>

        <label for="image">Image (JPG/PNG, max 5Mo)</label>
        <input id="image" name="image" type="file" accept="image/jpeg,image/png">

        <p style="margin:0.75rem 0;">Cliquer sur la carte pour remplir latitude/longitude. L’adresse reste modifiable manuellement.</p>
        <p id="geoStatus" style="margin:0 0 0.6rem; color:#475569; font-size:0.9rem;"></p>
        <div id="map" style="height: 330px;"></div>

        <div id="formErrors" class="alert alert-error" style="display:none; margin-top: 12px;"></div>

        <div style="margin-top:1rem; display:flex; gap:8px;">
            <button type="submit" class="btn-principal">Enregistrer</button>
            <a class="btn-secondaire" href="<?php echo BASE_URL; ?>/index.php?route=signalements/list">Annuler</a>
        </div>
    </form>
</div>

<link rel="stylesheet" href="https://unpkg.com/maplibre-gl@4.7.1/dist/maplibre-gl.css" />
<script src="https://unpkg.com/maplibre-gl@4.7.1/dist/maplibre-gl.js"></script>
<script src="<?php echo BASE_URL; ?>/public/js/validation.js"></script>
<script>
const mapStyle = {
  version: 8,
  name: 'Create Form Map',
  metadata: { 'mapbox:autocomposite': true },
  sources: {
    'osm-tiles': {
      type: 'raster',
      tiles: ['https://a.tile.openstreetmap.org/{z}/{x}/{y}.png', 'https://b.tile.openstreetmap.org/{z}/{x}/{y}.png', 'https://c.tile.openstreetmap.org/{z}/{x}/{y}.png'],
      tileSize: 256
    }
  },
  layers: [
    {
      id: 'osm-bg',
      type: 'raster',
      source: 'osm-tiles',
      minzoom: 0,
      maxzoom: 22
    }
  ]
};

const map = new maplibregl.Map({
  container: 'map',
  style: mapStyle,
  center: [10.1815, 36.8065],
  zoom: 14,
  pitch: 45,
  bearing: -15
});

map.addControl(new maplibregl.NavigationControl(), 'top-right');

let marker = null;
const geoStatus = document.getElementById('geoStatus');
const latitudeField = document.getElementById('latitude');
const longitudeField = document.getElementById('longitude');
const adresseField = document.getElementById('adresse');
const quartierField = document.getElementById('quartier');

function setMarker(lat, lng) {
    if (marker) marker.remove();
    const el = document.createElement('div');
    el.style.width = '18px';
    el.style.height = '18px';
    el.style.borderRadius = '50%';
    el.style.background = '#166534';
    el.style.border = '3px solid #fff';
    el.style.boxShadow = '0 4px 12px rgba(0,0,0,0.4)';
    marker = new maplibregl.Marker({ element: el }).setLngLat([lng, lat]).addTo(map);
}

async function autofillAddress(lat, lng) {
    if (!geoStatus) return;
    geoStatus.textContent = 'Recherche de l\'adresse...';

    try {
        const url = `https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=${encodeURIComponent(lat)}&lon=${encodeURIComponent(lng)}&addressdetails=1`;
        const response = await fetch(url, {
            headers: {
                'Accept': 'application/json'
            }
        });

        if (!response.ok) {
            throw new Error('Reverse geocoding failed');
        }

        const data = await response.json();
        const address = data.address || {};

        const adresseCandidate = data.display_name || [
            address.road,
            address.house_number,
            address.suburb,
            address.city,
            address.town,
            address.village
        ].filter(Boolean).join(', ');

        const quartierCandidate = address.suburb || address.neighbourhood || address.quarter || address.city_district || '';

        const normalizedLat = Number(data.lat);
        const normalizedLng = Number(data.lon);
        if (!Number.isNaN(normalizedLat) && !Number.isNaN(normalizedLng)) {
            latitudeField.value = normalizedLat.toFixed(8);
            longitudeField.value = normalizedLng.toFixed(8);
            setMarker(normalizedLat, normalizedLng);
        }

        if (adresseField && !adresseField.value.trim() && adresseCandidate) {
            adresseField.value = adresseCandidate.substring(0, 255);
        }

        if (quartierField && !quartierField.value.trim() && quartierCandidate) {
            quartierField.value = quartierCandidate.substring(0, 120);
        }

        geoStatus.textContent = 'Adresse, quartier et coordonnées corrigés automatiquement.';
    } catch (err) {
        geoStatus.textContent = 'Adresse automatique indisponible. Vous pouvez saisir manuellement.';
    }
}

async function geocodeFromAddress() {
    const raw = adresseField ? adresseField.value.trim() : '';
    if (raw.length < 5) return;

    if (geoStatus) {
        geoStatus.textContent = 'Recherche des coordonnées depuis l\'adresse...';
    }

    try {
        const query = encodeURIComponent(raw + ', Tunis, Tunisia');
        const url = `https://nominatim.openstreetmap.org/search?format=jsonv2&limit=1&q=${query}`;
        const response = await fetch(url, {
            headers: {
                'Accept': 'application/json'
            }
        });

        if (!response.ok) {
            throw new Error('Forward geocoding failed');
        }

        const rows = await response.json();
        if (!Array.isArray(rows) || rows.length === 0) {
            if (geoStatus) {
                geoStatus.textContent = 'Adresse non trouvée automatiquement.';
            }
            return;
        }

        const top = rows[0];
        const lat = Number(top.lat);
        const lng = Number(top.lon);
        if (Number.isNaN(lat) || Number.isNaN(lng)) {
            if (geoStatus) {
                geoStatus.textContent = 'Coordonnées non disponibles pour cette adresse.';
            }
            return;
        }

        latitudeField.value = lat.toFixed(8);
        longitudeField.value = lng.toFixed(8);
        setMarker(lat, lng);
        map.easeTo({ center: [lng, lat], zoom: 16, duration: 500 });

        if (quartierField && !quartierField.value.trim() && top.display_name) {
            const possibleQuarter = top.display_name.split(',')[1];
            if (possibleQuarter) {
                quartierField.value = possibleQuarter.trim().substring(0, 120);
            }
        }

        if (geoStatus) {
            geoStatus.textContent = 'Coordonnées trouvées et positionnées depuis l\'adresse.';
        }
    } catch (err) {
        if (geoStatus) {
            geoStatus.textContent = 'Recherche de coordonnées indisponible actuellement.';
        }
    }
}

let geocodeDebounce = null;
if (adresseField) {
    adresseField.addEventListener('input', () => {
        if (geocodeDebounce) {
            clearTimeout(geocodeDebounce);
        }
        geocodeDebounce = setTimeout(() => {
            geocodeFromAddress();
        }, 700);
    });
}

map.on('click', (e) => {
    const lat = Number(e.lngLat.lat.toFixed(8));
    const lng = Number(e.lngLat.lng.toFixed(8));
    latitudeField.value = lat.toFixed(8);
    longitudeField.value = lng.toFixed(8);
    setMarker(lat, lng);
    autofillAddress(lat, lng);
});

const aiBtn = document.getElementById('aiAssistBtn');
const aiResult = document.getElementById('aiAssistResult');
const titreField = document.getElementById('titre');
const categorieField = document.getElementById('categorie');

function escapeHtml(value) {
    return String(value || '').replace(/[&<>"']/g, (char) => ({
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#39;'
    }[char]));
}

if (aiBtn && aiResult) {
    aiBtn.addEventListener('click', async () => {
        const payload = {
            titre: titreField ? titreField.value.trim() : '',
            description: document.getElementById('description') ? document.getElementById('description').value.trim() : '',
            categorie: categorieField ? categorieField.value.trim() : '',
            adresse: adresseField ? adresseField.value.trim() : '',
            quartier: quartierField ? quartierField.value.trim() : ''
        };

        aiBtn.disabled = true;
        aiBtn.textContent = 'Analyse en cours...';

        try {
            const response = await fetch('<?php echo BASE_URL; ?>/index.php?route=signalements/ai-assist', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify(payload)
            });

            if (!response.ok) {
                throw new Error('AI request failed');
            }

            const json = await response.json();
            if (!json || !json.ok || !json.analysis) {
                throw new Error('Invalid AI response');
            }

            const analysis = json.analysis;
            const missing = Array.isArray(analysis.missing_fields) ? analysis.missing_fields : [];

            aiResult.style.display = 'block';
            aiResult.innerHTML = `
                <div class="ai-line"><strong>Titre suggéré:</strong> ${escapeHtml(analysis.suggested_title || '')}</div>
                <div class="ai-line"><strong>Catégorie suggérée:</strong> ${escapeHtml(analysis.suggested_category || '')}</div>
                <div class="ai-line"><strong>Priorité estimée:</strong> <span class="badge triage-${escapeHtml(analysis.priority || 'faible')}">${escapeHtml(analysis.priority || 'faible')}</span></div>
                <div class="ai-line"><strong>Résumé admin:</strong> ${escapeHtml(analysis.admin_summary || '')}</div>
                <div class="ai-line"><strong>Champs à compléter:</strong> ${missing.length ? escapeHtml(missing.join(' | ')) : 'Aucun'}</div>
                <div style="margin-top:0.6rem;">
                    <button id="aiApplyBtn" type="button" class="btn-principal">Appliquer suggestions</button>
                </div>
            `;

            const applyBtn = document.getElementById('aiApplyBtn');
            if (applyBtn) {
                applyBtn.addEventListener('click', () => {
                    if (titreField && analysis.suggested_title) {
                        titreField.value = analysis.suggested_title;
                    }
                    if (categorieField && analysis.suggested_category) {
                        categorieField.value = analysis.suggested_category;
                    }
                });
            }
        } catch (error) {
            aiResult.style.display = 'block';
            aiResult.innerHTML = '<div class="ai-line">Assistant IA indisponible pour le moment. Reessayez dans quelques secondes.</div>';
        } finally {
            aiBtn.disabled = false;
            aiBtn.textContent = 'Analyser mon brouillon';
        }
    });
}
</script>
