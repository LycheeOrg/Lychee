<template>
	<UModal v-model:open="visible" :dismissible="true">
		<template #body>
			<UFormField :label="question">
				<UInput id="title" v-model="title" class="w-full" />
			</UFormField>
		</template>
		<template #footer>
			<div class="flex w-full gap-2">
				<UButton
					color="neutral"
					variant="soft"
					class="flex-1 justify-center font-bold"
					@click="
						() => {
							visible = false;
						}
					"
				>
					{{ $t("dialogs.button.cancel") }}
				</UButton>
				<UButton color="error" class="flex-1 justify-center font-bold" @click="execute">
					{{ $t("dialogs.rename.rename") }}
				</UButton>
			</div>
		</template>
	</UModal>
</template>

<script setup lang="ts">
import { computed, onMounted, ref } from "vue";
import AlbumService from "@/services/album-service";
import PhotoService from "@/services/photo-service";
import { trans } from "laravel-vue-i18n";
import { watch } from "vue";

const props = defineProps<{
	album?: App.Http.Resources.Models.ThumbAlbumResource;
	photo?: App.Http.Resources.Models.PhotoResource;
}>();

const visible = defineModel<boolean>("open", { default: false });
const emits = defineEmits<{
	updated: [];
}>();

const title = ref<string | undefined>(undefined);

const question = computed(() => {
	if (props.photo) {
		return trans("dialogs.rename.photo");
	}
	return trans("dialogs.rename.album");
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
	if (!props.photo || !title.value) {
		return;
	}

	PhotoService.rename(props.photo.id, title.value).then(() => {
		emits("updated");
		AlbumService.clearCache(props.photo?.album_id);
	});
}

function executeAlbum() {
	if (!props.album || !title.value) {
		return;
	}

	AlbumService.rename(props.album.id, title.value).then(() => {
		emits("updated");
		AlbumService.clearCache(props.album?.id);
	});
}

onMounted(() => {
	if (props.album) {
		title.value = props.album.title;
	}
	if (props.photo) {
		title.value = props.photo.title;
	}
});

watch(
	() => props.album,
	(newAlbum: App.Http.Resources.Models.ThumbAlbumResource | undefined) => {
		if (newAlbum) {
			title.value = newAlbum.title;
		}
	},
);

watch(
	() => props.photo,
	(newPhoto: App.Http.Resources.Models.PhotoResource | undefined) => {
		if (newPhoto) {
			title.value = newPhoto.title;
		}
	},
);
</script>
