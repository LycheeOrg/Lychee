<template>
	<a class="flex-shrink-0 px-3 py-4 cursor-pointer {{ props.class }}" id="backButton">
		<MiniIcon :icon="props.icon" :fill="props.fill ?? 'fill-neutral-400'" class="my-0 w-4 h-4 mr-0 ml-0" />
	</a>
</template>
<script setup lang="ts">
import MiniIcon from "@/components/icons/MiniIcon.vue";
const props = defineProps<{
	class?: string;
	fill?: string;
	icon: string;
}>();
</script>
