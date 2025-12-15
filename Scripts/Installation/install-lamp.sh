#!/bin/bash
export PATH=$PATH:/usr/sbin

################################################################################
#                                                                              #
#         Script d'installation automatisé LAMP Stack sur Debian 13           #
#                                                                              #
#  Ce script installe :                                                       #
#  - Linux (Debian 13)                                                        #
#  - Apache (serveur web)                                                     #
#  - MariaDB (base de données)                                                #
#  - PHP (traitement du contenu dynamique)                                    #
#                                                                              #
#  Basé sur : https://wiki.crowncloud.net/?How_to_install_LAMP_stack_on_debian_13
#                                                                              #
################################################################################

set -e  # Quitter en cas d'erreur

# Couleurs pour l'affichage
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # Pas de couleur

# Variables
DB_ROOT_PASSWORD=""
DB_NAME="exampledb"
DB_USER="exampleuser"
DB_USER_PASSWORD=""

################################################################################
# Fonctions utilitaires
################################################################################

print_header() {
    echo -e "\n${BLUE}╔════════════════════════════════════════════════════════════╗${NC}"
    echo -e "${BLUE}║ $1${NC}"
    echo -e "${BLUE}╚════════════════════════════════════════════════════════════╝${NC}\n"
}

print_step() {
    echo -e "\n${YELLOW}➜ $1${NC}"
}

print_success() {
    echo -e "${GREEN}✓ $1${NC}"
}

print_error() {
    echo -e "${RED}✗ $1${NC}"
}

check_root() {
    if [[ $EUID -ne 0 ]]; then
        print_error "Ce script doit être exécuté en tant que root"
        exit 1
    fi
}

wait_for_key() {
    echo -e "\n${YELLOW}Appuyez sur Entrée pour continuer...${NC}"
    read -r
}

################################################################################
# Fonction interactive pour obtenir les mots de passe
################################################################################

get_passwords() {
    print_header "Configuration des mots de passe"
    
    echo -e "Veuillez définir les mots de passe pour MariaDB.\n"
    
    # Mot de passe root MariaDB
    while [[ -z "$DB_ROOT_PASSWORD" ]]; do
        read -sp "Mot de passe root MariaDB : " DB_ROOT_PASSWORD
        echo
        if [[ -z "$DB_ROOT_PASSWORD" ]]; then
            print_error "Le mot de passe ne peut pas être vide"
        fi
    done
    
    # Confirmation du mot de passe root
    local confirm_root_password=""
    read -sp "Confirmer le mot de passe root : " confirm_root_password
    echo
    
    if [[ "$DB_ROOT_PASSWORD" != "$confirm_root_password" ]]; then
        print_error "Les mots de passe ne correspondent pas"
        DB_ROOT_PASSWORD=""
        get_passwords
        return
    fi
    
    # Mot de passe utilisateur de base de données
    while [[ -z "$DB_USER_PASSWORD" ]]; do
        read -sp "Mot de passe pour l'utilisateur $DB_USER : " DB_USER_PASSWORD
        echo
        if [[ -z "$DB_USER_PASSWORD" ]]; then
            print_error "Le mot de passe ne peut pas être vide"
        fi
    done
    
    # Confirmation du mot de passe utilisateur
    local confirm_user_password=""
    read -sp "Confirmer le mot de passe utilisateur : " confirm_user_password
    echo
    
    if [[ "$DB_USER_PASSWORD" != "$DB_USER_PASSWORD" ]]; then
        print_error "Les mots de passe ne correspondent pas"
        DB_USER_PASSWORD=""
        get_passwords
        return
    fi
    
    print_success "Mots de passe définis avec succès"
}

################################################################################
# Étape 1 : Mise à jour du système
################################################################################

update_system() {
    print_header "Étape 1 : Mise à jour du système Debian 13"
    print_step "Mise à jour des listes de paquets et du système..."
    
    apt update && apt -y upgrade
    
    print_success "Système mis à jour avec succès"
}

################################################################################
# Étape 2 : Installation de MariaDB
################################################################################

install_mariadb() {
    print_header "Étape 2 : Installation de MariaDB Database Server"
    
    print_step "Installation de MariaDB Server et Client..."
    apt install -y mariadb-server mariadb-client
    print_success "MariaDB installé"
    
    print_step "Vérification du statut de MariaDB..."
    if systemctl status mariadb | grep -q "active (running)"; then
        print_success "MariaDB est en cours d'exécution"
    else
        print_error "MariaDB n'est pas actif"
        return 1
    fi
    
    print_step "Sécurisation de la base de données MariaDB..."
    
    # Changement du mot de passe root et suppression des utilisateurs anonymes
    mysql -u root << EOF
ALTER USER 'root'@'localhost' IDENTIFIED BY '$DB_ROOT_PASSWORD';
DELETE FROM mysql.user WHERE User='';
DELETE FROM mysql.user WHERE User='root' AND Host NOT IN ('localhost', '127.0.0.1', '::1');
DROP DATABASE IF EXISTS test;
DELETE FROM mysql.db WHERE Db='test' OR Db='test\\_%';
FLUSH PRIVILEGES;
EOF
    
    print_success "Base de données sécurisée"
    
    print_step "Création de la base de données et de l'utilisateur..."
    
    # Création de la base de données et de l'utilisateur
    mysql -u root -p"$DB_ROOT_PASSWORD" << EOF
CREATE DATABASE $DB_NAME;
CREATE USER '$DB_USER'@'localhost' IDENTIFIED BY '$DB_USER_PASSWORD';
GRANT ALL PRIVILEGES ON $DB_NAME.* TO '$DB_USER'@'localhost';
FLUSH PRIVILEGES;
EOF
    
    print_success "Base de données '$DB_NAME' et utilisateur '$DB_USER' créés"
}

