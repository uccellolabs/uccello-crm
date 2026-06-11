# 2. Installation en local

« En local » veut dire : **sur ton propre ordinateur**, pour tester le CRM avant
de le mettre en ligne. C'est l'étape idéale pour découvrir l'outil sans risque.

> Tu dois avoir terminé [l'étape 1 (prérequis)](01-prerequis.md) avant de
> continuer.

---

## Étape 1 — Récupérer le code

Si tu as reçu le projet sous forme de dossier, place-toi dedans :

```bash
cd chemin/vers/uccello-crm
```

Si le projet est sur un dépôt Git :

```bash
git clone <adresse-du-depot> uccello-crm
cd uccello-crm
```

---

## Étape 2 — Installer les briques du projet

Deux commandes : une pour la partie PHP, une pour la partie interface.

```bash
composer install   # installe les dépendances PHP (peut prendre 1-2 min)
npm install        # installe les dépendances de l'interface (Vue, Tailwind…)
```

> La première fois, ça télécharge beaucoup de fichiers. C'est normal que ce
> soit un peu long.

---

## Étape 3 — Créer le fichier de configuration

Le CRM lit ses réglages dans un fichier nommé `.env`. On part du modèle fourni :

```bash
cp .env.example .env
php artisan key:generate
```

La deuxième commande génère une **clé de sécurité** unique (pour chiffrer les
sessions, les mots de passe, etc.). Tu la verras apparaître dans `.env` à la
ligne `APP_KEY=`.

On détaille tous les réglages du `.env` dans
[la page configuration](03-configuration.md). Pour l'instant, on va surtout régler
la base de données à l'étape suivante.

---

## Étape 4 — Créer la base de données PostgreSQL

Le CRM a besoin d'une base de données vide nommée `uccello_crm`. Crée-la :

```bash
# Sur Mac (Homebrew) : ton utilisateur Mac est admin de Postgres
createdb uccello_crm

# Sur Linux : on passe par l'utilisateur "postgres"
sudo -u postgres createdb uccello_crm
```

Puis ouvre le fichier `.env` avec ton éditeur de texte et règle la section base
de données ainsi :

```ini
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=uccello_crm
DB_USERNAME=ton_utilisateur   # sur Mac, mets ton nom d'utilisateur Mac
DB_PASSWORD=                  # vide sur Mac Homebrew ; ton mot de passe sinon
```

> **Comment connaître mon `DB_USERNAME` ?**
> - Sur **Mac (Homebrew)** : c'est le nom de ta session (commande `whoami`), et
> le mot de passe est généralement vide.
> - Sur **Linux** : tu peux créer un utilisateur dédié (voir la page Hostinger),
> ou utiliser `postgres` avec le mot de passe que tu lui as donné.
> - Sur **Windows** : l'utilisateur est `postgres` et le mot de passe est celui
> choisi à l'installation.

---

## Étape 5 — Remplir la base avec les tables et des données de démo

```bash
php artisan migrate:fresh --seed
```

Cette commande :
1. **crée toutes les tables** nécessaires (`migrate`) ;
2. **ajoute des données de démonstration** (`--seed`) : une équipe « Acme CRM »,
   des entreprises, contacts, affaires, tâches… pour que l'app ne soit pas vide.

> `migrate:fresh` **efface tout** et repart de zéro. Parfait pour un premier
> test, mais ne l'utilise jamais sur des données que tu veux garder. Pour une
> mise à jour normale, on utilise `php artisan migrate` (sans `:fresh`).

---

## Étape 6 — Lancer l'application

```bash
composer run dev
```

Cette commande démarre **tout en même temps** : le serveur PHP, le serveur de
l'interface (Vite) et la file de tâches. Laisse cette fenêtre ouverte tant que tu
utilises le CRM.

Ouvre ton navigateur sur :

 **http://localhost:8000**

Tu vas être redirigé directement vers la page de connexion. Connecte-toi avec le
compte de démo :

- **E-mail :** `demo@uccello.test`
- **Mot de passe :** `password`

Et voilà, tu es dans l'application !

---

## Pour arrêter / relancer

- **Arrêter :** reviens dans la fenêtre du terminal et appuie sur `Ctrl + C`.
- **Relancer plus tard :** il suffit de refaire `composer run dev` (pas besoin de
  refaire les étapes 1 à 5).

---

## En cas de souci

- « **could not connect to server** » → PostgreSQL n'est pas démarré. Voir
  [le dépannage](06-depannage.md).
- « **Database "uccello_crm" does not exist** » → tu as oublié l'étape 4.
- Page blanche ou erreur de style → vérifie que `composer run dev` tourne bien.

Pour comprendre chaque réglage, passe à
**[la configuration `.env`](03-configuration.md)**.
Quand tu seras prêt à mettre le CRM en ligne, va voir
**[l'hébergement Hostinger](04-hebergement-hostinger.md)**.
