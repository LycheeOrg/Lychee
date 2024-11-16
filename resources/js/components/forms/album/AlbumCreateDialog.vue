<template>
	<Dialog v-model:visible="visible" pt:root:class="border-none" modal :dismissable-mask="true">
		<template #container="{ closeCallback }">
			<div v-focustrap class="flex flex-col relative max-w-full text-sm rounded-md pt-9">
				<p class="mb-5 px-9">{{ $t("lychee.TITLE_NEW_ALBUM") }}</p>
				<div class="inline-flex flex-col gap-2 px-9">
					<FloatLabel variant="on">
						<InputText id="title" v-model="title" />
						<label class="" for="title">{{ $t("lychee.ALBUM_TITLE") }}</label>
					</FloatLabel>
				</div>
				<div class="flex items-center mt-9">
					<Button @click="closeCallback" severity="secondary" class="w-full font-bold border-none rounded-bl-xl">
						{{ $t("lychee.CANCEL") }}
					</Button>
					<Button @click="create" severity="contrast" class="font-bold w-full border-none rounded-none rounded-br-xl" :disabled="!isValid">
						{{ $t("lychee.CREATE_ALBUM") }}
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

const props = defineProps<{
	parentId: string | null;
}>();

const visible = defineModel("visible", { default: false });
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
			visible.value = false;
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
