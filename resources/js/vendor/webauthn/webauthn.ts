/**
 * MIT License
 *
 * Copyright (c) Italo Israel Baeza Cabrera
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

type ResponseWithBody = Response & { body: string };

export default class WebAuthn {
	/**
	 * Routes for WebAuthn assertion (login) and attestation (register).
	 */
	#routes: {
		registerOptions: string;
		register: string;
		loginOptions: string;
		login: string;
	} = {
		registerOptions: "webauthn/register/options",
		register: "webauthn/register",
		loginOptions: "webauthn/login/options",
		login: "webauthn/login",
	};

	/**
	 * Headers to use in ALL requests done.
	 */
	#headers: {
		Accept: string;
		"Content-Type": string;
		"X-Requested-With": string;
		"X-XSRF-TOKEN"?: string;
		"X-CSRF-TOKEN"?: string;
	} = {
		Accept: "application/json",
		"Content-Type": "application/json",
		"X-Requested-With": "XMLHttpRequest",
	};

	/**
	 * If set to true, the credentials option will be set to 'include' on all fetch calls,
	 * or else it will use the default 'same-origin'. Use this if the backend is not the
	 * same origin as the client or the XSRF protection will break without the session.
	 */
	#includeCredentials: boolean = false;

	/**
	 * Create a new WebAuthn instance.
	 */
	constructor(
		routes: {
			registerOptions?: string;
			register?: string;
			loginOptions?: string;
			login?: string;
		} = {},
		headers: {} = {},
		includeCredentials: boolean = false,
		xcsrfToken: string | null = null,
	) {
		Object.assign(this.#routes, routes);
		Object.assign(this.#headers, headers);

		this.#includeCredentials = includeCredentials;

		let xsrfToken;
		let csrfToken;

		if (xcsrfToken === null) {
			// If the developer didn't issue an XSRF token, we will find it ourselves.
			xsrfToken = WebAuthn.#XsrfToken;
			csrfToken = WebAuthn.#firstInputWithCsrfToken;
		} else {
			// Check if it is a CSRF or XSRF token
			if (xcsrfToken.length === 40) {
				csrfToken = xcsrfToken;
			} else if (xcsrfToken.length === 224) {
				xsrfToken = xcsrfToken;
			} else {
				throw new TypeError("CSRF token or XSRF token provided does not match requirements. Must be 40 or 224 characters.");
			}
		}

		if (xsrfToken !== null) {
			this.#headers["X-XSRF-TOKEN"] ??= xsrfToken;
		} else if (csrfToken !== null) {
			this.#headers["X-CSRF-TOKEN"] ??= csrfToken;
		} else {
			// We didn't find it, and since is required, we will bail out.
			throw new TypeError(
				'Ensure a CSRF/XSRF token is manually set, or provided in a cookie "XSRF-TOKEN" or or there is meta tag named "csrf-token".',
			);
		}
	}

	/**
	 * Returns the CSRF token if it exists as a form input tag.
	 * @throws TypeError
	 */
	static get #firstInputWithCsrfToken(): string | null {
		// First, try finding an CSRF Token in the head.
		let token: HTMLInputElement | HTMLMetaElement | undefined;
		token = Array.from(document.head.getElementsByTagName("meta")).find((element) => element.name === "csrf-token");

		if (token) {
			return token.content;
		}

		// Then, try to find a hidden input containing the CSRF token.
		token = Array.from(document.getElementsByTagName("input")).find((input) => input.name === "_token" && input.type === "hidden");

		if (token) {
			return token.value;
		}

		return null;
	}

	/**
	 * Returns the value of the XSRF token if it exists in a cookie.
	 *
	 * Inspired by https://developer.mozilla.org/en-US/docs/Web/API/Document/cookie#example_2_get_a_sample_cookie_named_test2
	 */
	static get #XsrfToken(): string | null {
		const cookie = document.cookie.split(";").find((row) => /^\s*(X-)?[XC]SRF-TOKEN\s*=/.test(row));
		// We must remove all '%3D' from the end of the string.
		// Background:
		// The actual binary value of the CSFR value is encoded in Base64.
		// If the length of original, binary value is not a multiple of 3 bytes,
		// the encoding gets padded with `=` on the right; i.e. there might be
		// zero, one or two `=` at the end of the encoded value.
		// If the value is sent from the server to the client as part of a cookie,
		// the `=` character is URL-encoded as `%3D`, because `=` is already used
		// to separate a cookie key from its value.
		// When we send back the value to the server as part of an AJAX request,
		// Laravel expects an unpadded value.
		// Hence, we must remove the `%3D`.
		return cookie ? cookie.split("=")[1].trim().replace(/%3D/g, "") : null;
	}

	/**
	 * Returns a fetch promise to resolve later.
	 */
	#fetch(data: object, route: string, headers: {} = {}): Promise<Response> {
		const url = new URL(route, window.location.origin).href;
		return fetch(url, {
			method: "POST",
			credentials: this.#includeCredentials ? "include" : "same-origin",
			redirect: "error",
			headers: { ...this.#headers, ...headers },
			body: JSON.stringify(data),
		});
	}

	/**
	 * Decodes a BASE64 URL string into a normal string.
	 */
	static #base64UrlDecode(input: string): string {
		input = input.replace(/-/g, "+").replace(/_/g, "/");

		const pad = input.length % 4;

		if (pad) {
			if (pad === 1) {
				throw new Error("InvalidLengthError: Input base64url string is the wrong length to determine padding");
			}

			input += new Array(5 - pad).join("=");
		}

		return atob(input);
	}

	/**
	 * Transform a string into Uint8Array instance.
	 */
	static #uint8Array(input: string, useAtob: boolean = false): Uint8Array {
		return Uint8Array.from(useAtob ? atob(input) : WebAuthn.#base64UrlDecode(input), (c) => c.charCodeAt(0));
	}

	/**
	 * Encodes an array of bytes to a BASE64 URL string
	 */
	static #arrayToBase64String(arrayBuffer: ArrayBuffer | Uint8Array): string {
		return btoa(String.fromCharCode(...new Uint8Array(arrayBuffer)));
	}

	/**
	 * Parses the Public Key Options received from the Server for the browser.
	 */
	#parseIncomingServerOptions(publicKey: any): object {
		console.debug(publicKey);

		publicKey.challenge = WebAuthn.#uint8Array(publicKey.challenge);

		if ("user" in publicKey) {
			publicKey.user = {
				...publicKey.user,
				id: WebAuthn.#uint8Array(publicKey.user.id),
			};
		}

		["excludeCredentials", "allowCredentials"]
			.filter((key) => key in publicKey)
			.forEach((key) => {
				// @ts-ignore
				publicKey[key] = publicKey[key].map((data) => {
					return { ...data, id: WebAuthn.#uint8Array(data.id) };
				});
			});

		return publicKey;
	}

	/**
	 * Parses the outgoing credentials from the browser to the server.
	 */
	#parseOutgoingCredentials(credentials: Credential | PublicKeyCredential): {
		response: {};
		rawId: string;
		id: string;
		type: string;
	} {
		let parseCredentials = {
			id: credentials.id,
			type: credentials.type,
			// @ts-ignore
			rawId: WebAuthn.#arrayToBase64String(credentials.rawId),
			response: {},
		};

		["clientDataJSON", "attestationObject", "authenticatorData", "signature", "userHandle"]
			// @ts-ignore
			.filter((key) => key in credentials.response)
			// @ts-ignore
			.forEach((key) => (parseCredentials.response[key] = WebAuthn.#arrayToBase64String(credentials.response[key])));

		return parseCredentials;
	}

	/**
	 * Handles the response from the Server.
	 *
	 * Throws the entire response if is not OK (HTTP 2XX).
	 * @throws Response
	 */
	static #handleResponse(response: Response): Promise<JSON | ReadableStream> {
		if (!response.ok) {
			throw response;
		}

		// Here we will do a small trick. Since most of the responses from the server
		// are JSON, we will automatically parse the JSON body from the response. If
		// it's not JSON, we will push the body verbatim and let the dev handle it.
		return new Promise((resolve) => {
			response
				.json()
				.then((json: JSON) => resolve(json))
				.catch(() => resolve((response as ResponseWithBody).body));
		});
	}

	/**
	 * Register the user credentials from the browser/device.
	 *
	 * You can add request input if you are planning to register a user with WebAuthn from scratch.
	 */
	async register(request: {} = {}, response: {} = {}): Promise<JSON | ReadableStream> {
		const optionsResponse = await this.#fetch(request, this.#routes.registerOptions);
		const json = await optionsResponse.json();
		const publicKey = this.#parseIncomingServerOptions(json);
		// @ts-ignore
		const credentials = await navigator.credentials.create({ publicKey });
		// @ts-ignore
		const publicKeyCredential = this.#parseOutgoingCredentials(credentials);

		Object.assign(publicKeyCredential, response);
		Object.assign(publicKeyCredential, request);

		return await this.#fetch(publicKeyCredential, this.#routes.register).then(WebAuthn.#handleResponse);
	}

	/**
	 * Log in a user with his credentials.
	 *
	 * If no credentials are given, the app may return a blank assertion for userless login.
	 */
	async login(request: {} = {}, response: {} = {}): Promise<JSON | ReadableStream> {
		const optionsResponse = await this.#fetch(request, this.#routes.loginOptions);
		const json = await optionsResponse.json();
		const publicKey = this.#parseIncomingServerOptions(json);
		// @ts-ignore
		const credentials = await navigator.credentials.get({ publicKey });
		// @ts-ignore
		const publicKeyCredential = this.#parseOutgoingCredentials(credentials);

		Object.assign(publicKeyCredential, response);

		return await this.#fetch(publicKeyCredential, this.#routes.login, response).then(WebAuthn.#handleResponse);
	}

	/**
	 * Checks if the browser supports WebAuthn.
	 */
	static supportsWebAuthn(): boolean {
		return typeof PublicKeyCredential != "undefined";
	}

	/**
	 * Checks if the browser doesn't support WebAuthn.
	 */
	static doesntSupportWebAuthn(): boolean {
		return !this.supportsWebAuthn();
	}
}
