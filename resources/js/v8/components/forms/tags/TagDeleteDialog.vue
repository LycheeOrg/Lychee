<template>
	<UModal v-model:open="visible" :dismissible="true">
		<template #body>
			<p class="text-center text-highlighted max-w-xl text-wrap">
				{{ confirmMessage }}<br /><br />
				<span class="text-muted">
					<UIcon name="prime:exclamation-triangle" class="ltr:mr-2 rtl:ml-2 text-warning" />{{ $t("tags.delete_warning") }}
				</span>
			</p>
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
				<UButton class="flex-1 justify-center" color="error" @click="execute">
					{{ $t("tags.delete") }}
				</UButton>
			</div>
		</template>
	</UModal>
</template>
<script setup lang="ts">
import { sprintf } from "sprintf-js";
import { trans } from "laravel-vue-i18n";
import { useAppToast } from "@/v8/composables/useAppToast";
import TagsService from "@/services/tags-service";
import AlbumService from "@/services/album-service";
import { computed, ref, watch } from "vue";

const toast = useAppToast();
const props = defineProps<{
	tags: App.Http.Resources.Tags.TagResource[];
}>();

const visible = defineModel<boolean>("visible", { default: false });
const emits = defineEmits<{
	deleted: [];
}>();

const tags = ref<App.Http.Resources.Tags.TagResource[]>(props.tags);

const confirmMessage = computed(() =>
	tags.value.length === 1
		? sprintf(trans("tags.delete_confirm"), tags.value[0].name)
		: sprintf(trans("tags.delete_confirm_multiple"), tags.value.length),
);

function execute() {
	visible.value = false;
	TagsService.delete(tags.value.map((tag) => tag.id))
		.then(() => {
			toast.add({ severity: "success", summary: "Success", detail: "Tag deleted successfully", life: 3000 });
			AlbumService.clearCache();
			emits("deleted");
		})
		.catch((err) => {
			console.error("Error deleting tags:", err);
			toast.add({
				severity: "error",
				summary: "Error",
				detail: "Failed to delete tags: " + ((err as Error).message || "Unknown error"),
				life: 3000,
			});
		});
}

watch(
	() => props.tags,
	(newTags) => {
		tags.value = newTags;
	},
);
</script>
