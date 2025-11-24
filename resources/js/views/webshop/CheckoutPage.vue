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
		<Stepper :value="steps" linear class="basis-200" v-if="options !== undefined">
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
					<StepPanel :value="2">
						<div div class="grid grid-cols-2 gap-4 mb-4">
							<OrderSummary />
							<PaymentInProgress v-if="order?.status === 'processing'" />
							<PaymentForm v-else />
						</div>
						<div class="flex pt-6 justify-between">
							<Button label="Back" severity="secondary" icon="pi pi-arrow-left" @click="goToInfo" />
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
								class="border-2 border-dashed border-surface-200 dark:border-surface-700 rounded bg-surface-50 dark:bg-surface-950 flex-col gap-4 p-4 flex justify-center items-center font-medium"
							>
								<h2 class="font-bold text-xl">Thank you for your purchase!</h2>
								<div class="text-muted-color">
									<p>
										Your order number is: <strong class="text-muted-color-emphasis">{{ order?.id }}</strong>
									</p>
									<p>
										Your transaction id is: <strong class="text-muted-color-emphasis">{{ order?.transaction_id }}</strong>
									</p>
								</div>
								<p class="text-muted-color">
									<i class="pi pi-exclamation-triangle ltr:mr-2 rtl:mr-2 text-warning-600" />
									Please <strong class="text-muted-color-emphasis">note your transaction id and</strong> your
									<strong class="text-muted-color-emphasis">order number</strong> as you will need them to access your content.
								</p>
								<p class="text-muted-color">Enjoy your purchase!</p>
								<Button
									v-if="order?.status === 'closed'"
									text
									label="To my downloads"
									icon="pi pi-link"
									@click="openOrderPage"
									class="border-none"
								/>
							</div>
						</div>
						<div class="flex pt-6 ltr:justify-end rtl:justify-start">
							<Button label="To the gallery" icon="pi pi-arrow-right" @click="backToGallery" class="border-none" />
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
								<Button label="To the gallery" icon="pi pi-arrow-right" @click="backToGallery" class="border-none" />
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
import { onMounted, watch } from "vue";
import { useUserStore } from "@/stores/UserState";
import { useLycheeStateStore } from "@/stores/LycheeState";
import { useStepOne } from "@/composables/checkout/useStepOne";
import { useStepTwo } from "@/composables/checkout/useStepTwo";
import OrderSummary from "@/components/webshop/OrderSummary.vue";
import InfoSection from "@/components/webshop/InfoSection.vue";
import { useToast } from "primevue/usetoast";
import { type CheckoutSteps } from "@/config/constants";
import { useStepOffline } from "@/composables/checkout/useStepOffline";
import { useSteps } from "@/composables/checkout/useSteps";
import PaymentForm from "@/components/webshop/PaymentForm.vue";
import { useMollie } from "@/composables/checkout/useMollie";
import PaymentInProgress from "@/components/webshop/PaymentInProgress.vue";
import WebshopService from "@/services/webshop-service";

const props = defineProps<{
	step?: CheckoutSteps;
}>();

const lycheeStateStore = useLycheeStateStore();
const userStore = useUserStore();
const orderStore = useOrderManagementStore();
const { order } = storeToRefs(orderStore);
const router = useRouter();
const toast = useToast();

const { email, options, loadCheckoutOptions, loadEmailForUser, isStepOneValid } = useStepOne(userStore, orderStore);
const { stepToNumber, steps } = useSteps(options);
const { mollie } = useMollie(options, toast);
const { processPayment, isStepTwoValid } = useStepTwo(email, orderStore, steps, toast, mollie);

const { markAsOffline } = useStepOffline(email, router, orderStore);

function next() {
	if (options.value?.is_offline === true) {
		markAsOffline();
	} else {
		router.push({ name: "checkout", params: { step: "payment" } });
	}
}

async function backToGallery() {
	// We need to reset the order store to clear the previous order
	// Clear the cookie.
	await WebshopService.Order.forget();
	orderStore.reset();
	router.push({ name: "gallery" });
}

function openOrderPage() {
	router.push({ name: "order", params: { orderId: order.value?.id, transactionId: order.value?.transaction_id } });
}

function goToInfo() {
	router.push({ name: "checkout", params: { step: "info" } });
}

onMounted(async () => {
	await lycheeStateStore.load();
	await loadCheckoutOptions();

	if (props.step === undefined) {
		router.push({ name: "checkout", params: { step: "info" } });
	} else {
		steps.value = stepToNumber(props.step);
	}

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

		// Handle order status
		if (order.value?.status === "processing") {
			// Switch to step 2 if payment is in progress
			steps.value = 2;
			router.push({ name: "checkout", params: { step: "payment" } });
		}

		if (order.value?.status === "completed") {
			// Switch to completed page if order is completed
			router.push({ name: "checkout", params: { step: "completed" } });
		}

		if (order.value?.status === "offline") {
			// Switch to cancelled page if order is cancelled
			router.push({ name: "checkout", params: { step: "completed" } });
		}

		loadEmailForUser();
	});
});

watch(
	() => props.step,
	(newStep) => {
		steps.value = stepToNumber(newStep);
		console.log("Step changed to", newStep, "->", steps.value);
	},
);
</script>
