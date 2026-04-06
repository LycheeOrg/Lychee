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
from fastapi.responses import RedirectResponse

from app.api.dependencies import get_detector, get_store, require_api_key
from app.api.schemas import (
    ClusterCallbackPayload,
    ClusterFaceResult,
    ClusterSuggestion,
    DeleteEmbeddingsRequest,
    DeleteEmbeddingsResponse,
    DetectCallbackPayload,
    DetectCallbackResponse,
    DetectRequest,
    EmbeddingExportItem,
    EmbeddingExportResponse,
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
# GET / - Redirect to /health
# ---------------------------------------------------------------------------


@router.get("/")
async def root() -> RedirectResponse:
    """Redirect root to /health endpoint."""
    return RedirectResponse(url="/health")


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

    logger.info("Processing selfie match request (%d bytes)", len(image_bytes))

    loop = asyncio.get_running_loop()
    raw_faces: list[DetectedFace] = await loop.run_in_executor(executor, detector.detect_bytes, image_bytes)

    if not raw_faces:
        logger.warning("No face detected in uploaded selfie image")
        raise HTTPException(status_code=422, detail="No face detected in the uploaded image")

    best = raw_faces[0]  # highest confidence (sorted descending)
    matches = store.similarity_search(best.embedding, settings.match_threshold, limit=10)

    logger.info(
        "Selfie match found %d match(es) above threshold %.2f (detected face confidence: %.3f)",
        len(matches),
        settings.match_threshold,
        best.confidence,
    )

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


@router.get("/embeddings/export")
async def export_embeddings(
    request: Request,
    _: None = Depends(require_api_key),
) -> EmbeddingExportResponse:
    """Export all face embeddings with metadata for synchronization.

    Called by Lychee maintenance to re-sync face data after callback failures
    or to verify database consistency.
    """
    store: EmbeddingStore = get_store(request)
    all_data = store.get_all_with_metadata()

    items = [
        EmbeddingExportItem(
            lychee_face_id=row["lychee_face_id"],
            photo_id=row["photo_id"],
            laplacian_variance=row["laplacian_variance"],
            crop_path=row["crop_path"],
        )
        for row in all_data
    ]

    return EmbeddingExportResponse(count=len(items), embeddings=items)


# ---------------------------------------------------------------------------
# POST /cluster
# ---------------------------------------------------------------------------


@router.post("/cluster", status_code=202)
async def cluster(
    background_tasks: BackgroundTasks,
    request: Request,
    settings: AppSettings = Depends(get_settings),
    _: None = Depends(require_api_key),
) -> None:
    """Run DBSCAN clustering over all stored face embeddings.

    Immediately returns **202 Accepted** and schedules clustering as a background
    task. Results are POSTed back to Lychee's clustering results endpoint once
    clustering completes.
    """
    store: EmbeddingStore = get_store(request)
    executor: Executor = request.app.state.executor

    background_tasks.add_task(
        _run_clustering_job,
        store,
        executor,
        settings,
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
    logger.info("Starting detection job for photo_id=%s, path=%s", photo_id, image_path)
    try:
        loop = asyncio.get_running_loop()

        # --- 1. Detect faces (CPU-bound, runs in thread pool) ---
        raw_faces: list[DetectedFace] = await loop.run_in_executor(executor, detector.detect, image_path)

        if len(raw_faces) > settings.max_faces_per_photo:
            logger.info(
                "Limiting faces from %d to %d (max_faces_per_photo setting)",
                len(raw_faces),
                settings.max_faces_per_photo,
            )
        raw_faces = raw_faces[: settings.max_faces_per_photo]

        if not raw_faces:
            logger.info("No faces detected in photo_id=%s, sending empty results", photo_id)

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

            if suggestions:
                logger.debug(
                    "Found %d suggestion(s) for face with confidence=%.3f",
                    len(suggestions),
                    raw_face.confidence,
                )

            result = FaceResult(
                x=raw_face.x,
                y=raw_face.y,
                width=raw_face.width,
                height=raw_face.height,
                confidence=raw_face.confidence,
                embedding_id=emp_id,
                crop=crop_b64,
                laplacian_variance=raw_face.laplacian_variance,
                suggestions=[SuggestionResult(lychee_face_id=fid, confidence=conf) for fid, conf in suggestions],
            )
            face_data.append((emp_id, raw_face.embedding, result))

        # --- 3. POST success callback to Lychee ---
        payload = DetectCallbackPayload(
            photo_id=photo_id,
            faces=[fd[2] for fd in face_data],
        )
        callback_url = f"{settings.lychee_api_url}/api/v2/FaceDetection/results"

        async with httpx.AsyncClient(verify=settings.verify_ssl) as client:
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

        logger.info(
            "Successfully sent detection results to Lychee for photo_id=%s (%d face(s))",
            photo_id,
            len(face_data),
        )

        # --- 4. Persist embeddings + crops now that we have stable lychee_face_ids ---
        id_to_data: dict[str, tuple[list[float], FaceResult]] = {eid: (vec, res) for eid, vec, res in face_data}
        crop_dir = Path("data/faces")
        crop_dir.mkdir(parents=True, exist_ok=True)

        for mapping in callback_resp.faces:
            data = id_to_data.get(mapping.embedding_id)
            if data is not None:
                vec, face_result = data
                lychee_face_id = mapping.lychee_face_id

                # Save face crop to disk
                crop_path = f"faces/{lychee_face_id}.jpg"
                crop_file = crop_dir / f"{lychee_face_id}.jpg"
                import base64
                crop_bytes = base64.b64decode(face_result.crop)
                crop_file.write_bytes(crop_bytes)

                # Persist embedding with metadata
                store.add(
                    lychee_face_id=lychee_face_id,
                    embedding=vec,
                    photo_id=photo_id,
                    laplacian_variance=face_result.laplacian_variance,
                    crop_path=crop_path,
                )

    except Exception:
        logger.exception("Detection job failed for photo_id=%s; sending error callback", photo_id)
        await _send_error_callback(photo_id, "internal_error", "Detection pipeline failed", settings)


async def _send_error_callback(photo_id: str, error_code: str, message: str, settings: AppSettings) -> None:
    """Best-effort POST of an error callback to Lychee."""
    payload = ErrorCallbackPayload(photo_id=photo_id, error_code=error_code, message=message)
    callback_url = f"{settings.lychee_api_url}/api/v2/FaceDetection/results"
    try:
        async with httpx.AsyncClient(verify=settings.verify_ssl) as client:
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


# ---------------------------------------------------------------------------
# Background clustering job
# ---------------------------------------------------------------------------


async def _run_clustering_job(
    store: EmbeddingStore,
    executor: Executor,
    settings: AppSettings,
) -> None:
    """Run DBSCAN clustering and notify Lychee with results.

    Runs entirely as an async background task after the ``/cluster`` route has
    returned 202. CPU-bound clustering work is offloaded to ``executor`` via
    ``run_in_executor`` so the event loop remains responsive.
    """
    try:
        # --- 1. Fetch all embeddings from store ---
        all_embeddings = store.get_all()

        if not all_embeddings:
            # Send success callback with empty results
            payload = ClusterCallbackPayload(labels=[])
            await _send_cluster_callback(payload, settings)
            return

        # --- 2. Run DBSCAN clustering (CPU-bound, runs in thread pool) ---
        loop = asyncio.get_running_loop()
        clusterer = FaceClusterer(eps=settings.cluster_eps)
        results: list[tuple[str, int]] = await loop.run_in_executor(
            executor,
            clusterer.cluster,
            all_embeddings,
        )

        # --- 3. Build cluster label assignments ---
        labels = [ClusterFaceResult(face_id=fid, cluster_label=label) for fid, label in results]

        # --- 4. Generate cross-cluster suggestions ---
        # Only include face_ids that exist in cluster_results to pass PHP's exists:faces,id validation
        valid_face_ids = {fid for fid, _ in results}
        suggestions = _generate_cross_cluster_suggestions(
            results,
            all_embeddings,
            store,
            valid_face_ids,
            settings.match_threshold,
        )

        payload = ClusterCallbackPayload(labels=labels, suggestions=suggestions)

        # --- 5. POST success callback to Lychee ---
        await _send_cluster_callback(payload, settings)

    except Exception:
        logger.exception("Clustering job failed; sending empty results to Lychee")
        # PHP endpoint doesn't handle error payloads, so send empty results
        try:
            empty_payload = ClusterCallbackPayload(labels=[])
            await _send_cluster_callback(empty_payload, settings)
        except Exception:
            logger.exception("Failed to send fallback empty clustering results")


async def _send_cluster_callback(payload: ClusterCallbackPayload, settings: AppSettings) -> None:
    """POST clustering results to Lychee."""
    callback_url = f"{settings.lychee_api_url}/api/v2/FaceDetection/cluster-results"
    try:
        async with httpx.AsyncClient(verify=settings.verify_ssl) as client:
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
    except Exception:
        logger.exception("Failed to send clustering callback")
        raise


def _generate_cross_cluster_suggestions(
    cluster_results: list[tuple[str, int]],
    all_embeddings: list[tuple[str, list[float]]],
    store: EmbeddingStore,
    valid_face_ids: set[str],
    threshold: float,
    max_per_face: int = 3,
) -> list[ClusterSuggestion]:
    """Generate cross-cluster face suggestions for UI review.

    For each clustered face, find similar faces from different clusters
    that are above the similarity threshold. This helps identify potential
    mis-clusterings and allows manual review.

    Args:
        cluster_results: List of (face_id, cluster_label) tuples
        all_embeddings: List of (face_id, embedding) tuples
        store: Embedding store for similarity search
        valid_face_ids: Set of face IDs that exist in Lychee's database
        threshold: Minimum similarity threshold
        max_per_face: Maximum suggestions per face
    """
    suggestions = []
    face_to_cluster = {fid: label for fid, label in cluster_results}
    embedding_map = {fid: emb for fid, emb in all_embeddings}

    for face_id, cluster_label in cluster_results:
        # Skip noise points (cluster_label == -1)
        if cluster_label == -1:
            continue

        embedding = embedding_map.get(face_id)
        if embedding is None:
            continue

        # Find similar faces from the embedding store
        matches = store.similarity_search(embedding, threshold, limit=max_per_face + 10)

        # Filter to only faces from different clusters that exist in Lychee's database
        for suggested_face_id, confidence in matches:
            # Skip if suggested face doesn't exist in Lychee's database
            if suggested_face_id not in valid_face_ids:
                continue

            suggested_cluster = face_to_cluster.get(suggested_face_id)
            if suggested_cluster is not None and suggested_cluster != cluster_label and suggested_cluster != -1:
                suggestions.append(
                    ClusterSuggestion(
                        face_id=face_id,
                        suggested_face_id=suggested_face_id,
                        confidence=float(confidence),  # Ensure it's a Python float, not numpy
                    )
                )
                if len([s for s in suggestions if s.face_id == face_id]) >= max_per_face:
                    break

    return suggestions
