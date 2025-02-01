<template>
	<Dialog
		v-model:visible="is_webauthn_open"
		modal
		pt:root:class="border-none"
		pt:mask:style="backdrop-filter: blur(2px)"
		v-if="!isWebAuthnUnavailable"
	>
		<template #container="{ closeCallback }">
			<form v-focustrap class="flex flex-col gap-4 relative max-w-full text-sm rounded-md pt-9">
				<div class="inline-flex flex-col gap-2 px-9">
					<FloatLabel variant="on">
						<InputText id="username" v-model="username" />
						<label for="username">{{ $t("dialogs.login.username") }}</label>
					</FloatLabel>
				</div>
				<div class="flex items-center mt-9">
					<Button @click="closeCallback" severity="secondary" class="w-full font-bold border-none rounded-none rounded-bl-xl shrink-2">
						{{ $t("dialogs.button.cancel") }}
					</Button>
					<Button @click="login" severity="contrast" class="w-full font-bold border-none rounded-none rounded-br-xl shrink">
						{{ $t("dialogs.webauthn.u2f") }}
					</Button>
				</div>
			</form>
		</template>
	</Dialog>
</template>
<script setup lang="ts">
import Dialog from "primevue/dialog";
import FloatLabel from "primevue/floatlabel";
import Button from "primevue/button";
import { useToast } from "primevue/usetoast";
import { computed, Ref, ref } from "vue";
import InputText from "../forms/basic/InputText.vue";
import { trans } from "laravel-vue-i18n";
import WebAuthnService from "@/services/webauthn-service";
import { useAuthStore } from "@/stores/Auth";
import { useTogglablesStateStore } from "@/stores/ModalsState";
import AlbumService from "@/services/album-service";
import { storeToRefs } from "pinia";

const toast = useToast();
const emits = defineEmits<{
	"logged-in": [];
}>();

const togglableStore = useTogglablesStateStore();

const isWebAuthnUnavailable = computed<boolean>(() => WebAuthnService.isWebAuthnUnavailable());
const { is_webauthn_open } = storeToRefs(togglableStore);

const authStore = useAuthStore();
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
			authStore.setUser(null);
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
