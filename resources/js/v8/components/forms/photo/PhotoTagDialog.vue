<template>
	<UModal v-model:open="visible" :dismissible="true">
		<template #body>
			<p class="text-sm/8 text-center">
				{{ question }}
				<br />
				<span class="text-highlighted flex items-center justify-center gap-1">
					<UIcon name="lucide:triangle-alert" class="text-warning-600" />Press Enter to confirm each tag.
				</span>
			</p>
			<div class="my-3 first:mt-0 last:mb-0">
				<TagsInput v-model="tags" :placeholder="$t('dialogs.photo_tags.no_tags')" :add="true" />
			</div>
			<UCheckbox v-model="shallOverride" :label="$t('dialogs.photo_tags.tags_override_info')" :ui="{ label: 'text-sm text-muted' }" />
		</template>
		<template #footer>
			<div class="flex w-full gap-2">
				<UButton color="neutral" variant="soft" class="flex-1 justify-center font-bold" @click="close">
					{{ $t("dialogs.button.cancel") }}
				</UButton>
				<UButton color="neutral" class="flex-1 justify-center font-bold" @click="execute">
					{{ $t("dialogs.photo_tags.set_tags") }}
				</UButton>
			</div>
		</template>
	</UModal>
</template>
<script setup lang="ts">
import { computed, ref } from "vue";
import PhotoService from "@/services/photo-service";
import AlbumService from "@/services/album-service";
import { sprintf } from "sprintf-js";
import { useAppToast } from "@/v8/composables/useAppToast";
import { trans } from "laravel-vue-i18n";
import TagsService from "@/services/tags-service";
import TagsInput from "@/v8/components/forms/basic/TagsInput.vue";

const props = defineProps<{
	parentId: string | undefined;
	photo?: App.Http.Resources.Models.PhotoResource;
	photoIds?: string[];
}>();

const visible = defineModel<boolean>("open", { default: false });

const emits = defineEmits<{
	tagged: [];
}>();

const toast = useAppToast();

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
