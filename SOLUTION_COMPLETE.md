## 📋 SMART MUNICIPALITY - SOLUTION COMPLÈTE (4 PROBLÈMES)

### 🎯 RÉSUMÉ DES MODIFICATIONS

Ce document explique comment j'ai corrigé les 4 problèmes dans votre application Smart Municipality.

---

## ✅ PROBLÈME 1: CHANGEMENT DE LANGUE (FR/EN/AR)

### 📝 Situation
- Cliquer sur FR/EN/AR redirige vers la page "Carte intelligente" au lieu de rester sur la même page.

### ✨ Solution
La solution était déjà partiellement implémentée dans votre code, mais voici comment elle fonctionne :

#### 1️⃣ **Fichier: `controllers/BlogController.php`**
```php
public function setLanguage() {
    $allowed = ['fr', 'en', 'ar'];
    if (isset($_GET['lang']) && in_array($_GET['lang'], $allowed, true)) {
        $_SESSION['app_lang'] = $_GET['lang'];
    }
    $this->redirectBack();  // ← Redirection intelligente
}

private function redirectBack() {
    // 1. D'abord, regarder si un paramètre 'redirect' est passé
    $redirect = isset($_GET['redirect']) ? rawurldecode($_GET['redirect']) : '';
    if (!empty($redirect) && strpos($redirect, '://') === false && strpos($redirect, '/') === 0) {
        header('Location: ' . $redirect);
        exit();
    }
    
    // 2. Sinon, utiliser le HTTP_REFERER
    $referer = $_SERVER['HTTP_REFERER'] ?? '';
    if (!empty($referer) && strpos($referer, $_SERVER['HTTP_HOST']) !== false) {
        header('Location: ' . $referer);
        exit();
    }
    
    // 3. Fallback: rediriger vers la page blog par défaut
    header('Location: ' . BASE_URL . '/index.php?action=blog');
    exit();
}
```

#### 2️⃣ **Fichier: `views/App/Views/layouts/header.php`** (MODIFIÉ)
✅ Ajout du paramètre `redirect` qui capture l'URL actuelle :

```php
<?php $redirectUrl = rawurlencode($_SERVER['REQUEST_URI'] ?? '/index.php?action=blog'); ?>

<!-- Liens de langue avec paramètre redirect -->
<a href="<?php echo BASE_URL; ?>/index.php?action=setLanguage&lang=fr&redirect=<?php echo $redirectUrl; ?>">FR</a>
<a href="<?php echo BASE_URL; ?>/index.php?action=setLanguage&lang=en&redirect=<?php echo $redirectUrl; ?>">EN</a>
<a href="<?php echo BASE_URL; ?>/index.php?action=setLanguage&lang=ar&redirect=<?php echo $redirectUrl; ?>">AR</a>
```

#### 3️⃣ **Fichier: `legacy_router.php`** (AUCUNE MODIFICATION NÉCESSAIRE)
```php
case 'setLanguage':  $blogC->setLanguage();  break;
```

### 🧪 TEST
1. Allez à `http://localhost/smart/smart-municipality/index.php?action=blog`
2. Cliquez sur "FR", "EN" ou "AR"
3. ✅ Vous devez rester sur `?action=blog` avec la langue changée
4. Allez à `http://localhost/smart/smart-municipality/index.php?action=blog&search=test`
5. Cliquez sur une langue
6. ✅ Vous devez rester sur l'URL avec les paramètres conservés

---

## ✅ PROBLÈME 2: MODE SOMBRE (NAVBAR + CSS)

### 📝 Situation
- L'action `setTheme` existe mais le CSS ne change pas la navbar.

### ✨ Solution Appliquée

#### 1️⃣ **Fichier: `views/App/Views/layouts/header.php`** (MODIFIÉ)
✅ Ajout de la classe `dark-mode` au body :

```php
<?php $currentTheme = $_SESSION['user_theme'] ?? 'light'; ?>
<?php $darkModeClass = ($currentTheme === 'dark') ? 'dark-mode' : ''; ?>
<body class="role-<?php echo $userRole; ?> theme-<?php echo $currentTheme; ?> <?php echo $darkModeClass; ?>" style="font-size: <?php echo $currentFontSize; ?>%;">
```

#### 2️⃣ **Fichier: `public/css/style.css`** (MODIFIÉ)
✅ Ajout de styles pour le mode sombre au bout du fichier :