################################################################################
# Étape 3 : Installation d'Apache
################################################################################

install_apache() {
    print_header "Étape 3 : Installation d'Apache Web Server"
    
    print_step "Installation d'Apache et des utilitaires..."
    apt install -y apache2 apache2-utils
    print_success "Apache installé"
    
    print_step "Vérification de la version d'Apache..."
    apache2 -v | grep "Server version"
    
    print_step "Vérification du statut d'Apache..."
    if systemctl status apache2 | grep -q "active (running)"; then
        print_success "Apache est en cours d'exécution"
    else
        print_error "Apache n'est pas actif"
        return 1
    fi
}

################################################################################
# Étape 4 : Configuration du pare-feu UFW
################################################################################

configure_firewall() {
    print_header "Étape 4 : Configuration du pare-feu UFW"
    
    print_step "Installation d'UFW..."
    apt install -y ufw
    print_success "UFW installé"
    
    print_step "Configuration des règles de pare-feu..."
    ufw allow http
    ufw allow https
    ufw allow ssh
    print_success "Règles HTTP, HTTPS et SSH autorisées"
    
    print_step "Activation du pare-feu..."
    echo "y" | ufw enable
    ufw reload
    print_success "Pare-feu activé et rechargé"
}

################################################################################
# Étape 5 : Configuration d'Apache
################################################################################

configure_apache() {
    print_header "Étape 5 : Configuration d'Apache"
    
    print_step "Rechargement et activation d'Apache..."
    systemctl reload apache2
    systemctl enable apache2
    print_success "Apache rechargé et activé au démarrage"
}

################################################################################
# Étape 6 : Installation de PHP
################################################################################

install_php() {
    print_header "Étape 6 : Installation de PHP"
    
    print_step "Installation de PHP et ses modules..."
    apt install -y php libapache2-mod-php php-cli php-fpm php-json php-pdo \
                  php-mysql php-zip php-gd php-mbstring php-curl php-xml \
                  php-pear php-bcmath
    print_success "PHP et ses modules installés"
    
    print_step "Activation du module PHP pour Apache..."
    a2enmod php8.4
    systemctl restart apache2
    print_success "Module PHP8.4 activé et Apache redémarré"
    
    print_step "Vérification de la version de PHP..."
    php -v | head -n 1
}

################################################################################
# Étape 7 : Test de PHP
################################################################################

test_php() {
    print_header "Étape 7 : Test de PHP"
    
    print_step "Création du fichier de test PHP (info.php)..."
    echo "<?php phpinfo(); ?>" > /var/www/html/info.php
    print_success "Fichier info.php créé"
    
    echo -e "\n${YELLOW}Vous pouvez accéder à l'information PHP via :${NC}"
    echo -e "${GREEN}http://<votre_adresse_ip>/info.php${NC}\n"
    
    print_step "Suppression du fichier info.php pour des raisons de sécurité..."
    rm /var/www/html/info.php
    print_success "Fichier info.php supprimé"
}

################################################################################
# Affichage du résumé
################################################################################

print_summary() {
    print_header "Installation LAMP Stack terminée avec succès !"
    
    echo -e "${GREEN}Informations de connexion MariaDB :${NC}"
    echo "  Utilisateur root : root"
    echo "  Utilisateur application : $DB_USER"
    echo "  Base de données : $DB_NAME"
    echo ""
    
    echo -e "${GREEN}Services actifs :${NC}"
    echo "  - Apache 2.4"
    echo "  - MariaDB 11.x"
    echo "  - PHP 8.4"
    echo ""
    
    echo -e "${GREEN}Pare-feu UFW :${NC}"
    echo "  - HTTP (port 80) : Autorisé"
    echo "  - HTTPS (port 443) : Autorisé"
    echo "  - SSH (port 22) : Autorisé"
    echo ""
    
    echo -e "${YELLOW}Prochaines étapes recommandées :${NC}"
    echo "  1. Configurer votre domaine"
    echo "  2. Installer un certificat SSL (Let's Encrypt)"
    echo "  3. Configurer les fichiers VirtualHost d'Apache"
    echo "  4. Optimiser les configurations PHP et MariaDB"
    echo ""
}

################################################################################
# Gestion des erreurs
################################################################################

handle_error() {
    print_error "Une erreur s'est produite à l'étape : $1"
    echo -e "${RED}Veuillez consulter les messages d'erreur ci-dessus.${NC}"
    exit 1
}

################################################################################
# Fonction principale
################################################################################

main() {
    clear
    print_header "Script d'installation LAMP Stack - Debian 13"
    
    # Vérifier que le script est exécuté en tant que root
    check_root
    
    # Obtenir les mots de passe
    get_passwords
    
    # Exécuter les étapes d'installation
    update_system || handle_error "Mise à jour du système"
    wait_for_key
    
    install_mariadb || handle_error "Installation MariaDB"
    wait_for_key
    
    install_apache || handle_error "Installation Apache"
    wait_for_key
    
    configure_firewall || handle_error "Configuration du pare-feu"
    wait_for_key
    
    configure_apache || handle_error "Configuration d'Apache"
    wait_for_key
    
    install_php || handle_error "Installation PHP"
    wait_for_key
    
    test_php || handle_error "Test PHP"
    wait_for_key
    
    # Afficher le résumé
    print_summary
}

# Exécuter la fonction principale
main
