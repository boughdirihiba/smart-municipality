<?php
session_start();
require_once __DIR__ . '/../controllers/DashboardController.php';

// Vérification admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    $_SESSION['error'] = "Accès réservé aux administrateurs.";
    header('Location: /projetweb/views/frontoffice.php');
    exit();
}

$controller = new DashboardController();
$stats = $controller->getStats();
$posts = $controller->getAllPosts();
$comments = $controller->getAllComments();

$success_message = $_SESSION['success'] ?? '';
$error_message = $_SESSION['error'] ?? '';
unset($_SESSION['success'], $_SESSION['error']);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <title>Smart Municipality | Dashboard Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;14..32,400;14..32,500;14..32,600;14..32,700;14..32,800&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background: #f0f2f8; color: #1a2634; }
        .dashboard { display: grid; grid-template-columns: 280px 1fr; min-height: 100vh; }
        .sidebar { background: linear-gradient(145deg, #2c7a5e, #1e4a3b 100%); color: #e2f0ec; padding: 2rem 1rem; position: sticky; top: 0; height: 100vh; box-shadow: 4px 0 20px rgba(0,0,0,0.08); }
        .logo-area { text-align: center; margin-bottom: 2.5rem; padding-bottom: 1rem; border-bottom: 1px solid rgba(255,255,255,0.15); }
        .logo-icon { width: 75px; height: auto; margin-bottom: 12px; border-radius: 50%; box-shadow: 0 5px 15px rgba(0,0,0,0.2); background: white; padding: 4px; }
        .logo-text .smart { font-size: 1.8rem; font-weight: 800; color: white; letter-spacing: -0.5px; }
        .logo-text .municipality { font-size: 0.8rem; opacity: 0.85; font-weight: 500; }
        .nav-item { display: flex; align-items: center; gap: 1rem; padding: 0.85rem 1.2rem; border-radius: 1.2rem; cursor: pointer; transition: 0.25s; color: #cde3dd; margin-bottom: 0.6rem; font-weight: 500; }
        .nav-item i { width: 24px; font-size: 1.2rem; }
        .nav-item:hover, .nav-item.active { background: rgba(255,255,255,0.12); color: white; transform: translateX(6px); backdrop-filter: blur(4px); }
        .main-content { padding: 1.8rem 2rem; overflow-y: auto; background: #f5f9fc; }
        .top-bar { display: flex; align-items: center; justify-content: space-between; gap: 1rem; margin-bottom: 2rem; background: white; padding: 0.6rem 1.8rem; border-radius: 1.5rem; flex-wrap: wrap; box-shadow: 0 4px 12px rgba(0,0,0,0.02); border: 1px solid #eef2f6; }
        .page-title { font-size: 1.3rem; font-weight: 700; color: #1e4a3b; display: flex; align-items: center; gap: 0.6rem; }
        .blog-link { background: linear-gradient(135deg, #2c7a5e, #1e4a3b); color: white; padding: 0.5rem 1.3rem; border-radius: 2rem; text-decoration: none; font-weight: 600; transition: 0.25s; display: inline-flex; align-items: center; gap: 8px; font-size: 0.9rem; }
        .blog-link:hover { transform: translateY(-2px); box-shadow: 0 8px 18px rgba(44,122,94,0.3); }
        .refresh-btn { background: #f0f4f9; border: none; padding: 0.5rem 1.2rem; border-radius: 2rem; font-weight: 600; color: #2c5a4a; transition: 0.2s; cursor: pointer; display: inline-flex; align-items: center; gap: 8px; }
        .refresh-btn:hover { background: #e2e8f0; transform: scale(0.97); }
        .stats-grid { display: grid; grid-template-columns: repeat(4,1fr); gap: 1.5rem; margin-bottom: 2rem; }
        .stat-card { background: white; border-radius: 1.5rem; padding: 1.5rem; display: flex; align-items: center; justify-content: space-between; box-shadow: 0 6px 14px rgba(0,0,0,0.02); border: 1px solid #eef2f8; transition: 0.2s; }
        .stat-card:hover { transform: translateY(-4px); box-shadow: 0 15px 30px rgba(0,0,0,0.05); }
        .stat-info h3 { font-size: 2.2rem; font-weight: 800; color: #1e4a3b; }
        .stat-icon { width: 55px; height: 55px; background: #e4f3ed; border-radius: 1.2rem; display: flex; align-items: center; justify-content: center; font-size: 1.8rem; color: #2c7a5e; }
        .charts-section { display: grid; grid-template-columns: repeat(2,1fr); gap: 1.5rem; margin-bottom: 2rem; }
        .chart-card { background: white; border-radius: 1.5rem; padding: 1.3rem; box-shadow: 0 4px 12px rgba(0,0,0,0.03); border: 1px solid #edf2f7; }
        .chart-card.full-width { grid-column: span 2; }
        .chart-container { position: relative; height: 250px; }
        .data-section { background: white; border-radius: 1.5rem; padding: 1.8rem; margin-bottom: 2rem; border: 1px solid #edf2f7; }
        .section-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; border-left: 5px solid #2c7a5e; padding-left: 1rem; }
        .posts-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(340px,1fr)); gap: 1.8rem; margin-top: 0.5rem; }
        .post-card { background: white; border-radius: 1.5rem; overflow: hidden; transition: 0.25s; box-shadow: 0 8px 20px rgba(0,0,0,0.03); border: 1px solid #eef2f6; display: flex; flex-direction: column; }
        .post-card:hover { transform: translateY(-6px); box-shadow: 0 20px 30px -12px rgba(0,0,0,0.12); border-color: #d9e2ec; }
        .post-media { background: #fafcfd; display: flex; justify-content: center; align-items: center; padding: 1rem; min-height: 180px; border-bottom: 1px solid #f0f4f8; position: relative; }
        .post-media img, .post-media video { max-width: 100%; max-height: 180px; object-fit: contain; border-radius: 1rem; }
        .media-icon-overlay { position: absolute; top: 10px; right: 10px; background: rgba(0,0,0,0.6); border-radius: 30px; padding: 4px 8px; color: white; font-size: 0.7rem; display: flex; align-items: center; gap: 4px; backdrop-filter: blur(4px); }
        .no-media { color: #9aaebf; font-size: 0.85rem; display: flex; align-items: center; gap: 0.5rem; background: #f8fafc; padding: 0.6rem 1rem; border-radius: 2rem; }
        .post-content { padding: 1.2rem 1.2rem 0.8rem; }
        .post-content p { font-size: 0.95rem; line-height: 1.45; color: #2c3e42; display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden; }
        .post-meta { padding: 0 1.2rem; display: flex; justify-content: space-between; font-size: 0.75rem; color: #6f8f9c; border-top: 1px solid #eff3f8; padding-top: 0.8rem; padding-bottom: 0.8rem; }
        .post-meta i { margin-right: 4px; width: 16px; }
        .post-actions { display: flex; gap: 0.75rem; padding: 0.8rem 1.2rem 1.2rem; border-top: 1px solid #f0f4fa; background: #fefefe; }
        .btn-icon { background: transparent; border: none; font-size: 0.9rem; padding: 0.5rem 1rem; border-radius: 2rem; font-weight: 500; cursor: pointer; transition: all 0.2s ease; display: inline-flex; align-items: center; gap: 8px; }
        .btn-edit-card { color: #2c7a5e; background: #eef6f2; }
        .btn-edit-card:hover { background: #e0efe8; transform: translateY(-2px); }
        .btn-delete-card { color: #c2412c; background: #feece8; }
        .btn-delete-card:hover { background: #fbe0db; transform: translateY(-2px); }
        .data-table { width: 100%; border-collapse: collapse; }
        .data-table th { background: #f8fafd; padding: 1rem; text-align: left; font-weight: 600; color: #2c5a4a; }
        .data-table td { padding: 0.9rem 1rem; border-bottom: 1px solid #edf2f8; }
        .action-btns { display: flex; gap: 0.5rem; }
        .btn-edit, .btn-delete { background: none; border: none; cursor: pointer; padding: 0.4rem; border-radius: 0.5rem; font-size: 1rem; transition: 0.2s; }
        .btn-edit { color: #2c7a5e; }
        .btn-edit:hover { background: #eef6f2; transform: scale(1.05); }
        .btn-delete { color: #dc3c2c; }
        .btn-delete:hover { background: #feece8; transform: scale(1.05); }
        .alert-message { padding: 1rem; border-radius: 1rem; margin-bottom: 1.2rem; }
        .alert-success { background: #dff0e8; color: #155e44; border-left: 5px solid #2c7a5e; }
        .alert-error { background: #ffe6e2; color: #a13123; border-left: 5px solid #dc3c2c; }
        input, textarea, select { border: 1px solid #dce5ec; border-radius: 1rem; padding: 0.75rem 1rem; font-family: 'Inter', sans-serif; transition: 0.2s; width: 100%; margin-bottom: 1rem; }
        input:focus, textarea:focus, select:focus { outline: none; border-color: #2c7a5e; box-shadow: 0 0 0 3px rgba(44,122,94,0.1); }
        input[type="file"] { padding: 0.5rem; background: #f8fafc; border: 1px dashed #bcd0df; cursor: pointer; }
        input[type="file"]::-webkit-file-upload-button { background: #2c7a5e; color: white; border: none; padding: 0.5rem 1rem; border-radius: 2rem; margin-right: 1rem; cursor: pointer; transition: 0.2s; }
        input[type="file"]::-webkit-file-upload-button:hover { background: #1e5a46; }
        button[type="submit"] { background: #2c7a5e; color: white; border: none; border-radius: 2rem; padding: 0.6rem 1.5rem; font-weight: 600; cursor: pointer; transition: 0.2s; display: inline-flex; align-items: center; gap: 8px; }
        button[type="submit"]:hover { background: #1e5a46; transform: translateY(-1px); }
        .modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(18,28,32,0.6); backdrop-filter: blur(5px); z-index: 10000; justify-content: center; align-items: center; }
        .modal.show { display: flex; }
        .modal-content { background: white; border-radius: 2rem; padding: 2rem; width: 90%; max-width: 650px; max-height: 90vh; overflow-y: auto; box-shadow: 0 25px 40px rgba(0,0,0,0.2); }
        .modal-content h3 { margin-bottom: 1rem; display: flex; align-items: center; gap: 10px; }
        .current-media-preview { background: #f8fafc; padding: 0.8rem; border-radius: 1rem; margin: 0.5rem 0; }
        .current-media-preview img, .current-media-preview video { max-width: 100%; max-height: 150px; border-radius: 12px; margin-top: 8px; display: block; }
        .modal-buttons { display: flex; justify-content: flex-end; gap: 1rem; margin-top: 1.5rem; }
        @media (max-width: 1024px) { .dashboard { grid-template-columns: 1fr; } .sidebar { display: none; } .stats-grid { grid-template-columns: repeat(2,1fr); } .charts-section { grid-template-columns: 1fr; } .chart-card.full-width { grid-column: span 1; } }
    </style>
</head>
<body>
<div class="dashboard">
    <aside class="sidebar">
        <div class="logo-area">
            <img class="logo-icon" src="logo.png" alt="Logo">
            <div class="logo-text"><div class="smart">Smart</div><div class="municipality">Municipality</div></div>
        </div>
        <!-- MENU LATÉRAL CORRIGÉ -->
        <div class="nav-menu">
            <div class="nav-item active">
                <i class="fas fa-user-circle"></i>
                <span>Dashboard</span>
            </div>
            <div class="nav-item">
                <i class="fas fa-chart-line"></i>
                <span>Profil</span>
            </div>
            <div class="nav-item">
                <i class="fas fa-calendar-alt"></i>
                <span>Événements</span>
            </div>
            <div class="nav-item">
                <i class="fas fa-map-marked-alt"></i>
                <span>Carte</span>
            </div>
            <div class="nav-item">
                <i class="fas fa-blog"></i>
                <span>Blog</span>
            </div>
            <div class="nav-item">
                <i class="fas fa-concierge-bell"></i>
                <span>Services</span>
            </div>
            <div class="nav-item">
                <i class="fas fa-calendar-check"></i>
                <span>RDV</span>
            </div>
        </div>
    </aside>
    <main class="main-content">
        <div class="top-bar">
            <div class="page-title"><i class="fas fa-chart-line"></i> Dashboard Administrateur</div>
            <div style="display:flex; gap:1rem; align-items:center;">
                <button class="refresh-btn" onclick="location.reload()"><i class="fas fa-sync-alt"></i> Actualiser</button>
                <a href="frontoffice.php" class="blog-link"><i class="fas fa-blog"></i> Voir le Blog</a>
                <div style="display:flex; align-items:center; gap:8px;">
                    <img src="<?php echo $_SESSION['user_avatar'] ?? 'https://ui-avatars.com/api/?background=2c7a5e&color=fff'; ?>" style="width:32px; height:32px; border-radius:50%;">
                    <span><?php echo $_SESSION['user_name']; ?></span>
                </div>
            </div>
        </div>

        <?php if ($success_message): ?><div class="alert-message alert-success"><?php echo htmlspecialchars($success_message); ?></div><?php endif; ?>
        <?php if ($error_message): ?><div class="alert-message alert-error"><?php echo htmlspecialchars($error_message); ?></div><?php endif; ?>

        <!-- STATS -->
        <div class="stats-grid">
            <div class="stat-card"><div class="stat-info"><h3><?php echo $stats['totalPosts']; ?></h3><p>Total posts</p></div><div class="stat-icon"><i class="fas fa-blog"></i></div></div>
            <div class="stat-card"><div class="stat-info"><h3><?php echo $stats['totalUsers']; ?></h3><p>Utilisateurs</p></div><div class="stat-icon"><i class="fas fa-users"></i></div></div>
            <div class="stat-card"><div class="stat-info"><h3><?php echo $stats['totalComments']; ?></h3><p>Commentaires</p></div><div class="stat-icon"><i class="fas fa-comments"></i></div></div>
            <div class="stat-card"><div class="stat-info"><h3><?php echo $stats['totalReactions']; ?></h3><p>Réactions</p></div><div class="stat-icon"><i class="fas fa-heart"></i></div></div>
        </div>

        <!-- GRAPHIQUES -->
        <div class="charts-section">
            <div class="chart-card"><h3><i class="fas fa-chart-bar"></i> Posts par jour (7j)</h3><div class="chart-container"><canvas id="barChart"></canvas></div></div>
            <div class="chart-card"><h3><i class="fas fa-chart-pie"></i> Distribution média</h3><div class="chart-container"><canvas id="pieChart"></canvas></div></div>
            <div class="chart-card full-width"><h3><i class="fas fa-chart-line"></i> Activité (30 jours)</h3><div class="chart-container"><canvas id="lineChart"></canvas></div></div>
        </div>

        <!-- AJOUTER UN POST -->
        <div class="data-section">
            <div class="section-header"><h3><i class="fas fa-plus-circle"></i> Créer une publication</h3></div>
            <form id="createPostForm" method="POST" action="/projetweb/controllers/DashboardController.php" enctype="multipart/form-data">
                <input type="hidden" name="action" value="createPost">
                <textarea name="content" rows="3" placeholder="Contenu du post..." required></textarea>
                <div style="display:flex; gap:1rem; flex-wrap:wrap; align-items:center;">
                    <input type="file" name="image" accept="image/*" id="postImage">
                    <input type="file" name="video" accept="video/*" id="postVideo">
                    <button type="submit"><i class="fas fa-paper-plane"></i> Publier</button>
                </div>
                <small id="fileError" style="color:#dc3c2c; display:none;"></small>
            </form>
        </div>

        <!-- AJOUTER UN COMMENTAIRE -->
        <div class="data-section">
            <div class="section-header"><h3><i class="fas fa-comment-dots"></i> Ajouter un commentaire (admin)</h3></div>
            <form id="createCommentForm" method="POST" action="/projetweb/controllers/DashboardController.php">
                <input type="hidden" name="action" value="createComment">
                <select name="post_id" required>
                    <option value="">Choisir un post</option>
                    <?php foreach ($posts as $post): ?>
                        <option value="<?php echo $post['id']; ?>">Post #<?php echo $post['id']; ?> - <?php echo htmlspecialchars(substr($post['content'],0,50)); ?></option>
                    <?php endforeach; ?>
                </select>
                <textarea name="content" rows="2" placeholder="Votre commentaire..." required></textarea>
                <button type="submit"><i class="fas fa-comment"></i> Ajouter commentaire</button>
            </form>
        </div>

        <!-- GRILLE DES POSTS -->
        <div class="data-section">
            <div class="section-header"><h3><i class="fas fa-images"></i> Toutes les publications</h3><span><?php echo count($posts); ?> éléments</span></div>
            <div class="posts-grid" id="postsGrid">
                <?php foreach ($posts as $post): ?>
                <div class="post-card" data-post-id="<?php echo $post['id']; ?>">
                    <div class="post-media">
                        <?php if (!empty($post['image']) && strpos($post['image'], 'data:image') === 0): ?>
                            <img src="<?php echo $post['image']; ?>" alt="Image du post" loading="lazy">
                            <div class="media-icon-overlay"><i class="fas fa-image"></i> Image</div>
                        <?php elseif (!empty($post['video']) && strpos($post['video'], 'data:video') === 0): ?>
                            <video controls src="<?php echo $post['video']; ?>" preload="metadata"></video>
                            <div class="media-icon-overlay"><i class="fas fa-video"></i> Vidéo</div>
                        <?php else: ?>
                            <div class="no-media"><i class="fas fa-file-alt"></i> <span>Post textuel</span></div>
                        <?php endif; ?>
                    </div>
                    <div class="post-content">
                        <p><?php echo nl2br(htmlspecialchars(substr($post['content'], 0, 200))); ?><?php echo strlen($post['content']) > 200 ? '…' : ''; ?></p>
                    </div>
                    <div class="post-meta">
                        <span><i class="fas fa-user-circle"></i> <?php echo htmlspecialchars($post['user_name']); ?></span>
                        <span><i class="far fa-calendar-alt"></i> <?php echo date('d/m/Y H:i', strtotime($post['created_at'])); ?></span>
                        <span><i class="fas fa-comment"></i> <?php echo $post['comments_count']; ?></span>
                    </div>
                    <div class="post-actions">
                        <button class="btn-icon btn-edit-card" 
                            onclick="openEditPostModal(
                                <?php echo $post['id']; ?>, 
                                '<?php echo addslashes($post['content']); ?>',
                                '<?php echo addslashes($post['image'] ?? ''); ?>',
                                '<?php echo addslashes($post['video'] ?? ''); ?>'
                            )">
                            <i class="fas fa-edit"></i> Modifier
                        </button>
                        <form method="POST" action="/projetweb/controllers/DashboardController.php" onsubmit="return confirm('Supprimer définitivement ce post ?');">
                            <input type="hidden" name="action" value="deletePost">
                            <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
                            <button type="submit" class="btn-icon btn-delete-card"><i class="fas fa-trash-alt"></i> Supprimer</button>
                        </form>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- TABLEAU DES COMMENTAIRES -->
        <div class="data-section">
            <div class="section-header"><h3><i class="fas fa-comments"></i> Commentaires récents</h3></div>
            <div style="overflow-x:auto;">
                <table class="data-table">
                    <thead><tr><th>ID</th><th>Auteur</th><th>Post associé</th><th>Commentaire</th><th>Date</th><th>Actions</th></tr></thead>
                    <tbody>
                        <?php foreach ($comments as $comment): ?>
                        <tr>
                            <td><?php echo $comment['id']; ?></td>
                            <td><?php echo htmlspecialchars($comment['user_name']); ?></td>
                            <td><?php echo htmlspecialchars(substr($comment['post_content'],0,40)); ?>...</td>
                            <td><?php echo htmlspecialchars(substr($comment['content'],0,70)); ?>...</td>
                            <td><?php echo date('d/m/Y H:i', strtotime($comment['created_at'])); ?></td>
                            <td class="action-btns">
                                <button class="btn-edit" onclick="openEditCommentModalAdmin(<?php echo $comment['id']; ?>, '<?php echo addslashes($comment['content']); ?>')"><i class="fas fa-edit"></i></button>
                                <form method="POST" action="/projetweb/controllers/DashboardController.php" style="display:inline;" onsubmit="return confirm('Supprimer ce commentaire ?');">
                                    <input type="hidden" name="action" value="deleteComment"><input type="hidden" name="comment_id" value="<?php echo $comment['id']; ?>">
                                    <button type="submit" class="btn-delete"><i class="fas fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>

<!-- MODAL ÉDITION POST -->
<div id="editPostModal" class="modal">
    <div class="modal-content">
        <h3><i class="fas fa-edit"></i> Modifier la publication</h3>
        <form id="editPostForm" method="POST" action="/projetweb/controllers/DashboardController.php" enctype="multipart/form-data">
            <input type="hidden" name="action" value="updatePost">
            <input type="hidden" name="post_id" id="edit_post_id">
            <label>Contenu :</label>
            <textarea id="edit_post_content" name="content" rows="4" required></textarea>

            <div class="current-media-preview">
                <strong><i class="fas fa-photo-video"></i> Médias actuels :</strong>
                <div id="currentImagePreview"></div>
                <div id="currentVideoPreview"></div>
            </div>

            <label><i class="fas fa-image"></i> Nouvelle image (laissé vide = conserve) :</label>
            <input type="file" name="image" accept="image/*" id="edit_image">

            <label><i class="fas fa-video"></i> Nouvelle vidéo (laissé vide = conserve) :</label>
            <input type="file" name="video" accept="video/*" id="edit_video">

            <div class="modal-buttons">
                <button type="button" class="refresh-btn" onclick="closeEditPostModal()"><i class="fas fa-times"></i> Annuler</button>
                <button type="submit"><i class="fas fa-save"></i> Enregistrer</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL ÉDITION COMMENTAIRE -->
<div id="editCommentModalAdmin" class="modal">
    <div class="modal-content">
        <h3><i class="fas fa-comment-edit"></i> Éditer le commentaire</h3>
        <form id="editCommentForm" method="POST" action="/projetweb/controllers/DashboardController.php">
            <input type="hidden" name="action" value="updateComment">
            <input type="hidden" name="comment_id" id="edit_comment_id_admin">
            <textarea id="edit_comment_content_admin" name="content" rows="3" required></textarea>
            <div class="modal-buttons">
                <button type="button" class="refresh-btn" onclick="closeEditCommentModalAdmin()"><i class="fas fa-times"></i> Annuler</button>
                <button type="submit"><i class="fas fa-save"></i> Sauvegarder</button>
            </div>
        </form>
    </div>
</div>

<script>
    // GRAPHIQUES
    const postsByDay = <?php echo json_encode($stats['postsByDay']); ?>;
    const contentDist = <?php echo json_encode($stats['contentDistribution']); ?>;
    const activityTimeline = <?php echo json_encode($stats['activityTimeline']); ?>;

    new Chart(document.getElementById('barChart'), {
        type: 'bar',
        data: { labels: postsByDay.map(i=>i.date), datasets: [{ label:'Publications', data: postsByDay.map(i=>i.count), backgroundColor: '#2c7a5e', borderRadius: 8 }] },
        options: { responsive: true, maintainAspectRatio: false }
    });
    new Chart(document.getElementById('pieChart'), {
        type: 'pie',
        data: { labels: ['Avec image','Avec vidéo','Texte seul'], datasets: [{ data: [contentDist.with_image, contentDist.with_video, contentDist.text_only], backgroundColor: ['#2c7a5e','#F4B942','#8ba0ae'] }] },
        options: { responsive: true, maintainAspectRatio: false }
    });
    new Chart(document.getElementById('lineChart'), {
        type: 'line',
        data: { labels: activityTimeline.map(i=>i.date), datasets: [{ label:'Posts créés', data: activityTimeline.map(i=>i.posts_count), borderColor: '#2c7a5e', tension: 0.3, fill: true, backgroundColor: 'rgba(44,122,94,0.05)' }] },
        options: { responsive: true, maintainAspectRatio: false }
    });

    // VALIDATIONS
    document.getElementById('createPostForm')?.addEventListener('submit', function(e) {
        const content = this.querySelector('textarea[name="content"]').value.trim();
        const image = this.querySelector('input[name="image"]').files[0];
        const video = this.querySelector('input[name="video"]').files[0];
        const errorSpan = document.getElementById('fileError');
        if (!content) { alert('Le contenu du post ne peut pas être vide.'); e.preventDefault(); return; }
        if (image && video) { errorSpan.textContent = 'Vous ne pouvez pas uploader une image ET une vidéo en même temps.'; errorSpan.style.display = 'block'; e.preventDefault(); return; }
        if (image && !image.type.startsWith('image/')) { errorSpan.textContent = 'Format image invalide.'; errorSpan.style.display = 'block'; e.preventDefault(); return; }
        if (video && !video.type.startsWith('video/')) { errorSpan.textContent = 'Format vidéo invalide.'; errorSpan.style.display = 'block'; e.preventDefault(); return; }
        errorSpan.style.display = 'none';
    });

    document.getElementById('createCommentForm')?.addEventListener('submit', function(e) {
        const content = this.querySelector('textarea[name="content"]').value.trim();
        const postId = this.querySelector('select[name="post_id"]').value;
        if (!content) { alert('Le commentaire ne peut pas être vide.'); e.preventDefault(); }
        else if (!postId) { alert('Veuillez choisir un post.'); e.preventDefault(); }
    });

    document.getElementById('editPostForm')?.addEventListener('submit', function(e) {
        const content = document.getElementById('edit_post_content').value.trim();
        if (!content) { alert('Le contenu ne peut pas être vide.'); e.preventDefault(); }
        const newImage = document.getElementById('edit_image').files[0];
        const newVideo = document.getElementById('edit_video').files[0];
        if (newImage && newVideo) { alert('Veuillez choisir soit une image, soit une vidéo, pas les deux.'); e.preventDefault(); }
    });

    document.getElementById('editCommentForm')?.addEventListener('submit', function(e) {
        const content = document.getElementById('edit_comment_content_admin').value.trim();
        if (!content) { alert('Le commentaire ne peut pas être vide.'); e.preventDefault(); }
    });

    // MODAL POST
    function openEditPostModal(id, content, imageUrl, videoUrl) {
        document.getElementById('edit_post_id').value = id;
        document.getElementById('edit_post_content').value = content;
        const imgDiv = document.getElementById('currentImagePreview');
        const vidDiv = document.getElementById('currentVideoPreview');
        if (imageUrl && imageUrl.startsWith('data:image')) {
            imgDiv.innerHTML = `<img src="${imageUrl}" alt="Image actuelle"><br><small><i class="fas fa-image"></i> Image existante</small>`;
        } else {
            imgDiv.innerHTML = '<em>Aucune image</em>';
        }
        if (videoUrl && videoUrl.startsWith('data:video')) {
            vidDiv.innerHTML = `<video controls src="${videoUrl}"></video><br><small><i class="fas fa-video"></i> Vidéo existante</small>`;
        } else {
            vidDiv.innerHTML = '<em>Aucune vidéo</em>';
        }
        document.getElementById('edit_image').value = '';
        document.getElementById('edit_video').value = '';
        document.getElementById('editPostModal').classList.add('show');
    }
    function closeEditPostModal() { document.getElementById('editPostModal').classList.remove('show'); }

    function openEditCommentModalAdmin(id, content) {
        document.getElementById('edit_comment_id_admin').value = id;
        document.getElementById('edit_comment_content_admin').value = content;
        document.getElementById('editCommentModalAdmin').classList.add('show');
    }
    function closeEditCommentModalAdmin() { document.getElementById('editCommentModalAdmin').classList.remove('show'); }

    window.onclick = function(e) {
        if (e.target === document.getElementById('editPostModal')) closeEditPostModal();
        if (e.target === document.getElementById('editCommentModalAdmin')) closeEditCommentModalAdmin();
    };
</script>
</body>
</html>