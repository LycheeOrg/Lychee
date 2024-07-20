<template>
	<LoginModal :visible="isLoginOpen" @logged-in="refresh" />
	<Toolbar class="w-full" v-if="album">
		<template #start>
			<Button icon="pi pi-angle-left" class="mr-2" severity="secondary" text @click="goBack" />
			<!-- <Button v-if="user?.id" @click="openLeftMenu" icon="pi pi-bars" class="mr-2" severity="secondary" text /> -->
			<!-- <Button v-if="initdata?.user" @click="logout" icon="pi pi-sign-out" class="mr-2" severity="secondary" text /> -->
		</template>

		<template #center>
			{{ album.title }}
		</template>

		<template #end>
			<!-- <IconField>
				<InputIcon>
					<i class="pi pi-search" />
				</InputIcon>
				<InputText placeholder="Search" />
			</IconField> -->
			<template v-if="album.rights.can_edit">
				<Button v-if="!areDetailsOpen" icon="pi pi-angle-down" class="mr-2" @click="toggleDetails"> </Button>
				<Button v-if="areDetailsOpen" icon="pi pi-angle-up" class="mr-2" @click="toggleDetails"> </Button>
			</template>
			<!-- <SplitButton label="Save" :model="items"></SplitButton> -->
		</template>
	</Toolbar>
	<template v-if="config && album">
		<div v-if="noData" class="flex w-full h-full items-center justify-center text-xl text-muted-color">
			<span class="block">
				{{ "Nothing to see here" }}
			</span>
		</div>
		<div class="relative flex flex-wrap content-start w-full justify-start overflow-y-auto h-[calc(100vh-48px)]">
			<AlbumEdit v-model="areDetailsOpen" v-if="album.rights.can_edit" :album="album" :config="config" />
			<AlbumHero :album="album" @open-sharing-modal="openSharingModal" />
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
	<ShareAlbum ref="shareAlbum" v-if="album !== null" :title="album.title" :url="route.path"></ShareAlbum>
</template>
<script setup lang="ts">
import { useAuthStore } from "@/stores/Auth";
import LoginModal from "@/components/modals/LoginModal.vue";
import AlbumService from "@/services/album-service";
import Button from "primevue/button";
import Toolbar from "primevue/toolbar";
import { Ref, computed, ref } from "vue";
import { watch } from "vue";
import { useRoute, useRouter } from "vue-router";
import AlbumThumbPanel from "@/components/gallery/AlbumThumbPanel.vue";
import PhotoThumbPanel from "@/components/gallery/PhotoThumbPanel.vue";
import ShareAlbum from "@/components/modals/ShareAlbum.vue";
import AlbumHero from "@/components/gallery/AlbumHero.vue";
import AlbumEdit from "@/components/drawers/AlbumEdit.vue";

const areDetailsOpen = ref(true);
const router = useRouter();
const props = defineProps<{
	albumid: string;
}>();
const shareAlbum = ref();
const isLoginOpen = ref(false);
const albumid = ref(props.albumid);
const auth = useAuthStore();
const user = ref(undefined) as Ref<undefined | App.Http.Resources.Models.UserResource>;
const album = ref(null) as Ref<
	null | App.Http.Resources.Models.AlbumResource | App.Http.Resources.Models.TagAlbumResource | App.Http.Resources.Models.SmartAlbumResource
>;
const config = ref(null) as Ref<null | App.Http.Resources.GalleryConfigs.AlbumConfig>;
const layout = ref(null) as Ref<null | App.Http.Resources.GalleryConfigs.PhotoLayoutConfig>;
const children = ref([]) as Ref<App.Http.Resources.Models.ThumbAlbumResource[]>;
const photos = ref([]) as Ref<App.Http.Resources.Models.PhotoResource[]>;
const route = useRoute();
const noData = computed(() => (children.value === null || children.value.length === 0) && (photos.value === null || photos.value.length === 0));

function goBack() {
	if (config.value?.is_model_album === true && (album.value as App.Http.Resources.Models.AlbumResource | null)?.parent_id !== null) {
		router.push({ name: "album", params: { albumid: (album.value as App.Http.Resources.Models.AlbumResource | null)?.parent_id } });
	} else {
		router.push({ name: "gallery" });
	}
}

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
			album.value = data.data.resource;
			prepare();
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

function prepare() {
	if (config.value === null || album.value === null || config.value.is_accessible !== true) {
		return;
	}

	photos.value = album.value.photos as App.Http.Resources.Models.PhotoResource[];

	if (config.value.is_base_album === true) {
		const albumResource = album.value as App.Http.Resources.Models.AlbumResource;
		children.value = albumResource.albums as App.Http.Resources.Models.ThumbAlbumResource[];
	}
}

function openSharingModal() {
	shareAlbum.value.toggleModal();
}

function toggleDetails() {
	areDetailsOpen.value = !areDetailsOpen.value;
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
