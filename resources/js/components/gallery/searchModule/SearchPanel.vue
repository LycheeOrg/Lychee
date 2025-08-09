<template>
	<SearchBox
		v-if="props.searchMinimumLengh !== undefined"
		v-model:search="search"
		:search-minimum-lengh="props.searchMinimumLengh"
		@search="emits('search', search)"
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

const emits = defineEmits<{
	clear: [];
	search: [terms: string];
}>();

const props = defineProps<{
	searchMinimumLengh: number | undefined;
	isSearching: boolean;
	noData: boolean;
}>();

const search = defineModel<string>("search", { default: "" });
</script>
