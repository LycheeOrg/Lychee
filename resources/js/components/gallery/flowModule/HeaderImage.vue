<template>
	<div class="w-full h-(--header-height)">
		<template v-if="image_header_cover === 'fit'">
			<img alt="image background" class="absolute w-full h-full object-cover object-center" :src="header.thumb?.url ?? '/img/no_images.svg'" />
			<div class="w-full h-full bg-repeat absolute bg-[url(/img/noise.png)] backdrop-blur-3xl blur-3xl"></div>
			<img
				:src="header.medium?.url ?? header.small?.url ?? '/img/no_images.svg'"
				:alt="props.title"
				:class="{
					'w-full h-(--header-height) absolute object-contain': true,
				}"
			/>
		</template>
		<img
			v-else
			:src="header.medium?.url ?? header.small?.url ?? '/img/no_images.svg'"
			:alt="props.title"
			:class="{
				'w-full h-(--header-height) object-cover': true,
			}"
		/>
	</div>
</template>
<script setup lang="ts">
const props = defineProps<{
	header: App.Http.Resources.Models.SizeVariantsResouce;
	title: string;
	image_header_cover: "fit" | "cover";
}>();
</script>
