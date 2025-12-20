import WebshopService, { CardDetails } from "@/services/webshop-service";
import { OrderManagementStateStore } from "@/stores/OrderManagement";
import { AxiosError, AxiosResponse } from "axios";
import { ToastServiceMethods } from "primevue/toastservice";
import { Ref, ref } from "vue";
import { trans } from "laravel-vue-i18n";
import { useDummy } from "./useDummy";
import { useMollie } from "./useMollie";

const isStepTwoValid = ref(false);
const canProcessPayment = ref(false);
const selectedProvider = ref<undefined | App.Enum.OmnipayProviderType>(undefined);

export function useStepTwo(email: Ref<undefined | string>, orderManagement: OrderManagementStateStore, toast: ToastServiceMethods) {
	const { cardDetails, isCardNumberNotEmpty, isCardNumberValid, getFakeNumber, processDummyPayment } = useDummy(toast);
	const { processMolliePayment } = useMollie(toast);

	function setStepTwoValid() {
		isStepTwoValid.value = isCardValid() && canProcessPayment.value === true;
	}

	function updateCardDetails(details: CardDetails) {
		cardDetails.value = details;
		setStepTwoValid();
	}

	function isCardValid(): boolean {
		if (selectedProvider.value === "Mollie") {
			return true; // Mollie handles validation internally
		}

		if (selectedProvider.value === "PayPal") {
			return false; // Paypal handles validation internally
			// We want to disable the next button until PayPal process is complete
		}

		return isCardNumberValid() && isCardNumberNotEmpty();
	}

	function createSession() {
		if (selectedProvider.value === undefined || email.value === undefined) {
			return;
		}

		WebshopService.Checkout.createSession({
			email: email.value,
			provider: selectedProvider.value,
		})
			.then((response) => {
				orderManagement.order = response.data;
				canProcessPayment.value = response.data.can_process_payment;
				setStepTwoValid();
			})
			.catch((error) => {
				console.error("Error creating session:", error);
			});
	}

	async function processPayment() {
		if (selectedProvider.value === "Mollie") {
			await processMolliePayment(handleSuccess, handleError);
			return;
		}

		if (selectedProvider.value === "PayPal") {
			return; // PayPal payment is handled separately
		}

		processDummyPayment(handleSuccess, handleError);
	}

	function handleSuccess(response: AxiosResponse<App.Http.Resources.Shop.CheckoutResource>) {
		if (response.data.is_success) {
			toast.add({
				severity: "success",
				summary: trans("webshop.useStepTwo.success"),
				detail: trans("webshop.useStepTwo.paymentSuccess"),
				life: 3000,
			});
		}

		if (response.data.is_redirect && (response.data.redirect_url === null || response.data.redirect_url === "")) {
			toast.add({
				severity: "error",
				summary: trans("webshop.useStepTwo.error"),
				detail: trans("webshop.useStepTwo.redirectError"),
				life: 3000,
			});
			return;
		}

		if (response.data.is_redirect && response.data.redirect_url !== null && response.data.redirect_url !== "") {
			// Redirect the user to the provided URL
			window.location.href = response.data.redirect_url;
			return;
		}

		if (response.data.complete_url === null || response.data.complete_url === "") {
			toast.add({
				severity: "error",
				summary: trans("webshop.useStepTwo.error"),
				detail: trans("webshop.useStepTwo.finalizationError"),
				life: 3000,
			});
			return;
		}

		window.location.href = response.data.complete_url;
		return;
	}

	function handleError(error: AxiosError) {
		if (error.response?.status === 400) {
			toast.add({
				severity: "error",
				summary: trans("webshop.useStepTwo.badRequest"),
				detail: trans("webshop.useStepTwo.invalidInput"),
				life: 5000,
			});
		}
	}

	return {
		updateCardDetails,
		isStepTwoValid,
		cardDetails,
		createSession,
		selectedProvider,
		canProcessPayment,
		processPayment,
		getFakeNumber,
	};
}
