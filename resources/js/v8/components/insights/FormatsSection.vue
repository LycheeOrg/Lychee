<template>
	<InsightsSection :title="$t('insights.formats.title')">
		<template #actions>
			<InsightsToggle v-model="media" :options="medias" />
		</template>
		<div class="flex flex-col gap-6">
			<div class="grid gap-6 grid-cols-2 lg:grid-cols-4">
				<InsightsStat
					v-for="orientation in orientations"
					:key="orientation.key"
					:label="orientation.label"
					:value="formatNumber(orientation.count)"
					:note="total > 0 ? sprintf($t('insights.formats.share'), formatPercent(orientation.count / total)) : undefined"
				/>
			</div>
			<div class="grid gap-6 lg:grid-cols-[3fr_2fr]">
				<div class="flex flex-col gap-2 min-w-0">
					<div class="flex flex-wrap items-center justify-between gap-2">
						<h3 class="text-sm text-muted">{{ $t("insights.formats.dimensions") }}</h3>
						<InsightsToggle v-model="scale" :options="scales" />
					</div>
					<svg
						viewBox="0 0 400 400"
						class="w-full max-w-md mx-auto aspect-square"
						role="img"
						:aria-label="$t('insights.formats.dimensions')"
					>
						<line x1="200" y1="0" x2="200" y2="400" class="stroke-(--ui-border)" stroke-dasharray="3 4" />
						<line x1="0" y1="200" x2="400" y2="200" class="stroke-(--ui-border)" stroke-dasharray="3 4" />
						<rect
							v-for="frame in frames"
							:key="frame.key"
							:x="frame.x"
							:y="frame.y"
							:width="frame.width"
							:height="frame.height"
							fill="none"
							class="stroke-(--ui-primary) transition-[stroke-width,stroke-opacity]"
							:stroke-opacity="hovered === frame.key ? 1 : frame.opacity"
							:stroke-width="hovered === frame.key ? 4 : 1.5"
							@pointerenter="hovered = frame.key"
							@pointerleave="hovered = undefined"
						>
							<title>{{ frame.label }} · {{ formatNumber(frame.count) }}</title>
						</rect>
					</svg>
					<p class="text-xs text-dimmed">
						{{ sprintf($t("insights.formats.shown"), frames.length, props.formats.dimensions.formats) }}
					</p>
				</div>
				<div class="flex flex-col gap-2 min-w-0">
					<h3 class="text-sm text-muted">{{ $t("insights.formats.common") }}</h3>
					<ul class="flex flex-col gap-1 max-h-72 overflow-y-auto">
						<li
							v-for="frame in commonFrames"
							:key="frame.key"
							class="flex items-center justify-between gap-2 rounded-md px-2 py-1 text-sm"
							:class="hovered === frame.key ? 'bg-elevated' : ''"
							@pointerenter="hovered = frame.key"
							@pointerleave="hovered = undefined"
						>
							<span dir="ltr">{{ frame.label }}</span>
							<span class="font-bold text-highlighted">{{ formatNumber(frame.count) }}</span>
						</li>
					</ul>
					<h3 class="text-sm text-muted mt-4">{{ $t("insights.formats.aspect_ratios") }}</h3>
					<InsightsChart :option="ratioOption" :height="40 + ratios.length * 26" />
				</div>
			</div>
		</div>
	</InsightsSection>
</template>
<script setup lang="ts">
import { computed, ref } from "vue";
import { sprintf } from "sprintf-js";
import { trans } from "laravel-vue-i18n";
import { useInsightsTheme } from "@/v8/composables/insights/useInsightsTheme";
import { useLtRorRtL } from "@/utils/Helpers";
import { formatNumber, formatPercent } from "@/v8/utils/insights/format";
import { barOption } from "@/v8/utils/insights/options/common";
import InsightsChart from "./InsightsChart.vue";
import InsightsSection from "./InsightsSection.vue";
import InsightsStat from "./InsightsStat.vue";
import InsightsToggle from "./InsightsToggle.vue";

type Media = "all" | "photos" | "videos";
type Scale = "linear" | "log";

const props = defineProps<{ formats: App.Http.Resources.Insights.FormatsData }>();

const { palette } = useInsightsTheme();
const { isRTL } = useLtRorRtL();

const media = ref<Media>("all");
const scale = ref<Scale>("log");
const hovered = ref<string | undefined>(undefined);

const medias: { value: Media; label: string }[] = [
	{ value: "all", label: trans("insights.formats.all") },
	{ value: "photos", label: trans("insights.formats.photos") },
	{ value: "videos", label: trans("insights.formats.videos") },
];
const scales: { value: Scale; label: string }[] = [
	{ value: "linear", label: trans("insights.formats.linear") },
	{ value: "log", label: trans("insights.formats.log") },
];

const counts = computed(() => props.formats[media.value]);
const total = computed(() => counts.value.portrait + counts.value.landscape + counts.value.square + counts.value.unknown);
const orientations = computed(() =>
	(["portrait", "landscape", "square", "unknown"] as const).map((key) => ({
		key,
		label: trans(`insights.formats.${key}`),
		count: counts.value[key],
	})),
);

/** Centred frames, scaled so the largest side fits; frequent frames drawn last (on top). */
const frames = computed(() => {
	const d = props.formats.dimensions;
	const largest = Math.max(1, ...d.widths, ...d.heights);
	const most = Math.max(1, ...d.counts);
	const k = 380 / largest;
	const weight = (count: number) => (scale.value === "log" ? Math.log1p(count) / Math.log1p(most) : count / most);

	return d.widths
		.map((width, i) => ({
			key: `${width}×${d.heights[i]}`,
			label: `${width} × ${d.heights[i]}`,
			count: d.counts[i],
			x: 200 - (width * k) / 2,
			y: 200 - (d.heights[i] * k) / 2,
			width: width * k,
			height: d.heights[i] * k,
			opacity: 0.15 + 0.85 * weight(d.counts[i]),
		}))
		.reverse();
});

const commonFrames = computed(() => [...frames.value].reverse().slice(0, 20));

const ratios = computed(() => props.formats.aspect_ratios.filter((ratio) => ratio.count > 0));

const ratioOption = computed(() =>
	barOption({
		labels: ratios.value.map((ratio) =>
			ratio.group === "panorama" || ratio.group === "other" ? trans(`insights.formats.${ratio.group}`) : ratio.group,
		),
		values: ratios.value.map((ratio) => ratio.count),
		palette: palette.value,
		rtl: isRTL(),
		horizontal: true,
	}),
);
</script>
