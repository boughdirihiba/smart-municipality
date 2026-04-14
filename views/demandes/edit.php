<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modifier une demande</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: Arial, sans-serif;
            background: #f4f6f5;
            padding: 40px;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        h2 {
            color: #1f5f3a;
            margin-bottom: 20px;
            text-align: center;
        }
        input, select, textarea {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
        }
        button {
            background: #1f5f3a;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 20px;
            cursor: pointer;
            margin-right: 10px;
        }
        button:hover {
            background: #2ecc71;
        }
        .btn-cancel {
            background: #dc3545;
        }
        .btn-cancel:hover {
            background: #c82333;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>✏️ Modifier la demande #<?php echo $demande['id']; ?></h2>
        <form action="index.php?action=update" method="POST">
            <input type="hidden" name="id" value="<?php echo $demande['id']; ?>">
            
            <input type="text" name="nom" value="<?php echo htmlspecialchars($demande['nom']); ?>" required placeholder="Nom">
            
            <select name="type_service" required>
                <option value="">-- Type de service --</option>
                <option value="Légalisation de documents" <?php echo ($demande['type_service'] == 'Légalisation de documents') ? 'selected' : ''; ?>>Légalisation de documents</option>
                <option value="Extrait de naissance" <?php echo ($demande['type_service'] == 'Extrait de naissance') ? 'selected' : ''; ?>>Extrait de naissance</option>
                <option value="Paiement taxes" <?php echo ($demande['type_service'] == 'Paiement taxes') ? 'selected' : ''; ?>>Paiement taxes</option>
                <option value="Dépôt de dossier" <?php echo ($demande['type_service'] == 'Dépôt de dossier') ? 'selected' : ''; ?>>Dépôt de dossier</option>
            </select>
            
            <textarea name="documents" rows="3" required placeholder="Documents"><?php echo htmlspecialchars($demande['documents']); ?></textarea>
            
            <input type="date" name="date_creation" value="<?php echo $demande['date_creation']; ?>" required>
            
            <button type="submit">💾 Enregistrer</button>
            <a href="index.php?action=manage">
                <button type="button" class="btn-cancel">❌ Annuler</button>
            </a>
        </form>
    </div>
</body>
</html>