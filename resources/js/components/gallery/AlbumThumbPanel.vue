<template>
	<Panel :header="$t(props.header)" :toggleable="!isAlone" :pt:header:class="headerClass" class="border-0 w-full">
		<div class="flex flex-wrap flex-row flex-shrink w-full justify-start align-top">
			<template v-for="album in props.albums">
				<AlbumThumb
					:album="album"
					:cover_id="null"
					:asepct_ratio="props.config.album_thumb_css_aspect_ratio"
					:album_subtitle_type="props.config.album_subtitle_type"
					v-if="!album.is_nsfw || props.areNsfwVisible"
				/>
			</template>
		</div>
	</Panel>
</template>
<script setup lang="ts">
import Panel from "primevue/panel";
import AlbumThumb from "@/components/gallery/thumbs/AlbumThumb.vue";
import { computed } from "vue";

const props = defineProps<{
	areNsfwVisible: boolean;
	header: string;
	album: App.Http.Resources.Models.AlbumResource | undefined | null;
	albums: { [key: number]: App.Http.Resources.Models.ThumbAlbumResource };
	config: { album_thumb_css_aspect_ratio: string; album_subtitle_type: App.Enum.ThumbAlbumSubtitleType };
	isAlone: boolean;
}>();

const headerClass = computed(() => {
	return props.isAlone ? "hidden" : "";
});
</script>
