# 4. Héberger le CRM chez Hostinger

Mettre le CRM « en ligne » = le faire tourner sur un serveur accessible 24h/24
depuis Internet, avec ta propre adresse (ex. `crm.ton-entreprise.com`).

Chez Hostinger, **deux formules** peuvent convenir. On t'explique laquelle
choisir, puis on déroule l'installation étape par étape.

---

## Quelle formule Hostinger choisir ?

| Formule | Convient ? | Pourquoi |
|---------|-----------|----------|
| **VPS** (serveur privé virtuel) | **Recommandé** | Tu as les pleins pouvoirs : PHP, Node, PostgreSQL, tâches en arrière-plan. C'est la solution propre et durable. |
| **Hébergement Web (mutualisé)** | Possible mais limité | PHP est dispo, mais **PostgreSQL n'est en général pas fourni** (souvent c'est MySQL) et tu n'as pas la main pour les tâches en arrière-plan. À éviter pour ce projet. |
| **Cloud Hosting** | Acceptable | Entre les deux. Vérifie que PostgreSQL est disponible. |

 **Le reste de cette page suppose un VPS Hostinger** (à partir de quelques euros
par mois), qui est la voie la plus fiable pour une appli Laravel + PostgreSQL.

> **Raccourci possible :** Hostinger propose des VPS avec un **panneau de
> gestion** et parfois des modèles « Laravel » préinstallés. Tu peux aussi
> installer l'outil **[Laravel Forge](https://forge.laravel.com)** ou
> **[Ploi](https://ploi.io)** qui automatisent 90 % des étapes ci-dessous en
> branchant ton VPS. Si tu débutes, c'est un vrai gain de temps. Sinon, suis le
> guide manuel ci-dessous.

---

## Étape 0 — Commander le VPS et s'y connecter

1. Sur le tableau de bord Hostinger, commande un **VPS** (choisis un modèle
   Ubuntu 24.04 « clean » ou un modèle avec panneau si tu préfères).
2. Hostinger te donne une **adresse IP** et un **mot de passe root**.
3. Connecte-toi en SSH depuis ton ordinateur :

   ```bash
   ssh root@TON_ADRESSE_IP
   ```

(Sur Windows, utilise le terminal intégré, ou l'app **PuTTY**, ou la console
   SSH du panneau Hostinger directement dans le navigateur.)

> Bonne pratique : crée un utilisateur non-root pour le quotidien. Pour rester
> simple, ce guide continue en root, mais garde ça en tête pour plus tard.

---

## Étape 1 — Installer les outils sur le serveur

Une fois connecté en SSH, installe PHP, Node, PostgreSQL, Nginx, etc. :

```bash
apt update && apt upgrade -y

# PHP 8.3 + extensions nécessaires
apt install -y php8.3-fpm php8.3-cli php8.3-pgsql php8.3-mbstring \
  php8.3-xml php8.3-curl php8.3-zip php8.3-bcmath

# Composer
curl -sS https://getcomposer.org/installer | php
mv composer.phar /usr/local/bin/composer

# Node.js 22
curl -fsSL https://deb.nodesource.com/setup_22.x | bash -
apt install -y nodejs

# PostgreSQL + serveur web Nginx + Git
apt install -y postgresql nginx git unzip
systemctl enable --now postgresql nginx
```

---

## Étape 2 — Créer la base de données

```bash
sudo -u postgres psql
```

Tu es maintenant dans la console PostgreSQL. Colle ces lignes (remplace
`un_mot_de_passe_solide` par un vrai mot de passe que tu notes de côté) :

```sql
CREATE DATABASE uccello_crm;
CREATE USER uccello WITH PASSWORD 'un_mot_de_passe_solide';
GRANT ALL PRIVILEGES ON DATABASE uccello_crm TO uccello;
\c uccello_crm
GRANT ALL ON SCHEMA public TO uccello;
\q
```

Le `\q` te ramène au terminal normal.

---

## Étape 3 — Déposer le code du CRM

On place le projet dans `/var/www/uccello-crm`.

```bash
cd /var/www
git clone <adresse-du-depot> uccello-crm   # ou envoie les fichiers par SFTP
cd uccello-crm
```

> **Pas de dépôt Git ?** Tu peux envoyer le dossier du projet via un logiciel
> SFTP comme **FileZilla** (héberge l'IP, l'utilisateur et le mot de passe du
> VPS), dans `/var/www/uccello-crm`.

---

## Étape 4 — Installer et configurer

```bash
# Dépendances PHP, en version optimisée pour la production
composer install --no-dev --optimize-autoloader

# Construire l'interface (génère les fichiers finaux dans public/build)
npm install
npm run build

# Configuration
cp .env.example .env
php artisan key:generate
```

Édite ensuite le `.env` (`nano .env`) avec les **bons réglages de production** :

```ini
APP_NAME="Uccello CRM"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://crm.ton-domaine.com

DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=uccello_crm
DB_USERNAME=uccello
DB_PASSWORD=un_mot_de_passe_solide

SESSION_DRIVER=database
QUEUE_CONNECTION=database
CACHE_STORE=database

MAIL_MAILER=smtp
# ... voir la page 3 pour le détail des e-mails
```

> Voir [la page configuration](03-configuration.md) pour le détail de chaque
> ligne (e-mails, assistant IA, etc.).

Crée les tables. Sur un serveur de production, on **n'utilise pas** `migrate:fresh`
(qui efface tout) : la première fois, on lance simplement les migrations, et on
peut ajouter les données de démo si on le souhaite.

```bash
php artisan migrate --force
# Optionnel : ajouter les données de démonstration (équipe Acme, exemples)
php artisan db:seed --force
```

> Le `--force` confirme qu'on accepte d'exécuter ces commandes en production
> (Laravel demande une confirmation sinon).

---

## Étape 5 — Régler les permissions des dossiers

Le serveur web (`www-data`) doit pouvoir écrire dans deux dossiers :

```bash
chown -R www-data:www-data /var/www/uccello-crm/storage /var/www/uccello-crm/bootstrap/cache
chmod -R 775 /var/www/uccello-crm/storage /var/www/uccello-crm/bootstrap/cache
```

Puis mets en cache la configuration et les routes (ça accélère le site) :

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

> **À chaque mise à jour** du code ou du `.env`, relance ces trois commandes
> `*:cache` (ou un `php artisan optimize:clear` puis `optimize`).

---

## Étape 6 — Configurer Nginx (le serveur web)

Crée le fichier de configuration du site :

```bash
nano /etc/nginx/sites-available/uccello-crm
```

Colle ceci (remplace `crm.ton-domaine.com` par ton vrai domaine) :

```nginx
server {
    listen 80;
    server_name crm.ton-domaine.com;
    root /var/www/uccello-crm/public;

    index index.php;
    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

> Le `root` pointe vers le **sous-dossier `public`**, pas la racine du projet.
> C'est essentiel : c'est le seul dossier qui doit être exposé sur Internet.

Active le site et redémarre Nginx :

```bash
ln -s /etc/nginx/sites-available/uccello-crm /etc/nginx/sites-enabled/
nginx -t            # vérifie qu'il n'y a pas d'erreur de syntaxe
systemctl reload nginx
```

---

## Étape 7 — Brancher ton nom de domaine

Dans la zone DNS de ton domaine (chez Hostinger ou ailleurs), crée un
enregistrement **A** :

| Type | Nom | Valeur |
|------|-----|--------|
| A | `crm` (ou `@` pour le domaine racine) | l'**adresse IP** de ton VPS |

Attends quelques minutes (parfois jusqu'à 1 h) que la propagation se fasse.

---

## Étape 8 — Activer le HTTPS (cadenas vert)

Indispensable pour un CRM (tu manipules des données clients). Le certificat est
**gratuit** avec Let's Encrypt :

```bash
apt install -y certbot python3-certbot-nginx
certbot --nginx -d crm.ton-domaine.com
```

Certbot configure tout seul le HTTPS dans Nginx et renouvelle le certificat
automatiquement. Une fois terminé, ton site est accessible en
`https://crm.ton-domaine.com`.

---

## Étape 9 — Les tâches en arrière-plan (file d'attente)

Le CRM utilise une « file d'attente » pour des actions différées (ex. envoi
d'e-mails). Il faut un petit programme qui tourne en permanence pour la traiter.
On utilise **Supervisor** :

```bash
apt install -y supervisor
nano /etc/supervisor/conf.d/uccello-worker.conf
```

Colle :

```ini
[program:uccello-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/uccello-crm/artisan queue:work --sleep=3 --tries=3
autostart=true
autorestart=true
user=www-data
numprocs=1
redirect_stderr=true
stdout_logfile=/var/www/uccello-crm/storage/logs/worker.log
```

Active-le :

```bash
supervisorctl reread
supervisorctl update
supervisorctl start uccello-worker:*
```

> Optionnel mais propre : ajoute aussi le **planificateur** Laravel au cron pour
> les tâches récurrentes. `crontab -e` puis :
> ```
> * * * * * cd /var/www/uccello-crm && php artisan schedule:run >> /dev/null 2>&1
> ```

---

## C'est en ligne !

Ouvre `https://crm.ton-domaine.com`. Tu arrives directement sur la page de
connexion (la page d'accueil redirige vers l'app). Connecte-toi avec le compte de
démo (`demo@uccello.test` / `password`) si tu as lancé le seed, ou crée ton
propre compte.

> **Première chose à faire en production :** crée ton vrai compte
> administrateur, puis supprime ou change le mot de passe du compte de démo.

---

## Mettre à jour le CRM plus tard

Quand une nouvelle version du code est disponible :

```bash
cd /var/www/uccello-crm
git pull                                   # récupère le nouveau code
composer install --no-dev --optimize-autoloader
npm install && npm run build
php artisan migrate --force                # applique les nouvelles tables
php artisan optimize:clear && php artisan optimize
supervisorctl restart uccello-worker:*     # recharge le worker
```

Tu trouves ça trop fastidieux à configurer ? Il existe des solutions
beaucoup plus simples : lis **[Héberger sans se prendre la tête](05-hebergement-simple.md)**
(Laravel Cloud, Railway). En cas de problème, va voir **[le dépannage](06-depannage.md)**.
