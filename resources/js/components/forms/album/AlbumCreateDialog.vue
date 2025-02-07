<template>
	<Dialog
		v-model:visible="is_create_album_visible"
		pt:root:class="border-none"
		modal
		:dismissable-mask="true"
		@close="is_create_album_visible = false"
	>
		<template #container="{ closeCallback }">
			<div v-focustrap class="flex flex-col relative max-w-full text-sm rounded-md pt-9">
				<p class="mb-5 px-9">{{ $t("dialogs.new_album.info") }}</p>
				<div class="inline-flex flex-col gap-2 px-9">
					<FloatLabel variant="on">
						<InputText id="title" v-model="title" />
						<label for="title">{{ $t("dialogs.new_album.title") }}</label>
					</FloatLabel>
				</div>
				<div class="flex items-center mt-9">
					<Button @click="closeCallback" severity="secondary" class="w-full font-bold border-none rounded-bl-xl">
						{{ $t("dialogs.button.cancel") }}
					</Button>
					<Button @click="create" severity="contrast" class="font-bold w-full border-none rounded-none rounded-br-xl" :disabled="!isValid">
						{{ $t("dialogs.new_album.create") }}
					</Button>
				</div>
			</div>
		</template>
	</Dialog>
</template>
<script setup lang="ts">
import AlbumService from "@/services/album-service";
import Dialog from "primevue/dialog";
import InputText from "@/components/forms/basic/InputText.vue";
import { computed, ref, watch } from "vue";
import { useRouter } from "vue-router";
import FloatLabel from "primevue/floatlabel";
import Button from "primevue/button";
import { useToast } from "primevue/usetoast";
import { useTogglablesStateStore } from "@/stores/ModalsState";
import { storeToRefs } from "pinia";

const props = defineProps<{
	parentId: string | null;
}>();

const togglableStore = useTogglablesStateStore();
const { is_create_album_visible } = storeToRefs(togglableStore);

const parentId = ref(props.parentId);

const toast = useToast();
const router = useRouter();

const title = ref<string | undefined>(undefined);

const isValid = computed(() => title.value !== undefined && title.value.length > 0 && title.value.length <= 100);

function create() {
	if (!isValid.value) {
		return;
	}

	AlbumService.createAlbum({
		title: title.value as string,
		parent_id: parentId.value,
	})
		.then((response) => {
			title.value = undefined;
			is_create_album_visible.value = false;
			AlbumService.clearCache(parentId.value);
			router.push(`/gallery/${response.data}`);
		})
		.catch((error) => {
			toast.add({ severity: "error", summary: "Oups", detail: error.message });
		});
}

watch(
	() => props.parentId,
	(newAlbumID, _oldAlbumID) => {
		title.value = undefined;
		parentId.value = newAlbumID as string | null;
	},
);
</script>
