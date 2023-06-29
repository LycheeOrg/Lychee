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

function justify() {

	document.querySelector(".justified-layout");
	
	const jqJustifiedLayout = $(".justified-layout");
	let containerWidth = parseFloat(jqJustifiedLayout.width());
	if (containerWidth === 0) {
		// The reported width is zero, if `.justified-layout`
		// or any parent element is hidden via `display: none`.
		// Currently, this happens when a page reload is triggered
		// in photo view due to dorky timing constraints.
		// (In short: `lychee.load` initially hides the parent
		// container `.content`, and the parent container only
		// becomes visible _after_ the photo has been loaded which
		// is too late for this method.)
		// Also note, that this container and the parent
		// container are normally always visible, even if a photo
		// is shown as the photo view is drawn in the foreground
		// and covers this container.
		// Hence, this edge case here is really only a problem
		// during a full page reload in combination with
		// `lychee.load`.
		// Also note that the code below is wrong and outdated.
		// The alternative way to calculate the container width
		// depends on the window width and (falsely) assumes that
		// neither the left menu nor the right sidebar are open,
		// but that the `.content` box covers the whole viewport.
		// That was a correct assumption in the past, as the
		// sidebar was always closed after a full page reload, but
		// this assumption isn't true anymore since Lychee
		// remembers the state of the sidebar.
		// Luckily, this whole problem vanishes with the new
		// box model after
		// https://github.com/LycheeOrg/Lychee-front/pull/335 has been merged.
		// Then, we can use the view of the view container which
		// is always visible and always has the correct width
		// even for opened sidebars.
		// TODO: Unconditionally use the width of the view container and remove this alternative width calculation after https://github.com/LycheeOrg/Lychee-front/pull/335 has been merged
		containerWidth = $(window).width() - 2 * parseFloat(jqJustifiedLayout.css("margin"));
	}
	/** @type {number[]} */
	const ratio = photos.map(function (_photo) {
		const height = _photo.size_variants.original.height;
		const width = _photo.size_variants.original.width;
		const ratio = height > 0 ? width / height : 1;
		// If there is no small and medium size variants for videos,
		// we have to fall back to square thumbs
		return _photo.type &&
			_photo.type.indexOf("video") !== -1 &&
			_photo.size_variants.small === null &&
			_photo.size_variants.medium === null
			? 1
			: ratio;
	});

	/**
	 * An album listing has potentially hundreds of photos, hence
	 * only query for them once.
	 * @type {jQuery}
	 */
	const jqPhotoElements = $(".justified-layout > div.photo");
	const photoDefaultHeight = parseFloat(jqPhotoElements.css("--lychee-default-height"));

	const layoutGeometry = require("justified-layout")(ratio, {
		containerWidth: containerWidth,
		containerPadding: 0,
		targetRowHeight: photoDefaultHeight,
	});
	// if (lychee.rights.settings.can_edit) console.log(layoutGeometry);
	$(".justified-layout").css("height", layoutGeometry.containerHeight + "px");
	$(".justified-layout > div").each(function (i) {
		if (!layoutGeometry.boxes[i]) {
			// Race condition in search.find -- window content
			// and `photos` can get out of sync as search
			// query is being modified.
			return false;
		}
		const imgs = $(this)
			.css({
				top: layoutGeometry.boxes[i].top + "px",
				width: layoutGeometry.boxes[i].width + "px",
				height: layoutGeometry.boxes[i].height + "px",
				left: layoutGeometry.boxes[i].left + "px",
			})
			.find(".thumbimg > img");

		if (imgs.length > 0 && imgs[0].getAttribute("data-srcset")) {
			imgs[0].setAttribute("sizes", layoutGeometry.boxes[i].width + "px");
		}
	});
	// Show updated layout
	jqJustifiedLayout.removeClass("laying-out");
}