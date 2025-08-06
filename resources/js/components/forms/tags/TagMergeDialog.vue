<template>
	<Dialog v-model:visible="visible" pt:root:class="border-none" modal :dismissable-mask="true">
		<template #container="{ closeCallback }">
			<div>
				<p class="p-9 text-center text-muted-color-emphasis max-w-xl text-wrap">
					{{ sprintf($t("tags.merge_confirm"), selected.name, into.name) }}<br /><br />
					<span class="text-muted-color">
						<i class="pi pi-exclamation-triangle ltr:mr-2 rtl:ml-2 text-warning-700" />{{ $t("tags.merge_warning") }}
					</span>
				</p>
				<div class="flex">
					<Button severity="secondary" class="w-full border-none rounded-none rounded-bl-xl font-bold" @click="closeCallback">
						{{ $t("dialogs.button.cancel") }}
					</Button>
					<Button severity="danger" class="w-full border-none rounded-none rounded-br-xl font-bold" @click="execute">
						{{ $t("tags.merge") }}
					</Button>
				</div>
			</div>
		</template>
	</Dialog>
</template>
<script setup lang="ts">
import Button from "primevue/button";
import { sprintf } from "sprintf-js";
import AlbumService from "@/services/album-service";
import { useToast } from "primevue/usetoast";
import Dialog from "primevue/dialog";
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

const toast = useToast();
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
