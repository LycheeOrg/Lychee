import { defineStore } from "pinia";
import AlbumListV3Service from "@/services/album-list-v3-service";

export type AlbumListRow = {
	id: string;
	title: string;
	lft: number;
	rgt: number;
	coverId: string | null;
};

export type AlbumTreeNode = AlbumListRow & {
	depth: number;
	children: AlbumTreeNode[];
};

function toRows(ids: string[], titles: string[], lft: number[], rgt: number[], coverIds: (string | null)[]): AlbumListRow[] {
	return ids.map((id, i) => ({ id, title: titles[i], lft: lft[i], rgt: rgt[i], coverId: coverIds[i] }));
}

// Nested-set stack reconstruction: rows are visited in ascending `lft` order; the stack holds
// the chain of currently-open ancestors. A stack entry whose `rgt` is already behind the new
// row's `lft` has closed and is popped; whatever remains on top (if anything) is the parent.
function buildTree(rows: AlbumListRow[]): AlbumTreeNode[] {
	const sorted = [...rows].sort((a, b) => a.lft - b.lft);
	const roots: AlbumTreeNode[] = [];
	const stack: AlbumTreeNode[] = [];

	for (const row of sorted) {
		const node: AlbumTreeNode = { ...row, depth: 0, children: [] };

		while (stack.length > 0 && stack[stack.length - 1].rgt < node.lft) {
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
	return rows.filter((r) => r.id !== target.id && r.lft < target.lft && r.rgt > target.rgt).sort((a, b) => a.lft - b.lft);
}

export type AlbumListStore = ReturnType<typeof useAlbumListStore>;

export const useAlbumListStore = defineStore("album-list-store", {
	state: () => ({
		ids: [] as string[],
		titles: [] as string[],
		lft: [] as number[],
		rgt: [] as number[],
		coverIds: [] as (string | null)[],
		isLoading: false as boolean,
		isLoaded: false as boolean,
		error: undefined as unknown,
		_loadPromise: undefined as Promise<void> | undefined,
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

			this.isLoading = true;
			this.error = undefined;
			const promise = AlbumListV3Service.getAlbums()
				.then((response) => {
					this.ids = response.data.ids;
					this.titles = response.data.titles;
					this.lft = response.data.lft;
					this.rgt = response.data.rgt;
					this.coverIds = response.data.cover_ids;
					this.isLoaded = true;
				})
				.catch((error: unknown) => {
					this.error = error;
				})
				.finally(() => {
					this.isLoading = false;
					this._loadPromise = undefined;
				});

			this._loadPromise = promise;
			return promise;
		},

		invalidate() {
			this.isLoaded = false;
		},
	},
	getters: {
		rows(state): AlbumListRow[] {
			return toRows(state.ids, state.titles, state.lft, state.rgt, state.coverIds);
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
					if (roots.some((root) => row.lft > root.lft && row.rgt < root.rgt)) {
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
				return !rows.some((r) => r.id !== albumId && r.lft < target.lft && r.rgt > target.rgt);
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
