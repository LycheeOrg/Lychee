<template>
	<InsightsSection :title="$t('insights.storage.title')">
		<div class="flex flex-col gap-6">
			<div class="grid gap-6 grid-cols-2 lg:grid-cols-4">
				<InsightsStat :label="$t('insights.storage.originals')" :value="sizeToUnit(props.storage.total_size)" />
				<InsightsStat :label="$t('insights.storage.per_photo')" :value="average(props.storage.average_photo_size)" />
				<InsightsStat :label="$t('insights.storage.per_video')" :value="average(props.storage.average_video_size)" />
				<InsightsStat :label="$t('insights.storage.unknown')" :value="formatNumber(props.storage.size_unknown)" />
			</div>
			<SpaceDiagram v-if="props.showSpace" :album-id="props.albumId" :by-owner="props.byOwner" :owner="props.spaceOwner" />
			<template v-if="props.showDetails">
				<div class="flex flex-col gap-2">
					<h3 class="text-sm text-muted">{{ $t("insights.storage.variants") }}</h3>
					<SizeVariantMeter :key="props.albumId ?? ''" :album-id="props.albumId" />
				</div>
				<div v-if="props.albumId === null" class="flex flex-col gap-2">
					<div class="flex flex-wrap items-center justify-between gap-2">
						<h3 class="text-sm text-muted">{{ $t("insights.storage.albums") }}</h3>
						<USwitch v-model="includeChildren" :label="$t('insights.storage.collapse')" :ui="{ label: 'text-sm' }" />
					</div>
					<AlbumsTable v-show="!includeChildren" :show-username="true" :is-total="false" :album-id="undefined" />
					<AlbumsTable v-if="includeChildren" :show-username="true" :is-total="true" :album-id="undefined" />
				</div>
			</template>
		</div>
	</InsightsSection>
</template>
<script setup lang="ts">
import { ref } from "vue";
import { useSizeVariantStats } from "@/v8/composables/useSizeVariantStats";
import { formatNumber } from "@/v8/utils/insights/format";
import AlbumsTable from "@/v8/components/statistics/AlbumsTable.vue";
import SizeVariantMeter from "@/v8/components/statistics/SizeVariantMeter.vue";
import SpaceDiagram from "./SpaceDiagram.vue";
import InsightsSection from "./InsightsSection.vue";
import InsightsStat from "./InsightsStat.vue";

const props = defineProps<{
	storage: App.Http.Resources.Insights.StorageData;
	/** Size-variant meter and album table: own library, album tree, or the whole instance for administrators (FR-085-07). */
	showDetails: boolean;
	/** Album tree in album scope; null otherwise. */
	albumId: string | null;
	/** Space diagram (FR-085-20): shown for the Library period in every scope. */
	showSpace: boolean;
	/** Group the space diagram by owner (administrators, whole instance). */
	byOwner: boolean;
	/** Owner whose albums the space diagram keeps; null keeps all. */
	spaceOwner: string | null;
}>();

const { sizeToUnit } = useSizeVariantStats();
const includeChildren = ref(false);

function average(size: number | null): string {
	return size === null ? "—" : sizeToUnit(size);
}
</script>
