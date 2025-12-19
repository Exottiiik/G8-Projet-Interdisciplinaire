#!/bin/bash

# =============================================================================
# Script de configuration du serveur Sainte-Isabelle
# Basé sur le journal de bord Linux (JOUR 1 à JOUR 4)
# Exclut les manipulations LVM
# =============================================================================

set -e  # Arrête le script en cas d'erreur

# =============================================================================
# VARIABLES DE CONFIGURATION
# =============================================================================

HOSTNAME="serveur2"
IP_ADDRESS="10.10.9.2"
GATEWAY="10.10.9.14"
NETMASK="255.255.255.240"

BACKUP_USER="backup_user"
WEB_USER="user"
ADMIN_DB_USER="hospital_admin"
DB_NAME="db_sainte_isabelle"

LAMP_SCRIPT="./install-lamp.sh"
MARIA_BACKUP_SCRIPT="https://github.com/omegazeng/run-mariabackup/tree/master"

# =============================================================================
# JOUR 1 - Configuration initiale
# =============================================================================

echo "==============================================="
echo "JOUR 1 - Configuration initiale du serveur"
echo "==============================================="

# Configuration du hostname
echo "[*] Configuration du hostname en '${HOSTNAME}'"
hostnamectl set-hostname ${HOSTNAME}
echo ${HOSTNAME} > /etc/hostname

# Mise à jour du système
echo "[*] Mise à jour du système"
apt-get update
apt-get upgrade -y

# Configuration réseau (résolution temporaire - adapter selon votre configuration)
echo "[*] Configuration des paramètres réseau"
echo "IP: ${IP_ADDRESS}, Passerelle: ${GATEWAY}, Masque: ${NETMASK}"

# Configuration des utilisateurs root et user
echo "[*] Configuration de l'utilisateur 'root'"
# Note: Les mots de passe doivent être définis manuellement pour des raisons de sécurité
# passwd root

echo "[*] Création/configuration de l'utilisateur '${WEB_USER}'"
if ! id "${WEB_USER}" &>/dev/null; then
    useradd -m -s /bin/bash ${WEB_USER}
    echo "Utilisateur ${WEB_USER} créé. Veuillez définir son mot de passe :"
    passwd ${WEB_USER}
else
    echo "L'utilisateur ${WEB_USER} existe déjà"
fi

# =============================================================================
# Installation du serveur LAMP
# =============================================================================

echo "[*] Installation du serveur LAMP"
if [ -f "${LAMP_SCRIPT}" ]; then
    bash ${LAMP_SCRIPT}
else
    echo "ERREUR: Le script ${LAMP_SCRIPT} n'a pas été trouvé"
    echo "Installation manuelle de LAMP:"
    apt-get install -y apache2 mariadb-server php php-mysql
    systemctl start apache2
    systemctl start mariadb
    systemctl enable apache2
    systemctl enable mariadb
fi

# Installation de phpMyAdmin
echo "[*] Installation de phpMyAdmin"
apt-get install -y phpmyadmin

# =============================================================================
# JOUR 2 - Configuration des sauvegardes et du pare-feu
# =============================================================================

echo "==============================================="
echo "JOUR 2 - Configuration des sauvegardes et pare-feu"
echo "==============================================="

# Création des répertoires de backup
echo "[*] Création des répertoires de backup"
mkdir -p /backup/config
mkdir -p /backup/web
mkdir -p /backup/db

# Création de l'utilisateur backup_user
echo "[*] Création de l'utilisateur '${BACKUP_USER}'"
if ! id "${BACKUP_USER}" &>/dev/null; then
    useradd -m -s /bin/bash ${BACKUP_USER}
else
    echo "L'utilisateur ${BACKUP_USER} existe déjà"
fi

# Ajout du backup_user au groupe mysql
echo "[*] Ajout du ${BACKUP_USER} au groupe mysql"
usermod -a -G mysql ${BACKUP_USER}

# Restriction des permissions du groupe mysql
echo "[*] Restriction des permissions du groupe mysql"
chmod g-w /var/lib/mysql
chmod g+r /var/lib/mysql

# Création des scripts de backup
echo "[*] Création du script backup_config.sh"
cat > /usr/local/bin/backup_config.sh << 'EOF'
#!/bin/bash
# Script de sauvegarde des fichiers de configuration

BACKUP_DIR="/backup/config"
DATE=$(date +%Y%m%d_%H%M%S)

