# Lychee AI Vision Service

Facial recognition microservice for [Lychee](https://github.com/LycheeOrg/Lychee).

Detects faces in photos, stores embeddings, and supports selfie-based person
claiming via a REST API consumed by the Lychee PHP backend.

## Tech stack

| Concern | Library |
|---------|---------|
| Web framework | FastAPI + Uvicorn |
| Face detection & recognition | DeepFace (`ArcFace` + `retinaface` backend) |
| Face crop generation | Pillow |
| Embedding clustering | scikit-learn (DBSCAN) |
| Embedding storage | SQLite + sqlite-vec (default) / PostgreSQL + pgvector |
| HTTP client (callbacks) | httpx |
| Config | Pydantic BaseSettings |

## Directory layout

```
ai-vision-service/
├── app/
│   ├── __init__.py
│   ├── config.py          # AppSettings (Pydantic BaseSettings)
│   ├── main.py            # FastAPI app factory & lifespan handler
│   ├── api/
│   │   ├── __init__.py
│   │   ├── dependencies.py  # API key auth dependency
│   │   ├── routes.py        # /detect, /match, /health
│   │   └── schemas.py       # Pydantic request/response models
│   ├── detection/
│   │   ├── __init__.py
│   │   ├── detector.py    # DeepFace wrapper
│   │   └── cropper.py     # 150×150 JPEG crop generator
│   ├── embeddings/
│   │   ├── __init__.py
│   │   ├── store.py         # Abstract EmbeddingStore protocol
│   │   ├── sqlite_store.py  # SQLite + sqlite-vec implementation
│   │   └── pgvector_store.py # PostgreSQL + pgvector implementation
│   ├── clustering/
│   │   ├── __init__.py
│   │   └── clusterer.py   # DBSCAN clustering
│   └── matching/
│       ├── __init__.py
│       └── matcher.py     # Selfie similarity matching
├── tests/
│   └── __init__.py
├── Dockerfile
├── pyproject.toml
└── README.md
```

## Environment variables

All variables are prefixed `VISION_FACE_`.

| Variable | Required | Default | Description |
|----------|----------|---------|-------------|
| `VISION_FACE_LYCHEE_API_URL` | Yes | — | Lychee base URL for callbacks |
| `VISION_FACE_API_KEY` | Yes | — | Shared API key: validated on inbound requests from Lychee, and sent on outbound callbacks to Lychee |
| `VISION_FACE_VERIFY_SSL` | No | `true` | Verify SSL certificates when making callbacks to Lychee. Set to `false` for dev environments with self-signed certificates |
| `VISION_FACE_MODEL_NAME` | No | `ArcFace` | DeepFace recognition model |
| `VISION_FACE_DETECTOR_BACKEND` | No | `retinaface` | DeepFace detector backend (`retinaface`, `mtcnn`, `opencv`, `ssd`) |
| `VISION_FACE_DETECTION_THRESHOLD` | No | `0.5` | Confidence filter for detected faces |
| `VISION_FACE_MATCH_THRESHOLD` | No | `0.5` | Cosine-similarity cutoff for selfie matching |
| `VISION_FACE_RESCAN_IOU_THRESHOLD` | No | `0.5` | IoU threshold for bounding-box matching on re-scan |
| `VISION_FACE_MAX_FACES_PER_PHOTO` | No | `10` | Maximum faces included in a callback payload |
| `VISION_FACE_THREAD_POOL_SIZE` | No | `1` | Inference thread-pool size |
| `VISION_FACE_STORAGE_BACKEND` | No | `sqlite` | `sqlite` or `pgvector` |
| `VISION_FACE_STORAGE_PATH` | No | `/data/embeddings` | SQLite DB directory |
| `VISION_FACE_PG_HOST` | No* | `localhost` | PostgreSQL host (*required with pgvector) |
| `VISION_FACE_PG_PORT` | No | `5432` | PostgreSQL port |
| `VISION_FACE_PG_DATABASE` | No* | `ai_vision` | PostgreSQL database (*required with pgvector) |
| `VISION_FACE_PG_USER` | No* | `ai_vision` | PostgreSQL user (*required with pgvector) |
| `VISION_FACE_PG_PASSWORD` | No* | `` | PostgreSQL password (*required with pgvector) |
| `VISION_FACE_PHOTOS_PATH` | No | `/data/photos` | Shared volume mount for photo files |
| `VISION_FACE_WORKERS` | No | `1` | Number of Uvicorn worker processes |
| `VISION_FACE_LOG_LEVEL` | No | `info` | Log level |

## Development

### Setup

```bash
# Install uv (https://docs.astral.sh/uv/getting-started/installation/)
curl -LsSf https://astral.sh/uv/install.sh | sh

# Install all dependencies (including dev)
uv sync

# Configure .env file (create or edit .env in this directory)
# Minimum required variables:
# VISION_FACE_LYCHEE_API_URL=https://lychee.test
# VISION_FACE_API_KEY=changeme
# VISION_FACE_VERIFY_SSL=false
# VISION_FACE_PHOTOS_PATH=../../public/uploads
```

### Running locally

```bash
# Using uv run (recommended)
uv run python -m uvicorn app.main:app --host 0.0.0.0 --port 8000 --reload
```

The service will be available at http://localhost:8000
- API docs: http://localhost:8000/docs
- Health check: http://localhost:8000/health

### Linting and testing

```bash
# Lint and format
uv run ruff format
uv run ruff check --fix

# Type check
uv run ty check

# Run tests
uv run pytest

# Run tests with coverage
uv run pytest --cov=app --cov-report=html
```

## Docker

```bash
# Build (bakes ArcFace + RetinaFace models into the image – ~500 MB download on first build)
docker build -t lychee-ai-vision .

# Run
docker run --rm \
  -e VISION_FACE_LYCHEE_API_URL=http://lychee \
  -e VISION_FACE_API_KEY=changeme \
  -v /path/to/photos:/data/photos:ro \
  -v ai-vision-embeddings:/data/embeddings \
  -p 8000:8000 \
  lychee-ai-vision
```

---

*Last updated: 2026-04-24*
