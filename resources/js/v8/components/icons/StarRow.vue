<template>
	<div
		class="flex items-center"
		:class="{
			'h-3.5 gap-px': props.size === 'small',
			'h-5 gap-0.5': props.size === 'medium',
			'h-6 gap-0.5': props.size === 'large',
		}"
	>
		<span
			class="block px-0.5 relative"
			v-for="index in 5"
			:key="`avg-star-${index}`"
			:class="{
				'w-3.5 h-3.5': props.size === 'small',
				'w-5 h-5': props.size === 'medium',
				'w-6 h-6': props.size === 'large',
			}"
		>
			<UIcon
				:name="isHalf(index) ? 'prime:star-half-fill' : 'prime:star'"
				:class="{
					absolute: true,
					'opacity-0': !(isHalf(index) || isEmpty(index)),
					'text-amber-500': isHalf(index),
					'text-muted': isEmpty(index),
					'text-xs': props.size === 'small',
					'text-lg': props.size === 'medium',
					'text-xl': props.size === 'large',
				}"
			/>
			<UIcon
				name="prime:star-fill"
				:class="{
					absolute: true,
					'opacity-0': !isFull(index),
					'text-amber-500': isFull(index),
					'text-xs': props.size === 'small',
					'text-lg': props.size === 'medium',
					'text-xl': props.size === 'large',
				}"
			/>
		</span>
	</div>
</template>
<script lang="ts" setup>
const props = defineProps<{
	rating: number;
	size: "small" | "medium" | "large";
}>();

function isHalf(index: number): boolean {
	return index === Math.ceil(props.rating) && props.rating % 1 >= 0.25 && props.rating % 1 < 0.75;
}

function isEmpty(index: number): boolean {
	return index > Math.ceil(props.rating) || (index === Math.ceil(props.rating) && props.rating % 1 < 0.25);
}

function isFull(index: number): boolean {
	return index <= Math.floor(props.rating) || (index === Math.ceil(props.rating) && props.rating % 1 >= 0.75);
}
</script>
