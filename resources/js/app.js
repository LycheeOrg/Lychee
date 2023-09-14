import 'lazysizes';
import Mousetrap from 'mousetrap';
import WebAuthn from './vendor/webauthn/webauthn.js'
import 'mousetrap-global-bind';

// Keyboard
Mousetrap.addKeycodes({
	18: "ContextMenu",
	179: "play_pause",
	227: "rewind",
	228: "forward",
});

document.addEventListener('alpine:init', () => {

	Alpine.data('loginWebAuthn',
		(success_msg_val = "U2F_AUTHENTIFICATION_SUCCESS", error_msg_val = "ERROR_TEXT") => ({
			webAuthnOpen: false,
			success_msg: success_msg_val,
			error_msg: error_msg_val,
			username: null,
			userId: 1,

			isWebAuthnUnavailable() {
				return !window.isSecureContext && window.location.hostname !== 'localhost' && window.location.hostname !== '127.0.0.1';
			},

			login() {
				// work around because this does not refer to alpine anymore when inside WebAuthn then context.
				let alpine = this;
				let params = {};
				if (this.username !== '' && this.username !== null) {
					params.username = this.username;
				}
				else if (this.userId !== null) {
					params.user_id = this.userId;
				}
				new WebAuthn({ login: "/api/WebAuthn::login", loginOptions: "/api/WebAuthn::login/options" }, {}, false)
					// .login({ user_id: 1 })
					.login(params)
					.then(function () {
						alpine.$dispatch("notify", [{ type: "success", msg: alpine.success_msg }]);
						window.location.reload();
					})
					.catch(() => this.$dispatch("notify", [{ type: "error", msg: this.error_msg }]));
			}
		})
	)

	Alpine.data('registerWebAuthn',
		(success_msg_val = "U2F_REGISTRATION_SUCCESS", error_msg_val = "ERROR_TEXT") => ({
			success_msg: success_msg_val,
			error_msg: error_msg_val,


			isWebAuthnUnavailable() {
				return !window.isSecureContext && window.location.hostname !== 'localhost' && window.location.hostname !== '127.0.0.1';
			},

			register() {
				// work around because this does not refer to alpine anymore when inside WebAuthn then context.
				let alpine = this
				new WebAuthn({ register: "/api/WebAuthn::register", registerOptions: "/api/WebAuthn::register/options" }, {}, false)
					.register()
					.then(function () {
						// First reload then display
						alpine.$dispatch("reload-component");
						alpine.$dispatch("notify", [{ type: "success", msg: alpine.success_msg }]);
					})
					.catch((error) => {
						console.log(error);
						alpine.$dispatch("notify", [{ type: "error", msg: this.error_msg }]);
					});
			}
		})
	)

	Alpine.data('upload',
		(chunkSize_val, parallelism_val = 3) => ({
			isDropping: false,

			chunkSize: chunkSize_val,
			hasErrorOccurred: false,
			upload_processing_limit: parallelism_val,
			chnkStarts: [],
			fileList: [],

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
			outstandingResponsesCount: 0,
			/**
			 * The latest (aka highest) index of a file which is being or has
			 * been uploaded to the server.
			 *
			 * @type {number}
			 */
			latestFileIdx: 0,
			/**
			 * Semaphore whether a file is currently being uploaded.
			 *
			 * This is used as a semaphore to serialize the upload transmissions
			 * between several instances of the method {@link process}.
			 *
			 * @type {boolean}
			 */
			isUploadRunning: false,

			/**
			 * This callback is invoked when the last file has been processed.
			 *
			 * It closes the modal dialog or shows the close button and
			 * reloads the album.
			 */
			finish(wire, alpine = this) {
				if (!alpine.hasErrorOccurred) {
					console.log("Success!");
					wire.dispatch("reloadPage");
					wire.close();
				} else {
					console.log("Well something went wrong...");
					wire.dispatch("reloadPage");
					// Error
				}

			},

			process(fileIdx, wire, alpine = this) {
				alpine.outstandingResponsesCount++;
				this.livewireUploadChunk(fileIdx, wire, alpine);
			},

			complete(wire, alpine = this) {
				alpine.outstandingResponsesCount--;

				// Start the next one if possible.
				if (alpine.outstandingResponsesCount < alpine.upload_processing_limit &&
					alpine.latestFileIdx + 1 < alpine.fileList.length) {
					console.log("next file!");
					alpine.latestFileIdx++;
					alpine.process(alpine.latestFileIdx, wire, alpine)
				}
				else if (alpine.outstandingResponsesCount === 0 &&
					alpine.latestFileIdx + 1 === alpine.fileList.length) {
					console.log("Finish");
					alpine.finish(wire, alpine);
				}
				else {
					console.log("Curent threads: " + alpine.outstandingResponsesCount);
					console.log("Current index: " + alpine.latestFileIdx);
					console.log("Number of files: " + alpine.fileList.length);
					console.log("waiting...");
				}
			},

			/**
			 * Processes the upload and response for a single file.
			 *
			 * Note that up to `livewireUploadChunk` "instances" of
			 * this method can be "alive" simultaneously.
			 * The parameter `fileIdx` is limited by `latestFileIdx`.
			 *
			 * @param {number} fileIdx the index of the file being processed
			 * @param {Livewire} wire accessore to Livewire funtions
			 * @param {Alpine} alpine accessor to current Alpine object
			 */
			livewireUploadChunk(fileIdx, wire, alpine = this) {
				// End of chunk is start + chunkSize OR file size, whichever is greater
				const chunkEnd = Math.min(alpine.chnkStarts[fileIdx] + alpine.chunkSize, alpine.fileList[fileIdx].size);
				const chunk = alpine.fileList[fileIdx].slice(alpine.chnkStarts[fileIdx], chunkEnd);

				wire.upload('uploads.' + fileIdx + '.fileChunk', chunk,
					(success) => {
						alpine.complete(wire, alpine);
					},
					() => {
						alpine.hasErrorOccurred = true;
						alpine.complete(wire, alpine);
						wire.set('uploads.' + fileIdx + '.stage', 'error');
					},
					(event) => {
						console.log(event)
						if (event.detail.progress == 100) {
							alpine.chnkStarts[fileIdx] =
								Math.min(alpine.chnkStarts[fileIdx] + alpine.chunkSize, alpine.fileList[fileIdx].size);

							if (alpine.chnkStarts[fileIdx] < alpine.fileList[fileIdx].size) {
								let _time = Math.floor((Math.random() * 2000) + 1);
								console.log('sleeping ', _time, 'before next chunk upload');
								setTimeout(alpine.livewireUploadChunk, _time, fileIdx, wire, alpine);
							}
						}
					}
				);
			},

			start(wire, alpine = this) {
				for (let index = 0; index < Math.min(alpine.upload_processing_limit, alpine.fileList.length); index++) {
					alpine.process(index, wire, alpine);
					alpine.latestFileIdx = index;
				}
			},		
		})
	)
});



