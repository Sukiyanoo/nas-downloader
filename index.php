<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Téléchargement NAS</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary: #2563eb;
            --secondary: #3b82f6;
            --background: #f8fafc;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', system-ui;
        }

        body {
            background: var(--background);
            min-height: 100vh;
            padding: 2rem;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            padding: 2rem;
        }

        h1 {
            color: #1e293b;
            margin-bottom: 2rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .drop-zone {
            border: 2px dashed #cbd5e1;
            border-radius: 8px;
            padding: 2rem;
            text-align: center;
            margin-bottom: 1.5rem;
            transition: 0.2s;
            cursor: pointer;
            background: #f8fafc;
        }

        .drop-zone:hover {
            border-color: var(--primary);
            background: #eff6ff;
        }

        #urls {
            width: 100%;
            min-height: 120px;
            padding: 1rem;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            margin: 1rem 0;
            resize: vertical;
            font-family: monospace;
        }

        .type-selector {
            display: flex;
            gap: 1rem;
            margin: 1.5rem 0;
        }

        .type-btn {
            flex: 1;
            padding: 1rem;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            background: white;
            cursor: pointer;
            transition: 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .type-btn.active {
            border-color: var(--primary);
            background: #eff6ff;
        }

        button[type="submit"] {
            width: 100%;
            padding: 1rem;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 8px;
            font-weight: 500;
            cursor: pointer;
            transition: 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        button[type="submit"]:hover {
            background: var(--secondary);
            transform: translateY(-1px);
        }

        button[type="submit"]:active {
            transform: translateY(0);
        }

        #progress-container {
            margin-top: 2rem;
            background: #e2e8f0;
            border-radius: 8px;
            overflow: hidden;
            display: none;
        }

        #progress-bar {
            width: 0%;
            height: 8px;
            background: var(--primary);
            transition: width 0.3s ease;
        }

        #result {
            margin-top: 1.5rem;
        }

        .alert {
            padding: 1rem;
            border-radius: 8px;
            margin: 1rem 0;
        }

        .alert-success {
            background: #dcfce7;
            color: #166534;
        }

        .alert-error {
            background: #fee2e2;
            color: #991b1b;
        }

        .filename {
            font-family: monospace;
            background: #f1f5f9;
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1><i class="fas fa-cloud-download-alt"></i> Téléchargement NAS</h1>

        <div class="drop-zone" id="dropZone">
            <p>Glissez-déposez vos liens ici<br>ou <u>cliquez pour coller</u></p>
        </div>

        <form id="downloadForm">
            <textarea 
                id="urls" 
                placeholder="Collez un ou plusieurs liens (un par ligne)"
                required
            ></textarea>

            <div class="type-selector">
                <button type="button" class="type-btn active" data-type="film">
                    <i class="fas fa-film"></i> Film
                </button>
                <button type="button" class="type-btn" data-type="serie">
                    <i class="fas fa-tv"></i> Série
                </button>
            </div>

            <div id="progress-container">
                <div id="progress-bar"></div>
            </div>

            <button type="submit">
                <i class="fas fa-rocket"></i> Lancer le téléchargement
            </button>
        </form>

        <div id="result"></div>
    </div>

    <script>
        // Gestion du drag & drop
        const dropZone = document.getElementById('dropZone');
        const urlsTextarea = document.getElementById('urls');

        dropZone.addEventListener('click', async () => {
            try {
                const text = await navigator.clipboard.readText();
                urlsTextarea.value = text;
            } catch (error) {
                urlsTextarea.placeholder = 'Coller manuellement (Ctrl+V)';
            }
        });

        // Gestion du type média
        const typeBtns = document.querySelectorAll('.type-btn');
        let currentType = 'film';

        typeBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                typeBtns.forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
                currentType = btn.dataset.type;
            });
        });

        // Soumission du formulaire
        document.getElementById('downloadForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const urls = urlsTextarea.value.split('\n').filter(url => url.trim());
            
            if(urls.length === 0) {
                showAlert('Veuillez entrer au moins une URL', 'error');
                return;
            }

            const progressContainer = document.getElementById('progress-container');
            const progressBar = document.getElementById('progress-bar');
            progressContainer.style.display = 'block';
            
            for(const [index, url] of urls.entries()) {
                try {
                    const response = await fetch('download.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `url=${encodeURIComponent(url)}&type=${currentType}`
                    });

                    const data = await response.json();
                    
                    // Mise à jour de la barre de progression
                    const progress = Math.round((index + 1) / urls.length * 100);
                    progressBar.style.width = `${progress}%`;

                    if(data.status === 'success') {
                        showAlert(`
                            <i class="fas fa-check-circle"></i> 
                            Téléchargement réussi : 
                            <span class="filename">${data.path.split('/').pop()}</span>
                        `, 'success');
                    } else {
                        throw new Error(data.message);
                    }
                } catch (error) {
                    showAlert(`
                        <i class="fas fa-exclamation-circle"></i> 
                        Erreur : ${error.message}
                    `, 'error');
                }
            }

            // Réinitialisation
            progressBar.style.width = '0%';
            progressContainer.style.display = 'none';
            urlsTextarea.value = '';
        });

        function showAlert(message, type) {
            const alertDiv = document.createElement('div');
            alertDiv.className = `alert alert-${type}`;
            alertDiv.innerHTML = message;
            document.getElementById('result').appendChild(alertDiv);

            // Auto-dismiss after 5s
            setTimeout(() => {
                alertDiv.remove();
            }, 5000);
        }
    </script>
</body>
</html>
