<div class="card">
	<h1>Créer un signalement</h1>
	<p>Décrivez le problème, ajoutez une localisation et envoyez votre demande.</p>

	<?php if (!empty($errors)): ?>
		<div class="alert alert-error">
			<ul>
				<?php foreach ($errors as $error): ?>
					<li><?php echo e($error); ?></li>
				<?php endforeach; ?>
			</ul>
		</div>
	<?php endif; ?>

	<form class="form-grid" method="post" action="<?php echo BASE_URL; ?>/index.php?route=signalements/store" enctype="multipart/form-data">
		<label>
			<span>Titre</span>
			<input type="text" name="titre" value="<?php echo e($old['titre'] ?? ''); ?>" required>
		</label>

		<label>
			<span>Description</span>
			<textarea name="description" rows="5" required><?php echo e($old['description'] ?? ''); ?></textarea>
		</label>

		<label>
			<span>Catégorie</span>
			<select name="categorie" required>
				<?php $selectedCategorie = $old['categorie'] ?? ''; ?>
				<?php foreach (['route', 'eclairage', 'eau', 'transport', 'ordures', 'autre'] as $categorie): ?>
					<option value="<?php echo $categorie; ?>" <?php echo $selectedCategorie === $categorie ? 'selected' : ''; ?>><?php echo ucfirst($categorie); ?></option>
				<?php endforeach; ?>
			</select>
		</label>

		<label>
			<span>Adresse</span>
			<input type="text" name="adresse" value="<?php echo e($old['adresse'] ?? ''); ?>" required>
		</label>

		<label>
			<span>Quartier</span>
			<input type="text" name="quartier" value="<?php echo e($old['quartier'] ?? ''); ?>" required>
		</label>

		<div class="grid grid-2">
			<label>
				<span>Latitude</span>
				<input type="text" id="latitude" name="latitude" value="<?php echo e($old['latitude'] ?? ''); ?>" required>
			</label>
			<label>
				<span>Longitude</span>
				<input type="text" id="longitude" name="longitude" value="<?php echo e($old['longitude'] ?? ''); ?>" required>
			</label>
		</div>

		<label>
			<span>Localisation sur la carte (cliquez pour sélectionner)</span>
		</label>

		<label>
			<span>Image</span>
			<input type="file" name="image" accept="image/*">
		</label>

		<div class="form-actions">
			<button type="submit" class="btn-principal">Envoyer le signalement</button>
			<a class="btn-secondaire" href="<?php echo BASE_URL; ?>/index.php?route=signalements/list">Retour</a>
		</div>
	</form>
</div>

<section class="card">
	<div id="mapStatus" class="map-status" aria-live="polite">Initialisation de la carte...</div>
	<div id="signalementMap"></div>
</section>

<link id="maplibre-css" rel="stylesheet" href="https://unpkg.com/maplibre-gl@4.7.1/dist/maplibre-gl.css" onerror="this.onerror=null;this.href='https://cdn.jsdelivr.net/npm/maplibre-gl@4.7.1/dist/maplibre-gl.css';" />
<script src="https://unpkg.com/maplibre-gl@4.7.1/dist/maplibre-gl.js" onerror="this.onerror=null;var s=document.createElement('script');s.src='https://cdn.jsdelivr.net/npm/maplibre-gl@4.7.1/dist/maplibre-gl.js';document.head.appendChild(s);"></script>

