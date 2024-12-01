import { computed, ref, Ref } from "vue";

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
) {
	const isValidated = ref(false);

	function validate() {
		if (albums.value === undefined) {
			return true;
		}

		const errors = albums.value.filter(
			(a) =>
				a._lft === null || a._rgt === null || a._lft === 0 || a._rgt === 0 || a.isDuplicate_lft || a.isDuplicate_rgt || !a.isExpectedParentId,
		);
		return errors.length === 0;
	}

	function prepareAlbums() {
		if (originalAlbums.value === undefined) {
			return;
		}

		albums.value = [];

		let pile = [] as AlbumPile[];
		for (let index = 0; index < originalAlbums.value.length; index++) {
			const album = originalAlbums.value[index];

			const trimmedId = album.id.slice(0, 6);
			const trimmedParentId = (album.parent_id ?? "root").slice(0, 6);
			const isDuplicate_lft = hasDuplicateLft(album);
			const isDuplicate_rgt = hasDuplicateRgt(album);

			let isExpectedParentId = true;
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
			} else {
				// We are a leaf
				let breakOut = pile.length === 0;
				let currentRgt = album._rgt;

				while (!breakOut) {
					const last = pile[pile.length - 1];
					if (last.rgt === currentRgt + 1) {
						// We are the last leaf of the album
						pile.pop();
						currentRgt = last.rgt;
						breakOut = pile.length === 0;
					} else {
						breakOut = true;
					}
				}
			}
		}

		isValidated.value = validate();
	}

	function hasDuplicateLft(album: App.Http.Resources.Diagnostics.AlbumTree): boolean {
		if (originalAlbums.value === undefined) {
			return false;
		}

		return originalAlbums.value.filter((a) => a._lft === album._lft || a._rgt === album._lft).length > 1;
	}

	function hasDuplicateRgt(album: App.Http.Resources.Diagnostics.AlbumTree): boolean {
		if (originalAlbums.value === undefined) {
			return false;
		}

		return originalAlbums.value.filter((a) => a._lft === album._rgt || a._rgt === album._rgt).length > 1;
	}

	function check() {
		originalAlbums.value = albums.value?.sort((a, b) => a._lft - b._lft);
		prepareAlbums();
	}

	// We increment all the nodes left and right by 1.
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

	return {
		isValidated,
		validate,
		prepareAlbums,
		check,
		incrementLft,
		incrementRgt,
		decrementLft,
		decrementRgt,
	};
}
