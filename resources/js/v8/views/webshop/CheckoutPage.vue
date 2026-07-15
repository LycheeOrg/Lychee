<template>
	<UHeader :toggle="false">
		<template #left>
			<GoBack @go-back="backToGallery" />
		</template>
		{{ $t("webshop.checkout.checkout") }}
	</UHeader>
	<UCard class="border-0 md:max-w-3xl lg:max-w-5xl xl:max-w-7xl mt-9 mx-auto w-full" :ui="{ header: 'hidden' }">
		<UStepper v-if="options !== undefined" v-model="steps" :items="stepperItems" linear class="basis-200" />

		<template v-if="steps === 1">
			<div v-if="order" class="grid grid-cols-2 gap-4 mb-4">
				<OrderSummary />
				<InfoSection />
			</div>
			<div class="flex pt-6 ltr:justify-end rtl:justify-start">
				<UButton
					:label="$t('webshop.checkout.next')"
					:icon="ltr ? 'lucide:arrow-right' : 'lucide:arrow-left'"
					:disabled="!isStepOneValid"
					@click="next"
				/>
			</div>
		</template>
		<template v-else-if="steps === 2 && options?.is_offline !== true">
			<div class="grid grid-cols-2 gap-4 mb-4">
				<OrderSummary />
				<PaymentInProgress v-if="order?.status === 'processing'" />
				<PaymentForm v-else />
			</div>
			<div class="flex pt-6 justify-between">
				<template v-if="ltr">
					<UButton :label="$t('webshop.checkout.back')" color="neutral" variant="soft" icon="lucide:arrow-left" @click="goToInfo" />
					<UButton
						:label="$t('webshop.checkout.next')"
						icon="lucide:arrow-right"
						trailing
						:disabled="!isStepTwoValid"
						@click="processPayment"
					/>
				</template>
				<template v-else>
					<UButton :label="$t('webshop.checkout.back')" color="neutral" variant="soft" icon="lucide:arrow-right" @click="goToInfo" />
					<UButton
						:label="$t('webshop.checkout.next')"
						icon="lucide:arrow-left"
						trailing
						:disabled="!isStepTwoValid"
						@click="processPayment"
					/>
				</template>
			</div>
		</template>
		<template v-else-if="steps === 3 && options?.is_offline !== true">
			<div class="flex flex-col h-48">
				<ThankYou v-if="['completed', 'closed'].includes(order?.status ?? '')" />
				<CancelledFailed v-else />
				<div class="flex pt-6 ltr:justify-end rtl:justify-start">
					<UButton :label="$t('webshop.checkout.toTheGallery')" icon="lucide:arrow-right" @click="backToGallery" />
				</div>
			</div>
		</template>
		<template v-else-if="steps === 2 && options?.is_offline === true">
			<div class="flex flex-col h-48">
				<div
					class="border-2 border-dashed border-default rounded bg-elevated/50 flex-auto flex justify-center items-center font-medium text-center"
				>
					{{ $t("webshop.checkout.offlineThankYou") }}<br />
					<!-- Message is payment is required. -->
					{{ $t("webshop.checkout.offlinePaymentMessage") }}<br />
					<!-- Message if photos need to be processed. -->
					{{ $t("webshop.checkout.offlineProcessingMessage") }}
				</div>
				<div class="flex pt-6 ltr:justify-end rtl:justify-start">
					<UButton :label="$t('webshop.checkout.toTheGallery')" icon="lucide:arrow-right" @click="backToGallery" />
				</div>
			</div>
		</template>
	</UCard>
</template>
<script setup lang="ts">
import { storeToRefs } from "pinia";
import { useOrderManagementStore } from "@/stores/OrderManagement";
import { useRouter } from "vue-router";
import { computed, onMounted, watch } from "vue";
import { useUserStore } from "@/stores/UserState";
import { useLycheeStateStore } from "@/stores/LycheeState";
import { useStepOne } from "@/composables/checkout/useStepOne";
import { useStepTwo } from "@/composables/checkout/useStepTwo";
import OrderSummary from "@/v8/components/webshop/OrderSummary.vue";
import InfoSection from "@/v8/components/webshop/InfoSection.vue";
import { useAppToast } from "@/v8/composables/useAppToast";
import { type CheckoutSteps } from "@/config/constants";
import { useStepOffline } from "@/composables/checkout/useStepOffline";
import { useSteps } from "@/composables/checkout/useSteps";
import PaymentForm from "@/v8/components/webshop/PaymentForm.vue";
import PaymentInProgress from "@/v8/components/webshop/PaymentInProgress.vue";
import WebshopService from "@/services/webshop-service";
import ThankYou from "@/v8/components/webshop/ThankYou.vue";
import { useLtRorRtL } from "@/utils/Helpers";
import GoBack from "@/v8/components/headers/GoBack.vue";
import CancelledFailed from "@/v8/components/webshop/CancelledFailed.vue";
import { trans } from "laravel-vue-i18n";
import type { StepperItem } from "@nuxt/ui";

const props = defineProps<{
	step?: CheckoutSteps;
}>();

const lycheeStateStore = useLycheeStateStore();
const userStore = useUserStore();
const orderStore = useOrderManagementStore();
const { order } = storeToRefs(orderStore);
const router = useRouter();
const toast = useAppToast();

const { isLTR } = useLtRorRtL();

const ltr = computed(() => isLTR());

const {
	email,
	options,
	loadCheckoutOptions,
	loadEmailForUser,
	isStepOneValid,
	shippingStreetName,
	shippingStreetNumber,
	shippingAdditionalInfo,
	shippingCity,
	shippingPostCode,
	shippingCountry,
} = useStepOne(userStore, orderStore);
const { stepToNumber, steps } = useSteps(options);
const { processPayment, isStepTwoValid, canProcessPayment } = useStepTwo(email, orderStore, toast, {
	shippingStreetName,
	shippingStreetNumber,
	shippingAdditionalInfo,
	shippingCity,
	shippingPostCode,
	shippingCountry,
});

const { markAsOffline } = useStepOffline(email, router, orderStore);

const stepperItems = computed<StepperItem[]>(() => {
	if (options.value?.is_offline === true) {
		return [
			{ value: 1, title: trans("webshop.checkout.yourInfo") },
			{ value: 2, title: trans("webshop.checkout.confirmation") },
		];
	}
	return [
		{ value: 1, title: trans("webshop.checkout.yourInfo") },
		{ value: 2, title: trans("webshop.checkout.payment") },
		{ value: 3, title: trans("webshop.checkout.confirmation") },
	];
});

function next() {
	if (options.value?.is_offline === true) {
		markAsOffline();
	} else {
		router.push({ name: "checkout", params: { step: "payment" } });
	}
}

async function backToGallery() {
	if (["completed", "closed", "offline"].includes(order.value?.status || "")) {
		// We need to reset the order store to clear the previous order
		// Clear the cookie.
		await WebshopService.Order.forget();
		orderStore.reset();
	}
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
