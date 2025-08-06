<template>
	<Dialog v-model:visible="visible" pt:root:class="border-none" modal :dismissable-mask="true">
		<template #container="{ closeCallback }">
			<div>
				<p class="p-9 text-center text-muted-color max-w-xl text-wrap">
					<FloatLabel variant="on">
						<InputText id="title" v-model="name" />
						<label for="title">{{ $t("tags.rename_tag") }}</label>
					</FloatLabel>
				</p>
				<div class="flex">
					<Button severity="secondary" class="w-full border-none rounded-none rounded-bl-xl font-bold" @click="closeCallback">
						{{ $t("dialogs.button.cancel") }}
					</Button>
					<Button severity="primary" class="w-full border-none rounded-none rounded-br-xl font-bold" @click="execute">
						{{ $t("tags.rename") }}
					</Button>
				</div>
			</div>
		</template>
	</Dialog>
</template>

<script setup lang="ts">
import { ref, watch } from "vue";
import Button from "primevue/button";
import AlbumService from "@/services/album-service";
import Dialog from "primevue/dialog";
import InputText from "../basic/InputText.vue";
import FloatLabel from "primevue/floatlabel";
import { useToast } from "primevue/usetoast";
import TagsService from "@/services/tags-service";

const props = defineProps<{
	tag: App.Http.Resources.Tags.TagResource;
}>();

const toast = useToast();
const visible = defineModel<boolean>("visible", { default: false });
const emits = defineEmits<{
	updated: [];
}>();

const tag = ref<App.Http.Resources.Tags.TagResource>(props.tag);
const name = ref<string | undefined>(props.tag.name);

function execute() {
	if (!name.value) {
		return;
	}

	TagsService.rename(tag.value.id, name.value).then(() => {
		visible.value = false;
		toast.add({ severity: "success", summary: "Success", detail: "Tag renamed successfully", life: 3000 });
		AlbumService.clearCache();
		emits("updated");
	});
}

watch(
	() => props.tag,
	(newTag) => {
		tag.value = newTag;
		name.value = newTag.name;
	},
);
</script>
