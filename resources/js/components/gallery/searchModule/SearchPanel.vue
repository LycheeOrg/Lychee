<template>
	<SearchBox
		v-if="props.searchMinimumLengh !== undefined"
		:search-minimum-lengh="props.searchMinimumLengh"
		v-model:search="search_term"
		@search="emits('search', search_term)"
		@clear="emits('clear')"
	/>
	<div v-else-if="props.noData" class="flex w-full justify-center text-xl text-muted-color">
		<span class="block">
			{{ $t("gallery.search.no_results") }}
		</span>
	</div>
</template>
<script setup lang="ts">
import SearchBox from "@/components/forms/search/SearchBox.vue";
import { useTogglablesStateStore } from "@/stores/ModalsState";
import { storeToRefs } from "pinia";

const emits = defineEmits<{
	clear: [];
	search: [terms: string];
}>();

const props = defineProps<{
	searchMinimumLengh: number | undefined;
	isSearching: boolean;
	noData: boolean;
}>();

const togglableStore = useTogglablesStateStore();
const { search_term } = storeToRefs(togglableStore);
</script>
