<?php
$it = $item ?? [];
$isEditMode = !empty($isEdit);
$errorsList = $errors ?? [];
$tasks = is_array($it['tasks'] ?? null) ? $it['tasks'] : [];
$taskJson = json_encode($tasks, JSON_UNESCAPED_UNICODE) ?: '[]';
$progressionValue = (int)($it['progression'] ?? 0);
?>

<div class="card">
    <h1><?php echo $isEditMode ? 'Modifier intervention' : 'Nouvelle intervention'; ?></h1>

    <div style="background:#f0fdf4; border-left:4px solid #16a34a; padding:12px 16px; margin-bottom:1rem; border-radius:6px;">
        <strong style="color:#166534;">💰 Coût et Budget</strong>
        <p style="margin:6px 0 0; font-size:0.9rem; color:#166534;">
            <?php echo $isEditMode 
                ? 'Toute modification de type, localisation ou description recalculera automatiquement le coût estimé.'
                : 'Le système calculera automatiquement le coût estimé et le liera au budget correspondant selon le type et la zone.'; ?>
        </p>
        <p style="margin:6px 0 0; font-size:0.85rem; color:#166534; opacity:0.8;">
            <strong>Comment le budget est choisi:</strong> Correspond au type d'intervention + année courante + zone (si applicable).
        </p>
    </div>

    <?php if (!empty($it['from_signalement'])): ?>
        <div style="background:#e0f2fe; border-left:4px solid #0284c7; padding:12px 16px; margin-bottom:1rem; border-radius:6px;">
            <strong style="color:#0284c7;">ℹ️ Intervention créée à partir du signalement #{<?php echo (int)$it['from_signalement']; ?>}</strong>
            <p style="margin:6px 0 0; font-size:0.9rem; color:#0c4a6e;">Le titre et la description ont été pré-remplis. Vous pouvez les modifier selon vos besoins.</p>
        </div>
    <?php endif; ?>

    <?php if (!empty($errorsList)): ?>
        <div class="alert alert-error">
            <?php foreach ($errorsList as $err): ?>
                <div><?php echo e($err); ?></div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form id="interventionForm" method="post" action="<?php echo BASE_URL; ?>/index.php?route=<?php echo $isEditMode ? 'interventions/edit' : 'interventions/store'; ?>" novalidate>
        <?php if ($isEditMode): ?>
            <input type="hidden" name="id" value="<?php echo (int)($it['id'] ?? 0); ?>">
        <?php endif; ?>
        <?php if (!empty($it['from_signalement'])): ?>
            <input type="hidden" name="from_signalement" value="<?php echo (int)$it['from_signalement']; ?>">
        <?php endif; ?>

        <label for="titre">Titre</label>
        <input id="titre" name="titre" type="text" value="<?php echo e((string)($it['titre'] ?? '')); ?>" required>

        <label for="description" style="margin-top:0.8rem;">Description</label>
        <textarea id="description" name="description" required><?php echo e((string)($it['description'] ?? '')); ?></textarea>

        <div class="grid grid-2" style="margin-top:0.8rem;">
            <div>
                <label for="type">Type</label>
                <select id="type" name="type">
                    <?php foreach (['route', 'eclairage', 'eau', 'transport', 'ordures', 'autre'] as $tp): ?>
                        <option value="<?php echo $tp; ?>" <?php echo (($it['type'] ?? 'autre') === $tp) ? 'selected' : ''; ?>><?php echo ucfirst($tp); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label for="statut">Statut</label>
                <select id="statut" name="statut">
                    <?php foreach (['planifiee', 'en_cours', 'terminee', 'annulee'] as $st): ?>
                        <option value="<?php echo $st; ?>" <?php echo (($it['statut'] ?? 'planifiee') === $st) ? 'selected' : ''; ?>><?php echo $st; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="grid grid-2" style="margin-top:0.8rem;">
            <div>
                <label for="latitude">Latitude</label>
                <input id="latitude" name="latitude" type="text" value="<?php echo e((string)($it['latitude'] ?? '')); ?>" required>
            </div>
            <div>
                <label for="longitude">Longitude</label>
                <input id="longitude" name="longitude" type="text" value="<?php echo e((string)($it['longitude'] ?? '')); ?>" required>
            </div>
        </div>

        <label for="date_intervention" style="margin-top:0.8rem;">Date intervention (optionnelle)</label>
        <input id="date_intervention" name="date_intervention" type="date" value="<?php echo e((string)($it['date_intervention'] ?? '')); ?>">

        <div class="task-builder" data-initial-tasks="<?php echo e($taskJson); ?>" data-is-edit="<?php echo $isEditMode ? '1' : '0'; ?>" style="margin-top:1rem;">
            <div class="task-builder-head">
                <div>
                    <label style="margin-bottom:0.2rem;">Étapes / To-do list (générées automatiquement)</label>
                    <p style="font-size:0.9rem; color:#666; margin:0.4rem 0 0;">Marquez les tâches comme complétées au fur et à mesure de l'avancement.</p>
                </div>
                <div class="task-builder-score">
                    <strong id="progressionValue"><?php echo $progressionValue; ?>%</strong>
                    <span>progression auto</span>
                </div>
            </div>

            <div class="progress-track progress-track-lg" aria-hidden="true">
                <div class="progress-fill progress-fill is-high" id="progressionFill" style="width: <?php echo $progressionValue; ?>%;"></div>
            </div>

            <input type="hidden" id="tasks_json" name="tasks_json" value="<?php echo e($taskJson); ?>">
            <div id="tasksList" class="tasks-list"></div>
        </div>

        <div style="margin-top:1rem; display:flex; gap:8px;">
            <button class="btn-principal" type="submit"><?php echo $isEditMode ? 'Mettre a jour' : 'Creer'; ?></button>
            <a class="btn-secondaire" href="<?php echo BASE_URL; ?>/index.php?route=interventions/list">Retour</a>
        </div>
    </form>
