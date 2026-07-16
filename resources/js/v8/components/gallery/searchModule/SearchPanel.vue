<template>
	<SearchBox
		v-if="searchStore.config !== undefined"
		@search="(terms) => emits('search', terms)"
		@clear="emits('clear')"
		@clear-scope="emits('clearScope')"
	/>
	<UEmpty v-if="showNoResults" icon="lucide:search-x" :title="$t('gallery.search.no_results')" class="w-full mt-6" />
</template>
<script setup lang="ts">
import { computed } from "vue";
import SearchBox from "@/v8/components/forms/search/SearchBox.vue";
import { useSearchStore } from "@/stores/SearchState";

const emits = defineEmits<{
	clear: [];
	search: [terms: string];
	clearScope: [];
}>();

const searchStore = useSearchStore();

const props = defineProps<{
	noData: boolean;
}>();

// A search has actually run (searchTerm set) and came back empty, as opposed to
// the fresh, not-yet-searched state where noData is also true.
const showNoResults = computed<boolean>(() => searchStore.searchTerm !== undefined && !searchStore.isSearching && props.noData);
</script>
