<template>
	<Dialog
		v-model:visible="is_create_tag_album_visible"
		pt:root:class="border-none"
		modal
		:dismissable-mask="true"
		@close="is_create_tag_album_visible = false"
	>
		<template #container="{ closeCallback }">
			<div v-focustrap class="flex flex-col relative w-full md:w-lg text-sm rounded-md pt-9">
				<p class="mb-5 px-9">{{ $t("dialogs.new_tag_album.info") }}</p>
				<div class="inline-flex flex-col gap-3 px-9">
					<FloatLabel variant="on">
						<InputText id="title" v-model="title" />
						<label for="title">{{ $t("dialogs.new_tag_album.title") }}</label>
					</FloatLabel>
					<FloatLabel variant="on">
						<TagsInput v-model="tags" :add="false" />
						<label for="tags">{{ $t("dialogs.new_tag_album.set_tags") }}</label>
					</FloatLabel>
					<div class="text-muted-color-emphasis" v-html="$t('dialogs.new_tag_album.warn')" />
				</div>
				<div class="flex items-center mt-9">
					<Button @click="closeCallback" severity="secondary" class="w-full font-bold border-none rounded-none rounded-bl-xl">
						{{ $t("dialogs.button.cancel") }}
					</Button>
					<Button @click="create" severity="contrast" class="font-bold w-full border-none rounded-none rounded-br-xl" :disabled="!isValid">
						{{ $t("dialogs.new_tag_album.create") }}
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
import { computed, ref } from "vue";
import { useRouter } from "vue-router";
import InputText from "@/components/forms/basic/InputText.vue";
import Button from "primevue/button";
import { useToast } from "primevue/usetoast";
import { useTogglablesStateStore } from "@/stores/ModalsState";
import { storeToRefs } from "pinia";
import { trans } from "laravel-vue-i18n";
import TagsInput from "../basic/TagsInput.vue";

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
			is_create_tag_album_visible.value = false;
			AlbumService.clearAlbums();
			router.push(`/gallery/${response.data}`);
		})
		.catch((error) => {
			toast.add({ severity: "error", summary: trans("toasts.error"), detail: error.message });
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
