import WebAuthn from "@/vendor/webauthn/webauthn.js";
import type { Alpine, AlpineComponent } from "alpinejs";

type RegisterWebAuthn = AlpineComponent<{
	success_msg: string;
	error_msg: string;
	isWebAuthnUnavailable: () => boolean;
	register: () => void;
}>;

export const registerWebAuthn = (Alpine: Alpine) =>
	Alpine.data(
		"registerWebAuthn",
		// @ts-expect-error
		(success_msg_val: string = "U2F_REGISTRATION_SUCCESS", error_msg_val: string = "ERROR_TEXT"): RegisterWebAuthn => ({
			success_msg: success_msg_val,
			error_msg: error_msg_val,

			isWebAuthnUnavailable() {
				return !window.isSecureContext && window.location.hostname !== "localhost" && window.location.hostname !== "127.0.0.1";
			},

			register() {
				// work around because this does not refer to alpine anymore when inside WebAuthn then context.
				let alpine = this;

				new WebAuthn(
					{
						register: "/api/WebAuthn::register",
						registerOptions: "/api/WebAuthn::register/options",
					},
					{},
					false,
				)
					.register()
					.then(function () {
						// First reload then display
						alpine.$dispatch("reload-component");
						alpine.$dispatch("notify", [{ type: "success", msg: alpine.success_msg }]);
					})
					.catch((error) => {
						console.log(error);
						alpine.$dispatch("notify", [{ type: "error", msg: this.error_msg }]);
					});
			},
		}),
	);
