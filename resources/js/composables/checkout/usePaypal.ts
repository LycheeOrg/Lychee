import { ToastServiceMethods } from "primevue/toastservice";
import { Ref, ref } from "vue";
import { trans } from "laravel-vue-i18n";
import { loadScript, type PayPalNamespace } from "@paypal/paypal-js";
import WebshopService from "@/services/webshop-service";

const paypal = ref<PayPalNamespace | null>(null);

async function waitForElement(id: string, timeoutMs: number = 5000): Promise<HTMLElement> {
	return new Promise((resolve, reject) => {
		const startTime = Date.now();
		const interval = setInterval(() => {
			const element = document.getElementById(id);
			if (element) {
				clearInterval(interval);
				resolve(element);
			} else if (Date.now() - startTime > timeoutMs) {
				clearInterval(interval);
				reject(new Error(`Element with id "${id}" not found within ${timeoutMs}ms`));
			}
		}, 100);
	});
}

export function usePaypal(toast: ToastServiceMethods) {
	async function mountPaypal(options: Ref<undefined | App.Http.Resources.Shop.CheckoutOptionResource>) {
		if (options.value?.paypal_client_id === undefined || options.value?.paypal_client_id === null || options.value?.paypal_client_id === "") {
			toast.add({
				severity: "error",
				summary: trans("webshop.usePaypal.error"),
				detail: trans("webshop.usePaypal.client_id_missing"),
				life: 3000,
			});
			return;
		}

		await waitForElement("paypal-button-container");

		try {
			paypal.value = await loadScript({ clientId: options.value.paypal_client_id, currency: options.value.currency });
		} catch (error) {
			toast.add({
				severity: "error",
				summary: trans("webshop.usePaypal.sdkLoadError"),
				detail: trans("webshop.usePaypal.sdkLoadErrorDetail"),
				life: 3000,
			});
			console.error("failed to load the PayPal JS SDK script", error);
		}

		if (paypal.value !== null && paypal.value.Buttons !== undefined) {
			try {
				await paypal.value
					.Buttons({
						createOrder: createOrder,
						onApprove: onApprove,
						onCancel: onCancel,
						onError: (err: unknown) => {
							console.error("PayPal Buttons onError", err);
							toast.add({
								severity: "error",
								summary: trans("webshop.usePaypal.paymentError"),
								detail: trans("webshop.usePaypal.paymentErrorDetail"),
								life: 3000,
							});
						},
					})
					.render("#paypal-button-container");
			} catch (error) {
				console.error("failed to render the PayPal Buttons", error);
			}
		}
	}

	async function createOrder(): Promise<string> {
		try {
			return await WebshopService.Checkout.processCheckout({ additional_data: {} })
				.then((response) => {
					console.log("Created session for PayPal", response.data);
					if (!response.data.order?.transaction_id) {
						throw new Error("No transaction ID received from server");
					}
					return response.data.order.transaction_id;
				})
				.catch((error) => {
					console.log("Error creating session for PayPal", error);
					throw new Error(`Could not create PayPal order: ${error.message}`);
				});
		} catch (error) {
			console.error(error);
			throw new Error("Could not render PayPal Buttons");
		}
	}

	// eslint-disable-next-line @typescript-eslint/no-explicit-any
	async function onApprove(data: any, _actions: any) {
		try {
			WebshopService.Checkout.completePayment(data.orderID, "PayPal")
				.then((response) => {
					console.log("Completed PayPal payment", response.data);
					if (response.data.is_success && response.data.complete_url) {
						window.location.href = response.data.complete_url;
					} else if (!response.data.is_success && response.data.redirect_url) {
						window.location.href = response.data.redirect_url;
					} else {
						throw new Error("Unexpected response from server");
					}
				})
				.catch((error) => {
					console.log("Error completing PayPal payment", error);
					throw new Error(`Could not complete PayPal payment: ${error.message}`);
				});
		} catch (error) {
			console.error(error);
		}
	}

	// eslint-disable-next-line @typescript-eslint/no-explicit-any
	async function onCancel(data: any) {
		WebshopService.Checkout.cancelPayment(data.orderID)
			.then((response) => {
				console.log(response.data);
				window.location.href = response.data.redirect_url ?? "/";
			})
			.catch((error) => {
				console.error("Error cancelling PayPal payment", error);
			});
	}

	return {
		paypal,
		mountPaypal,
	};
}
