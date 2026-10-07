<template>
	<InsightsSection :title="$t('insights.exposure.title')">
		<template #actions>
			<InsightsToggle v-model="scale" :options="scales" />
		</template>
		<div class="flex flex-col gap-4">
			<div class="grid gap-6 grid-cols-2 lg:grid-cols-6">
				<InsightsStat :label="$t('insights.exposure.iso')" :value="mode('iso')" :note="$t('insights.exposure.mode')" />
				<InsightsStat :label="$t('insights.exposure.focal')" :value="mode('focal')" :note="$t('insights.exposure.mode')" />
				<InsightsStat :label="$t('insights.exposure.shutter')" :value="mode('shutter')" :note="$t('insights.exposure.mode')" />
				<InsightsStat :label="$t('insights.exposure.aperture')" :value="mode('aperture')" :note="$t('insights.exposure.mode')" />
				<InsightsStat :label="$t('insights.exposure.total_video')" :value="formatDuration(props.exposure.total_video_duration)" />
				<InsightsStat :label="$t('insights.exposure.average_video')" :value="averageVideo" />
			</div>
			<InsightsToggle v-model="field" :options="fields" />
			<template v-if="distribution.total > 0">
				<InsightsChart :option="option" :height="260" />
				<p class="text-sm text-muted">
					{{ summary }}
				</p>
			</template>
			<p v-else class="text-sm text-muted text-center py-6">{{ $t("insights.empty") }}</p>
			<p v-if="distribution.excluded > 0" class="text-xs text-dimmed">
				{{ sprintf($t("insights.exposure.excluded"), distribution.excluded) }}
			</p>
		</div>
	</InsightsSection>
</template>
<script setup lang="ts">
import { computed, ref } from "vue";
import { sprintf } from "sprintf-js";
import { trans } from "laravel-vue-i18n";
import { useInsightsTheme } from "@/v8/composables/insights/useInsightsTheme";
import { useLtRorRtL } from "@/utils/Helpers";
import { formatDuration } from "@/v8/utils/insights/format";
import { barOption } from "@/v8/utils/insights/options/common";
import { exposureFormatter, type ExposureField } from "@/v8/utils/insights/options/exposure";
import InsightsChart from "./InsightsChart.vue";
import InsightsSection from "./InsightsSection.vue";
import InsightsStat from "./InsightsStat.vue";
import InsightsToggle from "./InsightsToggle.vue";

type Scale = "linear" | "log";

const props = defineProps<{ exposure: App.Http.Resources.Insights.ExposureData }>();

const { palette } = useInsightsTheme();
const { isRTL } = useLtRorRtL();

const field = ref<ExposureField>("iso");
const scale = ref<Scale>("linear");

const fields: { value: ExposureField; label: string }[] = [
	{ value: "iso", label: trans("insights.exposure.iso") },
	{ value: "focal", label: trans("insights.exposure.focal") },
	{ value: "shutter", label: trans("insights.exposure.shutter") },
	{ value: "aperture", label: trans("insights.exposure.aperture") },
	{ value: "video_length", label: trans("insights.exposure.video_length") },
];
const scales: { value: Scale; label: string }[] = [
	{ value: "linear", label: trans("insights.exposure.linear") },
	{ value: "log", label: trans("insights.exposure.log") },
];

const distribution = computed(() => props.exposure[field.value]);
const format = computed(() => exposureFormatter(field.value));

const option = computed(() =>
	barOption({
		labels: distribution.value.values.map((value) => format.value(value)),
		values: distribution.value.counts,
		palette: palette.value,
		rtl: isRTL(),
		log: scale.value === "log",
	}),
);

const summary = computed(() => {
	const d = distribution.value;
	const f = format.value;
	const parts = [
		d.median === null ? null : `${trans("insights.exposure.median")} ${f(d.median)}`,
		d.mean === null ? null : `${trans("insights.exposure.mean")} ${f(d.mean)}`,
		d.mode === null ? null : `${trans("insights.exposure.mode")} ${f(d.mode)}`,
		d.min === null || d.max === null ? null : `${trans("insights.exposure.range")} ${f(d.min)} – ${f(d.max)}`,
	];
	return parts.filter((part) => part !== null).join(" · ");
});

const averageVideo = computed(() => {
	const videos = props.exposure.video_length.total;
	return videos === 0 ? "—" : formatDuration(props.exposure.total_video_duration / videos);
});

function mode(name: Exclude<ExposureField, "video_length">): string {
	const value = props.exposure[name].mode;
	return value === null ? "—" : exposureFormatter(name)(value);
}
</script>
