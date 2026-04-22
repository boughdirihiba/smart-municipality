<?php 
session_start();
require_once __DIR__ . '/../controllers/DashboardController.php';

$controller = new DashboardController();
$stats = $controller->getStats();
$posts = $controller->getAllPosts();

$success_message = $_SESSION['success'] ?? '';
$error_message = $_SESSION['error'] ?? '';
unset($_SESSION['success'], $_SESSION['error']);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Smart Municipality | Dashboard Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background: #f0f2f5; color: #1e2a32; }
        .dashboard { display: grid; grid-template-columns: 280px 1fr; min-height: 100vh; }
        
        .sidebar { 
            background: linear-gradient(180deg, #2FA084 0%, #0f3b2c 100%); 
            color: #e6f4ea; 
            padding: 2rem 1rem; 
            position: sticky; 
            top: 0; 
            height: 100vh; 
        }
        
        .logo-area { text-align: center; margin-bottom: 2rem; }
        .logo-icon { width: 80px; height: auto; margin-bottom: 10px; border-radius: 50%; }
        .logo-text .smart { font-size: 1.6rem; font-weight: 800; color: white; }
        .logo-text .municipality { font-size: 0.7rem; color: #c8e6d9; text-transform: uppercase; }
        
        .nav-item {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 0.75rem 1rem;
            border-radius: 1rem;
            cursor: pointer;
            transition: all 0.3s;
            color: #e6f4ea;
            margin-bottom: 0.5rem;
        }
        .nav-item:hover, .nav-item.active { background: rgba(255,255,255,0.15); transform: translateX(5px); }
        .nav-item i { width: 24px; color: #a8e6cf; }
        
        .main-content { padding: 1.5rem 2rem; overflow-y: auto; background: #f5f7fa; }
        
        .top-bar { 
            display: flex; 
            align-items: center; 
            justify-content: space-between; 
            gap: 1rem; 
            margin-bottom: 2rem; 
            background: white; 
            padding: 0.6rem 1.5rem; 
            border-radius: 1rem; 
            flex-wrap: wrap; 
        }
        .page-title { font-size: 1.2rem; font-weight: 700; color: #0f3b2c; }
        .blog-link { background: linear-gradient(135deg, #2FA084, #0f3b2c); color: white; padding: 0.5rem 1.2rem; border-radius: 0.8rem; text-decoration: none; font-weight: 600; transition: all 0.3s; }
        .blog-link:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(47,160,132,0.3); }
        .user-profile-top { display: flex; align-items: center; gap: 0.7rem; background: #f8f9fa; padding: 0.3rem 1rem 0.3rem 0.5rem; border-radius: 0.8rem; cursor: pointer; }
        .avatar-sm { width: 38px; height: 38px; border-radius: 0.8rem; overflow: hidden; }
        .avatar-sm img { width: 100%; height: 100%; object-fit: cover; }
        
        .stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 1.5rem; margin-bottom: 2rem; }
        .stat-card { background: white; border-radius: 1rem; padding: 1.5rem; display: flex; align-items: center; justify-content: space-between; box-shadow: 0 2px 8px rgba(0,0,0,0.05); transition: all 0.3s; }
        .stat-card:hover { transform: translateY(-5px); box-shadow: 0 8px 25px rgba(0,0,0,0.1); }
        .stat-info h3 { font-size: 2rem; font-weight: 700; color: #0f3b2c; }
        .stat-info p { color: #6c757d; font-size: 0.85rem; margin-top: 0.3rem; }
        .stat-icon { width: 55px; height: 55px; background: #e8f5e9; border-radius: 1rem; display: flex; align-items: center; justify-content: center; font-size: 1.8rem; color: #2b7a4b; }
        
        .charts-section { display: grid; grid-template-columns: repeat(2, 1fr); gap: 1.5rem; margin-bottom: 2rem; }
        .chart-card { background: white; border-radius: 1rem; padding: 1.5rem; box-shadow: 0 2px 8px rgba(0,0,0,0.05); }
        .chart-card h3 { font-size: 1.1rem; font-weight: 600; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.5rem; color: #1e2a32; }
        .chart-card h3 i { color: #2b7a4b; }
        .chart-container { position: relative; height: 250px; }
        .full-width { grid-column: span 2; }
        
        .data-section {
            background: white;
            border-radius: 1rem;
            padding: 1.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }
        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            flex-wrap: wrap;
            gap: 1rem;
        }
        .section-header h3 {
            font-size: 1.2rem;
            color: #0f3b2c;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .data-table {
            width: 100%;
            border-collapse: collapse;
            overflow-x: auto;
            display: block;
        }
        .data-table thead { background: #f8fafc; }
        .data-table th, .data-table td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid #e2edf2;
        }
        .data-table th { font-weight: 600; color: #0f3b2c; }
        .data-table td { color: #475569; }
        
        .post-content-preview { max-width: 300px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .post-media-preview { max-width: 60px; max-height: 40px; border-radius: 0.3rem; }
        
        .action-btns { display: flex; gap: 0.5rem; }
        .btn-edit, .btn-delete {
            background: none;
            border: none;
            cursor: pointer;
            padding: 0.4rem;
            border-radius: 0.4rem;
            transition: all 0.3s;
        }
        .btn-edit { color: #2FA084; }
        .btn-edit:hover { background: #e8f5e9; transform: scale(1.1); }
        .btn-delete { color: #dc3545; }
        .btn-delete:hover { background: #fee2e2; transform: scale(1.1); }
        
        .alert-message { padding: 1rem; border-radius: 0.5rem; margin-bottom: 1rem; }
        .alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert-error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        
        .refresh-btn { background: #2b7a4b; color: white; border: none; padding: 0.5rem 1rem; border-radius: 0.5rem; cursor: pointer; transition: all 0.3s; display: flex; align-items: center; gap: 0.5rem; }
        .refresh-btn:hover { background: #1e5a38; transform: translateY(-2px); }
        
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 10000;
            justify-content: center;
            align-items: center;
        }
        .modal.show { display: flex; }
        
        .modal-content {
            background: white;
            border-radius: 1rem;
            padding: 1.5rem;
            width: 90%;
            max-width: 500px;
        }
        .modal-content h3 { margin-bottom: 1rem; color: #2FA084; }
        .modal-content textarea {
            width: 100%;
            padding: 0.8rem;
            border: 1px solid #e2e8f0;
            border-radius: 0.8rem;
            margin-bottom: 1rem;
            font-family: inherit;
            resize: vertical;
        }
        .modal-buttons { display: flex; gap: 1rem; justify-content: flex-end; }
        .modal-save {
            background: linear-gradient(135deg, #2FA084, #0f3b2c);
            color: white;
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 0.5rem;
            cursor: pointer;
        }
        .modal-cancel {
            background: #e2e8f0;
            color: #475569;
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 0.5rem;
            cursor: pointer;
        }
        
        @media (max-width: 1024px) { 
            .stats-grid { grid-template-columns: repeat(2, 1fr); }
            .charts-section { grid-template-columns: 1fr; }
            .full-width { grid-column: span 1; }
        }
        @media (max-width: 768px) { 
            .dashboard { grid-template-columns: 1fr; }
            .sidebar { display: none; }
        }
    </style>
</head>
<body>
<div class="dashboard">
    <aside class="sidebar">
        <div class="logo-area">
            <img class="logo-icon" src="logo.png" alt="Logo" onerror="this.src='https://placehold.co/80x80/2FA084/white?text=SM'">
            <div class="logo-text">
                <div class="smart">Smart</div>
                <div class="municipality">Municipality</div>
            </div>
        </div>
        <div class="nav-menu">
            <div class="nav-item"><i class="fas fa-user"></i><span>Profil</span></div>
            <div class="nav-item"><i class="fas fa-exclamation-triangle"></i><span>Signalement</span></div>
            <div class="nav-item"><i class="fas fa-blog"></i><span>Blog</span></div>
            <div class="nav-item active"><i class="fas fa-chart-line"></i><span>Dashboard</span></div>
            <div class="nav-item"><i class="fas fa-globe"></i><span>Services</span></div>
            <div class="nav-item"><i class="fas fa-calendar-alt"></i><span>Événements</span></div>
            <div class="nav-item"><i class="fas fa-calendar-check"></i><span>RDV</span></div>
        </div>
    </aside>
    
    <main class="main-content">
        <div class="top-bar">
            <div class="page-title">
                <i class="fas fa-chart-line" style="color: #2b7a4b;"></i> Dashboard Administrateur
            </div>
            <div style="display: flex; gap: 1rem; align-items: center; flex-wrap: wrap;">
                <button class="refresh-btn" onclick="location.reload()">
                    <i class="fas fa-sync-alt"></i> Actualiser
                </button>
                <a href="/projetweb/views/frontoffice.php" class="blog-link">
                    <i class="fas fa-blog"></i> Voir le Blog
                </a>
                <div class="user-profile-top">
                    <div class="avatar-sm">
                        <img src="<?php echo $_SESSION['user_avatar'] ?? 'https://randomuser.me/api/portraits/lego/1.jpg'; ?>" alt="avatar">
                    </div>
                    <span><?php echo $_SESSION['user_name'] ?? 'Admin'; ?></span>
                    <i class="fas fa-chevron-down"></i>
                </div>
            </div>
        </div>
        
        <?php if ($success_message): ?>
            <div class="alert-message alert-success"><?php echo htmlspecialchars($success_message); ?></div>
        <?php endif; ?>
        <?php if ($error_message): ?>
            <div class="alert-message alert-error"><?php echo htmlspecialchars($error_message); ?></div>
        <?php endif; ?>
        
        <!-- Cartes statistiques -->
        <div class="stats-grid">
            <div class="stat-card"><div class="stat-info"><h3><?php echo $stats['totalPosts']; ?></h3><p><i class="fas fa-blog"></i> Total posts</p></div><div class="stat-icon"><i class="fas fa-blog"></i></div></div>
            <div class="stat-card"><div class="stat-info"><h3><?php echo $stats['totalUsers']; ?></h3><p><i class="fas fa-users"></i> Utilisateurs</p></div><div class="stat-icon"><i class="fas fa-users"></i></div></div>
            <div class="stat-card"><div class="stat-info"><h3><?php echo $stats['totalComments']; ?></h3><p><i class="fas fa-comments"></i> Commentaires</p></div><div class="stat-icon"><i class="fas fa-comments"></i></div></div>
            <div class="stat-card"><div class="stat-info"><h3><?php echo $stats['totalReactions']; ?></h3><p><i class="fas fa-heart"></i> Réactions</p></div><div class="stat-icon"><i class="fas fa-heart"></i></div></div>
        </div>
        
        <!-- Graphiques -->
        <div class="charts-section">
            <div class="chart-card"><h3><i class="fas fa-chart-bar"></i> Posts par jour (7 jours)</h3><div class="chart-container"><canvas id="barChart"></canvas></div></div>
            <div class="chart-card"><h3><i class="fas fa-chart-pie"></i> Distribution du contenu</h3><div class="chart-container"><canvas id="pieChart"></canvas></div></div>
            <div class="chart-card full-width"><h3><i class="fas fa-chart-line"></i> Activité (30 jours)</h3><div class="chart-container"><canvas id="lineChart"></canvas></div></div>
        </div>
        
        <!-- Tableau des publications récentes (avec modification) -->
        <div class="data-section">
            <div class="section-header">
                <h3><i class="fas fa-blog"></i> Publications récentes</h3>
            </div>
            <div style="overflow-x: auto;">
                <table class="data-table">
                    <thead>
                        <tr><th>ID</th><th>Auteur</th><th>Contenu</th><th>Média</th><th>Date</th><th>Commentaires</th><th>Actions</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($posts as $post): ?>
                        <tr>
                            <td><?php echo $post['id']; ?></td>
                            <td><?php echo htmlspecialchars($post['user_name']); ?></td>
                            <td class="post-content-preview"><?php echo htmlspecialchars(substr($post['content'], 0, 100)); ?>...</td>
                            <td>
                                <?php if (!empty($post['image'])): ?>
                                    <img src="<?php echo htmlspecialchars($post['image']); ?>" class="post-media-preview" alt="image">
                                <?php elseif (!empty($post['video'])): ?>
                                    <i class="fas fa-video"></i> Vidéo
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                            <td><?php echo date('d/m/Y H:i', strtotime($post['created_at'])); ?></td>
                            <td><?php echo $post['comments_count']; ?></td>
                            <td class="action-btns">
                                <button class="btn-edit" onclick="openEditPostModal(<?php echo $post['id']; ?>, '<?php echo addslashes($post['content']); ?>')"><i class="fas fa-edit"></i></button>
                                <form method="POST" action="/projetweb/controllers/DashboardController.php" style="display:inline;" onsubmit="return confirm('Supprimer cette publication ?')">
                                    <input type="hidden" name="action" value="deletePost">
                                    <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
                                    <button type="submit" class="btn-delete"><i class="fas fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <div class="footer-note" style="text-align:center; padding:1rem; color:#6c757d;">
            <i class="fas fa-chart-simple"></i> Données mises à jour en temps réel | Dernière mise à jour : <?php echo date('d/m/Y H:i:s'); ?>
        </div>
    </main>
</div>

<!-- MODAL MODIFIER UN POST -->
<div id="editPostModal" class="modal">
    <div class="modal-content">
        <h3><i class="fas fa-edit"></i> Modifier la publication</h3>
        <form method="POST" action="/projetweb/controllers/DashboardController.php" onsubmit="return validateEditPost()">
            <input type="hidden" name="action" value="updatePost">
            <input type="hidden" name="post_id" id="edit_post_id">
            <textarea name="content" id="edit_post_content" rows="5" placeholder="Contenu de la publication..." required></textarea>
            <div class="modal-buttons">
                <button type="button" class="modal-cancel" onclick="closeEditPostModal()">Annuler</button>
                <button type="submit" class="modal-save">Enregistrer</button>
            </div>
        </form>
    </div>
</div>

<script>
    // ========== GRAPHIQUES ==========
    const postsByDay = <?php echo json_encode($stats['postsByDay']); ?>;
    const contentDist = <?php echo json_encode($stats['contentDistribution']); ?>;
    const activityTimeline = <?php echo json_encode($stats['activityTimeline']); ?>;
    
    new Chart(document.getElementById('barChart'), {
        type: 'bar',
        data: { labels: postsByDay.map(i => i.date), datasets: [{ label: 'Posts', data: postsByDay.map(i => i.count), backgroundColor: '#2FA084' }] },
        options: { responsive: true, maintainAspectRatio: false }
    });
    
    new Chart(document.getElementById('pieChart'), {
        type: 'pie',
        data: { labels: ['Avec image', 'Avec vidéo', 'Texte seul'], datasets: [{ data: [contentDist.with_image, contentDist.with_video, contentDist.text_only], backgroundColor: ['#2FA084', '#FFB74D', '#78909C'] }] },
        options: { responsive: true, maintainAspectRatio: false }
    });
    
    new Chart(document.getElementById('lineChart'), {
        type: 'line',
        data: { labels: activityTimeline.map(i => i.date), datasets: [{ label: 'Posts créés', data: activityTimeline.map(i => i.posts_count), borderColor: '#2FA084', fill: true, tension: 0.4 }] },
        options: { responsive: true, maintainAspectRatio: false }
    });
    
    // ========== MODAL ÉDITION POST ==========
    function openEditPostModal(id, content) {
        document.getElementById('edit_post_id').value = id;
        document.getElementById('edit_post_content').value = content;
        document.getElementById('editPostModal').classList.add('show');
    }
    function closeEditPostModal() {
        document.getElementById('editPostModal').classList.remove('show');
    }
    
    // ========== VALIDATION JS (contrôle saisie uniquement) ==========
    function validateEditPost() {
        let content = document.getElementById('edit_post_content').value.trim();
        if (content === '') {
            alert('Le contenu de la publication ne peut pas être vide');
            return false;
        }
        return true;
    }
    
    // Fermer la modale en cliquant à l'extérieur
    window.onclick = function(e) {
        if (e.target === document.getElementById('editPostModal')) closeEditPostModal();
    }
</script>
</body>
</html>