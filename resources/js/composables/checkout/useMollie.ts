import { ToastServiceMethods } from "primevue/toastservice";
import { Ref, ref } from "vue";
import { $dt } from "@primeuix/themes";
import { trans } from "laravel-vue-i18n";

// eslint-disable-next-line @typescript-eslint/no-explicit-any
const mollie = ref<any | undefined>(undefined);
// eslint-disable-next-line @typescript-eslint/no-explicit-any
const mollieComponent = ref<any | undefined>(undefined);

// Check in the DOM if body has dark mode class
function isDarkMode(): boolean {
	return document.body.classList.contains("dark");
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

export function useMollie(options: Ref<undefined | App.Http.Resources.Shop.CheckoutOptionResource>, toast: ToastServiceMethods) {
	async function mountMollie() {
		if (options.value?.mollie_profile_id === undefined || options.value?.mollie_profile_id === null || options.value?.mollie_profile_id === "") {
			toast.add({
				severity: "error",
				summary: trans("webshop.useMollie.error"),
				detail: trans("webshop.useMollie.profileNotConfigured"),
				life: 3000,
			});
			return;
		}

		await waitForElement("checkout");

		// @ts-expect-error - Mollie is loaded from CDN
		mollie.value = Mollie(options.value.mollie_profile_id, { testmode: options.value.is_test_mode });
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
		mollieComponent.value = mollie.value.createComponent("card", optionsStyle);
		mollieComponent.value.mount("#checkout");
	}

	return {
		mollie,
		mountMollie,
		mollieComponent,
	};
}
