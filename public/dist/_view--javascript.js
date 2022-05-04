"use strict";

var _typeof = typeof Symbol === "function" && typeof Symbol.iterator === "symbol" ? function (obj) { return typeof obj; } : function (obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; };

var _templateObject = _taggedTemplateLiteral(["<svg class='iconic ", "'><use xlink:href='#", "' /></svg>"], ["<svg class='iconic ", "'><use xlink:href='#", "' /></svg>"]),
    _templateObject2 = _taggedTemplateLiteral(["<div class='divider'><h1>", "</h1></div>"], ["<div class='divider'><h1>", "</h1></div>"]),
    _templateObject3 = _taggedTemplateLiteral(["<div id='", "' class='edit'>", "</div>"], ["<div id='", "' class='edit'>", "</div>"]),
    _templateObject4 = _taggedTemplateLiteral(["<div id='multiselect' style='top: ", "px; left: ", "px;'></div>"], ["<div id='multiselect' style='top: ", "px; left: ", "px;'></div>"]),
    _templateObject5 = _taggedTemplateLiteral(["\n\t\t\t<div class='album ", " ", "'\n\t\t\t\tdata-id='", "'\n\t\t\t\tdata-nsfw='", "'\n\t\t\t\tdata-tabindex='", "'>\n\t\t\t\t  ", "\n\t\t\t\t  ", "\n\t\t\t\t  ", "\n\t\t\t\t<div class='overlay'>\n\t\t\t\t\t<h1 title='$", "'>$", "</h1>\n\t\t\t\t\t<a>", "</a>\n\t\t\t\t</div>\n\t\t\t"], ["\n\t\t\t<div class='album ", " ", "'\n\t\t\t\tdata-id='", "'\n\t\t\t\tdata-nsfw='", "'\n\t\t\t\tdata-tabindex='", "'>\n\t\t\t\t  ", "\n\t\t\t\t  ", "\n\t\t\t\t  ", "\n\t\t\t\t<div class='overlay'>\n\t\t\t\t\t<h1 title='$", "'>$", "</h1>\n\t\t\t\t\t<a>", "</a>\n\t\t\t\t</div>\n\t\t\t"]),
    _templateObject6 = _taggedTemplateLiteral(["\n\t\t\t\t<div class='badges'>\n\t\t\t\t\t<a class='badge ", " icn-warning'>", "</a>\n\t\t\t\t\t<a class='badge ", " icn-star'>", "</a>\n\t\t\t\t\t<a class='badge ", "'>", "</a>\n\t\t\t\t\t<a class='badge ", " ", " icn-share'>", "</a>\n\t\t\t\t\t<a class='badge ", "'>", "</a>\n\t\t\t\t\t<a class='badge ", "'>", "</a>\n\t\t\t\t\t<a class='badge ", "'>", "</a>\n\t\t\t\t\t<a class='badge ", " icn-cover'>", "</a>\n\t\t\t\t</div>\n\t\t\t\t"], ["\n\t\t\t\t<div class='badges'>\n\t\t\t\t\t<a class='badge ", " icn-warning'>", "</a>\n\t\t\t\t\t<a class='badge ", " icn-star'>", "</a>\n\t\t\t\t\t<a class='badge ", "'>", "</a>\n\t\t\t\t\t<a class='badge ", " ", " icn-share'>", "</a>\n\t\t\t\t\t<a class='badge ", "'>", "</a>\n\t\t\t\t\t<a class='badge ", "'>", "</a>\n\t\t\t\t\t<a class='badge ", "'>", "</a>\n\t\t\t\t\t<a class='badge ", " icn-cover'>", "</a>\n\t\t\t\t</div>\n\t\t\t\t"]),
    _templateObject7 = _taggedTemplateLiteral(["\n\t\t\t\t<div class='subalbum_badge'>\n\t\t\t\t\t<a class='badge badge--folder'>", "</a>\n\t\t\t\t</div>"], ["\n\t\t\t\t<div class='subalbum_badge'>\n\t\t\t\t\t<a class='badge badge--folder'>", "</a>\n\t\t\t\t</div>"]),
    _templateObject8 = _taggedTemplateLiteral(["\n\t\t\t<div class='photo ", "' data-album-id='", "' data-id='", "' data-tabindex='", "'>\n\t\t\t\t", "\n\t\t\t\t<div class='overlay'>\n\t\t\t\t\t<h1 title='$", "'>$", "</h1>\n\t\t\t"], ["\n\t\t\t<div class='photo ", "' data-album-id='", "' data-id='", "' data-tabindex='", "'>\n\t\t\t\t", "\n\t\t\t\t<div class='overlay'>\n\t\t\t\t\t<h1 title='$", "'>$", "</h1>\n\t\t\t"]),
    _templateObject9 = _taggedTemplateLiteral(["<a><span title='Camera Date'>", "</span>", "</a>"], ["<a><span title='Camera Date'>", "</span>", "</a>"]),
    _templateObject10 = _taggedTemplateLiteral(["<a>", "</a>"], ["<a>", "</a>"]),
    _templateObject11 = _taggedTemplateLiteral(["\n\t\t\t\t<div class='badges'>\n\t\t\t\t<a class='badge ", " icn-star'>", "</a>\n\t\t\t\t<a class='badge ", " icn-share'>", "</a>\n\t\t\t\t<a class='badge ", " icn-cover'>", "</a>\n\t\t\t\t</div>\n\t\t\t\t"], ["\n\t\t\t\t<div class='badges'>\n\t\t\t\t<a class='badge ", " icn-star'>", "</a>\n\t\t\t\t<a class='badge ", " icn-share'>", "</a>\n\t\t\t\t<a class='badge ", " icn-cover'>", "</a>\n\t\t\t\t</div>\n\t\t\t\t"]),
    _templateObject12 = _taggedTemplateLiteral(["\n\t\t<div id=\"image_overlay\">\n\t\t<h1>$", "</h1>\n\t\t"], ["\n\t\t<div id=\"image_overlay\">\n\t\t<h1>$", "</h1>\n\t\t"]),
    _templateObject13 = _taggedTemplateLiteral(["<video width=\"auto\" height=\"auto\" id='image' controls class='", "' autobuffer ", " data-tabindex='", "'><source src='", "'>Your browser does not support the video tag.</video>"], ["<video width=\"auto\" height=\"auto\" id='image' controls class='", "' autobuffer ", " data-tabindex='", "'><source src='", "'>Your browser does not support the video tag.</video>"]),
    _templateObject14 = _taggedTemplateLiteral(["<img id='image' class='", "' src='img/placeholder.png' draggable='false' alt='big' data-tabindex='", "'>"], ["<img id='image' class='", "' src='img/placeholder.png' draggable='false' alt='big' data-tabindex='", "'>"]),
    _templateObject15 = _taggedTemplateLiteral(["", ""], ["", ""]),
    _templateObject16 = _taggedTemplateLiteral(["<div class='no_content fadeIn'>", ""], ["<div class='no_content fadeIn'>", ""]),
    _templateObject17 = _taggedTemplateLiteral(["<p>", "</p>"], ["<p>", "</p>"]),
    _templateObject18 = _taggedTemplateLiteral(["\n\t\t\t<h1>$", "</h1>\n\t\t\t<div class='rows'>\n\t\t\t"], ["\n\t\t\t<h1>$", "</h1>\n\t\t\t<div class='rows'>\n\t\t\t"]),
    _templateObject19 = _taggedTemplateLiteral(["\n\t\t\t\t<div class='row'>\n\t\t\t\t\t<a class='name'>", "</a>\n\t\t\t\t\t<a class='status'></a>\n\t\t\t\t\t<p class='notice'></p>\n\t\t\t\t</div>\n\t\t\t\t"], ["\n\t\t\t\t<div class='row'>\n\t\t\t\t\t<a class='name'>", "</a>\n\t\t\t\t\t<a class='status'></a>\n\t\t\t\t\t<p class='notice'></p>\n\t\t\t\t</div>\n\t\t\t\t"]),
    _templateObject20 = _taggedTemplateLiteral(["\n\t\t<div class='row'>\n\t\t\t<a class='name'>", "</a>\n\t\t\t<a class='status'></a>\n\t\t\t<p class='notice'></p>\n\t\t</div>\n\t\t"], ["\n\t\t<div class='row'>\n\t\t\t<a class='name'>", "</a>\n\t\t\t<a class='status'></a>\n\t\t\t<p class='notice'></p>\n\t\t</div>\n\t\t"]),
    _templateObject21 = _taggedTemplateLiteral(["<a class='", "'>$", "<span data-index='", "'>", "</span></a>"], ["<a class='", "'>$", "<span data-index='", "'>", "</span></a>"]),
    _templateObject22 = _taggedTemplateLiteral(["<a class='", "'>$", "</a>"], ["<a class='", "'>$", "</a>"]),
    _templateObject23 = _taggedTemplateLiteral(["<div class='empty'>", "</div>"], ["<div class='empty'>", "</div>"]),
    _templateObject24 = _taggedTemplateLiteral(["<div class=\"users_view_line\">\n\t\t\t<p id=\"UserData", "\">\n\t\t\t<input name=\"id\" type=\"hidden\" inputmode=\"numeric\" value=\"", "\" />\n\t\t\t<input class=\"text\" name=\"username\" type=\"text\" value=\"$", "\" placeholder=\"username\" />\n\t\t\t<input class=\"text\" name=\"password\" type=\"text\" placeholder=\"new password\" />\n\t\t\t<span class=\"choice\" title=\"Allow uploads\">\n\t\t\t<label>\n\t\t\t<input type=\"checkbox\" name=\"may_upload\" />\n\t\t\t<span class=\"checkbox\"><svg class=\"iconic \"><use xlink:href=\"#check\"></use></svg></span>\n\t\t\t</label>\n\t\t\t</span>\n\t\t\t<span class=\"choice\" title=\"Restricted account\">\n\t\t\t<label>\n\t\t\t<input type=\"checkbox\" name=\"is_locked\" />\n\t\t\t<span class=\"checkbox\"><svg class=\"iconic \"><use xlink:href=\"#check\"></use></svg></span>\n\t\t\t</label>\n\t\t\t</span>\n\t\t\t</p>\n\t\t\t<a id=\"UserUpdate", "\"  class=\"basicModal__button basicModal__button_OK\">Save</a>\n\t\t\t<a id=\"UserDelete", "\"  class=\"basicModal__button basicModal__button_DEL\">Delete</a>\n\t\t</div>\n\t\t"], ["<div class=\"users_view_line\">\n\t\t\t<p id=\"UserData", "\">\n\t\t\t<input name=\"id\" type=\"hidden\" inputmode=\"numeric\" value=\"", "\" />\n\t\t\t<input class=\"text\" name=\"username\" type=\"text\" value=\"$", "\" placeholder=\"username\" />\n\t\t\t<input class=\"text\" name=\"password\" type=\"text\" placeholder=\"new password\" />\n\t\t\t<span class=\"choice\" title=\"Allow uploads\">\n\t\t\t<label>\n\t\t\t<input type=\"checkbox\" name=\"may_upload\" />\n\t\t\t<span class=\"checkbox\"><svg class=\"iconic \"><use xlink:href=\"#check\"></use></svg></span>\n\t\t\t</label>\n\t\t\t</span>\n\t\t\t<span class=\"choice\" title=\"Restricted account\">\n\t\t\t<label>\n\t\t\t<input type=\"checkbox\" name=\"is_locked\" />\n\t\t\t<span class=\"checkbox\"><svg class=\"iconic \"><use xlink:href=\"#check\"></use></svg></span>\n\t\t\t</label>\n\t\t\t</span>\n\t\t\t</p>\n\t\t\t<a id=\"UserUpdate", "\"  class=\"basicModal__button basicModal__button_OK\">Save</a>\n\t\t\t<a id=\"UserDelete", "\"  class=\"basicModal__button basicModal__button_DEL\">Delete</a>\n\t\t</div>\n\t\t"]),
    _templateObject25 = _taggedTemplateLiteral(["<div class=\"u2f_view_line\">\n\t\t\t<p id=\"CredentialData", "\">\n\t\t\t<input name=\"id\" type=\"hidden\" inputmode=\"numeric\" value=\"", "\" />\n\t\t\t<span class=\"text\">", "</span>\n\t\t\t<!--- <span class=\"choice\" title=\"Allow uploads\">\n\t\t\t<label>\n\t\t\t<input type=\"checkbox\" name=\"may_upload\" />\n\t\t\t<span class=\"checkbox\"><svg class=\"iconic \"><use xlink:href=\"#check\"></use></svg></span>\n\t\t\t</label>\n\t\t\t</span>\n\t\t\t<span class=\"choice\" title=\"Restricted account\">\n\t\t\t<label>\n\t\t\t<input type=\"checkbox\" name=\"is_locked\" />\n\t\t\t<span class=\"checkbox\"><svg class=\"iconic \"><use xlink:href=\"#check\"></use></svg></span>\n\t\t\t</label>\n\t\t\t</span>--->\n\t\t\t</p>\n\t\t\t<a id=\"CredentialDelete", "\"  class=\"basicModal__button basicModal__button_DEL\">Delete</a>\n\t\t</div>\n\t\t"], ["<div class=\"u2f_view_line\">\n\t\t\t<p id=\"CredentialData", "\">\n\t\t\t<input name=\"id\" type=\"hidden\" inputmode=\"numeric\" value=\"", "\" />\n\t\t\t<span class=\"text\">", "</span>\n\t\t\t<!--- <span class=\"choice\" title=\"Allow uploads\">\n\t\t\t<label>\n\t\t\t<input type=\"checkbox\" name=\"may_upload\" />\n\t\t\t<span class=\"checkbox\"><svg class=\"iconic \"><use xlink:href=\"#check\"></use></svg></span>\n\t\t\t</label>\n\t\t\t</span>\n\t\t\t<span class=\"choice\" title=\"Restricted account\">\n\t\t\t<label>\n\t\t\t<input type=\"checkbox\" name=\"is_locked\" />\n\t\t\t<span class=\"checkbox\"><svg class=\"iconic \"><use xlink:href=\"#check\"></use></svg></span>\n\t\t\t</label>\n\t\t\t</span>--->\n\t\t\t</p>\n\t\t\t<a id=\"CredentialDelete", "\"  class=\"basicModal__button basicModal__button_DEL\">Delete</a>\n\t\t</div>\n\t\t"]),
    _templateObject26 = _taggedTemplateLiteral(["$", "", ""], ["$", "", ""]),
    _templateObject27 = _taggedTemplateLiteral(["<span class='attr_", "_separator'>, </span>"], ["<span class='attr_", "_separator'>, </span>"]),
    _templateObject28 = _taggedTemplateLiteral(["<span class='attr_", " search'>$", "</span>"], ["<span class='attr_", " search'>$", "</span>"]),
    _templateObject29 = _taggedTemplateLiteral(["<span class='attr_", "'>$", "</span>"], ["<span class='attr_", "'>$", "</span>"]),
    _templateObject30 = _taggedTemplateLiteral(["\n\t\t\t\t\t\t <tr>\n\t\t\t\t\t\t\t <td>", "</td>\n\t\t\t\t\t\t\t <td>", "</td>\n\t\t\t\t\t\t </tr>\n\t\t\t\t\t\t "], ["\n\t\t\t\t\t\t <tr>\n\t\t\t\t\t\t\t <td>", "</td>\n\t\t\t\t\t\t\t <td>", "</td>\n\t\t\t\t\t\t </tr>\n\t\t\t\t\t\t "]),
    _templateObject31 = _taggedTemplateLiteral(["\n\t\t\t\t <div class='sidebar__divider'>\n\t\t\t\t\t <h1>", "</h1>\n\t\t\t\t </div>\n\t\t\t\t <div id='tags'>\n\t\t\t\t\t <div class='attr_", "'>", "</div>\n\t\t\t\t\t ", "\n\t\t\t\t </div>\n\t\t\t\t "], ["\n\t\t\t\t <div class='sidebar__divider'>\n\t\t\t\t\t <h1>", "</h1>\n\t\t\t\t </div>\n\t\t\t\t <div id='tags'>\n\t\t\t\t\t <div class='attr_", "'>", "</div>\n\t\t\t\t\t ", "\n\t\t\t\t </div>\n\t\t\t\t "]);

function _taggedTemplateLiteral(strings, raw) { return Object.freeze(Object.defineProperties(strings, { raw: { value: Object.freeze(raw) } })); }

/**
 * @description This module communicates with Lychee's API
 */

/**
 * @callback APISuccessCB
 * @param {Object} data the decoded JSON response
 * @returns {void}
 */

/**
 * @callback APIErrorCB
 * @param {XMLHttpRequest} jqXHR the jQuery XMLHttpRequest object, see {@link https://api.jquery.com/jQuery.ajax/#jqXHR}.
 * @param {Object} params the original JSON parameters of the request
 * @param {?LycheeException} lycheeException the Lychee exception
 * @returns {boolean}
 */

/**
 * @callback APIProgressCB
 * @param {ProgressEvent} event the progress event
 * @returns {void}
 */

/**
 * The main API object
 */
var api = {
	/**
  * Global, default error handler
  *
  * @type {?APIErrorCB}
  */
	onError: null
};

/**
 * Checks whether the returned error is probably due to an expired HTTP session.
 *
 * There seem to be two variants how an expired session may be reported:
 *
 *  1. The web-application has already been loaded, is fully initialized
 *     and a user tries to navigate to another part of the gallery.
 *     In this case, the AJAX request sends the previous, expired CSRF token
 *     and the backend responds with a 419 status code.
 *  2. The user completely reloads the website (e.g. typically be hitting
 *     F5 in most browsers).
 *     In this case, the CSRF token is re-generated by the backend, so no
 *     CSRF mismatch occurs, but the user is no longer authenticated. and the
 *     backend responds with a 401 status code.
 *
 * Note, case 2 also happens if a user directly navigates to a link
 * of the form `#/album-id/` or `#/album-id/photo-id` unless the album is
 * public, but password protected.
 * In that case, the backend also sends a 401 status code, but with a
 * special "Password Required" exception which is handled specially in
 * `album.js`.
 *
 * @param {XMLHttpRequest} jqXHR the jQuery XMLHttpRequest object, see {@link https://api.jquery.com/jQuery.ajax/#jqXHR}.
 * @param {?LycheeException} lycheeException the Lychee exception
 *
 * @returns {boolean}
 */
api.hasSessionExpired = function (jqXHR, lycheeException) {
	return jqXHR.status === 419 && !!lycheeException && lycheeException.exception.endsWith("SessionExpiredException") || jqXHR.status === 401 && !!lycheeException && lycheeException.exception.endsWith("UnauthenticatedException");
};

/**
 *
 * @param {string} fn
 * @param {Object} params
 * @param {?APISuccessCB} successCallback
 * @param {?APIProgressCB} responseProgressCB
 * @param {?APIErrorCB} errorCallback
 * @returns {void}
 */
api.post = function (fn, params) {
	var successCallback = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : null;
	var responseProgressCB = arguments.length > 3 && arguments[3] !== undefined ? arguments[3] : null;
	var errorCallback = arguments.length > 4 && arguments[4] !== undefined ? arguments[4] : null;

	loadingBar.show();

	/**
  * The success handler
  * @param {Object} data the decoded JSON object of the response
  */
	var successHandler = function successHandler(data) {
		setTimeout(loadingBar.hide, 100);
		if (successCallback) successCallback(data);
	};

	/**
  * The error handler
  * @param {XMLHttpRequest} jqXHR the jQuery XMLHttpRequest object, see {@link https://api.jquery.com/jQuery.ajax/#jqXHR}.
  */
	var errorHandler = function errorHandler(jqXHR) {
		/**
   * @type {?LycheeException}
   */
		var lycheeException = jqXHR.responseJSON;

		if (errorCallback) {
			var isHandled = errorCallback(jqXHR, params, lycheeException);
			if (isHandled) {
				setTimeout(loadingBar.hide, 100);
				return;
			}
		}
		// Call global error handler for unhandled errors
		api.onError(jqXHR, params, lycheeException);
	};

	var ajaxParams = {
		type: "POST",
		url: "api/" + fn,
		contentType: "application/json",
		data: JSON.stringify(params),
		dataType: "json",
		headers: {
			"X-XSRF-TOKEN": csrf.getCSRFCookieValue()
		},
		success: successHandler,
		error: errorHandler
	};

	if (responseProgressCB !== null) {
		ajaxParams.xhrFields = {
			onprogress: responseProgressCB
		};
	}

	$.ajax(ajaxParams);
};