</div>

<!-- Interactive map for selecting coordinates -->
<div id="interventionMap" style="width:100%; height:320px; border-radius:12px; overflow:hidden; margin-top:0.8rem;"></div>

<script>
    (function () {
        function safeParseTasks(raw) {
            try {
                const parsed = JSON.parse(raw || '[]');
                return Array.isArray(parsed) ? parsed : [];
            } catch (_err) {
                return [];
            }
        }

        function normalizeTasks(tasks) {
            return tasks
                .map(function (task) {
                    return {
                        label: String(task && task.label ? task.label : '').trim(),
                        done: Boolean(task && task.done)
                    };
                })
                .filter(function (task) {
                    return task.label !== '';
                });
        }

        const form = document.getElementById('interventionForm');
        const taskBuilder = document.querySelector('.task-builder');
        const tasksList = document.getElementById('tasksList');
        const tasksInput = document.getElementById('tasks_json');
        const addTaskBtn = document.getElementById('addTaskBtn');
        const progressValue = document.getElementById('progressionValue');
        const progressFill = document.getElementById('progressionFill');
        const titleInput = document.getElementById('titre');
        const descriptionInput = document.getElementById('description');
        const typeInput = document.getElementById('type');
        const latInput = document.getElementById('latitude');
        const lngInput = document.getElementById('longitude');

        function readTasksFromDom() {
            if (!tasksList) return [];

            return Array.from(tasksList.querySelectorAll('.task-row')).map(function (row) {
                const labelSpan = row.querySelector('.task-label-text');
                const checkbox = row.querySelector('.task-done');

                return {
                    label: labelSpan ? labelSpan.textContent.trim() : '',
                    done: checkbox ? checkbox.checked : false
                };
            });
        }

        function updateProgression(tasks) {
            const normalizedTasks = normalizeTasks(tasks);
            const total = normalizedTasks.length;
            const doneCount = normalizedTasks.reduce(function (count, task) {
                return count + (task.done ? 1 : 0);
            }, 0);
            const progression = total > 0 ? Math.round((doneCount / total) * 100) : 0;

            if (tasksInput) {
                tasksInput.value = JSON.stringify(normalizedTasks);
            }
            if (progressValue) {
                progressValue.textContent = progression + '%';
            }
            if (progressFill) {
                progressFill.style.width = progression + '%';
            }

            return progression;
        }

        function syncTasks() {
            updateProgression(readTasksFromDom());
        }

        function createTaskRow(task) {
            const row = document.createElement('div');
            row.className = 'task-row';

            const left = document.createElement('label');
            left.className = 'task-row-check';

            const checkbox = document.createElement('input');
            checkbox.type = 'checkbox';
            checkbox.className = 'task-done';
            checkbox.checked = Boolean(task && task.done);

            const checkText = document.createElement('span');
            checkText.textContent = 'Fait';

            left.appendChild(checkbox);
            left.appendChild(checkText);

            // Display task label as read-only text (auto-generated)
            const labelText = document.createElement('span');
            labelText.className = 'task-label-text';
            labelText.textContent = task && task.label ? task.label : '';
            labelText.style.flex = '1';

            checkbox.addEventListener('change', syncTasks);

            row.appendChild(left);
            row.appendChild(labelText);

            return row;
        }

        function addTaskRow(task) {
            if (!tasksList) return;
            tasksList.appendChild(createTaskRow(task || { label: '', done: false }));
        }

        function renderTasks(tasks) {
            if (!tasksList) return;
            const normalized = normalizeTasks(tasks);
            tasksList.innerHTML = '';
            normalized.forEach(function (task) {
                addTaskRow(task);
            });
            updateProgression(normalized);
        }

        let regenerateTimer = null;
        let regenerateSeq = 0;

        function scheduleTaskRegeneration() {
            if (!taskBuilder || taskBuilder.dataset.isEdit === '1') return;
            if (!descriptionInput || !typeInput) return;

            const description = String(descriptionInput.value || '').trim();
            if (description.length < 10) return;

            if (regenerateTimer) {
                clearTimeout(regenerateTimer);
            }

            regenerateTimer = setTimeout(function () {
                const seq = ++regenerateSeq;
                const body = new URLSearchParams({
                    type: String(typeInput.value || 'autre'),
                    titre: titleInput ? String(titleInput.value || '') : '',
                    description: description
                });

                fetch('<?php echo BASE_URL; ?>/index.php?route=interventions/generateTasks', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8'
                    },
                    body: body.toString()
                })
                    .then(function (res) { return res.ok ? res.json() : null; })
                    .then(function (data) {
                        if (!data || !data.ok || !Array.isArray(data.tasks)) return;
                        if (seq !== regenerateSeq) return;
                        renderTasks(data.tasks);
                    })
                    .catch(function () {
                    });
            }, 700);
        }

        if (taskBuilder && tasksList && tasksInput) {
            const initialTasks = normalizeTasks(safeParseTasks(taskBuilder.dataset.initialTasks || '[]'));
            tasksList.innerHTML = '';

            if (initialTasks.length > 0) {
                initialTasks.forEach(function (task) {
                    addTaskRow(task);
                });
            }

            if (form) {
                form.addEventListener('submit', function () {
                    syncTasks();
                });
            }

            if (descriptionInput) {
                descriptionInput.addEventListener('input', scheduleTaskRegeneration);
            }
            if (typeInput) {
                typeInput.addEventListener('change', scheduleTaskRegeneration);
            }
            if (titleInput) {
                titleInput.addEventListener('input', scheduleTaskRegeneration);
            }

            syncTasks();
        }

        function ensureMapLibreLoaded(cb) {
            if (window.maplibregl) return cb(null, window.maplibregl);

            if (!document.getElementById('maplibre-css')) {
                const link = document.createElement('link');
                link.id = 'maplibre-css';
                link.rel = 'stylesheet';
                link.href = 'https://unpkg.com/maplibre-gl@4.7.1/dist/maplibre-gl.css';
                link.onerror = function () {
                    this.onerror = null;
                    this.href = 'https://cdn.jsdelivr.net/npm/maplibre-gl@4.7.1/dist/maplibre-gl.css';
                };
                document.head.appendChild(link);
            }

            if (document.getElementById('maplibre-js-loaded')) return cb(null, window.maplibregl);

            const script = document.createElement('script');
            script.src = 'https://unpkg.com/maplibre-gl@4.7.1/dist/maplibre-gl.js';
            script.defer = true;
            script.onload = function () {
                if (!document.getElementById('maplibre-js-loaded')) {
                    const marker = document.createElement('meta');
                    marker.id = 'maplibre-js-loaded';
                    document.head.appendChild(marker);
                }
                cb(null, window.maplibregl);
            };
            script.onerror = function () {
                this.onerror = null;
                script.src = 'https://cdn.jsdelivr.net/npm/maplibre-gl@4.7.1/dist/maplibre-gl.js';
            };
            document.head.appendChild(script);
        }

        function initInterventionMap() {
            const parsedLat = parseFloat(latInput ? latInput.value : '');
            const parsedLng = parseFloat(lngInput ? lngInput.value : '');
            const defaultCenter = [10.1815, 36.8065];
            const center = Number.isFinite(parsedLng) && Number.isFinite(parsedLat) ? [parsedLng, parsedLat] : defaultCenter;

            const mapStyle = {
                version: 8,
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
                layers: [{ id: 'osm-bg', type: 'raster', source: 'osm-raster' }]
            };

            try {
                const map = new window.maplibregl.Map({
                    container: 'interventionMap',
                    style: mapStyle,
                    center: center,
                    zoom: 14
                });
                map.addControl(new window.maplibregl.NavigationControl(), 'top-right');

                let marker = null;

                function updateInputs(lat, lng) {
                    if (latInput) latInput.value = String(lat);
                    if (lngInput) lngInput.value = String(lng);
                    syncTasks();
                }

                function placeMarker(lngLat) {
                    if (marker) {
                        marker.remove();
                    }

                    marker = new window.maplibregl.Marker({ draggable: true })
                        .setLngLat(lngLat)
                        .addTo(map);

                    marker.on('dragend', function () {
                        const lngLatValue = marker.getLngLat();
                        updateInputs(lngLatValue.lat, lngLatValue.lng);
                    });
                }

                map.on('click', function (event) {
                    const lngLatValue = event.lngLat;
                    placeMarker([lngLatValue.lng, lngLatValue.lat]);
                    updateInputs(lngLatValue.lat, lngLatValue.lng);
                });

                if (Number.isFinite(parsedLat) && Number.isFinite(parsedLng)) {
                    placeMarker([parsedLng, parsedLat]);
                    map.setCenter([parsedLng, parsedLat]);
                }

                if (latInput) {
                    latInput.addEventListener('change', function () {
                        const lat = parseFloat(latInput.value);
                        const lng = parseFloat(lngInput ? lngInput.value : '');
                        if (Number.isFinite(lat) && Number.isFinite(lng)) {
                            placeMarker([lng, lat]);
                            map.setCenter([lng, lat]);
                        }
                    });
                }

                if (lngInput) {
                    lngInput.addEventListener('change', function () {
                        const lat = parseFloat(latInput ? latInput.value : '');
                        const lng = parseFloat(lngInput.value);
                        if (Number.isFinite(lat) && Number.isFinite(lng)) {
                            placeMarker([lng, lat]);
                            map.setCenter([lng, lat]);
                        }
                    });
                }
            } catch (_err) {
            }
        }

        try {
            const params = new URLSearchParams(window.location.search);
            const latParam = params.get('latitude') || params.get('lat');
            const lngParam = params.get('longitude') || params.get('lng');
            if (latParam && lngParam) {
                if (latInput && (!latInput.value || latInput.value.trim() === '')) latInput.value = latParam;
                if (lngInput && (!lngInput.value || lngInput.value.trim() === '')) lngInput.value = lngParam;
            }
        } catch (_err) {
        }

        ensureMapLibreLoaded(function (err) {
            if (err || !window.maplibregl) return;
            initInterventionMap();
        });
    })();
</script>
