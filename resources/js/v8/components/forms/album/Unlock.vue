<template>
	<UModal v-model:open="visible" :dismissible="true">
		<template #body>
			<p class="mb-5">{{ passwordRequiredMessage }}</p>
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
				<UButton
					color="primary"
					variant="solid"
					class="flex-1 justify-center font-bold"
					:disabled="!deactivate"
					:loading="isUnlocking"
					@click="unlock"
				>
					{{ $t("dialogs.unlock.unlock") }}
				</UButton>
			</div>
		</template>
	</UModal>
</template>
<script setup lang="ts">
import AlbumService from "@/services/album-service";
import { computed, onBeforeUnmount, ref, watch } from "vue";
import InputPassword from "@/v8/components/forms/basic/InputPassword.vue";
import { useAlbumStore } from "@/stores/AlbumState";
import { useAlbumListStore } from "@/stores/AlbumListState";
import { useAlbumsStore } from "@/stores/AlbumsState";
import { trans } from "laravel-vue-i18n";

const visible = defineModel("open", { default: false });

const emits = defineEmits<{
	reload: [];
	fail: [];
}>();

const albumStore = useAlbumStore();
const albumListStore = useAlbumListStore();
const albumsStore = useAlbumsStore();
// Fetch the id of the current album
const albumId = computed(() => albumStore.albumId);

// The title is only known when the album was opened from a listing, not from a direct link.
const passwordRequiredMessage = computed(() => {
	const title = albumsStore.titleOf(albumId.value);
	return title === undefined ? trans("dialogs.unlock.password_required") : trans("dialogs.unlock.password_required_named", { title: title });
});

const password = ref<string | undefined>(undefined);
const deactivate = computed(() => password.value !== undefined && password.value.length > 0);
const invalidPassword = ref(false);
// True from submitting the password until the dialog closes or the unlock fails.
const isUnlocking = ref(false);
// The unlock request in flight; aborted when the dialog closes so that its callbacks never run late.
let unlockRequest: AbortController | undefined = undefined;

watch(password, () => {
	invalidPassword.value = false;
});

watch(visible, (isVisible) => {
	if (!isVisible) {
		stopUnlocking();
	}
});

onBeforeUnmount(stopUnlocking);

function stopUnlocking() {
	unlockRequest?.abort();
	unlockRequest = undefined;
	isUnlocking.value = false;
}

function unlock() {
	if (albumId.value === undefined || password.value === undefined || isUnlocking.value) {
		return;
	}

	const requestedAlbumId = albumId.value;
	const request = new AbortController();
	unlockRequest = request;
	isUnlocking.value = true;
	AlbumService.unlock(requestedAlbumId, password.value, request.signal)
		.then((_response) => {
			if (request.signal.aborted) {
				return;
			}

			AlbumService.clearAlbums();
			AlbumService.clearCache(requestedAlbumId);
			albumListStore.invalidate();
			invalidPassword.value = false;
			emits("reload");
		})
		.catch((error) => {
			if (request.signal.aborted) {
				return;
			}

			unlockRequest = undefined;
			isUnlocking.value = false;
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
