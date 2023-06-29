/******/ (() => { // webpackBootstrap
var __webpack_exports__ = {};
/*!**************************************************!*\
  !*** ./resources/js/vendor/webauthn/webauthn.js ***!
  \**************************************************/
function _typeof(obj) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (obj) { return typeof obj; } : function (obj) { return obj && "function" == typeof Symbol && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }, _typeof(obj); }
function _toConsumableArray(arr) { return _arrayWithoutHoles(arr) || _iterableToArray(arr) || _unsupportedIterableToArray(arr) || _nonIterableSpread(); }
function _nonIterableSpread() { throw new TypeError("Invalid attempt to spread non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }
function _unsupportedIterableToArray(o, minLen) { if (!o) return; if (typeof o === "string") return _arrayLikeToArray(o, minLen); var n = Object.prototype.toString.call(o).slice(8, -1); if (n === "Object" && o.constructor) n = o.constructor.name; if (n === "Map" || n === "Set") return Array.from(o); if (n === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)) return _arrayLikeToArray(o, minLen); }
function _iterableToArray(iter) { if (typeof Symbol !== "undefined" && iter[Symbol.iterator] != null || iter["@@iterator"] != null) return Array.from(iter); }
function _arrayWithoutHoles(arr) { if (Array.isArray(arr)) return _arrayLikeToArray(arr); }
function _arrayLikeToArray(arr, len) { if (len == null || len > arr.length) len = arr.length; for (var i = 0, arr2 = new Array(len); i < len; i++) arr2[i] = arr[i]; return arr2; }
function ownKeys(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); enumerableOnly && (symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; })), keys.push.apply(keys, symbols); } return keys; }
function _objectSpread(target) { for (var i = 1; i < arguments.length; i++) { var source = null != arguments[i] ? arguments[i] : {}; i % 2 ? ownKeys(Object(source), !0).forEach(function (key) { _defineProperty(target, key, source[key]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)) : ownKeys(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } return target; }
function _defineProperty(obj, key, value) { key = _toPropertyKey(key); if (key in obj) { Object.defineProperty(obj, key, { value: value, enumerable: true, configurable: true, writable: true }); } else { obj[key] = value; } return obj; }
function _regeneratorRuntime() { "use strict"; /*! regenerator-runtime -- Copyright (c) 2014-present, Facebook, Inc. -- license (MIT): https://github.com/facebook/regenerator/blob/main/LICENSE */ _regeneratorRuntime = function _regeneratorRuntime() { return exports; }; var exports = {}, Op = Object.prototype, hasOwn = Op.hasOwnProperty, defineProperty = Object.defineProperty || function (obj, key, desc) { obj[key] = desc.value; }, $Symbol = "function" == typeof Symbol ? Symbol : {}, iteratorSymbol = $Symbol.iterator || "@@iterator", asyncIteratorSymbol = $Symbol.asyncIterator || "@@asyncIterator", toStringTagSymbol = $Symbol.toStringTag || "@@toStringTag"; function define(obj, key, value) { return Object.defineProperty(obj, key, { value: value, enumerable: !0, configurable: !0, writable: !0 }), obj[key]; } try { define({}, ""); } catch (err) { define = function define(obj, key, value) { return obj[key] = value; }; } function wrap(innerFn, outerFn, self, tryLocsList) { var protoGenerator = outerFn && outerFn.prototype instanceof Generator ? outerFn : Generator, generator = Object.create(protoGenerator.prototype), context = new Context(tryLocsList || []); return defineProperty(generator, "_invoke", { value: makeInvokeMethod(innerFn, self, context) }), generator; } function tryCatch(fn, obj, arg) { try { return { type: "normal", arg: fn.call(obj, arg) }; } catch (err) { return { type: "throw", arg: err }; } } exports.wrap = wrap; var ContinueSentinel = {}; function Generator() {} function GeneratorFunction() {} function GeneratorFunctionPrototype() {} var IteratorPrototype = {}; define(IteratorPrototype, iteratorSymbol, function () { return this; }); var getProto = Object.getPrototypeOf, NativeIteratorPrototype = getProto && getProto(getProto(values([]))); NativeIteratorPrototype && NativeIteratorPrototype !== Op && hasOwn.call(NativeIteratorPrototype, iteratorSymbol) && (IteratorPrototype = NativeIteratorPrototype); var Gp = GeneratorFunctionPrototype.prototype = Generator.prototype = Object.create(IteratorPrototype); function defineIteratorMethods(prototype) { ["next", "throw", "return"].forEach(function (method) { define(prototype, method, function (arg) { return this._invoke(method, arg); }); }); } function AsyncIterator(generator, PromiseImpl) { function invoke(method, arg, resolve, reject) { var record = tryCatch(generator[method], generator, arg); if ("throw" !== record.type) { var result = record.arg, value = result.value; return value && "object" == _typeof(value) && hasOwn.call(value, "__await") ? PromiseImpl.resolve(value.__await).then(function (value) { invoke("next", value, resolve, reject); }, function (err) { invoke("throw", err, resolve, reject); }) : PromiseImpl.resolve(value).then(function (unwrapped) { result.value = unwrapped, resolve(result); }, function (error) { return invoke("throw", error, resolve, reject); }); } reject(record.arg); } var previousPromise; defineProperty(this, "_invoke", { value: function value(method, arg) { function callInvokeWithMethodAndArg() { return new PromiseImpl(function (resolve, reject) { invoke(method, arg, resolve, reject); }); } return previousPromise = previousPromise ? previousPromise.then(callInvokeWithMethodAndArg, callInvokeWithMethodAndArg) : callInvokeWithMethodAndArg(); } }); } function makeInvokeMethod(innerFn, self, context) { var state = "suspendedStart"; return function (method, arg) { if ("executing" === state) throw new Error("Generator is already running"); if ("completed" === state) { if ("throw" === method) throw arg; return doneResult(); } for (context.method = method, context.arg = arg;;) { var delegate = context.delegate; if (delegate) { var delegateResult = maybeInvokeDelegate(delegate, context); if (delegateResult) { if (delegateResult === ContinueSentinel) continue; return delegateResult; } } if ("next" === context.method) context.sent = context._sent = context.arg;else if ("throw" === context.method) { if ("suspendedStart" === state) throw state = "completed", context.arg; context.dispatchException(context.arg); } else "return" === context.method && context.abrupt("return", context.arg); state = "executing"; var record = tryCatch(innerFn, self, context); if ("normal" === record.type) { if (state = context.done ? "completed" : "suspendedYield", record.arg === ContinueSentinel) continue; return { value: record.arg, done: context.done }; } "throw" === record.type && (state = "completed", context.method = "throw", context.arg = record.arg); } }; } function maybeInvokeDelegate(delegate, context) { var methodName = context.method, method = delegate.iterator[methodName]; if (undefined === method) return context.delegate = null, "throw" === methodName && delegate.iterator["return"] && (context.method = "return", context.arg = undefined, maybeInvokeDelegate(delegate, context), "throw" === context.method) || "return" !== methodName && (context.method = "throw", context.arg = new TypeError("The iterator does not provide a '" + methodName + "' method")), ContinueSentinel; var record = tryCatch(method, delegate.iterator, context.arg); if ("throw" === record.type) return context.method = "throw", context.arg = record.arg, context.delegate = null, ContinueSentinel; var info = record.arg; return info ? info.done ? (context[delegate.resultName] = info.value, context.next = delegate.nextLoc, "return" !== context.method && (context.method = "next", context.arg = undefined), context.delegate = null, ContinueSentinel) : info : (context.method = "throw", context.arg = new TypeError("iterator result is not an object"), context.delegate = null, ContinueSentinel); } function pushTryEntry(locs) { var entry = { tryLoc: locs[0] }; 1 in locs && (entry.catchLoc = locs[1]), 2 in locs && (entry.finallyLoc = locs[2], entry.afterLoc = locs[3]), this.tryEntries.push(entry); } function resetTryEntry(entry) { var record = entry.completion || {}; record.type = "normal", delete record.arg, entry.completion = record; } function Context(tryLocsList) { this.tryEntries = [{ tryLoc: "root" }], tryLocsList.forEach(pushTryEntry, this), this.reset(!0); } function values(iterable) { if (iterable) { var iteratorMethod = iterable[iteratorSymbol]; if (iteratorMethod) return iteratorMethod.call(iterable); if ("function" == typeof iterable.next) return iterable; if (!isNaN(iterable.length)) { var i = -1, next = function next() { for (; ++i < iterable.length;) if (hasOwn.call(iterable, i)) return next.value = iterable[i], next.done = !1, next; return next.value = undefined, next.done = !0, next; }; return next.next = next; } } return { next: doneResult }; } function doneResult() { return { value: undefined, done: !0 }; } return GeneratorFunction.prototype = GeneratorFunctionPrototype, defineProperty(Gp, "constructor", { value: GeneratorFunctionPrototype, configurable: !0 }), defineProperty(GeneratorFunctionPrototype, "constructor", { value: GeneratorFunction, configurable: !0 }), GeneratorFunction.displayName = define(GeneratorFunctionPrototype, toStringTagSymbol, "GeneratorFunction"), exports.isGeneratorFunction = function (genFun) { var ctor = "function" == typeof genFun && genFun.constructor; return !!ctor && (ctor === GeneratorFunction || "GeneratorFunction" === (ctor.displayName || ctor.name)); }, exports.mark = function (genFun) { return Object.setPrototypeOf ? Object.setPrototypeOf(genFun, GeneratorFunctionPrototype) : (genFun.__proto__ = GeneratorFunctionPrototype, define(genFun, toStringTagSymbol, "GeneratorFunction")), genFun.prototype = Object.create(Gp), genFun; }, exports.awrap = function (arg) { return { __await: arg }; }, defineIteratorMethods(AsyncIterator.prototype), define(AsyncIterator.prototype, asyncIteratorSymbol, function () { return this; }), exports.AsyncIterator = AsyncIterator, exports.async = function (innerFn, outerFn, self, tryLocsList, PromiseImpl) { void 0 === PromiseImpl && (PromiseImpl = Promise); var iter = new AsyncIterator(wrap(innerFn, outerFn, self, tryLocsList), PromiseImpl); return exports.isGeneratorFunction(outerFn) ? iter : iter.next().then(function (result) { return result.done ? result.value : iter.next(); }); }, defineIteratorMethods(Gp), define(Gp, toStringTagSymbol, "Generator"), define(Gp, iteratorSymbol, function () { return this; }), define(Gp, "toString", function () { return "[object Generator]"; }), exports.keys = function (val) { var object = Object(val), keys = []; for (var key in object) keys.push(key); return keys.reverse(), function next() { for (; keys.length;) { var key = keys.pop(); if (key in object) return next.value = key, next.done = !1, next; } return next.done = !0, next; }; }, exports.values = values, Context.prototype = { constructor: Context, reset: function reset(skipTempReset) { if (this.prev = 0, this.next = 0, this.sent = this._sent = undefined, this.done = !1, this.delegate = null, this.method = "next", this.arg = undefined, this.tryEntries.forEach(resetTryEntry), !skipTempReset) for (var name in this) "t" === name.charAt(0) && hasOwn.call(this, name) && !isNaN(+name.slice(1)) && (this[name] = undefined); }, stop: function stop() { this.done = !0; var rootRecord = this.tryEntries[0].completion; if ("throw" === rootRecord.type) throw rootRecord.arg; return this.rval; }, dispatchException: function dispatchException(exception) { if (this.done) throw exception; var context = this; function handle(loc, caught) { return record.type = "throw", record.arg = exception, context.next = loc, caught && (context.method = "next", context.arg = undefined), !!caught; } for (var i = this.tryEntries.length - 1; i >= 0; --i) { var entry = this.tryEntries[i], record = entry.completion; if ("root" === entry.tryLoc) return handle("end"); if (entry.tryLoc <= this.prev) { var hasCatch = hasOwn.call(entry, "catchLoc"), hasFinally = hasOwn.call(entry, "finallyLoc"); if (hasCatch && hasFinally) { if (this.prev < entry.catchLoc) return handle(entry.catchLoc, !0); if (this.prev < entry.finallyLoc) return handle(entry.finallyLoc); } else if (hasCatch) { if (this.prev < entry.catchLoc) return handle(entry.catchLoc, !0); } else { if (!hasFinally) throw new Error("try statement without catch or finally"); if (this.prev < entry.finallyLoc) return handle(entry.finallyLoc); } } } }, abrupt: function abrupt(type, arg) { for (var i = this.tryEntries.length - 1; i >= 0; --i) { var entry = this.tryEntries[i]; if (entry.tryLoc <= this.prev && hasOwn.call(entry, "finallyLoc") && this.prev < entry.finallyLoc) { var finallyEntry = entry; break; } } finallyEntry && ("break" === type || "continue" === type) && finallyEntry.tryLoc <= arg && arg <= finallyEntry.finallyLoc && (finallyEntry = null); var record = finallyEntry ? finallyEntry.completion : {}; return record.type = type, record.arg = arg, finallyEntry ? (this.method = "next", this.next = finallyEntry.finallyLoc, ContinueSentinel) : this.complete(record); }, complete: function complete(record, afterLoc) { if ("throw" === record.type) throw record.arg; return "break" === record.type || "continue" === record.type ? this.next = record.arg : "return" === record.type ? (this.rval = this.arg = record.arg, this.method = "return", this.next = "end") : "normal" === record.type && afterLoc && (this.next = afterLoc), ContinueSentinel; }, finish: function finish(finallyLoc) { for (var i = this.tryEntries.length - 1; i >= 0; --i) { var entry = this.tryEntries[i]; if (entry.finallyLoc === finallyLoc) return this.complete(entry.completion, entry.afterLoc), resetTryEntry(entry), ContinueSentinel; } }, "catch": function _catch(tryLoc) { for (var i = this.tryEntries.length - 1; i >= 0; --i) { var entry = this.tryEntries[i]; if (entry.tryLoc === tryLoc) { var record = entry.completion; if ("throw" === record.type) { var thrown = record.arg; resetTryEntry(entry); } return thrown; } } throw new Error("illegal catch attempt"); }, delegateYield: function delegateYield(iterable, resultName, nextLoc) { return this.delegate = { iterator: values(iterable), resultName: resultName, nextLoc: nextLoc }, "next" === this.method && (this.arg = undefined), ContinueSentinel; } }, exports; }
function asyncGeneratorStep(gen, resolve, reject, _next, _throw, key, arg) { try { var info = gen[key](arg); var value = info.value; } catch (error) { reject(error); return; } if (info.done) { resolve(value); } else { Promise.resolve(value).then(_next, _throw); } }
function _asyncToGenerator(fn) { return function () { var self = this, args = arguments; return new Promise(function (resolve, reject) { var gen = fn.apply(self, args); function _next(value) { asyncGeneratorStep(gen, resolve, reject, _next, _throw, "next", value); } function _throw(err) { asyncGeneratorStep(gen, resolve, reject, _next, _throw, "throw", err); } _next(undefined); }); }; }
function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }
function _defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, _toPropertyKey(descriptor.key), descriptor); } }
function _createClass(Constructor, protoProps, staticProps) { if (protoProps) _defineProperties(Constructor.prototype, protoProps); if (staticProps) _defineProperties(Constructor, staticProps); Object.defineProperty(Constructor, "prototype", { writable: false }); return Constructor; }
function _toPropertyKey(arg) { var key = _toPrimitive(arg, "string"); return _typeof(key) === "symbol" ? key : String(key); }
function _toPrimitive(input, hint) { if (_typeof(input) !== "object" || input === null) return input; var prim = input[Symbol.toPrimitive]; if (prim !== undefined) { var res = prim.call(input, hint || "default"); if (_typeof(res) !== "object") return res; throw new TypeError("@@toPrimitive must return a primitive value."); } return (hint === "string" ? String : Number)(input); }
function _classPrivateMethodInitSpec(obj, privateSet) { _checkPrivateRedeclaration(obj, privateSet); privateSet.add(obj); }
function _classPrivateFieldInitSpec(obj, privateMap, value) { _checkPrivateRedeclaration(obj, privateMap); privateMap.set(obj, value); }
function _checkPrivateRedeclaration(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }
function _classPrivateMethodGet(receiver, privateSet, fn) { if (!privateSet.has(receiver)) { throw new TypeError("attempted to get private field on non-instance"); } return fn; }
function _classStaticPrivateMethodGet(receiver, classConstructor, method) { _classCheckPrivateStaticAccess(receiver, classConstructor); return method; }
function _classStaticPrivateFieldSpecGet(receiver, classConstructor, descriptor) { _classCheckPrivateStaticAccess(receiver, classConstructor); _classCheckPrivateStaticFieldDescriptor(descriptor, "get"); return _classApplyDescriptorGet(receiver, descriptor); }
function _classCheckPrivateStaticFieldDescriptor(descriptor, action) { if (descriptor === undefined) { throw new TypeError("attempted to " + action + " private static field before its declaration"); } }
function _classCheckPrivateStaticAccess(receiver, classConstructor) { if (receiver !== classConstructor) { throw new TypeError("Private static access of wrong provenance"); } }
function _classPrivateFieldSet(receiver, privateMap, value) { var descriptor = _classExtractFieldDescriptor(receiver, privateMap, "set"); _classApplyDescriptorSet(receiver, descriptor, value); return value; }
function _classApplyDescriptorSet(receiver, descriptor, value) { if (descriptor.set) { descriptor.set.call(receiver, value); } else { if (!descriptor.writable) { throw new TypeError("attempted to set read only private field"); } descriptor.value = value; } }
function _classPrivateFieldGet(receiver, privateMap) { var descriptor = _classExtractFieldDescriptor(receiver, privateMap, "get"); return _classApplyDescriptorGet(receiver, descriptor); }
function _classExtractFieldDescriptor(receiver, privateMap, action) { if (!privateMap.has(receiver)) { throw new TypeError("attempted to " + action + " private field on non-instance"); } return privateMap.get(receiver); }
function _classApplyDescriptorGet(receiver, descriptor) { if (descriptor.get) { return descriptor.get.call(receiver); } return descriptor.value; }
var _routes = /*#__PURE__*/new WeakMap();
var _headers = /*#__PURE__*/new WeakMap();
var _includeCredentials = /*#__PURE__*/new WeakMap();
var _fetch = /*#__PURE__*/new WeakSet();
var _parseIncomingServerOptions = /*#__PURE__*/new WeakSet();
var _parseOutgoingCredentials = /*#__PURE__*/new WeakSet();
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
var WebAuthn = /*#__PURE__*/function () {
  /**
   * Routes for WebAuthn assertion (login) and attestation (register).
   *
   * @type {{registerOptions: string, register: string, loginOptions: string, login: string, }}
   */

  /**
   * Headers to use in ALL requests done.
   *
   * @type {{Accept: string, "Content-Type": string, "X-Requested-With": string}}
   */

  /**
   * If set to true, the credentials option will be set to 'include' on all fetch calls,
   * or else it will use the default 'same-origin'. Use this if the backend is not the
   * same origin as the client or the XSRF protection will break without the session.
   *
   * @type {boolean}
   */

  /**
   * Create a new WebAuthn instance.
   *
   * @param routes {{registerOptions: string, register: string, loginOptions: string, login: string}}
   * @param headers {{string}}
   * @param includeCredentials {boolean}
   * @param xcsrfToken {string|null} Either a csrf token (40 chars) or xsrfToken (224 chars)
   */
  function WebAuthn() {
    var routes = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
    var _headers2 = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};
    var includeCredentials = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : false;
    var xcsrfToken = arguments.length > 3 && arguments[3] !== undefined ? arguments[3] : null;
    _classCallCheck(this, WebAuthn);
    _classPrivateMethodInitSpec(this, _parseOutgoingCredentials);
    _classPrivateMethodInitSpec(this, _parseIncomingServerOptions);
    _classPrivateMethodInitSpec(this, _fetch);
    _classPrivateFieldInitSpec(this, _routes, {
      writable: true,
      value: {
        registerOptions: "webauthn::register/options",
        register: "webauthn::register",
        loginOptions: "webauthn::login/options",
        login: "webauthn::login"
      }
    });
    _classPrivateFieldInitSpec(this, _headers, {
      writable: true,
      value: {
        "Accept": "application/json",
        "Content-Type": "application/json",
        "X-Requested-With": "XMLHttpRequest"
      }
    });
    _classPrivateFieldInitSpec(this, _includeCredentials, {
      writable: true,
      value: false
    });
    Object.assign(_classPrivateFieldGet(this, _routes), routes);
    Object.assign(_classPrivateFieldGet(this, _headers), _headers2);
    _classPrivateFieldSet(this, _includeCredentials, includeCredentials);
    var xsrfToken;
    var csrfToken;
    if (xcsrfToken === null) {
      // If the developer didn't issue an XSRF token, we will find it ourselves.
      xsrfToken = _classStaticPrivateFieldSpecGet(WebAuthn, WebAuthn, _XsrfToken);
      csrfToken = _classStaticPrivateFieldSpecGet(WebAuthn, WebAuthn, _firstInputWithCsrfToken);
    } else {
      // Check if it is a CSRF or XSRF token
      if (xcsrfToken.length === 40) {
        csrfToken = xcsrfToken;
      } else if (xcsrfToken.length === 224) {
        xsrfToken = xcsrfToken;
      } else {
        throw new TypeError('CSRF token or XSRF token provided does not match requirements. Must be 40 or 224 characters.');
      }
    }
    if (xsrfToken !== null) {
      var _classPrivateFieldGet2, _XXSRFTOKEN, _classPrivateFieldGet3;
      (_classPrivateFieldGet3 = (_classPrivateFieldGet2 = _classPrivateFieldGet(this, _headers))[_XXSRFTOKEN = "X-XSRF-TOKEN"]) !== null && _classPrivateFieldGet3 !== void 0 ? _classPrivateFieldGet3 : _classPrivateFieldGet2[_XXSRFTOKEN] = xsrfToken;
    } else if (csrfToken !== null) {
      var _classPrivateFieldGet4, _XCSRFTOKEN, _classPrivateFieldGet5;
      (_classPrivateFieldGet5 = (_classPrivateFieldGet4 = _classPrivateFieldGet(this, _headers))[_XCSRFTOKEN = "X-CSRF-TOKEN"]) !== null && _classPrivateFieldGet5 !== void 0 ? _classPrivateFieldGet5 : _classPrivateFieldGet4[_XCSRFTOKEN] = csrfToken;
    } else {
      // We didn't find it, and since is required, we will bail out.
      throw new TypeError('Ensure a CSRF/XSRF token is manually set, or provided in a cookie "XSRF-TOKEN" or or there is meta tag named "csrf-token".');
    }
  }

  /**
   * Returns the CSRF token if it exists as a form input tag.
   *
   * @returns string
   * @throws TypeError
   */
  _createClass(WebAuthn, [{
    key: "register",
    value:
    /**
     * Register the user credentials from the browser/device.
     *
     * You can add request input if you are planning to register a user with WebAuthn from scratch.
     *
     * @param request {{string}}
     * @param response {{string}}
     * @returns Promise<JSON|ReadableStream>
     */
    function () {
      var _register = _asyncToGenerator( /*#__PURE__*/_regeneratorRuntime().mark(function _callee() {
        var request,
          response,
          optionsResponse,
          json,
          publicKey,
          credentials,
          publicKeyCredential,
          _args = arguments;
        return _regeneratorRuntime().wrap(function _callee$(_context) {
          while (1) switch (_context.prev = _context.next) {
            case 0:
              request = _args.length > 0 && _args[0] !== undefined ? _args[0] : {};
              response = _args.length > 1 && _args[1] !== undefined ? _args[1] : {};
              _context.next = 4;
              return _classPrivateMethodGet(this, _fetch, _fetch2).call(this, request, _classPrivateFieldGet(this, _routes).registerOptions);
            case 4:
              optionsResponse = _context.sent;
              _context.next = 7;
              return optionsResponse.json();
            case 7:
              json = _context.sent;
              publicKey = _classPrivateMethodGet(this, _parseIncomingServerOptions, _parseIncomingServerOptions2).call(this, json);
              _context.next = 11;
              return navigator.credentials.create({
                publicKey: publicKey
              });
            case 11:
              credentials = _context.sent;
              publicKeyCredential = _classPrivateMethodGet(this, _parseOutgoingCredentials, _parseOutgoingCredentials2).call(this, credentials);
              Object.assign(publicKeyCredential, response);
              _context.next = 16;
              return _classPrivateMethodGet(this, _fetch, _fetch2).call(this, publicKeyCredential, _classPrivateFieldGet(this, _routes).register).then(_classStaticPrivateMethodGet(WebAuthn, WebAuthn, _handleResponse));
            case 16:
              return _context.abrupt("return", _context.sent);
            case 17:
            case "end":
              return _context.stop();
          }
        }, _callee, this);
      }));
      function register() {
        return _register.apply(this, arguments);
      }
      return register;
    }()
    /**
     * Log in a user with his credentials.
     *
     * If no credentials are given, the app may return a blank assertion for userless login.
     *
     * @param request {{string}}
     * @param response {{string}}
     * @returns Promise<JSON|ReadableStream>
     */
  }, {
    key: "login",
    value: function () {
      var _login = _asyncToGenerator( /*#__PURE__*/_regeneratorRuntime().mark(function _callee2() {
        var request,
          response,
          optionsResponse,
          json,
          publicKey,
          credentials,
          publicKeyCredential,
          _args2 = arguments;
        return _regeneratorRuntime().wrap(function _callee2$(_context2) {
          while (1) switch (_context2.prev = _context2.next) {
            case 0:
              request = _args2.length > 0 && _args2[0] !== undefined ? _args2[0] : {};
              response = _args2.length > 1 && _args2[1] !== undefined ? _args2[1] : {};
              _context2.next = 4;
              return _classPrivateMethodGet(this, _fetch, _fetch2).call(this, request, _classPrivateFieldGet(this, _routes).loginOptions);
            case 4:
              optionsResponse = _context2.sent;
              _context2.next = 7;
              return optionsResponse.json();
            case 7:
              json = _context2.sent;
              publicKey = _classPrivateMethodGet(this, _parseIncomingServerOptions, _parseIncomingServerOptions2).call(this, json);
              _context2.next = 11;
              return navigator.credentials.get({
                publicKey: publicKey
              });
            case 11:
              credentials = _context2.sent;
              publicKeyCredential = _classPrivateMethodGet(this, _parseOutgoingCredentials, _parseOutgoingCredentials2).call(this, credentials);
              Object.assign(publicKeyCredential, response);
              _context2.next = 16;
              return _classPrivateMethodGet(this, _fetch, _fetch2).call(this, publicKeyCredential, _classPrivateFieldGet(this, _routes).login, response).then(_classStaticPrivateMethodGet(WebAuthn, WebAuthn, _handleResponse));
            case 16:
              return _context2.abrupt("return", _context2.sent);
            case 17:
            case "end":
              return _context2.stop();
          }
        }, _callee2, this);
      }));
      function login() {
        return _login.apply(this, arguments);
      }
      return login;
    }()
    /**
     * Checks if the browser supports WebAuthn.
     *
     * @returns {boolean}
     */
  }], [{
    key: "supportsWebAuthn",
    value: function supportsWebAuthn() {
      return typeof PublicKeyCredential != "undefined";
    }

    /**
     * Checks if the browser doesn't support WebAuthn.
     *
     * @returns {boolean}
     */
  }, {
    key: "doesntSupportWebAuthn",
    value: function doesntSupportWebAuthn() {
      return !this.supportsWebAuthn();
    }
  }]);
  return WebAuthn;
}();
function _get_firstInputWithCsrfToken() {
  // First, try finding an CSRF Token in the head.
  var token = Array.from(document.head.getElementsByTagName("meta")).find(function (element) {
    return element.name === "csrf-token";
  });
  if (token) {
    return token.content;
  }

  // Then, try to find a hidden input containing the CSRF token.
  token = Array.from(document.getElementsByTagName('input')).find(function (input) {
    return input.name === "_token" && input.type === "hidden";
  });
  if (token) {
    return token.value;
  }
  return null;
}
function _get_XsrfToken() {
  var cookie = document.cookie.split(";").find(function (row) {
    return /^\s*(X-)?[XC]SRF-TOKEN\s*=/.test(row);
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
}
function _fetch2(data, route) {
  var headers = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : {};
  return fetch(route, {
    method: "POST",
    credentials: _classPrivateFieldGet(this, _includeCredentials) ? "include" : "same-origin",
    redirect: "error",
    headers: _objectSpread(_objectSpread({}, _classPrivateFieldGet(this, _headers)), headers),
    body: JSON.stringify(data)
  });
}
function _base64UrlDecode(input) {
  input = input.replace(/-/g, "+").replace(/_/g, "/");
  var pad = input.length % 4;
  if (pad) {
    if (pad === 1) {
      throw new Error("InvalidLengthError: Input base64url string is the wrong length to determine padding");
    }
    input += new Array(5 - pad).join("=");
  }
  return atob(input);
}
function _uint8Array(input) {
  var useAtob = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : false;
  return Uint8Array.from(useAtob ? atob(input) : _classStaticPrivateMethodGet(WebAuthn, WebAuthn, _base64UrlDecode).call(WebAuthn, input), function (c) {
    return c.charCodeAt(0);
  });
}
function _arrayToBase64String(arrayBuffer) {
  return btoa(String.fromCharCode.apply(String, _toConsumableArray(new Uint8Array(arrayBuffer))));
}
function _parseIncomingServerOptions2(publicKey) {
  console.debug(publicKey);
  publicKey.challenge = _classStaticPrivateMethodGet(WebAuthn, WebAuthn, _uint8Array).call(WebAuthn, publicKey.challenge);
  if ('user' in publicKey) {
    publicKey.user = _objectSpread(_objectSpread({}, publicKey.user), {}, {
      id: _classStaticPrivateMethodGet(WebAuthn, WebAuthn, _uint8Array).call(WebAuthn, publicKey.user.id)
    });
  }
  ["excludeCredentials", "allowCredentials"].filter(function (key) {
    return key in publicKey;
  }).forEach(function (key) {
    publicKey[key] = publicKey[key].map(function (data) {
      return _objectSpread(_objectSpread({}, data), {}, {
        id: _classStaticPrivateMethodGet(WebAuthn, WebAuthn, _uint8Array).call(WebAuthn, data.id)
      });
    });
  });
  return publicKey;
}
function _parseOutgoingCredentials2(credentials) {
  var parseCredentials = {
    id: credentials.id,
    type: credentials.type,
    rawId: _classStaticPrivateMethodGet(WebAuthn, WebAuthn, _arrayToBase64String).call(WebAuthn, credentials.rawId),
    response: {}
  };
  ["clientDataJSON", "attestationObject", "authenticatorData", "signature", "userHandle"].filter(function (key) {
    return key in credentials.response;
  }).forEach(function (key) {
    return parseCredentials.response[key] = _classStaticPrivateMethodGet(WebAuthn, WebAuthn, _arrayToBase64String).call(WebAuthn, credentials.response[key]);
  });
  return parseCredentials;
}
function _handleResponse(response) {
  if (!response.ok) {
    throw response;
  }

  // Here we will do a small trick. Since most of the responses from the server
  // are JSON, we will automatically parse the JSON body from the response. If
  // it's not JSON, we will push the body verbatim and let the dev handle it.
  return new Promise(function (resolve) {
    response.json().then(function (json) {
      return resolve(json);
    })["catch"](function () {
      return resolve(response.body);
    });
  });
}
var _XsrfToken = {
  get: _get_XsrfToken,
  set: void 0
};
var _firstInputWithCsrfToken = {
  get: _get_firstInputWithCsrfToken,
  set: void 0
};
/******/ })()
;