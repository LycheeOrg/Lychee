<template>
	<Dialog
		v-model:visible="visible"
		modal
		:pt="{
			root: 'border-none',
		}"
	>
		<template #container="{ closeCallback }">
			<div v-focustrap class="flex flex-col relative max-w-full text-sm rounded-md pt-9">
				<p class="mb-5 px-9">{{ $t("lychee.NEW_TAG_ALBUM") }}</p>
				<div class="inline-flex flex-col gap-3 px-9">
					<FloatLabel>
						<InputText id="title" v-model="title" autofocus />
						<label class="" for="title">{{ $t("lychee.ALBUM_SET_TITLE") }}</label>
					</FloatLabel>
					<FloatLabel>
						<AutoComplete id="tags" v-model="tags" :typeahead="false" multiple field="title" separator="," />
						<label for="tags">{{ $t("lychee.ALBUM_SET_SHOWTAGS") }}</label>
					</FloatLabel>
				</div>
				<div class="flex items-center mt-9">
					<Button
						@click="closeCallback"
						text
						class="p-3 w-full font-bold border-none text-muted-color hover:text-danger-700 rounded-bl-xl flex-shrink-2"
						>{{ $t("lychee.CANCEL") }}</Button
					>
					<Button
						@click="create"
						text
						class="p-3 w-full font-bold border-none text-primary-500 hover:bg-primary-500 hover:text-surface-0 rounded-none rounded-br-xl flex-shrink"
						>{{ $t("lychee.CREATE_TAG_ALBUM") }}</Button
					>
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
const tags = ref(undefined as undefined | string);

function create() {
	console.log(tags.value);
	if (!title.value || !tags.value) {
		return;
	}

	AlbumService.createTag({
		title: title.value,
		tags: tags.value?.split(",") ?? [],
	}).then((response) => {
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
