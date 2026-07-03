/**
 * Library-agnostic toast contract shared by v7 (PrimeVue's ToastServiceMethods,
 * which already satisfies this shape structurally) and v8 (useAppToast()) -
 * lets composables that only ever call `toast.add(...)` accept either without
 * being duplicated per tree. See Feature 049 T-049-08.
 */
export type ToastSeverity = "success" | "info" | "warn" | "error" | "secondary";

export interface ToastOptions {
	severity: ToastSeverity;
	summary: string;
	detail?: string;
	life?: number;
}

export interface ToastLike {
	add(options: ToastOptions): void;
}