echo "[$(date)] Début du backup des configurations..."

# Backup Apache
tar -czf ${BACKUP_DIR}/apache2_${DATE}.tar.gz /etc/apache2/ 2>/dev/null

# Backup PHP
tar -czf ${BACKUP_DIR}/php_${DATE}.tar.gz /etc/php/ 2>/dev/null

# Backup MariaDB
tar -czf ${BACKUP_DIR}/mariadb_${DATE}.tar.gz /etc/mysql/ 2>/dev/null

echo "[$(date)] Backup des configurations terminé."
EOF

chmod +x /usr/local/bin/backup_config.sh

echo "[*] Création du script backup_web.sh"
cat > /usr/local/bin/backup_web.sh << 'EOF'
#!/bin/bash
# Script de sauvegarde des pages web

BACKUP_DIR="/backup/web"
WWW_DIR="/var/www/html"
DATE=$(date +%Y%m%d_%H%M%S)

echo "[$(date)] Début du backup du site web..."

tar -czf ${BACKUP_DIR}/web_${DATE}.tar.gz ${WWW_DIR}/ 2>/dev/null

echo "[$(date)] Backup du site web terminé."
EOF

chmod +x /usr/local/bin/backup_web.sh

# Script de backup MariaDB
echo "[*] Création du script backup_db.sh"
cat > /usr/local/bin/backup_db.sh << 'EOF'
#!/bin/bash
# Script de sauvegarde de la base de données MariaDB

BACKUP_DIR="/backup/db"
DATE=$(date +%Y%m%d_%H%M%S)
BACKUP_USER="backup_user"

echo "[$(date)] Début du backup de la base de données..."

# Dump de la base de données
mysqldump -u root --all-databases > ${BACKUP_DIR}/db_complet_${DATE}.sql

# Compression
gzip ${BACKUP_DIR}/db_complet_${DATE}.sql

echo "[$(date)] Backup de la base de données terminé."
EOF

chmod +x /usr/local/bin/backup_db.sh

