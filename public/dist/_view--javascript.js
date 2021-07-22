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
    _templateObject21 = _taggedTemplateLiteral(["<a class='color' data-color=\"rgb(", ",", ",", ")\" style=\"background-color:rgb(", " ", " ", ")\"></a>"], ["<a class='color' data-color=\"rgb(", ",", ",", ")\" style=\"background-color:rgb(", " ", " ", ")\"></a>"]),
    _templateObject22 = _taggedTemplateLiteral(["<div class='empty'>", "</div>"], ["<div class='empty'>", "</div>"]),
    _templateObject23 = _taggedTemplateLiteral(["<a class='", "'>$", "<span data-index='", "'>", "</span></a>"], ["<a class='", "'>$", "<span data-index='", "'>", "</span></a>"]),
    _templateObject24 = _taggedTemplateLiteral(["<a class='", "'>$", "</a>"], ["<a class='", "'>$", "</a>"]),
    _templateObject25 = _taggedTemplateLiteral(["<div class=\"users_view_line\">\n\t\t\t<p id=\"UserData", "\">\n\t\t\t<input name=\"id\" type=\"hidden\" value=\"", "\" />\n\t\t\t<input class=\"text\" name=\"username\" type=\"text\" value=\"$", "\" placeholder=\"username\" />\n\t\t\t<input class=\"text\" name=\"password\" type=\"text\" placeholder=\"new password\" />\n\t\t\t<span class=\"choice\" title=\"Allow uploads\">\n\t\t\t<label>\n\t\t\t<input type=\"checkbox\" name=\"upload\" />\n\t\t\t<span class=\"checkbox\"><svg class=\"iconic \"><use xlink:href=\"#check\"></use></svg></span>\n\t\t\t</label>\n\t\t\t</span>\n\t\t\t<span class=\"choice\" title=\"Restricted account\">\n\t\t\t<label>\n\t\t\t<input type=\"checkbox\" name=\"lock\" />\n\t\t\t<span class=\"checkbox\"><svg class=\"iconic \"><use xlink:href=\"#check\"></use></svg></span>\n\t\t\t</label>\n\t\t\t</span>\n\t\t\t</p>\n\t\t\t<a id=\"UserUpdate", "\"  class=\"basicModal__button basicModal__button_OK\">Save</a>\n\t\t\t<a id=\"UserDelete", "\"  class=\"basicModal__button basicModal__button_DEL\">Delete</a>\n\t\t</div>\n\t\t"], ["<div class=\"users_view_line\">\n\t\t\t<p id=\"UserData", "\">\n\t\t\t<input name=\"id\" type=\"hidden\" value=\"", "\" />\n\t\t\t<input class=\"text\" name=\"username\" type=\"text\" value=\"$", "\" placeholder=\"username\" />\n\t\t\t<input class=\"text\" name=\"password\" type=\"text\" placeholder=\"new password\" />\n\t\t\t<span class=\"choice\" title=\"Allow uploads\">\n\t\t\t<label>\n\t\t\t<input type=\"checkbox\" name=\"upload\" />\n\t\t\t<span class=\"checkbox\"><svg class=\"iconic \"><use xlink:href=\"#check\"></use></svg></span>\n\t\t\t</label>\n\t\t\t</span>\n\t\t\t<span class=\"choice\" title=\"Restricted account\">\n\t\t\t<label>\n\t\t\t<input type=\"checkbox\" name=\"lock\" />\n\t\t\t<span class=\"checkbox\"><svg class=\"iconic \"><use xlink:href=\"#check\"></use></svg></span>\n\t\t\t</label>\n\t\t\t</span>\n\t\t\t</p>\n\t\t\t<a id=\"UserUpdate", "\"  class=\"basicModal__button basicModal__button_OK\">Save</a>\n\t\t\t<a id=\"UserDelete", "\"  class=\"basicModal__button basicModal__button_DEL\">Delete</a>\n\t\t</div>\n\t\t"]),
    _templateObject26 = _taggedTemplateLiteral(["<div class=\"u2f_view_line\">\n\t\t\t<p id=\"CredentialData", "\">\n\t\t\t<input name=\"id\" type=\"hidden\" value=\"", "\" />\n\t\t\t<span class=\"text\">", "</span>\n\t\t\t<!--- <span class=\"choice\" title=\"Allow uploads\">\n\t\t\t<label>\n\t\t\t<input type=\"checkbox\" name=\"upload\" />\n\t\t\t<span class=\"checkbox\"><svg class=\"iconic \"><use xlink:href=\"#check\"></use></svg></span>\n\t\t\t</label>\n\t\t\t</span>\n\t\t\t<span class=\"choice\" title=\"Restricted account\">\n\t\t\t<label>\n\t\t\t<input type=\"checkbox\" name=\"lock\" />\n\t\t\t<span class=\"checkbox\"><svg class=\"iconic \"><use xlink:href=\"#check\"></use></svg></span>\n\t\t\t</label>\n\t\t\t</span>--->\n\t\t\t</p>\n\t\t\t<a id=\"CredentialDelete", "\"  class=\"basicModal__button basicModal__button_DEL\">Delete</a>\n\t\t</div>\n\t\t"], ["<div class=\"u2f_view_line\">\n\t\t\t<p id=\"CredentialData", "\">\n\t\t\t<input name=\"id\" type=\"hidden\" value=\"", "\" />\n\t\t\t<span class=\"text\">", "</span>\n\t\t\t<!--- <span class=\"choice\" title=\"Allow uploads\">\n\t\t\t<label>\n\t\t\t<input type=\"checkbox\" name=\"upload\" />\n\t\t\t<span class=\"checkbox\"><svg class=\"iconic \"><use xlink:href=\"#check\"></use></svg></span>\n\t\t\t</label>\n\t\t\t</span>\n\t\t\t<span class=\"choice\" title=\"Restricted account\">\n\t\t\t<label>\n\t\t\t<input type=\"checkbox\" name=\"lock\" />\n\t\t\t<span class=\"checkbox\"><svg class=\"iconic \"><use xlink:href=\"#check\"></use></svg></span>\n\t\t\t</label>\n\t\t\t</span>--->\n\t\t\t</p>\n\t\t\t<a id=\"CredentialDelete", "\"  class=\"basicModal__button basicModal__button_DEL\">Delete</a>\n\t\t</div>\n\t\t"]),
    _templateObject27 = _taggedTemplateLiteral(["$", "", ""], ["$", "", ""]),
    _templateObject28 = _taggedTemplateLiteral(["<span class='attr_", "_separator'>, </span>"], ["<span class='attr_", "_separator'>, </span>"]),
    _templateObject29 = _taggedTemplateLiteral(["<span class='attr_", " search'>$", "</span>"], ["<span class='attr_", " search'>$", "</span>"]),
    _templateObject30 = _taggedTemplateLiteral(["<span class='attr_", "'>$", "</span>"], ["<span class='attr_", "'>$", "</span>"]),
    _templateObject31 = _taggedTemplateLiteral(["\n\t\t\t\t\t\t <tr>\n\t\t\t\t\t\t\t <td>", "</td>\n\t\t\t\t\t\t\t <td>", "</td>\n\t\t\t\t\t\t </tr>\n\t\t\t\t\t\t "], ["\n\t\t\t\t\t\t <tr>\n\t\t\t\t\t\t\t <td>", "</td>\n\t\t\t\t\t\t\t <td>", "</td>\n\t\t\t\t\t\t </tr>\n\t\t\t\t\t\t "]),
    _templateObject32 = _taggedTemplateLiteral(["\n\t\t\t\t <div class='sidebar__divider'>\n\t\t\t\t\t <h1>", "</h1>\n\t\t\t\t </div>\n\t\t\t\t <div id='tags'>\n\t\t\t\t\t <div class='attr_", "'>", "</div>\n\t\t\t\t\t ", "\n\t\t\t\t </div>\n\t\t\t\t "], ["\n\t\t\t\t <div class='sidebar__divider'>\n\t\t\t\t\t <h1>", "</h1>\n\t\t\t\t </div>\n\t\t\t\t <div id='tags'>\n\t\t\t\t\t <div class='attr_", "'>", "</div>\n\t\t\t\t\t ", "\n\t\t\t\t </div>\n\t\t\t\t "]),
    _templateObject33 = _taggedTemplateLiteral(["\n\t\t\t\t<div class='sidebar__divider'>\n\t\t\t\t\t <h1>", "</h1>\n\t\t\t\t</div>\n\t\t\t\t<div class='palette'>\n\t\t\t\t \t", "\n\t\t\t\t</div>\n\t\t"], ["\n\t\t\t\t<div class='sidebar__divider'>\n\t\t\t\t\t <h1>", "</h1>\n\t\t\t\t</div>\n\t\t\t\t<div class='palette'>\n\t\t\t\t \t", "\n\t\t\t\t</div>\n\t\t"]);

