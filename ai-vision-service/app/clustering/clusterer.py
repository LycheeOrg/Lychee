"""Face embedding clustering using scikit-learn DBSCAN.

Groups stored face embeddings into clusters automatically, without requiring
the number of clusters to be specified in advance. Each cluster corresponds
to a likely distinct identity; noise points (label ``-1``) are unassigned.
"""

from __future__ import annotations

import numpy as np


class FaceClusterer:
    """Clusters face embeddings with DBSCAN.

    DBSCAN (Density-Based Spatial Clustering of Applications with Noise) is
    well-suited for face grouping because:
    - The number of clusters does not need to be specified up front.
    - Noise / outlier faces are marked with label ``-1`` rather than forced
      into a cluster.
    - It works on cosine distance natively when ``metric="cosine"``.

    Args:
        eps: Maximum cosine *distance* (1 - similarity) between two samples
            for them to be considered neighbours. Lower values mean tighter
            clusters.
        min_samples: Minimum number of samples in a neighbourhood to form a
            core point. Set to ``1`` so that even a single unique face gets
            its own cluster (rather than being labelled noise).
    """

    def __init__(self, eps: float = 0.4, min_samples: int = 1) -> None:
        self._eps = eps
        self._min_samples = min_samples

    def cluster(self, face_embeddings: list[tuple[str, list[float]]]) -> list[tuple[str, int]]:
        """Cluster the supplied embeddings.

        Args:
            face_embeddings: List of ``(lychee_face_id, embedding)`` pairs.
                Each embedding is a 512-dimensional float list.

        Returns:
            List of ``(lychee_face_id, cluster_label)`` pairs in the same
            order as the input.  ``cluster_label == -1`` means the face was
            classified as noise.

        Returns an empty list when ``face_embeddings`` is empty.
        """
        if not face_embeddings:
            return []

        from sklearn.cluster import DBSCAN

        ids = [fid for fid, _ in face_embeddings]
        vectors = np.array([emb for _, emb in face_embeddings], dtype=np.float32)

        # Normalise vectors to unit length so that Euclidean distance equals
        # sqrt(2 * (1 - cosine_similarity)). DBSCAN's cosine metric directly
        # uses (1 - cosine_similarity) as the distance measure.
        norms: np.ndarray = np.linalg.norm(vectors, axis=1, keepdims=True)
        # Avoid division by zero for zero-norm vectors
        norms = np.where(norms == 0, 1.0, norms)
        vectors = vectors / norms

        db = DBSCAN(eps=self._eps, min_samples=self._min_samples, metric="cosine")
        labels: list[int] = db.fit_predict(vectors).tolist()

        return list(zip(ids, labels, strict=True))
