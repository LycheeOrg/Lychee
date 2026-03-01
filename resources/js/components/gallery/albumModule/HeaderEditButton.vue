<template>
	<button :class="['h-8', 'w-1/5', 'cursor-pointer hover:bg-white/30 hover:rounded-md']" @click="emits('setPosition', props.position)">
		<i :class="['pi', positionIcon]" />
	</button>
</template>
<script setup lang="ts">
import { computed } from "vue";

const props = defineProps<{
	position: string;
}>();

const POSITION_ICONS: { [key: string]: string } = {
	"top-left": "pi-arrow-up-left",
	"top-right": "pi-arrow-up-right",
	"bottom-left": "pi-arrow-down-left",
	"bottom-right": "pi-arrow-down-right",
	center: "pi-circle",
};

const positionIcon = computed(() => POSITION_ICONS[props.position]);

const emits = defineEmits<{
	setPosition: [position: string];
}>();
</script>
