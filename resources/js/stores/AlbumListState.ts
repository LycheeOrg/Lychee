import { defineStore } from "pinia";
import AlbumListV3Service from "@/services/album-list-v3-service";

export type AlbumListRow = {
	id: string;
	title: string;
	_lft: number;
	_rgt: number;
	coverId: string | null;
};

export type AlbumTreeNode = AlbumListRow & {
	depth: number;
	children: AlbumTreeNode[];
};

function toRows(ids: string[], titles: string[], _lft: number[], _rgt: number[], coverIds: (string | null)[]): AlbumListRow[] {
	return ids.map((id, i) => ({ id, title: titles[i], _lft: _lft[i], _rgt: _rgt[i], coverId: coverIds[i] }));
}

// Nested-set stack reconstruction: rows are visited in ascending `lft` order; the stack holds
// the chain of currently-open ancestors. A stack entry whose `rgt` is already behind the new
// row's `lft` has closed and is popped; whatever remains on top (if anything) is the parent.
function buildTree(rows: AlbumListRow[]): AlbumTreeNode[] {
	const sorted = [...rows].sort((a, b) => a._lft - b._lft);
	const roots: AlbumTreeNode[] = [];
	const stack: AlbumTreeNode[] = [];

	for (const row of sorted) {
		const node: AlbumTreeNode = { ...row, depth: 0, children: [] };

		while (stack.length > 0 && stack[stack.length - 1]._rgt < node._lft) {
			stack.pop();
		}

		const parent = stack[stack.length - 1] as AlbumTreeNode | undefined;
		if (parent === undefined) {
			roots.push(node);
		} else {
			node.depth = parent.depth + 1;
			parent.children.push(node);
		}

		stack.push(node);
	}

	return roots;
}

function findAncestors(rows: AlbumListRow[], target: AlbumListRow): AlbumListRow[] {
	return rows.filter((r) => r.id !== target.id && r._lft < target._lft && r._rgt > target._rgt).sort((a, b) => a._lft - b._lft);
}

export type AlbumListStore = ReturnType<typeof useAlbumListStore>;

export const useAlbumListStore = defineStore("album-list-store", {
	state: () => ({
		ids: [] as string[],
		titles: [] as string[],
		_lft: [] as number[],
		_rgt: [] as number[],
		coverIds: [] as (string | null)[],
		isLoaded: false as boolean,
		error: undefined as unknown,
		_loadPromise: undefined as Promise<void> | undefined,
		// Bumped by `invalidate()` so a request started before it can't commit stale data
		// or leave `_loadPromise` pointing at a promise nobody will re-fetch through.
		_loadGeneration: 0 as number,
	}),
	actions: {
		/**
		 * Fetches the base-mode album list at most once per session (or since the last
		 * `invalidate()`); concurrent callers share the same in-flight request.
		 */
		ensureLoaded(): Promise<void> {
			if (this.isLoaded) {
				return Promise.resolve();
			}
			if (this._loadPromise !== undefined) {
				return this._loadPromise;
			}

			const generation = this._loadGeneration;
			this.error = undefined;
			const promise = AlbumListV3Service.getAlbums()
				.then((response) => {
					if (this._loadGeneration !== generation) {
						return;
					}
					this.ids = response.data.ids;
					this.titles = response.data.titles;
					this._lft = response.data._lft;
					this._rgt = response.data._rgt;
					this.coverIds = response.data.cover_ids;
					this.isLoaded = true;
				})
				.catch((error: unknown) => {
					if (this._loadGeneration !== generation) {
						return;
					}
					this.error = error;
				})
				.finally(() => {
					if (this._loadGeneration !== generation) {
						return;
					}
					this._loadPromise = undefined;
				});

			this._loadPromise = promise;
			return promise;
		},

		invalidate() {
			this.isLoaded = false;
			this._loadGeneration++;
			this._loadPromise = undefined;
		},
	},
	getters: {
		isLoading(state): boolean {
			return state._loadPromise !== undefined;
		},

		rows(state): AlbumListRow[] {
			return toRows(state.ids, state.titles, state._lft, state._rgt, state.coverIds);
		},

		tree(): AlbumTreeNode[] {
			return buildTree(this.rows);
		},

		/**
		 * Union, over every id in `rootIds`, of that album's own id plus every descendant
		 * (`lft`/`rgt` range strictly contained within it). `rootIds` may hold one id
		 * (single-album Move) or several (multi-album Merge) uniformly.
		 */
		getExcludedTargetIds(): (rootIds: string[]) => Set<string> {
			const rows = this.rows;
			return (rootIds: string[]): Set<string> => {
				const excluded = new Set<string>();
				const roots = rows.filter((r) => rootIds.includes(r.id));
				for (const root of roots) {
					excluded.add(root.id);
				}
				for (const row of rows) {
					if (excluded.has(row.id)) {
						continue;
					}
					if (roots.some((root) => row._lft > root._lft && row._rgt < root._rgt)) {
						excluded.add(row.id);
					}
				}
				return excluded;
			};
		},

		/** True iff no other album's range contains `albumId` — i.e. it has no ancestor. */
		isTopLevel(): (albumId: string) => boolean {
			const rows = this.rows;
			return (albumId: string): boolean => {
				const target = rows.find((r) => r.id === albumId);
				if (target === undefined) {
					return true;
				}
				return !rows.some((r) => r.id !== albumId && r._lft < target._lft && r._rgt > target._rgt);
			};
		},

		/** Full ancestor-chain path, ancestor titles joined by `/` then the album's own title. */
		buildBreadcrumb(): (albumId: string) => string {
			const rows = this.rows;
			return (albumId: string): string => {
				const target = rows.find((r) => r.id === albumId);
				if (target === undefined) {
					return "";
				}
				const ancestors = findAncestors(rows, target);
				return [...ancestors.map((a) => a.title), target.title].join("/");
			};
		},
	},
});
