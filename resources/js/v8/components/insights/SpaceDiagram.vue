<template>
	<div class="flex flex-col gap-2">
		<div class="flex flex-wrap items-center justify-between gap-2">
			<h3 class="text-sm text-muted">{{ $t("insights.storage.space") }}</h3>
			<InsightsToggle v-model="kind" :options="kinds" />
		</div>
		<InsightsChart v-if="tree.length > 0" :key="kind" :option="option" :height="420" />
		<p v-else-if="albums !== undefined" class="text-sm text-muted text-center py-6">{{ $t("insights.empty") }}</p>
	</div>
</template>
<script setup lang="ts">
import { computed, ref, watch } from "vue";
import { storeToRefs } from "pinia";
import { trans } from "laravel-vue-i18n";
import StatisticsService from "@/services/statistics-service";
import { useLycheeStateStore } from "@/stores/LycheeState";
import { useInsightsTheme } from "@/v8/composables/insights/useInsightsTheme";
import { useSizeVariantStats } from "@/v8/composables/useSizeVariantStats";
import { spaceOption, spaceTree } from "@/v8/utils/insights/options/space";
import InsightsChart from "./InsightsChart.vue";
import InsightsToggle from "./InsightsToggle.vue";

type Kind = "sunburst" | "treemap";

const props = defineProps<{
	/** Album tree to draw; null draws every album the endpoint returns. */
	albumId: string | null;
	/** Group roots by owner (administrators, whole instance). */
	byOwner: boolean;
	/** Keep only this owner's albums (the endpoint returns every owner to administrators). */
	owner: string | null;
}>();

const lycheeStore = useLycheeStateStore();
const { are_nsfw_visible } = storeToRefs(lycheeStore);
const { palette } = useInsightsTheme();
const { sizeToUnit } = useSizeVariantStats();

const kind = ref<Kind>("sunburst");
const kinds: { value: Kind; label: string }[] = [
	{ value: "sunburst", label: trans("insights.storage.sunburst") },
	{ value: "treemap", label: trans("insights.storage.treemap") },
];

const albums = ref<App.Http.Resources.Statistics.Album[] | undefined>(undefined);

const tree = computed(() =>
	spaceTree(
		(albums.value ?? []).filter(
			(album) => (!album.is_nsfw || are_nsfw_visible.value) && (props.owner === null || album.username === props.owner),
		),
		props.byOwner,
	),
);
const option = computed(() => spaceOption(tree.value, kind.value, palette.value, sizeToUnit, trans("insights.storage.space")));

watch(
	() => props.albumId,
	(albumId) => {
		albums.value = undefined;
		StatisticsService.getAlbumSpace(albumId).then((response) => (albums.value = response.data));
	},
	{ immediate: true },
);
</script>
