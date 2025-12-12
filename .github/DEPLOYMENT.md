# Guide de déploiement avec GitHub Actions

Ce guide explique comment configurer le déploiement automatique vers votre VPS via GitHub Actions.

## Prérequis

1. Un VPS avec Docker et Docker Compose installés
2. Un accès SSH au VPS
3. Un repository GitHub (public ou privé)

## Configuration des secrets GitHub

Allez dans **Settings > Secrets and variables > Actions** de votre repository GitHub et ajoutez les secrets suivants :

### Secrets requis

1. **`SSH_HOST`** : L'adresse IP ou le hostname de votre VPS
   - Exemple : `vps-xxxxxx5e.vps.ovh.net`

2. **`SSH_USER`** : L'utilisateur SSH pour se connecter au VPS
   - Exemple : `ubuntu`

3. **`SSH_PORT`** : Le port SSH (par défaut 22, mais peut être différent)
   - Exemple : `49159`

4. **`SSH_PRIVATE_KEY`** : La clé privée SSH pour l'authentification
   
   **Où générer la clé ?** Sur votre **machine locale** (pas sur le VPS).
   
   **Étapes détaillées :**
   
   a) **Sur votre machine locale**, générez une paire de clés SSH :
   ```bash
   ssh-keygen -t ed25519 -C "github-actions" -f ~/.ssh/github_actions_key
   ```
   (Vous pouvez laisser le passphrase vide en appuyant sur Entrée, ou en mettre une si vous préférez)
   
   b) **Copiez la clé PRIVÉE** dans les secrets GitHub :
   ```bash
   cat ~/.ssh/github_actions_key
   ```
   Copiez tout le contenu (de `-----BEGIN OPENSSH PRIVATE KEY-----` à `-----END OPENSSH PRIVATE KEY-----`) et collez-le dans le secret `SSH_PRIVATE_KEY` sur GitHub.
   
   c) **Ajoutez la clé PUBLIQUE sur le VPS** :
   ```bash
   # Option 1 : Depuis votre machine locale (recommandé)
   ssh-copy-id -i ~/.ssh/github_actions_key.pub -p 49159 ubuntu@vps-xxxxxx5e.vps.ovh.net
   
   # Option 2 : Copier manuellement
   cat ~/.ssh/github_actions_key.pub | ssh -p 49159 ubuntu@vps-xxxxxx5e.vps.ovh.net "mkdir -p ~/.ssh && chmod 700 ~/.ssh && cat >> ~/.ssh/authorized_keys && chmod 600 ~/.ssh/authorized_keys"
   ```
   
   **Important :** 
   - La clé **privée** (`github_actions_key`) → va dans les secrets GitHub
   - La clé **publique** (`github_actions_key.pub`) → va sur le VPS dans `~/.ssh/authorized_keys`

### Note importante

Le secret **`GITHUB_TOKEN`** est automatiquement fourni par GitHub Actions et ne nécessite pas de configuration manuelle. Il est utilisé pour :
- Push l'image Docker sur GHCR
- Se connecter à GHCR depuis le VPS

## Structure des fichiers sur le VPS

Le workflow va créer la structure suivante sur votre VPS :

```
~/recipes-library/
├── compose.yaml
└── compose.prod.yaml
```

## Variables d'environnement sur le VPS

Sur votre VPS, vous devez créer un fichier `.env` dans `~/recipes-library/` avec les variables nécessaires :

```bash
# Exemple de .env pour la production
APP_SECRET=votre_secret_app
CADDY_MERCURE_JWT_SECRET=votre_secret_mercure
SERVER_NAME=votre-domaine.com
HTTP_PORT=80
HTTPS_PORT=443
POSTGRES_VERSION=16
POSTGRES_DB=recipes_prod
POSTGRES_PASSWORD=votre_mot_de_passe_db
POSTGRES_USER=recipes_prod
MEILI_MASTER_KEY=votre_master_key_meilisearch
```

## Déclenchement du workflow

Le workflow se déclenche :
- **Manuellement** via l'onglet "Actions" de GitHub (bouton "Run workflow")

## Étapes du workflow

1. **Build** : Construction de l'image Docker avec le target `frankenphp_prod`
2. **Push** : Envoi de l'image sur GitHub Container Registry (ghcr.io)
3. **Deploy** :
   - Connexion SSH au VPS
   - Copie des fichiers docker-compose
   - Mise à jour de l'image dans compose.prod.yaml
   - Pull de la nouvelle image
   - Démarrage/redémarrage des conteneurs avec `docker compose up -d`

## Vérification du déploiement

Après le déploiement, vous pouvez vérifier l'état des conteneurs :

```bash
ssh -p <PORT> <USER>@<HOST>
cd ~/recipes-library
docker compose -f compose.yaml -f compose.prod.yaml ps
docker compose -f compose.yaml -f compose.prod.yaml logs -f
```

## Dépannage

### Erreur d'authentification SSH
- Vérifiez que la clé publique est bien dans `~/.ssh/authorized_keys` sur le VPS
- Vérifiez les permissions : `chmod 600 ~/.ssh/authorized_keys`

### Erreur de connexion à GHCR
- Vérifiez que le VPS peut accéder à internet
- Vérifiez que le token GitHub a les permissions nécessaires

### Erreur Docker Compose
- Vérifiez que Docker et Docker Compose sont installés sur le VPS
- Vérifiez que le fichier `.env` existe et contient toutes les variables nécessaires




