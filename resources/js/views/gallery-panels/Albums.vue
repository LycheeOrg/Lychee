<template>
	<LoginModal v-if="user?.id === null" :visible="isLoginOpen" @logged-in="refresh" />
	<UploadPanel v-if="canUpload" :visible="isUploadOpen" @close="isUploadOpen = false" :album-id="null" />
	<Toolbar class="w-full border-0">
		<template #start>
			<Button
				v-if="user?.id === null"
				icon="pi pi-sign-in"
				class="mr-2"
				severity="secondary"
				text
				@click="() => (isLoginOpen = !isLoginOpen)"
			/>
			<Button v-if="user?.id" @click="openLeftMenu" icon="pi pi-bars" class="mr-2" severity="secondary" text />
			<!-- <Button v-if="initdata?.user" @click="logout" icon="pi pi-sign-out" class="mr-2" severity="secondary" text /> -->
		</template>

		<template #center>
			{{ title }}
		</template>

		<template #end>
			<!-- <IconField>
				<InputIcon>
					<i class="pi pi-search" />
				</InputIcon>
				<InputText placeholder="Search" />
			</IconField> -->
			<!-- <SplitButton label="Save" :model="items"></SplitButton> -->
			<Button icon="pi pi-plus" severity="secondary" @click="openAddMenu" />
		</template>
	</Toolbar>
	<ContextMenu ref="addmenu" :model="addMenu">
		<template #item="{ item, props }">
			<a v-ripple v-bind="props.action" @click="item.callback">
				<span :class="item.icon" />
				<span class="ml-2">{{ $t(item.label) }}</span>
			</a>
		</template>
	</ContextMenu>
	<div v-if="rootConfig !== undefined">
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
import LoginModal from "@/components/modals/LoginModal.vue";
import AlbumService from "@/services/album-service";
import Button from "primevue/button";
import Toolbar from "primevue/toolbar";
import { computed, Ref, ref } from "vue";
import { useLycheeStateStore } from "@/stores/LycheeState";
import UploadPanel from "@/components/modals/UploadPanel.vue";
import { onKeyStroke } from "@vueuse/core";
import ContextMenu from "primevue/contextmenu";

const addmenu = ref();

// 'UPLOAD_PHOTO' => 'Upload Photo',
// 	'IMPORT_LINK' => 'Import from Link',
// 	'IMPORT_DROPBOX' => 'Import from Dropbox',
// 	'IMPORT_SERVER' => 'Import from Server',
// 	'NEW_ALBUM' => 'New Album',
// 	'NEW_TAG_ALBUM' => 'New Tag Album',
// 	'UPLOAD_TRACK' => 'Upload track',
// 	'DELETE_TRACK' => 'Delete track',

const addMenu = ref([
	{
		label: "lychee.UPLOAD_PHOTO",
		icon: "pi pi-upload",
		callback: () => (isUploadOpen.value = true),
	},
	{
		label: "lychee.IMPORT_LINK",
		icon: "pi pi-link",
		callback: () => {},
	},
	{
		label: "lychee.IMPORT_DROPBOX",
		icon: "pi pi-box",
		callback: () => {},
	},
	{
		label: "lychee.IMPORT_SERVER",
		icon: "pi pi-server",
		callback: () => {},
	},
	{
		label: "lychee.NEW_ALBUM",
		icon: "pi pi-folder",
		callback: () => {},
	},
	{
		label: "lychee.NEW_TAG_ALBUM",
		icon: "pi pi-tags",
		callback: () => {},
	},
]);
const title = ref("Albums");
const isLoginOpen = ref(false);
const isUploadOpen = ref(false);
const user = ref(undefined) as Ref<undefined | App.Http.Resources.Models.UserResource>;
const canUpload = computed(() => user.value?.id !== null);

const smartAlbums = ref([]) as Ref<App.Http.Resources.Models.ThumbAlbumResource[]>;
const albums = ref([]) as Ref<App.Http.Resources.Models.ThumbAlbumResource[]>;
const sharedAlbums = ref([]) as Ref<App.Http.Resources.Models.ThumbAlbumResource[]>;
const rootConfig = ref(undefined) as Ref<undefined | App.Http.Resources.GalleryConfigs.RootConfig>;

const lycheeStore = useLycheeStateStore();
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

const emit = defineEmits(["toggleLeftMenu"]);

function openLeftMenu() {
	lycheeStore.toggleLeftMenu();
}

function openAddMenu(event: Event) {
	addmenu.value.show(event);
}

onKeyStroke("u", () => {
	isUploadOpen.value = !isUploadOpen.value;
});

refresh();
</script>
