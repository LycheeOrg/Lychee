<template>
	<div
		class="w-full flex flex-col p-8 bg-surface-50 dark:bg-surface-950/25 rounded border border-surface-200 dark:border-surface-700"
		v-if="options !== undefined"
	>
		<div v-if="!canProcessPayment" class="flex flex-col">
			<div class="text-lg mb-12 font-bold text-center">Select your payment provider</div>
			<Select
				v-model="selectedProvider"
				:options="options.payment_providers"
				placeholder="Select a payment provider"
				class="mt-4"
				@update:modelValue="createSession"
			/>
		</div>
		<div v-else-if="selectedProvider === 'Mollie'" class="h-full flex flex-col justify-between">
			<div id="checkout" class="flex flex-wrap gap-x-4 justify-between"></div>
			<div class="text-muted-color text-xs text-center mt-4">
				This payment is
				<a href="https://www.pcisecuritystandards.org/" class="hover:text-primary-400 text-muted-color-emphasis">PCI-DSS</a>
				compliant.<br />
				Your card details are processed securely by {{ selectedProvider }}.
			</div>
		</div>
		<div v-else class="flex flex-col">
			<div class="text-lg mb-12 font-bold text-center" @click="getFakeNumber">Enter your info for {{ selectedProvider }}</div>
			<CardForm @updated="updateCardDetails" />
		</div>
	</div>
</template>
<script setup lang="ts">
import { useStepOne } from "@/composables/checkout/useStepOne";
import { useSteps } from "@/composables/checkout/useSteps";
import { useStepTwo } from "@/composables/checkout/useStepTwo";
import { useOrderManagementStore } from "@/stores/OrderManagement";
import { useUserStore } from "@/stores/UserState";
import { useToast } from "primevue/usetoast";
import { onMounted, watch } from "vue";
import CardForm from "@/components/forms/card/CardForm.vue";
import Select from "primevue/select";
import { useMollie } from "@/composables/checkout/useMollie";

const userStore = useUserStore();
const orderStore = useOrderManagementStore();

const toast = useToast();
const { steps } = useSteps();

const { email, options } = useStepOne(userStore, orderStore);
const { mollie, mollieComponent, mountMollie } = useMollie(options, toast);
const { canProcessPayment, createSession, selectedProvider, updateCardDetails, getFakeNumber } = useStepTwo(email, orderStore, steps, toast, mollie);

watch(
	() => selectedProvider.value,
	async (new_val) => {
		if (new_val === "Mollie") {
			mountMollie();
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
