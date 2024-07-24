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
					<div class="inline-flex flex-col gap-2 px-9 w-full">
						<label for="token-dialog-token">Token</label>
						<InputText
							class="mx-2 !w-2/3 bg-transparent pt-1 pb-0 px-0.5 h-7 border-b border-b-solid focus:border-b-primary-500 disabled:italic disabled:text-center inline-block"
							:disabled="isDisabled"
							v-model="token"
							:readonly="true"
						/>
						<!-- class="mx-2 w-2/3 bg-transparent pt-1 pb-0 px-0.5 h-7
						border-b border-b-solid focus:border-b-primary-400 border-b-neutral-800
						disabled:italic disabled:text-center text-text-main-200 disabled:text-text-main-400
						" -->
						<div class="inline w-1/4">
							<a id="button_reset_token" class="cursor-pointer" :title="$t('lychee.RESET')" @click="resetToken">
								<MiniIcon class="my-0 ml-1 w-4 h-4 ionicons hover:fill-primary-500" icon="reload" />
							</a>
							<!-- {{ -- < a id = "button_copy_token" @class(['button', 'hidden'=> $isDisabled]) title = '{{
						$t('lychee.URL_COPY_TO_CLIPBOARD') }}'>
							<x-icons.iconic class='my-0 ml-1 ionicons' icon='copy' />
						</a> --}} -->
							<a
								id="button_disable_token"
								:tile="$t('lychee.DISABLE_TOKEN_TOOLTIP')"
								@click="disableToken"
								:class="'cursor-pointer ' + (isDisabled ? 'hidden' : '')"
							>
								<MiniIcon class="w-4 h-4 my-0 ml-1 ionicons hover:fill-red-700" icon="ban" />
							</a>
						</div>
					</div>
				</div>
				<div class="flex justify-center">
					<Button @click="closeCallback" text autofocus class="p-3 w-full font-bold border-1 border-white-alpha-30 hover:bg-white-alpha-10">
						{{ $t("lychee.CLOSE") }}
					</Button>
				</div>
			</form>
		</template>
	</Dialog>
</template>
<script setup lang="ts">
import Dialog from "primevue/dialog";
import InputText from "../basic/InputText.vue";
import { ref } from "vue";
import MiniIcon from "@/components/icons/MiniIcon.vue";

const visible = defineModel();

const isDisabled = ref(true);
const token = ref(undefined as undefined | string);

function resetToken() {}
function disableToken() {}
</script>
