<template>
	<Dialog v-model:visible="visible" pt:root:class="border-none" modal :dismissable-mask="true">
		<template #container="{ closeCallback }">
			<div>
				<p class="p-9 text-center text-muted-color-emphasis max-w-xl text-wrap">
					{{ sprintf($t("tags.delete_confirm"), tag.name) }}<br /><br />
					<span class="text-muted-color">
						<i class="pi pi-exclamation-triangle ltr:mr-2 rtl:ml-2 text-warning-700" />{{ $t("tags.delete_warning") }}
					</span>
				</p>
				<div class="flex">
					<Button severity="secondary" class="w-full border-none rounded-none rounded-bl-xl font-bold" @click="closeCallback">
						{{ $t("dialogs.button.cancel") }}
					</Button>
					<Button severity="danger" class="w-full border-none rounded-none rounded-br-xl font-bold" @click="execute">
						{{ $t("tags.delete") }}
					</Button>
				</div>
			</div>
		</template>
	</Dialog>
</template>
<script setup lang="ts">
import { sprintf } from "sprintf-js";
import Dialog from "primevue/dialog";
import Button from "primevue/button";
import { useToast } from "primevue/usetoast";
import TagsService from "@/services/tags-service";
import AlbumService from "@/services/album-service";
import { ref, watch } from "vue";

const toast = useToast();
const props = defineProps<{
	tag: App.Http.Resources.Tags.TagResource;
}>();

const visible = defineModel<boolean>("visible", { default: false });
const emits = defineEmits<{
	deleted: [];
}>();

const tag = ref<App.Http.Resources.Tags.TagResource>(props.tag);

function execute() {
	visible.value = false;
	TagsService.delete(tag.value.id)
		.then(() => {
			toast.add({ severity: "success", summary: "Success", detail: "Tag deleted successfully", life: 3000 });
			AlbumService.clearCache();
			emits("deleted");
		})
		.catch((err) => {
			console.error("Error deleting tag:", err);
			toast.add({
				severity: "error",
				summary: "Error",
				detail: "Failed to delete tag: " + ((err as Error).message || "Unknown error"),
				life: 3000,
			});
		});
}

watch(
	() => props.tag,
	(newTag) => {
		tag.value = newTag;
	},
);
</script>
