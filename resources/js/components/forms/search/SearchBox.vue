<template>
	<div v-if="searchStore.config !== undefined" class="w-full max-w-5xl mx-auto">
		<SearchInputBar
			v-model="rawInput"
			v-model:advanced-open="advancedOpen"
			:min-length="searchStore.config.search_minimum_length"
			@search="onSearch"
		/>
		<Collapse :when="advancedOpen">
			<AdvancedSearchPanel ref="advancedPanelRef" @update:tokens="onAdvancedTokens" @clear="onClear" />
		</Collapse>
	</div>
</template>
<script lang="ts" setup>
import { ref, onMounted, nextTick } from "vue";
import { Collapse } from "vue-collapsed";
import SearchInputBar from "@/components/forms/search/SearchInputBar.vue";
import AdvancedSearchPanel from "@/components/forms/search/AdvancedSearchPanel.vue";
import { parseTokens } from "@/composables/useSearchTokenAssembler";
import { useSearchStore } from "@/stores/SearchState";

const searchStore = useSearchStore();

const emits = defineEmits<{
	search: [terms: string];
	clear: [];
}>();

// The query string displayed in the text input.
const rawInput = ref("");

// The token fragment assembled from the advanced panel fields.
const advancedTokens = ref("");

// The portion of rawInput that could not be parsed into advanced fields.
const remainder = ref("");

// Whether the advanced panel is open.
const advancedOpen = ref(false);

// Ref to the AdvancedSearchPanel instance (for calling parseAndLoad).
const advancedPanelRef = ref<InstanceType<typeof AdvancedSearchPanel> | null>(null);

// Flag used to ignore update:tokens emits that were triggered by our own
// parseAndLoad call (round-trip sync prevention).
let _syncingFromRaw = false;

// ---------------------------------------------------------------------------
// Combine advanced tokens + unrecognised remainder into the display string.
// ---------------------------------------------------------------------------
function buildDisplayString(tokens: string, rem: string): string {
	return [tokens, rem].filter(Boolean).join(" ").trim();
}

// ---------------------------------------------------------------------------
// Called when the advanced panel emits assembled tokens.
// Only act when the update originates from user interaction, not from our
// own parseAndLoad call.
// ---------------------------------------------------------------------------
function onAdvancedTokens(tokens: string) {
	if (_syncingFromRaw) return;
	advancedTokens.value = tokens;
	rawInput.value = buildDisplayString(tokens, remainder.value);
}

// ---------------------------------------------------------------------------
// Search
// ---------------------------------------------------------------------------
function onSearch() {
	const q = rawInput.value.trim();
	if (q) {
		emits("search", q);
	}
}

// ---------------------------------------------------------------------------
// Clear
// ---------------------------------------------------------------------------
function onClear() {
	rawInput.value = "";
	advancedTokens.value = "";
	remainder.value = "";
	advancedPanelRef.value?.parseAndLoad("");
	emits("clear");
}

// ---------------------------------------------------------------------------
// Watch the SearchInputBar's v-model changes by wrapping rawInput in a setter.
// We intercept updates via a computed v-model to drive round-trip parsing.
// ---------------------------------------------------------------------------
// Vue's v-model on SearchInputBar uses defineModel inside that component, so
// the parent's rawInput ref is updated whenever the user types. To hook into
// each change for round-trip parsing, we watch rawInput explicitly.
import { watch } from "vue";
watch(rawInput, (value) => {
	// Only run parse when the change came from user typing (not from our own
	// buildDisplayString assignment in onAdvancedTokens).
	if (_syncingFromRaw) return;
	const { remainder: rem } = parseTokens(value);
	remainder.value = rem;

	_syncingFromRaw = true;
	advancedPanelRef.value?.parseAndLoad(value);
	nextTick(() => {
		_syncingFromRaw = false;
	});
});

// If a parent passes an initial search term (e.g. from the store), pre-fill.
onMounted(() => {
	if (searchStore.searchTerm) {
		rawInput.value = searchStore.searchTerm;
	}
});
</script>
