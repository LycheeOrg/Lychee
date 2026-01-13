<template>
	<!-- Infinite Scroll Mode -->
	<PaginationInfiniteScroll
		v-if="mode === 'infinite_scroll'"
		:loading="loading"
		:has-more="hasMore"
		:resource-type="resourceType"
		@load-more="emit('loadMore')"
	/>

	<!-- Load More Button Mode -->
	<PaginationLoadMore
		v-else-if="mode === 'load_more_button'"
		:loading="loading"
		:has-more="hasMore"
		:remaining="remaining"
		:resource-type="resourceType"
		@load-more="emit('loadMore')"
	/>

	<!-- Page Navigation Mode -->
	<div v-else-if="mode === 'page_navigation' && totalPages > 1" class="flex justify-center w-full py-4">
		<Paginator
			:first="(currentPage - 1) * perPage"
			:rows="perPage"
			:total-records="total"
			:always-show="false"
			:pt:pcRowPerPageDropdown:class="'hidden'"
			@page="onPageChange"
		/>
	</div>
</template>
<script setup lang="ts">
import { computed } from "vue";
import PaginationInfiniteScroll from "./PaginationInfiniteScroll.vue";
import PaginationLoadMore from "./PaginationLoadMore.vue";
import Paginator, { type PageState } from "primevue/paginator";

const props = defineProps<{
	mode: App.Enum.PaginationMode;
	loading: boolean;
	hasMore: boolean;
	currentPage: number;
	lastPage: number;
	perPage: number;
	total: number;
	remaining?: number;
	resourceType?: "photos" | "albums";
}>();

const emit = defineEmits<{
	loadMore: [];
	goToPage: [page: number];
}>();

const totalPages = computed(() => props.lastPage);

function onPageChange(event: PageState) {
	const newPage = event.page + 1; // Paginator uses 0-based indexing
	emit("goToPage", newPage);
}
</script>
