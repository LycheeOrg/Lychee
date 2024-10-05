<template>
	<Dialog v-model:visible="visible" pt:root:class="border-none" modal :dismissable-mask="true">
		<template #container="{ closeCallback }">
			<div>
				<p class="p-9 text-center text-muted-color max-w-xl text-wrap">
					<FloatLabel>
						<InputText id="title" v-model="title" />
						<label for="title">{{ question }}</label>
					</FloatLabel>
				</p>
				<div class="flex">
					<Button severity="secondary" class="w-full border-none rounded-none rounded-bl-xl font-bold" @click="closeCallback">
						{{ $t("lychee.CANCEL") }}
					</Button>
					<Button severity="danger" class="w-full border-none rounded-none rounded-br-xl font-bold" @click="execute">
						{{ $t("lychee.RENAME") }}
					</Button>
				</div>
			</div>
		</template>
	</Dialog>
</template>

<script setup lang="ts">
import { computed, ref } from "vue";
import Button from "primevue/button";
import AlbumService from "@/services/album-service";
import Dialog from "primevue/dialog";
import PhotoService from "@/services/photo-service";
import { trans } from "laravel-vue-i18n";
import InputText from "../basic/InputText.vue";
import FloatLabel from "primevue/floatlabel";

const props = defineProps<{
	parentId: string | undefined;
	album?: App.Http.Resources.Models.ThumbAlbumResource;
	photo?: App.Http.Resources.Models.PhotoResource;
}>();

const visible = defineModel<boolean>("visible", { default: false });
const emits = defineEmits<{
	updated: [];
}>();

const title = ref(undefined as undefined | string);

const question = computed(() => {
	if (props.photo) {
		return trans("lychee.PHOTO_NEW_TITLE");
	}
	return trans("lychee.ALBUM_NEW_TITLE");
});

function execute() {
	if (!title.value) {
		return;
	}

	visible.value = false;
	if (props.photo) {
		executePhoto();
	} else {
		executeAlbum();
	}
}

function executePhoto() {
	// @ts-ignore
	PhotoService.rename(props.photo.id, title.value).then(() => {
		emits("updated");
		AlbumService.clearCache(props.photo?.album_id);
	});
}

function executeAlbum() {
	// @ts-ignore
	AlbumService.rename(props.album.id, title.value).then(() => {
		emits("updated");
		AlbumService.clearCache(props.album?.id);
	});
}
</script>