/**
 *
 * @param {string} url
 * @param {APISuccessCB} callback
 * @returns {void}
 */
api.getCSS = function (url, callback) {
	loadingBar.show();

	/**
  * The success handler
  * @param {Object} data the decoded JSON object of the response
  */
	var successHandler = function successHandler(data) {
		setTimeout(loadingBar.hide, 100);

		callback(data);
	};

	/**
  * The error handler
  * @param {XMLHttpRequest} jqXHR the jQuery XMLHttpRequest object, see {@link https://api.jquery.com/jQuery.ajax/#jqXHR}.
  */
	var errorHandler = function errorHandler(jqXHR) {
		api.onError(jqXHR, {}, null);
	};

	$.ajax({
		type: "GET",
		url: url,
		data: {},
		dataType: "text",
		headers: {
			"X-XSRF-TOKEN": csrf.getCSRFCookieValue()
		},
		success: successHandler,
		error: errorHandler
	});
};

var csrf = {};

/**
 * Returns the value of the CSRF token.
 *
 * Inspired by https://developer.mozilla.org/en-US/docs/Web/API/Document/cookie#example_2_get_a_sample_cookie_named_test2
 *
 * @returns {?string}
 */
csrf.getCSRFCookieValue = function () {
	var cookie = document.cookie.split(";").find(function (row) {
		return (/^\s*(X-)?[XC]SRF-TOKEN\s*=/.test(row)
		);
	});
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
	return cookie ? cookie.split("=")[1].trim().replaceAll("%3D", "") : null;
};

/**
 * @description Used as an alternative `main` to view single photos with `view.php`
 *
 * Note, the build script picks a subset of the JS files to build a variant
 * of the JS code for the special "view mode".
 * As this variant does not include all JS files, some objects are missing.
 * Hence, we must partially re-implement these objects to the extent which is
 * required by the methods we call.
 *
 * This approach is very tedious and error-prone, because we actually
 * duplicate code.
 * Also, it is not documented nor obvious why these "subset implementations"
 * are necessary.
 * Ideally, the full code base would be used all the time independent of
 * the users entry point.
 *
 * TODO: Find out why we actually need this approach. Re-implementing different variants of the same objects is very error-prone.
 */

// Sub-implementation of lychee -------------------------------------------------------------- //

var lychee = {};

lychee.content = $(".content");
lychee.imageview = $("#imageview");
lychee.mapview = $("#mapview");

/**
 * DON'T USE THIS METHOD.
 *
 * TODO: Find all invocations of this method and nuke them.
 *
 * This method does not cover all potentially dangerous characters and this
 * method should not be required on the first place.
 * jQuery and even native JS has better methods for this in the year 2022!
 *
 * @param {string} [html=""]
 * @returns {string}
 */
lychee.escapeHTML = function () {
	var html = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : "";

	// Ensure that html is a string
	html += "";

	// Escape all critical characters
	html = html.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;").replace(/'/g, "&#039;").replace(/`/g, "&#96;");

	return html;
};

/**
 * Creates a HTML string with some fancy variable substitution.
 *
 * Actually, this method should not be required in the year 2022.
 * jQuery and even native JS should probably provide a suitable alternative.
 * But this method is used so ubiquitous that it might be difficult to get
 * rid of it.
 *
 * TODO: Try it nonetheless.
 *
 * @param literalSections
 * @param substs
 * @returns {string}
 */
lychee.html = function (literalSections) {
	// Use raw literal sections: we don’t want
	// backslashes (\n etc.) to be interpreted
	var raw = literalSections.raw;
	var result = "";

	for (var _len = arguments.length, substs = Array(_len > 1 ? _len - 1 : 0), _key = 1; _key < _len; _key++) {
		substs[_key - 1] = arguments[_key];
	}

	substs.forEach(function (subst, i) {
		// Retrieve the literal section preceding
		// the current substitution
		var lit = raw[i];

		// If the substitution is preceded by a dollar sign,
		// we escape special characters in it
		if (lit.slice(-1) === "$") {
			subst = lychee.escapeHTML(subst);
			lit = lit.slice(0, -1);
		}

		result += lit;
		result += subst;
	});

	// Take care of last literal section
	// (Never fails, because an empty template string
	// produces one literal section, an empty string)
	result += raw[raw.length - 1];

	return result;
};

/**
 * @returns {string} - either `"touchend"` or `"click"`
 */
lychee.getEventName = function () {
	var touchendSupport = /Android|iPhone|iPad|iPod/i.test(navigator.userAgent || navigator.vendor || window.opera) && "ontouchend" in document.documentElement;
	return touchendSupport === true ? "touchend" : "click";
};

// Sub-implementation of photo -------------------------------------------------------------- //

var photo = {
	/** @type {?Photo} */
	json: null,
	/** @type {?LivePhotosKit.Player} */
	livePhotosObject: null
};

/**
 * @param {string} photoID
 * @param {string} service - one out of `"twitter"`, `"facebook"`, `"mail"` or `"dropbox"`
 * @returns {void}
 */
photo.share = function (photoID, service) {
	var url = location.toString();

	switch (service) {
		case "twitter":
			window.open("https://twitter.com/share?url=" + encodeURI(url));
			break;
		case "facebook":
			window.open("https://www.facebook.com/sharer.php?u=" + encodeURI(url));
			break;
		case "mail":
			location.href = "mailto:?subject=&body=" + encodeURI(url);
			break;
	}
};

/**
 * @returns {string}
 */
