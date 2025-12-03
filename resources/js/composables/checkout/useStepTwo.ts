import WebshopService, { CardDetails } from "@/services/webshop-service";
import { OrderManagementStateStore } from "@/stores/OrderManagement";
import { AxiosError, AxiosResponse } from "axios";
import { ToastServiceMethods } from "primevue/toastservice";
import { Ref, ref } from "vue";
import { trans } from "laravel-vue-i18n";

const isStepTwoValid = ref(false);
const canProcessPayment = ref(false);
const selectedProvider = ref<undefined | App.Enum.OmnipayProviderType>(undefined);
const cardDetails = ref<CardDetails>({
	number: "",
	expiryMonth: "",
	expiryYear: "",
	cvv: "",
});

export function useStepTwo(
	email: Ref<undefined | string>,
	orderManagement: OrderManagementStateStore,
	toast: ToastServiceMethods,
	// eslint-disable-next-line @typescript-eslint/no-explicit-any
	mollie: Ref<any | undefined>,
) {
	function setStepTwoValid() {
		isStepTwoValid.value = isCardValid() && canProcessPayment.value === true;
	}

	function updateCardDetails(details: CardDetails) {
		cardDetails.value = details;
		setStepTwoValid();
	}

	function getFakeNumber() {
		if (selectedProvider.value !== "Dummy") {
			return;
		}

		navigator.clipboard
			.writeText("4111111111111152")
			.then(() => toast.add({ severity: "info", summary: trans("webshop.useStepTwo.fakeCardClipboard"), life: 3000 }));

		return;
	}

	function isCardValid(): boolean {
		if (selectedProvider.value === "Mollie") {
			return true; // Mollie handles validation internally
		}

		return (
			isCardNumberValid() &&
			cardDetails.value.number.length > 0 &&
			cardDetails.value.expiryMonth !== "" &&
			cardDetails.value.expiryYear !== "" &&
			cardDetails.value.cvv !== ""
		);
	}

	function isCardNumberValid(): boolean {
		const number = cardDetails.value.number.replace(/\s+/g, "");
		const shouldDoubleEven = number.length % 2;
		let sum = 0;
		for (let i = 0; i < number.length; i++) {
			let intVal = parseInt(number.charAt(i));
			if (i % 2 !== shouldDoubleEven) {
				intVal *= 2;
				if (intVal > 9) {
					intVal = 1 + (intVal % 10);
				}
			}
			sum += intVal;
		}

		return sum % 10 === 0;
	}

	function createSession() {
		WebshopService.Checkout.createSession({
			email: email.value,
			provider: selectedProvider.value!,
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
			await processMolliePayment();
			return;
		}

		WebshopService.Checkout.processCheckout({
			additional_data: {
				card: {
					number: cardDetails.value.number.replace(/\s+/g, ""),
					expiryMonth: cardDetails.value.expiryMonth,
					expiryYear: cardDetails.value.expiryYear,
					cvv: cardDetails.value.cvv,
				},
			},
		})
			.then(handleSuccess)
			.catch(handleError);
	}

	async function processMolliePayment() {
		const { token, error } = await mollie.value.createToken();
		if (error) {
			// Something wrong happened while creating the token. Handle this situation gracefully.
			toast.add({
				severity: "error",
				summary: trans("toasts.error"),
				detail: trans("Something went wrong with Mollie."),
				life: 5000,
			});
			return;
		}

		WebshopService.Checkout.processCheckout({
			additional_data: {
				cardToken: token,
			},
		})
			.then(handleSuccess)
			.catch(handleError);
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
