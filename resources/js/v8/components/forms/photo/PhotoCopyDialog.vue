<template>
	<UModal v-model:open="visible" :dismissible="true">
		<template #body>
			<div v-if="titleCopyTo !== undefined">
				<p class="text-center text-muted flex items-center justify-center gap-2">
					{{ confirmation }}
					<UButton
						icon="prime:times"
						color="error"
						variant="ghost"
						size="xs"
						@click="
							titleCopyTo = undefined;
							destination_id = undefined;
						"
					/>
				</p>
			</div>
			<div v-else>
				<div v-if="error_no_target === false">
					<span class="font-bold">
						{{ question }}
					</span>
					<SearchTargetAlbum :album-ids="undefined" @selected="selected" @no-target="error_no_target = true" />
				</div>
				<div v-else>
					<p class="text-center text-muted">{{ $t("dialogs.photo_copy.no_albums") }}</p>
				</div>
			</div>
		</template>
		<template #footer>
			<div v-if="titleCopyTo !== undefined" class="flex w-full gap-2">
				<UButton color="neutral" variant="soft" class="flex-1 justify-center font-bold" @click="close">
					{{ $t("dialogs.button.cancel") }}
				</UButton>
				<UButton color="neutral" class="flex-1 justify-center font-bold" @click="execute">
					{{ $t("dialogs.photo_copy.copy") }}
				</UButton>
			</div>
			<UButton v-else color="neutral" variant="soft" class="w-full justify-center font-bold" @click="visible = false">
				{{ $t("dialogs.button.cancel") }}
			</UButton>
		</template>
	</UModal>
</template>
<script setup lang="ts">
import { computed, ref } from "vue";
import PhotoService from "@/services/photo-service";
import AlbumService from "@/services/album-service";
import { sprintf } from "sprintf-js";
import SearchTargetAlbum from "@/v8/components/forms/album/SearchTargetAlbum.vue";
import { useAppToast } from "@/v8/composables/useAppToast";
import { trans } from "laravel-vue-i18n";

const props = defineProps<{
	photo?: App.Http.Resources.Models.PhotoResource;
	photoIds?: string[];
}>();

const visible = defineModel<boolean>("open", { default: false });

const emits = defineEmits<{
	copy: [];
}>();

const toast = useAppToast();
const titleCopyTo = ref<string | undefined>(undefined);
const destination_id = ref<string | undefined | null>(undefined);
const error_no_target = ref(false);

function selected(target: App.Http.Resources.Models.TargetAlbumResource) {
	titleCopyTo.value = target.original;
	destination_id.value = target.id;
}

function close() {
	titleCopyTo.value = undefined;
	destination_id.value = undefined;
	visible.value = false;
}

const question = computed(() => {
	if (props.photo) {
		return sprintf(trans("dialogs.photo_copy.copy_to"), props.photo?.title);
	}
	return sprintf(trans("dialogs.photo_copy.copy_to_multiple"), props.photoIds?.length);
});

const confirmation = computed(() => {
	if (props.photo) {
		return sprintf(trans("dialogs.photo_copy.confirm"), props.photo.title, titleCopyTo.value);
	}
	return sprintf(trans("dialogs.photo_copy.confirm_multiple"), props.photoIds?.length, titleCopyTo.value);
});

function execute() {
	if (destination_id.value === undefined) {
		return;
	}

	visible.value = false;
	let photoCopiedIds = [];
	if (props.photo) {
		photoCopiedIds.push(props.photo.id);
	} else {
		photoCopiedIds = props.photoIds as string[];
	}
	PhotoService.copy(destination_id.value, photoCopiedIds).then(() => {
		toast.add({
			severity: "success",
			summary: trans("dialogs.photo_copy.copied"),
			life: 3000,
		});
		// Clear the cache for the destination album
		AlbumService.clearCache(destination_id.value);

		emits("copy");
	});
}
</script>