photo.getDirectLink = function () {
	return $("#imageview img").attr("src").replace(/"/g, "").replace(/url\(|\)$/gi, "");
};

/**
 * @returns {void}
 */
photo.show = function () {
	$("#imageview").removeClass("full");
	header.dom().removeClass("header--hidden");
};

/**
 * @returns {void}
 */
photo.hide = function () {
	if (visible.photo() && !visible.sidebar() && !visible.contextMenu()) {
		$("#imageview").addClass("full");
		header.dom().addClass("header--hidden");
	}
};

/**
 * @param {number} [animationDuration=300]
 * @param {number} [pauseBetweenUpdated=10]
 * @returns {void}
 */
photo.updateSizeLivePhotoDuringAnimation = function () {
	var animationDuration = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : 300;
	var pauseBetweenUpdated = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : 10;

	// For the LivePhotoKit, we need to call the updateSize manually
	// during CSS animations
	//
	var interval = setInterval(function () {
		if (photo.isLivePhotoInitialized()) {
			photo.livePhotosObject.updateSize();
		}
	}, pauseBetweenUpdated);

	setTimeout(function () {
		clearInterval(interval);
	}, animationDuration);
};

/**
 * @returns {boolean}
 */
photo.isLivePhotoInitialized = function () {
	return !!photo.livePhotosObject;
};

/**
 * @returns {void}
 */
photo.onresize = function () {
	// Copy of view.photo.onresize
	if (photo.json.size_variants.medium === null || photo.json.size_variants.medium2x === null) return;

	var imgWidth = photo.json.size_variants.medium.width;
	var imgHeight = photo.json.size_variants.medium.height;
	var containerWidth = parseFloat($("#imageview").width());
	var containerHeight = parseFloat($("#imageview").height());

	var width = imgWidth < containerWidth ? imgWidth : containerWidth;
	var height = width * imgHeight / imgWidth;
	if (height > containerHeight) {
		width = containerHeight * imgWidth / imgHeight;
	}

	$("img#image").attr("sizes", width + "px");
};

// Sub-implementation of contextMenu -------------------------------------------------------------- //

var contextMenu = {};

/**
 * @param {string} photoID
 * @param {jQuery.Event} e
 * @returns {void}
 */
contextMenu.sharePhoto = function (photoID, e) {
	var iconClass = "ionicons";

	var items = [{ title: build.iconic("twitter", iconClass) + "Twitter", fn: function fn() {
			return photo.share(photoID, "twitter");
		} }, { title: build.iconic("facebook", iconClass) + "Facebook", fn: function fn() {
			return photo.share(photoID, "facebook");
		} }, { title: build.iconic("envelope-closed") + "Mail", fn: function fn() {
			return photo.share(photoID, "mail");
		} }, { title: build.iconic("link-intact") + "Direct Link", fn: function fn() {
			return window.open(photo.getDirectLink(), "_newtab");
		} }];

	basicContext.show(items, e.originalEvent);
};

// Sub-implementation of photo -------------------------------------------------------------- //

var album = {
	json: null
};

album.isUploadable = function () {
	return false;
};

// Main -------------------------------------------------------------- //

var loadingBar = {
	show: function show() {},
	hide: function hide() {}
};

/**
 * @type {jQuery}
 */
var imageview = $("#imageview");

$(document).ready(function () {
	// Image View
	$(window).on("resize", photo.onresize);

	// Save ID of photo
	var queryParams = new URLSearchParams(document.location.search);
	var photoID = queryParams.get("p");

	// Set API error handler
	api.onError = handleAPIError;

	// Share
	header.dom("#button_share").on("click", function (e) {
		contextMenu.sharePhoto(photoID, e);
	});

	// Infobox
	header.dom("#button_info").on("click", function () {
		sidebar.toggle(true);
	});

	// Load photo
	loadPhotoInfo(photoID);
});

/**
 * TODO: This method is global for no particular reason. In case we ever clean up the view mode, this should be fixed, too.
 * @param {string} photoID
 */
var loadPhotoInfo = function loadPhotoInfo(photoID) {
	var params = {
		photoID: photoID
	};

	api.post("Photo::get", params,
	/** @param {Photo} data */
	function (data) {
		photo.json = data;

		// Set title
		var _title = data.title ? data.title : lychee.locale["UNTITLED"];
		// TODO: Actually the prefix should not be a hard-coded, but the value of `lychee.title`. However, I am unsure whether we load the configuration options in view mode.
		document.title = "Lychee – " + _title;
		header.dom(".header__title").text(_title);

		// Render HTML
		imageview.html(build.imageview(data, true, false).html);
		imageview.find(".arrow_wrapper").remove();
		imageview.addClass("fadeIn").show();
		photo.onresize();

		// Render Sidebar
		var structure = sidebar.createStructure.photo(data);
		var html = sidebar.render(structure);

		// Fullscreen
		var timeout = null;

		$(document).bind("mousemove", function () {
			clearTimeout(timeout);
			photo.show();
			timeout = setTimeout(photo.hide, 2500);
		});
		timeout = setTimeout(photo.hide, 2500);

		sidebar.dom(".sidebar__wrapper").html(html);
		sidebar.bind();
	});
};

/**
 * @param {XMLHttpRequest} jqXHR
 * @param {Object} params the original JSON parameters of the request
 * @param {?LycheeException} lycheeException the Lychee Exception
 * @returns {boolean}
 */
var handleAPIError = function handleAPIError(jqXHR, params, lycheeException) {
	var msg = jqXHR.statusText + (lycheeException ? " - " + lycheeException.message : "");
	loadingBar.show("error", msg);
	console.error("The server returned an error response", {
		description: msg,
		params: params,
		response: lycheeException
	});
	return true;
};

//noinspection HtmlUnknownTarget

/**
 * @description This module is used to generate HTML-Code.
 */

var build = {};

/**
 * @param {string} icon
 * @param {string} [classes=""]
 *
 * @returns {string}
 */
build.iconic = function (icon) {
	var classes = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : "";

	return lychee.html(_templateObject, classes, icon);
};

/**
 * @param {string} title
 * @returns {string}
 */
build.divider = function (title) {
	return lychee.html(_templateObject2, title);
};

/**
 * @param {string} id
 * @returns {string}
 */
build.editIcon = function (id) {
	return lychee.html(_templateObject3, id, build.iconic("pencil"));
};

/**
 * @param {number} top
 * @param {number} left
 * @returns {string}
 */
build.multiselect = function (top, left) {
	return lychee.html(_templateObject4, top, left);
};

/**
 * Returns HTML for the thumb of an album.
 *
 * @param {(Album|TagAlbum)} data
 *
 * @returns {string}
 */
build.getAlbumThumb = function (data) {
	var isVideo = data.thumb.type && data.thumb.type.indexOf("video") > -1;
	var isRaw = data.thumb.type && data.thumb.type.indexOf("raw") > -1;
	var thumb = data.thumb.thumb;
	var thumb2x = data.thumb.thumb2x;

	if (thumb === "uploads/thumb/" && isVideo) {
		return "<span class=\"thumbimg\"><img src='img/play-icon.png' alt='Photo thumbnail' data-overlay='false' draggable='false'></span>";
	}
	if (thumb === "uploads/thumb/" && isRaw) {
		return "<span class=\"thumbimg\"><img src='img/placeholder.png' alt='Photo thumbnail' data-overlay='false' draggable='false'></span>";
	}

	return "<span class=\"thumbimg" + (isVideo ? " video" : "") + "\"><img class='lazyload' src='img/placeholder.png' data-src='" + thumb + "' " + (thumb2x !== null ? "data-srcset='" + thumb2x + " 2x'" : "") + " alt='Photo thumbnail' data-overlay='false' draggable='false'></span>";
};

/**
 * @param {(Album|TagAlbum|SmartAlbum)} data
 * @param {boolean}                     disabled
 *
 * @returns {string} HTML for the album
 */
build.album = function (data) {
	var disabled = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : false;

	var formattedCreationTs = lychee.locale.printMonthYear(data.created_at);
	var formattedMinTs = lychee.locale.printMonthYear(data.min_taken_at);
	var formattedMaxTs = lychee.locale.printMonthYear(data.max_taken_at);
	var subtitle = formattedCreationTs;

	// check setting album_subtitle_type:
	// takedate: date range (min/max_takedate from EXIF; if missing defaults to creation)
	// creation: creation date of album
	// description: album description
	// default: any other type defaults to old style setting subtitles based of album sorting
	switch (lychee.album_subtitle_type) {
		case "description":
			subtitle = data.description ? data.description : "";
			break;
		case "takedate":
			if (formattedMinTs !== "" || formattedMaxTs !== "") {
				// either min_taken_at or max_taken_at is set
				subtitle = formattedMinTs === formattedMaxTs ? formattedMaxTs : formattedMinTs + " - " + formattedMaxTs;
				subtitle = "<span title='Camera Date'>" + build.iconic("camera-slr") + "</span>" + subtitle;
				break;
			}
		// fall through
		case "creation":
			break;
		case "oldstyle":
		default:
			if (lychee.sorting_albums && data.min_taken_at && data.max_taken_at) {
				if (lychee.sorting_albums.column === "max_taken_at" || lychee.sorting_albums.column === "min_taken_at") {
					if (formattedMinTs !== "" && formattedMaxTs !== "") {
						subtitle = formattedMinTs === formattedMaxTs ? formattedMaxTs : formattedMinTs + " - " + formattedMaxTs;
					} else if (formattedMinTs !== "" && lychee.sorting_albums.column === "min_taken_at") {
						subtitle = formattedMinTs;
					} else if (formattedMaxTs !== "" && lychee.sorting_albums.column === "max_taken_at") {
						subtitle = formattedMaxTs;
					}
				}
			}
	}

	var html = lychee.html(_templateObject5, disabled ? "disabled" : "", data.is_nsfw && lychee.nsfw_blur ? "blurred" : "", data.id, data.is_nsfw ? "1" : "0", tabindex.get_next_tab_index(), build.getAlbumThumb(data), build.getAlbumThumb(data), build.getAlbumThumb(data), data.title, data.title, subtitle);

	if (album.isUploadable() && !disabled) {
		var isCover = album.json && album.json.cover_id && data.thumb.id === album.json.cover_id;
		html += lychee.html(_templateObject6, data.is_nsfw ? "badge--nsfw" : "", build.iconic("warning"), data.id === SmartAlbumID.STARRED ? "badge--star" : "", build.iconic("star"), data.id === SmartAlbumID.RECENT ? "badge--visible badge--list" : "", build.iconic("clock"), data.id === SmartAlbumID.PUBLIC || data.is_public ? "badge--visible" : "", data.requires_link ? "badge--hidden" : "badge--not--hidden", build.iconic("eye"), data.id === SmartAlbumID.UNSORTED ? "badge--visible" : "", build.iconic("list"), data.has_password ? "badge--visible" : "", build.iconic("lock-locked"), data.is_tag_album ? "badge--tag" : "", build.iconic("tag"), isCover ? "badge--cover" : "", build.iconic("folder-cover"));
	}

	if (data.albums && data.albums.length > 0 || data.has_albums) {
		html += lychee.html(_templateObject7, build.iconic("layers"));
	}

	html += "</div>";

	return html;
};

/**
 * @param {Photo}   data
 * @param {boolean} disabled
 *
 * @returns {string} HTML for the photo
 */
build.photo = function (data) {
	var disabled = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : false;

	var html = "";
	var thumbnail = "";
	var thumb2x = "";
	// Note, album.json might not be loaded, if
	//  a) the photo is a single public photo in a private album
	//  b) the photo is part of a search result
	var isCover = album.json && album.json.cover_id === data.id;

	var isVideo = data.type && data.type.indexOf("video") > -1;
	var isRaw = data.type && data.type.indexOf("raw") > -1;
	var isLivePhoto = data.live_photo_url !== "" && data.live_photo_url !== null;

	if (data.size_variants.thumb === null) {
		if (isLivePhoto) {
			thumbnail = "<span class=\"thumbimg\"><img src='img/live-photo-icon.png' alt='Photo thumbnail' data-overlay='false' draggable='false' data-tabindex='" + tabindex.get_next_tab_index() + "'></span>";
		}
		if (isVideo) {
			thumbnail = "<span class=\"thumbimg\"><img src='img/play-icon.png' alt='Photo thumbnail' data-overlay='false' draggable='false' data-tabindex='" + tabindex.get_next_tab_index() + "'></span>";
		} else if (isRaw) {
			thumbnail = "<span class=\"thumbimg\"><img src='img/placeholder.png' alt='Photo thumbnail' data-overlay='false' draggable='false' data-tabindex='" + tabindex.get_next_tab_index() + "'></span>";
		}
	} else if (lychee.layout === 0) {
		if (data.size_variants.thumb2x !== null) {
			thumb2x = data.size_variants.thumb2x.url;
		}

		if (thumb2x !== "") {
			thumb2x = "data-srcset='" + thumb2x + " 2x'";
		}

		thumbnail = "<span class=\"thumbimg" + (isVideo ? " video" : "") + (isLivePhoto ? " livephoto" : "") + "\">";
		thumbnail += "<img class='lazyload' src='img/placeholder.png' data-src='" + data.size_variants.thumb.url + "' " + thumb2x + " alt='Photo thumbnail' data-overlay='false' draggable='false' >";
		thumbnail += "</span>";
	} else {
		if (data.size_variants.small !== null) {
			if (data.size_variants.small2x !== null) {
				thumb2x = "data-srcset='" + data.size_variants.small.url + " " + data.size_variants.small.width + "w, " + data.size_variants.small2x.url + " " + data.size_variants.small2x.width + "w'";
			}

			thumbnail = "<span class=\"thumbimg" + (isVideo ? " video" : "") + (isLivePhoto ? " livephoto" : "") + "\">";
			thumbnail += "<img class='lazyload' src='img/placeholder.png' data-src='" + data.size_variants.small.url + "' " + thumb2x + " alt='Photo thumbnail' data-overlay='false' draggable='false' >";
			thumbnail += "</span>";
		} else if (data.size_variants.medium !== null) {
			if (data.size_variants.medium2x !== null) {
				thumb2x = "data-srcset='" + data.size_variants.medium.url + " " + data.size_variants.medium.width + "w, " + data.size_variants.medium2x.url + " " + data.size_variants.medium2x.width + "w'";
			}

			thumbnail = "<span class=\"thumbimg" + (isVideo ? " video" : "") + (isLivePhoto ? " livephoto" : "") + "\">";
			thumbnail += "<img class='lazyload' src='img/placeholder.png' data-src='" + data.size_variants.medium.url + "' " + thumb2x + " alt='Photo thumbnail' data-overlay='false' draggable='false' >";
			thumbnail += "</span>";
		} else if (!isVideo) {
			// Fallback for images with no small or medium.
			thumbnail = "<span class=\"thumbimg" + (isLivePhoto ? " livephoto" : "") + "\">";
			thumbnail += "<img class='lazyload' src='img/placeholder.png' data-src='" + data.size_variants.original.url + "' alt='Photo thumbnail' data-overlay='false' draggable='false' >";
			thumbnail += "</span>";
		} else {
			// Fallback for videos with no small (the case of no thumb is
			// handled at the top of this function).

			if (data.size_variants.thumb2x !== null) {
				thumb2x = data.size_variants.thumb2x.url;
			}

			if (thumb2x !== "") {
				thumb2x = "data-srcset='" + data.size_variants.thumb.url + " " + data.size_variants.thumb.width + "w, " + thumb2x + " " + data.size_variants.thumb2x.width + "w'";
			}

			thumbnail = "<span class=\"thumbimg video\">";
			thumbnail += "<img class='lazyload' src='img/placeholder.png' data-src='" + data.size_variants.thumb.url + "' " + thumb2x + " alt='Photo thumbnail' data-overlay='false' draggable='false' >";
			thumbnail += "</span>";
		}
	}

	html += lychee.html(_templateObject8, disabled ? "disabled" : "", data.album_id, data.id, tabindex.get_next_tab_index(), thumbnail, data.title, data.title);

	if (data.taken_at !== null) html += lychee.html(_templateObject9, build.iconic("camera-slr"), lychee.locale.printDateTime(data.taken_at));else html += lychee.html(_templateObject10, lychee.locale.printDateTime(data.created_at));

	html += "</div>";

	if (album.isUploadable()) {
		// Note, `album.json` might be null, if the photo is displayed as
		// part of a search result and therefore the actual parent album
		// is not loaded. (The "parent" album is the virtual "search album"
		// in this case).
		// This also means that the displayed variant of the public badge of
		// a photo depends on the availability of the parent album.
		// This seems to be an undesired but unavoidable side effect.
		html += lychee.html(_templateObject11, data.is_starred ? "badge--star" : "", build.iconic("star"), data.is_public && album.json && !album.json.is_public ? "badge--visible badge--hidden" : "", build.iconic("eye"), isCover ? "badge--cover" : "", build.iconic("folder-cover"));
	}

	html += "</div>";

	return html;
};

/**
 * @param {Photo} data
 * @param {string} overlay_type
 * @param {boolean} [next=false]
 *
 * @returns {string}
 */
build.check_overlay_type = function (data, overlay_type) {
	var next = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : false;

	var types = ["desc", "date", "exif", "none"];
	var idx = types.indexOf(overlay_type);
	if (idx < 0) return "none";
	if (next) idx++;
	var exifHash = data.make + data.model + data.shutter + data.iso + (data.type.indexOf("video") !== 0 ? data.aperture + data.focal : "");

	for (var i = 0; i < types.length; i++) {
		var type = types[(idx + i) % types.length];
		if (type === "date" || type === "none") return type;
		if (type === "desc" && data.description && data.description !== "") return type;
		if (type === "exif" && exifHash !== "") return type;
	}
};

/**
 * @param {Photo} data
 * @returns {string}
 */
build.overlay_image = function (data) {
	var overlay = "";
	switch (build.check_overlay_type(data, lychee.image_overlay_type)) {
		case "desc":
			overlay = data.description;
			break;
		case "date":
			if (data.taken_at != null) overlay = "<a><span title='Camera Date'>" + build.iconic("camera-slr") + "</span>" + lychee.locale.printDateTime(data.taken_at) + "</a>";else overlay = lychee.locale.printDateTime(data.created_at);
			break;
		case "exif":
			var exifHash = data.make + data.model + data.shutter + data.aperture + data.focal + data.iso;
			if (exifHash !== "") {
				if (data.shutter && data.shutter !== "") overlay = data.shutter.replace("s", "sec");
				if (data.aperture && data.aperture !== "") {
					if (overlay !== "") overlay += " at ";
					overlay += data.aperture.replace("f/", "&fnof; / ");
				}
				if (data.iso && data.iso !== "") {
					if (overlay !== "") overlay += ", ";
					overlay += lychee.locale["PHOTO_ISO"] + " " + data.iso;
				}
				if (data.focal && data.focal !== "") {
					if (overlay !== "") overlay += "<br>";
					overlay += data.focal + (data.lens && data.lens !== "" ? " (" + data.lens + ")" : "");
				}
			}
			break;
		case "none":
		default:
			return "";
	}

	return lychee.html(_templateObject12, data.title ? data.title : lychee.locale["UNTITLED"]) + (overlay !== "" ? "<p>" + overlay + "</p>" : "") + "\n\t\t</div>\n\t\t";
};

/**
 * @param {Photo} data
 * @param {boolean} areControlsVisible
 * @param {boolean} autoplay
 * @returns {{thumb: string, html: string}}
 */
build.imageview = function (data, areControlsVisible, autoplay) {
	var html = "";
	var thumb = "";

	if (data.type.indexOf("video") > -1) {
		html += lychee.html(_templateObject13, areControlsVisible ? "" : "full", autoplay ? "autoplay" : "", tabindex.get_next_tab_index(), data.size_variants.original.url);
	} else if (data.type.indexOf("raw") > -1 && data.size_variants.medium === null) {
		html += lychee.html(_templateObject14, areControlsVisible ? "" : "full", tabindex.get_next_tab_index());
	} else {
		var img = "";

		if (data.live_photo_url === "" || data.live_photo_url === null) {
			// It's normal photo

			// See if we have the thumbnail loaded...
			$(".photo").each(function () {
				if ($(this).attr("data-id") && $(this).attr("data-id") === data.id) {
					var thumbimg = $(this).find("img");
					if (thumbimg.length > 0) {
						thumb = thumbimg[0].currentSrc ? thumbimg[0].currentSrc : thumbimg[0].src;
						return false;
					}
				}
			});

			if (data.size_variants.medium !== null) {
				var medium = "";

				if (data.size_variants.medium2x !== null) {
					medium = "srcset='" + data.size_variants.medium.url + " " + data.size_variants.medium.width + "w, " + data.size_variants.medium2x.url + " " + data.size_variants.medium2x.width + "w'";
				}
				img = "<img id='image' class='" + (areControlsVisible ? "" : "full") + "' src='" + data.size_variants.medium.url + "' " + medium + ("  draggable='false' alt='medium' data-tabindex='" + tabindex.get_next_tab_index() + "'>");
			} else {
				img = "<img id='image' class='" + (areControlsVisible ? "" : "full") + "' src='" + data.size_variants.original.url + "' draggable='false' alt='big' data-tabindex='" + tabindex.get_next_tab_index() + "'>";
			}
		} else {
			if (data.size_variants.medium !== null) {
				var medium_width = data.size_variants.medium.width;
				var medium_height = data.size_variants.medium.height;
				// It's a live photo
				img = "<div id='livephoto' data-live-photo data-proactively-loads-video='true' data-photo-src='" + data.size_variants.medium.url + "' data-video-src='" + data.live_photo_url + "'  style='width: " + medium_width + "px; height: " + medium_height + "px' data-tabindex='" + tabindex.get_next_tab_index() + "'></div>";
			} else {
				// It's a live photo
				img = "<div id='livephoto' data-live-photo data-proactively-loads-video='true' data-photo-src='" + data.size_variants.original.url + "' data-video-src='" + data.live_photo_url + "'  style='width: " + data.size_variants.original.width + "px; height: " + data.size_variants.original.height + "px' data-tabindex='" + tabindex.get_next_tab_index() + "'></div>";
			}
		}

		html += lychee.html(_templateObject15, img);
	}

	html += build.overlay_image(data) + ("\n\t\t\t<div class='arrow_wrapper arrow_wrapper--previous'><a id='previous'>" + build.iconic("caret-left") + "</a></div>\n\t\t\t<div class='arrow_wrapper arrow_wrapper--next'><a id='next'>" + build.iconic("caret-right") + "</a></div>\n\t\t\t");

	return { html: html, thumb: thumb };
};

/**
 * @param {string} type - either `"magnifying-glass"`, `"eye"`, `"cog"` or `"question-marks"`
 * @returns {string}
 */
build.no_content = function (type) {
	var html = "";

	html += lychee.html(_templateObject16, build.iconic(type));

	switch (type) {
		case "magnifying-glass":
			html += lychee.html(_templateObject17, lychee.locale["VIEW_NO_RESULT"]);
			break;
		case "eye":
			html += lychee.html(_templateObject17, lychee.locale["VIEW_NO_PUBLIC_ALBUMS"]);
			break;
		case "cog":
			html += lychee.html(_templateObject17, lychee.locale["VIEW_NO_CONFIGURATION"]);
			break;
		case "question-mark":
			html += lychee.html(_templateObject17, lychee.locale["VIEW_PHOTO_NOT_FOUND"]);
			break;
	}

	html += "</div>";

	return html;
};

/**
 * @param {string}                                           title the title of the dialog
 * @param {(FileList|File[]|DropboxFile[]|{name: string}[])} files a list of file entries to be shown in the dialog
 * @returns {string}                                                the HTML fragment for the dialog
 */
build.uploadModal = function (title, files) {
	var html = "";

	html += lychee.html(_templateObject18, title);

	var i = 0;

	while (i < files.length) {
		var file = files[i];

		if (file.name.length > 40) file.name = file.name.substr(0, 17) + "..." + file.name.substr(file.name.length - 20, 20);

		html += lychee.html(_templateObject19, file.name);

		i++;
	}

	html += "</div>";

	return html;
};

/**
 * Builds the HTML snippet for a row in the upload dialog.
 *
 * @param {string} name
 * @returns {string}
 */
build.uploadNewFile = function (name) {
	if (name.length > 40) {
		name = name.substring(0, 17) + "..." + name.substring(name.length - 20, name.length);
	}

	return lychee.html(_templateObject20, name);
};

/**
 * @param {string[]} tags
 * @returns {string}
 */
build.tags = function (tags) {
	var html = "";
	var editable = album.isUploadable();

	// Search is enabled if logged in (not publicMode) or public search is enabled
	var searchable = !lychee.publicMode || lychee.public_search;

	// build class_string for tag
	var a_class = searchable ? "tag search" : "tag";

	if (tags.length !== 0) {
		tags.forEach(function (tag, index) {
			if (editable) {
				html += lychee.html(_templateObject21, a_class, tag, index, build.iconic("x"));
			} else {
				html += lychee.html(_templateObject22, a_class, tag);
			}
		});
	} else {
		html = lychee.html(_templateObject23, lychee.locale["NO_TAGS"]);
	}

	return html;
};

/**
 * @param {User} user
 * @returns {string}
 */
build.user = function (user) {
	return lychee.html(_templateObject24, user.id, user.id, user.username, user.id, user.id);
};

/**
 * @param {WebAuthnCredential} credential
 * @returns {string}
 */
build.u2f = function (credential) {
	return lychee.html(_templateObject25, credential.id, credential.id, credential.id.slice(0, 30), credential.id);
};

/**
 * @description This module takes care of the header.
 */

/**
 * @namespace
 * @property {jQuery} _dom
 */
var header = {
	_dom: $(".header")
};

/**
 * @param {?string} [selector=null]
 * @returns {jQuery}
 */
header.dom = function (selector) {
	if (selector == null || selector === "") return header._dom;
	return header._dom.find(selector);
};

/**
 * @returns {void}
 */
header.bind = function () {
	// Event Name
	var eventName = lychee.getEventName();

	header.dom(".header__title").on(eventName, function (e) {
		if ($(this).hasClass("header__title--editable") === false) return false;

		if (lychee.enable_contextmenu_header === false) return false;

		if (visible.photo()) contextMenu.photoTitle(album.getID(), photo.getID(), e);else contextMenu.albumTitle(album.getID(), e);
	});

	header.dom("#button_visibility").on(eventName, function () {
		photo.setProtectionPolicy(photo.getID());
	});
	header.dom("#button_share").on(eventName, function (e) {
		contextMenu.sharePhoto(photo.getID(), e);
	});

	header.dom("#button_visibility_album").on(eventName, function () {
		album.setProtectionPolicy(album.getID());
	});

	header.dom("#button_sharing_album_users").on(eventName, function () {
		album.shareUsers(album.getID());
	});

	header.dom("#button_share_album").on(eventName, function (e) {
		contextMenu.shareAlbum(album.getID(), e);
	});

	header.dom("#button_signin").on(eventName, lychee.loginDialog);
	header.dom("#button_settings").on(eventName, function (e) {
		if ($(".leftMenu").css("display") === "none") {
			// left menu disabled on small screens
			contextMenu.config(e);
		} else {
			// standard left menu
			leftMenu.open();
		}
	});
	header.dom("#button_close_config").on(eventName, function () {
		tabindex.makeFocusable(header.dom());
		tabindex.makeFocusable(lychee.content);
		tabindex.makeUnfocusable(leftMenu._dom);
		multiselect.bind();
		lychee.load();
	});
	header.dom("#button_info_album").on(eventName, function () {
		sidebar.toggle(true);
	});
	header.dom("#button_info").on(eventName, function () {
		sidebar.toggle(true);
	});
	header.dom(".button--map-albums").on(eventName, function () {
		lychee.gotoMap();
	});
	header.dom("#button_map_album").on(eventName, function () {
		lychee.gotoMap(album.getID());
	});
	header.dom("#button_map").on(eventName, function () {
		lychee.gotoMap(album.getID());
	});
	header.dom(".button_add").on(eventName, contextMenu.add);
	header.dom("#button_more").on(eventName, function (e) {
		contextMenu.photoMore(photo.getID(), e);
	});
	header.dom("#button_move_album").on(eventName, function (e) {
		contextMenu.move([album.getID()], e, album.setAlbum, "ROOT", album.getParentID() != null);
	});
	header.dom("#button_nsfw_album").on(eventName, function () {
		album.toggleNSFW();
	});
	header.dom("#button_move").on(eventName, function (e) {
		contextMenu.move([photo.getID()], e, photo.setAlbum);
	});
	header.dom(".header__hostedwith").on(eventName, function () {
		window.open(lychee.website);
	});
	header.dom("#button_trash_album").on(eventName, function () {
		album.delete([album.getID()]);
	});
	header.dom("#button_trash").on(eventName, function () {
		photo.delete([photo.getID()]);
	});
	header.dom("#button_archive").on(eventName, function () {
		album.getArchive([album.getID()]);
	});
	header.dom("#button_star").on(eventName, function () {
		photo.toggleStar();
	});
	header.dom("#button_rotate_ccwise").on(eventName, function () {
		photoeditor.rotate(photo.getID(), -1);
	});
	header.dom("#button_rotate_cwise").on(eventName, function () {
		photoeditor.rotate(photo.getID(), 1);
	});
	header.dom("#button_back_home").on(eventName, function () {
		if (!album.json.parent_id) {
			lychee.goto();
		} else {
			lychee.goto(album.getParentID());
		}
	});
	header.dom("#button_back").on(eventName, function () {
		lychee.goto(album.getID());
	});
	header.dom("#button_back_map").on(eventName, function () {
		lychee.goto(album.getID());
	});
	header.dom("#button_fs_album_enter,#button_fs_enter").on(eventName, lychee.fullscreenEnter);
	header.dom("#button_fs_album_exit,#button_fs_exit").on(eventName, lychee.fullscreenExit).hide();

	header.dom(".header__search").on("keyup click", function () {
		if ($(this).val().length > 0) {
			lychee.goto("search/" + encodeURIComponent($(this).val()));
		} else if (search.json !== null) {
			search.reset();
		}
	});
	header.dom(".header__clear").on(eventName, function () {
		search.reset();
	});

	header.bind_back();
};

/**
 * @returns {void}
 */
header.bind_back = function () {
	// Event Name
	var eventName = lychee.getEventName();

	header.dom(".header__title").on(eventName, function () {
		if (lychee.landing_page_enable && visible.albums()) {
			window.location.href = ".";
		} else {
			return false;
		}
	});
};

/**
 * @returns {void}
 */
header.show = function () {
	lychee.imageview.removeClass("full");
	header.dom().removeClass("header--hidden");

	tabindex.restoreSettings(header.dom());

	photo.updateSizeLivePhotoDuringAnimation();
};

/**
 * @returns {void}
 */
header.hideIfLivePhotoNotPlaying = function () {
	// Hides the header, if current live photo is not playing
	if (!photo.isLivePhotoPlaying()) header.hide();
};

/**
 * @returns {void}
 */
header.hide = function () {
	if (visible.photo() && !visible.sidebar() && !visible.contextMenu() && basicModal.visible() === false) {
		tabindex.saveSettings(header.dom());
		tabindex.makeUnfocusable(header.dom());

		lychee.imageview.addClass("full");
		header.dom().addClass("header--hidden");

		photo.updateSizeLivePhotoDuringAnimation();
	}
};

/**
 * @param {string} title
 * @returns {void}
 */
header.setTitle = function (title) {
	var $title = header.dom(".header__title");
	var html = lychee.html(_templateObject26, title, build.iconic("caret-bottom"));

	$title.html(html);
};

/**
 *
 * @param {string} mode either one out of `"public"`, `"albums"`, `"album"`,
 *                      `"photo"`, `"map"` or `"config"`
 * @returns {void}
 */
header.setMode = function (mode) {
	if (mode === "albums" && lychee.publicMode === true) mode = "public";

	switch (mode) {
		case "public":
			header.dom().removeClass("header--view");
			header.dom(".header__toolbar--albums, .header__toolbar--album, .header__toolbar--photo, .header__toolbar--map, .header__toolbar--config").removeClass("header__toolbar--visible");
			header.dom(".header__toolbar--public").addClass("header__toolbar--visible");
			tabindex.makeFocusable(header.dom(".header__toolbar--public"));
			tabindex.makeUnfocusable(header.dom(".header__toolbar--albums, .header__toolbar--album, .header__toolbar--photo, .header__toolbar--map, .header__toolbar--config"));

			if (lychee.public_search) {
				var e = $(".header__search, .header__clear", ".header__toolbar--public");
				e.show();
				tabindex.makeFocusable(e);
			} else {
				var _e = $(".header__search, .header__clear", ".header__toolbar--public");
				_e.hide();
				tabindex.makeUnfocusable(_e);
			}

			// Set icon in Public mode
			if (lychee.map_display_public) {
				var _e2 = $(".button--map-albums", ".header__toolbar--public");
				_e2.show();
				tabindex.makeFocusable(_e2);
			} else {
				var _e3 = $(".button--map-albums", ".header__toolbar--public");
				_e3.hide();
				tabindex.makeUnfocusable(_e3);
			}

			// Set focus on login button
			if (lychee.active_focus_on_page_load) {
				$("#button_signin").focus();
			}
			return;

		case "albums":
			header.dom().removeClass("header--view");
			header.dom(".header__toolbar--public, .header__toolbar--album, .header__toolbar--photo, .header__toolbar--map, .header__toolbar--config").removeClass("header__toolbar--visible");
			header.dom(".header__toolbar--albums").addClass("header__toolbar--visible");

			tabindex.makeFocusable(header.dom(".header__toolbar--albums"));
			tabindex.makeUnfocusable(header.dom(".header__toolbar--public, .header__toolbar--album, .header__toolbar--photo, .header__toolbar--map, .header__toolbar--config"));

			// If map is disabled, we should hide the icon
			if (lychee.map_display) {
				var _e4 = $(".button--map-albums", ".header__toolbar--albums");
				_e4.show();
				tabindex.makeFocusable(_e4);
			} else {
				var _e5 = $(".button--map-albums", ".header__toolbar--albums");
				_e5.hide();
				tabindex.makeUnfocusable(_e5);
			}

			if (lychee.enable_button_add && lychee.may_upload) {
				var _e6 = $(".button_add", ".header__toolbar--albums");
				_e6.show();
				tabindex.makeFocusable(_e6);
			} else {
				var _e7 = $(".button_add", ".header__toolbar--albums");
				_e7.remove();
			}

			return;

		case "album":
			var albumID = album.getID();

			header.dom().removeClass("header--view");
			header.dom(".header__toolbar--public, .header__toolbar--albums, .header__toolbar--photo, .header__toolbar--map, .header__toolbar--config").removeClass("header__toolbar--visible");
			header.dom(".header__toolbar--album").addClass("header__toolbar--visible");

			tabindex.makeFocusable(header.dom(".header__toolbar--album"));
			tabindex.makeUnfocusable(header.dom(".header__toolbar--public, .header__toolbar--albums, .header__toolbar--photo, .header__toolbar--map, .header__toolbar--config"));

			// Hide download button when album empty or we are not allowed to
			// upload to it and it's not explicitly marked as downloadable.
			if (!album.json || album.json.photos.length === 0 && album.json.albums && album.json.albums.length === 0 || !album.isUploadable() && !album.json.is_downloadable) {
				var _e8 = $("#button_archive");
				_e8.hide();
				tabindex.makeUnfocusable(_e8);
			} else {
				var _e9 = $("#button_archive");
				_e9.show();
				tabindex.makeFocusable(_e9);
			}

			if (album.json && album.json.hasOwnProperty("is_share_button_visible") && !album.json.is_share_button_visible) {
				var _e10 = $("#button_share_album");
				_e10.hide();
				tabindex.makeUnfocusable(_e10);
			} else {
				var _e11 = $("#button_share_album");
				_e11.show();
				tabindex.makeFocusable(_e11);
			}

			// If map is disabled, we should hide the icon
			if (lychee.publicMode === true ? lychee.map_display_public : lychee.map_display) {
				var _e12 = $("#button_map_album");
				_e12.show();
				tabindex.makeFocusable(_e12);
			} else {
				var _e13 = $("#button_map_album");
				_e13.hide();
				tabindex.makeUnfocusable(_e13);
			}

			if (albumID === SmartAlbumID.STARRED || albumID === SmartAlbumID.PUBLIC || albumID === SmartAlbumID.RECENT) {
				$("#button_nsfw_album, #button_info_album, #button_trash_album, #button_visibility_album, #button_sharing_album_users, #button_move_album").hide();
				if (album.isUploadable()) {
					$(".button_add, .header__divider", ".header__toolbar--album").show();
					tabindex.makeFocusable($(".button_add, .header__divider", ".header__toolbar--album"));
				} else {
					$(".button_add, .header__divider", ".header__toolbar--album").hide();
					tabindex.makeUnfocusable($(".button_add, .header__divider", ".header__toolbar--album"));
				}
				tabindex.makeUnfocusable($("#button_nsfw_album, #button_info_album, #button_trash_album, #button_visibility_album, #button_sharing_album_users, #button_move_album"));
			} else if (albumID === SmartAlbumID.UNSORTED) {
				$("#button_nsfw_album, #button_info_album, #button_visibility_album, #button_sharing_album_users, #button_move_album").hide();
				$("#button_trash_album, .button_add, .header__divider", ".header__toolbar--album").show();
				tabindex.makeFocusable($("#button_trash_album, .button_add, .header__divider", ".header__toolbar--album"));
				tabindex.makeUnfocusable($("#button_nsfw_album, #button_info_album, #button_visibility_album, #button_sharing_album_users, #button_move_album"));
			} else if (album.isTagAlbum()) {
				$("#button_info_album").show();
				if (sidebar.keepSidebarVisible() && !visible.sidebar()) sidebar.toggle(false);
				$("#button_move_album").hide();
				$(".button_add, .header__divider", ".header__toolbar--album").hide();
				tabindex.makeFocusable($("#button_info_album"));
				tabindex.makeUnfocusable($("#button_move_album"));
				tabindex.makeUnfocusable($(".button_add, .header__divider", ".header__toolbar--album"));
				if (album.isUploadable()) {
					$("#button_nsfw_album, #button_visibility_album, #button_sharing_album_users, #button_trash_album").show();
					tabindex.makeFocusable($("#button_nsfw_album, #button_visibility_album, #button_sharing_album_users, #button_trash_album"));
					if ($("#button_visibility_album").is(":hidden")) {
						// This can happen with narrow screens.  In that
						// case we re-enable the add button which will
						// contain the overflow items.
						$(".button_add, .header__divider", ".header__toolbar--album").show();
						tabindex.makeFocusable($(".button_add, .header__divider", ".header__toolbar--album"));
					}
				} else {
					$("#button_nsfw_album, #button_visibility_album, #button_sharing_album_users, #button_trash_album").hide();
					tabindex.makeUnfocusable($("#button_nsfw_album, #button_visibility_album, #button_sharing_album_users, #button_trash_album"));
				}
			} else {
				$("#button_info_album").show();
				if (sidebar.keepSidebarVisible() && !visible.sidebar()) sidebar.toggle(false);
				tabindex.makeFocusable($("#button_info_album"));
				if (album.isUploadable()) {
					$("#button_nsfw_album, #button_trash_album, #button_move_album, #button_visibility_album, #button_sharing_album_users, .button_add, .header__divider", ".header__toolbar--album").show();
					tabindex.makeFocusable($("#button_nsfw_album, #button_trash_album, #button_move_album, #button_visibility_album, #button_sharing_album_users, .button_add, .header__divider", ".header__toolbar--album"));
				} else {
					$("#button_nsfw_album, #button_trash_album, #button_move_album, #button_visibility_album, #button_sharing_album_users, .button_add, .header__divider", ".header__toolbar--album").hide();
					tabindex.makeUnfocusable($("#button_nsfw_album, #button_trash_album, #button_move_album, #button_visibility_album, #button_sharing_album_users, .button_add, .header__divider", ".header__toolbar--album"));
				}
			}

			// Remove buttons if needed
			if (!lychee.enable_button_visibility) {
				var _e14 = $("#button_visibility_album", "#button_sharing_album_users", ".header__toolbar--album");
				_e14.remove();
			}
			if (!lychee.enable_button_share) {
				var _e15 = $("#button_share_album", ".header__toolbar--album");
				_e15.remove();
			}
			if (!lychee.enable_button_archive) {
				var _e16 = $("#button_archive", ".header__toolbar--album");
				_e16.remove();
			}
			if (!lychee.enable_button_move) {
				var _e17 = $("#button_move_album", ".header__toolbar--album");
				_e17.remove();
			}
			if (!lychee.enable_button_trash) {
				var _e18 = $("#button_trash_album", ".header__toolbar--album");
				_e18.remove();
			}
			if (!lychee.enable_button_fullscreen || !lychee.fullscreenAvailable()) {
				var _e19 = $("#button_fs_album_enter", ".header__toolbar--album");
				_e19.remove();
			}
			if (!lychee.enable_button_add) {
				var _e20 = $(".button_add", ".header__toolbar--album");
				_e20.remove();
			}

			return;

		case "photo":
			header.dom().addClass("header--view");
			header.dom(".header__toolbar--public, .header__toolbar--albums, .header__toolbar--album, .header__toolbar--map, .header__toolbar--config").removeClass("header__toolbar--visible");
			header.dom(".header__toolbar--photo").addClass("header__toolbar--visible");

			tabindex.makeFocusable(header.dom(".header__toolbar--photo"));
			tabindex.makeUnfocusable(header.dom(".header__toolbar--public, .header__toolbar--albums, .header__toolbar--album, .header__toolbar--map, .header__toolbar--config"));
			// If map is disabled, we should hide the icon
			if (lychee.publicMode === true ? lychee.map_display_public : lychee.map_display) {
				var _e21 = $("#button_map");
				_e21.show();
				tabindex.makeFocusable(_e21);
			} else {
				var _e22 = $("#button_map");
				_e22.hide();
				tabindex.makeUnfocusable(_e22);
			}

			if (album.isUploadable()) {
				var _e23 = $("#button_trash, #button_move, #button_visibility, #button_star");
				_e23.show();
				tabindex.makeFocusable(_e23);
			} else {
				var _e24 = $("#button_trash, #button_move, #button_visibility, #button_star");
				_e24.hide();
				tabindex.makeUnfocusable(_e24);
			}

			if (photo.json && photo.json.hasOwnProperty("is_share_button_visible") && !photo.json.is_share_button_visible) {
				var _e25 = $("#button_share");
				_e25.hide();
				tabindex.makeUnfocusable(_e25);
			} else {
				var _e26 = $("#button_share");
				_e26.show();
				tabindex.makeFocusable(_e26);
			}

			// Hide More menu if empty (see contextMenu.photoMore)
			$("#button_more").show();
			tabindex.makeFocusable($("#button_more"));
			if (!(album.isUploadable() || (photo.json.hasOwnProperty("is_downloadable") ? photo.json.is_downloadable : album.json && album.json.is_downloadable)) && !(photo.json.size_variants.original.url && photo.json.size_variants.original.url !== "")) {
				var _e27 = $("#button_more");
				_e27.hide();
				tabindex.makeUnfocusable(_e27);
			}

			// Remove buttons if needed
			if (!lychee.enable_button_visibility) {
				var _e28 = $("#button_visibility", ".header__toolbar--photo");
				_e28.remove();
			}
			if (!lychee.enable_button_share) {
				var _e29 = $("#button_share", ".header__toolbar--photo");
				_e29.remove();
			}
			if (!lychee.enable_button_move) {
				var _e30 = $("#button_move", ".header__toolbar--photo");
				_e30.remove();
			}
			if (!lychee.enable_button_trash) {
				var _e31 = $("#button_trash", ".header__toolbar--photo");
				_e31.remove();
			}
			if (!lychee.enable_button_fullscreen || !lychee.fullscreenAvailable()) {
				var _e32 = $("#button_fs_enter", ".header__toolbar--photo");
				_e32.remove();
			}
			if (!lychee.enable_button_more) {
				var _e33 = $("#button_more", ".header__toolbar--photo");
				_e33.remove();
			}
			if (!lychee.enable_button_rotate) {
				var _e34 = $("#button_rotate_cwise", ".header__toolbar--photo");
				_e34.remove();

				_e34 = $("#button_rotate_ccwise", ".header__toolbar--photo");
				_e34.remove();
			}
			return;
		case "map":
			header.dom().removeClass("header--view");
			header.dom(".header__toolbar--public, .header__toolbar--album, .header__toolbar--albums, .header__toolbar--photo, .header__toolbar--config").removeClass("header__toolbar--visible");
			header.dom(".header__toolbar--map").addClass("header__toolbar--visible");

			tabindex.makeFocusable(header.dom(".header__toolbar--map"));
			tabindex.makeUnfocusable(header.dom(".header__toolbar--public, .header__toolbar--album, .header__toolbar--albums, .header__toolbar--photo, .header__toolbar--config"));
			return;
		case "config":
			header.dom().addClass("header--view");
			header.dom(".header__toolbar--public, .header__toolbar--albums, .header__toolbar--album, .header__toolbar--photo, .header__toolbar--map").removeClass("header__toolbar--visible");
			header.dom(".header__toolbar--config").addClass("header__toolbar--visible");
			return;
	}
};

/**
 * Note that the pull-down menu is now enabled not only for editable
 * items but for all of public/albums/album/photo views, so 'editable' is a
 * bit of a misnomer at this point...
 *
 * @param {boolean} editable
 * @returns {void}
 */
header.setEditable = function (editable) {
	var $title = header.dom(".header__title");

	if (editable) $title.addClass("header__title--editable");else $title.removeClass("header__title--editable");
};

/**
 * @description This module is used to check if elements are visible or not.
 */

var visible = {};

/** @returns {boolean} */
visible.albums = function () {
	return !!header.dom(".header__toolbar--public").hasClass("header__toolbar--visible") || !!header.dom(".header__toolbar--albums").hasClass("header__toolbar--visible");
};

/** @returns {boolean} */
visible.album = function () {
	return !!header.dom(".header__toolbar--album").hasClass("header__toolbar--visible");
};

/** @returns {boolean} */
visible.photo = function () {
	return $("#imageview.fadeIn").length > 0;
};

/** @returns {boolean} */
visible.mapview = function () {
	return $("#mapview.fadeIn").length > 0;
};

/** @returns {boolean} */
visible.config = function () {
	return !!header.dom(".header__toolbar--config").hasClass("header__toolbar--visible");
};

/** @returns {boolean} */
visible.search = function () {
	return search.json !== null;
};

/** @returns {boolean} */
visible.sidebar = function () {
	return !!sidebar.dom().hasClass("active");
};

/** @returns {boolean} */
visible.sidebarbutton = function () {
	return visible.photo() || visible.album() && $("#button_info_album:visible").length > 0;
};

/** @returns {boolean} */
visible.header = function () {
	return !header.dom().hasClass("header--hidden");
};

/** @returns {boolean} */
visible.contextMenu = function () {
	return basicContext.visible();
};

/** @returns {boolean} */
visible.multiselect = function () {
	return $("#multiselect").length > 0;
};

/** @returns {boolean} */
visible.leftMenu = function () {
	return !!leftMenu.dom().hasClass("leftMenu__visible");
};

/**
 * @description This module takes care of the sidebar.
 */

/**
 * @namespace
 */
var sidebar = {
	/** @type {jQuery} */
	_dom: $(".sidebar"),
	types: {
		DEFAULT: 0,
		TAGS: 1
	},
	createStructure: {}
};

/**
 * @param {?string} [selector=null]
 * @returns {jQuery}
 */
sidebar.dom = function (selector) {
	if (selector == null || selector === "") return sidebar._dom;
	return sidebar._dom.find(selector);
};

/**
 * This function should be called after building and appending
 * the sidebars content to the DOM.
 * This function can be called multiple times, therefore
 * event handlers should be removed before binding a new one.
 *
 * @returns {void}
 */
sidebar.bind = function () {
	var eventName = lychee.getEventName();

	sidebar.dom("#edit_title").off(eventName).on(eventName, function () {
		if (visible.photo()) photo.setTitle([photo.getID()]);else if (visible.album()) album.setTitle([album.getID()]);
	});

	sidebar.dom("#edit_description").off(eventName).on(eventName, function () {
		if (visible.photo()) photo.setDescription(photo.getID());else if (visible.album()) album.setDescription(album.getID());
	});

	sidebar.dom("#edit_showtags").off(eventName).on(eventName, function () {
		album.setShowTags(album.getID());
	});

	sidebar.dom("#edit_tags").off(eventName).on(eventName, function () {
		photo.editTags([photo.getID()]);
	});

	sidebar.dom("#tags .tag").off(eventName).on(eventName, function () {
		sidebar.triggerSearch($(this).text());
	});

	sidebar.dom("#tags .tag span").off(eventName).on(eventName, function () {
		photo.deleteTag(photo.getID(), $(this).data("index"));
	});

	sidebar.dom("#edit_license").off(eventName).on(eventName, function () {
		if (visible.photo()) photo.setLicense(photo.getID());else if (visible.album()) album.setLicense(album.getID());
	});

	sidebar.dom("#edit_sorting").off(eventName).on(eventName, function () {
		album.setSorting(album.getID());
	});

	sidebar.dom(".attr_location").off(eventName).on(eventName, function () {
		sidebar.triggerSearch($(this).text());
	});
};

/**
 * @param {string} search_string
 * @returns {void}
 */
sidebar.triggerSearch = function (search_string) {
	// If public search is disabled -> do nothing
	if (lychee.publicMode && !lychee.public_search) {
		// Do not display an error -> just do nothing to not confuse the user
		return;
	}

	search.json = null;
	// We're either logged in or public search is allowed
	lychee.goto("search/" + encodeURIComponent(search_string));
};

/**
 * @returns {boolean}
 */
sidebar.keepSidebarVisible = function () {
	var v = sessionStorage.getItem("keepSidebarVisible");
	return v !== null ? v === "true" : false;
};

/**
 * @param {boolean} is_user_initiated - indicates if the user requested to
 *                                      toggle and hence the new state shall
 *                                      be saved in session storage
 * @returns {void}
 */
sidebar.toggle = function (is_user_initiated) {
	if (visible.sidebar() || visible.sidebarbutton()) {
		header.dom(".button--info").toggleClass("active");
		lychee.content.toggleClass("content--sidebar");
		lychee.imageview.toggleClass("image--sidebar");
		if (typeof view !== "undefined") view.album.content.justify(album.json ? album.json.photos : []);
		sidebar.dom().toggleClass("active");
		photo.updateSizeLivePhotoDuringAnimation();

		if (is_user_initiated) sessionStorage.setItem("keepSidebarVisible", visible.sidebar() ? "true" : "false");
	}
};

/**
 * Attributes/Values inside the sidebar are selectable by default.
 * Selection needs to be deactivated to prevent an unwanted selection
 * while using multiselect.
 *
 * @param {boolean} [selectable=true]
 * @returns {void}
 */
sidebar.setSelectable = function () {
	var selectable = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : true;

	if (selectable) sidebar.dom().removeClass("notSelectable");else sidebar.dom().addClass("notSelectable");
};

/**
 * @param {string} attr - selector of attribute without the `attr_` prefix
 * @param {?string} value - a `null` value is replaced by the empty string
 * @param {boolean} [dangerouslySetInnerHTML=false]
 *
 * @returns {void}
 */
sidebar.changeAttr = function (attr) {
	var value = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : "";
	var dangerouslySetInnerHTML = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : false;

	if (!attr) return;
	if (!value) value = "";

	// TODO: Don't use our home-brewed `escapeHTML` method; use `jQuery#text` instead
	// Escape value
	if (!dangerouslySetInnerHTML) value = lychee.escapeHTML(value);

	sidebar.dom(".attr_" + attr).html(value);
};

/**
 * @param {string} attr - selector of attribute without the `attr_` prefix
 * @returns {void}
 */
sidebar.hideAttr = function (attr) {
	sidebar.dom(".attr_" + attr).closest("tr").hide();
};

/**
 * Converts integer seconds into "hours:minutes:seconds".
 *
 * TODO: Consider to make this method part of `lychee.locale`.
 *
 * @param {(number|string)} d
 * @returns {string}
 */
sidebar.secondsToHMS = function (d) {
	d = Number(d);
	var h = Math.floor(d / 3600);
	var m = Math.floor(d % 3600 / 60);
	var s = Math.floor(d % 60);

	return (h > 0 ? h.toString() + "h" : "") + (m > 0 ? m.toString() + "m" : "") + (s > 0 || h === 0 && m === 0 ? s.toString() + "s" : "");
};

/**
 * @typedef Section
 *
 * @property {string}       title
 * @property {number}       type
 * @property {SectionRow[]} rows
 */

/**
 * @typedef SectionRow
 *
 * @property {string}            title
 * @property {string}            kind
 * @property {(string|string[])} value
 * @property {boolean}           [editable]
 */

/**
 * @param {?Photo} data
 * @returns {Section[]}
 */
sidebar.createStructure.photo = function (data) {
	if (!data) return [];

	var editable = typeof album !== "undefined" ? album.isUploadable() : false;
	var hasExif = !!data.taken_at || !!data.make || !!data.model || !!data.shutter || !!data.aperture || !!data.focal || !!data.iso;
	// Attributes for geo-position are nullable floats.
	// The geo-position 0°00'00'', 0°00'00'' at zero altitude is very unlikely
	// but valid (it's south of the coast of Ghana in the Atlantic)
	// So we must not calculate the sum and compare for zero.
	var hasLocation = data.longitude !== null || data.latitude !== null || data.altitude !== null;
	var structure = {};
	var isPublic = "";
	var isVideo = data.type && data.type.indexOf("video") > -1;
	var license = void 0;

	// Set the license string for a photo
	switch (data.license) {
		// if the photo doesn't have a license
		case "none":
			license = "";
			break;
		// Localize All Rights Reserved
		case "reserved":
			license = lychee.locale["PHOTO_RESERVED"];
			break;
		// Display anything else that's set
		default:
			license = data.license;
			break;
	}

	// Set value for public
	switch (data.is_public) {
		case 0:
			isPublic = lychee.locale["PHOTO_SHR_NO"];
			break;
		case 1:
			isPublic = lychee.locale["PHOTO_SHR_PHT"];
			break;
		case 2:
			isPublic = lychee.locale["PHOTO_SHR_ALB"];
			break;
		default:
			isPublic = "-";
			break;
	}

	structure.basics = {
		title: lychee.locale["PHOTO_BASICS"],
		type: sidebar.types.DEFAULT,
		rows: [{ title: lychee.locale["PHOTO_TITLE"], kind: "title", value: data.title, editable: editable }, { title: lychee.locale["PHOTO_UPLOADED"], kind: "uploaded", value: lychee.locale.printDateTime(data.created_at) }, { title: lychee.locale["PHOTO_DESCRIPTION"], kind: "description", value: data.description ? data.description : "", editable: editable }]
	};

	structure.image = {
		title: lychee.locale[isVideo ? "PHOTO_VIDEO" : "PHOTO_IMAGE"],
		type: sidebar.types.DEFAULT,
		rows: [{ title: lychee.locale["PHOTO_SIZE"], kind: "size", value: lychee.locale.printFilesizeLocalized(data.size_variants.original.filesize) }, { title: lychee.locale["PHOTO_FORMAT"], kind: "type", value: data.type }, {
			title: lychee.locale["PHOTO_RESOLUTION"],
			kind: "resolution",
			value: data.size_variants.original.width + " x " + data.size_variants.original.height
		}]
	};

	if (isVideo) {
		if (data.size_variants.original.width === 0 || data.size_variants.original.height === 0) {
			// Remove the "Resolution" line if we don't have the data.
			structure.image.rows.splice(-1, 1);
		}

		// We overload the database, storing duration (in full seconds) in
		// "aperture" and frame rate (floating point with three digits after
		// the decimal point) in "focal".
		if (data.aperture) {
			structure.image.rows.push({ title: lychee.locale["PHOTO_DURATION"], kind: "duration", value: sidebar.secondsToHMS(data.aperture) });
		}
		if (data.focal) {
			structure.image.rows.push({ title: lychee.locale["PHOTO_FPS"], kind: "fps", value: data.focal + " fps" });
		}
	}

	// Always create tags section - behaviour for editing
	// tags handled when constructing the html code for tags

	// TODO: IDE warns, that `value` is not property and `rows` is missing; the tags should actually be stored in a row for consistency
	// TODO: Consider to NOT call `build.tags` here, but simply pass the plain JSON. `build.tags` should be called in `sidebar.render` below
	structure.tags = {
		title: lychee.locale["PHOTO_TAGS"],
		type: sidebar.types.TAGS,
		value: build.tags(data.tags),
		editable: editable
	};

	// Only create EXIF section when EXIF data available
	if (hasExif) {
		structure.exif = {
			title: lychee.locale["PHOTO_CAMERA"],
			type: sidebar.types.DEFAULT,
			rows: isVideo ? [{ title: lychee.locale["PHOTO_CAPTURED"], kind: "takedate", value: lychee.locale.printDateTime(data.taken_at) }, { title: lychee.locale["PHOTO_MAKE"], kind: "make", value: data.make }, { title: lychee.locale["PHOTO_TYPE"], kind: "model", value: data.model }] : [{ title: lychee.locale["PHOTO_CAPTURED"], kind: "takedate", value: lychee.locale.printDateTime(data.taken_at) }, { title: lychee.locale["PHOTO_MAKE"], kind: "make", value: data.make }, { title: lychee.locale["PHOTO_TYPE"], kind: "model", value: data.model }, { title: lychee.locale["PHOTO_LENS"], kind: "lens", value: data.lens }, { title: lychee.locale["PHOTO_SHUTTER"], kind: "shutter", value: data.shutter }, { title: lychee.locale["PHOTO_APERTURE"], kind: "aperture", value: data.aperture }, { title: lychee.locale["PHOTO_FOCAL"], kind: "focal", value: data.focal }, { title: lychee.locale["PHOTO_ISO"], kind: "iso", value: data.iso }]
		};
	} else {
		structure.exif = {};
	}

	structure.sharing = {
		title: lychee.locale["PHOTO_SHARING"],
		type: sidebar.types.DEFAULT,
		rows: [{ title: lychee.locale["PHOTO_SHR_PLUBLIC"], kind: "public", value: isPublic }]
	};

	structure.license = {
		title: lychee.locale["PHOTO_REUSE"],
		type: sidebar.types.DEFAULT,
		rows: [{ title: lychee.locale["PHOTO_LICENSE"], kind: "license", value: license, editable: editable }]
	};

	if (hasLocation) {
		structure.location = {
			title: lychee.locale["PHOTO_LOCATION"],
			type: sidebar.types.DEFAULT,
			rows: [{
				title: lychee.locale["PHOTO_LATITUDE"],
				kind: "latitude",
				value: data.latitude ? DecimalToDegreeMinutesSeconds(data.latitude, true) : ""
			}, {
				title: lychee.locale["PHOTO_LONGITUDE"],
				kind: "longitude",
				value: data.longitude ? DecimalToDegreeMinutesSeconds(data.longitude, false) : ""
			},
			// No point in displaying sub-mm precision; 10cm is more than enough.
			{
				title: lychee.locale["PHOTO_ALTITUDE"],
				kind: "altitude",
				value: data.altitude ? (Math.round(data.altitude * 10) / 10).toString() + "m" : ""
			}, {
				title: lychee.locale["PHOTO_LOCATION"],
				kind: "location",
				// Explode location string into an array to keep street, city etc. separate
				// TODO: We should consider to keep the components apart on the server-side and send an structured object to the front-end.
				value: data.location ? data.location.split(",").map(function (item) {
					return item.trim();
				}) : ""
			}]
		};
		if (data.img_direction !== null) {
			// No point in display sub-degree precision.
			structure.location.rows.push({
				title: lychee.locale["PHOTO_IMGDIRECTION"],
				kind: "imgDirection",
				value: Math.round(data.img_direction).toString() + "°"
			});
		}
	} else {
		structure.location = {};
	}

	// Construct all parts of the structure
	var structure_ret = [structure.basics, structure.image, structure.tags, structure.exif, structure.location, structure.license];

	if (!lychee.publicMode) {
		structure_ret.push(structure.sharing);
	}

	return structure_ret;
};

/**
 * @param {(Album|TagAlbum|SmartAlbum)} data
 * @returns {Section[]}
 */
sidebar.createStructure.album = function (data) {
	if (!data) return [];

	var editable = album.isUploadable();
	var structure = {};
	var isPublic = data.is_public ? lychee.locale["ALBUM_SHR_YES"] : lychee.locale["ALBUM_SHR_NO"];
	var requiresLink = data.requires_link ? lychee.locale["ALBUM_SHR_YES"] : lychee.locale["ALBUM_SHR_NO"];
	var isDownloadable = data.is_downloadable ? lychee.locale["ALBUM_SHR_YES"] : lychee.locale["ALBUM_SHR_NO"];
	var isShareButtonVisible = data.is_share_button_visible ? lychee.locale["ALBUM_SHR_YES"] : lychee.locale["ALBUM_SHR_NO"];
	var hasPassword = data.has_password ? lychee.locale["ALBUM_SHR_YES"] : lychee.locale["ALBUM_SHR_NO"];
	var license = "";
	var sorting = "";

	// Set license string
	switch (data.license) {
		case "none":
			license = ""; // consistency
			break;
		case "reserved":
			license = lychee.locale["ALBUM_RESERVED"];
			break;
		default:
			license = data.license;
			break;
	}

	if (!lychee.publicMode) {
		if (!data.sorting) {
			sorting = lychee.locale["DEFAULT"];
		} else {
			sorting = data.sorting.column + " " + data.sorting.order;
		}
	}

	structure.basics = {
		title: lychee.locale["ALBUM_BASICS"],
		type: sidebar.types.DEFAULT,
		rows: [{ title: lychee.locale["ALBUM_TITLE"], kind: "title", value: data.title, editable: editable }, { title: lychee.locale["ALBUM_DESCRIPTION"], kind: "description", value: data.description ? data.description : "", editable: editable }]
	};

	if (album.isTagAlbum()) {
		structure.basics.rows.push({ title: lychee.locale["ALBUM_SHOW_TAGS"], kind: "showtags", value: data.show_tags, editable: editable });
	}

	var videoCount = data.photos.reduce(function (count, photo) {
		return count + (photo.type.indexOf("video") > -1 ? 1 : 0);
	}, 0);

	structure.album = {
		title: lychee.locale["ALBUM_ALBUM"],
		type: sidebar.types.DEFAULT,
		rows: [{ title: lychee.locale["ALBUM_CREATED"], kind: "created", value: lychee.locale.printDateTime(data.created_at) }]
	};
	if (data.albums && data.albums.length > 0) {
		structure.album.rows.push({ title: lychee.locale["ALBUM_SUBALBUMS"], kind: "subalbums", value: data.albums.length });
	}
	if (data.photos) {
		if (data.photos.length - videoCount > 0) {
			structure.album.rows.push({ title: lychee.locale["ALBUM_IMAGES"], kind: "images", value: data.photos.length - videoCount });
		}
	}
	if (videoCount > 0) {
		structure.album.rows.push({ title: lychee.locale["ALBUM_VIDEOS"], kind: "videos", value: videoCount });
	}

	if (data.photos && sorting !== "") {
		structure.album.rows.push({ title: lychee.locale["ALBUM_ORDERING"], kind: "sorting", value: sorting, editable: editable });
	}

	structure.share = {
		title: lychee.locale["ALBUM_SHARING"],
		type: sidebar.types.DEFAULT,
		rows: [{ title: lychee.locale["ALBUM_PUBLIC"], kind: "public", value: isPublic }, { title: lychee.locale["ALBUM_HIDDEN"], kind: "hidden", value: requiresLink }, { title: lychee.locale["ALBUM_DOWNLOADABLE"], kind: "downloadable", value: isDownloadable }, { title: lychee.locale["ALBUM_SHARE_BUTTON_VISIBLE"], kind: "share_button_visible", value: isShareButtonVisible }, { title: lychee.locale["ALBUM_PASSWORD"], kind: "password", value: hasPassword }]
	};

	if (data.owner_name) {
		structure.share.rows.push({ title: lychee.locale["ALBUM_OWNER"], kind: "owner", value: data.owner_name });
	}

	structure.license = {
		title: lychee.locale["ALBUM_REUSE"],
		type: sidebar.types.DEFAULT,
		rows: [{ title: lychee.locale["ALBUM_LICENSE"], kind: "license", value: license, editable: editable }]
	};

	// Construct all parts of the structure
	var structure_ret = [structure.basics, structure.album, structure.license];
	if (!lychee.publicMode) {
		structure_ret.push(structure.share);
	}

	return structure_ret;
};

/**
 * @param {Section[]} structure
 * @returns {boolean} - true if the passed structure contains a "location" section
 */
sidebar.has_location = function (structure) {
	var _has_location = false;

	structure.forEach(function (section) {
		if (section.title === lychee.locale["PHOTO_LOCATION"]) {
			_has_location = true;
		}
	});

	return _has_location;
};

/**
 * @param {Section[]} structure
 * @returns {string} - HTML
 */
sidebar.render = function (structure) {
	/**
  * @param {Section} section
  * @returns {string}
  */
	var renderDefault = function renderDefault(section) {
		var _html = "\n\t\t\t\t <div class='sidebar__divider'>\n\t\t\t\t\t <h1>" + section.title + "</h1>\n\t\t\t\t </div>\n\t\t\t\t <table>\n\t\t\t\t ";

		if (section.title === lychee.locale["PHOTO_LOCATION"]) {
			var _has_latitude = section.rows.findIndex(function (row) {
				return row.kind === "latitude" && row.value;
			}) !== -1;
			var _has_longitude = section.rows.findIndex(function (row) {
				return row.kind === "longitude" && row.value;
			}) !== -1;
			var idxLocation = section.rows.findIndex(function (row) {
				return row.kind === "location";
			});
			// Do not show location if not enabled
			if (idxLocation !== -1 && (lychee.publicMode === true && !lychee.location_show_public || !lychee.location_show)) {
				section.rows.splice(idxLocation, 1);
			}
			// Show map if we have coordinates
			if (_has_latitude && _has_longitude && lychee.map_display) {
				_html += "\n\t\t\t\t\t\t <div id=\"leaflet_map_single_photo\"></div>\n\t\t\t\t\t\t ";
			}
		}

		section.rows.forEach(function (row) {
			var value = row.value;

			// show only rows which have a value or are editable
			if (!(value === "" || value == null) || row.editable === true) {
				// Wrap span-element around value for easier selecting on change
				if (Array.isArray(row.value)) {
					value = row.value.reduce(
					/**
      * @param {string} prev
      * @param {string} cur
      */
					function (prev, cur) {
						// Add separator if needed
						if (prev !== "") {
							prev += lychee.html(_templateObject27, row.kind);
						}
						return prev + lychee.html(_templateObject28, row.kind, cur);
					}, "");
				} else {
					value = lychee.html(_templateObject29, row.kind, value);
				}

				// Add edit-icon to the value when editable
				if (row.editable === true) value += " " + build.editIcon("edit_" + row.kind);

				_html += lychee.html(_templateObject30, row.title, value);
			}
		});

		_html += "\n\t\t\t\t </table>\n\t\t\t\t ";

		return _html;
	};

	/**
  * @param {Section} section
  * @returns {string}
  */
	var renderTags = function renderTags(section) {
		var _html = "";
		var editable = "";

		// TODO: IDE warns me that the `Section` has no properties `editable` nor `value`; cause of the problem is that the section `tags` is built differently, see above
		// Add edit-icon to the value when editable
		if (section.editable === true) editable = build.editIcon("edit_tags");

		_html += lychee.html(_templateObject31, section.title, section.title.toLowerCase(), section.value, editable);

		return _html;
	};

	var html = "";

	structure.forEach(function (section) {
		if (section.type === sidebar.types.DEFAULT) html += renderDefault(section);else if (section.type === sidebar.types.TAGS) html += renderTags(section);
	});

	return html;
};

/**
 * Converts a decimal degree into integer degree, minutes and seconds.
 *
 * TODO: Consider to make this method part of `lychee.locale`.
 *
 * @param {number}  decimal
 * @param {boolean} type    - indicates if the passed decimal indicates a
 *                            latitude (`true`) or a longitude (`false`)
 * @returns {string}
 */
function DecimalToDegreeMinutesSeconds(decimal, type) {
	var d = Math.abs(decimal);
	var degrees = 0;
	var minutes = 0;
	var seconds = 0;
	var direction = void 0;

	// absolute value of decimal must be smaller than 180;
	if (d > 180) {
		return "";
	}

	// set direction; north assumed
	if (type && decimal < 0) {
		direction = "S";
	} else if (!type && decimal < 0) {
		direction = "W";
	} else if (!type) {
		direction = "E";
	} else {
		direction = "N";
	}

	//get degrees
	degrees = Math.floor(d);

	//get seconds
	seconds = (d - degrees) * 3600;

	//get minutes
	minutes = Math.floor(seconds / 60);

	//reset seconds
	seconds = Math.floor(seconds - minutes * 60);

	return degrees + "° " + minutes + "' " + seconds + '" ' + direction;
}

/**
 * @description This module takes care of the map view of a full album and its sub-albums.
 */

/**
 * @typedef MapProvider
 * @property {string} layer - URL pattern for map tile
 * @property {string} attribution - HTML with attribution
 */

var map_provider_layer_attribution = {
	/**
  * @type {MapProvider}
  */
	Wikimedia: {
		layer: "https://maps.wikimedia.org/osm-intl/{z}/{x}/{y}{r}.png",
		attribution: '<a href="https://wikimediafoundation.org/wiki/Maps_Terms_of_Use">Wikimedia</a>'
	},
	/**
  * @type {MapProvider}
  */
	"OpenStreetMap.org": {
		layer: "https://{s}.tile.osm.org/{z}/{x}/{y}.png",
		attribution: '&copy; <a href="https://osm.org/copyright">OpenStreetMap</a> contributors'
	},
	/**
  * @type {MapProvider}
  */
	"OpenStreetMap.de": {
		layer: "https://{s}.tile.openstreetmap.de/{z}/{x}/{y}.png ",
		attribution: '&copy; <a href="https://osm.org/copyright">OpenStreetMap</a> contributors'
	},
	/**
  * @type {MapProvider}
  */
	"OpenStreetMap.fr": {
		layer: "https://{s}.tile.openstreetmap.fr/osmfr/{z}/{x}/{y}.png ",
		attribution: '&copy; <a href="https://osm.org/copyright">OpenStreetMap</a> contributors'
	},
	/**
  * @type {MapProvider}
  */
	RRZE: {
		layer: "https://{s}.osm.rrze.fau.de/osmhd/{z}/{x}/{y}.png",
		attribution: '&copy; <a href="https://osm.org/copyright">OpenStreetMap</a> contributors'
	}
};

var mapview = {
	/** @type {?L.Map} */
	map: null,
	photoLayer: null,
	trackLayer: null,
	/** @type {(?LatLngBounds|?number[][])} */
	bounds: null,
	/** @type {?string} */
	albumID: null,
	/** @type {?string} */
	map_provider: null
};

/**
 * @typedef MapPhotoEntry
 *
 * @property {number} [lat] - latitude
 * @property {number} [lng] - longitude
 * @property {string} [thumbnail] - URL to the thumbnail
 * @property {string} [thumbnail2x] - URL to the high-res thumbnail
 * @property {string} url - URL to the small size-variant; quite a misnomer
 * @property {string} url2x - URL to the small, high-res size-variant; quite a misnomer
 * @property {string} name - the title of the photo
 * @property {string} taken_at - the takedate of the photo, formatted as a locale string
 * @property {string} albumID - the album ID
 * @property {string} photoID - the photo ID
 */

/**
 * @returns {boolean}
 */
mapview.isInitialized = function () {
	return !(mapview.map === null || mapview.photoLayer === null);
};

/**
 * @param {?string} _albumID
 * @param {string} _albumTitle
 *
 * @returns {void}
 */
mapview.title = function (_albumID, _albumTitle) {
	switch (_albumID) {
		case SmartAlbumID.STARRED:
			lychee.setTitle(lychee.locale["STARRED"], false);
			break;
		case SmartAlbumID.PUBLIC:
			lychee.setTitle(lychee.locale["PUBLIC"], false);
			break;
		case SmartAlbumID.RECENT:
			lychee.setTitle(lychee.locale["RECENT"], false);
			break;
		case SmartAlbumID.UNSORTED:
			lychee.setTitle(lychee.locale["UNSORTED"], false);
			break;
		case null:
			lychee.setTitle(lychee.locale["ALBUMS"], false);
			break;
		default:
			lychee.setTitle(_albumTitle ? _albumTitle : lychee.locale["UNTITLED"], false);
			break;
	}
};

/**
 * Opens the map view
 *
 * @param {?string} [albumID=null]
 * @returns {void}
 */
mapview.open = function () {
	var albumID = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : null;

	// If map functionality is disabled -> do nothing
	if (!lychee.map_display || lychee.publicMode === true && !lychee.map_display_public) {
		loadingBar.show("error", lychee.locale["ERROR_MAP_DEACTIVATED"]);
		return;
	}

	lychee.animate($("#mapview"), "fadeIn");
	$("#mapview").show();
	header.setMode("map");

	mapview.albumID = albumID;

	// initialize container only once
	if (!mapview.isInitialized()) {
		// Leaflet searches for icon in same directory as js file -> paths need
		// to be overwritten
		delete L.Icon.Default.prototype._getIconUrl;
		L.Icon.Default.mergeOptions({
			iconRetinaUrl: "img/marker-icon-2x.png",
			iconUrl: "img/marker-icon.png",
			shadowUrl: "img/marker-shadow.png"
		});

		// Set initial view to (0,0)
		mapview.map = L.map("leaflet_map_full").setView([0.0, 0.0], 13);

		L.tileLayer(map_provider_layer_attribution[lychee.map_provider].layer, {
			attribution: map_provider_layer_attribution[lychee.map_provider].attribution
		}).addTo(mapview.map);

		mapview.map_provider = lychee.map_provider;
	} else {
		if (mapview.map_provider !== lychee.map_provider) {
			// removew all layers
			mapview.map.eachLayer(function (layer) {
				mapview.map.removeLayer(layer);
			});

			L.tileLayer(map_provider_layer_attribution[lychee.map_provider].layer, {
				attribution: map_provider_layer_attribution[lychee.map_provider].attribution
			}).addTo(mapview.map);

			mapview.map_provider = lychee.map_provider;
		} else {
			// Mapview has already shown data -> remove only photoLayer and trackLayer showing photos and tracks
			mapview.photoLayer.clear();
			if (mapview.trackLayer !== null) {
				mapview.map.removeLayer(mapview.trackLayer);
			}
		}

		// Reset bounds
		mapview.bounds = null;
	}

	// Define how the photos on the map should look like
	mapview.photoLayer = L.photo.cluster().on("click", function (e) {
		/** @type {MapPhotoEntry} */
		var photo = {
			photoID: e.layer.photo.photoID,
			albumID: e.layer.photo.albumID,
			name: e.layer.photo.name,
			url: e.layer.photo.url,
			url2x: e.layer.photo.url2x,
			taken_at: lychee.locale.printDateTime(e.layer.photo.taken_at)
		};
		var template = "";

		// Retina version if available
		if (photo.url2x !== "") {
			template = template.concat('<img class="image-leaflet-popup" src="{url}" ', 'srcset="{url} 1x, {url2x} 2x" ', 'data-album-id="{albumID}" data-id="{photoID}"/><div><h1>{name}</h1><span title="Camera Date">', build.iconic("camera-slr"), "</span><p>{taken_at}</p></div>");
		} else {
			template = template.concat('<img class="image-leaflet-popup" src="{url}" ', 'data-album-id="{albumID}" data-id="{photoID}"/><div><h1>{name}</h1><span title="Camera Date">', build.iconic("camera-slr"), "</span><p>{taken_at}</p></div>");
		}

		e.layer.bindPopup(L.Util.template(template, photo), {
			minWidth: 400
		}).openPopup();
	});

	// Adjusts zoom and position of map to show all images
	var updateZoom = function updateZoom() {
		if (mapview.bounds) {
			mapview.map.fitBounds(mapview.bounds);
		} else {
			mapview.map.fitWorld();
		}
	};

	/**
  * Adds photos to the map.
  *
  * @param {(Album|TagAlbum|PositionData)} album
  *
  * @returns {void}
  */
	var addContentsToMap = function addContentsToMap(album) {
		// check if empty
		if (!album.photos) return;

		/** @type {MapPhotoEntry[]} */
		var photos = [];

		/** @type {?number} */
		var min_lat = null;
		/** @type {?number} */
		var min_lng = null;
		/** @type {?number} */
		var max_lat = null;
		/** @type {?number} */
		var max_lng = null;

		album.photos.forEach(
		/** @param {Photo} element */function (element) {
			if (element.latitude || element.longitude) {
				photos.push({
					lat: element.latitude,
					lng: element.longitude,
					thumbnail: element.size_variants.thumb !== null ? element.size_variants.thumb.url : "img/placeholder.png",
					thumbnail2x: element.size_variants.thumb2x !== null ? element.size_variants.thumb2x.url : null,
					url: element.size_variants.small !== null ? element.size_variants.small.url : element.url,
					url2x: element.size_variants.small2x !== null ? element.size_variants.small2x.url : null,
					name: element.title,
					taken_at: element.taken_at,
					albumID: element.album_id,
					photoID: element.id
				});

				// Update min/max lat/lng
				if (min_lat === null || min_lat > element.latitude) {
					min_lat = element.latitude;
				}
				if (min_lng === null || min_lng > element.longitude) {
					min_lng = element.longitude;
				}
				if (max_lat === null || max_lat < element.latitude) {
					max_lat = element.latitude;
				}
				if (max_lng === null || max_lng < element.longitude) {
					max_lng = element.longitude;
				}
			}
		});

		// Add Photos to map
		mapview.photoLayer.add(photos).addTo(mapview.map);

		if (photos.length > 0) {
			// update map bounds
			var dist_lat = max_lat - min_lat;
			var dist_lng = max_lng - min_lng;
			mapview.bounds = [[min_lat - 0.1 * dist_lat, min_lng - 0.1 * dist_lng], [max_lat + 0.1 * dist_lat, max_lng + 0.1 * dist_lng]];
		}

		// add track
		if (album.track_url) {
			mapview.trackLayer = new L.GPX(album.track_url, {
				async: true,
				marker_options: {
					startIconUrl: null,
					endIconUrl: null,
					shadowUrl: null
				}
			}).on("error", function (e) {
				lychee.error(lycche.locale["ERROR_GPX"] + e.err);
			}).on("loaded", function (e) {
				if (photos.length === 0) {
					// no photos, update map bound to center track
					mapview.bounds = e.target.getBounds();
					updateZoom();
				}
			});
			mapview.trackLayer.addTo(mapview.map);
		}

		// Update Zoom and Position
		updateZoom();
	};

	/**
  * Calls backend, retrieves information about photos and displays them.
  *
  * This function is called recursively to retrieve data for sub-albums.
  * Possible enhancement could be to only have a single ajax call.
  *
  * @param {?string} _albumID
  * @param {boolean} [_includeSubAlbums=true]
  */
	var getAlbumData = function getAlbumData(_albumID) {
		var _includeSubAlbums = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : true;

		/**
   * @param {PositionData} data
   */
		var successHandler = function successHandler(data) {
			addContentsToMap(data);
			mapview.title(_albumID, data.title);
		};

		if (_albumID !== "" && _albumID !== null) {
			// _albumID has been specified
			var params = {
				albumID: _albumID,
				includeSubAlbums: _includeSubAlbums
			};

			api.post("Album::getPositionData", params, successHandler);
		} else {
			// AlbumID is empty -> fetch all photos of all albums
			api.post("Albums::getPositionData", {}, successHandler);
		}
	};

	// If sub-albums are not requested and album.json already has all data,
	// we reuse it
	if (lychee.map_include_subalbums === false && album.json !== null && album.json.photos !== null) {
		addContentsToMap(album.json);
	} else {
		// Not all needed data has been  preloaded - we need to load everything
		getAlbumData(albumID, lychee.map_include_subalbums);
	}

	// Update Zoom and Position once more (for empty map)
	updateZoom();
};

/**
 * @returns {void}
 */
mapview.close = function () {
	// If map functionality is disabled -> do nothing
	if (!lychee.map_display) return;

	lychee.animate($("#mapview"), "fadeOut");
	$("#mapview").hide();
	header.setMode("album");

	// Make album focusable
	tabindex.makeFocusable(lychee.content);
};

/**
 * @param {jQuery} elem
 * @returns {void}
 */
mapview.goto = function (elem) {
	// If map functionality is disabled -> do nothing
	if (!lychee.map_display) return;

	var photoID = elem.attr("data-id");
	var albumID = elem.attr("data-album-id");

	if (albumID === "null") albumID = "unsorted";

	lychee.goto(albumID + "/" + photoID);
};

/**
 * @typedef {Object.<string, string>} Locale
 * @property {function} printFilesizeLocalized
 * @property {function} printDateTime
 * @property {function} printMonthYear
 */

lychee.locale = {
	USERNAME: "username",
	PASSWORD: "password",
	ENTER: "Enter",
	CANCEL: "Cancel",
	SIGN_IN: "Sign In",
	CLOSE: "Close",

	SETTINGS: "Settings",
	USERS: "Users",
	U2F: "U2F",
	NOTIFICATIONS: "Notifications",
	SHARING: "Sharing",
	CHANGE_LOGIN: "Change Login",
	CHANGE_SORTING: "Change Sorting",
	SET_DROPBOX: "Set Dropbox",
	ABOUT_LYCHEE: "About Lychee",
	DIAGNOSTICS: "Diagnostics",
	DIAGNOSTICS_GET_SIZE: "Request space usage",
	LOGS: "Show Logs",
	CLEAN_LOGS: "Clean Noise",
	SIGN_OUT: "Sign Out",
	UPDATE_AVAILABLE: "Update available!",
	MIGRATION_AVAILABLE: "Migration available!",
	CHECK_FOR_UPDATE: "Check for updates",
	DEFAULT_LICENSE: "Default License for new uploads:",
	SET_LICENSE: "Set License",
	SET_OVERLAY_TYPE: "Set Overlay",
	SET_MAP_PROVIDER: "Set OpenStreetMap tiles provider",
	SAVE_RISK: "Save my modifications, I accept the Risk!",
	MORE: "More",
	DEFAULT: "Default",

	SMART_ALBUMS: "Smart albums",
	SHARED_ALBUMS: "Shared albums",
	ALBUMS: "Albums",
	PHOTOS: "Pictures",
	SEARCH_RESULTS: "Search results",

	RENAME: "Rename",
	RENAME_ALL: "Rename All",
	MERGE: "Merge",
	MERGE_ALL: "Merge All",
	MAKE_PUBLIC: "Make Public",
	SHARE_ALBUM: "Share Album",
	SHARE_PHOTO: "Share Photo",
	SHARE_WITH: "Share with...",
	DOWNLOAD_ALBUM: "Download Album",
	ABOUT_ALBUM: "About Album",
	DELETE_ALBUM: "Delete Album",
	FULLSCREEN_ENTER: "Enter Fullscreen",
	FULLSCREEN_EXIT: "Exit Fullscreen",

	SHARING_ALBUM_USERS: "Share this album with users",
	SHARING_ALBUM_USERS_LONG_MESSAGE: "Select the users to share this album with",
	WAIT_FETCH_DATA: "Please wait while we get the data...",
	SHARING_ALBUM_USERS_NO_USERS: "There are no users to share the album with",

	DELETE_ALBUM_QUESTION: "Delete Album and Photos",
	KEEP_ALBUM: "Keep Album",
	DELETE_ALBUM_CONFIRMATION_1: "Are you sure you want to delete the album",
	DELETE_ALBUM_CONFIRMATION_2: "and all of the photos it contains? This action can't be undone!",

	DELETE_ALBUMS_QUESTION: "Delete Albums and Photos",
	KEEP_ALBUMS: "Keep Albums",
	DELETE_ALBUMS_CONFIRMATION_1: "Are you sure you want to delete all",
	DELETE_ALBUMS_CONFIRMATION_2: "selected albums and all of the photos they contain? This action can't be undone!",

	DELETE_UNSORTED_CONFIRM: "Are you sure you want to delete all photos from 'Unsorted'?<br>This action can't be undone!",
	CLEAR_UNSORTED: "Clear Unsorted",
	KEEP_UNSORTED: "Keep Unsorted",

	EDIT_SHARING: "Edit Sharing",
	MAKE_PRIVATE: "Make Private",

	CLOSE_ALBUM: "Close Album",
	CLOSE_PHOTO: "Close Photo",
	CLOSE_MAP: "Close Map",

	ADD: "Add",
	MOVE: "Move",
	MOVE_ALL: "Move All",
	DUPLICATE: "Duplicate",
	DUPLICATE_ALL: "Duplicate All",
	COPY_TO: "Copy to...",
	COPY_ALL_TO: "Copy All to...",
	DELETE: "Delete",
	DELETE_ALL: "Delete All",
	DOWNLOAD: "Download",
	DOWNLOAD_MEDIUM: "Download medium size",
	DOWNLOAD_SMALL: "Download small size",
	UPLOAD_PHOTO: "Upload Photo",
	IMPORT_LINK: "Import from Link",
	IMPORT_DROPBOX: "Import from Dropbox",
	IMPORT_SERVER: "Import from Server",
	NEW_ALBUM: "New Album",
	NEW_TAG_ALBUM: "New Tag Album",
	UPLOAD_TRACK: "Upload track",
	DELETE_TRACK: "Delete track",

	TITLE_NEW_ALBUM: "Enter a title for the new album:",
	UNTITLED: "Untitled",
	UNSORTED: "Unsorted",
	STARRED: "Starred",
	RECENT: "Recent",
	PUBLIC: "Public",
	NUM_PHOTOS: "Photos",

	CREATE_ALBUM: "Create Album",
	CREATE_TAG_ALBUM: "Create Tag Album",

	STAR_PHOTO: "Star Photo",
	STAR: "Star",
	UNSTAR: "Unstar",
	STAR_ALL: "Star Selected",
	UNSTAR_ALL: "Unstar Selected",
	TAGS: "Tags",
	TAGS_ALL: "Tags All",
	UNSTAR_PHOTO: "Unstar Photo",

	FULL_PHOTO: "Full Photo",
	ABOUT_PHOTO: "About Photo",
	DISPLAY_FULL_MAP: "Map",
	DIRECT_LINK: "Direct Link",
	DIRECT_LINKS: "Direct Links",
	QR_CODE: "QR Code",

	ALBUM_ABOUT: "About",
	ALBUM_BASICS: "Basics",
	ALBUM_TITLE: "Title",
	ALBUM_NEW_TITLE: "Enter a new title for this album:",
	ALBUMS_NEW_TITLE_1: "Enter a title for all",
	ALBUMS_NEW_TITLE_2: "selected albums:",
	ALBUM_SET_TITLE: "Set Title",
	ALBUM_DESCRIPTION: "Description",
	ALBUM_SHOW_TAGS: "Tags to show",
	ALBUM_NEW_DESCRIPTION: "Enter a new description for this album:",
	ALBUM_SET_DESCRIPTION: "Set Description",
	ALBUM_NEW_SHOWTAGS: "Enter tags of photos that will be visible in this album:",
	ALBUM_SET_SHOWTAGS: "Set tags to show",
	ALBUM_ALBUM: "Album",
	ALBUM_CREATED: "Created",
	ALBUM_IMAGES: "Images",
	ALBUM_VIDEOS: "Videos",
	ALBUM_SHARING: "Share",
	ALBUM_OWNER: "Owner",
	ALBUM_SHR_YES: "YES",
	ALBUM_SHR_NO: "No",
	ALBUM_PUBLIC: "Public",
	ALBUM_PUBLIC_EXPL: "Album can be viewed by others, subject to the restrictions below.",
	ALBUM_FULL: "Full size (v4 only)",
	ALBUM_FULL_EXPL: "Full size pictures are available",
	ALBUM_HIDDEN: "Hidden",
	ALBUM_HIDDEN_EXPL: "Only people with the direct link can view this album.",
	ALBUM_MARK_NSFW: "Mark album as sensitive",
	ALBUM_UNMARK_NSFW: "Unmark album as sensitive",
	ALBUM_NSFW: "Sensitive",
	ALBUM_NSFW_EXPL: "Album contains sensitive content.",
	ALBUM_DOWNLOADABLE: "Downloadable",
	ALBUM_DOWNLOADABLE_EXPL: "Visitors of your Lychee can download this album.",
	ALBUM_SHARE_BUTTON_VISIBLE: "Share button is visible",
	ALBUM_SHARE_BUTTON_VISIBLE_EXPL: "Display social media sharing links.",
	ALBUM_PASSWORD: "Password",
	ALBUM_PASSWORD_PROT: "Password protected",
	ALBUM_PASSWORD_PROT_EXPL: "Album only accessible with a valid password.",
	ALBUM_PASSWORD_REQUIRED: "This album is protected by a password. Enter the password below to view the photos of this album:",
	ALBUM_MERGE_1: "Are you sure you want to merge the album",
	ALBUM_MERGE_2: "into the album",
	ALBUMS_MERGE: "Are you sure you want to merge all selected albums into the album",
	MERGE_ALBUM: "Merge Albums",
	DONT_MERGE: "Don't Merge",
	ALBUM_MOVE_1: "Are you sure you want to move the album",
	ALBUM_MOVE_2: "into the album",
	ALBUMS_MOVE: "Are you sure you want to move all selected albums into the album",
	MOVE_ALBUMS: "Move Albums",
	NOT_MOVE_ALBUMS: "Don't Move",
	ROOT: "Root",
	ALBUM_REUSE: "Reuse",
	ALBUM_LICENSE: "License",
	ALBUM_SET_LICENSE: "Set License",
	ALBUM_LICENSE_HELP: "Need help choosing?",
	ALBUM_LICENSE_NONE: "None",
	ALBUM_RESERVED: "All Rights Reserved",
	ALBUM_SET_ORDER: "Set Order",
	ALBUM_ORDERING: "Order by",

	PHOTO_ABOUT: "About",
	PHOTO_BASICS: "Basics",
	PHOTO_TITLE: "Title",
	PHOTO_NEW_TITLE: "Enter a new title for this photo:",
	PHOTO_SET_TITLE: "Set Title",
	PHOTO_UPLOADED: "Uploaded",
	PHOTO_DESCRIPTION: "Description",
	PHOTO_NEW_DESCRIPTION: "Enter a new description for this photo:",
	PHOTO_SET_DESCRIPTION: "Set Description",
	PHOTO_NEW_LICENSE: "Add a License",
	PHOTO_SET_LICENSE: "Set License",
	PHOTO_REUSE: "Reuse",
	PHOTO_LICENSE: "License",
	PHOTO_LICENSE_HELP: "Need help choosing?",
	PHOTO_LICENSE_NONE: "None",
	PHOTO_RESERVED: "All Rights Reserved",
	PHOTO_IMAGE: "Image",
	PHOTO_VIDEO: "Video",
	PHOTO_SIZE: "Size",
	PHOTO_FORMAT: "Format",
	PHOTO_RESOLUTION: "Resolution",
	PHOTO_DURATION: "Duration",
	PHOTO_FPS: "Frame rate",
	PHOTO_TAGS: "Tags",
	PHOTO_NOTAGS: "No Tags",
	PHOTO_NEW_TAGS: "Enter your tags for this photo. You can add multiple tags by separating them with a comma:",
	PHOTO_NEW_TAGS_1: "Enter your tags for all",
	PHOTO_NEW_TAGS_2: "selected photos. Existing tags will be overwritten. You can add multiple tags by separating them with a comma:",
	PHOTO_SET_TAGS: "Set Tags",
	PHOTO_CAMERA: "Camera",
	PHOTO_CAPTURED: "Captured",
	PHOTO_MAKE: "Make",
	PHOTO_TYPE: "Type/Model",
	PHOTO_LENS: "Lens",
	PHOTO_SHUTTER: "Shutter Speed",
	PHOTO_APERTURE: "Aperture",
	PHOTO_FOCAL: "Focal Length",
	PHOTO_ISO: "ISO",
	PHOTO_SHARING: "Sharing",
	PHOTO_SHR_PLUBLIC: "Public",
	PHOTO_SHR_ALB: "Yes (Album)",
	PHOTO_SHR_PHT: "Yes (Photo)",
	PHOTO_SHR_NO: "No",
	PHOTO_DELETE: "Delete Photo",
	PHOTO_KEEP: "Keep Photo",
	PHOTO_DELETE_1: "Are you sure you want to delete the photo",
	PHOTO_DELETE_2: "? This action can't be undone!",
	PHOTO_DELETE_ALL_1: "Are you sure you want to delete all",
	PHOTO_DELETE_ALL_2: "selected photo? This action can't be undone!",
	PHOTOS_NEW_TITLE_1: "Enter a title for all",
	PHOTOS_NEW_TITLE_2: "selected photos:",
	PHOTO_MAKE_PRIVATE_ALBUM: "This photo is located in a public album. To make this photo private or public, edit the visibility of the associated album.",
	PHOTO_SHOW_ALBUM: "Show Album",
	PHOTO_PUBLIC: "Public",
	PHOTO_PUBLIC_EXPL: "Photo can be viewed by others, subject to the restrictions below.",
	PHOTO_FULL: "Original",
	PHOTO_FULL_EXPL: "Full-resolution picture is available.",
	PHOTO_HIDDEN: "Hidden",
	PHOTO_HIDDEN_EXPL: "Only people with the direct link can view this photo.",
	PHOTO_DOWNLOADABLE: "Downloadable",
	PHOTO_DOWNLOADABLE_EXPL: "Visitors of your gallery can download this photo.",
	PHOTO_SHARE_BUTTON_VISIBLE: "Share button is visible",
	PHOTO_SHARE_BUTTON_VISIBLE_EXPL: "Display social media sharing links.",
	PHOTO_PASSWORD_PROT: "Password protected",
	PHOTO_PASSWORD_PROT_EXPL: "Photo only accessible with a valid password.",
	PHOTO_EDIT_SHARING_TEXT: "The sharing properties of this photo will be changed to the following:",
	PHOTO_NO_EDIT_SHARING_TEXT: "Because this photo is located in a public album, it inherits that album's visibility settings.  Its current visibility is shown below for informational purposes only.",
	PHOTO_EDIT_GLOBAL_SHARING_TEXT: "The visibility of this photo can be fine-tuned using global Lychee settings. Its current visibility is shown below for informational purposes only.",
	PHOTO_SHARING_CONFIRM: "Save",
	PHOTO_LOCATION: "Location",
	PHOTO_LATITUDE: "Latitude",
	PHOTO_LONGITUDE: "Longitude",
	PHOTO_ALTITUDE: "Altitude",
	PHOTO_IMGDIRECTION: "Direction",

	LOADING: "Loading",
	ERROR: "Error",
	ERROR_TEXT: "Whoops, it looks like something went wrong. Please reload the site and try again!",
	ERROR_DB_1: "Unable to connect to host database because access was denied. Double-check your host, username and password and ensure that access from your current location is permitted.",
	ERROR_DB_2: "Unable to create the database. Double-check your host, username and password and ensure that the specified user has the rights to modify and add content to the database.",
	ERROR_CONFIG_FILE: "Unable to save this configuration. Permission denied in <b>'data/'</b>. Please set the read, write and execute rights for others in <b>'data/'</b> and <b>'uploads/'</b>. Take a look at the readme for more information.",
	ERROR_UNKNOWN: "Something unexpected happened. Please try again and check your installation and server. Take a look at the readme for more information.",
	ERROR_LOGIN: "Unable to save login. Please try again with another username and password!",
	ERROR_MAP_DEACTIVATED: "Map functionality has been deactivated under settings.",
	ERROR_SEARCH_DEACTIVATED: "Search functionality has been deactivated under settings.",
	SUCCESS: "OK",
	RETRY: "Retry",

	SETTINGS_WARNING: "Changing these advanced settings can be harmful to the stability, security and performance of this application. You should only modify them if you are sure of what you are doing.",
	SETTINGS_SUCCESS_LOGIN: "Login Info updated.",
	SETTINGS_SUCCESS_SORT: "Sorting order updated.",
	SETTINGS_SUCCESS_DROPBOX: "Dropbox Key updated.",
	SETTINGS_SUCCESS_LANG: "Language updated",
	SETTINGS_SUCCESS_LAYOUT: "Layout updated",
	SETTINGS_SUCCESS_IMAGE_OVERLAY: "EXIF Overlay setting updated",
	SETTINGS_SUCCESS_PUBLIC_SEARCH: "Public search updated",
	SETTINGS_SUCCESS_LICENSE: "Default license updated",
	SETTINGS_SUCCESS_MAP_DISPLAY: "Map display settings updated",
	SETTINGS_SUCCESS_MAP_DISPLAY_PUBLIC: "Map display settings for public albums updated",
	SETTINGS_SUCCESS_MAP_PROVIDER: "Map provider settings updated",

	U2F_NOT_SUPPORTED: "U2F not supported. Sorry.",
	U2F_NOT_SECURE: "Environment not secured. U2F not available.",
	U2F_REGISTER_KEY: "Register new device.",
	U2F_REGISTRATION_SUCCESS: "Registration successful!",
	U2F_AUTHENTIFICATION_SUCCESS: "Authentication successful!",
	U2F_CREDENTIALS: "Credentials",
	U2F_CREDENTIALS_DELETED: "Credentials deleted!",

	NEW_PHOTOS_NOTIFICATION: "Send new photos notification emails.",
	SETTINGS_SUCCESS_NEW_PHOTOS_NOTIFICATION: "New photos notification updated",
	USER_EMAIL_INSTRUCTION: "Add your email below to enable receiving email notifications.<br />To stop receiving emails, simply remove your email below.",

	SETTINGS_SUCCESS_CSS: "CSS updated",
	SETTINGS_SUCCESS_UPDATE: "Settings updated with success",

	DB_INFO_TITLE: "Enter your database connection details below:",
	DB_INFO_HOST: "Database Host (optional)",
	DB_INFO_USER: "Database Username",
	DB_INFO_PASSWORD: "Database Password",
	DB_INFO_TEXT: "Lychee will create its own database. If required, you can enter the name of an existing database instead:",
	DB_NAME: "Database Name (optional)",
	DB_PREFIX: "Table prefix (optional)",
	DB_CONNECT: "Connect",

	LOGIN_TITLE: "Enter a username and password for your installation:",
	LOGIN_USERNAME: "New Username",
	LOGIN_PASSWORD: "New Password",
	LOGIN_PASSWORD_CONFIRM: "Confirm Password",
	LOGIN_CREATE: "Create Login",

	PASSWORD_TITLE: "Enter your current username and password:",
	USERNAME_CURRENT: "Current Username",
	PASSWORD_CURRENT: "Current Password",
	PASSWORD_TEXT: "Your username and password will be changed to the following:",
	PASSWORD_CHANGE: "Change Login",

	EDIT_SHARING_TITLE: "Edit Sharing",
	EDIT_SHARING_TEXT: "The sharing-properties of this album will be changed to the following:",
	SHARE_ALBUM_TEXT: "This album will be shared with the following properties:",
	ALBUM_SHARING_CONFIRM: "Save",

	SORT_ALBUM_BY_1: "Sort albums by",
	SORT_ALBUM_BY_2: "in an",
	SORT_ALBUM_BY_3: "order.",

	SORT_ALBUM_SELECT_1: "Creation Time",
	SORT_ALBUM_SELECT_2: "Title",
	SORT_ALBUM_SELECT_3: "Description",
	SORT_ALBUM_SELECT_4: "Public",
	SORT_ALBUM_SELECT_5: "Latest Take Date",
	SORT_ALBUM_SELECT_6: "Oldest Take Date",

	SORT_PHOTO_BY_1: "Sort photos by",
	SORT_PHOTO_BY_2: "in an",
	SORT_PHOTO_BY_3: "order.",

	SORT_PHOTO_SELECT_1: "Upload Time",
	SORT_PHOTO_SELECT_2: "Take Date",
	SORT_PHOTO_SELECT_3: "Title",
	SORT_PHOTO_SELECT_4: "Description",
	SORT_PHOTO_SELECT_5: "Public",
	SORT_PHOTO_SELECT_6: "Star",
	SORT_PHOTO_SELECT_7: "Photo Format",

	SORT_ASCENDING: "Ascending",
	SORT_DESCENDING: "Descending",
	SORT_CHANGE: "Change Sorting",

	DROPBOX_TITLE: "Set Dropbox Key",
	DROPBOX_TEXT: "In order to import photos from your Dropbox, you need a valid drop-ins app key from <a href='https://www.dropbox.com/developers/apps/create'>their website</a>. Generate yourself a personal key and enter it below:",

	LANG_TEXT: "Change Lychee language for:",
	LANG_TITLE: "Change Language",

	CSS_TEXT: "Personalize your CSS:",
	CSS_TITLE: "Change CSS",

	LAYOUT_TYPE: "Layout of photos:",
	LAYOUT_SQUARES: "Square thumbnails",
	LAYOUT_JUSTIFIED: "With aspect, justified",
	LAYOUT_UNJUSTIFIED: "With aspect, unjustified",
	SET_LAYOUT: "Change layout",
	PUBLIC_SEARCH_TEXT: "Public search allowed:",

	IMAGE_OVERLAY_TEXT: "Display image overlay by default:",

	OVERLAY_TYPE: "Photo overlay:",
	OVERLAY_NONE: "None",
	OVERLAY_EXIF: "EXIF data",
	OVERLAY_DESCRIPTION: "Description",
	OVERLAY_DATE: "Date taken",

	MAP_PROVIDER: "Provider of OpenStreetMap tiles:",
	MAP_PROVIDER_WIKIMEDIA: "Wikimedia",
	MAP_PROVIDER_OSM_ORG: "OpenStreetMap.org (no retina)",
	MAP_PROVIDER_OSM_DE: "OpenStreetMap.de (no retina)",
	MAP_PROVIDER_OSM_FR: "OpenStreetMap.fr (no retina)",
	MAP_PROVIDER_RRZE: "University of Erlangen, Germany (only retina)",

	MAP_DISPLAY_TEXT: "Enable maps (provided by OpenStreetMap):",
	MAP_DISPLAY_PUBLIC_TEXT: "Enable maps for public albums (provided by OpenStreetMap):",
	MAP_INCLUDE_SUBALBUMS_TEXT: "Include photos of subalbums on map:",
	LOCATION_DECODING: "Decode GPS data into location name",
	LOCATION_SHOW: "Show location name",
	LOCATION_SHOW_PUBLIC: "Show location name for public mode",

	NSFW_VISIBLE_TEXT_1: "Make Sensitive albums visible by default.",
	NSFW_VISIBLE_TEXT_2: "If the album is public, it is still accessible, just hidden from the view and <b>can be revealed by pressing <kbd>H</kbd></b>.",
	SETTINGS_SUCCESS_NSFW_VISIBLE: "Default sensitive album visibility updated with success.",

	VIEW_NO_RESULT: "No results",
	VIEW_NO_PUBLIC_ALBUMS: "No public albums",
	VIEW_NO_CONFIGURATION: "No configuration",
	VIEW_PHOTO_NOT_FOUND: "Photo not found",

	NO_TAGS: "No Tags",

	UPLOAD_MANAGE_NEW_PHOTOS: "You can now manage your new photo(s).",
	UPLOAD_COMPLETE: "Upload complete",
	UPLOAD_COMPLETE_FAILED: "Failed to upload one or more photos.",
	UPLOAD_IMPORTING: "Importing",
	UPLOAD_IMPORTING_URL: "Importing URL",
	UPLOAD_UPLOADING: "Uploading",
	UPLOAD_FINISHED: "Finished",
	UPLOAD_PROCESSING: "Processing",
	UPLOAD_FAILED: "Failed",
	UPLOAD_FAILED_ERROR: "Upload failed. Server returned an error!",
	UPLOAD_FAILED_WARNING: "Upload failed. Server returned a warning!",
	UPLOAD_SKIPPED: "Skipped",
	UPLOAD_UPDATED: "Updated",
	UPLOAD_IMPORT_SKIPPED_DUPLICATE: "This photo has been skipped because it's already in your library.",
	UPLOAD_IMPORT_RESYNCED_DUPLICATE: "This photo has been skipped because it's already in your library, but its metadata has been updated.",
	UPLOAD_ERROR_CONSOLE: "Please take a look at the console of your browser for further details.",
	UPLOAD_UNKNOWN: "Server returned an unknown response. Please take a look at the console of your browser for further details.",
	UPLOAD_ERROR_UNKNOWN: "Upload failed. Server returned an unkown error!",
	UPLOAD_ERROR_POSTSIZE: "Upload failed. The PHP post_max_size limit is too small!",
	UPLOAD_ERROR_FILESIZE: "Upload failed. The PHP upload_max_filesize limit is too small!",
	UPLOAD_IN_PROGRESS: "Lychee is currently uploading!",
	UPLOAD_IMPORT_WARN_ERR: "The import has been finished, but returned warnings or errors. Please take a look at the log (Settings -> Show Log) for further details.",
	UPLOAD_IMPORT_COMPLETE: "Import complete",
	UPLOAD_IMPORT_INSTR: "Please enter the direct link to a photo to import it:",
	UPLOAD_IMPORT: "Import",
	UPLOAD_IMPORT_SERVER: "Importing from server",
	UPLOAD_IMPORT_SERVER_FOLD: "Folder empty or no readable files to process. Please take a look at the log (Settings -> Show Log) for further details.",
	UPLOAD_IMPORT_SERVER_INSTR: "This action will import all photos, folders and sub-folders which are located in the following directory. The <b>original files will be deleted</b> after the import when possible.",
	UPLOAD_ABSOLUTE_PATH: "Absolute path to directory",
	UPLOAD_IMPORT_SERVER_EMPT: "Could not start import because the folder was empty!",

	ABOUT_SUBTITLE: "Self-hosted photo-management done right",
	ABOUT_DESCRIPTION: "is a free photo-management tool, which runs on your server or web-space. Installing is a matter of seconds. Upload, manage and share photos like from a native application. Lychee comes with everything you need and all your photos are stored securely.",

	URL_COPY_TO_CLIPBOARD: "Copy to clipboard",
	URL_COPIED_TO_CLIPBOARD: "Copied URL to clipboard!",
	PHOTO_DIRECT_LINKS_TO_IMAGES: "Direct links to image files:",
	PHOTO_MEDIUM: "Medium",
	PHOTO_MEDIUM_HIDPI: "Medium HiDPI",
	PHOTO_SMALL: "Thumb",
	PHOTO_SMALL_HIDPI: "Thumb HiDPI",
	PHOTO_THUMB: "Square thumb",
	PHOTO_THUMB_HIDPI: "Square thumb HiDPI",
	PHOTO_LIVE_VIDEO: "Video part of live-photo",
	PHOTO_VIEW: "Lychee Photo View:",

	ERROR_GPX: "Error loading GPX file: ",

	/**
  * Formats a number representing a filesize in bytes as a localized string
  * @param {!number} filesize
  * @returns {string} A formatted and localized string
  */
	printFilesizeLocalized: function printFilesizeLocalized(filesize) {
		var suffix = [" B", " kB", " MB", " GB"];
		var i = 0;
		// Sic! We check if the number is larger than 1000 but divide by 1024 by intention
		// We aim at a number which has at most 3 non-decimal digits, i.e. the result shall be in the interval
		// [1000/1024, 1000) = [0.977, 1000)  (lower bound included, upper bound excluded)
		while (filesize >= 1000.0 && i < suffix.length) {
			filesize = filesize / 1024.0;
			i++;
		}

		// The number of decimal digits is anti-proportional to the number of non-decimal digits
		// In total, there shall always be three digits
		if (filesize >= 100.0) {
			filesize = Math.round(filesize);
		} else if (filesize >= 10.0) {
			filesize = Math.round(filesize * 10.0) / 10.0;
		} else {
			filesize = Math.round(filesize * 100.0) / 100.0;
		}

		return Number(filesize).toLocaleString() + suffix[i];
	},

	/**
  * Converts a JSON encoded date/time into a localized string relative to
  * the original timezone
  *
  * The localized string uses the JS "medium" verbosity.
  * The precise definition of "medium verbosity" depends on the current locale, but for Western languages this
  * means that the date portion is fully printed with digits (e.g. something like 03/30/2021 for English,
  * 30/03/2021 for French and 30.03.2021 for German), and that the time portion is printed with a resolution of
  * seconds with two digits for all parts either in 24h or 12h scheme (e.g. something like 02:24:13pm for English
  * and 14:24:13 for French/German).
  *
  * @param {?string} jsonDateTime
  * @returns {string} A formatted and localized time
  */
	printDateTime: function printDateTime(jsonDateTime) {
		if (typeof jsonDateTime !== "string" || jsonDateTime === "") return "";

		// Unfortunately, the built-in JS Date object is rather dumb.
		// It is only required to support the timezone of the runtime
		// environment and UTC.
		// Moreover, the method `toLocalString` may or may not convert
		// the represented time to the timezone of the runtime environment
		// before formatting it as a string.
		// However, we want to keep the printed time in the original timezone,
		// because this facilitates human interaction with a photo.
		// To this end we apply a "dirty" trick here.
		// We first cut off any explicit timezone indication from the JSON
		// string and only pass a date/time of the form `YYYYMMDDThhmmss` to
		// `Date`.
		// `Date` is required to interpret those time values according to the
		// local timezone (see [MDN Web Docs - Date Time String Format](https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/Date/parse#date_time_string_format)).
		// Most likely, the resulting `Date` object will represent the
		// wrong instant in time (given in seconds since epoch), but we only
		// want to call `toLocalString` which is fine and don't do any time
		// arithmetics.
		// Then we add the original timezone to the string manually.
		var splitDateTime = /^(\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}([,.]\d{1,6})?)([-Z+])(\d{2}:\d{2})?$/.exec(jsonDateTime);
		// The capturing groups are:
		//  - 0: the whole string
		//  - 1: the whole date/time segment incl. fractional seconds
		//  - 2: the fractional seconds (if present)
		//  - 3: the timezone separator, i.e. "Z", "-" or "+" (if present)
		//  - 4: the absolute timezone offset without the sign (if present)
		console.assert(splitDateTime.length === 5, "'jsonDateTime' is not formatted acc. to ISO 8601; passed string was: " + jsonDateTime);
		var locale = "default"; // use the user's browser settings
		var format = { dateStyle: "medium", timeStyle: "medium" };
		var result = new Date(splitDateTime[1]).toLocaleString(locale, format);
		if (splitDateTime[3] === "Z" || splitDateTime[4] === "00:00") {
			result += " UTC";
		} else {
			result += " UTC" + splitDateTime[3] + splitDateTime[4];
		}
		return result;
	},

	/**
  * Converts a JSON encoded date/time into a localized string which only displays month and year.
  *
  * The month is printed as a shortened word with 3/4 letters, the year is printed with 4 digits (e.g. something like
  * "Aug 2020" in English or "Août 2020" in French).
  *
  * @param {?string} jsonDateTime
  * @returns {string} A formatted and localized month and year
  */
	printMonthYear: function printMonthYear(jsonDateTime) {
		if (typeof jsonDateTime !== "string" || jsonDateTime === "") return "";
		var locale = "default"; // use the user's browser settings
		var format = { month: "short", year: "numeric" };
		return new Date(jsonDateTime).toLocaleDateString(locale, format);
	}
};

