\# ISTAWAH API



API REST Laravel de gestion multi-utilisateurs avec authentification, rôles, permissions et gestion de projets.



\## 📚 Sommaire

\- \[Architecture](#architecture)

\- \[Prérequis](#prérequis)

\- \[Installation](#installation)

\- \[Lancement](#lancement)

\- \[Tests](#tests)

\- \[Endpoints principaux](#endpoints-principaux)

\- \[Choix techniques](#choix-techniques)

\- \[Limites connues](#limites-connues)

\- \[Améliorations envisageables](#améliorations-envisageables)



\## 🏗️ Architecture



\### Stack

\- \*\*PHP 8.3\*\* + \*\*Laravel 13\*\*

\- \*\*SQLite\*\* (dev) / \*\*MySQL 8\*\* (prod / Docker)

\- \*\*Laravel Sanctum\*\* (tokens API)

\- \*\*PHPUnit\*\* (tests)

\- \*\*Docker / Docker Compose\*\*



\### Structure du projet

```

app/

├── Http/

│   ├── Controllers/Api/       # AuthController, UserController, ProjectController

│   ├── Middleware/            # EnsureUserHasRole (RBAC middleware)

│   ├── Requests/              # FormRequests (validation)

│   │   ├── Auth/

│   │   ├── User/

│   │   └── Project/

│   └── Resources/             # UserResource, ProjectResource

├── Models/                    # User, Role, Permission, Project

├── Policies/                  # UserPolicy, ProjectPolicy

└── Services/                  # AuthService, UserService, ProjectService

database/

├── migrations/                # 6 migrations métier + 4 par défaut

├── factories/                 # UserFactory

└── seeders/                   # RolePermissionSeeder, AdminUserSeeder

routes/

└── api.php                    # 22 routes API

tests/

└── Feature/                   # AuthTest, UserTest, ProjectTest (13 tests)

docker/

└── nginx.conf

Dockerfile

docker-compose.yml

```



\### Flux d'une requête

```

Request HTTP

&#x20;   ↓

Middleware auth:sanctum         (vérifie le token)

&#x20;   ↓

Middleware role:ADMIN           (vérifie le rôle — si route sensible)

&#x20;   ↓

FormRequest::authorize()        (appelle Policy)

&#x20;   ↓

Policy (UserPolicy / ProjectPolicy)

&#x20;   ↓

Controller                      (orchestration)

&#x20;   ↓

Service                         (logique métier)

&#x20;   ↓

Model + DB

&#x20;   ↓

Resource (JSON)

```



\### Séparation des responsabilités

\- \*\*Controllers\*\* : orchestration HTTP uniquement

\- \*\*FormRequests\*\* : validation + autorisation (`authorize()`)

\- \*\*Policies\*\* : règles d'accès fines (RBAC)

\- \*\*Services\*\* : logique métier réutilisable

\- \*\*Middleware\*\* : garde-fou en amont des routes sensibles

\- \*\*Resources\*\* : formatage JSON (aucune donnée sensible ne fuit)



\## ✅ Prérequis



\### Option A — Avec Docker (recommandé)

\- \*\*Docker Desktop\*\* ≥ 4.x

\- \*\*Docker Compose\*\* v2 (inclus dans Docker Desktop)



\### Option B — En local

\- \*\*PHP\*\* ≥ 8.3 avec extensions `pdo\_sqlite`, `mbstring`, `openssl`

\- \*\*Composer\*\* ≥ 2.x

\- \*\*SQLite\*\* (dev) ou \*\*MySQL 8\*\* (prod)



\## 🚀 Installation



\### Avec Docker (recommandé)



```bash

git clone <votre-repo> istawah-api

cd istawah-api

cp .env.docker .env

docker compose up -d --build

docker compose exec app php artisan key:generate

docker compose exec app php artisan migrate --seed

```



L'API est disponible sur \*\*http://localhost:8000/api\*\*.



\### En local (Herd, Valet, ou artisan serve)



```bash

git clone <votre-repo> istawah-api

cd istawah-api

cp .env.example .env

composer install

php artisan key:generate

type nul > database\\database.sqlite    # Windows

\# touch database/database.sqlite        # macOS/Linux

php artisan migrate:fresh --seed

php artisan serve

```



\## 🎬 Lancement



\### Docker

```bash

docker compose up -d

```



\### Local

```bash

php artisan serve

```



\## 🧪 Tests



```bash

php artisan test

```



\*\*Couverture\*\* (13 tests, 25 assertions) :

\- ✅ Inscription (register)

\- ✅ Connexion réussie (login success)

\- ✅ Connexion échouée (bad password)

\- ✅ Création d'utilisateur (admin)

\- ✅ Contrôle de rôle (user ne peut pas créer)

\- ✅ Accès ADMIN autorisé

\- ✅ Accès USER refusé sur route ADMIN

\- ✅ Anti-élévation de privilèges

\- ✅ Création de projet (manager)

\- ✅ Protection d'un projet (autre user → 403)

\- ✅ Validation des données (projet sans nom)

\- ✅ Affectation d'un membre à un projet



\## 📡 Endpoints principaux



\### 🔐 Authentification



| Méthode | URL | Auth | Description |

|---|---|---|---|

| POST | `/api/auth/register` | Non | Inscription (rôle USER par défaut) |

| POST | `/api/auth/login` | Non | Connexion → token |

| POST | `/api/auth/forgot-password` | Non | Demande de reset |

| POST | `/api/auth/reset-password` | Non | Reset du mot de passe |

| GET | `/api/auth/me` | Oui | Profil connecté |

| POST | `/api/auth/logout` | Oui | Révoque le token |



\### 👥 Utilisateurs



| Méthode | URL | Rôle requis |

|---|---|---|

| GET | `/api/users` | ADMIN |

| POST | `/api/users` | ADMIN |

| GET | `/api/users/{id}` | ADMIN ou self |

| PUT | `/api/users/{id}` | ADMIN ou self (sans `role`) |

| DELETE | `/api/users/{id}` | ADMIN |



\### 📋 Projets



| Méthode | URL | Rôle requis |

|---|---|---|

| GET | `/api/projects` | Membre ou créateur |

| POST | `/api/projects` | MANAGER / ADMIN |

| GET | `/api/projects/{id}` | Membre ou créateur |

| PUT | `/api/projects/{id}` | Créateur / MANAGER sur projet |

| PATCH | `/api/projects/{id}/archive` | Créateur / ADMIN |

| GET | `/api/projects/{id}/members` | Membre |

| POST | `/api/projects/{id}/members` | OWNER / MANAGER / ADMIN |

| DELETE | `/api/projects/{id}/members/{user}` | OWNER / MANAGER / ADMIN |



\### 🔒 Route ADMIN (exemple explicite)



| Méthode | URL | Rôle requis |

|---|---|---|

| GET | `/api/admin/ping` | ADMIN |



\## 🧠 Choix techniques



\### Sanctum vs Passport

\- \*\*Sanctum\*\* : léger, tokens simples, adapté SPA/mobile.

\- \*\*Passport\*\* : overkill pour ce périmètre.



\### RBAC par table vs enum

\- \*\*Table `roles` + `permissions` + `permission\_role`\*\* : extensible, permet d'ajouter des rôles/permissions sans redéploiement.



\### Policies + FormRequests

\- \*\*Policies\*\* : autorisation au niveau ressource (protection inter-utilisateurs).

\- \*\*FormRequests\*\* : validation centralisée + `authorize()`.

\- \*\*Double barrière\*\* : FormRequest → Policy, puis Service → contrôle métier.



\### Anti-élévation de privilèges

\- \*\*UserPolicy::update()\*\* : si `role` présent et user non-ADMIN → `false` (403).

\- \*\*UserService::update()\*\* : ignore silencieusement `role` si l'appelant n'est pas ADMIN (défense en profondeur).



\### Services

\- Logique métier isolée des controllers (testable unitairement).

\- Exemple : `ProjectService::create()` attache automatiquement le créateur en `OWNER` via `DB::transaction()`.



\### Soft archiving des projets

\- Colonnes `status` (`active` / `archived`) + `archived\_at`.

\- Permet de restaurer, historiser, filtrer.



\## ⚠️ Limites connues



\- Pas de refresh token Sanctum (tokens longue durée par défaut).

\- Rate limiting basique (throttle par défaut de Laravel).

\- Envoi de mails en `log` (dev) — à brancher sur SMTP en prod.

\- Pas de WebSockets / queues avancées.

\- Tests fonctionnels uniquement (pas de tests unitaires Services isolés).



\## 🔮 Améliorations envisageables



\- Permissions granulaires par projet.

\- Audit log (qui a fait quoi, quand).

\- Notifications mail transactionnelles.

\- CI/CD GitHub Actions.

\- OpenAPI / Swagger pour doc auto.

\- Rate limiting ciblé par endpoint sensible.

\- Multi-tenant.

\- Cache Redis pour les permissions.



\## 👤 Compte de démonstration



| Email | Mot de passe | Rôle |

|---|---|---|

| `admin@istawah.test` | `Password@123` | ADMIN |



\## 📦 Collection Postman



Importer `istawah.postman\_collection.json` dans Postman.

Configurer la variable `base\_url` = `http://localhost:8000/api`.

Après le login, le token est automatiquement stocké dans la variable `token`.



\## 📄 Licence



MIT — Projet réalisé pour le test technique ISTAWAH.

## 🏗️ Note sur les choix d'architecture

### Base : MVC

Le projet s'appuie sur le patron **MVC (Model — View — Controller)** fourni nativement par Laravel :

- **Models** (`app/Models/`) : User, Role, Permission, Project — accès aux données via Eloquent ORM.
- **Controllers** (`app/Http/Controllers/Api/`) : AuthController, UserController, ProjectController — orchestration des requêtes HTTP.
- **Vues** : non utilisées car c'est une **API pure** (réponses JSON uniquement, via des Resources).

### Enrichissements au-dessus du MVC

Pour répondre aux exigences du sujet (sécurité, maintenabilité, évolutivité), le MVC a été enrichi de **5 couches complémentaires** :

| Couche | Rôle | Pourquoi |
|---|---|---|
| **FormRequests** (`app/Http/Requests/`) | Validation + autorisation des entrées | Séparer la validation du controller (Single Responsibility) |
| **Policies** (`app/Policies/`) | Règles d'accès fines (RBAC) | Protéger chaque ressource, éviter l'élévation de privilèges |
| **Services** (`app/Services/`) | Logique métier réutilisable | Controllers fins, code testable unitairement |
| **Resources** (`app/Http/Resources/`) | Formatage des réponses JSON | Aucune donnée sensible ne fuit, structure cohérente |
| **Middleware** (`app/Http/Middleware/`) | Filtres transversaux (RBAC, auth) | Garde-fou en amont des routes sensibles |

### Flux d'une requête

```
Requête HTTP
    ↓
Middleware auth:sanctum        (authentification par token)
    ↓
Middleware role:ADMIN          (contrôle de rôle — routes sensibles)
    ↓
FormRequest::authorize()       (appelle la Policy)
    ↓
Policy (UserPolicy / ProjectPolicy)
    ↓
Controller                     (orchestration uniquement)
    ↓
Service                        (logique métier, transactions)
    ↓
Model + Eloquent               (accès base de données)
    ↓
Resource                       (formatage JSON)
    ↓
Réponse HTTP
```

### Bénéfices de cette architecture

- **Séparation des responsabilités** : chaque couche a un rôle unique et clair.
- **Testabilité** : 13 tests automatisés couvrent auth, RBAC et CRUD.
- **Sécurité multi-niveaux** : middleware + Policy + Service (défense en profondeur).
- **Évolutivité** : ajouter une fonctionnalité = ajouter une méthode dans le Service + une route + un test.
- **Maintenabilité** : un bug dans la validation se corrige dans le FormRequest, pas dans le controller.

### Ce qui n'a PAS été utilisé (et pourquoi)

| Technologie | Raison |
|---|---|
| **Repository Pattern** | Overhead inutile : Eloquent ORM joue déjà ce rôle. |
| **DTO (Data Transfer Objects)** | Non nécessaire à cette échelle — les FormRequests validés suffisent. |
| **CQRS / Event Sourcing** | Complexité non justifiée pour un module métier ISTAWAH. |
| **GraphQL** | Le sujet demande explicitement une API REST. |
| **JWT** | Sanctum est plus simple, plus adapté SPA/mobile, et suffisant. |