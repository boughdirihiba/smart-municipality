<?php
// Blog edit - edit existing post
$post = $post ?? [];
$pageTitle = $pageTitle ?? 'Éditer un post';
?>

<div class="container mt-5">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <a href="<?php echo BASE_URL; ?>/index.php?route=blog/detail&id=<?php echo (int)$post['id']; ?>" class="btn btn-secondary mb-3">
                <i class="fas fa-arrow-left"></i> Retour
            </a>

            <h1><?php echo htmlspecialchars($pageTitle); ?></h1>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
                </div>
            <?php endif; ?>

            <form method="POST" class="card p-4">
                <input type="hidden" name="id" value="<?php echo (int)$post['id']; ?>">

                <div class="mb-3">
                    <label for="content" class="form-label">Contenu</label>
                    <textarea class="form-control" id="content" name="content" rows="6" required><?php echo htmlspecialchars($post['content'] ?? ''); ?></textarea>
                </div>

                <div class="mb-3">
                    <label for="image" class="form-label">Image (URL)</label>
                    <input type="url" class="form-control" id="image" name="image" value="<?php echo htmlspecialchars($post['image'] ?? ''); ?>" placeholder="https://example.com/image.jpg">
                </div>

                <div class="mb-3">
                    <label for="video" class="form-label">Vidéo (URL/Embed)</label>
                    <input type="text" class="form-control" id="video" name="video" value="<?php echo htmlspecialchars($post['video'] ?? ''); ?>" placeholder="https://example.com/video">
                    <small class="text-muted">Note: Image et vidéo ne peuvent pas être utilisées ensemble</small>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save"></i> Enregistrer
                    </button>
                    <a href="<?php echo BASE_URL; ?>/index.php?route=blog/detail&id=<?php echo (int)$post['id']; ?>" class="btn btn-secondary">
                        Annuler
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