<script>
(function() {
  if (typeof maplibregl === 'undefined') {
    setTimeout(arguments.callee, 100);
    return;
  }

  const mapContainer = document.getElementById('signalementMap');
  const statusEl = document.getElementById('mapStatus');
  const latInput = document.getElementById('latitude');
  const lngInput = document.getElementById('longitude');
  const adresseInput = document.querySelector('input[name="adresse"]');
  const quartierInput = document.querySelector('input[name="quartier"]');

  // Coordonnées par défaut (Tunis)
  let defaultLat = parseFloat(latInput.value) || 36.8065;
  let defaultLng = parseFloat(lngInput.value) || 10.1615;

  // Même style que home.php/map.js
  const mapStyle = {
    version: 8,
    name: 'Smart Municipality Map',
    sources: {
      'osm-raster': {
        type: 'raster',
        tiles: [
          'https://a.tile.openstreetmap.org/{z}/{x}/{y}.png',
          'https://b.tile.openstreetmap.org/{z}/{x}/{y}.png',
          'https://c.tile.openstreetmap.org/{z}/{x}/{y}.png'
        ],
        tileSize: 256
      }
    },
    layers: [
      {
        id: 'osm-background',
        type: 'raster',
        source: 'osm-raster',
        minzoom: 0,
        maxzoom: 22
      }
    ]
  };

  const map = new maplibregl.Map({
    container: mapContainer,
    style: mapStyle,
    center: [defaultLng, defaultLat],
    zoom: 13
  });

  // Marqueur pour afficher la sélection
  let marker = null;

  function setMapStatus(text, type) {
    if (!statusEl) return;
    statusEl.className = 'map-status';
    if (type === 'loading') {
      statusEl.classList.add('is-loading');
    }
    if (type === 'error') {
      statusEl.classList.add('is-error');
    }
    statusEl.textContent = text;
  }

  function clearMapStatus() {
    if (!statusEl) return;
    statusEl.className = 'map-status';
    statusEl.textContent = '';
  }

  function updateMarker(lat, lng) {
    if (marker) {
      marker.remove();
    }

    marker = new maplibregl.Marker({ color: '#FF0000' })
      .setLngLat([lng, lat])
      .addTo(map);

    // Centrer la map sur le marqueur
    map.flyTo({
      center: [lng, lat],
      zoom: 15
    });
  }

  // Initialiser le marqueur s'il y a des coordonnées par défaut
  if (latInput.value && lngInput.value) {
    updateMarker(defaultLat, defaultLng);
  }

  function fetchLocalisationData(lat, lng) {
    const url = new URL('<?php echo BASE_URL; ?>/index.php', window.location.origin);
    url.searchParams.set('route', 'map/findLocalisation');
    url.searchParams.set('latitude', lat);
    url.searchParams.set('longitude', lng);

    // Première tentative: recherche locale dans notre DB
    fetch(url)
      .then(res => res.json())
      .then(data => {
        console.debug('map/findLocalisation response:', data);
        if (data.ok && data.localisation) {
          const loc = data.localisation;
          if (adresseInput && loc.adresse) {
            adresseInput.value = loc.adresse;
            adresseInput.classList.remove('auto-filled-error');
          }
          if (quartierInput && loc.quartier) {
            quartierInput.value = loc.quartier;
            quartierInput.classList.remove('auto-filled-error');
          }
          return;
        }

        // Si aucune localisation en base, utiliser Nominatim comme fallback
        const nominatimUrl = `https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=${encodeURIComponent(lat)}&lon=${encodeURIComponent(lng)}&accept-language=fr`;
        return fetch(nominatimUrl, { headers: { 'Referer': window.location.origin } })
          .then(r => r.json())
          .then(geo => {
            console.debug('nominatim response:', geo);
            if (geo && geo.address) {
              // Construire une adresse simple
              const a = [];
              if (geo.address.road) a.push(geo.address.road);
              if (geo.address.house_number) a.push(geo.address.house_number);
              if (geo.address.postcode) a.push(geo.address.postcode);
              if (geo.address.city) a.push(geo.address.city);
              const adresseStr = a.join(' ').trim();
              const quartierStr = geo.address.neighbourhood || geo.address.suburb || geo.address.city_district || geo.address.village || '';

              if (adresseInput && adresseStr) {
                adresseInput.value = adresseStr;
                adresseInput.classList.remove('auto-filled-error');
              }
              if (quartierInput && quartierStr) {
                quartierInput.value = quartierStr;
                quartierInput.classList.remove('auto-filled-error');
              }
            } else {
              if (adresseInput) adresseInput.classList.add('auto-filled-error');
              if (quartierInput) quartierInput.classList.add('auto-filled-error');
            }
          })
          .catch(err => {
            console.error('Erreur Nominatim:', err);
            if (adresseInput) adresseInput.classList.add('auto-filled-error');
            if (quartierInput) quartierInput.classList.add('auto-filled-error');
          });
      })
      .catch(err => {
        console.error('Erreur récupération localisation:', err);
      });
  }

  // Écouter les clics sur la map
  map.on('click', function(e) {
    const lat = e.lngLat.lat.toFixed(6);
    const lng = e.lngLat.lng.toFixed(6);

    // Remplir les champs
    latInput.value = lat;
    lngInput.value = lng;

    // Essayer de pré-remplir adresse et quartier
    fetchLocalisationData(parseFloat(lat), parseFloat(lng));

    // Mettre à jour le marqueur
    updateMarker(lat, lng);
  });

  // Mettre à jour le marqueur si les champs sont modifiés directement
  latInput.addEventListener('change', function() {
    const lat = parseFloat(this.value);
    const lng = parseFloat(lngInput.value);
    if (!isNaN(lat) && !isNaN(lng)) {
      updateMarker(lat, lng);
      fetchLocalisationData(lat, lng);
    }
  });

  lngInput.addEventListener('change', function() {
    const lat = parseFloat(latInput.value);
    const lng = parseFloat(this.value);
    if (!isNaN(lat) && !isNaN(lng)) {
      updateMarker(lat, lng);
      fetchLocalisationData(lat, lng);
    }
  });

  // Écouter les événements de la carte
  map.on('load', function() {
    clearMapStatus();
  });

  map.on('error', function(e) {
    console.error('Erreur carte:', e);
    setMapStatus('Erreur lors du chargement de la carte', 'error');
  });
})();
</script>