/**
 * @description Helper class to manage tabindex
 */

var tabindex = {
	offset_for_header: 100,
	next_tab_index: 100
};

/**
 * @param {jQuery} elem
 * @returns {void}
 */
tabindex.saveSettings = function (elem) {
	if (!lychee.enable_tabindex) return;

	// Todo: Make shorter notation
	// Get all elements which have a tabindex
	// TODO @Hallenser: What did you intended by the TODO above? It seems as if the jQuery selector is already as short as possible?
	var tmp = elem.find("[tabindex]");

	// iterate over all elements and set tabindex to stored value (i.e. make is not focusable)
	tmp.each(
	/**
  * @param {number} i - the index
  * @param {Element} e - the HTML element
  * @this {Element} - identical to `e`
  */
	function (i, e) {
		// TODO: shorter notation
		// TODO @Hallenser: What do you intended by the TODO `short notation`? Moreover: Why do we use `this` and `e`? They refer to the identical instance of a HTML element.
		var a = $(e).attr("tabindex");
		$(this).data("tabindex-saved", a);
	});
};

tabindex.restoreSettings = function (elem) {
	if (!lychee.enable_tabindex) return;

	// Todo: Make shorter notation
	// Get all elements which have a tabindex
	// TODO @Hallenser: What did you intended by the TODO above? It seems as if the jQuery selector is already as short as possible?
	var tmp = $(elem).find("[tabindex]");

	// iterate over all elements and set tabindex to stored value (i.e. make is not focussable)
	tmp.each(
	/**
  * @param {number} i - the index
  * @param {Element} e - the HTML element
  * @this {Element} - identical to `e`
  */
	function (i, e) {
		// TODO: shorter notation
		// TODO @Hallenser: What do you intended by the TODO `short notation`? Moreover: Why do we use `this` and `e`? They refer to the identical instance of a HTML element.
		var a = $(e).data("tabindex-saved");
		$(e).attr("tabindex", a);
	});
};

