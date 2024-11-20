<template>
	<Dialog
		v-model:visible="is_create_tag_album_visible"
		pt:root:class="border-none"
		modal
		:dismissable-mask="true"
		@close="is_create_tag_album_visible = false"
	>
		<template #container="{ closeCallback }">
			<div v-focustrap class="flex flex-col relative max-w-full text-sm rounded-md pt-9">
				<p class="mb-5 px-9">{{ $t("lychee.NEW_TAG_ALBUM") }}</p>
				<div class="inline-flex flex-col gap-3 px-9">
					<FloatLabel variant="on">
						<InputText id="title" v-model="title" />
						<label class="" for="title">{{ $t("lychee.ALBUM_TITLE") }}</label>
					</FloatLabel>
					<FloatLabel variant="on">
						<AutoComplete
							id="tags"
							v-model="tags"
							:typeahead="false"
							multiple
							class="pt-3 border-b hover:border-b-0"
							pt:inputmultiple:class="w-full border-t-0 border-l-0 border-r-0 border-b hover:border-b-primary-400 focus:border-b-primary-400"
						/>
						<label for="tags">{{ $t("lychee.ALBUM_SET_SHOWTAGS") }}</label>
					</FloatLabel>
				</div>
				<div class="flex items-center mt-9">
					<Button @click="closeCallback" severity="secondary" class="w-full font-bold border-none rounded-bl-xl">
						{{ $t("lychee.CANCEL") }}
					</Button>
					<Button @click="create" severity="contrast" class="font-bold w-full border-none rounded-none rounded-br-xl" :disabled="!isValid">
						{{ $t("lychee.CREATE_TAG_ALBUM") }}
					</Button>
				</div>
			</div>
		</template>
	</Dialog>
</template>
<script setup lang="ts">
import AlbumService from "@/services/album-service";
import Dialog from "primevue/dialog";
import FloatLabel from "primevue/floatlabel";
import { computed, ref, watch } from "vue";
import { useRouter } from "vue-router";
import InputText from "@/components/forms/basic/InputText.vue";
import Button from "primevue/button";
import AutoComplete from "primevue/autocomplete";
import { useToast } from "primevue/usetoast";
import { useTogglablesStateStore } from "@/stores/ModalsState";
import { storeToRefs } from "pinia";

const toast = useToast();
const router = useRouter();

const togglableStore = useTogglablesStateStore();
const { is_create_tag_album_visible } = storeToRefs(togglableStore);

const title = ref<string | undefined>(undefined);
const tags = ref<string[]>([]);

const isValid = computed(() => title.value !== undefined && title.value.length > 0 && title.value.length <= 100);

function create() {
	if (!isValid.value) {
		return;
	}

	AlbumService.createTag({
		title: title.value as string,
		tags: tags.value,
	})
		.then((response) => {
			AlbumService.clearAlbums();
			router.push(`/gallery/${response.data}`);
		})
		.catch((error) => {
			toast.add({ severity: "error", summary: "Oups", detail: error.message });
		});
}
</script>
<style lang="css">
.p-inputchips-input {
	border: none;
	border-bottom: 1px solid;
}
.p-inputchips-input:hover {
	border-bottom-color: var(--p-primary-500);
}
</style>
