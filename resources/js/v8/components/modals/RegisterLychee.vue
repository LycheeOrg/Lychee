<template>
	<UModal v-model:open="visible" :dismissible="true">
		<template #body>
			<h2 v-if="is_se_enabled" class="my-6 font-bold text-center">{{ $t("dialogs.about.thank_you") }}</h2>
			<template v-if="!is_se_enabled">
				<h2 class="my-6 font-bold text-center">{{ $t("dialogs.register.enter_license") }}</h2>
				<div class="text-center">
					<UFormField :label="$t('dialogs.register.license_key')">
						<UInput id="licenseKey" v-model="licenseKey" class="w-full" @update:model-value="licenseKeyIsInvValid = false" />
					</UFormField>
					<span v-if="licenseKey && licenseKeyIsInvValid" class="inline-block mt-4 font-bold text-error">
						{{ $t("dialogs.register.invalid_license") }}
					</span>
				</div>
			</template>
		</template>
		<template #footer>
			<div class="flex w-full gap-2">
				<UButton class="flex-1 justify-center" color="neutral" variant="soft" @click="closeCallback">
					{{ $t("dialogs.button.close") }}
				</UButton>
				<UButton v-if="!is_se_enabled" class="flex-1 justify-center" :disabled="!isValidForm" @click="register">
					{{ $t("dialogs.register.register") }}
				</UButton>
			</div>
		</template>
	</UModal>
</template>
<script setup lang="ts">
import { computed, Ref, ref } from "vue";
import { useLycheeStateStore } from "@/stores/LycheeState";
import { storeToRefs } from "pinia";
import MaintenanceService from "@/services/maintenance-service";
import { useAppToast } from "@/v8/composables/useAppToast";
import { trans } from "laravel-vue-i18n";

const visible = defineModel("open", { default: false }) as Ref<boolean>;
const licenseKey = ref<string | undefined>(undefined);
const lycheeStore = useLycheeStateStore();
const toast = useAppToast();

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