/**
 * @param {jQuery} elem
 * @param {boolean} [saveFocusElement=false]
 * @returns {void}
 */
tabindex.makeUnfocusable = function (elem) {
	var saveFocusElement = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : false;

	if (!lychee.enable_tabindex) return;

	// Todo: Make shorter notation
	// Get all elements which have a tabindex
	var tmp = elem.find("[tabindex]");

	// iterate over all elements and set tabindex to -1 (i.e. make is not focussable)
	tmp.each(
	/**
  * @param {number} i - the index
  * @param {Element} e - the HTML element
  */
	function (i, e) {
		$(e).attr("tabindex", "-1");
		// Save which element had focus before we make it unfocusable
		if (saveFocusElement && $(e).is(":focus")) {
			$(e).data("tabindex-focus", true);
			// Remove focus
			$(e).blur();
		}
	});

	// Disable input fields
	elem.find("input").attr("disabled", "disabled");
};

/**
 * @param {jQuery} elem
 * @param {boolean} [restoreFocusElement=false]
 * @returns {void}
 */
tabindex.makeFocusable = function (elem) {
	var restoreFocusElement = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : false;

	if (!lychee.enable_tabindex) return;

	// Todo: Make shorter notation
	// Get all elements which have a tabindex
	var tmp = elem.find("[data-tabindex]");

	// iterate over all elements and set tabindex to stored value
	tmp.each(
	/**
  * @param {number} i
  * @param {Element} e
  */
	function (i, e) {
		$(e).attr("tabindex", $(e).data("tabindex"));
		// restore focus element if wanted
		if (restoreFocusElement) {
			if ($(e).data("tabindex-focus") && lychee.active_focus_on_page_load) {
				$(e).focus();
				$(e).removeData("tabindex-focus");
			}
		}
	});

	// Enable input fields
	elem.find("input").removeAttr("disabled");
};

