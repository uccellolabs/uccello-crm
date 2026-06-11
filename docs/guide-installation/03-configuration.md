# 3. Le fichier de configuration `.env`

Le fichier `.env` (à la racine du projet) contient **tous les réglages** du CRM :
adresse du site, base de données, envoi d'e-mails, assistant IA… C'est un simple
fichier texte au format `CLÉ=valeur`.

> **Important :** ce fichier contient des secrets (mots de passe, clés). Il ne
> doit **jamais** être partagé publiquement ni envoyé sur un dépôt Git
> (il est d'ailleurs déjà ignoré par Git).

On passe en revue les réglages **les plus importants**. Tu peux laisser les
autres sur leurs valeurs par défaut.

---

## Réglages généraux de l'application

```ini
APP_NAME="Uccello CRM"     # le nom affiché un peu partout
APP_ENV=local              # "local" en test, "production" une fois en ligne
APP_KEY=base64:...         # généré par "php artisan key:generate" — n'y touche pas
APP_DEBUG=true             # true en local (affiche les erreurs détaillées)
APP_URL=http://localhost:8000
```

> **Une fois en ligne (en production)**, change impérativement :
> - `APP_ENV=production`
> - `APP_DEBUG=false` (sinon tes erreurs, parfois sensibles, s'afficheraient à tes
> visiteurs)
> - `APP_URL=https://ton-domaine.com`

---

## Base de données

C'est le réglage le plus important pour que l'app démarre.

```ini
DB_CONNECTION=pgsql        # on utilise PostgreSQL
DB_HOST=127.0.0.1          # l'adresse du serveur de base (local = 127.0.0.1)
DB_PORT=5432               # le port par défaut de PostgreSQL
DB_DATABASE=uccello_crm    # le nom de la base
DB_USERNAME=...            # l'utilisateur de la base
DB_PASSWORD=...            # son mot de passe (peut être vide en local sur Mac)
```

> Le fichier `.env.example` fourni utilise `sqlite` par défaut. Pour ce CRM, on
> **remplace** par les lignes `pgsql` ci-dessus.

---

## Envoi d'e-mails

Le CRM envoie des e-mails (invitations d'équipe, réinitialisation de mot de
passe…). En local, on n'envoie rien de réel : on se contente d'écrire les e-mails
dans un fichier de log.

```ini
MAIL_MAILER=log            # en local : les e-mails vont dans storage/logs
```

**En production**, branche un vrai service d'envoi. Le plus simple est un service
SMTP (ton hébergeur en fournit souvent un, ou utilise Brevo, Mailgun, Postmark…) :

```ini
MAIL_MAILER=smtp
MAIL_HOST=smtp.ton-service.com
MAIL_PORT=587
MAIL_USERNAME=ton-identifiant
MAIL_PASSWORD=ton-mot-de-passe
MAIL_FROM_ADDRESS="contact@ton-domaine.com"
MAIL_FROM_NAME="Uccello CRM"
```

---

## Assistant IA (optionnel)

Le CRM intègre un **assistant conversationnel** (la petite bulle de chat en bas à
droite) qui sait répondre à des questions sur tes données. Il a besoin d'une clé
d'API d'un fournisseur d'IA.

Par défaut, le fournisseur est **OpenAI** :

```ini
OPENAI_API_KEY=sk-...      # ta clé obtenue sur platform.openai.com
OPENAI_MODEL=gpt-4o
```

Tu préfères **Claude (Anthropic)** ? Change le fournisseur par défaut et fournis
sa clé :

```ini
AI_DEFAULT=anthropic
ANTHROPIC_API_KEY=sk-ant-...
ANTHROPIC_MODEL=claude-opus-4-8
```

> **Pas de clé ?** Aucun problème : tout le reste du CRM fonctionne
> normalement. Seule la bulle de chat affichera un message « indisponible ». Tu
> peux ajouter la clé plus tard.

---

## Réglages plus techniques (à laisser par défaut)

```ini
SESSION_DRIVER=database    # où sont stockées les sessions de connexion
QUEUE_CONNECTION=database  # où sont stockées les tâches en arrière-plan
CACHE_STORE=database       # où est stocké le cache
```

Ces trois réglages utilisent la base de données : c'est le choix le plus simple,
aucune installation supplémentaire (comme Redis) n'est nécessaire.

---

## Après avoir modifié le `.env`

À chaque modification du `.env`, vide le cache de configuration pour que les
changements soient pris en compte :

```bash
php artisan config:clear
```

Prêt à mettre le CRM en ligne ? Direction
**[l'hébergement Hostinger](04-hebergement-hostinger.md)**.
