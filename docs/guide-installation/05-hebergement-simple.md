# 5. Héberger le CRM sans se prendre la tête

La [page Hostinger](04-hebergement-hostinger.md) t'apprend à tout configurer
toi-même : c'est puissant et économique, mais ça fait beaucoup d'étapes (serveur,
Nginx, certificat, worker…).

Si tu préfères **le moins d'efforts possible**, il existe des plateformes qui
s'occupent de tout ça à ta place. Tu connectes ton code, tu remplis quelques
réglages, et c'est en ligne. On en détaille deux :

1. **Laravel Cloud** — l'option officielle, la plus simple.
2. **Railway** — une autre option « clic-clic » très accessible.

> Le principe commun : ces plateformes installent PHP, créent la base
> PostgreSQL, gèrent le HTTPS et les tâches de fond **automatiquement**. Tu n'ouvres
> jamais de terminal SSH.

---

## Option 1 — Laravel Cloud (recommandée)

[cloud.laravel.com](https://cloud.laravel.com) — c'est la plateforme **officielle**
créée par l'équipe de Laravel, justement pour héberger des applis comme ce CRM.
Tout est pensé pour Laravel : base de données, tâches de fond, mises à jour… rien
à configurer à la main.

### Ce qu'il te faut avant de commencer
- Ton code sur un dépôt **GitHub** (Laravel Cloud se connecte à GitHub).
- Un compte sur [cloud.laravel.com](https://cloud.laravel.com) (inscription
  gratuite, tu paies ensuite à l'usage).

### Étape 1 — Connecter ton dépôt
1. Crée un compte sur Laravel Cloud et connecte ton compte **GitHub**.
2. Clique sur **Create Application** (ou « Nouveau projet »).
3. Choisis le **dépôt** du CRM et la **branche** à déployer (souvent `main`).

### Étape 2 — Ajouter une base de données PostgreSQL
1. Dans ton application, ouvre l'onglet **Database**.
2. Crée une base **PostgreSQL** (Laravel Cloud propose ça en un clic).
3. Bonne nouvelle : Laravel Cloud **remplit automatiquement** les variables
   `DB_CONNECTION`, `DB_HOST`, `DB_DATABASE`, etc. Tu n'as rien à recopier.

### Étape 3 — Renseigner les variables d'environnement
C'est l'équivalent de ton fichier `.env`, mais saisi dans une interface. Ouvre
l'onglet **Environment** et vérifie / ajoute :

```ini
APP_NAME="Uccello CRM"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://ton-app.laravel.cloud   # l'adresse fournie par la plateforme

# Les DB_* sont déjà remplies par l'étape 2

# E-mails (voir page 3) — un service SMTP
MAIL_MAILER=smtp
MAIL_HOST=...
MAIL_USERNAME=...
MAIL_PASSWORD=...

# Assistant IA (optionnel)
OPENAI_API_KEY=sk-...
```

> `APP_KEY` est généré automatiquement par la plateforme. Pas besoin d'y toucher.

### Étape 4 — Indiquer comment construire l'appli
Laravel Cloud doit savoir **construire l'interface** (la partie Vue/Tailwind).
Dans les réglages de **build / déploiement**, assure-toi que ces commandes sont
présentes (elles le sont souvent par défaut pour un projet Laravel) :

```bash
composer install --no-dev --optimize-autoloader
npm install
npm run build
```

### Étape 5 — Lancer les migrations au déploiement
Pour créer les tables automatiquement à chaque mise en ligne, ajoute cette
commande dans la section **Deploy / Release commands** :

```bash
php artisan migrate --force
```

> Optionnel, **uniquement au tout premier déploiement**, si tu veux les données
> de démonstration : ajoute aussi `php artisan db:seed --force`, puis **retire-le**
> ensuite (sinon il se relancerait à chaque déploiement).

### Étape 6 — Les tâches de fond (worker)
Le CRM a besoin d'un « worker » pour les tâches différées (envoi d'e-mails…).
Sur Laravel Cloud, ouvre l'onglet **Workers** (ou « Background processes ») et
ajoute un worker avec la commande :

```bash
php artisan queue:work
```

C'est l'équivalent automatisé de ce qu'on faisait avec Supervisor sur Hostinger,
mais ici en deux clics.

### Étape 7 — Déployer
Clique sur **Deploy**. La plateforme installe tout, construit l'interface, lance
les migrations et met le site en ligne avec **HTTPS automatique**.

Ouvre l'adresse fournie (`https://ton-app.laravel.cloud`, ou ton domaine si tu en
as branché un dans l'onglet **Domains**). Tu arrives directement sur la page de
connexion.

### Mettre à jour plus tard
Encore plus simple : il suffit de **pousser ton code sur GitHub**. Laravel Cloud
détecte le changement et redéploie tout seul. Zéro commande à taper.

---

## Option 2 — Railway

[railway.app](https://railway.app) — une autre plateforme très accessible, sur le
même principe « connecte et déploie ».

1. Crée un compte et connecte **GitHub**.
2. **New Project → Deploy from GitHub repo**, choisis le dépôt du CRM.
3. Dans le projet, clique **New → Database → PostgreSQL**. Railway crée la base et
   expose ses identifiants comme variables.
4. Ouvre l'onglet **Variables** du service web et renseigne le `.env` :
   - les réglages `APP_*` (avec `APP_ENV=production`, `APP_DEBUG=false`) ;
   - les `DB_*` en les pointant vers la base PostgreSQL créée (Railway fournit les
     valeurs, tu les recopies ou utilises ses références `${{Postgres.*}}`) ;
   - les e-mails et la clé IA si besoin.
5. Dans les réglages de **build / start**, assure-toi d'avoir :
   - build : `composer install --no-dev --optimize-autoloader && npm install && npm run build`
   - release : `php artisan migrate --force`
6. Ajoute un second service pour le **worker** avec la commande
   `php artisan queue:work` (Railway permet plusieurs processus par projet).
7. **Deploy**. Railway fournit une URL en HTTPS ; tu peux brancher ton domaine
   ensuite.

> Railway facture à l'usage (avec un crédit d'essai). Pratique pour démarrer et
> tester, surveille juste ta consommation.

---

## Laquelle choisir ?

| Ton besoin | La meilleure option |
|------------|---------------------|
| Le moins d'efforts, fait pour Laravel | **Laravel Cloud** |
| Simple aussi, interface généraliste | **Railway** |
| Contrôle total + coût maîtrisé, prêt à mettre les mains dedans | **[VPS Hostinger](04-hebergement-hostinger.md)** |

---

## Le point commun à toutes les plateformes

Quelle que soit la solution, le CRM a **toujours** besoin des mêmes ingrédients.
Quand tu configures un hébergeur, retrouve simplement :

1. **PHP 8.3+** pour exécuter l'application.
2. Une étape de **build de l'interface** : `npm install && npm run build`.
3. Une base **PostgreSQL** + les réglages `DB_*`.
4. La commande d'initialisation : `php artisan migrate --force`.
5. Un **worker** : `php artisan queue:work` qui tourne en continu.
6. Les **variables d'environnement** = le contenu de ton `.env`.
7. Le **HTTPS** activé (automatique sur Laravel Cloud et Railway).

Sur Laravel Cloud et Railway, les points 1, 6 et 7 sont gérés pour toi : tu te
concentres sur le reste.

Un souci pendant l'installation ? Va voir **[le dépannage](06-depannage.md)**.
