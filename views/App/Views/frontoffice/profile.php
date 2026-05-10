<?php
$avatarName = $user['avatar'] ?? 'sidebar-photo.svg';
$displayName = trim(($user['prenom'] ?? '') . ' ' . ($user['nom'] ?? ''));
if ($displayName === '') $displayName = 'Utilisateur';
?>

<style>
.profile-grid { display: grid; grid-template-columns: 1fr 2fr; gap: 24px; align-items: start; }
.profile-avatar-card { text-align: center; }
.profile-avatar-card img { width: 120px; height: 120px; border-radius: 50%; object-fit: cover; border: 4px solid #10b981; margin-bottom: 16px; }
.profile-avatar-card strong { display: block; font-size: 18px; color: #0f172a; }
.profile-avatar-card span { color: #64748b; font-size: 14px; }
.form-group { margin-bottom: 20px; }
.form-group label { display: block; font-weight: 600; font-size: 13px; color: #334155; margin-bottom: 6px; }
.form-group input { width: 100%; padding: 11px 16px; border: 2px solid #e2e8f0; border-radius: 12px; font-size: 14px; font-family: inherit; transition: border-color 0.2s, box-shadow 0.2s; box-sizing: border-box; }
.form-group input:focus { outline: none; border-color: #10b981; box-shadow: 0 0 0 3px rgba(16,185,129,0.1); }
.form-row-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
.section-title { font-size: 16px; font-weight: 700; color: #0f172a; margin-bottom: 20px; padding-bottom: 10px; border-bottom: 2px solid #e2e8f0; }
.btn-save { background: linear-gradient(135deg, #10b981, #059669); color: white; border: none; padding: 12px 28px; border-radius: 40px; font-size: 14px; font-weight: 700; cursor: pointer; transition: all 0.2s; }
.btn-save:hover { transform: translateY(-1px); box-shadow: 0 6px 16px rgba(16,185,129,0.3); }
@media (max-width: 768px) {
    .profile-grid { grid-template-columns: 1fr; }
    .form-row-2 { grid-template-columns: 1fr; }
}
</style>

<section class="card profile-grid">
    <div class="card profile-avatar-card">
        <img src="<?php echo BASE_URL; ?>/public/uploads/<?php echo e($avatarName); ?>" alt="Avatar">
        <strong><?php echo e($displayName); ?></strong>
        <span><?php echo e($user['role'] ?? 'citoyen'); ?></span>
        <p style="color:#64748b;font-size:13px;margin-top:8px;"><?php echo e($user['email'] ?? ''); ?></p>
    </div>

    <div>
        <div class="card" style="margin-bottom:24px;">
            <p class="section-title">Informations personnelles</p>
            <form method="POST" action="<?php echo BASE_URL; ?>/index.php?route=profile/update">
                <div class="form-row-2">
                    <div class="form-group">
                        <label>Prénom</label>
                        <input type="text" name="prenom" value="<?php echo e($user['prenom'] ?? ''); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Nom</label>
                        <input type="text" name="nom" value="<?php echo e($user['nom'] ?? ''); ?>" required>
                    </div>
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" value="<?php echo e($user['email'] ?? ''); ?>" required>
                </div>
                <div class="form-row-2">
                    <div class="form-group">
                        <label>Téléphone</label>
                        <input type="text" name="telephone" value="<?php echo e($user['telephone'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label>Adresse</label>
                        <input type="text" name="adresse" value="<?php echo e($user['adresse'] ?? ''); ?>">
                    </div>
                </div>
                <button type="submit" class="btn-save">Enregistrer</button>
            </form>
        </div>

        <div class="card">
            <p class="section-title">Changer le mot de passe</p>
            <form method="POST" action="<?php echo BASE_URL; ?>/index.php?route=profile/password">
                <div class="form-group">
                    <label>Mot de passe actuel</label>
                    <input type="password" name="current_password" required>
                </div>
                <div class="form-row-2">
                    <div class="form-group">
                        <label>Nouveau mot de passe</label>
                        <input type="password" name="new_password" minlength="6" required>
                    </div>
                    <div class="form-group">
                        <label>Confirmer</label>
                        <input type="password" name="confirm_password" minlength="6" required>
                    </div>
                </div>
                <button type="submit" class="btn-save">Changer le mot de passe</button>
            </form>
        </div>
    </div>
</section>
