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
			<!-- base outline, always drawn so the empty portion of a half star still shows a silhouette -->
			<UIcon
				name="lucide:star"
				:class="{
					absolute: true,
					'text-amber-500': isFull(index) || isHalf(index),
					'text-muted': isEmpty(index),
					'text-xs': props.size === 'small',
					'text-lg': props.size === 'medium',
					'text-xl': props.size === 'large',
				}"
			/>
			<!-- solid overlay: lucide has no filled star glyph, so force fill:currentColor on the (fill="none") path -->
			<UIcon
				:name="isHalf(index) ? 'lucide:star-half' : 'lucide:star'"
				:class="[
					FILL_OVERRIDE_CLASS,
					{
						absolute: true,
						'opacity-0': !(isHalf(index) || isFull(index)),
						'text-amber-500': isHalf(index) || isFull(index),
						'text-xs': props.size === 'small',
						'text-lg': props.size === 'medium',
						'text-xl': props.size === 'large',
					},
				]"
			/>
		</span>
	</div>
</template>
<script lang="ts" setup>
import { FILL_OVERRIDE_CLASS } from "@/v8/icons";

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