/**
 * @returns {number}
 */
tabindex.get_next_tab_index = function () {
	tabindex.next_tab_index = tabindex.next_tab_index + 1;

	return tabindex.next_tab_index - 1;
};

/**
 * @returns {void}
 */
tabindex.reset = function () {
	tabindex.next_tab_index = tabindex.offset_for_header;
};

/**
 * @typedef {Object} LycheeException
 * @property {string} message     the message of the exception
 * @property {string} exception   the (base) name of the exception class; in developer mode the backend reports the full class name, in productive mode only the base name
 * @property {string} [file]      the file name where the exception has been thrown; only in developer mode
 * @property {number} [line]      the line number where the exception has been thrown; only in developer mode
 * @property {Array} [trace]      the backtrace; only in developer mode
 * @property {?LycheeException} [previous_exception] the previous exception, if any; only in developer mode
 */

/**
 * @typedef Photo
 *
 * @property {string}       id
 * @property {string}       title
 * @property {?string}      description
 * @property {string[]}     tags
 * @property {number}       is_public
 * @property {?string}      type
 * @property {?string}      iso
 * @property {?string}      aperture
 * @property {?string}      make
 * @property {?string}      model
 * @property {?string}      lens
 * @property {?string}      shutter
 * @property {?string}      focal
 * @property {?number}      latitude
 * @property {?number}      longitude
 * @property {?number}      altitude
 * @property {?number}      img_direction
 * @property {?string}      location
 * @property {?string}      taken_at
 * @property {?string}      taken_at_orig_tz
 * @property {boolean}      is_starred
 * @property {?string}      live_photo_url
 * @property {?string}      album_id
 * @property {string}       checksum
 * @property {string}       license
 * @property {string}       created_at
 * @property {string}       updated_at
 * @property {?string}      live_photo_content_id
 * @property {?string}      live_photo_checksum
 * @property {SizeVariants} size_variants
 * @property {boolean}      is_downloadable
 * @property {boolean}      is_share_button_visible
 * @property {?string}      [next_photo_id]
 * @property {?string}      [previous_photo_id]
 */

