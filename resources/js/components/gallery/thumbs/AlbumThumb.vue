<template>
	<router-link
		:to="{ name: 'album', params: { albumid: album.id } }"
		class="album-thumb block relative w-1/4 min-w-32 sm:w-[calc(25vw)] md:w-[calc(20vw)] lg:w-[calc(16vw)] xl:w-[calc(14vw)] 2xl:w-52 animate-zoomIn group"
		:class="(lycheeStore.are_nsfw_blurred ? 'blurred' : '') + ' ' + props.asepct_ratio"
		:data-id="props.album.id"
	>
		<AlbumThumbImage
			class="group-hover:border-primary-500 group-hover:-rotate-2 group-hover:-translate-x-3 group-hover:translate-y-2"
			:class="cssClass"
			:thumb="props.album.thumb"
		/>
		<AlbumThumbImage
			class="group-hover:border-primary-500 group-hover:rotate-6 group-hover:translate-x-3 group-hover:-translate-y-2"
			:class="cssClass"
			:thumb="props.album.thumb"
		/>
		<AlbumThumbImage class="group-hover:border-primary-500" :class="cssClass" :thumb="props.album.thumb" />
		<div
			class="overlay absolute mb-[1px] mx-[1px] p-0 border-0 w-[calc(100%-2px)] bottom-0 bg-gradient-to-t from-[#00000099] text-shadow-sm"
			:class="cssOverlay"
		>
			<h1
				class="w-full pt-3 pb-1 pr-1 pl-2 sm:pl-3 md:pl-4 text-sm text-surface-0 font-bold text-ellipsis whitespace-nowrap overflow-x-hidden"
				:title="props.album.title"
			>
				{{ props.album.title }}
			</h1>
			<span
				class="block mt-0 mr-0 mb-1.5 sm:mb-3 ml-2 sm:ml-3 md:ml-4 text-2xs text-surface-300"
				v-if="props.album_subtitle_type === 'description'"
			>
				{{ props.album.description }}
			</span>
			<span
				class="block mt-0 mr-0 mb-1.5 sm:mb-3 ml-2 sm:ml-3 md:ml-4 text-2xs text-surface-300"
				v-if="props.album_subtitle_type === 'takedate'"
			>
				<MiniIcon icon="camera" class="fill-neutral-200 w-3 h-3"></MiniIcon>{{ album.formatted_min_max ?? album.created_at }}
			</span>
			<span
				class="block mt-0 mr-0 mb-1.5 sm:mb-3 ml-2 sm:ml-3 md:ml-4 text-2xs text-surface-300"
				v-if="props.album_subtitle_type === 'creation'"
			>
				<MiniIcon icon="camera" class="fill-neutral-200 w-3 h-3"></MiniIcon>{{ album.created_at }}
			</span>
			<span
				class="block mt-0 mr-0 mb-1.5 sm:mb-3 ml-2 sm:ml-3 md:ml-4 text-2xs text-surface-300"
				v-if="props.album_subtitle_type === 'oldstyle'"
			>
				{{ album.formatted_min_max ?? album.created_at }}
			</span>
		</div>
		<span v-if="album.thumb?.type.includes('video')" class="w-full h-full absolute hover:opacity-70 transition-opacity duration-300">
			<img class="h-full w-full" alt="play" :src="play_icon" />
		</span>

		<div v-if="user?.id !== null" class="badges absolute mt-[-1px] ml-1 top-0 left-0">
			<ThumbBadge v-if="props.album.is_nsfw" class="badge--nsfw bg-[#ff82ee]" icon="warning" />
			<ThumbBadge v-if="props.album.id === 'starred'" class="badge--star bg-yellow-500" icon="star" />
			<ThumbBadge v-if="props.album.id === 'unsorted'" class="badge--unsorted bg-red-700" icon="list" />
			<ThumbBadge v-if="props.album.id === 'recent'" class="badge--recent bg-blue-700" icon="clock" />
			<ThumbBadge v-if="props.album.id === 'on_this_day'" class="badge--onthisday bg-green-600" icon="calendar" />
			<ThumbBadge
				v-if="props.album.is_public"
				:class="'badge--ispublic ' + (props.album.is_link_required ? 'bg-orange-400' : 'bg-green-600')"
				icon="eye"
			/>
			<ThumbBadge v-if="props.album.is_password_required" class="badge--locked bg-orange-400" icon="lock-locked" />
			<ThumbBadge v-if="props.album.is_tag_album" class="badge--tag bg-green-600" icon="download" />
			<ThumbBadge v-if="props.cover_id === props.album.thumb?.id" class="badge--cover bg-yellow-500" icon="folder-cover" />
		</div>
		<div
			v-if="album.has_subalbum"
			class="album_counters absolute right-2 top-1.5 flex flex-row gap-1 justify-end text-right font-bold font-sans drop-shadow-md"
		>
			<div class="layers relative py-1 px-1">
				<MiniIcon icon="layers" class="fill-white w-3 h-3" />
			</div>
		</div>
	</router-link>
</template>
<script setup lang="ts">
import { computed, ref, type Ref } from "vue";
import MiniIcon from "@/components/icons/MiniIcon.vue";
import ThumbBadge from "@/components/gallery/thumbs/ThumbBadge.vue";
import AlbumThumbImage from "@/components/gallery/thumbs/AlbumThumbImage.vue";
import { useAuthStore } from "@/stores/Auth";
import { useLycheeStateStore } from "@/stores/LycheeState";

const props = defineProps<{
	isSelected: boolean;
	cover_id: string | null;
	album: App.Http.Resources.Models.ThumbAlbumResource;
	asepct_ratio: string;
	album_subtitle_type: App.Enum.ThumbAlbumSubtitleType;
}>();

const auth = useAuthStore();
const lycheeStore = useLycheeStateStore();

const cssClass = computed(() => {
	let css = "";
	if (props.isSelected) {
		css += "outline outline-1.5 outline-primary-500";
	}
	return css;
});

const user = ref(null) as Ref<App.Http.Resources.Models.UserResource | null>;
auth.getUser().then((data) => {
	user.value = data;
});

const cssOverlay = computed(() => {
	if (lycheeStore.display_thumb_album_overlay === "never") {
		return "hidden";
	}
	if (lycheeStore.display_thumb_album_overlay === "hover") {
		return "opacity-0 group-hover:opacity-100 transition-all ease-out";
	}
	return "";
});

const play_icon = ref(window.assets_url + "/img/play-icon.png");
</script>
