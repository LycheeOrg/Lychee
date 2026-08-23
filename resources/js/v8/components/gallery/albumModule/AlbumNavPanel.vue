<template>
	<UButton
		icon="lucide:folder-tree"
		color="neutral"
		variant="solid"
		size="lg"
		class="lg:hidden fixed bottom-4 start-4 z-40 rounded-full shadow-lg"
		square
		@click="open = true"
	/>
	<UDashboardSidebar v-model:open="open" :toggle="false" :resizable="false" class="sticky top-0 h-svh">
		<UNavigationMenu
			orientation="vertical"
			:items="items"
			highlight
			:dir="isLTR() ? 'ltr' : 'rtl'"
			:ui="{ link: 'text-xs', childLink: 'text-xs' }"
		>
			<template #item-leading="{ item }">
				<Thumb :album-id="item.albumId" :photo-id="item.coverId" class="w-5 h-5 rounded object-cover shrink-0" />
			</template>
			<template #item-label="{ item }">
				<RouterLink
					v-if="item.children && item.children.length > 0"
					:to="{ name: 'album', params: { albumId: item.albumId } }"
					class="truncate hover:underline"
					:title="item.label"
					@click.stop
				>
					{{ item.label }}
				</RouterLink>
				<span v-else class="truncate" :title="item.label">{{ item.label }}</span>
			</template>
		</UNavigationMenu>
	</UDashboardSidebar>
</template>

<script setup lang="ts">
import { computed, onMounted } from "vue";
import { useRoute } from "vue-router";
import { useAlbumListStore, type AlbumTreeNode } from "@/stores/AlbumListState";
import { useLtRorRtL } from "@/utils/Helpers";
import Thumb from "@/v8/components/thumbs/Thumb.vue";
import type { NavigationMenuItem } from "@nuxt/ui";

type AlbumNavItem = NavigationMenuItem & { albumId: string; coverId: string | null };

const open = defineModel<boolean>("open", { default: false });

const { isLTR } = useLtRorRtL();
const route = useRoute();
const albumListStore = useAlbumListStore();

onMounted(() => {
	albumListStore.ensureLoaded();
});

// Items with children render as a pure accordion trigger (`to` omitted) so clicking anywhere
// on the row expands/collapses it instead of navigating - only the title, rendered as its own
// RouterLink in the `item-label` slot below, navigates. Leaf items keep `to` set, so the whole
// row navigates as usual since there's nothing to expand.
function toItem(node: AlbumTreeNode, activeId: string | undefined, activeLft: number | undefined, activeRgt: number | undefined): AlbumNavItem {
	const hasChildren = node.children.length > 0;
	const isAncestorOfActive = activeLft !== undefined && activeRgt !== undefined && node._lft < activeLft && node._rgt > activeRgt;
	return {
		label: node.title,
		to: hasChildren ? undefined : { name: "album", params: { albumId: node.id } },
		active: node.id === activeId,
		albumId: node.id,
		coverId: node.coverId,
		defaultOpen: hasChildren && isAncestorOfActive,
		children: hasChildren ? node.children.map((child) => toItem(child, activeId, activeLft, activeRgt)) : undefined,
	};
}

const items = computed<AlbumNavItem[]>(() => {
	const activeId = route.params.albumId as string | undefined;
	const activeRow = activeId !== undefined ? albumListStore.rows.find((r) => r.id === activeId) : undefined;
	return albumListStore.tree.map((node) => toItem(node, activeId, activeRow?._lft, activeRow?._rgt));
});
</script>
