<template>
	<UButton
		v-if="!isNavOpen"
		icon="lucide:folder-tree"
		color="neutral"
		variant="soft"
		size="lg"
		class="fixed bottom-4 inset-s-4 z-40 rounded-full shadow-lg"
		square
		@click="isNavOpen = true"
	/>

	<div
		v-if="isDesktop"
		class="sticky top-(--ui-header-height) h-[calc(100svh-var(--ui-header-height))] overflow-hidden shrink-0 transition-[width] duration-200"
		:class="isNavOpen ? 'w-(--nav-bar-width)' : 'w-0'"
	>
		<div class="w-(--nav-bar-width) h-full flex flex-col border-e border-default">
			<AlbumNavTree class="flex-1 min-h-0">
				<template #collapse>
					<!-- <div class="flex justify-end p-2 shrink-0"> -->
					<UButton icon="lucide:panel-left-close" color="neutral" variant="ghost" size="sm" square @click="isNavOpen = false" />
					<!-- </div> -->
				</template>
			</AlbumNavTree>
		</div>
	</div>

	<USlideover v-else v-model:open="isNavOpen" side="left">
		<template #content>
			<AlbumNavTree class="h-full" />
		</template>
	</USlideover>
</template>

<script setup lang="ts">
import { watch, watchEffect } from "vue";
import { useRoute } from "vue-router";
import { breakpointsTailwind, useBreakpoints } from "@vueuse/core";
import { useAlbumListStore } from "@/stores/AlbumListState";
import AlbumNavTree from "@/v8/components/gallery/albumModule/AlbumNavTree.vue";
import { useTogglablesStateStore } from "@/stores/ModalsState";
import { storeToRefs } from "pinia";

const route = useRoute();
const albumListStore = useAlbumListStore();
const togglablesStateStore = useTogglablesStateStore();

const breakpoints = useBreakpoints(breakpointsTailwind);
const isDesktop = breakpoints.greaterOrEqual("lg");
const { isNavOpen } = storeToRefs(togglablesStateStore);

// Matches `UDashboardSidebar`'s own default `autoClose` behavior it replaces: close the mobile
// off-canvas drawer on any navigation, not just an album change.
watch(
	() => route.fullPath,
	() => {
		if (!isDesktop.value) {
			isNavOpen.value = false;
		}
	},
);

// A plain one-shot `onMounted` call would be orphaned by an `invalidate()` (e.g. `Albums.vue`'s
// own mount-time `refresh()`) landing while this component's initial load is still in flight -
// the response gets discarded via the store's generation guard and nothing re-fetches it.
// Watching keeps re-triggering `ensureLoaded()` whenever the store drops back to unloaded.
watchEffect(() => {
	if (!albumListStore.isLoaded && !albumListStore.isLoading) {
		albumListStore.ensureLoaded();
	}
});
</script>
