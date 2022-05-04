"use strict";

var _typeof = typeof Symbol === "function" && typeof Symbol.iterator === "symbol" ? function (obj) { return typeof obj; } : function (obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; };

var _templateObject = _taggedTemplateLiteral(["<p>", " <input class='text' name='title' type='text' maxlength='100' placeholder='Title' value='", "'></p>"], ["<p>", " <input class='text' name='title' type='text' maxlength='100' placeholder='Title' value='", "'></p>"]),
    _templateObject2 = _taggedTemplateLiteral(["<p>", "\n\t\t\t\t\t\t\t<input class='text' name='title' type='text' maxlength='100' placeholder='Title' value='", "'>\n\t\t\t\t\t\t\t<input class='text' name='tags' type='text' minlength='1' placeholder='Tags' value=''>\n\t\t\t\t\t\t</p>"], ["<p>", "\n\t\t\t\t\t\t\t<input class='text' name='title' type='text' maxlength='100' placeholder='Title' value='", "'>\n\t\t\t\t\t\t\t<input class='text' name='tags' type='text' minlength='1' placeholder='Tags' value=''>\n\t\t\t\t\t\t</p>"]),
    _templateObject3 = _taggedTemplateLiteral(["\n\t\t\t<p>", "\n\t\t\t\t<input\n\t\t\t\t\tclass='text'\n\t\t\t\t\tname='show_tags'\n\t\t\t\t\ttype='text'\n\t\t\t\t\tminlength='1'\n\t\t\t\t\tplaceholder='Tags'\n\t\t\t\t\tvalue='$", "'\n\t\t\t\t>\n\t\t\t</p>"], ["\n\t\t\t<p>", "\n\t\t\t\t<input\n\t\t\t\t\tclass='text'\n\t\t\t\t\tname='show_tags'\n\t\t\t\t\ttype='text'\n\t\t\t\t\tminlength='1'\n\t\t\t\t\tplaceholder='Tags'\n\t\t\t\t\tvalue='$", "'\n\t\t\t\t>\n\t\t\t</p>"]),
    _templateObject4 = _taggedTemplateLiteral(["<input class='text' name='title' type='text' maxlength='100' placeholder='$", "' value='$", "'>"], ["<input class='text' name='title' type='text' maxlength='100' placeholder='$", "' value='$", "'>"]),
    _templateObject5 = _taggedTemplateLiteral(["<p>", " ", "</p>"], ["<p>", " ", "</p>"]),
    _templateObject6 = _taggedTemplateLiteral(["<p>", " $", " ", " ", "</p>"], ["<p>", " $", " ", " ", "</p>"]),
    _templateObject7 = _taggedTemplateLiteral(["<p>", "<input class='text' name='description' type='text' maxlength='800' placeholder='$", "' value='$", "'></p>"], ["<p>", "<input class='text' name='description' type='text' maxlength='800' placeholder='$", "' value='$", "'></p>"]),
    _templateObject8 = _taggedTemplateLiteral(["\n\t<div>\n\t\t<p>", "\n\t\t<span class=\"select\" style=\"width:270px\">\n\t\t\t<select name=\"license\" id=\"license\">\n\t\t\t\t<option value=\"none\">", "</option>\n\t\t\t\t<option value=\"reserved\">", "</option>\n\t\t\t\t<option value=\"CC0\">CC0 - Public Domain</option>\n\t\t\t\t<option value=\"CC-BY-1.0\">CC Attribution 1.0</option>\n\t\t\t\t<option value=\"CC-BY-2.0\">CC Attribution 2.0</option>\n\t\t\t\t<option value=\"CC-BY-2.5\">CC Attribution 2.5</option>\n\t\t\t\t<option value=\"CC-BY-3.0\">CC Attribution 3.0</option>\n\t\t\t\t<option value=\"CC-BY-4.0\">CC Attribution 4.0</option>\n\t\t\t\t<option value=\"CC-BY-ND-1.0\">CC Attribution-NoDerivatives 1.0</option>\n\t\t\t\t<option value=\"CC-BY-ND-2.0\">CC Attribution-NoDerivatives 2.0</option>\n\t\t\t\t<option value=\"CC-BY-ND-2.5\">CC Attribution-NoDerivatives 2.5</option>\n\t\t\t\t<option value=\"CC-BY-ND-3.0\">CC Attribution-NoDerivatives 3.0</option>\n\t\t\t\t<option value=\"CC-BY-ND-4.0\">CC Attribution-NoDerivatives 4.0</option>\n\t\t\t\t<option value=\"CC-BY-SA-1.0\">CC Attribution-ShareAlike 1.0</option>\n\t\t\t\t<option value=\"CC-BY-SA-2.0\">CC Attribution-ShareAlike 2.0</option>\n\t\t\t\t<option value=\"CC-BY-SA-2.5\">CC Attribution-ShareAlike 2.5</option>\n\t\t\t\t<option value=\"CC-BY-SA-3.0\">CC Attribution-ShareAlike 3.0</option>\n\t\t\t\t<option value=\"CC-BY-SA-4.0\">CC Attribution-ShareAlike 4.0</option>\n\t\t\t\t<option value=\"CC-BY-NC-1.0\">CC Attribution-NonCommercial 1.0</option>\n\t\t\t\t<option value=\"CC-BY-NC-2.0\">CC Attribution-NonCommercial 2.0</option>\n\t\t\t\t<option value=\"CC-BY-NC-2.5\">CC Attribution-NonCommercial 2.5</option>\n\t\t\t\t<option value=\"CC-BY-NC-3.0\">CC Attribution-NonCommercial 3.0</option>\n\t\t\t\t<option value=\"CC-BY-NC-4.0\">CC Attribution-NonCommercial 4.0</option>\n\t\t\t\t<option value=\"CC-BY-NC-ND-1.0\">CC Attribution-NonCommercial-NoDerivatives 1.0</option>\n\t\t\t\t<option value=\"CC-BY-NC-ND-2.0\">CC Attribution-NonCommercial-NoDerivatives 2.0</option>\n\t\t\t\t<option value=\"CC-BY-NC-ND-2.5\">CC Attribution-NonCommercial-NoDerivatives 2.5</option>\n\t\t\t\t<option value=\"CC-BY-NC-ND-3.0\">CC Attribution-NonCommercial-NoDerivatives 3.0</option>\n\t\t\t\t<option value=\"CC-BY-NC-ND-4.0\">CC Attribution-NonCommercial-NoDerivatives 4.0</option>\n\t\t\t\t<option value=\"CC-BY-NC-SA-1.0\">CC Attribution-NonCommercial-ShareAlike 1.0</option>\n\t\t\t\t<option value=\"CC-BY-NC-SA-2.0\">CC Attribution-NonCommercial-ShareAlike 2.0</option>\n\t\t\t\t<option value=\"CC-BY-NC-SA-2.5\">CC Attribution-NonCommercial-ShareAlike 2.5</option>\n\t\t\t\t<option value=\"CC-BY-NC-SA-3.0\">CC Attribution-NonCommercial-ShareAlike 3.0</option>\n\t\t\t\t<option value=\"CC-BY-NC-SA-4.0\">CC Attribution-NonCommercial-ShareAlike 4.0</option>\n\t\t\t</select>\n\t\t</span>\n\t\t<br />\n\t\t<a href=\"https://creativecommons.org/choose/\" target=\"_blank\">", "</a>\n\t\t</p>\n\t</div>"], ["\n\t<div>\n\t\t<p>", "\n\t\t<span class=\"select\" style=\"width:270px\">\n\t\t\t<select name=\"license\" id=\"license\">\n\t\t\t\t<option value=\"none\">", "</option>\n\t\t\t\t<option value=\"reserved\">", "</option>\n\t\t\t\t<option value=\"CC0\">CC0 - Public Domain</option>\n\t\t\t\t<option value=\"CC-BY-1.0\">CC Attribution 1.0</option>\n\t\t\t\t<option value=\"CC-BY-2.0\">CC Attribution 2.0</option>\n\t\t\t\t<option value=\"CC-BY-2.5\">CC Attribution 2.5</option>\n\t\t\t\t<option value=\"CC-BY-3.0\">CC Attribution 3.0</option>\n\t\t\t\t<option value=\"CC-BY-4.0\">CC Attribution 4.0</option>\n\t\t\t\t<option value=\"CC-BY-ND-1.0\">CC Attribution-NoDerivatives 1.0</option>\n\t\t\t\t<option value=\"CC-BY-ND-2.0\">CC Attribution-NoDerivatives 2.0</option>\n\t\t\t\t<option value=\"CC-BY-ND-2.5\">CC Attribution-NoDerivatives 2.5</option>\n\t\t\t\t<option value=\"CC-BY-ND-3.0\">CC Attribution-NoDerivatives 3.0</option>\n\t\t\t\t<option value=\"CC-BY-ND-4.0\">CC Attribution-NoDerivatives 4.0</option>\n\t\t\t\t<option value=\"CC-BY-SA-1.0\">CC Attribution-ShareAlike 1.0</option>\n\t\t\t\t<option value=\"CC-BY-SA-2.0\">CC Attribution-ShareAlike 2.0</option>\n\t\t\t\t<option value=\"CC-BY-SA-2.5\">CC Attribution-ShareAlike 2.5</option>\n\t\t\t\t<option value=\"CC-BY-SA-3.0\">CC Attribution-ShareAlike 3.0</option>\n\t\t\t\t<option value=\"CC-BY-SA-4.0\">CC Attribution-ShareAlike 4.0</option>\n\t\t\t\t<option value=\"CC-BY-NC-1.0\">CC Attribution-NonCommercial 1.0</option>\n\t\t\t\t<option value=\"CC-BY-NC-2.0\">CC Attribution-NonCommercial 2.0</option>\n\t\t\t\t<option value=\"CC-BY-NC-2.5\">CC Attribution-NonCommercial 2.5</option>\n\t\t\t\t<option value=\"CC-BY-NC-3.0\">CC Attribution-NonCommercial 3.0</option>\n\t\t\t\t<option value=\"CC-BY-NC-4.0\">CC Attribution-NonCommercial 4.0</option>\n\t\t\t\t<option value=\"CC-BY-NC-ND-1.0\">CC Attribution-NonCommercial-NoDerivatives 1.0</option>\n\t\t\t\t<option value=\"CC-BY-NC-ND-2.0\">CC Attribution-NonCommercial-NoDerivatives 2.0</option>\n\t\t\t\t<option value=\"CC-BY-NC-ND-2.5\">CC Attribution-NonCommercial-NoDerivatives 2.5</option>\n\t\t\t\t<option value=\"CC-BY-NC-ND-3.0\">CC Attribution-NonCommercial-NoDerivatives 3.0</option>\n\t\t\t\t<option value=\"CC-BY-NC-ND-4.0\">CC Attribution-NonCommercial-NoDerivatives 4.0</option>\n\t\t\t\t<option value=\"CC-BY-NC-SA-1.0\">CC Attribution-NonCommercial-ShareAlike 1.0</option>\n\t\t\t\t<option value=\"CC-BY-NC-SA-2.0\">CC Attribution-NonCommercial-ShareAlike 2.0</option>\n\t\t\t\t<option value=\"CC-BY-NC-SA-2.5\">CC Attribution-NonCommercial-ShareAlike 2.5</option>\n\t\t\t\t<option value=\"CC-BY-NC-SA-3.0\">CC Attribution-NonCommercial-ShareAlike 3.0</option>\n\t\t\t\t<option value=\"CC-BY-NC-SA-4.0\">CC Attribution-NonCommercial-ShareAlike 4.0</option>\n\t\t\t</select>\n\t\t</span>\n\t\t<br />\n\t\t<a href=\"https://creativecommons.org/choose/\" target=\"_blank\">", "</a>\n\t\t</p>\n\t</div>"]),
    _templateObject9 = _taggedTemplateLiteral(["\n\t\t<div><p>\n\t\t\t", "\n\t\t\t<span class=\"select\">\n\t\t\t\t<select id=\"sortingCol\" name=\"sortingCol\">\n\t\t\t\t\t<option value=''>-</option>\n\t\t\t\t\t<option value='created_at'>", "</option>\n\t\t\t\t\t<option value='taken_at'>", "</option>\n\t\t\t\t\t<option value='title'>", "</option>\n\t\t\t\t\t<option value='description'>", "</option>\n\t\t\t\t\t<option value='is_public'>", "</option>\n\t\t\t\t\t<option value='is_starred'>", "</option>\n\t\t\t\t\t<option value='type'>", "</option>\n\t\t\t\t</select>\n\t\t\t</span>\n\t\t\t", "\n\t\t\t<span class=\"select\">\n\t\t\t\t<select id=\"sortingOrder\" name=\"sortingOrder\">\n\t\t\t\t\t<option value=''>-</option>\n\t\t\t\t\t<option value='ASC'>", "</option>\n\t\t\t\t\t<option value='DESC'>", "</option>\n\t\t\t\t</select>\n\t\t\t</span>\n\t\t\t", "\n\t\t</p></div>"], ["\n\t\t<div><p>\n\t\t\t", "\n\t\t\t<span class=\"select\">\n\t\t\t\t<select id=\"sortingCol\" name=\"sortingCol\">\n\t\t\t\t\t<option value=''>-</option>\n\t\t\t\t\t<option value='created_at'>", "</option>\n\t\t\t\t\t<option value='taken_at'>", "</option>\n\t\t\t\t\t<option value='title'>", "</option>\n\t\t\t\t\t<option value='description'>", "</option>\n\t\t\t\t\t<option value='is_public'>", "</option>\n\t\t\t\t\t<option value='is_starred'>", "</option>\n\t\t\t\t\t<option value='type'>", "</option>\n\t\t\t\t</select>\n\t\t\t</span>\n\t\t\t", "\n\t\t\t<span class=\"select\">\n\t\t\t\t<select id=\"sortingOrder\" name=\"sortingOrder\">\n\t\t\t\t\t<option value=''>-</option>\n\t\t\t\t\t<option value='ASC'>", "</option>\n\t\t\t\t\t<option value='DESC'>", "</option>\n\t\t\t\t</select>\n\t\t\t</span>\n\t\t\t", "\n\t\t</p></div>"]),
    _templateObject10 = _taggedTemplateLiteral(["\n\t\t<form>\n\t\t\t<div class='switch'>\n\t\t\t\t<label>\n\t\t\t\t\t", ":&nbsp;\n\t\t\t\t\t<input type='checkbox' name='is_public'>\n\t\t\t\t\t<span class='slider round'></span>\n\t\t\t\t</label>\n\t\t\t\t<p>", "</p>\n\t\t\t</div>\n\t\t\t<div class='choice'>\n\t\t\t\t<label>\n\t\t\t\t\t<input type='checkbox' name='grants_full_photo'>\n\t\t\t\t\t<span class='checkbox'>", "</span>\n\t\t\t\t\t<span class='label'>", "</span>\n\t\t\t\t</label>\n\t\t\t\t<p>", "</p>\n\t\t\t</div>\n\t\t\t<div class='choice'>\n\t\t\t\t<label>\n\t\t\t\t\t<input type='checkbox' name='requires_link'>\n\t\t\t\t\t<span class='checkbox'>", "</span>\n\t\t\t\t\t<span class='label'>", "</span>\n\t\t\t\t</label>\n\t\t\t\t<p>", "</p>\n\t\t\t</div>\n\t\t\t<div class='choice'>\n\t\t\t\t<label>\n\t\t\t\t\t<input type='checkbox' name='is_downloadable'>\n\t\t\t\t\t<span class='checkbox'>", "</span>\n\t\t\t\t\t<span class='label'>", "</span>\n\t\t\t\t</label>\n\t\t\t\t<p>", "</p>\n\t\t\t</div>\n\t\t\t<div class='choice'>\n\t\t\t\t<label>\n\t\t\t\t\t<input type='checkbox' name='is_share_button_visible'>\n\t\t\t\t\t<span class='checkbox'>", "</span>\n\t\t\t\t\t<span class='label'>", "</span>\n\t\t\t\t</label>\n\t\t\t\t<p>", "</p>\n\t\t\t</div>\n\t\t\t<div class='choice'>\n\t\t\t\t<label>\n\t\t\t\t\t<input type='checkbox' name='has_password'>\n\t\t\t\t\t<span class='checkbox'>", "</span>\n\t\t\t\t\t<span class='label'>", "</span>\n\t\t\t\t</label>\n\t\t\t\t<p>", "</p>\n\t\t\t\t<input class='text' name='passwordtext' type='text' placeholder='", "' value=''>\n\t\t\t</div>\n\t\t\t<div class='hr'><hr></div>\n\t\t\t<div class='switch'>\n\t\t\t\t<label>\n\t\t\t\t\t", ":&nbsp;\n\t\t\t\t\t<input type='checkbox' name='is_nsfw'>\n\t\t\t\t\t<span class='slider round'></span>\n\t\t\t\t</label>\n\t\t\t\t<p>", "</p>\n\t\t\t</div>\n\t\t</form>\n\t"], ["\n\t\t<form>\n\t\t\t<div class='switch'>\n\t\t\t\t<label>\n\t\t\t\t\t", ":&nbsp;\n\t\t\t\t\t<input type='checkbox' name='is_public'>\n\t\t\t\t\t<span class='slider round'></span>\n\t\t\t\t</label>\n\t\t\t\t<p>", "</p>\n\t\t\t</div>\n\t\t\t<div class='choice'>\n\t\t\t\t<label>\n\t\t\t\t\t<input type='checkbox' name='grants_full_photo'>\n\t\t\t\t\t<span class='checkbox'>", "</span>\n\t\t\t\t\t<span class='label'>", "</span>\n\t\t\t\t</label>\n\t\t\t\t<p>", "</p>\n\t\t\t</div>\n\t\t\t<div class='choice'>\n\t\t\t\t<label>\n\t\t\t\t\t<input type='checkbox' name='requires_link'>\n\t\t\t\t\t<span class='checkbox'>", "</span>\n\t\t\t\t\t<span class='label'>", "</span>\n\t\t\t\t</label>\n\t\t\t\t<p>", "</p>\n\t\t\t</div>\n\t\t\t<div class='choice'>\n\t\t\t\t<label>\n\t\t\t\t\t<input type='checkbox' name='is_downloadable'>\n\t\t\t\t\t<span class='checkbox'>", "</span>\n\t\t\t\t\t<span class='label'>", "</span>\n\t\t\t\t</label>\n\t\t\t\t<p>", "</p>\n\t\t\t</div>\n\t\t\t<div class='choice'>\n\t\t\t\t<label>\n\t\t\t\t\t<input type='checkbox' name='is_share_button_visible'>\n\t\t\t\t\t<span class='checkbox'>", "</span>\n\t\t\t\t\t<span class='label'>", "</span>\n\t\t\t\t</label>\n\t\t\t\t<p>", "</p>\n\t\t\t</div>\n\t\t\t<div class='choice'>\n\t\t\t\t<label>\n\t\t\t\t\t<input type='checkbox' name='has_password'>\n\t\t\t\t\t<span class='checkbox'>", "</span>\n\t\t\t\t\t<span class='label'>", "</span>\n\t\t\t\t</label>\n\t\t\t\t<p>", "</p>\n\t\t\t\t<input class='text' name='passwordtext' type='text' placeholder='", "' value=''>\n\t\t\t</div>\n\t\t\t<div class='hr'><hr></div>\n\t\t\t<div class='switch'>\n\t\t\t\t<label>\n\t\t\t\t\t", ":&nbsp;\n\t\t\t\t\t<input type='checkbox' name='is_nsfw'>\n\t\t\t\t\t<span class='slider round'></span>\n\t\t\t\t</label>\n\t\t\t\t<p>", "</p>\n\t\t\t</div>\n\t\t</form>\n\t"]),
    _templateObject11 = _taggedTemplateLiteral(["<div class='choice'>\n\t\t\t\t\t\t<label>\n\t\t\t\t\t\t\t<input type='checkbox' name='", "'>\n\t\t\t\t\t\t\t<span class='checkbox'>", "</span>\n\t\t\t\t\t\t\t<span class='label'>", "</span>\n\t\t\t\t\t\t</label>\n\t\t\t\t\t\t<p></p>\n\t\t\t\t\t</div>"], ["<div class='choice'>\n\t\t\t\t\t\t<label>\n\t\t\t\t\t\t\t<input type='checkbox' name='", "'>\n\t\t\t\t\t\t\t<span class='checkbox'>", "</span>\n\t\t\t\t\t\t\t<span class='label'>", "</span>\n\t\t\t\t\t\t</label>\n\t\t\t\t\t\t<p></p>\n\t\t\t\t\t</div>"]),
    _templateObject12 = _taggedTemplateLiteral(["\n\t\t<div id='qr-code' class='downloads'></div>\n\t"], ["\n\t\t<div id='qr-code' class='downloads'></div>\n\t"]),
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
    _templateObject37 = _taggedTemplateLiteral(["<a class='", "'>$", "<span data-index='", "'>", "</span></a>"], ["<a class='", "'>$", "<span data-index='", "'>", "</span></a>"]),
    _templateObject38 = _taggedTemplateLiteral(["<a class='", "'>$", "</a>"], ["<a class='", "'>$", "</a>"]),
    _templateObject39 = _taggedTemplateLiteral(["<div class='empty'>", "</div>"], ["<div class='empty'>", "</div>"]),
    _templateObject40 = _taggedTemplateLiteral(["<div class=\"users_view_line\">\n\t\t\t<p id=\"UserData", "\">\n\t\t\t<input name=\"id\" type=\"hidden\" inputmode=\"numeric\" value=\"", "\" />\n\t\t\t<input class=\"text\" name=\"username\" type=\"text\" value=\"$", "\" placeholder=\"username\" />\n\t\t\t<input class=\"text\" name=\"password\" type=\"text\" placeholder=\"new password\" />\n\t\t\t<span class=\"choice\" title=\"Allow uploads\">\n\t\t\t<label>\n\t\t\t<input type=\"checkbox\" name=\"may_upload\" />\n\t\t\t<span class=\"checkbox\"><svg class=\"iconic \"><use xlink:href=\"#check\"></use></svg></span>\n\t\t\t</label>\n\t\t\t</span>\n\t\t\t<span class=\"choice\" title=\"Restricted account\">\n\t\t\t<label>\n\t\t\t<input type=\"checkbox\" name=\"is_locked\" />\n\t\t\t<span class=\"checkbox\"><svg class=\"iconic \"><use xlink:href=\"#check\"></use></svg></span>\n\t\t\t</label>\n\t\t\t</span>\n\t\t\t</p>\n\t\t\t<a id=\"UserUpdate", "\"  class=\"basicModal__button basicModal__button_OK\">Save</a>\n\t\t\t<a id=\"UserDelete", "\"  class=\"basicModal__button basicModal__button_DEL\">Delete</a>\n\t\t</div>\n\t\t"], ["<div class=\"users_view_line\">\n\t\t\t<p id=\"UserData", "\">\n\t\t\t<input name=\"id\" type=\"hidden\" inputmode=\"numeric\" value=\"", "\" />\n\t\t\t<input class=\"text\" name=\"username\" type=\"text\" value=\"$", "\" placeholder=\"username\" />\n\t\t\t<input class=\"text\" name=\"password\" type=\"text\" placeholder=\"new password\" />\n\t\t\t<span class=\"choice\" title=\"Allow uploads\">\n\t\t\t<label>\n\t\t\t<input type=\"checkbox\" name=\"may_upload\" />\n\t\t\t<span class=\"checkbox\"><svg class=\"iconic \"><use xlink:href=\"#check\"></use></svg></span>\n\t\t\t</label>\n\t\t\t</span>\n\t\t\t<span class=\"choice\" title=\"Restricted account\">\n\t\t\t<label>\n\t\t\t<input type=\"checkbox\" name=\"is_locked\" />\n\t\t\t<span class=\"checkbox\"><svg class=\"iconic \"><use xlink:href=\"#check\"></use></svg></span>\n\t\t\t</label>\n\t\t\t</span>\n\t\t\t</p>\n\t\t\t<a id=\"UserUpdate", "\"  class=\"basicModal__button basicModal__button_OK\">Save</a>\n\t\t\t<a id=\"UserDelete", "\"  class=\"basicModal__button basicModal__button_DEL\">Delete</a>\n\t\t</div>\n\t\t"]),
    _templateObject41 = _taggedTemplateLiteral(["<div class=\"u2f_view_line\">\n\t\t\t<p id=\"CredentialData", "\">\n\t\t\t<input name=\"id\" type=\"hidden\" inputmode=\"numeric\" value=\"", "\" />\n\t\t\t<span class=\"text\">", "</span>\n\t\t\t<!--- <span class=\"choice\" title=\"Allow uploads\">\n\t\t\t<label>\n\t\t\t<input type=\"checkbox\" name=\"may_upload\" />\n\t\t\t<span class=\"checkbox\"><svg class=\"iconic \"><use xlink:href=\"#check\"></use></svg></span>\n\t\t\t</label>\n\t\t\t</span>\n\t\t\t<span class=\"choice\" title=\"Restricted account\">\n\t\t\t<label>\n\t\t\t<input type=\"checkbox\" name=\"is_locked\" />\n\t\t\t<span class=\"checkbox\"><svg class=\"iconic \"><use xlink:href=\"#check\"></use></svg></span>\n\t\t\t</label>\n\t\t\t</span>--->\n\t\t\t</p>\n\t\t\t<a id=\"CredentialDelete", "\"  class=\"basicModal__button basicModal__button_DEL\">Delete</a>\n\t\t</div>\n\t\t"], ["<div class=\"u2f_view_line\">\n\t\t\t<p id=\"CredentialData", "\">\n\t\t\t<input name=\"id\" type=\"hidden\" inputmode=\"numeric\" value=\"", "\" />\n\t\t\t<span class=\"text\">", "</span>\n\t\t\t<!--- <span class=\"choice\" title=\"Allow uploads\">\n\t\t\t<label>\n\t\t\t<input type=\"checkbox\" name=\"may_upload\" />\n\t\t\t<span class=\"checkbox\"><svg class=\"iconic \"><use xlink:href=\"#check\"></use></svg></span>\n\t\t\t</label>\n\t\t\t</span>\n\t\t\t<span class=\"choice\" title=\"Restricted account\">\n\t\t\t<label>\n\t\t\t<input type=\"checkbox\" name=\"is_locked\" />\n\t\t\t<span class=\"checkbox\"><svg class=\"iconic \"><use xlink:href=\"#check\"></use></svg></span>\n\t\t\t</label>\n\t\t\t</span>--->\n\t\t\t</p>\n\t\t\t<a id=\"CredentialDelete", "\"  class=\"basicModal__button basicModal__button_DEL\">Delete</a>\n\t\t</div>\n\t\t"]),
    _templateObject42 = _taggedTemplateLiteral(["\n\t\t\t           ", "\n\t\t\t           <img class='cover' width='16' height='16' src='", "' alt=\"thumbnail\">\n\t\t\t           <div class='title'>$", "</div>\n\t\t\t           "], ["\n\t\t\t           ", "\n\t\t\t           <img class='cover' width='16' height='16' src='", "' alt=\"thumbnail\">\n\t\t\t           <div class='title'>$", "</div>\n\t\t\t           "]),
    _templateObject43 = _taggedTemplateLiteral(["$", "", ""], ["$", "", ""]),
    _templateObject44 = _taggedTemplateLiteral(["\n\t\t<a id=\"text_settings_close\" class=\"closetxt\" data-tabindex=\"-1\">", "</a>\n\t\t<a id=\"button_settings_close\" class=\"closebtn\" data-tabindex=\"20\">&times;</a>\n\t\t<a class=\"linkMenu\" id=\"button_settings_open\" data-tabindex=\"-1\"><svg class=\"iconic\"><use xlink:href=\"#cog\"></use></svg>", "</a>"], ["\n\t\t<a id=\"text_settings_close\" class=\"closetxt\" data-tabindex=\"-1\">", "</a>\n\t\t<a id=\"button_settings_close\" class=\"closebtn\" data-tabindex=\"20\">&times;</a>\n\t\t<a class=\"linkMenu\" id=\"button_settings_open\" data-tabindex=\"-1\"><svg class=\"iconic\"><use xlink:href=\"#cog\"></use></svg>", "</a>"]),
    _templateObject45 = _taggedTemplateLiteral(["\n\t\t<a class=\"linkMenu\" id=\"button_notifications\" data-tabindex=\"-1\">", "", " </a>\n\t\t"], ["\n\t\t<a class=\"linkMenu\" id=\"button_notifications\" data-tabindex=\"-1\">", "", " </a>\n\t\t"]),
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
    _templateObject56 = _taggedTemplateLiteral(["\n\t\t<div class='switch'>\n\t\t\t<label>\n\t\t\t\t<span class='label'>", ":</span>\n\t\t\t\t<input type='checkbox' name='is_public'>\n\t\t\t\t<span class='slider round'></span>\n\t\t\t</label>\n\t\t\t<p>", "</p>\n\t\t</div>\n\t"], ["\n\t\t<div class='switch'>\n\t\t\t<label>\n\t\t\t\t<span class='label'>", ":</span>\n\t\t\t\t<input type='checkbox' name='is_public'>\n\t\t\t\t<span class='slider round'></span>\n\t\t\t</label>\n\t\t\t<p>", "</p>\n\t\t</div>\n\t"]),
    _templateObject57 = _taggedTemplateLiteral(["\n\t\t<div class='choice'>\n\t\t\t<label>\n\t\t\t\t<input type='checkbox' name='grants_full_photo' disabled>\n\t\t\t\t<span class='checkbox'>", "</span>\n\t\t\t\t<span class='label'>", "</span>\n\t\t\t</label>\n\t\t\t<p>", "</p>\n\t\t</div>\n\t\t<div class='choice'>\n\t\t\t<label>\n\t\t\t\t<input type='checkbox' name='requires_link' disabled>\n\t\t\t\t<span class='checkbox'>", "</span>\n\t\t\t\t<span class='label'>", "</span>\n\t\t\t</label>\n\t\t\t<p>", "</p>\n\t\t</div>\n\t\t<div class='choice'>\n\t\t\t<label>\n\t\t\t\t<input type='checkbox' name='is_downloadable' disabled>\n\t\t\t\t<span class='checkbox'>", "</span>\n\t\t\t\t<span class='label'>", "</span>\n\t\t\t</label>\n\t\t\t<p>", "</p>\n\t\t</div>\n\t\t<div class='choice'>\n\t\t\t<label>\n\t\t\t\t<input type='checkbox' name='is_share_button_visible' disabled>\n\t\t\t\t<span class='checkbox'>", "</span>\n\t\t\t\t<span class='label'>", "</span>\n\t\t\t</label>\n\t\t\t<p>", "</p>\n\t\t</div>\n\t\t<div class='choice'>\n\t\t\t<label>\n\t\t\t\t<input type='checkbox' name='has_password' disabled>\n\t\t\t\t<span class='checkbox'>", "</span>\n\t\t\t\t<span class='label'>", "</span>\n\t\t\t</label>\n\t\t\t<p>", "</p>\n\t\t</div>\n\t"], ["\n\t\t<div class='choice'>\n\t\t\t<label>\n\t\t\t\t<input type='checkbox' name='grants_full_photo' disabled>\n\t\t\t\t<span class='checkbox'>", "</span>\n\t\t\t\t<span class='label'>", "</span>\n\t\t\t</label>\n\t\t\t<p>", "</p>\n\t\t</div>\n\t\t<div class='choice'>\n\t\t\t<label>\n\t\t\t\t<input type='checkbox' name='requires_link' disabled>\n\t\t\t\t<span class='checkbox'>", "</span>\n\t\t\t\t<span class='label'>", "</span>\n\t\t\t</label>\n\t\t\t<p>", "</p>\n\t\t</div>\n\t\t<div class='choice'>\n\t\t\t<label>\n\t\t\t\t<input type='checkbox' name='is_downloadable' disabled>\n\t\t\t\t<span class='checkbox'>", "</span>\n\t\t\t\t<span class='label'>", "</span>\n\t\t\t</label>\n\t\t\t<p>", "</p>\n\t\t</div>\n\t\t<div class='choice'>\n\t\t\t<label>\n\t\t\t\t<input type='checkbox' name='is_share_button_visible' disabled>\n\t\t\t\t<span class='checkbox'>", "</span>\n\t\t\t\t<span class='label'>", "</span>\n\t\t\t</label>\n\t\t\t<p>", "</p>\n\t\t</div>\n\t\t<div class='choice'>\n\t\t\t<label>\n\t\t\t\t<input type='checkbox' name='has_password' disabled>\n\t\t\t\t<span class='checkbox'>", "</span>\n\t\t\t\t<span class='label'>", "</span>\n\t\t\t</label>\n\t\t\t<p>", "</p>\n\t\t</div>\n\t"]),
    _templateObject58 = _taggedTemplateLiteral(["\n\t\t\t<p class='less'>", "</p>\n\t\t\t", "\n\t\t\t", "\n\t\t"], ["\n\t\t\t<p class='less'>", "</p>\n\t\t\t", "\n\t\t\t", "\n\t\t"]),
    _templateObject59 = _taggedTemplateLiteral(["\n\t\t\t", "\n\t\t\t<p class='photoPublic'>", "</p>\n\t\t\t", "\n\t\t"], ["\n\t\t\t", "\n\t\t\t<p class='photoPublic'>", "</p>\n\t\t\t", "\n\t\t"]),
    _templateObject60 = _taggedTemplateLiteral(["<p>", " <input class='text' name='description' type='text' maxlength='800' placeholder='", "' value='$", "'></p>"], ["<p>", " <input class='text' name='description' type='text' maxlength='800' placeholder='", "' value='$", "'></p>"]),
    _templateObject61 = _taggedTemplateLiteral(["<input class='text' name='tags' type='text' maxlength='800' placeholder='Tags' value='$", "'>"], ["<input class='text' name='tags' type='text' maxlength='800' placeholder='Tags' value='$", "'>"]),
    _templateObject62 = _taggedTemplateLiteral(["\n\t\t\t\t<a class='basicModal__button' id='", "' title='", "'>\n\t\t\t\t\t", "", "\n\t\t\t\t</a>\n\t\t\t"], ["\n\t\t\t\t<a class='basicModal__button' id='", "' title='", "'>\n\t\t\t\t\t", "", "\n\t\t\t\t</a>\n\t\t\t"]),
    _templateObject63 = _taggedTemplateLiteral(["\n\t\t\t<div class='downloads'>\n\t\t"], ["\n\t\t\t<div class='downloads'>\n\t\t"]),
    _templateObject64 = _taggedTemplateLiteral(["\n\t\t\t</div>\n\t\t"], ["\n\t\t\t</div>\n\t\t"]),
    _templateObject65 = _taggedTemplateLiteral(["\n\t\t\t<p>\n\t\t\t\t", "\n\t\t\t\t<br />\n\t\t\t\t<input class='text' readonly value='", "'>\n\t\t\t\t<a class='basicModal__button' title='", "'>\n\t\t\t\t\t", "\n\t\t\t\t</a>\n\t\t\t</p>\n\t\t"], ["\n\t\t\t<p>\n\t\t\t\t", "\n\t\t\t\t<br />\n\t\t\t\t<input class='text' readonly value='", "'>\n\t\t\t\t<a class='basicModal__button' title='", "'>\n\t\t\t\t\t", "\n\t\t\t\t</a>\n\t\t\t</p>\n\t\t"]),
    _templateObject66 = _taggedTemplateLiteral(["\n\t\t<div class='directLinks'>\n\t\t\t", "\n\t\t\t<p class='less'>\n\t\t\t\t", "\n\t\t\t</p>\n\t\t\t<div class='imageLinks'>\n\t"], ["\n\t\t<div class='directLinks'>\n\t\t\t", "\n\t\t\t<p class='less'>\n\t\t\t\t", "\n\t\t\t</p>\n\t\t\t<div class='imageLinks'>\n\t"]),
    _templateObject67 = _taggedTemplateLiteral(["\n\t\t</div>\n\t\t</div>\n\t"], ["\n\t\t</div>\n\t\t</div>\n\t"]),
    _templateObject68 = _taggedTemplateLiteral(["<p style=\"color: #d92c34; font-size: 1.3em; font-weight: bold; text-transform: capitalize; text-align: center;\">", "</p>"], ["<p style=\"color: #d92c34; font-size: 1.3em; font-weight: bold; text-transform: capitalize; text-align: center;\">", "</p>"]),
    _templateObject69 = _taggedTemplateLiteral(["<span class='attr_", "_separator'>, </span>"], ["<span class='attr_", "_separator'>, </span>"]),
    _templateObject70 = _taggedTemplateLiteral(["<span class='attr_", " search'>$", "</span>"], ["<span class='attr_", " search'>$", "</span>"]),
    _templateObject71 = _taggedTemplateLiteral(["<span class='attr_", "'>$", "</span>"], ["<span class='attr_", "'>$", "</span>"]),
    _templateObject72 = _taggedTemplateLiteral(["\n\t\t\t\t\t\t <tr>\n\t\t\t\t\t\t\t <td>", "</td>\n\t\t\t\t\t\t\t <td>", "</td>\n\t\t\t\t\t\t </tr>\n\t\t\t\t\t\t "], ["\n\t\t\t\t\t\t <tr>\n\t\t\t\t\t\t\t <td>", "</td>\n\t\t\t\t\t\t\t <td>", "</td>\n\t\t\t\t\t\t </tr>\n\t\t\t\t\t\t "]),
    _templateObject73 = _taggedTemplateLiteral(["\n\t\t\t\t <div class='sidebar__divider'>\n\t\t\t\t\t <h1>", "</h1>\n\t\t\t\t </div>\n\t\t\t\t <div id='tags'>\n\t\t\t\t\t <div class='attr_", "'>", "</div>\n\t\t\t\t\t ", "\n\t\t\t\t </div>\n\t\t\t\t "], ["\n\t\t\t\t <div class='sidebar__divider'>\n\t\t\t\t\t <h1>", "</h1>\n\t\t\t\t </div>\n\t\t\t\t <div id='tags'>\n\t\t\t\t\t <div class='attr_", "'>", "</div>\n\t\t\t\t\t ", "\n\t\t\t\t </div>\n\t\t\t\t "]),
    _templateObject74 = _taggedTemplateLiteral(["<h1>", "</h1>"], ["<h1>", "</h1>"]),
    _templateObject75 = _taggedTemplateLiteral(["<p>"], ["<p>"]),
    _templateObject76 = _taggedTemplateLiteral(["\n\t\t\t<p class='importServer'>\n\t\t\t\t", "\n\t\t\t\t<input class='text' name='path' type='text' placeholder='", "' value='", "uploads/import/'>\n\t\t\t</p>\n\t\t\t<div class='choice'>\n\t\t\t\t<label>\n\t\t\t\t\t<input type='checkbox' name='delete_imported' onchange='upload.check()'>\n\t\t\t\t\t<span class='checkbox'>", "</span>\n\t\t\t\t\t<span class='label'>", "</span>\n\t\t\t\t</label>\n\t\t\t\t<p>\n\t\t\t\t\t", "\n\t\t\t\t</p>\n\t\t\t</div>\n\t\t\t<div class='choice'>\n\t\t\t\t<label>\n\t\t\t\t\t<input type='checkbox' name='import_via_symlink' onchange='upload.check()'>\n\t\t\t\t\t<span class='checkbox'>", "</span>\n\t\t\t\t\t<span class='label'>", "</span>\n\t\t\t\t</label>\n\t\t\t\t<p>\n\t\t\t\t\t", "\n\t\t\t\t</p>\n\t\t\t</div>\n\t\t\t<div class='choice'>\n\t\t\t\t<label>\n\t\t\t\t\t<input type='checkbox' name='skip_duplicates' onchange='upload.check()'>\n\t\t\t\t\t<span class='checkbox'>", "</span>\n\t\t\t\t\t<span class='label'>", "</span>\n\t\t\t\t</label>\n\t\t\t\t<p>\n\t\t\t\t\t", "\n\t\t\t\t</p>\n\t\t\t</div>\n\t\t\t<div class='choice'>\n\t\t\t\t<label>\n\t\t\t\t\t<input type='checkbox' name='resync_metadata' onchange='upload.check()'>\n\t\t\t\t\t<span class='checkbox'>", "</span>\n\t\t\t\t\t<span class='label'>", "</span>\n\t\t\t\t</label>\n\t\t\t\t<p>\n\t\t\t\t\t", "\n\t\t\t\t</p>\n\t\t\t</div>\n\t\t"], ["\n\t\t\t<p class='importServer'>\n\t\t\t\t", "\n\t\t\t\t<input class='text' name='path' type='text' placeholder='", "' value='", "uploads/import/'>\n\t\t\t</p>\n\t\t\t<div class='choice'>\n\t\t\t\t<label>\n\t\t\t\t\t<input type='checkbox' name='delete_imported' onchange='upload.check()'>\n\t\t\t\t\t<span class='checkbox'>", "</span>\n\t\t\t\t\t<span class='label'>", "</span>\n\t\t\t\t</label>\n\t\t\t\t<p>\n\t\t\t\t\t", "\n\t\t\t\t</p>\n\t\t\t</div>\n\t\t\t<div class='choice'>\n\t\t\t\t<label>\n\t\t\t\t\t<input type='checkbox' name='import_via_symlink' onchange='upload.check()'>\n\t\t\t\t\t<span class='checkbox'>", "</span>\n\t\t\t\t\t<span class='label'>", "</span>\n\t\t\t\t</label>\n\t\t\t\t<p>\n\t\t\t\t\t", "\n\t\t\t\t</p>\n\t\t\t</div>\n\t\t\t<div class='choice'>\n\t\t\t\t<label>\n\t\t\t\t\t<input type='checkbox' name='skip_duplicates' onchange='upload.check()'>\n\t\t\t\t\t<span class='checkbox'>", "</span>\n\t\t\t\t\t<span class='label'>", "</span>\n\t\t\t\t</label>\n\t\t\t\t<p>\n\t\t\t\t\t", "\n\t\t\t\t</p>\n\t\t\t</div>\n\t\t\t<div class='choice'>\n\t\t\t\t<label>\n\t\t\t\t\t<input type='checkbox' name='resync_metadata' onchange='upload.check()'>\n\t\t\t\t\t<span class='checkbox'>", "</span>\n\t\t\t\t\t<span class='label'>", "</span>\n\t\t\t\t</label>\n\t\t\t\t<p>\n\t\t\t\t\t", "\n\t\t\t\t</p>\n\t\t\t</div>\n\t\t"]),
    _templateObject77 = _taggedTemplateLiteral(["url(\"", "\")"], ["url(\"", "\")"]),
    _templateObject78 = _taggedTemplateLiteral(["linear-gradient(to bottom, rgba(0, 0, 0, .4), rgba(0, 0, 0, .4)), url(\"", "\")"], ["linear-gradient(to bottom, rgba(0, 0, 0, .4), rgba(0, 0, 0, .4)), url(\"", "\")"]),
    _templateObject79 = _taggedTemplateLiteral(["\n\t\t\t<div class=\"setLogin\">\n\t\t\t  <p>$", "\n\t\t\t\t  <input name='oldUsername' class='text' type='text' placeholder='$", "' value=''>\n\t\t\t\t  <input name='oldPassword' class='text' type='password' placeholder='$", "' value=''>\n\t\t\t  </p>\n\t\t\t  <p>$", "\n\t\t\t\t  <input name='username' class='text' type='text' placeholder='$", "' value=''>\n\t\t\t\t  <input name='password' class='text' type='password' placeholder='$", "' value=''>\n\t\t\t\t  <input name='confirm' class='text' type='password' placeholder='$", "' value=''>\n\t\t\t  </p>\n\t\t\t<div class=\"basicModal__buttons\">\n\t\t\t\t<!--<a id=\"basicModal__cancel\" class=\"basicModal__button \">Cancel</a>-->\n\t\t\t\t<a id=\"basicModal__action_password_change\" class=\"basicModal__button \">$", "</a>\n\t\t\t</div>\n\t\t\t</div>"], ["\n\t\t\t<div class=\"setLogin\">\n\t\t\t  <p>$", "\n\t\t\t\t  <input name='oldUsername' class='text' type='text' placeholder='$", "' value=''>\n\t\t\t\t  <input name='oldPassword' class='text' type='password' placeholder='$", "' value=''>\n\t\t\t  </p>\n\t\t\t  <p>$", "\n\t\t\t\t  <input name='username' class='text' type='text' placeholder='$", "' value=''>\n\t\t\t\t  <input name='password' class='text' type='password' placeholder='$", "' value=''>\n\t\t\t\t  <input name='confirm' class='text' type='password' placeholder='$", "' value=''>\n\t\t\t  </p>\n\t\t\t<div class=\"basicModal__buttons\">\n\t\t\t\t<!--<a id=\"basicModal__cancel\" class=\"basicModal__button \">Cancel</a>-->\n\t\t\t\t<a id=\"basicModal__action_password_change\" class=\"basicModal__button \">$", "</a>\n\t\t\t</div>\n\t\t\t</div>"]),
    _templateObject80 = _taggedTemplateLiteral(["\n\t\t\t\t<div class=\"setSorting\">\n\t\t\t\t\t<p>\n\t\t\t\t\t\t$", "\n\t\t\t\t\t\t<span class=\"select\">\n\t\t\t\t\t\t\t<select id=\"settings_albums_sorting_column\" name=\"sorting_albums_column\">\n\t\t\t\t\t\t\t\t<option value='created_at'>$", "</option>\n\t\t\t\t\t\t\t\t<option value='title'>$", "</option>\n\t\t\t\t\t\t\t\t<option value='description'>$", "</option>\n\t\t\t\t\t\t\t\t<option value='is_public'>$", "</option>\n\t\t\t\t\t\t\t\t<option value='max_taken_at'>$", "</option>\n\t\t\t\t\t\t\t\t<option value='min_taken_at'>$", "</option>\n\t\t\t\t\t\t\t</select>\n\t\t\t\t\t\t</span>\n\t\t\t\t\t\t$", "\n\t\t\t\t\t\t<span class=\"select\">\n\t\t\t\t\t\t\t<select id=\"settings_albums_sorting_order\" name=\"sorting_albums_order\">\n\t\t\t\t\t\t\t\t<option value='ASC'>$", "</option>\n\t\t\t\t\t\t\t\t<option value='DESC'>$", "</option>\n\t\t\t\t\t\t\t</select>\n\t\t\t\t\t\t</span>\n\t\t\t\t\t\t$", "\n\t\t\t\t\t</p>\n\t\t\t\t\t<p>\n\t\t\t\t\t\t$", "\n\t\t\t\t\t\t<span class=\"select\">\n\t\t\t\t\t\t\t<select id=\"settings_photos_sorting_column\" name=\"sorting_photos_column\">\n\t\t\t\t\t\t\t\t<option value='created_at'>$", "</option>\n\t\t\t\t\t\t\t\t<option value='taken_at'>$", "</option>\n\t\t\t\t\t\t\t\t<option value='title'>$", "</option>\n\t\t\t\t\t\t\t\t<option value='description'>$", "</option>\n\t\t\t\t\t\t\t\t<option value='is_public'>$", "</option>\n\t\t\t\t\t\t\t\t<option value='is_starred'>$", "</option>\n\t\t\t\t\t\t\t\t<option value='type'>$", "</option>\n\t\t\t\t\t\t\t</select>\n\t\t\t\t  \t\t</span>\n\t\t\t\t\t\t$", "\n\t\t\t\t  \t\t<span class=\"select\">\n\t\t\t\t\t\t\t<select id=\"settings_photos_sorting_order\" name=\"sorting_photos_order\">\n\t\t\t\t\t\t\t\t<option value='ASC'>$", "</option>\n\t\t\t\t\t\t\t\t<option value='DESC'>$", "</option>\n\t\t\t\t\t\t\t</select>\n\t\t\t\t\t\t</span>\n\t\t\t\t\t\t$", "\n\t\t\t\t\t</p>\n\t\t\t\t\t<div class=\"basicModal__buttons\">\n\t\t\t\t\t\t<!--<a id=\"basicModal__cancel\" class=\"basicModal__button \">Cancel</a>-->\n\t\t\t\t\t\t<a id=\"basicModal__action_sorting_change\" class=\"basicModal__button \">$", "</a>\n\t\t\t\t\t</div>\n\t\t\t\t</div>\n\t\t\t"], ["\n\t\t\t\t<div class=\"setSorting\">\n\t\t\t\t\t<p>\n\t\t\t\t\t\t$", "\n\t\t\t\t\t\t<span class=\"select\">\n\t\t\t\t\t\t\t<select id=\"settings_albums_sorting_column\" name=\"sorting_albums_column\">\n\t\t\t\t\t\t\t\t<option value='created_at'>$", "</option>\n\t\t\t\t\t\t\t\t<option value='title'>$", "</option>\n\t\t\t\t\t\t\t\t<option value='description'>$", "</option>\n\t\t\t\t\t\t\t\t<option value='is_public'>$", "</option>\n\t\t\t\t\t\t\t\t<option value='max_taken_at'>$", "</option>\n\t\t\t\t\t\t\t\t<option value='min_taken_at'>$", "</option>\n\t\t\t\t\t\t\t</select>\n\t\t\t\t\t\t</span>\n\t\t\t\t\t\t$", "\n\t\t\t\t\t\t<span class=\"select\">\n\t\t\t\t\t\t\t<select id=\"settings_albums_sorting_order\" name=\"sorting_albums_order\">\n\t\t\t\t\t\t\t\t<option value='ASC'>$", "</option>\n\t\t\t\t\t\t\t\t<option value='DESC'>$", "</option>\n\t\t\t\t\t\t\t</select>\n\t\t\t\t\t\t</span>\n\t\t\t\t\t\t$", "\n\t\t\t\t\t</p>\n\t\t\t\t\t<p>\n\t\t\t\t\t\t$", "\n\t\t\t\t\t\t<span class=\"select\">\n\t\t\t\t\t\t\t<select id=\"settings_photos_sorting_column\" name=\"sorting_photos_column\">\n\t\t\t\t\t\t\t\t<option value='created_at'>$", "</option>\n\t\t\t\t\t\t\t\t<option value='taken_at'>$", "</option>\n\t\t\t\t\t\t\t\t<option value='title'>$", "</option>\n\t\t\t\t\t\t\t\t<option value='description'>$", "</option>\n\t\t\t\t\t\t\t\t<option value='is_public'>$", "</option>\n\t\t\t\t\t\t\t\t<option value='is_starred'>$", "</option>\n\t\t\t\t\t\t\t\t<option value='type'>$", "</option>\n\t\t\t\t\t\t\t</select>\n\t\t\t\t  \t\t</span>\n\t\t\t\t\t\t$", "\n\t\t\t\t  \t\t<span class=\"select\">\n\t\t\t\t\t\t\t<select id=\"settings_photos_sorting_order\" name=\"sorting_photos_order\">\n\t\t\t\t\t\t\t\t<option value='ASC'>$", "</option>\n\t\t\t\t\t\t\t\t<option value='DESC'>$", "</option>\n\t\t\t\t\t\t\t</select>\n\t\t\t\t\t\t</span>\n\t\t\t\t\t\t$", "\n\t\t\t\t\t</p>\n\t\t\t\t\t<div class=\"basicModal__buttons\">\n\t\t\t\t\t\t<!--<a id=\"basicModal__cancel\" class=\"basicModal__button \">Cancel</a>-->\n\t\t\t\t\t\t<a id=\"basicModal__action_sorting_change\" class=\"basicModal__button \">$", "</a>\n\t\t\t\t\t</div>\n\t\t\t\t</div>\n\t\t\t"]),
    _templateObject81 = _taggedTemplateLiteral(["\n\t\t\t<div class=\"setCSS\">\n\t\t\t\t<a id=\"basicModal__action_more\" class=\"basicModal__button basicModal__button_MORE\">", "</a>\n\t\t\t</div>\n\t\t\t"], ["\n\t\t\t<div class=\"setCSS\">\n\t\t\t\t<a id=\"basicModal__action_more\" class=\"basicModal__button basicModal__button_MORE\">", "</a>\n\t\t\t</div>\n\t\t\t"]),
    _templateObject82 = _taggedTemplateLiteral(["\n\t\t\t\t\t\t<div id=\"fullSettings\">\n\t\t\t\t\t\t<div class=\"setting_line\">\n\t\t\t\t\t\t<p class=\"warning\">\n\t\t\t\t\t\t", "\n\t\t\t\t\t\t</p>\n\t\t\t\t\t\t</div>\n\t\t\t\t\t\t"], ["\n\t\t\t\t\t\t<div id=\"fullSettings\">\n\t\t\t\t\t\t<div class=\"setting_line\">\n\t\t\t\t\t\t<p class=\"warning\">\n\t\t\t\t\t\t", "\n\t\t\t\t\t\t</p>\n\t\t\t\t\t\t</div>\n\t\t\t\t\t\t"]),
    _templateObject83 = _taggedTemplateLiteral(["\n\t\t\t\t\t\t\t\t<div class=\"setting_category\">\n\t\t\t\t\t\t\t\t\t<p>$", "</p>\n\t\t\t\t\t\t\t\t</div>"], ["\n\t\t\t\t\t\t\t\t<div class=\"setting_category\">\n\t\t\t\t\t\t\t\t\t<p>$", "</p>\n\t\t\t\t\t\t\t\t</div>"]),
    _templateObject84 = _taggedTemplateLiteral(["\n\t\t\t\t\t\t\t<div class=\"setting_line\">\n\t\t\t\t\t\t\t\t<p>\n\t\t\t\t\t\t\t\t\t<span class=\"text\">$", "</span>\n\t\t\t\t\t\t\t\t\t<input class=\"text\" name=\"$", "\" type=\"text\" value=\"$", "\" placeholder=\"\" />\n\t\t\t\t\t\t\t\t</p>\n\t\t\t\t\t\t\t</div>"], ["\n\t\t\t\t\t\t\t<div class=\"setting_line\">\n\t\t\t\t\t\t\t\t<p>\n\t\t\t\t\t\t\t\t\t<span class=\"text\">$", "</span>\n\t\t\t\t\t\t\t\t\t<input class=\"text\" name=\"$", "\" type=\"text\" value=\"$", "\" placeholder=\"\" />\n\t\t\t\t\t\t\t\t</p>\n\t\t\t\t\t\t\t</div>"]),
    _templateObject85 = _taggedTemplateLiteral(["\n\t\t\t\t\t\t<a id=\"FullSettingsSave_button\"  class=\"basicModal__button basicModal__button_SAVE\">", "</a>\n\t\t\t\t\t\t</div>"], ["\n\t\t\t\t\t\t<a id=\"FullSettingsSave_button\"  class=\"basicModal__button basicModal__button_SAVE\">", "</a>\n\t\t\t\t\t\t</div>"]),
    _templateObject86 = _taggedTemplateLiteral(["\n\t\t\t<div class=\"clear_logs_update\">\n\t\t\t\t<a id=\"Clean_Noise\" class=\"basicModal__button\">\n\t\t\t\t\t", "\n\t\t\t\t</a>\n\t\t\t</div>\n\t\t\t<pre class=\"logs_diagnostics_view\"></pre>"], ["\n\t\t\t<div class=\"clear_logs_update\">\n\t\t\t\t<a id=\"Clean_Noise\" class=\"basicModal__button\">\n\t\t\t\t\t", "\n\t\t\t\t</a>\n\t\t\t</div>\n\t\t\t<pre class=\"logs_diagnostics_view\"></pre>"]);

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

/**
 * @description Takes care of every action an album can handle and execute.
 */

var album = {
	/** @type {(?Album|?TagAlbum|?SearchAlbum)} */
	json: null
};

/**
 * @param {?string} id
 * @returns {boolean}
 */
album.isSmartID = function (id) {
	return id === SmartAlbumID.UNSORTED || id === SmartAlbumID.STARRED || id === SmartAlbumID.PUBLIC || id === SmartAlbumID.RECENT;
};

/**
 * @param {?string} id
 * @returns {boolean}
 */
album.isSearchID = function (id) {
	return id === SearchAlbumID;
};

/**
 * @param {?string} id
 * @returns {boolean}
 */
album.isModelID = function (id) {
	return typeof id === "string" && /^[-_0-9a-zA-Z]{24}$/.test(id);
};

/**
 * @returns {?string}
 */
album.getParentID = function () {
	if (album.json === null || album.isSmartID(album.json.id) || album.isSearchID(album.json.id) || !album.json.parent_id) {
		return null;
	}
	return album.json.parent_id;
};

/**
 * @returns {?string} the album ID
 */
album.getID = function () {
	/** @type {?string} */
	var id = null;

	// this is a Lambda
	var isID = function isID(_id) {
		return album.isSmartID(_id) || /*album.isSearchID(_id) || */album.isModelID(_id);
	};

	if (_photo3.json) id = _photo3.json.album_id;else if (album.json) id = album.json.id;else if (mapview.albumID) id = mapview.albumID;

	// Search
	if (isID(id) === false) id = $(".album:hover, .album.active").attr("data-id");
	if (isID(id) === false) id = $(".photo:hover, .photo.active").attr("data-album-id");

	if (isID(id) === true) return id;else return null;
};

/**
 * @returns {boolean}
 */
album.isTagAlbum = function () {
	return album.json && album.json.is_tag_album && album.json.is_tag_album === true;
};

/**
 * @param {?string} photoID
 * @returns {?Photo} the photo model
 */
album.getByID = function (photoID) {
	if (photoID == null || !album.json || !album.json.photos) {
		loadingBar.show("error", "Error: Album json not found !");
		return null;
	}

	var i = 0;
	while (i < album.json.photos.length) {
		if (album.json.photos[i].id === photoID) {
			return album.json.photos[i];
		}
		i++;
	}

	loadingBar.show("error", "Error: photo " + photoID + " not found !");
	return null;
};

/**
 * Returns the sub-album of the current album by ID, if found.
 *
 * Note: If the current album is the special {@link SearchAlbum}, then
 * also {@link TagAlbum} may be returned as a "sub album".
 *
 * @param {?string} albumID
 * @returns {(?Album|?TagAlbum)} the sub-album model
 */
album.getSubByID = function (albumID) {
	// The special `SearchAlbum`  may also contain `TagAlbum` as sub-albums
	if (albumID == null || !album.json || !album.json.albums && !album.json.tag_albums) {
		loadingBar.show("error", "Error: Album json not found!");
		return null;
	}

	var subAlbum = album.json.albums ? album.json.albums.find(function (a) {
		return a.id === albumID;
	}) : null;
	if (subAlbum) {
		return subAlbum;
	}

	var subTagAlbum = album.json.tag_albums ? album.json.tag_albums.find(function (a) {
		return a.id === albumID;
	}) : null;
	if (subTagAlbum) {
		return subTagAlbum;
	}

	loadingBar.show("error", "Error: album " + albumID + " not found!");
	return null;
};

/**
 * @param {string} photoID
 * @returns {void}
 */
album.deleteByID = function (photoID) {
	if (photoID == null || !album.json || !album.json.photos) {
		loadingBar.show("error", "Error: Album json not found !");
		return;
	}

	$.each(album.json.photos, function (i) {
		if (album.json.photos[i].id === photoID) {
			album.json.photos.splice(i, 1);
			return false;
		}
	});
};

/**
 * @param {string} albumID
 * @returns {boolean}
 */
album.deleteSubByID = function (albumID) {
	if (albumID == null || !album.json || !album.json.albums) {
		loadingBar.show("error", "Error: Album json not found !");
		return false;
	}

	var deleted = false;

	$.each(album.json.albums, function (i) {
		if (album.json.albums[i].id === albumID) {
			album.json.albums.splice(i, 1);
			deleted = true;
			return false;
		}
	});

	return deleted;
};

/**
 * @callback AlbumLoadedCB
 * @param {boolean} accessible - `true`, if the album has successfully been
 *                                loaded and parsed; `false`, if the album is
 *                                private or public, but unlocked
 * @returns {void}
 */

/**
 * @param {string} albumID
 * @param {?AlbumLoadedCB} [albumLoadedCB=null]
 *
 * @returns {void}
 */
album.load = function (albumID) {
	var albumLoadedCB = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : null;

	/**
  * @param {Album} data
  */
	var processAlbum = function processAlbum(data) {
		album.json = data;

		if (albumLoadedCB === null) {
			lychee.animate(lychee.content, "contentZoomOut");
		}
		var waitTime = 300;

		// Skip delay when we have a callback `albumLoadedCB`
		// Skip delay when opening a blank Lychee
		if (albumLoadedCB) waitTime = 0;
		if (!visible.albums() && !visible.photo() && !visible.album()) waitTime = 0;

		setTimeout(function () {
			view.album.init();

			if (albumLoadedCB === null) {
				lychee.animate(lychee.content, "contentZoomIn");
				header.setMode("album");
			}

			tabindex.makeFocusable(lychee.content);
			if (lychee.active_focus_on_page_load) {
				// Put focus on first element - either album or photo
				var first_album = $(".album:first");
				if (first_album.length !== 0) {
					first_album.focus();
				} else {
					var first_photo = $(".photo:first");
					if (first_photo.length !== 0) {
						first_photo.focus();
					}
				}
			}
		}, waitTime);
	};

	/**
  * @param {Album} data
  */
	var successHandler = function successHandler(data) {
		processAlbum(data);

		tabindex.makeFocusable(lychee.content);

		if (lychee.active_focus_on_page_load) {
			// Put focus on first element - either album or photo
			var first_album = $(".album:first");
			if (first_album.length !== 0) {
				first_album.focus();
			} else {
				var first_photo = $(".photo:first");
				if (first_photo.length !== 0) {
					first_photo.focus();
				}
			}
		}

		if (albumLoadedCB) albumLoadedCB(true);
	};

	/**
  * @param {XMLHttpRequest} jqXHR
  * @param {Object} params the original JSON parameters of the request
  * @param {?LycheeException} lycheeException the Lychee exception
  * @returns {boolean}
  */
	var errorHandler = function errorHandler(jqXHR, params, lycheeException) {
		if (jqXHR.status !== 401 && jqXHR.status !== 403) {
			// Any other error then unauthenticated or unauthorized
			// shall be handled by the global error handler.
			return false;
		}

		if (lycheeException.exception.endsWith("PasswordRequiredException")) {
			// If a password is required, then try to unlock the album
			// and in case of success, try again to load album with same
			// parameters
			password.getDialog(albumID, function () {
				albums.refresh();
				album.load(albumID, albumLoadedCB);
			});
			return true;
		} else if (albumLoadedCB) {
			// In case we could not successfully load and unlock the album,
			// but we have a callback, we call that and consider the error
			// handled.
			// Note: This case occurs for a single public photo on an
			// otherwise non-public album.
			album.json = null;
			albumLoadedCB(false);
			return true;
		} else {
			// In any other case, let the global error handler deal with the
			// problem.
			return false;
		}
	};

	api.post("Album::get", { albumID: albumID }, successHandler, null, errorHandler);
};

/**
 * Creates a new album.
 *
 * The method optionally calls the provided callback after the new album
 * has been created and passes the ID of the newly created album plus the
 * provided `IDs`.
 *
 * Actually, the callback should enclose all additional parameter it needs.
 * The parameter `IDs` is not needed by this method itself.
 * TODO: Refactor callbacks.
 * Also see comments for {@link TargetAlbumSelectedCB} and
 * {@link contextMenu.move}.
 *
 * @param {string[]}              [IDs=null]      some IDs which are passed on to the callback
 * @param {TargetAlbumSelectedCB} [callback=null] called upon successful creation of the album
 *
 * @returns {void}
 */
album.add = function () {
	var IDs = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : null;
	var callback = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : null;

	/**
  * @param {{title: string}} data
  * @returns {void}
  */
	var action = function action(data) {
		// let title = data.title;

		if (!data.title.trim()) {
			basicModal.error("title");
			return;
		}

		basicModal.close();

		var params = {
			title: data.title,
			parent_id: null
		};

		if (visible.albums() || album.isSmartID(album.json.id) || album.isSearchID(album.json.id)) {
			params.parent_id = null;
		} else if (visible.album()) {
			params.parent_id = album.json.id;
		} else if (visible.photo()) {
			params.parent_id = _photo3.json.album_id;
		}

		api.post("Album::add", params,
		/** @param {Album} _data */
		function (_data) {
			if (IDs != null && callback != null) {
				callback(IDs, _data.id, false); // we do not confirm
			} else {
				albums.refresh();
				lychee.goto(_data.id);
			}
		});
	};

	basicModal.show({
		body: lychee.html(_templateObject, lychee.locale["TITLE_NEW_ALBUM"], lychee.locale["UNTITLED"]),
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

/**
 * @returns {void}
 */
album.addByTags = function () {
	/** @param {{title: string, tags: string}} data */
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

		api.post("Album::addByTags", {
			title: data.title,
			tags: data.tags.split(",")
		},
		/** @param {TagAlbum} _data */
		function (_data) {
			albums.refresh();
			lychee.goto(_data.id);
		});
	};

	basicModal.show({
		body: lychee.html(_templateObject2, lychee.locale["TITLE_NEW_ALBUM"], lychee.locale["UNTITLED"]),
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

/**
 * @param {string} albumID
 * @returns {void}
 */
album.setShowTags = function (albumID) {
	/** @param {{show_tags: string}} data */
	var action = function action(data) {
		if (!data.show_tags.trim()) {
			basicModal.error("show_tags");
			return;
		}
		var new_show_tags = data.show_tags.split(",").map(function (tag) {
			return tag.trim();
		}).filter(function (tag) {
			return tag !== "" && tag.indexOf(",") === -1;
		}).sort();

		basicModal.close();

		if (visible.album()) {
			album.json.show_tags = new_show_tags;
			view.album.show_tags();
		}

		api.post("Album::setShowTags", {
			albumID: albumID,
			show_tags: new_show_tags
		}, function () {
			return album.reload();
		});
	};

	basicModal.show({
		body: lychee.html(_templateObject3, lychee.locale["ALBUM_NEW_SHOWTAGS"], album.json.show_tags.sort().join(", ")),
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

/**
 *
 * @param {string[]} albumIDs
 * @returns {boolean}
 */
album.setTitle = function (albumIDs) {
	var oldTitle = "";

	if (albumIDs.length === 1) {
		// Get old title if only one album is selected
		if (album.json) {
			if (album.getID() === albumIDs[0]) {
				oldTitle = album.json.title;
			} else oldTitle = album.getSubByID(albumIDs[0]).title;
		}
		if (!oldTitle) {
			var a = albums.getByID(albumIDs[0]);
			if (a) oldTitle = a.title;
		}
	}

	/** @param {{title: string}} data */
	var action = function action(data) {
		if (!data.title.trim()) {
			basicModal.error("title");
			return;
		}

		basicModal.close();

		var newTitle = data.title;

		if (visible.album()) {
			if (albumIDs.length === 1 && album.getID() === albumIDs[0]) {
				// Rename only one album

				album.json.title = newTitle;
				view.album.title();

				var _a = albums.getByID(albumIDs[0]);
				if (_a) _a.title = newTitle;
			} else {
				albumIDs.forEach(function (id) {
					album.getSubByID(id).title = newTitle;
					view.album.content.titleSub(id);

					var a = albums.getByID(id);
					if (a) a.title = newTitle;
				});
			}
		} else if (visible.albums()) {
			// Rename all albums

			albumIDs.forEach(function (id) {
				var a = albums.getByID(id);
				if (a) a.title = newTitle;
				view.albums.content.title(id);
			});
		}

		api.post("Album::setTitle", {
			albumIDs: albumIDs,
			title: newTitle
		});
	};

	var inputHTML = lychee.html(_templateObject4, lychee.locale["ALBUM_TITLE"], oldTitle);

	var dialogHTML = albumIDs.length === 1 ? lychee.html(_templateObject5, lychee.locale["ALBUM_NEW_TITLE"], inputHTML) : lychee.html(_templateObject6, lychee.locale["ALBUMS_NEW_TITLE_1"], albumIDs.length, lychee.locale["ALBUMS_NEW_TITLE_2"], inputHTML);

	basicModal.show({
		body: dialogHTML,
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

/**
 * @param {string} albumID
 * @returns {void}
 */
album.setDescription = function (albumID) {
	var oldDescription = album.json.description ? album.json.description : "";

	/** @param {{description: string}} data */
	var action = function action(data) {
		var description = data.description ? data.description : null;

		basicModal.close();

		if (visible.album()) {
			album.json.description = description;
			view.album.description();
		}

		api.post("Album::setDescription", {
			albumID: albumID,
			description: description
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

/**
 * @param {string} photoID
 * @returns {void}
 */
album.toggleCover = function (photoID) {
	album.json.cover_id = album.json.cover_id === photoID ? null : photoID;

	var params = {
		albumID: album.json.id,
		photoID: album.json.cover_id
	};

	api.post("Album::setCover", params, function () {
		view.album.content.cover(photoID);
		if (!album.getParentID()) {
			albums.refresh();
		}
	});
};

/**
 * @param {string} albumID
 * @returns {void}
 */
album.setLicense = function (albumID) {
	var callback = function callback() {
		$("select#license").val(album.json.license === "" ? "none" : album.json.license);
	};

	/** @param {{license: string}} data */
	var action = function action(data) {
		basicModal.close();

		api.post("Album::setLicense", {
			albumID: albumID,
			license: data.license
		}, function () {
			if (visible.album()) {
				album.json.license = data.license;
				view.album.license();
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

/**
 * @param {string} albumID
 * @returns {void}
 */
album.setSorting = function (albumID) {
	var callback = function callback() {
		if (album.json.sorting) {
			$("select#sortingCol").val(album.json.sorting.column);
			$("select#sortingOrder").val(album.json.sorting.order);
		} else {
			$("select#sortingCol").val("");
			$("select#sortingOrder").val("");
		}
	};

	/** @param {{sortingCol: string, sortingOrder: string}} data */
	var action = function action(data) {
		basicModal.close();

		api.post("Album::setSorting", {
			albumID: albumID,
			sorting_column: data.sortingCol,
			sorting_order: data.sortingOrder
		}, function () {
			if (visible.album()) {
				album.reload();
			}
		});
	};

	var msg = lychee.html(_templateObject9, lychee.locale["SORT_PHOTO_BY_1"], lychee.locale["SORT_PHOTO_SELECT_1"], lychee.locale["SORT_PHOTO_SELECT_2"], lychee.locale["SORT_PHOTO_SELECT_3"], lychee.locale["SORT_PHOTO_SELECT_4"], lychee.locale["SORT_PHOTO_SELECT_5"], lychee.locale["SORT_PHOTO_SELECT_6"], lychee.locale["SORT_PHOTO_SELECT_7"], lychee.locale["SORT_PHOTO_BY_2"], lychee.locale["SORT_ASCENDING"], lychee.locale["SORT_DESCENDING"], lychee.locale["SORT_PHOTO_BY_3"]);

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

/**
 * Sets the accessibility attributes of an album.
 *
 * @param {string} albumID
 * @returns {void}
 */
album.setProtectionPolicy = function (albumID) {
	var action = function action(data) {
		albums.refresh();

		// TODO: If the modal dialog would provide us with proper boolean values for the checkboxes as part of `data` the same way as it does for text inputs, then we would not need these slow and awkward jQeury selectors
		album.json.is_nsfw = $('.basicModal .switch input[name="is_nsfw"]:checked').length === 1;
		album.json.is_public = $('.basicModal .switch input[name="is_public"]:checked').length === 1;
		album.json.grants_full_photo = $('.basicModal .choice input[name="grants_full_photo"]:checked').length === 1;
		album.json.requires_link = $('.basicModal .choice input[name="requires_link"]:checked').length === 1;
		album.json.is_downloadable = $('.basicModal .choice input[name="is_downloadable"]:checked').length === 1;
		album.json.is_share_button_visible = $('.basicModal .choice input[name="is_share_button_visible"]:checked').length === 1;
		album.json.has_password = $('.basicModal .choice input[name="has_password"]:checked').length === 1;
		var newPassword = $('.basicModal .choice input[name="passwordtext"]').val() || null;

		// Modal input has been processed, now it can be closed
		basicModal.close();

		// Set data and refresh view
		if (visible.album()) {
			view.album.nsfw();
			view.album.public();
			view.album.requiresLink();
			view.album.downloadable();
			view.album.shareButtonVisible();
			view.album.password();
		}

		var params = {
			albumID: albumID,
			grants_full_photo: album.json.grants_full_photo,
			is_public: album.json.is_public,
			is_nsfw: album.json.is_nsfw,
			requires_link: album.json.requires_link,
			is_downloadable: album.json.is_downloadable,
			is_share_button_visible: album.json.is_share_button_visible
		};
		if (album.json.has_password) {
			if (newPassword) {
				// We send the password only if there's been a change; that way the
				// server will keep the current password if it wasn't changed.
				params.password = newPassword;
			}
		} else {
			params.password = null;
		}

		api.post("Album::setProtectionPolicy", params);
	};

	var msg = lychee.html(_templateObject10, lychee.locale["ALBUM_PUBLIC"], lychee.locale["ALBUM_PUBLIC_EXPL"], build.iconic("check"), lychee.locale["ALBUM_FULL"], lychee.locale["ALBUM_FULL_EXPL"], build.iconic("check"), lychee.locale["ALBUM_HIDDEN"], lychee.locale["ALBUM_HIDDEN_EXPL"], build.iconic("check"), lychee.locale["ALBUM_DOWNLOADABLE"], lychee.locale["ALBUM_DOWNLOADABLE_EXPL"], build.iconic("check"), lychee.locale["ALBUM_SHARE_BUTTON_VISIBLE"], lychee.locale["ALBUM_SHARE_BUTTON_VISIBLE_EXPL"], build.iconic("check"), lychee.locale["ALBUM_PASSWORD_PROT"], lychee.locale["ALBUM_PASSWORD_PROT_EXPL"], lychee.locale["PASSWORD"], lychee.locale["ALBUM_NSFW"], lychee.locale["ALBUM_NSFW_EXPL"]);

	var dialogSetupCB = function dialogSetupCB() {
		// TODO: If the modal dialog would provide this callback with proper jQuery objects for all input/select/choice elements, then we would not need these jQuery selectors
		$('.basicModal .switch input[name="is_public"]').prop("checked", album.json.is_public);
		$('.basicModal .switch input[name="is_nsfw"]').prop("checked", album.json.is_nsfw);
		if (album.json.is_public) {
			$(".basicModal .choice input").attr("disabled", false);
			// Initialize options based on album settings.
			$('.basicModal .choice input[name="grants_full_photo"]').prop("checked", album.json.grants_full_photo);
			$('.basicModal .choice input[name="requires_link"]').prop("checked", album.json.requires_link);
			$('.basicModal .choice input[name="is_downloadable"]').prop("checked", album.json.is_downloadable);
			$('.basicModal .choice input[name="is_share_button_visible"]').prop("checked", album.json.is_share_button_visible);
			$('.basicModal .choice input[name="has_password"]').prop("checked", album.json.has_password);
			if (album.json.has_password) {
				$('.basicModal .choice input[name="passwordtext"]').show();
			}
		} else {
			$(".basicModal .choice input").attr("disabled", true);
			// Initialize options based on global settings.
			$('.basicModal .choice input[name="grants_full_photo"]').prop("checked", lychee.grants_full_photo);
			$('.basicModal .choice input[name="requires_link"]').prop("checked", false);
			$('.basicModal .choice input[name="is_downloadable"]').prop("checked", lychee.is_downloadable);
			$('.basicModal .choice input[name="is_share_button_visible"]').prop("checked", lychee.is_share_button_visible);
			$('.basicModal .choice input[name="has_password"]').prop("checked", false);
			$('.basicModal .choice input[name="passwordtext"]').hide();
		}

		$('.basicModal .switch input[name="is_public"]').on("change", function () {
			$(".basicModal .choice input").attr("disabled", $(this).prop("checked") !== true);
		});

		$('.basicModal .choice input[name="has_password"]').on("change", function () {
			if ($(this).prop("checked") === true) {
				$('.basicModal .choice input[name="passwordtext"]').show().focus();
			} else {
				$('.basicModal .choice input[name="passwordtext"]').hide();
			}
		});
	};

	basicModal.show({
		body: msg,
		callback: dialogSetupCB,
		buttons: {
			action: {
				title: lychee.locale["ALBUM_SHARING_CONFIRM"],
				fn: action
			},
			cancel: {
				title: lychee.locale["CANCEL"],
				fn: basicModal.close
			}
		}
	});
};

/**
 * Lets a user update the sharing settings of an album.
 *
 * @param {string} albumID
 * @returns {void}
 */
album.shareUsers = function (albumID) {
	var action = function action(data) {
		basicModal.close();

		/** @type {number[]} */
		var sharingToAdd = [];
		/** @type {number[]} */
		var sharingToDelete = [];
		$(".basicModal .choice input").each(function (_, input) {
			var $input = $(input);
			if ($input.is(":checked")) {
				if ($input.data("sharingId") === undefined) {
					// Input is checked but has no sharing id => new share to create
					sharingToAdd.push(Number.parseInt(input.name));
				}
			} else {
				var sharingId = $input.data("sharingId");
				if (sharingId !== undefined) {
					// Input is not checked but has a sharing id => existing share to remove
					sharingToDelete.push(Number.parseInt(sharingId));
				}
			}
		});

		if (sharingToDelete.length > 0) {
			api.post("Sharing::delete", {
				shareIDs: sharingToDelete
			});
		}
		if (sharingToAdd.length > 0) {
			api.post("Sharing::add", {
				albumIDs: [albumID],
				userIDs: sharingToAdd
			});
		}
	};

	var msg = "<form id=\"sharing_people_form\"><p>" + lychee.locale["WAIT_FETCH_DATA"] + "</p></form>";

	var dialogSetupCB = function dialogSetupCB() {
		/** @param {SharingInfo} data */
		var successCallback = function successCallback(data) {
			var sharingForm = $("#sharing_people_form");
			sharingForm.empty();
			if (data.users.length !== 0) {
				sharingForm.append("<p>" + lychee.locale["SHARING_ALBUM_USERS_LONG_MESSAGE"] + "</p>");
				// Fill with the list of users
				data.users.forEach(function (user) {
					sharingForm.append(lychee.html(_templateObject11, user.id, build.iconic("check"), user.username));
				});
				data.shared.filter(function (val) {
					return val.album_id === albumID;
				}).forEach(function (sharing) {
					// Check all the shares that already exist, and store their sharing id on the element
					var elem = $(".basicModal .choice input[name=\"" + sharing.user_id + "\"]");
					elem.prop("checked", true);
					elem.data("sharingId", sharing.id);
				});
			} else {
				sharingForm.append("<p>" + lychee.locale["SHARING_ALBUM_USERS_NO_USERS"] + "</p>");
			}
		};

		api.post("Sharing::list", {}, successCallback);
	};

	basicModal.show({
		body: msg,
		callback: dialogSetupCB,
		buttons: {
			action: {
				title: lychee.locale["ALBUM_SHARING_CONFIRM"],
				fn: action
			},
			cancel: {
				title: lychee.locale["CANCEL"],
				fn: basicModal.close
			}
		}
	});
};

/**
 * Toggles the NSFW attribute of the currently loaded album.
 *
 * @returns {void}
 */
album.toggleNSFW = function () {
	album.json.is_nsfw = !album.json.is_nsfw;

	view.album.nsfw();

	api.post("Album::setNSFW", {
		albumID: album.json.id,
		is_nsfw: album.json.is_nsfw
	}, function () {
		return albums.refresh();
	});
};

/**
 * @param {string} service - either `"twitter"`, `"facebook"` or `"mail"`
 * @returns {void}
 */
album.share = function (service) {
	if (album.json.hasOwnProperty("is_share_button_visible") && !album.json.is_share_button_visible) {
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

/**
 * @returns {void}
 */
album.qrCode = function () {
	if (album.json.hasOwnProperty("is_share_button_visible") && !album.json.is_share_button_visible) {
		return;
	}

	var msg = lychee.html(_templateObject12);

	basicModal.show({
		body: msg,
		callback: function callback() {
			QrCreator.render({
				text: location.href,
				radius: 0.0,
				ecLevel: "H",
				fill: "#000000",
				background: "#FFFFFF",
				size: 440 // 500px (modal width) - 2*30px (padding)
			}, document.getElementById("qr-code"));
		},
		buttons: {
			cancel: {
				title: lychee.locale["CLOSE"],
				fn: basicModal.close
			}
		}
	});
};

/**
 * @param {string[]} albumIDs
 * @returns {void}
 */
album.getArchive = function (albumIDs) {
	location.href = "api/Album::getArchive?albumIDs=" + albumIDs.join();
};

/**
 * @param {string[]} albumIDs
 * @param {?string} albumID
 * @param {string} op1
 * @param {string} op2
 * @param {string} ops
 * @returns {string} the HTML content of the dialog
 */
album.buildMessage = function (albumIDs, albumID, op1, op2, ops) {
	var title = "";
	var sTitle = "";
	var msg = "";

	// Get title of first album
	if (albumID === null) {
		title = lychee.locale["ROOT"];
	} else {
		var album1 = albums.getByID(albumID);
		if (album1) {
			title = album1.title;
		}
	}

	// Fallback for first album without a title
	if (!title) title = lychee.locale["UNTITLED"];

	if (albumIDs.length === 1) {
		// Get title of second album
		var album2 = albums.getByID(albumIDs[0]);
		if (album2) {
			sTitle = album2.title;
		}

		// Fallback for second album without a title
		if (!sTitle) sTitle = lychee.locale["UNTITLED"];

		msg = lychee.html(_templateObject13, lychee.locale[op1], sTitle, lychee.locale[op2], title);
	} else {
		msg = lychee.html(_templateObject14, lychee.locale[ops], title);
	}

	return msg;
};

/**
 * @param {string[]} albumIDs
 * @returns {void}
 */
album.delete = function (albumIDs) {
	var action = {};
	var cancel = {};
	var msg = "";

	action.fn = function () {
		basicModal.close();

		api.post("Album::delete", {
			albumIDs: albumIDs
		}, function () {
			if (visible.albums()) {
				albumIDs.forEach(function (id) {
					view.albums.content.delete(id);
					albums.deleteByID(id);
				});
			} else if (visible.album()) {
				albums.refresh();
				if (albumIDs.length === 1 && album.getID() === albumIDs[0]) {
					lychee.goto(album.getParentID());
				} else {
					albumIDs.forEach(function (id) {
						album.deleteSubByID(id);
						view.album.content.deleteSub(id);
					});
				}
			}
		});
	};

	if (albumIDs.length === 1 && albumIDs[0] === "unsorted") {
		action.title = lychee.locale["CLEAR_UNSORTED"];
		cancel.title = lychee.locale["KEEP_UNSORTED"];

		msg = "<p>" + lychee.locale["DELETE_UNSORTED_CONFIRM"] + "</p>";
	} else if (albumIDs.length === 1) {
		var albumTitle = "";

		action.title = lychee.locale["DELETE_ALBUM_QUESTION"];
		cancel.title = lychee.locale["KEEP_ALBUM"];

		// Get title
		if (album.json) {
			if (album.getID() === albumIDs[0]) {
				albumTitle = album.json.title;
			} else albumTitle = album.getSubByID(albumIDs[0]).title;
		}
		if (!albumTitle) {
			var a = albums.getByID(albumIDs[0]);
			if (a) albumTitle = a.title;
		}

		// Fallback for album without a title
		if (!albumTitle) albumTitle = lychee.locale["UNTITLED"];

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

/**
 * @param {string[]} albumIDs
 * @param {string} albumID
 * @param {boolean} confirm
 */
album.merge = function (albumIDs, albumID) {
	var confirm = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : true;

	var action = function action() {
		basicModal.close();

		api.post("Album::merge", {
			albumID: albumID,
			albumIDs: albumIDs
		}, function () {
			return album.reload();
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

/**
 * @param {string[]} albumIDs source IDs
 * @param {string}   albumID  target ID
 * @param {boolean}  confirm  show confirmation dialog?
 */
album.setAlbum = function (albumIDs, albumID) {
	var confirm = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : true;

	var action = function action() {
		basicModal.close();

		api.post("Album::move", {
			albumID: albumID,
			albumIDs: albumIDs
		}, function () {
			return album.reload();
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

/**
 * @returns {void}
 */
album.apply_nsfw_filter = function () {
	if (lychee.nsfw_visible) {
		$('.album[data-nsfw="1"]').show();
	} else {
		$('.album[data-nsfw="1"]').hide();
	}
};

/**
 * Determines whether the user can upload to the currently active album.
 *
 * For special cases of no album / smart album / etc. we return true.
 * It's only for regular, non-matching albums that we return false.
 *
 * @returns {boolean}
 */
album.isUploadable = function () {
	if (lychee.admin) {
		return true;
	}
	if (lychee.publicMode || !lychee.may_upload) {
		return false;
	}

	// TODO: Comparison of numeric user IDs (instead of names) should be more robust
	return album.json === null || !album.json.owner_name || album.json.owner_name === lychee.username;
};

/**
 * @param {Photo} data
 */
album.updatePhoto = function (data) {
	/**
  * @param {?SizeVariant} src
  * @returns {?SizeVariant}
  */
	var deepCopySizeVariant = function deepCopySizeVariant(src) {
		if (src === undefined || src === null) return null;
		return {
			type: src.type,
			url: src.url,
			width: src.width,
			height: src.height,
			filesize: src.filesize
		};
	};

	if (album.json && album.json.photos) {
		var _photo2 = album.json.photos.find(function (p) {
			return p.id === data.id;
		});

		// Deep copy size variants
		_photo2.size_variants = {
			thumb: deepCopySizeVariant(data.size_variants.thumb),
			thumb2x: deepCopySizeVariant(data.size_variants.thumb2x),
			small: deepCopySizeVariant(data.size_variants.small),
			small2x: deepCopySizeVariant(data.size_variants.small2x),
			medium: deepCopySizeVariant(data.size_variants.medium),
			medium2x: deepCopySizeVariant(data.size_variants.medium2x),
			original: deepCopySizeVariant(data.size_variants.original)
		};
		view.album.content.updatePhoto(_photo2);
		albums.refresh();
	}
};

/**
 * @returns {void}
 */
album.reload = function () {
	var albumID = album.getID();

	album.refresh();
	albums.refresh();

	if (visible.album()) lychee.goto(albumID);else lychee.goto();
};

/**
 * @returns {void}
 */
album.refresh = function () {
	album.json = null;
};

/**
 * @returns {void}
 */
album.deleteTrack = function () {
	album.json.track_url = null;

	api.post("Album::deleteTrack", {
		albumID: album.json.id
	});
};

/**
 * @description Takes care of every action albums can handle and execute.
 */

var albums = {
	/** @type {?Albums} */
	json: null
};

/**
 * @returns {void}
 */
albums.load = function () {
	var startTime = new Date().getTime();

	lychee.animate(lychee.content, "contentZoomOut");

	/**
  * @param {Albums} data
  */
	var successCallback = function successCallback(data) {
		// Smart Albums
		if (data.smart_albums.length > 0) albums.localizeSmartAlbums(data.smart_albums);

		albums.json = data;

		// Skip delay when opening a blank Lychee
		var skipDelay = !visible.albums() && !visible.photo() && !visible.album() || visible.album() && lychee.content.html() === "";
		// Calculate delay
		var durationTime = new Date().getTime() - startTime;
		var waitTime = durationTime > 300 || skipDelay ? 0 : 300 - durationTime;

		setTimeout(function () {
			header.setMode("albums");
			view.albums.init();
			lychee.animate(lychee.content, "contentZoomIn");

			tabindex.makeFocusable(lychee.content);

			if (lychee.active_focus_on_page_load) {
				// Put focus on first element - either album or photo
				var first_album = $(".album:first");
				if (first_album.length !== 0) {
					first_album.focus();
				} else {
					var first_photo = $(".photo:first");
					if (first_photo.length !== 0) {
						first_photo.focus();
					}
				}
			}

			setTimeout(function () {
				lychee.footer_show();
			}, 300);
		}, waitTime);
	};

	if (albums.json === null) {
		api.post("Albums::get", {}, successCallback);
	} else {
		setTimeout(function () {
			header.setMode("albums");
			view.albums.init();
			lychee.animate(lychee.content, "contentZoomIn");

			tabindex.makeFocusable(lychee.content);

			if (lychee.active_focus_on_page_load) {
				// Put focus on first element - either album or photo
				var first_album = $(".album:first");
				if (first_album.length !== 0) {
					first_album.focus();
				} else {
					var first_photo = $(".photo:first");
					if (first_photo.length !== 0) {
						first_photo.focus();
					}
				}
			}
		}, 300);
	}
};

/**
 * @param {(Album|TagAlbum|SmartAlbum)} album
 * @returns {void}
 */
albums.parse = function (album) {
	if (!album.thumb) {
		album.thumb = {
			id: "",
			thumb: album.has_password ? "img/password.svg" : "img/no_images.svg",
			type: "image/svg+xml",
			thumb2x: null
		};
	}
};

/**
 * Normalizes the built-in smart albums.
 *
 * @param {SmartAlbums} data
 * @returns {void}
 */
albums.localizeSmartAlbums = function (data) {
	if (data.unsorted) {
		data.unsorted.title = lychee.locale["UNSORTED"];
	}

	if (data.starred) {
		data.starred.title = lychee.locale["STARRED"];
	}

	if (data.public) {
		data.public.title = lychee.locale["PUBLIC"];
		// TODO: Why do we need to set these two attributes? What component relies upon them, what happens if we don't set them? Is it legacy?
		data.public.is_public = true;
		data.public.requires_link = true;
	}

	if (data.recent) {
		data.recent.title = lychee.locale["RECENT"];
	}
};

/**
 * @param {?string} albumID
 * @returns {boolean}
 */
albums.isShared = function (albumID) {
	if (albumID == null) return false;
	if (!albums.json) return false;
	if (!albums.json.albums) return false;

	var found = false;

	/**
  * @this {Album}
  * @returns {boolean}
  */
	var func = function func() {
		if (this.id === albumID) {
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

/**
 * @param {?string} albumID
 * @returns {(null|Album|TagAlbum|SmartAlbum)}
 */
albums.getByID = function (albumID) {
	if (albumID == null) return null;
	if (!albums.json) return null;
	if (!albums.json.albums) return null;

	if (albums.json.smart_albums.hasOwnProperty(albumID)) {
		return albums.json.smart_albums[albumID];
	}

	var result = albums.json.tag_albums.find(function (tagAlbum) {
		return tagAlbum.id === albumID;
	});
	if (result) {
		return result;
	}

	result = albums.json.albums.find(function (album) {
		return album.id === albumID;
	});
	if (result) {
		return result;
	}

	result = albums.json.shared_albums.find(function (album) {
		return album.id === albumID;
	});
	if (result) {
		return result;
	}

	return null;
};

/**
 * Deletes a top-level album by ID from the cached JSON for albums.
 *
 * The method is called by {@link album.delete} after a top-level album has
 * successfully been deleted at the server-side.
 *
 * @param {?string} albumID
 * @returns {void}
 */
albums.deleteByID = function (albumID) {
	if (albumID == null) return;
	if (!albums.json) return;
	if (!albums.json.albums) return;

	var idx = albums.json.albums.findIndex(function (a) {
		return a.id === albumID;
	});
	albums.json.albums.splice(idx, 1);

	if (idx !== -1) return;

	idx = albums.json.shared_albums.findIndex(function (a) {
		return a.id === albumID;
	});
	albums.json.shared_albums.splice(idx, 1);

	if (idx !== -1) return;

	idx = albums.json.tag_albums.findIndex(function (a) {
		return a.id === albumID;
	});
	albums.json.tag_albums.splice(idx, 1);
};

/**
 * @returns {void}
 */
albums.refresh = function () {
	albums.json = null;
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

	return lychee.html(_templateObject17, classes, icon);
};

/**
 * @param {string} title
 * @returns {string}
 */
build.divider = function (title) {
	return lychee.html(_templateObject18, title);
};

/**
 * @param {string} id
 * @returns {string}
 */
build.editIcon = function (id) {
	return lychee.html(_templateObject19, id, build.iconic("pencil"));
};

/**
 * @param {number} top
 * @param {number} left
 * @returns {string}
 */
build.multiselect = function (top, left) {
	return lychee.html(_templateObject20, top, left);
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

	var html = lychee.html(_templateObject21, disabled ? "disabled" : "", data.is_nsfw && lychee.nsfw_blur ? "blurred" : "", data.id, data.is_nsfw ? "1" : "0", tabindex.get_next_tab_index(), build.getAlbumThumb(data), build.getAlbumThumb(data), build.getAlbumThumb(data), data.title, data.title, subtitle);

	if (album.isUploadable() && !disabled) {
		var isCover = album.json && album.json.cover_id && data.thumb.id === album.json.cover_id;
		html += lychee.html(_templateObject22, data.is_nsfw ? "badge--nsfw" : "", build.iconic("warning"), data.id === SmartAlbumID.STARRED ? "badge--star" : "", build.iconic("star"), data.id === SmartAlbumID.RECENT ? "badge--visible badge--list" : "", build.iconic("clock"), data.id === SmartAlbumID.PUBLIC || data.is_public ? "badge--visible" : "", data.requires_link ? "badge--hidden" : "badge--not--hidden", build.iconic("eye"), data.id === SmartAlbumID.UNSORTED ? "badge--visible" : "", build.iconic("list"), data.has_password ? "badge--visible" : "", build.iconic("lock-locked"), data.is_tag_album ? "badge--tag" : "", build.iconic("tag"), isCover ? "badge--cover" : "", build.iconic("folder-cover"));
	}

	if (data.albums && data.albums.length > 0 || data.has_albums) {
		html += lychee.html(_templateObject23, build.iconic("layers"));
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

	html += lychee.html(_templateObject24, disabled ? "disabled" : "", data.album_id, data.id, tabindex.get_next_tab_index(), thumbnail, data.title, data.title);

	if (data.taken_at !== null) html += lychee.html(_templateObject25, build.iconic("camera-slr"), lychee.locale.printDateTime(data.taken_at));else html += lychee.html(_templateObject26, lychee.locale.printDateTime(data.created_at));

	html += "</div>";

	if (album.isUploadable()) {
		// Note, `album.json` might be null, if the photo is displayed as
		// part of a search result and therefore the actual parent album
		// is not loaded. (The "parent" album is the virtual "search album"
		// in this case).
		// This also means that the displayed variant of the public badge of
		// a photo depends on the availability of the parent album.
		// This seems to be an undesired but unavoidable side effect.
		html += lychee.html(_templateObject27, data.is_starred ? "badge--star" : "", build.iconic("star"), data.is_public && album.json && !album.json.is_public ? "badge--visible badge--hidden" : "", build.iconic("eye"), isCover ? "badge--cover" : "", build.iconic("folder-cover"));
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

	return lychee.html(_templateObject28, data.title ? data.title : lychee.locale["UNTITLED"]) + (overlay !== "" ? "<p>" + overlay + "</p>" : "") + "\n\t\t</div>\n\t\t";
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
		html += lychee.html(_templateObject29, areControlsVisible ? "" : "full", autoplay ? "autoplay" : "", tabindex.get_next_tab_index(), data.size_variants.original.url);
	} else if (data.type.indexOf("raw") > -1 && data.size_variants.medium === null) {
		html += lychee.html(_templateObject30, areControlsVisible ? "" : "full", tabindex.get_next_tab_index());
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

		html += lychee.html(_templateObject31, img);
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

	html += lychee.html(_templateObject32, build.iconic(type));

	switch (type) {
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

/**
 * @param {string}                                           title the title of the dialog
 * @param {(FileList|File[]|DropboxFile[]|{name: string}[])} files a list of file entries to be shown in the dialog
 * @returns {string}                                                the HTML fragment for the dialog
 */
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

	return lychee.html(_templateObject36, name);
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
				html += lychee.html(_templateObject37, a_class, tag, index, build.iconic("x"));
			} else {
				html += lychee.html(_templateObject38, a_class, tag);
			}
		});
	} else {
		html = lychee.html(_templateObject39, lychee.locale["NO_TAGS"]);
	}

	return html;
};

/**
 * @param {User} user
 * @returns {string}
 */
build.user = function (user) {
	return lychee.html(_templateObject40, user.id, user.id, user.username, user.id, user.id);
};

/**
 * @param {WebAuthnCredential} credential
 * @returns {string}
 */
build.u2f = function (credential) {
	return lychee.html(_templateObject41, credential.id, credential.id, credential.id.slice(0, 30), credential.id);
};

/**
 * @description This module is used for the context menu.
 */

var contextMenu = {};

/**
 * @param {jQuery.Event} e
 */
contextMenu.add = function (e) {
	var items = [{ title: build.iconic("image") + lychee.locale["UPLOAD_PHOTO"], fn: function fn() {
			return $("#upload_files").click();
		} }, {}, { title: build.iconic("link-intact") + lychee.locale["IMPORT_LINK"], fn: function fn() {
			return upload.start.url();
		} }, { title: build.iconic("dropbox", "ionicons") + lychee.locale["IMPORT_DROPBOX"], fn: function fn() {
			return upload.start.dropbox();
		} }, { title: build.iconic("terminal") + lychee.locale["IMPORT_SERVER"], fn: function fn() {
			return upload.start.server();
		} }, {}, { title: build.iconic("folder") + lychee.locale["NEW_ALBUM"], fn: function fn() {
			return album.add();
		} }];

	if (visible.albums()) {
		items.push({ title: build.iconic("tags") + lychee.locale["NEW_TAG_ALBUM"], fn: function fn() {
				return album.addByTags();
			} });
	} else if (album.isSmartID(album.getID()) || album.isSearchID(album.getID())) {
		// remove Import and New album if smart album or search results
		items.splice(1);
	}

	if (!lychee.admin) {
		// remove import from dropbox and server if not admin
		items.splice(3, 2);
	} else if (!lychee.dropboxKey || lychee.dropboxKey === "") {
		// remove import from dropbox if dropboxKey not set
		items.splice(3, 1);
	}

	if (visible.album() && album.isUploadable()) {
		// prepend further buttons if menu bar is reduced on small screens
		var albumID = album.getID();
		if (album.isTagAlbum()) {
			// For tag albums the context menu is normally not used.
			items = [];
		}
		if (albumID.length === 24 || albumID === SmartAlbumID.UNSORTED) {
			if (albumID !== SmartAlbumID.UNSORTED) {
				var button_visibility_album = $("#button_visibility_album");
				if (button_visibility_album && button_visibility_album.css("display") === "none") {
					items.unshift({
						title: build.iconic("eye") + lychee.locale["VISIBILITY_ALBUM"],
						visible: lychee.enable_button_visibility,
						fn: function fn() {
							return album.setProtectionPolicy(albumID);
						}
					});
				}
			}
			var button_trash_album = $("#button_trash_album");
			if (button_trash_album && button_trash_album.css("display") === "none") {
				items.unshift({
					title: build.iconic("trash") + lychee.locale["DELETE_ALBUM"],
					visible: lychee.enable_button_trash,
					fn: function fn() {
						return album.delete([albumID]);
					}
				});
			}
			if (albumID !== SmartAlbumID.UNSORTED) {
				if (!album.isTagAlbum()) {
					var button_move_album = $("#button_move_album");
					if (button_move_album && button_move_album.css("display") === "none") {
						items.unshift({
							title: build.iconic("folder") + lychee.locale["MOVE_ALBUM"],
							visible: lychee.enable_button_move,
							fn: function fn(event) {
								return contextMenu.move([albumID], event, album.setAlbum, "ROOT", album.getParentID() !== null);
							}
						});
					}
				}
				var button_nsfw_album = $("#button_nsfw_album");
				if (button_nsfw_album && button_nsfw_album.css("display") === "none") {
					items.unshift({
						title: build.iconic("warning") + lychee.locale["ALBUM_MARK_NSFW"],
						visible: true,
						fn: function fn() {
							return album.toggleNSFW();
						}
					});
				}
			}
			if (!album.isSmartID(albumID) && lychee.map_display) {
				// display track add button if it's a regular album
				items.push({}, { title: build.iconic("location") + lychee.locale["UPLOAD_TRACK"], fn: function fn() {
						return $("#upload_track_file").click();
					} });
				if (album.json.track_url) {
					items.push({ title: build.iconic("trash") + lychee.locale["DELETE_TRACK"], fn: album.deleteTrack });
				}
			}
		}
	}

	basicContext.show(items, e.originalEvent);

	upload.notify();
};

/**
 * @param {string} albumID
 * @param {jQuery.Event} e
 *
 * @returns {void}
 */
contextMenu.album = function (albumID, e) {
	// Notice for 'Merge':
	// fn must call basicContext.close() first,
	// in order to keep the selection

	if (album.isSmartID(albumID) || album.isSearchID(albumID)) return;

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
		visible: true,
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

/**
 * @param {string[]} albumIDs
 * @param {jQuery.Event} e
 *
 * @returns {void}
 */
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
		visible: true,
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

/**
 * @callback ContextMenuActionCB
 *
 * @param {(Photo|Album)} entity
 */

/**
 * @callback ContextMenuEventCB
 *
 * @param {jQuery.Event} [e]
 * @returns {void}
 */

/**
 * @param {(Photo|Album)[]} lists
 * @param {string[]} exclude list of IDs to exclude
 * @param {ContextMenuActionCB} action
 * @param {?string} [parentID=null] parentID
 * @param {number} [layer=0]
 *
 * @returns {{title: string, disabled: boolean, fn: ContextMenuEventCB}[]}
 */
contextMenu.buildList = function (lists, exclude, action) {
	var parentID = arguments.length > 3 && arguments[3] !== undefined ? arguments[3] : null;
	var layer = arguments.length > 4 && arguments[4] !== undefined ? arguments[4] : 0;

	var items = [];

	lists.forEach(function (item) {
		if ((layer !== 0 || item.parent_id) && item.parent_id !== parentID) return;

		var thumb = "img/no_cover.svg";
		if (item.thumb && item.thumb.thumb) {
			if (item.thumb.thumb === "uploads/thumb/") {
				if (item.thumb.type && item.thumb.type.indexOf("video") > -1) {
					thumb = "img/play-icon.png";
				}
			} else {
				thumb = item.thumb.thumb;
			}
		} else if (item.size_variants) {
			if (item.size_variants.thumb === null) {
				if (item.type && item.type.indexOf("video") > -1) {
					thumb = "img/play-icon.png";
				}
			} else {
				thumb = item.size_variants.thumb.url;
			}
		}

		if (!item.title) item.title = lychee.locale["UNTITLED"];

		var prefix = layer > 0 ? "&nbsp;&nbsp;".repeat(layer - 1) + "└ " : "";

		var html = lychee.html(_templateObject42, prefix, thumb, item.title);

		items.push({
			title: html,
			disabled: exclude.findIndex(function (id) {
				return id === item.id;
			}) !== -1,
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
	});

	return items;
};

/**
 * @param {?string} albumID
 * @param {jQuery.Event} e
 *
 * @returns {void}
 */
contextMenu.albumTitle = function (albumID, e) {
	api.post("Albums::tree", {}, function (data) {
		var items = [];

		items = items.concat({ title: lychee.locale["ROOT"], disabled: albumID === null, fn: function fn() {
				return lychee.goto();
			} });

		if (data.albums && data.albums.length > 0) {
			items = items.concat({});
			items = items.concat(contextMenu.buildList(data.albums, albumID !== null ? [albumID] : [], function (a) {
				return lychee.goto(a.id);
			}));
		}

		if (data.shared_albums && data.shared_albums.length > 0) {
			items = items.concat({});
			items = items.concat(contextMenu.buildList(data.shared_albums, albumID !== null ? [albumID] : [], function (a) {
				return lychee.goto(a.id);
			}));
		}

		if (albumID !== null && !album.isSmartID(albumID) && !album.isSearchID(albumID) && album.isUploadable()) {
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

/**
 * @param {string} photoID
 * @param {jQuery.Event} e
 *
 * @returns {void}
 */
contextMenu.photo = function (photoID, e) {
	var coverActive = photoID === album.json.cover_id;

	var isPhotoStarred = album.getByID(photoID).is_starred;

	var items = [{
		title: build.iconic("star") + (isPhotoStarred ? lychee.locale["UNSTAR"] : lychee.locale["STAR"]),
		fn: function fn() {
			return _photo3.setStar([photoID], !isPhotoStarred);
		}
	}, { title: build.iconic("tag") + lychee.locale["TAGS"], fn: function fn() {
			return _photo3.editTags([photoID]);
		} },
	// for future work, use a list of all the ancestors.
	{
		title: build.iconic("folder-cover", coverActive ? "active" : "") + lychee.locale[coverActive ? "REMOVE_COVER" : "SET_COVER"],
		fn: function fn() {
			return album.toggleCover(photoID);
		}
	}, {}, { title: build.iconic("pencil") + lychee.locale["RENAME"], fn: function fn() {
			return _photo3.setTitle([photoID]);
		} }, {
		title: build.iconic("layers") + lychee.locale["COPY_TO"],
		fn: function fn() {
			basicContext.close();
			contextMenu.move([photoID], e, _photo3.copyTo);
		}
	},
	// Notice for 'Move':
	// fn must call basicContext.close() first,
	// in order to keep the selection
	{
		title: build.iconic("folder") + lychee.locale["MOVE"],
		fn: function fn() {
			basicContext.close();
			contextMenu.move([photoID], e, _photo3.setAlbum);
		}
	}, { title: build.iconic("trash") + lychee.locale["DELETE"], fn: function fn() {
			return _photo3.delete([photoID]);
		} }, { title: build.iconic("cloud-download") + lychee.locale["DOWNLOAD"], fn: function fn() {
			return _photo3.getArchive([photoID]);
		} }];
	if (album.isSmartID(album.getID()) || album.isSearchID(album.getID) || album.isTagAlbum()) {
		// Cover setting not supported for smart or tag albums and search results.
		items.splice(2, 1);
	}

	$('.photo[data-id="' + photoID + '"]').addClass("active");

	basicContext.show(items, e.originalEvent, contextMenu.close);
};

/**
 * @param {string[]} photoIDs
 * @param {jQuery.Event} e
 */
contextMenu.photoMulti = function (photoIDs, e) {
	multiselect.stopResize();

	var arePhotosStarred = false;
	var arePhotosNotStarred = false;
	photoIDs.forEach(function (id) {
		if (album.getByID(id).is_starred) {
			arePhotosStarred = true;
		} else {
			arePhotosNotStarred = true;
		}
	});

	var items = [
	// Only show the star/unstar menu item when the selected photos are
	// consistently either all starred or all not starred.
	{
		title: build.iconic("star") + (arePhotosNotStarred ? lychee.locale["STAR_ALL"] : lychee.locale["UNSTAR_ALL"]),
		visible: !(arePhotosStarred && arePhotosNotStarred),
		fn: function fn() {
			return _photo3.setStar(photoIDs, arePhotosNotStarred);
		}
	}, { title: build.iconic("tag") + lychee.locale["TAGS_ALL"], fn: function fn() {
			return _photo3.editTags(photoIDs);
		} }, {}, { title: build.iconic("pencil") + lychee.locale["RENAME_ALL"], fn: function fn() {
			return _photo3.setTitle(photoIDs);
		} }, {
		title: build.iconic("layers") + lychee.locale["COPY_ALL_TO"],
		fn: function fn() {
			basicContext.close();
			contextMenu.move(photoIDs, e, _photo3.copyTo);
		}
	}, {
		title: build.iconic("folder") + lychee.locale["MOVE_ALL"],
		fn: function fn() {
			basicContext.close();
			contextMenu.move(photoIDs, e, _photo3.setAlbum);
		}
	}, { title: build.iconic("trash") + lychee.locale["DELETE_ALL"], fn: function fn() {
			return _photo3.delete(photoIDs);
		} }, { title: build.iconic("cloud-download") + lychee.locale["DOWNLOAD_ALL"], fn: function fn() {
			return _photo3.getArchive(photoIDs, "FULL");
		} }];

	basicContext.show(items, e.originalEvent, contextMenu.close);
};

/**
 * @param {string} albumID
 * @param {string} photoID
 * @param {jQuery.Event} e
 */
contextMenu.photoTitle = function (albumID, photoID, e) {
	var items = [{ title: build.iconic("pencil") + lychee.locale["RENAME"], fn: function fn() {
			return _photo3.setTitle([photoID]);
		} }];

	// Note: We can also have a photo without its parent album being loaded
	// if the photo is a public photo within a private album
	var photos = album.json ? album.json.photos : [];

	if (photos.length > 0) {
		items.push({});

		items = items.concat(contextMenu.buildList(photos, [photoID], function (a) {
			return lychee.goto(albumID + "/" + a.id);
		}));
	}

	if (!album.isUploadable()) {
		// Remove Rename and the spacer.
		items.splice(0, 2);
	}

	basicContext.show(items, e.originalEvent, contextMenu.close);
};

/**
 * @param {string} photoID
 * @param {jQuery.Event} e
 */
contextMenu.photoMore = function (photoID, e) {
	// Show download-item when
	// a) We are allowed to upload to the album
	// b) the photo is explicitly marked as downloadable (v4-only)
	// c) or, the album is explicitly marked as downloadable

	var showDownload = album.isUploadable() || _photo3.json.is_downloadable;
	var showFull = !!(_photo3.json.size_variants.original.url && _photo3.json.size_variants.original.url !== "");

	var items = [{ title: build.iconic("fullscreen-enter") + lychee.locale["FULL_PHOTO"], visible: showFull, fn: function fn() {
			return window.open(_photo3.getDirectLink());
		} }, { title: build.iconic("cloud-download") + lychee.locale["DOWNLOAD"], visible: showDownload, fn: function fn() {
			return _photo3.getArchive([photoID]);
		} }];
	if (album.isUploadable()) {
		// prepend further buttons if menu bar is reduced on small screens
		var button_visibility = $("#button_visibility");
		if (button_visibility && button_visibility.css("display") === "none") {
			items.unshift({
				title: build.iconic("eye") + lychee.locale["VISIBILITY_PHOTO"],
				visible: lychee.enable_button_visibility,
				fn: function fn() {
					return _photo3.setProtectionPolicy(_photo3.getID());
				}
			});
		}
		var button_trash = $("#button_trash");
		if (button_trash && button_trash.css("display") === "none") {
			items.unshift({
				title: build.iconic("trash") + lychee.locale["DELETE"],
				visible: lychee.enable_button_trash,
				fn: function fn() {
					return _photo3.delete([_photo3.getID()]);
				}
			});
		}
		var button_move = $("#button_move");
		if (button_move && button_move.css("display") === "none") {
			items.unshift({
				title: build.iconic("folder") + lychee.locale["MOVE"],
				visible: lychee.enable_button_move,
				fn: function fn(event) {
					return contextMenu.move([_photo3.getID()], event, _photo3.setAlbum);
				}
			});
		}
		/* The condition below is copied from view.photo.header() */
		if (!(_photo3.json.type && (_photo3.json.type.indexOf("video") === 0 || _photo3.json.type === "raw") || _photo3.json.live_photo_url !== "" && _photo3.json.live_photo_url !== null)) {
			var button_rotate_cwise = $("#button_rotate_cwise");
			if (button_rotate_cwise && button_rotate_cwise.css("display") === "none") {
				items.unshift({
					title: build.iconic("clockwise") + lychee.locale["PHOTO_EDIT_ROTATECWISE"],
					visible: lychee.enable_button_move,
					fn: function fn() {
						return photoeditor.rotate(_photo3.getID(), 1);
					}
				});
			}
			var button_rotate_ccwise = $("#button_rotate_ccwise");
			if (button_rotate_ccwise && button_rotate_ccwise.css("display") === "none") {
				items.unshift({
					title: build.iconic("counterclockwise") + lychee.locale["PHOTO_EDIT_ROTATECCWISE"],
					visible: lychee.enable_button_move,
					fn: function fn() {
						return photoeditor.rotate(_photo3.getID(), -1);
					}
				});
			}
		}
	}

	basicContext.show(items, e.originalEvent);
};

/**
 * @param {Album[]} albums
 * @param {string} albumID
 *
 * @returns {string[]}
 */
contextMenu.getSubIDs = function (albums, albumID) {
	var ids = [albumID];

	albums.forEach(function (album) {
		if (album.parent_id === albumID) {
			ids = ids.concat(contextMenu.getSubIDs(albums, album.id));
		}

		if (album.albums && album.albums.length > 0) {
			ids = ids.concat(contextMenu.getSubIDs(album.albums, albumID));
		}
	});

	return ids;
};

/**
 * @callback TargetAlbumSelectedCB
 *
 * Called by {@link contextMenu.move} after the user has selected a target ID.
 * In most cases, {@link album.setAlbum} or {@link photo.setAlbum} are
 * directly used as the callback.
 * This design decision is the only reason, why this callback gets more
 * parameters than the selected target ID.
 * The parameter signature of this callback matches {@link album.setAlbum}.
 *
 * However, the callback should actually enclose all other parameters it
 * needs and only receive the target ID.
 *
 * TODO: Re-factor callbacks.
 *
 * @param {string[]} IDs      the source IDs
 * @param {?string} targetID  the ID of the target album
 * @param {boolean} [confirm] indicates whether the callback shall show a
 *                            confirmation dialog to the user for whatever to
 *                            callback is going to do
 * @returns {void}
 */

/**
 * Shows the context menu with the album tree and allows the user to select a target album.
 *
 * **ATTENTION:** The name `move` of this method is very badly chosen.
 * The method does not move anything, but only shows the menu and reports
 * the selected album.
 * In particular, the method is used by any operation which needs a target
 * album (i.e. merge, copy-to, etc.)
 *
 * TODO: Find a better name for this function.
 *
 * The method calls the provided callback after the user has selected a
 * target album and passes the ID of the target album together with the
 * source `IDs` and the event `e` to the callback.
 *
 * TODO: Actually the callbacks should enclose all additional parameters (e.g., `IDs`) they need. Refactor the callbacks.
 *
 * The name of the root node in the context menu may be provided by the caller
 * depending on the use-case.
 * Keep in mind, that the root album is not visible to the user during normal
 * browsing.
 * Photos on the root level are stashed away into a virtual album called
 * "Unsorted".
 * Albums on the root level are shown as siblings, but the root node itself
 * is invisible.
 * So the user actually sees a forest.
 * Hence, the root node should be named differently to meet the user's
 * expectations.
 * When the user moves/copies/merges photos, then the root node should be
 * called "Unsorted".
 * When the user moves/copies/merges albums, then the root node should be
 * called "Root".
 *
 * @param {string[]} IDs - IDs of source objects (either album or photo IDs)
 * @param {jQuery.Event} e - Some (?) event
 * @param {TargetAlbumSelectedCB} callback - to be called after the user has selected a target ID
 * @param {string} [kind=UNSORTED] - Name of root album; either "UNSORTED" or "ROOT"
 * @param {boolean} [display_root=true] - Whether the root (aka unsorted) album shall be shown
 */
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
				if (callback !== album.merge && callback !== _photo3.copyTo) {
					exclude.push(album.getID());
				}
				if (IDs.length === 1 && IDs[0] === album.getID() && album.getParentID() && callback === album.setAlbum) {
					// If moving the current album, exclude its parent.
					exclude.push(album.getParentID());
				}
			} else if (visible.photo()) {
				exclude.push(_photo3.json.album_id);
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
		if (display_root && album.getID() !== "unsorted" && !visible.albums()) {
			items.unshift({});
			items.unshift({ title: lychee.locale[kind], fn: function fn() {
					return callback(IDs, null);
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

/**
 * @param {string} photoID
 * @param {jQuery.Event} e
 *
 * @returns {void}
 */
contextMenu.sharePhoto = function (photoID, e) {
	if (!_photo3.json.is_share_button_visible) {
		return;
	}

	var iconClass = "ionicons";

	var items = [{ title: build.iconic("twitter", iconClass) + "Twitter", fn: function fn() {
			return _photo3.share(photoID, "twitter");
		} }, { title: build.iconic("facebook", iconClass) + "Facebook", fn: function fn() {
			return _photo3.share(photoID, "facebook");
		} }, { title: build.iconic("envelope-closed") + "Mail", fn: function fn() {
			return _photo3.share(photoID, "mail");
		} }, { title: build.iconic("dropbox", iconClass) + "Dropbox", visible: lychee.admin === true, fn: function fn() {
			return _photo3.share(photoID, "dropbox");
		} }, { title: build.iconic("link-intact") + lychee.locale["DIRECT_LINKS"], fn: function fn() {
			return _photo3.showDirectLinks(photoID);
		} }, { title: build.iconic("grid-two-up") + lychee.locale["QR_CODE"], fn: function fn() {
			return _photo3.qrCode(photoID);
		} }];

	basicContext.show(items, e.originalEvent);
};

/**
 * @param {string} albumID
 * @param {jQuery.Event} e
 *
 * @returns {void}
 */
contextMenu.shareAlbum = function (albumID, e) {
	if (!album.json.is_share_button_visible) {
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
			if (album.json.has_password) {
				// Copy the url with prefilled password param
				url += "?password=";
			}
			navigator.clipboard.writeText(url).then(function () {
				return loadingBar.show("success", lychee.locale["URL_COPIED_TO_CLIPBOARD"]);
			});
		}
	}, { title: build.iconic("grid-two-up") + lychee.locale["QR_CODE"], fn: function fn() {
			return album.qrCode();
		} }];

	basicContext.show(items, e.originalEvent);
};

/**
 * @returns {void}
 */
contextMenu.close = function () {
	if (!visible.contextMenu()) return;

	basicContext.close();

	multiselect.clearSelection();
	if (visible.multiselect()) {
		multiselect.close();
	}
};

/**
 * @param {jQuery.Event} e
 * @returns {void}
 */
contextMenu.config = function (e) {
	var items = [{ title: build.iconic("cog") + lychee.locale["SETTINGS"], fn: settings.open }];
	if (lychee.new_photos_notification) {
		items.push({ title: build.iconic("bell") + lychee.locale["NOTIFICATIONS"], fn: notifications.load });
	}
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

		if (visible.photo()) contextMenu.photoTitle(album.getID(), _photo3.getID(), e);else contextMenu.albumTitle(album.getID(), e);
	});

	header.dom("#button_visibility").on(eventName, function () {
		_photo3.setProtectionPolicy(_photo3.getID());
	});
	header.dom("#button_share").on(eventName, function (e) {
		contextMenu.sharePhoto(_photo3.getID(), e);
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
		_sidebar.toggle(true);
	});
	header.dom("#button_info").on(eventName, function () {
		_sidebar.toggle(true);
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
		contextMenu.photoMore(_photo3.getID(), e);
	});
	header.dom("#button_move_album").on(eventName, function (e) {
		contextMenu.move([album.getID()], e, album.setAlbum, "ROOT", album.getParentID() != null);
	});
	header.dom("#button_nsfw_album").on(eventName, function () {
		album.toggleNSFW();
	});
	header.dom("#button_move").on(eventName, function (e) {
		contextMenu.move([_photo3.getID()], e, _photo3.setAlbum);
	});
	header.dom(".header__hostedwith").on(eventName, function () {
		window.open(lychee.website);
	});
	header.dom("#button_trash_album").on(eventName, function () {
		album.delete([album.getID()]);
	});
	header.dom("#button_trash").on(eventName, function () {
		_photo3.delete([_photo3.getID()]);
	});
	header.dom("#button_archive").on(eventName, function () {
		album.getArchive([album.getID()]);
	});
	header.dom("#button_star").on(eventName, function () {
		_photo3.toggleStar();
	});
	header.dom("#button_rotate_ccwise").on(eventName, function () {
		photoeditor.rotate(_photo3.getID(), -1);
	});
	header.dom("#button_rotate_cwise").on(eventName, function () {
		photoeditor.rotate(_photo3.getID(), 1);
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

	_photo3.updateSizeLivePhotoDuringAnimation();
};

/**
 * @returns {void}
 */
header.hideIfLivePhotoNotPlaying = function () {
	// Hides the header, if current live photo is not playing
	if (!_photo3.isLivePhotoPlaying()) header.hide();
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

		_photo3.updateSizeLivePhotoDuringAnimation();
	}
};

/**
 * @param {string} title
 * @returns {void}
 */
header.setTitle = function (title) {
	var $title = header.dom(".header__title");
	var html = lychee.html(_templateObject43, title, build.iconic("caret-bottom"));

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
			return;

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

			if (lychee.enable_button_add && lychee.may_upload) {
				var _e7 = $(".button_add", ".header__toolbar--albums");
				_e7.show();
				tabindex.makeFocusable(_e7);
			} else {
				var _e8 = $(".button_add", ".header__toolbar--albums");
				_e8.remove();
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
				var _e9 = $("#button_archive");
				_e9.hide();
				tabindex.makeUnfocusable(_e9);
			} else {
				var _e10 = $("#button_archive");
				_e10.show();
				tabindex.makeFocusable(_e10);
			}

			if (album.json && album.json.hasOwnProperty("is_share_button_visible") && !album.json.is_share_button_visible) {
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
				if (_sidebar.keepSidebarVisible() && !visible.sidebar()) _sidebar.toggle(false);
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
				if (_sidebar.keepSidebarVisible() && !visible.sidebar()) _sidebar.toggle(false);
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

			return;

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

			if (_photo3.json && _photo3.json.hasOwnProperty("is_share_button_visible") && !_photo3.json.is_share_button_visible) {
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
			if (!(album.isUploadable() || (_photo3.json.hasOwnProperty("is_downloadable") ? _photo3.json.is_downloadable : album.json && album.json.is_downloadable)) && !(_photo3.json.size_variants.original.url && _photo3.json.size_variants.original.url !== "")) {
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
 * @description This module is used for bindings.
 */

$(document).ready(function () {
	$("#sensitive_warning").hide();

	// Event Name
	var eventName = lychee.getEventName();

	// Set API error handler
	api.onError = lychee.handleAPIError;

	$("html").css("visibility", "visible");

	// Multiselect
	multiselect.bind();

	// Header
	header.bind();

	// Image View
	lychee.imageview.on(eventName, ".arrow_wrapper--previous", function () {
		return _photo3.previous(false);
	}).on(eventName, ".arrow_wrapper--next", function () {
		return _photo3.next(false);
	}).on(eventName, "img, #livephoto", function () {
		return _photo3.cycle_display_overlay();
	});

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
				album.setTitle([album.getID()]);
				return false;
			} else if (visible.photo()) {
				_photo3.setTitle([_photo3.getID()]);
				return false;
			}
		}
	}).bind(["h"], function () {
		lychee.nsfw_visible = !lychee.nsfw_visible;
		album.apply_nsfw_filter();
		return false;
	}).bind(["d"], function () {
		if (album.isUploadable()) {
			if (visible.photo()) {
				_photo3.setDescription(_photo3.getID());
				return false;
			} else if (visible.album()) {
				album.setDescription(album.getID());
				return false;
			}
		}
	}).bind(["t"], function () {
		if (visible.photo() && album.isUploadable()) {
			_photo3.editTags([_photo3.getID()]);
			return false;
		}
	}).bind(["i", "ContextMenu"], function () {
		if (!visible.multiselect()) {
			_sidebar.toggle(true);
			return false;
		}
	}).bind(["command+backspace", "ctrl+backspace"], function () {
		if (album.isUploadable()) {
			if (visible.photo() && basicModal.visible() === false) {
				_photo3.delete([_photo3.getID()]);
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
			_photo3.cycle_display_overlay();
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
			if (!$(this).is("input") && !$(this).is("textarea")) {
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
		if (basicModal.visible() === true) basicModal.cancel();else if (visible.config() || visible.leftMenu()) leftMenu.close();else if (visible.contextMenu()) contextMenu.close();else if (visible.photo()) lychee.goto(album.getID());else if (visible.album() && !album.json.parent_id) lychee.goto();else if (visible.album()) lychee.goto(album.getParentID());else if (visible.albums() && search.json !== null) search.reset();else if (visible.mapview()) mapview.close();else if (visible.albums() && lychee.enable_close_tab_on_esc) {
			window.open("", "_self").close();
		}
		return false;
	});

	$(document)
	// Fullscreen on mobile
	.on("touchend", "#imageview #image", function () {
		// prevent triggering event 'mousemove'
		// why? this also prevents 'click' from firing which results in unexpected behaviour
		// unable to reproduce problems arising from 'mousemove' on iOS devices
		//			e.preventDefault();

		if (typeof swipe.obj === null || Math.abs(swipe.offsetX) <= 5 && Math.abs(swipe.offsetY) <= 5) {
			// Toggle header only if we're not moving to next/previous photo;
			// In this case, swipe.preventNextHeaderToggle is set to true
			if (!swipe.preventNextHeaderToggle) {
				if (visible.header()) {
					header.hide();
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
	}).swipe().on("swipeMove",
	/** @param {jQuery.Event} e */function (e) {
		if (visible.photo()) swipe.move(e.swipe);
	}).swipe().on("swipeEnd",
	/** @param {jQuery.Event} e */function (e) {
		if (visible.photo()) swipe.stop(e.swipe, _photo3.previous, _photo3.next);
	});

	// Document
	$(document)
	// Navigation
	.on("click", ".album",
	/** @param {jQuery.Event} e */function (e) {
		multiselect.albumClick(e, $(this));
	}).on("click", ".photo",
	/** @param {jQuery.Event} e */function (e) {
		multiselect.photoClick(e, $(this));
	})
	// Context Menu
	.on("contextmenu", ".photo",
	/** @param {jQuery.Event} e */function (e) {
		multiselect.photoContextMenu(e, $(this));
	}).on("contextmenu", ".album",
	/** @param {jQuery.Event} e */function (e) {
		multiselect.albumContextMenu(e, $(this));
	})
	// Upload
	.on("change", "#upload_files", function () {
		basicModal.close();
		upload.start.local(this.files);
	}).on("change", "#upload_track_file", function () {
		basicModal.close();
		upload.uploadTrack(this.files);
	})
	// Drag and Drop upload
	.on("dragover", function () {
		return false;
	}, false).on("drop",
	/** @param {jQuery.Event} e */function (e) {
		if (album.isUploadable() && !visible.contextMenu() && !basicModal.visible() && !visible.leftMenu() && !visible.config() && (visible.album() || visible.albums())) {
			// Detect if dropped item is a file or a link
			if (e.originalEvent.dataTransfer.files.length > 0) {
				upload.start.local(e.originalEvent.dataTransfer.files);
			} else if (e.originalEvent.dataTransfer.getData("Text").length > 3) {
				upload.start.url(e.originalEvent.dataTransfer.getData("Text"));
			}
		}

		return false;
	})
	// click on thumbnail on map
	.on("click", ".image-leaflet-popup", function () {
		mapview.goto($(this));
	})
	// Paste upload
	.on("paste",
	/** @param {jQuery.Event} e */function (e) {
		if (e.originalEvent.clipboardData.items) {
			var items = e.originalEvent.clipboardData.items;
			var filesToUpload = [];

			// Search clipboard items for an image
			for (var i = 0; i < items.length; i++) {
				if (items[i].type.indexOf("image") !== -1 || items[i].type.indexOf("video") !== -1) {
					filesToUpload.push(items[i].getAsFile());
				}
			}

			// We perform the check so deep because we don't want to
			// prevent the paste from working in text input fields, etc.
			if (filesToUpload.length > 0 && album.isUploadable() && !visible.contextMenu() && !basicModal.visible() && !visible.leftMenu() && !visible.config() && (visible.album() || visible.albums())) {
				upload.start.local(filesToUpload);

				return false;
			} else {
				return true;
			}
		}
	});

	// Fullscreen
	if (lychee.fullscreenAvailable()) $(document).on("fullscreenchange mozfullscreenchange webkitfullscreenchange msfullscreenchange", lychee.fullscreenUpdate);

	$("#sensitive_warning").on("click", view.album.nsfw_warning.next);

	/**
  * @param {number} scrollPos
  * @returns {void}
  */
	var rememberScrollPage = function rememberScrollPage(scrollPos) {
		if (visible.albums() && !visible.search() || visible.album()) {
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
		if (visible.album()) view.album.content.justify(album.json ? album.json.photos : []);
		if (visible.search()) view.album.content.justify(search.json.photos);
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

/**
 * @namespace
 * @property {jQuery} _dom
 */
var leftMenu = {
	_dom: $(".leftMenu")
};

/**
 * @param {?string} [selector=null]
 * @returns {jQuery}
 */
leftMenu.dom = function (selector) {
	if (selector == null || selector === "") return leftMenu._dom;
	return leftMenu._dom.find(selector);
};

/**
 * Note: on mobile we use a context menu instead; please make sure that
 * contextMenu.config is kept in sync with any changes here!
 *
 * @returns {void}
 */
leftMenu.build = function () {
	var html = lychee.html(_templateObject44, lychee.locale["CLOSE"], lychee.locale["SETTINGS"]);
	if (lychee.new_photos_notification) {
		html += lychee.html(_templateObject45, build.iconic("bell"), lychee.locale["NOTIFICATIONS"]);
	}
	html += lychee.html(_templateObject46, build.iconic("person"), lychee.locale["USERS"], build.iconic("key"), lychee.locale["U2F"], build.iconic("cloud"), lychee.locale["SHARING"]);
	html += lychee.html(_templateObject47, build.iconic("align-left"), lychee.locale["LOGS"], build.iconic("wrench"), lychee.locale["DIAGNOSTICS"], build.iconic("info"), lychee.locale["ABOUT_LYCHEE"], build.iconic("account-logout"), lychee.locale["SIGN_OUT"]);
	if (lychee.update_available) {
		html += lychee.html(_templateObject48, build.iconic("timer"), lychee.locale["UPDATE_AVAILABLE"]);
	}
	leftMenu._dom.html(html);
};

/** Set the width of the side navigation to 250px and the left margin of the page content to 250px
 *
 * @returns {void}
 */
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

/**
 * Set the width of the side navigation to 0 and the left margin of the page content to 0
 *
 * @returns {void}
 */
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

/**
 * @returns {void}
 */
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
	leftMenu.dom("#button_notifications").on(eventName, leftMenu.Notifications);
	leftMenu.dom("#button_users").on(eventName, leftMenu.Users);
	leftMenu.dom("#button_u2f").on(eventName, leftMenu.u2f);
	leftMenu.dom("#button_sharing").on(eventName, leftMenu.Sharing);
	leftMenu.dom("#button_update").on(eventName, leftMenu.Update);
};

/**
 * @returns {void}
 */
leftMenu.Logs = function () {
	view.logs.init();
};

/**
 * @returns {void}
 */
leftMenu.Diagnostics = function () {
	view.diagnostics.init();
};

/**
 * @returns {void}
 */
leftMenu.Update = function () {
	view.update.init();
};

/**
 * @returns {void}
 */
leftMenu.Notifications = function () {
	notifications.load();
};

/**
 * @returns {void}
 */
leftMenu.Users = function () {
	users.list();
};

/**
 * @returns {void}
 */
leftMenu.u2f = function () {
	u2f.list();
};

/**
 * @returns {void}
 */
leftMenu.Sharing = function () {
	sharing.list();
};

/**
 * @description This module is used to show and hide the loading bar.
 */

var loadingBar = {
	/** @type {?string} */
	status: null,
	/** @type {jQuery} */
	_dom: $("#loading")
};

/**
 * @param {string} [selector=""]
 * @returns {jQuery}
 */
loadingBar.dom = function (selector) {
	if (selector == null || selector === "") return loadingBar._dom;
	return loadingBar._dom.find(selector);
};

/**
 * @param {?string} status the status, either `null`, `"error"` or `"success"`
 * @param {?string} errorText the error text to show
 * @returns {void}
 */
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

		return;
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

		return;
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
	}
};

/**
 * @param {boolean} force
 * @returns {void}
 */
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
	share_button_visible: false,
	/**
  * Enable admin mode (multi-user)
  * @type boolean
  */
	admin: false,
	/**
  * Enable possibility to upload (multi-user)
  * @type boolean
  */
	may_upload: false,
	/**
  * Locked user (multi-user)
  * @type boolean
  */
	is_locked: false,
	/** @type {?string} */
	username: null,
	/**
  * Values:
  *
  *  - `0`: Use default, "square" layout.
  *  - `1`: Use Flickr-like "justified" layout.
  *  - `2`: Use Google-like "unjustified" layout
  *
  * @type {number}
  */
	layout: 1,
	/**
  * Display search in public mode.
  * @type boolean
  */
	public_search: false,
	/**
  * Overlay display type
  * @type {string}
  */
	image_overlay_type: "exif",
	/**
  * Image overlay type default type
  * @type {string}
  */
	image_overlay_type_default: "exif",
	/**
  * Display photo coordinates on map
  * @type boolean
  */
	map_display: false,
	/**
  * Display photos of public album on map (user not logged in)
  * @type boolean
  */
	map_display_public: false,
	/**
  * Use the GPS direction data on displayed maps
  * @type boolean
  */
	map_display_direction: true,
	/**
  * Provider of OSM Tiles
  * @type {string}
  */
	map_provider: "Wikimedia",
	/**
  * Include photos of subalbums on map
  * @type boolean
  */
	map_include_subalbums: false,
	/**
  * Retrieve location name from GPS data
  * @type boolean
  */
	location_decoding: false,
	/**
  * Caching mode for GPS data decoding
  * @type {string}
  */
	location_decoding_caching_type: "Harddisk",
	/**
  * Show location name
  * @type boolean
  */
	location_show: false,
	/**
  * Show location name for public albums
  * @type boolean
  */
	location_show_public: false,
	/**
  * Tolerance for navigating when swiping images to the left and right on mobile
  * @type {number}
  */
	swipe_tolerance_x: 150,
	/**
  * Tolerance for navigating when swiping images up and down
  * @type {number}
  */
	swipe_tolerance_y: 250,

	/**
  * Is landing page enabled?
  * @type boolean
  */
	landing_page_enabled: false,
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
	hide_content_during_imgview: false,
	device_type: "desktop",

	checkForUpdates: true,
	/**
  * The most recent, available Lychee version encoded as an integer, e.g. 040506
  * @type {number}
  */
	update_json: 0,
	update_available: false,
	new_photos_notification: false,
	/** @type {?SortingCriterion} */
	sorting_photos: null,
	/** @type {?SortingCriterion} */
	sorting_albums: null,
	/**
  * The absolute path of the server-side installation directory of Lychee, e.g. `/var/www/lychee`
  * @type {string}
  */
	location: "",

	lang: "",
	/** @type {string[]} */
	lang_available: [],

	dropbox: false,
	dropboxKey: "",

	content: $(".content"),
	imageview: $("#imageview"),
	footer: $("#footer"),

	/** @type {Locale} */
	locale: {},

	nsfw_unlocked_albums: []
};

/**
 * @returns {string}
 */
lychee.diagnostics = function () {
	return "/Diagnostics";
};

/**
 * @returns {string}
 */
lychee.logs = function () {
	return "/Logs";
};

/**
 * @returns {void}
 */
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

	if (lychee.checkForUpdates) lychee.getUpdate();
};

/**
 * @param {boolean} isFirstInitialization must be set to `false` if called
 *                                        for re-initialization to prevent
 *                                        multiple registrations of global
 *                                        event handlers
 * @returns {void}
 */
lychee.init = function () {
	var isFirstInitialization = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : true;

	lychee.adjustContentHeight();

	api.post("Session::init", {},
	/** @param {InitializationData} data */
	function (data) {
		lychee.parseInitializationData(data);

		if (data.status === 2) {
			// Logged in
			leftMenu.build();
			leftMenu.bind();
			lychee.setMode("logged_in");

			// Show dialog when there is no username and password
			// TODO: Refactor this. At least rename the flag `login` to something more understandable like `isAdminUserConfigured`, but rather re-factor the whole logic, i.e. the initial user should be created as part of the installation routine.
			// In particular it is completely insane to build the UI as if the admin user was successfully authenticated.
			// This might leak confidential photos to anybody if the DB is filled
			// with photos and the admin password reset to `null`.
			if (data.config.login === false) settings.createLogin();
		} else if (data.status === 1) {
			lychee.setMode("public");
		} else {
			loadingBar.show("error", "Error: Unexpected status");
			return;
		}

		if (isFirstInitialization) {
			$(window).on("popstate", function () {
				var autoplay = history.state && history.state.hasOwnProperty("autoplay") ? history.state.autoplay : true;
				lychee.load(autoplay);
			});
			lychee.load();
		}
	});
};

/**
 * @param {InitializationData} data
 * @returns {void}
 */
lychee.parseInitializationData = function (data) {
	lychee.update_json = data.update_json;
	lychee.update_available = data.update_available;

	// TODO: Let the backend report the version as a proper object with properties for major, minor and patch level
	lychee.versionCode = data.config.version;
	if (lychee.versionCode !== "") {
		var digits = lychee.versionCode.match(/.{1,2}/g);
		lychee.version = parseInt(digits[0]).toString() + "." + parseInt(digits[1]).toString() + "." + parseInt(digits[2]).toString();
	}

	// we copy the locale that exists only.
	// This ensures forward and backward compatibility.
	// e.g. if the front localization is unfinished in a language
	// or if we need to change some locale string
	for (var key in data.locale) {
		lychee.locale[key] = data.locale[key];
	}

	// Check status
	// 0 = No configuration
	// 1 = Logged out
	// 2 = Logged in
	if (data.status === 2) {
		// Logged in
		lychee.parsePublicInitializationData(data);
		lychee.parseProtectedInitializationData(data);

		lychee.may_upload = data.admin || data.may_upload;
		lychee.admin = data.admin;
		lychee.is_locked = data.is_locked;
		lychee.username = data.username;
	} else if (data.status === 1) {
		lychee.parsePublicInitializationData(data);
	} else {
		// should not happen.
	}
};

/**
 * Parses the configuration settings which are always available.
 *
 * TODO: If configuration management is re-factored on the backend, remember to use proper types in the first place
 *
 * @param {InitializationData} data
 * @returns {void}
 */
lychee.parsePublicInitializationData = function (data) {
	lychee.sorting_photos = data.config.sorting_photos;
	lychee.sorting_albums = data.config.sorting_albums;
	lychee.album_subtitle_type = data.config.album_subtitle_type || "oldstyle";
	lychee.checkForUpdates = data.config.check_for_updates;
	lychee.layout = Number.parseInt(data.config.layout, 10) || 1;
	lychee.landing_page_enable = data.config.landing_page_enable === "1";
	lychee.public_search = data.config.public_search === "1";
	lychee.image_overlay_type = data.config.image_overlay_type || "exif";
	lychee.image_overlay_type_default = lychee.image_overlay_type;
	lychee.map_display = data.config.map_display === "1";
	lychee.map_display_public = data.config.map_display_public === "1";
	lychee.map_display_direction = data.config.map_display_direction === "1";
	lychee.map_provider = data.config.map_provider || "Wikimedia";
	lychee.map_include_subalbums = data.config.map_include_subalbums === "1";
	lychee.location_show = data.config.location_show === "1";
	lychee.location_show_public = data.config.location_show_public === "1";
	lychee.swipe_tolerance_x = Number.parseInt(data.config.swipe_tolerance_x, 10) || 150;
	lychee.swipe_tolerance_y = Number.parseInt(data.config.swipe_tolerance_y, 10) || 250;

	lychee.nsfw_visible = data.config.nsfw_visible === "1";
	lychee.nsfw_visible_saved = lychee.nsfw_visible;
	lychee.nsfw_blur = data.config.nsfw_blur === "1";
	lychee.nsfw_warning = data.config.nsfw_warning === "1";

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
};

/**
 * Parses the configuration settings which are only available, if a user is authenticated.
 *
 * TODO: If configuration management is re-factored on the backend, remember to use proper types in the first place
 *
 * @param {InitializationData} data
 * @returns {void}
 */
lychee.parseProtectedInitializationData = function (data) {
	lychee.dropboxKey = data.config.dropbox_key || "";
	lychee.location = data.config.location || "";
	lychee.checkForUpdates = data.config.check_for_updates === "1";
	lychee.lang = data.config.lang || "";
	lychee.lang_available = data.config.lang_available || [];
	lychee.location_decoding = data.config.location_decoding === "1";
	lychee.default_license = data.config.default_license || "none";
	lychee.css = data.config.css || "";
	lychee.full_photo = data.config.full_photo === "1";
	lychee.downloadable = data.config.downloadable === "1";
	lychee.public_photos_hidden = data.config.public_photos_hidden === "1";
	lychee.share_button_visible = data.config.share_button_visible === "1";
	lychee.delete_imported = data.config.delete_imported === "1";
	lychee.import_via_symlink = data.config.import_via_symlink === "1";
	lychee.skip_duplicates = data.config.skip_duplicates === "1";
	lychee.editor_enabled = data.config.editor_enabled === "1";
	lychee.new_photos_notification = data.config.new_photos_notification === "1";
	lychee.upload_processing_limit = Number.parseInt(data.config.upload_processing_limit, 10) || 4;
};

/**
 * @param {{username: string, password: string}} data
 * @returns {void}
 */
lychee.login = function (data) {
	if (!data.username.trim()) {
		basicModal.error("username");
		return;
	}
	if (!data.password.trim()) {
		basicModal.error("password");
		return;
	}

	api.post("Session::login", data, function () {
		return window.location.reload();
	}, null, function (jqXHR) {
		if (jqXHR.status === 401) {
			basicModal.error("password");
			return true;
		} else {
			return false;
		}
	});
};

/**
 * @returns {void}
 */
lychee.loginDialog = function () {
	// Make background unfocusable
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

	// This feels awkward, because this hooks into the modal dialog in some
	// unpredictable way.
	// It would be better to have a checkbox for password-less login in the
	// dialog and then let the action handler of the modal dialog, i.e.
	// `lychee.login` handle both cases.
	// TODO: Refactor this.
	$("#signInKeyLess").on("click", u2f.login);

	if (lychee.checkForUpdates) lychee.getUpdate();

	tabindex.makeFocusable($(".basicModal"));
};

/**
 * @returns {void}
 */
lychee.logout = function () {
	api.post("Session::logout", {}, function () {
		return window.location.reload();
	});
};

/**
 * @param {?string} [url=null]
 * @param {boolean} [autoplay=true]
 *
 * @returns {void}
 */
lychee.goto = function () {
	var url = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : null;
	var autoplay = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : true;

	url = "#" + (url !== null ? url : "");
	history.pushState({ autoplay: autoplay }, null, url);
	lychee.load(autoplay);
};

/**
 * @param {?string} [albumID=null]
 * @param {boolean} [autoplay=true]
 *
 * @returns {void}
 */
lychee.gotoMap = function () {
	var albumID = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : null;
	var autoplay = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : true;

	// If map functionality is disabled -> go to album
	if (!lychee.map_display) {
		loadingBar.show("error", lychee.locale["ERROR_MAP_DEACTIVATED"]);
		return;
	}
	lychee.goto("map/" + (albumID !== null ? albumID : ""), autoplay);
};

/**
 * Triggers a reload, if the given IDs are in legacy format.
 *
 * If any of the IDs is in legacy format, the method first translates the IDs
 * into the modern format via an AJAX call to the backend and then triggers
 * an asynchronous reloading of the page with the resolved, modern IDs.
 * The function returns `true` in this case.
 *
 * If the IDs are already in modern format (and thus neither a translation
 * nor a reloading is required), the function returns `false`.
 * In this case this function is basically a no-op.
 *
 * @param {?string} albumID  the album ID
 * @param {?string} photoID  the photo ID
 * @param {boolean} autoplay indicates whether playback should start
 *                           automatically, if the indicated photo is a video
 *
 * @returns {boolean} `true`, if any of the IDs has been in legacy format
 *                   and an asynchronous reloading has been scheduled
 */
lychee.reloadIfLegacyIDs = function (albumID, photoID, autoplay) {
	/** @param {?string} id the inspected ID */
	var isLegacyID = function isLegacyID(id) {
		// The legacy IDs were pure numeric values. We exclude values which
		// have 24 digits, because these could also be modern IDs.
		// A modern IDs is a 24 character long, base64 encoded value and thus
		// could also match 24 digits by accident.
		return id && id.length !== 24 && parseInt(id, 10).toString() === id;
	};

	if (!isLegacyID(albumID) && !isLegacyID(photoID)) {
		// this function is a no-op if neither ID is in legacy format
		return false;
	}

	/**
  * Callback to be called asynchronously which executes the actual reloading.
  *
  * @param {?string} newAlbumID
  * @param {?string} newPhotoID
  *
  * @returns {void}
  */
	var reloadWithNewIDs = function reloadWithNewIDs(newAlbumID, newPhotoID) {
		var newUrl = "";
		if (newAlbumID) {
			newUrl += newAlbumID;
			newUrl += newPhotoID ? "/" + newPhotoID : "";
		}
		lychee.goto(newUrl, autoplay);
	};

	// We have to deal with three cases:
	//  1. the album and photo ID need to be translated
	//  2. only the album ID needs to be translated
	//  3. only the photo ID needs to be translated
	var params = {};
	if (isLegacyID(albumID)) params.albumID = parseInt(albumID, 10);
	if (isLegacyID(photoID)) params.photoID = parseInt(photoID, 10);
	api.post("Legacy::translateLegacyModelIDs", params, function (data) {
		reloadWithNewIDs(data.hasOwnProperty("albumID") ? data.albumID : albumID, data.hasOwnProperty("photoID") ? data.photoID : photoID);
	});

	return true;
};

/**
 * @param {boolean} [autoplay=true]
 * @returns {void}
 */
lychee.load = function () {
	var autoplay = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : true;

	var hash = document.location.hash.replace("#", "").split("/");
	var albumID = hash[0];
	var photoID = hash[1];

	contextMenu.close();
	multiselect.close();
	tabindex.reset();

	if (albumID && photoID) {
		if (albumID === "map") {
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
			_photo3.json = null;

			// Show Album -> it's below the map
			if (visible.photo()) view.photo.hide();
			if (visible.sidebar()) _sidebar.toggle(false);
			if (album.json && albumID === album.json.id) {
				view.album.title();
			}
			mapview.open(albumID);
			lychee.footer_hide();
		} else if (albumID === "search") {
			// Search has been triggered
			var search_string = decodeURIComponent(photoID);

			if (search_string.trim() === "") {
				// do nothing on "only space" search strings
				return;
			}
			// If public search is disabled -> do nothing
			if (lychee.publicMode === true && !lychee.public_search) {
				loadingBar.show("error", lychee.locale["ERROR_SEARCH_DEACTIVATED"]);
				return;
			}

			header.dom(".header__search").val(search_string);
			search.find(search_string);

			lychee.footer_show();
		} else {
			if (lychee.reloadIfLegacyIDs(albumID, photoID, autoplay)) {
				return;
			}

			$(".no_content").remove();
			// Show photo

			// Trash data
			_photo3.json = null;

			/**
    * @param {boolean} isParentAlbumAccessible
    * @returns {void}
    */
			var loadPhoto = function loadPhoto(isParentAlbumAccessible) {
				if (!isParentAlbumAccessible) {
					lychee.setMode("view");
				}
				_photo3.load(photoID, albumID, autoplay);

				// Make imageview focusable
				tabindex.makeFocusable(lychee.imageview);

				// Make thumbnails unfocusable and store which element had focus
				tabindex.makeUnfocusable(lychee.content, true);

				// hide contentview if requested
				if (lychee.hide_content_during_imgview) lychee.content.hide();

				lychee.footer_hide();
			};

			// Load Photo
			// If we don't have an album or the wrong album load the album
			// first and let the album loader load the photo afterwards or
			// load the photo directly.
			if (lychee.content.html() === "" || album.json === null || album.json.id !== albumID || header.dom(".header__search").length && header.dom(".header__search").val().length !== 0) {
				lychee.content.hide();
				album.load(albumID, loadPhoto);
			} else {
				loadPhoto(true);
			}
		}
	} else if (albumID) {
		if (albumID === "map") {
			$(".no_content").remove();
			// Show map of all albums
			// If map functionality is disabled -> do nothing
			if (!lychee.map_display) {
				loadingBar.show("error", lychee.locale["ERROR_MAP_DEACTIVATED"]);
				return;
			}

			// Trash data
			_photo3.json = null;

			// Show Album -> it's below the map
			if (visible.photo()) view.photo.hide();
			if (visible.sidebar()) _sidebar.toggle(false);
			mapview.open();
			lychee.footer_hide();
		} else if (albumID === "search") {
			// search string is empty -> do nothing
		} else {
			if (lychee.reloadIfLegacyIDs(albumID, photoID, autoplay)) {
				return;
			}

			$(".no_content").remove();
			// Trash data
			_photo3.json = null;

			// Show Album
			if (visible.photo()) {
				view.photo.hide();
				tabindex.makeUnfocusable(lychee.imageview);
			}
			if (visible.mapview()) mapview.close();
			if (visible.sidebar() && (album.isSmartID(albumID) || album.isSearchID(albumID))) _sidebar.toggle(false);
			$("#sensitive_warning").hide();
			if (album.json && albumID === album.json.id) {
				view.album.title();
				lychee.content.show();
				tabindex.makeFocusable(lychee.content, true);
				// If the album was loaded in the background (when content is
				// hidden), scrolling may not have worked.
				view.album.content.restoreScroll();
			} else {
				album.load(albumID);
			}
			lychee.footer_show();
		}
	} else {
		$(".no_content").remove();

		// Trash data
		search.json = null;
		album.json = null;
		_photo3.json = null;

		// Hide sidebar
		if (visible.sidebar()) _sidebar.toggle(false);

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

/**
 * @returns {void}
 */
lychee.getUpdate = function () {
	// console.log(lychee.update_available);
	// console.log(lychee.update_json);

	if (lychee.update_json !== 0) {
		if (lychee.update_available) {
			$(".version span").show();
		}
	} else {
		/**
   * @param {{lychee: {version: number}}} data
   */
		var success = function success(data) {
			if (data.lychee.version > parseInt(lychee.versionCode)) $(".version span").show();
		};

		$.ajax({
			url: lychee.updatePath,
			success: success
		});
	}
};

/**
 * Sets the title of the browser window and the title shown in the header bar.
 *
 * The window title is prefixed by the value of the configuration setting
 * `lychee.title`.
 *
 * If both, the prefix `lychee.title` and the given title, are not empty,
 * they are seperated by an en-dash.
 *
 * @param {string} [title=""]
 * @param {boolean} [editable=false]
 */
lychee.setTitle = function () {
	var title = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : "";
	var editable = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : false;

	document.title = lychee.title + (lychee.title && title ? " – " : "") + title;
	header.setEditable(editable);
	header.setTitle(title);
};

/**
 * @param {string} mode - one out of: `public`, `view`, `logged_in`
 */
lychee.setMode = function (mode) {
	if (lychee.is_locked) {
		$("#button_settings_open").remove();
	}
	if (!lychee.may_upload) {
		$("#button_sharing").remove();

		$(document).off("click", ".header__title--editable").off("touchend", ".header__title--editable").off("contextmenu", ".photo").off("contextmenu", ".album").off("drop");

		Mousetrap.unbind(["u"]).unbind(["s"]).unbind(["n"]).unbind(["r"]).unbind(["d"]).unbind(["t"]).unbind(["command+backspace", "ctrl+backspace"]).unbind(["command+a", "ctrl+a"]);
	}
	if (!lychee.admin) {
		$("#button_users, #button_logs, #button_diagnostics").remove();
	}

	if (mode === "logged_in") {
		// After login the keyboard short-cuts to login by password (l) and
		// by key (k) are not required anymore, so we unbind them.
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

/**
 * @param {jQuery} obj
 * @param {string} animation
 *
 * @returns {void}
 */
lychee.animate = function (obj, animation) {
	var animations = [["fadeIn", "fadeOut"], ["contentZoomIn", "contentZoomOut"]];

	for (var i = 0; i < animations.length; i++) {
		for (var x = 0; x < animations[i].length; x++) {
			if (animations[i][x] === animation) {
				obj.removeClass(animations[i][0] + " " + animations[i][1]).addClass(animation);
				return;
			}
		}
	}
};

/**
 * @callback DropboxLoadedCB
 * @returns {void}
 */

/**
 * Ensures that the Dropbox Chooser JS component is loaded and calls the
 * provided callback after loading.
 *
 * See {@link Dropbox}
 *
 * @param {DropboxLoadedCB} callback
 */
lychee.loadDropbox = function (callback) {
	if (!lychee.dropboxKey) {
		loadingBar.show("error", "Error: Dropbox key not set");
		return;
	}

	// If the dropbox component has already been loaded, immediately call
	// the callback; otherwise load the component first and call callback
	// on success.
	if (lychee.dropbox) {
		callback();
	} else {
		loadingBar.show();

		var g = document.createElement("script");
		var s = document.getElementsByTagName("script")[0];

		g.src = "https://www.dropbox.com/static/api/1/dropins.js";
		g.id = "dropboxjs";
		g.type = "text/javascript";
		g.async = true;
		g.setAttribute("data-app-key", lychee.dropboxKey);
		g.onload = g.onreadystatechange = function () {
			var rs = this.readyState;
			if (rs && rs !== "complete" && rs !== "loaded") return;
			lychee.dropbox = true;
			loadingBar.hide();
			callback();
		};
		s.parentNode.insertBefore(g, s);
	}
};

/**
 * @returns {string}
 */
lychee.getEventName = function () {
	if (lychee.device_type === "mobile") {
		return "touchend";
	}
	return "click";
};

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
 * @param {XMLHttpRequest} jqXHR
 * @param {Object} params the original JSON parameters of the request
 * @param {?LycheeException} lycheeException the Lychee Exception
 * @returns {boolean}
 */
lychee.handleAPIError = function (jqXHR, params, lycheeException) {
	if (api.hasSessionExpired(jqXHR, lycheeException)) {
		loadingBar.show("error", "Session expired.");
		setTimeout(function () {
			lychee.goto();
			window.location.reload();
		}, 3000);
	} else {
		var msg = jqXHR.statusText + (lycheeException ? " - " + lycheeException.message : "");
		loadingBar.show("error", msg);
		console.error("The server returned an error response", {
			description: msg,
			params: params,
			response: lycheeException
		});
	}
	return true;
};

/**
 * @returns {void}
 */
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

/**
 * @returns {void}
 */
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

/**
 * @returns {void}
 */
lychee.fullscreenToggle = function () {
	if (lychee.fullscreenStatus()) {
		lychee.fullscreenExit();
	} else {
		lychee.fullscreenEnter();
	}
};

/**
 * @returns {boolean}
 */
lychee.fullscreenStatus = function () {
	var elem = document.fullscreenElement || document.mozFullScreenElement || document.webkitFullscreenElement || document.msFullscreenElement;
	return !!elem;
};

/**
 * @returns {boolean}
 */
lychee.fullscreenAvailable = function () {
	return document.fullscreenEnabled || document.mozFullscreenEnabled || document.webkitFullscreenEnabled || document.msFullscreenEnabled;
};

/**
 * @returns {void}
 */
lychee.fullscreenUpdate = function () {
	if (lychee.fullscreenStatus()) {
		$("#button_fs_album_enter,#button_fs_enter").hide();
		$("#button_fs_album_exit,#button_fs_exit").show();
	} else {
		$("#button_fs_album_enter,#button_fs_enter").show();
		$("#button_fs_album_exit,#button_fs_exit").hide();
	}
};

/**
 * @returns {void}
 */
lychee.footer_show = function () {
	setTimeout(function () {
		lychee.footer.removeClass("hide_footer");
	}, 200);
};

/**
 * @returns {void}
 */
lychee.footer_hide = function () {
	lychee.footer.addClass("hide_footer");
};

/**
 * Sets the height of the content area.
 *
 * Because the height of the footer can vary, we need to set some
 * dimensions dynamically, at startup.
 *
 * @returns {void}
 */
lychee.adjustContentHeight = function () {
	if (lychee.footer.length > 0) {
		lychee.content.css("min-height", "calc(100vh - " + lychee.content.css("padding-top") + " - " + lychee.content.css("padding-bottom") + " - " + lychee.footer.outerHeight() + "px)");
		$("#container").css("padding-bottom", lychee.footer.outerHeight());
	} else {
		lychee.content.css("min-height", "calc(100vh - " + lychee.content.css("padding-top") + " - " + lychee.content.css("padding-bottom") + ")");
	}
};

/**
 * @returns {string}
 */
lychee.getBaseUrl = function () {
	if (location.href.includes("index.html")) {
		return location.href.replace("index.html" + location.hash, "");
	} else if (location.href.includes("gallery#")) {
		return location.href.replace("gallery" + location.hash, "");
	} else {
		return location.href.replace(location.hash, "");
	}
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
 * @description Select multiple albums or photos.
 */

/**
 * @param {jQuery.Event} e
 * @returns {boolean}
 */
var isSelectKeyPressed = function isSelectKeyPressed(e) {
	return e.metaKey || e.ctrlKey;
};

var multiselect = {
	/** @type {string[]} */
	ids: [],
	albumsSelected: 0,
	photosSelected: 0,
	/** @type {?jQuery} */
	lastClicked: null
};

/**
 * @typedef SelectionPosition
 *
 * @property {number} top
 * @property {number} right
 * @property {number} bottom
 * @property {number} left
 */

/**
 * @type {?SelectionPosition}
 */
multiselect.position = null;

multiselect.bind = function () {
	$(".content").on("mousedown", function (e) {
		if (e.which === 1) multiselect.show(e);
	});

	return true;
};

/**
 * @returns {void}
 */
multiselect.unbind = function () {
	$(".content").off("mousedown");
};

/**
 * @param {string} id
 * @returns {{position: number, selected: boolean}}
 */
multiselect.isSelected = function (id) {
	var pos = multiselect.ids.indexOf(id);

	return {
		selected: pos !== -1,
		position: pos
	};
};

/**
 * @param {jQuery} object
 * @param {string} id
 */
multiselect.toggleItem = function (object, id) {
	if (album.isSmartID(id) || album.isSearchID(id)) return;

	var selected = multiselect.isSelected(id).selected;

	if (selected === false) multiselect.addItem(object, id);else multiselect.removeItem(object, id);
};

/**
 * @param {jQuery} object
 * @param {string} id
 */
multiselect.addItem = function (object, id) {
	if (album.isSmartID(id) || album.isSearchID(id)) return;
	if (!lychee.admin && albums.isShared(id)) return;
	if (multiselect.isSelected(id).selected === true) return;

	var isAlbum = object.hasClass("album");

	if (isAlbum && multiselect.photosSelected > 0 || !isAlbum && multiselect.albumsSelected > 0) {
		loadingBar.show("error", "Please select either albums or photos!");
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

/**
 * @param {jQuery} object
 * @param {string} id
 */
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

/**
 * @param {jQuery.Event} e
 * @param {jQuery} albumObj
 *
 * @returns {void}
 */
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

/**
 * @param {jQuery.Event} e
 * @param {jQuery} photoObj
 *
 * @returns {void}
 */
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

/**
 * @param {jQuery.Event} e
 * @param {jQuery} albumObj
 *
 * @returns {void}
 */
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

/**
 * @param {jQuery.Event} e
 * @param {jQuery} photoObj
 *
 * @returns {void}
 */
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
		contextMenu.photo(_photo3.getID(), e);
	} else {
		loadingBar.show("error", "Could not find what you want.");
	}
};

/**
 * @returns {void}
 */
multiselect.clearSelection = function () {
	multiselect.deselect($(".photo.active, .album.active"));
	multiselect.ids = [];
	multiselect.albumsSelected = 0;
	multiselect.photosSelected = 0;
	multiselect.lastClicked = null;
};

/**
 * @param {jQuery.Event} e
 * @returns {boolean}
 */
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

	multiselect.position = {
		top: e.pageY,
		right: $(document).width() - e.pageX,
		bottom: $(document).height() - e.pageY,
		left: e.pageX
	};

	$("body").append(build.multiselect(multiselect.position.top, multiselect.position.left));

	$(document).on("mousemove", multiselect.resize).on("mouseup", function (_e) {
		if (_e.which === 1) {
			multiselect.getSelection(_e);
		}
	});
};

/**
 * @param {jQuery.Event} e
 * @returns {boolean}
 */
multiselect.resize = function (e) {
	if (multiselect.position === null) return false;

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

/**
 * @returns {void}
 */
multiselect.stopResize = function () {
	if (multiselect.position !== null) $(document).off("mousemove mouseup");
};

/**
 * @returns {null|{top: number, left: number, width: number, height: number}}
 */
multiselect.getSize = function () {
	if (!visible.multiselect()) return null;

	var $elem = $("#multiselect");
	var offset = $elem.offset();

	return {
		top: offset.top,
		left: offset.left,
		width: parseFloat($elem.css("width")),
		height: parseFloat($elem.css("height"))
	};
};

/**
 * TODO: This method is called **`get...`** but it doesn't get anything.
 *
 * @param {jQuery.Event} e
 * @returns {void}
 */
multiselect.getSelection = function (e) {
	var size = multiselect.getSize();

	if (visible.contextMenu()) return;
	if (!visible.multiselect()) return;

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

/**
 * @param {jQuery} elem
 * @returns {void}
 */
multiselect.select = function (elem) {
	elem.addClass("selected");
	elem.addClass("active");
};

/**
 * @param {jQuery} elem
 * @returns {void}
 */
multiselect.deselect = function (elem) {
	elem.removeClass("selected");
	elem.removeClass("active");
};

/**
 * Note, identical to {@link multiselect.close}
 * @returns {void}
 */
multiselect.hide = function () {
	_sidebar.setSelectable(true);
	multiselect.stopResize();
	multiselect.position = null;
	lychee.animate($("#multiselect"), "fadeOut");
	setTimeout(function () {
		return $("#multiselect").remove();
	}, 300);
};

/**
 * Note, identical to {@link multiselect.hide}
 * @returns {void}
 */
multiselect.close = function () {
	_sidebar.setSelectable(true);
	multiselect.stopResize();
	multiselect.position = null;
	lychee.animate($("#multiselect"), "fadeOut");
	setTimeout(function () {
		return $("#multiselect").remove();
	}, 300);
};

/**
 * @returns {void}
 */
multiselect.selectAll = function () {
	if (!album.isUploadable()) return;
	if (visible.search()) return;
	if (!visible.albums() && !visible.album) return;
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

var notifications = {
	/** @type {?EMailData} */
	json: null
};

/**
 * @param {EMailData} params
 * @returns {void}
 */
notifications.update = function (params) {
	if (params.email && params.email.length > 1) {
		var regexp = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;

		if (!regexp.test(String(params.email).toLowerCase())) {
			loadingBar.show("error", "Not a valid email address.");
			return;
		}
	}

	api.post("User::setEmail", params, function () {
		loadingBar.show("success", "Email updated!");
	});
};

notifications.load = function () {
	api.post("User::getEmail", {},
	/** @param {EMailData} data */function (data) {
		notifications.json = data;
		view.notifications.init();
	});
};

/**
 * @description Controls the access to password-protected albums and photos.
 */

var password = {};

/**
 * @callback UnlockSuccessCB
 * @returns {void}
 */

/**
 * Shows the "album unlock"-dialog, tries to unlock the album and calls
 * the provided callback in case of success.
 *
 * @param {string} albumID - the ID of the album which shall be unlocked
 * @param {UnlockSuccessCB} callback - called in case of success
 */
password.getDialog = function (albumID, callback) {
	/**
  * @typedef UnlockDialogResult
  * @property {string} password
  */

	/** @param {UnlockDialogResult} data */
	var action = function action(data) {
		var params = {
			albumID: albumID,
			password: data.password
		};

		api.post("Album::unlock", params, function () {
			basicModal.close();
			callback();
		}, null, function (jqXHR, params2, lycheeException) {
			if ((jqXHR.status === 401 || jqXHR.status === 403) && lycheeException.message.includes("Password is invalid")) {
				basicModal.error("password");
				return true;
			}
			basicModal.close();
			return false;
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

var _photo3 = {
	/** @type {?Photo} */
	json: null,
	cache: null,
	/** @type {?boolean} indicates whether the browser supports prefetching of images; `null` if support hasn't been determined yet */
	supportsPrefetch: null,
	/** @type {?LivePhotosKit.Player} */
	livePhotosObject: null
};

/**
 * @returns {?string} - the photo ID
 */
_photo3.getID = function () {
	var id = _photo3.json ? _photo3.json.id : $(".photo:hover, .photo.active").attr("data-id");
	id = typeof id === "string" && /^[-_0-9a-zA-Z]{24}$/.test(id) ? id : null;

	return id;
};

/**
 *
 * @param {string} photoID
 * @param {string} albumID
 * @param {boolean} autoplay - automatically start playback, if the photo is a video or live photo
 *
 * @returns {void}
 */
_photo3.load = function (photoID, albumID, autoplay) {
	/**
  * @param {Photo} data
  * @returns {void}
  */
	var successHandler = function successHandler(data) {
		_photo3.json = data;
		// TODO: `photo.json.original_album_id` is set only, but never read; do we need it?
		_photo3.json.original_album_id = _photo3.json.album_id;
		// TODO: Why do we overwrite the true album ID of a photo, by the externally provided one? I guess we need it, because the album which the user came from might also be a smart album or a tag album. However, in this case I would prefer to leave the `album_id  untouched (don't rename it to `original_album_id`) and call this one `effective_album_id` instead.
		_photo3.json.album_id = albumID;

		if (!visible.photo()) view.photo.show();
		view.photo.init(autoplay);
		lychee.imageview.show();

		if (!lychee.hide_content_during_imgview) {
			setTimeout(function () {
				lychee.content.show();
				tabindex.makeUnfocusable(lychee.content);
			}, 300);
		}
	};

	api.post("Photo::get", {
		photoID: photoID
	}, successHandler);
};

/**
 * @returns {boolean}
 */
_photo3.hasExif = function () {
	return !!_photo3.json.make || !!_photo3.json.model || !!_photo3.json.shutter || !!_photo3.json.aperture || !!_photo3.json.focal || !!_photo3.json.iso;
};

/**
 * @returns {boolean}
 */
_photo3.hasTakestamp = function () {
	return !!_photo3.json.taken_at;
};

/**
 * @returns {boolean}
 */
_photo3.hasDesc = function () {
	return !!_photo3.json.description;
};

/**
 * @returns {boolean}
 */
_photo3.isLivePhoto = function () {
	return !!_photo3.json && // In case it's called, but not initialized
	!!_photo3.json.live_photo_url;
};

/**
 * @returns {boolean}
 */
_photo3.isLivePhotoInitialized = function () {
	return !!_photo3.livePhotosObject;
};

/**
 * @returns {boolean}
 */
_photo3.isLivePhotoPlaying = function () {
	return _photo3.isLivePhotoInitialized() && _photo3.livePhotosObject.isPlaying;
};

/**
 * @returns {void}
 */
_photo3.cycle_display_overlay = function () {
	var oldType = build.check_overlay_type(_photo3.json, lychee.image_overlay_type);
	var newType = build.check_overlay_type(_photo3.json, oldType, true);
	if (oldType !== newType) {
		lychee.image_overlay_type = newType;
		$("#image_overlay").remove();
		var newOverlay = build.overlay_image(_photo3.json);
		if (newOverlay !== "") lychee.imageview.append(newOverlay);
	}
};

/**
 * Preloads the next and previous photos for better response time
 *
 * @param {string} photoID
 * @returns {void}
 */
_photo3.preloadNextPrev = function (photoID) {
	if (!album.json || !album.json.photos) return;

	var photo = album.getByID(photoID);
	if (!photo) return;

	var imgs = $("img#image");
	// TODO: consider replacing the test for "@2x." by a simple comparison to photo.size_variants.medium2x.url.
	var isUsing2xCurrently = imgs.length > 0 && imgs[0].currentSrc !== null && imgs[0].currentSrc.includes("@2x.");

	$("head [data-prefetch]").remove();

	/**
  * @param {string} preloadID
  * @returns {void}
  */
	var preload = function preload(preloadID) {
		var preloadPhoto = album.getByID(preloadID);
		var href = "";

		if (preloadPhoto.size_variants.medium != null) {
			href = preloadPhoto.size_variants.medium.url;
			if (preloadPhoto.size_variants.medium2x != null && isUsing2xCurrently) {
				// If the currently displayed image uses the 2x variant,
				// chances are that so will the next one.
				href = preloadPhoto.size_variants.medium2x.url;
			}
		} else if (preloadPhoto.type && preloadPhoto.type.indexOf("video") === -1) {
			// Preload the original size, but only if it's not a video
			href = preloadPhoto.size_variants.original.url;
		}

		if (href !== "") {
			if (photo.supportsPrefetch === null) {
				/**
     * Copied from https://www.smashingmagazine.com/2016/02/preload-what-is-it-good-for/
     *
     * TODO: This method should not be defined dynamically, but defined and executed upon initialization once
     *
     * @param {DOMTokenList} tokenList
     * @param {string} token
     * @returns {boolean}
     */
				var DOMTokenListSupports = function DOMTokenListSupports(tokenList, token) {
					try {
						if (!tokenList || !tokenList.supports) {
							return false;
						}
						return tokenList.supports(token);
					} catch (e) {
						if (e instanceof TypeError) {
							console.log("The DOMTokenList doesn't have a supported tokens list");
						} else {
							console.error("That shouldn't have happened");
						}
						return false;
					}
				};
				photo.supportsPrefetch = DOMTokenListSupports(document.createElement("link").relList, "prefetch");
			}

			if (photo.supportsPrefetch) {
				$("head").append(lychee.html(_templateObject51, href));
			} else {
				// According to https://caniuse.com/#feat=link-rel-prefetch,
				// as of mid-2019 it's mainly Safari (both on desktop and mobile)
				new Image().src = href;
			}
		}
	};

	if (photo.next_photo_id) {
		preload(photo.next_photo_id);
	}
	if (photo.previous_photo_id) {
		preload(photo.previous_photo_id);
	}
};

/**
 * @param {number} [animationDuration=300]
 * @param {number} [pauseBetweenUpdated=10]
 * @returns {void}
 */
_photo3.updateSizeLivePhotoDuringAnimation = function () {
	var animationDuration = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : 300;
	var pauseBetweenUpdated = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : 10;

	// For the LivePhotoKit, we need to call the updateSize manually
	// during CSS animations
	//
	var interval = setInterval(function () {
		if (_photo3.isLivePhotoInitialized()) {
			_photo3.livePhotosObject.updateSize();
		}
	}, pauseBetweenUpdated);

	setTimeout(function () {
		clearInterval(interval);
	}, animationDuration);
};

/**
 * @param {boolean} animate
 * @returns {void}
 */
_photo3.previous = function (animate) {
	var curPhoto = _photo3.getID() !== null && album.json ? album.getByID(_photo3.getID()) : null;
	if (!curPhoto || !curPhoto.previous_photo_id) return;

	var delay = animate ? 200 : 0;

	if (animate) {
		$("#imageview #image").css({
			WebkitTransform: "translateX(100%)",
			MozTransform: "translateX(100%)",
			transform: "translateX(100%)",
			opacity: 0
		});
	}

	setTimeout(function () {
		_photo3.livePhotosObject = null;
		lychee.goto(album.getID() + "/" + curPhoto.previous_photo_id, false);
	}, delay);
};

/**
 * @param {boolean} animate
 * @returns {void}
 */
_photo3.next = function (animate) {
	var curPhoto = _photo3.getID() !== null && album.json ? album.getByID(_photo3.getID()) : null;
	if (!curPhoto || !curPhoto.next_photo_id) return;

	var delay = animate ? 200 : 0;

	if (animate === true) {
		$("#imageview #image").css({
			WebkitTransform: "translateX(-100%)",
			MozTransform: "translateX(-100%)",
			transform: "translateX(-100%)",
			opacity: 0
		});
	}

	setTimeout(function () {
		_photo3.livePhotosObject = null;
		lychee.goto(album.getID() + "/" + curPhoto.next_photo_id, false);
	}, delay);
};

/**
 * @param {string[]} photoIDs
 * @returns {boolean}
 */
_photo3.delete = function (photoIDs) {
	var action = {};
	var cancel = {};
	var msg = "";
	var photoTitle = "";

	if (photoIDs.length === 1) {
		// Get title if only one photo is selected
		if (visible.photo()) photoTitle = _photo3.json.title;else photoTitle = album.getByID(photoIDs[0]).title;

		// Fallback for photos without a title
		if (!photoTitle) photoTitle = lychee.locale["UNTITLED"];
	}

	action.fn = function () {
		var nextPhotoID = null;
		var previousPhotoID = null;

		basicModal.close();

		photoIDs.forEach(function (id, index) {
			// Change reference for the next and previous photo
			var curPhoto = album.getByID(id);
			if (curPhoto.next_photo_id !== null || curPhoto.previous_photo_id !== null) {
				nextPhotoID = curPhoto.next_photo_id;
				previousPhotoID = curPhoto.previous_photo_id;

				if (previousPhotoID !== null) {
					album.getByID(previousPhotoID).next_photo_id = nextPhotoID;
				}
				if (nextPhotoID !== null) {
					album.getByID(nextPhotoID).previous_photo_id = previousPhotoID;
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
			if (nextPhotoID !== null && nextPhotoID !== _photo3.getID()) {
				lychee.goto(album.getID() + "/" + nextPhotoID);
			} else if (previousPhotoID !== null && previousPhotoID !== _photo3.getID()) {
				lychee.goto(album.getID() + "/" + previousPhotoID);
			} else {
				lychee.goto(album.getID());
			}
		} else if (!visible.albums()) {
			lychee.goto(album.getID());
		}

		api.post("Photo::delete", { photoIDs: photoIDs });
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

/**
 *
 * @param {string[]} photoIDs
 * @returns {void}
 */
_photo3.setTitle = function (photoIDs) {
	var oldTitle = "";
	var msg = "";

	if (photoIDs.length === 1) {
		// Get old title if only one photo is selected
		if (_photo3.json) oldTitle = _photo3.json.title;else if (album.json) oldTitle = album.getByID(photoIDs[0]).title;
	}

	/**
  * @param {{title: string}} data
  * @returns {void}
  */
	var action = function action(data) {
		if (!data.title.trim()) {
			basicModal.error("title");
			return;
		}

		basicModal.close();

		var newTitle = data.title ? data.title : null;

		if (visible.photo()) {
			_photo3.json.title = newTitle;
			view.photo.title();
		}

		photoIDs.forEach(function (id) {
			// TODO: The line below looks suspicious: It is inconsistent to the code some lines above.
			album.getByID(id).title = newTitle;
			view.album.content.title(id);
		});

		api.post("Photo::setTitle", {
			photoIDs: photoIDs,
			title: newTitle
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

/**
 *
 * @param {string[]} photoIDs IDs of photos to be copied
 * @param {?string} albumID ID of destination album; `null` means root album
 * @returns {void}
 */
_photo3.copyTo = function (photoIDs, albumID) {
	api.post("Photo::duplicate", {
		photoIDs: photoIDs,
		albumID: albumID
	}, function () {
		return album.reload();
	});
};

/**
 * @param {string[]} photoIDs
 * @param {string} albumID
 * @returns {void}
 */
_photo3.setAlbum = function (photoIDs, albumID) {
	var nextPhotoID = null;
	var previousPhotoID = null;

	photoIDs.forEach(function (id, index) {
		// Change reference for the next and previous photo
		var curPhoto = album.getByID(id);
		if (curPhoto.next_photo_id !== null || curPhoto.previous_photo_id !== null) {
			nextPhotoID = curPhoto.next_photo_id;
			previousPhotoID = curPhoto.previous_photo_id;

			if (previousPhotoID !== null) {
				album.getByID(previousPhotoID).next_photo_id = nextPhotoID;
			}
			if (nextPhotoID !== null) {
				album.getByID(nextPhotoID).previous_photo_id = previousPhotoID;
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
		if (nextPhotoID !== null && nextPhotoID !== _photo3.getID()) {
			lychee.goto(album.getID() + "/" + nextPhotoID);
		} else if (previousPhotoID !== null && previousPhotoID !== _photo3.getID()) {
			lychee.goto(album.getID() + "/" + previousPhotoID);
		} else {
			lychee.goto(album.getID());
		}
	}

	api.post("Photo::setAlbum", {
		photoIDs: photoIDs,
		albumID: albumID
	}, function () {
		// We only really need to do anything here if the destination
		// is a (possibly nested) subalbum of the current album; but
		// since we have no way of figuring it out (albums.json is
		// null), we need to reload.
		if (visible.album()) {
			album.reload();
		}
	});
};

/**
 * Toggles the star-property of the currently visible photo.
 *
 * @returns {void}
 */
_photo3.toggleStar = function () {
	_photo3.json.is_starred = !_photo3.json.is_starred;
	view.photo.star();
	albums.refresh();

	api.post("Photo::setStar", {
		photoIDs: [_photo3.json.id],
		is_starred: _photo3.json.is_starred
	});
};

/**
 * Sets the star-property of the given photos.
 *
 * @param {string[]} photoIDs
 * @param {boolean} isStarred
 * @returns {void}
 */
_photo3.setStar = function (photoIDs, isStarred) {
	photoIDs.forEach(function (id) {
		album.getByID(id).is_starred = isStarred;
		view.album.content.star(id);
	});

	albums.refresh();

	api.post("Photo::setStar", {
		photoIDs: photoIDs,
		is_starred: isStarred
	});
};

/**
 * Edits the protection policy of a photo.
 *
 * This method is a misnomer, it does not only set the policy, it also creates
 * and handles the edit dialog
 *
 * @param {string} photoID
 * @returns {void}
 */
_photo3.setProtectionPolicy = function (photoID) {
	var msg_switch = lychee.html(_templateObject56, lychee.locale["PHOTO_PUBLIC"], lychee.locale["PHOTO_PUBLIC_EXPL"]);

	var msg_choices = lychee.html(_templateObject57, build.iconic("check"), lychee.locale["PHOTO_FULL"], lychee.locale["PHOTO_FULL_EXPL"], build.iconic("check"), lychee.locale["PHOTO_HIDDEN"], lychee.locale["PHOTO_HIDDEN_EXPL"], build.iconic("check"), lychee.locale["PHOTO_DOWNLOADABLE"], lychee.locale["PHOTO_DOWNLOADABLE_EXPL"], build.iconic("check"), lychee.locale["PHOTO_SHARE_BUTTON_VISIBLE"], lychee.locale["PHOTO_SHARE_BUTTON_VISIBLE_EXPL"], build.iconic("check"), lychee.locale["PHOTO_PASSWORD_PROT"], lychee.locale["PHOTO_PASSWORD_PROT_EXPL"]);

	if (_photo3.json.is_public === 2) {
		// Public album. We can't actually change anything, but we will
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

		$('.basicModal .switch input[name="is_public"]').prop("checked", true);
		if (album.json) {
			if (album.json.grants_full_photo) {
				$('.basicModal .choice input[name="grants_full_photo"]').prop("checked", true);
			}
			// Photos in public albums are never hidden as such.  It's the
			// album that's hidden.  Or is that distinction irrelevant to end
			// users?
			if (album.json.is_downloadable) {
				$('.basicModal .choice input[name="is_downloadable"]').prop("checked", true);
			}
			if (album.json.has_password) {
				$('.basicModal .choice input[name="has_password"]').prop("checked", true);
			}
		}

		$(".basicModal .switch input").attr("disabled", true);
		$(".basicModal .switch .label").addClass("label--disabled");
	} else {
		// Private album -- each photo can be shared individually.

		var _msg = lychee.html(_templateObject59, msg_switch, lychee.locale["PHOTO_EDIT_GLOBAL_SHARING_TEXT"], msg_choices);

		// TODO: Actually, the action handler receives an object with values of all input fields. There is no need to run use a jQuery-selector
		var action = function action() {
			/**
    * Note: `newIsPublic` must be of type `number`, because `photo.is_public` is a number, too
    * @type {number}
    */
			var newIsPublic = $('.basicModal .switch input[name="is_public"]:checked').length;

			if (newIsPublic !== _photo3.json.is_public) {
				if (visible.photo()) {
					_photo3.json.is_public = newIsPublic;
					view.photo.public();
				}

				album.getByID(photoID).is_public = newIsPublic;
				view.album.content.public(photoID);

				albums.refresh();

				api.post("Photo::setPublic", {
					photoID: photoID,
					is_public: newIsPublic !== 0
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

		$('.basicModal .switch input[name="is_public"]').on("click", function () {
			if ($(this).prop("checked") === true) {
				if (lychee.full_photo) {
					$('.basicModal .choice input[name="grants_full_photo"]').prop("checked", true);
				}
				if (lychee.public_photos_hidden) {
					$('.basicModal .choice input[name="requires_link"]').prop("checked", true);
				}
				if (lychee.downloadable) {
					$('.basicModal .choice input[name="is_downloadable"]').prop("checked", true);
				}
				if (lychee.share_button_visible) {
					$('.basicModal .choice input[name="is_share_button_visible"]').prop("checked", true);
				}
				// Photos shared individually can't be password-protected.
			} else {
				$(".basicModal .choice input").prop("checked", false);
			}
		});

		if (_photo3.json.is_public === 1) {
			$('.basicModal .switch input[name="is_public"]').click();
		}
	}
};

/**
 * Edits the description of a photo.
 *
 * This method is a misnomer, it does not only set the description, it also creates and handles the edit dialog
 *
 * @param {string} photoID
 * @returns {void}
 */
_photo3.setDescription = function (photoID) {
	var oldDescription = _photo3.json.description ? _photo3.json.description : "";

	/**
  * @param {{description: string}} data
  */
	var action = function action(data) {
		basicModal.close();

		var description = data.description ? data.description : null;

		if (visible.photo()) {
			_photo3.json.description = description;
			view.photo.description();
		}

		api.post("Photo::setDescription", {
			photoID: photoID,
			description: description
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

/**
 * @param {string[]} photoIDs
 * @returns {void}
 */
_photo3.editTags = function (photoIDs) {
	/** @type {string[]} */
	var oldTags = [];

	// Get tags
	if (visible.photo()) oldTags = _photo3.json.tags.sort();else if (visible.album() && photoIDs.length === 1) oldTags = album.getByID(photoIDs[0]).tags.sort();else if (visible.search() && photoIDs.length === 1) oldTags = album.getByID(photoIDs[0]).tags.sort();else if (visible.album() && photoIDs.length > 1) {
		oldTags = album.getByID(photoIDs[0]).tags.sort();
		var areIdentical = photoIDs.every(function (id) {
			var oldTags2 = album.getByID(id).tags.sort();
			if (oldTags.length !== oldTags2.length) return false;
			for (var tagIdx = 0; tagIdx !== oldTags.length; tagIdx++) {
				if (oldTags[tagIdx] !== oldTags2[tagIdx]) return false;
			}
			return true;
		});
		if (!areIdentical) oldTags = [];
	}

	/**
  * @param {{tags: string}} data
  * @returns {void}
  */
	var action = function action(data) {
		basicModal.close();
		var newTags = data.tags.split(",").map(function (tag) {
			return tag.trim();
		}).filter(function (tag) {
			return tag !== "" && tag.indexOf(",") === -1;
		}).sort();
		_photo3.setTags(photoIDs, newTags);
	};

	var input = lychee.html(_templateObject61, oldTags.join(", "));

	var msg = photoIDs.length === 1 ? lychee.html(_templateObject5, lychee.locale["PHOTO_NEW_TAGS"], input) : lychee.html(_templateObject55, lychee.locale["PHOTO_NEW_TAGS_1"], photoIDs.length, lychee.locale["PHOTO_NEW_TAGS_2"], input);

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

/**
 * @param {string[]} photoIDs
 * @param {string[]} tags
 * @returns {void}
 */
_photo3.setTags = function (photoIDs, tags) {
	if (visible.photo()) {
		_photo3.json.tags = tags;
		view.photo.tags();
	}

	photoIDs.forEach(function (id) {
		album.getByID(id).tags = tags;
	});

	api.post("Photo::setTags", {
		photoIDs: photoIDs,
		tags: tags
	}, function () {
		// If we have any tag albums, force a refresh.
		if (albums.json && albums.json.tag_albums.length !== 0) {
			albums.refresh();
		}
	});
};

/**
 * Deletes the tag at the given index from the photo.
 *
 * @param {string} photoID
 * @param {number} index
 */
_photo3.deleteTag = function (photoID, index) {
	_photo3.json.tags.splice(index, 1);
	_photo3.setTags([photoID], _photo3.json.tags);
};

/**
 * @param {string} photoID
 * @param {string} service - one out of `"twitter"`, `"facebook"`, `"mail"` or `"dropbox"`
 * @returns {void}
 */
_photo3.share = function (photoID, service) {
	if (!_photo3.json.is_share_button_visible) {
		return;
	}

	var url = _photo3.getViewLink(photoID);

	switch (service) {
		case "twitter":
			window.open("https://twitter.com/share?url=" + encodeURI(url));
			break;
		case "facebook":
			window.open("https://www.facebook.com/sharer.php?u=" + encodeURI(url) + "&t=" + encodeURI(_photo3.json.title));
			break;
		case "mail":
			location.href = "mailto:?subject=" + encodeURI(_photo3.json.title) + "&body=" + encodeURI(url);
			break;
		case "dropbox":
			lychee.loadDropbox(function () {
				var filename = _photo3.json.title + "." + _photo3.getDirectLink().split(".").pop();
				Dropbox.save(_photo3.getDirectLink(), filename);
			});
			break;
	}
};

/**
 * @param {string} photoID
 * @returns {void}
 */
_photo3.setLicense = function (photoID) {
	/**
  * @param {{license: string}} data
  */
	var action = function action(data) {
		basicModal.close();
		var license = data.license;

		var params = {
			photoID: photoID,
			license: license
		};

		api.post("Photo::setLicense", params, function () {
			// update the photo JSON and reload the license in the sidebar
			_photo3.json.license = params.license;
			view.photo.license();
		});
	};

	var msg = lychee.html(_templateObject8, lychee.locale["PHOTO_LICENSE"], lychee.locale["PHOTO_LICENSE_NONE"], lychee.locale["PHOTO_RESERVED"], lychee.locale["PHOTO_LICENSE_HELP"]);

	basicModal.show({
		body: msg,
		callback: function callback() {
			$("select#license").val(_photo3.json.license === "" ? "none" : _photo3.json.license);
		},
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

/**
 * @param {string[]} photoIDs
 * @param {?string} [kind=null] - the type of size variant; one out of
 *                                `"FULL"`, `"MEDIUM2X"`, `"MEDIUM"`,
 *                                `"SMALL2X"`, `"SMALL"`, `"THUMB2X"` or
 *                                `"THUMB"`,
 * @returns {void}
 */
_photo3.getArchive = function (photoIDs) {
	var kind = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : null;

	if (photoIDs.length === 1 && kind === null) {
		// For a single photo, allow to pick the kind via a dialog box.

		var myPhoto = void 0;

		if (_photo3.json && _photo3.json.id === photoIDs[0]) {
			myPhoto = _photo3.json;
		} else {
			myPhoto = album.getByID(photoIDs[0]);
		}

		/**
   * @param {string} id - the ID of the button, same semantics as "kind"
   * @param {string} label - the caption on the button
   * @returns {string} - HTML
   */
		var buildButton = function buildButton(id, label) {
			return lychee.html(_templateObject62, id, lychee.locale["DOWNLOAD"], build.iconic("cloud-download"), label);
		};

		var msg = lychee.html(_templateObject63);

		if (myPhoto.size_variants.original.url) {
			msg += buildButton("FULL", lychee.locale["PHOTO_FULL"] + " (" + myPhoto.size_variants.original.width + "x" + myPhoto.size_variants.original.height + ",\n\t\t\t\t" + lychee.locale.printFilesizeLocalized(myPhoto.size_variants.original.filesize) + ")");
		}
		if (myPhoto.live_photo_url !== null) {
			msg += buildButton("LIVEPHOTOVIDEO", "" + lychee.locale["PHOTO_LIVE_VIDEO"]);
		}
		if (myPhoto.size_variants.medium2x !== null) {
			msg += buildButton("MEDIUM2X", lychee.locale["PHOTO_MEDIUM_HIDPI"] + " (" + myPhoto.size_variants.medium2x.width + "x" + myPhoto.size_variants.medium2x.height + ",\n\t\t\t\t" + lychee.locale.printFilesizeLocalized(myPhoto.size_variants.medium2x.filesize) + ")");
		}
		if (myPhoto.size_variants.medium !== null) {
			msg += buildButton("MEDIUM", lychee.locale["PHOTO_MEDIUM"] + " (" + myPhoto.size_variants.medium.width + "x" + myPhoto.size_variants.medium.height + ",\n\t\t\t\t" + lychee.locale.printFilesizeLocalized(myPhoto.size_variants.medium.filesize) + ")");
		}
		if (myPhoto.size_variants.small2x !== null) {
			msg += buildButton("SMALL2X", lychee.locale["PHOTO_SMALL_HIDPI"] + " (" + myPhoto.size_variants.small2x.width + "x" + myPhoto.size_variants.small2x.height + ",\n\t\t\t\t" + lychee.locale.printFilesizeLocalized(myPhoto.size_variants.small2x.filesize) + ")");
		}
		if (myPhoto.size_variants.small !== null) {
			msg += buildButton("SMALL", lychee.locale["PHOTO_SMALL"] + " (" + myPhoto.size_variants.small.width + "x" + myPhoto.size_variants.small.height + ",\n\t\t\t\t" + lychee.locale.printFilesizeLocalized(myPhoto.size_variants.small.filesize) + ")");
		}
		if (myPhoto.size_variants.thumb2x !== null) {
			msg += buildButton("THUMB2X", lychee.locale["PHOTO_THUMB_HIDPI"] + " (" + myPhoto.size_variants.thumb2x.width + "x" + myPhoto.size_variants.thumb2x.height + ",\n\t\t\t\t" + lychee.locale.printFilesizeLocalized(myPhoto.size_variants.thumb2x.filesize) + ")");
		}
		if (myPhoto.size_variants.thumb !== null) {
			msg += buildButton("THUMB", lychee.locale["PHOTO_THUMB"] + " (" + myPhoto.size_variants.thumb.width + "x" + myPhoto.size_variants.thumb.height + ",\n\t\t\t\t" + lychee.locale.printFilesizeLocalized(myPhoto.size_variants.thumb.filesize) + ")");
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
			var kind = this.id;
			basicModal.close();
			_photo3.getArchive(photoIDs, kind);
		});
	} else {
		location.href = "api/Photo::getArchive?photoIDs=" + photoIDs.join() + "&kind=" + kind;
	}
};

/**
 * Shows a dialog to share the view URL via a QR code.
 *
 * @param {string} photoID
 * @returns {void}
 */
_photo3.qrCode = function (photoID) {
	/** @type {?Photo} */
	var myPhoto = _photo3.json && _photo3.json.id === photoID ? _photo3.json : album.getByID(photoID);

	if (myPhoto == null) {
		lychee.error("Error: photo " + photoID + " not found !");
		return;
	}

	var msg = lychee.html(_templateObject12);

	basicModal.show({
		body: msg,
		callback: function callback() {
			QrCreator.render({
				text: _photo3.getViewLink(myPhoto.id),
				radius: 0.0,
				ecLevel: "H",
				fill: "#000000",
				background: "#FFFFFF",
				size: 440 // 500px (modal width) - 2*30px (padding)
			}, document.getElementById("qr-code"));
		},
		buttons: {
			cancel: {
				title: lychee.locale["CLOSE"],
				fn: basicModal.close
			}
		}
	});
};

/**
 * @returns {string}
 */
_photo3.getDirectLink = function () {
	return _photo3.json && _photo3.json.size_variants && _photo3.json.size_variants.original && _photo3.json.size_variants.original.url ? _photo3.json.size_variants.original.url : "";
};

/**
 * @param {string} photoID
 * @returns {string}
 */
_photo3.getViewLink = function (photoID) {
	return lychee.getBaseUrl() + "view?p=" + photoID;
};

/**
 * @param photoID
 * @returns {void}
 */
_photo3.showDirectLinks = function (photoID) {
	if (!_photo3.json || _photo3.json.id !== photoID) {
		return;
	}

	/**
  * @param {string} label
  * @param {string} url
  * @returns {string} - HTML
  */
	var buildLine = function buildLine(label, url) {
		return lychee.html(_templateObject65, label, url, lychee.locale["URL_COPY_TO_CLIPBOARD"], build.iconic("copy", "ionicons"));
	};

	var msg = lychee.html(_templateObject66, buildLine(lychee.locale["PHOTO_VIEW"], _photo3.getViewLink(photoID)), lychee.locale["PHOTO_DIRECT_LINKS_TO_IMAGES"]);

	if (_photo3.json.size_variants.original.url) {
		msg += buildLine(lychee.locale["PHOTO_FULL"] + " (" + _photo3.json.size_variants.original.width + "x" + _photo3.json.size_variants.original.height + ")", lychee.getBaseUrl() + _photo3.json.size_variants.original.url);
	}
	if (_photo3.json.size_variants.medium2x !== null) {
		msg += buildLine(lychee.locale["PHOTO_MEDIUM_HIDPI"] + " (" + _photo3.json.size_variants.medium2x.width + "x" + _photo3.json.size_variants.medium2x.height + ")", lychee.getBaseUrl() + _photo3.json.size_variants.medium2x.url);
	}
	if (_photo3.json.size_variants.medium !== null) {
		msg += buildLine(lychee.locale["PHOTO_MEDIUM"] + " (" + _photo3.json.size_variants.medium.width + "x" + _photo3.json.size_variants.medium.height + ")", lychee.getBaseUrl() + _photo3.json.size_variants.medium.url);
	}
	if (_photo3.json.size_variants.small2x !== null) {
		msg += buildLine(lychee.locale["PHOTO_SMALL_HIDPI"] + " (" + _photo3.json.size_variants.small2x.width + "x" + _photo3.json.size_variants.small2x.height + ")", lychee.getBaseUrl() + _photo3.json.size_variants.small2x.url);
	}
	if (_photo3.json.size_variants.small !== null) {
		msg += buildLine(lychee.locale["PHOTO_SMALL"] + " (" + _photo3.json.size_variants.small.width + "x" + _photo3.json.size_variants.small.height + ")", lychee.getBaseUrl() + _photo3.json.size_variants.small.url);
	}
	if (_photo3.json.size_variants.thumb2x !== null) {
		msg += buildLine(lychee.locale["PHOTO_THUMB_HIDPI"] + " (" + _photo3.json.size_variants.thumb2x.width + "x" + _photo3.json.size_variants.thumb2x.height + ")", lychee.getBaseUrl() + _photo3.json.size_variants.thumb2x.url);
	}
	if (_photo3.json.size_variants.thumb !== null) {
		msg += buildLine(lychee.locale["PHOTO_THUMB"] + " (" + _photo3.json.size_variants.thumb.width + "x" + _photo3.json.size_variants.thumb.height + ")", lychee.getBaseUrl() + _photo3.json.size_variants.thumb.url);
	}
	if (_photo3.json.live_photo_url) {
		msg += buildLine(" " + lychee.locale["PHOTO_LIVE_VIDEO"] + " ", lychee.getBaseUrl() + _photo3.json.live_photo_url);
	}

	msg += lychee.html(_templateObject67);

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
		navigator.clipboard.writeText($(this).prev().val()).then(function () {
			return loadingBar.show("success", lychee.locale["URL_COPIED_TO_CLIPBOARD"]);
		});
	});
};

/**
 * @description Takes care of every action a photoeditor can handle and execute.
 */

photoeditor = {};

/**
 * @param {string} photoID
 * @param {number} direction - either `1` or `-1`
 * @returns {void}
 */
photoeditor.rotate = function (photoID, direction) {
	api.post("PhotoEditor::rotate", {
		photoID: photoID,
		direction: direction
	},
	/** @param {Photo} data */
	function (data) {
		_photo3.json = data;
		// TODO: `photo.json.original_album_id` is set only, but never read; do we need it?
		_photo3.json.original_album_id = _photo3.json.album_id;
		if (album.json) {
			// TODO: Why do we overwrite the true album ID of a photo, by the externally provided one? I guess we need it, because the album which the user came from might also be a smart album or a tag album. However, in this case I would prefer to leave the `album_id  untouched (don't rename it to `original_album_id`) and call this one `effective_album_id` instead.
			_photo3.json.album_id = album.json.id;
		}

		var image = $("img#image");
		if (_photo3.json.size_variants.medium2x !== null) {
			image.prop("srcset", _photo3.json.size_variants.medium.url + " " + _photo3.json.size_variants.medium.width + "w, " + _photo3.json.size_variants.medium2x.url + " " + _photo3.json.size_variants.medium2x.width + "w");
		} else {
			image.prop("srcset", "");
		}
		image.prop("src", _photo3.json.size_variants.medium !== null ? _photo3.json.size_variants.medium.url : _photo3.json.size_variants.original.url);
		view.photo.onresize();
		view.photo.sidebar();
		album.updatePhoto(data);
	});
};

/**
 * @description Searches through your photos and albums.
 */

/**
 * The ID of the search album
 *
 * Constant `'search'`.
 *
 * @type {string}
 */
var SearchAlbumID = "search";

/**
 * @typedef SearchAlbum
 *
 * A "virtual" album which holds the search results in a form which is
 * mostly compatible with the other album types, i.e.
 * {@link Album}, {@link TagAlbum} and {@link SmartAlbum}.
 *
 * @property {string}  id                       - always equals `SearchAlbumID`
 * @property {string}  title                    - always equals `lychee.locale["SEARCH_RESULTS"]`
 * @property {Photo[]} photos                   - the found photos
 * @property {Album[]} albums                   - the found albums
 * @property {TagAlbum[]} tag_albums            - the found tag albums
 * @property {?Thumb}  thumb                    - always `null`; just a dummy entry, because all other albums {@link Album}, {@link TagAlbum}, {@link SmartAlbum} have it
 * @property {boolean} is_public                - always `false`; just a dummy entry, because all other albums {@link Album}, {@link TagAlbum}, {@link SmartAlbum} have it
 * @property {boolean} is_downloadable          - always `false`; just a dummy entry, because all other albums {@link Album}, {@link TagAlbum}, {@link SmartAlbum} have it
 * @property {boolean} is_share_button_visible  - always `false`; just a dummy entry, because all other albums {@link Album}, {@link TagAlbum}, {@link SmartAlbum} have it
 */

/**
 * The search object
 */
var search = {
	/** @type {?SearchResult} */
	json: null
};

/**
 * @param {string} term
 * @returns {void}
 */
search.find = function (term) {
	if (term.trim() === "") return;

	/** @param {SearchResult} data */
	var successHandler = function successHandler(data) {
		// Do nothing, if search result is identical to previous result
		if (search.json && search.json.checksum === data.checksum) {
			return;
		}

		search.json = data;

		// Create and assign a `SearchAlbum`
		album.json = {
			id: SearchAlbumID,
			title: lychee.locale["SEARCH_RESULTS"],
			photos: search.json.photos,
			albums: search.json.albums,
			tag_albums: search.json.tag_albums,
			thumb: null,
			is_public: false,
			is_downloadable: false,
			is_share_button_visible: false
		};

		var albumsData = "";
		var photosData = "";

		// Build HTML for album
		search.json.tag_albums.forEach(function (album) {
			albums.parse(album);
			albumsData += build.album(album);
		});
		search.json.albums.forEach(function (album) {
			albums.parse(album);
			albumsData += build.album(album);
		});

		// Build HTML for photo
		search.json.photos.forEach(function (photo) {
			photosData += build.photo(photo);
		});

		var albums_divider = lychee.locale["ALBUMS"];
		var photos_divider = lychee.locale["PHOTOS"];

		if (albumsData !== "") albums_divider += " (" + (search.json.tag_albums.length + search.json.albums.length) + ")";
		if (photosData !== "") {
			photos_divider += " (" + search.json.photos.length + ")";
			if (lychee.layout === 1) {
				photosData = '<div class="justified-layout">' + photosData + "</div>";
			} else if (lychee.layout === 2) {
				photosData = '<div class="unjustified-layout">' + photosData + "</div>";
			}
		}

		// 1. No albums and photos
		// 2. Only photos
		// 3. Only albums
		// 4. Albums and photos
		var html = albumsData === "" && photosData === "" ? "" : albumsData === "" ? build.divider(photos_divider) + photosData : photosData === "" ? build.divider(albums_divider) + albumsData : build.divider(albums_divider) + albumsData + build.divider(photos_divider) + photosData;

		$(".no_content").remove();
		lychee.animate($(".content"), "contentZoomOut");

		setTimeout(function () {
			if (visible.photo()) view.photo.hide();
			if (visible.sidebar()) _sidebar.toggle(false);
			if (visible.mapview()) mapview.close();

			header.setMode("albums");

			if (html === "") {
				lychee.content.html("");
				$("body").append(build.no_content("magnifying-glass"));
			} else {
				lychee.content.html(html);
				// Here we exploit the layout method of an album although
				// the search result is not a proper album.
				// It would be much better to have a component like
				// `view.photos` (note the plural form) which takes care of
				// all photo listings independent of the surrounding "thing"
				// (i.e. regular album, tag album, search result)
				view.album.content.justify(search.json.photos);
				lychee.animate(lychee.content, "contentZoomIn");
			}
			lychee.setTitle(lychee.locale["SEARCH_RESULTS"], false);

			$(window).scrollTop(0);
		}, 300);
	};

	/** @returns {void} */
	var timeoutHandler = function timeoutHandler() {
		if (header.dom(".header__search").val().length !== 0) {
			api.post("Search::run", { term: term }, successHandler);
		} else {
			search.reset();
		}
	};

	clearTimeout($(window).data("timeout"));
	$(window).data("timeout", setTimeout(timeoutHandler, 250));
};

search.reset = function () {
	header.dom(".header__search").val("");
	$(".no_content").remove();

	if (search.json !== null) {
		// Trash data
		album.json = null;
		_photo3.json = null;
		search.json = null;

		lychee.animate($(".divider"), "fadeOut");
		lychee.goto();
	}
};

/**
 * @description Lets you change settings.
 */

var settings = {};

/**
 * @returns {void}
 */
settings.open = function () {
	view.settings.init();
};

settings.createLogin = function () {
	/**
  * @param {XMLHttpRequest} jqXHR the jQuery XMLHttpRequest object, see {@link https://api.jquery.com/jQuery.ajax/#jqXHR}.
  * @param {Object} params the original JSON parameters of the request
  * @param {?LycheeException} lycheeException the Lychee exception
  * @returns {boolean}
  */
	var errorHandler = function errorHandler(jqXHR, params, lycheeException) {
		var htmlBody = "<p>" + lychee.locale["ERROR_LOGIN"] + "</p>";
		htmlBody += lycheeException ? "<p>" + lycheeException.message + "</p>" : "";
		basicModal.show({
			body: htmlBody,
			buttons: {
				action: {
					title: lychee.locale["RETRY"],
					fn: function fn() {
						return settings.createLogin();
					}
				}
			}
		});
		return true;
	};

	/**
  * @typedef SetLoginDialogResult
  *
  * @property {string} username
  * @property {string} password
  * @property {string} confirm
  */

	/**
  * @param {SetLoginDialogResult} data
  * @returns {void}
  */
	var action = function action(data) {
		var username = data.username;
		var password = data.password;
		var confirm = data.confirm;

		if (!username.trim()) {
			basicModal.error("username");
			return;
		}

		if (!password.trim()) {
			basicModal.error("password");
			return;
		}

		if (password !== confirm) {
			basicModal.error("confirm");
			return;
		}

		basicModal.close();

		var params = {
			username: username,
			password: password
		};

		api.post("Settings::setLogin", params, null, null, errorHandler);
	};

	var msg = "\n\t\t<p>\n\t\t\t" + lychee.locale["LOGIN_TITLE"] + "\n\t\t\t<input name='username' class='text' type='text' placeholder='" + lychee.locale["LOGIN_USERNAME"] + "' value=''>\n\t\t\t<input name='password' class='text' type='password' placeholder='" + lychee.locale["LOGIN_PASSWORD"] + "' value=''>\n\t\t\t<input name='confirm' class='text' type='password' placeholder='" + lychee.locale["LOGIN_PASSWORD_CONFIRM"] + "' value=''>\n\t\t</p>";

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

/**
 * A dictionary of (name,value)-pairs of the form.
 *
 * @typedef SettingsFormData
 *
 * @type {Object.<string, (string|number|Array)>}
 */

/**
 * From https://github.com/electerious/basicModal/blob/master/src/scripts/main.js
 *
 * @param {string} formSelector
 * @returns {SettingsFormData}
 */
settings.getValues = function (formSelector) {
	var values = {};

	/** @type {?NodeListOf<HTMLInputElement>} */
	var inputElements = document.querySelectorAll(formSelector + " input[name]");

	// Get value from all inputs
	inputElements.forEach(function (inputElement) {
		switch (inputElement.type) {
			case "checkbox":
			case "radio":
				values[inputElement.name] = inputElement.checked;
				break;
			case "number":
			case "range":
				values[inputElement.name] = parseInt(inputElement.value, 10);
				break;
			case "file":
				values[inputElement.name] = inputElement.files;
				break;
			default:
				switch (inputElement.getAttribute("inputmode")) {
					case "numeric":
						values[inputElement.name] = parseInt(inputElement.value, 10);
						break;
					case "decimal":
						values[inputElement.name] = parseFloat(inputElement.value);
						break;
					default:
						values[inputElement.name] = inputElement.value;
				}
		}
	});

	/** @type {?NodeListOf<HTMLSelectElement>} */
	var selectElements = document.querySelectorAll(formSelector + " select[name]");

	// Get name of selected option from all selects
	selectElements.forEach(function (selectElement) {
		values[selectElement.name] = selectElement.selectedIndex !== -1 ? selectElement.options[selectElement.selectedIndex].value : null;
	});

	return values;
};

/**
 * @callback SettingClickCB
 *
 * @param {SettingsFormData} formData
 * @returns {void}
 */

/**
 * From https://github.com/electerious/basicModal/blob/master/src/scripts/main.js.
 *
 * @param {string} inputSelector
 * @param {string} formSelector
 * @param {SettingClickCB} settingClickCB
 */
settings.bind = function (inputSelector, formSelector, settingClickCB) {
	$(inputSelector).on("click", function () {
		settingClickCB(settings.getValues(formSelector));
	});
};

/**
 * @param {SettingsFormData} params
 * @returns {void}
 */
settings.changeLogin = function (params) {
	if (params.username.length < 1) {
		loadingBar.show("error", "new username cannot be empty.");
		$("input[name=username]").addClass("error");
		return;
	} else {
		$("input[name=username]").removeClass("error");
	}

	if (params.password.length < 1) {
		loadingBar.show("error", "new password cannot be empty.");
		$("input[name=password]").addClass("error");
		return;
	} else {
		$("input[name=password]").removeClass("error");
	}

	if (params.password !== params.confirm) {
		loadingBar.show("error", "new password does not match.");
		$("input[name=confirm]").addClass("error");
		return;
	} else {
		$("input[name=confirm]").removeClass("error");
	}

	api.post("Settings::setLogin", params, function () {
		$("input[name]").removeClass("error");
		loadingBar.show("success", lychee.locale["SETTINGS_SUCCESS_LOGIN"]);
		view.settings.content.clearLogin();
	});
};

/**
 * @param {SettingsFormData} params
 * @returns {void}
 */
settings.changeSorting = function (params) {
	api.post("Settings::setSorting", params, function () {
		lychee.sorting_albums.column = params["sorting_albums_column"];
		lychee.sorting_albums.order = params["sorting_albums_order"];
		lychee.sorting_photos.column = params["sorting_photos_column"];
		lychee.sorting_photos.order = params["sorting_photos_order"];
		albums.refresh();
		loadingBar.show("success", lychee.locale["SETTINGS_SUCCESS_SORT"]);
	});
};

/**
 * @param {SettingsFormData} params
 * @returns {void}
 */
settings.changeDropboxKey = function (params) {
	// if params.key == "" key is cleared
	api.post("Settings::setDropboxKey", params, function () {
		lychee.dropboxKey = params.key;
		// if (callback) lychee.loadDropbox(callback)
		loadingBar.show("success", lychee.locale["SETTINGS_SUCCESS_DROPBOX"]);
	});
};

/**
 * @param {SettingsFormData} params
 * @returns {void}
 */
settings.changeLang = function (params) {
	api.post("Settings::setLang", params, function () {
		loadingBar.show("success", lychee.locale["SETTINGS_SUCCESS_LANG"]);
		lychee.init();
	});
};

/**
 * @param {SettingsFormData} params
 * @returns {void}
 */
settings.setDefaultLicense = function (params) {
	api.post("Settings::setDefaultLicense", params, function () {
		lychee.default_license = params.license;
		loadingBar.show("success", lychee.locale["SETTINGS_SUCCESS_LICENSE"]);
	});
};

/**
 * @param {SettingsFormData} params
 * @returns {void}
 */
settings.setLayout = function (params) {
	api.post("Settings::setLayout", params, function () {
		lychee.layout = params.layout;
		loadingBar.show("success", lychee.locale["SETTINGS_SUCCESS_LAYOUT"]);
	});
};

/**
 * @param {SettingsFormData} params
 * @returns {void}
 */
settings.changePublicSearch = function (params) {
	api.post("Settings::setPublicSearch", params, function () {
		loadingBar.show("success", lychee.locale["SETTINGS_SUCCESS_PUBLIC_SEARCH"]);
		lychee.public_search = params.public_search;
	});
};

/**
 * @param {SettingsFormData} params
 * @returns {void}
 */
settings.setOverlayType = function (params) {
	api.post("Settings::setOverlayType", params, function () {
		loadingBar.show("success", lychee.locale["SETTINGS_SUCCESS_IMAGE_OVERLAY"]);
		lychee.image_overlay_type = params.image_overlay_type;
		lychee.image_overlay_type_default = params.image_overlay_type;
	});
};

/**
 * @param {SettingsFormData} params
 * @returns {void}
 */
settings.changeMapDisplay = function (params) {
	api.post("Settings::setMapDisplay", params, function () {
		loadingBar.show("success", lychee.locale["SETTINGS_SUCCESS_MAP_DISPLAY"]);
		lychee.map_display = params.map_display;
		// Map functionality is disabled
		// -> map for public albums also needs to be disabled
		if (!lychee.map_display && lychee.map_display_public) {
			$("#MapDisplayPublic").click();
		}
	});
};

/**
 * @param {SettingsFormData} params
 * @returns {void}
 */
settings.changeMapDisplayPublic = function (params) {
	api.post("Settings::setMapDisplayPublic", params, function () {
		loadingBar.show("success", lychee.locale["SETTINGS_SUCCESS_MAP_DISPLAY_PUBLIC"]);
		lychee.map_display_public = params.map_display_public;
		// If public map functionality is enabled, but map in general is disabled
		// General map functionality needs to be enabled
		if (lychee.map_display_public && !lychee.map_display) {
			$("#MapDisplay").click();
		}
	});
};

/**
 * @param {SettingsFormData} params
 * @returns {void}
 */
settings.setMapProvider = function (params) {
	api.post("Settings::setMapProvider", params, function () {
		loadingBar.show("success", lychee.locale["SETTINGS_SUCCESS_MAP_PROVIDER"]);
		lychee.map_provider = params.map_provider;
	});
};

/**
 * @param {SettingsFormData} params
 * @returns {void}
 */
settings.changeMapIncludeSubAlbums = function (params) {
	api.post("Settings::setMapIncludeSubAlbums", params, function () {
		loadingBar.show("success", lychee.locale["SETTINGS_SUCCESS_MAP_DISPLAY"]);
		lychee.map_include_subalbums = params.map_include_subalbums;
	});
};

/**
 * @param {SettingsFormData} params
 * @returns {void}
 */
settings.changeLocationDecoding = function (params) {
	api.post("Settings::setLocationDecoding", params, function () {
		loadingBar.show("success", lychee.locale["SETTINGS_SUCCESS_MAP_DISPLAY"]);
		lychee.location_decoding = params.location_decoding;
	});
};

/**
 * @param {SettingsFormData} params
 * @returns {void}
 */
settings.changeNSFWVisible = function (params) {
	api.post("Settings::setNSFWVisible", params, function () {
		loadingBar.show("success", lychee.locale["SETTINGS_SUCCESS_NSFW_VISIBLE"]);
		lychee.nsfw_visible = params.nsfw_visible;
		lychee.nsfw_visible_saved = lychee.nsfw_visible;
	});
};

//TODO : later
// lychee.nsfw_blur = (data.config.nsfw_blur && data.config.nsfw_blur === '1') || false;
// lychee.nsfw_warning = (data.config.nsfw_warning && data.config.nsfw_warning === '1') || false;
// lychee.nsfw_warning_text = data.config.nsfw_warning_text || '<b>Sensitive content</b><br><p>This album contains sensitive content which some people may find offensive or disturbing.</p>';

/**
 * @param {SettingsFormData} params
 * @returns {void}
 */
settings.changeLocationShow = function (params) {
	api.post("Settings::setLocationShow", params, function () {
		loadingBar.show("success", lychee.locale["SETTINGS_SUCCESS_MAP_DISPLAY"]);
		lychee.location_show = params.location_show;
		// Don't show location
		// -> location for public albums also needs to be disabled
		if (!lychee.location_show && lychee.location_show_public) {
			$("#LocationShowPublic").click();
		}
	});
};

/**
 * @param {SettingsFormData} params
 * @returns {void}
 */
settings.changeLocationShowPublic = function (params) {
	api.post("Settings::setLocationShowPublic", params, function () {
		loadingBar.show("success", lychee.locale["SETTINGS_SUCCESS_MAP_DISPLAY"]);
		lychee.location_show_public = params.location_show_public;
		// If public map functionality is enabled, but map in general is disabled
		// General map functionality needs to be enabled
		if (lychee.location_show_public && !lychee.location_show) {
			$("#LocationShow").click();
		}
	});
};

/**
 * @param {SettingsFormData} params
 * @returns {void}
 */
settings.changeNewPhotosNotification = function (params) {
	api.post("Settings::setNewPhotosNotification", params, function () {
		loadingBar.show("success", lychee.locale["SETTINGS_SUCCESS_NEW_PHOTOS_NOTIFICATION"]);
		lychee.new_photos_notification = params.new_photos_notification;
	});
};

/**
 * @returns {void}
 */
settings.changeCSS = function () {
	var params = {
		css: $("#css").val()
	};
	api.post("Settings::setCSS", params, function () {
		lychee.css = params.css;
		loadingBar.show("success", lychee.locale["SETTINGS_SUCCESS_CSS"]);
	});
};

/**
 * @param {SettingsFormData} params
 * @returns {void}
 */
settings.save = function (params) {
	api.post("Settings::saveAll", params, function () {
		loadingBar.show("success", lychee.locale["SETTINGS_SUCCESS_UPDATE"]);
		view.full_settings.init();
		// re-read settings
		lychee.init(false);
	});
};

/**
 * @param {jQuery.Event} e
 * @returns {void}
 */
settings.save_enter = function (e) {
	// We only handle "enter"
	if (e.which !== 13) return;

	// show confirmation box
	$(":focus").blur();

	var action = {};
	var cancel = {};

	action.title = lychee.locale["ENTER"];
	action.msg = lychee.html(_templateObject68, lychee.locale["SAVE_RISK"]);

	cancel.title = lychee.locale["CANCEL"];

	action.fn = function () {
		settings.save(settings.getValues("#fullSettings"));
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
};

var sharing = {
	/** @type {?SharingInfo} */
	json: null
};

/**
 * @returns {void}
 */
sharing.add = function () {
	var params = {
		/** @type {string[]} */
		albumIDs: [],
		/** @type {number[]} */
		userIDs: []
	};

	$("#albums_list_to option").each(function () {
		params.albumIDs.push(this.value);
	});

	$("#user_list_to option").each(function () {
		params.userIDs.push(Number.parseInt(this.value, 10));
	});

	if (params.albumIDs.length === 0) {
		loadingBar.show("error", "Select an album to share!");
		return;
	}
	if (params.userIDs.length === 0) {
		loadingBar.show("error", "Select a user to share with!");
		return;
	}

	api.post("Sharing::add", params, function () {
		loadingBar.show("success", "Sharing updated!");
		sharing.list(); // reload user list
	});
};

/**
 * @returns {void}
 */
sharing.delete = function () {
	var params = {
		/** @type {number[]} */
		shareIDs: []
	};

	$('input[name="remove_id"]:checked').each(function () {
		params.shareIDs.push(Number.parseInt(this.value, 10));
	});

	if (params.shareIDs.length === 0) {
		loadingBar.show("error", "Select a sharing to remove!");
		return;
	}
	api.post("Sharing::delete", params, function () {
		loadingBar.show("success", "Sharing removed!");
		sharing.list(); // reload user list
	});
};

/**
 * @returns {void}
 */
sharing.list = function () {
	api.post("Sharing::list", {},
	/** @param {SharingInfo} data */
	function (data) {
		sharing.json = data;
		view.sharing.init();
	});
};

/**
 * @description This module takes care of the sidebar.
 */

/**
 * @namespace
 */
var _sidebar = {
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
_sidebar.dom = function (selector) {
	if (selector == null || selector === "") return _sidebar._dom;
	return _sidebar._dom.find(selector);
};

/**
 * This function should be called after building and appending
 * the sidebars content to the DOM.
 * This function can be called multiple times, therefore
 * event handlers should be removed before binding a new one.
 *
 * @returns {void}
 */
_sidebar.bind = function () {
	var eventName = lychee.getEventName();

	_sidebar.dom("#edit_title").off(eventName).on(eventName, function () {
		if (visible.photo()) _photo3.setTitle([_photo3.getID()]);else if (visible.album()) album.setTitle([album.getID()]);
	});

	_sidebar.dom("#edit_description").off(eventName).on(eventName, function () {
		if (visible.photo()) _photo3.setDescription(_photo3.getID());else if (visible.album()) album.setDescription(album.getID());
	});

	_sidebar.dom("#edit_showtags").off(eventName).on(eventName, function () {
		album.setShowTags(album.getID());
	});

	_sidebar.dom("#edit_tags").off(eventName).on(eventName, function () {
		_photo3.editTags([_photo3.getID()]);
	});

	_sidebar.dom("#tags .tag").off(eventName).on(eventName, function () {
		_sidebar.triggerSearch($(this).text());
	});

	_sidebar.dom("#tags .tag span").off(eventName).on(eventName, function () {
		_photo3.deleteTag(_photo3.getID(), $(this).data("index"));
	});

	_sidebar.dom("#edit_license").off(eventName).on(eventName, function () {
		if (visible.photo()) _photo3.setLicense(_photo3.getID());else if (visible.album()) album.setLicense(album.getID());
	});

	_sidebar.dom("#edit_sorting").off(eventName).on(eventName, function () {
		album.setSorting(album.getID());
	});

	_sidebar.dom(".attr_location").off(eventName).on(eventName, function () {
		_sidebar.triggerSearch($(this).text());
	});
};

/**
 * @param {string} search_string
 * @returns {void}
 */
_sidebar.triggerSearch = function (search_string) {
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
_sidebar.keepSidebarVisible = function () {
	var v = sessionStorage.getItem("keepSidebarVisible");
	return v !== null ? v === "true" : false;
};

/**
 * @param {boolean} is_user_initiated - indicates if the user requested to
 *                                      toggle and hence the new state shall
 *                                      be saved in session storage
 * @returns {void}
 */
_sidebar.toggle = function (is_user_initiated) {
	if (visible.sidebar() || visible.sidebarbutton()) {
		header.dom(".button--info").toggleClass("active");
		lychee.content.toggleClass("content--sidebar");
		lychee.imageview.toggleClass("image--sidebar");
		if (typeof view !== "undefined") view.album.content.justify(album.json ? album.json.photos : []);
		_sidebar.dom().toggleClass("active");
		_photo3.updateSizeLivePhotoDuringAnimation();

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
_sidebar.setSelectable = function () {
	var selectable = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : true;

	if (selectable) _sidebar.dom().removeClass("notSelectable");else _sidebar.dom().addClass("notSelectable");
};

/**
 * @param {string} attr - selector of attribute without the `attr_` prefix
 * @param {?string} value - a `null` value is replaced by the empty string
 * @param {boolean} [dangerouslySetInnerHTML=false]
 *
 * @returns {void}
 */
_sidebar.changeAttr = function (attr) {
	var value = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : "";
	var dangerouslySetInnerHTML = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : false;

	if (!attr) return;
	if (!value) value = "";

	// TODO: Don't use our home-brewed `escapeHTML` method; use `jQuery#text` instead
	// Escape value
	if (!dangerouslySetInnerHTML) value = lychee.escapeHTML(value);

	_sidebar.dom(".attr_" + attr).html(value);
};

/**
 * @param {string} attr - selector of attribute without the `attr_` prefix
 * @returns {void}
 */
_sidebar.hideAttr = function (attr) {
	_sidebar.dom(".attr_" + attr).closest("tr").hide();
};

/**
 * Converts integer seconds into "hours:minutes:seconds".
 *
 * TODO: Consider to make this method part of `lychee.locale`.
 *
 * @param {(number|string)} d
 * @returns {string}
 */
_sidebar.secondsToHMS = function (d) {
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
_sidebar.createStructure.photo = function (data) {
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
		type: _sidebar.types.DEFAULT,
		rows: [{ title: lychee.locale["PHOTO_TITLE"], kind: "title", value: data.title, editable: editable }, { title: lychee.locale["PHOTO_UPLOADED"], kind: "uploaded", value: lychee.locale.printDateTime(data.created_at) }, { title: lychee.locale["PHOTO_DESCRIPTION"], kind: "description", value: data.description ? data.description : "", editable: editable }]
	};

	structure.image = {
		title: lychee.locale[isVideo ? "PHOTO_VIDEO" : "PHOTO_IMAGE"],
		type: _sidebar.types.DEFAULT,
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
			structure.image.rows.push({ title: lychee.locale["PHOTO_DURATION"], kind: "duration", value: _sidebar.secondsToHMS(data.aperture) });
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
		type: _sidebar.types.TAGS,
		value: build.tags(data.tags),
		editable: editable
	};

	// Only create EXIF section when EXIF data available
	if (hasExif) {
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
		rows: [{ title: lychee.locale["PHOTO_SHR_PLUBLIC"], kind: "public", value: isPublic }]
	};

	structure.license = {
		title: lychee.locale["PHOTO_REUSE"],
		type: _sidebar.types.DEFAULT,
		rows: [{ title: lychee.locale["PHOTO_LICENSE"], kind: "license", value: license, editable: editable }]
	};

	if (hasLocation) {
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
_sidebar.createStructure.album = function (data) {
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
		type: _sidebar.types.DEFAULT,
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

	if (data.photos && sorting !== "") {
		structure.album.rows.push({ title: lychee.locale["ALBUM_ORDERING"], kind: "sorting", value: sorting, editable: editable });
	}

	structure.share = {
		title: lychee.locale["ALBUM_SHARING"],
		type: _sidebar.types.DEFAULT,
		rows: [{ title: lychee.locale["ALBUM_PUBLIC"], kind: "public", value: isPublic }, { title: lychee.locale["ALBUM_HIDDEN"], kind: "hidden", value: requiresLink }, { title: lychee.locale["ALBUM_DOWNLOADABLE"], kind: "downloadable", value: isDownloadable }, { title: lychee.locale["ALBUM_SHARE_BUTTON_VISIBLE"], kind: "share_button_visible", value: isShareButtonVisible }, { title: lychee.locale["ALBUM_PASSWORD"], kind: "password", value: hasPassword }]
	};

	if (data.owner_name) {
		structure.share.rows.push({ title: lychee.locale["ALBUM_OWNER"], kind: "owner", value: data.owner_name });
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

/**
 * @param {Section[]} structure
 * @returns {boolean} - true if the passed structure contains a "location" section
 */
_sidebar.has_location = function (structure) {
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
_sidebar.render = function (structure) {
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
							prev += lychee.html(_templateObject69, row.kind);
						}
						return prev + lychee.html(_templateObject70, row.kind, cur);
					}, "");
				} else {
					value = lychee.html(_templateObject71, row.kind, value);
				}

				// Add edit-icon to the value when editable
				if (row.editable === true) value += " " + build.editIcon("edit_" + row.kind);

				_html += lychee.html(_templateObject72, row.title, value);
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

		_html += lychee.html(_templateObject73, section.title, section.title.toLowerCase(), section.value, editable);

		return _html;
	};

	var html = "";

	structure.forEach(function (section) {
		if (section.type === _sidebar.types.DEFAULT) html += renderDefault(section);else if (section.type === _sidebar.types.TAGS) html += renderTags(section);
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
 * @description Swipes and moves an object.
 */

var swipe = {
	/** @type {?jQuery} */
	obj: null,
	/** @type {number} */
	offsetX: 0,
	/** @type {number} */
	offsetY: 0,
	/** @type {boolean} */
	preventNextHeaderToggle: false
};

/**
 * @param {jQuery} obj
 * @returns {void}
 */
swipe.start = function (obj) {
	swipe.obj = obj;
};

/**
 * @param {jQuery.Event} e
 * @returns {void}
 */
swipe.move = function (e) {
	if (swipe.obj === null) {
		return;
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
};

/**
 * @callback SwipeStoppedCB
 *
 * Find a better name for that, but I have no idea what this callback is
 * supposed to do.
 *
 * @param {boolean} animate
 * @returns {void}
 */

/**
 * @param {{x: number, y: number, direction: number, distance: number, angle: number, speed: number, }} e
 * @param {SwipeStoppedCB} left
 * @param {SwipeStoppedCB} right
 * @returns {void}
 */
swipe.stop = function (e, left, right) {
	// Only execute once
	if (swipe.obj === null) {
		return;
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

var u2f = {
	/** @type {?WebAuthnCredential[]} */
	json: null
};

/**
 * @returns {boolean}
 */
u2f.is_available = function () {
	if (!window.isSecureContext && window.location.hostname !== "localhost" && window.location.hostname !== "127.0.0.1") {
		var msg = lychee.html(_templateObject74, lychee.locale["U2F_NOT_SECURE"]);

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

/**
 * @returns {void}
 */
u2f.login = function () {
	if (!u2f.is_available()) {
		return;
	}

	new Larapass({
		login: "/api/WebAuthn::login",
		loginOptions: "/api/WebAuthn::login/gen"
	}).login({
		user_id: 0 // for now it is only available to Admin user via a secret key shortcut.
	}).then(function () {
		loadingBar.show("success", lychee.locale["U2F_AUTHENTIFICATION_SUCCESS"]);
		window.location.reload();
	}).catch(function () {
		return loadingBar.show("error", "Something went wrong!");
	});
};

/**
 * @returns {void}
 */
u2f.register = function () {
	if (!u2f.is_available()) {
		return;
	}

	var larapass = new Larapass({
		register: "/api/WebAuthn::register",
		registerOptions: "/api/WebAuthn::register/gen"
	});
	if (Larapass.supportsWebAuthn()) {
		larapass.register().then(function () {
			loadingBar.show("success", lychee.locale["U2F_REGISTRATION_SUCCESS"]);
			u2f.list(); // reload credential list
		}).catch(function () {
			return loadingBar.show("error", "Something went wrong!");
		});
	} else {
		loadingBar.show("error", lychee.locale["U2F_NOT_SUPPORTED"]);
	}
};

/**
 * @param {{id: string}} params - ID of WebAuthn credential
 */
u2f.delete = function (params) {
	api.post("WebAuthn::delete", params, function () {
		loadingBar.show("success", lychee.locale["U2F_CREDENTIALS_DELETED"]);
		u2f.list(); // reload credential list
	});
};

u2f.list = function () {
	api.post("WebAuthn::list", {},
	/** @param {WebAuthnCredential[]} data*/
	function (data) {
		u2f.json = data;
		view.u2f.init();
	});
};

/**
 * @description Takes care of every action an album can handle and execute.
 */

var upload = {};

var choiceDeleteSelector = '.basicModal .choice input[name="delete_imported"]';
var choiceSymlinkSelector = '.basicModal .choice input[name="import_via_symlink"]';
var choiceDuplicateSelector = '.basicModal .choice input[name="skip_duplicates"]';
var choiceResyncSelector = '.basicModal .choice input[name="resync_metadata"]';
var actionSelector = ".basicModal #basicModal__action";
var cancelSelector = ".basicModal #basicModal__cancel";
var firstRowStatusSelector = ".basicModal .rows .row .status";
var firstRowNoticeSelector = ".basicModal .rows .row p.notice";

var nRowStatusSelector = function nRowStatusSelector(row) {
	return ".basicModal .rows .row:nth-child(" + row + ") .status";
};

var showCloseButton = function showCloseButton() {
	$(actionSelector).show();
	// re-activate cancel button to close modal panel if needed
	$(cancelSelector).removeClass("basicModal__button--active").hide();
};

/**
 * @param {string} title
 * @param {(FileList|File[]|DropboxFile[]|{name: string}[])} files
 * @param {ModalDialogReadyCB} run_callback
 * @param {?ModalDialogButtonCB} cancel_callback
 */
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
	/**
  * @param {(FileList|File[])} files
  */
	local: function local(files) {
		if (files.length <= 0) return;

		var albumID = album.getID();
		var hasErrorOccurred = false;
		var hasWarningOccurred = false;
		/**
   * The number of requests which are "on the fly", i.e. for which a
   * response has not yet completely been received.
   *
   * Note, that Lychee supports a restricted kind of "parallelism"
   * which is limited by the configuration option
   * `lychee.upload_processing_limit`:
   * While always only a single file is uploaded at once, upload of the
   * next file already starts after transmission of the previous file
   * has been finished, the response to the previous file might still be
   * outstanding as the uploaded file is processed at the server-side.
   *
   * @type {number}
   */
		var outstandingResponsesCount = 0;
		/**
   * The latest (aka highest) index of a file which is being or has
   * been uploaded to the server.
   *
   * @type {number}
   */
		var latestFileIdx = 0;
		/**
   * Semaphore whether a file is currently being uploaded.
   *
   * This is used as a semaphore to serialize the upload transmissions
   * between several instances of the method {@link process}.
   *
   * @type {boolean}
   */
		var isUploadRunning = false;
		/**
   * Semaphore whether a further upload shall be cancelled on the next
   * occasion.
   *
   * @type {boolean}
   */
		var shallCancelUpload = false;

		/**
   * This callback is invoked when the last file has been processed.
   *
   * It closes the modal dialog or shows the close button and
   * reloads the album.
   */
		var finish = function finish() {
			window.onbeforeunload = null;

			$("#upload_files").val("");

			if (!hasErrorOccurred && !hasWarningOccurred) {
				// Success
				basicModal.close();
				upload.notify(lychee.locale["UPLOAD_COMPLETE"]);
			} else if (!hasErrorOccurred && hasWarningOccurred) {
				// Warning
				showCloseButton();
				upload.notify(lychee.locale["UPLOAD_COMPLETE"]);
			} else {
				// Error
				showCloseButton();
				if (shallCancelUpload) {
					$(".basicModal .rows .row:nth-child(n+" + (latestFileIdx + 2).toString() + ") .status").html(lychee.locale["UPLOAD_CANCELLED"]).addClass("warning");
				}
				upload.notify(lychee.locale["UPLOAD_COMPLETE"], lychee.locale["UPLOAD_COMPLETE_FAILED"]);
			}

			album.reload();
		};

		/**
   * Processes the upload and response for a single file.
   *
   * Note that up to `lychee.upload_processing_limit` "instances" of
   * this method can be "alive" simultaneously.
   * The parameter `fileIdx` is limited by `latestFileIdx`.
   *
   * @param {number} fileIdx the index of the file being processed
   */
		var process = function process(fileIdx) {
			/**
    * The upload progress of the file with index `fileIdx` so far.
    *
    * @type {number}
    */
			var uploadProgress = 0;

			/**
    * A function to be called when the upload has transmitted more data.
    *
    * This method updates the upload percentage counter in the dialog.
    *
    * If the progress equals 100%, i.e. if the upload has been
    * completed, this method
    *
    *  - unsets the semaphore for a running upload,
    *  - scrolls the dialog such that the file with index `fileIdx`
    *    becomes visible, and
    *  - changes the status text to "Upload processing".
    *
    * After the current upload has reached 100%, this method starts a
    * new upload, if
    *
    *  - there are more files to be uploaded,
    *  - no other upload is currently running, and
    *  - the number of outstanding responses does not exceed the
    *    processing limit of Lychee.
    *
    * @param {ProgressEvent} e
    * @this XMLHttpRequest
    */
			var onUploadProgress = function onUploadProgress(e) {
				if (e.lengthComputable !== true) return;

				// Calculate progress
				var progress = e.loaded / e.total * 100 | 0;

				// Set progress when progress has changed
				if (progress > uploadProgress) {
					uploadProgress = progress;
					/** @type {?jQuery} */
					var jqStatusMsg = $(nRowStatusSelector(fileIdx + 1));
					jqStatusMsg.html(uploadProgress + "%");

					if (progress >= 100) {
						jqStatusMsg.html(lychee.locale["UPLOAD_PROCESSING"]);
						isUploadRunning = false;
						var scrollPos = 0;
						if (fileIdx + 1 > 4) scrollPos = (fileIdx + 1 - 4) * 40;
						$(".basicModal .rows").scrollTop(scrollPos);

						// Start a new upload, if there are still pending
						// files
						if (!isUploadRunning && !shallCancelUpload && (outstandingResponsesCount < lychee.upload_processing_limit || lychee.upload_processing_limit === 0) && latestFileIdx + 1 < files.length) {
							latestFileIdx++;
							process(latestFileIdx);
						}
					}
				}
			};

			/**
    * A function to be called when a response has been received.
    *
    * This method updates the status of the affected file.
    *
    * @this XMLHttpRequest
    */
			var onLoaded = function onLoaded() {
				/** @type {?LycheeException} */
				var lycheeException = this.status >= 400 ? this.response : null;
				var errorText = "";
				var statusText = void 0;
				var statusClass = void 0;

				switch (this.status) {
					case 200:
					case 201:
					case 204:
						statusText = lychee.locale["UPLOAD_FINISHED"];
						statusClass = "success";
						break;
					case 409:
						statusText = lychee.locale["UPLOAD_SKIPPED"];
						errorText = lycheeException ? lycheeException.message : lychee.locale["UPLOAD_ERROR_UNKNOWN"];
						hasWarningOccurred = true;
						statusClass = "warning";
						break;
					case 413:
						statusText = lychee.locale["UPLOAD_FAILED"];
						errorText = lychee.locale["UPLOAD_ERROR_POSTSIZE"];
						hasErrorOccurred = true;
						statusClass = "error";
						break;
					default:
						statusText = lychee.locale["UPLOAD_FAILED"];
						errorText = lycheeException ? lycheeException.message : lychee.locale["UPLOAD_ERROR_UNKNOWN"];
						hasErrorOccurred = true;
						statusClass = "error";
						break;
				}

				$(nRowStatusSelector(fileIdx + 1)).html(statusText).addClass(statusClass);

				if (statusClass === "error") {
					api.onError(this, { albumID: albumID }, lycheeException);
				}

				if (errorText !== "") {
					$(".basicModal .rows .row:nth-child(" + (fileIdx + 1) + ") p.notice").html(errorText).show();
				}
			};

			/**
    * A function to be called when any response has been received
    * (after specific success and error callbacks have been executed)
    *
    * This method starts a new upload, if
    *
    *  - there are more files to be uploaded,
    *  - no other upload is currently running, and
    *  - the number of outstanding responses does not exceed the
    *    processing limit of Lychee.
    *
    * This method calls {@link finish}, if
    *
    *  - the process shall be cancelled or no more files are left for processing,
    *  - no upload is running anymore, and
    *  - no response is outstanding
    *
    * @this XMLHttpRequest
    */
			var onComplete = function onComplete() {
				outstandingResponsesCount--;

				if (!isUploadRunning && !shallCancelUpload && (outstandingResponsesCount < lychee.upload_processing_limit || lychee.upload_processing_limit === 0) && latestFileIdx + 1 < files.length) {
					latestFileIdx++;
					process(latestFileIdx);
				}

				if ((shallCancelUpload || latestFileIdx + 1 === files.length) && !isUploadRunning && outstandingResponsesCount === 0) {
					finish();
				}
			};

			var formData = new FormData();
			var xhr = new XMLHttpRequest();

			// For form data, a `null` value is indicated by the empty
			// string `""`. Form data falsely converts the value `null` to the
			// literal string `"null"`.
			formData.append("albumID", albumID ? albumID : "");
			formData.append("file", files[fileIdx]);

			// We must not use the `onload` event of the `XMLHttpRequestUpload`
			// object.
			// Instead, we only use the `onprogress` event and check within
			// the event handler if the progress counter reached 100%.
			// The reason is that `upload.onload` is not immediately called
			// after the browser has completed the upload (as the name
			// suggests), but only after the browser has already received the
			// response header.
			// For our purposes this is too late, as this way we would never
			// show the "processing" status, during which the backend has
			// received the upload, but has not yet started to send a response.
			xhr.upload.onprogress = onUploadProgress;
			xhr.onload = onLoaded;
			xhr.onloadend = onComplete;
			xhr.responseType = "json";
			xhr.open("POST", "api/Photo::add");
			xhr.setRequestHeader("X-XSRF-TOKEN", csrf.getCSRFCookieValue());
			xhr.setRequestHeader("Accept", "application/json");

			outstandingResponsesCount++;
			isUploadRunning = true;
			xhr.send(formData);
		};

		window.onbeforeunload = function () {
			return lychee.locale["UPLOAD_IN_PROGRESS"];
		};

		upload.show(lychee.locale["UPLOAD_UPLOADING"], files, function () {
			// Upload first file
			$(cancelSelector).show();
			process(0);
		}, function () {
			shallCancelUpload = true;
			hasErrorOccurred = true;
		});
	},

	/**
  * @param {string} preselectedUrl
  */
	url: function url() {
		var preselectedUrl = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : "";

		var albumID = album.getID();

		/**
   * @typedef UrlDialogResult
   * @property {string} url
   */

		/** @param {UrlDialogResult} data */
		var action = function action(data) {
			var runImport = function runImport() {
				$(firstRowStatusSelector).html(lychee.locale["UPLOAD_IMPORTING"]);

				var successHandler = function successHandler() {
					// Same code as in import.dropbox()
					basicModal.close();
					upload.notify(lychee.locale["UPLOAD_IMPORT_COMPLETE"]);
					album.reload();
				};

				/**
     * @param {XMLHttpRequest} jqXHR
     * @param {Object} params
     * @param {?LycheeException} lycheeException
     * @returns {boolean}
     */
				var errorHandler = function errorHandler(jqXHR, params, lycheeException) {
					// Same code as in import.dropbox()
					var errorText = void 0;
					var statusText = void 0;
					var statusClass = void 0;

					switch (jqXHR.status) {
						case 409:
							statusText = lychee.locale["UPLOAD_SKIPPED"];
							errorText = lycheeException ? lycheeException.message : lychee.locale["UPLOAD_IMPORT_WARN_ERR"];
							statusClass = "warning";
							break;
						default:
							statusText = lychee.locale["UPLOAD_FAILED"];
							errorText = lycheeException ? lycheeException.message : lychee.locale["UPLOAD_IMPORT_WARN_ERR"];
							statusClass = "error";
							break;
					}

					$(firstRowNoticeSelector).html(errorText).show();
					$(firstRowStatusSelector).html(statusText).addClass(statusClass);
					// Show close button
					$(".basicModal #basicModal__action.hidden").show();
					upload.notify(lychee.locale["UPLOAD_IMPORT_WARN_ERR"]);
					album.reload();
					return true;
				};

				// In theory, the backend is prepared to download a list of
				// URLs (note that `data.url`) is wrapped into an array.
				// However, we need a better dialog which allows input of a
				// list of URLs.
				// Another problem which already exists even for a single
				// URL concerns timeouts.
				// Below, we transmit a single HTTP request which must respond
				// within about 500ms either with a success or error response.
				// Otherwise, JS assumes that the AJAX request just timed out.
				// But the server, first need to download the image from the
				// specified URL, process it and then generate a HTTP response.
				// Probably, it would be much better to use a streamed
				// response here like we already have for imports from the
				// local server.
				// This way, the server could also report its own progress of
				// downloading the images.
				// TODO: Use a streamed response (see description above).
				api.post("Import::url", {
					urls: [data.url],
					albumID: albumID
				}, successHandler, null, errorHandler);
			};

			if (data.url && data.url.trim().length > 3) {
				basicModal.close();
				upload.show(lychee.locale["UPLOAD_IMPORTING_URL"], [{ name: data.url }], runImport);
			} else basicModal.error("link");
		};

		basicModal.show({
			body: lychee.html(_templateObject75) + lychee.locale["UPLOAD_IMPORT_INSTR"] + (" <input class='text' name='url' type='text' placeholder='https://' value='" + preselectedUrl + "'></p>"),
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

		var importDialogSetupCB = function importDialogSetupCB() {
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
		};

		/**
   * @typedef ServerImportDialogResult
   * @property {string} path
   * @property {boolean} delete_imported
   * @property {boolean} import_via_symlink
   * @property {boolean} skip_duplicates
   * @property {boolean} resync_metadata
   */

		/** @param {ServerImportDialogResult} data */
		var action = function action(data) {
			if (!data.path.trim()) {
				basicModal.error("path");
				return;
			} else {
				// Consolidate `data` before we close the modal dialog
				// TODO: We should fix the modal dialog to properly return the values of all input fields, incl. check boxes
				data.delete_imported = !!$(choiceDeleteSelector).prop("checked");
				data.import_via_symlink = !!$(choiceSymlinkSelector).prop("checked");
				data.skip_duplicates = !!$(choiceDuplicateSelector).prop("checked");
				data.resync_metadata = !!$(choiceResyncSelector).prop("checked");
				basicModal.close();
			}

			var isUploadCancelled = false;

			var cancelUpload = function cancelUpload() {
				if (!isUploadCancelled) {
					api.post("Import::serverCancel", {}, function () {
						isUploadCancelled = true;
					});
				}
			};

			var runUpload = function runUpload() {
				$(cancelSelector).show();

				// Variables holding state across the invocations of
				// processIncremental().
				var jqRows = $(".basicModal .rows");
				var lastReadIdx = 0;
				var currentPath = null;
				var jqCurrentRow = null; // the jQuery object of the current row
				var encounteredProblems = false;
				var topSkip = 0;

				/**
     * Worker function invoked from both the response progress
     * callback and the completion callback.
     *
     * @param {(ImportProgressReport|ImportEventReport)[]} reports
     */
				var processIncremental = function processIncremental(reports) {
					reports.slice(lastReadIdx).forEach(function (report) {
						if (report.type === "progress") {
							if (currentPath !== report.path) {
								// New directory. Add a new line to the dialog box at the end
								currentPath = report.path;
								jqCurrentRow = $(build.uploadNewFile(currentPath)).appendTo(jqRows);
								topSkip += jqCurrentRow.outerHeight();
							}

							if (report.progress !== 100) {
								$(".status", jqCurrentRow).text("" + report.progress + "%");
							} else {
								// Final status report for this directory.
								$(".status", jqCurrentRow).text(lychee.locale["UPLOAD_FINISHED"]).addClass("success");
							}
						} else if (report.type === "event") {
							var jqEventRow = void 0;
							if (jqCurrentRow) {
								if (currentPath !== report.path) {
									// If we already have a current row (for
									// progress reports) and the event does
									// not refer to that directory, we
									// insert the event row _before_ the
									// current row, so that the progress
									// report stays in sight.
									jqEventRow = $(build.uploadNewFile(report.path || "General")).insertBefore(jqCurrentRow);
									topSkip += jqEventRow.outerHeight();
								} else {
									// The problem is with the directory
									// itself, so alter its existing line.
									jqEventRow = jqCurrentRow;
								}
							} else {
								// If we do not have a current row yet, we
								// simply append it to the list of rows
								// (this might happen if the event occurs
								// before the first progress report)
								jqEventRow = $(build.uploadNewFile(report.path || "General")).appendTo(jqRows);
								topSkip += jqEventRow.outerHeight();
							}

							var severityClass = "";
							var statusText = "";
							var noteText = "";

							switch (report.severity) {
								case "debug":
								case "info":
									break;
								case "notice":
								case "warning":
									severityClass = "warning";
									break;
								case "error":
								case "critical":
								case "emergency":
									severityClass = "error";
									break;
							}

							switch (report.subtype) {
								case "mem_limit":
									statusText = lychee.locale["UPLOAD_WARNING"];
									noteText = lychee.locale["UPLOAD_IMPORT_LOW_MEMORY_EXPL"];
									break;
								case "FileOperationException":
								case "MediaFileOperationException":
									statusText = lychee.locale["UPLOAD_SKIPPED"];
									noteText = lychee.locale["UPLOAD_IMPORT_FAILED"];
									break;
								case "MediaFileUnsupportedException":
									statusText = lychee.locale["UPLOAD_SKIPPED"];
									noteText = lychee.locale["UPLOAD_IMPORT_UNSUPPORTED"];
									break;
								case "InvalidDirectoryException":
									statusText = lychee.locale["UPLOAD_FAILED"];
									noteText = lychee.locale["UPLOAD_IMPORT_NOT_A_DIRECTORY"];
									break;
								case "ReservedDirectoryException":
									statusText = lychee.locale["UPLOAD_FAILED"];
									noteText = lychee.locale["UPLOAD_IMPORT_PATH_RESERVED"];
									break;
								case "PhotoSkippedException":
									statusText = lychee.locale["UPLOAD_SKIPPED"];
									noteText = lychee.locale["UPLOAD_IMPORT_SKIPPED_DUPLICATE"];
									break;
								case "PhotoResyncedException":
									statusText = lychee.locale["UPLOAD_UPDATED"];
									noteText = lychee.locale["UPLOAD_IMPORT_RESYNCED_DUPLICATE"];
									break;
								case "ImportCancelledException":
									statusText = lychee.locale["UPLOAD_CANCELLED"];
									noteText = lychee.locale["UPLOAD_IMPORT_CANCELLED"];
									break;
								default:
									statusText = lychee.locale["UPLOAD_SKIPPED"];
									noteText = report.message;
									break;
							}

							$(".status", jqEventRow).text(statusText).addClass(severityClass);
							$(".notice", jqEventRow).text(noteText).show();

							encounteredProblems = true;
						}
					}); // forEach (resp)
					lastReadIdx = reports.length;
					$(jqRows).scrollTop(topSkip);
				}; // processIncremental

				/**
     * @param {ImportReport[]} reports
     */
				var successHandler = function successHandler(reports) {
					// reports is already JSON-parsed.
					processIncremental(reports);

					upload.notify(lychee.locale["UPLOAD_IMPORT_COMPLETE"], encounteredProblems ? lychee.locale["UPLOAD_COMPLETE_FAILED"] : null);

					album.reload();

					if (encounteredProblems) showCloseButton();else basicModal.close();
				};

				/**
     * @this {XMLHttpRequest}
     */
				var progressHandler = function progressHandler() {
					/** @type {string} */
					var response = this.response;
					/** @type {ImportReport[]} */
					var reports = [];
					// We received a possibly partial response.
					// We must ensure that the last object in the
					// array is complete and terminate the array.
					while (response.length > 2 && reports.length === 0) {
						// Search the last '}', assume that this terminates
						// the last JSON object, cut the string and terminate
						// the array with `]`.
						var fixedResponse = response.substring(0, response.lastIndexOf("}") + 1) + "]";
						try {
							// If the assumption is wrong and the last found
							// '}'  does not terminate the last object, then
							// `JSON.parse` will fail and tell us where the
							// problem occurred.
							reports = JSON.parse(fixedResponse);
						} catch (e) {
							if (e instanceof SyntaxError) {
								var errorPos = e.columnNumber;
								var lastBrace = response.lastIndexOf("}");
								var cutResponse = errorPos < lastBrace ? errorPos : lastBrace;
								response = response.substring(0, cutResponse);
							} else {
								// Something else went wrong
								upload.notify(lychee.locale["UPLOAD_COMPLETE"], lychee.locale["UPLOAD_COMPLETE_FAILED"]);

								album.reload();

								showCloseButton();

								return;
							}
						}
					}
					// The rest of the work is the same as for the full
					// response.
					processIncremental(reports);
				};

				var params = {
					albumID: albumID,
					path: data.path,
					delete_imported: data.delete_imported,
					import_via_symlink: data.import_via_symlink,
					skip_duplicates: data.skip_duplicates,
					resync_metadata: data.resync_metadata
				};

				api.post("Import::server", params, successHandler, progressHandler);
			};

			upload.show(lychee.locale["UPLOAD_IMPORT_SERVER"], [], runUpload, cancelUpload);
		}; // action

		var msg = lychee.html(_templateObject76, lychee.locale["UPLOAD_IMPORT_SERVER_INSTR"], lychee.locale["UPLOAD_ABSOLUTE_PATH"], lychee.location, build.iconic("check"), lychee.locale["UPLOAD_IMPORT_DELETE_ORIGINALS"], lychee.locale["UPLOAD_IMPORT_DELETE_ORIGINALS_EXPL"], build.iconic("check"), lychee.locale["UPLOAD_IMPORT_VIA_SYMLINK"], lychee.locale["UPLOAD_IMPORT_VIA_SYMLINK_EXPL"], build.iconic("check"), lychee.locale["UPLOAD_IMPORT_SKIP_DUPLICATES"], lychee.locale["UPLOAD_IMPORT_SKIP_DUPLICATES_EXPL"], build.iconic("check"), lychee.locale["UPLOAD_IMPORT_RESYNC_METADATA"], lychee.locale["UPLOAD_IMPORT_RESYNC_METADATA_EXPL"]);

		basicModal.show({
			body: msg,
			callback: importDialogSetupCB,
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

	dropbox: function dropbox() {
		var albumID = album.getID();

		/**
   * @param {DropboxFile[]} files
   */
		var action = function action(files) {
			var runImport = function runImport() {
				var successHandler = function successHandler() {
					// Same code as in import.url()
					basicModal.close();
					upload.notify(lychee.locale["UPLOAD_IMPORT_COMPLETE"]);
					album.reload();
				};

				/**
     * @param {XMLHttpRequest} jqXHR
     * @param {Object} params
     * @param {?LycheeException} lycheeException
     * @returns {boolean}
     */
				var errorHandler = function errorHandler(jqXHR, params, lycheeException) {
					// Same code as in import.url()
					var errorText = void 0;
					var statusText = void 0;
					var statusClass = void 0;

					switch (jqXHR.status) {
						case 409:
							statusText = lychee.locale["UPLOAD_SKIPPED"];
							errorText = lycheeException ? lycheeException.message : lychee.locale["UPLOAD_IMPORT_WARN_ERR"];
							statusClass = "warning";
							break;
						default:
							statusText = lychee.locale["UPLOAD_FAILED"];
							errorText = lycheeException ? lycheeException.message : lychee.locale["UPLOAD_IMPORT_WARN_ERR"];
							statusClass = "error";
							break;
					}

					$(firstRowNoticeSelector).html(errorText).show();
					$(firstRowStatusSelector).html(statusText).addClass(statusClass);
					// Show close button
					$(".basicModal #basicModal__action.hidden").show();
					upload.notify(lychee.locale["UPLOAD_IMPORT_WARN_ERR"]);
					album.reload();
					return true;
				};

				$(firstRowStatusSelector).html(lychee.locale["UPLOAD_IMPORTING"]);

				// TODO: Use a streamed response; see long comment in `import.url()` for the reasons
				api.post("Import::url", {
					urls: files.map(function (file) {
						return file.link;
					}),
					albumID: albumID
				}, successHandler, null, errorHandler);
			};

			files.forEach(function (file) {
				return file.name = file.link;
			});
			upload.show("Importing from Dropbox", files, runImport);
		};

		lychee.loadDropbox(function () {
			Dropbox.choose({
				linkType: "direct",
				multiselect: true,
				success: action
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

/**
 * @param {(FileList|File[])} files
 *
 * @returns {void}
 */
upload.uploadTrack = function (files) {
	var albumID = album.getID();
	if (files.length <= 0 || albumID === null) return;

	var runUpload = function runUpload() {
		/**
   * A function to be called when a response has been received.
   *
   * It closes the modal dialog or shows the close button and
   * reloads the album.
   *
   * @this XMLHttpRequest
   */
		var finish = function finish() {
			/** @type {?LycheeException} */
			var lycheeException = this.status >= 400 ? this.response : null;
			var errorText = "";
			var statusText = void 0;
			var statusClass = void 0;

			$("#upload_track_file").val("");

			switch (this.status) {
				case 200:
				case 201:
				case 204:
					statusText = lychee.locale["UPLOAD_FINISHED"];
					statusClass = "success";
					break;
				case 413:
					statusText = lychee.locale["UPLOAD_FAILED"];
					errorText = lychee.locale["UPLOAD_ERROR_POSTSIZE"];
					statusClass = "error";
					break;
				default:
					statusText = lychee.locale["UPLOAD_FAILED"];
					errorText = lycheeException ? lycheeException.message : lychee.locale["UPLOAD_ERROR_UNKNOWN"];
					statusClass = "error";
					break;
			}

			$(firstRowStatusSelector).html(statusText).addClass(statusClass);

			if (errorText !== "") {
				$(firstRowNoticeSelector).html(errorText).show();

				api.onError(this, { albumID: albumID }, lycheeException);
				showCloseButton();
				upload.notify(lychee.locale["UPLOAD_COMPLETE"], lychee.locale["UPLOAD_COMPLETE_FAILED"]);
			} else {
				basicModal.close();
				upload.notify(lychee.locale["UPLOAD_COMPLETE"]);
			}

			album.reload();
		}; // finish

		$(firstRowStatusSelector).html(lychee.locale["UPLOAD_UPLOADING"]);

		var formData = new FormData();
		var xhr = new XMLHttpRequest();

		formData.append("albumID", albumID);
		formData.append("file", files[0]);

		xhr.onload = finish;
		xhr.responseType = "json";
		xhr.open("POST", "api/Album::setTrack");
		xhr.setRequestHeader("X-XSRF-TOKEN", csrf.getCSRFCookieValue());
		xhr.setRequestHeader("Accept", "application/json");

		xhr.send(formData);
	}; // runUpload

	upload.show(lychee.locale["UPLOAD_UPLOADING"], files, runUpload);
};

var users = {
	/** @type {?User[]} */
	json: null
};

/**
 * Updates a user account.
 *
 * The object `params` must be kept in sync with the HTML form constructed
 * by {@link build.user}.
 *
 * @param {{id: number, username: string, password: string, may_upload: boolean, is_locked: boolean}} params
 * @returns {void}
 */
users.update = function (params) {
	if (params.username.length < 1) {
		loadingBar.show("error", "new username cannot be empty.");
		return;
	}

	// If the password is empty, then the password shall not be changed.
	// In this case, the password must not be an attribute of the object at
	// all.
	// An existing, but empty password, would indicate to clear the password.
	if (params.password.length === 0) {
		delete params.password;
	}

	api.post("User::save", params, function () {
		loadingBar.show("success", "User updated!");
		users.list(); // reload user list
	});
};

/**
 * Creates a new user account.
 *
 * The object `params` must be kept in sync with the HTML form constructed
 * by {@link view.users.content}.
 *
 * @param {{id: string, username: string, password: string, may_upload: boolean, is_locked: boolean}} params
 * @returns {void}
 */
users.create = function (params) {
	if (params.username.length < 1) {
		loadingBar.show("error", "new username cannot be empty.");
		return;
	}
	if (params.password.length < 1) {
		loadingBar.show("error", "new password cannot be empty.");
		return;
	}

	api.post("User::create", params, function () {
		loadingBar.show("success", "User created!");
		users.list(); // reload user list
	});
};

/**
 * Deletes a user account.
 *
 * The object `params` must be kept in sync with the HTML form constructed
 * by {@link build.user}.
 *
 * @param {{id: number}} params
 * @returns {boolean}
 */
users.delete = function (params) {
	api.post("User::delete", params, function () {
		loadingBar.show("success", "User deleted!");
		users.list(); // reload user list
	});
};

/**
 * @returns {void}
 */
users.list = function () {
	api.post("User::list", {},
	/** @param {User[]} data */
	function (data) {
		users.json = data;
		view.users.init();
	});
};

/**
 * @description Responsible to reflect data changes to the UI.
 */

var view = {};

view.albums = {
	/** @returns {void} */
	init: function init() {
		multiselect.clearSelection();

		view.albums.title();
		view.albums.content.init();
	},

	/** @returns {void} */
	title: function title() {
		if (lychee.landing_page_enable) {
			lychee.setTitle("", false);
		} else {
			lychee.setTitle(lychee.locale["ALBUMS"], false);
		}
	},

	content: {
		/** @returns {void} */
		init: function init() {
			var smartData = "";
			var tagAlbumsData = "";
			var albumsData = "";
			var sharedData = "";

			// Smart Albums
			if (lychee.publicMode === false && (albums.json.smart_albums.public || albums.json.smart_albums.recent || albums.json.smart_albums.starred || albums.json.smart_albums.unsorted || albums.json.tag_albums.length > 0)) {
				smartData = build.divider(lychee.locale["SMART_ALBUMS"]);
			}
			if (albums.json.smart_albums.unsorted) {
				albums.parse(albums.json.smart_albums.unsorted);
				smartData += build.album(albums.json.smart_albums.unsorted);
			}
			if (albums.json.smart_albums.public) {
				albums.parse(albums.json.smart_albums.public);
				smartData += build.album(albums.json.smart_albums.public);
			}
			if (albums.json.smart_albums.starred) {
				albums.parse(albums.json.smart_albums.starred);
				smartData += build.album(albums.json.smart_albums.starred);
			}
			if (albums.json.smart_albums.recent) {
				albums.parse(albums.json.smart_albums.recent);
				smartData += build.album(albums.json.smart_albums.recent);
			}

			// Tag albums
			tagAlbumsData += albums.json.tag_albums.reduce(function (html, tagAlbum) {
				albums.parse(tagAlbum);
				return html + build.album(tagAlbum);
			}, "");

			// Albums
			if (lychee.publicMode === false && albums.json.albums.length > 0) albumsData = build.divider(lychee.locale["ALBUMS"]);
			albumsData += albums.json.albums.reduce(function (html, album) {
				albums.parse(album);
				return html + build.album(album);
			}, "");

			var current_owner = "";
			// Shared
			sharedData += albums.json.shared_albums.reduce(function (html, album) {
				albums.parse(album);
				if (current_owner !== album.owner_name && lychee.publicMode === false) {
					html += build.divider(album.owner_name);
					current_owner = album.owner_name;
				}
				return html + build.album(album, !lychee.admin);
			}, "");

			if (smartData === "" && tagAlbumsData === "" && albumsData === "" && sharedData === "") {
				lychee.content.html("");
				$("body").append(build.no_content("eye"));
			} else {
				lychee.content.html(smartData + tagAlbumsData + albumsData + sharedData);
			}

			album.apply_nsfw_filter();

			// Restore scroll position
			var urls = JSON.parse(localStorage.getItem("scroll"));
			var urlWindow = window.location.href;
			$(window).scrollTop(urls != null && urls[urlWindow] ? urls[urlWindow] : 0);
		},

		/**
   * @param {string} albumID
   * @returns {void}
   */
		title: function title(albumID) {
			var album = albums.getByID(albumID);
			var title = album.title ? album.title : lychee.locale["UNTITLED"];

			$('.album[data-id="' + albumID + '"] .overlay h1').text(title).attr("title", title);
		},

		/**
   * @param {string} albumID
   * @returns {void}
   */
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
	/** @returns {void} */
	init: function init() {
		multiselect.clearSelection();

		view.album.sidebar();
		view.album.title();
		view.album.public();
		view.album.nsfw();
		view.album.nsfw_warning.init();
		view.album.content.init();

		// TODO: `init` is not a property of the Album JSON; this is a property of the view. Consider to move it to `view.album.isInitialized`
		album.json.init = true;
	},

	/** @returns {void} */
	title: function title() {
		if ((visible.album() || !album.json.init) && !visible.photo()) {
			switch (album.getID()) {
				case SmartAlbumID.STARRED:
					lychee.setTitle(lychee.locale["STARRED"], true);
					break;
				case SmartAlbumID.PUBLIC:
					lychee.setTitle(lychee.locale["PUBLIC"], true);
					break;
				case SmartAlbumID.RECENT:
					lychee.setTitle(lychee.locale["RECENT"], true);
					break;
				case SmartAlbumID.UNSORTED:
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
		/** @returns {void} */
		init: function init() {
			if (!lychee.nsfw_warning) {
				$("#sensitive_warning").hide();
				return;
			}

			if (album.json.is_nsfw && !lychee.nsfw_unlocked_albums.includes(album.json.id)) {
				$("#sensitive_warning").show();
			} else {
				$("#sensitive_warning").hide();
			}
		},

		/** @returns {void} */
		next: function next() {
			lychee.nsfw_unlocked_albums.push(album.json.id);
			$("#sensitive_warning").hide();
		}
	},

	content: {
		/** @returns {void} */
		init: function init() {
			var photosData = "";
			var albumsData = "";
			var html = "";

			if (album.json.albums) {
				album.json.albums.forEach(function (_album) {
					albums.parse(_album);
					albumsData += build.album(_album, !album.isUploadable());
				});
			}
			if (album.json.photos) {
				// Build photos
				album.json.photos.forEach(function (_photo) {
					photosData += build.photo(_photo, !album.isUploadable());
				});
			}

			if (photosData !== "") {
				if (lychee.layout === 1) {
					photosData = '<div class="justified-layout">' + photosData + "</div>";
				} else if (lychee.layout === 2) {
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

			// Add photos to view
			lychee.content.html(html);
			album.apply_nsfw_filter();

			view.album.content.justify(album.json ? album.json.photos : []);

			view.album.content.restoreScroll();
		},

		/** @returns {void} */
		restoreScroll: function restoreScroll() {
			// Restore scroll position
			var urls = JSON.parse(localStorage.getItem("scroll"));
			var urlWindow = window.location.href;
			$(window).scrollTop(urls != null && urls[urlWindow] ? urls[urlWindow] : 0);
		},

		/**
   * @param {string} photoID
   * @returns {void}
   */
		title: function title(photoID) {
			var photo = album.getByID(photoID);
			var title = photo.title ? photo.title : lychee.locale["UNTITLED"];

			$('.photo[data-id="' + photoID + '"] .overlay h1').text(title).attr("title", title);
		},

		/**
   * @param {string} albumID
   * @returns {void}
   */
		titleSub: function titleSub(albumID) {
			var album = album.getSubByID(albumID);
			var title = album.title ? album.title : lychee.locale["UNTITLED"];

			$('.album[data-id="' + albumID + '"] .overlay h1').text(title).attr("title", title);
		},

		/**
   * @param {string} photoID
   * @returns {void}
   */
		star: function star(photoID) {
			var $badge = $('.photo[data-id="' + photoID + '"] .icn-star');

			if (album.getByID(photoID).is_starred) $badge.addClass("badge--star");else $badge.removeClass("badge--star");
		},

		/**
   * @param {string} photoID
   * @returns {void}
   */
		public: function _public(photoID) {
			var $badge = $('.photo[data-id="' + photoID + '"] .icn-share');

			if (album.getByID(photoID).is_public === 1) $badge.addClass("badge--visible badge--hidden");else $badge.removeClass("badge--visible badge--hidden");
		},

		/**
   * @param {string} photoID
   * @returns {void}
   */
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

		/**
   * @param {Photo} data
   * @returns {void}
   */
		updatePhoto: function updatePhoto(data) {
			var src = void 0,
			    srcset = "";

			// This mimicks the structure of build.photo
			if (lychee.layout === 0) {
				src = data.size_variants.thumb.url;
				if (data.size_variants.thumb2x !== null) {
					srcset = data.size_variants.thumb2x.url + " 2x";
				}
			} else {
				if (data.size_variants.small !== null) {
					src = data.size_variants.small.url;
					if (data.size_variants.small2x !== null) {
						srcset = data.size_variants.small.url + " " + data.size_variants.small.width + "w, " + data.size_variants.small2x.url + " " + data.size_variants.small2x.width + "w";
					}
				} else if (data.size_variants.medium !== null) {
					src = data.size_variants.medium.url;
					if (data.size_variants.medium2x !== null) {
						srcset = data.size_variants.medium.url + " " + data.size_variants.medium.width + "w, " + data.size_variants.medium2x.url + " " + data.size_variants.medium2x.width + "w";
					}
				} else if (!data.type || data.type.indexOf("video") !== 0) {
					src = data.size_variants.original.url;
				} else {
					src = data.size_variants.thumb.url;
					if (data.size_variants.thumb2x !== null) {
						srcset = data.size_variants.thumb.url + " " + data.size_variants.thumb.width + "w, " + data.size_variants.thumb2x.url + " " + data.size_variants.thumb2x.width + "w";
					}
				}
			}

			$('.photo[data-id="' + data.id + '"] > span.thumbimg > img').attr("data-src", src).attr("data-srcset", srcset).addClass("lazyload");

			view.album.content.justify(album.json ? album.json.photos : []);
		},

		/**
   * @param {string} photoID
   * @param {boolean} [justify=false]
   * @returns {void}
   */
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
							_sidebar.changeAttr("images", (album.json.photos.length - videoCount).toString());
						} else {
							_sidebar.hideAttr("images");
						}
						if (videoCount > 0) {
							_sidebar.changeAttr("videos", videoCount.toString());
						} else {
							_sidebar.hideAttr("videos");
						}
					}
					if (album.json.photos.length <= 0) {
						lychee.content.find(".divider").remove();
					}
					if (justify) {
						view.album.content.justify(album.json ? album.json.photos : []);
					}
				}
			});
		},

		/**
   * @param {string} albumID
   * @returns {void}
   */
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
							_sidebar.changeAttr("subalbums", album.json.albums.length.toString());
						} else {
							_sidebar.hideAttr("subalbums");
						}
					}
				}
			});
		},

		/**
   * Lays out the photos inside an album or a search result.
   *
   * This method is a misnomer, because it does not necessarily
   * create a justified layout, but the configured layout as specified
   * by `lychee.layout` which can also be a non-justified layout.
   *
   * Also note that this method is bastardized by `search.find`.
   * Hence, this method would better not be part of `view.album.content`,
   * because it is not exclusively used for an album.
   *
   * @param {Photo[]} photos - the photos to be laid out
   *
   * @returns {void}
   */
		justify: function justify(photos) {
			if (photos.length === 0) return;
			if (lychee.layout === 1) {
				var containerWidth = parseFloat($(".justified-layout").width());
				if (containerWidth === 0) {
					// Triggered on Reload in photo view.
					containerWidth = $(window).width() - parseFloat($(".justified-layout").css("margin-left")) - parseFloat($(".justified-layout").css("margin-right")) - parseFloat($(".content").css("padding-right"));
				}
				/** @type {number[]} */
				var ratio = photos.map(function (_photo) {
					var height = _photo.size_variants.original.height;
					var width = _photo.size_variants.original.width;
					var ratio = height > 0 ? width / height : 1;
					// If there is no small and medium size variants for videos,
					// we have to fall back to square thumbs
					return _photo.type && _photo.type.indexOf("video") !== -1 && _photo.size_variants.small === null && _photo.size_variants.medium === null ? 1 : ratio;
				});

				var layoutGeometry = require("justified-layout")(ratio, {
					containerWidth: containerWidth,
					containerPadding: 0,
					// boxSpacing: {
					//     horizontal: 42,
					//     vertical: 150
					// },
					targetRowHeight: parseFloat($(".photo").css("--lychee-default-height"))
				});
				// if (lychee.admin) console.log(layoutGeometry);
				$(".justified-layout").css("height", layoutGeometry.containerHeight + "px");
				$(".justified-layout > div").each(function (i) {
					if (!layoutGeometry.boxes[i]) {
						// Race condition in search.find -- window content
						// and `photos` can get out of sync as search
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
			} else if (lychee.layout === 2) {
				var _containerWidth = parseFloat($(".unjustified-layout").width());
				if (_containerWidth === 0) {
					// Triggered on Reload in photo view.
					_containerWidth = $(window).width() - parseFloat($(".unjustified-layout").css("margin-left")) - parseFloat($(".unjustified-layout").css("margin-right")) - parseFloat($(".content").css("padding-right"));
				}
				// For whatever reason, the calculation of margin is
				// super-slow in Firefox (tested with 68), so we make sure to
				// do it just once, outside the loop.  Height doesn't seem to
				// be affected, but we do it the same way for consistency.
				var margin = parseFloat($(".photo").css("margin-right"));
				var origHeight = parseFloat($(".photo").css("max-height"));
				$(".unjustified-layout > div").each(function (i) {
					if (!photos[i]) {
						// Race condition in search.find -- window content
						// and `photos` can get out of sync as search
						// query is being modified.
						return false;
					}
					var ratio = photos[i].size_variants.original.height > 0 ? photos[i].size_variants.original.width / photos[i].size_variants.original.height : 1;
					if (photos[i].type && photos[i].type.indexOf("video") > -1) {
						// Video.  If there's no small and medium, we have
						// to fall back to the square thumb.
						if (photos[i].size_variants.small === null && photos[i].size_variants.medium === null) {
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

	/**
  * @returns {void}
  */
	description: function description() {
		_sidebar.changeAttr("description", album.json.description ? album.json.description : "");
	},

	/**
  * @returns {void}
  */
	show_tags: function show_tags() {
		_sidebar.changeAttr("show_tags", album.json.show_tags.join(", "));
	},

	/**
  * @returns {void}
  */
	license: function license() {
		var license = void 0;
		switch (album.json.license) {
			case "none":
				// TODO: If we do not use `"none"` as a literal string, we should convert `license` to a nullable DB attribute and use `null` for none to be consistent which everything else
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

	/**
  * @returns {void}
  */
	public: function _public() {
		$("#button_visibility_album, #button_sharing_album_users").removeClass("active--not-hidden active--hidden");

		if (album.json.is_public) {
			if (album.json.requires_link) {
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

	/**
  * @returns {void}
  */
	requiresLink: function requiresLink() {
		if (album.json.requires_link) _sidebar.changeAttr("hidden", lychee.locale["ALBUM_SHR_YES"]);else _sidebar.changeAttr("hidden", lychee.locale["ALBUM_SHR_NO"]);
	},

	/**
  * @returns {void}
  */
	nsfw: function nsfw() {
		if (album.json.is_nsfw) {
			// Sensitive
			$("#button_nsfw_album").addClass("active").attr("title", lychee.locale["ALBUM_UNMARK_NSFW"]);
		} else {
			// Not Sensitive
			$("#button_nsfw_album").removeClass("active").attr("title", lychee.locale["ALBUM_MARK_NSFW"]);
		}
	},

	/**
  * @returns {void}
  */
	downloadable: function downloadable() {
		if (album.json.is_downloadable) _sidebar.changeAttr("downloadable", lychee.locale["ALBUM_SHR_YES"]);else _sidebar.changeAttr("downloadable", lychee.locale["ALBUM_SHR_NO"]);
	},

	/**
  * @returns {void}
  */
	shareButtonVisible: function shareButtonVisible() {
		if (album.json.is_share_button_visible) _sidebar.changeAttr("share_button_visible", lychee.locale["ALBUM_SHR_YES"]);else _sidebar.changeAttr("share_button_visible", lychee.locale["ALBUM_SHR_NO"]);
	},

	/**
  * @returns {void}
  */
	password: function password() {
		if (album.json.has_password) _sidebar.changeAttr("password", lychee.locale["ALBUM_SHR_YES"]);else _sidebar.changeAttr("password", lychee.locale["ALBUM_SHR_NO"]);
	},

	/**
  * @returns {void}
  */
	sidebar: function sidebar() {
		if ((visible.album() || album.json && !album.json.init) && !visible.photo()) {
			var structure = _sidebar.createStructure.album(album.json);
			var html = _sidebar.render(structure);

			_sidebar.dom(".sidebar__wrapper").html(html);
			_sidebar.bind();
		}
	}
};

view.photo = {
	/**
  * @param {boolean} autoplay
  * @returns {void}
  */
	init: function init(autoplay) {
		multiselect.clearSelection();

		view.photo.sidebar();
		view.photo.title();
		view.photo.star();
		view.photo.public();
		view.photo.header();
		view.photo.photo(autoplay);

		// TODO: `init` is not a property of the Photo JSON; this is a property of the view. Consider to move it to `view.photo.isInitialized`
		_photo3.json.init = true;
	},

	/**
  * @returns {void}
  */
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
			// For live Photos: header animation only if LivePhoto is not playing
			if (!_photo3.isLivePhotoPlaying() && lychee.header_auto_hide) {
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

	/**
  * @returns {void}
  */
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

	/**
  * @returns {void}
  */
	title: function title() {
		if (_photo3.json.init) _sidebar.changeAttr("title", _photo3.json.title ? _photo3.json.title : "");
		lychee.setTitle(_photo3.json.title ? _photo3.json.title : lychee.locale["UNTITLED"], true);
	},

	/**
  * @returns {void}
  */
	description: function description() {
		if (_photo3.json.init) _sidebar.changeAttr("description", _photo3.json.description ? _photo3.json.description : "");
	},

	/**
  * @returns {void}
  */
	license: function license() {
		var license = void 0;

		// Process key to display correct string
		switch (_photo3.json.license) {
			case "none":
				// TODO: If we do not use `"none"` as a literal string, we should convert `license` to a nullable DB attribute and use `null` for none to be consistent which everything else
				license = ""; // none is displayed as - thus is empty (uniformity of the display).
				break;
			case "reserved":
				license = lychee.locale["PHOTO_RESERVED"];
				break;
			default:
				license = _photo3.json.license;
				break;
		}

		// Update the sidebar if the photo is visible
		if (_photo3.json.init) _sidebar.changeAttr("license", license);
	},

	/**
  * @returns {void}
  */
	star: function star() {
		if (_photo3.json.is_starred) {
			// Starred
			$("#button_star").addClass("active").attr("title", lychee.locale["UNSTAR_PHOTO"]);
		} else {
			// Unstarred
			$("#button_star").removeClass("active").attr("title", lychee.locale["STAR_PHOTO"]);
		}
	},

	/**
  * @returns {void}
  */
	public: function _public() {
		$("#button_visibility").removeClass("active--hidden active--not-hidden");

		if (_photo3.json.is_public === 1 || _photo3.json.is_public === 2) {
			// Photo public
			if (_photo3.json.is_public === 1) {
				$("#button_visibility").addClass("active--hidden");
			} else {
				$("#button_visibility").addClass("active--not-hidden");
			}

			if (_photo3.json.init) _sidebar.changeAttr("public", lychee.locale["PHOTO_SHR_YES"]);
		} else {
			// Photo private
			if (_photo3.json.init) _sidebar.changeAttr("public", lychee.locale["PHOTO_SHR_NO"]);
		}
	},

	/**
  * @returns {void}
  */
	tags: function tags() {
		_sidebar.changeAttr("tags", build.tags(_photo3.json.tags), true);
		_sidebar.bind();
	},

	/**
  * @param {boolean} autoplay
  * @returns {void}
  */
	photo: function photo(autoplay) {
		var ret = build.imageview(_photo3.json, visible.header(), autoplay);
		lychee.imageview.html(ret.html);
		tabindex.makeFocusable(lychee.imageview);

		// Init Live Photo if needed
		if (_photo3.isLivePhoto()) {
			// Package gives warning that function will be remove and
			// shoud be replaced by LivePhotosKit.augementElementAsPlayer
			// But, LivePhotosKit.augementElementAsPlayer is not yet available
			_photo3.livePhotosObject = LivePhotosKit.Player(document.getElementById("livephoto"));
		}

		view.photo.onresize();

		var $nextArrow = lychee.imageview.find("a#next");
		var $previousArrow = lychee.imageview.find("a#previous");
		var photoID = _photo3.getID();
		/** @type {?Photo} */
		var photoInAlbum = album.json && album.json.photos ? album.getByID(photoID) : null;
		/** @type {?Photo} */
		var nextPhotoInAlbum = photoInAlbum && photoInAlbum.next_photo_id ? album.getByID(photoInAlbum.next_photo_id) : null;
		/** @type {?Photo} */
		var prevPhotoInAlbum = photoInAlbum && photoInAlbum.previous_photo_id ? album.getByID(photoInAlbum.previous_photo_id) : null;

		var img = $("img#image");
		if (img.length > 0) {
			if (!img[0].complete || img[0].currentSrc !== null && img[0].currentSrc === "") {
				// Image is still loading.  Display the thumb version in the
				// background.
				if (ret.thumb !== "") {
					img.css("background-image", lychee.html(_templateObject77, ret.thumb));
				}

				// Don't preload next/prev until the requested image is
				// fully loaded.
				img.on("load", function () {
					_photo3.preloadNextPrev(_photo3.getID());
				});
			} else {
				_photo3.preloadNextPrev(_photo3.getID());
			}
		}

		if (nextPhotoInAlbum === null || lychee.viewMode === true) {
			$nextArrow.hide();
		} else {
			// Check if thumbUrl exists (for videos w/o ffmpeg, we add a play-icon)
			var thumbUrl = "img/placeholder.png";
			if (nextPhotoInAlbum.size_variants.thumb !== null) {
				thumbUrl = nextPhotoInAlbum.size_variants.thumb.url;
			} else if (nextPhotoInAlbum.type.indexOf("video") > -1) {
				thumbUrl = "img/play-icon.png";
			}
			$nextArrow.css("background-image", lychee.html(_templateObject78, thumbUrl));
		}

		if (prevPhotoInAlbum === null || lychee.viewMode === true) {
			$previousArrow.hide();
		} else {
			// Check if thumbUrl exists (for videos w/o ffmpeg, we add a play-icon)
			var _thumbUrl = "img/placeholder.png";
			if (prevPhotoInAlbum.size_variants.thumb !== null) {
				_thumbUrl = prevPhotoInAlbum.size_variants.thumb.url;
			} else if (prevPhotoInAlbum.type.indexOf("video") > -1) {
				_thumbUrl = "img/play-icon.png";
			}
			$previousArrow.css("background-image", lychee.html(_templateObject78, _thumbUrl));
		}
	},

	/**
  * @returns {void}
  */
	sidebar: function sidebar() {
		var structure = _sidebar.createStructure.photo(_photo3.json);
		var html = _sidebar.render(structure);
		var has_location = !!(_photo3.json.latitude && _photo3.json.longitude);

		_sidebar.dom(".sidebar__wrapper").html(html);
		_sidebar.bind();

		if (has_location && lychee.map_display) {
			// Leaflet searches for icon in same directory as js file -> paths needs
			// to be overwritten
			delete L.Icon.Default.prototype._getIconUrl;
			L.Icon.Default.mergeOptions({
				iconRetinaUrl: "img/marker-icon-2x.png",
				iconUrl: "img/marker-icon.png",
				shadowUrl: "img/marker-shadow.png"
			});

			var myMap = L.map("leaflet_map_single_photo").setView([_photo3.json.latitude, _photo3.json.longitude], 13);

			L.tileLayer(map_provider_layer_attribution[lychee.map_provider].layer, {
				attribution: map_provider_layer_attribution[lychee.map_provider].attribution
			}).addTo(myMap);

			if (!lychee.map_display_direction || !_photo3.json.img_direction) {
				// Add Marker to map, direction is not set
				L.marker([_photo3.json.latitude, _photo3.json.longitude]).addTo(myMap);
			} else {
				// Add Marker, direction has been set
				var viewDirectionIcon = L.icon({
					iconUrl: "img/view-angle-icon.png",
					iconRetinaUrl: "img/view-angle-icon-2x.png",
					iconSize: [100, 58], // size of the icon
					iconAnchor: [50, 49] // point of the icon which will correspond to marker's location
				});
				var marker = L.marker([_photo3.json.latitude, _photo3.json.longitude], { icon: viewDirectionIcon }).addTo(myMap);
				marker.setRotationAngle(_photo3.json.img_direction);
			}
		}
	},

	/**
  * @returns {void}
  */
	header: function header() {
		/* Note: the condition below is duplicated in contextMenu.photoMore() */
		if (_photo3.json.type && (_photo3.json.type.indexOf("video") === 0 || _photo3.json.type === "raw") || _photo3.json.live_photo_url !== "" && _photo3.json.live_photo_url !== null) {
			$("#button_rotate_cwise, #button_rotate_ccwise").hide();
		} else {
			$("#button_rotate_cwise, #button_rotate_ccwise").show();
		}
	},

	/**
  * @returns {void}
  */
	onresize: function onresize() {
		if (!_photo3.json || _photo3.json.size_variants.medium === null || _photo3.json.size_variants.medium2x === null) return;

		// Calculate the width of the image in the current window without
		// borders and set 'sizes' to it.
		var imgWidth = _photo3.json.size_variants.medium.width;
		var imgHeight = _photo3.json.size_variants.medium.height;
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
	/**
  * @returns {void}
  */
	init: function init() {
		multiselect.clearSelection();

		view.photo.hide();
		view.settings.title();
		header.setMode("config");
		view.settings.content.init();
	},

	/**
  * @returns {void}
  */
	title: function title() {
		lychee.setTitle(lychee.locale["SETTINGS"], false);
	},

	/**
  * @returns {void}
  */
	clearContent: function clearContent() {
		lychee.content.html('<div class="settings_view"></div>');
	},

	content: {
		/**
   * @returns {void}
   */
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
				view.settings.content.setNotification();
				view.settings.content.setCSS();
				view.settings.content.moreButton();
			}
		},

		/**
   * @returns {void}
   */
		setLogin: function setLogin() {
			var msg = lychee.html(_templateObject79, lychee.locale["PASSWORD_TITLE"], lychee.locale["USERNAME_CURRENT"], lychee.locale["PASSWORD_CURRENT"], lychee.locale["PASSWORD_TEXT"], lychee.locale["LOGIN_USERNAME"], lychee.locale["LOGIN_PASSWORD"], lychee.locale["LOGIN_PASSWORD_CONFIRM"], lychee.locale["PASSWORD_CHANGE"]);

			$(".settings_view").append(msg);

			settings.bind("#basicModal__action_password_change", ".setLogin", settings.changeLogin);
		},

		/**
   * @returns {void}
   */
		clearLogin: function clearLogin() {
			$("input[name=oldUsername], input[name=oldPassword], input[name=username], input[name=password], input[name=confirm]").val("");
		},

		/**
   * Renders the area of the settings related to sorting
   *
   * TODO: Note, the method is a misnomer.
   * It does not **set** any sorting, see {@link settings.changeSorting}
   * for that.
   * This method only creates the HTML GUI.
   *
   * @returns {void}
   */
		setSorting: function setSorting() {
			var msg = lychee.html(_templateObject80, lychee.locale["SORT_ALBUM_BY_1"], lychee.locale["SORT_ALBUM_SELECT_1"], lychee.locale["SORT_ALBUM_SELECT_2"], lychee.locale["SORT_ALBUM_SELECT_3"], lychee.locale["SORT_ALBUM_SELECT_4"], lychee.locale["SORT_ALBUM_SELECT_5"], lychee.locale["SORT_ALBUM_SELECT_6"], lychee.locale["SORT_ALBUM_BY_2"], lychee.locale["SORT_ASCENDING"], lychee.locale["SORT_DESCENDING"], lychee.locale["SORT_ALBUM_BY_3"], lychee.locale["SORT_PHOTO_BY_1"], lychee.locale["SORT_PHOTO_SELECT_1"], lychee.locale["SORT_PHOTO_SELECT_2"], lychee.locale["SORT_PHOTO_SELECT_3"], lychee.locale["SORT_PHOTO_SELECT_4"], lychee.locale["SORT_PHOTO_SELECT_5"], lychee.locale["SORT_PHOTO_SELECT_6"], lychee.locale["SORT_PHOTO_SELECT_7"], lychee.locale["SORT_PHOTO_BY_2"], lychee.locale["SORT_ASCENDING"], lychee.locale["SORT_DESCENDING"], lychee.locale["SORT_PHOTO_BY_3"], lychee.locale["SORT_CHANGE"]);

			$(".settings_view").append(msg);

			if (lychee.sorting_albums) {
				$(".setSorting select#settings_albums_sorting_column").val(lychee.sorting_albums.column);
				$(".setSorting select#settings_albums_sorting_order").val(lychee.sorting_albums.order);
			}

			if (lychee.sorting_photos) {
				$(".setSorting select#settings_photos_sorting_column").val(lychee.sorting_photos.column);
				$(".setSorting select#settings_photos_sorting_order").val(lychee.sorting_photos.order);
			}

			settings.bind("#basicModal__action_sorting_change", ".setSorting", settings.changeSorting);
		},

		/**
   * @returns {void}
   */
		setDropboxKey: function setDropboxKey() {
			var msg = "\n\t\t\t<div class=\"setDropBox\">\n\t\t\t  <p>" + lychee.locale["DROPBOX_TEXT"] + "\n\t\t\t  <input class='text' name='key' type='text' placeholder='Dropbox API Key' value='" + lychee.dropboxKey + "'>\n\t\t\t  </p>\n\t\t\t\t<div class=\"basicModal__buttons\">\n\t\t\t\t\t<a id=\"basicModal__action_dropbox_change\" class=\"basicModal__button\">" + lychee.locale["DROPBOX_TITLE"] + "</a>\n\t\t\t\t</div>\n\t\t\t  </div>\n\t\t\t  ";

			$(".settings_view").append(msg);
			settings.bind("#basicModal__action_dropbox_change", ".setDropBox", settings.changeDropboxKey);
		},

		/**
   * @returns {void}
   */
		setLang: function setLang() {
			var msg = "\n\t\t\t\t<div class=\"setLang\">\n\t\t\t\t\t<p>\n\t\t\t\t\t\t" + lychee.locale["LANG_TEXT"] + "\n\t\t\t  \t\t\t<span class=\"select\">\n\t\t\t\t\t\t\t<select id=\"settings_lang\" name=\"lang\">\n\t\t\t\t\t\t\t\t" + lychee.lang_available.reduce(function (html, lang_av) {
				return html + (lychee.lang === lang_av ? "<option selected>" : "<option>") + lang_av + "</option>";
			}, "") + "\n\t\t\t\t\t\t\t</select>\n\t\t\t  \t\t\t</span>\n\t\t\t\t\t</p>\n\t\t\t\t\t<div class=\"basicModal__buttons\">\n\t\t\t\t\t\t<a id=\"basicModal__action_set_lang\" class=\"basicModal__button\">" + lychee.locale["LANG_TITLE"] + "</a>\n\t\t\t\t\t</div>\n\t\t\t\t</div>";

			$(".settings_view").append(msg);
			settings.bind("#basicModal__action_set_lang", ".setLang", settings.changeLang);
		},

		/**
   * @returns {void}
   */
		setDefaultLicense: function setDefaultLicense() {
			var msg = "\n\t\t\t<div class=\"setDefaultLicense\">\n\t\t\t<p>" + lychee.locale["DEFAULT_LICENSE"] + "\n\t\t\t<span class=\"select\" style=\"width:270px\">\n\t\t\t\t<select name=\"license\" id=\"license\">\n\t\t\t\t\t<option value=\"none\">" + lychee.locale["PHOTO_LICENSE_NONE"] + "</option>\n\t\t\t\t\t<option value=\"reserved\">" + lychee.locale["PHOTO_RESERVED"] + "</option>\n\t\t\t\t\t<option value=\"CC0\">CC0 - Public Domain</option>\n\t\t\t\t\t<option value=\"CC-BY-1.0\">CC Attribution 1.0</option>\n\t\t\t\t\t<option value=\"CC-BY-2.0\">CC Attribution 2.0</option>\n\t\t\t\t\t<option value=\"CC-BY-2.5\">CC Attribution 2.5</option>\n\t\t\t\t\t<option value=\"CC-BY-3.0\">CC Attribution 3.0</option>\n\t\t\t\t\t<option value=\"CC-BY-4.0\">CC Attribution 4.0</option>\n\t\t\t\t\t<option value=\"CC-BY-ND-1.0\">CC Attribution-NoDerivatives 1.0</option>\n\t\t\t\t\t<option value=\"CC-BY-ND-2.0\">CC Attribution-NoDerivatives 2.0</option>\n\t\t\t\t\t<option value=\"CC-BY-ND-2.5\">CC Attribution-NoDerivatives 2.5</option>\n\t\t\t\t\t<option value=\"CC-BY-ND-3.0\">CC Attribution-NoDerivatives 3.0</option>\n\t\t\t\t\t<option value=\"CC-BY-ND-4.0\">CC Attribution-NoDerivatives 4.0</option>\n\t\t\t\t\t<option value=\"CC-BY-SA-1.0\">CC Attribution-ShareAlike 1.0</option>\n\t\t\t\t\t<option value=\"CC-BY-SA-2.0\">CC Attribution-ShareAlike 2.0</option>\n\t\t\t\t\t<option value=\"CC-BY-SA-2.5\">CC Attribution-ShareAlike 2.5</option>\n\t\t\t\t\t<option value=\"CC-BY-SA-3.0\">CC Attribution-ShareAlike 3.0</option>\n\t\t\t\t\t<option value=\"CC-BY-SA-4.0\">CC Attribution-ShareAlike 4.0</option>\n\t\t\t\t\t<option value=\"CC-BY-NC-1.0\">CC Attribution-NonCommercial 1.0</option>\n\t\t\t\t\t<option value=\"CC-BY-NC-2.0\">CC Attribution-NonCommercial 2.0</option>\n\t\t\t\t\t<option value=\"CC-BY-NC-2.5\">CC Attribution-NonCommercial 2.5</option>\n\t\t\t\t\t<option value=\"CC-BY-NC-3.0\">CC Attribution-NonCommercial 3.0</option>\n\t\t\t\t\t<option value=\"CC-BY-NC-4.0\">CC Attribution-NonCommercial 4.0</option>\n\t\t\t\t\t<option value=\"CC-BY-NC-ND-1.0\">CC Attribution-NonCommercial-NoDerivatives 1.0</option>\n\t\t\t\t\t<option value=\"CC-BY-NC-ND-2.0\">CC Attribution-NonCommercial-NoDerivatives 2.0</option>\n\t\t\t\t\t<option value=\"CC-BY-NC-ND-2.5\">CC Attribution-NonCommercial-NoDerivatives 2.5</option>\n\t\t\t\t\t<option value=\"CC-BY-NC-ND-3.0\">CC Attribution-NonCommercial-NoDerivatives 3.0</option>\n\t\t\t\t\t<option value=\"CC-BY-NC-ND-4.0\">CC Attribution-NonCommercial-NoDerivatives 4.0</option>\n\t\t\t\t\t<option value=\"CC-BY-NC-SA-1.0\">CC Attribution-NonCommercial-ShareAlike 1.0</option>\n\t\t\t\t\t<option value=\"CC-BY-NC-SA-2.0\">CC Attribution-NonCommercial-ShareAlike 2.0</option>\n\t\t\t\t\t<option value=\"CC-BY-NC-SA-2.5\">CC Attribution-NonCommercial-ShareAlike 2.5</option>\n\t\t\t\t\t<option value=\"CC-BY-NC-SA-3.0\">CC Attribution-NonCommercial-ShareAlike 3.0</option>\n\t\t\t\t\t<option value=\"CC-BY-NC-SA-4.0\">CC Attribution-NonCommercial-ShareAlike 4.0</option>\n\t\t\t\t</select>\n\t\t\t</span>\n\t\t\t<br />\n\t\t\t<a href=\"https://creativecommons.org/choose/\" target=\"_blank\">" + lychee.locale["PHOTO_LICENSE_HELP"] + "</a>\n\t\t\t</p>\n\t\t\t<div class=\"basicModal__buttons\">\n\t\t\t\t<a id=\"basicModal__action_set_license\" class=\"basicModal__button\">" + lychee.locale["SET_LICENSE"] + "</a>\n\t\t\t</div>\n\t\t\t</div>\n\t\t\t";
			$(".settings_view").append(msg);
			$("select#license").val(lychee.default_license === "" ? "none" : lychee.default_license);
			settings.bind("#basicModal__action_set_license", ".setDefaultLicense", settings.setDefaultLicense);
		},

		/**
   * @returns {void}
   */
		setLayout: function setLayout() {
			var msg = "\n\t\t\t<div class=\"setLayout\">\n\t\t\t<p>" + lychee.locale["LAYOUT_TYPE"] + "\n\t\t\t<span class=\"select\" style=\"width:270px\">\n\t\t\t\t<select name=\"layout\" id=\"layout\">\n\t\t\t\t\t<option value=\"0\">" + lychee.locale["LAYOUT_SQUARES"] + "</option>\n\t\t\t\t\t<option value=\"1\">" + lychee.locale["LAYOUT_JUSTIFIED"] + "</option>\n\t\t\t\t\t<option value=\"2\">" + lychee.locale["LAYOUT_UNJUSTIFIED"] + "</option>\n\t\t\t\t</select>\n\t\t\t</span>\n\t\t\t</p>\n\t\t\t<div class=\"basicModal__buttons\">\n\t\t\t\t<a id=\"basicModal__action_set_layout\" class=\"basicModal__button\">" + lychee.locale["SET_LAYOUT"] + "</a>\n\t\t\t</div>\n\t\t\t</div>\n\t\t\t";
			$(".settings_view").append(msg);
			$("select#layout").val(lychee.layout);
			settings.bind("#basicModal__action_set_layout", ".setLayout", settings.setLayout);
		},

		/**
   * @returns {void}
   */
		setPublicSearch: function setPublicSearch() {
			var msg = "\n\t\t\t<div class=\"setPublicSearch\">\n\t\t\t<p>" + lychee.locale["PUBLIC_SEARCH_TEXT"] + "\n\t\t\t<label class=\"switch\">\n\t\t\t  <input id=\"PublicSearch\" type=\"checkbox\" name=\"public_search\">\n\t\t\t  <span class=\"slider round\"></span>\n\t\t\t</label>\n\t\t\t</p>\n\t\t\t</div>\n\t\t\t";

			$(".settings_view").append(msg);
			if (lychee.public_search) $("#PublicSearch").click();

			settings.bind("#PublicSearch", ".setPublicSearch", settings.changePublicSearch);
		},

		/**
   * @returns {void}
   */
		setNSFWVisible: function setNSFWVisible() {
			var msg = "\n\t\t\t<div class=\"setNSFWVisible\">\n\t\t\t<p>" + lychee.locale["NSFW_VISIBLE_TEXT_1"] + "\n\t\t\t<label class=\"switch\">\n\t\t\t  <input id=\"NSFWVisible\" type=\"checkbox\" name=\"nsfw_visible\">\n\t\t\t  <span class=\"slider round\"></span>\n\t\t\t</label></p>\n\t\t\t<p>" + lychee.locale["NSFW_VISIBLE_TEXT_2"] + "\n\t\t\t</p>\n\t\t\t</div>\n\t\t\t";

			$(".settings_view").append(msg);
			if (lychee.nsfw_visible_saved) {
				$("#NSFWVisible").click();
			}

			settings.bind("#NSFWVisible", ".setNSFWVisible", settings.changeNSFWVisible);
		},
		// TODO: extend to the other settings.

		/**
   * @returns {void}
   */
		setOverlayType: function setOverlayType() {
			var msg = "\n\t\t\t<div class=\"setOverlayType\">\n\t\t\t<p>" + lychee.locale["OVERLAY_TYPE"] + "\n\t\t\t<span class=\"select\" style=\"width:270px\">\n\t\t\t\t<select name=\"image_overlay_type\" id=\"ImgOverlayType\">\n\t\t\t\t\t<option value=\"exif\">" + lychee.locale["OVERLAY_EXIF"] + "</option>\n\t\t\t\t\t<option value=\"desc\">" + lychee.locale["OVERLAY_DESCRIPTION"] + "</option>\n\t\t\t\t\t<option value=\"date\">" + lychee.locale["OVERLAY_DATE"] + "</option>\n\t\t\t\t\t<option value=\"none\">" + lychee.locale["OVERLAY_NONE"] + "</option>\n\t\t\t\t</select>\n\t\t\t</span>\n\t\t\t<div class=\"basicModal__buttons\">\n\t\t\t\t<a id=\"basicModal__action_set_overlay_type\" class=\"basicModal__button\">" + lychee.locale["SET_OVERLAY_TYPE"] + "</a>\n\t\t\t</div>\n\t\t\t</div>\n\t\t\t";

			$(".settings_view").append(msg);

			$("select#ImgOverlayType").val(!lychee.image_overlay_type_default ? "exif" : lychee.image_overlay_type_default);
			settings.bind("#basicModal__action_set_overlay_type", ".setOverlayType", settings.setOverlayType);
		},

		/**
   * @returns {void}
   */
		setMapDisplay: function setMapDisplay() {
			var msg = "\n\t\t\t<div class=\"setMapDisplay\">\n\t\t\t<p>" + lychee.locale["MAP_DISPLAY_TEXT"] + "\n\t\t\t<label class=\"switch\">\n\t\t\t  <input id=\"MapDisplay\" type=\"checkbox\" name=\"map_display\">\n\t\t\t  <span class=\"slider round\"></span>\n\t\t\t</label>\n\t\t\t</p>\n\t\t\t</div>\n\t\t\t";

			$(".settings_view").append(msg);
			if (lychee.map_display) $("#MapDisplay").click();

			settings.bind("#MapDisplay", ".setMapDisplay", settings.changeMapDisplay);

			msg = "\n\t\t\t<div class=\"setMapDisplayPublic\">\n\t\t\t<p>" + lychee.locale["MAP_DISPLAY_PUBLIC_TEXT"] + "\n\t\t\t<label class=\"switch\">\n\t\t\t\t<input id=\"MapDisplayPublic\" type=\"checkbox\" name=\"map_display_public\">\n\t\t\t\t<span class=\"slider round\"></span>\n\t\t\t</label>\n\t\t\t</p>\n\t\t\t</div>\n\t\t\t";

			$(".settings_view").append(msg);
			if (lychee.map_display_public) $("#MapDisplayPublic").click();

			settings.bind("#MapDisplayPublic", ".setMapDisplayPublic", settings.changeMapDisplayPublic);

			msg = "\n\t\t\t<div class=\"setMapProvider\">\n\t\t\t<p>" + lychee.locale["MAP_PROVIDER"] + "\n\t\t\t<span class=\"select\" style=\"width:270px\">\n\t\t\t\t<select name=\"map_provider\" id=\"MapProvider\">\n\t\t\t\t\t<option value=\"Wikimedia\">" + lychee.locale["MAP_PROVIDER_WIKIMEDIA"] + "</option>\n\t\t\t\t\t<option value=\"OpenStreetMap.org\">" + lychee.locale["MAP_PROVIDER_OSM_ORG"] + "</option>\n\t\t\t\t\t<option value=\"OpenStreetMap.de\">" + lychee.locale["MAP_PROVIDER_OSM_DE"] + "</option>\n\t\t\t\t\t<option value=\"OpenStreetMap.fr\">" + lychee.locale["MAP_PROVIDER_OSM_FR"] + "</option>\n\t\t\t\t\t<option value=\"RRZE\">" + lychee.locale["MAP_PROVIDER_RRZE"] + "</option>\n\t\t\t\t</select>\n\t\t\t</span>\n\t\t\t<div class=\"basicModal__buttons\">\n\t\t\t\t<a id=\"basicModal__action_set_map_provider\" class=\"basicModal__button\">" + lychee.locale["SET_MAP_PROVIDER"] + "</a>\n\t\t\t</div>\n\t\t\t</div>\n\t\t\t";

			$(".settings_view").append(msg);

			$("select#MapProvider").val(!lychee.map_provider ? "Wikimedia" : lychee.map_provider);
			settings.bind("#basicModal__action_set_map_provider", ".setMapProvider", settings.setMapProvider);

			msg = "\n\t\t\t<div class=\"setMapIncludeSubAlbums\">\n\t\t\t<p>" + lychee.locale["MAP_INCLUDE_SUBALBUMS_TEXT"] + "\n\t\t\t<label class=\"switch\">\n\t\t\t  <input id=\"MapIncludeSubAlbums\" type=\"checkbox\" name=\"map_include_subalbums\">\n\t\t\t  <span class=\"slider round\"></span>\n\t\t\t</label>\n\t\t\t</p>\n\t\t\t</div>\n\t\t\t";

			$(".settings_view").append(msg);
			if (lychee.map_include_subalbums) $("#MapIncludeSubAlbums").click();

			settings.bind("#MapIncludeSubAlbums", ".setMapIncludeSubAlbums", settings.changeMapIncludeSubAlbums);

			msg = "\n\t\t\t<div class=\"setLocationDecoding\">\n\t\t\t<p>" + lychee.locale["LOCATION_DECODING"] + "\n\t\t\t<label class=\"switch\">\n\t\t\t  <input id=\"LocationDecoding\" type=\"checkbox\" name=\"location_decoding\">\n\t\t\t  <span class=\"slider round\"></span>\n\t\t\t</label>\n\t\t\t</p>\n\t\t\t</div>\n\t\t\t";

			$(".settings_view").append(msg);
			if (lychee.location_decoding) $("#LocationDecoding").click();

			settings.bind("#LocationDecoding", ".setLocationDecoding", settings.changeLocationDecoding);

			msg = "\n\t\t\t<div class=\"setLocationShow\">\n\t\t\t<p>" + lychee.locale["LOCATION_SHOW"] + "\n\t\t\t<label class=\"switch\">\n\t\t\t  <input id=\"LocationShow\" type=\"checkbox\" name=\"location_show\">\n\t\t\t  <span class=\"slider round\"></span>\n\t\t\t</label>\n\t\t\t</p>\n\t\t\t</div>\n\t\t\t";

			$(".settings_view").append(msg);
			if (lychee.location_show) $("#LocationShow").click();

			settings.bind("#LocationShow", ".setLocationShow", settings.changeLocationShow);

			msg = "\n\t\t\t<div class=\"setLocationShowPublic\">\n\t\t\t<p>" + lychee.locale["LOCATION_SHOW_PUBLIC"] + "\n\t\t\t<label class=\"switch\">\n\t\t\t\t<input id=\"LocationShowPublic\" type=\"checkbox\" name=\"location_show_public\">\n\t\t\t\t<span class=\"slider round\"></span>\n\t\t\t</label>\n\t\t\t</p>\n\t\t\t</div>\n\t\t\t";

			$(".settings_view").append(msg);
			if (lychee.location_show_public) $("#LocationShowPublic").click();

			settings.bind("#LocationShowPublic", ".setLocationShowPublic", settings.changeLocationShowPublic);
		},

		/**
   * @returns {void}
   */
		setNotification: function setNotification() {
			var msg = "\n\t\t\t<div class=\"setNewPhotosNotification\">\n\t\t\t<p>" + lychee.locale["NEW_PHOTOS_NOTIFICATION"] + "\n\t\t\t<label class=\"switch\">\n\t\t\t\t<input id=\"NewPhotosNotification\" type=\"checkbox\" name=\"new_photos_notification\">\n\t\t\t\t<span class=\"slider round\"></span>\n\t\t\t</label>\n\t\t\t</p>\n\t\t\t</div>\n\t\t\t";

			$(".settings_view").append(msg);
			if (lychee.new_photos_notification) $("#NewPhotosNotification").click();

			settings.bind("#NewPhotosNotification", ".setNewPhotosNotification", settings.changeNewPhotosNotification);
		},

		/**
   * @returns {void}
   */
		setCSS: function setCSS() {
			var msg = "\n\t\t\t<div class=\"setCSS\">\n\t\t\t<p>" + lychee.locale["CSS_TEXT"] + "</p>\n\t\t\t<textarea id=\"css\"></textarea>\n\t\t\t<div class=\"basicModal__buttons\">\n\t\t\t\t<a id=\"basicModal__action_set_css\" class=\"basicModal__button\">" + lychee.locale["CSS_TITLE"] + "</a>\n\t\t\t</div>\n\t\t\t</div>";

			$(".settings_view").append(msg);

			var css_addr = $($("link")[1]).attr("href");

			api.getCSS(css_addr, function (data) {
				$("#css").html(data);
			});

			settings.bind("#basicModal__action_set_css", ".setCSS", settings.changeCSS);
		},

		/**
   * @returns {void}
   */
		moreButton: function moreButton() {
			var msg = lychee.html(_templateObject81, lychee.locale["MORE"]);

			$(".settings_view").append(msg);

			$("#basicModal__action_more").on("click", view.full_settings.init);
		}
	}
};

view.full_settings = {
	/**
  * @returns {void}
  */
	init: function init() {
		multiselect.clearSelection();

		view.full_settings.title();
		view.full_settings.content.init();
	},

	/**
  * @returns {void}
  */
	title: function title() {
		lychee.setTitle("Full Settings", false);
	},

	/**
  * @returns {void}
  */
	clearContent: function clearContent() {
		lychee.content.html('<div class="settings_view"></div>');
	},

	content: {
		init: function init() {
			view.full_settings.clearContent();

			api.post("Settings::getAll", {},
			/** @param {ConfigSetting[]} data */
			function (data) {
				var msg = lychee.html(_templateObject82, lychee.locale["SETTINGS_WARNING"]);

				var prev = "";
				data.forEach(function (_config) {
					if (_config.cat && prev !== _config.cat) {
						msg += lychee.html(_templateObject83, _config.cat);
						prev = _config.cat;
					}
					// prevent 'null' string for empty values
					var val = _config.value ? _config.value : "";
					msg += lychee.html(_templateObject84, _config.key, _config.key, val);
				});

				msg += lychee.html(_templateObject85, lychee.locale["SAVE_RISK"]);

				$(".settings_view").append(msg);

				settings.bind("#FullSettingsSave_button", "#fullSettings", settings.save);

				$("#fullSettings").on("keypress", function (e) {
					settings.save_enter(e);
				});
			});
		}
	}
};

view.notifications = {
	/** @returns {void} */
	init: function init() {
		multiselect.clearSelection();

		view.photo.hide();
		view.notifications.title();
		header.setMode("config");
		view.notifications.content.init();
	},

	/** @returns {void} */
	title: function title() {
		lychee.setTitle("Notifications", false);
	},

	/** @returns {void} */
	clearContent: function clearContent() {
		lychee.content.html('<div class="settings_view"></div>');
	},

	content: {
		/** @returns {void} */
		init: function init() {
			view.notifications.clearContent();

			var html = "\n\t\t\t\t<div class=\"setting_line\">\n\t\t\t\t\t<p>" + lychee.locale["USER_EMAIL_INSTRUCTION"] + "</p>\n\t\t\t\t</div><div class=\"setLogin\">\n\t\t\t\t\t<p id=\"UserUpdate\">\n\t\t\t\t\t\tEnter your email address:\n\t\t\t\t\t\t<input\n\t\t\t\t\t\t\tname=\"email\" class=\"text\" type=\"text\"\n\t\t\t\t\t\t\tplaceholder=\"email@example.com\"\n\t\t\t\t\t\t\tvalue=\"" + (notifications.json && notifications.json.email ? notifications.json.email : "") + "\"\n\t\t\t\t\t\t>\n\t\t\t\t\t</p>\n\t\t\t\t\t<div class=\"basicModal__buttons\">\n\t\t\t\t\t\t<a id=\"UserUpdate_button\" class=\"basicModal__button\">Save</a>\n\t\t\t\t\t</div>\n\t\t\t\t</div>";

			$(".settings_view").append(html);
			settings.bind("#UserUpdate_button", "#UserUpdate", notifications.update);
		}
	}
};

view.users = {
	/** @returns {void} */
	init: function init() {
		multiselect.clearSelection();

		view.photo.hide();
		view.users.title();
		header.setMode("config");
		view.users.content.init();
	},

	/** @returns {void} */
	title: function title() {
		lychee.setTitle("Users", false);
	},

	/** @returns {void} */
	clearContent: function clearContent() {
		lychee.content.html('<div class="users_view"></div>');
	},

	content: {
		/** @returns {void} */
		init: function init() {
			view.users.clearContent();

			if (users.json.length === 0) {
				$(".users_view").append('<div class="users_view_line" style="margin-bottom: 50px;"><p style="text-align: center">User list is empty!</p></div>');
			}

			var html = "\n\t\t\t\t<div class=\"users_view_line\"><p>\n\t\t\t\t\t<span class=\"text\">username</span>\n\t\t\t\t\t<span class=\"text\">new password</span>\n\t\t\t\t\t<span class=\"text_icon\" title=\"Allow uploads\">\n\t\t\t\t\t\t" + build.iconic("data-transfer-upload") + "\n\t\t\t\t\t</span>\n\t\t\t\t\t<span class=\"text_icon\" title=\"Restricted account\">\n\t\t\t\t\t\t" + build.iconic("lock-locked") + "\n\t\t\t\t\t</span>\n\t\t\t\t</p></div>";

			$(".users_view").append(html);

			users.json.forEach(function (_user) {
				$(".users_view").append(build.user(_user));
				// TODO: Instead of binding an event handler to each input element it would be much more efficient, to bind a single event handler to the common parent view, let the event bubble up the DOM tree and use the `originalElement` property of the event to get the input element which caused the event.
				settings.bind("#UserUpdate" + _user.id, "#UserData" + _user.id, users.update);
				settings.bind("#UserDelete" + _user.id, "#UserData" + _user.id, users.delete);
				if (_user.may_upload) {
					$("#UserData" + _user.id + ' .choice input[name="may_upload"]').click();
				}
				if (_user.is_locked) {
					$("#UserData" + _user.id + ' .choice input[name="is_locked"]').click();
				}
			});

			html = "\n\t\t\t\t<div class=\"users_view_line\" " + (users.json.length === 0 ? 'style="padding-top: 0px;"' : "") + ">\n\t\t\t\t\t<p id=\"UserCreate\">\n\t\t\t\t\t\t<input class=\"text\" name=\"username\" type=\"text\" value=\"\" placeholder=\"new username\" />\n\t\t\t\t\t\t<input class=\"text\" name=\"password\" type=\"text\" placeholder=\"new password\" />\n\t\t\t\t\t\t<span class=\"choice\" title=\"Allow uploads\">\n\t\t\t\t\t\t\t<label>\n\t\t\t\t\t\t\t\t<input type=\"checkbox\" name=\"may_upload\" />\n\t\t\t\t\t\t\t\t<span class=\"checkbox\"><svg class=\"iconic \"><use xlink:href=\"#check\"></use></svg></span>\n\t\t\t\t\t\t\t</label>\n\t\t\t\t\t\t</span>\n\t\t\t\t\t\t<span class=\"choice\" title=\"Restricted account\">\n\t\t\t\t\t\t\t<label>\n\t\t\t\t\t\t\t\t<input type=\"checkbox\" name=\"is_locked\" />\n\t\t\t\t\t\t\t\t<span class=\"checkbox\"><svg class=\"iconic \"><use xlink:href=\"#check\"></use></svg></span>\n\t\t\t\t\t\t\t</label>\n\t\t\t\t\t\t</span>\n\t\t\t\t\t</p>\n\t\t\t\t\t<a id=\"UserCreate_button\"  class=\"basicModal__button basicModal__button_CREATE\">Create</a>\n\t\t\t\t</div>";
			$(".users_view").append(html);
			settings.bind("#UserCreate_button", "#UserCreate", users.create);
		}
	}
};

view.sharing = {
	/** @returns {void} */
	init: function init() {
		multiselect.clearSelection();

		view.photo.hide();
		view.sharing.title();
		header.setMode("config");
		view.sharing.content.init();
	},

	/** @returns {void} */
	title: function title() {
		lychee.setTitle("Sharing", false);
	},

	/** @returns {void} */
	clearContent: function clearContent() {
		lychee.content.html('<div class="sharing_view"></div>');
	},

	content: {
		/** @returns {void} */
		init: function init() {
			view.sharing.clearContent();

			if (sharing.json.shared.length === 0) {
				$(".sharing_view").append('<div class="sharing_view_line" style="margin-bottom: 50px;"><p style="text-align: center">Sharing list is empty!</p></div>');
			}

			var albumOptions = sharing.json.albums.reduce(function (acc, _album) {
				return acc + ("<option value=\"" + _album.id + "\">" + _album.title + "</option>");
			}, "");

			var userOptions = sharing.json.users.reduce(function (acc, _user) {
				return acc + ("<option value=\"" + _user.id + "\">" + _user.username + "</option>");
			}, "");

			var sharingOptions = sharing.json.shared.reduce(function (acc, _shareInfo) {
				return acc + ("\n\t\t\t\t\t\t<p>\n\t\t\t\t\t\t\t<span class=\"text\">" + _shareInfo.title + "</span>\n\t\t\t\t\t\t\t<span class=\"text\">" + _shareInfo.username + "</span>\n\t\t\t\t\t\t\t<span class=\"choice\">\n\t\t\t\t\t\t\t\t<label>\n\t\t\t\t\t\t\t\t\t<input type=\"checkbox\" name=\"remove_id\" value=\"" + _shareInfo.id + "\"/>\n\t\t\t\t\t\t\t\t\t<span class=\"checkbox\">\n\t\t\t\t\t\t\t\t\t\t<svg class=\"iconic \"><use xlink:href=\"#check\"></use></svg>\n\t\t\t\t\t\t\t\t\t</span>\n\t\t\t\t\t\t\t\t</label>\n\t\t\t\t\t\t\t</span>\n\t\t\t\t\t\t</p>");
			}, "");

			var html = "\n\t\t\t\t<div class=\"sharing_view_line\"><p>Share</p></div>\n\t\t\t\t<div class=\"sharing_view_line\">\n\t\t\t\t\t<div class=\"col-xs-5\">\n\t\t\t\t\t\t<select name=\"from\" id=\"albums_list\" class=\"form-control select\" size=\"13\" multiple=\"multiple\">\n\t\t\t\t\t\t\t" + albumOptions + "\n\t\t\t\t\t\t</select>\n\t\t\t\t\t</div>\n\t\t\t\t\t<div class=\"col-xs-2\">\n\t\t\t\t\t\t<!--<button type=\"button\" id=\"albums_list_undo\" class=\"btn btn-primary btn-block\">undo</button>-->\n\t\t\t\t\t\t<button type=\"button\" id=\"albums_list_rightAll\" class=\"btn btn-default btn-block blue\">\n\t\t\t\t\t\t\t" + build.iconic("media-skip-forward") + "\n\t\t\t\t\t\t</button>\n\t\t\t\t\t\t<button type=\"button\" id=\"albums_list_rightSelected\" class=\"btn btn-default btn-block blue\">\n\t\t\t\t\t\t\t" + build.iconic("chevron-right") + "\n\t\t\t\t\t\t</button>\n\t\t\t\t\t\t<button type=\"button\" id=\"albums_list_leftSelected\" class=\"btn btn-default btn-block grey\">\n\t\t\t\t\t\t\t" + build.iconic("chevron-left") + "\n\t\t\t\t\t\t</button>\n\t\t\t\t\t\t<button type=\"button\" id=\"albums_list_leftAll\" class=\"btn btn-default btn-block grey\">\n\t\t\t\t\t\t\t" + build.iconic("media-skip-backward") + "\n\t\t\t\t\t\t</button>\n\t\t\t\t\t\t<!--<button type=\"button\" id=\"albums_list_redo\" class=\"btn btn-warning btn-block\">redo</button>-->\n\t\t\t\t\t</div>\n\t\t\t\t\t<div class=\"col-xs-5\">\n\t\t\t\t\t\t<select name=\"to\" id=\"albums_list_to\" class=\"form-control select\" size=\"13\" multiple=\"multiple\"></select>\n\t\t\t\t\t</div>\n\t\t\t\t</div>\n\t\t\t\t<div class=\"sharing_view_line\"><p class=\"with\">with</p></div>\n\t\t\t\t<div class=\"sharing_view_line\">\n\t\t\t\t\t<div class=\"col-xs-5\">\n\t\t\t\t\t\t<select name=\"from\" id=\"user_list\" class=\"form-control select\" size=\"13\" multiple=\"multiple\">\n\t\t\t\t\t\t\t" + userOptions + "\n\t\t\t\t\t\t</select>\n\t\t\t\t\t</div>\n\t\t\t\t\t<div class=\"col-xs-2\">\n\t\t\t\t\t\t<!--<button type=\"button\" id=\"user_list_undo\" class=\"btn btn-primary btn-block\">undo</button>-->\n\t\t\t\t\t\t<button type=\"button\" id=\"user_list_rightAll\" class=\"btn btn-default btn-block blue\">\n\t\t\t\t\t\t\t" + build.iconic("media-skip-forward") + "\n\t\t\t\t\t\t</button>\n\t\t\t\t\t\t<button type=\"button\" id=\"user_list_rightSelected\" class=\"btn btn-default btn-block blue\">\n\t\t\t\t\t\t\t" + build.iconic("chevron-right") + "\n\t\t\t\t\t\t</button>\n\t\t\t\t\t\t<button type=\"button\" id=\"user_list_leftSelected\" class=\"btn btn-default btn-block grey\">\n\t\t\t\t\t\t\t" + build.iconic("chevron-left") + "\n\t\t\t\t\t\t</button>\n\t\t\t\t\t\t<button type=\"button\" id=\"user_list_leftAll\" class=\"btn btn-default btn-block grey\">\n\t\t\t\t\t\t\t" + build.iconic("media-skip-backward") + "\n\t\t\t\t\t\t</button>\n\t\t\t\t\t\t<!--<button type=\"button\" id=\"user_list_redo\" class=\"btn btn-warning btn-block\">redo</button>-->\n\t\t\t\t\t</div>\n\t\t\t\t\t<div class=\"col-xs-5\">\n\t\t\t\t\t\t<select name=\"to\" id=\"user_list_to\" class=\"form-control select\" size=\"13\" multiple=\"multiple\"></select>\n\t\t\t\t\t</div>\n\t\t\t\t</div>\n\t\t\t\t<div class=\"sharing_view_line\"><a id=\"Share_button\" class=\"basicModal__button\">Share</a></div>\n\t\t\t\t<div class=\"sharing_view_line\">\n\t\t\t\t\t" + sharingOptions + "\n\t\t\t\t</div>";

			if (sharing.json.shared.length !== 0) {
				html += '<div class="sharing_view_line"><a id="Remove_button"  class="basicModal__button">Remove</a></div>';
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
	/** @returns {void} */
	init: function init() {
		multiselect.clearSelection();

		view.photo.hide();
		view.logs.title();
		header.setMode("config");
		view.logs.content.init();
	},

	/** @returns {void} */
	title: function title() {
		lychee.setTitle("Logs", false);
	},

	/** @returns {void} */
	clearContent: function clearContent() {
		var html = lychee.html(_templateObject86, lychee.locale["CLEAN_LOGS"]);
		lychee.content.html(html);

		$("#Clean_Noise").on("click", function () {
			api.post("Logs::clearNoise", {}, view.logs.init);
		});
	},

	content: {
		/** @returns {void} */
		init: function init() {
			/**
    * @param {LogEntry[]} logEntries
    * @returns {void}
    */
			var successHandler = function successHandler(logEntries) {
				/**
     * TODO: Consider moving this method to `lychee.locale`
     * @param {Date} datetime
     * @returns {string}
     */
				var formatDateTime = function formatDateTime(datetime) {
					return "" + datetime.getUTCFullYear() + "-" + String(datetime.getUTCMonth() + 1).padStart(2, "0") + "-" + String(datetime.getUTCDate()).padStart(2, "0") + " " + String(datetime.getUTCHours()).padStart(2, "0") + ":" + String(datetime.getUTCMinutes()).padStart(2, "0") + ":" + String(datetime.getUTCSeconds()).padStart(2, "0") + " UTC";
				};
				var html = logEntries.reduce(function (acc, logEntry) {
					return acc + formatDateTime(new Date(logEntry.created_at)) + " -- " + logEntry.type.padEnd(7) + " -- " + logEntry.function + " -- " + logEntry.line + " -- " + logEntry.text + "\n";
				}, "<pre>") + "</pre>";
				$(".logs_diagnostics_view").html(html);
			};

			view.logs.clearContent();
			api.post("Logs::list", {}, successHandler);
		}
	}
};

view.diagnostics = {
	/** @returns {void} */
	init: function init() {
		multiselect.clearSelection();

		view.photo.hide();
		view.diagnostics.title();
		header.setMode("config");
		view.diagnostics.content.init();
	},

	/** @returns {void} */
	title: function title() {
		lychee.setTitle("Diagnostics", false);
	},

	/**
  * @param {number} update - The update status: `0`: not on master branch;
  *                          `1`: up-to-date; `2`: not up-to-date;
  *                          `3`: requires migration
  * @returns {void}
  */
	clearContent: function clearContent(update) {
		var html = "";

		if (update === 2) {
			html = view.diagnostics.button("", lychee.locale["UPDATE_AVAILABLE"]);
		} else if (update === 3) {
			html = view.diagnostics.button("", lychee.locale["MIGRATION_AVAILABLE"]);
		} else if (update > 0) {
			html = view.diagnostics.button("Check_", lychee.locale["CHECK_FOR_UPDATE"]);
		}

		html += '<pre class="logs_diagnostics_view"></pre>';
		lychee.content.html(html);
	},

	/**
  * @param {string} type
  * @param {string} locale
  * @returns {string} - HTML
  */
	button: function button(type, locale) {
		return "\n\t\t\t<div class=\"clear_logs_update\">\n\t\t\t\t<a id=\"" + type + "Update_Lychee\" class=\"basicModal__button\">" + locale + "</a>\n\t\t\t</div>";
	},

	/** @returns {string} */
	bind: function bind() {
		$("#Update_Lychee").on("click", view.diagnostics.call_apply_update);
		$("#Check_Update_Lychee").on("click", view.diagnostics.call_check_update);
	},

	content: {
		/** @returns {void} */
		init: function init() {
			view.diagnostics.clearContent(0);
			api.post("Diagnostics::get", {}, view.diagnostics.content.parseResponse);
		},

		/**
   * @param {DiagnosticInfo} data
   * @returns {void}
   */
		parseResponse: function parseResponse(data) {
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
		},

		/**
   * @param {string} id
   * @param {string} title
   * @param {string[]} arr
   * @returns {string} - HTML
   */
		block: function block(id, title, arr) {
			var html = "";
			html += '<pre id="content_diag_' + id + '">\n\n\n\n';
			html += "    " + title + "\n";
			html += "    ".padEnd(title.length, "-") + "\n";
			html += arr.reduce(function (acc, line) {
				return acc + "    " + line + "\n";
			}, "");
			html += "</pre>\n";
			return html;
		}
	},

	/** @returns {void} */
	call_check_update: function call_check_update() {
		api.post("Update::check", {},
		/** @param {{updateStatus: string}} data */
		function (data) {
			loadingBar.show("success", data.updateStatus);
			$("#Check_Update_Lychee").remove();
		});
	},

	/** @returns {void} */
	call_apply_update: function call_apply_update() {
		api.post("Update::apply", {},
		/** @param {{updateMsgs: string[]}} data */
		function (data) {
			var html = view.preify(data.updateMsgs, "");
			$("#Update_Lychee").remove();
			$(html).prependTo(".logs_diagnostics_view");
		});
	},

	/** @returns {void} */
	call_get_size: function call_get_size() {
		api.post("Diagnostics::getSize", {},
		/** @param {string[]} data */
		function (data) {
			var html = view.preify(data, "");
			$("#Get_Size_Lychee").remove();
			$(html).appendTo("#content_diag_sys");
		});
	}
};

view.update = {
	/** @returns {void} */
	init: function init() {
		multiselect.clearSelection();

		view.photo.hide();
		view.update.title();
		header.setMode("config");
		view.update.content.init();
	},

	/** @returns {void} */
	title: function title() {
		lychee.setTitle("Update", false);
	},

	/** @returns {void} */
	clearContent: function clearContent() {
		var html = '<pre class="logs_diagnostics_view"></pre>';
		lychee.content.html(html);
	},

	content: {
		init: function init() {
			view.update.clearContent();

			// code duplicate
			api.post("Update::apply", {},
			/** @param {{updateMsgs: string[]}} data */
			function (data) {
				var html = view.preify(data.updateMsgs, "");
				lychee.content.html(html);
			});
		}
	}
};

/**
 * @param {string[]} data
 * @param {string} cssClass
 * @returns {string} - HTML which wraps `data` into a `<pre>`-tag
 */
view.preify = function (data, cssClass) {
	return data.reduce(function (acc, line) {
		return acc + "    " + line + "\n";
	}, '<pre class="' + cssClass + '">') + "</pre>";
};

view.u2f = {
	/** @returns {void} */
	init: function init() {
		multiselect.clearSelection();

		view.photo.hide();
		view.u2f.title();
		header.setMode("config");
		view.u2f.content.init();
	},

	/** @returns {void} */
	title: function title() {
		lychee.setTitle(lychee.locale["U2F"], false);
	},

	/** @returns {void} */
	clearContent: function clearContent() {
		lychee.content.html('<div class="u2f_view"></div>');
	},

	content: {
		/** @returns {void} */
		init: function init() {
			view.u2f.clearContent();

			if (u2f.json.length === 0) {
				$(".u2f_view").append('<div class="u2f_view_line"><p class="single">Credentials list is empty!</p></div>');
			} else {
				var _html2 = "\n\t\t\t\t\t<div class=\"u2f_view_line\"><p><span class=\"text\">\n\t\t\t\t\t\t" + lychee.locale["U2F_CREDENTIALS"] + "\n\t\t\t\t\t</span></p></div>";

				$(".u2f_view").append(_html2);

				u2f.json.forEach(function (credential) {
					// TODO: Don't query the DOM for the same element in each loop iteration
					$(".u2f_view").append(build.u2f(credential));
					settings.bind("#CredentialDelete" + credential.id, "#CredentialData" + credential.id, u2f.delete);
				});
			}

			var html = "\n\t\t\t\t<div class=\"u2f_view_line\">\n\t\t\t\t\t<a id=\"RegisterU2FButton\"  class=\"basicModal__button basicModal__button_CREATE\">\n\t\t\t\t\t\t" + lychee.locale["U2F_REGISTER_KEY"] + "\n\t\t\t\t\t</a>\n\t\t\t\t</div>";
			$(".u2f_view").append(html);
			$("#RegisterU2FButton").on("click", u2f.register);
		}
	}
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
	return !!_sidebar.dom().hasClass("active");
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