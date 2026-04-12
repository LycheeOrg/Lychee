<template>
	<span class="badge inline-block ltr:ml-1 rtl:mr-1 px-2 pt-2 pb-1 rounded-md rounded-t-none text-surface-0 text-center" :class="spanClass">
		<i v-if="props.pi" :class="piClass" />
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
	borderColor?: string;
}>();

const piClass = computed(() => (props.pi ? `pi pi-${props.pi}` : ""));
const iconHref = computed(() => (props.icon ? `#${props.icon}` : ""));
const spanClass = computed(() => props.class + (props.borderColor ? ` ${props.borderColor}` : " border-solid border-white border border-t-0"));
</script>
