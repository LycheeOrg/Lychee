"""FastAPI route handlers.

Endpoints:
  POST /detect   - Accept a face-detection job; run async, callback to Lychee.
  POST /match    - Accept a selfie; return top-N similar stored faces.
  GET  /health   - Return service health and embedding count.
"""

from __future__ import annotations

import asyncio
import logging
import uuid
from pathlib import Path
from typing import TYPE_CHECKING

import httpx
from fastapi import APIRouter, BackgroundTasks, Depends, HTTPException, Request, UploadFile

from app.api.dependencies import get_detector, get_store, require_api_key
from app.api.schemas import (
    ClusterFaceResult,
    ClusterResponse,
    DeleteEmbeddingsRequest,
    DeleteEmbeddingsResponse,
    DetectCallbackPayload,
    DetectCallbackResponse,
    DetectRequest,
    ErrorCallbackPayload,
    FaceResult,
    HealthResponse,
    MatchResponse,
    MatchResult,
    SuggestionResult,
)
from app.clustering.clusterer import FaceClusterer
from app.config import AppSettings, get_settings
from app.detection.cropper import generate_crop

if TYPE_CHECKING:
    from concurrent.futures import Executor

    from app.detection.detector import DetectedFace, FaceDetector
    from app.embeddings.store import EmbeddingStore

logger = logging.getLogger(__name__)

router = APIRouter()


# ---------------------------------------------------------------------------
# POST /detect
# ---------------------------------------------------------------------------


@router.post("/detect", status_code=202)
async def detect(
    body: DetectRequest,
    background_tasks: BackgroundTasks,
    request: Request,
    settings: AppSettings = Depends(get_settings),
    _: None = Depends(require_api_key),
) -> None:
    """Accept a face-detection job.

    Validates the photo path (path-traversal protection), then immediately
    returns **202 Accepted** and schedules detection as a background task.
    Results are POSTed back to Lychee's results endpoint once detection
    completes.
    """
    resolved = Path(settings.photos_path.removesuffix("/") + "/" + body.photo_path.removeprefix("/")).resolve()
    photos_root = Path(settings.photos_path).resolve()

    if not str(resolved).startswith(str(photos_root) + "/") and resolved != photos_root:
        raise HTTPException(status_code=400, detail=f"photo_path {resolved} is outside the allowed directory")

    if not resolved.is_file():
        raise HTTPException(status_code=400, detail="photo_path does not exist or is not a file")

    detector: FaceDetector = get_detector(request)
    store: EmbeddingStore = get_store(request)
    executor: Executor = request.app.state.executor

    background_tasks.add_task(
        _run_detection_job,
        body.photo_id,
        resolved,
        detector,
        store,
        executor,
        settings,
    )


# ---------------------------------------------------------------------------
# POST /match
# ---------------------------------------------------------------------------


@router.post("/match")
async def match(
    file: UploadFile,
    request: Request,
    settings: AppSettings = Depends(get_settings),
    _: None = Depends(require_api_key),
) -> MatchResponse:
    """Match a selfie against stored face embeddings.

    Accepts a multipart image upload, detects the face, embeds it, and returns
    the closest matches from the embedding store above the configured threshold.

    Returns **422** if no face is detected in the selfie.
    """
    image_bytes = await file.read()

    detector: FaceDetector = get_detector(request)
    store: EmbeddingStore = get_store(request)
    executor: Executor = request.app.state.executor

    loop = asyncio.get_running_loop()
    raw_faces: list[DetectedFace] = await loop.run_in_executor(executor, detector.detect_bytes, image_bytes)

    if not raw_faces:
        raise HTTPException(status_code=422, detail="No face detected in the uploaded image")

    best = raw_faces[0]  # highest confidence (sorted descending)
    matches = store.similarity_search(best.embedding, settings.match_threshold, limit=10)

    return MatchResponse(matches=[MatchResult(lychee_face_id=face_id, confidence=conf) for face_id, conf in matches])


# ---------------------------------------------------------------------------
# DELETE /embeddings
# ---------------------------------------------------------------------------


@router.delete("/embeddings")
async def delete_embeddings(
    body: DeleteEmbeddingsRequest,
    request: Request,
    _: None = Depends(require_api_key),
) -> DeleteEmbeddingsResponse:
    """Delete face embeddings by their Lychee Face IDs.

    Called by Lychee when dismissed faces are permanently removed or when
    a photo is deleted.
    """
    store: EmbeddingStore = get_store(request)
    deleted = store.delete_many(body.face_ids)
    return DeleteEmbeddingsResponse(deleted=deleted)


# ---------------------------------------------------------------------------
# POST /cluster
# ---------------------------------------------------------------------------


