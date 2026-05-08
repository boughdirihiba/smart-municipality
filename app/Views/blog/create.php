<?php
// Blog create - new post form
$pageTitle = $pageTitle ?? 'Créer un post';
?>

<div class="container mt-5">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <a href="<?php echo BASE_URL; ?>/index.php?route=blog/index" class="btn btn-secondary mb-3">
                <i class="fas fa-arrow-left"></i> Retour
            </a>

            <h1><?php echo htmlspecialchars($pageTitle); ?></h1>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
                </div>
            <?php endif; ?>

            <form method="POST" class="card p-4">
                <div class="mb-3">
                    <label for="content" class="form-label">Contenu</label>
                    <textarea class="form-control" id="content" name="content" rows="6" placeholder="Votre message..." required></textarea>
                </div>

                <div class="mb-3">
                    <label for="image" class="form-label">Image (URL)</label>
                    <input type="url" class="form-control" id="image" name="image" placeholder="https://example.com/image.jpg">
                </div>

                <div class="mb-3">
                    <label for="video" class="form-label">Vidéo (URL/Embed)</label>
                    <input type="text" class="form-control" id="video" name="video" placeholder="https://example.com/video">
                    <small class="text-muted">Note: Image et vidéo ne peuvent pas être utilisées ensemble</small>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save"></i> Publier
                    </button>
                    <a href="<?php echo BASE_URL; ?>/index.php?route=blog/index" class="btn btn-secondary">
                        Annuler
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
