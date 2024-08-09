<template>
	<a class="inline-block px-3 py-2.5 cursor-pointer transition-all duration-300 ease-in h-full" :class="props.class">
		<MiniIcon :icon="props.icon" fill="" class="my-0 w-4 h-4 mr-0 ml-0" />
	</a>
</template>
<script setup lang="ts">
import MiniIcon from "@/components/icons/MiniIcon.vue";

const props = defineProps<{
	icon: string;
	class?: string;
}>();
</script>
