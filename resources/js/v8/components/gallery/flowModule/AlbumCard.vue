<template>
	<div
		class="w-full md:w-2xl rounded-2xl overflow-hidden"
		:style="{
			'--header-height': props.config.image_header_height + 'rem',
			'--carousel-height': props.config.carousel_height + 'rem',
			'--top-images-height': props.config.image_header_height / 2 + 'rem',
		}"
	>
		<HeaderImage
			v-if="header"
			:header="header"
			:image_header_cover="props.config.image_header_cover"
			:title="props.album.title"
			:is-nsfw="props.album.is_nsfw"
			:is-more-open="seeMore"
			@clicked="headerClicked"
		/>
		<TopImages v-else :photos="props.album.photos" :album-id="props.album.id" :is-nsfw="props.album.is_nsfw" @clicked="carouselClicked" />
		<CarouselImages
			v-if="props.config.is_image_header_enabled && props.config.is_carousel_enabled && props.album.photos.length > 1"
			:album-id="props.album.id"
			:photos="props.album.photos"
			:drop-first="props.config.is_highlight_first_picture"
			:is-nsfw="props.album.is_nsfw"
			@clicked="carouselClicked"
		/>
		<div class="p-6 bg-elevated">
			<h3>
				<span class="text-2xl font-semibold text-highlighted">{{ props.album.title }}</span>
				<span v-if="props.album.owner_name" class="text-muted mx-2">{{ sprintf($t("flow.by_author"), props.album.owner_name) }}</span>
			</h3>
			<p v-if="!seeMore" class="text-muted text-xs">
				<UTooltip :text="props.album.published_created_at">
					<span>{{ props.album.diff_published_created_at }}</span>
				</UTooltip>
			</p>
			<template v-else>
				<p v-if="!props.album.min_max_text" class="text-sm text-muted">
					{{ props.album.published_created_at }}
				</p>
				<p v-if="props.album.min_max_text" class="text-sm text-muted">
					<MiniIcon icon="camera-slr" class="w-3 h-3 m-0 mr-1 -mt-1 fill-muted" />{{ props.album.min_max_text }}
				</p>
				<p v-if="props.album.num_children" class="block text-muted text-sm">
					{{ props.album.num_children }} {{ $t("gallery.album.hero.subalbums") }}
				</p>
				<p v-if="props.album.num_photos" class="block text-muted text-sm">
					{{ props.album.num_photos }} {{ $t("gallery.album.hero.images") }}
				</p>
			</template>
			<p
				v-if="props.album.description"
				:class="{
					'text-sm text-muted prose prose-sm dark:prose-invert max-w-full my-8': true,
					'line-clamp-3': !seeMore && hasMore,
				}"
				v-html="props.album.description"
			></p>
			<div class="flex justify-between items-end flex-row-reverse">
				<UButton
					v-if="props.config.is_display_open_album_button"
					as="a"
					:href="router.resolve({ name: 'flow-album', params: { albumId: props.album.id } }).href"
					color="neutral"
					class="font-bold -mb-2 -mx-2"
				>
					{{ $t("flow.open_album") }}
					<UIcon v-if="isLTR()" name="lucide:chevrons-right" />
					<UIcon v-if="!isLTR()" name="lucide:chevrons-left" />
				</UButton>
				<UButton
					v-if="hasMore && !seeMore"
					variant="ghost"
					class="font-bold text-muted hover:text-highlighted cursor-pointer p-0"
					color="neutral"
					@click="
						() => {
							seeMore = true;
						}
					"
				>
					{{ $t("flow.show_more") }}
				</UButton>
				<AlbumStatistics v-if="props.album.statistics && seeMore" :stats="props.album.statistics" />
			</div>
		</div>
	</div>
</template>
<script setup lang="ts">
import { computed } from "vue";
import { useRouter } from "vue-router";
import HeaderImage from "./HeaderImage.vue";
import TopImages from "./TopImages.vue";
import CarouselImages from "./CarouselImages.vue";
import AlbumStatistics from "@/v8/components/gallery/albumModule/AlbumStatistics.vue";
import { ref } from "vue";
import MiniIcon from "@/v8/components/icons/MiniIcon.vue";
import { sprintf } from "sprintf-js";
import { useLtRorRtL } from "@/utils/Helpers";

const { isLTR } = useLtRorRtL();

const router = useRouter();
const seeMore = ref(false);

const props = defineProps<{
	album: App.Http.Resources.Flow.FlowItemResource;
	config: App.Http.Resources.Flow.InitResource;
}>();

const emits = defineEmits<{
	setSelection: [album: App.Http.Resources.Flow.FlowItemResource, idx: number];
}>();

function headerClicked() {
	emits("setSelection", props.album, 0);
}

function carouselClicked(idx: number) {
	emits("setSelection", props.album, idx);
}

const hasMore = computed(() => props.config.is_compact_mode_enabled && props.config.is_image_header_enabled);

const header = computed<App.Http.Resources.Models.SizeVariantsResouce | undefined>(() => {
	if (!props.config.is_image_header_enabled) {
		return undefined;
	}

	let photo: App.Http.Resources.Models.SizeVariantsResouce | null = null;
	if (!props.config.is_highlight_first_picture) {
		photo = props.album.cover;
	}

	return photo ?? props.album.photos[0]?.size_variants ?? undefined;
});
</script>
