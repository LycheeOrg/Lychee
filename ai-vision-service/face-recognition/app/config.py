"""Application configuration via Pydantic BaseSettings.

All settings are loaded from environment variables prefixed with ``VISION_FACE_``.
Example: the ``api_key`` field maps to the ``VISION_FACE_API_KEY`` env var.
"""

from functools import lru_cache
from pathlib import Path

from pydantic_settings import BaseSettings, SettingsConfigDict


class AppSettings(BaseSettings):
    """Runtime configuration for the AI Vision Service.

    All values are read from environment variables prefixed ``VISION_FACE_``.
    """

    # --- Required ---
    lychee_api_url: str
    """Lychee instance base URL for callbacks (e.g. ``http://lychee``). No trailing slash."""

    api_key: str
    """Shared API key used in both directions: validated on *inbound* requests from Lychee
    (``X-API-Key`` header) and sent as ``X-API-Key`` on *outbound* callbacks to Lychee.
    Must match ``AI_VISION_FACE_API_KEY`` in the Lychee ``.env``."""

    verify_ssl: bool = True
    """Whether to verify SSL certificates when making callbacks to Lychee.
    Set to ``False`` for development environments with self-signed certificates.
    **WARNING:** Disabling SSL verification in production is a security risk."""

    # --- Model ---
    model_name: str = "ArcFace"
    """DeepFace recognition model name.  ``ArcFace`` = high-accuracy 512-dim embeddings (default);
    other supported models include ``Facenet512``, ``VGG-Face``, etc."""

    detector_backend: str = "retinaface"
    """DeepFace detector backend.  ``retinaface`` = high-accuracy (default);
    alternatives include ``mtcnn``, ``opencv``, ``ssd``."""

    # --- Detection thresholds ---
    detection_threshold: float = 0.5
    """Bounding-box confidence filter — faces below this score are excluded from the callback payload."""

    match_threshold: float = 0.5
    """Cosine-similarity cutoff for selfie match results and suggestion candidates."""

    rescan_iou_threshold: float = 0.5
    """IoU threshold for bounding-box matching on re-scan (preserves ``person_id``)."""

    max_faces_per_photo: int = 10
    """Maximum faces included in a callback payload (top-N by confidence; rest dropped)."""

    # --- Concurrency ---
    thread_pool_size: int = 1
    """Number of threads in the ``ThreadPoolExecutor`` used for CPU-bound inference."""

    workers: int = 1
    """Number of Uvicorn worker processes."""

    # --- Embedding storage ---
    storage_backend: str = "sqlite"
    """Embedding storage engine: ``sqlite`` or ``pgvector``."""

    storage_path: str = "/data/embeddings"
    """SQLite DB directory (used when ``storage_backend = "sqlite"``)."""

    # --- PostgreSQL (pgvector) ---
    pg_host: str = "localhost"
    """PostgreSQL host (only when ``storage_backend = "pgvector"``)."""

    pg_port: int = 5432
    """PostgreSQL port."""

    pg_database: str = "ai_vision"
    """PostgreSQL database name."""

    pg_user: str = "ai_vision"
    """PostgreSQL username."""

    pg_password: str = ""
    """PostgreSQL password."""

    # --- Photo volume ---
    photos_path: str = "/data/photos"
    """Shared Docker-volume mount point for photo files.

    ``photo_path`` values from Lychee are validated to reside within this prefix
    (path-traversal protection).
    """

    # --- Logging ---
    log_level: str = "info"
    """Uvicorn/application log level."""

    # --- Clustering ---
    cluster_eps: float = 0.6
    """DBSCAN epsilon (max cosine distance) for face clustering.
    Lower values produce tighter, more homogeneous clusters."""

    # --- Quality filtering ---
    blur_threshold: float = 0.5
    """Laplacian variance threshold for blur detection.
    Face crops with a variance below this value are discarded before embedding."""

    model_root: str = "/root/.deepface"
    """Root directory for DeepFace model weights.  Exposed as ``DEEPFACE_HOME`` when the service starts.
    Defaults to the library's default (``~/.deepface``) but can be overridden to point to a shared
    Docker volume if desired."""

    model_config = SettingsConfigDict(
        env_prefix="VISION_FACE_",
        # Support .env files in development but never require them in production.
        # Load project root .env first (fallback), then working directory .env (override)
        env_file=(
            Path(__file__).parent.parent / ".env",  # Project root (fallback)
            ".env",  # Current working directory (takes precedence)
        ),
        env_file_encoding="utf-8",
        case_sensitive=False,
        extra="ignore",  # Ignore extra fields (e.g., from Lychee's .env when running from main project)
    )


@lru_cache
def get_settings() -> AppSettings:
    """Return a cached ``AppSettings`` instance.

    Call this function via ``Depends(get_settings)`` in FastAPI route handlers.
    Override ``app.dependency_overrides[get_settings]`` in tests to inject
    mock settings without touching environment variables.
    """
    return AppSettings()  # ty: ignore
