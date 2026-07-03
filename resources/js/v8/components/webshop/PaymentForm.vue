<template>
	<div
		v-if="options !== undefined"
		class="w-full flex flex-col p-8 bg-elevated/50 rounded border border-default"
		:class="{
			'bg-elevated/50': selectedProvider !== 'PayPal',
		}"
	>
		<div v-if="!canProcessPayment" class="flex flex-col">
			<div class="text-lg mb-12 font-bold text-center">{{ $t("webshop.paymentForm.selectProvider") }}</div>
			<USelectMenu
				v-model="selectedProvider"
				:items="options.payment_providers"
				:placeholder="$t('webshop.paymentForm.selectProviderPlaceholder')"
				class="mt-4"
				@update:model-value="createSession"
			/>
		</div>
		<div v-else-if="selectedProvider === 'Mollie'" class="h-full flex flex-col justify-between">
			<div id="checkout" class="flex flex-wrap gap-x-4 justify-between"></div>
			<div
				class="text-muted text-xs text-center mt-4"
				v-html="
					sprintf(
						trans('webshop.paymentForm.pciCompliant'),
						'<a href=\'https://www.pcisecuritystandards.org/\' class=\'hover:text-primary text-highlighted\'>PCI-DSS</a>',
						selectedProvider,
					)
				"
			></div>
		</div>
		<div v-else-if="selectedProvider === 'PayPal'" class="h-full flex flex-col justify-between">
			<div id="paypal-button-container" class="flex flex-wrap gap-x-4 justify-between"></div>
		</div>
		<div v-else class="flex flex-col">
			<div class="text-lg mb-12 font-bold text-center" @click="getFakeNumber">
				{{ sprintf(trans("webshop.paymentForm.enterInfo"), selectedProvider) }}
			</div>
			<CardForm @updated="updateCardDetails" />
		</div>
	</div>
</template>
<script setup lang="ts">
import { useStepOne } from "@/composables/checkout/useStepOne";
import { useStepTwo } from "@/composables/checkout/useStepTwo";
import { useOrderManagementStore } from "@/stores/OrderManagement";
import { useUserStore } from "@/stores/UserState";
import { useAppToast } from "@/v8/composables/useAppToast";
import { onMounted, watch } from "vue";
import CardForm from "@/v8/components/forms/card/CardForm.vue";
import { useMollie } from "@/composables/checkout/useMollie";
import { trans } from "laravel-vue-i18n";
import { sprintf } from "sprintf-js";
import { usePaypal } from "@/composables/checkout/usePaypal";

const userStore = useUserStore();
const orderStore = useOrderManagementStore();

const toast = useAppToast();

const { email, options } = useStepOne(userStore, orderStore);
const { mollieComponent, mountMollie } = useMollie(toast);
const { mountPaypal } = usePaypal(toast);
const { canProcessPayment, createSession, selectedProvider, updateCardDetails, getFakeNumber } = useStepTwo(email, orderStore, toast);

watch(
	() => selectedProvider.value,
	async (new_val) => {
		if (new_val === "Mollie") {
			mountMollie(options);
		}
		if (new_val === "PayPal") {
			console.log("Mounting PayPal");
			mountPaypal(options);
		}
	},
);

onMounted(async () => {
	mollieComponent.value?.unmount();
});
</script>

<style lang="css">
.mollie-card-component {
	width: 100%;
}

.mollie-card-component--verificationCode,
.mollie-card-component--expiryDate {
	width: calc(50% - 0.5rem);
}

.mollie-component--cardNumber,
.mollie-component--expiryDate,
.mollie-component--verificationCode,
.mollie-component--cardHolder {
	width: 100%;
	background: transparent;
	border-bottom: 1px solid var(--p-surface-300);
}

.dark .mollie-component--cardNumber,
.dark .mollie-component--expiryDate,
.dark .mollie-component--verificationCode,
.dark .mollie-component--cardHolder {
	background: transparent;
	padding-bottom: 0.15rem;
	border-bottom: 1px solid var(--p-surface-300);

	&:hover {
		border-bottom: 1px solid var(--p-primary-400);
	}
}

.mollie-card-component__error {
	color: var(--danger);
	font-size: 0.75rem;
	margin-top: 0.25rem;
}

.is-invalid {
	border-bottom: 1px solid var(--danger);
}

.has-focus {
	border-bottom: 1px solid var(--p-primary-400) !important;
}
</style>
