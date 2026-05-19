"""Selfie-to-face similarity matching.

Accepts a selfie image (as raw bytes), detects the face, embeds it, and
returns the best matching Lychee Face IDs from the embedding store.
"""

from __future__ import annotations

from typing import TYPE_CHECKING

from app.api.schemas import MatchResponse, MatchResult

if TYPE_CHECKING:
    from app.detection.detector import FaceDetector
    from app.embeddings.store import EmbeddingStore


class FaceMatcher:
    """Encapsulates the selfie-match logic used by the ``POST /match`` route.

    Args:
        detector: Loaded :class:`~app.detection.detector.FaceDetector`.
        store: Configured :class:`~app.embeddings.store.EmbeddingStore`.
        threshold: Minimum cosine-similarity score for a match to be included.
    """

    def __init__(
        self,
        detector: FaceDetector,
        store: EmbeddingStore,
        threshold: float = 0.5,
    ) -> None:
        self._detector = detector
        self._store = store
        self._threshold = threshold

    def match(self, image_bytes: bytes, limit: int = 10) -> MatchResponse:
        """Run selfie matching.

        Args:
            image_bytes: Raw bytes of the uploaded selfie image.
            limit: Maximum number of matches to return.

        Returns:
            :class:`~app.api.schemas.MatchResponse` with matches ordered by
            descending confidence.  The list may be empty if no stored face
            exceeds the threshold.

        Raises:
            ValueError: If no face is detected in the selfie.
        """
        raw_faces = self._detector.detect_bytes(image_bytes)
        if not raw_faces:
            raise ValueError("No face detected in the selfie image.")

        # Use the highest-confidence face for matching.
        best = raw_faces[0]
        matches = self._store.similarity_search(best.embedding, self._threshold, limit=limit)

        return MatchResponse(
            matches=[MatchResult(lychee_face_id=face_id, confidence=conf) for face_id, conf in matches]
        )
