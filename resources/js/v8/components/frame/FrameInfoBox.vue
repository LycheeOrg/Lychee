<template>
	<div
		v-if="lines.length > 0"
		class="max-w-[min(40rem,80vw)] rounded-xl px-5 py-3 text-[clamp(0.875rem,1.4vw,1.5rem)] leading-snug text-white [text-shadow:0_1px_3px_rgb(0_0_0/0.8)]"
		:style="{ backgroundColor: boxBackground(info.background_color, info.background_opacity), opacity: info.opacity / 100 }"
		data-frame-box="info"
	>
		<div
			v-for="line in lines"
			:key="line.kind"
			class="whitespace-pre-wrap"
			:class="{ 'font-semibold': line.kind === 'title' }"
			:data-frame-line="line.kind"
			v-text="line.text"
		></div>
	</div>
</template>
<script setup lang="ts">
import { computed } from "vue";
import { boxBackground, infoLines, type FrameInfo, type FramePhoto } from "@/v8/utils/frameOverlay";

const props = defineProps<{
	info: FrameInfo;
	photo: FramePhoto | null;
}>();

const lines = computed(() => infoLines(props.info, props.photo));
</script>
