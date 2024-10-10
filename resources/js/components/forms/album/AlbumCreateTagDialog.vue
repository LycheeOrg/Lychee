<template>
	<Dialog v-model:visible="visible" pt:root:class="border-none" modal :dismissable-mask="true">
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
					<Button @click="create" severity="contrast" class="font-bold w-full border-none rounded-none rounded-br-xl">
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
import { ref, watch } from "vue";
import { useRouter } from "vue-router";
import InputText from "@/components/forms/basic/InputText.vue";
import Button from "primevue/button";
import AutoComplete from "primevue/autocomplete";

const props = defineProps<{
	visible: boolean;
}>();

const router = useRouter();
const visible = ref(props.visible);

const title = ref(undefined as undefined | string);
const tags = ref([] as string[]);

function create() {
	if (!title.value || tags.value.length === 0) {
		return;
	}

	AlbumService.createTag({
		title: title.value,
		tags: tags.value,
	}).then((response) => {
		AlbumService.clearAlbums();
		router.push(`/gallery/${response.data}`);
	});
}

watch(
	() => props.visible,
	(newVisible, _oldVisible) => {
		visible.value = newVisible;
	},
);
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
