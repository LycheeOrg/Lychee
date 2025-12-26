import WebshopService, { CardDetails } from "@/services/webshop-service";
import { AxiosError, AxiosResponse } from "axios";
import { trans } from "laravel-vue-i18n";
import { ToastServiceMethods } from "primevue/toastservice";
import { ref } from "vue";

const cardDetails = ref<CardDetails>({
	number: "",
	expiryMonth: "",
	expiryYear: "",
	cvv: "",
});

export function useDummy(toast: ToastServiceMethods) {
	function getFakeNumber() {
		navigator.clipboard
			.writeText("4111111111111152")
			.then(() => toast.add({ severity: "info", summary: trans("webshop.useStepTwo.fakeCardClipboard"), life: 3000 }));

		return;
	}

	function isCardNumberNotEmpty(): boolean {
		return (
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
			if (i % 2 === shouldDoubleEven) {
				intVal *= 2;
				if (intVal > 9) {
					intVal = 1 + (intVal % 10);
				}
			}
			sum += intVal;
		}
		return sum % 10 === 0;
	}

	function processDummyPayment(
		handleSuccess: (response: AxiosResponse<App.Http.Resources.Shop.CheckoutResource>) => void,
		handleError: (error: AxiosError) => void,
	) {
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

	return {
		cardDetails,
		getFakeNumber,
		isCardNumberValid,
		isCardNumberNotEmpty,
		processDummyPayment,
	};
}
