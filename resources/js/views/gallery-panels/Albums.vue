<template>
	<KeybindingsHelp v-model:visible="isKeybindingsHelpOpen" v-if="user?.id" />
	<div v-if="rootConfig && rootRights">
		<AlbumsHeader
			v-model:is-login-open="isLoginOpen"
			v-if="user"
			:user="user"
			title="lychee.ALBUMS"
			:rights="rootRights"
			@refresh="refresh"
			@help="isKeybindingsHelpOpen = true"
			:config="rootConfig"
		/>
		<AlbumThumbPanel
			v-if="smartAlbums.length > 0"
			header="lychee.SMART_ALBUMS"
			:albums="smartAlbums"
			:user="user"
			:config="rootConfig"
			:is-alone="!albums.length"
			:are-nsfw-visible="false"
		/>
		<AlbumThumbPanel
			v-if="albums.length > 0"
			header="lychee.ALBUMS"
			:albums="albums"
			:user="user"
			:config="rootConfig"
			:is-alone="!sharedAlbums.length"
			:are-nsfw-visible="are_nsfw_visible"
		/>
		<AlbumThumbPanel
			v-if="sharedAlbums.length > 0"
			header="lychee.SHARED_ALBUMS"
			:albums="sharedAlbums"
			:user="user"
			:config="rootConfig"
			:is-alone="!albums.length"
			:are-nsfw-visible="are_nsfw_visible"
		/>
	</div>
</template>
<script setup lang="ts">
import AlbumThumbPanel from "@/components/gallery/AlbumThumbPanel.vue";
import { useAuthStore } from "@/stores/Auth";
import AlbumService from "@/services/album-service";
import { Ref, ref } from "vue";
import AlbumsHeader from "@/components/headers/AlbumsHeader.vue";
import { useLycheeStateStore } from "@/stores/LycheeState";
import { storeToRefs } from "pinia";
import { onKeyStroke } from "@vueuse/core";
import { shouldIgnoreKeystroke } from "@/utils/keybindings-utils";
import KeybindingsHelp from "@/components/modals/KeybindingsHelp.vue";

const isLoginOpen = ref(false);

const user = ref(undefined) as Ref<undefined | App.Http.Resources.Models.UserResource>;
const isKeybindingsHelpOpen = ref(false);
const smartAlbums = ref([]) as Ref<App.Http.Resources.Models.ThumbAlbumResource[]>;
const albums = ref([]) as Ref<App.Http.Resources.Models.ThumbAlbumResource[]>;
const sharedAlbums = ref([]) as Ref<App.Http.Resources.Models.ThumbAlbumResource[]>;
const rootConfig = ref(undefined) as Ref<undefined | App.Http.Resources.GalleryConfigs.RootConfig>;
const rootRights = ref(undefined) as Ref<undefined | App.Http.Resources.Rights.RootAlbumRightsResource>;
const auth = useAuthStore();
const lycheeStore = useLycheeStateStore();
lycheeStore.init();
const { are_nsfw_visible } = storeToRefs(lycheeStore);

function refresh() {
	auth.getUser().then((data) => {
		user.value = data;

		// display popup if logged in and set..
		if (user.value.id && lycheeStore.show_keybinding_help_popup) {
			isKeybindingsHelpOpen.value = true;
		}
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

refresh();

onKeyStroke("h", () => !shouldIgnoreKeystroke() && (are_nsfw_visible.value = !are_nsfw_visible.value));
</script>
