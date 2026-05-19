"""Tests for app.clustering.clusterer."""

from __future__ import annotations

import math

from app.clustering.clusterer import FaceClusterer

# ---------------------------------------------------------------------------
# Helpers
# ---------------------------------------------------------------------------


def _unit_vec(index: int = 0, dim: int = 512) -> list[float]:
    v = [0.0] * dim
    v[index] = 1.0
    return v


def _similar_vec(base_index: int, noise_index: int, noise: float = 0.05) -> list[float]:
    """Return a vector close to the unit-vector at ``base_index``."""
    v = [0.0] * 512
    v[base_index] = 1.0
    v[noise_index] = noise
    norm = math.sqrt(1.0 + noise**2)
    return [x / norm for x in v]


# ---------------------------------------------------------------------------
# FaceClusterer.cluster()
# ---------------------------------------------------------------------------


def test_empty_input_returns_empty() -> None:
    clusterer = FaceClusterer()
    assert clusterer.cluster([]) == []


def test_single_face_gets_cluster_zero() -> None:
    """With min_samples=1 a single face should form cluster 0 (not noise)."""
    clusterer = FaceClusterer(eps=0.4, min_samples=1)
    result = clusterer.cluster([("face-1", _unit_vec(0))])
    assert result == [("face-1", 0)]


def test_identical_faces_in_same_cluster() -> None:
    """Two identical embeddings must land in the same cluster."""
    clusterer = FaceClusterer(eps=0.4, min_samples=1)
    vec = _unit_vec(0)
    result = clusterer.cluster([("face-1", vec.copy()), ("face-2", vec.copy())])
    labels = {fid: label for fid, label in result}
    assert labels["face-1"] == labels["face-2"]


def test_distinct_faces_in_different_clusters() -> None:
    """Orthogonal embeddings must land in separate clusters with tight eps."""
    clusterer = FaceClusterer(eps=0.1, min_samples=1)
    result = clusterer.cluster(
        [
            ("face-a", _unit_vec(0)),
            ("face-b", _unit_vec(1)),
            ("face-c", _unit_vec(2)),
        ]
    )
    labels = {fid: label for fid, label in result}
    # All three should be in different clusters (no two share a label)
    assert len(set(labels.values())) == 3


def test_similar_faces_grouped_together() -> None:
    """Slightly varied embeddings of the same person must form one cluster."""
    base = _unit_vec(0)
    similar_1 = _similar_vec(0, 10, noise=0.05)
    similar_2 = _similar_vec(0, 20, noise=0.05)
    clusterer = FaceClusterer(eps=0.3, min_samples=1)
    result = clusterer.cluster(
        [
            ("face-1", base),
            ("face-2", similar_1),
            ("face-3", similar_2),
        ]
    )
    labels = {fid: label for fid, label in result}
    assert labels["face-1"] == labels["face-2"] == labels["face-3"]


def test_output_preserves_order() -> None:
    """Output IDs must appear in the same order as the input."""
    clusterer = FaceClusterer()
    ids = [f"face-{i}" for i in range(10)]
    embeddings = [(fid, _unit_vec(i % 512)) for i, fid in enumerate(ids)]
    result = clusterer.cluster(embeddings)
    assert [fid for fid, _ in result] == ids


def test_two_clusters_noise_excluded() -> None:
    """With min_samples=2 an isolated point should get label -1 (noise)."""
    clusterer = FaceClusterer(eps=0.1, min_samples=2)
    result = clusterer.cluster(
        [
            ("alice-1", _unit_vec(0)),
            ("alice-2", _similar_vec(0, 10, noise=0.02)),
            ("noise", _unit_vec(1)),  # isolated, not enough neighbours
        ]
    )
    labels = {fid: label for fid, label in result}
    # alice-1 and alice-2 should be in the same cluster
    assert labels["alice-1"] == labels["alice-2"]
    assert labels["alice-1"] != -1
    # noise face should be labelled as noise
    assert labels["noise"] == -1
