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
	type AlbumTree,
	type AugmentedAlbumTree,
	type ModifiedAlbums,
	type ErrorDescriptor,
} from "@lychee-org/nested-set-checker-wasm";

export type { AlbumTree, AugmentedAlbumTree, ModifiedAlbums };

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

// Builds the struct-of-arrays source `prepareAlbums` expects, sorted by `_lft`, from the
// current (possibly user-edited) `albums`. Only the per-field arrays are permuted by index
// here — no per-album object is ever materialized.
function sortedByLft(current: AugmentedAlbumTree): AlbumTree {
	const order = Array.from(current.id.keys()).sort((a, b) => current._lft[a] - current._lft[b]);
	return {
		id: order.map((i) => current.id[i]),
		title: order.map((i) => current.title[i]),
		parent_id: order.map((i) => current.parent_id[i]),
		_lft: Int32Array.from(order, (i) => current._lft[i]),
		_rgt: Int32Array.from(order, (i) => current._rgt[i]),
	};
}

export function useTreeOperations(originalAlbums: Ref<AlbumTree | undefined>, albums: Ref<AugmentedAlbumTree | undefined>, toast: ToastLike) {
	const isValidated = ref(false);
	const errors = ref<string[]>([]);

	async function prepareAlbums(sourceAlbums?: AlbumTree) {
		// Use provided source, or fall back to originalAlbums for initial load
		const source = sourceAlbums ?? originalAlbums.value;
		if (source === undefined) {
			return;
		}

		await ensureWasm();
		const result = wasmPrepareAlbums(source);

		albums.value = result.albums;
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
		void prepareAlbums(sortedByLft(albums.value)).then(() => {
			errors.value.forEach((e) => toast.add({ severity: "error", summary: trans("toasts.error"), detail: e, life: 3000 }));
		});
	}

	function incrementLft(id: string) {
		if (albums.value === undefined) {
			return;
		}
		albums.value = wasmIncrementLft(albums.value, id);
	}

	function incrementRgt(id: string) {
		if (albums.value === undefined) {
			return;
		}
		albums.value = wasmIncrementRgt(albums.value, id);
	}

	function decrementLft(id: string) {
		if (albums.value === undefined) {
			return;
		}
		albums.value = wasmDecrementLft(albums.value, id);
	}

	function decrementRgt(id: string) {
		if (albums.value === undefined) {
			return;
		}
		albums.value = wasmDecrementRgt(albums.value, id);
	}

	function getModifiedAlbums(): ModifiedAlbums {
		if (albums.value === undefined || originalAlbums.value === undefined) {
			return { id: [], _lft: new Int32Array(), _rgt: new Int32Array(), parent_id: [] };
		}
		return wasmGetModifiedAlbums(albums.value, originalAlbums.value);
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
