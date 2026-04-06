"""Tests for the SQLite embedding store."""

from __future__ import annotations

import math
from typing import TYPE_CHECKING

import pytest

from app.embeddings.sqlite_store import SQLiteEmbeddingStore

if TYPE_CHECKING:
    from pathlib import Path

# ---------------------------------------------------------------------------
# Fixtures
# ---------------------------------------------------------------------------


@pytest.fixture
def store(tmp_path: Path) -> SQLiteEmbeddingStore:
    """Return a fresh SQLite store backed by a temp directory."""
    return SQLiteEmbeddingStore(storage_path=str(tmp_path))


def _unit_vec(dim: int = 512, index: int = 0) -> list[float]:
    """Return a unit vector with a 1.0 at ``index`` and 0.0 elsewhere."""
    v = [0.0] * dim
    v[index] = 1.0
    return v


# ---------------------------------------------------------------------------
# add / count
# ---------------------------------------------------------------------------


def test_add_increments_count(store: SQLiteEmbeddingStore) -> None:
    assert store.count() == 0
    store.add("face-1", _unit_vec(index=0))
    assert store.count() == 1
    store.add("face-2", _unit_vec(index=1))
    assert store.count() == 2


def test_add_upsert_does_not_duplicate(store: SQLiteEmbeddingStore) -> None:
    """Re-adding an existing lychee_face_id must not create a duplicate row."""
    store.add("face-1", _unit_vec(index=0))
    store.add("face-1", _unit_vec(index=0))
    assert store.count() == 1


# ---------------------------------------------------------------------------
# delete
# ---------------------------------------------------------------------------


def test_delete_removes_entry(store: SQLiteEmbeddingStore) -> None:
    store.add("face-1", _unit_vec(index=0))
    store.delete("face-1")
    assert store.count() == 0


def test_delete_unknown_id_is_noop(store: SQLiteEmbeddingStore) -> None:
    """Deleting a non-existent ID must not raise."""
    store.delete("nonexistent")
    assert store.count() == 0


# ---------------------------------------------------------------------------
# similarity_search
# ---------------------------------------------------------------------------


def test_similarity_search_returns_identical_face(store: SQLiteEmbeddingStore) -> None:
    """An exact match should have similarity ≈ 1.0."""
    vec = _unit_vec(index=0)
    store.add("face-1", vec)

    results = store.similarity_search(vec, threshold=0.9, limit=10)
    assert len(results) == 1
    lychee_id, sim = results[0]
    assert lychee_id == "face-1"
    assert sim == pytest.approx(1.0, abs=1e-4)


def test_similarity_search_excludes_below_threshold(store: SQLiteEmbeddingStore) -> None:
    """Results below ``threshold`` must be excluded."""
    store.add("face-1", _unit_vec(index=0))

    # Query with an orthogonal vector - cosine similarity = 0.0
    query = _unit_vec(index=1)
    results = store.similarity_search(query, threshold=0.5, limit=10)
    assert results == []


def test_similarity_search_respects_limit(store: SQLiteEmbeddingStore) -> None:
    """At most ``limit`` results should be returned."""
    for i in range(20):
        # All vectors point in roughly the same direction → high similarity
        v = [1.0 / math.sqrt(512)] * 512
        store.add(f"face-{i}", v)

    results = store.similarity_search([1.0 / math.sqrt(512)] * 512, threshold=0.0, limit=5)
    assert len(results) <= 5


def test_similarity_search_ordered_descending(store: SQLiteEmbeddingStore) -> None:
    """Results must be ordered by descending similarity."""
    store.add("face-exact", _unit_vec(index=0))
    store.add("face-close", [0.9, 0.1] + [0.0] * 510)

    # Normalise the close vector
    norm = math.sqrt(0.9**2 + 0.1**2)
    close = [0.9 / norm, 0.1 / norm] + [0.0] * 510
    store.add("face-close-n", close)

    results = store.similarity_search(_unit_vec(index=0), threshold=0.0, limit=10)
    sims = [r[1] for r in results]
    assert sims == sorted(sims, reverse=True)


def test_empty_store_returns_no_results(store: SQLiteEmbeddingStore) -> None:
    results = store.similarity_search(_unit_vec(), threshold=0.0, limit=10)
    assert results == []
