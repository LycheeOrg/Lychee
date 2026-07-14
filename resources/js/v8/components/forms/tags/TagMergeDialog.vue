<template>
	<UModal v-model:open="visible" :dismissible="true">
		<template #body>
			<p class="text-center text-highlighted max-w-xl text-wrap">
				{{ sprintf($t("tags.merge_confirm"), selected.name, into.name) }}<br /><br />
				<span class="text-muted">
					<UIcon name="lucide:triangle-alert" class="ltr:mr-2 rtl:ml-2 text-warning" />{{ $t("tags.merge_warning") }}
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
					{{ $t("tags.merge") }}
				</UButton>
			</div>
		</template>
	</UModal>
</template>
<script setup lang="ts">
import { sprintf } from "sprintf-js";
import AlbumService from "@/services/album-service";
import { useAppToast } from "@/v8/composables/useAppToast";
import TagsService from "@/services/tags-service";
import { ref, watch } from "vue";

const props = defineProps<{
	selected: App.Http.Resources.Tags.TagResource;
	into: App.Http.Resources.Tags.TagResource;
}>();

const visible = defineModel<boolean>("visible", { default: false });

const emits = defineEmits<{
	merged: [];
}>();

const toast = useAppToast();
const selected = ref<App.Http.Resources.Tags.TagResource>(props.selected);
const into = ref<App.Http.Resources.Tags.TagResource>(props.into);

function execute() {
	TagsService.merge(selected.value.id, into.value.id).then(() => {
		visible.value = false;
		toast.add({ severity: "success", summary: "Success", detail: "Tags merged successfully", life: 3000 });
		AlbumService.clearCache();
		emits("merged");
	});
}

watch(
	() => [props.selected, props.into],
	([newSelected, newInto]) => {
		selected.value = newSelected;
		into.value = newInto;
	},
);
</script>
