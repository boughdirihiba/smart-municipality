<?php
$avatarName = $user['avatar'] ?? 'sidebar-photo.svg';
$displayName = trim(($user['prenom'] ?? '') . ' ' . ($user['nom'] ?? ''));
if ($displayName === '') $displayName = 'Utilisateur';
?>

<section class="profile-grid">
    <div class="card profile-card profile-avatar-card">
        <img class="profile-avatar" src="<?php echo BASE_URL; ?>/public/uploads/<?php echo e($avatarName); ?>" alt="Avatar">
        <div class="profile-name"><?php echo e($displayName); ?></div>
        <div class="profile-role"><?php echo e($user['role'] ?? 'citoyen'); ?></div>
        <div class="profile-email"><?php echo e($user['email'] ?? ''); ?></div>
    </div>

    <div>
        <div class="card profile-card" style="margin-bottom:24px;">
            <p class="profile-section-title">Informations personnelles</p>
            <form class="profile-form" method="POST" action="<?php echo BASE_URL; ?>/index.php?route=profile/update">
                <div class="form-row-2">
                    <div class="form-group">
                        <label>Prénom</label>
                        <input class="input" type="text" name="prenom" value="<?php echo e($user['prenom'] ?? ''); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Nom</label>
                        <input class="input" type="text" name="nom" value="<?php echo e($user['nom'] ?? ''); ?>" required>
                    </div>
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input class="input" type="email" name="email" value="<?php echo e($user['email'] ?? ''); ?>" required>
                </div>
                <div class="form-row-2">
                    <div class="form-group">
                        <label>Téléphone</label>
                        <input class="input" type="text" name="telephone" value="<?php echo e($user['telephone'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label>Adresse</label>
                        <input class="input" type="text" name="adresse" value="<?php echo e($user['adresse'] ?? ''); ?>">
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">Enregistrer</button>
            </form>
        </div>

        <div class="card profile-card">
            <p class="profile-section-title">Changer le mot de passe</p>
            <form class="profile-form" method="POST" action="<?php echo BASE_URL; ?>/index.php?route=profile/password">
                <div class="form-group">
                    <label>Mot de passe actuel</label>
                    <input class="input" type="password" name="current_password" required>
                </div>
                <div class="form-row-2">
                    <div class="form-group">
                        <label>Nouveau mot de passe</label>
                        <input class="input" type="password" name="new_password" minlength="6" required>
                    </div>
                    <div class="form-group">
                        <label>Confirmer</label>
                        <input class="input" type="password" name="confirm_password" minlength="6" required>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">Changer le mot de passe</button>
            </form>
        </div>
    </div>
</section>
