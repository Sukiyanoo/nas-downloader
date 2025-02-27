<div align="center">

# 🖥️ Système de Téléchargement Automatisé pour NAS

*Interface web PHP pour organiser automatiquement des médias sur NAS avec intégration Jellyfin*  
![PHP](https://img.shields.io/badge/PHP-8%2B-777BB4?logo=php) 
![Apache](https://img.shields.io/badge/Apache-2.4-D22128?logo=apache) 

</div>

## 🎯 Fonctionnalités
<table>
  <tr>
    <td width="33%" valign="top">
      <h3>📦 Organisation</h3>
      <ul>
        <li>Détection automatique Films/Séries</li>
        <li>Structure de dossiers intelligente</li>
        <li>Montage réseau NFS/SMB</li>
      </ul>
    </td>
    <td width="33%" valign="top">
      <h3>⚙️ Intégration</h3>
      <ul>
        <li>Rafraîchissement Jellyfin automatique</li>
        <li>Journalisation des opérations</li>
        <li>Gestion des erreurs détaillée</li>
      </ul>
    </td>
    <td width="33%" valign="top">
      <h3>🔒 Sécurité</h3>
      <ul>
        <li>Whitelist IP avec wildcards</li>
        <li>Validation des liens entrants</li>
        <li>Sanitisation des noms de fichiers</li>
      </ul>
    </td>
  </tr>
</table>

## 🛠️ Prérequis

✅ Serveur Debian 12 (VM/conteneur)

✅ Apache2 + PHP 8+

✅ Modules PHP : json, curl

✅ Wget + outils NFS/SMB

✅ Jellyfin avec API activée

## 🚀 Installation
<div align="center">
  <img src="https://upload.wikimedia.org/wikipedia/commons/3/35/Tux.svg" width="100" alt="Linux">
</div>

1. Configuration du serveur
sudo apt install apache2 php libapache2-mod-php php-curl wget nfs-common cifs-utils -y

2. Déploiement des fichiers
sudo cp {index.php,download.php,style.css} /var/www/html/

3. Configuration des permissions
sudo chown -R www-data:www-data /var/www/html/
sudo chmod -R 755 /chemin/montage/nas


## 🔧 Configuration
Dans download.php

definir ('ALLOWED_IPS', ['192.168.1.*']); // 🔐 IPs autorisées

definir ('JELLYFIN_TOKEN', 'your_token_here'); // 🔑 Token API

definir ('MEDIA_PATH', '/chemin/montage/nas'); // 📁 Chemin NAS


<div align="center">
  <h3>📜 Structure des fichiers</h3>
  <pre>
📦 /var/www/html
├── 📄 index.php    # Interface utilisateur
├── 📄 download.php # Core du système
└── 📄 style.css    # Styles CSS
  </pre>
</div>




<div align="center">
  <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQ4pDM-XXQyYGYYz02A4qgyS9HgtFGZbW3xag&s" width="150" alt="Jellyfin">
  <p>🔄 Rafraîchissement automatique après téléchargement</p>
</div>
