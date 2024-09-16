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
		<template #container="{ closeCallback }">
			<form>
				<div v-focustrap class="flex flex-col gap-4 relative w-[500px] text-sm rounded-md pt-9">
					<div class="flex flex-row justify-between gap-2 px-9 w-full items-center">
						<label for="token-dialog-token" class="flex-shrink">Token</label>
						<InputText
							class="flex-grow[4] bg-transparent w-full pt-1 pb-0 px-0.5 h-7 border-b border-b-solid focus:border-b-primary-500 disabled:italic disabled:text-center inline-block"
							:disabled="isDisabled"
							v-model="token"
							:readonly="true"
						/>
						<Button text severity="secondary" class="group" :title="$t('lychee.RESET')" @click="resetToken">
							<MiniIcon class="w-4 h-4 ionicons group-hover:fill-primary-500" icon="reload" />
						</Button>
						<Button
							text
							severity="secondary"
							class="group"
							:tile="$t('lychee.DISABLE_TOKEN_TOOLTIP')"
							@click="unsetToken"
							v-if="!isDisabled"
						>
							<MiniIcon class="w-4 h-4 ionicons group-hover:fill-red-700" icon="ban" />
						</Button>
					</div>
				</div>
				<div class="flex justify-center mt-9">
					<Button
						@click="closeCallback"
						severity="secondary"
						class="w-full border-none font-bold border-1 border-white-alpha-30 hover:bg-white-alpha-10"
					>
						{{ $t("lychee.CLOSE") }}
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
import MiniIcon from "@/components/icons/MiniIcon.vue";
import ProfileService from "@/services/profile-service";

const visible = defineModel();

const isDisabled = ref(true);
const token = ref(undefined as undefined | string);

function resetToken() {
	ProfileService.resetToken().then((response) => {
		token.value = response.data.token;
		isDisabled.value = false;
	});
}
function unsetToken() {
	ProfileService.unsetToken().then(() => {
		token.value = trans("lychee.DISABLED_TOKEN_STATUS_MSG");
		isDisabled.value = true;
	});
}

watch(visible, (_value) => {
	AuthService.user().then((response) => {
		token.value = response.data.has_token ? trans("lychee.TOKEN_NOT_AVAILABLE") : trans("lychee.DISABLED_TOKEN_STATUS_MSG");
		isDisabled.value = !response.data.has_token;
	});
});
</script>
