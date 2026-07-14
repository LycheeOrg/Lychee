<template>
	<button :class="['h-8', 'w-1/5', 'cursor-pointer hover:bg-white/30 hover:rounded-md']" @click="emits('setPosition', props.position)">
		<UIcon :name="positionIcon" />
	</button>
</template>
<script setup lang="ts">
import { computed } from "vue";

const props = defineProps<{
	position: string;
}>();

const POSITION_ICONS: { [key: string]: string } = {
	"top-left": "lucide:arrow-up-left",
	"top-right": "lucide:arrow-up-right",
	"bottom-left": "lucide:arrow-down-left",
	"bottom-right": "lucide:arrow-down-right",
	center: "lucide:circle",
};

const positionIcon = computed(() => POSITION_ICONS[props.position]);

const emits = defineEmits<{
	setPosition: [position: string];
}>();
</script>
