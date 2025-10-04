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
				<Step :value="2">Payment</Step>
				<Step :value="3">Confirmation</Step>
			</StepList>
			<StepPanels>
				<StepPanel v-slot="{ activateCallback }" :value="1">
					<div class="grid grid-cols-2 gap-4 mb-4" v-if="order">
						<OrderSummary />
						<InfoSection />
					</div>
					<div class="flex pt-6 ltr:justify-end rtl:justify-start">
						<Button label="Next" icon="pi pi-arrow-right" @click="activateCallback(2)" class="border-none" :disabled="!isStepOneValid" />
					</div>
				</StepPanel>
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
							<div v-else class="flex flex-col">
								<div class="text-lg mb-12 font-bold text-center" @click="getFakeNumber">Enter your info for {{ selectedProvider }}</div>
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
				<StepPanel :value="'confirm'">
					<div class="flex flex-col h-48">
						<div
							class="border-2 border-dashed border-surface-200 dark:border-surface-700 rounded bg-surface-50 dark:bg-surface-950 flex-auto flex justify-center items-center font-medium"
						>
							Enjoy your purchase!
						</div>
					</div>
				</StepPanel>
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
import { onMounted, ref } from "vue";
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


const props = defineProps<{
	step?: CheckoutSteps
}>();

const lycheeStateStore = useLycheeStateStore();
const userStore = useUserStore();
const orderStore = useOrderManagementStore();
const { order } = storeToRefs(orderStore);
const router = useRouter();
const toast = useToast();

function stepToNumber(step: CheckoutSteps | undefined) : number {
	switch (step) {
		case 'info':
			return 1;
		case 'payment':
			return 2;
		case 'confirm':
			return 3;
		default:
			return 1;
	}
}

const step = ref<number>(stepToNumber(props.step));

const { email, options, loadCheckoutOptions, isStepOneValid } = useStepOne(userStore);

const { canProcessPayment, createSession, selectedProvider, processPayment, updateCardDetails, isStepTwoValid, getFakeNumber } = useStepTwo(email, orderStore, step, toast);


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
});
</script>