```css
/* ========== DARK MODE ENHANCEMENTS ========== */
body.dark-mode,
body.theme-dark {
    --accessibility-btn-bg: rgba(255, 255, 255, 0.08);
    --accessibility-btn-border: rgba(255, 255, 255, 0.16);
    --accessibility-btn-hover: rgba(255, 255, 255, 0.16);
}

/* Accessibility panel in dark mode */
body.dark-mode .accessibility-panel,
body.theme-dark .accessibility-panel {
    background: rgba(0, 0, 0, 0.3);
    border-radius: 12px;
    padding: 0.5rem 0.75rem;
}

body.dark-mode .accessibility-btn,
body.theme-dark .accessibility-btn {
    background: var(--accessibility-btn-bg);
    border-color: var(--accessibility-btn-border);
    color: #e5e7eb;
}

body.dark-mode .accessibility-btn:hover,
body.dark-mode .accessibility-btn.active,
body.theme-dark .accessibility-btn:hover,
body.theme-dark .accessibility-btn.active {
    background: var(--accessibility-btn-hover);
    color: #ffffff;
}

/* Additional dark mode enhancements */
body.dark-mode input,
body.dark-mode textarea,
body.dark-mode select,
body.theme-dark input,
body.theme-dark textarea,
body.theme-dark select {
    background: rgba(255, 255, 255, 0.08);
    color: #e5e7eb;
    border-color: rgba(255, 255, 255, 0.12);
}
```

#### 3️⃣ **Fichier: `legacy_router.php`** (AUCUNE MODIFICATION)
```php
case 'setTheme':  $blogC->setTheme();  break;
```

### 🧪 TEST
1. Cliquez sur l'icône 🌙 (lune) dans la barre d'accessibilité
2. ✅ La page doit passer en mode sombre
3. ✅ La navbar, les cartes et le texte doivent avoir des couleurs sombres
4. Cliquez sur ☀️ (soleil) pour revenir au mode clair

---

## ✅ PROBLÈME 3: BOUTON DASHBOARD (ADMIN UNIQUEMENT)

### 📝 Situation
- Besoin d'ajouter un bouton "Dashboard" visible uniquement pour les admins.

### ✨ Solution Appliquée

#### 1️⃣ **Fichier: `views/App/Views/layouts/header.php`** (MODIFIÉ)
✅ Ajout d'un groupe d'accessibilité pour le bouton Dashboard :

```php
<?php if ($userRole === 'admin'): ?>
<div class="accessibility-group">
    <a class="accessibility-btn" href="<?php echo BASE_URL; ?>/index.php?action=dashboard&redirect=<?php echo $redirectUrl; ?>" title="Accès au tableau de bord">📊</a>
</div>
<?php endif; ?>
```

**Emplacement dans le header.php :**
```html
<div class="accessibility-panel">
    <!-- Groupe Langue -->
    <div class="accessibility-group">...</div>
    
    <!-- Groupe Taille -->
    <div class="accessibility-group">...</div>
    
    <!-- Groupe Thème -->
    <div class="accessibility-group">...</div>
    
    <!-- NEW: Groupe Dashboard (Admin uniquement) -->
    <?php if ($userRole === 'admin'): ?>
    <div class="accessibility-group">
        <a class="accessibility-btn" href="<?php echo BASE_URL; ?>/index.php?action=dashboard&redirect=<?php echo $redirectUrl; ?>" title="Accès au tableau de bord">📊</a>
    </div>
    <?php endif; ?>
</div>
```

### 🧪 TEST
1. **Si vous êtes admin :** Vous devez voir un bouton 📊 dans la barre d'accessibilité
2. **Si vous êtes citoyen :** Le bouton ne doit pas s'afficher
3. Cliquez sur 📊 → ✅ Vous devez accéder au dashboard avec l'URL `?action=dashboard`

---

## ✅ PROBLÈME 4: AJOUT DE POST (STATUT & AFFICHAGE)

### 📝 Situation
- Les nouveaux articles n'apparaissent ni dans `?action=blog` ni dans `?action=getpost`.

### 🔍 Causes Identifiées
1. **Pas de colonne `statut`** dans la table `posts`
2. **createPost()** n'insère pas le statut
3. **getPosts()** et **getPostById()** ne filtrent pas par statut

### ✨ Solutions Appliquées

#### 1️⃣ **Fichier: `sql/schema.sql`** (MODIFIÉ)
✅ Ajout de la colonne `statut` :

