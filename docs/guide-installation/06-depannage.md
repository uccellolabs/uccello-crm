# 6. Dépannage (FAQ)

Les erreurs les plus fréquentes et comment les régler. Cherche ton message
d'erreur dans la liste (Ctrl+F est ton ami).

---

### « SQLSTATE… could not connect to server » / « Connection refused »

La base de données PostgreSQL n'est pas démarrée, ou les réglages `DB_*` sont
faux.

- **Vérifie que PostgreSQL tourne :**
  - Mac : `brew services start postgresql@16`
  - Linux : `sudo systemctl start postgresql`
- **Vérifie le `.env`** : `DB_HOST`, `DB_PORT` (5432), `DB_DATABASE`,
  `DB_USERNAME`, `DB_PASSWORD`.
- Après modif du `.env` : `php artisan config:clear`.

---

### « database "uccello_crm" does not exist »

Tu as oublié de créer la base.

```bash
createdb uccello_crm                 # Mac
sudo -u postgres createdb uccello_crm  # Linux
```

Puis relance `php artisan migrate --seed`.

---

### « No application encryption key has been specified »

La clé de sécurité n'a pas été générée.

```bash
php artisan key:generate
```

---

### Page blanche, ou « 500 Server Error »

1. Regarde le **journal d'erreurs** : `storage/logs/laravel.log` (les dernières
   lignes décrivent le problème).
2. En local, mets temporairement `APP_DEBUG=true` dans `.env` puis
   `php artisan config:clear` pour voir l'erreur détaillée à l'écran.
3. Souvent : un problème de **permissions** sur `storage/` (voir ci-dessous).

---

### « The stream or file storage/logs/laravel.log could not be opened »
### « Permission denied » sur `storage` ou `bootstrap/cache`

Le serveur n'a pas le droit d'écrire dans ces dossiers (typique en production).

```bash
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache
```

---

### Les styles/design ne s'affichent pas (page « toute moche »)

L'interface n'a pas été construite.

- **En local :** assure-toi que `composer run dev` (ou `npm run dev`) tourne dans
  un terminal ouvert.
- **En production :** lance `npm run build`. Les fichiers finaux doivent être dans
  `public/build`.

---

### « Vite manifest not found »

Tu es en production mais le build n'a pas été fait. Lance :

```bash
npm install && npm run build
```

---

### J'ai changé le `.env` mais rien ne change

Laravel garde la config en cache (surtout en production).

```bash
php artisan config:clear
# ou en production, pour tout rafraîchir :
php artisan optimize:clear
php artisan optimize
```

---

### La bulle de chat IA affiche « indisponible »

C'est normal si tu n'as pas fourni de clé d'API. Ajoute `OPENAI_API_KEY` (ou
bascule sur Anthropic) dans le `.env` — voir
[la page configuration](03-configuration.md) — puis `php artisan config:clear`.
Ce n'est pas une erreur bloquante : le reste du CRM fonctionne sans.

---

### Les invitations / e-mails ne partent pas

En local, `MAIL_MAILER=log` : les e-mails ne sont pas réellement envoyés, ils sont
écrits dans `storage/logs/laravel.log`. Pour de vrais envois, configure un service
SMTP (voir [la page configuration](03-configuration.md)).

---

### « 419 Page Expired » lors de la connexion

Problème de session/cookies. Vérifie que `APP_URL` correspond bien à l'adresse que
tu utilises dans le navigateur, puis `php artisan config:clear`. En production,
assure-toi d'être en HTTPS.

---

### Les tâches en arrière-plan ne s'exécutent pas (en production)

Le worker ne tourne pas. Vérifie Supervisor :

```bash
supervisorctl status
supervisorctl restart uccello-worker:*
```

(Voir [la page Hostinger, étape 9](04-hebergement-hostinger.md).)

---

## Toujours bloqué ?

1. **Lis le dernier message** dans `storage/logs/laravel.log` — il est souvent
   très explicite.
2. **Copie le message d'erreur exact** dans un moteur de recherche : la
   communauté Laravel est immense, la réponse existe quasi toujours.
3. Vérifie que tu as bien suivi **toutes** les étapes de
   [l'installation locale](02-installation-locale.md) dans l'ordre.

Retour au [sommaire du guide](../../README.md).
