<template>
	<Dialog
		v-model:visible="visible"
		modal
		:pt="{
			root: 'border-none',
			mask: {
				style: 'backdrop-filter: blur(2px)',
			},
		}"
	>
		<template #container>
			<form>
				<div v-focustrap class="flex flex-col gap-4 relative w-[500px] text-sm rounded-md pt-9">
					<div class="flex flex-col justify-center gap-2 px-9 w-full items-center">
						<template v-if="token === undefined">
							<span>{{ tokenText }}</span>
							<a v-if="isDisabled" severity="contrast" class="cursor-pointer font-bold text-primary-500 underline" @click="resetToken">
								{{ $t("profile.token.create") }}
							</a>
							<a v-else severity="contrast" class="cursor-pointer font-bold text-primary-500 underline" @click="resetToken">
								{{ $t("profile.token.reset") }}
							</a>
						</template>
						<template v-else>
							<span><i class="text-danger-600 pi pi-exclamation-triangle ltr:mr-2 rtl:ml-2" />{{ $t("profile.token.warning") }}</span>
							<InputText
								v-model="token"
								class="grow-4 bg-transparent w-full pt-1 pb-0 px-0.5 h-7 border-b border-b-solid focus:border-b-primary-500 disabled:italic disabled:text-center inline-block"
								:readonly="true"
							/>
						</template>
					</div>
				</div>
				<div class="flex justify-center mt-9">
					<Button severity="secondary" class="w-full border-none font-bold rounded-none ltr:rounded-bl-lg rtl:rounded-br-lg" @click="close">
						{{ $t("dialogs.button.close") }}
					</Button>
					<Button
						v-if="!isDisabled && token === undefined"
						severity="danger"
						class="w-full border-none font-bold rounded-none ltr:rounded-br-lg rtl:rounded-bl-lg"
						@click="disable"
					>
						{{ $t("profile.token.disable") }}
					</Button>
				</div>
			</form>
		</template>
	</Dialog>
</template>
<script setup lang="ts">
import { ref, watch } from "vue";
import Dialog from "primevue/dialog";
import Button from "primevue/button";
import { trans } from "laravel-vue-i18n";
import AuthService from "@/services/auth-service";
import InputText from "@/components/forms/basic/InputText.vue";
import ProfileService from "@/services/profile-service";
import { useToast } from "primevue/usetoast";

const visible = defineModel<boolean>();

const isDisabled = ref(true);
const token = ref<string | undefined>(undefined);
const tokenText = ref("");
const toast = useToast();

function resetToken() {
	ProfileService.resetToken().then((response) => {
		token.value = response.data.token;
		isDisabled.value = false;
	});
}

function close() {
	// ! We hide this value to prevent the token from being displayed again.
	token.value = undefined;
	visible.value = false;
}

function disable() {
	ProfileService.unsetToken().then(() => {
		toast.add({
			severity: "success",
			summary: trans("profile.token.disabled"),
			life: 3000,
		});
		visible.value = false;
	});
}

watch(visible, () => {
	AuthService.user().then((response) => {
		tokenText.value = response.data.has_token ? trans("profile.token.unavailable") : trans("profile.token.no_data");
		isDisabled.value = !response.data.has_token;
	});
});
</script>
