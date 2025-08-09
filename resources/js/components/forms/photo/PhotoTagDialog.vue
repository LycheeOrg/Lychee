<template>
	<Dialog v-model:visible="visible" pt:root:class="border-none" modal :dismissable-mask="true">
		<template #container>
			<div class="p-9 text-center text-muted-color">
				<p class="text-sm/8">
					{{ question }}
					<br />
					<span class="text-muted-color-emphasis">
						<i class="text-warning-600 pi pi-exclamation-triangle ltr:mr-2 rtl:ml-2" />Press Enter to confirm each tag.
					</span>
				</p>
				<div class="my-3 first:mt-0 last:mb-0">
					<TagsInput v-model="tags" :placeholder="$t('dialogs.photo_tags.no_tags')" :add="true" />
				</div>
				<div>
					<Checkbox v-model="shallOverride" :binary="true" inputId="shallOverride" />
					<label for="shallOverride" class="ml-2 text-sm text-muted-color">{{ $t("dialogs.photo_tags.tags_override_info") }}</label>
				</div>
			</div>
			<div class="flex">
				<Button severity="secondary" class="font-bold w-full border-none rounded-none rounded-bl-xl" @click="close">
					{{ $t("dialogs.button.cancel") }}
				</Button>
				<Button severity="contrast" class="font-bold w-full border-none rounded-none rounded-br-xl" @click="execute">
					{{ $t("dialogs.photo_tags.set_tags") }}
				</Button>
			</div>
		</template>
	</Dialog>
</template>
<script setup lang="ts">
import { computed, ref } from "vue";
import PhotoService from "@/services/photo-service";
import AlbumService from "@/services/album-service";
import { sprintf } from "sprintf-js";
import { useToast } from "primevue/usetoast";
import Button from "primevue/button";
import Dialog from "primevue/dialog";
import Checkbox from "primevue/checkbox";
import { trans } from "laravel-vue-i18n";
import TagsService from "@/services/tags-service";
import TagsInput from "../basic/TagsInput.vue";

const props = defineProps<{
	parentId: string | undefined;
	photo?: App.Http.Resources.Models.PhotoResource;
	photoIds?: string[];
}>();

const visible = defineModel<boolean>("visible", { default: false });

const emits = defineEmits<{
	tagged: [];
}>();

const toast = useToast();

const question = computed(() => {
	if (props.photo) {
		return trans("dialogs.photo_tags.question");
	}
	return sprintf(trans("dialogs.photo_tags.question_multiple"), props.photoIds?.length);
});

const shallOverride = ref(false);
const tags = ref<string[]>([]);

function close() {
	visible.value = false;
	tags.value = [];
	shallOverride.value = false;
}

function execute() {
	if (tags.value === undefined) {
		return;
	}

	let photoTaggedIds = [];
	if (props.photo) {
		photoTaggedIds.push(props.photo.id);
	} else {
		photoTaggedIds = props.photoIds as string[];
	}

	PhotoService.tags(photoTaggedIds, tags.value, shallOverride.value).then(() => {
		toast.add({
			severity: "success",
			summary: trans("dialogs.photo_tags.updated"),
			life: 3000,
		});
		AlbumService.clearCache(props.parentId);
		TagsService.clearCache();
		close();
		emits("tagged");
	});
}
</script>
