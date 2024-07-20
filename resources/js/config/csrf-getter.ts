const CSRF = {
	get() {
		// If the developer didn't issue an XSRF token, we will find it ourselves.
		let xcsrfToken = CSRF.xsrfToken();
		let csrfToken = CSRF.firstInputWithCsrfToken();

		// Check if it is a CSRF or XSRF token
		if (xcsrfToken !== null) {
			return xcsrfToken;
		} else if (csrfToken !== null) {
			return csrfToken;
		} else {
			// We didn't find it, and since is required, we will bail out.
			throw new TypeError(
				'Ensure a CSRF/XSRF token is manually set, or provided in a cookie "XSRF-TOKEN" or or there is meta tag named "csrf-token".',
			);
		}
	},

	/**
	 * Returns the CSRF token if it exists as a form input tag.
	 * @throws TypeError
	 */
	firstInputWithCsrfToken(): string | null {
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
	},

	/**
	 * Returns the value of the XSRF token if it exists in a cookie.
	 *
	 * Inspired by https://developer.mozilla.org/en-US/docs/Web/API/Document/cookie#example_2_get_a_sample_cookie_named_test2
	 */
	xsrfToken(): string | null {
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
		return cookie !== undefined ? cookie.split("=")[1].trim().replace(/%3D/g, "") : null;
	},
};

export default CSRF;
