<template>
	<span
		class="badge inline-block ml-1 px-2 pt-2 pb-1 border-solid border-white border border-t-0 rounded-md rounded-t-none text-surface-0 text-center"
		:class="props.class"
	>
		<i :class="piClass" v-if="props.pi" />
		<svg v-if="props.icon" class="iconic inline w-4 h-4 fill-white">
			<use :xlink:href="iconHref" />
		</svg>
	</span>
</template>
<script setup lang="ts">
import { computed } from "vue";

const props = defineProps<{
	class: string;
	icon?: string;
	pi?: string;
}>();

const piClass = computed(() => (props.pi ? `pi pi-${props.pi}` : ""));
const iconHref = computed(() => (props.icon ? `#${props.icon}` : ""));
</script>
