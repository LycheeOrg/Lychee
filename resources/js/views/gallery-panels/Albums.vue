<template>
	<div v-if="rootConfig && rootRights">
		<AlbumsHeader v-model:is-login-open="isLoginOpen" v-if="user" :user="user" title="lychee.ALBUMS" :rights="rootRights" @refresh="refresh" />
		<AlbumThumbPanel
			v-if="smartAlbums.length > 0"
			header="lychee.SMART_ALBUMS"
			:albums="smartAlbums"
			:user="user"
			:config="rootConfig"
			:is-alone="!albums.length"
		/>
		<AlbumThumbPanel
			v-if="albums.length > 0"
			header="lychee.ALBUMS"
			:albums="albums"
			:user="user"
			:config="rootConfig"
			:is-alone="!sharedAlbums.length"
		/>
		<AlbumThumbPanel
			v-if="sharedAlbums.length > 0"
			header="lychee.SHARED_ALBUMS"
			:albums="sharedAlbums"
			:user="user"
			:config="rootConfig"
			:is-alone="!albums.length"
		/>
	</div>
</template>
<script setup lang="ts">
import AlbumThumbPanel from "@/components/gallery/AlbumThumbPanel.vue";
import { useAuthStore } from "@/stores/Auth";
import AlbumService from "@/services/album-service";
import { Ref, ref } from "vue";
import AlbumsHeader from "@/components/headers/AlbumsHeader.vue";

const isLoginOpen = ref(false);

const user = ref(undefined) as Ref<undefined | App.Http.Resources.Models.UserResource>;

const smartAlbums = ref([]) as Ref<App.Http.Resources.Models.ThumbAlbumResource[]>;
const albums = ref([]) as Ref<App.Http.Resources.Models.ThumbAlbumResource[]>;
const sharedAlbums = ref([]) as Ref<App.Http.Resources.Models.ThumbAlbumResource[]>;
const rootConfig = ref(undefined) as Ref<undefined | App.Http.Resources.GalleryConfigs.RootConfig>;
const rootRights = ref(undefined) as Ref<undefined | App.Http.Resources.Rights.RootAlbumRightsResource>;
const auth = useAuthStore();

function refresh() {
	auth.getUser().then((data) => {
		user.value = data;
	});

	AlbumService.getAll()
		.then((data) => {
			smartAlbums.value = (data.data.smart_albums as App.Http.Resources.Models.ThumbAlbumResource[]) ?? [];
			albums.value = data.data.albums as App.Http.Resources.Models.ThumbAlbumResource[];
			smartAlbums.value = smartAlbums.value.concat(data.data.tag_albums as App.Http.Resources.Models.ThumbAlbumResource[]);
			sharedAlbums.value = (data.data.shared_albums as App.Http.Resources.Models.ThumbAlbumResource[]) ?? [];
			rootConfig.value = data.data.config;
			rootRights.value = data.data.rights;

			if (albums.value.length === 0 && smartAlbums.value.length === 0 && sharedAlbums.value.length === 0) {
				isLoginOpen.value = true;
			}
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

const emit = defineEmits<{
	(e: "toggleLeftMenu"): void;
}>();

refresh();
</script>
