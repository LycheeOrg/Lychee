import WebAuthn from "@/vendor/webauthn/webauthn.js";
import type { Alpine, AlpineComponent } from "alpinejs";

interface LoginParam {
	username?: string;
	user_id?: number;
}

type LoginWebAuthn = AlpineComponent<{
	webAuthnOpen: boolean;
	success_msg: string;
	error_msg: string;
	username: string | null;
	userId: number | null;
	isWebAuthnUnavailable: () => boolean;
	login: () => void;
	handleKeyUp: (event: KeyboardEvent) => void;
}>;

export const loginWebAuthn = (Alpine: Alpine) =>
	Alpine.data(
		"loginWebAuthn",
		// @ts-expect-error
		(success_msg_val: string = "U2F_AUTHENTIFICATION_SUCCESS", error_msg_val: string = "ERROR_TEXT"): LoginWebAuthn => ({
			webAuthnOpen: false,
			success_msg: success_msg_val,
			error_msg: error_msg_val,
			username: null,
			userId: 1,

			isWebAuthnUnavailable(): boolean {
				return !window.isSecureContext && window.location.hostname !== "localhost" && window.location.hostname !== "127.0.0.1";
			},

			login(): void {
				// work around because this does not refer to alpine anymore when inside WebAuthn then context.
				const alpine = this;
				const params: LoginParam = {};
				if (this.username !== "" && this.username !== null) {
					params.username = this.username;
				} else if (this.userId !== null) {
					params.user_id = this.userId;
				}
				new WebAuthn({ login: "/api/WebAuthn::login", loginOptions: "/api/WebAuthn::login/options" }, {}, false)
					.login(params)
					.then(function () {
						alpine.$dispatch("notify", [{ type: "success", msg: alpine.success_msg }]);
						window.location.reload();
					})
					.catch(() => alpine.$dispatch("notify", [{ type: "error", msg: this.error_msg }]));
			},

			handleKeyUp(event: KeyboardEvent): void {
				const skipped = ["TEXTAREA", "INPUT", "SELECT"];

				if (document.activeElement !== null && skipped.includes(document.activeElement.nodeName)) {
					return;
				}

				if (event.key === "Escape") {
					this.webAuthnOpen = false;
					return;
				}

				if (event.key === "k") {
					this.webAuthnOpen = true;
					return;
				}

				if (event.key === "Enter" && this.webAuthnOpen === true) {
					this.login();
					return;
				}
			},
		}),
	);
