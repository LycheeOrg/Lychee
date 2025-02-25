<template>
	<a class="px-1 cursor-pointer group hidden sm:inline-block h-8" @click="layout = 'square'" :title="$t('gallery.layout.squares')">
		<MiniIcon icon="squares" fill="fill-transparent" :class="squareClass" />
	</a>
	<a class="px-1 cursor-pointer group hidden sm:inline-block h-8" @click="layout = 'justified'" :title="$t('gallery.layout.justified')">
		<MiniIcon icon="justified" fill="" :class="justifiedClass" />
	</a>
	<a class="px-1 cursor-pointer group hidden sm:inline-block h-8" @click="layout = 'masonry'" :title="$t('gallery.layout.masonry')">
		<MiniIcon icon="masonry" fill="fill-transparent" :class="masonryClass" />
	</a>
	<a class="px-1 cursor-pointer group hidden sm:inline-block h-8" @click="layout = 'grid'" :title="$t('gallery.layout.grid')">
		<MiniIcon icon="grid" fill="fill-transparent" :class="gridClass" />
	</a>
</template>
<script setup lang="ts">
import { Ref } from "vue";
import MiniIcon from "@/components/icons/MiniIcon.vue";
import { useLayoutClass } from "@/layouts/PhotoLayout";

const layout = defineModel("layout") as Ref<App.Enum.PhotoLayoutType>;

// Layouts stuff
const { squareClass, justifiedClass, masonryClass, gridClass } = useLayoutClass(layout);
</script>