```sql
CREATE TABLE IF NOT EXISTS posts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    content TEXT NOT NULL,
    image VARCHAR(255) NULL,
    video VARCHAR(255) NULL,
    statut ENUM('publie', 'brouillon', 'supprime') DEFAULT 'publie',  -- NEW
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_posts_user FOREIGN KEY (user_id) REFERENCES utilisateurs(id) ON DELETE CASCADE
);
```

#### 2️⃣ **Fichier: `sql/migration_add_statut_to_posts.sql`** (NOUVEAU)
✅ Script de migration pour les bases existantes :

```sql
ALTER TABLE posts ADD COLUMN IF NOT EXISTS statut ENUM('publie', 'brouillon', 'supprime') DEFAULT 'publie' AFTER video;
UPDATE posts SET statut = 'publie' WHERE statut IS NULL;
```

**À exécuter :** `mysql smart_municipality < migration_add_statut_to_posts.sql`

#### 3️⃣ **Fichier: `controllers/BlogController.php`** (MODIFIÉ)
✅ **Modification 1 : createPost() - Ajouter le statut**

```php
public function createPost($data, $files) {
    // ... validation ...
    
    $sql = "INSERT INTO posts (user_id, content, image, video, statut, created_at) 
            VALUES (:user_id, :content, :image, :video, :statut, NOW())";
    $stmt = $this->db->prepare($sql);
    $result = $stmt->execute([
        ':user_id' => $_SESSION['user_id'],
        ':content' => trim(htmlspecialchars($data['content'])),
        ':image' => $image,
        ':video' => $video,
        ':statut' => 'publie'  // ← NOUVEAU: Toujours publier par défaut
    ]);
    $_SESSION[$result ? 'success' : 'error'] = $result ? "Post publié" : "Erreur";
    $this->redirectBack();
}
```

✅ **Modification 2 : getPosts() - Filtrer par statut**

```php
public function getPosts($search = '') {
    try {
        $sql = "SELECT p.*, u.name as user_name, u.avatar as user_avatar,
                (SELECT COUNT(*) FROM comments WHERE post_id = p.id) as comments_count,
                (SELECT COUNT(*) FROM reactions WHERE post_id = p.id) as reactions_count
                FROM posts p 
                JOIN users u ON p.user_id = u.id
                WHERE p.statut = 'publie'";  // ← NOUVEAU: Filtre par statut
        
        if (!empty($search)) {
            $sql .= " AND (p.content LIKE :search OR u.name LIKE :search)";  // ← Changé de WHERE à AND
        }
        $sql .= " ORDER BY p.created_at DESC";
        // ... reste du code ...
    }
}
```

✅ **Modification 3 : getPostById() - Filtrer par statut**

```php
public function getPostById(int $id) {
    try {
        $sql = "SELECT p.*, u.name as user_name, u.avatar as user_avatar,
                (SELECT COUNT(*) FROM comments WHERE post_id = p.id) as comments_count,
                (SELECT COUNT(*) FROM reactions WHERE post_id = p.id) as reactions_count
                FROM posts p
                JOIN users u ON p.user_id = u.id
                WHERE p.id = :id AND p.statut = 'publie'";  // ← NOUVEAU: Filtre ajouté
        // ... reste du code ...
    }
}
```

### 🧪 TEST

#### Étape 1 : Ajouter la colonne à la BDD
```bash
# Si la table posts existe déjà, exécutez :
mysql smart_municipality < sql/migration_add_statut_to_posts.sql

# Ou en PhpMyAdmin, exécutez :
ALTER TABLE posts ADD COLUMN IF NOT EXISTS statut ENUM('publie', 'brouillon', 'supprime') DEFAULT 'publie' AFTER video;
```

#### Étape 2 : Tester la création de post
1. Connectez-vous en tant qu'admin
2. Allez à `?action=blog` → Formulaire d'ajout de post
3. Soumettez un nouvel article
4. ✅ Le post doit s'afficher immédiatement dans la liste
5. Cliquez sur le post → ✅ La page `?action=getpost&id=X` doit afficher le post

#### Étape 3 : Vérifier les données
```sql
-- Dans PhpMyAdmin ou terminal mysql :
SELECT id, user_id, content, statut, created_at FROM posts;

-- Tous les posts doivent avoir statut='publie'
```

---

## 📋 RÉSUMÉ DES FICHIERS MODIFIÉS