function _taggedTemplateLiteral(strings, raw) { return Object.freeze(Object.defineProperties(strings, { raw: { value: Object.freeze(raw) } })); }

function gup(b) {
	b = b.replace(/[\[]/, "\\[").replace(/[\]]/, "\\]");

	var a = "[\\?&]" + b + "=([^&#]*)";
	var d = new RegExp(a);
	var c = d.exec(window.location.href);

	if (c === null) return "";else return c[1];
}

/**
 * @description This module communicates with Lychee's API
 */

var api = {
	onError: null
};

api.isTimeout = function (errorThrown, jqXHR) {
	if (errorThrown && (errorThrown === "Bad Request" && jqXHR && jqXHR.responseJSON && jqXHR.responseJSON.error && jqXHR.responseJSON.error === "Session timed out" || errorThrown === "unknown status" && jqXHR && jqXHR.status && jqXHR.status === 419 && jqXHR.responseJSON && jqXHR.responseJSON.message && jqXHR.responseJSON.message === "CSRF token mismatch.")) {
		return true;
	}

	return false;
};

api.post = function (fn, params, callback) {
	var responseProgressCB = arguments.length > 3 && arguments[3] !== undefined ? arguments[3] : null;

	loadingBar.show();

	params = $.extend({ function: fn }, params);

	var api_url = "api/" + fn;

	var success = function success(data) {
		setTimeout(loadingBar.hide, 100);

		// Catch errors
		if (typeof data === "string" && data.substring(0, 7) === "Error: ") {
			api.onError(data.substring(7, data.length), params, data);
			return false;
		}

		callback(data);
	};

	var error = function error(jqXHR, textStatus, errorThrown) {
		api.onError(api.isTimeout(errorThrown, jqXHR) ? "Session timed out." : "Server error or API not found.", params, errorThrown);
	};

	var ajaxParams = {
		type: "POST",
		url: api_url,
		data: params,
		dataType: "json",
		success: success,
		error: error
	};

	if (responseProgressCB !== null) {
		ajaxParams.xhrFields = {
			onprogress: responseProgressCB
		};
	}

	$.ajax(ajaxParams);
};

api.get = function (url, callback) {
	loadingBar.show();

	var success = function success(data) {
		setTimeout(loadingBar.hide, 100);

		// Catch errors
		if (typeof data === "string" && data.substring(0, 7) === "Error: ") {
			api.onError(data.substring(7, data.length), params, data);
			return false;
		}

		callback(data);
	};

	var error = function error(jqXHR, textStatus, errorThrown) {
		api.onError(api.isTimeout(errorThrown, jqXHR) ? "Session timed out." : "Server error or API not found.", {}, errorThrown);
	};

	$.ajax({
		type: "GET",
		url: url,
		data: {},
		dataType: "text",
		success: success,
		error: error
	});
};

api.post_raw = function (fn, params, callback) {
	loadingBar.show();

	params = $.extend({ function: fn }, params);

	var api_url = "api/" + fn;

	var success = function success(data) {
		setTimeout(loadingBar.hide, 100);

		// Catch errors
		if (typeof data === "string" && data.substring(0, 7) === "Error: ") {
			api.onError(data.substring(7, data.length), params, data);
			return false;
		}

		callback(data);
	};

	var error = function error(jqXHR, textStatus, errorThrown) {
		api.onError(api.isTimeout(errorThrown, jqXHR) ? "Session timed out." : "Server error or API not found.", params, errorThrown);
	};

	$.ajax({
		type: "POST",
		url: api_url,
		data: params,
		dataType: "text",
		success: success,
		error: error
	});
};

var csrf = {};

csrf.addLaravelCSRF = function (event, jqxhr, settings) {
	if (settings.url !== lychee.updatePath) {
		jqxhr.setRequestHeader("X-XSRF-TOKEN", csrf.getCookie("XSRF-TOKEN"));
	}
};

csrf.escape = function (s) {
	return s.replace(/([.*+?\^${}()|\[\]\/\\])/g, "\\$1");
};

csrf.getCookie = function (name) {
	// we stop the selection at = (default json) but also at % to prevent any %3D at the end of the string
	var match = document.cookie.match(RegExp("(?:^|;\\s*)" + csrf.escape(name) + "=([^;^%]*)"));
	return match ? match[1] : null;
};

csrf.bind = function () {
	$(document).on("ajaxSend", csrf.addLaravelCSRF);
};

/**
 * @description Used to view single photos with view.php
 */

// Sub-implementation of lychee -------------------------------------------------------------- //

var lychee = {};

lychee.content = $(".content");
lychee.imageview = $("#imageview");
lychee.mapview = $("#mapview");

lychee.escapeHTML = function () {
	var html = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : "";

	// Ensure that html is a string
	html += "";

	// Escape all critical characters
	html = html.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;").replace(/'/g, "&#039;").replace(/`/g, "&#96;");

	return html;
};

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

lychee.getEventName = function () {
	var touchendSupport = /Android|iPhone|iPad|iPod/i.test(navigator.userAgent || navigator.vendor || window.opera) && "ontouchend" in document.documentElement;
	return touchendSupport === true ? "touchend" : "click";
};

// Sub-implementation of photo -------------------------------------------------------------- //

var photo = {
	json: null
};

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

