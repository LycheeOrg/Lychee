<template>
	<div>
		<div
			:class="{
				'flex justify-center items-center h-8 w-8 text-surface-0 text-2xl filter-shadow': true,
				'md:opacity-0 md:group-hover:opacity-50 md:transition-all md:ease-out': true,
			}"
		>
			<span
				:class="{
					pi: true,
					// 'pi-heart-fill': props.isFavourite,
					'pi-cart-arrow-down': true,
				}"
			></span>
		</div>
		<div
			:class="{
				'flex justify-center items-center h-8 w-8 text-surface-0 text-2xl cursor-pointer': true,
				'opacity-0 md:opacity-0 md:hover:opacity-100 md:transition-all md:ease-out': true,
			}"
			@click="propagateClick"
		>
			<span
				:class="{
					pi: true,
					// 'pi-cart-arrow-down': props.isFavourite,
					'pi-cart-arrow-down': true,
				}"
			></span>
		</div>
	</div>
</template>
<script setup lang="ts">


const emits = defineEmits<{
	click: [];
}>();
function propagateClick(e: MouseEvent) {
	e.stopPropagation();
	emits("click");
}
</script>
