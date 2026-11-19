<template>
	<div class="flex flex-col h-full">
		<div class="flex items-center justify-between p-2 border-b border-default mb-2">
			<div class="flex items-center gap-1 shrink-0">
				<UButton
					icon="lucide:chevrons-down"
					color="neutral"
					variant="ghost"
					size="xs"
					:label="$t('gallery.nav_tree.expand_all')"
					@click="expandAll"
				/>
				<UButton
					icon="lucide:chevrons-up"
					color="neutral"
					variant="ghost"
					size="xs"
					:label="$t('gallery.nav_tree.collapse_all')"
					@click="collapseAll"
				/>
			</div>
			<slot name="collapse" />
		</div>

		<div ref="scrollParentRef" class="album-nav-scroll overflow-y-auto flex-1 min-h-0" style="contain: strict">
			<div :style="{ position: 'relative', height: `${totalSize}px`, width: '100%' }">
				<div
					v-for="item in virtualRows"
					:key="item.row.node.id"
					class="absolute top-0 left-0 w-full flex items-center gap-1.5 pe-2"
					:class="{ 'cursor-pointer': item.row.hasChildren }"
					:style="{
						height: `${item.size}px`,
						transform: `translate3d(0, ${item.start}px, 0)`,
						contain: 'layout size paint',
						paddingInlineStart: `${item.row.depth + 0.5}rem`,
					}"
					@click="item.row.hasChildren ? toggle(item.row.node.id) : navigate(item.row.node.id)"
				>
					<Thumb
						:album-id="item.row.node.id"
						:photo-id="item.row.node.coverId"
						class="w-5 h-5 rounded object-cover shrink-0"
						type="small"
					/>

					<RouterLink
						:to="{ name: 'album', params: { albumId: item.row.node.id } }"
						class="truncate min-w-0 text-xs hover:underline"
						:class="item.row.node.id === activeAlbumId ? 'text-primary font-medium' : 'text-default'"
						:title="item.row.node.title"
						@click.stop
					>
						{{ item.row.node.title }}
					</RouterLink>

					<MiniIcon
						v-if="item.row.hasChildren"
						icon="layers"
						fill="fill-current"
						:class="`w-3 h-3 shrink-0 ms-auto ${item.row.node.id === activeAlbumId ? 'text-primary' : 'text-muted'} ${
							isExpanded(item.row.node.id) ? 'opacity-50' : 'opacity-100'
						}`"
					/>
				</div>
			</div>
		</div>
	</div>
</template>

<script setup lang="ts">
import { computed, ref } from "vue";
import { useRoute, useRouter } from "vue-router";
import { useVirtualizer } from "@tanstack/vue-virtual";
import { useAlbumListStore } from "@/stores/AlbumListState";
import { useAlbumNavFlatTree } from "@/v8/composables/album/albumNavTree";
import Thumb from "@/v8/components/thumbs/Thumb.vue";
import MiniIcon from "@/v8/components/icons/MiniIcon.vue";

const ROW_HEIGHT = 24;

const route = useRoute();
const router = useRouter();
const albumListStore = useAlbumListStore();

function navigate(albumId: string): void {
	router.push({ name: "album", params: { albumId } });
}

const activeAlbumId = computed(() => route.params.albumId as string | undefined);
const tree = computed(() => albumListStore.tree);
const rows = computed(() => albumListStore.rows);

const { flatRows, isExpanded, toggle, expandAll, collapseAll } = useAlbumNavFlatTree(tree, activeAlbumId, rows);

const scrollParentRef = ref<HTMLElement | null>(null);

const virtualizer = useVirtualizer(
	computed(() => ({
		count: flatRows.value.length,
		getScrollElement: () => scrollParentRef.value,
		estimateSize: () => ROW_HEIGHT,
		overscan: 8,
		getItemKey: (index: number) => flatRows.value[index]?.node.id ?? index,
	})),
);

const totalSize = computed(() => virtualizer.value.getTotalSize());
const virtualRows = computed(() => virtualizer.value.getVirtualItems().map((item) => ({ ...item, row: flatRows.value[item.index] })));
</script>

<style scoped>
.album-nav-scroll::-webkit-scrollbar {
	display: none;
}
.album-nav-scroll {
	-ms-overflow-style: none; /* IE and Edge */
	scrollbar-width: none; /* Firefox */
}
</style>
