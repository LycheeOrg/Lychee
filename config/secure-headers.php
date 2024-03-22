<?php

use function Safe\parse_url;

return [
	/*
	 * Server
	 *
	 * Reference: https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Server
	 *
	 * Note: when server is empty string, it will not add to response header
	 */

	'server' => '',

	/*
	 * X-Content-Type-Options
	 *
	 * Reference: https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/X-Content-Type-Options
	 *
	 * Available Value: 'nosniff'
	 */

	'x-content-type-options' => 'nosniff',

	/*
	 * X-Download-Options
	 *
	 * Reference: https://msdn.microsoft.com/en-us/library/jj542450(v=vs.85).aspx
	 *
	 * Available Value: 'noopen'
	 */

	'x-download-options' => 'noopen',

	/*
	 * X-Frame-Options
	 *
	 * Reference: https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/X-Frame-Options
	 *
	 * Available Value: 'deny', 'sameorigin'
	 */

	'x-frame-options' => 'deny',    // deny because we don't use frame.

	/*
	 * X-Permitted-Cross-Domain-Policies
	 *
	 * Reference: https://www.adobe.com/devnet/adobe-media-server/articles/cross-domain-xml-for-streaming.html
	 *
	 * Available Value: 'all', 'none', 'master-only', 'by-content-type', 'by-ftp-filename'
	 */

	'x-permitted-cross-domain-policies' => 'none',

	/*
	 * X-XSS-Protection
	 *
	 * Reference: https://blogs.msdn.microsoft.com/ieinternals/2011/01/31/controlling-the-xss-filter
	 *
	 * Available Value: '1', '0', '1; mode=block'
	 */

	'x-xss-protection' => '1; mode=block',

	/*
	 * Referrer-Policy
	 *
	 * Reference: https://w3c.github.io/webappsec-referrer-policy
	 *
	 * Available Value: 'no-referrer', 'no-referrer-when-downgrade', 'origin', 'origin-when-cross-origin',
	 *                  'same-origin', 'strict-origin', 'strict-origin-when-cross-origin', 'unsafe-url'
	 */

	'referrer-policy' => 'no-referrer',

	/*
	 * Clear-Site-Data
	 *
	 * Reference: https://w3c.github.io/webappsec-clear-site-data/
	 */

	'clear-site-data' => [
		'enable' => false,
		'all' => false,
		'cache' => true,
		'cookies' => true,
		'storage' => true,
		'executionContexts' => true,
	],

	/*
	 * HTTP Strict Transport Security
	 *
	 * Reference: https://developer.mozilla.org/en-US/docs/Web/Security/HTTP_strict_transport_security
	 *
	 * Please ensure your website had set up ssl/tls before enable hsts.
	 */

	'hsts' => [
		'enable' => env('SECURITY_HEADER_HSTS_ENABLE', false),
		'max-age' => 15552000,
		'include-sub-domains' => false,
	],

	/*
	 * Expect-CT
	 *
	 * Reference: https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Expect-CT
	 */

	'expect-ct' => [
		'enable' => false,
		'max-age' => 2147483648,
		'enforce' => false,
		'report-uri' => null,
	],

	/*
	 * Public Key Pinning
	 *
	 * Reference: https://developer.mozilla.org/en-US/docs/Web/Security/Public_Key_Pinning
	 *
	 * hpkp will be ignored if hashes is empty.
	 */

	'hpkp' => [
		'hashes' => [
			// 'sha256-hash-value',
		],
		'include-sub-domains' => false,
		'max-age' => 15552000,
		'report-only' => false,
		'report-uri' => null,
	],

	/*
	 * Feature Policy
	 *
	 * Reference: https://wicg.github.io/feature-policy/
	 */

	'feature-policy' => [
		'enable' => true,

		/*
		 * Each directive details can be found on:
		 *
		 * https://github.com/WICG/feature-policy/blob/master/features.md
		 *
		 * 'none', '*' and 'self allow' are mutually exclusive,
		 * the priority is 'none' > '*' > 'self allow'.
		 */
		'autoplay' => [
			'none' => false,
			'*' => false,
			'self' => true,
			'allow' => [
				// 'url',
			],
		],

		'camera' => [
			'none' => false,
			'*' => false,
			'self' => false,
			'allow' => [
				// 'url',
			],
		],

		'encrypted-media' => [
			'none' => false,
			'*' => false,
			'self' => true,
			'allow' => [
				// 'url',
			],
		],

		'fullscreen' => [
			'none' => false,
			'*' => false,
			'self' => true,
			'allow' => [
				// 'url',
			],
		],

		'geolocation' => [
			'none' => false,
			'*' => false,
			'self' => false,
			'allow' => [
				// 'url',
			],
		],

		'microphone' => [
			'none' => false,
			'*' => false,
			'self' => false,
			'allow' => [
				// 'url',
			],
		],

		'midi' => [
			'none' => false,
			'*' => false,
			'self' => false,
			'allow' => [
				// 'url',
			],
		],

		'payment' => [
			'none' => false,
			'*' => false,
			'self' => false,
			'allow' => [
				// 'url',
			],
		],

		'picture-in-picture' => [
			'none' => false,
			'*' => true,
			'self' => false,
			'allow' => [
				// 'url',
			],
		],

		'accelerometer' => [
			'none' => false,
			'*' => false,
			'self' => false,
			'allow' => [
				// 'url',
			],
		],

		'ambient-light-sensor' => [
			'none' => false,
			'*' => false,
			'self' => false,
			'allow' => [
				// 'url',
			],
		],

		'gyroscope' => [
			'none' => false,
			'*' => false,
			'self' => false,
			'allow' => [
				// 'url',
			],
		],

		'magnetometer' => [
			'none' => false,
			'*' => false,
			'self' => false,
			'allow' => [
				// 'url',
			],
		],

		'speaker' => [
			'none' => false,
			'*' => false,
			'self' => true,
			'allow' => [
				// 'url',
			],
		],

		'sync-xhr' => [
			'none' => false,
			'*' => true,
			'self' => false,
			'allow' => [
				// 'url',
			],
		],

		'usb' => [
			'none' => false,
			'*' => false,
			'self' => false,
			'allow' => [
				// 'url',
			],
		],

		'vr' => [
			'none' => false,
			'*' => false,
			'self' => true,
			'allow' => [
				// 'url',
			],
		],
	],

	/*
	 * Content Security Policy
	 *
	 * Reference: https://developer.mozilla.org/en-US/docs/Web/Security/CSP
	 *
	 * csp will be ignored if custom-csp is not null. To disable csp, set custom-csp to empty string.
	 *
	 * Note: custom-csp does not support report-only.
	 */

	/*
	 * There is no easy way to use CSP with debug bar at the moment, so we disable CSP if debug bar is enabled.
	 */
	'csp' => [
		'enable' => true,

		// https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Content-Security-Policy-Report-Only
		'report-only' => false,

		// https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Content-Security-Policy/report-to
		'report-to' => '',

		// https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Content-Security-Policy/report-uri
		'report-uri' => [
			// uri
		],

		// https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Content-Security-Policy/block-all-mixed-content
		'block-all-mixed-content' => false,

		// https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Content-Security-Policy/upgrade-insecure-requests
		'upgrade-insecure-requests' => false,

		// https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Content-Security-Policy/base-uri
		'base-uri' => [
		],

		// https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Content-Security-Policy/child-src
		'child-src' => [
		],

		// https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Content-Security-Policy/connect-src
		'connect-src' => array_merge(
			['https://lycheeorg.github.io/update.json'],
			explode(',', env('SECURITY_HEADER_CSP_CONNECT_SRC', ''))
		),

		// https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Content-Security-Policy/default-src
		'default-src' => [
			'self' => false,
		],

		// https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Content-Security-Policy/font-src
		'font-src' => [
		],

		// https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Content-Security-Policy/form-action
		'form-action' => [
			'self' => true,
		],

		// https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Content-Security-Policy/frame-ancestors
		'frame-ancestors' => [
		],

		// https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Content-Security-Policy/frame-src
		'frame-src' => [
		],

		// https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Content-Security-Policy/img-src
		'img-src' => [
			'self' => true,
			// Allow OpenStreetMap tile images to be fetched from the different provides
			// Allow image to be directly encoded at the img source parameter
			'allow' => array_merge(
				[
					'https://maps.wikimedia.org/osm-intl/',
					'https://tile.openstreetmap.org/',
					'https://tile.openstreetmap.de/',
					'https://a.tile.openstreetmap.fr/osmfr/',
					'https://b.tile.openstreetmap.fr/osmfr/',
					'https://c.tile.openstreetmap.fr/osmfr/',
					'https://a.osm.rrze.fau.de/osmhd/',
					'https://b.osm.rrze.fau.de/osmhd/',
					'https://c.osm.rrze.fau.de/osmhd/',
					'data:', // required by openstreetmap
					'blob:', // required for "live" photos
				],
				// Add the S3 URL to the list of allowed image sources
				env('AWS_ACCESS_KEY_ID', '') === '' ? [] :
				[
					// @phpstan-ignore-next-line
					str_replace(parse_url(env('AWS_URL'), PHP_URL_PATH), '', env('AWS_URL')),
				]
			),
		],

		// https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Content-Security-Policy/manifest-src
		'manifest-src' => [
		],

		// https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Content-Security-Policy/media-src
		'media-src' => [
			'self' => true,
			'allow' => [
				'blob:', // required for "live" photos
			],
		],

		// https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Content-Security-Policy/navigate-to
		'navigate-to' => [
			'unsafe-allow-redirects' => false,
		],

		// https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Content-Security-Policy/object-src
		'object-src' => [
			'none' => true,
		],

		// https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Content-Security-Policy/plugin-types
		'plugin-types' => [
			// 'application/pdf',
		],

		// https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Content-Security-Policy/prefetch-src
		'prefetch-src' => [
		],

		// https://w3c.github.io/webappsec-trusted-types/dist/spec/#integration-with-content-security-policy
		'require-trusted-types-for' => [
			'script' => false,
		],

		// https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Content-Security-Policy/sandbox
		'sandbox' => [
			'enable' => false,

			'allow-downloads-without-user-activation' => false,

			'allow-forms' => false,

			'allow-modals' => false,

			'allow-orientation-lock' => false,

			'allow-pointer-lock' => false,

			'allow-popups' => false,

			'allow-popups-to-escape-sandbox' => false,

			'allow-presentation' => false,

			'allow-same-origin' => false,

			'allow-scripts' => false,

			'allow-storage-access-by-user-activation' => false,

			'allow-top-navigation' => false,

			'allow-top-navigation-by-user-activation' => false,
		],

		// https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Content-Security-Policy/script-src
		'script-src' => [
			'none' => false,

			'self' => true,

			// https://www.chromestatus.com/feature/5792234276388864
			'report-sample' => true,

			'allow' => array_merge(
				['https://www.dropbox.com/static/api/1/dropins.js'],
				explode(',', env('SECURITY_HEADER_SCRIPT_SRC_ALLOW', ''))
			),

			'schemes' => [
				// 'data:',
				// 'https:',
			],

			/* followings are only work for `script` and `style` related directives */
			'unsafe-inline' => false,

			'unsafe-eval' => false,

			// https://www.w3.org/TR/CSP3/#unsafe-hashes-usage
			'unsafe-hashes' => true,

			// Enable `strict-dynamic` will *ignore* `self`, `unsafe-inline`,
			// `allow` and `schemes`. You can find more information from:
			// https://www.w3.org/TR/CSP3/#strict-dynamic-usage
			'strict-dynamic' => false,

			'hashes' => [
				'sha256' => [
					// 'sha256-hash-value-with-base64-encode',

					// lychee.startDrag(event)
					'FdKE+KVp/tkYM5hwGXGeKZ1EmS4DJ8kbnsKo5YymNrc=',

					// lychee.endDrag(event)
					'bY67+0U7yUmtjaisfHv+mZXHsAptKwcV1a4EacCUL5M=',

					// lychee.overDrag(event)
					'fwPcZ6SFcvBLfJYjzlBRZfKzcidwsD4GPcmkVECbSKM=',

					// lychee.leaveDrag(event)
					'FCPseLYJ4+r0Mbp93zyaq/x4zQEEPLgEectDgkA/V3A=',

					// lychee.finishDrag(event)
					'T0Fzr5h5zkZyE3QOpQ9anSTcWp19WQ14eO86qdlSdvA=',

					// upload.check()
					'CL4mGy9ZhHM+PkLDZsWVuM25kEFBv3FXlmWe/O9Unmc=',

					/*
	const hashMatch = document.location.hash.replace("#", "").split("/");
	const albumID = hashMatch[0] ?? '';
	const photoID = hashMatch[1] ?? '';
	const elem = document.getElementById('redirectData');
	const gallery = elem.dataset.gallery;
	const base = elem.dataset.redirect;

	if (photoID !== '') {
		window.location = gallery + '/' + albumID + '/' + photoID;
	} else if (albumID !== '') {
		window.location = gallery + '/' + albumID;
	} else {
		window.location = base;
	}
 */
					'okzzdI+OgeNYCr3oJXDZ/rPI5WwGyiU5V/RwOQrv5zE=',

					/*
	document.addEventListener("DOMContentLoaded", function(event) {
		document.querySelector("form").addEventListener("submit", function(e){
			document.querySelector("form").hidden = true;
			var text = document.createElement("div");
			text.innerHTML = "Migration started. <b>DO NOT REFRESH THE PAGE</b>.";
			document.querySelector(".form").appendChild(text);
			// e.preventDefault();    //stop form from submitting
		});
	});

*/
					'hHvKTS0wUaMuiFMar2j4TbjYjlLQMR/c5b0bA9DLi6g=',
				],

				'sha384' => [
					// 'sha384-hash-value-with-base64-encode',
				],

				'sha512' => [
					// 'sha512-hash-value-with-base64-encode',
				],
			],

			'nonces' => [
				// 'base64-encoded',
			],

			'unsafe-hashed-attributes' => false,

			'add-generated-nonce' => false,
		],

		// https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Content-Security-Policy/script-src-attr
		'script-src-attr' => [
		],

		// https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Content-Security-Policy/script-src-elem
		'script-src-elem' => [
		],

		// https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Content-Security-Policy/style-src
		'style-src' => [
			'self' => true,
			'unsafe-inline' => true, // We need this one due to direct styles (not just style classes) applied by JavaScript
		],

		// https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Content-Security-Policy/style-src-attr
		'style-src-attr' => [
		],

		// https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Content-Security-Policy/style-src-elem
		'style-src-elem' => [
		],

		// https://w3c.github.io/webappsec-trusted-types/dist/spec/#trusted-types-csp-directive
		'trusted-types' => [
			'enable' => false,

			'allow-duplicates' => false,

			'default' => false,

			'policies' => [
			],
		],

		// https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Content-Security-Policy/worker-src
		'worker-src' => [
		],
	],
];
