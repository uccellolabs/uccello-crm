## Cet outil est gratuit. Le construire pour ton métier, c'est mon travail.

Moi c'est Jonathan, je construis des outils sur mesure, boostés à l'IA,
pour faire gagner du temps aux entreprises.

Je ne suis pas as un consultant qui te dit d'aller sur ChatGPT.
Je suis plutôt celui qui te livre l'outil souverain qui le fait à ta place.

Ce CRM, je l'ai codé en quelques jours, avec l'IA et 21 ans de métier
derrière. C'est une démo de ce que je sais faire.

Si tu veux aller plus loin que la version offerte :
- le brancher sur tes outils (mails, agenda, compta, ta stack existant)
- une IA souveraine, hébergée en France, tes données restent chez toi
- des fonctions métier sur mesure, pensées pour ta boîte

On en parle 30 minutes : 👉 [Réserver un créneau](https://calendar.app.google/qLg5TxaxEcJdsw9T7)

Sinon, le code est à toi. Fais-en ce que tu veux.

### Voir la démo en vidéo

[![Démo d'Uccello CRM en vidéo](https://img.youtube.com/vi/UY--uJo_hio/maxresdefault.jpg)](https://youtu.be/UY--uJo_hio)

**[Regarder la démo sur YouTube](https://youtu.be/UY--uJo_hio)** : un tour rapide du CRM en action.

---

# Guide d'installation d'Uccello CRM

Bienvenue ! Ce guide explique **pas à pas** comment installer et mettre en ligne
Uccello CRM, **même si tu n'es pas développeur professionnel**. Il suffit d'être
un peu débrouillard, de savoir copier-coller des commandes dans un terminal et de
lire les messages d'erreur sans paniquer.

> **C'est quoi Uccello CRM ?**
> Un logiciel de gestion de la relation client (CRM) : entreprises, contacts,
> pipeline commercial (kanban des affaires), tâches, activités, champs
> personnalisés, et même un assistant IA intégré. Techniquement, c'est une
> application **Laravel 13** (PHP) avec une interface **Vue 3 / Inertia** et une
> base de données **PostgreSQL**.

---

## Sommaire du guide

Lis les pages dans l'ordre la première fois. Ensuite tu pourras revenir
directement à la section qui t'intéresse.

| #  | Page | Pour quoi faire |
|----|------|-----------------|
| 1  | [Prérequis](docs/guide-installation/01-prerequis.md) | Les outils à installer **avant** de commencer (PHP, Node, PostgreSQL…) |
| 2  | [Installation en local](docs/guide-installation/02-installation-locale.md) | Faire tourner le CRM sur **ton propre ordinateur** pour tester |
| 3  | [Le fichier de configuration `.env`](docs/guide-installation/03-configuration.md) | Comprendre et remplir les réglages (base de données, e-mails, IA…) |
| 4  | [Héberger chez Hostinger](docs/guide-installation/04-hebergement-hostinger.md) | Mettre le CRM en ligne sur un **VPS Hostinger** (contrôle total, coût maîtrisé) |
| 5  | [Héberger sans se prendre la tête](docs/guide-installation/05-hebergement-simple.md) | La voie **la plus simple** : Laravel Cloud (officiel) ou Railway, sans serveur à configurer |
| 6  | [Dépannage (FAQ)](docs/guide-installation/06-depannage.md) | Les erreurs fréquentes et comment les régler |

---

## La version ultra-rapide (pour les pressés)

Si tu as déjà PHP 8.3+, Composer, Node 20+ et PostgreSQL installés :

```bash
# 1. Récupérer les dépendances
composer install
npm install

# 2. Préparer la configuration
cp .env.example .env
php artisan key:generate

# 3. Créer la base puis la remplir avec des données de démo
php artisan migrate:fresh --seed

# 4. Lancer l'application
composer run dev
```

Puis ouvre **http://localhost:8000** dans ton navigateur.

Compte de démonstration :
- **E-mail :** `demo@uccello.test`
- **Mot de passe :** `password`

> La page d'accueil redirige **automatiquement** vers l'application
> (le tableau de bord si tu es connecté, sinon la page de connexion). Pas de
> page vitrine à cliquer : tu arrives directement dans le CRM.

Si une de ces étapes coince, pas de panique : tout est détaillé dans les pages
suivantes. Commence par [les prérequis](docs/guide-installation/01-prerequis.md).
