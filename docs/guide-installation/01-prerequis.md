# 1. Les prérequis

Avant d'installer le CRM, ton ordinateur (ou ton serveur) a besoin de quelques
outils. C'est comme avoir une perceuse et un tournevis avant de monter un meuble.

Voici la liste. Ne t'inquiète pas, on explique comment installer chacun juste en
dessous.

| Outil | Version minimum | À quoi ça sert |
|-------|-----------------|----------------|
| **PHP** | 8.3 | Le langage qui fait tourner le moteur du CRM (Laravel) |
| **Composer** | 2.x | Le « gestionnaire de paquets » de PHP (installe les briques du projet) |
| **Node.js** | 20 ou 22 | Pour construire l'interface visuelle (les pages que tu vois à l'écran) |
| **PostgreSQL** | 14+ | La base de données où sont rangées toutes tes données |
| **Git** | n'importe | Pour récupérer le code (optionnel si tu as déjà les fichiers) |

---

## Sur macOS (avec Homebrew)

[Homebrew](https://brew.sh) est le moyen le plus simple d'installer des outils
sur Mac. Si tu ne l'as pas, ouvre l'app **Terminal** et colle :

```bash
/bin/bash -c "$(curl -fsSL https://raw.githubusercontent.com/Homebrew/install/HEAD/install.sh)"
```

Ensuite, installe tout d'un coup :

```bash
brew install php composer node postgresql@16 git
brew services start postgresql@16   # démarre la base de données
```

---

## Sur Windows

Le plus simple sur Windows est d'utiliser **[Laravel Herd](https://herd.laravel.com)**
(gratuit). Il installe **PHP, Composer et Node** d'un seul coup, sans prise de tête.

1. Télécharge et installe Herd.
2. Installe **PostgreSQL** séparément depuis
   [postgresql.org/download/windows](https://www.postgresql.org/download/windows/)
   (note bien le mot de passe que tu choisis pour l'utilisateur `postgres`).
3. Installe **Git** depuis [git-scm.com](https://git-scm.com/download/win).

> Astuce avancée : tu peux aussi utiliser **WSL2** (un Linux dans Windows) et
> suivre les instructions Linux ci-dessous. C'est plus « pro » mais un peu plus
> technique.

---

## Sur Linux (Ubuntu / Debian)

```bash
sudo apt update
sudo apt install -y php8.3 php8.3-cli php8.3-pgsql php8.3-mbstring \
  php8.3-xml php8.3-curl php8.3-zip php8.3-bcmath unzip git curl

# Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# Node.js 22 (via NodeSource)
curl -fsSL https://deb.nodesource.com/setup_22.x | sudo -E bash -
sudo apt install -y nodejs

# PostgreSQL
sudo apt install -y postgresql postgresql-contrib
sudo systemctl enable --now postgresql
```

---

## Vérifier que tout est bien installé

Colle ces commandes une par une. Chacune doit afficher un **numéro de version**
(et pas une erreur « command not found ») :

```bash
php --version        # doit afficher 8.3 ou plus
composer --version   # doit afficher 2.x
node --version       # doit afficher v20 ou v22
psql --version       # doit afficher 14 ou plus
git --version
```

Si l'une d'elles renvoie une erreur, reprends son installation ci-dessus avant de
continuer.

> **Et les extensions PHP ?** Laravel a besoin de quelques extensions PHP
> (`pdo_pgsql`, `mbstring`, `xml`, `curl`, `zip`, `bcmath`). Sur Mac avec Homebrew
> et sur Herd, elles sont déjà incluses. Sur Linux, elles sont dans la commande
> `apt install` ci-dessus.

---

Une fois tous les outils installés, passe à l'étape suivante :
**[Installation en local](02-installation-locale.md)**.
