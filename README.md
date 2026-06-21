# CTF Sandbox Monitoring – Backend API

Backend API for a **CTF (Capture The Flag) Sandbox Monitoring** system that manages sandbox environments, Proxmox load balancer nodes, and real-time CTF matches.

## Tech Stack

| Layer | Technology |
|---|---|
| **Framework** | Laravel 12 |
| **Runtime** | PHP 8.3 + Swoole (via Laravel Octane) |
| **Database** | PostgreSQL |
| **Auth** | Laravel Sanctum (token-based API authentication) |
| **Container** | Docker + Docker Compose |
| **Streaming** | Server-Sent Events (SSE) |

## Main Features

### Swoole (Laravel Octane)
The application runs on top of Swoole through Laravel Octane for high performance. Swoole keeps the application in memory, eliminating bootstrap overhead on every request.

Configuration in `.env`:

```env
OCTANE_SERVER=swoole
```

### Laravel Sanctum
Token-based authentication for all API endpoints. After login or registration, users receive a `plainTextToken` that must be sent in the request header:

```http
Authorization: Bearer <token>
```

### Server-Sent Events (SSE)
The `GET /api/sandboxes/{id}/stream` endpoint continuously sends live events using SSE. Clients subscribe and receive:

- `snapshot` event — initial sandbox state
- `sandbox-event` event — new activities (`process_start`, `file_access`, etc.)

### Load Balancer & Node Scoring
An automatic scoring system selects the most suitable Proxmox node based on node weight, active sandbox count, and pending sandbox count. Supports rebalancing queued sandboxes.

### CtfLog – Database Logger
The `CtfLog` entity records command and script execution results into the `ctf_logs` table. It is automatically used by all seeders and Artisan commands through the `CtfLogger` service.

---

## Setup & Installation

### Prerequisites

- PHP 8.3+ with the Swoole extension
- PostgreSQL
- Composer
- Node.js 20+

### Installation

```bash
# Clone repository
git clone <repo-url> && cd ctf-sandbox-monitoring-ai

# Install PHP dependencies
composer install

# Install Node dependencies
npm install

# Copy environment file
cp .env.example .env
php artisan key:generate

docker exec -it --user sail ctf-sandbox /bin/bash


```

### Database

Ensure PostgreSQL is running and the database has been created according to the environment configuration:

```bash
# Run migrations
php artisan migrate

# Seed initial data
php artisan db:seed
```

### Local Server

```bash
# Development (server + queue + logs + vite)
composer run dev

# Production
composer run main
```

---

## Docker

### Build & Run

```bash
# Build image
docker compose build

# Run container (connects to an external database)
docker compose up -d

# Or use the Makefile
make build
make up

# to login as sail user in docker
make ex-php 
```

---
## Proxmox User Setup

Before creating sandboxes, make sure the Proxmox environment is ready.

## User and Permission Setup
- Create the required user in Datacenter → Permissions → Users.
- Assign the appropriate permissions at the cluster permission level.
- Configure a permission group or policy for the user.
- For admin-like access, keep the default policy or mirror the admin role as needed.

## Prerequisites
- Proxmox VE post-installation is complete (find the script [here](https://community-scripts.org/?q=pve+post).
- ISO images or CT templates are already uploaded for VM or container creation.
## API Documentation

Refer to the Proxmox API viewer for endpoint examples and request formats:
```
https://pve.proxmox.com/pve-docs/api-viewer/
```
## Generating a Token

To generate a token, send a request to:

```
https://{{PROXMOX_DEFAULT_NETWORK}}/api2/json/access/ticket
```

Use the response like this:

- ticket → stored in cookies
- CSRFPreventionToken → sent as a request header

Make sure the required image template is available in Proxmox, both for containers and for virtual machines.

---

## API Endpoints

### Sandboxes

| Endpoint Name | Method | Description |
|---|---|---|
| Sandbox Provision | `POST /api/sandboxes` | The backend selects the best node and provisions a new sandbox. (U need to have the template image to start creating one)|
| Activate Sandbox | `POST /api/sandboxes/{id}/active` | Starts the specified sandbox. |
| Sandbox Detail | `GET /api/sandboxes/{id}` | Retrieve sandbox details. |
| Sandbox Events | `POST /api/sandboxes/{id}/events` | Agent sends activity events (`process_start`, `file_access`, `network_connection`, `alert`). |
| Live Stream | `GET /api/sandboxes/{id}/stream` | Subscribe to live events using SSE (one-way streaming) or WebSocket (bidirectional communication). |

### Load Balancer

| Endpoint Name | Method | Description |
|---|---|---|
| List Nodes | `GET /api/load-balancer/nodes` | View all nodes along with their weights and statuses. |
| Update Node | `PATCH /api/load-balancer/nodes/{id}` | Update node weight, status, or capacity. |
| Rebalance | `POST /api/load-balancer/rebalance` | Recalculate sandbox placement distribution. |

### CTF Matches

| Endpoint Name | Method | Description |
|---|---|---|
| List Matches | `POST /api/ctf/matches` | List active and completed matches. |
| Join Match | `POST /api/ctf/matches/{id}/join` | Join a match. |
| Submit Answer | `POST /api/ctf/matches/{id}/submit` | Submit a correct answer or challenge solution to gain points. |
| Scoreboard | `GET /api/ctf/matches/{id}/scoreboard` | View the real-time scoreboard. |

---

## Artisan Commands

| Command | Description |
|---|---|
| `php artisan proxmox:nodes-sync` | Randomizes the status and weight of all Proxmox nodes. |
| `php artisan proxmox:nodes-sync --dry-run` | Simulates changes without saving them. |
| `php artisan db:seed` | Run all seeders. |
| `php artisan migrate:fresh --seed` | Reset the database and reseed it. |
| `php artisan route:list` | Display all registered routes. |

### Logging

- Seeding, queries, and related processes are automatically recorded in the logs.

---