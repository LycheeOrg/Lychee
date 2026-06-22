# How-To: Configure Facial Recognition (AI Vision)

**Author:** Lychee Team  
**Last Updated:** 2026-06-22  
**Feature:** 030-ai-vision-service  
**Related:** [Feature 030 Spec](../4-architecture/features/030-ai-vision-service/spec.md)

## Overview

Lychee's facial recognition feature is powered by a sidecar Python service ([`lychee-facial-recognition`](https://github.com/LycheeOrg/Lychee-Facial-Recognition)). When enabled, Lychee detects faces in photos, clusters them, groups them into Person profiles, and lets users claim their own profile. This guide covers:

1. [Prerequisites](#prerequisites)
2. [Docker Compose setup](#docker-compose-setup)
3. [Shared volume configuration](#shared-volume-configuration)
4. [Non-Docker (bare-metal) setup](#non-docker-bare-metal-setup)
5. [Environment variables](#environment-variables)
6. [Enabling the feature in Lychee admin](#enabling-the-feature-in-lychee-admin)
7. [Permission modes](#permission-modes)
8. [Running a bulk scan](#running-a-bulk-scan)
9. [Clustering](#clustering)
10. [Maintenance operations](#maintenance-operations)
11. [Service health check](#service-health-check)
12. [Troubleshooting](#troubleshooting)

---

## Prerequisites

### Docker deployment

- Docker and Docker Compose v2
- A working Lychee deployment (see [docker-compose.yaml](../../../docker-compose.yaml))
- The `lychee_worker` container running (face scans are processed through the queue)

### Non-Docker (bare-metal) deployment

- Python 3.13+ with [`uv`](https://docs.astral.sh/uv/)
- A working Lychee installation (Apache/Nginx + PHP)
- A running queue worker (`php artisan queue:work`) — face scans are processed through the queue
- Git (to clone the facial recognition service repository)

---

## Docker Compose Setup

Add the `lychee_facial_recognition` service to your `docker-compose.yaml`. The complete example is in [docker-compose.yaml](../../../docker-compose.yaml). The key stanza:

```yaml
services:
  lychee_api:
    # ... existing config ...
    environment:
      AI_VISION_ENABLED: "${AI_VISION_ENABLED:-true}"
      AI_VISION_FACE_API_KEY: "${AI_VISION_API_KEY:-changeme}"
      AI_VISION_FACE_URL: "http://lychee_facial_recognition:8000"
    volumes:
      - ./lychee/uploads:/app/public/uploads

  lychee_facial_recognition:
    expose:
      - "${APP_PORT_AI_FACE:-8001}"
    ports:
      - "${APP_PORT_AI_FACE:-8001}:8000"
    image: ghcr.io/lycheeorg/lychee-facial-recognition:latest
    restart: unless-stopped
    security_opt:
      - no-new-privileges:true
    cap_drop:
      - ALL
    environment:
      VISION_FACE_LYCHEE_API_URL: "http://lychee_api:8000"
      VISION_FACE_API_KEY: "${AI_VISION_API_KEY:-changeme}"
      VISION_FACE_VERIFY_SSL: "${AI_VISION_VERIFY_SSL:-true}"
      VISION_FACE_PHOTOS_PATH: "/data/photos"
      VISION_FACE_STORAGE_BACKEND: sqlite
      VISION_FACE_STORAGE_PATH: "/data/embeddings"
    volumes:
      - ./lychee/uploads:/data/photos:ro
      - ai_vision_embeddings:/data/embeddings
    networks:
      - lychee
    depends_on:
      lychee_api:
        condition: service_healthy
    healthcheck:
      test: ["CMD", "curl", "-f", "http://localhost:8000/health"]
      interval: 30s
      timeout: 10s
      retries: 3
      start_period: 60s

volumes:
  ai_vision_embeddings:
    name: lychee_ai_vision_embeddings
    driver: local
```

---

## Shared Volume Configuration

The facial recognition service reads photo files directly from the filesystem — no HTTP file transfer. Both containers must mount the same upload directory:

| Container | Mount path | Mode |
|---|---|---|
| `lychee_api` | `/app/public/uploads` | read/write |
| `lychee_facial_recognition` | `/data/photos` | read-only |
| Host bind mount | `./lychee/uploads` | (source for both) |

**Critical:** The host path `./lychee/uploads` must be identical for both mounts. If you use an absolute path or a named volume for `lychee_api`'s uploads, apply the same source to `lychee_facial_recognition`.

---

## Non-Docker (Bare-Metal) Setup

If you run Lychee directly on a server (e.g. via Apache/Nginx + PHP), you can run the facial recognition service as a standalone Python process without Docker.

### 1. Clone and install the service

```bash
cd /opt
git clone https://github.com/LycheeOrg/Lychee-Facial-Recognition.git
cd Lychee-Facial-Recognition

# Install dependencies (uv will create a .venv automatically)
uv sync
```

### 2. Configure the service

Copy the example environment file and edit it:

```bash
cp .env.example .env
```

Key variables to set in the `.env` file:

```bash
# URL where Lychee is accessible (used for callbacks)
VISION_FACE_LYCHEE_API_URL=http://localhost

# Shared secret — must match AI_VISION_FACE_API_KEY in Lychee's .env
VISION_FACE_API_KEY=changeme

# Point directly to Lychee's uploads directory on disk
VISION_FACE_PHOTOS_PATH=/var/www/lychee/public/uploads

# Where to store the SQLite embedding database
VISION_FACE_STORAGE_BACKEND=sqlite
VISION_FACE_STORAGE_PATH=/opt/Lychee-Facial-Recognition/data/embeddings

# SSL verification (set to false if Lychee is on localhost without HTTPS)
VISION_FACE_VERIFY_SSL=false
```

> Adjust `VISION_FACE_PHOTOS_PATH` to match your actual Lychee uploads directory. The service reads photo files directly from the filesystem — it must have read access to this directory.

### 3. Configure Lychee

In your Lychee `.env` file, point to the facial recognition service:

```bash
AI_VISION_ENABLED=true
AI_VISION_FACE_URL=http://localhost:8000
AI_VISION_FACE_API_KEY=changeme
```

> **Important:** `AI_VISION_FACE_API_KEY` (Lychee) must match `VISION_FACE_API_KEY` (Python service).

### 4. Start the service

```bash
cd /opt/Lychee-Facial-Recognition
uv run uvicorn app.main:app --host 0.0.0.0 --port 8000
```

For production, run it as a background process using a process manager like systemd or supervisor.

**Example systemd unit** (`/etc/systemd/system/lychee-facial-recognition.service`):

```ini
[Unit]
Description=Lychee Facial Recognition Service
After=network.target

[Service]
Type=simple
User=www-data
WorkingDirectory=/opt/Lychee-Facial-Recognition
EnvironmentFile=/opt/Lychee-Facial-Recognition/.env
ExecStart=/usr/bin/uv run uvicorn app.main:app --host 127.0.0.1 --port 8000
Restart=on-failure
RestartSec=5

[Install]
WantedBy=multi-user.target
```

```bash
sudo systemctl daemon-reload
sudo systemctl enable --now lychee-facial-recognition
```

### 5. Verify the service is running

```bash
curl http://localhost:8000/health
```

Expected response:
```json
{"status": "ok", "model_loaded": true, "embedding_count": 0}
```

> **Note:** On first start, the service will download the face detection model, which may take a few minutes depending on your internet connection.

---

## Environment Variables

### Lychee API (`lychee_api` / `lychee_worker`)

Add these to `x-common-env` or the service's `environment` block:

| Variable | Description | Default |
|---|---|---|
| `AI_VISION_ENABLED` | Master kill-switch for the AI Vision subsystem | `true` |
| `AI_VISION_FACE_URL` | Internal URL of the facial recognition service | — |
| `AI_VISION_FACE_API_KEY` | Shared secret for mutual authentication (`X-API-Key` header) | — |
| `AI_VISION_FACE_RESCAN_IOU_THRESHOLD` | IoU threshold for preserving `person_id` on re-scan | `0.3` |
| `AI_VISION_FACE_STUCK_SCAN_THRESHOLD_MINUTES` | Minutes before a pending scan is considered stuck | `720` |

> Generate strong secrets with: `openssl rand -hex 32`

### Facial Recognition Service (`lychee_facial_recognition`)

| Variable | Description | Default |
|---|---|---|
| **Connection** | | |
| `VISION_FACE_LYCHEE_API_URL` | Base URL of the Lychee API (for callbacks) | — |
| `VISION_FACE_API_KEY` | Must match `AI_VISION_FACE_API_KEY` in Lychee | — |
| `VISION_FACE_VERIFY_SSL` | Verify SSL certificates when connecting to Lychee | `true` |
| `VISION_FACE_SKIP_LYCHEE_CHECK` | Skip Lychee connectivity check at startup | `false` |
| **Logging** | | |
| `VISION_FACE_LOG_LEVEL` | Log level: debug, info, warning, error, critical | `info` |
| **Clustering** | | |
| `VISION_FACE_CLUSTER_EPS` | DBSCAN epsilon (max cosine distance); lower = tighter clusters | `0.6` |
| **Storage** | | |
| `VISION_FACE_PHOTOS_PATH` | Path where photos are accessible by the service (container mount or local directory) | `/data/photos` |
| `VISION_FACE_STORAGE_BACKEND` | Embedding store engine: `sqlite` or `pgvector` | `sqlite` |
| `VISION_FACE_STORAGE_PATH` | Directory for the SQLite embedding database | `/data/embeddings` |
| **Concurrency** | | |
| `VISION_FACE_THREAD_POOL_SIZE` | CPU threads for face detection inference | `1` |
| `VISION_FACE_WORKERS` | Uvicorn worker processes | `1` |
| **Queue** | | |
| `VISION_FACE_QUEUE_BACKEND` | Job queue backend: `database` or `redis` | `database` |
| `VISION_FACE_QUEUE_MAX_SIZE` | Max pending jobs (0 = unlimited); excess requests get HTTP 429 | `0` |
| **Detection thresholds** | | |
| `VISION_FACE_DETECTION_THRESHOLD` | Bounding-box confidence filter (0-1) | `0.5` |
| `VISION_FACE_MATCH_THRESHOLD` | Cosine-similarity cutoff for selfie match and suggestion candidates | `0.5` |
| `VISION_FACE_RESCAN_IOU_THRESHOLD` | IoU threshold for bounding-box matching on re-scan | `0.5` |
| `VISION_FACE_MAX_FACES_PER_PHOTO` | Maximum faces included in a callback payload (top-N by confidence) | `10` |
| **Quality filtering** | | |
| `VISION_FACE_MIN_FACE_SIZE_PIXELS` | Minimum face size in pixels (longest side); 0 = disabled | `0` |
| `VISION_FACE_BLUR_THRESHOLD` | Laplacian variance threshold; faces below this are discarded as blurry | `0.5` |

> See the full list of environment variables at the [Lychee-Facial-Recognition `.env.example`](https://github.com/LycheeOrg/Lychee-Facial-Recognition/blob/master/.env.example).

---

## Enabling the Feature in Lychee Admin

After starting the facial recognition service (Docker or bare-metal), enable the feature in **Admin → Settings → AI Vision**:

1. **AI Vision enabled** — master toggle; set to `On`.
2. **Facial recognition enabled** — sub-toggle; set to `On`.
3. Configure optional settings:

| Setting | Default | Description |
|---|---|---|
| `ai_vision_face_permission_mode` | `restricted` | Who can view People, face overlays, and manage faces |
| `ai_vision_face_selfie_confidence_threshold` | `0.8` | Minimum confidence for selfie-based person claim |
| `ai_vision_face_person_is_searchable_default` | `On` | Default `is_searchable` flag for new Person records |
| `ai_vision_face_allow_user_claim` | `On` | Allow non-admin users to claim a Person |
| `ai_vision_face_overlay_enabled` | `On` | Show face bounding-box overlays in the UI |
| `ai_vision_face_overlay_default_visibility` | `visible` | Default overlay state when opening a photo (`visible` or `hidden`; toggle with `P` key) |
| `ai_vision_face_recognition_warning` | `On` | Show legal warning on Face Clusters and Face Maintenance pages |

These settings are only visible on Supporter Edition instances.

---

## Permission Modes

`ai_vision_face_permission_mode` controls who can view People, face overlays, and perform face management. Choose the mode that matches your deployment scenario.

| Mode | Best for |
|---|---|
| `public` | Community/open galleries where anyone can browse people |
| `private` | Personal or team galleries — all features require login |
| `privacy-preserving` | Multi-user deployments — users only see their own content |
| `restricted` | High-privacy or admin-controlled deployments |

**Permission matrix:**

| Operation | `public` | `private` | `privacy-preserving` | `restricted` |
|---|---|---|---|---|
| View People page | Guest | Logged in | Owner + admin | Admin only |
| View face overlays | Album access | Logged in | Owner + admin | Owner + admin |
| Create / edit Person | Logged in | Logged in | Owner + admin | Admin only |
| Assign face | Logged in | Logged in | Owner + admin | Admin only |
| Trigger scan | Logged in | Logged in | Owner + admin | Owner + admin |
| Claim person (selfie) | Logged in | Logged in | Logged in | Logged in |
| Merge persons | Logged in | Logged in | Owner + admin | Admin only |

> **Default:** `restricted` — the most conservative option.

---

## Running a Bulk Scan

After setup, scan your existing photo library for faces:

**Via the admin UI:**
1. Navigate to **Admin → Maintenance**.
2. Find the **Bulk Face Scan** card and click **Scan all unscanned photos**.

**Via CLI:**
```bash
# Scan all unscanned photos
php artisan lychee:scan-faces

# Scan only a specific album
php artisan lychee:scan-faces --album={album_id}
```

Scanning runs asynchronously through the queue. Ensure your queue worker is running (`lychee_worker` container in Docker, or `php artisan queue:work` on bare-metal). Progress is visible in the queue job history.

---

## Clustering

After faces have been detected, run DBSCAN clustering to group similar faces together. This helps with bulk assignment of faces to Person records.

**Via the admin UI:**
1. Navigate to **Admin → Maintenance**.
2. Find the **Run Face Clustering** card and click to trigger clustering.

Clustering is performed by the Python service. It reads all stored embeddings, runs DBSCAN (controlled by `VISION_FACE_CLUSTER_EPS`), and posts the results back to Lychee. Faces receive a `cluster_label`:

- `NULL` — not yet clustered
- `-1` — noise (not part of any cluster)
- `0, 1, 2, ...` — cluster ID

Cluster results can be reviewed and assigned to persons from the **Face Clusters** page in the admin UI.

---

## Maintenance Operations

All maintenance operations are available in **Admin → Maintenance**:

| Operation | Description |
|---|---|
| **Bulk Face Scan** | Enqueue all unscanned photos for face detection |
| **Run Face Clustering** | Trigger DBSCAN clustering on all face embeddings |
| **Destroy Dismissed Faces** | Hard-delete all faces marked as dismissed (also removes embeddings from the Python service) |
| **Sync Face Embeddings** | Synchronise embedding data between Lychee and the Python service |
| **Reset Face Scan Status** | Reset stuck-pending or failed photos so they can be re-scanned |

**Additional CLI commands:**

```bash
# Re-enqueue all failed scans
php artisan lychee:rescan-failed-faces

# Also reset photos stuck in pending for longer than 60 minutes
php artisan lychee:rescan-failed-faces --stuck-pending --older-than=60
```

---

## Service Health Check

The facial recognition service exposes a `/health` endpoint:

```bash
# Docker — from the host (default mapped port 8001):
curl http://localhost:8001/health

# Docker — inside the lychee network, from another container:
curl http://lychee_facial_recognition:8000/health

# Non-Docker — directly on the default port:
curl http://localhost:8000/health
```

A healthy response:
```json
{"status": "ok", "model_loaded": true, "embedding_count": 1234}
```

**Docker** will also report the container's health status — wait for `healthy` before triggering scans:

```bash
docker compose ps
```

**Non-Docker** — check the service process is running:

```bash
# If managed by systemd:
systemctl status lychee-facial-recognition
```

Lychee's **Admin → Diagnostics** page includes an AI Vision service health check that verifies connectivity, health status, and configuration consistency.

---

## Troubleshooting

### AI Vision endpoints return 403

- Check that `ai_vision_enabled = 1` and `ai_vision_face_enabled = 1` in admin settings.

### Photos are not scanned / `face_scan_status` stays `pending`

1. Verify the queue worker is running:
   - **Docker:** `docker compose ps` — check that `lychee_worker` is up.
   - **Non-Docker:** ensure `php artisan queue:work` is running (or check your supervisor/systemd unit).
2. Confirm `QUEUE_CONNECTION` is not `sync` in Lychee's environment.
3. Check the facial recognition service health endpoint.
4. Review logs:
   - **Docker:** `docker compose logs lychee-worker`
   - **Non-Docker:** check your Laravel log at `storage/logs/laravel.log` and the service's stdout/journal.
5. If photos are stuck in `pending` for a long time, reset them:
   ```bash
   php artisan lychee:rescan-failed-faces --stuck-pending --older-than=60
   ```

### Facial recognition service cannot find photos

- **Docker:** compare volume mounts — the host `./lychee/uploads` path must be the same in both the `lychee_api` and `lychee_facial_recognition` volume definitions.
- **Non-Docker:** verify `VISION_FACE_PHOTOS_PATH` points to your Lychee uploads directory (e.g. `/var/www/lychee/public/uploads`) and that the service process has read access to it.
- In both cases, verify `VISION_FACE_PHOTOS_PATH` matches the actual path where photos are accessible by the service.

### API key mismatch errors (401 from either service)

- `AI_VISION_FACE_API_KEY` (Lychee) must equal `VISION_FACE_API_KEY` (Python service). The same key is used in both directions via the `X-API-Key` header.
- Restart both services after changing the secret:
  - **Docker:** `docker compose restart`
  - **Non-Docker:** restart the Python service and clear Lychee's config cache (`php artisan config:clear`).

### Selfie claim returns "no match found"

- Lower `ai_vision_face_selfie_confidence_threshold` (default `0.8`) in admin settings to accept less-certain matches.
- Ensure the photo library has been fully scanned first.

### Clustering produces too many / too few clusters

- Adjust `VISION_FACE_CLUSTER_EPS` (default `0.6`). Lower values create tighter, more numerous clusters; higher values merge more faces together.
- Re-run clustering from Admin → Maintenance after changing the value.

---

*Last updated: 2026-06-22*
