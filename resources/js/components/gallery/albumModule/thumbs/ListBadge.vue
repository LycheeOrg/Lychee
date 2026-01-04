<template>
	<span class="inline-block ltr:ml-1 rtl:mr-1 text-center">
		<i v-if="props.pi" :class="piClass" />
		<svg v-if="props.icon" class="iconic inline w-4 h-4" :class="props.class">
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
