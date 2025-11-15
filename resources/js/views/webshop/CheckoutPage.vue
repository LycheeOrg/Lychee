<template>
	<Toolbar class="w-full border-0 h-14 rounded-none">
		<template #start>
			<OpenLeftMenu />
		</template>

		<template #center>
			{{ "Checkout" }}
		</template>

		<template #end> </template>
	</Toolbar>
	<Panel :pt:header:class="'hidden'" class="border-0 md:max-w-3xl lg:max-w-5xl xl:max-w-7xl mt-9 mx-auto w-full">
		<Stepper :value="step" linear class="basis-[50rem]" v-if="options !== undefined">
			<StepList>
				<Step :value="1">Your info</Step>
				<template v-if="!options.is_offline">
					<Step :value="2">Payment</Step>
					<Step :value="3">Confirmation</Step>
				</template>
				<template v-else>
					<Step :value="2">Confirmation</Step>
				</template>
			</StepList>
			<StepPanels>
				<StepPanel :value="1">
					<div class="grid grid-cols-2 gap-4 mb-4" v-if="order">
						<OrderSummary />
						<InfoSection />
					</div>
					<div class="flex pt-6 ltr:justify-end rtl:justify-start">
						<!-- FIX RTL for the arrow of the button -->
						<Button label="Next" icon="pi pi-arrow-right" @click="next" class="border-none" :disabled="!isStepOneValid" />
					</div>
				</StepPanel>
				<template v-if="!options.is_offline">
					<StepPanel v-slot="{ activateCallback }" :value="2">
						<div div class="grid grid-cols-2 gap-4 mb-4">
							<OrderSummary />
							<div
								class="w-full flex flex-col p-8 bg-surface-50 dark:bg-surface-950/25 rounded border border-surface-200 dark:border-surface-700"
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
										<a href="https://www.pcisecuritystandards.org/" class="hover:text-primary-400 text-muted-color-emphasis"
											>PCI-DSS</a
										>
										compliant.<br />
										Your card details are processed securely by {{ selectedProvider }}.
									</div>
								</div>
								<div v-else class="flex flex-col">
									<div class="text-lg mb-12 font-bold text-center" @click="getFakeNumber">
										Enter your info for {{ selectedProvider }}
									</div>
									<CardForm @updated="updateCardDetails" />
								</div>
							</div>
						</div>
						<div class="flex pt-6 justify-between">
							<Button label="Back" severity="secondary" icon="pi pi-arrow-left" @click="activateCallback(1)" />
							<Button
								label="Next"
								icon="pi pi-arrow-right"
								iconPos="right"
								@click="processPayment"
								class="border-none"
								:disabled="!isStepTwoValid"
							/>
						</div>
					</StepPanel>
					<StepPanel :value="3">
						<div class="flex flex-col h-48">
							<div
								class="border-2 border-dashed border-surface-200 dark:border-surface-700 rounded bg-surface-50 dark:bg-surface-950 flex-auto flex justify-center items-center font-medium"
							>
								Enjoy your purchase!
							</div>
						</div>
					</StepPanel>
				</template>
				<template v-else>
					<StepPanel :value="2">
						<div class="flex flex-col h-48">
							<div
								class="border-2 border-dashed border-surface-200 dark:border-surface-700 rounded bg-surface-50 dark:bg-surface-950 flex-auto flex justify-center items-center font-medium text-center"
							>
								Thank you for your purchase!<br />
								<!-- Message is payment is required. -->
								We will get in touch with you shortly via email with the payment instructions.<br />
								<!-- Message if photos need to be processed. -->
								We will notify you once your photos are ready to be downloaded.
							</div>
							<div class="flex pt-6 ltr:justify-end rtl:justify-start">
								<Button label="To the gallery" icon="pi pi-arrow-right" @click="next" class="border-none" />
							</div>
						</div>
					</StepPanel>
				</template>
			</StepPanels>
		</Stepper>
	</Panel>