| Fichier | Modifications |
|---------|---------------|
| `controllers/BlogController.php` | ✅ createPost(): ajouter `:statut => 'publie'` |
| | ✅ getPosts(): ajouter `WHERE p.statut = 'publie'` |
| | ✅ getPostById(): ajouter `AND p.statut = 'publie'` |
| `views/App/Views/layouts/header.php` | ✅ Ajouter classe `dark-mode` au body |
| | ✅ Ajouter bouton Dashboard pour admins |
| `public/css/style.css` | ✅ Ajouter CSS pour dark-mode et accessibility panel |
| `sql/schema.sql` | ✅ Ajouter colonne `statut` dans la table `posts` |
| `sql/migration_add_statut_to_posts.sql` | ✅ NOUVEAU: Script de migration |

---

## 🔧 CHECKLIST DE VALIDATION

### Avant de tester
- [ ] Fichiers modifiés sauvegardés
- [ ] Migration SQL appliquée à la BDD
- [ ] Cache/session effacé (si applicable)

### Pendant les tests
- [ ] Changement de langue → reste sur la même page ✅
- [ ] Bouton mode sombre → CSS appliqué ✅
- [ ] Bouton Dashboard visible pour admins ✅
- [ ] Nouveaux posts s'affichent dans la liste ✅

### Après les tests
- [ ] Vérifier la BDD : colonne `statut` présente
- [ ] Vérifier les logs PHP pour erreurs
- [ ] Tester en différents navigateurs (Chrome, Firefox, Safari)
- [ ] Tester en responsive mode (mobile)

---

## 🆘 DÉPANNAGE

### ❌ Les posts n'apparaissent toujours pas
```
1. Vérifiez que la migration a été appliquée :
   SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME='posts' AND COLUMN_NAME='statut';
   
2. Vérifiez que createPost insère bien le statut :
   SELECT * FROM posts ORDER BY id DESC LIMIT 1; → doit avoir statut='publie'
   
3. Vérifiez les logs PHP :
   tail -f /var/log/apache2/error.log
```

### ❌ Le mode sombre ne s'applique pas
```
1. Vérifiez que body a la classe dark-mode :
   Inspecter l'élément body → doit avoir class="... dark-mode"
   
2. Vérifiez le CSS :
   public/css/style.css → doit contenir "body.dark-mode {"
   
3. Rafraîchissez le cache navigateur : Ctrl+Shift+R
```

### ❌ Le changement de langue redirige mal
```
1. Vérifiez que le paramètre redirect est bien encodé :
   Clic droit → Inspecter l'élément sur le lien FR/EN/AR
   href doit contenir "&redirect=%2Findex.php%3Faction%3Dblog"
   
2. Vérifiez que $_SERVER['REQUEST_URI'] n'est pas vide :
   echo $_SERVER['REQUEST_URI']; dans le header
```

---

## 📞 NOTES TECHNIQUES

1. **Sécurité du paramètre `redirect`** :
   - La méthode `redirectBack()` vérifie que l'URL commence par `/` et ne contient pas `://`
   - Cela empêche les attaques de redirection ouverte

2. **Persistance du thème** :
   - Stocké en `$_SESSION['user_theme']`
   - Persiste pendant la session utilisateur
   - À implémenter : Sauvegarder dans la BDD pour la persistance cross-session

3. **Encodage de l'URL** :
   - `rawurlencode()` : encode l'URL pour l'HTML
   - `rawurldecode()` : décode avant la redirection
   - Exemple : `/index.php?action=blog` → `%2Findex.php%3Faction%3Dblog`

---

## 🎨 AMÉLIORATION FUTURE (optionnelle)

### Persistance du thème en BDD
```php
// Dans la table utilisateurs, ajouter :
ALTER TABLE utilisateurs ADD COLUMN theme ENUM('light', 'dark') DEFAULT 'light';

// Dans le contrôleur :
$_SESSION['user_theme'] = $user['theme'];  // Au lieu de $_SESSION['user_theme']
```

### Bouton "Brouillon"
```php
// Permettre aux admins de créer des posts en brouillon
// Modifier createPost pour accepter un paramètre `statut` :
':statut' => $_POST['is_draft'] ? 'brouillon' : 'publie'
```

### Suppression logique
```sql
-- Plutôt que de supprimer, marquer comme supprimé :
UPDATE posts SET statut = 'supprime' WHERE id = X;
```

---

**✅ SOLUTION COMPLÈTE PRÊTE À UTILISER !**

Posez des questions si vous avez besoin de précisions.
