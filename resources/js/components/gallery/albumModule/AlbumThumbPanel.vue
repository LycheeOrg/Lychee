<template>
	<Panel
		v-if="isTimeline === false"
		:header="$t(props.header)"
		:toggleable="!isAlone"
		:pt:header:class="headerClass"
		class="border-0 w-full"
		:class="paddingTopClass"
	>
		<!-- Grid view (existing) -->
		<div class="flex flex-wrap flex-row shrink w-full justify-start gap-1 sm:gap-2 md:gap-4 pt-4">
			<AlbumListView
				v-if="album_view_mode === 'list'"
				:albums="props.albums"
				:selected-ids="props.selectedAlbums"
				@album-clicked="propagateClickedFromList"
				@album-contexted="propagateMenuOpenFromList"
			/>
			<AlbumThumbPanelList
				v-else
				:albums="props.albums"
				:is-interactive="props.isInteractive"
				:selected-albums="props.selectedAlbums"
				@clicked="(id, e) => emits('clicked', id, e)"
				@contexted="(id, e) => emits('contexted', id, e)"
			/>
		</div>
	</Panel>
	<Panel v-else :header="'Timeline'" :toggleable="!isAlone" :pt:header:class="headerClass" class="border-0 w-full">
		<Timeline
			v-if="isLeftBorderVisible"
			:value="albumsTimeLine"
			:pt:eventopposite:class="'hidden'"
			class="mt-4"
			:align="isLTR() ? 'left' : 'right'"
		>
			<template #content="slotProps">
				<div :dir="isLTR() ? 'ltr' : 'rtl'" class="flex flex-wrap flex-row shrink w-full justify-start gap-1 sm:gap-2 md:gap-4 pb-8">
					<div class="w-full ltr:text-left rtl:text-right font-semibold text-muted-color-emphasis text-lg">{{ slotProps.item.header }}</div>

					<!-- List view -->
					<AlbumListView
						v-if="album_view_mode === 'list'"
						:albums="slotProps.item.data"
						:selected-ids="props.selectedAlbums"
						@album-clicked="propagateClickedFromList"
						@album-contexted="propagateMenuOpenFromList"
					/>

					<AlbumThumbPanelList
						v-else
						:albums="slotProps.item.data"
						:is-interactive="props.isInteractive"
						:selected-albums="props.selectedAlbums"
						@clicked="(id, e) => emits('clicked', id, e)"
						@contexted="(id, e) => emits('contexted', id, e)"
					/>
				</div>
			</template>
		</Timeline>
		<div v-else>
			<template v-for="(albumTimeline, idx) in albumsTimeLine" :key="'albumTimeline' + idx">
				<div
					v-if="albumTimeline.data.filter((a) => !a.is_nsfw || are_nsfw_visible).length > 0"
					class="flex flex-wrap flex-row shrink w-full justify-start gap-1 sm:gap-2 md:gap-4 pb-8"
				>
					<div class="w-full ltr:text-left rtl:text-right font-semibold text-muted-color-emphasis text-lg">{{ albumTimeline.header }}</div>

					<AlbumListView
						v-if="album_view_mode === 'list'"
						:albums="albumTimeline.data"
						:selected-ids="props.selectedAlbums"
						@album-clicked="propagateClickedFromList"
						@album-contexted="propagateMenuOpenFromList"
					/>

					<AlbumThumbPanelList
						v-else
						:albums="albumTimeline.data"
						:is-interactive="props.isInteractive"
						:selected-albums="props.selectedAlbums"
						@clicked="(id, e) => emits('clicked', id, e)"
						@contexted="(id, e) => emits('contexted', id, e)"
					/>
				</div>
			</template>
		</div>
	</Panel>
</template>
<script setup lang="ts">
import Panel from "primevue/panel";
import { computed, onMounted, watch } from "vue";
import { type SplitData, useSplitter } from "@/composables/album/splitter";
import Timeline from "primevue/timeline";
import { useLycheeStateStore } from "@/stores/LycheeState";
import { storeToRefs } from "pinia";
import AlbumThumbPanelList from "./AlbumThumbPanelList.vue";
import AlbumListView from "./AlbumListView.vue";
import { useLtRorRtL } from "@/utils/Helpers";
import { isTouchDevice } from "@/utils/keybindings-utils";

const { isLTR } = useLtRorRtL();

const lycheeStore = useLycheeStateStore();
const { are_nsfw_visible, is_timeline_left_border_visible, is_debug_enabled, album_view_mode } = storeToRefs(lycheeStore);

const props = defineProps<{
	header: string;
	albums: App.Http.Resources.Models.ThumbAlbumResource[];
	isAlone: boolean;
	isInteractive?: boolean;
	selectedAlbums: string[];
	isTimeline: boolean;
}>();

// bubble up.
const emits = defineEmits<{
	clicked: [id: string, event: MouseEvent];
	contexted: [id: string, event: MouseEvent];
}>();

const { spliter, verifyOrder } = useSplitter();

// Handlers for list view - emit album ID directly
const propagateClickedFromList = (e: MouseEvent, album: App.Http.Resources.Models.ThumbAlbumResource) => {
	emits("clicked", album.id, e);
};

const propagateMenuOpenFromList = (e: MouseEvent, album: App.Http.Resources.Models.ThumbAlbumResource) => {
	emits("contexted", album.id, e);
};

const albumsTimeLine = computed<SplitData<App.Http.Resources.Models.ThumbAlbumResource>[]>(() =>
	split(props.albums as App.Http.Resources.Models.ThumbAlbumResource[]),
);

const isTimeline = computed(() => props.isTimeline && albumsTimeLine.value.length > 1);

// We do not show the left border on touch devices (mostly phones) due to limited real estate.
const isLeftBorderVisible = computed(() => is_timeline_left_border_visible.value && !isTouchDevice());

const headerClass = computed(() => {
	return props.isAlone ? "hidden" : "";
});

const paddingTopClass = computed(() => {
	return props.isAlone && lycheeStore.album_view_mode === "list" ? "pt-4" : "pt-0";
});

function split(albums: App.Http.Resources.Models.ThumbAlbumResource[]) {
	return spliter(
		albums,
		(a: App.Http.Resources.Models.ThumbAlbumResource) => a.timeline?.time_date ?? "",
		(a: App.Http.Resources.Models.ThumbAlbumResource) => a.timeline?.format ?? "Others",
	);
}

function validate(albums: App.Http.Resources.Models.ThumbAlbumResource[]) {
	if (props.isTimeline) {
		const splitted = split(albums);
		verifyOrder(is_debug_enabled.value, albums, splitted);
	}
}

onMounted(() => {
	validate(props.albums as App.Http.Resources.Models.ThumbAlbumResource[]);
});

watch(
	() => props.isTimeline,
	() => {
		if (props.isTimeline) {
			validate(props.albums as App.Http.Resources.Models.ThumbAlbumResource[]);
		}
	},
);
</script>
