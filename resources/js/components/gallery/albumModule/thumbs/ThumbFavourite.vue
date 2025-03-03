<template>
	<div
		:class="{
			'absolute top-0 right-0 flex justify-center items-center h-8 w-8 text-surface-0 text-2xl filter-shadow': true,
			'md:opacity-0 md:group-hover:opacity-50 md:transition-all md:ease-out': !props.isFavourite,
		}"
	>
		<span
			:class="{
				pi: true,
				'pi-heart-fill': props.isFavourite,
				'pi-heart': !props.isFavourite,
			}"
		></span>
	</div>
	<div
		:class="{
			'absolute top-0 right-0 flex justify-center items-center h-8 w-8 text-surface-0 text-2xl cursor-pointer': true,
			'opacity-0 md:opacity-0 md:hover:opacity-100 md:transition-all md:ease-out': !props.isFavourite,
		}"
		@click="propagateClick"
	>
		<span
			:class="{
				pi: true,
				'pi-heart': props.isFavourite,
				'pi-heart-fill': !props.isFavourite,
			}"
		></span>
	</div>
</template>
<script setup lang="ts">
const props = defineProps<{
	isFavourite: boolean;
}>();
const emits = defineEmits<{
	click: [];
}>();
function propagateClick(e: MouseEvent) {
	e.stopPropagation();
	emits("click");
}
</script>
