import { trans } from "laravel-vue-i18n";
import { type ToastServiceMethods } from "primevue/toastservice";
import { sprintf } from "sprintf-js";
import { ref, type Ref } from "vue";

export type Augmented = {
	prefix: string;
	trimmedId: string;
	trimmedParentId: string;
	isDuplicate_rgt: boolean;
	isDuplicate_lft: boolean;
	isExpectedParentId: boolean;
};

export type AlbumPile = {
	parentId: string;
	rgt: number;
};

export type AugmentedAlbum = App.Http.Resources.Diagnostics.AlbumTree & Augmented;

export function useTreeOperations(
	originalAlbums: Ref<App.Http.Resources.Diagnostics.AlbumTree[] | undefined>,
	albums: Ref<AugmentedAlbum[] | undefined>,
	toast: ToastServiceMethods,
) {
	const isValidated = ref(false);
	const errors = ref<string[]>([]);

	function isError(album: AugmentedAlbum): boolean {
		return (
			album._lft === null ||
			album._rgt === null ||
			album._lft === 0 ||
			album._rgt === 0 ||
			album.isDuplicate_lft ||
			album.isDuplicate_rgt ||
			!album.isExpectedParentId
		);
	}

	function setErrors(): void {
		if (albums.value === undefined) {
			errors.value = [];
			return;
		}

		const result: string[] = [];
		for (const a of albums.value) {
			if (!isError(a)) {
				continue;
			}
			const shortId = a.trimmedId;
			if (a._lft === null || a._lft === 0) {
				result.push(sprintf(trans("fix-tree.errors.invalid_left"), shortId));
			} else if (a._rgt === null || a._rgt === 0) {
				result.push(sprintf(trans("fix-tree.errors.invalid_right"), shortId));
			} else if (a._lft >= a._rgt) {
				result.push(sprintf(trans("fix-tree.errors.invalid_left_right"), shortId, a._lft, a._rgt));
			} else if (a.isDuplicate_lft) {
				result.push(sprintf(trans("fix-tree.errors.duplicate_left"), shortId, a._lft));
			} else if (a.isDuplicate_rgt) {
				result.push(sprintf(trans("fix-tree.errors.duplicate_right"), shortId, a._rgt));
			} else if (!a.isExpectedParentId) {
				result.push(sprintf(trans("fix-tree.errors.parent"), shortId, a.parent_id ?? "root"));
			} else {
				result.push(sprintf(trans("fix-tree.errors.unknown"), shortId));
			}
		}
		errors.value = result;
	}

	function validate() {
		setErrors();

		return errors.value.length === 0;
	}

	function prepareAlbums(sourceAlbums?: App.Http.Resources.Diagnostics.AlbumTree[]) {
		// Use provided source, or fall back to originalAlbums for initial load
		const source = sourceAlbums ?? originalAlbums.value;
		if (source === undefined) {
			return;
		}

		// Build duplicate detection sets in O(n) instead of O(n²)
		const { duplicateLfts, duplicateRgts } = buildDuplicateSets(source);

		albums.value = [];

		const pile = [] as AlbumPile[];
		for (const album of source) {
			const trimmedId = album.id.slice(0, 6);
			const trimmedParentId = (album.parent_id ?? "root").slice(0, 6);
			const isDuplicate_lft = duplicateLfts.has(album._lft);
			const isDuplicate_rgt = duplicateRgts.has(album._rgt);

			let isExpectedParentId = true;
			// If current lft is greater than the last rgt,
			// we are no longer a child of the last album. We pop out the pile until we are smaller than _rgt of the pile.
			while (pile.length > 0 && (album._lft > pile[pile.length - 1].rgt || album._rgt > pile[pile.length - 1].rgt)) {
				pile.pop();
			}

			if (pile.length > 0) {
				// We are inside an album
				const last = pile[pile.length - 1];
				isExpectedParentId = last.parentId === album.parent_id;
			} else {
				// We are at the root
				isExpectedParentId = album.parent_id === null;
			}

			albums.value.push({
				...album,
				prefix: "  │ ".repeat(pile.length),
				trimmedId,
				trimmedParentId,
				isDuplicate_lft,
				isDuplicate_rgt,
				isExpectedParentId,
			});

			if (album._rgt > album._lft + 1) {
				// We are a parent
				pile.push({ parentId: album.id, rgt: album._rgt });
			}
		}

		isValidated.value = validate();
	}

	/**
	 * Build sets of duplicate lft/rgt values in O(n).
	 * A value is duplicate if it appears more than once as either _lft or _rgt across all albums.
	 */
	function buildDuplicateSets(albumList: App.Http.Resources.Diagnostics.AlbumTree[]): {
		duplicateLfts: Set<number>;
		duplicateRgts: Set<number>;
	} {
		// Count how many times each value appears as _lft
		const lftCounts = new Map<number, number>();
		// Count how many times each value appears as _rgt
		const rgtCounts = new Map<number, number>();

		for (const album of albumList) {
			lftCounts.set(album._lft, (lftCounts.get(album._lft) ?? 0) + 1);
			rgtCounts.set(album._rgt, (rgtCounts.get(album._rgt) ?? 0) + 1);
		}

		// A _lft value is duplicate if: (count as _lft) + (count as _rgt) > 1
		// A _rgt value is duplicate if: (count as _lft) + (count as _rgt) > 1
		const duplicateLfts = new Set<number>();
		const duplicateRgts = new Set<number>();

		for (const album of albumList) {
			const lftTotal = (lftCounts.get(album._lft) ?? 0) + (rgtCounts.get(album._lft) ?? 0);
			if (lftTotal > 1) {
				duplicateLfts.add(album._lft);
			}

			const rgtTotal = (lftCounts.get(album._rgt) ?? 0) + (rgtCounts.get(album._rgt) ?? 0);
			if (rgtTotal > 1) {
				duplicateRgts.add(album._rgt);
			}
		}

		return { duplicateLfts, duplicateRgts };
	}

	function check() {
		if (albums.value === undefined) {
			return;
		}
		// Sort current albums and revalidate without overwriting the baseline
		const sortedAlbums = albums.value.sort((a, b) => a._lft - b._lft);
		prepareAlbums(sortedAlbums);
		errors.value.forEach((e) => toast.add({ severity: "error", summary: trans("toasts.error"), detail: e, life: 3000 }));
	}

	// We increment all the nodes' (>= lft) left and right by 1.
	function incrementLft(id: string) {
		if (albums.value === undefined) {
			return;
		}

		const lft = albums.value.find((a) => a.id === id)?._lft as number;

		albums.value = albums.value.map((a) => {
			if (a._lft < lft) {
				return a;
			}
			a._lft += 1;
			a._rgt += 1;
			return a;
		});
	}

	// We increment all the nodes above rgt by 1 and increment rgt by 1.
	function incrementRgt(id: string) {
		if (albums.value === undefined) {
			return;
		}

		const rgt = albums.value.find((a) => a.id === id)?._rgt as number;

		albums.value = albums.value.map((a) => {
			if (a._rgt < rgt) {
				return a;
			}
			if (a._rgt === rgt) {
				a._rgt += 1;
			} else {
				a._lft += 1;
				a._rgt += 1;
			}
			return a;
		});
	}

	// We decrement all the nodes above lft by 1.
	function decrementLft(id: string) {
		if (albums.value === undefined) {
			return;
		}

		const lft = albums.value.find((a) => a.id === id)?._lft as number;

		albums.value = albums.value.map((a) => {
			if (a._lft < lft) {
				return a;
			}
			a._lft -= 1;
			a._rgt -= 1;
			return a;
		});
	}

	// We decrement all the nodes above rgt by 1 and decrement rgt by 1 IF lft > rgt - 1.
	function decrementRgt(id: string) {
		if (albums.value === undefined) {
			return;
		}

		const rgt = albums.value.find((a) => a.id === id)?._rgt as number;

		albums.value = albums.value.map((a) => {
			if (a._rgt < rgt) {
				return a;
			}
			// safety check
			if (a._lft === rgt - 1) {
				return a;
			}
			if (a._rgt === rgt) {
				a._rgt -= 1;
			} else {
				a._lft += 1;
				a._rgt += 1;
			}
			return a;
		});
	}

	function getModifiedAlbums(): { id: string; _lft: number; _rgt: number; parent_id: string | null }[] {
		if (albums.value === undefined || originalAlbums.value === undefined) {
			return [];
		}

		const originalMap = new Map(originalAlbums.value.map((a) => [a.id, { _lft: a._lft, _rgt: a._rgt, parent_id: a.parent_id }]));

		return albums.value
			.filter((a) => {
				const original = originalMap.get(a.id);
				if (original === undefined) {
					return true; // New album, include it
				}
				return a._lft !== original._lft || a._rgt !== original._rgt || a.parent_id !== original.parent_id;
			})
			.map((a) => ({
				id: a.id,
				_lft: a._lft,
				_rgt: a._rgt,
				parent_id: a.parent_id,
			}));
	}

	return {
		isValidated,
		setErrors,
		validate,
		prepareAlbums,
		check,
		incrementLft,
		incrementRgt,
		decrementLft,
		decrementRgt,
		getModifiedAlbums,
	};
}
