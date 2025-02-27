<?php
// Vérification de l'accès
$allowed_ips = ['*.*.*.*'];
$client_ip = $_SERVER['REMOTE_ADDR'];
if(!in_array_wildcard($client_ip, $allowed_ips)) {
    die("Accès non autorisé");
}

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $url = $_POST['url'];
    $type = $_POST['type'];
    
    try {
        // Extraction du nom de fichier
        $parsed_url = parse_url($url);
        $path = $parsed_url['path'];
        $filename = pathinfo($path, PATHINFO_FILENAME);
        $extension = pathinfo($path, PATHINFO_EXTENSION) ?: 'mp4';
        
        // Nettoyage du nom
        $clean_name = urldecode($filename);
        $clean_name = preg_replace('/[^\w\-\(\) ]/', '_', $clean_name);
        $clean_name = str_replace('.', '_', $clean_name);
        
        // Construction du chemin final
        if($type === 'film') {
            $target_dir = "/mnt/nas-films/";
            $final_path = $target_dir . $clean_name . '.' . $extension;
        } else {
            preg_match('/S(\d{1,2})E(\d{1,2})/i', $clean_name, $matches);
            $season = isset($matches[1]) ? sprintf('%02d', $matches[1]) : '01';
            $show_name = preg_replace('/\.?S\d{1,2}E\d{1,2}.*/i', '', $clean_name);
            $show_name = str_replace('.', '_', $show_name);
            
            $target_dir = "ton_partage{$show_name}/saison{$season}/";
            $final_path = $target_dir . $clean_name . '.' . $extension;
        }
        
        // Création des dossiers avec les bonnes permissions
        if(!is_dir($target_dir)) {
            if(!mkdir($target_dir, 0775, true)) {
                throw new Exception("Impossible de créer le dossier : {$target_dir}");
            }
        }
        
        // Téléchargement avec logs détaillés
        $wget_cmd = "wget -O '{$final_path}' '{$url}' 2>&1";
        exec($wget_cmd, $output, $return_code);
        
        if($return_code === 0) {
            $result = [
                'status' => 'success',
                'message' => 'Téléchargement réussi !',
                'path' => $final_path,
                'jellyfin_url' => "https://jellyfinserver.example/web/#/home.html"
            ];
        } else {
            throw new Exception("Erreur wget (Code: {$return_code}) : " . implode("\n", $output));
        }
        
    } catch (Exception $e) {
        $result = [
            'status' => 'error',
            'message' => $e->getMessage(),
            'debug' => [
                'original_url' => $url,
                'clean_name' => $clean_name,
                'target_dir' => $target_dir,
                'wget_cmd' => $wget_cmd
            ]
        ];
    }
    
    // Refresh Jellyfin
    exec("curl -X POST http:/jellyfinserver:8096/Library/Refresh -H 'X-MediaBrowser-Token: LE_TOKEN'");
    
    header('Content-Type: application/json');
    echo json_encode($result);
    exit;
}

// Fonction de vérification IP avec wildcards
function in_array_wildcard($needle, $haystack) {
    foreach($haystack as $pattern) {
        if(fnmatch($pattern, $needle)) return true;
    }
    return false;
}
