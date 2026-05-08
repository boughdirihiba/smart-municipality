<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Upload document - Smart Municipality</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #eef2f7 100%);
            min-height: 100vh;
            padding: 40px;
        }
        .upload-container {
            max-width: 600px;
            margin: 0 auto;
            background: white;
            border-radius: 32px;
            padding: 40px;
            box-shadow: 0 20px 35px -12px rgba(0,0,0,0.1);
        }
        .upload-header { text-align: center; margin-bottom: 30px; }
        .upload-header i { font-size: 60px; color: #10b981; margin-bottom: 20px; }
        .upload-header h2 { font-size: 28px; color: #0f172a; margin-bottom: 10px; }
        .drop-zone {
            border: 2px dashed #e2e8f0;
            border-radius: 24px;
            padding: 40px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
            margin-bottom: 20px;
        }
        .drop-zone:hover, .drop-zone.dragover {
            border-color: #10b981;
            background: #f0fdf4;
        }
        .drop-zone i { font-size: 48px; color: #10b981; margin-bottom: 15px; }
        .file-info {
            background: #f8fafc;
            border-radius: 16px;
            padding: 15px;
            margin: 20px 0;
            display: none;
        }
        .btn-upload {
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
            border: none;
            padding: 16px 32px;
            border-radius: 40px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            width: 100%;
        }
        .btn-upload:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(16,185,129,0.3); }
        .btn-back {
            display: inline-block;
            margin-top: 20px;
            text-align: center;
            width: 100%;
            color: #64748b;
            text-decoration: none;
        }
        .btn-back:hover { color: #10b981; }
    </style>
</head>
<body>
    <div class="upload-container">
        <div class="upload-header">
            <i class="fas fa-cloud-upload-alt"></i>
            <h2>Ajouter un document</h2>
            <p>Pour la demande #<?php echo $_GET['demande_id']; ?></p>
        </div>

        <form action="index.php?action=upload_document" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="demande_id" value="<?php echo $_GET['demande_id']; ?>">
            
            <div class="drop-zone" id="dropZone">
                <i class="fas fa-file-pdf"></i>
                <p>Glissez-déposez votre fichier ou cliquez</p>
                <input type="file" name="fichier" id="fileInput" style="display: none;" required>
            </div>

            <div class="file-info" id="fileInfo">
                <i class="fas fa-file"></i>
                <span id="fileName"></span>
                <small id="fileSize"></small>
            </div>

            <button type="submit" class="btn-upload">
                <i class="fas fa-upload"></i> Téléverser
            </button>
        </form>

        <a href="index.php?action=manage" class="btn-back">
            <i class="fas fa-arrow-left"></i> Retour
        </a>
    </div>

    <script>
        const dropZone = document.getElementById('dropZone');
        const fileInput = document.getElementById('fileInput');
        const fileInfo = document.getElementById('fileInfo');
        const fileName = document.getElementById('fileName');
        const fileSize = document.getElementById('fileSize');

        dropZone.addEventListener('click', () => fileInput.click());
        dropZone.addEventListener('dragover', (e) => { e.preventDefault(); dropZone.classList.add('dragover'); });
        dropZone.addEventListener('dragleave', () => { dropZone.classList.remove('dragover'); });
        dropZone.addEventListener('drop', (e) => {
            e.preventDefault();
            dropZone.classList.remove('dragover');
            if(e.dataTransfer.files.length) {
                fileInput.files = e.dataTransfer.files;
                showFileInfo(e.dataTransfer.files[0]);
            }
        });
        fileInput.addEventListener('change', (e) => {
            if(e.target.files.length) showFileInfo(e.target.files[0]);
        });
        function showFileInfo(file) {
            fileName.textContent = file.name;
            let size = file.size;
            if(size < 1024) size = size + ' B';
            else if(size < 1048576) size = (size / 1024).toFixed(2) + ' KB';
            else size = (size / 1048576).toFixed(2) + ' MB';
            fileSize.textContent = ' - ' + size;
            fileInfo.style.display = 'block';
        }
    </script>
</body>
</html>