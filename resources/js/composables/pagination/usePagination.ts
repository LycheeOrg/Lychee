import { ref, computed, type Ref, type ComputedRef } from "vue";

export type PaginationUIMode = "infinite_scroll" | "load_more_button" | "page_navigation";

export interface PaginationState {
	currentPage: Ref<number>;
	lastPage: Ref<number>;
	perPage: Ref<number>;
	total: Ref<number>;
	loading: Ref<boolean>;
}

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

export function usePagination(
	loadFunction: (page: number, append: boolean) => Promise<void>,
	initialState?: Partial<PaginationState>,
): UsePaginationReturn {
	const currentPage = ref(initialState?.currentPage?.value ?? 1);
	const lastPage = ref(initialState?.lastPage?.value ?? 0);
	const perPage = ref(initialState?.perPage?.value ?? 0);
	const total = ref(initialState?.total?.value ?? 0);
	const loading = ref(initialState?.loading?.value ?? false);

	const hasMore = computed(() => currentPage.value < lastPage.value);

	const remaining = computed(() => {
		const loaded = currentPage.value * perPage.value;
		return Math.max(0, total.value - loaded);
	});

	async function loadMore(): Promise<void> {
		if (!hasMore.value || loading.value) {
			return;
		}
		loading.value = true;
		try {
			await loadFunction(currentPage.value + 1, true);
			currentPage.value++;
		} finally {
			loading.value = false;
		}
	}

	async function goToPage(page: number): Promise<void> {
		if (page < 1 || page > lastPage.value || loading.value) {
			return;
		}
		loading.value = true;
		try {
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
