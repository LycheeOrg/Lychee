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
		<UPagination :page="currentPage" :items-per-page="perPage" :total="total" @update:page="onPageChange" />
	</div>
</template>
<script setup lang="ts">
import { computed } from "vue";
import PaginationInfiniteScroll from "./PaginationInfiniteScroll.vue";
import PaginationLoadMore from "./PaginationLoadMore.vue";

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

function onPageChange(page: number) {
	emit("goToPage", page);
}
</script>
