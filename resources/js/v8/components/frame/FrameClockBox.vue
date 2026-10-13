<template>
	<div
		v-if="clock.is_time_enabled || clock.is_date_enabled"
		class="rounded-xl px-5 py-3 text-white [text-shadow:0_1px_3px_rgb(0_0_0/0.8)]"
		:style="{ backgroundColor: boxBackground(clock.background_color, clock.background_opacity), opacity: clock.opacity / 100 }"
		data-frame-box="clock"
	>
		<div v-if="clock.is_time_enabled" class="text-[clamp(2rem,6vw,6rem)] font-light leading-none tabular-nums" data-frame-time>
			{{ time }}
		</div>
		<div v-if="clock.is_date_enabled" class="text-[clamp(1rem,2vw,2rem)]" :class="{ 'mt-2': clock.is_time_enabled }" data-frame-date>
			{{ date }}
		</div>
	</div>
</template>
<script setup lang="ts">
import { computed, onMounted, onUnmounted, ref } from "vue";
import { getActiveLanguage } from "laravel-vue-i18n";
import { boxBackground, type FrameClock } from "@/v8/utils/frameOverlay";

const props = defineProps<{
	clock: FrameClock;
}>();

// The visitor's browser locale on an English install, the Lychee language otherwise (FR-088-02).
function locale(): string {
	const language = getActiveLanguage();
	return language.toLowerCase().startsWith("en") ? navigator.language : language.replace("_", "-");
}

const now = ref(new Date());
let timer: number | undefined = undefined;

// One timeout, re-armed for the next minute boundary (NFR-088-05).
function tick() {
	now.value = new Date();
	timer = window.setTimeout(tick, 60_000 - (now.value.getSeconds() * 1000 + now.value.getMilliseconds()));
}

const time = computed(() =>
	new Intl.DateTimeFormat(locale(), {
		hour: "numeric",
		minute: "2-digit",
		hourCycle: props.clock.time_format === "12h" ? "h12" : "h23",
	}).format(now.value),
);

const date = computed(() => new Intl.DateTimeFormat(locale(), { weekday: "long", day: "numeric", month: "long", year: "numeric" }).format(now.value));

onMounted(tick);
onUnmounted(() => window.clearTimeout(timer));
</script>
