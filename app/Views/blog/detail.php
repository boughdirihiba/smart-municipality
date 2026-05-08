<?php
// Blog detail - single post with comments
$post = $post ?? [];
$comments = $comments ?? [];
$commentCount = $commentCount ?? 0;
$pageTitle = $pageTitle ?? 'Blog Post';
?>

<div class="container mt-5">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <a href="<?php echo BASE_URL; ?>/index.php?route=blog/index" class="btn btn-secondary mb-3">
                <i class="fas fa-arrow-left"></i> Retour
            </a>

            <article class="card mb-4">
                <?php if (!empty($post['image'])): ?>
                    <img src="<?php echo htmlspecialchars($post['image']); ?>" class="card-img-top" alt="Post">
                <?php endif; ?>
                <div class="card-body">
                    <p class="text-muted">
                        <strong>Par</strong> <?php echo htmlspecialchars($post['prenom'] ?? '') . ' ' . htmlspecialchars($post['nom'] ?? ''); ?>
                        <br>
                        <small><?php echo htmlspecialchars($post['created_at'] ?? ''); ?></small>
                    </p>

                    <div class="post-content mb-4">
                        <?php echo nl2br(htmlspecialchars($post['content'] ?? '')); ?>
                    </div>

                    <?php if (!empty($post['video'])): ?>
                        <div class="video-container mb-4">
                            <iframe width="100%" height="400" src="<?php echo htmlspecialchars($post['video']); ?>" frameborder="0" allowfullscreen></iframe>
                        </div>
                    <?php endif; ?>

                    <div class="post-actions">
                        <?php if (isset($_SESSION['user']) && ($_SESSION['user']['role'] === 'admin' || $_SESSION['user']['id'] == $post['user_id'])): ?>
                            <a href="<?php echo BASE_URL; ?>/index.php?route=blog/edit&id=<?php echo (int)$post['id']; ?>" class="btn btn-warning btn-sm">
                                <i class="fas fa-edit"></i> Éditer
                            </a>
                            <a href="<?php echo BASE_URL; ?>/index.php?route=blog/delete&id=<?php echo (int)$post['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Êtes-vous sûr?')">
                                <i class="fas fa-trash"></i> Supprimer
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </article>

            <!-- Comments section -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Commentaires (<?php echo $commentCount; ?>)</h5>
                </div>
                <div class="card-body">
                    <?php if (isset($_SESSION['success'])): ?>
                        <div class="alert alert-success alert-dismissible fade show">
                            <?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?>
                        </div>
                    <?php endif; ?>

                    <!-- Add comment form -->
                    <?php if (isset($_SESSION['user'])): ?>
                        <form method="POST" action="<?php echo BASE_URL; ?>/index.php?route=blog/addComment" class="mb-4">
                            <input type="hidden" name="post_id" value="<?php echo (int)$post['id']; ?>">
                            <div class="mb-3">
                                <textarea class="form-control" name="content" rows="3" placeholder="Votre commentaire..." required></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary btn-sm">
                                <i class="fas fa-comment"></i> Commenter
                            </button>
                        </form>
                    <?php else: ?>
                        <p class="text-muted mb-3">
                            <a href="<?php echo BASE_URL; ?>/index.php?route=login">Connectez-vous</a> pour commenter.
                        </p>
                    <?php endif; ?>

                    <!-- Comments list -->
                    <div class="comments-list">
                        <?php if (empty($comments)): ?>
                            <p class="text-muted">Aucun commentaire pour le moment.</p>
                        <?php else: ?>
                            <?php foreach ($comments as $c): ?>
                                <div class="card mb-3 bg-light">
                                    <div class="card-body">
                                        <p class="card-text">
                                            <strong><?php echo htmlspecialchars($c['prenom'] ?? '') . ' ' . htmlspecialchars($c['nom'] ?? ''); ?></strong>
                                            <br>
                                            <small class="text-muted"><?php echo htmlspecialchars($c['created_at'] ?? ''); ?></small>
                                        </p>
                                        <p class="card-text"><?php echo nl2br(htmlspecialchars($c['content'] ?? '')); ?></p>
                                        <?php if (isset($_SESSION['user']) && ($_SESSION['user']['role'] === 'admin' || $_SESSION['user']['id'] == $c['user_id'])): ?>
                                            <a href="<?php echo BASE_URL; ?>/index.php?route=blog/deleteComment&id=<?php echo (int)$c['id']; ?>" class="btn btn-danger btn-xs" onclick="return confirm('Êtes-vous sûr?')">
                                                <i class="fas fa-trash"></i> Supprimer
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
