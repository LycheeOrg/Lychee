import WebAuthn from "../vendor/webauthn/webauthn.js";

export default { loginWebAuthn, registerWebAuthn };

export function loginWebAuthn(success_msg_val = "U2F_AUTHENTIFICATION_SUCCESS", error_msg_val = "ERROR_TEXT") {
	return {
		webAuthnOpen: false,
		success_msg: success_msg_val,
		error_msg: error_msg_val,
		username: null,
		userId: 1,

		isWebAuthnUnavailable() {
			return !window.isSecureContext && window.location.hostname !== "localhost" && window.location.hostname !== "127.0.0.1";
		},

		login() {
			// work around because this does not refer to alpine anymore when inside WebAuthn then context.
			let alpine = this;
			let params = {};
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
				.catch(() => this.$dispatch("notify", [{ type: "error", msg: this.error_msg }]));
		},
	};
}

export function registerWebAuthn(success_msg_val = "U2F_REGISTRATION_SUCCESS", error_msg_val = "ERROR_TEXT") {
	return {
		success_msg: success_msg_val,
		error_msg: error_msg_val,

		isWebAuthnUnavailable() {
			return !window.isSecureContext && window.location.hostname !== "localhost" && window.location.hostname !== "127.0.0.1";
		},

		register() {
			// work around because this does not refer to alpine anymore when inside WebAuthn then context.
			let alpine = this;
			new WebAuthn({ register: "/api/WebAuthn::register", registerOptions: "/api/WebAuthn::register/options" }, {}, false)
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
	};
}
