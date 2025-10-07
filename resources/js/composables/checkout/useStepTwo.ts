import WebshopService, { CardDetails } from "@/services/webshop-service";
import { OrderManagementStateStore } from "@/stores/OrderManagement";
import axios, { AxiosError, AxiosResponse } from "axios";
import { ToastServiceMethods } from "primevue/toastservice";
import { Ref, ref } from "vue";

export function useStepTwo(
	email: Ref<undefined | string>,
	orderManagement: OrderManagementStateStore,
	step: Ref<number>,
	toast: ToastServiceMethods,
) {
	const selectedProvider = ref<undefined | App.Enum.OmnipayProviderType>(undefined);
	const canProcessPayment = ref(false);
	const cardDetails = ref<CardDetails>({
		number: "",
		expiryMonth: "",
		expiryYear: "",
		cvv: "",
	});

	const isStepTwoValid = ref(false)

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
			.then(() => toast.add({ severity: "info", summary: "fake card number available in clip board", life: 3000 }));

		return;
	}

	function isCardValid(): boolean {
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
		let sum = 0;
		for (let i = 0; i < number.length; i++) {
			let intVal = parseInt(number.charAt(i));
			if (i % 2 === 0) {
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
				console.log("Created session:", response);
				orderManagement.order = response.data;
				canProcessPayment.value = response.data.can_process_payment;
			})
			.catch((error) => {
				console.error("Error creating session:", error);
			});
	}

	function processPayment() {
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

	function handleSuccess(response: AxiosResponse<App.Http.Resources.Shop.CheckoutResource>) {
		console.log("Payment processed:", response.data);

		if (response.data.is_success) {
			toast.add({ severity: "success", summary: "Success", detail: "Payment processed successfully.", life: 3000 });
		}

		if (response.data.is_redirect && (response.data.redirect_url === null || response.data.redirect_url === "")) {
			toast.add({ severity: "error", summary: "Error", detail: "Redirection requested but target is absent.", life: 3000 });
			return;
		}

		if (response.data.is_redirect && response.data.redirect_url !== null && response.data.redirect_url !== "") {
			// Redirect the user to the provided URL
			window.location.href = response.data.redirect_url;
			return;
		}

		if (response.data.redirect_url === null || response.data.redirect_url === "") {
			toast.add({ severity: "error", summary: "Error", detail: "Finalization requested but target is absent.", life: 3000 });
			return;
		}

		axios.get(response.data.redirect_url!).then((data: AxiosResponse<App.Http.Resources.Shop.CheckoutResource>) => {
			console.log("Finalization completed:", data);
			if (data.data.is_success && data.data.order) {
				toast.add({ severity: "success", summary: "Success", detail: "Order finalized successfully.", life: 3000 });
				orderManagement.order = data.data.order;
			} else {
				toast.add({ severity: "error", summary: "Error", detail: "Order finalization failed.", life: 5000 });
			}
		});
		// https://lychee.test/api/v2/Shop/Checkout/Finalize/Dummy/37b8f4bc-a3a6-4119-bca9-5865a167505c
		step.value = 3;
		return;
	}

	function handleError(error: AxiosError) {
		console.log(error);
		if (error.status === 400) {
			toast.add({ severity: "error", summary: "Bad Request", detail: "The request was invalid. Please check your input.", life: 5000 });
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
