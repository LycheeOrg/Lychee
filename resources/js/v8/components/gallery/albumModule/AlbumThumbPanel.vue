<template>
	<div v-if="isTimeline === false" class="border-0 w-full px-4" :class="paddingTopClass">
		<template v-if="isAlone">
			<div class="flex flex-wrap flex-row shrink w-full justify-start gap-1 sm:gap-2 md:gap-4 pt-4">
				<AlbumListView
					v-if="album_view_mode === 'list'"
					:albums="props.albums"
					:selected-ids="props.selectedAlbums"
					@clicked="propagateClicked"
					@selected="propagateSelected"
					@contexted="propagateContexted"
				/>
				<AlbumThumbPanelList
					v-else
					:albums="props.albums"
					:selected-albums="props.selectedAlbums"
					@clicked="propagateClicked"
					@selected="propagateSelected"
					@contexted="propagateContexted"
				/>
			</div>
		</template>
		<UCollapsible v-else v-model:open="isOpen" class="w-full">
			<button type="button" class="flex items-center gap-2 ltr:pr-4 rtl:pl-4 text-left font-semibold text-highlighted py-2">
				<UIcon name="lucide:chevron-down" class="transition-transform" :class="{ '-rotate-90': !isOpen }" />
				{{ $t(props.header) }}
			</button>
			<template #content>
				<div class="flex flex-wrap flex-row shrink w-full justify-start gap-1 sm:gap-2 md:gap-4 pt-4">
					<AlbumListView
						v-if="album_view_mode === 'list'"
						:albums="props.albums"
						:selected-ids="props.selectedAlbums"
						@clicked="propagateClicked"
						@selected="propagateSelected"
						@contexted="propagateContexted"
					/>
					<AlbumThumbPanelList
						v-else
						:albums="props.albums"
						:selected-albums="props.selectedAlbums"
						@clicked="propagateClicked"
						@selected="propagateSelected"
						@contexted="propagateContexted"
					/>
				</div>
			</template>
		</UCollapsible>
	</div>
	<div v-else class="border-0 w-full">
		<template v-for="(albumTimeline, idx) in albumsTimeLine" :key="'albumTimeline' + idx">
			<div
				v-if="albumTimeline.data.filter((a) => !a.is_nsfw || are_nsfw_visible).length > 0"
				class="flex flex-wrap flex-row shrink w-full justify-start gap-1 sm:gap-2 md:gap-4 pb-8"
				:class="{ 'ltr:border-l rtl:border-r border-accented ltr:pl-4 rtl:pr-4': isLeftBorderVisible }"
			>
				<div class="w-full ltr:text-left rtl:text-right font-semibold text-toned text-lg">{{ albumTimeline.header }}</div>

				<AlbumListView
					v-if="album_view_mode === 'list'"
					:albums="albumTimeline.data"
					:selected-ids="props.selectedAlbums"
					@clicked="propagateClicked"
					@contexted="propagateContexted"
				/>

				<AlbumThumbPanelList
					v-else
					:albums="albumTimeline.data"
					:selected-albums="props.selectedAlbums"
					@clicked="propagateClicked"
					@contexted="propagateContexted"
				/>
			</div>
		</template>
	</div>
</template>
<script setup lang="ts">
import { computed, onMounted, ref, watch } from "vue";
import { type SplitData, useSplitter } from "@/composables/album/splitter";
import { useLycheeStateStore } from "@/stores/LycheeState";
import { storeToRefs } from "pinia";
import AlbumThumbPanelList from "./AlbumThumbPanelList.vue";
import AlbumListView from "./AlbumListView.vue";
import { isTouchDevice } from "@/utils/keybindings-utils";
import { usePropagateAlbumEvents } from "@/composables/album/propagateEvents";

const lycheeStore = useLycheeStateStore();
const { are_nsfw_visible, is_timeline_left_border_visible, is_debug_enabled, album_view_mode } = storeToRefs(lycheeStore);

const props = defineProps<{
	header: string;
	albums: App.Http.Resources.Models.ThumbAlbumResource[];
	isAlone: boolean;
	selectedAlbums: string[];
	isTimeline: boolean;
}>();

const isOpen = ref(true);

// bubble up.
const emits = defineEmits<{
	clicked: [event: MouseEvent, id: string];
	selected: [event: MouseEvent, id: string];
	contexted: [event: MouseEvent, id: string];
}>();
const { propagateClicked, propagateContexted } = usePropagateAlbumEvents(emits);

function propagateSelected(event: MouseEvent, id: string) {
	emits("selected", event, id);
}

const { spliter, verifyOrder } = useSplitter();

const albumsTimeLine = computed<SplitData<App.Http.Resources.Models.ThumbAlbumResource>[]>(() =>
	split(props.albums as App.Http.Resources.Models.ThumbAlbumResource[]),
);

const isTimeline = computed(() => props.isTimeline && albumsTimeLine.value.length > 1);

// We do not show the left border on touch devices (mostly phones) due to limited real estate.
const isLeftBorderVisible = computed(() => is_timeline_left_border_visible.value && !isTouchDevice());

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
