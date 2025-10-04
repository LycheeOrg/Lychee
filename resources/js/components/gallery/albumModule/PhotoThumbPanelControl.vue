<template>
	<a class="px-1 cursor-pointer group hidden sm:inline-block h-8" :title="$t('gallery.layout.squares')" @click="layout = 'square'">
		<MiniIcon icon="squares" fill="fill-transparent" :class="layoutStore.squareClass" />
	</a>
	<a class="px-1 cursor-pointer group hidden sm:inline-block h-8" :title="$t('gallery.layout.justified')" @click="layout = 'justified'">
		<MiniIcon icon="justified" fill="" :class="layoutStore.justifiedClass" />
	</a>
	<a class="px-1 cursor-pointer group hidden sm:inline-block h-8" :title="$t('gallery.layout.masonry')" @click="layout = 'masonry'">
		<MiniIcon icon="masonry" fill="fill-transparent" :class="layoutStore.masonryClass" />
	</a>
	<a class="px-1 cursor-pointer group hidden sm:inline-block h-8" :title="$t('gallery.layout.grid')" @click="layout = 'grid'">
		<MiniIcon icon="grid" fill="fill-transparent" :class="layoutStore.gridClass" />
	</a>
</template>
<script setup lang="ts">
import MiniIcon from "@/components/icons/MiniIcon.vue";
import { useLayoutStore } from "@/stores/LayoutState";
import { storeToRefs } from "pinia";

const layoutStore = useLayoutStore();
const { layout } = storeToRefs(layoutStore);
</script>
