import { trans } from "laravel-vue-i18n";
import { type ToastLike } from "@/composables/toast-contract";
import { sprintf } from "sprintf-js";
import { ref, type Ref } from "vue";
import init, {
	prepareAlbums as wasmPrepareAlbums,
	incrementLft as wasmIncrementLft,
	incrementRgt as wasmIncrementRgt,
	decrementLft as wasmDecrementLft,
	decrementRgt as wasmDecrementRgt,
	getModifiedAlbums as wasmGetModifiedAlbums,
	setPanicHook,
	type AlbumTree as WasmAlbumTree,
	type AugmentedAlbum as WasmAugmentedAlbum,
	type ErrorDescriptor,
} from "@lychee-org/nested-set-checker-wasm";

export type Augmented = {
	prefix: string;
	trimmedId: string;
	trimmedParentId: string;
	isDuplicate_rgt: boolean;
	isDuplicate_lft: boolean;
	isExpectedParentId: boolean;
};

export type AugmentedAlbum = App.Http.Resources.Diagnostics.AlbumTree & Augmented;

let wasmReady: Promise<void> | null = null;

// The Wasm module must be `init()`ed exactly once before any of its exports can be
// called. `prepareAlbums` awaits this; every other operation below assumes it has
// already resolved, which holds as long as callers always populate `albums` via
// `prepareAlbums` first (the same precondition the original composable had for `albums`
// being defined at all).
function ensureWasm(): Promise<void> {
	if (wasmReady === null) {
		wasmReady = init().then(() => setPanicHook());
	}
	return wasmReady;
}

const ERROR_TRANS_ARGS: Record<ErrorDescriptor["kind"], (e: ErrorDescriptor) => unknown[]> = {
	invalid_left: (e) => [e.trimmedId],
	invalid_right: (e) => [e.trimmedId],
	invalid_left_right: (e) => [e.trimmedId, e.lft, e.rgt],
	duplicate_left: (e) => [e.trimmedId, e.lft],
	duplicate_right: (e) => [e.trimmedId, e.rgt],
	parent: (e) => [e.trimmedId, e.parentId ?? "root"],
	unknown: (e) => [e.trimmedId],
};

// `ErrorDescriptor.kind` maps 1:1 onto the `fix-tree.errors.<kind>` translation keys;
// only the string lookup and interpolation happen here, not the decision of which error
// applies to a given row (that's `prepareAlbums`, in Rust/Wasm).
function formatError(e: ErrorDescriptor): string {
	return sprintf(trans(`fix-tree.errors.${e.kind}`), ...ERROR_TRANS_ARGS[e.kind](e));
}

export function useTreeOperations(
	originalAlbums: Ref<App.Http.Resources.Diagnostics.AlbumTree[] | undefined>,
	albums: Ref<AugmentedAlbum[] | undefined>,
	toast: ToastLike,
) {
	const isValidated = ref(false);
	const errors = ref<string[]>([]);

	async function prepareAlbums(sourceAlbums?: App.Http.Resources.Diagnostics.AlbumTree[]) {
		// Use provided source, or fall back to originalAlbums for initial load
		const source = sourceAlbums ?? originalAlbums.value;
		if (source === undefined) {
			return;
		}

		await ensureWasm();
		const result = wasmPrepareAlbums(source as WasmAlbumTree[]);

		albums.value = result.albums as AugmentedAlbum[];
		errors.value = result.errors.map(formatError);
		isValidated.value = result.isValid;
	}

	function validate() {
		return errors.value.length === 0;
	}

	function check() {
		if (albums.value === undefined) {
			return;
		}
		// Sort current albums and revalidate without overwriting the baseline
		const sortedAlbums = albums.value.slice().sort((a, b) => a._lft - b._lft);
		void prepareAlbums(sortedAlbums).then(() => {
			errors.value.forEach((e) => toast.add({ severity: "error", summary: trans("toasts.error"), detail: e, life: 3000 }));
		});
	}

	function incrementLft(id: string) {
		if (albums.value === undefined) {
			return;
		}
		albums.value = wasmIncrementLft(albums.value as WasmAugmentedAlbum[], id) as AugmentedAlbum[];
	}

	function incrementRgt(id: string) {
		if (albums.value === undefined) {
			return;
		}
		albums.value = wasmIncrementRgt(albums.value as WasmAugmentedAlbum[], id) as AugmentedAlbum[];
	}

	function decrementLft(id: string) {
		if (albums.value === undefined) {
			return;
		}
		albums.value = wasmDecrementLft(albums.value as WasmAugmentedAlbum[], id) as AugmentedAlbum[];
	}

	function decrementRgt(id: string) {
		if (albums.value === undefined) {
			return;
		}
		albums.value = wasmDecrementRgt(albums.value as WasmAugmentedAlbum[], id) as AugmentedAlbum[];
	}

	function getModifiedAlbums(): { id: string; _lft: number; _rgt: number; parent_id: string | null }[] {
		if (albums.value === undefined || originalAlbums.value === undefined) {
			return [];
		}
		return wasmGetModifiedAlbums(albums.value as WasmAlbumTree[], originalAlbums.value as WasmAlbumTree[]) as {
			id: string;
			_lft: number;
			_rgt: number;
			parent_id: string | null;
		}[];
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
		getModifiedAlbums,
	};
}