// Alpine.bind('openLeftMenu', () => ({ '@click'() { leftMenuOpen = ! leftMenuOpen }}))
// })
// Mousetrap
// .bind(["l"], function () {
// 	lychee.loginDialog();
// 	return false;
// 	})
// .bind(["k"], function () {
// 	u2f.login();
// 	return false;
// })
// .bind(["left"], function () {
// 	if (
// 		visible.photo() &&
// 		(!visible.header() || $("img#image").is(":focus") || $("img#livephoto").is(":focus") || $(":focus").length === 0)
// 	) {
// 		$("#imageview a#previous").click();
// 		return false;
// 	}
// 	return true;
// })
// .bind(["right"], function () {
// 	if (
// 		visible.photo() &&
// 		(!visible.header() || $("img#image").is(":focus") || $("img#livephoto").is(":focus") || $(":focus").length === 0)
// 	) {
// 		$("#imageview a#next").click();
// 		return false;
// 	}
// 	return true;
// })
// .bind(["u"], function () {
// 	if (!visible.photo() && album.isUploadable()) {
// 		$("#upload_files").click();
// 		return false;
// 	}
// })
// .bind(["n"], function () {
// 	if (!visible.photo() && album.isUploadable()) {
// 		album.add();
// 		return false;
// 	}
// })
// .bind(["s"], function () {
// 	if (visible.photo() && album.isUploadable()) {
// 		header.dom("#button_star").click();
// 		return false;
// 	} else if (visible.albums()) {
// 		header.dom(".header__search").focus();
// 		return false;
// 	}
// })
// .bind(["r"], function () {
// 	if (album.isUploadable()) {
// 		if (visible.album()) {
// 			album.setTitle(album.getID());
// 			return false;
// 		} else if (visible.photo()) {
// 			photo.setTitle([photo.getID()]);
// 			return false;
// 		}
// 	}
// })
// .bind(["h"], album.toggle_nsfw_filter)
// .bind(["d"], function () {
// 	if (album.isUploadable()) {
// 		if (visible.photo()) {
// 			photo.setDescription(photo.getID());
// 			return false;
// 		} else if (visible.album()) {
// 			album.setDescription(album.getID());
// 			return false;
// 		}
// 	}
// })
// .bind(["t"], function () {
// 	if (visible.photo() && album.isUploadable()) {
// 		photo.editTags([photo.getID()]);
// 		return false;
// 	}
// })
// .bind(["i", "ContextMenu"], function () {
// 	if (!visible.multiselect()) {
// 		sidebar.toggle();
// 		return false;
// 	}
// })
// .bind(["command+backspace", "ctrl+backspace"], function () {
// 	if (album.isUploadable()) {
// 		if (visible.photo() && basicModal.visible() === false) {
// 			photo.delete([photo.getID()]);
// 			return false;
// 		} else if (visible.album() && basicModal.visible() === false) {
// 			album.delete([album.getID()]);
// 			return false;
// 		}
// 	}
// })
// .bind(["command+a", "ctrl+a"], function () {
// 	if (visible.album() && basicModal.visible() === false) {
// 		multiselect.selectAll();
// 		return false;
// 	} else if (visible.albums() && basicModal.visible() === false) {
// 		multiselect.selectAll();
// 		return false;
// 	}
// })
// .bind(["o"], function () {
// 	if (visible.photo()) {
// 		photo.cycle_display_overlay();
// 		return false;
// 	}
// })
// .bind(["f"], function () {
// 	if (visible.album() || visible.photo()) {
// 		lychee.fullscreenToggle();
// 		return false;
// 	}
// });