</template>
<script setup lang="ts">
import OpenLeftMenu from "@/components/headers/OpenLeftMenu.vue";
import Panel from "primevue/panel";
import Stepper from "primevue/stepper";
import StepList from "primevue/steplist";
import Step from "primevue/step";
import StepPanels from "primevue/steppanels";
import StepPanel from "primevue/steppanel";
import Button from "primevue/button";
import Toolbar from "primevue/toolbar";
import { storeToRefs } from "pinia";
import { useOrderManagementStore } from "@/stores/OrderManagement";
import { useRouter } from "vue-router";
import { onMounted, ref, watch } from "vue";
import { useUserStore } from "@/stores/UserState";
import { useLycheeStateStore } from "@/stores/LycheeState";
import { useStepOne } from "@/composables/checkout/useStepOne";
import Select from "primevue/select";
import { useStepTwo } from "@/composables/checkout/useStepTwo";
import CardForm from "@/components/forms/card/CardForm.vue";
import OrderSummary from "@/components/webshop/OrderSummary.vue";
import InfoSection from "@/components/webshop/InfoSection.vue";
import { useToast } from "primevue/usetoast";
import { type CheckoutSteps } from "@/config/constants";
import { $dt } from "@primeuix/themes";
import { useStepOffline } from "@/composables/checkout/useStepOffline";

const props = defineProps<{
	step?: CheckoutSteps;
}>();

const lycheeStateStore = useLycheeStateStore();
const userStore = useUserStore();
const orderStore = useOrderManagementStore();
const { order } = storeToRefs(orderStore);
const router = useRouter();
const toast = useToast();
// eslint-disable-next-line @typescript-eslint/no-explicit-any
const mollie = ref<any | undefined>(undefined);
// eslint-disable-next-line @typescript-eslint/no-explicit-any
const mollieComponent = ref<any | undefined>(undefined);

// Check in the DOM if body has dark mode class
function isDarkMode(): boolean {
	return document.body.classList.contains("dark");
}

function stepToNumber(step: CheckoutSteps | undefined): number {
	switch (step) {
		case "info":
			return 1;
		case "payment":
			return 2;
		case "confirm":
			return 3;
		case "completed":
			return 2;
		default:
			return 1;
	}
}

const step = ref<number>(stepToNumber(props.step));

const { email, options, loadCheckoutOptions, isStepOneValid } = useStepOne(userStore);

const { canProcessPayment, createSession, selectedProvider, processPayment, updateCardDetails, isStepTwoValid, getFakeNumber } = useStepTwo(
	email,
	orderStore,
	step,
	toast,
	mollie,
);

const { markAsOffline } = useStepOffline(email, step, orderStore);

function next() {
	if (options.value?.is_offline === true) {
		markAsOffline();
	} else {
		step.value = 2;
	}
}

async function waitForElement(id: string): Promise<HTMLElement> {
	return new Promise((resolve) => {
		const interval = setInterval(() => {
			const element = document.getElementById(id);
			if (element) {
				clearInterval(interval);
				resolve(element);
			}
		}, 100);
	});
}

async function mountMollie() {
	if (options.value?.mollie_profile_id === undefined || options.value?.mollie_profile_id === null || options.value?.mollie_profile_id === "") {
		toast.add({ severity: "error", summary: "Error", detail: "Mollie profile ID is not configured.", life: 3000 });
		return;
	}

	await waitForElement("checkout");

	// @ts-expect-error - Mollie is loaded from CDN
	mollie.value = Mollie(options.value.mollie_profile_id, { testmode: options.value.is_test_mode });

	console.log($dt("formField.color"));
	const style = isDarkMode() ? "dark" : "light";
	const optionsStyle = {
		styles: {
			base: {
				// @ts-expect-error - dynamic access
				backgroundColor: $dt("content.background").value[style].value,
				// @ts-expect-error - dynamic access
				color: $dt("text.color").value[style].value,
				fontSize: "16px",
				"::placeholder": {
					color: "transparent",
				},
			},
			valid: {
				color: "#090",
			},
		},
	};
	console.log("Mounting Mollie component with options:", optionsStyle);
	mollieComponent.value = mollie.value.createComponent("card", optionsStyle);
	mollieComponent.value.mount("#checkout");
}

watch(
	() => selectedProvider.value,
	async (new_val) => {
		if (new_val === "Mollie") {
			mountMollie();
		}
	},
);

onMounted(async () => {
	await lycheeStateStore.load();
	userStore.load().then(() => {
		if (userStore.user && userStore.user.email) {
			email.value = userStore.user.email;
		}
	});
	orderStore.load().then(() => {
		if (order.value === undefined || order.value?.items === null || order.value.items.length === 0) {
			// Redirect to basket if no items
			router.push({ name: "gallery" });
		}
	});
	loadCheckoutOptions();
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