/**
 * @typedef SizeVariants
 *
 * @property {SizeVariant}  original
 * @property {?SizeVariant} medium2x
 * @property {?SizeVariant} medium
 * @property {?SizeVariant} small2x
 * @property {?SizeVariant} small
 * @property {?SizeVariant} thumb2x
 * @property {?SizeVariant} thumb
 */

/**
 * @typedef SizeVariant
 *
 * @property {number} type
 * @property {string} url
 * @property {number} width
 * @property {number} height
 * @property {number} filesize
 */

/**
 * @typedef SortingCriterion
 *
 * @property {string} column
 * @property {string} order
 */

/**
 * @typedef Album
 *
 * @property {string}  id
 * @property {string}  parent_id
 * @property {string}  created_at
 * @property {string}  updated_at
 * @property {string}  title
 * @property {?string} description
 * @property {string}  license
 * @property {Photo[]} photos
 * @property {Album[]} [albums]
 * @property {?string} cover_id
 * @property {?Thumb}  thumb
 * @property {string}  [owner_name] optional, only shown in authenticated mode
 * @property {boolean} is_public
 * @property {boolean} is_downloadable
 * @property {boolean} is_share_button_visible
 * @property {boolean} is_nsfw
 * @property {boolean} grants_full_photo
 * @property {boolean} requires_link
 * @property {boolean} has_password
 * @property {boolean} has_albums
 * @property {?string} min_taken_at
 * @property {?string} max_taken_at
 * @property {?SortingCriterion} sorting
 */

