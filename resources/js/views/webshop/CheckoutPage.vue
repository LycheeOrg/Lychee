<template>
	<Toolbar class="w-full border-0 h-14 rounded-none">
		<template #start>
			<OpenLeftMenu />
		</template>

		<template #center>
			{{ $t("webshop.checkout.checkout") }}
		</template>

		<template #end> </template>
	</Toolbar>
	<Panel :pt:header:class="'hidden'" class="border-0 md:max-w-3xl lg:max-w-5xl xl:max-w-7xl mt-9 mx-auto w-full">
		<Stepper :value="steps" linear class="basis-200" v-if="options !== undefined">
			<StepList>
				<Step :value="1">{{ $t("webshop.checkout.yourInfo") }}</Step>
				<template v-if="!options.is_offline">
					<Step :value="2">{{ $t("webshop.checkout.payment") }}</Step>
					<Step :value="3">{{ $t("webshop.checkout.confirmation") }}</Step>
				</template>
				<template v-else>
					<Step :value="2">{{ $t("webshop.checkout.confirmation") }}</Step>
				</template>
			</StepList>
			<StepPanels>
				<StepPanel :value="1">
					<div class="grid grid-cols-2 gap-4 mb-4" v-if="order">
						<OrderSummary />
						<InfoSection />
					</div>
					<div class="flex pt-6 ltr:justify-end rtl:justify-start">
						<Button
							v-if="ltr"
							:label="$t('webshop.checkout.next')"
							icon="pi pi-arrow-right"
							@click="next"
							class="border-none"
							:disabled="!isStepOneValid"
						/>
						<Button
							v-else
							:label="$t('webshop.checkout.next')"
							icon="pi pi-arrow-left"
							@click="next"
							class="border-none"
							:disabled="!isStepOneValid"
						/>
					</div>
				</StepPanel>
				<template v-if="!options.is_offline">
					<StepPanel :value="2">
						<div class="grid grid-cols-2 gap-4 mb-4">
							<OrderSummary />
							<PaymentInProgress v-if="order?.status === 'processing'" />
							<PaymentForm v-else />
						</div>
						<div class="flex pt-6 justify-between">
							<template v-if="ltr">
								<Button :label="$t('webshop.checkout.back')" severity="secondary" icon="pi pi-arrow-left" @click="goToInfo" />
								<Button
									:label="$t('webshop.checkout.next')"
									icon="pi pi-arrow-right"
									iconPos="right"
									@click="processPayment"
									class="border-none"
									:disabled="!isStepTwoValid"
								/>
							</template>
							<template v-else>
								<Button :label="$t('webshop.checkout.back')" severity="secondary" icon="pi pi-arrow-right" @click="goToInfo" />
								<Button
									:label="$t('webshop.checkout.next')"
									icon="pi pi-arrow-left"
									iconPos="right"
									@click="processPayment"
									class="border-none"
									:disabled="!isStepTwoValid"
								/>
							</template>
						</div>
					</StepPanel>
					<StepPanel :value="3">
						<div class="flex flex-col h-48">
							<ThankYou />
							<div class="flex pt-6 ltr:justify-end rtl:justify-start">
								<Button
									v-if="ltr"
									:label="$t('webshop.checkout.toTheGallery')"
									icon="pi pi-arrow-right"
									@click="backToGallery"
									class="border-none"
								/>
								<Button
									v-else
									:label="$t('webshop.checkout.toTheGallery')"
									icon="pi pi-arrow-right"
									@click="backToGallery"
									class="border-none"
								/>
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
								{{ $t("webshop.checkout.offlineThankYou") }}<br />
								<!-- Message is payment is required. -->
								{{ $t("webshop.checkout.offlinePaymentMessage") }}<br />
								<!-- Message if photos need to be processed. -->
								{{ $t("webshop.checkout.offlineProcessingMessage") }}
							</div>
							<div class="flex pt-6 ltr:justify-end rtl:justify-start">
								<Button
									v-if="ltr"
									:label="$t('webshop.checkout.toTheGallery')"
									icon="pi pi-arrow-right"
									@click="backToGallery"
									class="border-none"
								/>
								<Button
									v-else
									:label="$t('webshop.checkout.toTheGallery')"
									icon="pi pi-arrow-right"
									@click="backToGallery"
									class="border-none"
								/>
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
import { computed, onMounted, watch } from "vue";
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
import ThankYou from "@/components/webshop/ThankYou.vue";
import { useLtRorRtL } from "@/utils/Helpers";

const props = defineProps<{
	step?: CheckoutSteps;
}>();

const lycheeStateStore = useLycheeStateStore();
const userStore = useUserStore();
const orderStore = useOrderManagementStore();
const { order } = storeToRefs(orderStore);
const router = useRouter();
const toast = useToast();

const { isLTR } = useLtRorRtL();

const ltr = computed(() => isLTR());

const { email, options, loadCheckoutOptions, loadEmailForUser, isStepOneValid } = useStepOne(userStore, orderStore);
const { stepToNumber, steps } = useSteps(options);
const { mollie } = useMollie(options, toast);
const { processPayment, isStepTwoValid, canProcessPayment } = useStepTwo(email, orderStore, toast, mollie);

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

function goToInfo() {
	canProcessPayment.value = false;
	order.value!.provider = null;
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

		if (["completed", "closed", "offline"].includes(order.value?.status || "")) {
			router.push({ name: "checkout", params: { step: "completed" } });
		}

		loadEmailForUser();
	});
});

watch(
	() => props.step,
	(newStep) => {
		steps.value = stepToNumber(newStep);
	},
);
</script>
