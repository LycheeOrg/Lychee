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
			<div v-if="hasExistingTags" class="my-3 text-center">
				<p class="text-muted text-xs mb-1.5">{{ $t("dialogs.photo_tags.existing_tags") }}</p>
				<div class="flex flex-wrap items-center justify-center gap-x-1 gap-y-1.5">
					<code v-for="tag in commonTags" :key="`common-${tag}`" :class="tagChipClass">{{ tag }}</code>
					<template v-if="partialTags.length > 0">
						<span class="text-dimmed text-2xl leading-none ltr:ml-1 rtl:mr-1">(</span>
						<code v-for="tag in partialTags" :key="`partial-${tag}`" :class="tagChipClass">{{ tag }}</code>
						<span class="text-dimmed text-2xl leading-none ltr:mr-1 rtl:ml-1">)</span>
					</template>
				</div>
				<p v-if="partialTags.length > 0" class="text-muted text-xs mt-1.5 italic">{{ $t("dialogs.photo_tags.partial_tags_info") }}</p>
			</div>
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
				<UButton variant="solid" color="neutral" class="flex-1 justify-center font-bold" @click="execute">
					{{ $t("dialogs.photo_tags.set_tags") }}
				</UButton>
			</div>
		</template>
	</UModal>
</template>
<script setup lang="ts">
import { computed, ref, toRef } from "vue";
import PhotoService from "@/services/photo-service";
import AlbumService from "@/services/album-service";
import { sprintf } from "sprintf-js";
import { useAppToast } from "@/v8/composables/useAppToast";
import { trans } from "laravel-vue-i18n";
import TagsService from "@/services/tags-service";
import TagsInput from "@/v8/components/forms/basic/TagsInput.vue";
import { useExistingTags } from "@/composables/tags/existingTags";

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

const { commonTags, partialTags, hasExistingTags } = useExistingTags(toRef(props, "photo"), toRef(props, "photoIds"));

// Slack-like inline code rendering for the tags already set on the photos.
const tagChipClass = "font-mono text-xs px-1.5 py-0.5 rounded-sm border border-default bg-elevated text-error";

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
