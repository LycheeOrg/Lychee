# How-To: Configure Facial Recognition (AI Vision)

**Author:** Lychee Team  
**Last Updated:** 2026-03-22  
**Feature:** 030-ai-vision-service  
**Related:** [Feature 030 Spec](../4-architecture/features/030-ai-vision-service/spec.md)

## Overview

Lychee's facial recognition feature is powered by a sidecar Python service (`ai-vision-service`). When enabled, Lychee detects faces in photos, groups them into Person profiles, and lets users claim their own profile. This guide covers:

1. [Prerequisites](#prerequisites)
2. [Docker Compose setup](#docker-compose-setup)
3. [Shared volume configuration](#shared-volume-configuration)
4. [Environment variables](#environment-variables)
5. [Enabling the feature in Lychee admin](#enabling-the-feature-in-lychee-admin)
6. [Permission modes](#permission-modes)
7. [Running a bulk scan](#running-a-bulk-scan)
8. [Service health check](#service-health-check)
9. [Troubleshooting](#troubleshooting)

---

## Prerequisites

- Docker and Docker Compose v2
- A working Lychee deployment (see [docker-compose.minimal.yaml](../../../docker-compose.minimal.yaml))
- A **Supporter Edition (SE)** licence — AI Vision is an SE-only feature

---

## Docker Compose Setup

Add the `ai_vision` service to your `docker-compose.yaml`. The complete minimal example is in [docker-compose.minimal.yaml](../../../docker-compose.minimal.yaml). The key stanza:

```yaml
services:
  lychee_api:
    # ... existing config ...
    volumes:
      - ./lychee/uploads:/app/public/uploads   # Lychee upload directory

  ai_vision:
    build:
      context: ./ai-vision-service             # Build from source, OR use a pre-built image
    container_name: lychee-ai-vision
    restart: unless-stopped
    security_opt:
      - no-new-privileges:true
    cap_drop:
      - ALL
    environment:
      VISION_FACE_LYCHEE_API_URL: "http://lychee_api:8000"
      VISION_FACE_API_KEY: "${AI_VISION_API_KEY}"
      VISION_FACE_PHOTOS_PATH: "/data/photos"
      VISION_FACE_STORAGE_PATH: "/data/embeddings"
    volumes:
      - ./lychee/uploads:/data/photos:ro        # Shared read-only photos volume
      - ai_vision_embeddings:/data/embeddings   # Persistent embeddings store
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

The AI Vision service reads photo files directly from the filesystem — no HTTP file transfer. This requires both containers to mount the same upload directory:

| Container | Mount path | Mode |
|---|---|---|
| `lychee_api` | `/app/public/uploads` | read/write |
| `ai_vision` | `/data/photos` | read-only |
| Host bind mount | `./lychee/uploads` | (source for both) |

**Critical:** The host path `./lychee/uploads` must be identical for both mounts. If you use an absolute path or a named volume for `lychee_api`'s uploads, apply the same source to `ai_vision`.

---

## Environment Variables

### Lychee API (`lychee_api` / `lychee_worker`)

Add these to `x-common-env` or the service's `environment` block:

| Variable | Description | Example |
|---|---|---|
| `AI_VISION_FACE_URL` | Internal URL of the AI Vision service | `http://lychee-ai-vision:8000` |
| `AI_VISION_FACE_API_KEY` | Shared secret used in both directions: Lychee sends it on scan requests to Python; Python sends it on callback responses to Lychee | `changeme-strong-random-value` |

> Generate strong secrets with: `openssl rand -hex 32`

### AI Vision Service (`ai_vision`)

| Variable | Description | Default |
|---|---|---|
| `VISION_FACE_LYCHEE_API_URL` | Base URL of the Lychee API (for callbacks) | — |
| `VISION_FACE_API_KEY` | Must match `AI_VISION_FACE_API_KEY` in Lychee | — |
| `VISION_FACE_VERIFY_SSL` | Verify SSL certificates when connecting to Lychee. Set to `false` for dev environments with self-signed certificates | `true` |
| `VISION_FACE_PHOTOS_PATH` | Path where photos are mounted inside the container | `/data/photos` |
| `VISION_FACE_STORAGE_PATH` | Path for persisting face embeddings | `/data/embeddings` |

---

## Enabling the Feature in Lychee Admin

After starting the containers, enable the feature in **Admin → Settings → AI Vision**:

1. **AI Vision enabled** — master toggle; set to `On`.
2. **Facial recognition enabled** — sub-toggle; set to `On`.
3. Configure optional settings (permission mode, batch size, etc.).

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
php artisan lychee:scan-faces
```

Scanning runs asynchronously through the queue. Ensure the `lychee_worker` container is running. Progress is visible in the queue job history.

---

## Service Health Check

The AI Vision service exposes a `/health` endpoint:

```bash
# Inside the lychee network, from another container:
curl http://lychee-ai-vision:8000/health

# From the host (if you expose the port):
curl http://localhost:<MAPPED_PORT>/health
```

A healthy response:
```json
{"status": "ok", "version": "x.y.z"}
```

Docker will also report the container's health status — wait for `healthy` before triggering scans:

```bash
docker compose ps
```

---

## Troubleshooting

### AI Vision endpoints return 403

- Confirm the Lychee instance is a **Supporter Edition** licence.
- Check that `ai_vision_enabled = 1` and `ai_vision_face_enabled = 1` in admin settings.

### Photos are not scanned / `face_scan_status` stays `pending`

1. Verify the `lychee_worker` container is running (`docker compose ps`).
2. Confirm `QUEUE_CONNECTION` is not `sync` in the Lychee worker environment.
3. Check the AI Vision service health endpoint.
4. Review `lychee_worker` logs: `docker compose logs lychee-worker`.

### AI Vision service cannot find photos

- Compare volume mounts: the host `./lychee/uploads` path must be the same in both the `lychee_api` and `ai_vision` volume definitions.
- Verify `VISION_FACE_PHOTOS_PATH` inside the container matches the volume mount destination.

### API key mismatch errors (401 from AI Vision / Lychee)

- `AI_VISION_FACE_API_KEY` (Lychee) must equal `VISION_FACE_API_KEY` (Python service). The same key is used in both directions.
- Restart both containers after changing the secret.

### Selfie claim returns "no match found"

- Lower `ai_vision_face_selfie_confidence_threshold` (default `0.8`) in admin settings to accept less-certain matches.
- Ensure the photo library has been fully scanned first.

---

*Last updated: 2026-03-22*
