<template>
	<UModal v-model:open="visible" :dismissible="true">
		<template #body>
			<p class="mb-5">{{ $t("dialogs.unlock.password_required") }}</p>
			<UFormField :label="$t('dialogs.unlock.password')">
				<InputPassword id="albumPassword" v-model="password" @keydown.enter="unlock" />
				<UAlert v-if="invalidPassword" color="error" variant="soft" class="mt-2" :description="$t('dialogs.unlock.invalid_password')" />
			</UFormField>
		</template>
		<template #footer>
			<div class="flex w-full gap-2">
				<UButton color="neutral" variant="soft" class="flex-1 justify-center font-bold" @click="hide">
					{{ $t("dialogs.button.cancel") }}
				</UButton>
				<UButton color="primary" variant="solid" class="flex-1 justify-center font-bold" :disabled="!deactivate" @click="unlock">
					{{ $t("dialogs.unlock.unlock") }}
				</UButton>
			</div>
		</template>
	</UModal>
</template>
<script setup lang="ts">
import AlbumService from "@/services/album-service";
import { computed, ref, watch } from "vue";
import InputPassword from "@/v8/components/forms/basic/InputPassword.vue";
import { useAlbumStore } from "@/stores/AlbumState";
import { useAlbumListStore } from "@/stores/AlbumListState";

const visible = defineModel("open", { default: false });

const emits = defineEmits<{
	reload: [];
	fail: [];
}>();

const albumStore = useAlbumStore();
const albumListStore = useAlbumListStore();
// Fetch the id of the current album
const albumId = computed(() => albumStore.albumId);

const password = ref<string | undefined>(undefined);
const deactivate = computed(() => password.value !== undefined && password.value.length > 0);
const invalidPassword = ref(false);

watch(password, () => {
	invalidPassword.value = false;
});

function unlock() {
	if (albumId.value === undefined || password.value === undefined) {
		return;
	}

	AlbumService.unlock(albumId.value, password.value)
		.then((_response) => {
			AlbumService.clearAlbums();
			AlbumService.clearCache(albumId.value);
			albumListStore.invalidate();
			invalidPassword.value = false;
			emits("reload");
		})
		.catch((error) => {
			if (error.response && error.response.status === 403) {
				invalidPassword.value = true;
				return;
			}

			visible.value = false;
			emits("fail");
		});
}

function hide() {
	visible.value = false;
	history.back();
}
</script>
