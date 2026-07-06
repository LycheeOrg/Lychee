<template>
	<UModal v-model:open="visible" :dismissible="true">
		<template #body>
			<UFormField :label="$t('tags.rename_tag')">
				<UInput id="title" v-model="name" class="w-full" />
			</UFormField>
		</template>
		<template #footer>
			<div class="flex w-full gap-2">
				<UButton
					class="flex-1 justify-center"
					color="neutral"
					variant="soft"
					@click="
						() => {
							visible = false;
						}
					"
				>
					{{ $t("dialogs.button.cancel") }}
				</UButton>
				<UButton class="flex-1 justify-center" color="primary" @click="execute">
					{{ $t("tags.rename") }}
				</UButton>
			</div>
		</template>
	</UModal>
</template>

<script setup lang="ts">
import { ref, watch } from "vue";
import AlbumService from "@/services/album-service";
import { useAppToast } from "@/v8/composables/useAppToast";
import TagsService from "@/services/tags-service";

const props = defineProps<{
	tag: App.Http.Resources.Tags.TagResource;
}>();

const toast = useAppToast();
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
