(() => {
  const cfg = window.SMART_MAP_CONFIG || {};
  const statusEl = document.getElementById('mapStatus');

  const MAPLIBRE_WAIT_MS = 14000;
  const FETCH_TIMEOUT_MS = 12000;
  const FETCH_RETRIES = 1;
  const WORLD_CENTER = [0, 20];
  const WORLD_ZOOM = 1.45;
  const TUNISIA_COORDS = [9.5375, 33.8869];
  const TUNIS_COORDS = [10.1815, 36.8065];

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

  const markers = [];
  const sourceIds = {
    criticalZones: 'critical-zones-source',
    signalements: 'signalements-source',
    tunisia: 'tunisia-source'
  };

  let map = null;
  let maplibreRef = null;
  let is3D = true;
  let hasFocusedTunisia = false;

  function setMapStatus(text, type, withRetry, retryHandler) {
    if (!statusEl) return;

    statusEl.className = 'map-status';
    if (type === 'loading') {
      statusEl.classList.add('is-loading');
    }
    if (type === 'error') {
      statusEl.classList.add('is-error');
    }

    statusEl.innerHTML = '';
    statusEl.appendChild(document.createTextNode(text));

    if (withRetry) {
      const btn = document.createElement('button');
      btn.type = 'button';
      btn.textContent = 'Reessayer';
      btn.addEventListener('click', () => {
        if (typeof retryHandler === 'function') {
          retryHandler();
        }
      });
      statusEl.appendChild(btn);
    }
  }

  function clearMapStatus() {
    if (!statusEl) return;
    statusEl.className = 'map-status';
    statusEl.innerHTML = '';
  }

  function waitForMaplibre() {
    return new Promise((resolve, reject) => {
      if (window.maplibregl) {
        resolve(window.maplibregl);
        return;
      }

      const startedAt = Date.now();
      const timer = window.setInterval(() => {
        if (window.maplibregl) {
          window.clearInterval(timer);
          resolve(window.maplibregl);
          return;
        }

        if (Date.now() - startedAt > MAPLIBRE_WAIT_MS) {
          window.clearInterval(timer);
          reject(new Error('MapLibre indisponible'));
        }
      }, 150);
    });
  }

  async function fetchWithTimeout(url) {
    const controller = new AbortController();
    const timeoutId = window.setTimeout(() => {
      controller.abort();
    }, FETCH_TIMEOUT_MS);

    try {
      return await fetch(url, {
        signal: controller.signal,
        cache: 'no-store'
      });
    } finally {
      window.clearTimeout(timeoutId);
    }
  }

  function statusColor(statut) {
    if (statut === 'resolu') return 'green';
    if (statut === 'en_cours') return 'orange';
    if (statut === 'rejete') return 'red';
    return 'orange';
  }

  function priorityColor(priority, statut) {
    if (priority === 'urgent') return '#dc2626';
    if (priority === 'moyen') return '#f59e0b';
    if (priority === 'faible') return '#16a34a';

    return statusColor(statut);
  }

  function escapeHtml(v) {
    return String(v || '').replace(/[&<>"']/g, (m) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[m]));
  }

  function openStreetMapLink(lat, lng) {
    return `https://www.openstreetmap.org/?mlat=${encodeURIComponent(lat)}&mlon=${encodeURIComponent(lng)}#map=18/${encodeURIComponent(lat)}/${encodeURIComponent(lng)}`;
  }

  function buildSignalementPopup(it, lat, lng, showActions = true) {
    const detailsUrl = `${window.location.origin}${window.location.pathname}?route=signalements/detail&id=${encodeURIComponent(it.id)}`;
    const adminEditUrl = `${window.location.origin}${window.location.pathname}?route=admin/edit&id=${encodeURIComponent(it.id)}`;
    const imageHtml = it.image_url
      ? `<img src="${it.image_url}" alt="photo" style="width:100%; max-width:220px; height:110px; object-fit:cover; border-radius:12px; margin-top:8px; border:1px solid #dbe6ef;">`
      : '';

    return `
      <div style="min-width:210px; max-width:240px;">
        <div style="display:flex; align-items:flex-start; justify-content:space-between; gap:8px; margin-bottom:8px;">
          <div>
            <div style="display:inline-flex; align-items:center; gap:6px; padding:3px 8px; border-radius:999px; background:rgba(47,160,132,0.12); color:#0f3b2c; font-size:11px; font-weight:700; margin-bottom:6px;">Signalement</div>
            <h4 style="margin:0; font-size:0.92rem; color:#0f172a; line-height:1.2;">${escapeHtml(it.titre)}</h4>
          </div>
          <div style="flex:0 0 auto; width:10px; height:10px; border-radius:50%; background:${statusColor(it.statut)}; margin-top:4px;"></div>
        </div>

        <div style="display:grid; gap:5px; font-size:0.8rem; color:#334155;">
          <div><strong>Catégorie:</strong> ${escapeHtml(it.categorie)}</div>
          <div><strong>Statut:</strong> ${escapeHtml(it.statut)}</div>
          ${it.adresse ? `<div><strong>Adresse:</strong> ${escapeHtml(it.adresse)}</div>` : ''}
          ${it.quartier ? `<div><strong>Quartier:</strong> ${escapeHtml(it.quartier)}</div>` : ''}
          <div><strong>Coordonnées:</strong> ${lat.toFixed(5)}, ${lng.toFixed(5)}</div>
          <div style="line-height:1.45;">${escapeHtml(it.description)}</div>
        </div>

        ${imageHtml}

        ${showActions ? `
        <div style="display:flex; gap:6px; flex-wrap:wrap; margin-top:10px;">
          <a href="${detailsUrl}" style="display:inline-flex; align-items:center; justify-content:center; padding:7px 10px; background:linear-gradient(135deg,#2FA084,#0f3b2c); color:#fff; text-decoration:none; border-radius:999px; font-size:11px; font-weight:700; box-shadow:0 10px 18px rgba(15,59,44,0.18);">Voir détail</a>
          ${cfg.isAdmin ? `<a href="${adminEditUrl}" style="display:inline-flex; align-items:center; justify-content:center; padding:7px 10px; background:#1d4ed8; color:#fff; text-decoration:none; border-radius:999px; font-size:11px; font-weight:700;">Modifier</a>` : ''}
          <a href="${openStreetMapLink(lat, lng)}" target="_blank" rel="noopener" style="display:inline-flex; align-items:center; justify-content:center; padding:7px 10px; background:#e2e8f0; color:#0f172a; text-decoration:none; border-radius:999px; font-size:11px; font-weight:700;">Ouvrir</a>
        </div>` : ''}
      </div>
    `;
  }

  function clearMarkers() {
    markers.length = 0;
  }

  function markerElement(color) {
    const pin = document.createElement('div');
    pin.style.position = 'relative';
    pin.style.width = '22px';
    pin.style.height = '34px';
    pin.style.cursor = 'pointer';
    pin.style.pointerEvents = 'none';
    pin.style.willChange = 'transform';
    pin.style.backfaceVisibility = 'hidden';
    pin.style.WebkitBackfaceVisibility = 'hidden';

    const head = document.createElement('div');
    head.style.position = 'absolute';
    head.style.top = '0';
    head.style.left = '50%';
    head.style.transform = 'translateX(-50%)';
    head.style.width = '22px';
    head.style.height = '22px';
    head.style.borderRadius = '50%';
    head.style.background = color;
    head.style.border = '2px solid #ffffff';
    head.style.boxShadow = '0 4px 12px rgba(0,0,0,0.35)';

    const tip = document.createElement('div');
    tip.style.position = 'absolute';
    tip.style.left = '50%';
    tip.style.bottom = '0';
    tip.style.width = '0';
    tip.style.height = '0';
    tip.style.transform = 'translateX(-50%)';
    tip.style.borderLeft = '6px solid transparent';
    tip.style.borderRight = '6px solid transparent';
    tip.style.borderTop = `12px solid ${color}`;
    tip.style.filter = 'drop-shadow(0 3px 4px rgba(0,0,0,0.25))';

    pin.appendChild(head);
    pin.appendChild(tip);

    return pin;
  }

  function attachMarkerPopupInteractions(markerNode, fullPopup, lng, lat) {
    markerNode.addEventListener('click', (event) => {
      event.stopPropagation();
      zoomToSignalement(lng, lat);
      if (!fullPopup.isOpen()) {
        fullPopup.addTo(map);
      }
    });

    fullPopup.on('open', () => {
      window.setTimeout(() => {
        const popupElement = fullPopup.getElement?.();
        if (popupElement) {
          popupElement.style.pointerEvents = 'auto';
        }
      }, 0);
    });
  }

  function zoomToSignalement(lng, lat) {
    if (!map) return;

    map.flyTo({
      center: [lng, lat],
      zoom: 15.6,
      pitch: is3D ? 55 : 0,
      bearing: is3D ? -20 : 0,
      padding: { top: 170, bottom: 130, left: 120, right: 120 },
      offset: [0, -70],
      duration: 1800,
      essential: true
    });
  }

  function applyPerspective(withAnimation) {
    if (!map) return;

    const pitch = hasFocusedTunisia && is3D ? 55 : 75;
    const bearing = hasFocusedTunisia && is3D ? -20 : 45;

    if (withAnimation) {
      map.easeTo({
        pitch,
        bearing,
        duration: 6500
      });
      return;
    }

    map.jumpTo({
      pitch,
      bearing
    });
  }

  function flyToTunisAndLoad() {
    if (!map) return;

    hasFocusedTunisia = true;
    setMapStatus('Approche depuis l espace...', 'loading', false);

    map.flyTo({
      center: WORLD_CENTER,
      zoom: 0.8,
      pitch: 0,
      bearing: 0,
      duration: 5200,
      essential: true
    });

    map.once('moveend', () => {
      setMapStatus('Vue mondiale atteinte, zoom sur Tunis...', 'loading', false);

      map.flyTo({
        center: TUNIS_COORDS,
        zoom: 12.8,
        pitch: is3D ? 55 : 0,
        bearing: is3D ? -20 : 0,
        duration: 3800,
        essential: true
      });

      map.once('moveend', () => {
        loadMarkers();
      });
    });
  }

  function tunisiaPinElement() {
    const pin = document.createElement('div');
    pin.style.position = 'relative';
    pin.style.width = '26px';
    pin.style.height = '40px';
    pin.style.cursor = 'pointer';
    pin.style.pointerEvents = 'none';
    pin.style.willChange = 'transform';
    pin.style.backfaceVisibility = 'hidden';
    pin.style.WebkitBackfaceVisibility = 'hidden';

    const head = document.createElement('div');
    head.style.position = 'absolute';
    head.style.top = '0';
    head.style.left = '50%';
    head.style.transform = 'translateX(-50%)';
    head.style.width = '26px';
    head.style.height = '26px';
    head.style.borderRadius = '50%';
    head.style.background = '#2563eb';
    head.style.border = '2px solid #ffffff';
    head.style.boxShadow = '0 5px 12px rgba(0,0,0,0.35)';

    const tip = document.createElement('div');
    tip.style.position = 'absolute';
    tip.style.left = '50%';
    tip.style.bottom = '0';
    tip.style.width = '0';
    tip.style.height = '0';
    tip.style.transform = 'translateX(-50%)';
    tip.style.borderLeft = '7px solid transparent';
    tip.style.borderRight = '7px solid transparent';
    tip.style.borderTop = '14px solid #2563eb';

    pin.appendChild(head);
    pin.appendChild(tip);
    pin.addEventListener('click', flyToTunisAndLoad);
    pin.title = 'Tunisie - cliquer pour aller a Tunis';

    return pin;
  }

  function addTunisiaPin() {
    if (!map || !maplibreRef) return;

    const geojson = {
      type: 'FeatureCollection',
      features: [
        {
          type: 'Feature',
          properties: {
            name: 'Tunisie'
          },
          geometry: {
            type: 'Point',
            coordinates: TUNISIA_COORDS
          }
        }
      ]
    };

    if (!map.getSource(sourceIds.tunisia)) {
      map.addSource(sourceIds.tunisia, {
        type: 'geojson',
        data: geojson
      });

      map.addLayer({
        id: 'tunisia-circle',
        type: 'circle',
        source: sourceIds.tunisia,
        paint: {
          'circle-radius': 13,
          'circle-color': '#2563eb',
          'circle-stroke-width': 2,
          'circle-stroke-color': '#ffffff'
        }
      });
    }

    map.off('click', 'tunisia-circle', () => {
      flyToTunisAndLoad();
    });
    map.on('click', 'tunisia-circle', () => {
      flyToTunisAndLoad();
    });

    map.off('mouseenter', 'tunisia-circle', () => {
      map.getCanvas().style.cursor = 'pointer';
    });
    map.on('mouseenter', 'tunisia-circle', () => {
      map.getCanvas().style.cursor = 'pointer';
    });

    map.off('mouseleave', 'tunisia-circle', () => {
      map.getCanvas().style.cursor = '';
    });
    map.on('mouseleave', 'tunisia-circle', () => {
      map.getCanvas().style.cursor = '';
    });
  }

  function addViewModeControl() {
    if (!map || !maplibreRef) return;

    class ViewModeControl {
      onAdd(instance) {
        this._map = instance;
        this._btn = document.createElement('button');
        this._btn.type = 'button';
        this._btn.className = 'maplibregl-ctrl-icon';
        this._btn.style.width = 'auto';
        this._btn.style.padding = '0 10px';
        this._btn.style.fontSize = '12px';
        this._btn.style.fontWeight = '700';
        this._btn.textContent = is3D ? 'Mode 2D' : 'Mode 3D';
        this._btn.addEventListener('click', () => {
          is3D = !is3D;
          this._btn.textContent = is3D ? 'Mode 2D' : 'Mode 3D';
          applyPerspective(true);
        });

        this._container = document.createElement('div');
        this._container.className = 'maplibregl-ctrl maplibregl-ctrl-group';
        this._container.appendChild(this._btn);
        return this._container;
      }

      onRemove() {
        this._container.parentNode.removeChild(this._container);
        this._map = undefined;
      }
    }

    map.addControl(new ViewModeControl(), 'top-right');
  }

  function drawCriticalZones(items) {
    if (!cfg.isAdmin || !map) return;

    const buckets = {};
    items.forEach((it) => {
      const key = `${Math.round(Number(it.latitude) * 20) / 20}_${Math.round(Number(it.longitude) * 20) / 20}`;
      buckets[key] = (buckets[key] || 0) + 1;
    });

    const features = [];
    Object.keys(buckets).forEach((key) => {
      const count = buckets[key];
      if (count < 3) return;

      const parts = key.split('_').map(Number);
      const lat = parts[0];
      const lng = parts[1];
      const delta = 0.02;

      features.push({
        type: 'Feature',
        properties: {
          count,
          level: count >= 7 ? 'high' : 'medium'
        },
        geometry: {
          type: 'Polygon',
          coordinates: [[
            [lng - delta, lat - delta],
            [lng + delta, lat - delta],
            [lng + delta, lat + delta],
            [lng - delta, lat + delta],
            [lng - delta, lat - delta]
          ]]
        }
      });
    });

    const geojson = {
      type: 'FeatureCollection',
      features
    };

    if (map.getSource(sourceIds.criticalZones)) {
      map.getSource(sourceIds.criticalZones).setData(geojson);
      return;
    }

    map.addSource(sourceIds.criticalZones, {
      type: 'geojson',
      data: geojson
    });

    map.addLayer({
      id: 'critical-zones-fill',
      type: 'fill-extrusion',
      source: sourceIds.criticalZones,
      paint: {
        'fill-extrusion-color': [
          'match',
          ['get', 'level'],
          'high',
          '#dc2626',
          '#f59e0b'
        ],
        'fill-extrusion-height': [
          'interpolate',
          ['linear'],
          ['get', 'count'],
          3,
          200,
          10,
          900
        ],
        'fill-extrusion-base': 0,
        'fill-extrusion-opacity': 0.5
      }
    });

    map.addLayer({
      id: 'critical-zones-outline',
      type: 'line',
      source: sourceIds.criticalZones,
      paint: {
        'line-color': [
          'match',
          ['get', 'level'],
          'high',
          '#b91c1c',
          '#d97706'
        ],
        'line-width': 2
      }
    });
  }

  async function loadMarkers() {
    if (!map || !cfg.apiUrl) return;

    setMapStatus('Chargement des signalements...', 'loading', false);

    const categorie = document.getElementById('filterCategorie')?.value || '';
    const date = document.getElementById('filterDate')?.value || '';
    const zone = document.getElementById('filterZone')?.value || '';
    const query = new URLSearchParams({ categorie, date, zone });

    let items = null;
    let error = null;

    for (let attempt = 0; attempt <= FETCH_RETRIES; attempt += 1) {
      try {
        const response = await fetchWithTimeout(`${cfg.apiUrl}&${query.toString()}`);
        if (!response.ok) {
          throw new Error(`API ${response.status}`);
        }

        items = await response.json();
        error = null;
        break;
      } catch (err) {
        error = err;
      }
    }

    if (error) {
      setMapStatus('La carte est lente ou indisponible. Verifiez la connexion puis reessayez.', 'error', true, loadMarkers);
      return;
    }

    clearMarkers();

    const bounds = new maplibreRef.LngLatBounds();
    const safeItems = Array.isArray(items) ? items : [];

    const features = [];
    const signalementMap = {};

    safeItems.forEach((it) => {
      const lng = Number(it.longitude);
      const lat = Number(it.latitude);
      if (!Number.isFinite(lng) || !Number.isFinite(lat)) {
        return;
      }

      const color = priorityColor(it.priority, it.statut);
      features.push({
        type: 'Feature',
        properties: {
          id: it.id,
          color: color,
          statut: it.statut
        },
        geometry: {
          type: 'Point',
          coordinates: [lng, lat]
        }
      });

      signalementMap[it.id] = it;
      bounds.extend([lng, lat]);
    });

    const geojson = {
      type: 'FeatureCollection',
      features
    };

    if (map.getSource(sourceIds.signalements)) {
      map.getSource(sourceIds.signalements).setData(geojson);
    } else {
      map.addSource(sourceIds.signalements, {
        type: 'geojson',
        data: geojson
      });

      map.addLayer({
        id: 'signalements-circle',
        type: 'circle',
        source: sourceIds.signalements,
        paint: {
          'circle-radius': 11,
          'circle-color': ['get', 'color'],
          'circle-stroke-width': 2,
          'circle-stroke-color': '#ffffff',
          'circle-stroke-opacity': 1
        }
      });

      map.addLayer({
        id: 'signalements-highlight',
        type: 'circle',
        source: sourceIds.signalements,
        paint: {
          'circle-radius': 14,
          'circle-color': 'transparent',
          'circle-stroke-width': 1,
          'circle-stroke-color': 'rgba(255, 255, 255, 0.5)'
        },
        filter: ['boolean', ['feature-state', 'hover'], false]
      });
    }

    if (!map.getLayer('signalements-circle')) {
      map.addLayer({
        id: 'signalements-circle',
        type: 'circle',
        source: sourceIds.signalements,
        paint: {
          'circle-radius': 11,
          'circle-color': ['get', 'color'],
          'circle-stroke-width': 2,
          'circle-stroke-color': '#ffffff',
          'circle-stroke-opacity': 1
        }
      });
    }

    if (!map.getLayer('signalements-highlight')) {
      map.addLayer({
        id: 'signalements-highlight',
        type: 'circle',
        source: sourceIds.signalements,
        paint: {
          'circle-radius': 14,
          'circle-color': 'transparent',
          'circle-stroke-width': 1,
          'circle-stroke-color': 'rgba(255, 255, 255, 0.5)'
        },
        filter: ['boolean', ['feature-state', 'hover'], false]
      });
    }

    map.off('click', 'signalements-circle', handleSignalementClick);
    map.on('click', 'signalements-circle', (e) => handleSignalementClick(e, signalementMap));

    map.off('mouseenter', 'signalements-circle', () => {
      map.getCanvas().style.cursor = 'pointer';
    });
    map.on('mouseenter', 'signalements-circle', () => {
      map.getCanvas().style.cursor = 'pointer';
    });

    map.off('mouseleave', 'signalements-circle', () => {
      map.getCanvas().style.cursor = '';
    });
    map.on('mouseleave', 'signalements-circle', () => {
      map.getCanvas().style.cursor = '';
    });

    drawCriticalZones(safeItems);

    if (!bounds.isEmpty()) {
      map.fitBounds(bounds, {
        padding: { top: 80, bottom: 80, left: 80, right: 80 },
        maxZoom: 16,
        duration: 0
      });
    }

    clearMapStatus();
  }

  function handleSignalementClick(e, signalementMap) {
    if (!e.features.length) return;

    const feature = e.features[0];
    const it = signalementMap[feature.properties.id];
    if (!it) return;

    const lng = Number(it.longitude);
    const lat = Number(it.latitude);

    zoomToSignalement(lng, lat);

    const popup = new maplibreRef.Popup({
      anchor: 'top',
      offset: 18,
      closeButton: true,
      closeOnClick: true,
      maxWidth: '240px'
    }).setLngLat([lng, lat]).setHTML(buildSignalementPopup(it, lat, lng, true));

    popup.addTo(map);
  }

  function initMap() {
    map = new maplibreRef.Map({
      container: 'map',
      style: mapStyle,
      center: WORLD_CENTER,
      zoom: -2.5,
      minZoom: -2.5,
      pitch: 75,
      bearing: 45,
      antialias: false
    });

    map.addControl(new maplibreRef.NavigationControl(), 'top-right');
    addViewModeControl();

    map.on('load', () => {
      map.resize();
      applyPerspective(false);
      flyToTunisAndLoad();
    });

    window.addEventListener('resize', () => {
      if (map) {
        map.resize();
      }
    });

  }

  async function bootstrapMap() {
    if (!cfg.apiUrl) {
      setMapStatus('Configuration carte absente.', 'error', false);
      return;
    }

    setMapStatus('Initialisation de la carte...', 'loading', false);

    try {
      maplibreRef = await waitForMaplibre();
      initMap();
    } catch (_err) {
      setMapStatus('Impossible de charger la librairie carte. Verifiez internet puis reessayez.', 'error', true, bootstrapMap);
    }
  }

  document.getElementById('btnFiltrer')?.addEventListener('click', () => {
    if (!hasFocusedTunisia) {
      flyToTunisAndLoad();
      return;
    }

    loadMarkers();
  });

  bootstrapMap();
})();