// Mousetrap.bind(["play_pause"], function () {
// 	// If it's a video, we toggle play/pause
// 	let video = $("video");

// 	if (video.length !== 0) {
// 		if (video[0].paused) {
// 			video[0].play();
// 		} else {
// 			video[0].pause();
// 		}
// 	}
// });

// Mousetrap.bindGlobal("enter", function () {
// 	if (basicModal.visible() === true) {
// 		// check if any of the input fields is focussed
// 		// apply action, other do nothing
// 		if ($(".basicModal__content input").is(":focus")) {
// 			basicModal.action();
// 			return false;
// 		}
// 	} else if (
// 		visible.photo() &&
// 		!lychee.header_auto_hide &&
// 		($("img#image").is(":focus") || $("img#livephoto").is(":focus") || $(":focus").length === 0)
// 	) {
// 		if (visible.header()) {
// 			header.hide();
// 		} else {
// 			header.show();
// 		}
// 		return false;
// 	}
// 	let clicked = false;
// 	$(":focus").each(function () {
// 		if (!$(this).is("input")) {
// 			$(this).click();
// 			clicked = true;
// 		}
// 	});
// 	if (clicked) {
// 		return false;
// 	}
// });

// Prevent 'esc keyup' event to trigger 'go back in history'
// and 'alt keyup' to show a webapp context menu for Fire TV
// Mousetrap.bindGlobal(
// 	["esc", "ContextMenu"],
// 	function () {
// 		return false;
// 	},
// 	"keyup"
// );

// Mousetrap.bindGlobal(["esc", "command+up"], function () {
// 	Livewire.emit('back');
// 	// if (basicModal.visible() === true) basicModal.cancel();
// 	// else if (visible.config() || visible.leftMenu()) leftMenu.close();
// 	// else if (visible.contextMenu()) contextMenu.close();
// 	// else if (visible.photo()) lychee.goto(album.getID());
// 	// else if (visible.album() && !album.json.parent_id) lychee.goto();
// 	// else if (visible.album()) lychee.goto(album.getParent());
// 	// else if (visible.albums() && search.hash !== null) search.reset();
// 	// else if (visible.mapview()) mapview.close();
// 	// else if (visible.albums() && lychee.enable_close_tab_on_esc) {
// 	// 	window.open("", "_self").close();
// 	// }
// 	return false;
// });

// const u2f = {
// 	/** @type {?WebAuthnCredential[]} */
// 	json: null,
// };

// /**
//  * @returns {void}
//  */
// u2f.login = function () {

// 	new WebAuthn(
// 		{
// 			login: "/api/WebAuthn::login",
// 			loginOptions: "/api/WebAuthn::login/options",
// 		},
// 		{},
// 		false
// 	)
// 		.login({
// 			user_id: 1, // for now it is only available to Admin user via a secret key shortcut.
// 		})
// 		.then(function () {
// 			loadingBar.show("success", lychee.locale["U2F_AUTHENTIFICATION_SUCCESS"]);
// 			window.location.reload();
// 		})
// 		.catch(() => loadingBar.show("error", lychee.locale["ERROR_TEXT"]));
// };

// /**
//  * @returns {void}
//  */
// u2f.register = function () {
// 	if (!u2f.is_available()) {
// 		return;
// 	}

// 	const webauthn = new WebAuthn(
// 		{
// 			register: "/api/WebAuthn::register",
// 			registerOptions: "/api/WebAuthn::register/options",
// 		},
// 		{},
// 		false
// 	);
// 	if (WebAuthn.supportsWebAuthn()) {
// 		webauthn
// 			.register()
// 			.then(function () {
// 				loadingBar.show("success", lychee.locale["U2F_REGISTRATION_SUCCESS"]);
// 				u2f.list(); // reload credential list
// 			})
// 			.catch(() => loadingBar.show("error", lychee.locale["ERROR_TEXT"]));
// 	} else {
// 		loadingBar.show("error", lychee.locale["U2F_NOT_SUPPORTED"]);
// 	}
// };

// /**
//  * @param {{id: string}} params - ID of WebAuthn credential
//  */
// u2f.delete = function (params) {
// 	api.post("WebAuthn::delete", params, function () {
// 		loadingBar.show("success", lychee.locale["U2F_CREDENTIALS_DELETED"]);
// 		u2f.list(); // reload credential list
// 	});
// };

// u2f.list = function () {
// 	api.post(
// 		"WebAuthn::list",
// 		{},
// 		/** @param {WebAuthnCredential[]} data*/
// 		function (data) {
// 			u2f.json = data;
// 			view.u2f.init();
// 		}
// 	);
// };

