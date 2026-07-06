<template>
	<SearchBox v-if="searchStore.config !== undefined" @search="(terms) => emits('search', terms)" @clear="emits('clear')" />
	<div v-else-if="props.noData" class="flex w-full justify-center text-xl text-muted">
		<span class="block">
			{{ $t("gallery.search.no_results") }}
		</span>
	</div>
</template>
<script setup lang="ts">
import SearchBox from "@/v8/components/forms/search/SearchBox.vue";
import { useSearchStore } from "@/stores/SearchState";

const emits = defineEmits<{
	clear: [];
	search: [terms: string];
}>();

const searchStore = useSearchStore();

const props = defineProps<{
	noData: boolean;
}>();
</script>