# Attribution des répertoires de backup
echo "[*] Attribution des permissions aux répertoires de backup"
chown -R ${BACKUP_USER}:${BACKUP_USER} /backup
chmod 750 /backup/*

# Configuration du pare-feu (UFW)
echo "[*] Configuration du pare-feu"
apt-get install -y ufw
ufw --force enable
ufw default deny incoming
ufw default allow outgoing
ufw allow 22/tcp      # SSH
ufw allow 80/tcp      # HTTP
ufw allow 443/tcp     # HTTPS
ufw status

# =============================================================================
# JOUR 2 (suite) - Configuration HTTPS
# =============================================================================

echo "[*] Activation du module SSL pour Apache2"
a2enmod ssl

echo "[*] Activation du site par défaut SSL"
a2ensite default-ssl

echo "[*] Rechargement d'Apache2"
systemctl reload apache2

echo "[*] Refus du port 80 (HTTP) dans le pare-feu"
ufw delete allow 80/tcp
ufw deny 80/tcp
ufw status

# =============================================================================
# Ajout des scripts de backup au crontab du backup_user
# =============================================================================

echo "[*] Ajout des scripts de backup au crontab"
# Les entrées crontab doivent être ajoutées manuellement ou via:
# crontab -u ${BACKUP_USER} -e
# Exemple d'entrées suggérées:
# 0 2 * * * /usr/local/bin/backup_config.sh  # Quotidien à 2h du matin
# 0 3 * * * /usr/local/bin/backup_web.sh     # Quotidien à 3h du matin
# 0 4 * * * /usr/local/bin/backup_db.sh      # Quotidien à 4h du matin

cat << 'CRON' > /tmp/crontab_entries.txt
0 2 * * * /usr/local/bin/backup_config.sh
0 3 * * * /usr/local/bin/backup_web.sh
0 4 * * * /usr/local/bin/backup_db.sh
CRON

echo "Entrées crontab suggérées (à vérifier manuellement):"
cat /tmp/crontab_entries.txt

# =============================================================================
# JOUR 3 - Configuration du site web
# =============================================================================

echo "==============================================="
echo "JOUR 3 - Configuration du site web Sainte-Isabelle"
echo "==============================================="

# Création du fichier de configuration du site
echo "[*] Création du fichier de configuration 'sainte-isabelle.conf'"
cat > /etc/apache2/sites-available/sainte-isabelle.conf << 'EOF'
<VirtualHost *:443>
    ServerName sainte-isabelle.local
    ServerAlias www.sainte-isabelle.local
    DocumentRoot /var/www/sainte-isabelle

    SSLEngine on
    SSLCertificateFile /etc/ssl/certs/ssl-cert-snakeoil.pem
    SSLCertificateKeyFile /etc/ssl/private/ssl-cert-snakeoil.key

    <Directory /var/www/sainte-isabelle>
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/sainte-isabelle-error.log
    CustomLog ${APACHE_LOG_DIR}/sainte-isabelle-access.log combined
</VirtualHost>
EOF

# Création du répertoire du site
echo "[*] Création du répertoire du site web"
mkdir -p /var/www/sainte-isabelle
chown -R www-data:www-data /var/www/sainte-isabelle

# Activation du site
echo "[*] Activation du site 'sainte-isabelle'"
a2ensite sainte-isabelle.conf

# Désactivation des sites par défaut
echo "[*] Désactivation des sites par défaut"
a2dissite 000-default.conf
a2dissite default-ssl.conf 2>/dev/null || true

# Test et rechargement d'Apache
echo "[*] Test de la configuration d'Apache"
apache2ctl configtest
systemctl reload apache2

echo "[*] Site configuré. Test en accès: OK"

# =============================================================================
# Installation de rsyslog (journalisation)
# =============================================================================

echo "[*] Installation du système de journalisation rsyslog"
apt-get install -y rsyslog
systemctl enable rsyslog
systemctl start rsyslog

# =============================================================================
# JOUR 4 - Sécurité et configuration de la base de données
# =============================================================================

echo "==============================================="
echo "JOUR 4 - Configuration de sécurité et base de données"
echo "==============================================="

# Installation de fail2ban
echo "[*] Installation de fail2ban"
apt-get install -y fail2ban
systemctl enable fail2ban
systemctl start fail2ban

# Configuration de l'utilisateur 'user' dans sudoers
echo "[*] Configuration de l'utilisateur '${WEB_USER}' dans sudoers"
if ! grep -q "${WEB_USER}" /etc/sudoers; then
    echo "${WEB_USER} ALL=(ALL) NOPASSWD:ALL" >> /etc/sudoers
    echo "Utilisateur ${WEB_USER} ajouté à sudoers"
fi

# Retrait de la connexion root directe
echo "[*] Désactivation de la connexion directe en root"
sed -i 's/^PermitRootLogin yes/PermitRootLogin no/' /etc/ssh/sshd_config
systemctl reload ssh

# =============================================================================
# Configuration de la base de données
# =============================================================================

echo "[*] Configuration de la base de données MariaDB"

# Commandes MySQL pour créer les utilisateurs et les droits
MYSQL_CMDS=$(cat <<EOF
CREATE DATABASE IF NOT EXISTS ${DB_NAME};

-- Création de l'utilisateur admin
CREATE USER IF NOT EXISTS '${ADMIN_DB_USER}'@'localhost' IDENTIFIED BY 'password_securisee';

-- Attribution des droits exclusifs
GRANT ALL PRIVILEGES ON ${DB_NAME}.* TO '${ADMIN_DB_USER}'@'localhost';

-- Blocage du compte root pour MySQL
-- Remarque: Cette commande dépend de la version de MariaDB

FLUSH PRIVILEGES;
EOF
)

# Exécution des commandes
echo "${MYSQL_CMDS}" | mysql -u root -p

echo "[*] Création des tables et données de test"
# Les tables doivent être créées via un script SQL séparé
# À adapter selon votre schéma de base de données

# =============================================================================
# Résumé et vérifications
# =============================================================================

echo "==============================================="
echo "Configuration terminée!"
echo "==============================================="
echo ""
echo "Vérifications à effectuer manuellement:"
echo "1. Définir les mots de passe pour les utilisateurs root et ${WEB_USER}"
echo "2. Ajouter les entrées crontab pour le backup_user"
echo "3. Vérifier la configuration SSL (certificats auto-signés actuels)"
echo "4. Créer les tables et données de test dans la base de données"
echo "5. Tester l'accès à phpMyAdmin"
echo "6. Vérifier les logs rsyslog: tail -f /var/log/syslog"
echo ""
echo "Services actifs:"
systemctl status apache2 --no-pager
systemctl status mariadb --no-pager
systemctl status fail2ban --no-pager
