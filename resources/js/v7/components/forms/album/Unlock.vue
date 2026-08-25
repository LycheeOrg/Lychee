<template>
	<Dialog v-model:visible="visible" modal pt:root:class="border-none" pt:mask:style="backdrop-filter: blur(2px)">
		<template #container>
			<div v-focustrap class="flex flex-col relative max-w-xl text-sm rounded-md pt-9">
				<p class="mb-5 px-9">{{ $t("dialogs.unlock.password_required") }}</p>
				<div class="inline-flex flex-col gap-2 px-9">
					<FloatLabel variant="on">
						<InputPassword id="albumPassword" v-model="password" @keydown.enter="unlock" />
						<label for="albumPassword">{{ $t("dialogs.unlock.password") }}</label>
					</FloatLabel>
					<Message v-if="invalidPassword" severity="error">{{ $t("dialogs.unlock.invalid_password") }}</Message>
				</div>
				<div class="flex items-center mt-9">
					<Button severity="secondary" class="w-full font-bold border-none rounded-bl-xl" @click="hide">
						{{ $t("dialogs.button.cancel") }}
					</Button>
					<Button
						severity="contrast"
						class="font-bold w-full border-none rounded-none rounded-br-xl"
						:disabled="!deactivate"
						@click="unlock"
					>
						{{ $t("dialogs.unlock.unlock") }}
					</Button>
				</div>
			</div>
		</template>
	</Dialog>
</template>
<script setup lang="ts">
import AlbumService from "@/services/album-service";
import Button from "primevue/button";
import Dialog from "primevue/dialog";
import FloatLabel from "primevue/floatlabel";
import Message from "primevue/message";
import { computed, ref, watch } from "vue";
import InputPassword from "@/v7/components/forms/basic/InputPassword.vue";
import { useAlbumStore } from "@/stores/AlbumState";

const visible = defineModel("visible", { default: false });

const emits = defineEmits<{
	reload: [];
	fail: [];
}>();

const albumStore = useAlbumStore();
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
