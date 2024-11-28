<template>
	<Dialog v-model:visible="visible" modal pt:root:class="border-none" pt:mask:style="backdrop-filter: blur(2px)" v-if="!isWebAuthnUnavailable">
		<template #container="{ closeCallback }">
			<form v-focustrap class="flex flex-col gap-4 relative max-w-full text-sm rounded-md pt-9">
				<div class="inline-flex flex-col gap-2 px-9">
					<FloatLabel variant="on">
						<InputText id="username" v-model="username" />
						<label class="" for="username">{{ $t("lychee.USERNAME") }}</label>
					</FloatLabel>
				</div>
				<div class="flex items-center mt-9">
					<Button @click="closeCallback" severity="secondary" class="w-full font-bold border-none rounded-none rounded-bl-xl flex-shrink-2">
						{{ $t("lychee.CANCEL") }}
					</Button>
					<Button @click="login" severity="contrast" class="w-full font-bold border-none rounded-none rounded-br-xl flex-shrink">
						{{ $t("lychee.U2F") }}
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
import AlbumService from "@/services/album-service";

const toast = useToast();
const visible = defineModel("visible", { default: false }) as Ref<boolean>;
const emits = defineEmits<{
	"logged-in": [];
}>();

const isWebAuthnUnavailable = computed<boolean>(() => WebAuthnService.isWebAuthnUnavailable());

const authStore = useAuthStore();
const username = ref("");
const userId = ref<number | null>(null);

function login() {
	WebAuthnService.login(username.value, userId.value)
		.then(function () {
			toast.add({
				severity: "success",
				summary: trans("lychee.U2F_AUTHENTIFICATION_SUCCESS"),
				life: 3000,
			});
			visible.value = false;
			authStore.setUser(null);
			AlbumService.clearCache();
			emits("logged-in");
		})
		.catch((e) =>
			toast.add({
				severity: "error",
				summary: trans("lychee.ERROR_TEXT"),
				detail: e.response.data.message,
				life: 3000,
			}),
		);
}
</script>
