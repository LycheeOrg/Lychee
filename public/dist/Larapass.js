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

class Larapass
{
    /**
     * Headers to use in ALL requests done.
     *
     * @type {{Accept: string, "X-Requested-With": string, "Content-Type": string}}
     */
    headers = {
        'Content-Type':     'application/json',
        'Accept':           'application/json',
        'X-Requested-With': 'XMLHttpRequest'
    };

    /**
     * Routes for WebAuthn assertion (login) and attestation (register).
     *
     * @type {{registerOptions: string, loginOptions: string, login: string, register: string}}
     */
    routes = {
        loginOptions:    'webauthn/login/options',
        login:           'webauthn/login',
        registerOptions: 'webauthn/register/options',
        register:        'webauthn/register',
    }

    /**
     * Create a new Larapass instance.
     *
     * @param routes {{registerOptions: string, loginOptions: string, login: string, register: string}}
     * @param headers {{string}}
     */
    constructor(routes = {}, headers = {})
    {
        this.routes = {...this.routes, ...routes};

        this.headers = {
            ...this.headers,
            ...headers
        }

        // If the developer didn't issue an XSRF token, we will find it ourselves.
        if (headers['X-XSRF-TOKEN'] === undefined) {
            this.headers['X-XSRF-TOKEN'] = Larapass.#getXsrfToken()
        }
    }

    /**
     * Returns the XSRF token if it exists.
     *
     * @returns string|undefined
     * @throws TypeError
     */
    static #getXsrfToken()
    {
        let tokenContainer;

        // First, let's get the token if it exists as a cookie, since most apps use it by default.
        tokenContainer = document.cookie.split('; ').find(row => row.startsWith('XSRF-TOKEN'))
        if (tokenContainer !== undefined) {
            return decodeURIComponent(tokenContainer.split('=')[1]);
        }

        // If it doesn't exists, we will try to get it from the head meta tags as last resort.
        tokenContainer = document.getElementsByName('csrf-token')[0];
        if (tokenContainer !== undefined) {
            return tokenContainer.content;
        }

        throw new TypeError('There is no cookie with "X-XSRF-TOKEN" or meta tag with "csrf-token".')
    }

    /**
     * Returns a fetch promise to resolve later.
     *
     * @param data {{string}}
     * @param route {string}
     * @param headers {{string}}
     * @returns {Promise<Response>}
     */
    #fetch(data, route, headers = {})
    {
        return fetch(route, {
            method: 'POST',
            credentials: 'same-origin',
            redirect: 'error',
            headers: {...this.headers, ...headers},
            body: JSON.stringify(data)
        })
    }

    /**
     * Decodes a BASE64 URL string into a normal string.
     *
     * @param input {string}
     * @returns {string|Iterable}
     */
    static #base64UrlDecode(input)
    {
        input = input.replace(/-/g, '+').replace(/_/g, '/');

        const pad = input.length % 4;
        if (pad) {
            if (pad === 1) {
                throw new Error('InvalidLengthError: Input base64url string is the wrong length to determine padding');
            }
            input += new Array(5-pad).join('=');
        }

        return window.atob(input);
    }

    /**
     * Transform an string into Uint8Array instance.
     *
     * @param input {string}
     * @param atob {boolean}
     * @returns {Uint8Array}
     */
    static #uint8Array(input, atob = false)
    {
        return Uint8Array.from(
            atob ? window.atob(input) : Larapass.#base64UrlDecode(input),
            c => c.charCodeAt(0)
        )
    }

    /**
     * Encodes an array of bytes to a BASE64 URL string
     *
     * @param arrayBuffer {ArrayBuffer|Uint8Array}
     * @returns {string}
     */
    static #arrayToBase64String(arrayBuffer)
    {
        return btoa(String.fromCharCode(...new Uint8Array(arrayBuffer)));
    }

    /**
     * Parses the Public Key Options received from the Server for the browser.
     *
     * @param publicKey {Object}
     * @returns {Object}
     */
    #parseIncomingServerOptions(publicKey)
    {
        publicKey.challenge = Larapass.#uint8Array(publicKey.challenge)

        if (publicKey.user !== undefined) {
            publicKey.user = {
                ...publicKey.user,
                id: Larapass.#uint8Array(publicKey.user.id, true),
            };
        }

        ['excludeCredentials', 'allowCredentials']
            .filter(key => publicKey[key] !== undefined)
            .forEach(key => {
                publicKey[key] = publicKey[key].map(
                    data => {
                        return {
                            ...data,
                            id: Larapass.#uint8Array(data.id),
                        };
                    }
                )
            })

        return publicKey;
    }

    /**
     * Parses the outgoing credentials from the browser to the server.
     *
     * @param credentials {Credential|PublicKeyCredential}
     * @return {{response: {string}, rawId: string, id: string, type: string}}
     */
    #parseOutgoingCredentials(credentials)
    {
        let parseCredentials = {
            id: credentials.id,
            type: credentials.type,
            rawId: Larapass.#arrayToBase64String(credentials.rawId),
            response: {},
        };

        ['clientDataJSON', 'attestationObject', 'authenticatorData', 'signature', 'userHandle']
            .filter(key => credentials.response[key] !== undefined)
            .forEach(key => {
                parseCredentials.response[key] = Larapass.#arrayToBase64String(credentials.response[key]);
            })

        return parseCredentials;
    }

    /**
     * Checks if the browser supports WebAuthn.
     *
     * @returns {boolean}
     */
    static supportsWebAuthn()
    {
        return typeof(PublicKeyCredential) != 'undefined';
    }

    /**
     * Handles the response from the Server.
     *
     * Throws the entire response if is not OK (HTTP 2XX).
     *
     * @param response {Response}
     * @returns Promise<JSON|ReadableStream>
     * @throws Response
     */
    static #handleResponse(response)
    {
        if (! response.ok) {
            throw response;
        }

        // Here we will do a small trick. Since most of the responses from the server
        // are JSON, we will automatically parse the JSON body from the response. If
        // it's not JSON, we will push the body verbatim and let the dev handle it.
        return new Promise(resolve => {
            response.json()
                .then(json => resolve(json))
                .catch(() => resolve(response.body))
        })
    }

    /**
     * Log in an user with his credentials.
     *
     * If no credentials are given, Larapass can return a blank assertion for typeless login.
     *
     * @param data {{string}}
     * @param headers {{string}}
     * @returns Promise<JSON|ReadableStream>
     */
    async login(data = {}, headers = {})
    {
        const optionsResponse = await this.#fetch(data, this.routes.loginOptions)
        const json = await optionsResponse.json()
        const publicKey = this.#parseIncomingServerOptions(json)
        const credentials = await navigator.credentials.get({publicKey})
        const publicKeyCredential = this.#parseOutgoingCredentials(credentials)

        return await this.#fetch(publicKeyCredential, this.routes.login, headers)
            .then(Larapass.#handleResponse);
    }

    /**
     * Register the user credentials from the browser/device.
     *
     * You can add data if you are planning to register an user with WebAuthn from scratch.
     *
     * @param data {{string}}
     * @param headers {{string}}
     * @returns Promise<JSON|ReadableStream>
     */
    async register(data = {}, headers = {})
    {
        const optionsResponse = await this.#fetch(data, this.routes.registerOptions)
        const json = await optionsResponse.json()
        const publicKey = this.#parseIncomingServerOptions(json)
        const credentials = await navigator.credentials.create({publicKey})
        const publicKeyCredential = this.#parseOutgoingCredentials(credentials)

        return await this.#fetch(publicKeyCredential, this.routes.register, headers)
            .then(Larapass.#handleResponse);
    }
}