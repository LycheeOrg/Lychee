<template>
	<UModal v-model:open="is_create_album_visible" :dismissible="true">
		<template #body>
			<p class="mb-5">{{ $t("dialogs.new_album.info") }}</p>
			<div class="inline-flex flex-col gap-2 w-full">
				<UFormField :label="$t('dialogs.new_album.title')">
					<UInput id="title" v-model="title" class="w-full" />
				</UFormField>
				<div v-if="visibilityInfo" class="text-xs text-muted mt-2 flex items-center gap-1">
					<UIcon name="prime:info-circle" />
					{{ $t(visibilityInfo) }}
				</div>
			</div>
		</template>
		<template #footer>
			<div class="flex w-full gap-2">
				<UButton color="neutral" variant="soft" class="flex-1 justify-center font-bold" @click="is_create_album_visible = false">
					{{ $t("dialogs.button.cancel") }}
				</UButton>
				<UButton color="primary" class="flex-1 justify-center font-bold" :disabled="!isValid" @click="create">
					{{ $t("dialogs.new_album.create") }}
				</UButton>
			</div>
		</template>
	</UModal>
</template>
<script setup lang="ts">
import AlbumService from "@/services/album-service";
import { computed, ref } from "vue";
import { useRouter } from "vue-router";
import { useAppToast } from "@/v8/composables/useAppToast";
import { useTogglablesStateStore } from "@/stores/ModalsState";
import { storeToRefs } from "pinia";
import { onKeyPressed } from "@vueuse/core";
import { usePhotoRoute } from "@/composables/photo/photoRoute";
import { useLycheeStateStore } from "@/stores/LycheeState";

const togglableStore = useTogglablesStateStore();
const { is_create_album_visible } = storeToRefs(togglableStore);
const router = useRouter();
const { getParentId } = usePhotoRoute(router);

const toast = useAppToast();
const lycheeState = useLycheeStateStore();

const title = ref<string | undefined>(undefined);

const visibilityInfo = computed(() => {
	const protection = lycheeState.default_album_protection;
	const parentId = getParentId();

	switch (protection) {
		case "private":
			return "dialogs.new_album.visibility_private";
		case "public":
			return "dialogs.new_album.visibility_public";
		case "public_hidden":
			return "dialogs.new_album.visibility_public_hidden";
		case "inherit":
			return parentId ? "dialogs.new_album.visibility_inherit" : "dialogs.new_album.visibility_inherit_no_parent";
		default:
			return null;
	}
});

const isValid = computed(() => title.value !== undefined && title.value.length > 0 && title.value.length <= 100);

function create() {
	if (!isValid.value) {
		return;
	}
	// TODO later: Add tick boxes for inheriting properties from parent album
	// (public, visible, download, password, license. sorting etc...)

	AlbumService.createAlbum({
		title: title.value as string,
		parent_id: getParentId() ?? null,
	})
		.then((response) => {
			title.value = undefined;
			is_create_album_visible.value = false;
			AlbumService.clearCache(getParentId());
			router.push(`/gallery/${response.data}`);
		})
		.catch((error) => {
			toast.add({ severity: "error", summary: "Oups", detail: error.message });
		});
}

onKeyPressed("Enter", () => is_create_album_visible.value && isValid.value && create());
</script>
