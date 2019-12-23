<?php


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
     * Available Value: 'deny', 'sameorigin', 'allow-from <uri>'
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

    'custom-csp' => null,

    'csp' => [
        'report-only' => false,
        'report-uri' => null,
        'block-all-mixed-content' => false,
        'upgrade-insecure-requests' => false,

        /*
         * Please references script-src directive for available values, only `script-src` and `style-src`
         * supports `add-generated-nonce`.
         *
         * Note: when directive value is empty, it will use `none` for that directive.
         */

        'script-src' => [
            'allow' => [
                // 'url',
            ],
            'hashes' => [
	            'sha256' => [
	            	'8bLztrDF3NUpheSuvAzpebgX1DpPJEfhmUHKTwGF4qA=',
		            ]
            ],
            'nonces' => [
                // 'base64-encoded',
            ],
            'schemes' => [
                // 'https:',
            ],
            'self' => true,
            'unsafe-inline' => false,
            'unsafe-eval' => false,
            'strict-dynamic' => false,
            'unsafe-hashed-attributes' => false,
            // https://www.chromestatus.com/feature/5792234276388864
            'report-sample' => true,
            'add-generated-nonce' => false,
        ],

        'style-src' => [
            'allow' => [
	            'https://fonts.googleapis.com'
            ],
            'hashes' => [
                // 'sha256' => [
                //     'hash-value',
                // ],
            ],
            'nonces' => [
                //
            ],
            'schemes' => [
                // 'https:',
            ],
            'self' => true,
            'unsafe-inline' => true,
            'report-sample' => true,
            'add-generated-nonce' => false,
        ],
        'img-src' => [
            'self' => true,
      			// Allow OpenStreetMap tile images to be fetched from the different provides
      			// Allow image to be directly encoded at the img source parameter
            'allow' => [
              'https://maps.wikimedia.org/osm-intl/',
              'https://a.tile.osm.org/',
              'https://b.tile.osm.org/',
              'https://c.tile.osm.org/',
              'https://a.tile.openstreetmap.de/',
              'https://b.tile.openstreetmap.de/',
              'https://c.tile.openstreetmap.de/',
              'https://a.tile.openstreetmap.fr/osmfr/',
              'https://b.tile.openstreetmap.fr/osmfr/',
              'https://c.tile.openstreetmap.fr/osmfr/',
              'https://a.osm.rrze.fau.de/osmhd/',
              'https://b.osm.rrze.fau.de/osmhd/',
              'https://c.osm.rrze.fau.de/osmhd/',
              'data:',
              'blob:',
            ],
        ],
        'default-src' => [
            'self' => true,
            'allow' => [
              'blob:',
            ],
        ],
        'base-uri' => [
            //
        ],
        'connect-src' => [
	        'allow' => [
		        'http://lycheeorg.github.io/update.json',
            'blob:'
	        ],
            'self' => true
        ],
        'font-src' => [
        	'allow' => [
		        'https://fonts.gstatic.com'
	        ],
            'self' => true
            //
        ],
        'form-action' => [
            //
        ],
        'frame-ancestors' => [
            //
        ],
        'frame-src' => [
            //
        ],
        'manifest-src' => [
            //
        ],
        'media-src' => [
            'self' => true,
            'allow' => [
              'blob:',
            ],
        ],
        'object-src' => [
            //
        ],
        'worker-src' => [
            //
        ],
        'plugin-types' => [
            // 'application/x-shockwave-flash',
        ],
        'require-sri-for' => '',
        'sandbox' => '',

    ],

];