/**
 * @typedef TagAlbum
 *
 * @property {string}   id
 * @property {string}   created_at
 * @property {string}   updated_at
 * @property {string}   title
 * @property {?string}  description
 * @property {string[]} show_tags
 * @property {Photo[]}  photos
 * @property {?Thumb}   thumb
 * @property {string}   [owner_name] optional, only shown in authenticated mode
 * @property {boolean}  is_public
 * @property {boolean}  is_downloadable
 * @property {boolean}  is_share_button_visible
 * @property {boolean}  is_nsfw
 * @property {boolean}  grants_full_photo
 * @property {boolean}  requires_link
 * @property {boolean}  has_password
 * @property {?string}  min_taken_at
 * @property {?string}  max_taken_at
 * @property {?SortingCriterion}  sorting
 * @property {boolean}  is_tag_album always true
 */

/**
 * @typedef SmartAlbum
 *
 * @property {string}  id
 * @property {string}  title
 * @property {Photo[]} photos
 * @property {?Thumb}  thumb
 * @property {boolean} is_public
 * @property {boolean} is_downloadable
 * @property {boolean} is_share_button_visible
 */

/**
 * @typedef Thumb
 *
 * @property {string}  id
 * @property {string}  type
 * @property {string}  thumb
 * @property {?string} thumb2x
 */

/**
 * @typedef SharingInfo
 *
 * DTO returned by `Sharing::list`
 *
 * @property {{id: number, album_id: string, user_id: number, username: string, title: string}[]} shared
 * @property {{id: string, title: string}[]}                                                      albums
 * @property {{id: number, username: string}[]}                                                   users
 */

/**
 * @typedef SearchResult
 *
 * DTO returned by `Search::run`
 *
 * @property {Album[]}    albums
 * @property {TagAlbum[]} tag_albums
 * @property {Photo[]}    photos
 * @property {string}     checksum - checksum of the search result to
 *                                   efficiently determine if the result has
 *                                   changed since the last time
 */

/**
 * @typedef Albums
 *
 * @property {SmartAlbums} smart_albums
 * @property {TagAlbum[]}  tag_albums
 * @property {Album[]}     albums
 * @property {Album[]}     shared_albums
 */

/**
 * @typedef SmartAlbums
 *
 * @property {?SmartAlbum} unsorted
 * @property {?SmartAlbum} starred
 * @property {?SmartAlbum} public
 * @property {?SmartAlbum} recent
 */

/**
 * The IDs of the built-in, smart albums.
 *
 * @type {Readonly<{RECENT: string, STARRED: string, PUBLIC: string, UNSORTED: string}>}
 */
var SmartAlbumID = Object.freeze({
	UNSORTED: "unsorted",
	STARRED: "starred",
	PUBLIC: "public",
	RECENT: "recent"
});

/**
 * @typedef User
 *
 * @property {number}  id
 * @property {string}  username
 * @property {?string} email
 * @property {boolean} may_upload
 * @property {boolean} is_locked
 */

/**
 * @typedef WebAuthnCredential
 *
 * @property {string} id
 */

/**
 * @typedef PositionData
 *
 * @property {?string} id - album ID
 * @property {?string} title - album title
 * @property {Photo[]} photos
 * @property {?string} track_url - URL to GPX track
 */

/**
 * @typedef EMailData
 *
 * @property {?string} email
 */

/**
 * @typedef ConfigSetting
 *
 * @property {number} id
 * @property {string} key
 * @property {?string} value - TODO: this should have the correct type depending on `type_range`
 * @property {string} cat
 * @property {string} type_range
 * @property {number} confidentiality - `0`: public setting, `2`: informational, `3`: admin only
 * @property {string} description
 */

/**
 * @typedef LogEntry
 *
 * @property {number} id
 * @property {string} created_at
 * @property {string} updated_at
 * @property {string} type
 * @property {string} function
 * @property {number} line
 * @property {string} text
 */

/**
 * @typedef DiagnosticInfo
 *
 * @property {string[]} errors
 * @property {string[]} infos
 * @property {string[]} configs
 * @property {number} update - `0`: not on master branch; `1`: up-to-date; `2`: not up-to-date; `3`: requires migration
 */

/**
 * @typedef FrameSettings
 *
 * @property {number} refresh
 */

/**
 * @typedef InitializationData
 *
 * @property {number} status - `1`: unauthenticated, `2`: authenticated
 * @property {boolean} admin
 * @property {boolean} may_upload
 * @property {boolean} is_locked
 * @property {number} update_json - version number of latest available update
 * @property {boolean} update_available
 * @property {Object.<string, string>} locale
 * @property {string} [username] - only if user is not the admin; TODO: Change that
 * @property {ConfigurationData} config
 * @property {DeviceConfiguration} config_device
 */

/**
 * @typedef ConfigurationData
 *
 * @property {string}   album_subtitle_type
 * @property {string}   check_for_updates       - actually a boolean
 * @property {string}   [default_license]
 * @property {string}   [delete_imported]       - actually a boolean
 * @property {string}   downloadable            - actually a boolean
 * @property {string}   [dropbox_key]
 * @property {string}   editor_enabled          - actually a boolean
 * @property {string}   full_photo              - actually a boolean
 * @property {string}   image_overlay_type
 * @property {string}   landing_page_enable     - actually a boolean
 * @property {string}   lang
 * @property {string[]} lang_available
 * @property {string}   layout                  - actually a number: `0`, `1` or `2`
 * @property {string}   [location]
 * @property {string}   location_decoding       - actually a boolean
 * @property {string}   location_show           - actually a boolean
 * @property {string}   location_show_public    - actually a boolean
 * @property {string}   map_display             - actually a boolean
 * @property {string}   map_display_direction   - actually a boolean
 * @property {string}   map_display_public      - actually a boolean
 * @property {string}   map_include_subalbums   - actually a boolean
 * @property {string}   map_provider
 * @property {string}   new_photos_notification - actually a boolean
 * @property {string}   nsfw_blur               - actually a boolean
 * @property {string}   nsfw_visible            - actually a boolean
 * @property {string}   nsfw_warning            - actually a boolean
 * @property {string}   nsfw_warning_admin      - actually a boolean
 * @property {string}   public_photos_hidden    - actually a boolean
 * @property {string}   public_search           - actually a boolean
 * @property {string}   share_button_visible    - actually a boolean
 * @property {string}   [skip_duplicates]       - actually a boolean
 * @property {SortingCriterion} sorting_albums
 * @property {SortingCriterion} sorting_photos
 * @property {string}   swipe_tolerance_x       - actually a number
 * @property {string}   swipe_tolerance_y       - actually a number
 * @property {string}   upload_processing_limit - actually a number
 * @property {string}   version                 - actually a number
 */

/**
 * @typedef DeviceConfiguration
 *
 * @property {string}  device_type
 * @property {boolean} header_auto_hide
 * @property {boolean} active_focus_on_page_load
 * @property {boolean} enable_button_visibility
 * @property {boolean} enable_button_share
 * @property {boolean} enable_button_archive
 * @property {boolean} enable_button_move
 * @property {boolean} enable_button_trash
 * @property {boolean} enable_button_fullscreen
 * @property {boolean} enable_button_download
 * @property {boolean} enable_button_add
 * @property {boolean} enable_button_more
 * @property {boolean} enable_button_rotate
 * @property {boolean} enable_close_tab_on_esc
 * @property {boolean} enable_contextmenu_header
 * @property {boolean} hide_content_during_imgview
 * @property {boolean} enable_tabindex
 */

/**
 * The JSON object for incremental reports sent by the
 * back-end within a streamed response.
 *
 * @typedef ImportReport
 *
 * @property {string} type - indicates the type of report;
 *                           `'progress'`: {@link ImportProgressReport},
 *                           `'event'`: {@link ImportEventReport}
 */

/**
 * The JSON object for cumulative progress reports sent by the
 * back-end within a streamed response.
 *
 * @typedef ImportProgressReport
 *
 * @property {string} type - `'progress'`
 * @property {string} path
 * @property {number} progress
 */

/**
 * The JSON object for events sent by the back-end within a streamed response.
 *
 * @typedef ImportEventReport
 *
 * @property {string} type - `'event'`
 * @property {string} subtype - the subtype of event; equals the base name of the exception class which caused this event on the back-end
 * @property {number} severity - either `'debug'`, `'info'`, `'notice'`, `'warning'`, `'error'`, `'critical'` or `'emergency'`
 * @property {?string} path - the path to the affected file or directory
 * @property {string} message - a message text
 */

(function (window, factory) {
	var basicContext = factory(window, window.document);
	window.basicContext = basicContext;
	if ((typeof module === "undefined" ? "undefined" : _typeof(module)) == "object" && module.exports) {
		module.exports = basicContext;
	}
})(window, function l(window, document) {
	var ITEM = "item",
	    SEPARATOR = "separator";

	var dom = function dom() {
		var elem = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : "";

		return document.querySelector(".basicContext " + elem);
	};

	var valid = function valid() {
		var item = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};

		var emptyItem = Object.keys(item).length === 0 ? true : false;

		if (emptyItem === true) item.type = SEPARATOR;
		if (item.type == null) item.type = ITEM;
		if (item.class == null) item.class = "";
		if (item.visible !== false) item.visible = true;
		if (item.icon == null) item.icon = null;
		if (item.title == null) item.title = "Undefined";

		// Add disabled class when item disabled
		if (item.disabled !== true) item.disabled = false;
		if (item.disabled === true) item.class += " basicContext__item--disabled";

		// Item requires a function when
		// it's not a separator and not disabled
		if (item.fn == null && item.type !== SEPARATOR && item.disabled === false) {
			console.warn("Missing fn for item '" + item.title + "'");
			return false;
		}

		return true;
	};

	var buildItem = function buildItem(item, num) {
		var html = "",
		    span = "";

		// Parse and validate item
		if (valid(item) === false) return "";

		// Skip when invisible
		if (item.visible === false) return "";

		// Give item a unique number
		item.num = num;

		// Generate span/icon-element
		if (item.icon !== null) span = "<span class='basicContext__icon " + item.icon + "'></span>";

		// Generate item
		if (item.type === ITEM) {
			html = "\n\t\t       <tr class='basicContext__item " + item.class + "'>\n\t\t           <td class='basicContext__data' data-num='" + item.num + "'>" + span + item.title + "</td>\n\t\t       </tr>\n\t\t       ";
		} else if (item.type === SEPARATOR) {
			html = "\n\t\t       <tr class='basicContext__item basicContext__item--separator'></tr>\n\t\t       ";
		}

		return html;
	};

	var build = function build(items) {
		var html = "";

		html += "\n\t        <div class='basicContextContainer'>\n\t            <div class='basicContext'>\n\t                <table>\n\t                    <tbody>\n\t        ";

		items.forEach(function (item, i) {
			return html += buildItem(item, i);
		});

		html += "\n\t                    </tbody>\n\t                </table>\n\t            </div>\n\t        </div>\n\t        ";

		return html;
	};

	var getNormalizedEvent = function getNormalizedEvent() {
		var e = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};

		var pos = {
			x: e.clientX,
			y: e.clientY
		};

		if (e.type === "touchend" && (pos.x == null || pos.y == null)) {
			// We need to capture clientX and clientY from original event
			// when the event 'touchend' does not return the touch position

			var touches = e.changedTouches;

			if (touches != null && touches.length > 0) {
				pos.x = touches[0].clientX;
				pos.y = touches[0].clientY;
			}
		}

		// Position unknown
		if (pos.x == null || pos.x < 0) pos.x = 0;
		if (pos.y == null || pos.y < 0) pos.y = 0;

		return pos;
	};

	var getPosition = function getPosition(e, context) {
		// Get the click position
		var normalizedEvent = getNormalizedEvent(e);

		// Set the initial position
		var x = normalizedEvent.x,
		    y = normalizedEvent.y;

		var container = document.querySelector(".basicContextContainer");

		// Get size of browser
		var browserSize = {
			width: container.offsetWidth,
			height: container.offsetHeight
		};

		// Get size of context
		var contextSize = {
			width: context.offsetWidth,
			height: context.offsetHeight
		};

		// Fix position based on context and browser size
		if (x + contextSize.width > browserSize.width) x = x - (x + contextSize.width - browserSize.width);
		if (y + contextSize.height > browserSize.height) y = y - (y + contextSize.height - browserSize.height);

		// Make context scrollable and start at the top of the browser
		// when context is higher than the browser
		if (contextSize.height > browserSize.height) {
			y = 0;
			context.classList.add("basicContext--scrollable");
		}

		// Calculate the relative position of the mouse to the context
		var rx = normalizedEvent.x - x,
		    ry = normalizedEvent.y - y;

		return { x: x, y: y, rx: rx, ry: ry };
	};

	var bind = function bind() {
		var item = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};

		if (item.fn == null) return false;
		if (item.visible === false) return false;
		if (item.disabled === true) return false;

		dom("td[data-num='" + item.num + "']").onclick = item.fn;
		dom("td[data-num='" + item.num + "']").oncontextmenu = item.fn;

		return true;
	};

	var show = function show(items, e, fnClose, fnCallback) {
		// Build context
		var html = build(items);

		// Add context to the body
		document.body.insertAdjacentHTML("beforeend", html);

		// Cache the context
		var context = dom();

		// Calculate position
		var position = getPosition(e, context);

		// Set position
		context.style.left = position.x + "px";
		context.style.top = position.y + "px";
		context.style.transformOrigin = position.rx + "px " + position.ry + "px";
		context.style.opacity = 1;

		// Close fn fallback
		if (fnClose == null) fnClose = close;

		// Bind click on background
		context.parentElement.onclick = fnClose;
		context.parentElement.oncontextmenu = fnClose;

		// Bind click on items
		items.forEach(bind);

		// Do not trigger default event or further propagation
		if (typeof e.preventDefault === "function") e.preventDefault();
		if (typeof e.stopPropagation === "function") e.stopPropagation();

		// Call callback when a function
		if (typeof fnCallback === "function") fnCallback();

		return true;
	};

	var visible = function visible() {
		var elem = dom();

		return !(elem == null || elem.length === 0);
	};

	var close = function close() {
		if (visible() === false) return false;

		var container = document.querySelector(".basicContextContainer");

		container.parentElement.removeChild(container);

		return true;
	};

	return {
		ITEM: ITEM,
		SEPARATOR: SEPARATOR,
		show: show,
		visible: visible,
		close: close
	};
});