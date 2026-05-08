<?php
// Blog index - list all posts
$posts = $posts ?? [];
$search = $search ?? '';
$pageTitle = $pageTitle ?? 'Blog';
?>

<div class="container mt-5">
    <div class="row">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1><?php echo htmlspecialchars($pageTitle); ?></h1>
                <?php if (isset($_SESSION['user']) && $_SESSION['user']['role'] === 'admin'): ?>
                    <a href="<?php echo BASE_URL; ?>/index.php?route=blog/create" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Nouveau post
                    </a>
                <?php endif; ?>
            </div>

            <!-- Search form -->
            <form method="GET" class="mb-4">
                <div class="input-group">
                    <input type="hidden" name="route" value="blog/index">
                    <input type="text" class="form-control" name="search" placeholder="Rechercher..." value="<?php echo htmlspecialchars($search); ?>">
                    <button class="btn btn-primary" type="submit">
                        <i class="fas fa-search"></i> Rechercher
                    </button>
                </div>
            </form>

            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    <?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?>
                    <button type="button" class="btn-close"></button>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
                    <button type="button" class="btn-close"></button>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="row mt-4">
        <?php if (empty($posts)): ?>
            <div class="col-12">
                <div class="alert alert-info">
                    Aucun post trouvé.
                </div>
            </div>
        <?php else: ?>
            <?php foreach ($posts as $post): ?>
                <div class="col-md-6 mb-4">
                    <div class="card h-100">
                        <?php if (!empty($post['image'])): ?>
                            <img src="<?php echo htmlspecialchars($post['image']); ?>" class="card-img-top" alt="Post">
                        <?php endif; ?>
                        <div class="card-body">
                            <h5 class="card-title">
                                <a href="<?php echo BASE_URL; ?>/index.php?route=blog/detail&id=<?php echo (int)$post['id']; ?>" style="text-decoration: none; color: inherit;">
                                    <?php echo htmlspecialchars(substr($post['content'] ?? '', 0, 60)) . '...'; ?>
                                </a>
                            </h5>
                            <p class="card-text text-muted">
                                Par <?php echo htmlspecialchars($post['prenom'] ?? 'Unknown') . ' ' . htmlspecialchars($post['nom'] ?? ''); ?>
                            </p>
                            <small class="text-muted">
                                <?php echo htmlspecialchars($post['created_at'] ?? ''); ?>
                            </small>
                            <div class="mt-3">
                                <a href="<?php echo BASE_URL; ?>/index.php?route=blog/detail&id=<?php echo (int)$post['id']; ?>" class="btn btn-sm btn-info">
                                    Lire
                                </a>
                                <?php if (isset($_SESSION['user']) && ($_SESSION['user']['role'] === 'admin' || $_SESSION['user']['id'] == $post['user_id'])): ?>
                                    <a href="<?php echo BASE_URL; ?>/index.php?route=blog/edit&id=<?php echo (int)$post['id']; ?>" class="btn btn-sm btn-warning">
                                        Éditer
                                    </a>
                                    <a href="<?php echo BASE_URL; ?>/index.php?route=blog/delete&id=<?php echo (int)$post['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Êtes-vous sûr?')">
                                        Supprimer
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>
