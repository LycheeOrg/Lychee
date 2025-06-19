<template>
	<div :class="{ 'w-full relative': true, 'h-(--header-height)': !props.isMoreOpen }">
		<template v-if="props.isMoreOpen">
			<img
				:src="header.medium?.url ?? header.small?.url ?? '/img/no_images.svg'"
				:alt="props.title"
				class="w-full object-cover"
				@click="emits('clicked')"
			/>
		</template>
		<template v-else-if="image_header_cover === 'fit'">
			<img alt="image background" class="absolute w-full h-full object-cover object-center" :src="header.thumb?.url ?? '/img/no_images.svg'" />
			<div class="w-full h-full bg-repeat absolute bg-[url(/img/noise.png)] backdrop-blur-3xl blur-3xl"></div>
			<img
				:src="header.medium?.url ?? header.small?.url ?? '/img/no_images.svg'"
				:alt="props.title"
				class="w-full h-(--header-height) absolute object-contain"
				@click="emits('clicked')"
			/>
		</template>
		<img
			v-else
			:src="header.medium?.url ?? header.small?.url ?? '/img/no_images.svg'"
			:alt="props.title"
			class="w-full h-(--header-height) object-cover"
			@click="emits('clicked')"
		/>
		<Blur v-if="props.isNsfw">
			<i class="pi pi-eye-slash text-6xl text-surface-0"></i>
		</Blur>
	</div>
</template>
<script setup lang="ts">
import Blur from "./Blur.vue";

const props = defineProps<{
	header: App.Http.Resources.Models.SizeVariantsResouce;
	title: string;
	image_header_cover: App.Enum.CoverFitType;
	isNsfw: boolean;
	isMoreOpen: boolean;
}>();

const emits = defineEmits<{ clicked: [] }>();
</script>
