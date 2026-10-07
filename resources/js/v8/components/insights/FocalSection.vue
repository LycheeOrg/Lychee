<template>
	<InsightsSection :title="$t('insights.focal.title')">
		<template #actions>
			<USelect v-model="device" :items="devices" size="xs" class="w-56" :aria-label="$t('insights.focal.device')" />
		</template>
		<div v-if="cones.length > 0" class="flex flex-col gap-4">
			<svg viewBox="0 0 820 420" class="w-full" dir="ltr" role="img" :aria-label="$t('insights.focal.title')">
				<line x1="150" y1="210" x2="810" y2="210" class="stroke-(--ui-border)" stroke-dasharray="4 5" />
				<path
					v-for="cone in [...cones].reverse()"
					:key="cone.focal"
					:d="cone.path"
					class="fill-(--ui-primary) stroke-(--ui-primary) transition-[fill-opacity,stroke-width]"
					:fill-opacity="hovered === cone.focal ? 0.4 : 0.1"
					stroke-opacity="0.85"
					:stroke-width="hovered === cone.focal ? 3.5 : 1.5"
					@pointerenter="hovered = cone.focal"
					@pointerleave="hovered = undefined"
				>
					<title>{{ cone.label }} · {{ cone.angleLabel }} · {{ cone.photos }}</title>
				</path>
				<!-- Schematic camera: body, grip, lens. -->
				<g class="fill-(--ui-text-muted)">
					<rect x="40" y="170" width="78" height="80" rx="10" />
					<rect x="58" y="158" width="28" height="16" rx="3" />
					<rect x="118" y="186" width="32" height="48" rx="4" />
				</g>
			</svg>
			<div class="grid gap-2 grid-cols-2 sm:grid-cols-5">
				<button
					v-for="cone in cones"
					:key="cone.focal"
					type="button"
					class="flex flex-col items-start rounded-lg border border-default px-3 py-2 text-start"
					:class="hovered === cone.focal ? 'bg-elevated' : ''"
					@pointerenter="hovered = cone.focal"
					@pointerleave="hovered = undefined"
					@focus="hovered = cone.focal"
					@blur="hovered = undefined"
				>
					<span class="font-bold text-highlighted">{{ cone.label }}</span>
					<span class="text-sm text-muted">{{ cone.angleLabel }}</span>
					<span class="text-xs text-dimmed">{{ cone.photos }}</span>
				</button>
			</div>
			<p class="text-xs text-dimmed">{{ $t("insights.focal.note") }}</p>
		</div>
		<p v-else class="text-sm text-muted text-center py-6">{{ $t("insights.empty") }}</p>
	</InsightsSection>
</template>
<script setup lang="ts">
import { computed, ref, watch } from "vue";
import { sprintf } from "sprintf-js";
import { trans, trans_choice } from "laravel-vue-i18n";
import { formatFocal, formatNumber } from "@/v8/utils/insights/format";
import InsightsSection from "./InsightsSection.vue";

const ALL = "__all__";
/** Full-frame diagonal in mm (36 × 24 mm sensor). */
const FULL_FRAME_DIAGONAL = Math.hypot(36, 24);
const ORIGIN = { x: 150, y: 210 };

const props = defineProps<{
	focal: App.Http.Resources.Insights.DistributionData;
	perDevice: App.Http.Resources.Insights.DeviceFocalData[];
}>();

const device = ref(ALL);
const hovered = ref<number | undefined>(undefined);

// A new scope or period brings a new device list: start again from all devices.
watch(
	() => props.perDevice,
	() => (device.value = ALL),
);

const devices = computed(() => [
	{ value: ALL, label: trans("insights.focal.all_devices") },
	...props.perDevice.map((entry, i) => ({
		value: String(i),
		label: `${entry.name ?? trans("insights.unknown")} · ${formatNumber(sum(entry.counts))}`,
	})),
]);

function sum(values: number[]): number {
	return values.reduce((total, value) => total + value, 0);
}

/** The five most frequent focal lengths of the selection, shortest first (FR-085-18). */
const cones = computed(() => {
	// An index from before a reload may point past the new list: fall back to all devices.
	const selection = (device.value === ALL ? undefined : props.perDevice[Number(device.value)]) ?? props.focal;
	const top = selection.values
		.map((focal, i) => ({ focal, photos: selection.counts[i] }))
		.sort((a, b) => b.photos - a.photos || a.focal - b.focal)
		.slice(0, 5)
		.sort((a, b) => a.focal - b.focal);

	return top.map((item, i) => {
		const angle = 2 * Math.atan(FULL_FRAME_DIAGONAL / (2 * item.focal));
		// Longer focal lengths reach further; wide cones are shortened to stay inside the drawing.
		const reach = Math.min(230 + i * 105, 200 / Math.sin(angle / 2));
		const dx = reach * Math.cos(angle / 2);
		const dy = reach * Math.sin(angle / 2);
		return {
			focal: item.focal,
			label: formatFocal(item.focal),
			angleLabel: sprintf(trans("insights.focal.angle"), formatNumber((angle * 180) / Math.PI, 1)),
			photos: trans_choice("insights.time_span.photos", item.photos, { count: formatNumber(item.photos) }),
			path: `M ${ORIGIN.x} ${ORIGIN.y} L ${ORIGIN.x + dx} ${ORIGIN.y - dy} A ${reach} ${reach} 0 0 1 ${ORIGIN.x + dx} ${ORIGIN.y + dy} Z`,
		};
	});
});
</script>
