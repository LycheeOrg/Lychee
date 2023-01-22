import 'lazysizes';
import Mousetrap from 'mousetrap';
import 'mousetrap-global-bind';

// var Mousetrap = require('mousetrap');

// Keyboard
Mousetrap.addKeycodes({
	18: "ContextMenu",
	179: "play_pause",
	227: "rewind",
	228: "forward",
});


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
Mousetrap.bindGlobal(
	["esc", "ContextMenu"],
	function () {
		return false;
	},
	"keyup"
);

Mousetrap.bindGlobal(["esc", "command+up"], function () {
	Livewire.emit('back');
	// if (basicModal.visible() === true) basicModal.cancel();
	// else if (visible.config() || visible.leftMenu()) leftMenu.close();
	// else if (visible.contextMenu()) contextMenu.close();
	// else if (visible.photo()) lychee.goto(album.getID());
	// else if (visible.album() && !album.json.parent_id) lychee.goto();
	// else if (visible.album()) lychee.goto(album.getParent());
	// else if (visible.albums() && search.hash !== null) search.reset();
	// else if (visible.mapview()) mapview.close();
	// else if (visible.albums() && lychee.enable_close_tab_on_esc) {
	// 	window.open("", "_self").close();
	// }
	return false;
});