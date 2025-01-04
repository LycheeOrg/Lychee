<template>
	<Dialog v-model:visible="visible" modal pt:root:class="border-none m-3" pt:mask:style="backdrop-filter: blur(2px)" @hide="closeCallback">
		<template #container="{ closeCallback }">
			<div class="flex flex-col gap-4 bg-gradient-to-b from-bg-300 to-bg-400 relative max-w-[500px] text-sm rounded-md text-muted-color">
				<div class="p-9 text-muted-color-emphasis">
					<h2 class="my-6 font-bold text-center" v-if="is_se_enabled">{{ $t("dialogs.about.thank_you") }}</h2>
					<template v-if="!is_se_enabled">
						<h2 class="my-6 font-bold text-center">{{ $t("dialogs.register.enter_license") }}</h2>
						<p class="text-muted-color text-center">
							<FloatLabel variant="on">
								<InputText v-model="licenseKey" id="licenseKey" class="w-full" @update:model-value="licenseKeyIsInvValid = false" />
								<label for="licenseKey">{{ $t("dialogs.register.license_key") }}</label>
							</FloatLabel>
							<span class="inline-block mt-4 font-bold text-danger-600" v-if="licenseKey && licenseKeyIsInvValid">
								{{ $t("dialogs.register.invalid_license") }}
							</span>
						</p>
					</template>
				</div>
				<div class="flex justify-center">
					<Button @click="closeCallback" severity="info" class="w-full font-bold border-none rounded-none rounded-bl-xl">
						{{ $t("dialogs.button.close") }}
					</Button>
					<Button
						v-if="!is_se_enabled"
						@click="register"
						severity="contrast"
						:disabled="!isValidForm"
						class="w-full font-bold border-none rounded-none rounded-br-xl"
					>
						{{ $t("dialogs.register.register") }}
					</Button>
				</div>
			</div>
		</template>
	</Dialog>
</template>
<script setup lang="ts">
import { computed, Ref, ref } from "vue";
import Button from "primevue/button";
import Dialog from "primevue/dialog";
import { useLycheeStateStore } from "@/stores/LycheeState";
import { storeToRefs } from "pinia";
import InputText from "../forms/basic/InputText.vue";
import FloatLabel from "primevue/floatlabel";
import MaintenanceService from "@/services/maintenance-service";
import { useToast } from "primevue/usetoast";
import { trans } from "laravel-vue-i18n";

const visible = defineModel("visible") as Ref<boolean>;
const licenseKey = ref<string | undefined>(undefined);
const lycheeStore = useLycheeStateStore();
const toast = useToast();

const isValidForm = computed(() => {
	return licenseKey.value !== undefined && licenseKey.value !== "";
});

const licenseKeyIsInvValid = ref(false);

const { is_se_enabled, is_se_preview_enabled, is_se_info_hidden } = storeToRefs(lycheeStore);

function closeCallback() {
	visible.value = false;
}

function register() {
	if (licenseKey.value === undefined || licenseKey.value === "") {
		return;
	}

	MaintenanceService.register(licenseKey.value)
		.then((response) => {
			if (response.data.success) {
				is_se_enabled.value = true;
				is_se_preview_enabled.value = false;
				is_se_info_hidden.value = false;
				visible.value = false;
				toast.add({
					severity: "success",
					summary: trans("dialogs.about.thank_you"),
					life: 5000,
				});
			} else {
				licenseKeyIsInvValid.value = true;
			}
		})
		.catch(() => {
			licenseKeyIsInvValid.value = true;
		});
}
</script>
