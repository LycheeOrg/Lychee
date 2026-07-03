import { reactive } from "vue";

/**
 * Promise-based confirm dialog, backed by a singleton ConfirmModalHost.vue
 * mounted once in v8/views/App.vue - replaces PrimeVue's useConfirm()/
 * <ConfirmDialog> for the v8 tree - see Feature 049 FR-049-05, DO-049-02.
 */
export interface ConfirmDialogOptions {
	title: string;
	message: string;
	acceptLabel?: string;
	rejectLabel?: string;
	severity?: "danger" | "warning" | "info";
}

interface ConfirmDialogState {
	open: boolean;
	options: ConfirmDialogOptions;
	resolve: ((value: boolean) => void) | null;
}

export const confirmDialogState: ConfirmDialogState = reactive({
	open: false,
	options: { title: "", message: "" },
	resolve: null,
});

export function useConfirmDialog() {
	function confirm(options: ConfirmDialogOptions): Promise<boolean> {
		return new Promise<boolean>((resolve) => {
			confirmDialogState.resolve?.(false);
			confirmDialogState.options = options;
			confirmDialogState.resolve = resolve;
			confirmDialogState.open = true;
		});
	}

	return { confirm };
}

export function settleConfirmDialog(value: boolean): void {
	confirmDialogState.resolve?.(value);
	confirmDialogState.resolve = null;
	confirmDialogState.open = false;
}
