import { useToast } from "@nuxt/ui/composables/useToast";
import type { ToastLike, ToastOptions, ToastSeverity } from "@/composables/toast-contract";

/**
 * Wraps Nuxt UI's useToast() with the app's existing PrimeVue-era call shape
 * (severity/summary/detail/life), so v8 call sites keep the same external
 * contract as v7's PrimeVue-toast-service usage - see Feature 049 FR-049-04,
 * DO-049-01.
 */
const SEVERITY_TO_COLOR: Record<ToastSeverity, "success" | "info" | "warning" | "error" | "secondary"> = {
	success: "success",
	info: "info",
	warn: "warning",
	error: "error",
	secondary: "secondary",
};

export function useAppToast(): ToastLike {
	const toast = useToast();

	function add(options: ToastOptions): void {
		toast.add({
			color: SEVERITY_TO_COLOR[options.severity],
			title: options.summary,
			description: options.detail,
			duration: options.life,
		});
	}

	return { add };
}
