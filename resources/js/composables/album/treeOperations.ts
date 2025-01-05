import { set } from "@vueuse/core";
import { trans } from "laravel-vue-i18n";
import { ToastServiceMethods } from "primevue/toastservice";
import { sprintf } from "sprintf-js";
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

		errors.value = albums.value.filter(isError).map((a) => {
			if (a._lft === null || a._lft === 0) {
				return sprintf(trans("fix-tree.errors.invalid_left"), a.id.slice(0, 6)); // `Album ${a.id.slice(0, 6)} has an invalid left value.`;
			}
			if (a._rgt === null || a._rgt === 0) {
				return sprintf(trans("fix-tree.errors.invalid_right"), a.id.slice(0, 6)); // `Album ${a.id.slice(0, 6)} has an invalid right value.`;
			}
			if (a._lft >= a._rgt) {
				return sprintf(trans("fix-tree.errors.invalid_left_right"), a.id.slice(0, 6), a._lft, a._rgt); // `Album ${a.id.slice(0, 6)} has an invalid left/right values. Left should be strictly smaller than right: ${a._lft} < ${a._rgt}.`;
			}
			if (a.isDuplicate_lft) {
				return sprintf(trans("fix-tree.errors.duplicate_left"), a.id.slice(0, 6), a._lft); // `Album ${a.id.slice(0, 6)} has a duplicate left value ${a._lft}.`;
			}
			if (a.isDuplicate_rgt) {
				return sprintf(trans("fix-tree.errors.duplicate_right"), a.id.slice(0, 6), a._rgt); // `Album ${a.id.slice(0, 6)} has a duplicate right value  ${a._rgt}.`;
			}
			if (!a.isExpectedParentId) {
				return sprintf(trans("fix-tree.errors.parent"), a.id.slice(0, 6), a.parent_id ?? "root"); // `Album ${a.id.slice(0, 6)} has an unexpected parent id ${a.parent_id ?? "root"}.`;
			}
			return sprintf(trans("fix-tree.errors.unknown"), a.id.slice(0, 6)); // `Album ${a.id.slice(0, 6)} has an unknown error.`;
		});
	}

	function validate() {
		setErrors();

		return errors.value.length === 0;
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
	};
}