photo.getDirectLink = function () {
	return $("#imageview img").attr("src").replace(/"/g, "").replace(/url\(|\)$/gi, "");
};

photo.show = function () {
	$("#imageview").removeClass("full");
	header.dom().removeClass("header--hidden");

	return true;
};

photo.hide = function () {
	if (visible.photo() && !visible.sidebar() && !visible.contextMenu()) {
		$("#imageview").addClass("full");
		header.dom().addClass("header--hidden");

		return true;
	}

	return false;
};

photo.onresize = function () {
	// Copy of view.photo.onresize
	if (photo.json.sizeVariants.medium === null || photo.json.sizeVariants.medium2x === null) return;

	var imgWidth = photo.json.sizeVariants.medium.width;
	var imgHeight = photo.json.sizeVariants.medium.height;
	var containerWidth = parseFloat($("#imageview").width(), 10);
	var containerHeight = parseFloat($("#imageview").height(), 10);

	var width = imgWidth < containerWidth ? imgWidth : containerWidth;
	var height = width * imgHeight / imgWidth;
	if (height > containerHeight) {
		width = containerHeight * imgWidth / imgHeight;
	}

	$("img#image").attr("sizes", width + "px");
};

// Sub-implementation of contextMenu -------------------------------------------------------------- //

var contextMenu = {};

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

// Main -------------------------------------------------------------- //

var loadingBar = {
	show: function show() {},
	hide: function hide() {}
};

var imageview = $("#imageview");

$(document).ready(function () {
	// set CSRF protection (Laravel)
	csrf.bind();

	// Image View
	$(window).on("resize", photo.onresize);

	// Save ID of photo
	var photoID = gup("p");

	// Set API error handler
	api.onError = error;

	// Share
	header.dom("#button_share").on("click", function (e) {
		contextMenu.sharePhoto(photoID, e);
	});

	// Infobox
	header.dom("#button_info").on("click", sidebar.toggle);

	// Load photo
	loadPhotoInfo(photoID);
});

var loadPhotoInfo = function loadPhotoInfo(photoID) {
	var params = {
		photoID: photoID,
		password: ""
	};

	api.post("Photo::get", params, function (data) {
		if (data === "Warning: Photo private!" || data === "Warning: Wrong password!") {
			$("body").append(build.no_content("question-mark")).removeClass("view");
			header.dom().remove();
			return false;
		}

		photo.json = data;

		// Set title
		if (!data.title) data.title = "Untitled";
		document.title = "Lychee - " + data.title;
		header.dom(".header__title").html(lychee.escapeHTML(data.title));

		// Render HTML
		imageview.html(build.imageview(data, true).html);
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

var error = function error(errorThrown, params, data) {
	console.error({
		description: errorThrown,
		params: params,
		response: data
	});

	loadingBar.show("error", errorThrown);
};

/**
 * @description This module is used to generate HTML-Code.
 */

var build = {};

build.iconic = function (icon) {
	var classes = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : "";

	var html = "";

	html += lychee.html(_templateObject, classes, icon);

	return html;
};

build.divider = function (title) {
	var html = "";

	html += lychee.html(_templateObject2, title);

	return html;
};

build.editIcon = function (id) {
	var html = "";

	html += lychee.html(_templateObject3, id, build.iconic("pencil"));

	return html;
};

build.multiselect = function (top, left) {
	return lychee.html(_templateObject4, top, left);
};

// two additional images that are barely visible seems a bit overkill - use same image 3 times
// if this simplification comes to pass data.types, data.thumbs and data.thumbs2x no longer need to be arrays
build.getAlbumThumb = function (data) {
	var isVideo = void 0;
	var isRaw = void 0;
	var thumb = void 0;

	isVideo = data.thumb.type && data.thumb.type.indexOf("video") > -1;
	isRaw = data.thumb.type && data.thumb.type.indexOf("raw") > -1;
	thumb = data.thumb.thumb;
	var thumb2x = "";

	if (thumb === "uploads/thumb/" && isVideo) {
		return "<span class=\"thumbimg\"><img src='img/play-icon.png' alt='Photo thumbnail' data-overlay='false' draggable='false'></span>";
	}
	if (thumb === "uploads/thumb/" && isRaw) {
		return "<span class=\"thumbimg\"><img src='img/placeholder.png' alt='Photo thumbnail' data-overlay='false' draggable='false'></span>";
	}

	thumb2x = data.thumb.thumb2x;

	return "<span class=\"thumbimg" + (isVideo ? " video" : "") + "\"><img class='lazyload' src='img/placeholder.png' data-src='" + thumb + "' " + (thumb2x !== "" ? "data-srcset='" + thumb2x + " 2x'" : "") + " alt='Photo thumbnail' data-overlay='false' draggable='false'></span>";
};

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
			if (lychee.sortingAlbums !== "" && data.min_taken_at && data.max_taken_at) {
				var sortingAlbums = lychee.sortingAlbums.replace("ORDER BY ", "").split(" ");
				if (sortingAlbums[0] === "max_taken_at" || sortingAlbums[0] === "min_taken_at") {
					if (formattedMinTs !== "" && formattedMaxTs !== "") {
						subtitle = formattedMinTs === formattedMaxTs ? formattedMaxTs : formattedMinTs + " - " + formattedMaxTs;
					} else if (formattedMinTs !== "" && sortingAlbums[0] === "min_taken_at") {
						subtitle = formattedMinTs;
					} else if (formattedMaxTs !== "" && sortingAlbums[0] === "max_taken_at") {
						subtitle = formattedMaxTs;
					}
				}
			}
	}

	var html = lychee.html(_templateObject5, disabled ? "disabled" : "", data.nsfw && data.nsfw === "1" && lychee.nsfw_blur ? "blurred" : "", data.id, data.nsfw && data.nsfw === "1" ? "1" : "0", tabindex.get_next_tab_index(), build.getAlbumThumb(data), build.getAlbumThumb(data), build.getAlbumThumb(data), data.title, data.title, subtitle);

	if (album.isUploadable() && !disabled) {
		var isCover = album.json && album.json.cover_id && data.thumb.id === album.json.cover_id;
		html += lychee.html(_templateObject6, data.nsfw === "1" ? "badge--nsfw" : "", build.iconic("warning"), data.star === "1" ? "badge--star" : "", build.iconic("star"), data.recent === "1" ? "badge--visible badge--list" : "", build.iconic("clock"), data.public === "1" ? "badge--visible" : "", data.visible === "1" ? "badge--not--hidden" : "badge--hidden", build.iconic("eye"), data.unsorted === "1" ? "badge--visible" : "", build.iconic("list"), data.password === "1" ? "badge--visible" : "", build.iconic("lock-locked"), data.tag_album === "1" ? "badge--tag" : "", build.iconic("tag"), isCover ? "badge--cover" : "", build.iconic("folder-cover"));
	}

	if (data.albums && data.albums.length > 0 || data.hasOwnProperty("has_albums") && data.has_albums === "1") {
		html += lychee.html(_templateObject7, build.iconic("layers"));
	}

	html += "</div>";

	return html;
};

build.photo = function (data) {
	var disabled = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : false;

	var html = "";
	var thumbnail = "";
	var thumb2x = "";
	var isCover = data.id === album.json.cover_id;

	var isVideo = data.type && data.type.indexOf("video") > -1;
	var isRaw = data.type && data.type.indexOf("raw") > -1;
	var isLivePhoto = data.livePhotoUrl !== "" && data.livePhotoUrl !== null;

	if (data.sizeVariants.thumb === null) {
		if (isLivePhoto) {
			thumbnail = "<span class=\"thumbimg\"><img src='img/live-photo-icon.png' alt='Photo thumbnail' data-overlay='false' draggable='false' data-tabindex='" + tabindex.get_next_tab_index() + "'></span>";
		}
		if (isVideo) {
			thumbnail = "<span class=\"thumbimg\"><img src='img/play-icon.png' alt='Photo thumbnail' data-overlay='false' draggable='false' data-tabindex='" + tabindex.get_next_tab_index() + "'></span>";
		} else if (isRaw) {
			thumbnail = "<span class=\"thumbimg\"><img src='img/placeholder.png' alt='Photo thumbnail' data-overlay='false' draggable='false' data-tabindex='" + tabindex.get_next_tab_index() + "'></span>";
		}
	} else if (lychee.layout === "0") {
		if (data.sizeVariants.thumb2x !== null) {
			thumb2x = data.sizeVariants.thumb2x.url;
		}

		if (thumb2x !== "") {
			thumb2x = "data-srcset='" + thumb2x + " 2x'";
		}

		thumbnail = "<span class=\"thumbimg" + (isVideo ? " video" : "") + (isLivePhoto ? " livephoto" : "") + "\">";
		thumbnail += "<img class='lazyload' src='img/placeholder.png' data-src='" + data.sizeVariants.thumb.url + "' " + thumb2x + " alt='Photo thumbnail' data-overlay='false' draggable='false' >";
		thumbnail += "</span>";
	} else {
		if (data.sizeVariants.small !== null) {
			if (data.sizeVariants.small2x !== null) {
				thumb2x = "data-srcset='" + data.sizeVariants.small.url + " " + data.sizeVariants.small.width + "w, " + data.sizeVariants.small2x.url + " " + data.sizeVariants.small2x.width + "w'";
			}

			thumbnail = "<span class=\"thumbimg" + (isVideo ? " video" : "") + (isLivePhoto ? " livephoto" : "") + "\">";
			thumbnail += "<img class='lazyload' src='img/placeholder.png' data-src='" + data.sizeVariants.small.url + "' " + thumb2x + " alt='Photo thumbnail' data-overlay='false' draggable='false' >";
			thumbnail += "</span>";
		} else if (data.sizeVariants.medium !== null) {
			if (data.sizeVariants.medium2x !== null) {
				thumb2x = "data-srcset='" + data.sizeVariants.medium.url + " " + data.sizeVariants.medium.width + "w, " + data.sizeVariants.medium2x.url + " " + data.sizeVariants.medium2x.width + "w'";
			}

			thumbnail = "<span class=\"thumbimg" + (isVideo ? " video" : "") + (isLivePhoto ? " livephoto" : "") + "\">";
			thumbnail += "<img class='lazyload' src='img/placeholder.png' data-src='" + data.sizeVariants.medium.url + "' " + thumb2x + " alt='Photo thumbnail' data-overlay='false' draggable='false' >";
			thumbnail += "</span>";
		} else if (!isVideo) {
			// Fallback for images with no small or medium.
			thumbnail = "<span class=\"thumbimg" + (isLivePhoto ? " livephoto" : "") + "\">";
			thumbnail += "<img class='lazyload' src='img/placeholder.png' data-src='" + data.url + "' alt='Photo thumbnail' data-overlay='false' draggable='false' >";
			thumbnail += "</span>";
		} else {
			// Fallback for videos with no small (the case of no thumb is
			// handled at the top of this function).

			if (data.sizeVariants.thumb2x !== null) {
				thumb2x = data.sizeVariants.thumb2x.url;
			}

			if (thumb2x !== "") {
				thumb2x = "data-srcset='" + data.sizeVariants.thumb.url + " " + data.sizeVariants.thumb.width + "w, " + thumb2x + " " + data.sizeVariants.thumb2x.width + "w'";
			}

			thumbnail = "<span class=\"thumbimg video\">";
			thumbnail += "<img class='lazyload' src='img/placeholder.png' data-src='" + data.sizeVariants.thumb.url + "' " + thumb2x + " alt='Photo thumbnail' data-overlay='false' draggable='false' >";
			thumbnail += "</span>";
		}
	}

	html += lychee.html(_templateObject8, disabled ? "disabled" : "", data.album, data.id, tabindex.get_next_tab_index(), thumbnail, data.title, data.title);

	if (data.taken_at !== null) html += lychee.html(_templateObject9, build.iconic("camera-slr"), lychee.locale.printDateTime(data.taken_at));else html += lychee.html(_templateObject10, lychee.locale.printDateTime(data.created_at));

	html += "</div>";

	if (album.isUploadable()) {
		html += lychee.html(_templateObject11, data.star === "1" ? "badge--star" : "", build.iconic("star"), data.public === "1" && album.json.public !== "1" ? "badge--visible badge--hidden" : "", build.iconic("eye"), isCover ? "badge--cover" : "", build.iconic("folder-cover"));
	}

	html += "</div>";

	return html;
};

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

	return lychee.html(_templateObject12, data.title) + (overlay !== "" ? "<p>" + overlay + "</p>" : "") + "\n\t\t</div>\n\t\t";
};

build.imageview = function (data, visibleControls, autoplay) {
	var html = "";
	var thumb = "";

	if (data.type.indexOf("video") > -1) {
		html += lychee.html(_templateObject13, visibleControls === true ? "" : "full", autoplay ? "autoplay" : "", tabindex.get_next_tab_index(), data.url);
	} else if (data.type.indexOf("raw") > -1 && data.sizeVariants.medium === null) {
		html += lychee.html(_templateObject14, visibleControls === true ? "" : "full", tabindex.get_next_tab_index());
	} else {
		var img = "";

		if (data.livePhotoUrl === "" || data.livePhotoUrl === null) {
			// It's normal photo

			// See if we have the thumbnail loaded...
			$(".photo").each(function () {
				if ($(this).attr("data-id") && $(this).attr("data-id") == data.id) {
					var thumbimg = $(this).find("img");
					if (thumbimg.length > 0) {
						thumb = thumbimg[0].currentSrc ? thumbimg[0].currentSrc : thumbimg[0].src;
						return false;
					}
				}
			});

			if (data.sizeVariants.medium !== null) {
				var medium = "";

				if (data.sizeVariants.medium2x !== null) {
					medium = "srcset='" + data.sizeVariants.medium.url + " " + data.sizeVariants.medium.width + "w, " + data.sizeVariants.medium2x.url + " " + data.sizeVariants.medium2x.width + "w'";
				}
				img = "<img id='image' class='" + (visibleControls === true ? "" : "full") + "' src='" + data.sizeVariants.medium.url + "' " + medium + ("  draggable='false' alt='medium' data-tabindex='" + tabindex.get_next_tab_index() + "'>");
			} else {
				img = "<img id='image' class='" + (visibleControls === true ? "" : "full") + "' src='" + data.url + "' draggable='false' alt='big' data-tabindex='" + tabindex.get_next_tab_index() + "'>";
			}
		} else {
			if (data.sizeVariants.medium !== null) {
				var medium_width = data.sizeVariants.medium.width;
				var medium_height = data.sizeVariants.medium.height;
				// It's a live photo
				img = "<div id='livephoto' data-live-photo data-proactively-loads-video='true' data-photo-src='" + data.sizeVariants.medium.url + "' data-video-src='" + data.livePhotoUrl + "'  style='width: " + medium_width + "px; height: " + medium_height + "px' data-tabindex='" + tabindex.get_next_tab_index() + "'></div>";
			} else {
				// It's a live photo
				img = "<div id='livephoto' data-live-photo data-proactively-loads-video='true' data-photo-src='" + data.url + "' data-video-src='" + data.livePhotoUrl + "'  style='width: " + data.width + "px; height: " + data.height + "px' data-tabindex='" + tabindex.get_next_tab_index() + "'></div>";
			}
		}

		html += lychee.html(_templateObject15, img);
	}

	html += build.overlay_image(data) + ("\n\t\t\t<div class='arrow_wrapper arrow_wrapper--previous'><a id='previous'>" + build.iconic("caret-left") + "</a></div>\n\t\t\t<div class='arrow_wrapper arrow_wrapper--next'><a id='next'>" + build.iconic("caret-right") + "</a></div>\n\t\t\t");

	return { html: html, thumb: thumb };
};

build.no_content = function (typ) {
	var html = "";

	html += lychee.html(_templateObject16, build.iconic(typ));

	switch (typ) {
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

build.uploadNewFile = function (name) {
	if (name.length > 40) {
		name = name.substr(0, 17) + "..." + name.substr(name.length - 20, 20);
	}

	return lychee.html(_templateObject20, name);
};

build.colors = function (colors) {
	var html = "";
	var editable = typeof album !== "undefined" ? album.isUploadable() : false;

	// Search is enabled if logged in (not publicMode) or public seach is enabled
	var searchable = lychee.publicMode === false || lychee.public_search === true;

	// build class_string for tag
	var a_class = "color";
	if (searchable) {
		a_class = a_class + " search";
	}

	if (colors.length > 0) {
		colors.forEach(function (color, index) {
			html += lychee.html(_templateObject21, color.r, color.g, color.b, color.r, color.g, color.b);
		});
	} else {
		html = lychee.html(_templateObject22, lychee.locale["NO_PALETTE"]);
	}

	return html;
};

build.tags = function (tags) {
	var html = "";
	var editable = typeof album !== "undefined" ? album.isUploadable() : false;

	// Search is enabled if logged in (not publicMode) or public seach is enabled
	var searchable = lychee.publicMode === false || lychee.public_search === true;

	// build class_string for tag
	var a_class = "tag";
	if (searchable) {
		a_class = a_class + " search";
	}

	if (tags !== "") {
		tags = tags.split(",");

		tags.forEach(function (tag, index) {
			if (editable) {
				html += lychee.html(_templateObject23, a_class, tag, index, build.iconic("x"));
			} else {
				html += lychee.html(_templateObject24, a_class, tag);
			}
		});
	} else {
		html = lychee.html(_templateObject22, lychee.locale["NO_TAGS"]);
	}

	return html;
};

build.user = function (user) {
	var html = lychee.html(_templateObject25, user.id, user.id, user.username, user.id, user.id);

	return html;
};

build.u2f = function (credential) {
	return lychee.html(_templateObject26, credential.id, credential.id, credential.id.slice(0, 30), credential.id);
};

/**
 * @description This module takes care of the header.
 */

var header = {
	_dom: $(".header")
};

header.dom = function (selector) {
	if (selector == null || selector === "") return header._dom;
	return header._dom.find(selector);
};

header.bind = function () {
	// Event Name
	var eventName = lychee.getEventName();

	header.dom(".header__title").on(eventName, function (e) {
		if ($(this).hasClass("header__title--editable") === false) return false;

		if (lychee.enable_contextmenu_header === false) return false;

		if (visible.photo()) contextMenu.photoTitle(album.getID(), photo.getID(), e);else contextMenu.albumTitle(album.getID(), e);
	});

	header.dom("#button_visibility").on(eventName, function (e) {
		photo.setPublic(photo.getID(), e);
	});
	header.dom("#button_share").on(eventName, function (e) {
		contextMenu.sharePhoto(photo.getID(), e);
	});

	header.dom("#button_visibility_album").on(eventName, function (e) {
		album.setPublic(album.getID(), e);
	});

	header.dom("#button_sharing_album_users").on(eventName, function (e) {
		album.shareUsers(album.getID(), e);
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
	header.dom("#button_info_album").on(eventName, sidebar.toggle);
	header.dom("#button_info").on(eventName, sidebar.toggle);
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
		contextMenu.move([album.getID()], e, album.setAlbum, "ROOT", album.getParent() != "");
	});
	header.dom("#button_nsfw_album").on(eventName, function (e) {
		album.setNSFW(album.getID());
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
		photo.setStar([photo.getID()]);
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
			lychee.goto(album.getParent());
		}
	});
	header.dom("#button_back").on(eventName, function () {
		lychee.goto(album.getID());
	});
	header.dom("#button_back_map").on(eventName, function () {
		lychee.goto(album.getID() || "");
	});
	header.dom("#button_fs_album_enter,#button_fs_enter").on(eventName, lychee.fullscreenEnter);
	header.dom("#button_fs_album_exit,#button_fs_exit").on(eventName, lychee.fullscreenExit).hide();

	header.dom(".header__search").on("keyup click", function () {
		if ($(this).val().length > 0) {
			lychee.goto("search/" + encodeURIComponent($(this).val()));
		} else if (search.hash !== null) {
			search.reset();
		}
	});
	header.dom(".header__clear").on(eventName, function () {
		search.reset();
	});

	header.bind_back();

	return true;
};

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

header.show = function () {
	lychee.imageview.removeClass("full");
	header.dom().removeClass("header--hidden");

	tabindex.restoreSettings(header.dom());

	photo.updateSizeLivePhotoDuringAnimation();

	return true;
};

header.hideIfLivePhotoNotPlaying = function () {
	// Hides the header, if current live photo is not playing
	if (photo.isLivePhotoPlaying() == true) return false;
	return header.hide();
};

header.hide = function () {
	if (visible.photo() && !visible.sidebar() && !visible.contextMenu() && basicModal.visible() === false) {
		tabindex.saveSettings(header.dom());
		tabindex.makeUnfocusable(header.dom());

		lychee.imageview.addClass("full");
		header.dom().addClass("header--hidden");

		photo.updateSizeLivePhotoDuringAnimation();

		return true;
	}

	return false;
};

header.setTitle = function () {
	var title = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : "Untitled";

	var $title = header.dom(".header__title");
	var html = lychee.html(_templateObject27, title, build.iconic("caret-bottom"));

	$title.html(html);

	return true;
};

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
			return true;

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

			if (lychee.enable_button_add) {
				var _e6 = $(".button_add", ".header__toolbar--albums");
				_e6.show();
				tabindex.makeFocusable(_e6);
			} else {
				var _e7 = $(".button_add", ".header__toolbar--albums");
				_e7.remove();
			}

			return true;

		case "album":
			var albumID = album.getID();

			header.dom().removeClass("header--view");
			header.dom(".header__toolbar--public, .header__toolbar--albums, .header__toolbar--photo, .header__toolbar--map, .header__toolbar--config").removeClass("header__toolbar--visible");
			header.dom(".header__toolbar--album").addClass("header__toolbar--visible");

			tabindex.makeFocusable(header.dom(".header__toolbar--album"));
			tabindex.makeUnfocusable(header.dom(".header__toolbar--public, .header__toolbar--albums, .header__toolbar--photo, .header__toolbar--map, .header__toolbar--config"));

			// Hide download button when album empty or we are not allowed to
			// upload to it and it's not explicitly marked as downloadable.
			if (!album.json || album.json.photos === false && album.json.albums && album.json.albums.length === 0 || !album.isUploadable() && album.json.downloadable === "0") {
				var _e8 = $("#button_archive");
				_e8.hide();
				tabindex.makeUnfocusable(_e8);
			} else {
				var _e9 = $("#button_archive");
				_e9.show();
				tabindex.makeFocusable(_e9);
			}

			if (album.json && album.json.hasOwnProperty("share_button_visible") && album.json.share_button_visible !== "1") {
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

			if (albumID === "starred" || albumID === "public" || albumID === "recent") {
				$("#button_nsfw_album, #button_info_album, #button_trash_album, #button_visibility_album, #button_sharing_album_users, #button_move_album").hide();
				if (album.isUploadable()) {
					$(".button_add, .header__divider", ".header__toolbar--album").show();
					tabindex.makeFocusable($(".button_add, .header__divider", ".header__toolbar--album"));
				} else {
					$(".button_add, .header__divider", ".header__toolbar--album").hide();
					tabindex.makeUnfocusable($(".button_add, .header__divider", ".header__toolbar--album"));
				}
				tabindex.makeUnfocusable($("#button_nsfw_album, #button_info_album, #button_trash_album, #button_visibility_album, #button_sharing_album_users, #button_move_album"));
			} else if (albumID === "unsorted") {
				$("#button_nsfw_album, #button_info_album, #button_visibility_album, #button_sharing_album_users, #button_move_album").hide();
				$("#button_trash_album, .button_add, .header__divider", ".header__toolbar--album").show();
				tabindex.makeFocusable($("#button_trash_album, .button_add, .header__divider", ".header__toolbar--album"));
				tabindex.makeUnfocusable($("#button_nsfw_album, #button_info_album, #button_visibility_album, #button_sharing_album_users, #button_move_album"));
			} else if (album.isTagAlbum()) {
				$("#button_info_album").show();
				$("#button_nsfw_album, #button_move_album").hide();
				$(".button_add, .header__divider", ".header__toolbar--album").hide();
				tabindex.makeFocusable($("#button_info_album"));
				tabindex.makeUnfocusable($("#button_nsfw_album, #button_move_album"));
				tabindex.makeUnfocusable($(".button_add, .header__divider", ".header__toolbar--album"));
				if (album.isUploadable()) {
					$("#button_visibility_album, #button_sharing_album_users, #button_trash_album").show();
					tabindex.makeFocusable($("#button_visibility_album, #button_sharing_album_users, #button_trash_album"));
				} else {
					$("#button_visibility_album, #button_sharing_album_users, #button_trash_album").hide();
					tabindex.makeUnfocusable($("#button_visibility_album, #button_sharing_album_users, #button_trash_album"));
				}
			} else {
				$("#button_info_album").show();
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

			return true;

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

			if (photo.json && photo.json.hasOwnProperty("share_button_visible") && photo.json.share_button_visible !== "1") {
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
			if (!(album.isUploadable() || (photo.json.hasOwnProperty("downloadable") ? photo.json.downloadable === "1" : album.json && album.json.downloadable && album.json.downloadable === "1")) && !(photo.json.url && photo.json.url !== "")) {
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
			return true;
		case "map":
			header.dom().removeClass("header--view");
			header.dom(".header__toolbar--public, .header__toolbar--album, .header__toolbar--albums, .header__toolbar--photo, .header__toolbar--config").removeClass("header__toolbar--visible");
			header.dom(".header__toolbar--map").addClass("header__toolbar--visible");

			tabindex.makeFocusable(header.dom(".header__toolbar--map"));
			tabindex.makeUnfocusable(header.dom(".header__toolbar--public, .header__toolbar--album, .header__toolbar--albums, .header__toolbar--photo, .header__toolbar--config"));
			return true;
		case "config":
			header.dom().addClass("header--view");
			header.dom(".header__toolbar--public, .header__toolbar--albums, .header__toolbar--album, .header__toolbar--photo, .header__toolbar--map").removeClass("header__toolbar--visible");
			header.dom(".header__toolbar--config").addClass("header__toolbar--visible");
			return true;
	}

	return false;
};

// Note that the pull-down menu is now enabled not only for editable
// items but for all of public/albums/album/photo views, so 'editable' is a
// bit of a misnomer at this point...
header.setEditable = function (editable) {
	var $title = header.dom(".header__title");

	if (editable) $title.addClass("header__title--editable");else $title.removeClass("header__title--editable");

	return true;
};

/**
 * @description This module is used to check if elements are visible or not.
 */

var visible = {};

visible.albums = function () {
	if (header.dom(".header__toolbar--public").hasClass("header__toolbar--visible")) return true;
	if (header.dom(".header__toolbar--albums").hasClass("header__toolbar--visible")) return true;
	return false;
};

visible.album = function () {
	if (header.dom(".header__toolbar--album").hasClass("header__toolbar--visible")) return true;
	return false;
};

visible.photo = function () {
	if ($("#imageview.fadeIn").length > 0) return true;
	return false;
};

visible.mapview = function () {
	if ($("#mapview.fadeIn").length > 0) return true;
	return false;
};

visible.config = function () {
	if (header.dom(".header__toolbar--config").hasClass("header__toolbar--visible")) return true;
	return false;
};

visible.search = function () {
	if (search.hash != null) return true;
	return false;
};

visible.sidebar = function () {
	if (sidebar.dom().hasClass("active") === true) return true;
	return false;
};

visible.sidebarbutton = function () {
	if (visible.photo()) return true;
	if (visible.album() && $("#button_info_album:visible").length > 0) return true;
	return false;
};

visible.header = function () {
	if (header.dom().hasClass("header--hidden") === true) return false;
	return true;
};

visible.contextMenu = function () {
	return basicContext.visible();
};

visible.multiselect = function () {
	if ($("#multiselect").length > 0) return true;
	return false;
};

visible.leftMenu = function () {
	if (leftMenu.dom().hasClass("leftMenu__visible")) return true;
	return false;
};

/**
 * @description This module takes care of the sidebar.
 */

var sidebar = {
	_dom: $(".sidebar"),
	types: {
		DEFAULT: 0,
		TAGS: 1,
		PALETTE: 2
	},
	createStructure: {}
};

sidebar.dom = function (selector) {
	if (selector == null || selector === "") return sidebar._dom;

	return sidebar._dom.find(selector);
};

sidebar.bind = function () {
	// This function should be called after building and appending
	// the sidebars content to the DOM.
	// This function can be called multiple times, therefore
	// event handlers should be removed before binding a new one.

	// Event Name
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

	sidebar.dom(".color").off(eventName).on(eventName, function () {
		sidebar.triggerSearch($(this).data('color'));
	});

	return true;
};

sidebar.triggerSearch = function (search_string) {
	// If public search is diabled -> do nothing
	if (lychee.publicMode === true && !lychee.public_search) {
		// Do not display an error -> just do nothing to not confuse the user
		return;
	}

	search.hash = null;
	// We're either logged in or public search is allowed
	lychee.goto("search/" + encodeURIComponent(search_string));
};

sidebar.toggle = function () {
	if (visible.sidebar() || visible.sidebarbutton()) {
		header.dom(".button--info").toggleClass("active");
		lychee.content.toggleClass("content--sidebar");
		lychee.imageview.toggleClass("image--sidebar");
		if (typeof view !== "undefined") view.album.content.justify();
		sidebar.dom().toggleClass("active");
		photo.updateSizeLivePhotoDuringAnimation();

		return true;
	}

	return false;
};

sidebar.setSelectable = function () {
	var selectable = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : true;

	// Attributes/Values inside the sidebar are selectable by default.
	// Selection needs to be deactivated to prevent an unwanted selection
	// while using multiselect.

	if (selectable === true) sidebar.dom().removeClass("notSelectable");else sidebar.dom().addClass("notSelectable");
};

sidebar.changeAttr = function (attr) {
	var value = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : "-";
	var dangerouslySetInnerHTML = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : false;

	if (attr == null || attr === "") return false;

	// Set a default for the value
	if (value == null || value === "") value = "-";

	// Escape value
	if (dangerouslySetInnerHTML === false) value = lychee.escapeHTML(value);

	// Set new value
	sidebar.dom(".attr_" + attr).html(value);

	return true;
};

sidebar.hideAttr = function (attr) {
	sidebar.dom(".attr_" + attr).closest("tr").hide();
};

sidebar.secondsToHMS = function (d) {
	d = Number(d);
	var h = Math.floor(d / 3600);
	var m = Math.floor(d % 3600 / 60);
	var s = Math.floor(d % 60);

	return (h > 0 ? h.toString() + "h" : "") + (m > 0 ? m.toString() + "m" : "") + (s > 0 || h == 0 && m == 0 ? s.toString() + "s" : "");
};

sidebar.createStructure.photo = function (data) {
	if (data == null || data === "") return false;

	var editable = typeof album !== "undefined" ? album.isUploadable() : false;
	var exifHash = data.taken_at + data.make + data.model + data.shutter + data.aperture + data.focal + data.iso;
	var locationHash = data.longitude + data.latitude + data.altitude;
	var structure = {};
	var _public = "";
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
	switch (data.public) {
		case "0":
			_public = lychee.locale["PHOTO_SHR_NO"];
			break;
		case "1":
			_public = lychee.locale["PHOTO_SHR_PHT"];
			break;
		case "2":
			_public = lychee.locale["PHOTO_SHR_ALB"];
			break;
		default:
			_public = "-";
			break;
	}

	structure.basics = {
		title: lychee.locale["PHOTO_BASICS"],
		type: sidebar.types.DEFAULT,
		rows: [{ title: lychee.locale["PHOTO_TITLE"], kind: "title", value: data.title, editable: editable }, { title: lychee.locale["PHOTO_UPLOADED"], kind: "uploaded", value: lychee.locale.printDateTime(data.created_at) }, { title: lychee.locale["PHOTO_DESCRIPTION"], kind: "description", value: data.description, editable: editable }]
	};

	structure.image = {
		title: lychee.locale[isVideo ? "PHOTO_VIDEO" : "PHOTO_IMAGE"],
		type: sidebar.types.DEFAULT,
		rows: [{ title: lychee.locale["PHOTO_SIZE"], kind: "size", value: lychee.locale.printFilesizeLocalized(data.filesize) }, { title: lychee.locale["PHOTO_FORMAT"], kind: "type", value: data.type }, { title: lychee.locale["PHOTO_RESOLUTION"], kind: "resolution", value: data.width + " x " + data.height }]
	};

	if (isVideo) {
		if (data.width === 0 || data.height === 0) {
			// Remove the "Resolution" line if we don't have the data.
			structure.image.rows.splice(-1, 1);
		}

		// We overload the database, storing duration (in full seconds) in
		// "aperture" and frame rate (floating point with three digits after
		// the decimal point) in "focal".
		if (data.aperture != "") {
			structure.image.rows.push({ title: lychee.locale["PHOTO_DURATION"], kind: "duration", value: sidebar.secondsToHMS(data.aperture) });
		}
		if (data.focal != "") {
			structure.image.rows.push({ title: lychee.locale["PHOTO_FPS"], kind: "fps", value: data.focal + " fps" });
		}
	}

	// Always create tags section - behaviour for editing
	//tags handled when contructing the html code for tags

	structure.tags = {
		title: lychee.locale["PHOTO_TAGS"],
		type: sidebar.types.TAGS,
		value: build.tags(data.tags),
		editable: editable
	};

	structure.palette = {
		title: lychee.locale["PHOTO_PALETTE"],
		type: sidebar.types.PALETTE,
		value: data.colors.length > 0 ? build.colors(data.colors) : ''

		// Only create EXIF section when EXIF data available
	};if (exifHash !== "") {
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
		rows: [{ title: lychee.locale["PHOTO_SHR_PLUBLIC"], kind: "public", value: _public }]
	};

	structure.license = {
		title: lychee.locale["PHOTO_REUSE"],
		type: sidebar.types.DEFAULT,
		rows: [{ title: lychee.locale["PHOTO_LICENSE"], kind: "license", value: license, editable: editable }]
	};

	if (locationHash !== "" && locationHash !== 0) {
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
				value: data.altitude ? (Math.round(parseFloat(data.altitude) * 10) / 10).toString() + "m" : ""
			}, { title: lychee.locale["PHOTO_LOCATION"], kind: "location", value: data.location ? data.location : "" }]
		};
		if (data.imgDirection) {
			// No point in display sub-degree precision.
			structure.location.rows.push({
				title: lychee.locale["PHOTO_IMGDIRECTION"],
				kind: "imgDirection",
				value: Math.round(data.imgDirection).toString() + "°"
			});
		}
	} else {
		structure.location = {};
	}

	// Construct all parts of the structure
	var structure_ret = [structure.basics, structure.image, structure.tags, structure.exif, structure.location, structure.license, structure.palette];

	if (!lychee.publicMode) {
		structure_ret.push(structure.sharing);
	}

	return structure_ret;
};

sidebar.createStructure.album = function (album) {
	var data = album.json;

	if (data == null || data === "") return false;

	var editable = album.isUploadable();
	var structure = {};
	var _public = "";
	var hidden = "";
	var downloadable = "";
	var share_button_visible = "";
	var password = "";
	var license = "";
	var sorting = "";

	// Set value for public
	switch (data.public) {
		case "0":
			_public = lychee.locale["ALBUM_SHR_NO"];
			break;
		case "1":
			_public = lychee.locale["ALBUM_SHR_YES"];
			break;
		default:
			_public = "-";
			break;
	}

	// Set value for hidden
	switch (data.visible) {
		case "0":
			hidden = lychee.locale["ALBUM_SHR_YES"];
			break;
		case "1":
			hidden = lychee.locale["ALBUM_SHR_NO"];
			break;
		default:
			hidden = "-";
			break;
	}

	// Set value for downloadable
	switch (data.downloadable) {
		case "0":
			downloadable = lychee.locale["ALBUM_SHR_NO"];
			break;
		case "1":
			downloadable = lychee.locale["ALBUM_SHR_YES"];
			break;
		default:
			downloadable = "-";
			break;
	}

	// Set value for share_button_visible
	switch (data.share_button_visible) {
		case "0":
			share_button_visible = lychee.locale["ALBUM_SHR_NO"];
			break;
		case "1":
			share_button_visible = lychee.locale["ALBUM_SHR_YES"];
			break;
		default:
			share_button_visible = "-";
			break;
	}

	// Set value for password
	switch (data.password) {
		case "0":
			password = lychee.locale["ALBUM_SHR_NO"];
			break;
		case "1":
			password = lychee.locale["ALBUM_SHR_YES"];
			break;
		default:
			password = "-";
			break;
	}

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

	if (data.sorting_col === "") {
		sorting = lychee.locale["DEFAULT"];
	} else {
		sorting = data.sorting_col + " " + data.sorting_order;
	}

	structure.basics = {
		title: lychee.locale["ALBUM_BASICS"],
		type: sidebar.types.DEFAULT,
		rows: [{ title: lychee.locale["ALBUM_TITLE"], kind: "title", value: data.title, editable: editable }, { title: lychee.locale["ALBUM_DESCRIPTION"], kind: "description", value: data.description, editable: editable }]
	};

	if (album.isTagAlbum()) {
		structure.basics.rows.push({ title: lychee.locale["ALBUM_SHOW_TAGS"], kind: "showtags", value: data.show_tags, editable: editable });
	}

	var videoCount = 0;
	$.each(data.photos, function () {
		if (this.type && this.type.indexOf("video") > -1) {
			videoCount++;
		}
	});
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

	if (data.photos) {
		structure.album.rows.push({ title: lychee.locale["ALBUM_ORDERING"], kind: "sorting", value: sorting, editable: editable });
	}

	structure.share = {
		title: lychee.locale["ALBUM_SHARING"],
		type: sidebar.types.DEFAULT,
		rows: [{ title: lychee.locale["ALBUM_PUBLIC"], kind: "public", value: _public }, { title: lychee.locale["ALBUM_HIDDEN"], kind: "hidden", value: hidden }, { title: lychee.locale["ALBUM_DOWNLOADABLE"], kind: "downloadable", value: downloadable }, { title: lychee.locale["ALBUM_SHARE_BUTTON_VISIBLE"], kind: "share_button_visible", value: share_button_visible }, { title: lychee.locale["ALBUM_PASSWORD"], kind: "password", value: password }]
	};

	if (data.owner != null) {
		structure.share.rows.push({ title: lychee.locale["ALBUM_OWNER"], kind: "owner", value: data.owner });
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

sidebar.has_location = function (structure) {
	if (structure == null || structure === "" || structure === false) return false;

	var _has_location = false;

	structure.forEach(function (section) {
		if (section.title == lychee.locale["PHOTO_LOCATION"]) {
			_has_location = true;
		}
	});

	return _has_location;
};

sidebar.render = function (structure) {
	if (structure == null || structure === "" || structure === false) return false;

	var html = "";

	var renderDefault = function renderDefault(section) {
		var _html = "";

		_html += "\n\t\t\t\t <div class='sidebar__divider'>\n\t\t\t\t\t <h1>" + section.title + "</h1>\n\t\t\t\t </div>\n\t\t\t\t <table>\n\t\t\t\t ";

		if (section.title == lychee.locale["PHOTO_LOCATION"]) {
			var _has_latitude = false;
			var _has_longitude = false;

			section.rows.forEach(function (row, index, object) {
				if (row.kind == "latitude" && row.value !== "") {
					_has_latitude = true;
				}

				if (row.kind == "longitude" && row.value !== "") {
					_has_longitude = true;
				}

				// Do not show location is not enabled
				if (row.kind == "location" && (lychee.publicMode === true && !lychee.location_show_public || !lychee.location_show)) {
					object.splice(index, 1);
				} else {
					// Explode location string into an array to keep street, city etc separate
					if (!(row.value === "" || row.value == null)) {
						section.rows[index].value = row.value.split(",").map(function (item) {
							return item.trim();
						});
					}
				}
			});

			if (_has_latitude && _has_longitude && lychee.map_display) {
				_html += "\n\t\t\t\t\t\t <div id=\"leaflet_map_single_photo\"></div>\n\t\t\t\t\t\t ";
			}
		}

		section.rows.forEach(function (row) {
			var value = row.value;

			// show only Exif rows which have a value or if its editable
			if (!(value === "" || value == null) || row.editable === true) {
				// Wrap span-element around value for easier selecting on change
				if (Array.isArray(row.value)) {
					value = "";
					row.value.forEach(function (v) {
						if (v === "" || v == null) {
							return;
						}
						// Add separator if needed
						if (value !== "") {
							value += lychee.html(_templateObject28, row.kind);
						}
						value += lychee.html(_templateObject29, row.kind, v);
					});
				} else {
					value = lychee.html(_templateObject30, row.kind, value);
				}

				// Add edit-icon to the value when editable
				if (row.editable === true) value += " " + build.editIcon("edit_" + row.kind);

				_html += lychee.html(_templateObject31, row.title, value);
			}
		});

		_html += "\n\t\t\t\t </table>\n\t\t\t\t ";

		return _html;
	};

	var renderTags = function renderTags(section) {
		var _html = "";
		var editable = "";

		// Add edit-icon to the value when editable
		if (section.editable === true) editable = build.editIcon("edit_tags");

		_html += lychee.html(_templateObject32, section.title, section.title.toLowerCase(), section.value, editable);

		return _html;
	};

	var renderPalette = function renderPalette(section) {
		var _html = "";
		_html += lychee.html(_templateObject33, section.title, section.value);
		return _html;
	};

	structure.forEach(function (section) {
		if (section.type === sidebar.types.DEFAULT) html += renderDefault(section);else if (section.type === sidebar.types.TAGS) html += renderTags(section);else if (section.type === sidebar.types.PALETTE && section.value !== '') html += renderPalette(section);
	});

	return html;
};

function DecimalToDegreeMinutesSeconds(decimal, type) {
	var degrees = 0;
	var minutes = 0;
	var seconds = 0;
	var direction = void 0;

	//decimal must be integer or float no larger than 180;
	//type must be Boolean
	if (Math.abs(decimal) > 180 || typeof type !== "boolean") {
		return false;
	}

	//inputs OK, proceed
	//type is latitude when true, longitude when false

	//set direction; north assumed
	if (type && decimal < 0) {
		direction = "S";
	} else if (!type && decimal < 0) {
		direction = "W";
	} else if (!type) {
		direction = "E";
	} else {
		direction = "N";
	}

	//get absolute value of decimal
	var d = Math.abs(decimal);

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

var map_provider_layer_attribution = {
	Wikimedia: {
		layer: "https://maps.wikimedia.org/osm-intl/{z}/{x}/{y}{r}.png",
		attribution: '<a href="https://wikimediafoundation.org/wiki/Maps_Terms_of_Use">Wikimedia</a>'
	},
	"OpenStreetMap.org": {
		layer: "https://{s}.tile.osm.org/{z}/{x}/{y}.png",
		attribution: '&copy; <a href="https://osm.org/copyright">OpenStreetMap</a> contributors'
	},
	"OpenStreetMap.de": {
		layer: "https://{s}.tile.openstreetmap.de/{z}/{x}/{y}.png ",
		attribution: '&copy; <a href="https://osm.org/copyright">OpenStreetMap</a> contributors'
	},
	"OpenStreetMap.fr": {
		layer: "https://{s}.tile.openstreetmap.fr/osmfr/{z}/{x}/{y}.png ",
		attribution: '&copy; <a href="https://osm.org/copyright">OpenStreetMap</a> contributors'
	},
	RRZE: {
		layer: "https://{s}.osm.rrze.fau.de/osmhd/{z}/{x}/{y}.png",
		attribution: '&copy; <a href="https://osm.org/copyright">OpenStreetMap</a> contributors'
	}
};

var mapview = {
	map: null,
	photoLayer: null,
	min_lat: null,
	min_lng: null,
	max_lat: null,
	max_lng: null,
	albumID: null,
	map_provider: null
};

mapview.isInitialized = function () {
	if (mapview.map === null || mapview.photoLayer === null) {
		return false;
	}
	return true;
};

mapview.title = function (_albumID, _albumTitle) {
	switch (_albumID) {
		case "f":
			lychee.setTitle(lychee.locale["STARRED"], false);
			break;
		case "s":
			lychee.setTitle(lychee.locale["PUBLIC"], false);
			break;
		case "r":
			lychee.setTitle(lychee.locale["RECENT"], false);
			break;
		case "0":
			lychee.setTitle(lychee.locale["UNSORTED"], false);
			break;
		case null:
			lychee.setTitle(lychee.locale["ALBUMS"], false);
			break;
		default:
			lychee.setTitle(_albumTitle, false);
			break;
	}
};

// Open the map view
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
	if (mapview.isInitialized() == false) {
		// Leaflet seaches for icon in same directoy as js file -> paths needs
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
			// Mapview has already shown data -> remove only photoLayer showing photos
			mapview.photoLayer.clear();
		}

		// Reset min/max lat/lgn Values
		mapview.min_lat = null;
		mapview.max_lat = null;
		mapview.min_lng = null;
		mapview.max_lng = null;
	}

	// Define how the photos on the map should look like
	mapview.photoLayer = L.photo.cluster().on("click", function (e) {
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
		if (mapview.min_lat && mapview.min_lng && mapview.max_lat && mapview.max_lng) {
			var dist_lat = mapview.max_lat - mapview.min_lat;
			var dist_lng = mapview.max_lng - mapview.min_lng;
			mapview.map.fitBounds([[mapview.min_lat - 0.1 * dist_lat, mapview.min_lng - 0.1 * dist_lng], [mapview.max_lat + 0.1 * dist_lat, mapview.max_lng + 0.1 * dist_lng]]);
		} else {
			mapview.map.fitWorld();
		}
	};

	// Adds photos to the map
	var addPhotosToMap = function addPhotosToMap(album) {
		// check if empty
		if (!album.photos) return;

		var photos = [];

		album.photos.forEach(function (element, index) {
			if (element.latitude || element.longitude) {
				photos.push({
					lat: parseFloat(element.latitude),
					lng: parseFloat(element.longitude),
					thumbnail: element.sizeVariants.thumb !== null ? element.sizeVariants.thumb.url : "img/placeholder.png",
					thumbnail2x: element.sizeVariants.thumb2x !== null ? element.sizeVariants.thumb2x.url : null,
					url: element.sizeVariants.small !== null ? element.sizeVariants.small.url : element.url,
					url2x: element.sizeVariants.small2x !== null ? element.sizeVariants.small2x.url : null,
					name: element.title,
					taken_at: element.taken_at,
					albumID: element.album,
					photoID: element.id
				});

				// Update min/max lat/lng
				if (mapview.min_lat === null || mapview.min_lat > element.latitude) {
					mapview.min_lat = parseFloat(element.latitude);
				}
				if (mapview.min_lng === null || mapview.min_lng > element.longitude) {
					mapview.min_lng = parseFloat(element.longitude);
				}
				if (mapview.max_lat === null || mapview.max_lat < element.latitude) {
					mapview.max_lat = parseFloat(element.latitude);
				}
				if (mapview.max_lng === null || mapview.max_lng < element.longitude) {
					mapview.max_lng = parseFloat(element.longitude);
				}
			}
		});

		// Add Photos to map
		mapview.photoLayer.add(photos).addTo(mapview.map);

		// Update Zoom and Position
		updateZoom();
	};

	// Call backend, retrieve information of photos and display them
	// This function is called recursively to retrieve data for sub-albums
	// Possible enhancement could be to only have a single ajax call
	var getAlbumData = function getAlbumData(_albumID) {
		var _includeSubAlbums = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : true;

		if (_albumID !== "" && _albumID !== null) {
			// _ablumID has been to a specific album
			var _params = {
				albumID: _albumID,
				includeSubAlbums: _includeSubAlbums,
				password: ""
			};

			api.post("Album::getPositionData", _params, function (data) {
				if (data === "Warning: Wrong password!") {
					password.getDialog(_albumID, function () {
						_params.password = password.value;

						api.post("Album::getPositionData", _params, function (_data) {
							addPhotosToMap(_data);
							mapview.title(_albumID, _data.title);
						});
					});
				} else {
					addPhotosToMap(data);
					mapview.title(_albumID, data.title);
				}
			});
		} else {
			// AlbumID is empty -> fetch all photos of all albums
			// _ablumID has been to a specific album
			var _params2 = {
				includeSubAlbums: _includeSubAlbums,
				password: ""
			};

			api.post("Albums::getPositionData", _params2, function (data) {
				if (data === "Warning: Wrong password!") {
					password.getDialog(_albumID, function () {
						_params2.password = password.value;

						api.post("Albums::getPositionData", _params2, function (_data) {
							addPhotosToMap(_data);
							mapview.title(_albumID, _data.title);
						});
					});
				} else {
					addPhotosToMap(data);
					mapview.title(_albumID, data.title);
				}
			});
		}
	};

	// If subalbums not being included and album.json already has all data
	// -> we can reuse it
	if (lychee.map_include_subalbums === false && album.json !== null && album.json.photos !== null) {
		addPhotosToMap(album.json);
	} else {
		// Not all needed data has been  preloaded - we need to load everything
		getAlbumData(albumID, lychee.map_include_subalbums);
	}

	// Update Zoom and Position once more (for empty map)
	updateZoom();
};

mapview.close = function () {
	// If map functionality is disabled -> do nothing
	if (!lychee.map_display) return;

	lychee.animate($("#mapview"), "fadeOut");
	$("#mapview").hide();
	header.setMode("album");

	// Make album focussable
	tabindex.makeFocusable(lychee.content);
};

mapview.goto = function (elem) {
	// If map functionality is disabled -> do nothing
	if (!lychee.map_display) return;

	var photoID = elem.attr("data-id");
	var albumID = elem.attr("data-album-id");

	if (albumID == "null") albumID = 0;

	if (album.json == null || albumID !== album.json.id) {
		album.refresh();
	}

	lychee.goto(albumID + "/" + photoID);
};

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

	TITLE_NEW_ALBUM: "Enter a title for the new album:",
	UNTITLED: "Untilted",
	UNSORTED: "Unsorted",
	STARRED: "Starred",
	RECENT: "Recent",
	PUBLIC: "Public",
	NUM_PHOTOS: "Photos",

	CREATE_ALBUM: "Create Album",
	CREATE_TAG_ALBUM: "Create Tag Album",

	STAR_PHOTO: "Star Photo",
	STAR: "Star",
	STAR_ALL: "Star All",
	TAGS: "Tags",
	TAGS_ALL: "Tags All",
	UNSTAR_PHOTO: "Unstar Photo",

	FULL_PHOTO: "Full Photo",
	ABOUT_PHOTO: "About Photo",
	DISPLAY_FULL_MAP: "Map",
	DIRECT_LINK: "Direct Link",
	DIRECT_LINKS: "Direct Links",

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
	NSFW_VISIBLE_TEXT_2: "If the album is public, it is still accessible, just hidden from the view and <b>can be revealed by pressing <hkb>H</hkb></b>.",
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

	/**
  * Formats a number representing a filesize in bytes as a localized string
  * @param {!number} filesize
  * @return {string} A formatted and localized string
  */
	printFilesizeLocalized: function printFilesizeLocalized(filesize) {
		console.assert(Number.isInteger(filesize), "printFilesizeLocalized: expected integer, got %s", typeof filesize === "undefined" ? "undefined" : _typeof(filesize));
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
  * @return {string} A formatted and localized time
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
		var splitDateTime = /^(\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2})([-Z+])(\d{2}:\d{2})?$/.exec(jsonDateTime);
		console.assert(splitDateTime.length === 4, "'jsonDateTime' is not formatted acc. to ISO 8601; passed string was: " + jsonDateTime);
		var locale = "default"; // use the user's browser settings
		var format = { dateStyle: "medium", timeStyle: "medium" };
		var result = new Date(splitDateTime[1]).toLocaleString(locale, format);
		if (splitDateTime[2] === "Z" || splitDateTime[3] === "00:00") {
			result += " UTC";
		} else {
			result += " UTC" + splitDateTime[2] + splitDateTime[3];
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
  * @return {string} A formatted and localized month and year
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

tabindex.saveSettings = function (elem) {
	if (!lychee.enable_tabindex) return;

	// Todo: Make shorter notation
	// Get all elements which have a tabindex
	var tmp = $(elem).find("[tabindex]");

	// iterate over all elements and set tabindex to stored value (i.e. make is not focussable)
	tmp.each(function (i, e) {
		// TODO: shorter notation
		a = $(e).attr("tabindex");
		$(this).data("tabindex-saved", a);
	});
};

tabindex.restoreSettings = function (elem) {
	if (!lychee.enable_tabindex) return;

	// Todo: Make shorter noation
	// Get all elements which have a tabindex
	var tmp = $(elem).find("[tabindex]");

	// iterate over all elements and set tabindex to stored value (i.e. make is not focussable)
	tmp.each(function (i, e) {
		// TODO: shorter notation
		a = $(e).data("tabindex-saved");
		$(e).attr("tabindex", a);
	});
};

tabindex.makeUnfocusable = function (elem) {
	var saveFocusElement = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : false;

	if (!lychee.enable_tabindex) return;

	// Todo: Make shorter noation
	// Get all elements which have a tabindex
	var tmp = $(elem).find("[tabindex]");

	// iterate over all elements and set tabindex to -1 (i.e. make is not focussable)
	tmp.each(function (i, e) {
		$(e).attr("tabindex", "-1");
		// Save which element had focus before we make it unfocusable
		if (saveFocusElement && $(e).is(":focus")) {
			$(e).data("tabindex-focus", true);
			// Remove focus
			$(e).blur();
		}
	});

	// Disable input fields
	$(elem).find("input").attr("disabled", "disabled");
};

tabindex.makeFocusable = function (elem) {
	var restoreFocusElement = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : false;

	if (!lychee.enable_tabindex) return;

	// Todo: Make shorter noation
	// Get all elements which have a tabindex
	var tmp = $(elem).find("[data-tabindex]");

	// iterate over all elements and set tabindex to stored value (i.e. make is not focussable)
	tmp.each(function (i, e) {
		$(e).attr("tabindex", $(e).data("tabindex"));
		// restore focus elemente if wanted
		if (restoreFocusElement) {
			if ($(e).data("tabindex-focus") && lychee.active_focus_on_page_load) {
				$(e).focus();
				$(e).removeData("tabindex-focus");
			}
		}
	});

	// Enable input fields
	$(elem).find("input").removeAttr("disabled");
};

tabindex.get_next_tab_index = function () {
	tabindex.next_tab_index = tabindex.next_tab_index + 1;

	return tabindex.next_tab_index - 1;
};

tabindex.reset = function () {
	tabindex.next_tab_index = tabindex.offset_for_header;
};

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