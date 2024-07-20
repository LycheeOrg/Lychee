<template>
	<span
		class="badge inline-block ml-1 px-2 pt-2 pb-1 border-solid border-white border border-t-0 rounded-md rounded-t-none text-surface-0 text-center"
		:class="props.class"
	>
		<svg class="iconic inline w-4 h-4 fill-white">
			<use :xlink:href="'#' + props.icon" />
		</svg>
	</span>
</template>
<script setup lang="ts">
const props = defineProps<{
	class: string;
	icon: string;
}>();
</script>
