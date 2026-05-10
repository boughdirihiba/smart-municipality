<?php
// Variables provided by legacy_router: $categories (array with nom, description, image_url, id, nb_evenements)
$categories = $categories ?? [];
?>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<style>
    .ev-hero {
        background: linear-gradient(135deg, #0f3b2c 0%, #1b6a53 100%);
        padding: 48px 24px;
        text-align: center;
        color: white;
        border-radius: 24px;
        margin-bottom: 36px;
    }
    .ev-hero h1 { font-size: 1.6rem; font-weight: 700; margin: 0 0 10px; }
    .ev-hero p  { font-size: 0.95rem; opacity: 0.85; margin: 0; }

    .ev-section-title { font-size: 1.5rem; font-weight: 700; color: #0f3b2c; margin-bottom: 6px; }
    .ev-section-sub   { color: #64748b; font-size: 0.9rem; margin-bottom: 28px; }

    .ev-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 24px;
    }
    .ev-cat-card {
        background: white;
        border-radius: 20px;
        overflow: hidden;
        box-shadow: 0 4px 16px rgba(0,0,0,0.07);
        transition: transform 0.25s, box-shadow 0.25s;
    }
    .ev-cat-card:hover { transform: translateY(-6px); box-shadow: 0 12px 32px rgba(15,59,44,0.15); }
    .ev-cat-img {
        width: 100%;
        height: 180px;
        object-fit: cover;
        display: block;
        background: #e2e8f0;
    }
    .ev-cat-img-placeholder {
        width: 100%;
        height: 180px;
        background: linear-gradient(135deg, #0f3b2c, #1b6a53);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 3rem;
        color: rgba(255,255,255,0.5);
    }
    .ev-cat-body { padding: 20px; }
    .ev-cat-name { font-size: 1.15rem; font-weight: 700; color: #0f172a; margin-bottom: 6px; }
    .ev-cat-desc { color: #64748b; font-size: 0.82rem; line-height: 1.5; margin-bottom: 14px; min-height: 38px; }
    .ev-cat-count {
        display: inline-flex; align-items: center; gap: 6px;
        background: #e8f5e9; color: #1a5e2a;
        padding: 4px 12px; border-radius: 20px;
        font-size: 0.75rem; font-weight: 600; margin-bottom: 14px;
    }
    .ev-cat-btn {
        display: flex; align-items: center; justify-content: center; gap: 8px;
        background: linear-gradient(135deg, #1a5e2a, #4caf50);
        color: white; text-decoration: none;
        padding: 10px 20px; border-radius: 40px;
        font-size: 0.85rem; font-weight: 600;
        transition: all 0.2s;
    }
    .ev-cat-btn:hover { transform: translateY(-2px); box-shadow: 0 6px 16px rgba(26,94,42,0.3); color: white; }

    .ev-empty { text-align: center; padding: 60px 20px; color: #94a3b8; }
    .ev-empty i { font-size: 3rem; margin-bottom: 16px; display: block; }

    @media (max-width: 600px) {
        .ev-hero h1 { font-size: 1.2rem; }
        .ev-grid { grid-template-columns: 1fr; }
    }
</style>

<div class="ev-hero">
    <h1><i class="fas fa-calendar-star me-2"></i>Événements</h1>
    <p>Choisissez une catégorie pour voir tous les événements associés</p>
</div>

<h2 class="ev-section-title">Catégories d'événements</h2>
<p class="ev-section-sub">Explorez les événements par catégorie</p>

<?php if (empty($categories)): ?>
<div class="ev-empty">
    <i class="fas fa-calendar-times"></i>
    <p>Aucune catégorie disponible pour le moment.</p>
</div>
<?php else: ?>
<div class="ev-grid">
    <?php foreach ($categories as $cat): ?>
    <div class="ev-cat-card">
        <?php if (!empty($cat['image_url'])): ?>
            <img class="ev-cat-img"
                 src="<?php echo BASE_URL; ?>/public/uploads/<?php echo htmlspecialchars($cat['image_url']); ?>"
                 alt="<?php echo htmlspecialchars($cat['nom']); ?>"
                 onerror="this.style.display='none';this.nextElementSibling.style.display='flex';">
            <div class="ev-cat-img-placeholder" style="display:none;">
                <i class="fas fa-calendar-alt"></i>
            </div>
        <?php else: ?>
            <div class="ev-cat-img-placeholder">
                <i class="fas fa-calendar-alt"></i>
            </div>
        <?php endif; ?>

        <div class="ev-cat-body">
            <div class="ev-cat-name"><?php echo htmlspecialchars($cat['nom']); ?></div>
            <div class="ev-cat-desc"><?php echo htmlspecialchars($cat['description'] ?? ''); ?></div>
            <div class="ev-cat-count">
                <i class="fas fa-calendar-check"></i>
                <?php echo (int)($cat['nb_evenements'] ?? 0); ?> événement<?php echo ($cat['nb_evenements'] ?? 0) != 1 ? 's' : ''; ?>
            </div>
            <a href="<?php echo BASE_URL; ?>/index.php?action=evenements_categorie&id=<?php echo $cat['id']; ?>"
               class="ev-cat-btn">
                Voir les événements <i class="fas fa-arrow-right"></i>
            </a>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>
