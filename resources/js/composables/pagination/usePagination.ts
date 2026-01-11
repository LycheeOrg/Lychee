import { ref, computed, type Ref, type ComputedRef } from "vue";

/**
 * Pagination UI mode types supported by the application.
 * - infinite_scroll: Automatically loads more content as user scrolls
 * - load_more_button: Shows a button to manually load next page
 * - page_navigation: Traditional page-by-page navigation
 */
export type PaginationUIMode = "infinite_scroll" | "load_more_button" | "page_navigation";

/**
 * Pagination state managed by the composable.
 * All fields are reactive refs that track the current pagination status.
 */
export interface PaginationState {
	currentPage: Ref<number>;
	lastPage: Ref<number>;
	perPage: Ref<number>;
	total: Ref<number>;
	loading: Ref<boolean>;
}

/**
 * Return type of usePagination composable.
 * Provides both state refs and action methods for pagination control.
 */
export interface UsePaginationReturn {
	currentPage: Ref<number>;
	lastPage: Ref<number>;
	perPage: Ref<number>;
	total: Ref<number>;
	loading: Ref<boolean>;
	hasMore: ComputedRef<boolean>;
	remaining: ComputedRef<number>;
	loadMore: () => Promise<void>;
	goToPage: (page: number) => Promise<void>;
	reset: () => void;
}

/**
 * Composable for managing paginated data loading.
 * Handles state tracking, prevents duplicate requests, and provides convenient methods.
 *
 * @param loadFunction - Function to load data for a specific page. First param is page number,
 *                       second param indicates whether to append (true) or replace (false) data.
 * @param initialState - Optional initial values for pagination state
 * @returns Reactive pagination state and control methods
 */
export function usePagination(
	loadFunction: (page: number, append: boolean) => Promise<void>,
	initialState?: Partial<PaginationState>,
): UsePaginationReturn {
	const currentPage = ref(initialState?.currentPage?.value ?? 1);
	const lastPage = ref(initialState?.lastPage?.value ?? 0);
	const perPage = ref(initialState?.perPage?.value ?? 0);
	const total = ref(initialState?.total?.value ?? 0);
	const loading = ref(initialState?.loading?.value ?? false);

	// True when more pages are available to load
	const hasMore = computed(() => currentPage.value < lastPage.value);

	// Calculate how many items remain unloaded
	// Uses Math.max to handle edge case where loaded > total (shouldn't happen but defensive)
	const remaining = computed(() => {
		const loaded = currentPage.value * perPage.value;
		return Math.max(0, total.value - loaded);
	});

	/**
	 * Load the next page and append results to existing data.
	 * Guards against:
	 * - Loading beyond last page (hasMore check)
	 * - Duplicate requests while already loading (loading check)
	 *
	 * Used by infinite scroll and "Load More" button components.
	 */
	async function loadMore(): Promise<void> {
		if (!hasMore.value || loading.value) {
			return;
		}
		loading.value = true;
		try {
			// Pass append=true to merge with existing data
			await loadFunction(currentPage.value + 1, true);
			currentPage.value++;
		} finally {
			loading.value = false;
		}
	}

	/**
	 * Navigate to a specific page, replacing existing data.
	 * Guards against:
	 * - Invalid page numbers (< 1 or > lastPage)
	 * - Duplicate requests while already loading
	 *
	 * Used by page navigation component (when implemented).
	 */
	async function goToPage(page: number): Promise<void> {
		if (page < 1 || page > lastPage.value || loading.value) {
			return;
		}
		loading.value = true;
		try {
			// Pass append=false to replace existing data
			await loadFunction(page, false);
			currentPage.value = page;
		} finally {
			loading.value = false;
		}
	}

	function reset(): void {
		currentPage.value = 1;
		lastPage.value = 0;
		perPage.value = 0;
		total.value = 0;
		loading.value = false;
	}

	return {
		currentPage,
		lastPage,
		perPage,
		total,
		loading,
		hasMore,
		remaining,
		loadMore,
		goToPage,
		reset,
	};
}