@router.post("/cluster")
async def cluster(
    request: Request,
    settings: AppSettings = Depends(get_settings),
    _: None = Depends(require_api_key),
) -> ClusterResponse:
    """Run DBSCAN clustering over all stored face embeddings.

    Reads every embedding from the store, clusters them, and returns the
    per-face cluster assignments. The PHP layer uses these to set
    ``faces.cluster_label`` for the cluster-review UI.
    """
    store: EmbeddingStore = get_store(request)
    all_embeddings = store.get_all()

    if not all_embeddings:
        return ClusterResponse(total_faces=0, num_clusters=0, assignments=[])

    clusterer = FaceClusterer(eps=settings.cluster_eps)
    results = clusterer.cluster(all_embeddings)

    assignments = [ClusterFaceResult(lychee_face_id=fid, cluster_label=label) for fid, label in results]
    distinct_labels = {label for _, label in results if label != -1}

    return ClusterResponse(
        total_faces=len(results),
        num_clusters=len(distinct_labels),
        assignments=assignments,
    )


# ---------------------------------------------------------------------------
# GET /health
# ---------------------------------------------------------------------------


@router.get("/health")
async def health(request: Request) -> HealthResponse:
    """Return service health, model-loaded status, and embedding count.

    This endpoint is intentionally unauthenticated so that load-balancers and
    Docker health checks can probe it without an API key.
    """
    detector: FaceDetector = request.app.state.detector
    store: EmbeddingStore = request.app.state.store

    return HealthResponse(
        status="ok" if detector.is_loaded else "degraded",
        model_loaded=detector.is_loaded,
        embedding_count=store.count(),
    )


# ---------------------------------------------------------------------------
# Background detection job
# ---------------------------------------------------------------------------


async def _run_detection_job(
    photo_id: str,
    image_path: Path,
    detector: FaceDetector,
    store: EmbeddingStore,
    executor: Executor,
    settings: AppSettings,
) -> None:
    """Detect faces, build the callback payload, and notify Lychee.

    Runs entirely as an async background task after the ``/detect`` route has
    returned 202.  All CPU-bound work is offloaded to ``executor`` via
    ``run_in_executor`` so the event loop remains responsive.
    """
    try:
        loop = asyncio.get_running_loop()

        # --- 1. Detect faces (CPU-bound, runs in thread pool) ---
        raw_faces: list[DetectedFace] = await loop.run_in_executor(executor, detector.detect, image_path)
        raw_faces = raw_faces[: settings.max_faces_per_photo]

        # --- 2. For each face: generate crop + search suggestions ---
        face_data: list[tuple[str, list[float], FaceResult]] = []

        for raw_face in raw_faces:
            emp_id = str(uuid.uuid4())

            crop_b64: str = await loop.run_in_executor(
                executor,
                generate_crop,
                image_path,
                raw_face.x,
                raw_face.y,
                raw_face.width,
                raw_face.height,
            )

            suggestions = store.similarity_search(raw_face.embedding, settings.match_threshold, limit=10)

            result = FaceResult(
                x=raw_face.x,
                y=raw_face.y,
                width=raw_face.width,
                height=raw_face.height,
                confidence=raw_face.confidence,
                embedding_id=emp_id,
                crop=crop_b64,
                suggestions=[SuggestionResult(lychee_face_id=fid, confidence=conf) for fid, conf in suggestions],
            )
            face_data.append((emp_id, raw_face.embedding, result))

        # --- 3. POST success callback to Lychee ---
        payload = DetectCallbackPayload(
            photo_id=photo_id,
            faces=[fd[2] for fd in face_data],
        )
        callback_url = f"{settings.lychee_api_url}/api/v2/FaceDetection/results"

        async with httpx.AsyncClient() as client:
            response = await client.post(
                callback_url,
                json=payload.model_dump(),
                headers={
                    "X-API-Key": settings.api_key,
                    "Content-Type": "application/json",
                    "Accept": "application/json",
                },
                timeout=30.0,
            )
            response.raise_for_status()
            callback_resp = DetectCallbackResponse.model_validate(response.json())

        # --- 4. Persist embeddings now that we have stable lychee_face_ids ---
        id_to_vector: dict[str, list[float]] = {eid: vec for eid, vec, _ in face_data}
        for mapping in callback_resp.faces:
            vec = id_to_vector.get(mapping.embedding_id)
            if vec is not None:
                store.add(mapping.lychee_face_id, vec)

    except Exception:
        logger.exception("Detection job failed for photo_id=%s; sending error callback", photo_id)
        await _send_error_callback(photo_id, "internal_error", "Detection pipeline failed", settings)


async def _send_error_callback(photo_id: str, error_code: str, message: str, settings: AppSettings) -> None:
    """Best-effort POST of an error callback to Lychee."""
    payload = ErrorCallbackPayload(photo_id=photo_id, error_code=error_code, message=message)
    callback_url = f"{settings.lychee_api_url}/api/v2/FaceDetection/results"
    try:
        async with httpx.AsyncClient() as client:
            await client.post(
                callback_url,
                json=payload.model_dump(),
                headers={
                    "X-API-Key": settings.api_key,
                    "Content-Type": "application/json",
                    "Accept": "application/json",
                },
                timeout=10.0,
            )
    except Exception:
        logger.exception("Failed to send error callback for photo_id=%s", photo_id)
