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
				<div v-if="hasExistingTags" class="my-3">
					<p class="text-xs mb-1">{{ $t("dialogs.photo_tags.existing_tags") }}</p>
					<div class="flex flex-wrap items-center justify-center gap-x-1 gap-y-1.5">
						<code v-for="tag in commonTags" :key="`common-${tag}`" :class="tagChipClass">{{ tag }}</code>
						<template v-if="partialTags.length > 0">
							<span class="text-2xl leading-none ltr:ml-1 rtl:mr-1">(</span>
							<code v-for="tag in partialTags" :key="`partial-${tag}`" :class="tagChipClass">{{ tag }}</code>
							<span class="text-2xl leading-none ltr:mr-1 rtl:ml-1">)</span>
						</template>
					</div>
					<p v-if="partialTags.length > 0" class="text-xs mt-1.5 italic">{{ $t("dialogs.photo_tags.partial_tags_info") }}</p>
				</div>
				<div class="my-3 first:mt-0 last:mb-0">
					<TagsInput v-model="tags" :placeholder="$t('dialogs.photo_tags.no_tags')" :add="true" />
				</div>
				<div>
					<Checkbox v-model="shallOverride" :binary="true" input-id="shallOverride" />
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
import { computed, ref, toRef } from "vue";
import PhotoService from "@/services/photo-service";
import AlbumService from "@/services/album-service";
import { sprintf } from "sprintf-js";
import { useToast } from "primevue/usetoast";
import Button from "primevue/button";
import Dialog from "primevue/dialog";
import Checkbox from "primevue/checkbox";
import { trans } from "laravel-vue-i18n";
import TagsService from "@/services/tags-service";
import TagsInput from "@/v7/components/forms/basic/TagsInput.vue";
import { useExistingTags } from "@/composables/tags/existingTags";
import { useAlbumStore } from "@/stores/AlbumState";

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
const albumStore = useAlbumStore();

const question = computed(() => {
	if (props.photo) {
		return trans("dialogs.photo_tags.question");
	}
	return sprintf(trans("dialogs.photo_tags.question_multiple"), props.photoIds?.length);
});

const { commonTags, partialTags, hasExistingTags } = useExistingTags(toRef(props, "photo"), toRef(props, "photoIds"));

// Slack-like inline code rendering for the tags already set on the photos.
const tagChipClass =
	"font-mono text-xs px-1.5 py-0.5 rounded-sm border border-surface-400 dark:border-surface-700 bg-surface-100 dark:bg-surface-800 text-red-500";

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
		albumStore.bumpTagsRevision();
		close();
		emits("tagged");
	});
}
</script>
