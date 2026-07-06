<template>
	<UModal v-if="!isWebAuthnUnavailable" v-model:open="is_webauthn_open">
		<template #body>
			<form class="flex flex-col gap-4 relative max-w-md w-full text-sm rounded-md">
				<UFormField :label="$t('dialogs.login.username')">
					<UInput id="username" v-model="username" class="w-full" />
				</UFormField>
			</form>
		</template>
		<template #footer>
			<div class="flex w-full gap-2">
				<UButton
					color="neutral"
					variant="soft"
					class="flex-1 justify-center font-bold"
					@click="
						() => {
							is_webauthn_open = false;
						}
					"
				>
					{{ $t("dialogs.button.cancel") }}
				</UButton>
				<UButton color="neutral" class="flex-1 justify-center font-bold" @click="login">
					{{ $t("dialogs.webauthn.u2f") }}
				</UButton>
			</div>
		</template>
	</UModal>
</template>
<script setup lang="ts">
import { useAppToast } from "@/v8/composables/useAppToast";
import { computed, ref } from "vue";
import { trans } from "laravel-vue-i18n";
import WebAuthnService from "@/services/webauthn-service";
import { useTogglablesStateStore } from "@/stores/ModalsState";
import AlbumService from "@/services/album-service";
import { storeToRefs } from "pinia";
import { useUserStore } from "@/stores/UserState";

const toast = useAppToast();
const emits = defineEmits<{
	"logged-in": [];
}>();

const togglableStore = useTogglablesStateStore();
const userStore = useUserStore();

const isWebAuthnUnavailable = computed<boolean>(() => WebAuthnService.isWebAuthnUnavailable());
const { is_webauthn_open } = storeToRefs(togglableStore);

const username = ref("");
const userId = ref<number | null>(null);

function login() {
	WebAuthnService.login(username.value, userId.value)
		.then(function () {
			toast.add({
				severity: "success",
				summary: trans("dialogs.webauthn.success"),
				life: 3000,
			});
			is_webauthn_open.value = false;
			userStore.setUser(undefined);
			AlbumService.clearCache();
			emits("logged-in");
		})
		.catch((e) =>
			toast.add({
				severity: "error",
				summary: trans("dialogs.webauthn.error"),
				detail: e.response.data.message,
				life: 3000,
			}),
		);
}
</script>
