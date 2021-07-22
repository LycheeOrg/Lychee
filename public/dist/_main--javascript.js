"use strict";

var _slicedToArray = function () { function sliceIterator(arr, i) { var _arr = []; var _n = true; var _d = false; var _e = undefined; try { for (var _i = arr[Symbol.iterator](), _s; !(_n = (_s = _i.next()).done); _n = true) { _arr.push(_s.value); if (i && _arr.length === i) break; } } catch (err) { _d = true; _e = err; } finally { try { if (!_n && _i["return"]) _i["return"](); } finally { if (_d) throw _e; } } return _arr; } return function (arr, i) { if (Array.isArray(arr)) { return arr; } else if (Symbol.iterator in Object(arr)) { return sliceIterator(arr, i); } else { throw new TypeError("Invalid attempt to destructure non-iterable instance"); } }; }();

var _typeof = typeof Symbol === "function" && typeof Symbol.iterator === "symbol" ? function (obj) { return typeof obj; } : function (obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; };

var _templateObject = _taggedTemplateLiteral(["<p>", " <input class='text' name='title' type='text' maxlength='100' placeholder='Title' value='Untitled'></p>"], ["<p>", " <input class='text' name='title' type='text' maxlength='100' placeholder='Title' value='Untitled'></p>"]),
    _templateObject2 = _taggedTemplateLiteral(["<p>", "\n\t\t\t\t\t\t\t<input class='text' name='title' type='text' maxlength='100' placeholder='Title' value='Untitled'>\n\t\t\t\t\t\t\t<input class='text' name='tags' type='text' minlength='1' placeholder='Tags' value=''>\n\t\t\t\t\t\t</p>"], ["<p>", "\n\t\t\t\t\t\t\t<input class='text' name='title' type='text' maxlength='100' placeholder='Title' value='Untitled'>\n\t\t\t\t\t\t\t<input class='text' name='tags' type='text' minlength='1' placeholder='Tags' value=''>\n\t\t\t\t\t\t</p>"]),
    _templateObject3 = _taggedTemplateLiteral(["<p>", "\n\t\t\t\t\t\t\t<input class='text' name='show_tags' type='text' minlength='1' placeholder='Tags' value='$", "'>\n\t\t\t\t\t\t</p>"], ["<p>", "\n\t\t\t\t\t\t\t<input class='text' name='show_tags' type='text' minlength='1' placeholder='Tags' value='$", "'>\n\t\t\t\t\t\t</p>"]),
    _templateObject4 = _taggedTemplateLiteral(["<input class='text' name='title' type='text' maxlength='100' placeholder='$", "' value='$", "'>"], ["<input class='text' name='title' type='text' maxlength='100' placeholder='$", "' value='$", "'>"]),
    _templateObject5 = _taggedTemplateLiteral(["<p>", " ", "</p>"], ["<p>", " ", "</p>"]),
    _templateObject6 = _taggedTemplateLiteral(["<p>", " $", " ", " ", "</p>"], ["<p>", " $", " ", " ", "</p>"]),
    _templateObject7 = _taggedTemplateLiteral(["<p>", "<input class='text' name='description' type='text' maxlength='800' placeholder='$", "' value='$", "'></p>"], ["<p>", "<input class='text' name='description' type='text' maxlength='800' placeholder='$", "' value='$", "'></p>"]),
    _templateObject8 = _taggedTemplateLiteral(["\n\t<div>\n\t\t<p>", "\n\t\t<span class=\"select\" style=\"width:270px\">\n\t\t\t<select name=\"license\" id=\"license\">\n\t\t\t\t<option value=\"none\">", "</option>\n\t\t\t\t<option value=\"reserved\">", "</option>\n\t\t\t\t<option value=\"CC0\">CC0 - Public Domain</option>\n\t\t\t\t<option value=\"CC-BY-1.0\">CC Attribution 1.0</option>\n\t\t\t\t<option value=\"CC-BY-2.0\">CC Attribution 2.0</option>\n\t\t\t\t<option value=\"CC-BY-2.5\">CC Attribution 2.5</option>\n\t\t\t\t<option value=\"CC-BY-3.0\">CC Attribution 3.0</option>\n\t\t\t\t<option value=\"CC-BY-4.0\">CC Attribution 4.0</option>\n\t\t\t\t<option value=\"CC-BY-ND-1.0\">CC Attribution-NoDerivatives 1.0</option>\n\t\t\t\t<option value=\"CC-BY-ND-2.0\">CC Attribution-NoDerivatives 2.0</option>\n\t\t\t\t<option value=\"CC-BY-ND-2.5\">CC Attribution-NoDerivatives 2.5</option>\n\t\t\t\t<option value=\"CC-BY-ND-3.0\">CC Attribution-NoDerivatives 3.0</option>\n\t\t\t\t<option value=\"CC-BY-ND-4.0\">CC Attribution-NoDerivatives 4.0</option>\n\t\t\t\t<option value=\"CC-BY-SA-1.0\">CC Attribution-ShareAlike 1.0</option>\n\t\t\t\t<option value=\"CC-BY-SA-2.0\">CC Attribution-ShareAlike 2.0</option>\n\t\t\t\t<option value=\"CC-BY-SA-2.5\">CC Attribution-ShareAlike 2.5</option>\n\t\t\t\t<option value=\"CC-BY-SA-3.0\">CC Attribution-ShareAlike 3.0</option>\n\t\t\t\t<option value=\"CC-BY-SA-4.0\">CC Attribution-ShareAlike 4.0</option>\n\t\t\t\t<option value=\"CC-BY-NC-1.0\">CC Attribution-NonCommercial 1.0</option>\n\t\t\t\t<option value=\"CC-BY-NC-2.0\">CC Attribution-NonCommercial 2.0</option>\n\t\t\t\t<option value=\"CC-BY-NC-2.5\">CC Attribution-NonCommercial 2.5</option>\n\t\t\t\t<option value=\"CC-BY-NC-3.0\">CC Attribution-NonCommercial 3.0</option>\n\t\t\t\t<option value=\"CC-BY-NC-4.0\">CC Attribution-NonCommercial 4.0</option>\n\t\t\t\t<option value=\"CC-BY-NC-ND-1.0\">CC Attribution-NonCommercial-NoDerivatives 1.0</option>\n\t\t\t\t<option value=\"CC-BY-NC-ND-2.0\">CC Attribution-NonCommercial-NoDerivatives 2.0</option>\n\t\t\t\t<option value=\"CC-BY-NC-ND-2.5\">CC Attribution-NonCommercial-NoDerivatives 2.5</option>\n\t\t\t\t<option value=\"CC-BY-NC-ND-3.0\">CC Attribution-NonCommercial-NoDerivatives 3.0</option>\n\t\t\t\t<option value=\"CC-BY-NC-ND-4.0\">CC Attribution-NonCommercial-NoDerivatives 4.0</option>\n\t\t\t\t<option value=\"CC-BY-NC-SA-1.0\">CC Attribution-NonCommercial-ShareAlike 1.0</option>\n\t\t\t\t<option value=\"CC-BY-NC-SA-2.0\">CC Attribution-NonCommercial-ShareAlike 2.0</option>\n\t\t\t\t<option value=\"CC-BY-NC-SA-2.5\">CC Attribution-NonCommercial-ShareAlike 2.5</option>\n\t\t\t\t<option value=\"CC-BY-NC-SA-3.0\">CC Attribution-NonCommercial-ShareAlike 3.0</option>\n\t\t\t\t<option value=\"CC-BY-NC-SA-4.0\">CC Attribution-NonCommercial-ShareAlike 4.0</option>\n\t\t\t</select>\n\t\t</span>\n\t\t<br />\n\t\t<a href=\"https://creativecommons.org/choose/\" target=\"_blank\">", "</a>\n\t\t</p>\n\t</div>"], ["\n\t<div>\n\t\t<p>", "\n\t\t<span class=\"select\" style=\"width:270px\">\n\t\t\t<select name=\"license\" id=\"license\">\n\t\t\t\t<option value=\"none\">", "</option>\n\t\t\t\t<option value=\"reserved\">", "</option>\n\t\t\t\t<option value=\"CC0\">CC0 - Public Domain</option>\n\t\t\t\t<option value=\"CC-BY-1.0\">CC Attribution 1.0</option>\n\t\t\t\t<option value=\"CC-BY-2.0\">CC Attribution 2.0</option>\n\t\t\t\t<option value=\"CC-BY-2.5\">CC Attribution 2.5</option>\n\t\t\t\t<option value=\"CC-BY-3.0\">CC Attribution 3.0</option>\n\t\t\t\t<option value=\"CC-BY-4.0\">CC Attribution 4.0</option>\n\t\t\t\t<option value=\"CC-BY-ND-1.0\">CC Attribution-NoDerivatives 1.0</option>\n\t\t\t\t<option value=\"CC-BY-ND-2.0\">CC Attribution-NoDerivatives 2.0</option>\n\t\t\t\t<option value=\"CC-BY-ND-2.5\">CC Attribution-NoDerivatives 2.5</option>\n\t\t\t\t<option value=\"CC-BY-ND-3.0\">CC Attribution-NoDerivatives 3.0</option>\n\t\t\t\t<option value=\"CC-BY-ND-4.0\">CC Attribution-NoDerivatives 4.0</option>\n\t\t\t\t<option value=\"CC-BY-SA-1.0\">CC Attribution-ShareAlike 1.0</option>\n\t\t\t\t<option value=\"CC-BY-SA-2.0\">CC Attribution-ShareAlike 2.0</option>\n\t\t\t\t<option value=\"CC-BY-SA-2.5\">CC Attribution-ShareAlike 2.5</option>\n\t\t\t\t<option value=\"CC-BY-SA-3.0\">CC Attribution-ShareAlike 3.0</option>\n\t\t\t\t<option value=\"CC-BY-SA-4.0\">CC Attribution-ShareAlike 4.0</option>\n\t\t\t\t<option value=\"CC-BY-NC-1.0\">CC Attribution-NonCommercial 1.0</option>\n\t\t\t\t<option value=\"CC-BY-NC-2.0\">CC Attribution-NonCommercial 2.0</option>\n\t\t\t\t<option value=\"CC-BY-NC-2.5\">CC Attribution-NonCommercial 2.5</option>\n\t\t\t\t<option value=\"CC-BY-NC-3.0\">CC Attribution-NonCommercial 3.0</option>\n\t\t\t\t<option value=\"CC-BY-NC-4.0\">CC Attribution-NonCommercial 4.0</option>\n\t\t\t\t<option value=\"CC-BY-NC-ND-1.0\">CC Attribution-NonCommercial-NoDerivatives 1.0</option>\n\t\t\t\t<option value=\"CC-BY-NC-ND-2.0\">CC Attribution-NonCommercial-NoDerivatives 2.0</option>\n\t\t\t\t<option value=\"CC-BY-NC-ND-2.5\">CC Attribution-NonCommercial-NoDerivatives 2.5</option>\n\t\t\t\t<option value=\"CC-BY-NC-ND-3.0\">CC Attribution-NonCommercial-NoDerivatives 3.0</option>\n\t\t\t\t<option value=\"CC-BY-NC-ND-4.0\">CC Attribution-NonCommercial-NoDerivatives 4.0</option>\n\t\t\t\t<option value=\"CC-BY-NC-SA-1.0\">CC Attribution-NonCommercial-ShareAlike 1.0</option>\n\t\t\t\t<option value=\"CC-BY-NC-SA-2.0\">CC Attribution-NonCommercial-ShareAlike 2.0</option>\n\t\t\t\t<option value=\"CC-BY-NC-SA-2.5\">CC Attribution-NonCommercial-ShareAlike 2.5</option>\n\t\t\t\t<option value=\"CC-BY-NC-SA-3.0\">CC Attribution-NonCommercial-ShareAlike 3.0</option>\n\t\t\t\t<option value=\"CC-BY-NC-SA-4.0\">CC Attribution-NonCommercial-ShareAlike 4.0</option>\n\t\t\t</select>\n\t\t</span>\n\t\t<br />\n\t\t<a href=\"https://creativecommons.org/choose/\" target=\"_blank\">", "</a>\n\t\t</p>\n\t</div>"]),
    _templateObject9 = _taggedTemplateLiteral(["\n\t<div>\n\t\t<p>"], ["\n\t<div>\n\t\t<p>"]),
    _templateObject10 = _taggedTemplateLiteral(["\n\t\t\t<form>\n\t\t\t\t<div class='switch'>\n\t\t\t\t\t<label>\n\t\t\t\t\t\t", ":&nbsp;\n\t\t\t\t\t\t<input type='checkbox' name='public'>\n\t\t\t\t\t\t<span class='slider round'></span>\n\t\t\t\t\t</label>\n\t\t\t\t\t<p>", "</p>\n\t\t\t\t</div>\n\t\t\t\t<div class='choice'>\n\t\t\t\t\t<label>\n\t\t\t\t\t\t<input type='checkbox' name='full_photo'>\n\t\t\t\t\t\t<span class='checkbox'>", "</span>\n\t\t\t\t\t\t<span class='label'>", "</span>\n\t\t\t\t\t</label>\n\t\t\t\t\t<p>", "</p>\n\t\t\t\t</div>\n\t\t\t\t<div class='choice'>\n\t\t\t\t\t<label>\n\t\t\t\t\t\t<input type='checkbox' name='hidden'>\n\t\t\t\t\t\t<span class='checkbox'>", "</span>\n\t\t\t\t\t\t<span class='label'>", "</span>\n\t\t\t\t\t</label>\n\t\t\t\t\t<p>", "</p>\n\t\t\t\t</div>\n\t\t\t\t<div class='choice'>\n\t\t\t\t\t<label>\n\t\t\t\t\t\t<input type='checkbox' name='downloadable'>\n\t\t\t\t\t\t<span class='checkbox'>", "</span>\n\t\t\t\t\t\t<span class='label'>", "</span>\n\t\t\t\t\t</label>\n\t\t\t\t\t<p>", "</p>\n\t\t\t\t</div>\n\t\t\t\t<div class='choice'>\n\t\t\t\t\t<label>\n\t\t\t\t\t\t<input type='checkbox' name='share_button_visible'>\n\t\t\t\t\t\t<span class='checkbox'>", "</span>\n\t\t\t\t\t\t<span class='label'>", "</span>\n\t\t\t\t\t</label>\n\t\t\t\t\t<p>", "</p>\n\t\t\t\t</div>\n\t\t\t\t<div class='choice'>\n\t\t\t\t\t<label>\n\t\t\t\t\t\t<input type='checkbox' name='password'>\n\t\t\t\t\t\t<span class='checkbox'>", "</span>\n\t\t\t\t\t\t<span class='label'>", "</span>\n\t\t\t\t\t</label>\n\t\t\t\t\t<p>", "</p>\n\t\t\t\t\t<input class='text' name='passwordtext' type='text' placeholder='", "' value=''>\n\t\t\t\t</div>\n\t\t\t\t<div class='hr'><hr></div>\n\t\t\t\t<div class='switch'>\n\t\t\t\t\t<label>\n\t\t\t\t\t\t", ":&nbsp;\n\t\t\t\t\t\t<input type='checkbox' name='nsfw'>\n\t\t\t\t\t\t<span class='slider round'></span>\n\t\t\t\t\t</label>\n\t\t\t\t\t<p>", "</p>\n\t\t\t\t</div>\n\t\t\t</form>\n\t\t"], ["\n\t\t\t<form>\n\t\t\t\t<div class='switch'>\n\t\t\t\t\t<label>\n\t\t\t\t\t\t", ":&nbsp;\n\t\t\t\t\t\t<input type='checkbox' name='public'>\n\t\t\t\t\t\t<span class='slider round'></span>\n\t\t\t\t\t</label>\n\t\t\t\t\t<p>", "</p>\n\t\t\t\t</div>\n\t\t\t\t<div class='choice'>\n\t\t\t\t\t<label>\n\t\t\t\t\t\t<input type='checkbox' name='full_photo'>\n\t\t\t\t\t\t<span class='checkbox'>", "</span>\n\t\t\t\t\t\t<span class='label'>", "</span>\n\t\t\t\t\t</label>\n\t\t\t\t\t<p>", "</p>\n\t\t\t\t</div>\n\t\t\t\t<div class='choice'>\n\t\t\t\t\t<label>\n\t\t\t\t\t\t<input type='checkbox' name='hidden'>\n\t\t\t\t\t\t<span class='checkbox'>", "</span>\n\t\t\t\t\t\t<span class='label'>", "</span>\n\t\t\t\t\t</label>\n\t\t\t\t\t<p>", "</p>\n\t\t\t\t</div>\n\t\t\t\t<div class='choice'>\n\t\t\t\t\t<label>\n\t\t\t\t\t\t<input type='checkbox' name='downloadable'>\n\t\t\t\t\t\t<span class='checkbox'>", "</span>\n\t\t\t\t\t\t<span class='label'>", "</span>\n\t\t\t\t\t</label>\n\t\t\t\t\t<p>", "</p>\n\t\t\t\t</div>\n\t\t\t\t<div class='choice'>\n\t\t\t\t\t<label>\n\t\t\t\t\t\t<input type='checkbox' name='share_button_visible'>\n\t\t\t\t\t\t<span class='checkbox'>", "</span>\n\t\t\t\t\t\t<span class='label'>", "</span>\n\t\t\t\t\t</label>\n\t\t\t\t\t<p>", "</p>\n\t\t\t\t</div>\n\t\t\t\t<div class='choice'>\n\t\t\t\t\t<label>\n\t\t\t\t\t\t<input type='checkbox' name='password'>\n\t\t\t\t\t\t<span class='checkbox'>", "</span>\n\t\t\t\t\t\t<span class='label'>", "</span>\n\t\t\t\t\t</label>\n\t\t\t\t\t<p>", "</p>\n\t\t\t\t\t<input class='text' name='passwordtext' type='text' placeholder='", "' value=''>\n\t\t\t\t</div>\n\t\t\t\t<div class='hr'><hr></div>\n\t\t\t\t<div class='switch'>\n\t\t\t\t\t<label>\n\t\t\t\t\t\t", ":&nbsp;\n\t\t\t\t\t\t<input type='checkbox' name='nsfw'>\n\t\t\t\t\t\t<span class='slider round'></span>\n\t\t\t\t\t</label>\n\t\t\t\t\t<p>", "</p>\n\t\t\t\t</div>\n\t\t\t</form>\n\t\t"]),
    _templateObject11 = _taggedTemplateLiteral(["<div class='choice'>\n\t\t\t\t\t\t\t<label>\n\t\t\t\t\t\t\t\t<input type='checkbox' name='", "'>\n\t\t\t\t\t\t\t\t<span class='checkbox'>", "</span>\n\t\t\t\t\t\t\t\t<span class='label'>", "</span>\n\t\t\t\t\t\t\t</label>\n\t\t\t\t\t\t\t<p></p>\n\t\t\t\t\t\t</div>"], ["<div class='choice'>\n\t\t\t\t\t\t\t<label>\n\t\t\t\t\t\t\t\t<input type='checkbox' name='", "'>\n\t\t\t\t\t\t\t\t<span class='checkbox'>", "</span>\n\t\t\t\t\t\t\t\t<span class='label'>", "</span>\n\t\t\t\t\t\t\t</label>\n\t\t\t\t\t\t\t<p></p>\n\t\t\t\t\t\t</div>"]),
    _templateObject12 = _taggedTemplateLiteral(["?albumIDs=", ""], ["?albumIDs=", ""]),
    _templateObject13 = _taggedTemplateLiteral(["<p>", " '$", "' ", " '$", "'?</p>"], ["<p>", " '$", "' ", " '$", "'?</p>"]),
    _templateObject14 = _taggedTemplateLiteral(["<p>", " '$", "'?</p>"], ["<p>", " '$", "'?</p>"]),
    _templateObject15 = _taggedTemplateLiteral(["<p>", " '$", "' ", "</p>"], ["<p>", " '$", "' ", "</p>"]),
    _templateObject16 = _taggedTemplateLiteral(["<p>", " $", " ", "</p>"], ["<p>", " $", " ", "</p>"]),
    _templateObject17 = _taggedTemplateLiteral(["<svg class='iconic ", "'><use xlink:href='#", "' /></svg>"], ["<svg class='iconic ", "'><use xlink:href='#", "' /></svg>"]),
    _templateObject18 = _taggedTemplateLiteral(["<div class='divider'><h1>", "</h1></div>"], ["<div class='divider'><h1>", "</h1></div>"]),
    _templateObject19 = _taggedTemplateLiteral(["<div id='", "' class='edit'>", "</div>"], ["<div id='", "' class='edit'>", "</div>"]),
    _templateObject20 = _taggedTemplateLiteral(["<div id='multiselect' style='top: ", "px; left: ", "px;'></div>"], ["<div id='multiselect' style='top: ", "px; left: ", "px;'></div>"]),
    _templateObject21 = _taggedTemplateLiteral(["\n\t\t\t<div class='album ", " ", "'\n\t\t\t\tdata-id='", "'\n\t\t\t\tdata-nsfw='", "'\n\t\t\t\tdata-tabindex='", "'>\n\t\t\t\t  ", "\n\t\t\t\t  ", "\n\t\t\t\t  ", "\n\t\t\t\t<div class='overlay'>\n\t\t\t\t\t<h1 title='$", "'>$", "</h1>\n\t\t\t\t\t<a>", "</a>\n\t\t\t\t</div>\n\t\t\t"], ["\n\t\t\t<div class='album ", " ", "'\n\t\t\t\tdata-id='", "'\n\t\t\t\tdata-nsfw='", "'\n\t\t\t\tdata-tabindex='", "'>\n\t\t\t\t  ", "\n\t\t\t\t  ", "\n\t\t\t\t  ", "\n\t\t\t\t<div class='overlay'>\n\t\t\t\t\t<h1 title='$", "'>$", "</h1>\n\t\t\t\t\t<a>", "</a>\n\t\t\t\t</div>\n\t\t\t"]),
    _templateObject22 = _taggedTemplateLiteral(["\n\t\t\t\t<div class='badges'>\n\t\t\t\t\t<a class='badge ", " icn-warning'>", "</a>\n\t\t\t\t\t<a class='badge ", " icn-star'>", "</a>\n\t\t\t\t\t<a class='badge ", "'>", "</a>\n\t\t\t\t\t<a class='badge ", " ", " icn-share'>", "</a>\n\t\t\t\t\t<a class='badge ", "'>", "</a>\n\t\t\t\t\t<a class='badge ", "'>", "</a>\n\t\t\t\t\t<a class='badge ", "'>", "</a>\n\t\t\t\t\t<a class='badge ", " icn-cover'>", "</a>\n\t\t\t\t</div>\n\t\t\t\t"], ["\n\t\t\t\t<div class='badges'>\n\t\t\t\t\t<a class='badge ", " icn-warning'>", "</a>\n\t\t\t\t\t<a class='badge ", " icn-star'>", "</a>\n\t\t\t\t\t<a class='badge ", "'>", "</a>\n\t\t\t\t\t<a class='badge ", " ", " icn-share'>", "</a>\n\t\t\t\t\t<a class='badge ", "'>", "</a>\n\t\t\t\t\t<a class='badge ", "'>", "</a>\n\t\t\t\t\t<a class='badge ", "'>", "</a>\n\t\t\t\t\t<a class='badge ", " icn-cover'>", "</a>\n\t\t\t\t</div>\n\t\t\t\t"]),
    _templateObject23 = _taggedTemplateLiteral(["\n\t\t\t\t<div class='subalbum_badge'>\n\t\t\t\t\t<a class='badge badge--folder'>", "</a>\n\t\t\t\t</div>"], ["\n\t\t\t\t<div class='subalbum_badge'>\n\t\t\t\t\t<a class='badge badge--folder'>", "</a>\n\t\t\t\t</div>"]),
    _templateObject24 = _taggedTemplateLiteral(["\n\t\t\t<div class='photo ", "' data-album-id='", "' data-id='", "' data-tabindex='", "'>\n\t\t\t\t", "\n\t\t\t\t<div class='overlay'>\n\t\t\t\t\t<h1 title='$", "'>$", "</h1>\n\t\t\t"], ["\n\t\t\t<div class='photo ", "' data-album-id='", "' data-id='", "' data-tabindex='", "'>\n\t\t\t\t", "\n\t\t\t\t<div class='overlay'>\n\t\t\t\t\t<h1 title='$", "'>$", "</h1>\n\t\t\t"]),
    _templateObject25 = _taggedTemplateLiteral(["<a><span title='Camera Date'>", "</span>", "</a>"], ["<a><span title='Camera Date'>", "</span>", "</a>"]),
    _templateObject26 = _taggedTemplateLiteral(["<a>", "</a>"], ["<a>", "</a>"]),
    _templateObject27 = _taggedTemplateLiteral(["\n\t\t\t\t<div class='badges'>\n\t\t\t\t<a class='badge ", " icn-star'>", "</a>\n\t\t\t\t<a class='badge ", " icn-share'>", "</a>\n\t\t\t\t<a class='badge ", " icn-cover'>", "</a>\n\t\t\t\t</div>\n\t\t\t\t"], ["\n\t\t\t\t<div class='badges'>\n\t\t\t\t<a class='badge ", " icn-star'>", "</a>\n\t\t\t\t<a class='badge ", " icn-share'>", "</a>\n\t\t\t\t<a class='badge ", " icn-cover'>", "</a>\n\t\t\t\t</div>\n\t\t\t\t"]),
    _templateObject28 = _taggedTemplateLiteral(["\n\t\t<div id=\"image_overlay\">\n\t\t<h1>$", "</h1>\n\t\t"], ["\n\t\t<div id=\"image_overlay\">\n\t\t<h1>$", "</h1>\n\t\t"]),
    _templateObject29 = _taggedTemplateLiteral(["<video width=\"auto\" height=\"auto\" id='image' controls class='", "' autobuffer ", " data-tabindex='", "'><source src='", "'>Your browser does not support the video tag.</video>"], ["<video width=\"auto\" height=\"auto\" id='image' controls class='", "' autobuffer ", " data-tabindex='", "'><source src='", "'>Your browser does not support the video tag.</video>"]),
    _templateObject30 = _taggedTemplateLiteral(["<img id='image' class='", "' src='img/placeholder.png' draggable='false' alt='big' data-tabindex='", "'>"], ["<img id='image' class='", "' src='img/placeholder.png' draggable='false' alt='big' data-tabindex='", "'>"]),
    _templateObject31 = _taggedTemplateLiteral(["", ""], ["", ""]),
    _templateObject32 = _taggedTemplateLiteral(["<div class='no_content fadeIn'>", ""], ["<div class='no_content fadeIn'>", ""]),
    _templateObject33 = _taggedTemplateLiteral(["<p>", "</p>"], ["<p>", "</p>"]),
    _templateObject34 = _taggedTemplateLiteral(["\n\t\t\t<h1>$", "</h1>\n\t\t\t<div class='rows'>\n\t\t\t"], ["\n\t\t\t<h1>$", "</h1>\n\t\t\t<div class='rows'>\n\t\t\t"]),
    _templateObject35 = _taggedTemplateLiteral(["\n\t\t\t\t<div class='row'>\n\t\t\t\t\t<a class='name'>", "</a>\n\t\t\t\t\t<a class='status'></a>\n\t\t\t\t\t<p class='notice'></p>\n\t\t\t\t</div>\n\t\t\t\t"], ["\n\t\t\t\t<div class='row'>\n\t\t\t\t\t<a class='name'>", "</a>\n\t\t\t\t\t<a class='status'></a>\n\t\t\t\t\t<p class='notice'></p>\n\t\t\t\t</div>\n\t\t\t\t"]),
    _templateObject36 = _taggedTemplateLiteral(["\n\t\t<div class='row'>\n\t\t\t<a class='name'>", "</a>\n\t\t\t<a class='status'></a>\n\t\t\t<p class='notice'></p>\n\t\t</div>\n\t\t"], ["\n\t\t<div class='row'>\n\t\t\t<a class='name'>", "</a>\n\t\t\t<a class='status'></a>\n\t\t\t<p class='notice'></p>\n\t\t</div>\n\t\t"]),
    _templateObject37 = _taggedTemplateLiteral(["<a class='color' data-color=\"rgb(", ",", ",", ")\" style=\"background-color:rgb(", " ", " ", ")\"></a>"], ["<a class='color' data-color=\"rgb(", ",", ",", ")\" style=\"background-color:rgb(", " ", " ", ")\"></a>"]),
    _templateObject38 = _taggedTemplateLiteral(["<div class='empty'>", "</div>"], ["<div class='empty'>", "</div>"]),
    _templateObject39 = _taggedTemplateLiteral(["<a class='", "'>$", "<span data-index='", "'>", "</span></a>"], ["<a class='", "'>$", "<span data-index='", "'>", "</span></a>"]),
    _templateObject40 = _taggedTemplateLiteral(["<a class='", "'>$", "</a>"], ["<a class='", "'>$", "</a>"]),
    _templateObject41 = _taggedTemplateLiteral(["<div class=\"users_view_line\">\n\t\t\t<p id=\"UserData", "\">\n\t\t\t<input name=\"id\" type=\"hidden\" value=\"", "\" />\n\t\t\t<input class=\"text\" name=\"username\" type=\"text\" value=\"$", "\" placeholder=\"username\" />\n\t\t\t<input class=\"text\" name=\"password\" type=\"text\" placeholder=\"new password\" />\n\t\t\t<span class=\"choice\" title=\"Allow uploads\">\n\t\t\t<label>\n\t\t\t<input type=\"checkbox\" name=\"upload\" />\n\t\t\t<span class=\"checkbox\"><svg class=\"iconic \"><use xlink:href=\"#check\"></use></svg></span>\n\t\t\t</label>\n\t\t\t</span>\n\t\t\t<span class=\"choice\" title=\"Restricted account\">\n\t\t\t<label>\n\t\t\t<input type=\"checkbox\" name=\"lock\" />\n\t\t\t<span class=\"checkbox\"><svg class=\"iconic \"><use xlink:href=\"#check\"></use></svg></span>\n\t\t\t</label>\n\t\t\t</span>\n\t\t\t</p>\n\t\t\t<a id=\"UserUpdate", "\"  class=\"basicModal__button basicModal__button_OK\">Save</a>\n\t\t\t<a id=\"UserDelete", "\"  class=\"basicModal__button basicModal__button_DEL\">Delete</a>\n\t\t</div>\n\t\t"], ["<div class=\"users_view_line\">\n\t\t\t<p id=\"UserData", "\">\n\t\t\t<input name=\"id\" type=\"hidden\" value=\"", "\" />\n\t\t\t<input class=\"text\" name=\"username\" type=\"text\" value=\"$", "\" placeholder=\"username\" />\n\t\t\t<input class=\"text\" name=\"password\" type=\"text\" placeholder=\"new password\" />\n\t\t\t<span class=\"choice\" title=\"Allow uploads\">\n\t\t\t<label>\n\t\t\t<input type=\"checkbox\" name=\"upload\" />\n\t\t\t<span class=\"checkbox\"><svg class=\"iconic \"><use xlink:href=\"#check\"></use></svg></span>\n\t\t\t</label>\n\t\t\t</span>\n\t\t\t<span class=\"choice\" title=\"Restricted account\">\n\t\t\t<label>\n\t\t\t<input type=\"checkbox\" name=\"lock\" />\n\t\t\t<span class=\"checkbox\"><svg class=\"iconic \"><use xlink:href=\"#check\"></use></svg></span>\n\t\t\t</label>\n\t\t\t</span>\n\t\t\t</p>\n\t\t\t<a id=\"UserUpdate", "\"  class=\"basicModal__button basicModal__button_OK\">Save</a>\n\t\t\t<a id=\"UserDelete", "\"  class=\"basicModal__button basicModal__button_DEL\">Delete</a>\n\t\t</div>\n\t\t"]),
    _templateObject42 = _taggedTemplateLiteral(["<div class=\"u2f_view_line\">\n\t\t\t<p id=\"CredentialData", "\">\n\t\t\t<input name=\"id\" type=\"hidden\" value=\"", "\" />\n\t\t\t<span class=\"text\">", "</span>\n\t\t\t<!--- <span class=\"choice\" title=\"Allow uploads\">\n\t\t\t<label>\n\t\t\t<input type=\"checkbox\" name=\"upload\" />\n\t\t\t<span class=\"checkbox\"><svg class=\"iconic \"><use xlink:href=\"#check\"></use></svg></span>\n\t\t\t</label>\n\t\t\t</span>\n\t\t\t<span class=\"choice\" title=\"Restricted account\">\n\t\t\t<label>\n\t\t\t<input type=\"checkbox\" name=\"lock\" />\n\t\t\t<span class=\"checkbox\"><svg class=\"iconic \"><use xlink:href=\"#check\"></use></svg></span>\n\t\t\t</label>\n\t\t\t</span>--->\n\t\t\t</p>\n\t\t\t<a id=\"CredentialDelete", "\"  class=\"basicModal__button basicModal__button_DEL\">Delete</a>\n\t\t</div>\n\t\t"], ["<div class=\"u2f_view_line\">\n\t\t\t<p id=\"CredentialData", "\">\n\t\t\t<input name=\"id\" type=\"hidden\" value=\"", "\" />\n\t\t\t<span class=\"text\">", "</span>\n\t\t\t<!--- <span class=\"choice\" title=\"Allow uploads\">\n\t\t\t<label>\n\t\t\t<input type=\"checkbox\" name=\"upload\" />\n\t\t\t<span class=\"checkbox\"><svg class=\"iconic \"><use xlink:href=\"#check\"></use></svg></span>\n\t\t\t</label>\n\t\t\t</span>\n\t\t\t<span class=\"choice\" title=\"Restricted account\">\n\t\t\t<label>\n\t\t\t<input type=\"checkbox\" name=\"lock\" />\n\t\t\t<span class=\"checkbox\"><svg class=\"iconic \"><use xlink:href=\"#check\"></use></svg></span>\n\t\t\t</label>\n\t\t\t</span>--->\n\t\t\t</p>\n\t\t\t<a id=\"CredentialDelete", "\"  class=\"basicModal__button basicModal__button_DEL\">Delete</a>\n\t\t</div>\n\t\t"]),
    _templateObject43 = _taggedTemplateLiteral(["\n\t\t\t           ", "\n\t\t\t           <img class='cover' width='16' height='16' src='", "'>\n\t\t\t           <div class='title'>$", "</div>\n\t\t\t           "], ["\n\t\t\t           ", "\n\t\t\t           <img class='cover' width='16' height='16' src='", "'>\n\t\t\t           <div class='title'>$", "</div>\n\t\t\t           "]),
    _templateObject44 = _taggedTemplateLiteral(["$", "", ""], ["$", "", ""]),
    _templateObject45 = _taggedTemplateLiteral(["\n\t\t<a id=\"text_settings_close\" class=\"closetxt\" data-tabindex=\"-1\">", "</a>\n\t\t<a id=\"button_settings_close\" class=\"closebtn\" data-tabindex=\"20\">&times;</a>\n\t\t<a class=\"linkMenu\" id=\"button_settings_open\" data-tabindex=\"-1\"><svg class=\"iconic\"><use xlink:href=\"#cog\"></use></svg>", "</a>"], ["\n\t\t<a id=\"text_settings_close\" class=\"closetxt\" data-tabindex=\"-1\">", "</a>\n\t\t<a id=\"button_settings_close\" class=\"closebtn\" data-tabindex=\"20\">&times;</a>\n\t\t<a class=\"linkMenu\" id=\"button_settings_open\" data-tabindex=\"-1\"><svg class=\"iconic\"><use xlink:href=\"#cog\"></use></svg>", "</a>"]),
    _templateObject46 = _taggedTemplateLiteral(["\n\t\t<a class=\"linkMenu\" id=\"button_users\" data-tabindex=\"-1\">", "", " </a>\n\t\t<a class=\"linkMenu\" id=\"button_u2f\" data-tabindex=\"-1\">", "", " </a>\n\t\t<a class=\"linkMenu\" id=\"button_sharing\" data-tabindex=\"-1\">", "", "</a>"], ["\n\t\t<a class=\"linkMenu\" id=\"button_users\" data-tabindex=\"-1\">", "", " </a>\n\t\t<a class=\"linkMenu\" id=\"button_u2f\" data-tabindex=\"-1\">", "", " </a>\n\t\t<a class=\"linkMenu\" id=\"button_sharing\" data-tabindex=\"-1\">", "", "</a>"]),
    _templateObject47 = _taggedTemplateLiteral(["\n\t\t<a class=\"linkMenu\" id=\"button_logs\" data-tabindex=\"-1\">", "", "</a>\n\t\t<a class=\"linkMenu\" id=\"button_diagnostics\" data-tabindex=\"-1\">", "", "</a>\n\t\t<a class=\"linkMenu\" id=\"button_about\" data-tabindex=\"-1\">", "", "</a>\n\t\t<a class=\"linkMenu\" id=\"button_signout\" data-tabindex=\"21\">", "", "</a>"], ["\n\t\t<a class=\"linkMenu\" id=\"button_logs\" data-tabindex=\"-1\">", "", "</a>\n\t\t<a class=\"linkMenu\" id=\"button_diagnostics\" data-tabindex=\"-1\">", "", "</a>\n\t\t<a class=\"linkMenu\" id=\"button_about\" data-tabindex=\"-1\">", "", "</a>\n\t\t<a class=\"linkMenu\" id=\"button_signout\" data-tabindex=\"21\">", "", "</a>"]),
    _templateObject48 = _taggedTemplateLiteral(["\n\t\t<a class=\"linkMenu\" id=\"button_update\"  data-tabindex=\"-1\">", "", "</a>\n\t\t"], ["\n\t\t<a class=\"linkMenu\" id=\"button_update\"  data-tabindex=\"-1\">", "", "</a>\n\t\t"]),
    _templateObject49 = _taggedTemplateLiteral(["\n\t\t\t\t<h1>Lychee ", "</h1>\n\t\t\t\t<div class='version'><span><a target='_blank' href='", "'>", "</a></span></div>\n\t\t\t\t<h1>", "</h1>\n\t\t\t\t<p><a target='_blank' href='", "'>Lychee</a> ", "</p>\n\t\t\t  "], ["\n\t\t\t\t<h1>Lychee ", "</h1>\n\t\t\t\t<div class='version'><span><a target='_blank' href='", "'>", "</a></span></div>\n\t\t\t\t<h1>", "</h1>\n\t\t\t\t<p><a target='_blank' href='", "'>Lychee</a> ", "</p>\n\t\t\t  "]),
    _templateObject50 = _taggedTemplateLiteral(["\n\t\t\t<a class='signInKeyLess' id='signInKeyLess'>", "</a>\n\t\t\t<form>\n\t\t\t\t<p class='signIn'>\n\t\t\t\t\t<input class='text' name='username' autocomplete='on' type='text' placeholder='$", "' autocapitalize='off' data-tabindex='", "'>\n\t\t\t\t\t<input class='text' name='password' autocomplete='current-password' type='password' placeholder='$", "' data-tabindex='", "'>\n\t\t\t\t</p>\n\t\t\t\t<p class='version'>Lychee ", "<span> &#8211; <a target='_blank' href='", "' data-tabindex='-1'>", "</a><span></p>\n\t\t\t</form>\n\t\t\t"], ["\n\t\t\t<a class='signInKeyLess' id='signInKeyLess'>", "</a>\n\t\t\t<form>\n\t\t\t\t<p class='signIn'>\n\t\t\t\t\t<input class='text' name='username' autocomplete='on' type='text' placeholder='$", "' autocapitalize='off' data-tabindex='", "'>\n\t\t\t\t\t<input class='text' name='password' autocomplete='current-password' type='password' placeholder='$", "' data-tabindex='", "'>\n\t\t\t\t</p>\n\t\t\t\t<p class='version'>Lychee ", "<span> &#8211; <a target='_blank' href='", "' data-tabindex='-1'>", "</a><span></p>\n\t\t\t</form>\n\t\t\t"]),
    _templateObject51 = _taggedTemplateLiteral(["<link data-prefetch rel=\"prefetch\" href=\"", "\">"], ["<link data-prefetch rel=\"prefetch\" href=\"", "\">"]),
    _templateObject52 = _taggedTemplateLiteral(["<p>", " '", "'", "</p>"], ["<p>", " '", "'", "</p>"]),
    _templateObject53 = _taggedTemplateLiteral(["<p>", " ", " ", "</p>"], ["<p>", " ", " ", "</p>"]),
    _templateObject54 = _taggedTemplateLiteral(["<input class='text' name='title' type='text' maxlength='100' placeholder='Title' value='$", "'>"], ["<input class='text' name='title' type='text' maxlength='100' placeholder='Title' value='$", "'>"]),
    _templateObject55 = _taggedTemplateLiteral(["<p>", " ", " ", " ", "</p>"], ["<p>", " ", " ", " ", "</p>"]),
    _templateObject56 = _taggedTemplateLiteral(["\n\t\t<div class='switch'>\n\t\t\t<label>\n\t\t\t\t<span class='label'>", ":</span>\n\t\t\t\t<input type='checkbox' name='public'>\n\t\t\t\t<span class='slider round'></span>\n\t\t\t</label>\n\t\t\t<p>", "</p>\n\t\t</div>\n\t"], ["\n\t\t<div class='switch'>\n\t\t\t<label>\n\t\t\t\t<span class='label'>", ":</span>\n\t\t\t\t<input type='checkbox' name='public'>\n\t\t\t\t<span class='slider round'></span>\n\t\t\t</label>\n\t\t\t<p>", "</p>\n\t\t</div>\n\t"]),
    _templateObject57 = _taggedTemplateLiteral(["\n\t\t<div class='choice'>\n\t\t\t<label>\n\t\t\t\t<input type='checkbox' name='full_photo' disabled>\n\t\t\t\t<span class='checkbox'>", "</span>\n\t\t\t\t<span class='label'>", "</span>\n\t\t\t</label>\n\t\t\t<p>", "</p>\n\t\t</div>\n\t\t<div class='choice'>\n\t\t\t<label>\n\t\t\t\t<input type='checkbox' name='hidden' disabled>\n\t\t\t\t<span class='checkbox'>", "</span>\n\t\t\t\t<span class='label'>", "</span>\n\t\t\t</label>\n\t\t\t<p>", "</p>\n\t\t</div>\n\t\t<div class='choice'>\n\t\t\t<label>\n\t\t\t\t<input type='checkbox' name='downloadable' disabled>\n\t\t\t\t<span class='checkbox'>", "</span>\n\t\t\t\t<span class='label'>", "</span>\n\t\t\t</label>\n\t\t\t<p>", "</p>\n\t\t</div>\n\t\t<div class='choice'>\n\t\t\t<label>\n\t\t\t\t<input type='checkbox' name='share_button_visible' disabled>\n\t\t\t\t<span class='checkbox'>", "</span>\n\t\t\t\t<span class='label'>", "</span>\n\t\t\t</label>\n\t\t\t<p>", "</p>\n\t\t</div>\n\t\t<div class='choice'>\n\t\t\t<label>\n\t\t\t\t<input type='checkbox' name='password' disabled>\n\t\t\t\t<span class='checkbox'>", "</span>\n\t\t\t\t<span class='label'>", "</span>\n\t\t\t</label>\n\t\t\t<p>", "</p>\n\t\t</div>\n\t"], ["\n\t\t<div class='choice'>\n\t\t\t<label>\n\t\t\t\t<input type='checkbox' name='full_photo' disabled>\n\t\t\t\t<span class='checkbox'>", "</span>\n\t\t\t\t<span class='label'>", "</span>\n\t\t\t</label>\n\t\t\t<p>", "</p>\n\t\t</div>\n\t\t<div class='choice'>\n\t\t\t<label>\n\t\t\t\t<input type='checkbox' name='hidden' disabled>\n\t\t\t\t<span class='checkbox'>", "</span>\n\t\t\t\t<span class='label'>", "</span>\n\t\t\t</label>\n\t\t\t<p>", "</p>\n\t\t</div>\n\t\t<div class='choice'>\n\t\t\t<label>\n\t\t\t\t<input type='checkbox' name='downloadable' disabled>\n\t\t\t\t<span class='checkbox'>", "</span>\n\t\t\t\t<span class='label'>", "</span>\n\t\t\t</label>\n\t\t\t<p>", "</p>\n\t\t</div>\n\t\t<div class='choice'>\n\t\t\t<label>\n\t\t\t\t<input type='checkbox' name='share_button_visible' disabled>\n\t\t\t\t<span class='checkbox'>", "</span>\n\t\t\t\t<span class='label'>", "</span>\n\t\t\t</label>\n\t\t\t<p>", "</p>\n\t\t</div>\n\t\t<div class='choice'>\n\t\t\t<label>\n\t\t\t\t<input type='checkbox' name='password' disabled>\n\t\t\t\t<span class='checkbox'>", "</span>\n\t\t\t\t<span class='label'>", "</span>\n\t\t\t</label>\n\t\t\t<p>", "</p>\n\t\t</div>\n\t"]),
    _templateObject58 = _taggedTemplateLiteral(["\n\t\t\t<p class='less'>", "</p>\n\t\t\t", "\n\t\t\t", "\n\t\t"], ["\n\t\t\t<p class='less'>", "</p>\n\t\t\t", "\n\t\t\t", "\n\t\t"]),
    _templateObject59 = _taggedTemplateLiteral(["\n\t\t\t", "\n\t\t\t<p class='photoPublic'>", "</p>\n\t\t\t", "\n\t\t"], ["\n\t\t\t", "\n\t\t\t<p class='photoPublic'>", "</p>\n\t\t\t", "\n\t\t"]),
    _templateObject60 = _taggedTemplateLiteral(["<p>", " <input class='text' name='description' type='text' maxlength='800' placeholder='", "' value='$", "'></p>"], ["<p>", " <input class='text' name='description' type='text' maxlength='800' placeholder='", "' value='$", "'></p>"]),
    _templateObject61 = _taggedTemplateLiteral(["<input class='text' name='tags' type='text' maxlength='800' placeholder='Tags' value='$", "'>"], ["<input class='text' name='tags' type='text' maxlength='800' placeholder='Tags' value='$", "'>"]),
    _templateObject62 = _taggedTemplateLiteral(["\n\t\t\t\t<a class='basicModal__button' id='", "' title='", "'>\n\t\t\t\t\t", "", "\n\t\t\t\t</a>\n\t\t\t"], ["\n\t\t\t\t<a class='basicModal__button' id='", "' title='", "'>\n\t\t\t\t\t", "", "\n\t\t\t\t</a>\n\t\t\t"]),
    _templateObject63 = _taggedTemplateLiteral(["\n\t\t\t<div class='downloads'>\n\t\t"], ["\n\t\t\t<div class='downloads'>\n\t\t"]),
    _templateObject64 = _taggedTemplateLiteral(["\n\t\t\t</div>\n\t\t"], ["\n\t\t\t</div>\n\t\t"]),
    _templateObject65 = _taggedTemplateLiteral(["?photoIDs=", "&kind=", ""], ["?photoIDs=", "&kind=", ""]),
    _templateObject66 = _taggedTemplateLiteral(["\n\t\t\t<p>\n\t\t\t\t", "\n\t\t\t\t<br />\n\t\t\t\t<input class='text' readonly value='", "'>\n\t\t\t\t<a class='basicModal__button' title='", "'>\n\t\t\t\t\t", "\n\t\t\t\t</a>\n\t\t\t</p>\n\t\t"], ["\n\t\t\t<p>\n\t\t\t\t", "\n\t\t\t\t<br />\n\t\t\t\t<input class='text' readonly value='", "'>\n\t\t\t\t<a class='basicModal__button' title='", "'>\n\t\t\t\t\t", "\n\t\t\t\t</a>\n\t\t\t</p>\n\t\t"]),
    _templateObject67 = _taggedTemplateLiteral(["\n\t\t<div class='directLinks'>\n\t\t\t", "\n\t\t\t<p class='less'>\n\t\t\t\t", "\n\t\t\t</p>\n\t\t\t<div class='imageLinks'>\n\t"], ["\n\t\t<div class='directLinks'>\n\t\t\t", "\n\t\t\t<p class='less'>\n\t\t\t\t", "\n\t\t\t</p>\n\t\t\t<div class='imageLinks'>\n\t"]),
    _templateObject68 = _taggedTemplateLiteral(["\n\t\t</div>\n\t\t</div>\n\t"], ["\n\t\t</div>\n\t\t</div>\n\t"]),
    _templateObject69 = _taggedTemplateLiteral(["<p style=\"color: #d92c34; font-size: 1.3em; font-weight: bold; text-transform: capitalize; text-align: center;\">", "</p>"], ["<p style=\"color: #d92c34; font-size: 1.3em; font-weight: bold; text-transform: capitalize; text-align: center;\">", "</p>"]),
    _templateObject70 = _taggedTemplateLiteral(["<span class='attr_", "_separator'>, </span>"], ["<span class='attr_", "_separator'>, </span>"]),
    _templateObject71 = _taggedTemplateLiteral(["<span class='attr_", " search'>$", "</span>"], ["<span class='attr_", " search'>$", "</span>"]),
    _templateObject72 = _taggedTemplateLiteral(["<span class='attr_", "'>$", "</span>"], ["<span class='attr_", "'>$", "</span>"]),
    _templateObject73 = _taggedTemplateLiteral(["\n\t\t\t\t\t\t <tr>\n\t\t\t\t\t\t\t <td>", "</td>\n\t\t\t\t\t\t\t <td>", "</td>\n\t\t\t\t\t\t </tr>\n\t\t\t\t\t\t "], ["\n\t\t\t\t\t\t <tr>\n\t\t\t\t\t\t\t <td>", "</td>\n\t\t\t\t\t\t\t <td>", "</td>\n\t\t\t\t\t\t </tr>\n\t\t\t\t\t\t "]),
    _templateObject74 = _taggedTemplateLiteral(["\n\t\t\t\t <div class='sidebar__divider'>\n\t\t\t\t\t <h1>", "</h1>\n\t\t\t\t </div>\n\t\t\t\t <div id='tags'>\n\t\t\t\t\t <div class='attr_", "'>", "</div>\n\t\t\t\t\t ", "\n\t\t\t\t </div>\n\t\t\t\t "], ["\n\t\t\t\t <div class='sidebar__divider'>\n\t\t\t\t\t <h1>", "</h1>\n\t\t\t\t </div>\n\t\t\t\t <div id='tags'>\n\t\t\t\t\t <div class='attr_", "'>", "</div>\n\t\t\t\t\t ", "\n\t\t\t\t </div>\n\t\t\t\t "]),
    _templateObject75 = _taggedTemplateLiteral(["\n\t\t\t\t<div class='sidebar__divider'>\n\t\t\t\t\t <h1>", "</h1>\n\t\t\t\t</div>\n\t\t\t\t<div class='palette'>\n\t\t\t\t \t", "\n\t\t\t\t</div>\n\t\t"], ["\n\t\t\t\t<div class='sidebar__divider'>\n\t\t\t\t\t <h1>", "</h1>\n\t\t\t\t</div>\n\t\t\t\t<div class='palette'>\n\t\t\t\t \t", "\n\t\t\t\t</div>\n\t\t"]),
    _templateObject76 = _taggedTemplateLiteral(["<h1>", "</h1>"], ["<h1>", "</h1>"]),
    _templateObject77 = _taggedTemplateLiteral(["<p>"], ["<p>"]),
    _templateObject78 = _taggedTemplateLiteral(["\n\t\t\t<p class='importServer'>\n\t\t\t\t", "\n\t\t\t\t<input class='text' name='path' type='text' placeholder='", "' value='", "uploads/import/'>\n\t\t\t</p>\n\t\t"], ["\n\t\t\t<p class='importServer'>\n\t\t\t\t", "\n\t\t\t\t<input class='text' name='path' type='text' placeholder='", "' value='", "uploads/import/'>\n\t\t\t</p>\n\t\t"]),
    _templateObject79 = _taggedTemplateLiteral(["\n\t\t\t<div class='choice'>\n\t\t\t\t<label>\n\t\t\t\t\t<input type='checkbox' name='delete' onchange='upload.check()'>\n\t\t\t\t\t<span class='checkbox'>", "</span>\n\t\t\t\t\t<span class='label'>", "</span>\n\t\t\t\t</label>\n\t\t\t\t<p>\n\t\t\t\t\t", "\n\t\t\t\t</p>\n\t\t\t</div>\n\t\t\t<div class='choice'>\n\t\t\t\t<label>\n\t\t\t\t\t<input type='checkbox' name='symlinks' onchange='upload.check()'>\n\t\t\t\t\t<span class='checkbox'>", "</span>\n\t\t\t\t\t<span class='label'>", "</span>\n\t\t\t\t</label>\n\t\t\t\t<p>\n\t\t\t\t\t", "\n\t\t\t\t</p>\n\t\t\t</div>\n\t\t\t<div class='choice'>\n\t\t\t\t<label>\n\t\t\t\t\t<input type='checkbox' name='skipduplicates' onchange='upload.check()'>\n\t\t\t\t\t<span class='checkbox'>", "</span>\n\t\t\t\t\t<span class='label'>", "</span>\n\t\t\t\t</label>\n\t\t\t\t<p>\n\t\t\t\t\t", "\n\t\t\t\t</p>\n\t\t\t</div>\n\t\t\t<div class='choice'>\n\t\t\t\t<label>\n\t\t\t\t\t<input type='checkbox' name='resyncmetadata' onchange='upload.check()'>\n\t\t\t\t\t<span class='checkbox'>", "</span>\n\t\t\t\t\t<span class='label'>", "</span>\n\t\t\t\t</label>\n\t\t\t\t<p>\n\t\t\t\t\t", "\n\t\t\t\t</p>\n\t\t\t</div>\n\t\t"], ["\n\t\t\t<div class='choice'>\n\t\t\t\t<label>\n\t\t\t\t\t<input type='checkbox' name='delete' onchange='upload.check()'>\n\t\t\t\t\t<span class='checkbox'>", "</span>\n\t\t\t\t\t<span class='label'>", "</span>\n\t\t\t\t</label>\n\t\t\t\t<p>\n\t\t\t\t\t", "\n\t\t\t\t</p>\n\t\t\t</div>\n\t\t\t<div class='choice'>\n\t\t\t\t<label>\n\t\t\t\t\t<input type='checkbox' name='symlinks' onchange='upload.check()'>\n\t\t\t\t\t<span class='checkbox'>", "</span>\n\t\t\t\t\t<span class='label'>", "</span>\n\t\t\t\t</label>\n\t\t\t\t<p>\n\t\t\t\t\t", "\n\t\t\t\t</p>\n\t\t\t</div>\n\t\t\t<div class='choice'>\n\t\t\t\t<label>\n\t\t\t\t\t<input type='checkbox' name='skipduplicates' onchange='upload.check()'>\n\t\t\t\t\t<span class='checkbox'>", "</span>\n\t\t\t\t\t<span class='label'>", "</span>\n\t\t\t\t</label>\n\t\t\t\t<p>\n\t\t\t\t\t", "\n\t\t\t\t</p>\n\t\t\t</div>\n\t\t\t<div class='choice'>\n\t\t\t\t<label>\n\t\t\t\t\t<input type='checkbox' name='resyncmetadata' onchange='upload.check()'>\n\t\t\t\t\t<span class='checkbox'>", "</span>\n\t\t\t\t\t<span class='label'>", "</span>\n\t\t\t\t</label>\n\t\t\t\t<p>\n\t\t\t\t\t", "\n\t\t\t\t</p>\n\t\t\t</div>\n\t\t"]),
    _templateObject80 = _taggedTemplateLiteral(["url(\"", "\")"], ["url(\"", "\")"]),
    _templateObject81 = _taggedTemplateLiteral(["linear-gradient(to bottom, rgba(0, 0, 0, .4), rgba(0, 0, 0, .4)), url(\"", "\")"], ["linear-gradient(to bottom, rgba(0, 0, 0, .4), rgba(0, 0, 0, .4)), url(\"", "\")"]),
    _templateObject82 = _taggedTemplateLiteral(["\n\t\t\t<div class=\"setCSS\">\n\t\t\t\t<a id=\"basicModal__action_more\" class=\"basicModal__button basicModal__button_MORE\">", "</a>\n\t\t\t</div>\n\t\t\t"], ["\n\t\t\t<div class=\"setCSS\">\n\t\t\t\t<a id=\"basicModal__action_more\" class=\"basicModal__button basicModal__button_MORE\">", "</a>\n\t\t\t</div>\n\t\t\t"]),
    _templateObject83 = _taggedTemplateLiteral(["\n\t\t\t\t<div id=\"fullSettings\">\n\t\t\t\t<div class=\"setting_line\">\n\t\t\t\t<p class=\"warning\">\n\t\t\t\t", "\n\t\t\t\t</p>\n\t\t\t\t</div>\n\t\t\t\t"], ["\n\t\t\t\t<div id=\"fullSettings\">\n\t\t\t\t<div class=\"setting_line\">\n\t\t\t\t<p class=\"warning\">\n\t\t\t\t", "\n\t\t\t\t</p>\n\t\t\t\t</div>\n\t\t\t\t"]),
    _templateObject84 = _taggedTemplateLiteral(["\n\t\t\t\t\t\t<div class=\"setting_category\">\n\t\t\t\t\t\t<p>\n\t\t\t\t\t\t$", "\n\t\t\t\t\t\t</p>\n\t\t\t\t\t\t</div>"], ["\n\t\t\t\t\t\t<div class=\"setting_category\">\n\t\t\t\t\t\t<p>\n\t\t\t\t\t\t$", "\n\t\t\t\t\t\t</p>\n\t\t\t\t\t\t</div>"]),
    _templateObject85 = _taggedTemplateLiteral(["\n\t\t\t<div class=\"setting_line\">\n\t\t\t\t<p>\n\t\t\t\t<span class=\"text\">$", "</span>\n\t\t\t\t<input class=\"text\" name=\"$", "\" type=\"text\" value=\"$", "\" placeholder=\"\" />\n\t\t\t\t</p>\n\t\t\t</div>\n\t\t"], ["\n\t\t\t<div class=\"setting_line\">\n\t\t\t\t<p>\n\t\t\t\t<span class=\"text\">$", "</span>\n\t\t\t\t<input class=\"text\" name=\"$", "\" type=\"text\" value=\"$", "\" placeholder=\"\" />\n\t\t\t\t</p>\n\t\t\t</div>\n\t\t"]),
    _templateObject86 = _taggedTemplateLiteral(["\n\t\t\t<a id=\"FullSettingsSave_button\"  class=\"basicModal__button basicModal__button_SAVE\">", "</a>\n\t\t</div>\n\t\t\t"], ["\n\t\t\t<a id=\"FullSettingsSave_button\"  class=\"basicModal__button basicModal__button_SAVE\">", "</a>\n\t\t</div>\n\t\t\t"]),
    _templateObject87 = _taggedTemplateLiteral(["<div class=\"clear_logs_update\"><a id=\"Clean_Noise\" class=\"basicModal__button\">", "</a></div>"], ["<div class=\"clear_logs_update\"><a id=\"Clean_Noise\" class=\"basicModal__button\">", "</a></div>"]),
    _templateObject88 = _taggedTemplateLiteral(["<a id=\"", "Update_Lychee\" class=\"basicModal__button\">", "</a>"], ["<a id=\"", "Update_Lychee\" class=\"basicModal__button\">", "</a>"]);

function _taggedTemplateLiteral(strings, raw) { return Object.freeze(Object.defineProperties(strings, { raw: { value: Object.freeze(raw) } })); }

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

function gup(b) {
	b = b.replace(/[\[]/, "\\[").replace(/[\]]/, "\\]");

	var a = "[\\?&]" + b + "=([^&#]*)";
	var d = new RegExp(a);
	var c = d.exec(window.location.href);

	if (c === null) return "";else return c[1];
}

/**
 * @description Takes care of every action an album can handle and execute.
 */

var album = {
	json: null
};

album.isSmartID = function (id) {
	return id === "unsorted" || id === "starred" || id === "public" || id === "recent";
};

album.getParent = function () {
	if (album.json == null || album.isSmartID(album.json.id) === true || !album.json.parent_id || album.json.parent_id === 0) {
		return "";
	}
	return album.json.parent_id;
};

album.getID = function () {
	var id = null;

	// this is a Lambda
	var isID = function isID(_id) {
		if (album.isSmartID(_id)) {
			return true;
		}
		return $.isNumeric(_id);
	};

	if (_photo.json) id = _photo.json.album;else if (album.json) id = album.json.id;else if (mapview.albumID) id = mapview.albumID;

	// Search
	if (isID(id) === false) id = $(".album:hover, .album.active").attr("data-id");
	if (isID(id) === false) id = $(".photo:hover, .photo.active").attr("data-album-id");

	if (isID(id) === true) return id;else return false;
};

album.isTagAlbum = function () {
	return album.json && album.json.tag_album && album.json.tag_album === "1";
};

album.getByID = function (photoID) {
	// Function returns the JSON of a photo

	if (photoID == null || !album.json || !album.json.photos) {
		lychee.error("Error: Album json not found !");
		return undefined;
	}

	var i = 0;
	while (i < album.json.photos.length) {
		if (parseInt(album.json.photos[i].id) === parseInt(photoID)) {
			return album.json.photos[i];
		}
		i++;
	}

	lychee.error("Error: photo " + photoID + " not found !");
	return undefined;
};

album.getSubByID = function (albumID) {
	// Function returns the JSON of a subalbum

	if (albumID == null || !album.json || !album.json.albums) {
		lychee.error("Error: Album json not found!");
		return undefined;
	}

	var i = 0;
	while (i < album.json.albums.length) {
		if (parseInt(album.json.albums[i].id) === parseInt(albumID)) {
			return album.json.albums[i];
		}
		i++;
	}

	lychee.error("Error: album " + albumID + " not found!");
	return undefined;
};

// noinspection DuplicatedCode
album.deleteByID = function (photoID) {
	if (photoID == null || !album.json || !album.json.photos) {
		lychee.error("Error: Album json not found !");
		return false;
	}

	var deleted = false;

	$.each(album.json.photos, function (i) {
		if (parseInt(album.json.photos[i].id) === parseInt(photoID)) {
			album.json.photos.splice(i, 1);
			deleted = true;
			return false;
		}
	});

	return deleted;
};

// noinspection DuplicatedCode
album.deleteSubByID = function (albumID) {
	if (albumID == null || !album.json || !album.json.albums) {
		lychee.error("Error: Album json not found !");
		return false;
	}

	var deleted = false;

	$.each(album.json.albums, function (i) {
		if (parseInt(album.json.albums[i].id) === parseInt(albumID)) {
			album.json.albums.splice(i, 1);
			deleted = true;
			return false;
		}
	});

	return deleted;
};

album.load = function (albumID) {
	var refresh = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : false;

	var params = {
		albumID: albumID,
		password: ""
	};

	var processData = function processData(data) {
		if (data === "Warning: Wrong password!") {
			// User hit Cancel at the password prompt
			return false;
		}

		if (data === "Warning: Album private!") {
			if (document.location.hash.replace("#", "").split("/")[1] !== undefined) {
				// Display photo only
				lychee.setMode("view");
				lychee.footer_hide();
			} else {
				// Album not public
				lychee.content.show();
				lychee.footer_show();
				if (!visible.albums() && !visible.album()) lychee.goto();
			}
			return false;
		}

		album.json = data;

		if (refresh === false) {
			lychee.animate(".content", "contentZoomOut");
		}
		var waitTime = 300;

		// Skip delay when refresh is true
		// Skip delay when opening a blank Lychee
		if (refresh === true) waitTime = 0;
		if (!visible.albums() && !visible.photo() && !visible.album()) waitTime = 0;

		setTimeout(function () {
			view.album.init();

			if (refresh === false) {
				lychee.animate(lychee.content, "contentZoomIn");
				header.setMode("album");
			}

			tabindex.makeFocusable(lychee.content);
			if (lychee.active_focus_on_page_load) {
				// Put focus on first element - either album or photo
				var _first_album = $(".album:first");
				if (_first_album.length !== 0) {
					_first_album.focus();
				} else {
					first_photo = $(".photo:first");
					if (first_photo.length !== 0) {
						first_photo.focus();
					}
				}
			}
		}, waitTime);
	};

	api.post("Album::get", params, function (data) {
		if (data === "Warning: Wrong password!") {
			password.getDialog(albumID, function () {
				params.password = password.value;

				api.post("Album::get", params, function (_data) {
					albums.refresh();
					processData(_data);
				});
			});
		} else {
			processData(data);
			// save scroll position for this URL
			if (data && data.albums && data.albums.length > 0) {
				setTimeout(function () {
					var urls = JSON.parse(localStorage.getItem("scroll"));
					var urlWindow = window.location.href;

					if (urls != null && urls[urlWindow]) {
						$(window).scrollTop(urls[urlWindow]);
					}
				}, 500);
			}

			tabindex.makeFocusable(lychee.content);

			if (lychee.active_focus_on_page_load) {
				// Put focus on first element - either album or photo
				first_album = $(".album:first");
				if (first_album.length !== 0) {
					first_album.focus();
				} else {
					first_photo = $(".photo:first");
					if (first_photo.length !== 0) {
						first_photo.focus();
					}
				}
			}
		}
	});
};

album.parse = function () {
	if (!album.json.title) album.json.title = lychee.locale["UNTITLED"];
};

album.add = function () {
	var IDs = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : null;
	var callback = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : null;

	var action = function action(data) {
		// let title = data.title;

		var isNumber = function isNumber(n) {
			return !isNaN(parseInt(n, 10)) && isFinite(n);
		};

		if (!data.title.trim()) {
			basicModal.error("title");
			return;
		}

		basicModal.close();

		var params = {
			title: data.title,
			parent_id: 0
		};

		if (visible.albums() || album.isSmartID(album.json.id)) {
			params.parent_id = 0;
		} else if (visible.album()) {
			params.parent_id = album.json.id;
		} else if (visible.photo()) {
			params.parent_id = _photo.json.album;
		}

		api.post("Album::add", params, function (_data) {
			if (_data !== false && isNumber(_data)) {
				if (IDs != null && callback != null) {
					callback(IDs, _data, false); // we do not confirm
				} else {
					albums.refresh();
					lychee.goto(_data);
				}
			} else {
				lychee.error(null, params, _data);
			}
		});
	};

	basicModal.show({
		body: lychee.html(_templateObject, lychee.locale["TITLE_NEW_ALBUM"]),
		buttons: {
			action: {
				title: lychee.locale["CREATE_ALBUM"],
				fn: action
			},
			cancel: {
				title: lychee.locale["CANCEL"],
				fn: basicModal.close
			}
		}
	});
};

album.addByTags = function () {
	var action = function action(data) {
		if (!data.title.trim()) {
			basicModal.error("title");
			return;
		}
		if (!data.tags.trim()) {
			basicModal.error("tags");
			return;
		}

		basicModal.close();

		var params = {
			title: data.title,
			tags: data.tags
		};

		api.post("Album::addByTags", params, function (_data) {
			var isNumber = function isNumber(n) {
				return !isNaN(parseInt(n, 10)) && isFinite(n);
			};
			if (_data !== false && isNumber(_data)) {
				albums.refresh();
				lychee.goto(_data);
			} else {
				lychee.error(null, params, _data);
			}
		});
	};

	basicModal.show({
		body: lychee.html(_templateObject2, lychee.locale["TITLE_NEW_ALBUM"]),
		buttons: {
			action: {
				title: lychee.locale["CREATE_TAG_ALBUM"],
				fn: action
			},
			cancel: {
				title: lychee.locale["CANCEL"],
				fn: basicModal.close
			}
		}
	});
};

album.setShowTags = function (albumID) {
	var oldShowTags = album.json.show_tags;

	var action = function action(data) {
		if (!data.show_tags.trim()) {
			basicModal.error("show_tags");
			return;
		}

		var show_tags = data.show_tags;
		basicModal.close();

		if (visible.album()) {
			album.json.show_tags = show_tags;
			view.album.show_tags();
		}
		var params = {
			albumID: albumID,
			show_tags: show_tags
		};

		api.post("Album::setShowTags", params, function (_data) {
			if (_data !== true) {
				lychee.error(null, params, _data);
			} else {
				album.reload();
			}
		});
	};

	basicModal.show({
		body: lychee.html(_templateObject3, lychee.locale["ALBUM_NEW_SHOWTAGS"], oldShowTags),
		buttons: {
			action: {
				title: lychee.locale["ALBUM_SET_SHOWTAGS"],
				fn: action
			},
			cancel: {
				title: lychee.locale["CANCEL"],
				fn: basicModal.close
			}
		}
	});
};

album.setTitle = function (albumIDs) {
	var oldTitle = "";
	var msg = "";

	if (!albumIDs) return false;
	if (!(albumIDs instanceof Array)) {
		albumIDs = [albumIDs];
	}

	if (albumIDs.length === 1) {
		// Get old title if only one album is selected
		if (album.json) {
			if (parseInt(album.getID()) === parseInt(albumIDs[0])) {
				oldTitle = album.json.title;
			} else oldTitle = album.getSubByID(albumIDs[0]).title;
		}
		if (!oldTitle && albums.json) oldTitle = albums.getByID(albumIDs[0]).title;
	}

	var action = function action(data) {
		if (!data.title.trim()) {
			basicModal.error("title");
			return;
		}

		basicModal.close();

		var newTitle = data.title;

		if (visible.album()) {
			if (albumIDs.length === 1 && parseInt(album.getID()) === parseInt(albumIDs[0])) {
				// Rename only one album

				album.json.title = newTitle;
				view.album.title();

				if (albums.json) albums.getByID(albumIDs[0]).title = newTitle;
			} else {
				albumIDs.forEach(function (id) {
					album.getSubByID(id).title = newTitle;
					view.album.content.titleSub(id);

					if (albums.json) albums.getByID(id).title = newTitle;
				});
			}
		} else if (visible.albums()) {
			// Rename all albums

			albumIDs.forEach(function (id) {
				albums.getByID(id).title = newTitle;
				view.albums.content.title(id);
			});
		}

		var params = {
			albumIDs: albumIDs.join(),
			title: newTitle
		};

		api.post("Album::setTitle", params, function (_data) {
			if (_data !== true) {
				lychee.error(null, params, _data);
			}
		});
	};

	var input = lychee.html(_templateObject4, lychee.locale["ALBUM_TITLE"], oldTitle);

	if (albumIDs.length === 1) msg = lychee.html(_templateObject5, lychee.locale["ALBUM_NEW_TITLE"], input);else msg = lychee.html(_templateObject6, lychee.locale["ALBUMS_NEW_TITLE_1"], albumIDs.length, lychee.locale["ALBUMS_NEW_TITLE_2"], input);

	basicModal.show({
		body: msg,
		buttons: {
			action: {
				title: lychee.locale["ALBUM_SET_TITLE"],
				fn: action
			},
			cancel: {
				title: lychee.locale["CANCEL"],
				fn: basicModal.close
			}
		}
	});
};

album.setDescription = function (albumID) {
	var oldDescription = album.json.description;

	var action = function action(data) {
		var description = data.description;

		basicModal.close();

		if (visible.album()) {
			album.json.description = description;
			view.album.description();
		}

		var params = {
			albumID: albumID,
			description: description
		};

		api.post("Album::setDescription", params, function (_data) {
			if (_data !== true) {
				lychee.error(null, params, _data);
			}
		});
	};

	basicModal.show({
		body: lychee.html(_templateObject7, lychee.locale["ALBUM_NEW_DESCRIPTION"], lychee.locale["ALBUM_DESCRIPTION"], oldDescription),
		buttons: {
			action: {
				title: lychee.locale["ALBUM_SET_DESCRIPTION"],
				fn: action
			},
			cancel: {
				title: lychee.locale["CANCEL"],
				fn: basicModal.close
			}
		}
	});
};

album.toggleCover = function (photoID) {
	if (!photoID) return false;

	album.json.cover_id = album.json.cover_id === photoID ? "" : photoID;

	var params = {
		albumID: album.json.id,
		photoID: album.json.cover_id
	};

	api.post("Album::setCover", params, function (data) {
		if (data !== true) {
			lychee.error(null, params, data);
		} else {
			view.album.content.cover(photoID);
			if (!album.getParent()) {
				albums.refresh();
			}
		}
	});
};

album.setLicense = function (albumID) {
	var callback = function callback() {
		$("select#license").val(album.json.license === "" ? "none" : album.json.license);
		return false;
	};

	var action = function action(data) {
		var license = data.license;

		basicModal.close();

		var params = {
			albumID: albumID,
			license: license
		};

		api.post("Album::setLicense", params, function (_data) {
			if (_data !== true) {
				lychee.error(null, params, _data);
			} else {
				if (visible.album()) {
					album.json.license = params.license;
					view.album.license();
				}
			}
		});
	};

	var msg = lychee.html(_templateObject8, lychee.locale["ALBUM_LICENSE"], lychee.locale["ALBUM_LICENSE_NONE"], lychee.locale["ALBUM_RESERVED"], lychee.locale["ALBUM_LICENSE_HELP"]);

	basicModal.show({
		body: msg,
		callback: callback,
		buttons: {
			action: {
				title: lychee.locale["ALBUM_SET_LICENSE"],
				fn: action
			},
			cancel: {
				title: lychee.locale["CANCEL"],
				fn: basicModal.close
			}
		}
	});
};

album.setSorting = function (albumID) {
	var callback = function callback() {
		$("select#sortingCol").val(album.json.sorting_col);
		$("select#sortingOrder").val(album.json.sorting_order);
		return false;
	};

	var action = function action(data) {
		var typePhotos = data.sortingCol;
		var orderPhotos = data.sortingOrder;

		basicModal.close();

		var params = {
			albumID: albumID,
			typePhotos: typePhotos,
			orderPhotos: orderPhotos
		};

		api.post("Album::setSorting", params, function (_data) {
			if (_data !== true) {
				lychee.error(null, params, _data);
			} else {
				if (visible.album()) {
					album.reload();
				}
			}
		});
	};

	var msg = lychee.html(_templateObject9) + lychee.locale["SORT_PHOTO_BY_1"] + "\n\t\t<span class=\"select\">\n\t\t\t<select id=\"sortingCol\" name=\"sortingCol\">\n\t\t\t\t<option value=''>-</option>\n\t\t\t\t<option value='id'>" + lychee.locale["SORT_PHOTO_SELECT_1"] + "</option>\n\t\t\t\t<option value='taken_at'>" + lychee.locale["SORT_PHOTO_SELECT_2"] + "</option>\n\t\t\t\t<option value='title'>" + lychee.locale["SORT_PHOTO_SELECT_3"] + "</option>\n\t\t\t\t<option value='description'>" + lychee.locale["SORT_PHOTO_SELECT_4"] + "</option>\n\t\t\t\t<option value='public'>" + lychee.locale["SORT_PHOTO_SELECT_5"] + "</option>\n\t\t\t\t<option value='star'>" + lychee.locale["SORT_PHOTO_SELECT_6"] + "</option>\n\t\t\t\t<option value='type'>" + lychee.locale["SORT_PHOTO_SELECT_7"] + "</option>\n\t\t\t</select>\n\t\t</span>\n\t\t" + lychee.locale["SORT_PHOTO_BY_2"] + "\n\t\t<span class=\"select\">\n\t\t\t<select id=\"sortingOrder\" name=\"sortingOrder\">\n\t\t\t\t<option value='ASC'>" + lychee.locale["SORT_ASCENDING"] + "</option>\n\t\t\t\t<option value='DESC'>" + lychee.locale["SORT_DESCENDING"] + "</option>\n\t\t\t</select>\n\t\t</span>\n\t\t" + lychee.locale["SORT_PHOTO_BY_3"] + "\n\t\t</p>\n\t</div>";

	basicModal.show({
		body: msg,
		callback: callback,
		buttons: {
			action: {
				title: lychee.locale["ALBUM_SET_ORDER"],
				fn: action
			},
			cancel: {
				title: lychee.locale["CANCEL"],
				fn: basicModal.close
			}
		}
	});
};

album.setPublic = function (albumID, e) {
	var password = "";

	if (!basicModal.visible()) {
		var msg = lychee.html(_templateObject10, lychee.locale["ALBUM_PUBLIC"], lychee.locale["ALBUM_PUBLIC_EXPL"], build.iconic("check"), lychee.locale["ALBUM_FULL"], lychee.locale["ALBUM_FULL_EXPL"], build.iconic("check"), lychee.locale["ALBUM_HIDDEN"], lychee.locale["ALBUM_HIDDEN_EXPL"], build.iconic("check"), lychee.locale["ALBUM_DOWNLOADABLE"], lychee.locale["ALBUM_DOWNLOADABLE_EXPL"], build.iconic("check"), lychee.locale["ALBUM_SHARE_BUTTON_VISIBLE"], lychee.locale["ALBUM_SHARE_BUTTON_VISIBLE_EXPL"], build.iconic("check"), lychee.locale["ALBUM_PASSWORD_PROT"], lychee.locale["ALBUM_PASSWORD_PROT_EXPL"], lychee.locale["PASSWORD"], lychee.locale["ALBUM_NSFW"], lychee.locale["ALBUM_NSFW_EXPL"]);

		basicModal.show({
			body: msg,
			buttons: {
				action: {
					title: lychee.locale["ALBUM_SHARING_CONFIRM"],
					// Call setPublic function without showing the modal
					fn: function fn() {
						return album.setPublic(albumID, e);
					}
				},
				cancel: {
					title: lychee.locale["CANCEL"],
					fn: basicModal.close
				}
			}
		});

		$('.basicModal .switch input[name="public"]').on("click", function () {
			if ($(this).prop("checked") === true) {
				$(".basicModal .choice input").attr("disabled", false);

				if (album.json.public === "1") {
					// Initialize options based on album settings.
					if (album.json.full_photo !== null && album.json.full_photo === "1") $('.basicModal .choice input[name="full_photo"]').prop("checked", true);
					if (album.json.visible === "0") $('.basicModal .choice input[name="hidden"]').prop("checked", true);
					if (album.json.downloadable === "1") $('.basicModal .choice input[name="downloadable"]').prop("checked", true);
					if (album.json.share_button_visible === "1") $('.basicModal .choice input[name="share_button_visible"]').prop("checked", true);
					if (album.json.password === "1") {
						$('.basicModal .choice input[name="password"]').prop("checked", true);
						$('.basicModal .choice input[name="passwordtext"]').show();
					}
				} else {
					// Initialize options based on global settings.
					if (lychee.full_photo) {
						$('.basicModal .choice input[name="full_photo"]').prop("checked", true);
					}
					if (lychee.downloadable) {
						$('.basicModal .choice input[name="downloadable"]').prop("checked", true);
					}
					if (lychee.share_button_visible) {
						$('.basicModal .choice input[name="share_button_visible"]').prop("checked", true);
					}
				}
			} else {
				$(".basicModal .choice input").prop("checked", false).attr("disabled", true);
				$('.basicModal .choice input[name="passwordtext"]').hide();
			}
		});

		if (album.json.nsfw === "1") {
			$('.basicModal .switch input[name="nsfw"]').prop("checked", true);
		} else {
			$('.basicModal .switch input[name="nsfw"]').prop("checked", false);
		}

		if (album.json.public === "1") {
			$('.basicModal .switch input[name="public"]').click();
		} else {
			$(".basicModal .choice input").attr("disabled", true);
		}

		$('.basicModal .choice input[name="password"]').on("change", function () {
			if ($(this).prop("checked") === true) $('.basicModal .choice input[name="passwordtext"]').show().focus();else $('.basicModal .choice input[name="passwordtext"]').hide();
		});

		return true;
	}

	albums.refresh();

	// Set public
	if ($('.basicModal .switch input[name="nsfw"]:checked').length === 1) {
		album.json.nsfw = "1";
	} else {
		album.json.nsfw = "0";
	}

	// Set public
	if ($('.basicModal .switch input[name="public"]:checked').length === 1) {
		album.json.public = "1";
	} else {
		album.json.public = "0";
	}

	// Set full photo
	if ($('.basicModal .choice input[name="full_photo"]:checked').length === 1) {
		album.json.full_photo = "1";
	} else {
		album.json.full_photo = "0";
	}

	// Set visible
	if ($('.basicModal .choice input[name="hidden"]:checked').length === 1) {
		album.json.visible = "0";
	} else {
		album.json.visible = "1";
	}

	// Set downloadable
	if ($('.basicModal .choice input[name="downloadable"]:checked').length === 1) {
		album.json.downloadable = "1";
	} else {
		album.json.downloadable = "0";
	}

	// Set share_button_visible
	if ($('.basicModal .choice input[name="share_button_visible"]:checked').length === 1) {
		album.json.share_button_visible = "1";
	} else {
		album.json.share_button_visible = "0";
	}

	// Set password
	var oldPassword = album.json.password;
	if ($('.basicModal .choice input[name="password"]:checked').length === 1) {
		password = $('.basicModal .choice input[name="passwordtext"]').val();
		album.json.password = "1";
	} else {
		password = "";
		album.json.password = "0";
	}

	// Modal input has been processed, now it can be closed
	basicModal.close();

	// Set data and refresh view
	if (visible.album()) {
		view.album.nsfw();
		view.album.public();
		view.album.hidden();
		view.album.downloadable();
		view.album.shareButtonVisible();
		view.album.password();
	}

	var params = {
		albumID: albumID,
		full_photo: album.json.full_photo,
		public: album.json.public,
		nsfw: album.json.nsfw,
		visible: album.json.visible,
		downloadable: album.json.downloadable,
		share_button_visible: album.json.share_button_visible
	};
	if (oldPassword !== album.json.password || password.length > 0) {
		// We send the password only if there's been a change; that way the
		// server will keep the current password if it wasn't changed.
		params.password = password;
	}

	api.post("Album::setPublic", params, function (data) {
		if (data !== true) lychee.error(null, params, data);
	});
};

album.shareUsers = function (albumID, e) {
	if (!basicModal.visible()) {
		var msg = "<form id=\"sharing_people_form\">\n\t\t\t<p>" + lychee.locale["WAIT_FETCH_DATA"] + "</p>\n\t\t</form>";

		api.post("Sharing::List", {}, function (data) {
			var sharingForm = $("#sharing_people_form");
			sharingForm.empty();
			if (data !== undefined) {
				if (data.users !== undefined) {
					sharingForm.append("<p>" + lychee.locale["SHARING_ALBUM_USERS_LONG_MESSAGE"] + "</p>");
					// Fill with the list of users
					data.users.forEach(function (user) {
						sharingForm.append(lychee.html(_templateObject11, user.id, build.iconic("check"), user.username));
					});
					var sharingOfAlbum = data.shared !== undefined ? data.shared.filter(function (val) {
						return val.album_id === albumID;
					}) : [];
					sharingOfAlbum.forEach(function (sharing) {
						// Check all the shares who already exists, and store their sharing id on the element
						var elem = $(".basicModal .choice input[name=\"" + sharing.user_id + "\"]");
						elem.prop("checked", true);
						elem.data("sharingId", sharing.id);
					});
				} else {
					sharingForm.append("<p>" + lychee.locale["SHARING_ALBUM_USERS_NO_USERS"] + "</p>");
				}
			}
		});

		basicModal.show({
			body: msg,
			buttons: {
				action: {
					title: lychee.locale["ALBUM_SHARING_CONFIRM"],
					fn: function fn(data) {
						album.shareUsers(albumID, e);
					}
				},
				cancel: {
					title: lychee.locale["CANCEL"],
					fn: basicModal.close
				}
			}
		});
		return true;
	}

	basicModal.close();

	var sharingToAdd = [];
	var sharingToDelete = [];
	$(".basicModal .choice input").each(function (_, input) {
		var $input = $(input);
		if ($input.is(":checked")) {
			if ($input.data("sharingId") === undefined) {
				// Input is checked but has no sharing id => new share to create
				sharingToAdd.push(input.name);
			}
		} else {
			var sharingId = $input.data("sharingId");
			if (sharingId !== undefined) {
				// Input is not checked but has a sharing id => existing share to remove
				sharingToDelete.push(sharingId);
			}
		}
	});

	if (sharingToDelete.length > 0) {
		var params = { ShareIDs: sharingToDelete.join(",") };
		api.post("Sharing::Delete", params, function (data) {
			if (data !== true) {
				loadingBar.show("error", data.description);
				lychee.error(null, params, data);
			}
		});
	}
	if (sharingToAdd.length > 0) {
		var params = {
			albumIDs: albumID,
			UserIDs: sharingToAdd.join(",")
		};
		api.post("Sharing::Add", params, function (data) {
			if (data !== true) {
				loadingBar.show("error", data.description);
				lychee.error(null, params, data);
			} else {
				loadingBar.show("success", "Sharing updated!");
			}
		});
	}

	return true;
};

album.setNSFW = function (albumID, e) {
	album.json.nsfw = album.json.nsfw === "0" ? "1" : "0";

	view.album.nsfw();

	var params = {
		albumID: albumID
	};

	api.post("Album::setNSFW", params, function (data) {
		if (data !== true) {
			lychee.error(null, params, data);
		} else {
			albums.refresh();
		}
	});
};

album.share = function (service) {
	if (album.json.hasOwnProperty("share_button_visible") && album.json.share_button_visible !== "1") {
		return;
	}

	var url = location.href;

	switch (service) {
		case "twitter":
			window.open("https://twitter.com/share?url=" + encodeURI(url));
			break;
		case "facebook":
			window.open("https://www.facebook.com/sharer.php?u=" + encodeURI(url) + "&t=" + encodeURI(album.json.title));
			break;
		case "mail":
			location.href = "mailto:?subject=" + encodeURI(album.json.title) + "&body=" + encodeURI(url);
			break;
	}
};

album.getArchive = function (albumIDs) {
	location.href = "api/Album::getArchive" + lychee.html(_templateObject12, albumIDs.join());
};

album.buildMessage = function (albumIDs, albumID, op1, op2, ops) {
	var title = "";
	var sTitle = "";
	var msg = "";

	if (!albumIDs) return false;
	if (albumIDs instanceof Array === false) albumIDs = [albumIDs];

	// Get title of first album
	if (parseInt(albumID, 10) === 0) {
		title = lychee.locale["ROOT"];
	} else if (albums.json) {
		album1 = albums.getByID(albumID);
		if (album1) {
			title = album1.title;
		}
	}

	// Fallback for first album without a title
	if (title === "") title = lychee.locale["UNTITLED"];

	if (albumIDs.length === 1) {
		// Get title of second album
		if (albums.json) {
			album2 = albums.getByID(albumIDs[0]);
			if (album2) {
				sTitle = album2.title;
			}
		}

		// Fallback for second album without a title
		if (sTitle === "") sTitle = lychee.locale["UNTITLED"];

		msg = lychee.html(_templateObject13, lychee.locale[op1], sTitle, lychee.locale[op2], title);
	} else {
		msg = lychee.html(_templateObject14, lychee.locale[ops], title);
	}

	return msg;
};

album.delete = function (albumIDs) {
	var action = {};
	var cancel = {};
	var msg = "";

	if (!albumIDs) return false;
	if (albumIDs instanceof Array === false) albumIDs = [albumIDs];

	action.fn = function () {
		basicModal.close();

		var params = {
			albumIDs: albumIDs.join()
		};

		api.post("Album::delete", params, function (data) {
			if (visible.albums()) {
				albumIDs.forEach(function (id) {
					view.albums.content.delete(id);
					albums.deleteByID(id);
				});
			} else if (visible.album()) {
				albums.refresh();
				if (albumIDs.length === 1 && album.getID() == albumIDs[0]) {
					lychee.goto(album.getParent());
				} else {
					albumIDs.forEach(function (id) {
						album.deleteSubByID(id);
						view.album.content.deleteSub(id);
					});
				}
			}

			if (data !== true) lychee.error(null, params, data);
		});
	};

	if (albumIDs.toString() === "unsorted") {
		action.title = lychee.locale["CLEAR_UNSORTED"];
		cancel.title = lychee.locale["KEEP_UNSORTED"];

		msg = "<p>" + lychee.locale["DELETE_UNSORTED_CONFIRM"] + "</p>";
	} else if (albumIDs.length === 1) {
		var albumTitle = "";

		action.title = lychee.locale["DELETE_ALBUM_QUESTION"];
		cancel.title = lychee.locale["KEEP_ALBUM"];

		// Get title
		if (album.json) {
			if (parseInt(album.getID()) === parseInt(albumIDs[0])) {
				albumTitle = album.json.title;
			} else albumTitle = album.getSubByID(albumIDs[0]).title;
		}
		if (!albumTitle && albums.json) albumTitle = albums.getByID(albumIDs).title;

		// Fallback for album without a title
		if (albumTitle === "") albumTitle = lychee.locale["UNTITLED"];

		msg = lychee.html(_templateObject15, lychee.locale["DELETE_ALBUM_CONFIRMATION_1"], albumTitle, lychee.locale["DELETE_ALBUM_CONFIRMATION_2"]);
	} else {
		action.title = lychee.locale["DELETE_ALBUMS_QUESTION"];
		cancel.title = lychee.locale["KEEP_ALBUMS"];

		msg = lychee.html(_templateObject16, lychee.locale["DELETE_ALBUMS_CONFIRMATION_1"], albumIDs.length, lychee.locale["DELETE_ALBUMS_CONFIRMATION_2"]);
	}

	basicModal.show({
		body: msg,
		buttons: {
			action: {
				title: action.title,
				fn: action.fn,
				class: "red"
			},
			cancel: {
				title: cancel.title,
				fn: basicModal.close
			}
		}
	});
};

album.merge = function (albumIDs, albumID) {
	var confirm = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : true;

	var action = function action() {
		basicModal.close();
		albumIDs.unshift(albumID);

		var params = {
			albumIDs: albumIDs.join()
		};

		api.post("Album::merge", params, function (data) {
			if (data !== true) {
				lychee.error(null, params, data);
			} else {
				album.reload();
			}
		});
	};

	if (confirm) {
		basicModal.show({
			body: album.buildMessage(albumIDs, albumID, "ALBUM_MERGE_1", "ALBUM_MERGE_2", "ALBUMS_MERGE"),
			buttons: {
				action: {
					title: lychee.locale["MERGE_ALBUM"],
					fn: action,
					class: "red"
				},
				cancel: {
					title: lychee.locale["DONT_MERGE"],
					fn: basicModal.close
				}
			}
		});
	} else {
		action();
	}
};

album.setAlbum = function (albumIDs, albumID) {
	var confirm = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : true;

	var action = function action() {
		basicModal.close();
		albumIDs.unshift(albumID);

		var params = {
			albumIDs: albumIDs.join()
		};

		api.post("Album::move", params, function (data) {
			if (data !== true) {
				lychee.error(null, params, data);
			} else {
				album.reload();
			}
		});
	};

	if (confirm) {
		basicModal.show({
			body: album.buildMessage(albumIDs, albumID, "ALBUM_MOVE_1", "ALBUM_MOVE_2", "ALBUMS_MOVE"),
			buttons: {
				action: {
					title: lychee.locale["MOVE_ALBUMS"],
					fn: action,
					class: "red"
				},
				cancel: {
					title: lychee.locale["NOT_MOVE_ALBUMS"],
					fn: basicModal.close
				}
			}
		});
	} else {
		action();
	}
};

album.apply_nsfw_filter = function () {
	if (lychee.nsfw_visible) {
		$('.album[data-nsfw="1"]').show();
	} else {
		$('.album[data-nsfw="1"]').hide();
	}
};

album.toggle_nsfw_filter = function () {
	lychee.nsfw_visible = !lychee.nsfw_visible;
	album.apply_nsfw_filter();
	return false;
};

album.isUploadable = function () {
	if (lychee.admin) {
		return true;
	}
	if (lychee.publicMode || !lychee.upload) {
		return false;
	}

	// For special cases of no album / smart album / etc. we return true.
	// It's only for regular non-matching albums that we return false.
	if (album.json === null || !album.json.owner) {
		return true;
	}

	return album.json.owner === lychee.username;
};

album.updatePhoto = function (data) {
	var deepCopySizeVariant = function deepCopySizeVariant(src) {
		if (src === undefined || src === null) return null;
		var result = {};
		result.url = src.url;
		result.width = src.width;
		result.height = src.height;
		return result;
	};

	if (album.json) {
		$.each(album.json.photos, function () {
			if (this.id === data.id) {
				this.width = data.width;
				this.height = data.height;
				this.url = data.url;
				this.filesize = data.filesize;
				// Deep copy size variants
				this.sizeVariants = {
					thumb: null,
					thumb2x: null,
					small: null,
					small2x: null,
					medium: null,
					medium2x: null
				};
				if (data.sizeVariants !== undefined && data.sizeVariants !== null) {
					this.sizeVariants.thumb = deepCopySizeVariant(data.sizeVariants.thumb);
					this.sizeVariants.thumb2x = deepCopySizeVariant(data.sizeVariants.thumb2x);
					this.sizeVariants.small = deepCopySizeVariant(data.sizeVariants.small);
					this.sizeVariants.small2x = deepCopySizeVariant(data.sizeVariants.small2x);
					this.sizeVariants.medium = deepCopySizeVariant(data.sizeVariants.medium);
					this.sizeVariants.medium2x = deepCopySizeVariant(data.sizeVariants.medium2x);
				}
				view.album.content.updatePhoto(this);
				albums.refresh();
				return false;
			}
			return true;
		});
	}
};

album.reload = function () {
	var albumID = album.getID();

	album.refresh();
	albums.refresh();

	if (visible.album()) lychee.goto(albumID);else lychee.goto();
};

album.refresh = function () {
	album.json = null;
};

/**
 * @description Takes care of every action albums can handle and execute.
 */

var albums = {
	json: null
};

albums.load = function () {
	var startTime = new Date().getTime();

	lychee.animate(".content", "contentZoomOut");

	if (albums.json === null) {
		api.post("Albums::get", {}, function (data) {
			var waitTime = void 0;

			// Smart Albums
			if (data.smartalbums != null) albums._createSmartAlbums(data.smartalbums);

			albums.json = data;

			// Calculate delay
			var durationTime = new Date().getTime() - startTime;
			if (durationTime > 300) waitTime = 0;else waitTime = 300 - durationTime;

			// Skip delay when opening a blank Lychee
			if (!visible.albums() && !visible.photo() && !visible.album()) waitTime = 0;
			if (visible.album() && lychee.content.html() === "") waitTime = 0;

			setTimeout(function () {
				header.setMode("albums");
				view.albums.init();
				lychee.animate(lychee.content, "contentZoomIn");

				tabindex.makeFocusable(lychee.content);

				if (lychee.active_focus_on_page_load) {
					// Put focus on first element - either album or photo
					var _first_album2 = $(".album:first");
					if (_first_album2.length !== 0) {
						_first_album2.focus();
					} else {
						var _first_photo = $(".photo:first");
						if (_first_photo.length !== 0) {
							_first_photo.focus();
						}
					}
				}

				setTimeout(function () {
					lychee.footer_show();
				}, 300);
			}, waitTime);
		});
	} else {
		setTimeout(function () {
			header.setMode("albums");
			view.albums.init();
			lychee.animate(lychee.content, "contentZoomIn");

			tabindex.makeFocusable(lychee.content);

			if (lychee.active_focus_on_page_load) {
				// Put focus on first element - either album or photo
				first_album = $(".album:first");
				if (first_album.length !== 0) {
					first_album.focus();
				} else {
					first_photo = $(".photo:first");
					if (first_photo.length !== 0) {
						first_photo.focus();
					}
				}
			}
		}, 300);
	}
};

albums.parse = function (album) {
	if (!album.thumb) {
		album.thumb = {};
		album.thumb.id = "";
		album.thumb.thumb = album.password === "1" ? "img/password.svg" : "img/no_images.svg";
		album.thumb.type = "";
		album.thumb.thumb2x = "";
	}
};

// TODO: REFACTOR THIS
albums._createSmartAlbums = function (data) {
	if (data.unsorted) {
		data.unsorted = {
			id: "unsorted",
			title: lychee.locale["UNSORTED"],
			created_at: null,
			unsorted: "1",
			thumb: data.unsorted.thumb
		};
	}

	if (data.starred) {
		data.starred = {
			id: "starred",
			title: lychee.locale["STARRED"],
			created_at: null,
			star: "1",
			thumb: data.starred.thumb
		};
	}

	if (data.public) {
		data.public = {
			id: "public",
			title: lychee.locale["PUBLIC"],
			created_at: null,
			public: "1",
			visible: "0",
			thumb: data.public.thumb
		};
	}

	if (data.recent) {
		data.recent = {
			id: "recent",
			title: lychee.locale["RECENT"],
			created_at: null,
			recent: "1",
			thumb: data.recent.thumb
		};
	}
};

albums.isShared = function (albumID) {
	if (albumID == null) return false;
	if (!albums.json) return false;
	if (!albums.json.albums) return false;

	var found = false;

	var func = function func() {
		if (parseInt(this.id, 10) === parseInt(albumID, 10)) {
			found = true;
			return false; // stop the loop
		}
		if (this.albums) {
			$.each(this.albums, func);
		}
	};

	if (albums.json.shared_albums !== null) $.each(albums.json.shared_albums, func);

	return found;
};

albums.getByID = function (albumID) {
	// Function returns the JSON of an album

	if (albumID == null) return undefined;
	if (!albums.json) return undefined;
	if (!albums.json.albums) return undefined;

	var json = undefined;

	var func = function func() {
		if (parseInt(this.id, 10) === parseInt(albumID, 10)) {
			json = this;
			return false; // stop the loop
		}
		if (this.albums) {
			$.each(this.albums, func);
		}
	};

	$.each(albums.json.albums, func);

	if (json === undefined && albums.json.shared_albums !== null) $.each(albums.json.shared_albums, func);

	if (json === undefined && albums.json.smartalbums !== null) $.each(albums.json.smartalbums, func);

	return json;
};

albums.deleteByID = function (albumID) {
	// Function returns the JSON of an album
	// This function is only ever invoked for top-level albums so it
	// doesn't need to descend down the albums tree.

	if (albumID == null) return false;
	if (!albums.json) return false;
	if (!albums.json.albums) return false;

	var deleted = false;

	$.each(albums.json.albums, function (i) {
		if (parseInt(albums.json.albums[i].id) === parseInt(albumID)) {
			albums.json.albums.splice(i, 1);
			deleted = true;
			return false; // stop the loop
		}
	});

	if (deleted === false) {
		if (!albums.json.shared_albums) return undefined;
		$.each(albums.json.shared_albums, function (i) {
			if (parseInt(albums.json.shared_albums[i].id) === parseInt(albumID)) {
				albums.json.shared_albums.splice(i, 1);
				deleted = true;
				return false; // stop the loop
			}
		});
	}

	if (deleted === false) {
		if (!albums.json.smartalbums) return undefined;
		$.each(albums.json.smartalbums, function (i) {
			if (parseInt(albums.json.smartalbums[i].id) === parseInt(albumID)) {
				delete albums.json.smartalbums[i];
				deleted = true;
				return false; // stop the loop
			}
		});
	}

	return deleted;
};

albums.refresh = function () {
	albums.json = null;
};

/**
 * @description This module is used to generate HTML-Code.
 */

var build = {};

build.iconic = function (icon) {
	var classes = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : "";

	var html = "";

	html += lychee.html(_templateObject17, classes, icon);

	return html;
};

build.divider = function (title) {
	var html = "";

	html += lychee.html(_templateObject18, title);

	return html;
};

build.editIcon = function (id) {
	var html = "";

	html += lychee.html(_templateObject19, id, build.iconic("pencil"));

	return html;
};

build.multiselect = function (top, left) {
	return lychee.html(_templateObject20, top, left);
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

	var html = lychee.html(_templateObject21, disabled ? "disabled" : "", data.nsfw && data.nsfw === "1" && lychee.nsfw_blur ? "blurred" : "", data.id, data.nsfw && data.nsfw === "1" ? "1" : "0", tabindex.get_next_tab_index(), build.getAlbumThumb(data), build.getAlbumThumb(data), build.getAlbumThumb(data), data.title, data.title, subtitle);

	if (album.isUploadable() && !disabled) {
		var isCover = album.json && album.json.cover_id && data.thumb.id === album.json.cover_id;
		html += lychee.html(_templateObject22, data.nsfw === "1" ? "badge--nsfw" : "", build.iconic("warning"), data.star === "1" ? "badge--star" : "", build.iconic("star"), data.recent === "1" ? "badge--visible badge--list" : "", build.iconic("clock"), data.public === "1" ? "badge--visible" : "", data.visible === "1" ? "badge--not--hidden" : "badge--hidden", build.iconic("eye"), data.unsorted === "1" ? "badge--visible" : "", build.iconic("list"), data.password === "1" ? "badge--visible" : "", build.iconic("lock-locked"), data.tag_album === "1" ? "badge--tag" : "", build.iconic("tag"), isCover ? "badge--cover" : "", build.iconic("folder-cover"));
	}

	if (data.albums && data.albums.length > 0 || data.hasOwnProperty("has_albums") && data.has_albums === "1") {
		html += lychee.html(_templateObject23, build.iconic("layers"));
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

	html += lychee.html(_templateObject24, disabled ? "disabled" : "", data.album, data.id, tabindex.get_next_tab_index(), thumbnail, data.title, data.title);

	if (data.taken_at !== null) html += lychee.html(_templateObject25, build.iconic("camera-slr"), lychee.locale.printDateTime(data.taken_at));else html += lychee.html(_templateObject26, lychee.locale.printDateTime(data.created_at));

	html += "</div>";

	if (album.isUploadable()) {
		html += lychee.html(_templateObject27, data.star === "1" ? "badge--star" : "", build.iconic("star"), data.public === "1" && album.json.public !== "1" ? "badge--visible badge--hidden" : "", build.iconic("eye"), isCover ? "badge--cover" : "", build.iconic("folder-cover"));
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

	return lychee.html(_templateObject28, data.title) + (overlay !== "" ? "<p>" + overlay + "</p>" : "") + "\n\t\t</div>\n\t\t";
};

build.imageview = function (data, visibleControls, autoplay) {
	var html = "";
	var thumb = "";

	if (data.type.indexOf("video") > -1) {
		html += lychee.html(_templateObject29, visibleControls === true ? "" : "full", autoplay ? "autoplay" : "", tabindex.get_next_tab_index(), data.url);
	} else if (data.type.indexOf("raw") > -1 && data.sizeVariants.medium === null) {
		html += lychee.html(_templateObject30, visibleControls === true ? "" : "full", tabindex.get_next_tab_index());
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

		html += lychee.html(_templateObject31, img);
	}

	html += build.overlay_image(data) + ("\n\t\t\t<div class='arrow_wrapper arrow_wrapper--previous'><a id='previous'>" + build.iconic("caret-left") + "</a></div>\n\t\t\t<div class='arrow_wrapper arrow_wrapper--next'><a id='next'>" + build.iconic("caret-right") + "</a></div>\n\t\t\t");

	return { html: html, thumb: thumb };
};

build.no_content = function (typ) {
	var html = "";

	html += lychee.html(_templateObject32, build.iconic(typ));

	switch (typ) {
		case "magnifying-glass":
			html += lychee.html(_templateObject33, lychee.locale["VIEW_NO_RESULT"]);
			break;
		case "eye":
			html += lychee.html(_templateObject33, lychee.locale["VIEW_NO_PUBLIC_ALBUMS"]);
			break;
		case "cog":
			html += lychee.html(_templateObject33, lychee.locale["VIEW_NO_CONFIGURATION"]);
			break;
		case "question-mark":
			html += lychee.html(_templateObject33, lychee.locale["VIEW_PHOTO_NOT_FOUND"]);
			break;
	}

	html += "</div>";

	return html;
};

build.uploadModal = function (title, files) {
	var html = "";

	html += lychee.html(_templateObject34, title);

	var i = 0;

	while (i < files.length) {
		var file = files[i];

		if (file.name.length > 40) file.name = file.name.substr(0, 17) + "..." + file.name.substr(file.name.length - 20, 20);

		html += lychee.html(_templateObject35, file.name);

		i++;
	}

	html += "</div>";

	return html;
};

build.uploadNewFile = function (name) {
	if (name.length > 40) {
		name = name.substr(0, 17) + "..." + name.substr(name.length - 20, 20);
	}

	return lychee.html(_templateObject36, name);
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
			html += lychee.html(_templateObject37, color.r, color.g, color.b, color.r, color.g, color.b);
		});
	} else {
		html = lychee.html(_templateObject38, lychee.locale["NO_PALETTE"]);
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
				html += lychee.html(_templateObject39, a_class, tag, index, build.iconic("x"));
			} else {
				html += lychee.html(_templateObject40, a_class, tag);
			}
		});
	} else {
		html = lychee.html(_templateObject38, lychee.locale["NO_TAGS"]);
	}

	return html;
};

build.user = function (user) {
	var html = lychee.html(_templateObject41, user.id, user.id, user.username, user.id, user.id);

	return html;
};

build.u2f = function (credential) {
	return lychee.html(_templateObject42, credential.id, credential.id, credential.id.slice(0, 30), credential.id);
};

/**
 * @description This module is used for the context menu.
 */

var contextMenu = {};

contextMenu.add = function (e) {
	var items = [{ title: build.iconic("image") + lychee.locale["UPLOAD_PHOTO"], fn: function fn() {
			return $("#upload_files").click();
		} }, {}, { title: build.iconic("link-intact") + lychee.locale["IMPORT_LINK"], fn: upload.start.url }, { title: build.iconic("dropbox", "ionicons") + lychee.locale["IMPORT_DROPBOX"], fn: upload.start.dropbox }, { title: build.iconic("terminal") + lychee.locale["IMPORT_SERVER"], fn: upload.start.server }, {}, { title: build.iconic("folder") + lychee.locale["NEW_ALBUM"], fn: album.add }];

	if (visible.albums()) {
		items.push({ title: build.iconic("tags") + lychee.locale["NEW_TAG_ALBUM"], fn: album.addByTags });
	}

	if (!lychee.admin) {
		// remove import from dropbox and server if not admin
		items.splice(3, 2);
	} else if (!lychee.dropboxKey || lychee.dropboxKey === "") {
		// remove import from dropbox if dropboxKey not set
		items.splice(3, 1);
	}

	// prepend further buttons if menu bar is reduced on small screens
	var button_visibility_album = $("#button_visibility_album");
	if (button_visibility_album && button_visibility_album.css("display") === "none") {
		items.unshift({
			title: build.iconic("eye") + lychee.locale["VISIBILITY_ALBUM"],
			visible: lychee.enable_button_visibility,
			fn: function fn(event) {
				return album.setPublic(album.getID(), event);
			}
		});
	}
	var button_trash_album = $("#button_trash_album");
	if (button_trash_album && button_trash_album.css("display") === "none") {
		items.unshift({
			title: build.iconic("trash") + lychee.locale["DELETE_ALBUM"],
			visible: lychee.enable_button_trash,
			fn: function fn() {
				return album.delete([album.getID()]);
			}
		});
	}
	var button_move_album = $("#button_move_album");
	if (button_move_album && button_move_album.css("display") === "none") {
		items.unshift({
			title: build.iconic("folder") + lychee.locale["MOVE_ALBUM"],
			visible: lychee.enable_button_move,
			fn: function fn(event) {
				return contextMenu.move([album.getID()], event, album.setAlbum, "ROOT", album.getParent() !== "");
			}
		});
	}
	var button_nsfw_album = $("#button_nsfw_album");
	if (button_nsfw_album && button_nsfw_album.css("display") === "none") {
		items.unshift({
			title: build.iconic("warning") + lychee.locale["ALBUM_MARK_NSFW"],
			visible: true,
			fn: function fn() {
				return album.setNSFW(album.getID());
			}
		});
	}

	basicContext.show(items, e.originalEvent);

	upload.notify();
};

contextMenu.album = function (albumID, e) {
	// Notice for 'Merge':
	// fn must call basicContext.close() first,
	// in order to keep the selection

	if (album.isSmartID(albumID)) return false;

	// Show merge-item when there's more than one album
	// Commented out because it doesn't consider subalbums or shared albums.
	// let showMerge = (albums.json && albums.json.albums && Object.keys(albums.json.albums).length>1);
	var showMerge = true;

	var items = [{ title: build.iconic("pencil") + lychee.locale["RENAME"], fn: function fn() {
			return album.setTitle([albumID]);
		} }, {
		title: build.iconic("collapse-left") + lychee.locale["MERGE"],
		visible: showMerge,
		fn: function fn() {
			basicContext.close();
			contextMenu.move([albumID], e, album.merge, "ROOT", false);
		}
	}, {
		title: build.iconic("folder") + lychee.locale["MOVE"],
		visible: lychee.sub_albums,
		fn: function fn() {
			basicContext.close();
			contextMenu.move([albumID], e, album.setAlbum, "ROOT");
		}
	}, { title: build.iconic("trash") + lychee.locale["DELETE"], fn: function fn() {
			return album.delete([albumID]);
		} }, { title: build.iconic("cloud-download") + lychee.locale["DOWNLOAD"], fn: function fn() {
			return album.getArchive([albumID]);
		} }];

	if (visible.album()) {
		// not top level
		var myalbum = album.getSubByID(albumID);
		if (myalbum.thumb.id) {
			var coverActive = myalbum.thumb.id === album.json.cover_id;
			// prepend context menu item
			items.unshift({
				title: build.iconic("folder-cover", coverActive ? "active" : "") + lychee.locale[coverActive ? "REMOVE_COVER" : "SET_COVER"],
				fn: function fn() {
					return album.toggleCover(myalbum.thumb.id);
				}
			});
		}
	}

	$('.album[data-id="' + albumID + '"]').addClass("active");

	basicContext.show(items, e.originalEvent, contextMenu.close);
};

contextMenu.albumMulti = function (albumIDs, e) {
	multiselect.stopResize();

	// Automatically merge selected albums when albumIDs contains more than one album
	// Show list of albums otherwise
	var autoMerge = albumIDs.length > 1;

	// Show merge-item when there's more than one album
	// Commented out because it doesn't consider subalbums or shared albums.
	// let showMerge = (albums.json && albums.json.albums && Object.keys(albums.json.albums).length>1);
	var showMerge = true;

	var items = [{ title: build.iconic("pencil") + lychee.locale["RENAME_ALL"], fn: function fn() {
			return album.setTitle(albumIDs);
		} }, {
		title: build.iconic("collapse-left") + lychee.locale["MERGE_ALL"],
		visible: showMerge && autoMerge,
		fn: function fn() {
			var albumID = albumIDs.shift();
			album.merge(albumIDs, albumID);
		}
	}, {
		title: build.iconic("collapse-left") + lychee.locale["MERGE"],
		visible: showMerge && !autoMerge,
		fn: function fn() {
			basicContext.close();
			contextMenu.move(albumIDs, e, album.merge, "ROOT", false);
		}
	}, {
		title: build.iconic("folder") + lychee.locale["MOVE_ALL"],
		visible: lychee.sub_albums,
		fn: function fn() {
			basicContext.close();
			contextMenu.move(albumIDs, e, album.setAlbum, "ROOT");
		}
	}, { title: build.iconic("trash") + lychee.locale["DELETE_ALL"], fn: function fn() {
			return album.delete(albumIDs);
		} }, { title: build.iconic("cloud-download") + lychee.locale["DOWNLOAD_ALL"], fn: function fn() {
			return album.getArchive(albumIDs);
		} }];

	basicContext.show(items, e.originalEvent, contextMenu.close);
};

contextMenu.buildList = function (lists, exclude, action) {
	var parent = arguments.length > 3 && arguments[3] !== undefined ? arguments[3] : 0;
	var layer = arguments.length > 4 && arguments[4] !== undefined ? arguments[4] : 0;

	var find = function find(excl, id) {
		for (var _i = 0; _i < excl.length; _i++) {
			if (parseInt(excl[_i], 10) === parseInt(id, 10)) return true;
		}
		return false;
	};

	var items = [];

	var i = 0;
	while (i < lists.length) {
		if (layer === 0 && !lists[i].parent_id || lists[i].parent_id === parent) {
			(function () {
				var item = lists[i];

				var thumb = "img/no_cover.svg";
				if (item.thumb && item.thumb.thumb) {
					if (item.thumb.thumb === "uploads/thumb/") {
						if (item.thumb.type && item.thumb.type.indexOf("video") > -1) {
							thumb = "img/play-icon.png";
						}
					} else {
						thumb = item.thumb.thumb;
					}
				} else if (item.sizeVariants) {
					if (item.sizeVariants.thumb === null) {
						if (item.type && item.type.indexOf("video") > -1) {
							thumb = "img/play-icon.png";
						}
					} else {
						thumb = item.sizeVariants.thumb.url;
					}
				}

				if (item.title === "") item.title = lychee.locale["UNTITLED"];

				var prefix = layer > 0 ? "&nbsp;&nbsp;".repeat(layer - 1) + "└ " : "";

				var html = lychee.html(_templateObject43, prefix, thumb, item.title);

				items.push({
					title: html,
					disabled: find(exclude, item.id),
					fn: function fn() {
						return action(item);
					}
				});

				if (item.albums && item.albums.length > 0) {
					items = items.concat(contextMenu.buildList(item.albums, exclude, action, item.id, layer + 1));
				} else {
					// Fallback for flat tree representation.  Should not be
					// needed anymore but shouldn't hurt either.
					items = items.concat(contextMenu.buildList(lists, exclude, action, item.id, layer + 1));
				}
			})();
		}

		i++;
	}

	return items;
};

contextMenu.albumTitle = function (albumID, e) {
	api.post("Albums::tree", {}, function (data) {
		var items = [];

		items = items.concat({ title: lychee.locale["ROOT"], disabled: albumID === false, fn: function fn() {
				return lychee.goto();
			} });

		if (data.albums && data.albums.length > 0) {
			items = items.concat({});
			items = items.concat(contextMenu.buildList(data.albums, albumID !== false ? [parseInt(albumID, 10)] : [], function (a) {
				return lychee.goto(a.id);
			}));
		}

		if (data.shared_albums && data.shared_albums.length > 0) {
			items = items.concat({});
			items = items.concat(contextMenu.buildList(data.shared_albums, albumID !== false ? [parseInt(albumID, 10)] : [], function (a) {
				return lychee.goto(a.id);
			}));
		}

		if (albumID !== false && !album.isSmartID(albumID) && album.isUploadable()) {
			if (items.length > 0) {
				items.unshift({});
			}

			items.unshift({ title: build.iconic("pencil") + lychee.locale["RENAME"], fn: function fn() {
					return album.setTitle([albumID]);
				} });
		}

		basicContext.show(items, e.originalEvent, contextMenu.close);
	});
};

contextMenu.photo = function (photoID, e) {
	var coverActive = photoID === album.json.cover_id;

	var items = [{ title: build.iconic("star") + lychee.locale["STAR"], fn: function fn() {
			return _photo.setStar([photoID]);
		} }, { title: build.iconic("tag") + lychee.locale["TAGS"], fn: function fn() {
			return _photo.editTags([photoID]);
		} },
	// for future work, use a list of all the ancestors.
	{
		title: build.iconic("folder-cover", coverActive ? "active" : "") + lychee.locale[coverActive ? "REMOVE_COVER" : "SET_COVER"],
		fn: function fn() {
			return album.toggleCover(photoID);
		}
	}, {}, { title: build.iconic("pencil") + lychee.locale["RENAME"], fn: function fn() {
			return _photo.setTitle([photoID]);
		} }, {
		title: build.iconic("layers") + lychee.locale["COPY_TO"],
		fn: function fn() {
			basicContext.close();
			contextMenu.move([photoID], e, _photo.copyTo, "UNSORTED");
		}
	},
	// Notice for 'Move':
	// fn must call basicContext.close() first,
	// in order to keep the selection
	{
		title: build.iconic("folder") + lychee.locale["MOVE"],
		fn: function fn() {
			basicContext.close();
			contextMenu.move([photoID], e, _photo.setAlbum, "UNSORTED");
		}
	}, { title: build.iconic("trash") + lychee.locale["DELETE"], fn: function fn() {
			return _photo.delete([photoID]);
		} }, { title: build.iconic("cloud-download") + lychee.locale["DOWNLOAD"], fn: function fn() {
			return _photo.getArchive([photoID]);
		} }];

	$('.photo[data-id="' + photoID + '"]').addClass("active");

	basicContext.show(items, e.originalEvent, contextMenu.close);
};

contextMenu.countSubAlbums = function (photoIDs) {
	var count = 0;

	var i = void 0,
	    j = void 0;

	if (album.albums) {
		for (i = 0; i < photoIDs.length; i++) {
			for (j = 0; j < album.albums.length; j++) {
				if (album.albums[j].id === photoIDs[i]) {
					count++;
					break;
				}
			}
		}
	}

	return count;
};

contextMenu.photoMulti = function (photoIDs, e) {
	// Notice for 'Move All':
	// fn must call basicContext.close() first,
	// in order to keep the selection and multiselect
	var subcount = contextMenu.countSubAlbums(photoIDs);
	var photocount = photoIDs.length - subcount;

	if (subcount && photocount) {
		multiselect.deselect(".photo.active, .album.active");
		multiselect.close();
		lychee.error("Please select either albums or photos!");
		return;
	}
	if (subcount) {
		contextMenu.albumMulti(photoIDs, e);
		return;
	}

	multiselect.stopResize();

	var items = [{ title: build.iconic("star") + lychee.locale["STAR_ALL"], fn: function fn() {
			return _photo.setStar(photoIDs);
		} }, { title: build.iconic("tag") + lychee.locale["TAGS_ALL"], fn: function fn() {
			return _photo.editTags(photoIDs);
		} }, {}, { title: build.iconic("pencil") + lychee.locale["RENAME_ALL"], fn: function fn() {
			return _photo.setTitle(photoIDs);
		} }, {
		title: build.iconic("layers") + lychee.locale["COPY_ALL_TO"],
		fn: function fn() {
			basicContext.close();
			contextMenu.move(photoIDs, e, _photo.copyTo, "UNSORTED");
		}
	}, {
		title: build.iconic("folder") + lychee.locale["MOVE_ALL"],
		fn: function fn() {
			basicContext.close();
			contextMenu.move(photoIDs, e, _photo.setAlbum, "UNSORTED");
		}
	}, { title: build.iconic("trash") + lychee.locale["DELETE_ALL"], fn: function fn() {
			return _photo.delete(photoIDs);
		} }, { title: build.iconic("cloud-download") + lychee.locale["DOWNLOAD_ALL"], fn: function fn() {
			return _photo.getArchive(photoIDs, "FULL");
		} }];

	basicContext.show(items, e.originalEvent, contextMenu.close);
};

contextMenu.photoTitle = function (albumID, photoID, e) {
	var items = [{ title: build.iconic("pencil") + lychee.locale["RENAME"], fn: function fn() {
			return _photo.setTitle([photoID]);
		} }];

	var data = album.json;

	if (data.photos !== false && data.photos.length > 0) {
		items.push({});

		items = items.concat(contextMenu.buildList(data.photos, [photoID], function (a) {
			return lychee.goto(albumID + "/" + a.id);
		}));
	}

	if (!album.isUploadable()) {
		// Remove Rename and the spacer.
		items.splice(0, 2);
	}

	basicContext.show(items, e.originalEvent, contextMenu.close);
};

contextMenu.photoMore = function (photoID, e) {
	// Show download-item when
	// a) We are allowed to upload to the album
	// b) the photo is explicitly marked as downloadable (v4-only)
	// c) or, the album is explicitly marked as downloadable
	var showDownload = album.isUploadable() || (_photo.json.hasOwnProperty("downloadable") ? _photo.json.downloadable === "1" : album.json && album.json.downloadable && album.json.downloadable === "1");
	var showFull = _photo.json.url && _photo.json.url !== "";

	var items = [{ title: build.iconic("fullscreen-enter") + lychee.locale["FULL_PHOTO"], visible: !!showFull, fn: function fn() {
			return window.open(_photo.getDirectLink());
		} }, { title: build.iconic("cloud-download") + lychee.locale["DOWNLOAD"], visible: !!showDownload, fn: function fn() {
			return _photo.getArchive([photoID]);
		} }];
	// prepend further buttons if menu bar is reduced on small screens
	var button_visibility = $("#button_visibility");
	if (button_visibility && button_visibility.css("display") === "none") {
		items.unshift({
			title: build.iconic("eye") + lychee.locale["VISIBILITY_PHOTO"],
			visible: lychee.enable_button_visibility,
			fn: function fn(event) {
				return _photo.setPublic(_photo.getID(), event);
			}
		});
	}
	var button_trash = $("#button_trash");
	if (button_trash && button_trash.css("display") === "none") {
		items.unshift({
			title: build.iconic("trash") + lychee.locale["DELETE"],
			visible: lychee.enable_button_trash,
			fn: function fn() {
				return _photo.delete([_photo.getID()]);
			}
		});
	}
	var button_move = $("#button_move");
	if (button_move && button_move.css("display") === "none") {
		items.unshift({
			title: build.iconic("folder") + lychee.locale["MOVE"],
			visible: lychee.enable_button_move,
			fn: function fn(event) {
				return contextMenu.move([_photo.getID()], event, _photo.setAlbum);
			}
		});
	}
	var button_rotate_cwise = $("#button_rotate_cwise");
	if (button_rotate_cwise && button_rotate_cwise.css("display") === "none") {
		items.unshift({
			title: build.iconic("clockwise") + lychee.locale["PHOTO_EDIT_ROTATECWISE"],
			visible: lychee.enable_button_move,
			fn: function fn() {
				return photoeditor.rotate(_photo.getID(), 1);
			}
		});
	}
	var button_rotate_ccwise = $("#button_rotate_ccwise");
	if (button_rotate_ccwise && button_rotate_ccwise.css("display") === "none") {
		items.unshift({
			title: build.iconic("counterclockwise") + lychee.locale["PHOTO_EDIT_ROTATECCWISE"],
			visible: lychee.enable_button_move,
			fn: function fn() {
				return photoeditor.rotate(_photo.getID(), -1);
			}
		});
	}

	basicContext.show(items, e.originalEvent);
};

contextMenu.getSubIDs = function (albums, albumID) {
	var ids = [parseInt(albumID, 10)];
	var a = void 0;

	for (a = 0; a < albums.length; a++) {
		if (parseInt(albums[a].parent_id, 10) === parseInt(albumID, 10)) {
			ids = ids.concat(contextMenu.getSubIDs(albums, albums[a].id));
		}

		if (albums[a].albums && albums[a].albums.length > 0) {
			ids = ids.concat(contextMenu.getSubIDs(albums[a].albums, albumID));
		}
	}

	return ids;
};

contextMenu.move = function (IDs, e, callback) {
	var kind = arguments.length > 3 && arguments[3] !== undefined ? arguments[3] : "UNSORTED";
	var display_root = arguments.length > 4 && arguments[4] !== undefined ? arguments[4] : true;

	var items = [];

	api.post("Albums::tree", {}, function (data) {
		var addItems = function addItems(albums) {
			// Disable all children
			// It's not possible to move us into them
			var i = void 0,
			    s = void 0;
			var exclude = [];
			for (i = 0; i < IDs.length; i++) {
				var sub = contextMenu.getSubIDs(albums, IDs[i]);
				for (s = 0; s < sub.length; s++) {
					exclude.push(sub[s]);
				}
			}
			if (visible.album()) {
				// For merging, don't exclude the parent.
				// For photo copy, don't exclude the current album.
				if (callback !== album.merge && callback !== _photo.copyTo) {
					exclude.push(album.getID().toString());
				}
				if (IDs.length === 1 && IDs[0] === album.getID() && album.getParent() && callback === album.setAlbum) {
					// If moving the current album, exclude its parent.
					exclude.push(album.getParent().toString());
				}
			} else if (visible.photo()) {
				exclude.push(_photo.json.album.toString());
			}
			items = items.concat(contextMenu.buildList(albums, exclude.concat(IDs), function (a) {
				return callback(IDs, a.id);
			}));
		};

		if (data.albums && data.albums.length > 0) {
			// items = items.concat(contextMenu.buildList(data.albums, [ album.getID() ], (a) => callback(IDs, a.id))); //photo.setAlbum

			addItems(data.albums);
		}

		if (data.shared_albums && data.shared_albums.length > 0 && lychee.admin) {
			items = items.concat({});
			addItems(data.shared_albums);
		}

		// Show Unsorted when unsorted is not the current album
		if (display_root && album.getID() !== "0" && !visible.albums()) {
			items.unshift({});
			items.unshift({ title: lychee.locale[kind], fn: function fn() {
					return callback(IDs, 0);
				} });
		}

		// Don't allow to move the current album to a newly created subalbum
		// (creating a cycle).
		if (IDs.length !== 1 || IDs[0] !== (album.json ? album.json.id : null) || callback !== album.setAlbum) {
			items.unshift({});
			items.unshift({ title: lychee.locale["NEW_ALBUM"], fn: function fn() {
					return album.add(IDs, callback);
				} });
		}

		basicContext.show(items, e.originalEvent, contextMenu.close);
	});
};

contextMenu.sharePhoto = function (photoID, e) {
	// v4+ only
	if (_photo.json.hasOwnProperty("share_button_visible") && _photo.json.share_button_visible !== "1") {
		return;
	}

	var iconClass = "ionicons";

	var items = [{ title: build.iconic("twitter", iconClass) + "Twitter", fn: function fn() {
			return _photo.share(photoID, "twitter");
		} }, { title: build.iconic("facebook", iconClass) + "Facebook", fn: function fn() {
			return _photo.share(photoID, "facebook");
		} }, { title: build.iconic("envelope-closed") + "Mail", fn: function fn() {
			return _photo.share(photoID, "mail");
		} }, { title: build.iconic("dropbox", iconClass) + "Dropbox", visible: lychee.admin === true, fn: function fn() {
			return _photo.share(photoID, "dropbox");
		} }, { title: build.iconic("link-intact") + lychee.locale["DIRECT_LINKS"], fn: function fn() {
			return _photo.showDirectLinks(photoID);
		} }];

	basicContext.show(items, e.originalEvent);
};

contextMenu.shareAlbum = function (albumID, e) {
	// v4+ only
	if (album.json.hasOwnProperty("share_button_visible") && album.json.share_button_visible !== "1") {
		return;
	}

	var iconClass = "ionicons";

	var items = [{ title: build.iconic("twitter", iconClass) + "Twitter", fn: function fn() {
			return album.share("twitter");
		} }, { title: build.iconic("facebook", iconClass) + "Facebook", fn: function fn() {
			return album.share("facebook");
		} }, { title: build.iconic("envelope-closed") + "Mail", fn: function fn() {
			return album.share("mail");
		} }, {
		title: build.iconic("link-intact") + lychee.locale["DIRECT_LINK"],
		fn: function fn() {
			var url = lychee.getBaseUrl() + "r/" + albumID;
			if (album.json.password === "1") {
				// Copy the url with prefilled password param
				url += "?password=";
			}
			if (lychee.clipboardCopy(url)) {
				loadingBar.show("success", lychee.locale["URL_COPIED_TO_CLIPBOARD"]);
			}
		}
	}];

	basicContext.show(items, e.originalEvent);
};

contextMenu.close = function () {
	if (!visible.contextMenu()) return false;

	basicContext.close();

	multiselect.clearSelection();
	if (visible.multiselect()) {
		multiselect.close();
	}
};

contextMenu.config = function (e) {
	var items = [{ title: build.iconic("cog") + lychee.locale["SETTINGS"], fn: settings.open }];
	if (lychee.admin) {
		items.push({ title: build.iconic("person") + lychee.locale["USERS"], fn: users.list });
	}
	items.push({ title: build.iconic("key") + lychee.locale["U2F"], fn: u2f.list });
	items.push({ title: build.iconic("cloud") + lychee.locale["SHARING"], fn: sharing.list });
	if (lychee.admin) {
		items.push({
			title: build.iconic("align-left") + lychee.locale["LOGS"],
			fn: function fn() {
				view.logs.init();
			}
		});
		items.push({
			title: build.iconic("wrench") + lychee.locale["DIAGNOSTICS"],
			fn: function fn() {
				view.diagnostics.init();
			}
		});
		if (lychee.update_available) {
			items.push({ title: build.iconic("timer") + lychee.locale["UPDATE_AVAILABLE"], fn: view.update.init });
		}
	}
	items.push({ title: build.iconic("info") + lychee.locale["ABOUT_LYCHEE"], fn: lychee.aboutDialog });
	items.push({ title: build.iconic("account-logout") + lychee.locale["SIGN_OUT"], fn: lychee.logout });

	basicContext.show(items, e.originalEvent);
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

		if (visible.photo()) contextMenu.photoTitle(album.getID(), _photo.getID(), e);else contextMenu.albumTitle(album.getID(), e);
	});

	header.dom("#button_visibility").on(eventName, function (e) {
		_photo.setPublic(_photo.getID(), e);
	});
	header.dom("#button_share").on(eventName, function (e) {
		contextMenu.sharePhoto(_photo.getID(), e);
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
	header.dom("#button_info_album").on(eventName, _sidebar.toggle);
	header.dom("#button_info").on(eventName, _sidebar.toggle);
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
		contextMenu.photoMore(_photo.getID(), e);
	});
	header.dom("#button_move_album").on(eventName, function (e) {
		contextMenu.move([album.getID()], e, album.setAlbum, "ROOT", album.getParent() != "");
	});
	header.dom("#button_nsfw_album").on(eventName, function (e) {
		album.setNSFW(album.getID());
	});
	header.dom("#button_move").on(eventName, function (e) {
		contextMenu.move([_photo.getID()], e, _photo.setAlbum);
	});
	header.dom(".header__hostedwith").on(eventName, function () {
		window.open(lychee.website);
	});
	header.dom("#button_trash_album").on(eventName, function () {
		album.delete([album.getID()]);
	});
	header.dom("#button_trash").on(eventName, function () {
		_photo.delete([_photo.getID()]);
	});
	header.dom("#button_archive").on(eventName, function () {
		album.getArchive([album.getID()]);
	});
	header.dom("#button_star").on(eventName, function () {
		_photo.setStar([_photo.getID()]);
	});
	header.dom("#button_rotate_ccwise").on(eventName, function () {
		photoeditor.rotate(_photo.getID(), -1);
	});
	header.dom("#button_rotate_cwise").on(eventName, function () {
		photoeditor.rotate(_photo.getID(), 1);
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

	_photo.updateSizeLivePhotoDuringAnimation();

	return true;
};

header.hideIfLivePhotoNotPlaying = function () {
	// Hides the header, if current live photo is not playing
	if (_photo.isLivePhotoPlaying() == true) return false;
	return header.hide();
};

header.hide = function () {
	if (visible.photo() && !visible.sidebar() && !visible.contextMenu() && basicModal.visible() === false) {
		tabindex.saveSettings(header.dom());
		tabindex.makeUnfocusable(header.dom());

		lychee.imageview.addClass("full");
		header.dom().addClass("header--hidden");

		_photo.updateSizeLivePhotoDuringAnimation();

		return true;
	}

	return false;
};

header.setTitle = function () {
	var title = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : "Untitled";

	var $title = header.dom(".header__title");
	var html = lychee.html(_templateObject44, title, build.iconic("caret-bottom"));

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
				var _e2 = $(".header__search, .header__clear", ".header__toolbar--public");
				_e2.hide();
				tabindex.makeUnfocusable(_e2);
			}

			// Set icon in Public mode
			if (lychee.map_display_public) {
				var _e3 = $(".button--map-albums", ".header__toolbar--public");
				_e3.show();
				tabindex.makeFocusable(_e3);
			} else {
				var _e4 = $(".button--map-albums", ".header__toolbar--public");
				_e4.hide();
				tabindex.makeUnfocusable(_e4);
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
				var _e5 = $(".button--map-albums", ".header__toolbar--albums");
				_e5.show();
				tabindex.makeFocusable(_e5);
			} else {
				var _e6 = $(".button--map-albums", ".header__toolbar--albums");
				_e6.hide();
				tabindex.makeUnfocusable(_e6);
			}

			if (lychee.enable_button_add) {
				var _e7 = $(".button_add", ".header__toolbar--albums");
				_e7.show();
				tabindex.makeFocusable(_e7);
			} else {
				var _e8 = $(".button_add", ".header__toolbar--albums");
				_e8.remove();
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
				var _e9 = $("#button_archive");
				_e9.hide();
				tabindex.makeUnfocusable(_e9);
			} else {
				var _e10 = $("#button_archive");
				_e10.show();
				tabindex.makeFocusable(_e10);
			}

			if (album.json && album.json.hasOwnProperty("share_button_visible") && album.json.share_button_visible !== "1") {
				var _e11 = $("#button_share_album");
				_e11.hide();
				tabindex.makeUnfocusable(_e11);
			} else {
				var _e12 = $("#button_share_album");
				_e12.show();
				tabindex.makeFocusable(_e12);
			}

			// If map is disabled, we should hide the icon
			if (lychee.publicMode === true ? lychee.map_display_public : lychee.map_display) {
				var _e13 = $("#button_map_album");
				_e13.show();
				tabindex.makeFocusable(_e13);
			} else {
				var _e14 = $("#button_map_album");
				_e14.hide();
				tabindex.makeUnfocusable(_e14);
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
				var _e15 = $("#button_visibility_album", "#button_sharing_album_users", ".header__toolbar--album");
				_e15.remove();
			}
			if (!lychee.enable_button_share) {
				var _e16 = $("#button_share_album", ".header__toolbar--album");
				_e16.remove();
			}
			if (!lychee.enable_button_archive) {
				var _e17 = $("#button_archive", ".header__toolbar--album");
				_e17.remove();
			}
			if (!lychee.enable_button_move) {
				var _e18 = $("#button_move_album", ".header__toolbar--album");
				_e18.remove();
			}
			if (!lychee.enable_button_trash) {
				var _e19 = $("#button_trash_album", ".header__toolbar--album");
				_e19.remove();
			}
			if (!lychee.enable_button_fullscreen || !lychee.fullscreenAvailable()) {
				var _e20 = $("#button_fs_album_enter", ".header__toolbar--album");
				_e20.remove();
			}
			if (!lychee.enable_button_add) {
				var _e21 = $(".button_add", ".header__toolbar--album");
				_e21.remove();
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
				var _e22 = $("#button_map");
				_e22.show();
				tabindex.makeFocusable(_e22);
			} else {
				var _e23 = $("#button_map");
				_e23.hide();
				tabindex.makeUnfocusable(_e23);
			}

			if (album.isUploadable()) {
				var _e24 = $("#button_trash, #button_move, #button_visibility, #button_star");
				_e24.show();
				tabindex.makeFocusable(_e24);
			} else {
				var _e25 = $("#button_trash, #button_move, #button_visibility, #button_star");
				_e25.hide();
				tabindex.makeUnfocusable(_e25);
			}

			if (_photo.json && _photo.json.hasOwnProperty("share_button_visible") && _photo.json.share_button_visible !== "1") {
				var _e26 = $("#button_share");
				_e26.hide();
				tabindex.makeUnfocusable(_e26);
			} else {
				var _e27 = $("#button_share");
				_e27.show();
				tabindex.makeFocusable(_e27);
			}

			// Hide More menu if empty (see contextMenu.photoMore)
			$("#button_more").show();
			tabindex.makeFocusable($("#button_more"));
			if (!(album.isUploadable() || (_photo.json.hasOwnProperty("downloadable") ? _photo.json.downloadable === "1" : album.json && album.json.downloadable && album.json.downloadable === "1")) && !(_photo.json.url && _photo.json.url !== "")) {
				var _e28 = $("#button_more");
				_e28.hide();
				tabindex.makeUnfocusable(_e28);
			}

			// Remove buttons if needed
			if (!lychee.enable_button_visibility) {
				var _e29 = $("#button_visibility", ".header__toolbar--photo");
				_e29.remove();
			}
			if (!lychee.enable_button_share) {
				var _e30 = $("#button_share", ".header__toolbar--photo");
				_e30.remove();
			}
			if (!lychee.enable_button_move) {
				var _e31 = $("#button_move", ".header__toolbar--photo");
				_e31.remove();
			}
			if (!lychee.enable_button_trash) {
				var _e32 = $("#button_trash", ".header__toolbar--photo");
				_e32.remove();
			}
			if (!lychee.enable_button_fullscreen || !lychee.fullscreenAvailable()) {
				var _e33 = $("#button_fs_enter", ".header__toolbar--photo");
				_e33.remove();
			}
			if (!lychee.enable_button_more) {
				var _e34 = $("#button_more", ".header__toolbar--photo");
				_e34.remove();
			}
			if (!lychee.enable_button_rotate) {
				var _e35 = $("#button_rotate_cwise", ".header__toolbar--photo");
				_e35.remove();

				_e35 = $("#button_rotate_ccwise", ".header__toolbar--photo");
				_e35.remove();
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
 * @description This module is used for bindings.
 */

$(document).ready(function () {
	$("#sensitive_warning").hide();

	// Event Name
	var eventName = lychee.getEventName();

	// set CSRF protection (Laravel)
	csrf.bind();

	// Set API error handler
	api.onError = lychee.error;

	$("html").css("visibility", "visible");

	// Multiselect
	multiselect.bind();

	// Header
	header.bind();

	// Image View
	lychee.imageview.on(eventName, ".arrow_wrapper--previous", _photo.previous).on(eventName, ".arrow_wrapper--next", _photo.next).on(eventName, "img, #livephoto", _photo.cycle_display_overlay);

	// Keyboard
	Mousetrap.addKeycodes({
		18: "ContextMenu",
		179: "play_pause",
		227: "rewind",
		228: "forward"
	});

	Mousetrap.bind(["l"], function () {
		lychee.loginDialog();
		return false;
	}).bind(["k"], function () {
		u2f.login();
		return false;
	}).bind(["left"], function () {
		if (visible.photo() && (!visible.header() || $("img#image").is(":focus") || $("img#livephoto").is(":focus") || $(":focus").length === 0)) {
			$("#imageview a#previous").click();
			return false;
		}
		return true;
	}).bind(["right"], function () {
		if (visible.photo() && (!visible.header() || $("img#image").is(":focus") || $("img#livephoto").is(":focus") || $(":focus").length === 0)) {
			$("#imageview a#next").click();
			return false;
		}
		return true;
	}).bind(["u"], function () {
		if (!visible.photo() && album.isUploadable()) {
			$("#upload_files").click();
			return false;
		}
	}).bind(["n"], function () {
		if (!visible.photo() && album.isUploadable()) {
			album.add();
			return false;
		}
	}).bind(["s"], function () {
		if (visible.photo() && album.isUploadable()) {
			header.dom("#button_star").click();
			return false;
		} else if (visible.albums()) {
			header.dom(".header__search").focus();
			return false;
		}
	}).bind(["r"], function () {
		if (album.isUploadable()) {
			if (visible.album()) {
				album.setTitle(album.getID());
				return false;
			} else if (visible.photo()) {
				_photo.setTitle([_photo.getID()]);
				return false;
			}
		}
	}).bind(["h"], album.toggle_nsfw_filter).bind(["d"], function () {
		if (album.isUploadable()) {
			if (visible.photo()) {
				_photo.setDescription(_photo.getID());
				return false;
			} else if (visible.album()) {
				album.setDescription(album.getID());
				return false;
			}
		}
	}).bind(["t"], function () {
		if (visible.photo() && album.isUploadable()) {
			_photo.editTags([_photo.getID()]);
			return false;
		}
	}).bind(["i", "ContextMenu"], function () {
		if (!visible.multiselect()) {
			_sidebar.toggle();
			return false;
		}
	}).bind(["command+backspace", "ctrl+backspace"], function () {
		if (album.isUploadable()) {
			if (visible.photo() && basicModal.visible() === false) {
				_photo.delete([_photo.getID()]);
				return false;
			} else if (visible.album() && basicModal.visible() === false) {
				album.delete([album.getID()]);
				return false;
			}
		}
	}).bind(["command+a", "ctrl+a"], function () {
		if (visible.album() && basicModal.visible() === false) {
			multiselect.selectAll();
			return false;
		} else if (visible.albums() && basicModal.visible() === false) {
			multiselect.selectAll();
			return false;
		}
	}).bind(["o"], function () {
		if (visible.photo()) {
			_photo.cycle_display_overlay();
			return false;
		}
	}).bind(["f"], function () {
		if (visible.album() || visible.photo()) {
			lychee.fullscreenToggle();
			return false;
		}
	});

	Mousetrap.bind(["play_pause"], function () {
		// If it's a video, we toggle play/pause
		var video = $("video");

		if (video.length !== 0) {
			if (video[0].paused) {
				video[0].play();
			} else {
				video[0].pause();
			}
		}
	});

	Mousetrap.bindGlobal("enter", function () {
		if (basicModal.visible() === true) {
			// check if any of the input fields is focussed
			// apply action, other do nothing
			if ($(".basicModal__content input").is(":focus")) {
				basicModal.action();
				return false;
			}
		} else if (visible.photo() && !lychee.header_auto_hide && ($("img#image").is(":focus") || $("img#livephoto").is(":focus") || $(":focus").length === 0)) {
			if (visible.header()) {
				header.hide();
			} else {
				header.show();
			}
			return false;
		}
		var clicked = false;
		$(":focus").each(function () {
			if (!$(this).is("input")) {
				$(this).click();
				clicked = true;
			}
		});
		if (clicked) {
			return false;
		}
	});

	// Prevent 'esc keyup' event to trigger 'go back in history'
	// and 'alt keyup' to show a webapp context menu for Fire TV
	Mousetrap.bindGlobal(["esc", "ContextMenu"], function () {
		return false;
	}, "keyup");

	Mousetrap.bindGlobal(["esc", "command+up"], function () {
		if (basicModal.visible() === true) basicModal.cancel();else if (visible.config() || visible.leftMenu()) leftMenu.close();else if (visible.contextMenu()) contextMenu.close();else if (visible.photo()) lychee.goto(album.getID());else if (visible.album() && !album.json.parent_id) lychee.goto();else if (visible.album()) lychee.goto(album.getParent());else if (visible.albums() && search.hash !== null) search.reset();else if (visible.mapview()) mapview.close();else if (visible.albums() && lychee.enable_close_tab_on_esc) {
			window.open("", "_self").close();
		}
		return false;
	});

	$(document)
	// Fullscreen on mobile
	.on("touchend", "#imageview #image", function (e) {
		// prevent triggering event 'mousemove'
		// why? this also prevents 'click' from firing which results in unexpected behaviour
		// unable to reproduce problems arising from 'mousemove' on iOS devices
		//			e.preventDefault();

		if (typeof swipe.obj === "undefined" || Math.abs(swipe.offsetX) <= 5 && Math.abs(swipe.offsetY) <= 5) {
			// Toggle header only if we're not moving to next/previous photo;
			// In this case, swipe.preventNextHeaderToggle is set to true
			if (typeof swipe.preventNextHeaderToggle === "undefined" || !swipe.preventNextHeaderToggle) {
				if (visible.header()) {
					header.hide(e);
				} else {
					header.show();
				}
			}

			// For next 'touchend', behave again as normal and toggle header
			swipe.preventNextHeaderToggle = false;
		}
	});
	$("#imageview")
	// Swipe on mobile
	.swipe().on("swipeStart", function () {
		if (visible.photo()) swipe.start($("#imageview #image, #imageview #livephoto"));
	}).swipe().on("swipeMove", function (e) {
		if (visible.photo()) swipe.move(e.swipe);
	}).swipe().on("swipeEnd", function (e) {
		if (visible.photo()) swipe.stop(e.swipe, _photo.previous, _photo.next);
	});

	// Document
	$(document)
	// Navigation
	.on("click", ".album", function (e) {
		multiselect.albumClick(e, $(this));
	}).on("click", ".photo", function (e) {
		multiselect.photoClick(e, $(this));
	})
	// Context Menu
	.on("contextmenu", ".photo", function (e) {
		multiselect.photoContextMenu(e, $(this));
	}).on("contextmenu", ".album", function (e) {
		multiselect.albumContextMenu(e, $(this));
	})
	// Upload
	.on("change", "#upload_files", function () {
		basicModal.close();
		upload.start.local(this.files);
	})
	// Drag and Drop upload
	.on("dragover", function () {
		return false;
	}, false).on("drop", function (e) {
		if (!album.isUploadable() || visible.contextMenu() || basicModal.visible() || visible.leftMenu() || visible.config() || !(visible.album() || visible.albums())) {
			return false;
		}

		// Detect if dropped item is a file or a link
		if (e.originalEvent.dataTransfer.files.length > 0) upload.start.local(e.originalEvent.dataTransfer.files);else if (e.originalEvent.dataTransfer.getData("Text").length > 3) upload.start.url(e.originalEvent.dataTransfer.getData("Text"));

		return false;
	})
	// click on thumbnail on map
	.on("click", ".image-leaflet-popup", function (e) {
		mapview.goto($(this));
	})
	// Paste upload
	.on("paste", function (e) {
		if (e.originalEvent.clipboardData.items) {
			var items = e.originalEvent.clipboardData.items;
			var filesToUpload = [];

			// Search clipboard items for an image
			for (var i = 0; i < items.length; i++) {
				if (items[i].type.indexOf("image") !== -1 || items[i].type.indexOf("video") !== -1) {
					filesToUpload.push(items[i].getAsFile());
				}
			}

			if (filesToUpload.length > 0) {
				// We perform the check so deep because we don't want to
				// prevent the paste from working in text input fields, etc.
				if (album.isUploadable() && !visible.contextMenu() && !basicModal.visible() && !visible.leftMenu() && !visible.config() && (visible.album() || visible.albums())) {
					upload.start.local(filesToUpload);
				}

				return false;
			}
		}
	});
	// Fullscreen
	if (lychee.fullscreenAvailable()) $(document).on("fullscreenchange mozfullscreenchange webkitfullscreenchange msfullscreenchange", lychee.fullscreenUpdate);

	$("#sensitive_warning").on("click", view.album.nsfw_warning.next);

	var rememberScrollPage = function rememberScrollPage(scrollPos) {
		// only for albums with subalbums
		if (album && album.json && album.json.albums && album.json.albums.length > 0) {
			var urls = JSON.parse(localStorage.getItem("scroll"));
			if (urls == null || urls.length < 1) {
				urls = {};
			}

			var urlWindow = window.location.href;
			var urlScroll = scrollPos;

			urls[urlWindow] = urlScroll;

			if (urlScroll < 1) {
				delete urls[urlWindow];
			}

			localStorage.setItem("scroll", JSON.stringify(urls));
		}
	};

	$(window)
	// resize
	.on("resize", function () {
		if (visible.album() || visible.search()) view.album.content.justify();
		if (visible.photo()) view.photo.onresize();
	})
	// remember scroll positions
	.on("scroll", function () {
		var topScroll = $(window).scrollTop();
		rememberScrollPage(topScroll);
	});

	// Init
	lychee.init();
});

/**
 * @description This module is used for the context menu.
 */

var leftMenu = {
	_dom: $(".leftMenu")
};

leftMenu.dom = function (selector) {
	if (selector == null || selector === "") return leftMenu._dom;
	return leftMenu._dom.find(selector);
};

leftMenu.build = function () {
	var html = lychee.html(_templateObject45, lychee.locale["CLOSE"], lychee.locale["SETTINGS"]);
	html += lychee.html(_templateObject46, build.iconic("person"), lychee.locale["USERS"], build.iconic("key"), lychee.locale["U2F"], build.iconic("cloud"), lychee.locale["SHARING"]);
	html += lychee.html(_templateObject47, build.iconic("align-left"), lychee.locale["LOGS"], build.iconic("wrench"), lychee.locale["DIAGNOSTICS"], build.iconic("info"), lychee.locale["ABOUT_LYCHEE"], build.iconic("account-logout"), lychee.locale["SIGN_OUT"]);
	if (lychee.update_available) {
		html += lychee.html(_templateObject48, build.iconic("timer"), lychee.locale["UPDATE_AVAILABLE"]);
	}
	leftMenu._dom.html(html);
};

/* Set the width of the side navigation to 250px and the left margin of the page content to 250px */
leftMenu.open = function () {
	leftMenu._dom.addClass("leftMenu__visible");
	lychee.content.addClass("leftMenu__open");
	lychee.footer.addClass("leftMenu__open");
	header.dom(".header__title").addClass("leftMenu__open");
	loadingBar.dom().addClass("leftMenu__open");

	// Make background unfocusable
	tabindex.makeUnfocusable(header.dom());
	tabindex.makeUnfocusable(lychee.content);
	tabindex.makeFocusable(leftMenu._dom);
	$("#button_signout").focus();

	multiselect.unbind();
};

/* Set the width of the side navigation to 0 and the left margin of the page content to 0 */
leftMenu.close = function () {
	leftMenu._dom.removeClass("leftMenu__visible");
	lychee.content.removeClass("leftMenu__open");
	lychee.footer.removeClass("leftMenu__open");
	$(".content").removeClass("leftMenu__open");
	header.dom(".header__title").removeClass("leftMenu__open");
	loadingBar.dom().removeClass("leftMenu__open");

	tabindex.makeFocusable(header.dom());
	tabindex.makeFocusable(lychee.content);
	tabindex.makeUnfocusable(leftMenu._dom);

	multiselect.bind();
	lychee.load();
};

leftMenu.bind = function () {
	// Event Name
	var eventName = lychee.getEventName();

	leftMenu.dom("#button_settings_close").on(eventName, leftMenu.close);
	leftMenu.dom("#text_settings_close").on(eventName, leftMenu.close);
	leftMenu.dom("#button_settings_open").on(eventName, settings.open);
	leftMenu.dom("#button_signout").on(eventName, lychee.logout);
	leftMenu.dom("#button_logs").on(eventName, leftMenu.Logs);
	leftMenu.dom("#button_diagnostics").on(eventName, leftMenu.Diagnostics);
	leftMenu.dom("#button_about").on(eventName, lychee.aboutDialog);
	leftMenu.dom("#button_users").on(eventName, leftMenu.Users);
	leftMenu.dom("#button_u2f").on(eventName, leftMenu.u2f);
	leftMenu.dom("#button_sharing").on(eventName, leftMenu.Sharing);
	leftMenu.dom("#button_update").on(eventName, leftMenu.Update);

	return true;
};

leftMenu.Logs = function () {
	view.logs.init();
};

leftMenu.Diagnostics = function () {
	view.diagnostics.init();
};

leftMenu.Update = function () {
	view.update.init();
};

leftMenu.Users = function () {
	users.list();
};

leftMenu.u2f = function () {
	u2f.list();
};

leftMenu.Sharing = function () {
	sharing.list();
};

/**
 * @description This module is used to show and hide the loading bar.
 */

var loadingBar = {
	status: null,
	_dom: $("#loading")
};

loadingBar.dom = function (selector) {
	if (selector == null || selector === "") return loadingBar._dom;
	return loadingBar._dom.find(selector);
};

loadingBar.show = function (status, errorText) {
	if (status === "error") {
		// Set status
		loadingBar.status = "error";

		// Parse text
		if (errorText) errorText = errorText.replace("<br>", "");
		if (!errorText) errorText = lychee.locale["ERROR_TEXT"];

		// Move header down
		if (visible.header()) header.dom().addClass("header--error");

		// Also move down the dark background
		if (basicModal.visible()) {
			$(".basicModalContainer").addClass("basicModalContainer--error");
			$(".basicModal").addClass("basicModal--error");
		}

		// Modify loading
		loadingBar.dom().removeClass("loading uploading error success").html("<h1>" + lychee.locale["ERROR"] + (": <span>" + errorText + "</span></h1>")).addClass(status).show();

		// Set timeout
		clearTimeout(loadingBar._timeout);
		loadingBar._timeout = setTimeout(function () {
			return loadingBar.hide(true);
		}, 3000);

		return true;
	}

	if (status === "success") {
		// Set status
		loadingBar.status = "success";

		// Parse text
		if (errorText) errorText = errorText.replace("<br>", "");
		if (!errorText) errorText = lychee.locale["ERROR_TEXT"];

		// Move header down
		if (visible.header()) header.dom().addClass("header--error");

		// Also move down the dark background
		if (basicModal.visible()) {
			$(".basicModalContainer").addClass("basicModalContainer--error");
			$(".basicModal").addClass("basicModal--error");
		}

		// Modify loading
		loadingBar.dom().removeClass("loading uploading error success").html("<h1>" + lychee.locale["SUCCESS"] + (": <span>" + errorText + "</span></h1>")).addClass(status).show();

		// Set timeout
		clearTimeout(loadingBar._timeout);
		loadingBar._timeout = setTimeout(function () {
			return loadingBar.hide(true);
		}, 2000);

		return true;
	}

	if (loadingBar.status === null) {
		// Set status
		loadingBar.status = lychee.locale["LOADING"];

		// Set timeout
		clearTimeout(loadingBar._timeout);
		loadingBar._timeout = setTimeout(function () {
			// Move header down
			if (visible.header()) header.dom().addClass("header--loading");

			// Modify loading
			loadingBar.dom().removeClass("loading uploading error").html("").addClass("loading").show();
		}, 1000);

		return true;
	}
};

loadingBar.hide = function (force) {
	if (loadingBar.status !== "error" && loadingBar.status !== "success" && loadingBar.status != null || force) {
		// Remove status
		loadingBar.status = null;

		// Move header up
		header.dom().removeClass("header--error header--loading");
		// Also move up the dark background
		$(".basicModalContainer").removeClass("basicModalContainer--error");
		$(".basicModal").removeClass("basicModal--error");

		// Set timeout
		clearTimeout(loadingBar._timeout);
		setTimeout(function () {
			return loadingBar.dom().hide();
		}, 300);
	}
};

/**
 * @description This module provides the basic functions of Lychee.
 */

var lychee = {
	title: document.title,
	version: "",
	versionCode: "", // not really needed anymore

	updatePath: "https://LycheeOrg.github.io/update.json",
	updateURL: "https://github.com/LycheeOrg/Lychee/releases",
	website: "https://LycheeOrg.github.io",

	publicMode: false,
	viewMode: false,
	full_photo: true,
	downloadable: false,
	public_photos_hidden: true,
	share_button_visible: false, // enable only v4+
	api_V2: false, // enable api_V2
	sub_albums: false, // enable sub_albums features
	admin: false, // enable admin mode (multi-user)
	upload: false, // enable possibility to upload (multi-user)
	lock: false, // locked user (multi-user)
	username: null,
	layout: "1", // 0: Use default, "square" layout. 1: Use Flickr-like "justified" layout. 2: Use Google-like "unjustified" layout
	public_search: false, // display Search in publicMode
	image_overlay_type: "exif", // current Overlay display type
	image_overlay_type_default: "exif", // image overlay type default type
	map_display: false, // display photo coordinates on map
	map_display_public: false, // display photos of public album on map (user not logged in)
	map_display_direction: true, // use the GPS direction data on displayed maps
	map_provider: "Wikimedia", // Provider of OSM Tiles
	map_include_subalbums: false, // include photos of subalbums on map
	location_decoding: false, // retrieve location name from GPS data
	location_decoding_caching_type: "Harddisk", // caching mode for GPS data decoding
	location_show: false, // show location name
	location_show_public: false, // show location name for public albums
	swipe_tolerance_x: 150, // tolerance for navigating when swiping images to the left and right on mobile
	swipe_tolerance_y: 250, // tolerance for navigating when swiping images up and down

	landing_page_enabled: false, // is landing page enabled ?
	delete_imported: false,
	import_via_symlink: false,
	skip_duplicates: false,

	nsfw_visible: true,
	nsfw_visible_saved: true,
	nsfw_blur: false,
	nsfw_warning: false,

	album_subtitle_type: "oldstyle",

	upload_processing_limit: 4,

	// this is device specific config, in this case default is Desktop.
	header_auto_hide: true,
	active_focus_on_page_load: false,
	enable_button_visibility: true,
	enable_button_share: true,
	enable_button_archive: true,
	enable_button_move: true,
	enable_button_trash: true,
	enable_button_fullscreen: true,
	enable_button_download: true,
	enable_button_add: true,
	enable_button_more: true,
	enable_button_rotate: true,
	enable_close_tab_on_esc: false,
	enable_tabindex: false,
	enable_contextmenu_header: true,
	hide_content_during_imageview: false,
	device_type: "desktop",

	checkForUpdates: "1",
	update_json: 0,
	update_available: false,
	sortingPhotos: "",
	sortingAlbums: "",
	location: "",

	lang: "",
	lang_available: {},

	dropbox: false,
	dropboxKey: "",

	content: $(".content"),
	imageview: $("#imageview"),
	footer: $("#footer"),

	locale: {},

	nsfw_unlocked_albums: []
};

lychee.diagnostics = function () {
	return "/Diagnostics";
};

lychee.logs = function () {
	return "/Logs";
};

lychee.aboutDialog = function () {
	var msg = lychee.html(_templateObject49, lychee.version, lychee.updateURL, lychee.locale["UPDATE_AVAILABLE"], lychee.locale["ABOUT_SUBTITLE"], lychee.website, lychee.locale["ABOUT_DESCRIPTION"]);

	basicModal.show({
		body: msg,
		buttons: {
			cancel: {
				title: lychee.locale["CLOSE"],
				fn: basicModal.close
			}
		}
	});

	if (lychee.checkForUpdates === "1") lychee.getUpdate();
};

lychee.init = function () {
	var exitview = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : true;

	lychee.adjustContentHeight();

	api.post("Session::init", {}, function (data) {
		if (data.status === 0) {
			// No configuration

			lychee.setMode("public");

			header.dom().hide();
			lychee.content.hide();
			$("body").append(build.no_content("cog"));
			settings.createConfig();

			return true;
		}

		lychee.sub_albums = data.sub_albums || false;
		lychee.update_json = data.update_json;
		lychee.update_available = data.update_available;
		lychee.landing_page_enable = data.config.landing_page_enable && data.config.landing_page_enable === "1" || false;

		lychee.versionCode = data.config.version;
		if (lychee.versionCode !== "") {
			var digits = lychee.versionCode.match(/.{1,2}/g);
			lychee.version = parseInt(digits[0]).toString() + "." + parseInt(digits[1]).toString() + "." + parseInt(digits[2]).toString();
		}

		// we copy the locale that exists only.
		// This ensure forward and backward compatibility.
		// e.g. if the front localization is unfished in a language
		// or if we need to change some locale string
		for (var key in data.locale) {
			lychee.locale[key] = data.locale[key];
		}

		var validatedSwipeToleranceX = data.config.swipe_tolerance_x && !isNaN(parseInt(data.config.swipe_tolerance_x)) && parseInt(data.config.swipe_tolerance_x) || 150;
		var validatedSwipeToleranceY = data.config.swipe_tolerance_y && !isNaN(parseInt(data.config.swipe_tolerance_y)) && parseInt(data.config.swipe_tolerance_y) || 250;

		// Check status
		// 0 = No configuration
		// 1 = Logged out
		// 2 = Logged in
		if (data.status === 2) {
			// Logged in

			lychee.sortingPhotos = data.config.sorting_Photos || data.config.sortingPhotos || "";
			lychee.sortingAlbums = data.config.sorting_Albums || data.config.sortingAlbums || "";
			lychee.album_subtitle_type = data.config.album_subtitle_type || "oldstyle";
			lychee.dropboxKey = data.config.dropbox_key || data.config.dropboxKey || "";
			lychee.location = data.config.location || "";
			lychee.checkForUpdates = data.config.check_for_updates || data.config.checkForUpdates || "1";
			lychee.lang = data.config.lang || "";
			lychee.lang_available = data.config.lang_available || {};
			lychee.layout = data.config.layout || "1";
			lychee.public_search = data.config.public_search && data.config.public_search === "1" || false;
			lychee.image_overlay_type = !data.config.image_overlay_type ? "exif" : data.config.image_overlay_type;
			lychee.image_overlay_type_default = lychee.image_overlay_type;
			lychee.map_display = data.config.map_display && data.config.map_display === "1" || false;
			lychee.map_display_public = data.config.map_display_public && data.config.map_display_public === "1" || false;
			lychee.map_display_direction = data.config.map_display_direction && data.config.map_display_direction === "1" || false;
			lychee.map_provider = !data.config.map_provider ? "Wikimedia" : data.config.map_provider;
			lychee.map_include_subalbums = data.config.map_include_subalbums && data.config.map_include_subalbums === "1" || false;
			lychee.location_decoding = data.config.location_decoding && data.config.location_decoding === "1" || false;
			lychee.location_decoding_caching_type = !data.config.location_decoding_caching_type ? "Harddisk" : data.config.location_decoding_caching_type;
			lychee.location_show = data.config.location_show && data.config.location_show === "1" || false;
			lychee.location_show_public = data.config.location_show_public && data.config.location_show_public === "1" || false;
			lychee.swipe_tolerance_x = validatedSwipeToleranceX;
			lychee.swipe_tolerance_y = validatedSwipeToleranceY;

			lychee.default_license = data.config.default_license || "none";
			lychee.css = data.config.css || "";
			lychee.full_photo = data.config.full_photo == null || data.config.full_photo === "1";
			lychee.downloadable = data.config.downloadable && data.config.downloadable === "1" || false;
			lychee.public_photos_hidden = data.config.public_photos_hidden == null || data.config.public_photos_hidden === "1";
			lychee.share_button_visible = data.config.share_button_visible && data.config.share_button_visible === "1" || false;
			lychee.delete_imported = data.config.delete_imported && data.config.delete_imported === "1";
			lychee.import_via_symlink = data.config.import_via_symlink && data.config.import_via_symlink === "1";
			lychee.skip_duplicates = data.config.skip_duplicates && data.config.skip_duplicates === "1";
			lychee.nsfw_visible = data.config.nsfw_visible && data.config.nsfw_visible === "1" || false;
			lychee.nsfw_blur = data.config.nsfw_blur && data.config.nsfw_blur === "1" || false;
			lychee.nsfw_warning = data.config.nsfw_warning_admin && data.config.nsfw_warning_admin === "1" || false;

			lychee.header_auto_hide = data.config_device.header_auto_hide;
			lychee.active_focus_on_page_load = data.config_device.active_focus_on_page_load;
			lychee.enable_button_visibility = data.config_device.enable_button_visibility;
			lychee.enable_button_share = data.config_device.enable_button_share;
			lychee.enable_button_archive = data.config_device.enable_button_archive;
			lychee.enable_button_move = data.config_device.enable_button_move;
			lychee.enable_button_trash = data.config_device.enable_button_trash;
			lychee.enable_button_fullscreen = data.config_device.enable_button_fullscreen;
			lychee.enable_button_download = data.config_device.enable_button_download;
			lychee.enable_button_add = data.config_device.enable_button_add;
			lychee.enable_button_more = data.config_device.enable_button_more;
			lychee.enable_button_rotate = data.config_device.enable_button_rotate;
			lychee.enable_close_tab_on_esc = data.config_device.enable_close_tab_on_esc;
			lychee.enable_tabindex = data.config_device.enable_tabindex;
			lychee.enable_contextmenu_header = data.config_device.enable_contextmenu_header;
			lychee.hide_content_during_imgview = data.config_device.hide_content_during_imgview;
			lychee.device_type = data.config_device.device_type || "desktop"; // we set default as Desktop

			lychee.editor_enabled = data.config.editor_enabled && data.config.editor_enabled === "1" || false;

			lychee.nsfw_visible_saved = lychee.nsfw_visible;

			lychee.upload_processing_limit = parseInt(data.config.upload_processing_limit);
			// when null or any non stringified numeric value is sent from the server we get NaN.
			// we fix this.
			if (isNaN(lychee.upload_processing_limit)) lychee.upload_processing_limit = 4;

			// leftMenu
			leftMenu.build();
			leftMenu.bind();

			lychee.upload = data.admin || data.upload;
			lychee.admin = data.admin;
			lychee.lock = data.lock;
			lychee.username = data.username;
			lychee.setMode("logged_in");

			// Show dialog when there is no username and password
			if (data.config.login === false) settings.createLogin();
		} else if (data.status === 1) {
			// Logged out

			// TODO remove sortingPhoto once the v4 is out
			lychee.sortingPhotos = data.config.sorting_Photos || data.config.sortingPhotos || "";
			lychee.sortingAlbums = data.config.sorting_Albums || data.config.sortingAlbums || "";
			lychee.album_subtitle_type = data.config.album_subtitle_type || "oldstyle";
			lychee.checkForUpdates = data.config.check_for_updates || data.config.checkForUpdates || "1";
			lychee.layout = data.config.layout || "1";
			lychee.public_search = data.config.public_search && data.config.public_search === "1" || false;
			lychee.image_overlay_type = !data.config.image_overlay_type ? "exif" : data.config.image_overlay_type;
			lychee.image_overlay_type_default = lychee.image_overlay_type;
			lychee.map_display = data.config.map_display && data.config.map_display === "1" || false;
			lychee.map_display_public = data.config.map_display_public && data.config.map_display_public === "1" || false;
			lychee.map_display_direction = data.config.map_display_direction && data.config.map_display_direction === "1" || false;
			lychee.map_provider = !data.config.map_provider ? "Wikimedia" : data.config.map_provider;
			lychee.map_include_subalbums = data.config.map_include_subalbums && data.config.map_include_subalbums === "1" || false;
			lychee.location_show = data.config.location_show && data.config.location_show === "1" || false;
			lychee.location_show_public = data.config.location_show_public && data.config.location_show_public === "1" || false;
			lychee.swipe_tolerance_x = validatedSwipeToleranceX;
			lychee.swipe_tolerance_y = validatedSwipeToleranceY;

			lychee.nsfw_visible = data.config.nsfw_visible && data.config.nsfw_visible === "1" || false;
			lychee.nsfw_blur = data.config.nsfw_blur && data.config.nsfw_blur === "1" || false;
			lychee.nsfw_warning = data.config.nsfw_warning && data.config.nsfw_warning === "1" || false;

			lychee.header_auto_hide = data.config_device.header_auto_hide;
			lychee.active_focus_on_page_load = data.config_device.active_focus_on_page_load;
			lychee.enable_button_visibility = data.config_device.enable_button_visibility;
			lychee.enable_button_share = data.config_device.enable_button_share;
			lychee.enable_button_archive = data.config_device.enable_button_archive;
			lychee.enable_button_move = data.config_device.enable_button_move;
			lychee.enable_button_trash = data.config_device.enable_button_trash;
			lychee.enable_button_fullscreen = data.config_device.enable_button_fullscreen;
			lychee.enable_button_download = data.config_device.enable_button_download;
			lychee.enable_button_add = data.config_device.enable_button_add;
			lychee.enable_button_more = data.config_device.enable_button_more;
			lychee.enable_button_rotate = data.config_device.enable_button_rotate;
			lychee.enable_close_tab_on_esc = data.config_device.enable_close_tab_on_esc;
			lychee.enable_tabindex = data.config_device.enable_tabindex;
			lychee.enable_contextmenu_header = data.config_device.enable_contextmenu_header;
			lychee.hide_content_during_imgview = data.config_device.hide_content_during_imgview;
			lychee.device_type = data.config_device.device_type || "desktop"; // we set default as Desktop
			lychee.nsfw_visible_saved = lychee.nsfw_visible;

			// console.log(lychee.full_photo);
			lychee.setMode("public");
		} else {
			// should not happen.
		}

		if (exitview) {
			$(window).bind("popstate", lychee.load);
			lychee.load();
		}
	});
};

lychee.login = function (data) {
	var username = data.username;
	var password = data.password;

	if (!username.trim()) {
		basicModal.error("username");
		return;
	}
	if (!password.trim()) {
		basicModal.error("password");
		return;
	}

	var params = {
		username: username,
		password: password
	};

	api.post("Session::login", params, function (_data) {
		if (_data === true) {
			window.location.reload();
		} else {
			// Show error and reactive button
			basicModal.error("password");
		}
	});
};

lychee.loginDialog = function () {
	// Make background make unfocusable
	tabindex.makeUnfocusable(header.dom());
	tabindex.makeUnfocusable(lychee.content);
	tabindex.makeUnfocusable(lychee.imageview);

	var msg = lychee.html(_templateObject50, build.iconic("key"), lychee.locale["USERNAME"], tabindex.get_next_tab_index(), lychee.locale["PASSWORD"], tabindex.get_next_tab_index(), lychee.version, lychee.updateURL, lychee.locale["UPDATE_AVAILABLE"]);

	basicModal.show({
		body: msg,
		buttons: {
			action: {
				title: lychee.locale["SIGN_IN"],
				fn: lychee.login,
				attributes: [["data-tabindex", tabindex.get_next_tab_index()]]
			},
			cancel: {
				title: lychee.locale["CANCEL"],
				fn: basicModal.close,
				attributes: [["data-tabindex", tabindex.get_next_tab_index()]]
			}
		}
	});
	$("#signInKeyLess").on("click", u2f.login);

	if (lychee.checkForUpdates === "1") lychee.getUpdate();

	tabindex.makeFocusable(basicModal.dom());
};

lychee.logout = function () {
	api.post("Session::logout", {}, function () {
		window.location.reload();
	});
};

lychee.goto = function () {
	var url = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : "";
	var autoplay = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : true;

	url = "#" + url;

	history.pushState(null, null, url);
	lychee.load(autoplay);
};

lychee.gotoMap = function () {
	var albumID = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : "";
	var autoplay = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : true;

	// If map functionality is disabled -> go to album
	if (!lychee.map_display) {
		loadingBar.show("error", lychee.locale["ERROR_MAP_DEACTIVATED"]);
		return;
	}
	lychee.goto("map/" + albumID, autoplay);
};

lychee.load = function () {
	var autoplay = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : true;

	var albumID = "";
	var photoID = "";
	var hash = document.location.hash.replace("#", "").split("/");

	contextMenu.close();
	multiselect.close();
	tabindex.reset();

	if (hash[0] != null) albumID = hash[0];
	if (hash[1] != null) photoID = hash[1];

	if (albumID && photoID) {
		if (albumID == "map") {
			// If map functionality is disabled -> do nothing
			if (!lychee.map_display) {
				loadingBar.show("error", lychee.locale["ERROR_MAP_DEACTIVATED"]);
				return;
			}
			$(".no_content").remove();
			// show map
			// albumID has been stored in photoID due to URL format #map/albumID
			albumID = photoID;

			// Trash data
			_photo.json = null;

			// Show Album -> it's below the map
			if (visible.photo()) view.photo.hide();
			if (visible.sidebar()) _sidebar.toggle();
			if (album.json && albumID === album.json.id) {
				view.album.title();
			}
			mapview.open(albumID);
			lychee.footer_hide();
		} else if (albumID == "search") {
			// Search has been triggered
			var search_string = decodeURIComponent(photoID);

			if (search_string.trim() === "") {
				// do nothing on "only space" search strings
				return;
			}
			// If public search is diabled -> do nothing
			if (lychee.publicMode === true && !lychee.public_search) {
				loadingBar.show("error", lychee.locale["ERROR_SEARCH_DEACTIVATED"]);
				return;
			}

			header.dom(".header__search").val(search_string);
			search.find(search_string);

			lychee.footer_show();
		} else {
			$(".no_content").remove();
			// Show photo

			// Trash data
			_photo.json = null;

			// Show Photo
			if (lychee.content.html() === "" || album.json == null || header.dom(".header__search").length && header.dom(".header__search").val().length !== 0) {
				lychee.content.hide();
				album.load(albumID, true);
			}
			_photo.load(photoID, albumID, autoplay);

			// Make imageview focussable
			tabindex.makeFocusable(lychee.imageview);

			// Make thumbnails unfocusable and store which element had focus
			tabindex.makeUnfocusable(lychee.content, true);

			// hide contentview if requested
			if (lychee.hide_content_during_imgview) lychee.content.hide();

			lychee.footer_hide();
		}
	} else if (albumID) {
		if (albumID == "map") {
			$(".no_content").remove();
			// Show map of all albums
			// If map functionality is disabled -> do nothing
			if (!lychee.map_display) {
				loadingBar.show("error", lychee.locale["ERROR_MAP_DEACTIVATED"]);
				return;
			}

			// Trash data
			_photo.json = null;

			// Show Album -> it's below the map
			if (visible.photo()) view.photo.hide();
			if (visible.sidebar()) _sidebar.toggle();
			mapview.open();
			lychee.footer_hide();
		} else if (albumID == "search") {
			// search string is empty -> do nothing
		} else {
			$(".no_content").remove();
			// Trash data
			_photo.json = null;

			// Show Album
			if (visible.photo()) {
				view.photo.hide();
				tabindex.makeUnfocusable(lychee.imageview);
			}
			if (visible.mapview()) mapview.close();
			if (visible.sidebar() && album.isSmartID(albumID)) _sidebar.toggle();
			$("#sensitive_warning").hide();
			if (album.json && albumID === album.json.id) {
				view.album.title();
				lychee.content.show();
				tabindex.makeFocusable(lychee.content, true);
			} else {
				album.load(albumID);
			}
			lychee.footer_show();
		}
	} else {
		$(".no_content").remove();
		// Trash albums.json when filled with search results
		if (search.hash != null) {
			albums.json = null;
			search.hash = null;
		}

		// Trash data
		album.json = null;
		_photo.json = null;

		// Hide sidebar
		if (visible.sidebar()) _sidebar.toggle();

		// Show Albums
		if (visible.photo()) {
			view.photo.hide();
			tabindex.makeUnfocusable(lychee.imageview);
		}
		if (visible.mapview()) mapview.close();
		$("#sensitive_warning").hide();
		lychee.content.show();
		lychee.footer_show();
		albums.load();
	}
};

lychee.getUpdate = function () {
	// console.log(lychee.update_available);
	// console.log(lychee.update_json);

	if (lychee.update_json !== 0) {
		if (lychee.update_available) {
			$(".version span").show();
		}
	} else {
		var success = function success(data) {
			if (data.lychee.version > parseInt(lychee.versionCode)) $(".version span").show();
		};

		$.ajax({
			url: lychee.updatePath,
			success: success
		});
	}
};

lychee.setTitle = function (title, editable) {
	if (lychee.title === title) {
		document.title = lychee.title + " - " + lychee.locale["ALBUMS"];
	} else {
		document.title = lychee.title + " - " + title;
	}

	header.setEditable(editable);
	header.setTitle(title);
};

lychee.setMode = function (mode) {
	if (lychee.lock) {
		$("#button_settings_open").remove();
	}
	if (!lychee.upload) {
		$("#button_sharing").remove();

		$(document).off("click", ".header__title--editable").off("touchend", ".header__title--editable").off("contextmenu", ".photo").off("contextmenu", ".album").off("drop");

		Mousetrap.unbind(["u"]).unbind(["s"]).unbind(["n"]).unbind(["r"]).unbind(["d"]).unbind(["t"]).unbind(["command+backspace", "ctrl+backspace"]).unbind(["command+a", "ctrl+a"]);
	}
	if (!lychee.admin) {
		$("#button_users, #button_logs, #button_diagnostics").remove();
	}

	if (mode === "logged_in") {
		// we are logged in, we do not need that short cut anymore. :)
		Mousetrap.unbind(["l"]).unbind(["k"]);

		// The code searches by class, so remove the other instance.
		$(".header__search, .header__clear", ".header__toolbar--public").remove();

		if (!lychee.editor_enabled) {
			$("#button_rotate_cwise").remove();
			$("#button_rotate_ccwise").remove();
		}
		return;
	} else {
		$(".header__search, .header__clear", ".header__toolbar--albums").remove();
		$("#button_rotate_cwise").remove();
		$("#button_rotate_ccwise").remove();
	}

	$("#button_settings, .header__divider, .leftMenu").remove();

	if (mode === "public") {
		lychee.publicMode = true;
	} else if (mode === "view") {
		Mousetrap.unbind(["esc", "command+up"]);

		$("#button_back, a#next, a#previous").remove();
		$(".no_content").remove();

		lychee.publicMode = true;
		lychee.viewMode = true;
	}

	// just mak
	header.bind_back();
};

lychee.animate = function (obj, animation) {
	var animations = [["fadeIn", "fadeOut"], ["contentZoomIn", "contentZoomOut"]];

	if (!obj.jQuery) obj = $(obj);

	for (var i = 0; i < animations.length; i++) {
		for (var x = 0; x < animations[i].length; x++) {
			if (animations[i][x] == animation) {
				obj.removeClass(animations[i][0] + " " + animations[i][1]).addClass(animation);
				return true;
			}
		}
	}

	return false;
};

lychee.retinize = function () {
	var path = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : "";

	var extention = path.split(".").pop();
	var isPhoto = extention !== "svg";

	if (isPhoto === true) {
		path = path.replace(/\.[^/.]+$/, "");
		path = path + "@2x" + "." + extention;
	}

	return {
		path: path,
		isPhoto: isPhoto
	};
};

lychee.loadDropbox = function (callback) {
	if (lychee.dropbox === false && lychee.dropboxKey != null && lychee.dropboxKey !== "") {
		loadingBar.show();

		var g = document.createElement("script");
		var s = document.getElementsByTagName("script")[0];

		g.src = "https://www.dropbox.com/static/api/1/dropins.js";
		g.id = "dropboxjs";
		g.type = "text/javascript";
		g.async = "true";
		g.setAttribute("data-app-key", lychee.dropboxKey);
		g.onload = g.onreadystatechange = function () {
			var rs = this.readyState;
			if (rs && rs !== "complete" && rs !== "loaded") return;
			lychee.dropbox = true;
			loadingBar.hide();
			callback();
		};
		s.parentNode.insertBefore(g, s);
	} else if (lychee.dropbox === true && lychee.dropboxKey != null && lychee.dropboxKey !== "") {
		callback();
	} else {
		settings.setDropboxKey(callback);
	}
};

lychee.getEventName = function () {
	if (lychee.device_type === "mobile") {
		return "touchend";
	}
	return "click";
};

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

lychee.error = function (errorThrown) {
	var params = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : "";
	var data = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : "";

	loadingBar.show("error", errorThrown);

	if (errorThrown === "Session timed out.") {
		setTimeout(function () {
			lychee.goto();
			window.location.reload();
		}, 3000);
	} else {
		console.error({
			description: errorThrown,
			params: params,
			response: data
		});
	}
};

lychee.fullscreenEnter = function () {
	var elem = document.documentElement;
	if (elem.requestFullscreen) {
		elem.requestFullscreen();
	} else if (elem.mozRequestFullScreen) {
		/* Firefox */
		elem.mozRequestFullScreen();
	} else if (elem.webkitRequestFullscreen) {
		/* Chrome, Safari and Opera */
		elem.webkitRequestFullscreen();
	} else if (elem.msRequestFullscreen) {
		/* IE/Edge */
		elem.msRequestFullscreen();
	}
};

lychee.fullscreenExit = function () {
	if (document.exitFullscreen) {
		document.exitFullscreen();
	} else if (document.mozCancelFullScreen) {
		/* Firefox */
		document.mozCancelFullScreen();
	} else if (document.webkitExitFullscreen) {
		/* Chrome, Safari and Opera */
		document.webkitExitFullscreen();
	} else if (document.msExitFullscreen) {
		/* IE/Edge */
		document.msExitFullscreen();
	}
};

lychee.fullscreenToggle = function () {
	if (lychee.fullscreenStatus()) {
		lychee.fullscreenExit();
	} else {
		lychee.fullscreenEnter();
	}
};

lychee.fullscreenStatus = function () {
	var elem = document.fullscreenElement || document.mozFullScreenElement || document.webkitFullscreenElement || document.msFullscreenElement;
	return elem ? true : false;
};

lychee.fullscreenAvailable = function () {
	return document.fullscreenEnabled || document.mozFullscreenEnabled || document.webkitFullscreenEnabled || document.msFullscreenEnabled;
};

lychee.fullscreenUpdate = function () {
	if (lychee.fullscreenStatus()) {
		$("#button_fs_album_enter,#button_fs_enter").hide();
		$("#button_fs_album_exit,#button_fs_exit").show();
	} else {
		$("#button_fs_album_enter,#button_fs_enter").show();
		$("#button_fs_album_exit,#button_fs_exit").hide();
	}
};

lychee.footer_show = function () {
	setTimeout(function () {
		lychee.footer.removeClass("hide_footer");
	}, 200);
};

lychee.footer_hide = function () {
	lychee.footer.addClass("hide_footer");
};

// Because the height of the footer can vary, we need to set some
// dimensions dynamically, at startup.
lychee.adjustContentHeight = function () {
	if (lychee.footer.length > 0) {
		lychee.content.css("min-height", "calc(100vh - " + lychee.content.css("padding-top") + " - " + lychee.content.css("padding-bottom") + " - " + lychee.footer.outerHeight() + "px)");
		$("#container").css("padding-bottom", lychee.footer.outerHeight());
	} else {
		lychee.content.css("min-height", "calc(100vh - " + lychee.content.css("padding-top") + " - " + lychee.content.css("padding-bottom") + ")");
	}
};

lychee.getBaseUrl = function () {
	if (location.href.includes("index.html")) {
		return location.href.replace("index.html" + location.hash, "");
	} else if (location.href.includes("gallery#")) {
		return location.href.replace("gallery" + location.hash, "");
	} else {
		return location.href.replace(location.hash, "");
	}
};

// Copied from https://github.com/feross/clipboard-copy/blob/9eba597c774feed48301fef689099599d612387c/index.js
lychee.clipboardCopy = function (text) {
	// Use the Async Clipboard API when available. Requires a secure browsing
	// context (i.e. HTTPS)
	if (navigator.clipboard) {
		return navigator.clipboard.writeText(text).catch(function (err) {
			throw err !== undefined ? err : new DOMException("The request is not allowed", "NotAllowedError");
		});
	}

	// ...Otherwise, use document.execCommand() fallback

	// Put the text to copy into a <span>
	var span = document.createElement("span");
	span.textContent = text;

	// Preserve consecutive spaces and newlines
	span.style.whiteSpace = "pre";

	// Add the <span> to the page
	document.body.appendChild(span);

	// Make a selection object representing the range of text selected by the user
	var selection = window.getSelection();
	var range = window.document.createRange();
	selection.removeAllRanges();
	range.selectNode(span);
	selection.addRange(range);

	// Copy text to the clipboard
	var success = false;

	try {
		success = window.document.execCommand("copy");
	} catch (err) {
		console.log("error", err);
	}

	// Cleanup
	selection.removeAllRanges();
	window.document.body.removeChild(span);

	return success;
	// ? Promise.resolve()
	// : Promise.reject(new DOMException('The request is not allowed', 'NotAllowedError'))
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

/**
 * @description Select multiple albums or photos.
 */

var isSelectKeyPressed = function isSelectKeyPressed(e) {
	return e.metaKey || e.ctrlKey;
};

var multiselect = {
	ids: [],
	albumsSelected: 0,
	photosSelected: 0,
	lastClicked: null
};

multiselect.position = {
	top: null,
	right: null,
	bottom: null,
	left: null
};

multiselect.bind = function () {
	$(".content").on("mousedown", function (e) {
		if (e.which === 1) multiselect.show(e);
	});

	return true;
};

multiselect.unbind = function () {
	$(".content").off("mousedown");
};

multiselect.isSelected = function (id) {
	var pos = $.inArray(id, multiselect.ids);

	return {
		selected: pos !== -1,
		position: pos
	};
};

multiselect.toggleItem = function (object, id) {
	if (album.isSmartID(id)) return;

	var selected = multiselect.isSelected(id).selected;

	if (selected === false) multiselect.addItem(object, id);else multiselect.removeItem(object, id);
};

multiselect.addItem = function (object, id) {
	if (album.isSmartID(id)) return;
	if (!lychee.admin && albums.isShared(id)) return;
	if (multiselect.isSelected(id).selected === true) return;

	var isAlbum = object.hasClass("album");

	if (isAlbum && multiselect.photosSelected > 0 || !isAlbum && multiselect.albumsSelected > 0) {
		lychee.error("Please select either albums or photos!");
		return;
	}

	multiselect.ids.push(id);
	multiselect.select(object);

	if (isAlbum) {
		multiselect.albumsSelected++;
	} else {
		multiselect.photosSelected++;
	}

	multiselect.lastClicked = object;
};

multiselect.removeItem = function (object, id) {
	var _multiselect$isSelect = multiselect.isSelected(id),
	    selected = _multiselect$isSelect.selected,
	    position = _multiselect$isSelect.position;

	if (selected === false) return;

	multiselect.ids.splice(position, 1);
	multiselect.deselect(object);

	var isAlbum = object.hasClass("album");

	if (isAlbum) {
		multiselect.albumsSelected--;
	} else {
		multiselect.photosSelected--;
	}

	multiselect.lastClicked = object;
};

multiselect.albumClick = function (e, albumObj) {
	var id = albumObj.attr("data-id");

	if ((isSelectKeyPressed(e) || e.shiftKey) && album.isUploadable()) {
		if (albumObj.hasClass("disabled")) return;

		if (isSelectKeyPressed(e)) {
			multiselect.toggleItem(albumObj, id);
		} else {
			if (multiselect.albumsSelected > 0) {
				// Click with Shift. Select all elements between the current
				// element and the last clicked-on one.

				if (albumObj.prevAll(".album").toArray().includes(multiselect.lastClicked[0])) {
					albumObj.prevUntil(multiselect.lastClicked, ".album").each(function () {
						multiselect.addItem($(this), $(this).attr("data-id"));
					});
				} else if (albumObj.nextAll(".album").toArray().includes(multiselect.lastClicked[0])) {
					albumObj.nextUntil(multiselect.lastClicked, ".album").each(function () {
						multiselect.addItem($(this), $(this).attr("data-id"));
					});
				}
			}

			multiselect.addItem(albumObj, id);
		}
	} else {
		lychee.goto(id);
	}
};

multiselect.photoClick = function (e, photoObj) {
	var id = photoObj.attr("data-id");

	if ((isSelectKeyPressed(e) || e.shiftKey) && album.isUploadable()) {
		if (photoObj.hasClass("disabled")) return;

		if (isSelectKeyPressed(e)) {
			multiselect.toggleItem(photoObj, id);
		} else {
			if (multiselect.photosSelected > 0) {
				// Click with Shift. Select all elements between the current
				// element and the last clicked-on one.

				if (photoObj.prevAll(".photo").toArray().includes(multiselect.lastClicked[0])) {
					photoObj.prevUntil(multiselect.lastClicked, ".photo").each(function () {
						multiselect.addItem($(this), $(this).attr("data-id"));
					});
				} else if (photoObj.nextAll(".photo").toArray().includes(multiselect.lastClicked[0])) {
					photoObj.nextUntil(multiselect.lastClicked, ".photo").each(function () {
						multiselect.addItem($(this), $(this).attr("data-id"));
					});
				}
			}

			multiselect.addItem(photoObj, id);
		}
	} else {
		lychee.goto(album.getID() + "/" + id);
	}
};

multiselect.albumContextMenu = function (e, albumObj) {
	var id = albumObj.attr("data-id");
	var selected = multiselect.isSelected(id).selected;

	if (albumObj.hasClass("disabled")) return;

	if (selected !== false && multiselect.ids.length > 1) {
		contextMenu.albumMulti(multiselect.ids, e);
	} else {
		contextMenu.album(id, e);
	}
};

multiselect.photoContextMenu = function (e, photoObj) {
	var id = photoObj.attr("data-id");
	var selected = multiselect.isSelected(id).selected;

	if (photoObj.hasClass("disabled")) return;

	if (selected !== false && multiselect.ids.length > 1) {
		contextMenu.photoMulti(multiselect.ids, e);
	} else if (visible.album() || visible.search()) {
		contextMenu.photo(id, e);
	} else if (visible.photo()) {
		// should not happen... but you never know...
		contextMenu.photo(_photo.getID(), e);
	} else {
		lychee.error("Could not find what you want.");
	}
};

multiselect.clearSelection = function () {
	multiselect.deselect(".photo.active, .album.active");
	multiselect.ids = [];
	multiselect.albumsSelected = 0;
	multiselect.photosSelected = 0;
	multiselect.lastClicked = null;
};

multiselect.show = function (e) {
	if (!album.isUploadable()) return false;
	if (!visible.albums() && !visible.album()) return false;
	if ($(".album:hover, .photo:hover").length !== 0) return false;
	if (visible.search()) return false;
	if (visible.multiselect()) $("#multiselect").remove();

	_sidebar.setSelectable(false);

	if (!isSelectKeyPressed(e) && !e.shiftKey) {
		multiselect.clearSelection();
	}

	multiselect.position.top = e.pageY;
	multiselect.position.right = $(document).width() - e.pageX;
	multiselect.position.bottom = $(document).height() - e.pageY;
	multiselect.position.left = e.pageX;

	$("body").append(build.multiselect(multiselect.position.top, multiselect.position.left));

	$(document).on("mousemove", multiselect.resize).on("mouseup", function (_e) {
		if (_e.which === 1) {
			multiselect.getSelection(_e);
		}
	});
};

multiselect.resize = function (e) {
	if (multiselect.position.top === null || multiselect.position.right === null || multiselect.position.bottom === null || multiselect.position.left === null) return false;

	// Default CSS
	var newCSS = {
		top: null,
		bottom: null,
		height: null,
		left: null,
		right: null,
		width: null
	};

	if (e.pageY >= multiselect.position.top) {
		newCSS.top = multiselect.position.top;
		newCSS.bottom = "inherit";
		newCSS.height = Math.min(e.pageY, $(document).height() - 3) - multiselect.position.top;
	} else {
		newCSS.top = "inherit";
		newCSS.bottom = multiselect.position.bottom;
		newCSS.height = multiselect.position.top - Math.max(e.pageY, 2);
	}

	if (e.pageX >= multiselect.position.left) {
		newCSS.right = "inherit";
		newCSS.left = multiselect.position.left;
		newCSS.width = Math.min(e.pageX, $(document).width() - 3) - multiselect.position.left;
	} else {
		newCSS.right = multiselect.position.right;
		newCSS.left = "inherit";
		newCSS.width = multiselect.position.left - Math.max(e.pageX, 2);
	}

	// Updated all CSS properties at once
	$("#multiselect").css(newCSS);
};

multiselect.stopResize = function () {
	if (multiselect.position.top !== null) $(document).off("mousemove mouseup");
};

multiselect.getSize = function () {
	if (!visible.multiselect()) return false;

	var $elem = $("#multiselect");
	var offset = $elem.offset();

	return {
		top: offset.top,
		left: offset.left,
		width: parseFloat($elem.css("width"), 10),
		height: parseFloat($elem.css("height"), 10)
	};
};

multiselect.getSelection = function (e) {
	var size = multiselect.getSize();

	if (visible.contextMenu()) return false;
	if (!visible.multiselect()) return false;

	$(".photo, .album").each(function () {
		// We select if there's even a slightest overlap.  Overlap between
		// an object and the selection occurs if the left edge of the
		// object is to the left of the right edge of the selection *and*
		// the right edge of the object is to the right of the left edge of
		// the selection; analogous for top/bottom.
		if ($(this).offset().left < size.left + size.width && $(this).offset().left + $(this).width() > size.left && $(this).offset().top < size.top + size.height && $(this).offset().top + $(this).height() > size.top) {
			var id = $(this).attr("data-id");

			if (isSelectKeyPressed(e)) {
				multiselect.toggleItem($(this), id);
			} else {
				multiselect.addItem($(this), id);
			}
		}
	});

	multiselect.hide();
};

multiselect.select = function (id) {
	var el = $(id);

	el.addClass("selected");
	el.addClass("active");
};

multiselect.deselect = function (id) {
	var el = $(id);

	el.removeClass("selected");
	el.removeClass("active");
};

multiselect.hide = function () {
	_sidebar.setSelectable(true);

	multiselect.stopResize();

	multiselect.position.top = null;
	multiselect.position.right = null;
	multiselect.position.bottom = null;
	multiselect.position.left = null;

	lychee.animate("#multiselect", "fadeOut");
	setTimeout(function () {
		return $("#multiselect").remove();
	}, 300);
};

multiselect.close = function () {
	_sidebar.setSelectable(true);

	multiselect.stopResize();

	multiselect.position.top = null;
	multiselect.position.right = null;
	multiselect.position.bottom = null;
	multiselect.position.left = null;

	lychee.animate("#multiselect", "fadeOut");
	setTimeout(function () {
		return $("#multiselect").remove();
	}, 300);
};

multiselect.selectAll = function () {
	if (!album.isUploadable()) return false;
	if (visible.search()) return false;
	if (!visible.albums() && !visible.album) return false;
	if (visible.multiselect()) $("#multiselect").remove();

	_sidebar.setSelectable(false);

	multiselect.clearSelection();

	$(".photo").each(function () {
		multiselect.addItem($(this), $(this).attr("data-id"));
	});

	if (multiselect.photosSelected === 0) {
		// There are no pictures.  Try albums then.
		$(".album").each(function () {
			multiselect.addItem($(this), $(this).attr("data-id"));
		});
	}
};

/**
 * @description Controls the access to password-protected albums and photos.
 */

var password = {
	value: ""
};

password.getDialog = function (albumID, callback) {
	var action = function action(data) {
		var passwd = data.password;

		var params = {
			albumID: albumID,
			password: passwd
		};

		api.post("Album::getPublic", params, function (_data) {
			if (_data === true) {
				basicModal.close();
				password.value = passwd;
				callback();
			} else {
				basicModal.error("password");
			}
		});
	};

	var cancel = function cancel() {
		basicModal.close();
		if (!visible.albums() && !visible.album()) lychee.goto();
	};

	var msg = "\n\t\t\t  <p>\n\t\t\t\t  " + lychee.locale["ALBUM_PASSWORD_REQUIRED"] + "\n\t\t\t\t  <input name='password' class='text' type='password' placeholder='" + lychee.locale["PASSWORD"] + "' value=''>\n\t\t\t  </p>\n\t\t\t  ";

	basicModal.show({
		body: msg,
		buttons: {
			action: {
				title: lychee.locale["ENTER"],
				fn: action
			},
			cancel: {
				title: lychee.locale["CANCEL"],
				fn: cancel
			}
		}
	});
};

/**
 * @description Takes care of every action a photo can handle and execute.
 */

var _photo = {
	json: null,
	cache: null,
	supportsPrefetch: null,
	LivePhotosObject: null
};

_photo.getID = function () {
	var id = null;

	if (_photo.json) id = _photo.json.id;else id = $(".photo:hover, .photo.active").attr("data-id");

	if ($.isNumeric(id) === true) return id;else return false;
};

_photo.load = function (photoID, albumID, autoplay) {
	var checkContent = function checkContent() {
		if (album.json != null && album.json.photos) _photo.load(photoID, albumID, autoplay);else setTimeout(checkContent, 100);
	};

	var checkPasswd = function checkPasswd() {
		if (password.value !== "") _photo.load(photoID, albumID, autoplay);else setTimeout(checkPasswd, 200);
	};

	// we need to check the album.json.photos because otherwise the script is too fast and this raise an error.
	if (album.json == null || album.json.photos == null) {
		checkContent();
		return false;
	}

	var params = {
		photoID: photoID,
		password: password.value
	};

	api.post("Photo::get", params, function (data) {
		if (data === "Warning: Photo private!") {
			lychee.content.show();
			lychee.goto();
			return false;
		}

		if (data === "Warning: Wrong password!") {
			checkPasswd();
			return false;
		}

		_photo.json = data;
		_photo.json.original_album = _photo.json.album;
		_photo.json.album = albumID;

		if (!visible.photo()) view.photo.show();
		view.photo.init(autoplay);
		lychee.imageview.show();

		if (!lychee.hide_content_during_imgview) {
			setTimeout(function () {
				lychee.content.show();
				tabindex.makeUnfocusable(lychee.content);
			}, 300);
		}
	});
};

_photo.hasExif = function () {
	var exifHash = _photo.json.make + _photo.json.model + _photo.json.shutter + _photo.json.aperture + _photo.json.focal + _photo.json.iso;

	return exifHash !== "";
};

_photo.hasTakestamp = function () {
	return _photo.json.taken_at !== null;
};

_photo.hasDesc = function () {
	return _photo.json.description && _photo.json.description !== "";
};

_photo.isLivePhoto = function () {
	if (!_photo.json) return false; // In case it's called, but not initialized
	return _photo.json.livePhotoUrl && _photo.json.livePhotoUrl !== "";
};

_photo.isLivePhotoInitizalized = function () {
	return _photo.LivePhotosObject !== null;
};

_photo.isLivePhotoPlaying = function () {
	if (_photo.isLivePhotoInitizalized() === false) return false;
	return _photo.LivePhotosObject.isPlaying;
};

_photo.cycle_display_overlay = function () {
	var oldtype = build.check_overlay_type(_photo.json, lychee.image_overlay_type);
	var newtype = build.check_overlay_type(_photo.json, oldtype, true);
	if (oldtype !== newtype) {
		lychee.image_overlay_type = newtype;
		$("#image_overlay").remove();
		var newoverlay = build.overlay_image(_photo.json);
		if (newoverlay !== "") lychee.imageview.append(newoverlay);
	}
};

// Preload the next and previous photos for better response time
_photo.preloadNextPrev = function (photoID) {
	if (album.json && album.json.photos && album.getByID(photoID)) {
		var previousPhotoID = album.getByID(photoID).previousPhoto;
		var nextPhotoID = album.getByID(photoID).nextPhoto;
		var imgs = $("img#image");
		var isUsing2xCurrently = imgs.length > 0 && imgs[0].currentSrc !== null && imgs[0].currentSrc.includes("@2x.");

		$("head [data-prefetch]").remove();

		var preload = function preload(preloadID) {
			var preloadPhoto = album.getByID(preloadID);
			var href = "";

			if (preloadPhoto.sizeVariants.medium != null) {
				href = preloadPhoto.sizeVariants.medium.url;
				if (preloadPhoto.sizeVariants.medium2x != null && isUsing2xCurrently) {
					// If the currently displayed image uses the 2x variant,
					// chances are that so will the next one.
					href = preloadPhoto.sizeVariants.medium2x.url;
				}
			} else if (preloadPhoto.type && preloadPhoto.type.indexOf("video") === -1) {
				// Preload the original size, but only if it's not a video
				href = preloadPhoto.url;
			}

			if (href !== "") {
				if (_photo.supportsPrefetch === null) {
					// Copied from https://www.smashingmagazine.com/2016/02/preload-what-is-it-good-for/
					var DOMTokenListSupports = function DOMTokenListSupports(tokenList, token) {
						if (!tokenList || !tokenList.supports) {
							return null;
						}
						try {
							return tokenList.supports(token);
						} catch (e) {
							if (e instanceof TypeError) {
								console.log("The DOMTokenList doesn't have a supported tokens list");
							} else {
								console.error("That shouldn't have happened");
							}
						}
					};
					_photo.supportsPrefetch = DOMTokenListSupports(document.createElement("link").relList, "prefetch");
				}

				if (_photo.supportsPrefetch) {
					$("head").append(lychee.html(_templateObject51, href));
				} else {
					// According to https://caniuse.com/#feat=link-rel-prefetch,
					// as of mid-2019 it's mainly Safari (both on desktop and mobile)
					new Image().src = href;
				}
			}
		};

		if (nextPhotoID && nextPhotoID !== "") {
			preload(nextPhotoID);
		}
		if (previousPhotoID && previousPhotoID !== "") {
			preload(previousPhotoID);
		}
	}
};

_photo.parse = function () {
	if (!_photo.json.title) _photo.json.title = lychee.locale["UNTITLED"];
};

_photo.updateSizeLivePhotoDuringAnimation = function () {
	var animationDuraction = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : 300;
	var pauseBetweenUpdated = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : 10;

	// For the LivePhotoKit, we need to call the updateSize manually
	// during CSS animations
	//
	var interval = setInterval(function () {
		if (_photo.isLivePhotoInitizalized()) {
			_photo.LivePhotosObject.updateSize();
		}
	}, pauseBetweenUpdated);

	setTimeout(function () {
		clearInterval(interval);
	}, animationDuraction);
};

_photo.previous = function (animate) {
	if (_photo.getID() !== false && album.json && album.getByID(_photo.getID()) && album.getByID(_photo.getID()).previousPhoto !== "") {
		var delay = 0;

		if (animate === true) {
			delay = 200;

			$("#imageview #image").css({
				WebkitTransform: "translateX(100%)",
				MozTransform: "translateX(100%)",
				transform: "translateX(100%)",
				opacity: 0
			});
		}

		setTimeout(function () {
			if (_photo.getID() === false) return false;
			_photo.LivePhotosObject = null;
			lychee.goto(album.getID() + "/" + album.getByID(_photo.getID()).previousPhoto, false);
		}, delay);
	}
};

_photo.next = function (animate) {
	if (_photo.getID() !== false && album.json && album.getByID(_photo.getID()) && album.getByID(_photo.getID()).nextPhoto !== "") {
		var delay = 0;

		if (animate === true) {
			delay = 200;

			$("#imageview #image").css({
				WebkitTransform: "translateX(-100%)",
				MozTransform: "translateX(-100%)",
				transform: "translateX(-100%)",
				opacity: 0
			});
		}

		setTimeout(function () {
			if (_photo.getID() === false) return false;
			_photo.LivePhotosObject = null;
			lychee.goto(album.getID() + "/" + album.getByID(_photo.getID()).nextPhoto, false);
		}, delay);
	}
};

_photo.delete = function (photoIDs) {
	var action = {};
	var cancel = {};
	var msg = "";
	var photoTitle = "";

	if (!photoIDs) return false;
	if (photoIDs instanceof Array === false) photoIDs = [photoIDs];

	if (photoIDs.length === 1) {
		// Get title if only one photo is selected
		if (visible.photo()) photoTitle = _photo.json.title;else photoTitle = album.getByID(photoIDs).title;

		// Fallback for photos without a title
		if (photoTitle === "") photoTitle = lychee.locale["UNTITLED"];
	}

	action.fn = function () {
		var nextPhoto = "";
		var previousPhoto = "";

		basicModal.close();

		photoIDs.forEach(function (id, index) {
			// Change reference for the next and previous photo
			if (album.getByID(id).nextPhoto !== "" || album.getByID(id).previousPhoto !== "") {
				nextPhoto = album.getByID(id).nextPhoto;
				previousPhoto = album.getByID(id).previousPhoto;

				if (previousPhoto !== "") {
					album.getByID(previousPhoto).nextPhoto = nextPhoto;
				}
				if (nextPhoto !== "") {
					album.getByID(nextPhoto).previousPhoto = previousPhoto;
				}
			}

			album.deleteByID(id);
			view.album.content.delete(id, index === photoIDs.length - 1);
		});

		albums.refresh();

		// Go to next photo if there is a next photo and
		// next photo is not the current one. Also try the previous one.
		// Show album otherwise.
		if (visible.photo()) {
			if (nextPhoto !== "" && nextPhoto !== _photo.getID()) {
				lychee.goto(album.getID() + "/" + nextPhoto);
			} else if (previousPhoto !== "" && previousPhoto !== _photo.getID()) {
				lychee.goto(album.getID() + "/" + previousPhoto);
			} else {
				lychee.goto(album.getID());
			}
		} else if (!visible.albums()) {
			lychee.goto(album.getID());
		}

		var params = {
			photoIDs: photoIDs.join()
		};

		api.post("Photo::delete", params, function (data) {
			if (data !== true) lychee.error(null, params, data);
		});
	};

	if (photoIDs.length === 1) {
		action.title = lychee.locale["PHOTO_DELETE"];
		cancel.title = lychee.locale["PHOTO_KEEP"];

		msg = lychee.html(_templateObject52, lychee.locale["PHOTO_DELETE_1"], photoTitle, lychee.locale["PHOTO_DELETE_2"]);
	} else {
		action.title = lychee.locale["PHOTO_DELETE"];
		cancel.title = lychee.locale["PHOTO_KEEP"];

		msg = lychee.html(_templateObject53, lychee.locale["PHOTO_DELETE_ALL_1"], photoIDs.length, lychee.locale["PHOTO_DELETE_ALL_2"]);
	}

	basicModal.show({
		body: msg,
		buttons: {
			action: {
				title: action.title,
				fn: action.fn,
				class: "red"
			},
			cancel: {
				title: cancel.title,
				fn: basicModal.close
			}
		}
	});
};

_photo.setTitle = function (photoIDs) {
	var oldTitle = "";
	var msg = "";

	if (!photoIDs) return false;
	if (photoIDs instanceof Array === false) photoIDs = [photoIDs];

	if (photoIDs.length === 1) {
		// Get old title if only one photo is selected
		if (_photo.json) oldTitle = _photo.json.title;else if (album.json) oldTitle = album.getByID(photoIDs).title;
	}

	var action = function action(data) {
		if (!data.title.trim()) {
			basicModal.error("title");
			return;
		}

		basicModal.close();

		var newTitle = data.title;

		if (visible.photo()) {
			_photo.json.title = newTitle === "" ? "Untitled" : newTitle;
			view.photo.title();
		}

		photoIDs.forEach(function (id) {
			album.getByID(id).title = newTitle;
			view.album.content.title(id);
		});

		var params = {
			photoIDs: photoIDs.join(),
			title: newTitle
		};

		api.post("Photo::setTitle", params, function (_data) {
			if (_data !== true) {
				lychee.error(null, params, _data);
			}
		});
	};

	var input = lychee.html(_templateObject54, oldTitle);

	if (photoIDs.length === 1) msg = lychee.html(_templateObject5, lychee.locale["PHOTO_NEW_TITLE"], input);else msg = lychee.html(_templateObject55, lychee.locale["PHOTOS_NEW_TITLE_1"], photoIDs.length, lychee.locale["PHOTOS_NEW_TITLE_2"], input);

	basicModal.show({
		body: msg,
		buttons: {
			action: {
				title: lychee.locale["PHOTO_SET_TITLE"],
				fn: action
			},
			cancel: {
				title: lychee.locale["CANCEL"],
				fn: basicModal.close
			}
		}
	});
};

_photo.copyTo = function (photoIDs, albumID) {
	if (!photoIDs) return false;
	if (photoIDs instanceof Array === false) photoIDs = [photoIDs];

	var params = {
		photoIDs: photoIDs.join(),
		albumID: albumID
	};

	api.post("Photo::duplicate", params, function (data) {
		if (data !== true) {
			lychee.error(null, params, data);
		} else {
			if (albumID === album.getID()) {
				album.reload();
			} else {
				// Lychee v3 does not support the albumID argument to
				// Photo::duplicate so we need to do it manually, which is
				// imperfect, as it moves the source photos, not the duplicates.
				_photo.setAlbum(photoIDs, albumID);
			}
		}
	});
};

_photo.setAlbum = function (photoIDs, albumID) {
	var nextPhoto = "";
	var previousPhoto = "";

	if (!photoIDs) return false;
	if (photoIDs instanceof Array === false) photoIDs = [photoIDs];

	photoIDs.forEach(function (id, index) {
		// Change reference for the next and previous photo
		if (album.getByID(id).nextPhoto !== "" || album.getByID(id).previousPhoto !== "") {
			nextPhoto = album.getByID(id).nextPhoto;
			previousPhoto = album.getByID(id).previousPhoto;

			if (previousPhoto !== "") {
				album.getByID(previousPhoto).nextPhoto = nextPhoto;
			}
			if (nextPhoto !== "") {
				album.getByID(nextPhoto).previousPhoto = previousPhoto;
			}
		}

		album.deleteByID(id);
		view.album.content.delete(id, index === photoIDs.length - 1);
	});

	albums.refresh();

	// Go to next photo if there is a next photo and
	// next photo is not the current one. Also try the previous one.
	// Show album otherwise.
	if (visible.photo()) {
		if (nextPhoto !== "" && nextPhoto !== _photo.getID()) {
			lychee.goto(album.getID() + "/" + nextPhoto);
		} else if (previousPhoto !== "" && previousPhoto !== _photo.getID()) {
			lychee.goto(album.getID() + "/" + previousPhoto);
		} else {
			lychee.goto(album.getID());
		}
	}

	var params = {
		photoIDs: photoIDs.join(),
		albumID: albumID
	};

	api.post("Photo::setAlbum", params, function (data) {
		if (data !== true) {
			lychee.error(null, params, data);
		} else {
			// We only really need to do anything here if the destination
			// is a (possibly nested) subalbum of the current album; but
			// since we have no way of figuring it out (albums.json is
			// null), we need to reload.
			if (visible.album()) {
				album.reload();
			}
		}
	});
};

_photo.setStar = function (photoIDs) {
	if (!photoIDs) return false;

	if (visible.photo()) {
		_photo.json.star = _photo.json.star === "0" ? "1" : "0";
		view.photo.star();
	}

	photoIDs.forEach(function (id) {
		album.getByID(id).star = album.getByID(id).star === "0" ? "1" : "0";
		view.album.content.star(id);
	});

	albums.refresh();

	var params = {
		photoIDs: photoIDs.join()
	};

	api.post("Photo::setStar", params, function (data) {
		if (data !== true) lychee.error(null, params, data);
	});
};

_photo.setPublic = function (photoID, e) {
	var msg_switch = lychee.html(_templateObject56, lychee.locale["PHOTO_PUBLIC"], lychee.locale["PHOTO_PUBLIC_EXPL"]);

	var msg_choices = lychee.html(_templateObject57, build.iconic("check"), lychee.locale["PHOTO_FULL"], lychee.locale["PHOTO_FULL_EXPL"], build.iconic("check"), lychee.locale["PHOTO_HIDDEN"], lychee.locale["PHOTO_HIDDEN_EXPL"], build.iconic("check"), lychee.locale["PHOTO_DOWNLOADABLE"], lychee.locale["PHOTO_DOWNLOADABLE_EXPL"], build.iconic("check"), lychee.locale["PHOTO_SHARE_BUTTON_VISIBLE"], lychee.locale["PHOTO_SHARE_BUTTON_VISIBLE_EXPL"], build.iconic("check"), lychee.locale["PHOTO_PASSWORD_PROT"], lychee.locale["PHOTO_PASSWORD_PROT_EXPL"]);

	if (_photo.json.public === "2") {
		// Public album. We can't actually change anything but we will
		// display the current settings.

		var msg = lychee.html(_templateObject58, lychee.locale["PHOTO_NO_EDIT_SHARING_TEXT"], msg_switch, msg_choices);

		basicModal.show({
			body: msg,
			buttons: {
				cancel: {
					title: lychee.locale["CLOSE"],
					fn: basicModal.close
				}
			}
		});

		$('.basicModal .switch input[name="public"]').prop("checked", true);
		if (album.json) {
			if (album.json.full_photo !== null && album.json.full_photo === "1") {
				$('.basicModal .choice input[name="full_photo"]').prop("checked", true);
			}
			// Photos in public albums are never hidden as such.  It's the
			// album that's hidden.  Or is that distinction irrelevant to end
			// users?
			if (album.json.downloadable === "1") {
				$('.basicModal .choice input[name="downloadable"]').prop("checked", true);
			}
			if (album.json.password === "1") {
				$('.basicModal .choice input[name="password"]').prop("checked", true);
			}
		}

		$(".basicModal .switch input").attr("disabled", true);
		$(".basicModal .switch .label").addClass("label--disabled");
	} else {
		// Private album -- each photo can be shared individually.

		var _msg = lychee.html(_templateObject59, msg_switch, lychee.locale["PHOTO_EDIT_GLOBAL_SHARING_TEXT"], msg_choices);

		var action = function action() {
			var newPublic = $('.basicModal .switch input[name="public"]:checked').length === 1 ? "1" : "0";

			if (newPublic !== _photo.json.public) {
				if (visible.photo()) {
					_photo.json.public = newPublic;
					view.photo.public();
				}

				album.getByID(photoID).public = newPublic;
				view.album.content.public(photoID);

				albums.refresh();

				// Photo::setPublic simply flips the current state.
				// Ugly API but effective...
				api.post("Photo::setPublic", { photoID: photoID }, function (data) {
					if (data !== true) lychee.error(null, params, data);
				});
			}

			basicModal.close();
		};

		basicModal.show({
			body: _msg,
			buttons: {
				action: {
					title: lychee.locale["PHOTO_SHARING_CONFIRM"],
					fn: action
				},
				cancel: {
					title: lychee.locale["CANCEL"],
					fn: basicModal.close
				}
			}
		});

		$('.basicModal .switch input[name="public"]').on("click", function () {
			if ($(this).prop("checked") === true) {
				if (lychee.full_photo) {
					$('.basicModal .choice input[name="full_photo"]').prop("checked", true);
				}
				if (lychee.public_photos_hidden) {
					$('.basicModal .choice input[name="hidden"]').prop("checked", true);
				}
				if (lychee.downloadable) {
					$('.basicModal .choice input[name="downloadable"]').prop("checked", true);
				}
				if (lychee.share_button_visible) {
					$('.basicModal .choice input[name="share_button_visible"]').prop("checked", true);
				}
				// Photos shared individually can't be password-protected.
			} else {
				$(".basicModal .choice input").prop("checked", false);
			}
		});

		if (_photo.json.public === "1") {
			$('.basicModal .switch input[name="public"]').click();
		}
	}

	return true;
};

_photo.setDescription = function (photoID) {
	var oldDescription = _photo.json.description;

	var action = function action(data) {
		basicModal.close();

		var description = data.description;

		if (visible.photo()) {
			_photo.json.description = description;
			view.photo.description();
		}

		var params = {
			photoID: photoID,
			description: description
		};

		api.post("Photo::setDescription", params, function (_data) {
			if (_data !== true) {
				lychee.error(null, params, _data);
			}
		});
	};

	basicModal.show({
		body: lychee.html(_templateObject60, lychee.locale["PHOTO_NEW_DESCRIPTION"], lychee.locale["PHOTO_DESCRIPTION"], oldDescription),
		buttons: {
			action: {
				title: lychee.locale["PHOTO_SET_DESCRIPTION"],
				fn: action
			},
			cancel: {
				title: lychee.locale["CANCEL"],
				fn: basicModal.close
			}
		}
	});
};

_photo.editTags = function (photoIDs) {
	var oldTags = "";
	var msg = "";

	if (!photoIDs) return false;
	if (photoIDs instanceof Array === false) photoIDs = [photoIDs];

	// Get tags
	if (visible.photo()) oldTags = _photo.json.tags;else if (visible.album() && photoIDs.length === 1) oldTags = album.getByID(photoIDs).tags;else if (visible.search() && photoIDs.length === 1) oldTags = album.getByID(photoIDs).tags;else if (visible.album() && photoIDs.length > 1) {
		var same = true;
		photoIDs.forEach(function (id) {
			same = album.getByID(id).tags === album.getByID(photoIDs[0]).tags && same === true;
		});
		if (same === true) oldTags = album.getByID(photoIDs[0]).tags;
	}

	// Improve tags
	oldTags = oldTags.replace(/,/g, ", ");

	var action = function action(data) {
		basicModal.close();
		_photo.setTags(photoIDs, data.tags);
	};

	var input = lychee.html(_templateObject61, oldTags);

	if (photoIDs.length === 1) msg = lychee.html(_templateObject5, lychee.locale["PHOTO_NEW_TAGS"], input);else msg = lychee.html(_templateObject55, lychee.locale["PHOTO_NEW_TAGS_1"], photoIDs.length, lychee.locale["PHOTO_NEW_TAGS_2"], input);

	basicModal.show({
		body: msg,
		buttons: {
			action: {
				title: lychee.locale["PHOTO_SET_TAGS"],
				fn: action
			},
			cancel: {
				title: lychee.locale["CANCEL"],
				fn: basicModal.close
			}
		}
	});
};

_photo.setTags = function (photoIDs, tags) {
	if (!photoIDs) return false;
	if (photoIDs instanceof Array === false) photoIDs = [photoIDs];

	// Parse tags
	tags = tags.replace(/(\ ,\ )|(\ ,)|(,\ )|(,{1,}\ {0,})|(,$|^,)/g, ",");
	tags = tags.replace(/,$|^,|(\ ){0,}$/g, "");

	if (visible.photo()) {
		_photo.json.tags = tags;
		view.photo.tags();
	}

	photoIDs.forEach(function (id, index, array) {
		album.getByID(id).tags = tags;
	});

	var params = {
		photoIDs: photoIDs.join(),
		tags: tags
	};

	api.post("Photo::setTags", params, function (data) {
		if (data !== true) {
			lychee.error(null, params, data);
		} else if (albums.json && albums.json.smartalbums) {
			$.each(Object.entries(albums.json.smartalbums), function () {
				if (this.length == 2 && this[1]["tag_album"] === "1") {
					// If we have any tag albums, force a refresh.
					albums.refresh();
					return false;
				}
			});
		}
	});
};

_photo.deleteTag = function (photoID, index) {
	var tags = void 0;

	// Remove
	tags = _photo.json.tags.split(",");
	tags.splice(index, 1);

	// Save
	_photo.json.tags = tags.toString();
	_photo.setTags([photoID], _photo.json.tags);
};

_photo.share = function (photoID, service) {
	if (_photo.json.hasOwnProperty("share_button_visible") && _photo.json.share_button_visible !== "1") {
		return;
	}

	var url = _photo.getViewLink(photoID);

	switch (service) {
		case "twitter":
			window.open("https://twitter.com/share?url=" + encodeURI(url));
			break;
		case "facebook":
			window.open("https://www.facebook.com/sharer.php?u=" + encodeURI(url) + "&t=" + encodeURI(_photo.json.title));
			break;
		case "mail":
			location.href = "mailto:?subject=" + encodeURI(_photo.json.title) + "&body=" + encodeURI(url);
			break;
		case "dropbox":
			lychee.loadDropbox(function () {
				var filename = _photo.json.title + "." + _photo.getDirectLink().split(".").pop();
				Dropbox.save(_photo.getDirectLink(), filename);
			});
			break;
	}
};

_photo.setLicense = function (photoID) {
	var callback = function callback() {
		$("select#license").val(_photo.json.license === "" ? "none" : _photo.json.license);
		return false;
	};

	var action = function action(data) {
		basicModal.close();
		var license = data.license;

		var params = {
			photoID: photoID,
			license: license
		};

		api.post("Photo::setLicense", params, function (_data) {
			if (_data !== true) {
				lychee.error(null, params, _data);
			} else {
				// update the photo JSON and reload the license in the sidebar
				_photo.json.license = params.license;
				view.photo.license();
			}
		});
	};

	var msg = lychee.html(_templateObject8, lychee.locale["PHOTO_LICENSE"], lychee.locale["PHOTO_LICENSE_NONE"], lychee.locale["PHOTO_RESERVED"], lychee.locale["PHOTO_LICENSE_HELP"]);

	basicModal.show({
		body: msg,
		callback: callback,
		buttons: {
			action: {
				title: lychee.locale["PHOTO_SET_LICENSE"],
				fn: action
			},
			cancel: {
				title: lychee.locale["CANCEL"],
				fn: basicModal.close
			}
		}
	});
};

_photo.getArchive = function (photoIDs) {
	var kind = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : null;

	if (photoIDs.length === 1 && kind === null) {
		// For a single photo, allow to pick the kind via a dialog box.

		var myPhoto = void 0;

		if (_photo.json && _photo.json.id === photoIDs[0]) {
			myPhoto = _photo.json;
		} else {
			myPhoto = album.getByID(photoIDs[0]);
		}

		var buildButton = function buildButton(id, label) {
			return lychee.html(_templateObject62, id, lychee.locale["DOWNLOAD"], build.iconic("cloud-download"), label);
		};

		var msg = lychee.html(_templateObject63);

		if (myPhoto.url) {
			msg += buildButton("FULL", lychee.locale["PHOTO_FULL"] + " (" + myPhoto.width + "x" + myPhoto.height + ", " + lychee.locale.printFilesizeLocalized(myPhoto.filesize) + ")");
		}
		if (myPhoto.livePhotoUrl !== null) {
			msg += buildButton("LIVEPHOTOVIDEO", "" + lychee.locale["PHOTO_LIVE_VIDEO"]);
		}
		if (myPhoto.sizeVariants.medium2x !== null) {
			msg += buildButton("MEDIUM2X", lychee.locale["PHOTO_MEDIUM_HIDPI"] + " (" + myPhoto.sizeVariants.medium2x.width + "x" + myPhoto.sizeVariants.medium2x.height + ")");
		}
		if (myPhoto.sizeVariants.medium !== null) {
			msg += buildButton("MEDIUM", lychee.locale["PHOTO_MEDIUM"] + " (" + myPhoto.sizeVariants.medium.width + "x" + myPhoto.sizeVariants.medium.height + ")");
		}
		if (myPhoto.sizeVariants.small2x !== null) {
			msg += buildButton("SMALL2X", lychee.locale["PHOTO_SMALL_HIDPI"] + " (" + myPhoto.sizeVariants.small2x.width + "x" + myPhoto.sizeVariants.small2x.height + ")");
		}
		if (myPhoto.sizeVariants.small !== null) {
			msg += buildButton("SMALL", lychee.locale["PHOTO_SMALL"] + " (" + myPhoto.sizeVariants.small.width + "x" + myPhoto.sizeVariants.small.height + ")");
		}
		if (myPhoto.sizeVariants.thumb2x !== null) {
			msg += buildButton("THUMB2X", lychee.locale["PHOTO_THUMB_HIDPI"] + " (" + myPhoto.sizeVariants.thumb2x.width + "x" + myPhoto.sizeVariants.thumb2x.height + ")");
		}
		if (myPhoto.sizeVariants.thumb !== null) {
			msg += buildButton("THUMB", lychee.locale["PHOTO_THUMB"] + " (" + myPhoto.sizeVariants.thumb.width + "x" + myPhoto.sizeVariants.thumb.height + ")");
		}

		msg += lychee.html(_templateObject64);

		basicModal.show({
			body: msg,
			buttons: {
				cancel: {
					title: lychee.locale["CLOSE"],
					fn: basicModal.close
				}
			}
		});

		$(".downloads .basicModal__button").on(lychee.getEventName(), function () {
			kind = this.id;
			basicModal.close();
			_photo.getArchive(photoIDs, kind);
		});

		return true;
	}

	location.href = "api/Photo::getArchive" + lychee.html(_templateObject65, photoIDs.join(), kind);
};

_photo.getDirectLink = function () {
	var url = "";

	if (_photo.json && _photo.json.url && _photo.json.url !== "") url = _photo.json.url;

	return url;
};

_photo.getViewLink = function (photoID) {
	var url = "view?p=" + photoID;

	return lychee.getBaseUrl() + url;
};

_photo.showDirectLinks = function (photoID) {
	if (!_photo.json || _photo.json.id != photoID) {
		return;
	}

	var buildLine = function buildLine(label, url) {
		return lychee.html(_templateObject66, label, url, lychee.locale["URL_COPY_TO_CLIPBOARD"], build.iconic("copy", "ionicons"));
	};

	var msg = lychee.html(_templateObject67, buildLine(lychee.locale["PHOTO_VIEW"], _photo.getViewLink(photoID)), lychee.locale["PHOTO_DIRECT_LINKS_TO_IMAGES"]);

	if (_photo.json.url) {
		msg += buildLine(lychee.locale["PHOTO_FULL"] + " (" + _photo.json.width + "x" + _photo.json.height + ")", lychee.getBaseUrl() + _photo.json.url);
	}
	if (_photo.json.sizeVariants.medium2x !== null) {
		msg += buildLine(lychee.locale["PHOTO_MEDIUM_HIDPI"] + " (" + _photo.json.sizeVariants.medium2x.width + "x" + _photo.json.sizeVariants.medium2x.height + ")", lychee.getBaseUrl() + _photo.json.sizeVariants.medium2x.url);
	}
	if (_photo.json.sizeVariants.medium !== null) {
		msg += buildLine(lychee.locale["PHOTO_MEDIUM"] + " (" + _photo.json.sizeVariants.medium.width + "x" + _photo.json.sizeVariants.medium.height + ")", lychee.getBaseUrl() + _photo.json.sizeVariants.medium.url);
	}
	if (_photo.json.sizeVariants.small2x !== null) {
		msg += buildLine(lychee.locale["PHOTO_SMALL_HIDPI"] + " (" + _photo.json.sizeVariants.small2x.width + "x" + _photo.json.sizeVariants.small2x.height + ")", lychee.getBaseUrl() + _photo.json.sizeVariants.small2x.url);
	}
	if (_photo.json.sizeVariants.small !== null) {
		msg += buildLine(lychee.locale["PHOTO_SMALL"] + " (" + _photo.json.sizeVariants.small.width + "x" + _photo.json.sizeVariants.small.height + ")", lychee.getBaseUrl() + _photo.json.sizeVariants.small.url);
	}
	if (_photo.json.sizeVariants.thumb2x !== null) {
		msg += buildLine(lychee.locale["PHOTO_THUMB_HIDPI"] + " (" + _photo.json.sizeVariants.thumb2x.width + "x" + _photo.json.sizeVariants.thumb2x.height + ")", lychee.getBaseUrl() + _photo.json.sizeVariants.thumb2x.url);
	}
	if (_photo.json.sizeVariants.thumb !== null) {
		msg += buildLine(lychee.locale["PHOTO_THUMB"] + " (" + _photo.json.sizeVariants.thumb.width + "x" + _photo.json.sizeVariants.thumb.height + ")", lychee.getBaseUrl() + _photo.json.sizeVariants.thumb.url);
	}
	if (_photo.json.livePhotoUrl !== "") {
		msg += buildLine(" " + lychee.locale["PHOTO_LIVE_VIDEO"] + " ", lychee.getBaseUrl() + _photo.json.livePhotoUrl);
	}

	msg += lychee.html(_templateObject68);

	basicModal.show({
		body: msg,
		buttons: {
			cancel: {
				title: lychee.locale["CLOSE"],
				fn: basicModal.close
			}
		}
	});

	// Ensure that no input line is selected on opening.
	$(".basicModal input:focus").blur();

	$(".directLinks .basicModal__button").on(lychee.getEventName(), function () {
		if (lychee.clipboardCopy($(this).prev().val())) {
			loadingBar.show("success", lychee.locale["URL_COPIED_TO_CLIPBOARD"]);
		}
	});
};

/**
 * @description Takes care of every action a photoeditor can handle and execute.
 */

photoeditor = {};

photoeditor.rotate = function (photoID, direction) {
	if (!photoID) return false;
	if (!direction) return false;

	var params = {
		photoID: photoID,
		direction: direction
	};

	api.post("PhotoEditor::rotate", params, function (data) {
		if (data === false) {
			lychee.error(null, params, data);
		} else {
			_photo.json = data;
			_photo.json.original_album = _photo.json.album;
			if (album.json) {
				_photo.json.album = album.json.id;
			}

			var image = $("img#image");
			if (_photo.json.sizeVariants.medium2x !== null) {
				image.prop("srcset", _photo.json.sizeVariants.medium.url + " " + _photo.json.sizeVariants.medium.width + "w, " + _photo.json.sizeVariants.medium2x.url + " " + _photo.json.sizeVariants.medium2x.width + "w");
			} else {
				image.prop("srcset", "");
			}
			image.prop("src", _photo.json.sizeVariants.medium !== null ? _photo.json.sizeVariants.medium.url : _photo.json.url);
			view.photo.onresize();
			view.photo.sidebar();

			album.updatePhoto(data);
		}
	});
};

/**
 * @description Searches through your photos and albums.
 */

var search = {
	hash: null
};

search.find = function (term) {
	if (term.trim() === "") return false;

	clearTimeout($(window).data("timeout"));

	$(window).data("timeout", setTimeout(function () {
		if (header.dom(".header__search").val().length !== 0) {
			api.post("search", { term: term }, function (data) {
				var html = "";
				var albumsData = "";
				var photosData = "";

				// Build albums
				if (data && data.albums) {
					albums.json = { albums: data.albums };
					$.each(albums.json.albums, function () {
						albums.parse(this);
						albumsData += build.album(this);
					});
				}

				// Build photos
				if (data && data.photos) {
					album.json = { photos: data.photos };
					$.each(album.json.photos, function () {
						photosData += build.photo(this);
					});
				}

				var albums_divider = lychee.locale["ALBUMS"];
				var photos_divider = lychee.locale["PHOTOS"];

				if (albumsData !== "") albums_divider += " (" + data.albums.length + ")";
				if (photosData !== "") {
					photos_divider += " (" + data.photos.length + ")";
					if (lychee.layout === "1") {
						photosData = '<div class="justified-layout">' + photosData + "</div>";
					} else if (lychee.layout === "2") {
						photosData = '<div class="unjustified-layout">' + photosData + "</div>";
					}
				}

				// 1. No albums and photos
				// 2. Only photos
				// 3. Only albums
				// 4. Albums and photos
				if (albumsData === "" && photosData === "") html = "error";else if (albumsData === "") html = build.divider(photos_divider) + photosData;else if (photosData === "") html = build.divider(albums_divider) + albumsData;else html = build.divider(albums_divider) + albumsData + build.divider(photos_divider) + photosData;

				// Only refresh view when search results are different
				if (search.hash !== data.hash) {
					$(".no_content").remove();

					lychee.animate(".content", "contentZoomOut");

					search.hash = data.hash;

					setTimeout(function () {
						if (visible.photo()) view.photo.hide();
						if (visible.sidebar()) _sidebar.toggle();
						if (visible.mapview()) mapview.close();

						header.setMode("albums");

						if (html === "error") {
							lychee.content.html("");
							$("body").append(build.no_content("magnifying-glass"));
						} else {
							lychee.content.html(html);
							view.album.content.justify();
							lychee.animate(lychee.content, "contentZoomIn");
						}
						lychee.setTitle(lychee.locale["SEARCH_RESULTS"], false);
					}, 300);
				}
			});
		} else search.reset();
	}, 250));
};

search.reset = function () {
	header.dom(".header__search").val("");
	$(".no_content").remove();

	if (search.hash != null) {
		// Trash data
		albums.json = null;
		album.json = null;
		_photo.json = null;
		search.hash = null;

		lychee.animate(".divider", "fadeOut");
		lychee.goto();
	}
};

/**
 * @description Lets you change settings.
 */

var settings = {};

settings.open = function () {
	view.settings.init();
};

settings.createConfig = function () {
	var action = function action(data) {
		var dbName = data.dbName || "";
		var dbUser = data.dbUser || "";
		var dbPassword = data.dbPassword || "";
		var dbHost = data.dbHost || "";
		var dbTablePrefix = data.dbTablePrefix || "";

		if (dbUser.length < 1) {
			basicModal.error("dbUser");
			return false;
		}

		if (dbHost.length < 1) dbHost = "localhost";
		if (dbName.length < 1) dbName = "lychee";

		var params = {
			dbName: dbName,
			dbUser: dbUser,
			dbPassword: dbPassword,
			dbHost: dbHost,
			dbTablePrefix: dbTablePrefix
		};

		api.post("Config::create", params, function (_data) {
			if (_data !== true) {
				// Connection failed
				if (_data === "Warning: Connection failed!") {
					basicModal.show({
						body: "<p>" + lychee.locale["ERROR_DB_1"] + "</p>",
						buttons: {
							action: {
								title: lychee.locale["RETRY"],
								fn: settings.createConfig
							}
						}
					});

					return false;
				}

				// Creation failed
				if (_data === "Warning: Creation failed!") {
					basicModal.show({
						body: "<p>" + lychee.locale["ERROR_DB_2"] + "</p>",
						buttons: {
							action: {
								title: lychee.locale["RETRY"],
								fn: settings.createConfig
							}
						}
					});

					return false;
				}

				// Could not create file
				if (_data === "Warning: Could not create file!") {
					basicModal.show({
						body: "<p>" + lychee.locale["ERROR_CONFIG_FILE"] + "</p>",
						buttons: {
							action: {
								title: lychee.locale["RETRY"],
								fn: settings.createConfig
							}
						}
					});

					return false;
				}

				// Something went wrong
				basicModal.show({
					body: "<p>" + lychee.locale["ERROR_UNKNOWN"] + "</p>",
					buttons: {
						action: {
							title: lychee.locale["RETRY"],
							fn: settings.createConfig
						}
					}
				});

				return false;
			} else {
				// Configuration successful
				window.location.reload();

				return false;
			}
		});
	};

	var msg = "\n\t\t\t  <p>\n\t\t\t\t  " + lychee.locale["DB_INFO_TITLE"] + "\n\t\t\t\t  <input name='dbHost' class='text' type='text' placeholder='" + lychee.locale["DB_INFO_HOST"] + "' value=''>\n\t\t\t\t  <input name='dbUser' class='text' type='text' placeholder='" + lychee.locale["DB_INFO_USER"] + "' value=''>\n\t\t\t\t  <input name='dbPassword' class='text' type='password' placeholder='" + lychee.locale["DB_INFO_PASSWORD"] + "' value=''>\n\t\t\t  </p>\n\t\t\t  <p>\n\t\t\t\t  " + lychee.locale["DB_INFO_TEXT"] + "\n\t\t\t\t  <input name='dbName' class='text' type='text' placeholder='" + lychee.locale["DB_NAME"] + "' value=''>\n\t\t\t\t  <input name='dbTablePrefix' class='text' type='text' placeholder='" + lychee.locale["DB_PREFIX"] + "' value=''>\n\t\t\t  </p>\n\t\t\t  ";

	basicModal.show({
		body: msg,
		buttons: {
			action: {
				title: lychee.locale["DB_CONNECT"],
				fn: action
			}
		}
	});
};

settings.createLogin = function () {
	var action = function action(data) {
		var username = data.username;
		var password = data.password;
		var confirm = data.confirm;

		if (!username.trim()) {
			basicModal.error("username");
			return false;
		}

		if (!password.trim()) {
			basicModal.error("password");
			return false;
		}

		if (password !== confirm) {
			basicModal.error("confirm");
			return false;
		}

		basicModal.close();

		var params = {
			username: username,
			password: password
		};

		api.post("Settings::setLogin", params, function (_data) {
			if (_data !== true) {
				basicModal.show({
					body: "<p>" + lychee.locale["ERROR_LOGIN"] + "</p>",
					buttons: {
						action: {
							title: lychee.locale["RETRY"],
							fn: settings.createLogin
						}
					}
				});
			}
			// else
			// {
			// 	window.location.reload()
			// }
		});
	};

	var msg = "\n\t\t\t  <p>\n\t\t\t\t  " + lychee.locale["LOGIN_TITLE"] + "\n\t\t\t\t  <input name='username' class='text' type='text' placeholder='" + lychee.locale["LOGIN_USERNAME"] + "' value=''>\n\t\t\t\t  <input name='password' class='text' type='password' placeholder='" + lychee.locale["LOGIN_PASSWORD"] + "' value=''>\n\t\t\t\t  <input name='confirm' class='text' type='password' placeholder='" + lychee.locale["LOGIN_PASSWORD_CONFIRM"] + "' value=''>\n\t\t\t  </p>\n\t\t\t  ";

	basicModal.show({
		body: msg,
		buttons: {
			action: {
				title: lychee.locale["LOGIN_CREATE"],
				fn: action
			}
		}
	});
};

// from https://github.com/electerious/basicModal/blob/master/src/scripts/main.js
settings.getValues = function (form_name) {
	var values = {};
	var inputs_select = $(form_name + " input[name], " + form_name + " select[name]");

	// Get value from all inputs
	$(inputs_select).each(function () {
		var name = $(this).attr("name");
		// Store name and value of input
		values[name] = $(this).val();
	});
	return Object.keys(values).length === 0 ? null : values;
};

// from https://github.com/electerious/basicModal/blob/master/src/scripts/main.js
settings.bind = function (item, name, fn) {
	// if ($(item).length)
	// {
	//     console.log('found');
	// }
	// else
	// {
	//     console.log('not found: ' + item);
	// }
	// Action-button
	$(item).on("click", function () {
		fn(settings.getValues(name));
	});
};

settings.changeLogin = function (params) {
	if (params.username.length < 1) {
		loadingBar.show("error", "new username cannot be empty.");
		$("input[name=username]").addClass("error");
		return false;
	} else {
		$("input[name=username]").removeClass("error");
	}

	if (params.password.length < 1) {
		loadingBar.show("error", "new password cannot be empty.");
		$("input[name=password]").addClass("error");
		return false;
	} else {
		$("input[name=password]").removeClass("error");
	}

	if (params.password !== params.confirm) {
		loadingBar.show("error", "new password does not match.");
		$("input[name=confirm]").addClass("error");
		return false;
	} else {
		$("input[name=confirm]").removeClass("error");
	}

	api.post("Settings::setLogin", params, function (data) {
		if (data !== true) {
			loadingBar.show("error", data.description);
			lychee.error(null, datas, data);
		} else {
			$("input[name]").removeClass("error");
			loadingBar.show("success", lychee.locale["SETTINGS_SUCCESS_LOGIN"]);
			view.settings.content.clearLogin();
		}
	});
};

settings.changeSorting = function (params) {
	api.post("Settings::setSorting", params, function (data) {
		if (data === true) {
			lychee.sortingAlbums = "ORDER BY " + params["typeAlbums"] + " " + params["orderAlbums"];
			lychee.sortingPhotos = "ORDER BY " + params["typePhotos"] + " " + params["orderPhotos"];
			albums.refresh();
			loadingBar.show("success", lychee.locale["SETTINGS_SUCCESS_SORT"]);
		} else lychee.error(null, params, data);
	});
};

settings.changeDropboxKey = function (params) {
	// if params.key == "" key is cleared
	api.post("Settings::setDropboxKey", params, function (data) {
		if (data === true) {
			lychee.dropboxKey = params.key;
			// if (callback) lychee.loadDropbox(callback)
			loadingBar.show("success", lychee.locale["SETTINGS_SUCCESS_DROPBOX"]);
		} else lychee.error(null, params, data);
	});
};

settings.changeLang = function (params) {
	api.post("Settings::setLang", params, function (data) {
		if (data === true) {
			loadingBar.show("success", lychee.locale["SETTINGS_SUCCESS_LANG"]);
			lychee.init();
		} else lychee.error(null, params, data);
	});
};

settings.setDefaultLicense = function (params) {
	api.post("Settings::setDefaultLicense", params, function (data) {
		if (data === true) {
			lychee.default_license = params.license;
			loadingBar.show("success", lychee.locale["SETTINGS_SUCCESS_LICENSE"]);
		} else lychee.error(null, params, data);
	});
};

settings.setLayout = function (params) {
	api.post("Settings::setLayout", params, function (data) {
		if (data === true) {
			lychee.layout = params.layout;
			loadingBar.show("success", lychee.locale["SETTINGS_SUCCESS_LAYOUT"]);
		} else lychee.error(null, params, data);
	});
};

settings.changePublicSearch = function () {
	var params = {};
	if ($("#PublicSearch:checked").length === 1) {
		params.public_search = "1";
	} else {
		params.public_search = "0";
	}
	api.post("Settings::setPublicSearch", params, function (data) {
		if (data === true) {
			loadingBar.show("success", lychee.locale["SETTINGS_SUCCESS_PUBLIC_SEARCH"]);
			lychee.public_search = params.public_search === "1";
		} else lychee.error(null, params, data);
	});
};

settings.setOverlayType = function () {
	// validate the input
	var params = {};
	var check = $("#ImageOverlay:checked") ? true : false;
	var type = $("#ImgOverlayType").val();
	if (check && type === "exif") {
		params.image_overlay_type = "exif";
	} else if (check && type === "desc") {
		params.image_overlay_type = "desc";
	} else if (check && type === "date") {
		params.image_overlay_type = "date";
	} else if (check && type === "none") {
		params.image_overlay_type = "none";
	} else {
		params.image_overlay_type = "exif";
		console.log("Error - default used");
	}

	api.post("Settings::setOverlayType", params, function (data) {
		if (data === true) {
			loadingBar.show("success", lychee.locale["SETTINGS_SUCCESS_IMAGE_OVERLAY"]);
			lychee.image_overlay_type = params.image_overlay_type;
			lychee.image_overlay_type_default = params.image_overlay_type;
		} else lychee.error(null, params, data);
	});
};

settings.changeMapDisplay = function () {
	var params = {};
	if ($("#MapDisplay:checked").length === 1) {
		params.map_display = "1";
	} else {
		params.map_display = "0";
	}
	api.post("Settings::setMapDisplay", params, function (data) {
		if (data === true) {
			loadingBar.show("success", lychee.locale["SETTINGS_SUCCESS_MAP_DISPLAY"]);
			lychee.map_display = params.map_display === "1";
		} else lychee.error(null, params, data);
	});
	// Map functionality is disabled
	// -> map for public albums also needs to be disabled
	if (lychee.map_display_public === true) {
		$("#MapDisplayPublic").click();
	}
};

settings.changeMapDisplayPublic = function () {
	var params = {};
	if ($("#MapDisplayPublic:checked").length === 1) {
		params.map_display_public = "1";

		// If public map functionality is enabled, but map in general is disabled
		// General map functionality needs to be enabled
		if (lychee.map_display === false) {
			$("#MapDisplay").click();
		}
	} else {
		params.map_display_public = "0";
	}
	api.post("Settings::setMapDisplayPublic", params, function (data) {
		if (data === true) {
			loadingBar.show("success", lychee.locale["SETTINGS_SUCCESS_MAP_DISPLAY_PUBLIC"]);
			lychee.map_display_public = params.map_display_public === "1";
		} else lychee.error(null, params, data);
	});
};

settings.setMapProvider = function () {
	// validate the input
	var params = {};
	params.map_provider = $("#MapProvider").val();

	api.post("Settings::setMapProvider", params, function (data) {
		if (data === true) {
			loadingBar.show("success", lychee.locale["SETTINGS_SUCCESS_MAP_PROVIDER"]);
			lychee.map_provider = params.map_provider;
		} else lychee.error(null, params, data);
	});
};

settings.changeMapIncludeSubalbums = function () {
	var params = {};
	if ($("#MapIncludeSubalbums:checked").length === 1) {
		params.map_include_subalbums = "1";
	} else {
		params.map_include_subalbums = "0";
	}
	api.post("Settings::setMapIncludeSubalbums", params, function (data) {
		if (data === true) {
			loadingBar.show("success", lychee.locale["SETTINGS_SUCCESS_MAP_DISPLAY"]);
			lychee.map_include_subalbums = params.map_include_subalbums === "1";
		} else lychee.error(null, params, data);
	});
};

settings.changeLocationDecoding = function () {
	var params = {};
	if ($("#LocationDecoding:checked").length === 1) {
		params.location_decoding = "1";
	} else {
		params.location_decoding = "0";
	}
	api.post("Settings::setLocationDecoding", params, function (data) {
		if (data === true) {
			loadingBar.show("success", lychee.locale["SETTINGS_SUCCESS_MAP_DISPLAY"]);
			lychee.location_decoding = params.location_decoding === "1";
		} else lychee.error(null, params, data);
	});
};

settings.changeNSFWVisible = function () {
	var params = {};
	if ($("#NSFWVisible:checked").length === 1) {
		params.nsfw_visible = "1";
	} else {
		params.nsfw_visible = "0";
	}
	api.post("Settings::setNSFWVisible", params, function (data) {
		if (data === true) {
			loadingBar.show("success", lychee.locale["SETTINGS_SUCCESS_NSFW_VISIBLE"]);
			lychee.nsfw_visible = params.nsfw_visible === "1";
			lychee.nsfw_visible_saved = lychee.nsfw_visible;
		} else {
			lychee.error(null, params, data);
		}
	});
};

//TODO : later
// lychee.nsfw_blur = (data.config.nsfw_blur && data.config.nsfw_blur === '1') || false;
// lychee.nsfw_warning = (data.config.nsfw_warning && data.config.nsfw_warning === '1') || false;
// lychee.nsfw_warning_text = data.config.nsfw_warning_text || '<b>Sensitive content</b><br><p>This album contains sensitive content which some people may find offensive or disturbing.</p>';

settings.changeLocationShow = function () {
	var params = {};
	if ($("#LocationShow:checked").length === 1) {
		params.location_show = "1";
	} else {
		params.location_show = "0";
		// Don't show location
		// -> location for public albums also needs to be disabled
		if (lychee.location_show_public === true) {
			$("#LocationShowPublic").click();
		}
	}
	api.post("Settings::setLocationShow", params, function (data) {
		if (data === true) {
			loadingBar.show("success", lychee.locale["SETTINGS_SUCCESS_MAP_DISPLAY"]);
			lychee.location_show = params.location_show === "1";
		} else lychee.error(null, params, data);
	});
};

settings.changeLocationShowPublic = function () {
	var params = {};
	if ($("#LocationShowPublic:checked").length === 1) {
		params.location_show_public = "1";
		// If public map functionality is enabled, but map in general is disabled
		// General map functionality needs to be enabled
		if (lychee.location_show === false) {
			$("#LocationShow").click();
		}
	} else {
		params.location_show_public = "0";
	}
	api.post("Settings::setLocationShowPublic", params, function (data) {
		if (data === true) {
			loadingBar.show("success", lychee.locale["SETTINGS_SUCCESS_MAP_DISPLAY"]);
			lychee.location_show_public = params.location_show_public === "1";
		} else lychee.error(null, params, data);
	});
};

settings.changeCSS = function () {
	var params = {};
	params.css = $("#css").val();

	api.post("Settings::setCSS", params, function (data) {
		if (data === true) {
			lychee.css = params.css;
			loadingBar.show("success", lychee.locale["SETTINGS_SUCCESS_CSS"]);
		} else lychee.error(null, params, data);
	});
};

settings.save = function (params) {
	var exitview = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : true;

	api.post("Settings::saveAll", params, function (data) {
		if (data === true) {
			loadingBar.show("success", lychee.locale["SETTINGS_SUCCESS_UPDATE"]);
			view.full_settings.init();
			// re-read settings
			lychee.init(exitview);
		} else lychee.error("Check the Logs", params, data);
	});
};

settings.save_enter = function (e) {
	if (e.which === 13) {
		// show confirmation box
		$(":focus").blur();

		var action = {};
		var cancel = {};

		action.title = lychee.locale["ENTER"];
		action.msg = lychee.html(_templateObject69, lychee.locale["SAVE_RISK"]);

		cancel.title = lychee.locale["CANCEL"];

		action.fn = function () {
			settings.save(settings.getValues("#fullSettings"), false);
			basicModal.close();
		};

		basicModal.show({
			body: action.msg,
			buttons: {
				action: {
					title: action.title,
					fn: action.fn,
					class: "red"
				},
				cancel: {
					title: cancel.title,
					fn: basicModal.close
				}
			}
		});
	}
};

var sharing = {
	json: null
};

sharing.add = function () {
	var params = {
		albumIDs: "",
		UserIDs: ""
	};

	$("#albums_list_to option").each(function () {
		if (params.albumIDs !== "") params.albumIDs += ",";
		params.albumIDs += this.value;
	});

	$("#user_list_to option").each(function () {
		if (params.UserIDs !== "") params.UserIDs += ",";
		params.UserIDs += this.value;
	});

	if (params.albumIDs === "") {
		loadingBar.show("error", "Select an album to share!");
		return false;
	}
	if (params.UserIDs === "") {
		loadingBar.show("error", "Select a user to share with!");
		return false;
	}

	api.post("Sharing::Add", params, function (data) {
		if (data !== true) {
			loadingBar.show("error", data.description);
			lychee.error(null, params, data);
		} else {
			loadingBar.show("success", "Sharing updated!");
			sharing.list(); // reload user list
		}
	});
};

sharing.delete = function () {
	var params = {
		ShareIDs: ""
	};

	$('input[name="remove_id"]:checked').each(function () {
		if (params.ShareIDs !== "") params.ShareIDs += ",";
		params.ShareIDs += this.value;
	});

	if (params.ShareIDs === "") {
		loadingBar.show("error", "Select a sharing to remove!");
		return false;
	}
	api.post("Sharing::Delete", params, function (data) {
		if (data !== true) {
			loadingBar.show("error", data.description);
			lychee.error(null, params, data);
		} else {
			loadingBar.show("success", "Sharing removed!");
			sharing.list(); // reload user list
		}
	});
};

sharing.list = function () {
	api.post("Sharing::List", {}, function (data) {
		sharing.json = data;
		view.sharing.init();
	});
};

/**
 * @description This module takes care of the sidebar.
 */

var _sidebar = {
	_dom: $(".sidebar"),
	types: {
		DEFAULT: 0,
		TAGS: 1,
		PALETTE: 2
	},
	createStructure: {}
};

_sidebar.dom = function (selector) {
	if (selector == null || selector === "") return _sidebar._dom;

	return _sidebar._dom.find(selector);
};

_sidebar.bind = function () {
	// This function should be called after building and appending
	// the sidebars content to the DOM.
	// This function can be called multiple times, therefore
	// event handlers should be removed before binding a new one.

	// Event Name
	var eventName = lychee.getEventName();

	_sidebar.dom("#edit_title").off(eventName).on(eventName, function () {
		if (visible.photo()) _photo.setTitle([_photo.getID()]);else if (visible.album()) album.setTitle([album.getID()]);
	});

	_sidebar.dom("#edit_description").off(eventName).on(eventName, function () {
		if (visible.photo()) _photo.setDescription(_photo.getID());else if (visible.album()) album.setDescription(album.getID());
	});

	_sidebar.dom("#edit_showtags").off(eventName).on(eventName, function () {
		album.setShowTags(album.getID());
	});

	_sidebar.dom("#edit_tags").off(eventName).on(eventName, function () {
		_photo.editTags([_photo.getID()]);
	});

	_sidebar.dom("#tags .tag").off(eventName).on(eventName, function () {
		_sidebar.triggerSearch($(this).text());
	});

	_sidebar.dom("#tags .tag span").off(eventName).on(eventName, function () {
		_photo.deleteTag(_photo.getID(), $(this).data("index"));
	});

	_sidebar.dom("#edit_license").off(eventName).on(eventName, function () {
		if (visible.photo()) _photo.setLicense(_photo.getID());else if (visible.album()) album.setLicense(album.getID());
	});

	_sidebar.dom("#edit_sorting").off(eventName).on(eventName, function () {
		album.setSorting(album.getID());
	});

	_sidebar.dom(".attr_location").off(eventName).on(eventName, function () {
		_sidebar.triggerSearch($(this).text());
	});

	_sidebar.dom(".color").off(eventName).on(eventName, function () {
		_sidebar.triggerSearch($(this).data('color'));
	});

	return true;
};

_sidebar.triggerSearch = function (search_string) {
	// If public search is diabled -> do nothing
	if (lychee.publicMode === true && !lychee.public_search) {
		// Do not display an error -> just do nothing to not confuse the user
		return;
	}

	search.hash = null;
	// We're either logged in or public search is allowed
	lychee.goto("search/" + encodeURIComponent(search_string));
};

_sidebar.toggle = function () {
	if (visible.sidebar() || visible.sidebarbutton()) {
		header.dom(".button--info").toggleClass("active");
		lychee.content.toggleClass("content--sidebar");
		lychee.imageview.toggleClass("image--sidebar");
		if (typeof view !== "undefined") view.album.content.justify();
		_sidebar.dom().toggleClass("active");
		_photo.updateSizeLivePhotoDuringAnimation();

		return true;
	}

	return false;
};

_sidebar.setSelectable = function () {
	var selectable = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : true;

	// Attributes/Values inside the sidebar are selectable by default.
	// Selection needs to be deactivated to prevent an unwanted selection
	// while using multiselect.

	if (selectable === true) _sidebar.dom().removeClass("notSelectable");else _sidebar.dom().addClass("notSelectable");
};

_sidebar.changeAttr = function (attr) {
	var value = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : "-";
	var dangerouslySetInnerHTML = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : false;

	if (attr == null || attr === "") return false;

	// Set a default for the value
	if (value == null || value === "") value = "-";

	// Escape value
	if (dangerouslySetInnerHTML === false) value = lychee.escapeHTML(value);

	// Set new value
	_sidebar.dom(".attr_" + attr).html(value);

	return true;
};

_sidebar.hideAttr = function (attr) {
	_sidebar.dom(".attr_" + attr).closest("tr").hide();
};

_sidebar.secondsToHMS = function (d) {
	d = Number(d);
	var h = Math.floor(d / 3600);
	var m = Math.floor(d % 3600 / 60);
	var s = Math.floor(d % 60);

	return (h > 0 ? h.toString() + "h" : "") + (m > 0 ? m.toString() + "m" : "") + (s > 0 || h == 0 && m == 0 ? s.toString() + "s" : "");
};

_sidebar.createStructure.photo = function (data) {
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
		type: _sidebar.types.DEFAULT,
		rows: [{ title: lychee.locale["PHOTO_TITLE"], kind: "title", value: data.title, editable: editable }, { title: lychee.locale["PHOTO_UPLOADED"], kind: "uploaded", value: lychee.locale.printDateTime(data.created_at) }, { title: lychee.locale["PHOTO_DESCRIPTION"], kind: "description", value: data.description, editable: editable }]
	};

	structure.image = {
		title: lychee.locale[isVideo ? "PHOTO_VIDEO" : "PHOTO_IMAGE"],
		type: _sidebar.types.DEFAULT,
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
			structure.image.rows.push({ title: lychee.locale["PHOTO_DURATION"], kind: "duration", value: _sidebar.secondsToHMS(data.aperture) });
		}
		if (data.focal != "") {
			structure.image.rows.push({ title: lychee.locale["PHOTO_FPS"], kind: "fps", value: data.focal + " fps" });
		}
	}

	// Always create tags section - behaviour for editing
	//tags handled when contructing the html code for tags

	structure.tags = {
		title: lychee.locale["PHOTO_TAGS"],
		type: _sidebar.types.TAGS,
		value: build.tags(data.tags),
		editable: editable
	};

	structure.palette = {
		title: lychee.locale["PHOTO_PALETTE"],
		type: _sidebar.types.PALETTE,
		value: data.colors.length > 0 ? build.colors(data.colors) : ''

		// Only create EXIF section when EXIF data available
	};if (exifHash !== "") {
		structure.exif = {
			title: lychee.locale["PHOTO_CAMERA"],
			type: _sidebar.types.DEFAULT,
			rows: isVideo ? [{ title: lychee.locale["PHOTO_CAPTURED"], kind: "takedate", value: lychee.locale.printDateTime(data.taken_at) }, { title: lychee.locale["PHOTO_MAKE"], kind: "make", value: data.make }, { title: lychee.locale["PHOTO_TYPE"], kind: "model", value: data.model }] : [{ title: lychee.locale["PHOTO_CAPTURED"], kind: "takedate", value: lychee.locale.printDateTime(data.taken_at) }, { title: lychee.locale["PHOTO_MAKE"], kind: "make", value: data.make }, { title: lychee.locale["PHOTO_TYPE"], kind: "model", value: data.model }, { title: lychee.locale["PHOTO_LENS"], kind: "lens", value: data.lens }, { title: lychee.locale["PHOTO_SHUTTER"], kind: "shutter", value: data.shutter }, { title: lychee.locale["PHOTO_APERTURE"], kind: "aperture", value: data.aperture }, { title: lychee.locale["PHOTO_FOCAL"], kind: "focal", value: data.focal }, { title: lychee.locale["PHOTO_ISO"], kind: "iso", value: data.iso }]
		};
	} else {
		structure.exif = {};
	}

	structure.sharing = {
		title: lychee.locale["PHOTO_SHARING"],
		type: _sidebar.types.DEFAULT,
		rows: [{ title: lychee.locale["PHOTO_SHR_PLUBLIC"], kind: "public", value: _public }]
	};

	structure.license = {
		title: lychee.locale["PHOTO_REUSE"],
		type: _sidebar.types.DEFAULT,
		rows: [{ title: lychee.locale["PHOTO_LICENSE"], kind: "license", value: license, editable: editable }]
	};

	if (locationHash !== "" && locationHash !== 0) {
		structure.location = {
			title: lychee.locale["PHOTO_LOCATION"],
			type: _sidebar.types.DEFAULT,
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

_sidebar.createStructure.album = function (album) {
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
		type: _sidebar.types.DEFAULT,
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
		type: _sidebar.types.DEFAULT,
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
		type: _sidebar.types.DEFAULT,
		rows: [{ title: lychee.locale["ALBUM_PUBLIC"], kind: "public", value: _public }, { title: lychee.locale["ALBUM_HIDDEN"], kind: "hidden", value: hidden }, { title: lychee.locale["ALBUM_DOWNLOADABLE"], kind: "downloadable", value: downloadable }, { title: lychee.locale["ALBUM_SHARE_BUTTON_VISIBLE"], kind: "share_button_visible", value: share_button_visible }, { title: lychee.locale["ALBUM_PASSWORD"], kind: "password", value: password }]
	};

	if (data.owner != null) {
		structure.share.rows.push({ title: lychee.locale["ALBUM_OWNER"], kind: "owner", value: data.owner });
	}

	structure.license = {
		title: lychee.locale["ALBUM_REUSE"],
		type: _sidebar.types.DEFAULT,
		rows: [{ title: lychee.locale["ALBUM_LICENSE"], kind: "license", value: license, editable: editable }]
	};

	// Construct all parts of the structure
	var structure_ret = [structure.basics, structure.album, structure.license];
	if (!lychee.publicMode) {
		structure_ret.push(structure.share);
	}

	return structure_ret;
};

_sidebar.has_location = function (structure) {
	if (structure == null || structure === "" || structure === false) return false;

	var _has_location = false;

	structure.forEach(function (section) {
		if (section.title == lychee.locale["PHOTO_LOCATION"]) {
			_has_location = true;
		}
	});

	return _has_location;
};

_sidebar.render = function (structure) {
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
							value += lychee.html(_templateObject70, row.kind);
						}
						value += lychee.html(_templateObject71, row.kind, v);
					});
				} else {
					value = lychee.html(_templateObject72, row.kind, value);
				}

				// Add edit-icon to the value when editable
				if (row.editable === true) value += " " + build.editIcon("edit_" + row.kind);

				_html += lychee.html(_templateObject73, row.title, value);
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

		_html += lychee.html(_templateObject74, section.title, section.title.toLowerCase(), section.value, editable);

		return _html;
	};

	var renderPalette = function renderPalette(section) {
		var _html = "";
		_html += lychee.html(_templateObject75, section.title, section.value);
		return _html;
	};

	structure.forEach(function (section) {
		if (section.type === _sidebar.types.DEFAULT) html += renderDefault(section);else if (section.type === _sidebar.types.TAGS) html += renderTags(section);else if (section.type === _sidebar.types.PALETTE && section.value !== '') html += renderPalette(section);
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
 * @description Swipes and moves an object.
 */

var swipe = {
	obj: null,
	offsetX: 0,
	offsetY: 0,
	preventNextHeaderToggle: false
};

swipe.start = function (obj) {
	if (obj) swipe.obj = obj;
	return true;
};

swipe.move = function (e) {
	if (swipe.obj === null) {
		return false;
	}

	if (Math.abs(e.x) > Math.abs(e.y)) {
		swipe.offsetX = -1 * e.x;
		swipe.offsetY = 0.0;
	} else {
		swipe.offsetX = 0.0;
		swipe.offsetY = +1 * e.y;
	}

	var value = "translate(" + swipe.offsetX + "px, " + swipe.offsetY + "px)";
	swipe.obj.css({
		WebkitTransform: value,
		MozTransform: value,
		transform: value
	});
	return;
};

swipe.stop = function (e, left, right) {
	// Only execute once
	if (swipe.obj == null) {
		return false;
	}

	if (e.y <= -lychee.swipe_tolerance_y) {
		lychee.goto(album.getID());
	} else if (e.y >= lychee.swipe_tolerance_y) {
		lychee.goto(album.getID());
	} else if (e.x <= -lychee.swipe_tolerance_x) {
		left(true);

		// 'touchend' will be called after 'swipeEnd'
		// in case of moving to next image, we want to skip
		// the toggling of the header
		swipe.preventNextHeaderToggle = true;
	} else if (e.x >= lychee.swipe_tolerance_x) {
		right(true);

		// 'touchend' will be called after 'swipeEnd'
		// in case of moving to next image, we want to skip
		// the toggling of the header
		swipe.preventNextHeaderToggle = true;
	} else {
		var value = "translate(0px, 0px)";
		swipe.obj.css({
			WebkitTransform: value,
			MozTransform: value,
			transform: value
		});
	}

	swipe.obj = null;
	swipe.offsetX = 0;
	swipe.offsetY = 0;

	return;
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

var u2f = {
	json: null
};

u2f.is_available = function () {
	if (!window.isSecureContext && window.location.hostname !== "localhost" && window.location.hostname !== "127.0.0.1") {
		var msg = lychee.html(_templateObject76, lychee.locale["U2F_NOT_SECURE"]);

		basicModal.show({
			body: msg,
			buttons: {
				cancel: {
					title: lychee.locale["CLOSE"],
					fn: basicModal.close
				}
			}
		});

		return false;
	}
	return true;
};

u2f.login = function () {
	if (!u2f.is_available()) {
		return;
	}

	new Larapass({
		login: "/api/webauthn::login",
		loginOptions: "/api/webauthn::login/gen"
	}).login({
		user_id: 0 // for now it is only available to Admin user via a secret key shortcut.
	}).then(function (data) {
		loadingBar.show("success", lychee.locale["U2F_AUTHENTIFICATION_SUCCESS"]);
		window.location.reload();
	}).catch(function (error) {
		return loadingBar.show("error", "Something went wrong!");
	});
};

u2f.register = function () {
	if (!u2f.is_available()) {
		return;
	}

	var larapass = new Larapass({
		register: "/api/webauthn::register",
		registerOptions: "/api/webauthn::register/gen"
	});
	if (Larapass.supportsWebAuthn()) {
		larapass.register().then(function (response) {
			loadingBar.show("success", lychee.locale["U2F_REGISTRATION_SUCCESS"]);
			u2f.list(); // reload credential list
		}).catch(function (response) {
			return loadingBar.show("error", "Something went wrong!");
		});
	} else {
		loadingBar.show("error", lychee.locale["U2F_NOT_SUPPORTED"]);
	}
};

u2f.delete = function (params) {
	api.post("webauthn::delete", params, function (data) {
		console.log(data);
		if (!data) {
			loadingBar.show("error", data.description);
			lychee.error(null, params, data);
		} else {
			loadingBar.show("success", lychee.locale["U2F_CREDENTIALS_DELETED"]);
			u2f.list(); // reload credential list
		}
	});
};

u2f.list = function () {
	api.post("webauthn::list", {}, function (data) {
		u2f.json = data;
		view.u2f.init();
	});
};

/**
 * @description Takes care of every action an album can handle and execute.
 */

var upload = {};

var choiceDeleteSelector = '.basicModal .choice input[name="delete"]';
var choiceSymlinkSelector = '.basicModal .choice input[name="symlinks"]';
var choiceDuplicateSelector = '.basicModal .choice input[name="skipduplicates"]';
var choiceResyncSelector = '.basicModal .choice input[name="resyncmetadata"]';
var actionSelector = ".basicModal #basicModal__action";
var cancelSelector = ".basicModal #basicModal__cancel";
var lastRowSelector = ".basicModal .rows .row:last-child";
var prelastRowSelector = ".basicModal .rows .row:nth-last-child(2)";

var nRowStatusSelector = function nRowStatusSelector(row) {
	return ".basicModal .rows .row:nth-child(" + row + ") .status";
};

var showCloseButton = function showCloseButton() {
	$(actionSelector).show();
	// re-activate cancel button to close modal panel if needed
	$(cancelSelector).removeClass("basicModal__button--active").hide();
};

upload.show = function (title, files, run_callback) {
	var cancel_callback = arguments.length > 3 && arguments[3] !== undefined ? arguments[3] : null;

	basicModal.show({
		body: build.uploadModal(title, files),
		buttons: {
			action: {
				title: lychee.locale["CLOSE"],
				class: "hidden",
				fn: function fn() {
					if ($(actionSelector).is(":visible")) basicModal.close();
				}
			},
			cancel: {
				title: lychee.locale["CANCEL"],
				class: "red hidden",
				fn: function fn() {
					// close modal if close button is displayed
					if ($(actionSelector).is(":visible")) basicModal.close();
					if (cancel_callback) {
						$(cancelSelector).addClass("busy");
						cancel_callback();
					}
				}
			}
		},
		callback: run_callback
	});
};

upload.notify = function (title, text) {
	if (text == null || text === "") text = lychee.locale["UPLOAD_MANAGE_NEW_PHOTOS"];

	if (!window.webkitNotifications) return false;

	if (window.webkitNotifications.checkPermission() !== 0) window.webkitNotifications.requestPermission();

	if (window.webkitNotifications.checkPermission() === 0 && title) {
		var popup = window.webkitNotifications.createNotification("", title, text);
		popup.show();
	}
};

upload.start = {
	local: function local(files) {
		var albumID = album.getID();
		var error = false;
		var warning = false;
		var processing_count = 0;
		var next_upload = 0;
		var currently_uploading = false;
		var cancelUpload = false;

		var process = function process(file_num) {
			var formData = new FormData();
			var xhr = new XMLHttpRequest();
			var pre_progress = 0;
			var progress = 0;

			if (file_num === 0) {
				$(cancelSelector).show();
			}

			var finish = function finish() {
				window.onbeforeunload = null;

				$("#upload_files").val("");

				if (error === false && warning === false) {
					// Success
					basicModal.close();
					upload.notify(lychee.locale["UPLOAD_COMPLETE"]);
				} else if (error === false && warning === true) {
					// Warning
					showCloseButton();
					upload.notify(lychee.locale["UPLOAD_COMPLETE"]);
				} else {
					// Error
					showCloseButton();
					upload.notify(lychee.locale["UPLOAD_COMPLETE"], lychee.locale["UPLOAD_COMPLETE_FAILED"]);
				}

				albums.refresh();

				if (album.getID() === false) lychee.goto("unsorted");else album.load(albumID);
			};

			formData.append("function", "Photo::add");
			formData.append("albumID", albumID);
			formData.append(0, files[file_num]);

			var api_url = "api/" + "Photo::add";

			xhr.open("POST", api_url);

			xhr.onload = function () {
				var data = null;
				var errorText = "";

				var isNumber = function isNumber(n) {
					return !isNaN(parseFloat(n)) && isFinite(n);
				};

				data = xhr.responseText;

				if (typeof data === "string" && data.search("phpdebugbar") !== -1) {
					// get rid of phpdebugbar thingy
					var debug_bar_n = data.search("<link rel='stylesheet' type='text/css'");
					if (debug_bar_n > 0) {
						data = data.slice(0, debug_bar_n);
					}
				}

				try {
					data = JSON.parse(data);
				} catch (e) {
					data = "";
				}

				// Set status
				if (xhr.status === 200 && isNumber(data)) {
					// Success
					$(nRowStatusSelector(file_num + 1)).html(lychee.locale["UPLOAD_FINISHED"]).addClass("success");
				} else {
					if (xhr.status === 413 || data.substr(0, 6) === "Error:") {
						if (xhr.status === 413) {
							errorText = lychee.locale["UPLOAD_ERROR_POSTSIZE"];
						} else {
							errorText = data.substr(6);
							if (errorText === " validation failed") {
								errorText = lychee.locale["UPLOAD_ERROR_FILESIZE"];
							} else {
								errorText += " " + lychee.locale["UPLOAD_ERROR_CONSOLE"];
							}
						}
						error = true;

						// Error Status
						$(nRowStatusSelector(file_num + 1)).html(lychee.locale["UPLOAD_FAILED"]).addClass("error");

						// Throw error
						lychee.error(lychee.locale["UPLOAD_FAILED_ERROR"], xhr, data);
					} else if (data.substr(0, 8) === "Warning:") {
						errorText = data.substr(8);
						warning = true;

						// Warning Status
						$(nRowStatusSelector(file_num + 1)).html(lychee.locale["UPLOAD_SKIPPED"]).addClass("warning");

						// Throw error
						lychee.error(lychee.locale["UPLOAD_FAILED_WARNING"], xhr, data);
					} else {
						errorText = lychee.locale["UPLOAD_UNKNOWN"];
						error = true;

						// Error Status
						$(nRowStatusSelector(file_num + 1)).html(lychee.locale["UPLOAD_FAILED"]).addClass("error");

						// Throw error
						lychee.error(lychee.locale["UPLOAD_ERROR_UNKNOWN"], xhr, data);
					}

					$(".basicModal .rows .row:nth-child(" + (file_num + 1) + ") p.notice").html(errorText).show();
				}

				processing_count--;

				// Upload next file
				if (!currently_uploading && !cancelUpload && (processing_count < lychee.upload_processing_limit || lychee.upload_processing_limit === 0) && next_upload < files.length) {
					process(next_upload);
				}

				// Finish upload when all files are finished
				if (!currently_uploading && processing_count === 0) {
					finish();
				}
			};

			xhr.upload.onprogress = function (e) {
				if (e.lengthComputable !== true) return false;

				// Calculate progress
				progress = e.loaded / e.total * 100 | 0;

				// Set progress when progress has changed
				if (progress > pre_progress) {
					$(nRowStatusSelector(file_num + 1)).html(progress + "%");
					pre_progress = progress;
				}

				if (progress >= 100) {
					// Scroll to the uploading file
					var scrollPos = 0;
					if (file_num + 1 > 4) scrollPos = (file_num + 1 - 4) * 40;
					$(".basicModal .rows").scrollTop(scrollPos);

					// Set status to processing
					$(nRowStatusSelector(file_num + 1)).html(lychee.locale["UPLOAD_PROCESSING"]);
					processing_count++;
					currently_uploading = false;

					// Upload next file
					if (!cancelUpload && (processing_count < lychee.upload_processing_limit || lychee.upload_processing_limit === 0) && next_upload < files.length) {
						process(next_upload);
					}
				}
			};

			currently_uploading = true;
			next_upload++;

			xhr.setRequestHeader("X-XSRF-TOKEN", csrf.getCookie("XSRF-TOKEN"));
			xhr.send(formData);
		};

		if (files.length <= 0) return false;
		if (albumID === false || visible.albums() === true) albumID = 0;

		window.onbeforeunload = function () {
			return lychee.locale["UPLOAD_IN_PROGRESS"];
		};

		upload.show(lychee.locale["UPLOAD_UPLOADING"], files, function () {
			// Upload first file
			process(next_upload);
		}, function () {
			cancelUpload = true;
			error = true;
		});
	},

	url: function url() {
		var _url = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : "";

		var albumID = album.getID();

		_url = typeof _url === "string" ? _url : "";

		if (albumID === false) albumID = 0;

		var action = function action(data) {
			var files = [];

			if (data.link && data.link.trim().length > 3) {
				basicModal.close();

				files[0] = {
					name: data.link
				};

				upload.show(lychee.locale["UPLOAD_IMPORTING_URL"], files, function () {
					$(".basicModal .rows .row .status").html(lychee.locale["UPLOAD_IMPORTING"]);

					var params = {
						url: data.link,
						albumID: albumID
					};

					api.post("Import::url", params, function (_data) {
						// Same code as in import.dropbox()

						if (_data !== true) {
							$(".basicModal .rows .row p.notice").html(lychee.locale["UPLOAD_IMPORT_WARN_ERR"]).show();

							$(".basicModal .rows .row .status").html(lychee.locale["UPLOAD_FINISHED"]).addClass("warning");

							// Show close button
							$(".basicModal #basicModal__action.hidden").show();

							// Log error
							lychee.error(null, params, _data);
						} else {
							basicModal.close();
						}

						upload.notify(lychee.locale["UPLOAD_IMPORT_COMPLETE"]);

						albums.refresh();

						if (album.getID() === false) lychee.goto("0");else album.load(albumID);
					});
				});
			} else basicModal.error("link");
		};

		basicModal.show({
			body: lychee.html(_templateObject77) + lychee.locale["UPLOAD_IMPORT_INSTR"] + (" <input class='text' name='link' type='text' placeholder='http://' value='" + _url + "'></p>"),
			buttons: {
				action: {
					title: lychee.locale["UPLOAD_IMPORT"],
					fn: action
				},
				cancel: {
					title: lychee.locale["CANCEL"],
					fn: basicModal.close
				}
			}
		});
	},

	server: function server() {
		var albumID = album.getID();
		if (albumID === false) albumID = 0;

		var action = function action(data) {
			if (!data.path.trim()) {
				basicModal.error("path");
				return;
			}

			var files = [];

			files[0] = {
				name: data.path
			};

			var delete_imported = $(choiceDeleteSelector).prop("checked") ? "1" : "0";
			var import_via_symlink = $(choiceSymlinkSelector).prop("checked") ? "1" : "0";
			var skip_duplicates = $(choiceDuplicateSelector).prop("checked") ? "1" : "0";
			var resync_metadata = $(choiceResyncSelector).prop("checked") ? "1" : "0";
			var cancelUpload = false;

			upload.show(lychee.locale["UPLOAD_IMPORT_SERVER"], files, function () {
				$(cancelSelector).show();
				$(".basicModal .rows .row .status").html(lychee.locale["UPLOAD_IMPORTING"]);

				var params = {
					albumID: albumID,
					path: data.path,
					delete_imported: delete_imported,
					import_via_symlink: import_via_symlink,
					skip_duplicates: skip_duplicates,
					resync_metadata: resync_metadata
				};

				// Variables holding state across the invocations of
				// processIncremental().
				var lastReadIdx = 0;
				var currentDir = data.path;
				var encounteredProblems = false;
				var topSkip = 0;

				// Worker function invoked from both the response progress
				// callback and the completion callback.
				var processIncremental = function processIncremental(jsonResponse) {
					// Skip the part that we've already processed during
					// the previous invocation(s).
					var newResponse = jsonResponse.substring(lastReadIdx);
					// Because of all the potential buffering along the way,
					// we can't be sure if the last line is complete.  For
					// that reason, our custom protocol terminates every
					// line with the newline character, including the last
					// line.
					var lastNewline = newResponse.lastIndexOf("\n");
					if (lastNewline === -1) {
						// No valid input data to process.
						return;
					}
					if (lastNewline !== newResponse.length - 1) {
						// Last line is not newline-terminated, so it
						// must be incomplete.  Strip it; it will be
						// handled during the next invocation.
						newResponse = newResponse.substring(0, lastNewline + 1);
					}
					// Advance the counter past the last valid character.
					lastReadIdx += newResponse.length;
					newResponse.split("\n").forEach(function (resp) {
						var matches = resp.match(/^Status: (.*): (\d+)$/);
						if (matches !== null) {
							if (matches[2] !== "100") {
								if (currentDir !== matches[1]) {
									// New directory.  Add a new line to
									// the dialog box.
									currentDir = matches[1];
									$(".basicModal .rows").append(build.uploadNewFile(currentDir));
									topSkip += $(lastRowSelector).outerHeight();
								}
								$(lastRowSelector + " .status").html(matches[2] + "%");
							} else {
								// Final status report for this directory.
								$(lastRowSelector + " .status").html(lychee.locale["UPLOAD_FINISHED"]).addClass("success");
							}
						} else if ((matches = resp.match(/^Problem: (.*): ([^:]*)$/)) !== null) {
							var rowSelector = void 0;
							if (currentDir !== matches[1]) {
								$(lastRowSelector).before(build.uploadNewFile(matches[1]));
								rowSelector = prelastRowSelector;
							} else {
								// The problem is with the directory
								// itself, so alter its existing line.
								rowSelector = lastRowSelector;
								topSkip -= $(rowSelector).outerHeight();
							}
							if (matches[2] === "Given path is not a directory" || matches[2] === "Given path is reserved") {
								$(rowSelector + " .status").html(lychee.locale["UPLOAD_FAILED"]).addClass("error");
							} else if (matches[2] === "Skipped duplicate (resynced metadata)") {
								$(rowSelector + " .status").html(lychee.locale["UPLOAD_UPDATED"]).addClass("warning");
							} else if (matches[2] === "Import cancelled") {
								$(rowSelector + " .status").html(lychee.locale["UPLOAD_CANCELLED"]).addClass("error");
							} else {
								$(rowSelector + " .status").html(lychee.locale["UPLOAD_SKIPPED"]).addClass("warning");
							}
							var translations = {
								"Given path is not a directory": "UPLOAD_IMPORT_NOT_A_DIRECTORY",
								"Given path is reserved": "UPLOAD_IMPORT_PATH_RESERVED",
								"Could not read file": "UPLOAD_IMPORT_UNREADABLE",
								"Could not import file": "UPLOAD_IMPORT_FAILED",
								"Unsupported file type": "UPLOAD_IMPORT_UNSUPPORTED",
								"Could not create album": "UPLOAD_IMPORT_ALBUM_FAILED",
								"Skipped duplicate": "UPLOAD_IMPORT_SKIPPED_DUPLICATE",
								"Skipped duplicate (resynced metadata)": "UPLOAD_IMPORT_RESYNCED_DUPLICATE",
								"Import cancelled": "UPLOAD_IMPORT_CANCELLED"
							};
							$(rowSelector + " .notice").html(matches[2] in translations ? lychee.locale[translations[matches[2]]] : matches[2]).show();
							topSkip += $(rowSelector).outerHeight();
							encounteredProblems = true;
						} else if (resp === "Warning: Approaching memory limit") {
							$(lastRowSelector).before(build.uploadNewFile(lychee.locale["UPLOAD_IMPORT_LOW_MEMORY"]));
							topSkip += $(prelastRowSelector).outerHeight();
							$(prelastRowSelector + " .status").html(lychee.locale["UPLOAD_WARNING"]).addClass("warning");
							$(prelastRowSelector + " .notice").html(lychee.locale["UPLOAD_IMPORT_LOW_MEMORY_EXPL"]).show();
						}
						$(".basicModal .rows").scrollTop(topSkip);
					}); // forEach (resp)
				}; // processIncremental

				api.post("Import::server", params, function (_data) {
					// _data is already JSON-parsed.
					processIncremental(_data);

					albums.refresh();

					upload.notify(lychee.locale["UPLOAD_IMPORT_COMPLETE"], encounteredProblems ? lychee.locale["UPLOAD_COMPLETE_FAILED"] : null);

					if (album.getID() === false) lychee.goto("0");else album.load(albumID);

					if (encounteredProblems) showCloseButton();else basicModal.close();
				}, function (event) {
					// We received a possibly partial response.
					// We need to begin by terminating the data with a
					// '"' so that it can be JSON-parsed.
					var response = this.response;
					if (response.length > 0) {
						if (response.substring(this.response.length - 1) === '"') {
							// This might be either a terminating '"'
							// or it may come from, say, a filename, in
							// which case it would be escaped.
							if (response.length > 1) {
								if (response.substring(this.response.length - 2) === '"') {
									response += '"';
								}
								// else it's a complete response,
								// requiring no termination from us.
							} else {
								// The response is just '"'.
								response += '"';
							}
						} else {
							// This should be the most common case for
							// partial responses.
							response += '"';
						}
					}
					// Parse the response as JSON.  This will remove
					// the surrounding '"' characters, unescape any '"'
					// from the middle, and translate '\n' sequences into
					// newlines.
					var jsonResponse = void 0;
					try {
						jsonResponse = JSON.parse(response);
					} catch (e) {
						// Most likely a SyntaxError due to something
						// that went wrong on the server side.
						$(lastRowSelector + " .status").html(lychee.locale["UPLOAD_FAILED"]).addClass("error");

						albums.refresh();
						upload.notify(lychee.locale["UPLOAD_COMPLETE"], lychee.locale["UPLOAD_COMPLETE_FAILED"]);

						if (album.getID() === false) lychee.goto("0");else album.load(albumID);

						showCloseButton();

						return;
					}
					// The rest of the work is the same as for the full
					// response.
					processIncremental(jsonResponse);
				}); // api.post
			}, function () {
				if (!cancelUpload) {
					api.post("Import::serverCancel", {}, function (resp) {
						if (resp === "true") cancelUpload = true;
					});
				}
			}); // upload.show
		}; // action

		var msg = lychee.html(_templateObject78, lychee.locale["UPLOAD_IMPORT_SERVER_INSTR"], lychee.locale["UPLOAD_ABSOLUTE_PATH"], lychee.location);
		msg += lychee.html(_templateObject79, build.iconic("check"), lychee.locale["UPLOAD_IMPORT_DELETE_ORIGINALS"], lychee.locale["UPLOAD_IMPORT_DELETE_ORIGINALS_EXPL"], build.iconic("check"), lychee.locale["UPLOAD_IMPORT_VIA_SYMLINK"], lychee.locale["UPLOAD_IMPORT_VIA_SYMLINK_EXPL"], build.iconic("check"), lychee.locale["UPLOAD_IMPORT_SKIP_DUPLICATES"], lychee.locale["UPLOAD_IMPORT_SKIP_DUPLICATES_EXPL"], build.iconic("check"), lychee.locale["UPLOAD_IMPORT_RESYNC_METADATA"], lychee.locale["UPLOAD_IMPORT_RESYNC_METADATA_EXPL"]);

		basicModal.show({
			body: msg,
			buttons: {
				action: {
					title: lychee.locale["UPLOAD_IMPORT"],
					fn: action
				},
				cancel: {
					title: lychee.locale["CANCEL"],
					fn: basicModal.close
				}
			}
		});

		var $delete = $(choiceDeleteSelector);
		var $symlinks = $(choiceSymlinkSelector);
		var $duplicates = $(choiceDuplicateSelector);
		var $resync = $(choiceResyncSelector);

		if (lychee.delete_imported) {
			$delete.prop("checked", true);
			$symlinks.prop("checked", false).prop("disabled", true);
		} else {
			if (lychee.import_via_symlink) {
				$symlinks.prop("checked", true);
				$delete.prop("checked", false).prop("disabled", true);
			}
		}
		if (lychee.skip_duplicates) {
			$duplicates.prop("checked", true);
			if (lychee.resync_metadata) $resync.prop("checked", true);
		} else {
			$resync.prop("disabled", true);
		}
	},

	dropbox: function dropbox() {
		var albumID = album.getID();
		if (albumID === false) albumID = 0;

		var success = function success(files) {
			var links = "";

			for (var i = 0; i < files.length; i++) {
				links += files[i].link + ",";

				files[i] = {
					name: files[i].link
				};
			}

			// Remove last comma
			links = links.substr(0, links.length - 1);

			upload.show("Importing from Dropbox", files, function () {
				$(".basicModal .rows .row .status").html(lychee.locale["UPLOAD_IMPORTING"]);

				var params = {
					url: links,
					albumID: albumID
				};

				api.post("Import::url", params, function (data) {
					// Same code as in import.url()

					if (data !== true) {
						$(".basicModal .rows .row p.notice").html(lychee.locale["UPLOAD_IMPORT_WARN_ERR"]).show();

						$(".basicModal .rows .row .status").html(lychee.locale["UPLOAD_FINISHED"]).addClass("warning");

						// Show close button
						$(".basicModal #basicModal__action.hidden").show();

						// Log error
						lychee.error(null, params, data);
					} else {
						basicModal.close();
					}

					upload.notify(lychee.locale["UPLOAD_IMPORT_COMPLETE"]);

					albums.refresh();

					if (album.getID() === false) lychee.goto("0");else album.load(albumID);
				});
			});
		};

		lychee.loadDropbox(function () {
			Dropbox.choose({
				linkType: "direct",
				multiselect: true,
				success: success
			});
		});
	}
};

upload.check = function () {
	var $delete = $(choiceDeleteSelector);
	var $symlinks = $(choiceSymlinkSelector);

	if ($delete.prop("checked")) {
		$symlinks.prop("checked", false).prop("disabled", true);
	} else {
		$symlinks.prop("disabled", false);
		if ($symlinks.prop("checked")) {
			$delete.prop("checked", false).prop("disabled", true);
		} else {
			$delete.prop("disabled", false);
		}
	}

	var $duplicates = $(choiceDuplicateSelector);
	var $resync = $(choiceResyncSelector);

	if ($duplicates.prop("checked")) {
		$resync.prop("disabled", false);
	} else {
		$resync.prop("checked", false).prop("disabled", true);
	}
};

var users = {
	json: null
};

users.update = function (params) {
	if (params.username.length < 1) {
		loadingBar.show("error", "new username cannot be empty.");
		return false;
	}

	if ($("#UserData" + params.id + ' .choice input[name="upload"]:checked').length === 1) {
		params.upload = "1";
	} else {
		params.upload = "0";
	}
	if ($("#UserData" + params.id + ' .choice input[name="lock"]:checked').length === 1) {
		params.lock = "1";
	} else {
		params.lock = "0";
	}

	api.post("User::Save", params, function (data) {
		if (data !== true) {
			loadingBar.show("error", data.description);
			lychee.error(null, params, data);
		} else {
			loadingBar.show("success", "User updated!");
			users.list(); // reload user list
		}
	});
};

users.create = function (params) {
	if (params.username.length < 1) {
		loadingBar.show("error", "new username cannot be empty.");
		return false;
	}
	if (params.password.length < 1) {
		loadingBar.show("error", "new password cannot be empty.");
		return false;
	}

	if ($('#UserCreate .choice input[name="upload"]:checked').length === 1) {
		params.upload = "1";
	} else {
		params.upload = "0";
	}
	if ($('#UserCreate .choice input[name="lock"]:checked').length === 1) {
		params.lock = "1";
	} else {
		params.lock = "0";
	}

	api.post("User::Create", params, function (data) {
		if (data !== true) {
			loadingBar.show("error", data.description);
			lychee.error(null, params, data);
		} else {
			loadingBar.show("success", "User created!");
			users.list(); // reload user list
		}
	});
};

users.delete = function (params) {
	api.post("User::Delete", params, function (data) {
		if (data !== true) {
			loadingBar.show("error", data.description);
			lychee.error(null, params, data);
		} else {
			loadingBar.show("success", "User deleted!");
			users.list(); // reload user list
		}
	});
};

users.list = function () {
	api.post("User::List", {}, function (data) {
		users.json = data;
		view.users.init();
	});
};

/**
 * @description Responsible to reflect data changes to the UI.
 */

var view = {};

view.albums = {
	init: function init() {
		multiselect.clearSelection();

		view.albums.title();
		view.albums.content.init();
	},

	title: function title() {
		if (lychee.landing_page_enable) {
			if (lychee.title !== "Lychee v4") {
				lychee.setTitle(lychee.title, false);
			} else {
				lychee.setTitle(lychee.locale["ALBUMS"], false);
			}
		} else {
			lychee.setTitle(lychee.locale["ALBUMS"], false);
		}
	},

	content: {
		scrollPosition: 0,

		init: function init() {
			var smartData = "";
			var albumsData = "";
			var sharedData = "";

			// Smart Albums
			if (albums.json.smartalbums != null) {
				if (lychee.publicMode === false) {
					smartData = build.divider(lychee.locale["SMART_ALBUMS"]);
				}
				if (albums.json.smartalbums.unsorted) {
					albums.parse(albums.json.smartalbums.unsorted);
					smartData += build.album(albums.json.smartalbums.unsorted);
				}
				if (albums.json.smartalbums.public) {
					albums.parse(albums.json.smartalbums.public);
					smartData += build.album(albums.json.smartalbums.public);
				}
				if (albums.json.smartalbums.starred) {
					albums.parse(albums.json.smartalbums.starred);
					smartData += build.album(albums.json.smartalbums.starred);
				}
				if (albums.json.smartalbums.recent) {
					albums.parse(albums.json.smartalbums.recent);
					smartData += build.album(albums.json.smartalbums.recent);
				}

				Object.entries(albums.json.smartalbums).forEach(function (_ref) {
					var _ref2 = _slicedToArray(_ref, 2),
					    albumName = _ref2[0],
					    albumData = _ref2[1];

					if (albumData["tag_album"] === "1") {
						albums.parse(albumData);
						smartData += build.album(albumData);
					}
				});
			}

			// Albums
			if (albums.json.albums && albums.json.albums.length !== 0) {
				$.each(albums.json.albums, function () {
					if (!this.parent_id || this.parent_id === 0) {
						albums.parse(this);
						albumsData += build.album(this);
					}
				});

				// Add divider
				if (lychee.publicMode === false) albumsData = build.divider(lychee.locale["ALBUMS"]) + albumsData;
			}

			var current_owner = "";
			var i = void 0;
			// Shared
			if (albums.json.shared_albums && albums.json.shared_albums.length !== 0) {
				for (i = 0; i < albums.json.shared_albums.length; ++i) {
					var alb = albums.json.shared_albums[i];
					if (!alb.parent_id || alb.parent_id === 0) {
						albums.parse(alb);
						if (current_owner !== alb.owner && lychee.publicMode === false) {
							sharedData += build.divider(alb.owner);
							current_owner = alb.owner;
						}
						sharedData += build.album(alb, !lychee.admin);
					}
				}
			}

			if (smartData === "" && albumsData === "" && sharedData === "") {
				lychee.content.html("");
				$("body").append(build.no_content("eye"));
			} else {
				lychee.content.html(smartData + albumsData + sharedData);
			}

			album.apply_nsfw_filter();
			// Restore scroll position
			if (view.albums.content.scrollPosition != null && view.albums.content.scrollPosition !== 0) {
				$(document).scrollTop(view.albums.content.scrollPosition);
			}
		},

		title: function title(albumID) {
			var title = albums.getByID(albumID).title;

			title = lychee.escapeHTML(title);

			$('.album[data-id="' + albumID + '"] .overlay h1').html(title).attr("title", title);
		},

		delete: function _delete(albumID) {
			$('.album[data-id="' + albumID + '"]').css("opacity", 0).animate({
				width: 0,
				marginLeft: 0
			}, 300, function () {
				$(this).remove();
				if (albums.json.albums.length <= 0) lychee.content.find(".divider:last-child").remove();
			});
		}
	}
};

view.album = {
	init: function init() {
		multiselect.clearSelection();

		album.parse();

		view.album.sidebar();
		view.album.title();
		view.album.public();
		view.album.nsfw();
		view.album.nsfw_warning.init();
		view.album.content.init();

		album.json.init = 1;
	},

	title: function title() {
		if ((visible.album() || !album.json.init) && !visible.photo()) {
			switch (album.getID()) {
				case "starred":
					lychee.setTitle(lychee.locale["STARRED"], true);
					break;
				case "public":
					lychee.setTitle(lychee.locale["PUBLIC"], true);
					break;
				case "recent":
					lychee.setTitle(lychee.locale["RECENT"], true);
					break;
				case "unsorted":
					lychee.setTitle(lychee.locale["UNSORTED"], true);
					break;
				default:
					if (album.json.init) _sidebar.changeAttr("title", album.json.title);
					lychee.setTitle(album.json.title, true);
					break;
			}
		}
	},

	nsfw_warning: {
		init: function init() {
			if (!lychee.nsfw_warning) {
				$("#sensitive_warning").hide();
				return;
			}

			if (album.json.nsfw && album.json.nsfw === "1" && !lychee.nsfw_unlocked_albums.includes(album.json.id)) {
				$("#sensitive_warning").show();
			} else {
				$("#sensitive_warning").hide();
			}
		},

		next: function next() {
			lychee.nsfw_unlocked_albums.push(album.json.id);
			$("#sensitive_warning").hide();
		}
	},

	content: {
		init: function init() {
			var photosData = "";
			var albumsData = "";
			var html = "";

			if (album.json.albums && album.json.albums !== false) {
				$.each(album.json.albums, function () {
					albums.parse(this);
					albumsData += build.album(this, !album.isUploadable());
				});
			}
			if (album.json.photos && album.json.photos !== false) {
				// Build photos
				$.each(album.json.photos, function () {
					photosData += build.photo(this, !album.isUploadable());
				});
			}

			if (photosData !== "") {
				if (lychee.layout === "1") {
					photosData = '<div class="justified-layout">' + photosData + "</div>";
				} else if (lychee.layout === "2") {
					photosData = '<div class="unjustified-layout">' + photosData + "</div>";
				}
			}

			if (albumsData !== "" && photosData !== "") {
				html = build.divider(lychee.locale["ALBUMS"]);
			}
			html += albumsData;
			if (albumsData !== "" && photosData !== "") {
				html += build.divider(lychee.locale["PHOTOS"]);
			}
			html += photosData;

			// Save and reset scroll position
			view.albums.content.scrollPosition = $(document).scrollTop();
			requestAnimationFrame(function () {
				return $(document).scrollTop(0);
			});

			// Add photos to view
			lychee.content.html(html);
			album.apply_nsfw_filter();

			view.album.content.justify();
		},

		title: function title(photoID) {
			var title = album.getByID(photoID).title;

			title = lychee.escapeHTML(title);

			$('.photo[data-id="' + photoID + '"] .overlay h1').html(title).attr("title", title);
		},

		titleSub: function titleSub(albumID) {
			var title = album.getSubByID(albumID).title;

			title = lychee.escapeHTML(title);

			$('.album[data-id="' + albumID + '"] .overlay h1').html(title).attr("title", title);
		},

		star: function star(photoID) {
			var $badge = $('.photo[data-id="' + photoID + '"] .icn-star');

			if (album.getByID(photoID).star === "1") $badge.addClass("badge--star");else $badge.removeClass("badge--star");
		},

		public: function _public(photoID) {
			var $badge = $('.photo[data-id="' + photoID + '"] .icn-share');

			if (album.getByID(photoID).public === "1") $badge.addClass("badge--visible badge--hidden");else $badge.removeClass("badge--visible badge--hidden");
		},

		cover: function cover(photoID) {
			$(".album .icn-cover").removeClass("badge--cover");
			$(".photo .icn-cover").removeClass("badge--cover");

			if (album.json.cover_id === photoID) {
				var badge = $('.photo[data-id="' + photoID + '"] .icn-cover');
				if (badge.length > 0) {
					badge.addClass("badge--cover");
				} else {
					$.each(album.json.albums, function () {
						if (this.thumb.id === photoID) {
							$('.album[data-id="' + this.id + '"] .icn-cover').addClass("badge--cover");
							return false;
						}
					});
				}
			}
		},

		updatePhoto: function updatePhoto(data) {
			var src = void 0,
			    srcset = "";

			// This mimicks the structure of build.photo
			if (lychee.layout === "0") {
				src = data.sizeVariants.thumb.url;
				if (data.sizeVariants.thumb2x !== null) {
					srcset = data.sizeVariants.thumb2x.url + " 2x";
				}
			} else {
				if (data.sizeVariants.small !== null) {
					src = data.sizeVariants.small.url;
					if (data.sizeVariants.small2x !== null) {
						srcset = data.sizeVariants.small.url + " " + data.sizeVariants.small.width + "w, " + data.sizeVariants.small2x.url + " " + data.sizeVariants.small2x.width + "w";
					}
				} else if (data.sizeVariants.medium !== null) {
					src = data.sizeVariants.medium.url;
					if (data.sizeVariants.medium2x !== null) {
						srcset = data.sizeVariants.medium.url + " " + data.sizeVariants.medium.width + "w, " + data.sizeVariants.medium2x.url + " " + data.sizeVariants.medium2x.width + "w";
					}
				} else if (!data.type || data.type.indexOf("video") !== 0) {
					src = data.url;
				} else {
					src = data.sizeVariants.thumb.url;
					if (data.sizeVariants.thumb2x !== null) {
						srcset = data.sizeVariants.thumb.url + " " + data.sizeVariants.thumb.width + "w, " + data.sizeVariants.thumb2x.url + " " + data.sizeVariants.thumb2x.width + "w";
					}
				}
			}

			$('.photo[data-id="' + data.id + '"] > span.thumbimg > img').attr("data-src", src).attr("data-srcset", srcset).addClass("lazyload");

			view.album.content.justify();
		},

		delete: function _delete(photoID) {
			var justify = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : false;

			$('.photo[data-id="' + photoID + '"]').css("opacity", 0).animate({
				width: 0,
				marginLeft: 0
			}, 300, function () {
				$(this).remove();
				// Only when search is not active
				if (album.json) {
					if (visible.sidebar()) {
						var videoCount = 0;
						$.each(album.json.photos, function () {
							if (this.type && this.type.indexOf("video") > -1) {
								videoCount++;
							}
						});
						if (album.json.photos.length - videoCount > 0) {
							_sidebar.changeAttr("images", album.json.photos.length - videoCount);
						} else {
							_sidebar.hideAttr("images");
						}
						if (videoCount > 0) {
							_sidebar.changeAttr("videos", videoCount);
						} else {
							_sidebar.hideAttr("videos");
						}
					}
					if (album.json.photos.length <= 0) {
						lychee.content.find(".divider").remove();
					}
					if (justify) {
						view.album.content.justify();
					}
				}
			});
		},

		deleteSub: function deleteSub(albumID) {
			$('.album[data-id="' + albumID + '"]').css("opacity", 0).animate({
				width: 0,
				marginLeft: 0
			}, 300, function () {
				$(this).remove();
				if (album.json) {
					if (album.json.albums.length <= 0) {
						lychee.content.find(".divider").remove();
					}
					if (visible.sidebar()) {
						if (album.json.albums.length > 0) {
							_sidebar.changeAttr("subalbums", album.json.albums.length);
						} else {
							_sidebar.hideAttr("subalbums");
						}
					}
				}
			});
		},

		justify: function justify() {
			if (!album.json || !album.json.photos || album.json.photos === false) return;
			if (lychee.layout === "1") {
				var containerWidth = parseFloat($(".justified-layout").width(), 10);
				if (containerWidth == 0) {
					// Triggered on Reload in photo view.
					containerWidth = $(window).width() - parseFloat($(".justified-layout").css("margin-left"), 10) - parseFloat($(".justified-layout").css("margin-right"), 10) - parseFloat($(".content").css("padding-right"), 10);
				}
				var ratio = [];
				$.each(album.json.photos, function (i) {
					ratio[i] = this.height > 0 ? this.width / this.height : 1;
					if (this.type && this.type.indexOf("video") > -1) {
						// Video.  If there's no small and medium, we have
						// to fall back to the square thumb.
						if (this.small === "" && this.medium === "") {
							ratio[i] = 1;
						}
					}
				});
				var layoutGeometry = require("justified-layout")(ratio, {
					containerWidth: containerWidth,
					containerPadding: 0,
					// boxSpacing: {
					//     horizontal: 42,
					//     vertical: 150
					// },
					targetRowHeight: parseFloat($(".photo").css("--lychee-default-height"), 10)
				});
				// if (lychee.admin) console.log(layoutGeometry);
				$(".justified-layout").css("height", layoutGeometry.containerHeight + "px");
				$(".justified-layout > div").each(function (i) {
					if (!layoutGeometry.boxes[i]) {
						// Race condition in search.find -- window content
						// and album.json can get out of sync as search
						// query is being modified.
						return false;
					}
					$(this).css("top", layoutGeometry.boxes[i].top);
					$(this).css("width", layoutGeometry.boxes[i].width);
					$(this).css("height", layoutGeometry.boxes[i].height);
					$(this).css("left", layoutGeometry.boxes[i].left);

					var imgs = $(this).find(".thumbimg > img");
					if (imgs.length > 0 && imgs[0].getAttribute("data-srcset")) {
						imgs[0].setAttribute("sizes", layoutGeometry.boxes[i].width + "px");
					}
				});
			} else if (lychee.layout === "2") {
				var _containerWidth = parseFloat($(".unjustified-layout").width(), 10);
				if (_containerWidth == 0) {
					// Triggered on Reload in photo view.
					_containerWidth = $(window).width() - parseFloat($(".unjustified-layout").css("margin-left"), 10) - parseFloat($(".unjustified-layout").css("margin-right"), 10) - parseFloat($(".content").css("padding-right"), 10);
				}
				// For whatever reason, the calculation of margin is
				// super-slow in Firefox (tested with 68), so we make sure to
				// do it just once, outside the loop.  Height doesn't seem to
				// be affected, but we do it the same way for consistency.
				var margin = parseFloat($(".photo").css("margin-right"), 10);
				var origHeight = parseFloat($(".photo").css("max-height"), 10);
				$(".unjustified-layout > div").each(function (i) {
					if (!album.json.photos[i]) {
						// Race condition in search.find -- window content
						// and album.json can get out of sync as search
						// query is being modified.
						return false;
					}
					var ratio = album.json.photos[i].height > 0 ? album.json.photos[i].width / album.json.photos[i].height : 1;
					if (album.json.photos[i].type && album.json.photos[i].type.indexOf("video") > -1) {
						// Video.  If there's no small and medium, we have
						// to fall back to the square thumb.
						if (album.json.photos[i].small === "" && album.json.photos[i].medium === "") {
							ratio = 1;
						}
					}

					var height = origHeight;
					var width = height * ratio;
					var imgs = $(this).find(".thumbimg > img");

					if (width > _containerWidth - margin) {
						width = _containerWidth - margin;
						height = width / ratio;
					}

					$(this).css("width", width + "px");
					$(this).css("height", height + "px");
					if (imgs.length > 0 && imgs[0].getAttribute("data-srcset")) {
						imgs[0].setAttribute("sizes", width + "px");
					}
				});
			}
		}
	},

	description: function description() {
		_sidebar.changeAttr("description", album.json.description);
	},

	show_tags: function show_tags() {
		_sidebar.changeAttr("show_tags", album.json.show_tags);
	},

	license: function license() {
		var license = void 0;
		switch (album.json.license) {
			case "none":
				license = ""; // none is displayed as - thus is empty.
				break;
			case "reserved":
				license = lychee.locale["ALBUM_RESERVED"];
				break;
			default:
				license = album.json.license;
				// console.log('default');
				break;
		}

		_sidebar.changeAttr("license", license);
	},

	public: function _public() {
		$("#button_visibility_album, #button_sharing_album_users").removeClass("active--not-hidden active--hidden");

		if (album.json.public === "1") {
			if (album.json.visible === "0") {
				$("#button_visibility_album, #button_sharing_album_users").addClass("active--hidden");
			} else {
				$("#button_visibility_album, #button_sharing_album_users").addClass("active--not-hidden");
			}

			$(".photo .iconic-share").remove();

			if (album.json.init) _sidebar.changeAttr("public", lychee.locale["ALBUM_SHR_YES"]);
		} else {
			if (album.json.init) _sidebar.changeAttr("public", lychee.locale["ALBUM_SHR_NO"]);
		}
	},

	hidden: function hidden() {
		if (album.json.visible === "1") _sidebar.changeAttr("hidden", lychee.locale["ALBUM_SHR_NO"]);else _sidebar.changeAttr("hidden", lychee.locale["ALBUM_SHR_YES"]);
	},

	nsfw: function nsfw() {
		if (album.json.nsfw === "1") {
			// Sensitive
			$("#button_nsfw_album").addClass("active").attr("title", lychee.locale["ALBUM_UNMARK_NSFW"]);
		} else {
			// Not Sensitive
			$("#button_nsfw_album").removeClass("active").attr("title", lychee.locale["ALBUM_MARK_NSFW"]);
		}
	},

	downloadable: function downloadable() {
		if (album.json.downloadable === "1") _sidebar.changeAttr("downloadable", lychee.locale["ALBUM_SHR_YES"]);else _sidebar.changeAttr("downloadable", lychee.locale["ALBUM_SHR_NO"]);
	},

	shareButtonVisible: function shareButtonVisible() {
		if (album.json.share_button_visible === "1") _sidebar.changeAttr("share_button_visible", lychee.locale["ALBUM_SHR_YES"]);else _sidebar.changeAttr("share_button_visible", lychee.locale["ALBUM_SHR_NO"]);
	},

	password: function password() {
		if (album.json.password === "1") _sidebar.changeAttr("password", lychee.locale["ALBUM_SHR_YES"]);else _sidebar.changeAttr("password", lychee.locale["ALBUM_SHR_NO"]);
	},

	sidebar: function sidebar() {
		if ((visible.album() || album.json && album.json.init) && !visible.photo()) {
			var structure = _sidebar.createStructure.album(album);
			var html = _sidebar.render(structure);

			_sidebar.dom(".sidebar__wrapper").html(html);
			_sidebar.bind();
		}
	}
};

view.photo = {
	init: function init(autoplay) {
		multiselect.clearSelection();

		_photo.parse();

		view.photo.sidebar();
		view.photo.title();
		view.photo.star();
		view.photo.public();
		view.photo.header();
		view.photo.photo(autoplay);

		_photo.json.init = 1;
	},

	show: function show() {
		// Change header
		lychee.content.addClass("view");
		header.setMode("photo");

		// Make body not scrollable
		// use bodyScrollLock package to enable locking on iOS
		// Simple overflow: hidden not working on iOS Safari
		// Only the info pane needs scrolling
		// Touch event for swiping of photo still work

		scrollLock.disablePageScroll($(".sidebar__wrapper").get());

		// Fullscreen
		var timeout = null;
		$(document).bind("mousemove", function () {
			clearTimeout(timeout);
			// For live Photos: header animtion only if LivePhoto is not playing
			if (!_photo.isLivePhotoPlaying() && lychee.header_auto_hide) {
				header.show();
				timeout = setTimeout(header.hideIfLivePhotoNotPlaying, 2500);
			}
		});

		// we also put this timeout to enable it by default when you directly click on a picture.
		if (lychee.header_auto_hide) {
			setTimeout(header.hideIfLivePhotoNotPlaying, 2500);
		}

		lychee.animate(lychee.imageview, "fadeIn");
	},

	hide: function hide() {
		header.show();

		lychee.content.removeClass("view");
		header.setMode("album");

		// Make body scrollable
		scrollLock.enablePageScroll($(".sidebar__wrapper").get());

		// Disable Fullscreen
		$(document).unbind("mousemove");
		if ($("video").length) {
			$("video")[$("video").length - 1].pause();
		}

		// Hide Photo
		lychee.animate(lychee.imageview, "fadeOut");
		setTimeout(function () {
			lychee.imageview.hide();
			view.album.sidebar();
		}, 300);
	},

	title: function title() {
		if (_photo.json.init) _sidebar.changeAttr("title", _photo.json.title);
		lychee.setTitle(_photo.json.title, true);
	},

	description: function description() {
		if (_photo.json.init) _sidebar.changeAttr("description", _photo.json.description);
	},

	license: function license() {
		var license = void 0;

		// Process key to display correct string
		switch (_photo.json.license) {
			case "none":
				license = ""; // none is displayed as - thus is empty (uniformity of the display).
				break;
			case "reserved":
				license = lychee.locale["PHOTO_RESERVED"];
				break;
			default:
				license = _photo.json.license;
				break;
		}

		// Update the sidebar if the photo is visible
		if (_photo.json.init) _sidebar.changeAttr("license", license);
	},

	star: function star() {
		if (_photo.json.star === "1") {
			// Starred
			$("#button_star").addClass("active").attr("title", lychee.locale["UNSTAR_PHOTO"]);
		} else {
			// Unstarred
			$("#button_star").removeClass("active").attr("title", lychee.locale["STAR_PHOTO"]);
		}
	},

	public: function _public() {
		$("#button_visibility").removeClass("active--hidden active--not-hidden");

		if (_photo.json.public === "1" || _photo.json.public === "2") {
			// Photo public
			if (_photo.json.public === "1") {
				$("#button_visibility").addClass("active--hidden");
			} else {
				$("#button_visibility").addClass("active--not-hidden");
			}

			if (_photo.json.init) _sidebar.changeAttr("public", lychee.locale["PHOTO_SHR_YES"]);
		} else {
			// Photo private
			if (_photo.json.init) _sidebar.changeAttr("public", "No");
		}
	},

	tags: function tags() {
		_sidebar.changeAttr("tags", build.tags(_photo.json.tags), true);
		_sidebar.bind();
	},

	photo: function photo(autoplay) {
		var ret = build.imageview(_photo.json, visible.header(), autoplay);
		lychee.imageview.html(ret.html);
		tabindex.makeFocusable(lychee.imageview);

		// Init Live Photo if needed
		if (_photo.isLivePhoto()) {
			// Package gives warning that function will be remove and
			// shoud be replaced by LivePhotosKit.augementElementAsPlayer
			// But, LivePhotosKit.augementElementAsPlayer is not yet available
			_photo.LivePhotosObject = LivePhotosKit.Player(document.getElementById("livephoto"));
		}

		view.photo.onresize();

		var $nextArrow = lychee.imageview.find("a#next");
		var $previousArrow = lychee.imageview.find("a#previous");
		var photoID = _photo.getID();
		var hasNext = album.json && album.json.photos && album.getByID(photoID) && album.getByID(photoID).nextPhoto != null && album.getByID(photoID).nextPhoto !== "";
		var hasPrevious = album.json && album.json.photos && album.getByID(photoID) && album.getByID(photoID).previousPhoto != null && album.getByID(photoID).previousPhoto !== "";

		var img = $("img#image");
		if (img.length > 0) {
			if (!img[0].complete || img[0].currentSrc !== null && img[0].currentSrc === "") {
				// Image is still loading.  Display the thumb version in the
				// background.
				if (ret.thumb !== "") {
					img.css("background-image", lychee.html(_templateObject80, ret.thumb));
				}

				// Don't preload next/prev until the requested image is
				// fully loaded.
				img.on("load", function () {
					_photo.preloadNextPrev(_photo.getID());
				});
			} else {
				_photo.preloadNextPrev(_photo.getID());
			}
		}

		if (hasNext === false || lychee.viewMode === true) {
			$nextArrow.hide();
		} else {
			var nextPhotoID = album.getByID(photoID).nextPhoto;
			var nextPhoto = album.getByID(nextPhotoID);

			// Check if thumbUrl exists (for videos w/o ffmpeg, we add a play-icon)
			var thumbUrl = "img/placeholder.png";
			if (nextPhoto.sizeVariants.thumb !== null) {
				thumbUrl = nextPhoto.sizeVariants.thumb.url;
			} else if (nextPhoto.type.indexOf("video") > -1) {
				thumbUrl = "img/play-icon.png";
			}
			$nextArrow.css("background-image", lychee.html(_templateObject81, thumbUrl));
		}

		if (hasPrevious === false || lychee.viewMode === true) {
			$previousArrow.hide();
		} else {
			var previousPhotoID = album.getByID(photoID).previousPhoto;
			var previousPhoto = album.getByID(previousPhotoID);

			// Check if thumbUrl exists (for videos w/o ffmpeg, we add a play-icon)
			var _thumbUrl = "img/placeholder.png";
			if (previousPhoto.sizeVariants.thumb !== null) {
				_thumbUrl = previousPhoto.sizeVariants.thumb.url;
			} else if (previousPhoto.type.indexOf("video") > -1) {
				_thumbUrl = "img/play-icon.png";
			}
			$previousArrow.css("background-image", lychee.html(_templateObject81, _thumbUrl));
		}
	},

	sidebar: function sidebar() {
		var structure = _sidebar.createStructure.photo(_photo.json);
		var html = _sidebar.render(structure);
		var has_location = _photo.json.latitude && _photo.json.longitude ? true : false;

		_sidebar.dom(".sidebar__wrapper").html(html);
		_sidebar.bind();

		if (has_location && lychee.map_display) {
			// Leaflet seaches for icon in same directoy as js file -> paths needs
			// to be overwritten
			delete L.Icon.Default.prototype._getIconUrl;
			L.Icon.Default.mergeOptions({
				iconRetinaUrl: "img/marker-icon-2x.png",
				iconUrl: "img/marker-icon.png",
				shadowUrl: "img/marker-shadow.png"
			});

			var mymap = L.map("leaflet_map_single_photo").setView([_photo.json.latitude, _photo.json.longitude], 13);

			L.tileLayer(map_provider_layer_attribution[lychee.map_provider].layer, {
				attribution: map_provider_layer_attribution[lychee.map_provider].attribution
			}).addTo(mymap);

			if (!lychee.map_display_direction || !_photo.json.imgDirection || _photo.json.imgDirection === "") {
				// Add Marker to map, direction is not set
				L.marker([_photo.json.latitude, _photo.json.longitude]).addTo(mymap);
			} else {
				// Add Marker, direction has been set
				var viewDirectionIcon = L.icon({
					iconUrl: "img/view-angle-icon.png",
					iconRetinaUrl: "img/view-angle-icon-2x.png",
					iconSize: [100, 58], // size of the icon
					iconAnchor: [50, 49] // point of the icon which will correspond to marker's location
				});
				var marker = L.marker([_photo.json.latitude, _photo.json.longitude], { icon: viewDirectionIcon }).addTo(mymap);
				marker.setRotationAngle(_photo.json.imgDirection);
			}
		}
	},

	header: function header() {
		if (_photo.json.type && (_photo.json.type.indexOf("video") === 0 || _photo.json.type === "raw") || _photo.json.livePhotoUrl !== "" && _photo.json.livePhotoUrl !== null) {
			$("#button_rotate_cwise, #button_rotate_ccwise").hide();
		} else {
			$("#button_rotate_cwise, #button_rotate_ccwise").show();
		}
	},

	onresize: function onresize() {
		if (!_photo.json || _photo.json.sizeVariants.medium === null || _photo.json.sizeVariants.medium2x === null) return;

		// Calculate the width of the image in the current window without
		// borders and set 'sizes' to it.
		var imgWidth = _photo.json.sizeVariants.medium.width;
		var imgHeight = _photo.json.sizeVariants.medium.height;
		var containerWidth = $(window).outerWidth();
		var containerHeight = $(window).outerHeight();

		// Image can be no larger than its natural size, but it can be
		// smaller depending on the size of the window.
		var width = imgWidth < containerWidth ? imgWidth : containerWidth;
		var height = width * imgHeight / imgWidth;
		if (height > containerHeight) {
			width = containerHeight * imgWidth / imgHeight;
		}

		$("img#image").attr("sizes", width + "px");
	}
};

view.settings = {
	init: function init() {
		multiselect.clearSelection();

		view.photo.hide();
		view.settings.title();
		header.setMode("config");
		view.settings.content.init();
	},

	title: function title() {
		lychee.setTitle(lychee.locale["SETTINGS"], false);
	},

	clearContent: function clearContent() {
		lychee.content.html('<div class="settings_view"></div>');
	},

	content: {
		init: function init() {
			view.settings.clearContent();
			view.settings.content.setLogin();
			if (lychee.admin) {
				view.settings.content.setSorting();
				view.settings.content.setDropboxKey();
				view.settings.content.setLang();
				view.settings.content.setDefaultLicense();
				view.settings.content.setLayout();
				view.settings.content.setPublicSearch();
				view.settings.content.setOverlayType();
				view.settings.content.setMapDisplay();
				view.settings.content.setNSFWVisible();
				view.settings.content.setCSS();
				view.settings.content.moreButton();
			}
		},

		setLogin: function setLogin() {
			var msg = "\n\t\t\t<div class=\"setLogin\">\n\t\t\t  <p>\n\t\t\t\t  " + lychee.locale["PASSWORD_TITLE"] + "\n\t\t\t\t  <input name='oldUsername' class='text' type='text' placeholder='" + lychee.locale["USERNAME_CURRENT"] + "' value=''>\n\t\t\t\t  <input name='oldPassword' class='text' type='password' placeholder='" + lychee.locale["PASSWORD_CURRENT"] + "' value=''>\n\t\t\t  </p>\n\t\t\t  <p>\n\t\t\t\t  " + lychee.locale["PASSWORD_TEXT"] + "\n\t\t\t\t  <input name='username' class='text' type='text' placeholder='" + lychee.locale["LOGIN_USERNAME"] + "' value=''>\n\t\t\t\t  <input name='password' class='text' type='password' placeholder='" + lychee.locale["LOGIN_PASSWORD"] + "' value=''>\n\t\t\t\t  <input name='confirm' class='text' type='password' placeholder='" + lychee.locale["LOGIN_PASSWORD_CONFIRM"] + "' value=''>\n\t\t\t  </p>\n\t\t\t<div class=\"basicModal__buttons\">\n\t\t\t\t<!--<a id=\"basicModal__cancel\" class=\"basicModal__button \">Cancel</a>-->\n\t\t\t\t<a id=\"basicModal__action_password_change\" class=\"basicModal__button \">" + lychee.locale["PASSWORD_CHANGE"] + "</a>\n\t\t\t</div>\n\t\t\t</div>";

			$(".settings_view").append(msg);

			settings.bind("#basicModal__action_password_change", ".setLogin", settings.changeLogin);
		},

		clearLogin: function clearLogin() {
			$("input[name=oldUsername], input[name=oldPassword], input[name=username], input[name=password], input[name=confirm]").val("");
		},

		setSorting: function setSorting() {
			var sortingPhotos = [];
			var sortingAlbums = [];

			var msg = "\n\t\t\t<div class=\"setSorting\">\n\t\t\t  <p>" + lychee.locale["SORT_ALBUM_BY_1"] + "\n\t\t\t\t  <span class=\"select\">\n\t\t\t\t\t  <select id=\"settings_albums_type\" name=\"typeAlbums\">\n\t\t\t\t\t\t  <option value='id'>" + lychee.locale["SORT_ALBUM_SELECT_1"] + "</option>\n\t\t\t\t\t\t  <option value='title'>" + lychee.locale["SORT_ALBUM_SELECT_2"] + "</option>\n\t\t\t\t\t\t  <option value='description'>" + lychee.locale["SORT_ALBUM_SELECT_3"] + "</option>\n\t\t\t\t\t\t  <option value='public'>" + lychee.locale["SORT_ALBUM_SELECT_4"] + "</option>\n\t\t\t\t\t\t  <option value='max_taken_at'>" + lychee.locale["SORT_ALBUM_SELECT_5"] + "</option>\n\t\t\t\t\t\t  <option value='min_taken_at'>" + lychee.locale["SORT_ALBUM_SELECT_6"] + "</option>\n\t\t\t\t\t  </select>\n\t\t\t\t  </span>\n\t\t\t\t  " + lychee.locale["SORT_ALBUM_BY_2"] + "\n\t\t\t\t  <span class=\"select\">\n\t\t\t\t\t  <select id=\"settings_albums_order\" name=\"orderAlbums\">\n\t\t\t\t\t\t  <option value='ASC'>" + lychee.locale["SORT_ASCENDING"] + "</option>\n\t\t\t\t\t\t  <option value='DESC'>" + lychee.locale["SORT_DESCENDING"] + "</option>\n\t\t\t\t\t  </select>\n\t\t\t\t  </span>\n\t\t\t\t  " + lychee.locale["SORT_ALBUM_BY_3"] + "\n\t\t\t  </p>\n\t\t\t  <p>" + lychee.locale["SORT_PHOTO_BY_1"] + "\n\t\t\t\t  <span class=\"select\">\n\t\t\t\t\t  <select id=\"settings_photos_type\" name=\"typePhotos\">\n\t\t\t\t\t\t  <option value='id'>" + lychee.locale["SORT_PHOTO_SELECT_1"] + "</option>\n\t\t\t\t\t\t  <option value='taken_at'>" + lychee.locale["SORT_PHOTO_SELECT_2"] + "</option>\n\t\t\t\t\t\t  <option value='title'>" + lychee.locale["SORT_PHOTO_SELECT_3"] + "</option>\n\t\t\t\t\t\t  <option value='description'>" + lychee.locale["SORT_PHOTO_SELECT_4"] + "</option>\n\t\t\t\t\t\t  <option value='public'>" + lychee.locale["SORT_PHOTO_SELECT_5"] + "</option>\n\t\t\t\t\t\t  <option value='star'>" + lychee.locale["SORT_PHOTO_SELECT_6"] + "</option>\n\t\t\t\t\t\t  <option value='type'>" + lychee.locale["SORT_PHOTO_SELECT_7"] + "</option>\n\t\t\t\t\t  </select>\n\t\t\t\t  </span>\n\t\t\t\t  " + lychee.locale["SORT_PHOTO_BY_2"] + "\n\t\t\t\t  <span class=\"select\">\n\t\t\t\t\t  <select id=\"settings_photos_order\" name=\"orderPhotos\">\n\t\t\t\t\t\t  <option value='ASC'>" + lychee.locale["SORT_ASCENDING"] + "</option>\n\t\t\t\t\t\t  <option value='DESC'>" + lychee.locale["SORT_DESCENDING"] + "</option>\n\t\t\t\t\t  </select>\n\t\t\t\t  </span>\n\t\t\t\t  " + lychee.locale["SORT_PHOTO_BY_3"] + "\n\t\t\t  </p>\n\t\t\t\t<div class=\"basicModal__buttons\">\n\t\t\t\t\t<!--<a id=\"basicModal__cancel\" class=\"basicModal__button \">Cancel</a>-->\n\t\t\t\t\t<a id=\"basicModal__action_sorting_change\" class=\"basicModal__button \">" + lychee.locale["SORT_CHANGE"] + "</a>\n\t\t\t\t</div>\n\t\t\t  </div>\n\t\t\t  ";

			$(".settings_view").append(msg);

			if (lychee.sortingAlbums !== "") {
				sortingAlbums = lychee.sortingAlbums.replace("ORDER BY ", "").split(" ");

				$(".setSorting select#settings_albums_type").val(sortingAlbums[0]);
				$(".setSorting select#settings_albums_order").val(sortingAlbums[1]);
			}

			if (lychee.sortingPhotos !== "") {
				sortingPhotos = lychee.sortingPhotos.replace("ORDER BY ", "").split(" ");

				$(".setSorting select#settings_photos_type").val(sortingPhotos[0]);
				$(".setSorting select#settings_photos_order").val(sortingPhotos[1]);
			}

			settings.bind("#basicModal__action_sorting_change", ".setSorting", settings.changeSorting);
		},

		setDropboxKey: function setDropboxKey() {
			var msg = "\n\t\t\t<div class=\"setDropBox\">\n\t\t\t  <p>" + lychee.locale["DROPBOX_TEXT"] + "\n\t\t\t  <input class='text' name='key' type='text' placeholder='Dropbox API Key' value='" + lychee.dropboxKey + "'>\n\t\t\t  </p>\n\t\t\t\t<div class=\"basicModal__buttons\">\n\t\t\t\t\t<a id=\"basicModal__action_dropbox_change\" class=\"basicModal__button\">" + lychee.locale["DROPBOX_TITLE"] + "</a>\n\t\t\t\t</div>\n\t\t\t  </div>\n\t\t\t  ";

			$(".settings_view").append(msg);
			settings.bind("#basicModal__action_dropbox_change", ".setDropBox", settings.changeDropboxKey);
		},

		setLang: function setLang() {
			var msg = "\n\t\t\t<div class=\"setLang\">\n\t\t\t<p>" + lychee.locale["LANG_TEXT"] + "\n\t\t\t  <span class=\"select\">\n\t\t\t\t  <select id=\"settings_photos_order\" name=\"lang\">";
			var i = 0;
			while (i < lychee.lang_available.length) {
				var lang_av = lychee.lang_available[i];
				msg += "<option " + (lychee.lang === lang_av ? "selected" : "") + ">" + lang_av + "</option>";
				i += 1;
			}
			msg += "\n\t\t\t\t  </select>\n\t\t\t  </span>\n\t\t\t</p>\n\t\t\t<div class=\"basicModal__buttons\">\n\t\t\t\t<a id=\"basicModal__action_set_lang\" class=\"basicModal__button\">" + lychee.locale["LANG_TITLE"] + "</a>\n\t\t\t</div>\n\t\t\t</div>";

			$(".settings_view").append(msg);
			settings.bind("#basicModal__action_set_lang", ".setLang", settings.changeLang);
		},

		setDefaultLicense: function setDefaultLicense() {
			var msg = "\n\t\t\t<div class=\"setDefaultLicense\">\n\t\t\t<p>" + lychee.locale["DEFAULT_LICENSE"] + "\n\t\t\t<span class=\"select\" style=\"width:270px\">\n\t\t\t\t<select name=\"license\" id=\"license\">\n\t\t\t\t\t<option value=\"none\">" + lychee.locale["PHOTO_LICENSE_NONE"] + "</option>\n\t\t\t\t\t<option value=\"reserved\">" + lychee.locale["PHOTO_RESERVED"] + "</option>\n\t\t\t\t\t<option value=\"CC0\">CC0 - Public Domain</option>\n\t\t\t\t\t<option value=\"CC-BY-1.0\">CC Attribution 1.0</option>\n\t\t\t\t\t<option value=\"CC-BY-2.0\">CC Attribution 2.0</option>\n\t\t\t\t\t<option value=\"CC-BY-2.5\">CC Attribution 2.5</option>\n\t\t\t\t\t<option value=\"CC-BY-3.0\">CC Attribution 3.0</option>\n\t\t\t\t\t<option value=\"CC-BY-4.0\">CC Attribution 4.0</option>\n\t\t\t\t\t<option value=\"CC-BY-ND-1.0\">CC Attribution-NoDerivatives 1.0</option>\n\t\t\t\t\t<option value=\"CC-BY-ND-2.0\">CC Attribution-NoDerivatives 2.0</option>\n\t\t\t\t\t<option value=\"CC-BY-ND-2.5\">CC Attribution-NoDerivatives 2.5</option>\n\t\t\t\t\t<option value=\"CC-BY-ND-3.0\">CC Attribution-NoDerivatives 3.0</option>\n\t\t\t\t\t<option value=\"CC-BY-ND-4.0\">CC Attribution-NoDerivatives 4.0</option>\n\t\t\t\t\t<option value=\"CC-BY-SA-1.0\">CC Attribution-ShareAlike 1.0</option>\n\t\t\t\t\t<option value=\"CC-BY-SA-2.0\">CC Attribution-ShareAlike 2.0</option>\n\t\t\t\t\t<option value=\"CC-BY-SA-2.5\">CC Attribution-ShareAlike 2.5</option>\n\t\t\t\t\t<option value=\"CC-BY-SA-3.0\">CC Attribution-ShareAlike 3.0</option>\n\t\t\t\t\t<option value=\"CC-BY-SA-4.0\">CC Attribution-ShareAlike 4.0</option>\n\t\t\t\t\t<option value=\"CC-BY-NC-1.0\">CC Attribution-NonCommercial 1.0</option>\n\t\t\t\t\t<option value=\"CC-BY-NC-2.0\">CC Attribution-NonCommercial 2.0</option>\n\t\t\t\t\t<option value=\"CC-BY-NC-2.5\">CC Attribution-NonCommercial 2.5</option>\n\t\t\t\t\t<option value=\"CC-BY-NC-3.0\">CC Attribution-NonCommercial 3.0</option>\n\t\t\t\t\t<option value=\"CC-BY-NC-4.0\">CC Attribution-NonCommercial 4.0</option>\n\t\t\t\t\t<option value=\"CC-BY-NC-ND-1.0\">CC Attribution-NonCommercial-NoDerivatives 1.0</option>\n\t\t\t\t\t<option value=\"CC-BY-NC-ND-2.0\">CC Attribution-NonCommercial-NoDerivatives 2.0</option>\n\t\t\t\t\t<option value=\"CC-BY-NC-ND-2.5\">CC Attribution-NonCommercial-NoDerivatives 2.5</option>\n\t\t\t\t\t<option value=\"CC-BY-NC-ND-3.0\">CC Attribution-NonCommercial-NoDerivatives 3.0</option>\n\t\t\t\t\t<option value=\"CC-BY-NC-ND-4.0\">CC Attribution-NonCommercial-NoDerivatives 4.0</option>\n\t\t\t\t\t<option value=\"CC-BY-NC-SA-1.0\">CC Attribution-NonCommercial-ShareAlike 1.0</option>\n\t\t\t\t\t<option value=\"CC-BY-NC-SA-2.0\">CC Attribution-NonCommercial-ShareAlike 2.0</option>\n\t\t\t\t\t<option value=\"CC-BY-NC-SA-2.5\">CC Attribution-NonCommercial-ShareAlike 2.5</option>\n\t\t\t\t\t<option value=\"CC-BY-NC-SA-3.0\">CC Attribution-NonCommercial-ShareAlike 3.0</option>\n\t\t\t\t\t<option value=\"CC-BY-NC-SA-4.0\">CC Attribution-NonCommercial-ShareAlike 4.0</option>\n\t\t\t\t</select>\n\t\t\t</span>\n\t\t\t<br />\n\t\t\t<a href=\"https://creativecommons.org/choose/\" target=\"_blank\">" + lychee.locale["PHOTO_LICENSE_HELP"] + "</a>\n\t\t\t</p>\n\t\t\t<div class=\"basicModal__buttons\">\n\t\t\t\t<a id=\"basicModal__action_set_license\" class=\"basicModal__button\">" + lychee.locale["SET_LICENSE"] + "</a>\n\t\t\t</div>\n\t\t\t</div>\n\t\t\t";
			$(".settings_view").append(msg);
			$("select#license").val(lychee.default_license === "" ? "none" : lychee.default_license);
			settings.bind("#basicModal__action_set_license", ".setDefaultLicense", settings.setDefaultLicense);
		},

		setLayout: function setLayout() {
			var msg = "\n\t\t\t<div class=\"setLayout\">\n\t\t\t<p>" + lychee.locale["LAYOUT_TYPE"] + "\n\t\t\t<span class=\"select\" style=\"width:270px\">\n\t\t\t\t<select name=\"layout\" id=\"layout\">\n\t\t\t\t\t<option value=\"0\">" + lychee.locale["LAYOUT_SQUARES"] + "</option>\n\t\t\t\t\t<option value=\"1\">" + lychee.locale["LAYOUT_JUSTIFIED"] + "</option>\n\t\t\t\t\t<option value=\"2\">" + lychee.locale["LAYOUT_UNJUSTIFIED"] + "</option>\n\t\t\t\t</select>\n\t\t\t</span>\n\t\t\t</p>\n\t\t\t<div class=\"basicModal__buttons\">\n\t\t\t\t<a id=\"basicModal__action_set_layout\" class=\"basicModal__button\">" + lychee.locale["SET_LAYOUT"] + "</a>\n\t\t\t</div>\n\t\t\t</div>\n\t\t\t";
			$(".settings_view").append(msg);
			$("select#layout").val(lychee.layout);
			settings.bind("#basicModal__action_set_layout", ".setLayout", settings.setLayout);
		},

		setPublicSearch: function setPublicSearch() {
			var msg = "\n\t\t\t<div class=\"setPublicSearch\">\n\t\t\t<p>" + lychee.locale["PUBLIC_SEARCH_TEXT"] + "\n\t\t\t<label class=\"switch\">\n\t\t\t  <input id=\"PublicSearch\" type=\"checkbox\">\n\t\t\t  <span class=\"slider round\"></span>\n\t\t\t</label>\n\t\t\t</p>\n\t\t\t</div>\n\t\t\t";

			$(".settings_view").append(msg);
			if (lychee.public_search) $("#PublicSearch").click();

			settings.bind("#PublicSearch", ".setPublicSearch", settings.changePublicSearch);
		},

		setNSFWVisible: function setNSFWVisible() {
			var msg = "\n\t\t\t<div class=\"setNSFWVisible\">\n\t\t\t<p>" + lychee.locale["NSFW_VISIBLE_TEXT_1"] + "\n\t\t\t<label class=\"switch\">\n\t\t\t  <input id=\"NSFWVisible\" type=\"checkbox\">\n\t\t\t  <span class=\"slider round\"></span>\n\t\t\t</label></p>\n\t\t\t<p>" + lychee.locale["NSFW_VISIBLE_TEXT_2"] + "\n\t\t\t</p>\n\t\t\t</div>\n\t\t\t";

			$(".settings_view").append(msg);
			if (lychee.nsfw_visible_saved) {
				$("#NSFWVisible").click();
			}

			settings.bind("#NSFWVisible", ".setNSFWVisible", settings.changeNSFWVisible);
		},
		// TODO: extend to the other settings.

		setOverlayType: function setOverlayType() {
			var msg = "\n\t\t\t<div class=\"setOverlayType\">\n\t\t\t<p>" + lychee.locale["OVERLAY_TYPE"] + "\n\t\t\t<span class=\"select\" style=\"width:270px\">\n\t\t\t\t<select name=\"OverlayType\" id=\"ImgOverlayType\">\n\t\t\t\t\t<option value=\"exif\">" + lychee.locale["OVERLAY_EXIF"] + "</option>\n\t\t\t\t\t<option value=\"desc\">" + lychee.locale["OVERLAY_DESCRIPTION"] + "</option>\n\t\t\t\t\t<option value=\"date\">" + lychee.locale["OVERLAY_DATE"] + "</option>\n\t\t\t\t\t<option value=\"none\">" + lychee.locale["OVERLAY_NONE"] + "</option>\n\t\t\t\t</select>\n\t\t\t</span>\n\t\t\t<div class=\"basicModal__buttons\">\n\t\t\t\t<a id=\"basicModal__action_set_overlay_type\" class=\"basicModal__button\">" + lychee.locale["SET_OVERLAY_TYPE"] + "</a>\n\t\t\t</div>\n\t\t\t</div>\n\t\t\t";

			$(".settings_view").append(msg);

			$("select#ImgOverlayType").val(!lychee.image_overlay_type_default ? "exif" : lychee.image_overlay_type_default);
			settings.bind("#basicModal__action_set_overlay_type", ".setOverlayType", settings.setOverlayType);
		},

		setMapDisplay: function setMapDisplay() {
			var msg = "\n\t\t\t<div class=\"setMapDisplay\">\n\t\t\t<p>" + lychee.locale["MAP_DISPLAY_TEXT"] + "\n\t\t\t<label class=\"switch\">\n\t\t\t  <input id=\"MapDisplay\" type=\"checkbox\">\n\t\t\t  <span class=\"slider round\"></span>\n\t\t\t</label>\n\t\t\t</p>\n\t\t\t</div>\n\t\t\t";

			$(".settings_view").append(msg);
			if (lychee.map_display) $("#MapDisplay").click();

			settings.bind("#MapDisplay", ".setMapDisplay", settings.changeMapDisplay);

			msg = "\n\t\t\t<div class=\"setMapDisplayPublic\">\n\t\t\t<p>" + lychee.locale["MAP_DISPLAY_PUBLIC_TEXT"] + "\n\t\t\t<label class=\"switch\">\n\t\t\t\t<input id=\"MapDisplayPublic\" type=\"checkbox\">\n\t\t\t\t<span class=\"slider round\"></span>\n\t\t\t</label>\n\t\t\t</p>\n\t\t\t</div>\n\t\t\t";

			$(".settings_view").append(msg);
			if (lychee.map_display_public) $("#MapDisplayPublic").click();

			settings.bind("#MapDisplayPublic", ".setMapDisplayPublic", settings.changeMapDisplayPublic);

			msg = "\n\t\t\t<div class=\"setMapProvider\">\n\t\t\t<p>" + lychee.locale["MAP_PROVIDER"] + "\n\t\t\t<span class=\"select\" style=\"width:270px\">\n\t\t\t\t<select name=\"MapProvider\" id=\"MapProvider\">\n\t\t\t\t\t<option value=\"Wikimedia\">" + lychee.locale["MAP_PROVIDER_WIKIMEDIA"] + "</option>\n\t\t\t\t\t<option value=\"OpenStreetMap.org\">" + lychee.locale["MAP_PROVIDER_OSM_ORG"] + "</option>\n\t\t\t\t\t<option value=\"OpenStreetMap.de\">" + lychee.locale["MAP_PROVIDER_OSM_DE"] + "</option>\n\t\t\t\t\t<option value=\"OpenStreetMap.fr\">" + lychee.locale["MAP_PROVIDER_OSM_FR"] + "</option>\n\t\t\t\t\t<option value=\"RRZE\">" + lychee.locale["MAP_PROVIDER_RRZE"] + "</option>\n\t\t\t\t</select>\n\t\t\t</span>\n\t\t\t<div class=\"basicModal__buttons\">\n\t\t\t\t<a id=\"basicModal__action_set_map_provider\" class=\"basicModal__button\">" + lychee.locale["SET_MAP_PROVIDER"] + "</a>\n\t\t\t</div>\n\t\t\t</div>\n\t\t\t";

			$(".settings_view").append(msg);

			$("select#MapProvider").val(!lychee.map_provider ? "Wikimedia" : lychee.map_provider);
			settings.bind("#basicModal__action_set_map_provider", ".setMapProvider", settings.setMapProvider);

			msg = "\n\t\t\t<div class=\"setMapIncludeSubalbums\">\n\t\t\t<p>" + lychee.locale["MAP_INCLUDE_SUBALBUMS_TEXT"] + "\n\t\t\t<label class=\"switch\">\n\t\t\t  <input id=\"MapIncludeSubalbums\" type=\"checkbox\">\n\t\t\t  <span class=\"slider round\"></span>\n\t\t\t</label>\n\t\t\t</p>\n\t\t\t</div>\n\t\t\t";

			$(".settings_view").append(msg);
			if (lychee.map_include_subalbums) $("#MapIncludeSubalbums").click();

			settings.bind("#MapIncludeSubalbums", ".setMapIncludeSubalbums", settings.changeMapIncludeSubalbums);

			msg = "\n\t\t\t<div class=\"setLocationDecoding\">\n\t\t\t<p>" + lychee.locale["LOCATION_DECODING"] + "\n\t\t\t<label class=\"switch\">\n\t\t\t  <input id=\"LocationDecoding\" type=\"checkbox\">\n\t\t\t  <span class=\"slider round\"></span>\n\t\t\t</label>\n\t\t\t</p>\n\t\t\t</div>\n\t\t\t";

			$(".settings_view").append(msg);
			if (lychee.location_decoding) $("#LocationDecoding").click();

			settings.bind("#LocationDecoding", ".setLocationDecoding", settings.changeLocationDecoding);

			msg = "\n\t\t\t<div class=\"setLocationShow\">\n\t\t\t<p>" + lychee.locale["LOCATION_SHOW"] + "\n\t\t\t<label class=\"switch\">\n\t\t\t  <input id=\"LocationShow\" type=\"checkbox\">\n\t\t\t  <span class=\"slider round\"></span>\n\t\t\t</label>\n\t\t\t</p>\n\t\t\t</div>\n\t\t\t";

			$(".settings_view").append(msg);
			if (lychee.location_show) $("#LocationShow").click();

			settings.bind("#LocationShow", ".setLocationShow", settings.changeLocationShow);

			msg = "\n\t\t\t<div class=\"setLocationShowPublic\">\n\t\t\t<p>" + lychee.locale["LOCATION_SHOW_PUBLIC"] + "\n\t\t\t<label class=\"switch\">\n\t\t\t\t<input id=\"LocationShowPublic\" type=\"checkbox\">\n\t\t\t\t<span class=\"slider round\"></span>\n\t\t\t</label>\n\t\t\t</p>\n\t\t\t</div>\n\t\t\t";

			$(".settings_view").append(msg);
			if (lychee.location_show_public) $("#LocationShowPublic").click();

			settings.bind("#LocationShowPublic", ".setLocationShowPublic", settings.changeLocationShowPublic);
		},

		setCSS: function setCSS() {
			var msg = "\n\t\t\t<div class=\"setCSS\">\n\t\t\t<p>" + lychee.locale["CSS_TEXT"] + "</p>\n\t\t\t<textarea id=\"css\"></textarea>\n\t\t\t<div class=\"basicModal__buttons\">\n\t\t\t\t<a id=\"basicModal__action_set_css\" class=\"basicModal__button\">" + lychee.locale["CSS_TITLE"] + "</a>\n\t\t\t</div>\n\t\t\t</div>";

			$(".settings_view").append(msg);

			var css_addr = $($("link")[1]).attr("href");

			api.get(css_addr, function (data) {
				$("#css").html(data);
			});

			settings.bind("#basicModal__action_set_css", ".setCSS", settings.changeCSS);
		},

		moreButton: function moreButton() {
			var msg = lychee.html(_templateObject82, lychee.locale["MORE"]);

			$(".settings_view").append(msg);

			$("#basicModal__action_more").on("click", view.full_settings.init);
		}
	}
};

view.full_settings = {
	init: function init() {
		multiselect.clearSelection();

		view.full_settings.title();
		view.full_settings.content.init();
	},

	title: function title() {
		lychee.setTitle("Full Settings", false);
	},

	clearContent: function clearContent() {
		lychee.content.html('<div class="settings_view"></div>');
	},

	content: {
		init: function init() {
			view.full_settings.clearContent();

			api.post("Settings::getAll", {}, function (data) {
				var msg = lychee.html(_templateObject83, lychee.locale["SETTINGS_WARNING"]);

				var prev = "";
				$.each(data, function () {
					if (this.cat && prev !== this.cat) {
						msg += lychee.html(_templateObject84, this.cat);
						prev = this.cat;
					}
					// prevent 'null' string for empty values
					var val = this.value ? this.value : "";
					msg += lychee.html(_templateObject85, this.key, this.key, val);
				});

				msg += lychee.html(_templateObject86, lychee.locale["SAVE_RISK"]);
				$(".settings_view").append(msg);

				settings.bind("#FullSettingsSave_button", "#fullSettings", settings.save);

				$("#fullSettings").on("keypress", function (e) {
					settings.save_enter(e);
				});
			});
		}
	}
};

view.users = {
	init: function init() {
		multiselect.clearSelection();

		view.photo.hide();
		view.users.title();
		header.setMode("config");
		view.users.content.init();
	},

	title: function title() {
		lychee.setTitle("Users", false);
	},

	clearContent: function clearContent() {
		lychee.content.html('<div class="users_view"></div>');
	},

	content: {
		init: function init() {
			view.users.clearContent();

			if (users.json.length === 0) {
				$(".users_view").append('<div class="users_view_line" style="margin-bottom: 50px;"><p style="text-align: center">User list is empty!</p></div>');
			}

			var html = "";

			html += '<div class="users_view_line">' + "<p>" + '<span class="text">username</span>' + '<span class="text">new password</span>' + '<span class="text_icon" title="Allow uploads">' + build.iconic("data-transfer-upload") + "</span>" + '<span class="text_icon" title="Restricted account">' + build.iconic("lock-locked") + "</span>" + "</p>" + "</div>";

			$(".users_view").append(html);

			$.each(users.json, function () {
				$(".users_view").append(build.user(this));
				settings.bind("#UserUpdate" + this.id, "#UserData" + this.id, users.update);
				settings.bind("#UserDelete" + this.id, "#UserData" + this.id, users.delete);
				if (this.upload === 1) {
					$("#UserData" + this.id + ' .choice input[name="upload"]').click();
				}
				if (this.lock === 1) {
					$("#UserData" + this.id + ' .choice input[name="lock"]').click();
				}
			});

			html = '<div class="users_view_line"';

			if (users.json.length === 0) {
				html += ' style="padding-top: 0px;"';
			}
			html += ">" + '<p id="UserCreate">' + '<input class="text" name="username" type="text" value="" placeholder="new username" /> ' + '<input class="text" name="password" type="text" placeholder="new password" /> ' + '<span class="choice" title="Allow uploads">' + "<label>" + '<input type="checkbox" name="upload" />' + '<span class="checkbox"><svg class="iconic "><use xlink:href="#check"></use></svg></span>' + "</label>" + "</span> " + '<span class="choice" title="Restricted account">' + "<label>" + '<input type="checkbox" name="lock" />' + '<span class="checkbox"><svg class="iconic "><use xlink:href="#check"></use></svg></span>' + "</label>" + "</span>" + "</p> " + '<a id="UserCreate_button"  class="basicModal__button basicModal__button_CREATE">Create</a>' + "</div>";
			$(".users_view").append(html);
			settings.bind("#UserCreate_button", "#UserCreate", users.create);
		}
	}
};

view.sharing = {
	init: function init() {
		multiselect.clearSelection();

		view.photo.hide();
		view.sharing.title();
		header.setMode("config");
		view.sharing.content.init();
	},

	title: function title() {
		lychee.setTitle("Sharing", false);
	},

	clearContent: function clearContent() {
		lychee.content.html('<div class="sharing_view"></div>');
	},

	content: {
		init: function init() {
			view.sharing.clearContent();

			if (sharing.json.shared.length === 0) {
				$(".sharing_view").append('<div class="sharing_view_line" style="margin-bottom: 50px;"><p style="text-align: center">Sharing list is empty!</p></div>');
			}

			var html = "";

			html += "\n\t\t\t<div class=\"sharing_view_line\"><p>Share</p></div>\n\t\t\t<div class=\"sharing_view_line\">\n\t\t\t\t<div class=\"col-xs-5\">\n\t\t\t\t\t<select name=\"from\" id=\"albums_list\" class=\"form-control select\" size=\"13\" multiple=\"multiple\">";

			$.each(sharing.json.albums, function () {
				html += "<option value=\"" + this.id + "\">" + this.title + "</option>";
			});

			html += "</select>\n\t\t\t\t</div>\n\n\t\t\t\t<div class=\"col-xs-2\">\n\t\t\t\t\t<!--<button type=\"button\" id=\"albums_list_undo\" class=\"btn btn-primary btn-block\">undo</button>-->\n\t\t\t\t\t<button type=\"button\" id=\"albums_list_rightAll\" class=\"btn btn-default btn-block blue\">" + build.iconic("media-skip-forward") + "</button>\n\t\t\t\t\t<button type=\"button\" id=\"albums_list_rightSelected\" class=\"btn btn-default btn-block blue\">" + build.iconic("chevron-right") + "</button>\n\t\t\t\t\t<button type=\"button\" id=\"albums_list_leftSelected\" class=\"btn btn-default btn-block grey\">" + build.iconic("chevron-left") + "</button>\n\t\t\t\t\t<button type=\"button\" id=\"albums_list_leftAll\" class=\"btn btn-default btn-block grey\">" + build.iconic("media-skip-backward") + "</button>\n\t\t\t\t\t<!--<button type=\"button\" id=\"albums_list_redo\" class=\"btn btn-warning btn-block\">redo</button>-->\n\t\t\t\t</div>\n\n\t\t\t\t<div class=\"col-xs-5\">\n\t\t\t\t\t<select name=\"to\" id=\"albums_list_to\" class=\"form-control select\" size=\"13\" multiple=\"multiple\"></select>\n\t\t\t\t</div>\n\t\t\t</div>";

			html += "\n\t\t\t<div class=\"sharing_view_line\"><p class=\"with\">with</p></div>\n\t\t\t<div class=\"sharing_view_line\">\n\t\t\t\t<div class=\"col-xs-5\">\n\t\t\t\t\t<select name=\"from\" id=\"user_list\" class=\"form-control select\" size=\"13\" multiple=\"multiple\">";

			$.each(sharing.json.users, function () {
				html += "<option value=\"" + this.id + "\">" + this.username + "</option>";
			});

			html += "</select>\n\t\t\t\t</div>\n\n\t\t\t\t<div class=\"col-xs-2\">\n\t\t\t\t\t<!--<button type=\"button\" id=\"user_list_undo\" class=\"btn btn-primary btn-block\">undo</button>-->\n\t\t\t\t\t<button type=\"button\" id=\"user_list_rightAll\" class=\"btn btn-default btn-block blue\">" + build.iconic("media-skip-forward") + "</button>\n\t\t\t\t\t<button type=\"button\" id=\"user_list_rightSelected\" class=\"btn btn-default btn-block blue\">" + build.iconic("chevron-right") + "</button>\n\t\t\t\t\t<button type=\"button\" id=\"user_list_leftSelected\" class=\"btn btn-default btn-block grey\">" + build.iconic("chevron-left") + "</button>\n\t\t\t\t\t<button type=\"button\" id=\"user_list_leftAll\" class=\"btn btn-default btn-block grey\">" + build.iconic("media-skip-backward") + "</button>\n\t\t\t\t\t<!--<button type=\"button\" id=\"user_list_redo\" class=\"btn btn-warning btn-block\">redo</button>-->\n\t\t\t\t</div>\n\n\t\t\t\t<div class=\"col-xs-5\">\n\t\t\t\t\t<select name=\"to\" id=\"user_list_to\" class=\"form-control select\" size=\"13\" multiple=\"multiple\"></select>\n\t\t\t\t</div>\n\t\t\t</div>";
			html += "<div class=\"sharing_view_line\"><a id=\"Share_button\"  class=\"basicModal__button\">Share</a></div>";
			html += '<div class="sharing_view_line">';

			$.each(sharing.json.shared, function () {
				html += "<p><span class=\"text\">" + this.title + "</span><span class=\"text\">" + this.username + '</span><span class="choice">' + "<label>" + '<input type="checkbox" name="remove_id" value="' + this.id + '"/>' + '<span class="checkbox"><svg class="iconic "><use xlink:href="#check"></use></svg></span>' + "</label>" + "</span></p>" + "";
			});

			html += "</div>";
			if (sharing.json.shared.length !== 0) {
				html += "<div class=\"sharing_view_line\"><a id=\"Remove_button\"  class=\"basicModal__button\">Remove</a></div>";
			}

			$(".sharing_view").append(html);

			$("#albums_list").multiselect();
			$("#user_list").multiselect();
			$("#Share_button").on("click", sharing.add).on("mouseenter", function () {
				$("#albums_list_to, #user_list_to").addClass("borderBlue");
			}).on("mouseleave", function () {
				$("#albums_list_to, #user_list_to").removeClass("borderBlue");
			});

			$("#Remove_button").on("click", sharing.delete);
		}
	}
};

view.logs = {
	init: function init() {
		multiselect.clearSelection();

		view.photo.hide();
		view.logs.title();
		header.setMode("config");
		view.logs.content.init();
	},

	title: function title() {
		lychee.setTitle("Logs", false);
	},

	clearContent: function clearContent() {
		var html = "";
		html += lychee.html(_templateObject87, lychee.locale["CLEAN_LOGS"]);
		html += '<pre class="logs_diagnostics_view"></pre>';
		lychee.content.html(html);

		$("#Clean_Noise").on("click", function () {
			api.post_raw("Logs::clearNoise", {}, function () {
				view.logs.init();
			});
		});
	},

	content: {
		init: function init() {
			view.logs.clearContent();
			api.post_raw("Logs", {}, function (data) {
				$(".logs_diagnostics_view").html(data);
			});
		}
	}
};

view.diagnostics = {
	init: function init() {
		multiselect.clearSelection();

		view.photo.hide();
		view.diagnostics.title("Diagnostics");
		header.setMode("config");
		view.diagnostics.content.init();
	},

	title: function title() {
		lychee.setTitle("Diagnostics", false);
	},

	clearContent: function clearContent(update) {
		var html = "";

		if (update === 2) {
			html += view.diagnostics.button("", lychee.locale["UPDATE_AVAILABLE"]);
		} else if (update === 3) {
			html += view.diagnostics.button("", lychee.locale["MIGRATION_AVAILABLE"]);
		} else if (update > 0) {
			html += view.diagnostics.button("Check_", lychee.locale["CHECK_FOR_UPDATE"]);
		}

		html += '<pre class="logs_diagnostics_view"></pre>';
		lychee.content.html(html);
	},

	button: function button(type, locale) {
		var html = "";
		html += '<div class="clear_logs_update">';
		html += lychee.html(_templateObject88, type, locale);
		html += "</div>";

		return html;
	},

	bind: function bind() {
		$("#Update_Lychee").on("click", view.diagnostics.call_apply_update);
		$("#Check_Update_Lychee").on("click", view.diagnostics.call_check_update);
	},

	content: {
		init: function init() {
			view.diagnostics.clearContent(false);

			view.diagnostics.content.v_2();
		},

		v_2: function v_2() {
			api.post("Diagnostics", {}, function (data) {
				view.diagnostics.clearContent(data.update);
				var html = "";

				html += view.diagnostics.content.block("error", "Diagnostics", data.errors);
				html += view.diagnostics.content.block("sys", "System Information", data.infos);
				html += '<a id="Get_Size_Lychee" class="basicModal__button button_left">';
				html += '<svg class="iconic"><use xlink:href="#reload"></use></svg>';
				html += lychee.html(_templateObject31, lychee.locale["DIAGNOSTICS_GET_SIZE"]);
				html += "</a>";
				html += view.diagnostics.content.block("conf", "Config Information", data.configs);

				$(".logs_diagnostics_view").html(html);

				view.diagnostics.bind();

				$("#Get_Size_Lychee").on("click", view.diagnostics.call_get_size);
			});
		},

		print_array: function print_array(arr) {
			var html = "";
			var i = void 0;

			for (i = 0; i < arr.length; i++) {
				html += "    " + arr[i] + "\n";
			}
			return html;
		},

		block: function block(id, title, arr) {
			var html = "";
			html += '<pre id="content_diag_' + id + '">\n\n\n\n';
			html += "    " + title + "\n";
			html += "    ".padEnd(title.length, "-") + "\n";
			html += view.diagnostics.content.print_array(arr);
			html += "</pre>\n";
			return html;
		}
	},

	call_check_update: function call_check_update() {
		api.post("Update::Check", [], function (data) {
			loadingBar.show("success", data);
			$("#Check_Update_Lychee").remove();
		});
	},

	call_apply_update: function call_apply_update() {
		api.post("Update::Apply", [], function (data) {
			var html = view.preify(data, "");
			$("#Update_Lychee").remove();
			$(html).prependTo(".logs_diagnostics_view");
		});
	},

	call_get_size: function call_get_size() {
		api.post("Diagnostics::getSize", [], function (data) {
			var html = view.preify(data, "");
			$("#Get_Size_Lychee").remove();
			$(html).appendTo("#content_diag_sys");
		});
	}
};

view.update = {
	init: function init() {
		multiselect.clearSelection();

		view.photo.hide();
		view.update.title();
		header.setMode("config");
		view.update.content.init();
	},

	title: function title() {
		lychee.setTitle("Update", false);
	},

	clearContent: function clearContent() {
		var html = "";
		html += '<pre class="logs_diagnostics_view"></pre>';
		lychee.content.html(html);
	},

	content: {
		init: function init() {
			view.update.clearContent();

			// code duplicate
			api.post("Update::Apply", [], function (data) {
				var html = view.preify(data, "logs_diagnostics_view");
				lychee.content.html(html);
			});
		}
	}
};

view.preify = function (data, css) {
	var html = '<pre class="' + css + '">';
	if (Array.isArray(data)) {
		for (var i = 0; i < data.length; i++) {
			html += "    " + data[i] + "\n";
		}
	} else {
		html += "    " + data;
	}
	html += "</pre>";

	return html;
};

view.u2f = {
	init: function init() {
		multiselect.clearSelection();

		view.photo.hide();
		view.u2f.title();
		header.setMode("config");
		view.u2f.content.init();
	},

	title: function title() {
		lychee.setTitle(lychee.locale["U2F"], false);
	},

	clearContent: function clearContent() {
		lychee.content.html('<div class="u2f_view"></div>');
	},

	content: {
		init: function init() {
			view.u2f.clearContent();

			var html = "";

			if (u2f.json.length === 0) {
				$(".u2f_view").append('<div class="u2f_view_line"><p class="single">Credentials list is empty!</p></div>');
			} else {
				html += '<div class="u2f_view_line">' + "<p>" + '<span class="text">' + lychee.locale["U2F_CREDENTIALS"] + "</span>" +
				// '<span class="text_icon" title="Allow uploads">' + build.iconic('data-transfer-upload') + '</span>' +
				// '<span class="text_icon" title="Restricted account">' + build.iconic('lock-locked') + '</span>' +
				"</p>" + "</div>";

				$(".u2f_view").append(html);

				$.each(u2f.json, function () {
					$(".u2f_view").append(build.u2f(this));
					settings.bind("#CredentialDelete" + this.id, "#CredentialData" + this.id, u2f.delete);
					// if (this.upload === 1) {
					//     $('#UserData' + this.id + ' .choice input[name="upload"]').click();
					// }
					// if (this.lock === 1) {
					//     $('#UserData' + this.id + ' .choice input[name="lock"]').click();
					// }
				});
			}

			html = '<div class="u2f_view_line"';

			// if (u2f.json.length === 0) {
			//     html += ' style="padding-top: 0px;"';
			// }
			html += ">" + '<a id="RegisterU2FButton"  class="basicModal__button basicModal__button_CREATE">' + lychee.locale["U2F_REGISTER_KEY"] + "</a>" + "</div>";
			$(".u2f_view").append(html);
			$("#RegisterU2FButton").on("click", u2f.register);
		}
	}
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
	if (_sidebar.dom().hasClass("active") === true) return true;
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

(function ($) {
	var Swipe = function Swipe(el) {
		var self = this;

		this.el = $(el);
		this.pos = { start: { x: 0, y: 0 }, end: { x: 0, y: 0 } };
		this.startTime = null;

		el.on("touchstart", function (e) {
			self.touchStart(e);
		});
		el.on("touchmove", function (e) {
			self.touchMove(e);
		});
		el.on("touchend", function () {
			self.swipeEnd();
		});
		el.on("mousedown", function (e) {
			self.mouseDown(e);
		});
	};

	Swipe.prototype = {
		touchStart: function touchStart(e) {
			var touch = e.originalEvent.touches[0];

			this.swipeStart(e, touch.pageX, touch.pageY);
		},

		touchMove: function touchMove(e) {
			var touch = e.originalEvent.touches[0];

			this.swipeMove(e, touch.pageX, touch.pageY);
		},

		mouseDown: function mouseDown(e) {
			var self = this;

			this.swipeStart(e, e.pageX, e.pageY);

			this.el.on("mousemove", function (_e) {
				self.mouseMove(_e);
			});
			this.el.on("mouseup", function () {
				self.mouseUp();
			});
		},

		mouseMove: function mouseMove(e) {
			this.swipeMove(e, e.pageX, e.pageY);
		},

		mouseUp: function mouseUp(e) {
			this.swipeEnd(e);

			this.el.off("mousemove");
			this.el.off("mouseup");
		},

		swipeStart: function swipeStart(e, x, y) {
			this.pos.start.x = x;
			this.pos.start.y = y;
			this.pos.end.x = x;
			this.pos.end.y = y;

			this.startTime = new Date().getTime();

			this.trigger("swipeStart", e);
		},

		swipeMove: function swipeMove(e, x, y) {
			this.pos.end.x = x;
			this.pos.end.y = y;

			this.trigger("swipeMove", e);
		},

		swipeEnd: function swipeEnd(e) {
			this.trigger("swipeEnd", e);
		},

		trigger: function trigger(e, originalEvent) {
			var self = this;

			var event = $.Event(e),
			    x = self.pos.start.x - self.pos.end.x,
			    y = self.pos.end.y - self.pos.start.y,
			    radians = Math.atan2(y, x),
			    direction = "up",
			    distance = Math.round(Math.sqrt(Math.pow(x, 2) + Math.pow(y, 2))),
			    angle = Math.round(radians * 180 / Math.PI),
			    speed = Math.round(distance / (new Date().getTime() - self.startTime) * 1000);

			if (angle < 0) {
				angle = 360 - Math.abs(angle);
			}

			if (angle <= 45 && angle >= 0 || angle <= 360 && angle >= 315) {
				direction = "left";
			} else if (angle >= 135 && angle <= 225) {
				direction = "right";
			} else if (angle > 45 && angle < 135) {
				direction = "down";
			}

			event.originalEvent = originalEvent;

			event.swipe = {
				x: x,
				y: y,
				direction: direction,
				distance: distance,
				angle: angle,
				speed: speed
			};

			$(self.el).trigger(event);
		}
	};

	$.fn.swipe = function () {
		// let swipe = new Swipe(this);
		new Swipe(this);

		return this;
	};
})(jQuery);

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