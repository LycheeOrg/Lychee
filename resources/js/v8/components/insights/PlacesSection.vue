<template>
	<InsightsSection :title="$t('insights.places.title')">
		<template v-if="isMapEnabled" #actions>
			<UButton variant="ghost" color="neutral" size="sm" icon="lucide:map" :label="$t('insights.places.open_map')" :to="{ name: 'map' }" />
		</template>
		<InsightsStat
			:label="$t('insights.places.located')"
			:value="formatNumber(props.places.located)"
			:note="sprintf($t('insights.places.share'), formatPercent(props.places.share))"
		/>
	</InsightsSection>
</template>
<script setup lang="ts">
import { computed } from "vue";
import { storeToRefs } from "pinia";
import { sprintf } from "sprintf-js";
import { useGlobalRightsStore } from "@/stores/GlobalRightsState";
import { formatNumber, formatPercent } from "@/v8/utils/insights/format";
import InsightsSection from "./InsightsSection.vue";
import InsightsStat from "./InsightsStat.vue";

const props = defineProps<{ places: App.Http.Resources.Insights.PlacesData }>();

const globalRightsStore = useGlobalRightsStore();
const { rights } = storeToRefs(globalRightsStore);
globalRightsStore.ensureLoaded();

const isMapEnabled = computed(() => rights.value?.modules.is_map_enabled ?? false);
</script>
