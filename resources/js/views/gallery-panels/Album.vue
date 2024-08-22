<template>
	<AlbumHeader v-if="album && config && user" :album="album" :config="config" :user="user" v-model:are-details-open="areDetailsOpen" @refresh="refresh" />
	<template v-if="config && album">
		<div class="relative flex flex-wrap content-start w-full justify-start overflow-y-auto h-[calc(100vh-66px)]">
			<AlbumEdit v-model="areDetailsOpen" v-if="album.rights.can_edit" :album="album" :config="config" />
			<div v-if="noData" class="flex w-full h-full items-center justify-center text-xl text-muted-color">
				<span class="block">
					{{ "Nothing to see here" }}
				</span>
			</div>
			<AlbumHero v-if="!noData" :album="album" @open-sharing-modal="openSharingModal" />
			<AlbumThumbPanel
				v-if="children !== null && children.length > 0"
				header="lychee.ALBUMS"
				:albums="children"
				:config="config"
				:is-alone="!photos?.length"
			/>
			<PhotoThumbPanel
				v-if="layout !== null && photos !== null && photos.length > 0"
				header="lychee.PHOTOS"
				:photos="photos"
				:album="album"
				:config="config"
				:gallery-config="layout"
			/>
		</div>
	</template>
	<ShareAlbum ref="shareAlbum" v-if="album" :title="album.title" :url="route.path"></ShareAlbum>
</template>
<script setup lang="ts">
import { useAuthStore } from "@/stores/Auth";
import AlbumService from "@/services/album-service";
import { Ref, computed, ref } from "vue";
import { watch } from "vue";
import { useRoute } from "vue-router";
import AlbumThumbPanel from "@/components/gallery/AlbumThumbPanel.vue";
import PhotoThumbPanel from "@/components/gallery/PhotoThumbPanel.vue";
import ShareAlbum from "@/components/modals/ShareAlbum.vue";
import AlbumHero from "@/components/gallery/AlbumHero.vue";
import AlbumEdit from "@/components/drawers/AlbumEdit.vue";
import AlbumHeader from "@/components/headers/AlbumHeader.vue";

const areDetailsOpen = ref(false);
const props = defineProps<{
	albumid: string;
}>();
const shareAlbum = ref();
const isLoginOpen = ref(false);
const albumid = ref(props.albumid);
const auth = useAuthStore();
const user = ref(undefined) as Ref<undefined | App.Http.Resources.Models.UserResource>;

const modelAlbum = ref(undefined) as Ref<undefined | App.Http.Resources.Models.AlbumResource>;
const tagAlbum = ref(undefined) as Ref<undefined | App.Http.Resources.Models.TagAlbumResource>;
const smartAlbum = ref(undefined) as Ref<undefined | App.Http.Resources.Models.SmartAlbumResource>;

const album = computed<
	undefined | App.Http.Resources.Models.AlbumResource | App.Http.Resources.Models.TagAlbumResource | App.Http.Resources.Models.SmartAlbumResource
>(() => modelAlbum.value || tagAlbum.value || smartAlbum.value);
const config = ref(null) as Ref<null | App.Http.Resources.GalleryConfigs.AlbumConfig>;
const layout = ref(null) as Ref<null | App.Http.Resources.GalleryConfigs.PhotoLayoutConfig>;
const photos = ref([]) as Ref<App.Http.Resources.Models.PhotoResource[]>;
const route = useRoute();
const noData = computed(() => children.value.length === 0 && (photos.value === null || photos.value.length === 0));
const children = computed<App.Http.Resources.Models.ThumbAlbumResource[]>(() => modelAlbum.value?.albums ?? []);

AlbumService.getLayout().then((data) => {
	layout.value = data.data;
});

function refresh() {
	auth.getUser().then((data: App.Http.Resources.Models.UserResource) => {
		user.value = data;
	});

	AlbumService.get(albumid.value)
		.then((data) => {
			console.log(data.data);
			config.value = data.data.config;
			modelAlbum.value = undefined;
			tagAlbum.value = undefined;
			smartAlbum.value = undefined;
			if (data.data.config.is_model_album) {
				modelAlbum.value = data.data.resource as App.Http.Resources.Models.AlbumResource;
			} else if (data.data.config.is_base_album) {
				tagAlbum.value = data.data.resource as App.Http.Resources.Models.TagAlbumResource;
			} else {
				smartAlbum.value = data.data.resource as App.Http.Resources.Models.SmartAlbumResource;
			}
			photos.value = album.value?.photos ?? [];
		})
		.catch((error) => {
			// We are required to login :)
			if (error.response.status === 401) {
				isLoginOpen.value = true;
			} else {
				console.error(error);
			}
		});
}

function openSharingModal() {
	shareAlbum.value.toggleModal();
}

refresh();

watch(
	() => route.params.albumid,
	(newId, oldId) => {
		console.log("newId", newId, "oldId", oldId);
		albumid.value = newId as string;
		refresh();
	},
);
</script>
