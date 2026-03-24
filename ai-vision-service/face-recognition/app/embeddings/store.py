"""Abstract EmbeddingStore protocol.

All embedding storage backends must conform to this interface so that the
application code is independent of the concrete storage engine chosen.
"""

from __future__ import annotations

from typing import Protocol, runtime_checkable


@runtime_checkable
class EmbeddingStore(Protocol):
    """Protocol for face embedding storage backends.

    Implementations must be thread-safe when called from concurrent request
    handlers.
    """

    def add(self, lychee_face_id: str, embedding: list[float]) -> None:
        """Persist an embedding, keyed by its Lychee Face ID.

        If an entry for ``lychee_face_id`` already exists, it is replaced.

        Args:
            lychee_face_id: Stable Lychee ``Face.id`` (string PK).
            embedding: 512-dimensional ArcFace float vector.
        """
        ...

    def delete(self, lychee_face_id: str) -> None:
        """Remove an embedding by Lychee Face ID.

        No-op if the ID is not found.

        Args:
            lychee_face_id: Stable Lychee ``Face.id`` to remove.
        """
        ...

    def similarity_search(
        self,
        embedding: list[float],
        threshold: float,
        limit: int = 10,
    ) -> list[tuple[str, float]]:
        """Return the most similar stored faces.

        Args:
            embedding: Query embedding vector.
            threshold: Minimum cosine-similarity score (0.0-1.0).
            limit: Maximum number of results to return.

        Returns:
            List of ``(lychee_face_id, similarity)`` tuples ordered by
            descending similarity score. Only entries above ``threshold``
            are included.
        """
        ...

    def delete_many(self, lychee_face_ids: list[str]) -> int:
        """Remove multiple embeddings by Lychee Face ID.

        Args:
            lychee_face_ids: List of Lychee ``Face.id`` strings to remove.

        Returns:
            Number of embeddings actually deleted (IDs not found are silently
            skipped).
        """
        ...

    def get_all(self) -> list[tuple[str, list[float]]]:
        """Return all stored embeddings.

        Returns:
            List of ``(lychee_face_id, embedding)`` pairs.  Used by the
            clustering endpoint to read the full dataset into memory.
        """
        ...

    def count(self) -> int:
        """Return the total number of stored embeddings."""
        ...
