/******/ (() => { // webpackBootstrap
/******/ 	var __webpack_modules__ = ({

/***/ "./resources/assets/js/app.js":
/*!************************************!*\
  !*** ./resources/assets/js/app.js ***!
  \************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var lazysizes__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! lazysizes */ "./node_modules/lazysizes/lazysizes.js");
/* harmony import */ var lazysizes__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(lazysizes__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var mousetrap__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! mousetrap */ "./node_modules/mousetrap/mousetrap.js");
/* harmony import */ var mousetrap__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(mousetrap__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var mousetrap_global_bind__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! mousetrap-global-bind */ "./node_modules/mousetrap-global-bind/mousetrap-global-bind.js");
/* harmony import */ var mousetrap_global_bind__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(mousetrap_global_bind__WEBPACK_IMPORTED_MODULE_2__);




// var Mousetrap = require('mousetrap');

// Keyboard
mousetrap__WEBPACK_IMPORTED_MODULE_1___default().addKeycodes({
  18: "ContextMenu",
  179: "play_pause",
  227: "rewind",
  228: "forward"
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
mousetrap__WEBPACK_IMPORTED_MODULE_1___default().bindGlobal(["esc", "ContextMenu"], function () {
  return false;
}, "keyup");
mousetrap__WEBPACK_IMPORTED_MODULE_1___default().bindGlobal(["esc", "command+up"], function () {
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
  var jqJustifiedLayout = $(".justified-layout");
  var containerWidth = parseFloat(jqJustifiedLayout.width());
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
  var ratio = photos.map(function (_photo) {
    var height = _photo.size_variants.original.height;
    var width = _photo.size_variants.original.width;
    var ratio = height > 0 ? width / height : 1;
    // If there is no small and medium size variants for videos,
    // we have to fall back to square thumbs
    return _photo.type && _photo.type.indexOf("video") !== -1 && _photo.size_variants.small === null && _photo.size_variants.medium === null ? 1 : ratio;
  });

  /**
   * An album listing has potentially hundreds of photos, hence
   * only query for them once.
   * @type {jQuery}
   */
  var jqPhotoElements = $(".justified-layout > div.photo");
  var photoDefaultHeight = parseFloat(jqPhotoElements.css("--lychee-default-height"));
  var layoutGeometry = __webpack_require__(/*! justified-layout */ "./node_modules/justified-layout/lib/index.js")(ratio, {
    containerWidth: containerWidth,
    containerPadding: 0,
    targetRowHeight: photoDefaultHeight
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
    var imgs = $(this).css({
      top: layoutGeometry.boxes[i].top + "px",
      width: layoutGeometry.boxes[i].width + "px",
      height: layoutGeometry.boxes[i].height + "px",
      left: layoutGeometry.boxes[i].left + "px"
    }).find(".thumbimg > img");
    if (imgs.length > 0 && imgs[0].getAttribute("data-srcset")) {
      imgs[0].setAttribute("sizes", layoutGeometry.boxes[i].width + "px");
    }
  });
  // Show updated layout
  jqJustifiedLayout.removeClass("laying-out");
}

/***/ }),

/***/ "./node_modules/justified-layout/lib/index.js":
/*!****************************************************!*\
  !*** ./node_modules/justified-layout/lib/index.js ***!
  \****************************************************/
/***/ ((module, __unused_webpack_exports, __webpack_require__) => {

"use strict";
/*!
 * Copyright 2019 SmugMug, Inc.
 * Licensed under the terms of the MIT license. Please see LICENSE file in the project root for terms.
 * @license
 */



var Row = __webpack_require__(/*! ./row */ "./node_modules/justified-layout/lib/row.js");

/**
 * Create a new, empty row.
 *
 * @method createNewRow
 * @param layoutConfig {Object} The layout configuration
 * @param layoutData {Object} The current state of the layout
 * @return A new, empty row of the type specified by this layout.
 */

function createNewRow(layoutConfig, layoutData) {

	var isBreakoutRow;

	// Work out if this is a full width breakout row
	if (layoutConfig.fullWidthBreakoutRowCadence !== false) {
		if (((layoutData._rows.length + 1) % layoutConfig.fullWidthBreakoutRowCadence) === 0) {
			isBreakoutRow = true;
		}
	}

	return new Row({
		top: layoutData._containerHeight,
		left: layoutConfig.containerPadding.left,
		width: layoutConfig.containerWidth - layoutConfig.containerPadding.left - layoutConfig.containerPadding.right,
		spacing: layoutConfig.boxSpacing.horizontal,
		targetRowHeight: layoutConfig.targetRowHeight,
		targetRowHeightTolerance: layoutConfig.targetRowHeightTolerance,
		edgeCaseMinRowHeight: 0.5 * layoutConfig.targetRowHeight,
		edgeCaseMaxRowHeight: 2 * layoutConfig.targetRowHeight,
		rightToLeft: false,
		isBreakoutRow: isBreakoutRow,
		widowLayoutStyle: layoutConfig.widowLayoutStyle
	});
}

/**
 * Add a completed row to the layout.
 * Note: the row must have already been completed.
 *
 * @method addRow
 * @param layoutConfig {Object} The layout configuration
 * @param layoutData {Object} The current state of the layout
 * @param row {Row} The row to add.
 * @return {Array} Each item added to the row.
 */

function addRow(layoutConfig, layoutData, row) {

	layoutData._rows.push(row);
	layoutData._layoutItems = layoutData._layoutItems.concat(row.getItems());

	// Increment the container height
	layoutData._containerHeight += row.height + layoutConfig.boxSpacing.vertical;

	return row.items;
}

/**
 * Calculate the current layout for all items in the list that require layout.
 * "Layout" means geometry: position within container and size
 *
 * @method computeLayout
 * @param layoutConfig {Object} The layout configuration
 * @param layoutData {Object} The current state of the layout
 * @param itemLayoutData {Array} Array of items to lay out, with data required to lay out each item
 * @return {Object} The newly-calculated layout, containing the new container height, and lists of layout items
 */

function computeLayout(layoutConfig, layoutData, itemLayoutData) {

	var laidOutItems = [],
		itemAdded,
		currentRow,
		nextToLastRowHeight;

	// Apply forced aspect ratio if specified, and set a flag.
	if (layoutConfig.forceAspectRatio) {
		itemLayoutData.forEach(function (itemData) {
			itemData.forcedAspectRatio = true;
			itemData.aspectRatio = layoutConfig.forceAspectRatio;
		});
	}

	// Loop through the items
	itemLayoutData.some(function (itemData, i) {

		if (isNaN(itemData.aspectRatio)) {
			throw new Error("Item " + i + " has an invalid aspect ratio");
		}

		// If not currently building up a row, make a new one.
		if (!currentRow) {
			currentRow = createNewRow(layoutConfig, layoutData);
		}

		// Attempt to add item to the current row.
		itemAdded = currentRow.addItem(itemData);

		if (currentRow.isLayoutComplete()) {

			// Row is filled; add it and start a new one
			laidOutItems = laidOutItems.concat(addRow(layoutConfig, layoutData, currentRow));

			if (layoutData._rows.length >= layoutConfig.maxNumRows) {
				currentRow = null;
				return true;
			}

			currentRow = createNewRow(layoutConfig, layoutData);

			// Item was rejected; add it to its own row
			if (!itemAdded) {

				itemAdded = currentRow.addItem(itemData);

				if (currentRow.isLayoutComplete()) {

					// If the rejected item fills a row on its own, add the row and start another new one
					laidOutItems = laidOutItems.concat(addRow(layoutConfig, layoutData, currentRow));
					if (layoutData._rows.length >= layoutConfig.maxNumRows) {
						currentRow = null;
						return true;
					}
					currentRow = createNewRow(layoutConfig, layoutData);
				}
			}
		}

	});

	// Handle any leftover content (orphans) depending on where they lie
	// in this layout update, and in the total content set.
	if (currentRow && currentRow.getItems().length && layoutConfig.showWidows) {

		// Last page of all content or orphan suppression is suppressed; lay out orphans.
		if (layoutData._rows.length) {

			// Only Match previous row's height if it exists and it isn't a breakout row
			if (layoutData._rows[layoutData._rows.length - 1].isBreakoutRow) {
				nextToLastRowHeight = layoutData._rows[layoutData._rows.length - 1].targetRowHeight;
			} else {
				nextToLastRowHeight = layoutData._rows[layoutData._rows.length - 1].height;
			}

			currentRow.forceComplete(false, nextToLastRowHeight);

		} else {

			// ...else use target height if there is no other row height to reference.
			currentRow.forceComplete(false);

		}

		laidOutItems = laidOutItems.concat(addRow(layoutConfig, layoutData, currentRow));
		layoutConfig._widowCount = currentRow.getItems().length;

	}

	// We need to clean up the bottom container padding
	// First remove the height added for box spacing
	layoutData._containerHeight = layoutData._containerHeight - layoutConfig.boxSpacing.vertical;
	// Then add our bottom container padding
	layoutData._containerHeight = layoutData._containerHeight + layoutConfig.containerPadding.bottom;

	return {
		containerHeight: layoutData._containerHeight,
		widowCount: layoutConfig._widowCount,
		boxes: layoutData._layoutItems
	};

}

/**
 * Takes in a bunch of box data and config. Returns
 * geometry to lay them out in a justified view.
 *
 * @method covertSizesToAspectRatios
 * @param sizes {Array} Array of objects with widths and heights
 * @return {Array} A list of aspect ratios
 */

module.exports = function (input, config) {
	var layoutConfig = {};
	var layoutData = {};

	// Defaults
	var defaults = {
		containerWidth: 1060,
		containerPadding: 10,
		boxSpacing: 10,
		targetRowHeight: 320,
		targetRowHeightTolerance: 0.25,
		maxNumRows: Number.POSITIVE_INFINITY,
		forceAspectRatio: false,
		showWidows: true,
		fullWidthBreakoutRowCadence: false,
		widowLayoutStyle: 'left'
	};

	var containerPadding = {};
	var boxSpacing = {};

	config = config || {};

	// Merge defaults and config passed in
	layoutConfig = Object.assign(defaults, config);

	// Sort out padding and spacing values
	containerPadding.top = (!isNaN(parseFloat(layoutConfig.containerPadding.top))) ? layoutConfig.containerPadding.top : layoutConfig.containerPadding;
	containerPadding.right = (!isNaN(parseFloat(layoutConfig.containerPadding.right))) ? layoutConfig.containerPadding.right : layoutConfig.containerPadding;
	containerPadding.bottom = (!isNaN(parseFloat(layoutConfig.containerPadding.bottom))) ? layoutConfig.containerPadding.bottom : layoutConfig.containerPadding;
	containerPadding.left = (!isNaN(parseFloat(layoutConfig.containerPadding.left))) ? layoutConfig.containerPadding.left : layoutConfig.containerPadding;
	boxSpacing.horizontal = (!isNaN(parseFloat(layoutConfig.boxSpacing.horizontal))) ? layoutConfig.boxSpacing.horizontal : layoutConfig.boxSpacing;
	boxSpacing.vertical = (!isNaN(parseFloat(layoutConfig.boxSpacing.vertical))) ? layoutConfig.boxSpacing.vertical : layoutConfig.boxSpacing;

	layoutConfig.containerPadding = containerPadding;
	layoutConfig.boxSpacing = boxSpacing;

	// Local
	layoutData._layoutItems = [];
	layoutData._awakeItems = [];
	layoutData._inViewportItems = [];
	layoutData._leadingOrphans = [];
	layoutData._trailingOrphans = [];
	layoutData._containerHeight = layoutConfig.containerPadding.top;
	layoutData._rows = [];
	layoutData._orphans = [];
	layoutConfig._widowCount = 0;

	// Convert widths and heights to aspect ratios if we need to
	return computeLayout(layoutConfig, layoutData, input.map(function (item) {
		if (item.width && item.height) {
			return { aspectRatio: item.width / item.height };
		} else {
			return { aspectRatio: item };
		}
	}));
};


/***/ }),

/***/ "./node_modules/justified-layout/lib/row.js":
/*!**************************************************!*\
  !*** ./node_modules/justified-layout/lib/row.js ***!
  \**************************************************/
/***/ ((module) => {

/*!
 * Copyright 2019 SmugMug, Inc.
 * Licensed under the terms of the MIT license. Please see LICENSE file in the project root for terms.
 * @license
 */

/**
 * Row
 * Wrapper for each row in a justified layout.
 * Stores relevant values and provides methods for calculating layout of individual rows.
 *
 * @param {Object} layoutConfig - The same as that passed
 * @param {Object} Initialization parameters. The following are all required:
 * @param params.top {Number} Top of row, relative to container
 * @param params.left {Number} Left side of row relative to container (equal to container left padding)
 * @param params.width {Number} Width of row, not including container padding
 * @param params.spacing {Number} Horizontal spacing between items
 * @param params.targetRowHeight {Number} Layout algorithm will aim for this row height
 * @param params.targetRowHeightTolerance {Number} Row heights may vary +/- (`targetRowHeight` x `targetRowHeightTolerance`)
 * @param params.edgeCaseMinRowHeight {Number} Absolute minimum row height for edge cases that cannot be resolved within tolerance.
 * @param params.edgeCaseMaxRowHeight {Number} Absolute maximum row height for edge cases that cannot be resolved within tolerance.
 * @param params.isBreakoutRow {Boolean} Is this row in particular one of those breakout rows? Always false if it's not that kind of photo list
 * @param params.widowLayoutStyle {String} If widows are visible, how should they be laid out?
 * @constructor
 */

var Row = module.exports = function (params) {

	// Top of row, relative to container
	this.top = params.top;

	// Left side of row relative to container (equal to container left padding)
	this.left = params.left;

	// Width of row, not including container padding
	this.width = params.width;

	// Horizontal spacing between items
	this.spacing = params.spacing;

	// Row height calculation values
	this.targetRowHeight = params.targetRowHeight;
	this.targetRowHeightTolerance = params.targetRowHeightTolerance;
	this.minAspectRatio = this.width / params.targetRowHeight * (1 - params.targetRowHeightTolerance);
	this.maxAspectRatio = this.width / params.targetRowHeight * (1 + params.targetRowHeightTolerance);

	// Edge case row height minimum/maximum
	this.edgeCaseMinRowHeight = params.edgeCaseMinRowHeight;
	this.edgeCaseMaxRowHeight = params.edgeCaseMaxRowHeight;

	// Widow layout direction
	this.widowLayoutStyle = params.widowLayoutStyle;

	// Full width breakout rows
	this.isBreakoutRow = params.isBreakoutRow;

	// Store layout data for each item in row
	this.items = [];

	// Height remains at 0 until it's been calculated
	this.height = 0;

};

Row.prototype = {

	/**
	 * Attempt to add a single item to the row.
	 * This is the heart of the justified algorithm.
	 * This method is direction-agnostic; it deals only with sizes, not positions.
	 *
	 * If the item fits in the row, without pushing row height beyond min/max tolerance,
	 * the item is added and the method returns true.
	 *
	 * If the item leaves row height too high, there may be room to scale it down and add another item.
	 * In this case, the item is added and the method returns true, but the row is incomplete.
	 *
	 * If the item leaves row height too short, there are too many items to fit within tolerance.
	 * The method will either accept or reject the new item, favoring the resulting row height closest to within tolerance.
	 * If the item is rejected, left/right padding will be required to fit the row height within tolerance;
	 * if the item is accepted, top/bottom cropping will be required to fit the row height within tolerance.
	 *
	 * @method addItem
	 * @param itemData {Object} Item layout data, containing item aspect ratio.
	 * @return {Boolean} True if successfully added; false if rejected.
	 */

	addItem: function (itemData) {

		var newItems = this.items.concat(itemData),
			// Calculate aspect ratios for items only; exclude spacing
			rowWidthWithoutSpacing = this.width - (newItems.length - 1) * this.spacing,
			newAspectRatio = newItems.reduce(function (sum, item) {
				return sum + item.aspectRatio;
			}, 0),
			targetAspectRatio = rowWidthWithoutSpacing / this.targetRowHeight,
			previousRowWidthWithoutSpacing,
			previousAspectRatio,
			previousTargetAspectRatio;

		// Handle big full-width breakout photos if we're doing them
		if (this.isBreakoutRow) {
			// Only do it if there's no other items in this row
			if (this.items.length === 0) {
				// Only go full width if this photo is a square or landscape
				if (itemData.aspectRatio >= 1) {
					// Close out the row with a full width photo
					this.items.push(itemData);
					this.completeLayout(rowWidthWithoutSpacing / itemData.aspectRatio, 'justify');
					return true;
				}
			}
		}

		if (newAspectRatio < this.minAspectRatio) {

			// New aspect ratio is too narrow / scaled row height is too tall.
			// Accept this item and leave row open for more items.

			this.items.push(Object.assign({}, itemData));
			return true;

		} else if (newAspectRatio > this.maxAspectRatio) {

			// New aspect ratio is too wide / scaled row height will be too short.
			// Accept item if the resulting aspect ratio is closer to target than it would be without the item.
			// NOTE: Any row that falls into this block will require cropping/padding on individual items.

			if (this.items.length === 0) {

				// When there are no existing items, force acceptance of the new item and complete the layout.
				// This is the pano special case.
				this.items.push(Object.assign({}, itemData));
				this.completeLayout(rowWidthWithoutSpacing / newAspectRatio, 'justify');
				return true;

			}

			// Calculate width/aspect ratio for row before adding new item
			previousRowWidthWithoutSpacing = this.width - (this.items.length - 1) * this.spacing;
			previousAspectRatio = this.items.reduce(function (sum, item) {
				return sum + item.aspectRatio;
			}, 0);
			previousTargetAspectRatio = previousRowWidthWithoutSpacing / this.targetRowHeight;

			if (Math.abs(newAspectRatio - targetAspectRatio) > Math.abs(previousAspectRatio - previousTargetAspectRatio)) {

				// Row with new item is us farther away from target than row without; complete layout and reject item.
				this.completeLayout(previousRowWidthWithoutSpacing / previousAspectRatio, 'justify');
				return false;

			} else {

				// Row with new item is us closer to target than row without;
				// accept the new item and complete the row layout.
				this.items.push(Object.assign({}, itemData));
				this.completeLayout(rowWidthWithoutSpacing / newAspectRatio, 'justify');
				return true;

			}

		} else {

			// New aspect ratio / scaled row height is within tolerance;
			// accept the new item and complete the row layout.
			this.items.push(Object.assign({}, itemData));
			this.completeLayout(rowWidthWithoutSpacing / newAspectRatio, 'justify');
			return true;

		}

	},

	/**
	 * Check if a row has completed its layout.
	 *
	 * @method isLayoutComplete
	 * @return {Boolean} True if complete; false if not.
	 */

	isLayoutComplete: function () {
		return this.height > 0;
	},

	/**
	 * Set row height and compute item geometry from that height.
	 * Will justify items within the row unless instructed not to.
	 *
	 * @method completeLayout
	 * @param newHeight {Number} Set row height to this value.
	 * @param widowLayoutStyle {String} How should widows display? Supported: left | justify | center
	 */

	completeLayout: function (newHeight, widowLayoutStyle) {

		var itemWidthSum = this.left,
			rowWidthWithoutSpacing = this.width - (this.items.length - 1) * this.spacing,
			clampedToNativeRatio,
			clampedHeight,
			errorWidthPerItem,
			roundedCumulativeErrors,
			singleItemGeometry,
			centerOffset;

		// Justify unless explicitly specified otherwise.
		if (typeof widowLayoutStyle === 'undefined' || ['justify', 'center', 'left'].indexOf(widowLayoutStyle) < 0) {
			widowLayoutStyle = 'left';
		}

		// Clamp row height to edge case minimum/maximum.
		clampedHeight = Math.max(this.edgeCaseMinRowHeight, Math.min(newHeight, this.edgeCaseMaxRowHeight));

		if (newHeight !== clampedHeight) {

			// If row height was clamped, the resulting row/item aspect ratio will be off,
			// so force it to fit the width (recalculate aspectRatio to match clamped height).
			// NOTE: this will result in cropping/padding commensurate to the amount of clamping.
			this.height = clampedHeight;
			clampedToNativeRatio = (rowWidthWithoutSpacing / clampedHeight) / (rowWidthWithoutSpacing / newHeight);

		} else {

			// If not clamped, leave ratio at 1.0.
			this.height = newHeight;
			clampedToNativeRatio = 1.0;

		}

		// Compute item geometry based on newHeight.
		this.items.forEach(function (item) {

			item.top = this.top;
			item.width = item.aspectRatio * this.height * clampedToNativeRatio;
			item.height = this.height;

			// Left-to-right.
			// TODO right to left
			// item.left = this.width - itemWidthSum - item.width;
			item.left = itemWidthSum;

			// Increment width.
			itemWidthSum += item.width + this.spacing;

		}, this);

		// If specified, ensure items fill row and distribute error
		// caused by rounding width and height across all items.
		if (widowLayoutStyle === 'justify') {

			itemWidthSum -= (this.spacing + this.left);

			errorWidthPerItem = (itemWidthSum - this.width) / this.items.length;
			roundedCumulativeErrors = this.items.map(function (item, i) {
				return Math.round((i + 1) * errorWidthPerItem);
			});


			if (this.items.length === 1) {

				// For rows with only one item, adjust item width to fill row.
				singleItemGeometry = this.items[0];
				singleItemGeometry.width -= Math.round(errorWidthPerItem);

			} else {

				// For rows with multiple items, adjust item width and shift items to fill the row,
				// while maintaining equal spacing between items in the row.
				this.items.forEach(function (item, i) {
					if (i > 0) {
						item.left -= roundedCumulativeErrors[i - 1];
						item.width -= (roundedCumulativeErrors[i] - roundedCumulativeErrors[i - 1]);
					} else {
						item.width -= roundedCumulativeErrors[i];
					}
				});

			}

		} else if (widowLayoutStyle === 'center') {

			// Center widows
			centerOffset = (this.width - itemWidthSum) / 2;

			this.items.forEach(function (item) {
				item.left += centerOffset + this.spacing;
			}, this);

		}

	},

	/**
	 * Force completion of row layout with current items.
	 *
	 * @method forceComplete
	 * @param fitToWidth {Boolean} Stretch current items to fill the row width.
	 *                             This will likely result in padding.
	 * @param fitToWidth {Number}
	 */

	forceComplete: function (fitToWidth, rowHeight) {

		// TODO Handle fitting to width
		// var rowWidthWithoutSpacing = this.width - (this.items.length - 1) * this.spacing,
		// 	currentAspectRatio = this.items.reduce(function (sum, item) {
		// 		return sum + item.aspectRatio;
		// 	}, 0);

		if (typeof rowHeight === 'number') {

			this.completeLayout(rowHeight, this.widowLayoutStyle);

		} else {

			// Complete using target row height.
			this.completeLayout(this.targetRowHeight, this.widowLayoutStyle);
		}

	},

	/**
	 * Return layout data for items within row.
	 * Note: returns actual list, not a copy.
	 *
	 * @method getItems
	 * @return Layout data for items within row.
	 */

	getItems: function () {
		return this.items;
	}

};


/***/ }),

/***/ "./node_modules/lazysizes/lazysizes.js":
/*!*********************************************!*\
  !*** ./node_modules/lazysizes/lazysizes.js ***!
  \*********************************************/
/***/ ((module) => {

(function(window, factory) {
	var lazySizes = factory(window, window.document, Date);
	window.lazySizes = lazySizes;
	if( true && module.exports){
		module.exports = lazySizes;
	}
}(typeof window != 'undefined' ?
      window : {}, 
/**
 * import("./types/global")
 * @typedef { import("./types/lazysizes-config").LazySizesConfigPartial } LazySizesConfigPartial
 */
function l(window, document, Date) { // Pass in the window Date function also for SSR because the Date class can be lost
	'use strict';
	/*jshint eqnull:true */

	var lazysizes,
		/**
		 * @type { LazySizesConfigPartial }
		 */
		lazySizesCfg;

	(function(){
		var prop;

		var lazySizesDefaults = {
			lazyClass: 'lazyload',
			loadedClass: 'lazyloaded',
			loadingClass: 'lazyloading',
			preloadClass: 'lazypreload',
			errorClass: 'lazyerror',
			//strictClass: 'lazystrict',
			autosizesClass: 'lazyautosizes',
			fastLoadedClass: 'ls-is-cached',
			iframeLoadMode: 0,
			srcAttr: 'data-src',
			srcsetAttr: 'data-srcset',
			sizesAttr: 'data-sizes',
			//preloadAfterLoad: false,
			minSize: 40,
			customMedia: {},
			init: true,
			expFactor: 1.5,
			hFac: 0.8,
			loadMode: 2,
			loadHidden: true,
			ricTimeout: 0,
			throttleDelay: 125,
		};

		lazySizesCfg = window.lazySizesConfig || window.lazysizesConfig || {};

		for(prop in lazySizesDefaults){
			if(!(prop in lazySizesCfg)){
				lazySizesCfg[prop] = lazySizesDefaults[prop];
			}
		}
	})();

	if (!document || !document.getElementsByClassName) {
		return {
			init: function () {},
			/**
			 * @type { LazySizesConfigPartial }
			 */
			cfg: lazySizesCfg,
			/**
			 * @type { true }
			 */
			noSupport: true,
		};
	}

	var docElem = document.documentElement;

	var supportPicture = window.HTMLPictureElement;

	var _addEventListener = 'addEventListener';

	var _getAttribute = 'getAttribute';

	/**
	 * Update to bind to window because 'this' becomes null during SSR
	 * builds.
	 */
	var addEventListener = window[_addEventListener].bind(window);

	var setTimeout = window.setTimeout;

	var requestAnimationFrame = window.requestAnimationFrame || setTimeout;

	var requestIdleCallback = window.requestIdleCallback;

	var regPicture = /^picture$/i;

	var loadEvents = ['load', 'error', 'lazyincluded', '_lazyloaded'];

	var regClassCache = {};

	var forEach = Array.prototype.forEach;

	/**
	 * @param ele {Element}
	 * @param cls {string}
	 */
	var hasClass = function(ele, cls) {
		if(!regClassCache[cls]){
			regClassCache[cls] = new RegExp('(\\s|^)'+cls+'(\\s|$)');
		}
		return regClassCache[cls].test(ele[_getAttribute]('class') || '') && regClassCache[cls];
	};

	/**
	 * @param ele {Element}
	 * @param cls {string}
	 */
	var addClass = function(ele, cls) {
		if (!hasClass(ele, cls)){
			ele.setAttribute('class', (ele[_getAttribute]('class') || '').trim() + ' ' + cls);
		}
	};

	/**
	 * @param ele {Element}
	 * @param cls {string}
	 */
	var removeClass = function(ele, cls) {
		var reg;
		if ((reg = hasClass(ele,cls))) {
			ele.setAttribute('class', (ele[_getAttribute]('class') || '').replace(reg, ' '));
		}
	};

	var addRemoveLoadEvents = function(dom, fn, add){
		var action = add ? _addEventListener : 'removeEventListener';
		if(add){
			addRemoveLoadEvents(dom, fn);
		}
		loadEvents.forEach(function(evt){
			dom[action](evt, fn);
		});
	};

	/**
	 * @param elem { Element }
	 * @param name { string }
	 * @param detail { any }
	 * @param noBubbles { boolean }
	 * @param noCancelable { boolean }
	 * @returns { CustomEvent }
	 */
	var triggerEvent = function(elem, name, detail, noBubbles, noCancelable){
		var event = document.createEvent('Event');

		if(!detail){
			detail = {};
		}

		detail.instance = lazysizes;

		event.initEvent(name, !noBubbles, !noCancelable);

		event.detail = detail;

		elem.dispatchEvent(event);
		return event;
	};

	var updatePolyfill = function (el, full){
		var polyfill;
		if( !supportPicture && ( polyfill = (window.picturefill || lazySizesCfg.pf) ) ){
			if(full && full.src && !el[_getAttribute]('srcset')){
				el.setAttribute('srcset', full.src);
			}
			polyfill({reevaluate: true, elements: [el]});
		} else if(full && full.src){
			el.src = full.src;
		}
	};

	var getCSS = function (elem, style){
		return (getComputedStyle(elem, null) || {})[style];
	};

	/**
	 *
	 * @param elem { Element }
	 * @param parent { Element }
	 * @param [width] {number}
	 * @returns {number}
	 */
	var getWidth = function(elem, parent, width){
		width = width || elem.offsetWidth;

		while(width < lazySizesCfg.minSize && parent && !elem._lazysizesWidth){
			width =  parent.offsetWidth;
			parent = parent.parentNode;
		}

		return width;
	};

	var rAF = (function(){
		var running, waiting;
		var firstFns = [];
		var secondFns = [];
		var fns = firstFns;

		var run = function(){
			var runFns = fns;

			fns = firstFns.length ? secondFns : firstFns;

			running = true;
			waiting = false;

			while(runFns.length){
				runFns.shift()();
			}

			running = false;
		};

		var rafBatch = function(fn, queue){
			if(running && !queue){
				fn.apply(this, arguments);
			} else {
				fns.push(fn);

				if(!waiting){
					waiting = true;
					(document.hidden ? setTimeout : requestAnimationFrame)(run);
				}
			}
		};

		rafBatch._lsFlush = run;

		return rafBatch;
	})();

	var rAFIt = function(fn, simple){
		return simple ?
			function() {
				rAF(fn);
			} :
			function(){
				var that = this;
				var args = arguments;
				rAF(function(){
					fn.apply(that, args);
				});
			}
		;
	};

	var throttle = function(fn){
		var running;
		var lastTime = 0;
		var gDelay = lazySizesCfg.throttleDelay;
		var rICTimeout = lazySizesCfg.ricTimeout;
		var run = function(){
			running = false;
			lastTime = Date.now();
			fn();
		};
		var idleCallback = requestIdleCallback && rICTimeout > 49 ?
			function(){
				requestIdleCallback(run, {timeout: rICTimeout});

				if(rICTimeout !== lazySizesCfg.ricTimeout){
					rICTimeout = lazySizesCfg.ricTimeout;
				}
			} :
			rAFIt(function(){
				setTimeout(run);
			}, true)
		;

		return function(isPriority){
			var delay;

			if((isPriority = isPriority === true)){
				rICTimeout = 33;
			}

			if(running){
				return;
			}

			running =  true;

			delay = gDelay - (Date.now() - lastTime);

			if(delay < 0){
				delay = 0;
			}

			if(isPriority || delay < 9){
				idleCallback();
			} else {
				setTimeout(idleCallback, delay);
			}
		};
	};

	//based on http://modernjavascript.blogspot.de/2013/08/building-better-debounce.html
	var debounce = function(func) {
		var timeout, timestamp;
		var wait = 99;
		var run = function(){
			timeout = null;
			func();
		};
		var later = function() {
			var last = Date.now() - timestamp;

			if (last < wait) {
				setTimeout(later, wait - last);
			} else {
				(requestIdleCallback || run)(run);
			}
		};

		return function() {
			timestamp = Date.now();

			if (!timeout) {
				timeout = setTimeout(later, wait);
			}
		};
	};

	var loader = (function(){
		var preloadElems, isCompleted, resetPreloadingTimer, loadMode, started;

		var eLvW, elvH, eLtop, eLleft, eLright, eLbottom, isBodyHidden;

		var regImg = /^img$/i;
		var regIframe = /^iframe$/i;

		var supportScroll = ('onscroll' in window) && !(/(gle|ing)bot/.test(navigator.userAgent));

		var shrinkExpand = 0;
		var currentExpand = 0;

		var isLoading = 0;
		var lowRuns = -1;

		var resetPreloading = function(e){
			isLoading--;
			if(!e || isLoading < 0 || !e.target){
				isLoading = 0;
			}
		};

		var isVisible = function (elem) {
			if (isBodyHidden == null) {
				isBodyHidden = getCSS(document.body, 'visibility') == 'hidden';
			}

			return isBodyHidden || !(getCSS(elem.parentNode, 'visibility') == 'hidden' && getCSS(elem, 'visibility') == 'hidden');
		};

		var isNestedVisible = function(elem, elemExpand){
			var outerRect;
			var parent = elem;
			var visible = isVisible(elem);

			eLtop -= elemExpand;
			eLbottom += elemExpand;
			eLleft -= elemExpand;
			eLright += elemExpand;

			while(visible && (parent = parent.offsetParent) && parent != document.body && parent != docElem){
				visible = ((getCSS(parent, 'opacity') || 1) > 0);

				if(visible && getCSS(parent, 'overflow') != 'visible'){
					outerRect = parent.getBoundingClientRect();
					visible = eLright > outerRect.left &&
						eLleft < outerRect.right &&
						eLbottom > outerRect.top - 1 &&
						eLtop < outerRect.bottom + 1
					;
				}
			}

			return visible;
		};

		var checkElements = function() {
			var eLlen, i, rect, autoLoadElem, loadedSomething, elemExpand, elemNegativeExpand, elemExpandVal,
				beforeExpandVal, defaultExpand, preloadExpand, hFac;
			var lazyloadElems = lazysizes.elements;

			if((loadMode = lazySizesCfg.loadMode) && isLoading < 8 && (eLlen = lazyloadElems.length)){

				i = 0;

				lowRuns++;

				for(; i < eLlen; i++){

					if(!lazyloadElems[i] || lazyloadElems[i]._lazyRace){continue;}

					if(!supportScroll || (lazysizes.prematureUnveil && lazysizes.prematureUnveil(lazyloadElems[i]))){unveilElement(lazyloadElems[i]);continue;}

					if(!(elemExpandVal = lazyloadElems[i][_getAttribute]('data-expand')) || !(elemExpand = elemExpandVal * 1)){
						elemExpand = currentExpand;
					}

					if (!defaultExpand) {
						defaultExpand = (!lazySizesCfg.expand || lazySizesCfg.expand < 1) ?
							docElem.clientHeight > 500 && docElem.clientWidth > 500 ? 500 : 370 :
							lazySizesCfg.expand;

						lazysizes._defEx = defaultExpand;

						preloadExpand = defaultExpand * lazySizesCfg.expFactor;
						hFac = lazySizesCfg.hFac;
						isBodyHidden = null;

						if(currentExpand < preloadExpand && isLoading < 1 && lowRuns > 2 && loadMode > 2 && !document.hidden){
							currentExpand = preloadExpand;
							lowRuns = 0;
						} else if(loadMode > 1 && lowRuns > 1 && isLoading < 6){
							currentExpand = defaultExpand;
						} else {
							currentExpand = shrinkExpand;
						}
					}

					if(beforeExpandVal !== elemExpand){
						eLvW = innerWidth + (elemExpand * hFac);
						elvH = innerHeight + elemExpand;
						elemNegativeExpand = elemExpand * -1;
						beforeExpandVal = elemExpand;
					}

					rect = lazyloadElems[i].getBoundingClientRect();

					if ((eLbottom = rect.bottom) >= elemNegativeExpand &&
						(eLtop = rect.top) <= elvH &&
						(eLright = rect.right) >= elemNegativeExpand * hFac &&
						(eLleft = rect.left) <= eLvW &&
						(eLbottom || eLright || eLleft || eLtop) &&
						(lazySizesCfg.loadHidden || isVisible(lazyloadElems[i])) &&
						((isCompleted && isLoading < 3 && !elemExpandVal && (loadMode < 3 || lowRuns < 4)) || isNestedVisible(lazyloadElems[i], elemExpand))){
						unveilElement(lazyloadElems[i]);
						loadedSomething = true;
						if(isLoading > 9){break;}
					} else if(!loadedSomething && isCompleted && !autoLoadElem &&
						isLoading < 4 && lowRuns < 4 && loadMode > 2 &&
						(preloadElems[0] || lazySizesCfg.preloadAfterLoad) &&
						(preloadElems[0] || (!elemExpandVal && ((eLbottom || eLright || eLleft || eLtop) || lazyloadElems[i][_getAttribute](lazySizesCfg.sizesAttr) != 'auto')))){
						autoLoadElem = preloadElems[0] || lazyloadElems[i];
					}
				}

				if(autoLoadElem && !loadedSomething){
					unveilElement(autoLoadElem);
				}
			}
		};

		var throttledCheckElements = throttle(checkElements);

		var switchLoadingClass = function(e){
			var elem = e.target;

			if (elem._lazyCache) {
				delete elem._lazyCache;
				return;
			}

			resetPreloading(e);
			addClass(elem, lazySizesCfg.loadedClass);
			removeClass(elem, lazySizesCfg.loadingClass);
			addRemoveLoadEvents(elem, rafSwitchLoadingClass);
			triggerEvent(elem, 'lazyloaded');
		};
		var rafedSwitchLoadingClass = rAFIt(switchLoadingClass);
		var rafSwitchLoadingClass = function(e){
			rafedSwitchLoadingClass({target: e.target});
		};

		var changeIframeSrc = function(elem, src){
			var loadMode = elem.getAttribute('data-load-mode') || lazySizesCfg.iframeLoadMode;

			// loadMode can be also a string!
			if (loadMode == 0) {
				elem.contentWindow.location.replace(src);
			} else if (loadMode == 1) {
				elem.src = src;
			}
		};

		var handleSources = function(source){
			var customMedia;

			var sourceSrcset = source[_getAttribute](lazySizesCfg.srcsetAttr);

			if( (customMedia = lazySizesCfg.customMedia[source[_getAttribute]('data-media') || source[_getAttribute]('media')]) ){
				source.setAttribute('media', customMedia);
			}

			if(sourceSrcset){
				source.setAttribute('srcset', sourceSrcset);
			}
		};

		var lazyUnveil = rAFIt(function (elem, detail, isAuto, sizes, isImg){
			var src, srcset, parent, isPicture, event, firesLoad;

			if(!(event = triggerEvent(elem, 'lazybeforeunveil', detail)).defaultPrevented){

				if(sizes){
					if(isAuto){
						addClass(elem, lazySizesCfg.autosizesClass);
					} else {
						elem.setAttribute('sizes', sizes);
					}
				}

				srcset = elem[_getAttribute](lazySizesCfg.srcsetAttr);
				src = elem[_getAttribute](lazySizesCfg.srcAttr);

				if(isImg) {
					parent = elem.parentNode;
					isPicture = parent && regPicture.test(parent.nodeName || '');
				}

				firesLoad = detail.firesLoad || (('src' in elem) && (srcset || src || isPicture));

				event = {target: elem};

				addClass(elem, lazySizesCfg.loadingClass);

				if(firesLoad){
					clearTimeout(resetPreloadingTimer);
					resetPreloadingTimer = setTimeout(resetPreloading, 2500);
					addRemoveLoadEvents(elem, rafSwitchLoadingClass, true);
				}

				if(isPicture){
					forEach.call(parent.getElementsByTagName('source'), handleSources);
				}

				if(srcset){
					elem.setAttribute('srcset', srcset);
				} else if(src && !isPicture){
					if(regIframe.test(elem.nodeName)){
						changeIframeSrc(elem, src);
					} else {
						elem.src = src;
					}
				}

				if(isImg && (srcset || isPicture)){
					updatePolyfill(elem, {src: src});
				}
			}

			if(elem._lazyRace){
				delete elem._lazyRace;
			}
			removeClass(elem, lazySizesCfg.lazyClass);

			rAF(function(){
				// Part of this can be removed as soon as this fix is older: https://bugs.chromium.org/p/chromium/issues/detail?id=7731 (2015)
				var isLoaded = elem.complete && elem.naturalWidth > 1;

				if( !firesLoad || isLoaded){
					if (isLoaded) {
						addClass(elem, lazySizesCfg.fastLoadedClass);
					}
					switchLoadingClass(event);
					elem._lazyCache = true;
					setTimeout(function(){
						if ('_lazyCache' in elem) {
							delete elem._lazyCache;
						}
					}, 9);
				}
				if (elem.loading == 'lazy') {
					isLoading--;
				}
			}, true);
		});

		/**
		 *
		 * @param elem { Element }
		 */
		var unveilElement = function (elem){
			if (elem._lazyRace) {return;}
			var detail;

			var isImg = regImg.test(elem.nodeName);

			//allow using sizes="auto", but don't use. it's invalid. Use data-sizes="auto" or a valid value for sizes instead (i.e.: sizes="80vw")
			var sizes = isImg && (elem[_getAttribute](lazySizesCfg.sizesAttr) || elem[_getAttribute]('sizes'));
			var isAuto = sizes == 'auto';

			if( (isAuto || !isCompleted) && isImg && (elem[_getAttribute]('src') || elem.srcset) && !elem.complete && !hasClass(elem, lazySizesCfg.errorClass) && hasClass(elem, lazySizesCfg.lazyClass)){return;}

			detail = triggerEvent(elem, 'lazyunveilread').detail;

			if(isAuto){
				 autoSizer.updateElem(elem, true, elem.offsetWidth);
			}

			elem._lazyRace = true;
			isLoading++;

			lazyUnveil(elem, detail, isAuto, sizes, isImg);
		};

		var afterScroll = debounce(function(){
			lazySizesCfg.loadMode = 3;
			throttledCheckElements();
		});

		var altLoadmodeScrollListner = function(){
			if(lazySizesCfg.loadMode == 3){
				lazySizesCfg.loadMode = 2;
			}
			afterScroll();
		};

		var onload = function(){
			if(isCompleted){return;}
			if(Date.now() - started < 999){
				setTimeout(onload, 999);
				return;
			}


			isCompleted = true;

			lazySizesCfg.loadMode = 3;

			throttledCheckElements();

			addEventListener('scroll', altLoadmodeScrollListner, true);
		};

		return {
			_: function(){
				started = Date.now();

				lazysizes.elements = document.getElementsByClassName(lazySizesCfg.lazyClass);
				preloadElems = document.getElementsByClassName(lazySizesCfg.lazyClass + ' ' + lazySizesCfg.preloadClass);

				addEventListener('scroll', throttledCheckElements, true);

				addEventListener('resize', throttledCheckElements, true);

				addEventListener('pageshow', function (e) {
					if (e.persisted) {
						var loadingElements = document.querySelectorAll('.' + lazySizesCfg.loadingClass);

						if (loadingElements.length && loadingElements.forEach) {
							requestAnimationFrame(function () {
								loadingElements.forEach( function (img) {
									if (img.complete) {
										unveilElement(img);
									}
								});
							});
						}
					}
				});

				if(window.MutationObserver){
					new MutationObserver( throttledCheckElements ).observe( docElem, {childList: true, subtree: true, attributes: true} );
				} else {
					docElem[_addEventListener]('DOMNodeInserted', throttledCheckElements, true);
					docElem[_addEventListener]('DOMAttrModified', throttledCheckElements, true);
					setInterval(throttledCheckElements, 999);
				}

				addEventListener('hashchange', throttledCheckElements, true);

				//, 'fullscreenchange'
				['focus', 'mouseover', 'click', 'load', 'transitionend', 'animationend'].forEach(function(name){
					document[_addEventListener](name, throttledCheckElements, true);
				});

				if((/d$|^c/.test(document.readyState))){
					onload();
				} else {
					addEventListener('load', onload);
					document[_addEventListener]('DOMContentLoaded', throttledCheckElements);
					setTimeout(onload, 20000);
				}

				if(lazysizes.elements.length){
					checkElements();
					rAF._lsFlush();
				} else {
					throttledCheckElements();
				}
			},
			checkElems: throttledCheckElements,
			unveil: unveilElement,
			_aLSL: altLoadmodeScrollListner,
		};
	})();


	var autoSizer = (function(){
		var autosizesElems;

		var sizeElement = rAFIt(function(elem, parent, event, width){
			var sources, i, len;
			elem._lazysizesWidth = width;
			width += 'px';

			elem.setAttribute('sizes', width);

			if(regPicture.test(parent.nodeName || '')){
				sources = parent.getElementsByTagName('source');
				for(i = 0, len = sources.length; i < len; i++){
					sources[i].setAttribute('sizes', width);
				}
			}

			if(!event.detail.dataAttr){
				updatePolyfill(elem, event.detail);
			}
		});
		/**
		 *
		 * @param elem {Element}
		 * @param dataAttr
		 * @param [width] { number }
		 */
		var getSizeElement = function (elem, dataAttr, width){
			var event;
			var parent = elem.parentNode;

			if(parent){
				width = getWidth(elem, parent, width);
				event = triggerEvent(elem, 'lazybeforesizes', {width: width, dataAttr: !!dataAttr});

				if(!event.defaultPrevented){
					width = event.detail.width;

					if(width && width !== elem._lazysizesWidth){
						sizeElement(elem, parent, event, width);
					}
				}
			}
		};

		var updateElementsSizes = function(){
			var i;
			var len = autosizesElems.length;
			if(len){
				i = 0;

				for(; i < len; i++){
					getSizeElement(autosizesElems[i]);
				}
			}
		};

		var debouncedUpdateElementsSizes = debounce(updateElementsSizes);

		return {
			_: function(){
				autosizesElems = document.getElementsByClassName(lazySizesCfg.autosizesClass);
				addEventListener('resize', debouncedUpdateElementsSizes);
			},
			checkElems: debouncedUpdateElementsSizes,
			updateElem: getSizeElement
		};
	})();

	var init = function(){
		if(!init.i && document.getElementsByClassName){
			init.i = true;
			autoSizer._();
			loader._();
		}
	};

	setTimeout(function(){
		if(lazySizesCfg.init){
			init();
		}
	});

	lazysizes = {
		/**
		 * @type { LazySizesConfigPartial }
		 */
		cfg: lazySizesCfg,
		autoSizer: autoSizer,
		loader: loader,
		init: init,
		uP: updatePolyfill,
		aC: addClass,
		rC: removeClass,
		hC: hasClass,
		fire: triggerEvent,
		gW: getWidth,
		rAF: rAF,
	};

	return lazysizes;
}
));


/***/ }),

/***/ "./resources/assets/scss/app.scss":
/*!****************************************!*\
  !*** ./resources/assets/scss/app.scss ***!
  \****************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
// extracted by mini-css-extract-plugin


/***/ }),

/***/ "./resources/css/filepond.css":
/*!************************************!*\
  !*** ./resources/css/filepond.css ***!
  \************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
// extracted by mini-css-extract-plugin


/***/ }),

/***/ "./node_modules/mousetrap-global-bind/mousetrap-global-bind.js":
/*!*********************************************************************!*\
  !*** ./node_modules/mousetrap-global-bind/mousetrap-global-bind.js ***!
  \*********************************************************************/
/***/ (() => {

/**
 * adds a bindGlobal method to Mousetrap that allows you to
 * bind specific keyboard shortcuts that will still work
 * inside a text input field
 *
 * usage:
 * Mousetrap.bindGlobal('ctrl+s', _saveChanges);
 */
/* global Mousetrap:true */
(function(Mousetrap) {
    var _globalCallbacks = {};
    var _originalStopCallback = Mousetrap.prototype.stopCallback;

    Mousetrap.prototype.stopCallback = function(e, element, combo, sequence) {
        var self = this;

        if (self.paused) {
            return true;
        }

        if (_globalCallbacks[combo] || _globalCallbacks[sequence]) {
            return false;
        }

        return _originalStopCallback.call(self, e, element, combo);
    };

    Mousetrap.prototype.bindGlobal = function(keys, callback, action) {
        var self = this;
        self.bind(keys, callback, action);

        if (keys instanceof Array) {
            for (var i = 0; i < keys.length; i++) {
                _globalCallbacks[keys[i]] = true;
            }
            return;
        }

        _globalCallbacks[keys] = true;
    };
	
	Mousetrap.prototype.unbindGlobal = function(keys, action) {
		var self = this;
		self.unbind(keys, action);

		if (keys instanceof Array) {
			for (var i = 0; i < keys.length; i++) {
				_globalCallbacks[keys[i]] = false;
			}
			return;
		}

		_globalCallbacks[keys] = false;
	};

    Mousetrap.init();
}) (Mousetrap);


/***/ }),

/***/ "./node_modules/mousetrap/mousetrap.js":
/*!*********************************************!*\
  !*** ./node_modules/mousetrap/mousetrap.js ***!
  \*********************************************/
/***/ ((module, exports, __webpack_require__) => {

var __WEBPACK_AMD_DEFINE_RESULT__;/*global define:false */
/**
 * Copyright 2012-2017 Craig Campbell
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 * Mousetrap is a simple keyboard shortcut library for Javascript with
 * no external dependencies
 *
 * @version 1.6.5
 * @url craig.is/killing/mice
 */
(function(window, document, undefined) {

    // Check if mousetrap is used inside browser, if not, return
    if (!window) {
        return;
    }

    /**
     * mapping of special keycodes to their corresponding keys
     *
     * everything in this dictionary cannot use keypress events
     * so it has to be here to map to the correct keycodes for
     * keyup/keydown events
     *
     * @type {Object}
     */
    var _MAP = {
        8: 'backspace',
        9: 'tab',
        13: 'enter',
        16: 'shift',
        17: 'ctrl',
        18: 'alt',
        20: 'capslock',
        27: 'esc',
        32: 'space',
        33: 'pageup',
        34: 'pagedown',
        35: 'end',
        36: 'home',
        37: 'left',
        38: 'up',
        39: 'right',
        40: 'down',
        45: 'ins',
        46: 'del',
        91: 'meta',
        93: 'meta',
        224: 'meta'
    };

    /**
     * mapping for special characters so they can support
     *
     * this dictionary is only used incase you want to bind a
     * keyup or keydown event to one of these keys
     *
     * @type {Object}
     */
    var _KEYCODE_MAP = {
        106: '*',
        107: '+',
        109: '-',
        110: '.',
        111 : '/',
        186: ';',
        187: '=',
        188: ',',
        189: '-',
        190: '.',
        191: '/',
        192: '`',
        219: '[',
        220: '\\',
        221: ']',
        222: '\''
    };

    /**
     * this is a mapping of keys that require shift on a US keypad
     * back to the non shift equivelents
     *
     * this is so you can use keyup events with these keys
     *
     * note that this will only work reliably on US keyboards
     *
     * @type {Object}
     */
    var _SHIFT_MAP = {
        '~': '`',
        '!': '1',
        '@': '2',
        '#': '3',
        '$': '4',
        '%': '5',
        '^': '6',
        '&': '7',
        '*': '8',
        '(': '9',
        ')': '0',
        '_': '-',
        '+': '=',
        ':': ';',
        '\"': '\'',
        '<': ',',
        '>': '.',
        '?': '/',
        '|': '\\'
    };

    /**
     * this is a list of special strings you can use to map
     * to modifier keys when you specify your keyboard shortcuts
     *
     * @type {Object}
     */
    var _SPECIAL_ALIASES = {
        'option': 'alt',
        'command': 'meta',
        'return': 'enter',
        'escape': 'esc',
        'plus': '+',
        'mod': /Mac|iPod|iPhone|iPad/.test(navigator.platform) ? 'meta' : 'ctrl'
    };

    /**
     * variable to store the flipped version of _MAP from above
     * needed to check if we should use keypress or not when no action
     * is specified
     *
     * @type {Object|undefined}
     */
    var _REVERSE_MAP;

    /**
     * loop through the f keys, f1 to f19 and add them to the map
     * programatically
     */
    for (var i = 1; i < 20; ++i) {
        _MAP[111 + i] = 'f' + i;
    }

    /**
     * loop through to map numbers on the numeric keypad
     */
    for (i = 0; i <= 9; ++i) {

        // This needs to use a string cause otherwise since 0 is falsey
        // mousetrap will never fire for numpad 0 pressed as part of a keydown
        // event.
        //
        // @see https://github.com/ccampbell/mousetrap/pull/258
        _MAP[i + 96] = i.toString();
    }

    /**
     * cross browser add event method
     *
     * @param {Element|HTMLDocument} object
     * @param {string} type
     * @param {Function} callback
     * @returns void
     */
    function _addEvent(object, type, callback) {
        if (object.addEventListener) {
            object.addEventListener(type, callback, false);
            return;
        }

        object.attachEvent('on' + type, callback);
    }

    /**
     * takes the event and returns the key character
     *
     * @param {Event} e
     * @return {string}
     */
    function _characterFromEvent(e) {

        // for keypress events we should return the character as is
        if (e.type == 'keypress') {
            var character = String.fromCharCode(e.which);

            // if the shift key is not pressed then it is safe to assume
            // that we want the character to be lowercase.  this means if
            // you accidentally have caps lock on then your key bindings
            // will continue to work
            //
            // the only side effect that might not be desired is if you
            // bind something like 'A' cause you want to trigger an
            // event when capital A is pressed caps lock will no longer
            // trigger the event.  shift+a will though.
            if (!e.shiftKey) {
                character = character.toLowerCase();
            }

            return character;
        }

        // for non keypress events the special maps are needed
        if (_MAP[e.which]) {
            return _MAP[e.which];
        }

        if (_KEYCODE_MAP[e.which]) {
            return _KEYCODE_MAP[e.which];
        }

        // if it is not in the special map

        // with keydown and keyup events the character seems to always
        // come in as an uppercase character whether you are pressing shift
        // or not.  we should make sure it is always lowercase for comparisons
        return String.fromCharCode(e.which).toLowerCase();
    }

    /**
     * checks if two arrays are equal
     *
     * @param {Array} modifiers1
     * @param {Array} modifiers2
     * @returns {boolean}
     */
    function _modifiersMatch(modifiers1, modifiers2) {
        return modifiers1.sort().join(',') === modifiers2.sort().join(',');
    }

    /**
     * takes a key event and figures out what the modifiers are
     *
     * @param {Event} e
     * @returns {Array}
     */
    function _eventModifiers(e) {
        var modifiers = [];

        if (e.shiftKey) {
            modifiers.push('shift');
        }

        if (e.altKey) {
            modifiers.push('alt');
        }

        if (e.ctrlKey) {
            modifiers.push('ctrl');
        }

        if (e.metaKey) {
            modifiers.push('meta');
        }

        return modifiers;
    }

    /**
     * prevents default for this event
     *
     * @param {Event} e
     * @returns void
     */
    function _preventDefault(e) {
        if (e.preventDefault) {
            e.preventDefault();
            return;
        }

        e.returnValue = false;
    }

    /**
     * stops propogation for this event
     *
     * @param {Event} e
     * @returns void
     */
    function _stopPropagation(e) {
        if (e.stopPropagation) {
            e.stopPropagation();
            return;
        }

        e.cancelBubble = true;
    }

    /**
     * determines if the keycode specified is a modifier key or not
     *
     * @param {string} key
     * @returns {boolean}
     */
    function _isModifier(key) {
        return key == 'shift' || key == 'ctrl' || key == 'alt' || key == 'meta';
    }

    /**
     * reverses the map lookup so that we can look for specific keys
     * to see what can and can't use keypress
     *
     * @return {Object}
     */
    function _getReverseMap() {
        if (!_REVERSE_MAP) {
            _REVERSE_MAP = {};
            for (var key in _MAP) {

                // pull out the numeric keypad from here cause keypress should
                // be able to detect the keys from the character
                if (key > 95 && key < 112) {
                    continue;
                }

                if (_MAP.hasOwnProperty(key)) {
                    _REVERSE_MAP[_MAP[key]] = key;
                }
            }
        }
        return _REVERSE_MAP;
    }

    /**
     * picks the best action based on the key combination
     *
     * @param {string} key - character for key
     * @param {Array} modifiers
     * @param {string=} action passed in
     */
    function _pickBestAction(key, modifiers, action) {

        // if no action was picked in we should try to pick the one
        // that we think would work best for this key
        if (!action) {
            action = _getReverseMap()[key] ? 'keydown' : 'keypress';
        }

        // modifier keys don't work as expected with keypress,
        // switch to keydown
        if (action == 'keypress' && modifiers.length) {
            action = 'keydown';
        }

        return action;
    }

    /**
     * Converts from a string key combination to an array
     *
     * @param  {string} combination like "command+shift+l"
     * @return {Array}
     */
    function _keysFromString(combination) {
        if (combination === '+') {
            return ['+'];
        }

        combination = combination.replace(/\+{2}/g, '+plus');
        return combination.split('+');
    }

    /**
     * Gets info for a specific key combination
     *
     * @param  {string} combination key combination ("command+s" or "a" or "*")
     * @param  {string=} action
     * @returns {Object}
     */
    function _getKeyInfo(combination, action) {
        var keys;
        var key;
        var i;
        var modifiers = [];

        // take the keys from this pattern and figure out what the actual
        // pattern is all about
        keys = _keysFromString(combination);

        for (i = 0; i < keys.length; ++i) {
            key = keys[i];

            // normalize key names
            if (_SPECIAL_ALIASES[key]) {
                key = _SPECIAL_ALIASES[key];
            }

            // if this is not a keypress event then we should
            // be smart about using shift keys
            // this will only work for US keyboards however
            if (action && action != 'keypress' && _SHIFT_MAP[key]) {
                key = _SHIFT_MAP[key];
                modifiers.push('shift');
            }

            // if this key is a modifier then add it to the list of modifiers
            if (_isModifier(key)) {
                modifiers.push(key);
            }
        }

        // depending on what the key combination is
        // we will try to pick the best event for it
        action = _pickBestAction(key, modifiers, action);

        return {
            key: key,
            modifiers: modifiers,
            action: action
        };
    }

    function _belongsTo(element, ancestor) {
        if (element === null || element === document) {
            return false;
        }

        if (element === ancestor) {
            return true;
        }

        return _belongsTo(element.parentNode, ancestor);
    }

    function Mousetrap(targetElement) {
        var self = this;

        targetElement = targetElement || document;

        if (!(self instanceof Mousetrap)) {
            return new Mousetrap(targetElement);
        }

        /**
         * element to attach key events to
         *
         * @type {Element}
         */
        self.target = targetElement;

        /**
         * a list of all the callbacks setup via Mousetrap.bind()
         *
         * @type {Object}
         */
        self._callbacks = {};

        /**
         * direct map of string combinations to callbacks used for trigger()
         *
         * @type {Object}
         */
        self._directMap = {};

        /**
         * keeps track of what level each sequence is at since multiple
         * sequences can start out with the same sequence
         *
         * @type {Object}
         */
        var _sequenceLevels = {};

        /**
         * variable to store the setTimeout call
         *
         * @type {null|number}
         */
        var _resetTimer;

        /**
         * temporary state where we will ignore the next keyup
         *
         * @type {boolean|string}
         */
        var _ignoreNextKeyup = false;

        /**
         * temporary state where we will ignore the next keypress
         *
         * @type {boolean}
         */
        var _ignoreNextKeypress = false;

        /**
         * are we currently inside of a sequence?
         * type of action ("keyup" or "keydown" or "keypress") or false
         *
         * @type {boolean|string}
         */
        var _nextExpectedAction = false;

        /**
         * resets all sequence counters except for the ones passed in
         *
         * @param {Object} doNotReset
         * @returns void
         */
        function _resetSequences(doNotReset) {
            doNotReset = doNotReset || {};

            var activeSequences = false,
                key;

            for (key in _sequenceLevels) {
                if (doNotReset[key]) {
                    activeSequences = true;
                    continue;
                }
                _sequenceLevels[key] = 0;
            }

            if (!activeSequences) {
                _nextExpectedAction = false;
            }
        }

        /**
         * finds all callbacks that match based on the keycode, modifiers,
         * and action
         *
         * @param {string} character
         * @param {Array} modifiers
         * @param {Event|Object} e
         * @param {string=} sequenceName - name of the sequence we are looking for
         * @param {string=} combination
         * @param {number=} level
         * @returns {Array}
         */
        function _getMatches(character, modifiers, e, sequenceName, combination, level) {
            var i;
            var callback;
            var matches = [];
            var action = e.type;

            // if there are no events related to this keycode
            if (!self._callbacks[character]) {
                return [];
            }

            // if a modifier key is coming up on its own we should allow it
            if (action == 'keyup' && _isModifier(character)) {
                modifiers = [character];
            }

            // loop through all callbacks for the key that was pressed
            // and see if any of them match
            for (i = 0; i < self._callbacks[character].length; ++i) {
                callback = self._callbacks[character][i];

                // if a sequence name is not specified, but this is a sequence at
                // the wrong level then move onto the next match
                if (!sequenceName && callback.seq && _sequenceLevels[callback.seq] != callback.level) {
                    continue;
                }

                // if the action we are looking for doesn't match the action we got
                // then we should keep going
                if (action != callback.action) {
                    continue;
                }

                // if this is a keypress event and the meta key and control key
                // are not pressed that means that we need to only look at the
                // character, otherwise check the modifiers as well
                //
                // chrome will not fire a keypress if meta or control is down
                // safari will fire a keypress if meta or meta+shift is down
                // firefox will fire a keypress if meta or control is down
                if ((action == 'keypress' && !e.metaKey && !e.ctrlKey) || _modifiersMatch(modifiers, callback.modifiers)) {

                    // when you bind a combination or sequence a second time it
                    // should overwrite the first one.  if a sequenceName or
                    // combination is specified in this call it does just that
                    //
                    // @todo make deleting its own method?
                    var deleteCombo = !sequenceName && callback.combo == combination;
                    var deleteSequence = sequenceName && callback.seq == sequenceName && callback.level == level;
                    if (deleteCombo || deleteSequence) {
                        self._callbacks[character].splice(i, 1);
                    }

                    matches.push(callback);
                }
            }

            return matches;
        }

        /**
         * actually calls the callback function
         *
         * if your callback function returns false this will use the jquery
         * convention - prevent default and stop propogation on the event
         *
         * @param {Function} callback
         * @param {Event} e
         * @returns void
         */
        function _fireCallback(callback, e, combo, sequence) {

            // if this event should not happen stop here
            if (self.stopCallback(e, e.target || e.srcElement, combo, sequence)) {
                return;
            }

            if (callback(e, combo) === false) {
                _preventDefault(e);
                _stopPropagation(e);
            }
        }

        /**
         * handles a character key event
         *
         * @param {string} character
         * @param {Array} modifiers
         * @param {Event} e
         * @returns void
         */
        self._handleKey = function(character, modifiers, e) {
            var callbacks = _getMatches(character, modifiers, e);
            var i;
            var doNotReset = {};
            var maxLevel = 0;
            var processedSequenceCallback = false;

            // Calculate the maxLevel for sequences so we can only execute the longest callback sequence
            for (i = 0; i < callbacks.length; ++i) {
                if (callbacks[i].seq) {
                    maxLevel = Math.max(maxLevel, callbacks[i].level);
                }
            }

            // loop through matching callbacks for this key event
            for (i = 0; i < callbacks.length; ++i) {

                // fire for all sequence callbacks
                // this is because if for example you have multiple sequences
                // bound such as "g i" and "g t" they both need to fire the
                // callback for matching g cause otherwise you can only ever
                // match the first one
                if (callbacks[i].seq) {

                    // only fire callbacks for the maxLevel to prevent
                    // subsequences from also firing
                    //
                    // for example 'a option b' should not cause 'option b' to fire
                    // even though 'option b' is part of the other sequence
                    //
                    // any sequences that do not match here will be discarded
                    // below by the _resetSequences call
                    if (callbacks[i].level != maxLevel) {
                        continue;
                    }

                    processedSequenceCallback = true;

                    // keep a list of which sequences were matches for later
                    doNotReset[callbacks[i].seq] = 1;
                    _fireCallback(callbacks[i].callback, e, callbacks[i].combo, callbacks[i].seq);
                    continue;
                }

                // if there were no sequence matches but we are still here
                // that means this is a regular match so we should fire that
                if (!processedSequenceCallback) {
                    _fireCallback(callbacks[i].callback, e, callbacks[i].combo);
                }
            }

            // if the key you pressed matches the type of sequence without
            // being a modifier (ie "keyup" or "keypress") then we should
            // reset all sequences that were not matched by this event
            //
            // this is so, for example, if you have the sequence "h a t" and you
            // type "h e a r t" it does not match.  in this case the "e" will
            // cause the sequence to reset
            //
            // modifier keys are ignored because you can have a sequence
            // that contains modifiers such as "enter ctrl+space" and in most
            // cases the modifier key will be pressed before the next key
            //
            // also if you have a sequence such as "ctrl+b a" then pressing the
            // "b" key will trigger a "keypress" and a "keydown"
            //
            // the "keydown" is expected when there is a modifier, but the
            // "keypress" ends up matching the _nextExpectedAction since it occurs
            // after and that causes the sequence to reset
            //
            // we ignore keypresses in a sequence that directly follow a keydown
            // for the same character
            var ignoreThisKeypress = e.type == 'keypress' && _ignoreNextKeypress;
            if (e.type == _nextExpectedAction && !_isModifier(character) && !ignoreThisKeypress) {
                _resetSequences(doNotReset);
            }

            _ignoreNextKeypress = processedSequenceCallback && e.type == 'keydown';
        };

        /**
         * handles a keydown event
         *
         * @param {Event} e
         * @returns void
         */
        function _handleKeyEvent(e) {

            // normalize e.which for key events
            // @see http://stackoverflow.com/questions/4285627/javascript-keycode-vs-charcode-utter-confusion
            if (typeof e.which !== 'number') {
                e.which = e.keyCode;
            }

            var character = _characterFromEvent(e);

            // no character found then stop
            if (!character) {
                return;
            }

            // need to use === for the character check because the character can be 0
            if (e.type == 'keyup' && _ignoreNextKeyup === character) {
                _ignoreNextKeyup = false;
                return;
            }

            self.handleKey(character, _eventModifiers(e), e);
        }

        /**
         * called to set a 1 second timeout on the specified sequence
         *
         * this is so after each key press in the sequence you have 1 second
         * to press the next key before you have to start over
         *
         * @returns void
         */
        function _resetSequenceTimer() {
            clearTimeout(_resetTimer);
            _resetTimer = setTimeout(_resetSequences, 1000);
        }

        /**
         * binds a key sequence to an event
         *
         * @param {string} combo - combo specified in bind call
         * @param {Array} keys
         * @param {Function} callback
         * @param {string=} action
         * @returns void
         */
        function _bindSequence(combo, keys, callback, action) {

            // start off by adding a sequence level record for this combination
            // and setting the level to 0
            _sequenceLevels[combo] = 0;

            /**
             * callback to increase the sequence level for this sequence and reset
             * all other sequences that were active
             *
             * @param {string} nextAction
             * @returns {Function}
             */
            function _increaseSequence(nextAction) {
                return function() {
                    _nextExpectedAction = nextAction;
                    ++_sequenceLevels[combo];
                    _resetSequenceTimer();
                };
            }

            /**
             * wraps the specified callback inside of another function in order
             * to reset all sequence counters as soon as this sequence is done
             *
             * @param {Event} e
             * @returns void
             */
            function _callbackAndReset(e) {
                _fireCallback(callback, e, combo);

                // we should ignore the next key up if the action is key down
                // or keypress.  this is so if you finish a sequence and
                // release the key the final key will not trigger a keyup
                if (action !== 'keyup') {
                    _ignoreNextKeyup = _characterFromEvent(e);
                }

                // weird race condition if a sequence ends with the key
                // another sequence begins with
                setTimeout(_resetSequences, 10);
            }

            // loop through keys one at a time and bind the appropriate callback
            // function.  for any key leading up to the final one it should
            // increase the sequence. after the final, it should reset all sequences
            //
            // if an action is specified in the original bind call then that will
            // be used throughout.  otherwise we will pass the action that the
            // next key in the sequence should match.  this allows a sequence
            // to mix and match keypress and keydown events depending on which
            // ones are better suited to the key provided
            for (var i = 0; i < keys.length; ++i) {
                var isFinal = i + 1 === keys.length;
                var wrappedCallback = isFinal ? _callbackAndReset : _increaseSequence(action || _getKeyInfo(keys[i + 1]).action);
                _bindSingle(keys[i], wrappedCallback, action, combo, i);
            }
        }

        /**
         * binds a single keyboard combination
         *
         * @param {string} combination
         * @param {Function} callback
         * @param {string=} action
         * @param {string=} sequenceName - name of sequence if part of sequence
         * @param {number=} level - what part of the sequence the command is
         * @returns void
         */
        function _bindSingle(combination, callback, action, sequenceName, level) {

            // store a direct mapped reference for use with Mousetrap.trigger
            self._directMap[combination + ':' + action] = callback;

            // make sure multiple spaces in a row become a single space
            combination = combination.replace(/\s+/g, ' ');

            var sequence = combination.split(' ');
            var info;

            // if this pattern is a sequence of keys then run through this method
            // to reprocess each pattern one key at a time
            if (sequence.length > 1) {
                _bindSequence(combination, sequence, callback, action);
                return;
            }

            info = _getKeyInfo(combination, action);

            // make sure to initialize array if this is the first time
            // a callback is added for this key
            self._callbacks[info.key] = self._callbacks[info.key] || [];

            // remove an existing match if there is one
            _getMatches(info.key, info.modifiers, {type: info.action}, sequenceName, combination, level);

            // add this call back to the array
            // if it is a sequence put it at the beginning
            // if not put it at the end
            //
            // this is important because the way these are processed expects
            // the sequence ones to come first
            self._callbacks[info.key][sequenceName ? 'unshift' : 'push']({
                callback: callback,
                modifiers: info.modifiers,
                action: info.action,
                seq: sequenceName,
                level: level,
                combo: combination
            });
        }

        /**
         * binds multiple combinations to the same callback
         *
         * @param {Array} combinations
         * @param {Function} callback
         * @param {string|undefined} action
         * @returns void
         */
        self._bindMultiple = function(combinations, callback, action) {
            for (var i = 0; i < combinations.length; ++i) {
                _bindSingle(combinations[i], callback, action);
            }
        };

        // start!
        _addEvent(targetElement, 'keypress', _handleKeyEvent);
        _addEvent(targetElement, 'keydown', _handleKeyEvent);
        _addEvent(targetElement, 'keyup', _handleKeyEvent);
    }

    /**
     * binds an event to mousetrap
     *
     * can be a single key, a combination of keys separated with +,
     * an array of keys, or a sequence of keys separated by spaces
     *
     * be sure to list the modifier keys first to make sure that the
     * correct key ends up getting bound (the last key in the pattern)
     *
     * @param {string|Array} keys
     * @param {Function} callback
     * @param {string=} action - 'keypress', 'keydown', or 'keyup'
     * @returns void
     */
    Mousetrap.prototype.bind = function(keys, callback, action) {
        var self = this;
        keys = keys instanceof Array ? keys : [keys];
        self._bindMultiple.call(self, keys, callback, action);
        return self;
    };

    /**
     * unbinds an event to mousetrap
     *
     * the unbinding sets the callback function of the specified key combo
     * to an empty function and deletes the corresponding key in the
     * _directMap dict.
     *
     * TODO: actually remove this from the _callbacks dictionary instead
     * of binding an empty function
     *
     * the keycombo+action has to be exactly the same as
     * it was defined in the bind method
     *
     * @param {string|Array} keys
     * @param {string} action
     * @returns void
     */
    Mousetrap.prototype.unbind = function(keys, action) {
        var self = this;
        return self.bind.call(self, keys, function() {}, action);
    };

    /**
     * triggers an event that has already been bound
     *
     * @param {string} keys
     * @param {string=} action
     * @returns void
     */
    Mousetrap.prototype.trigger = function(keys, action) {
        var self = this;
        if (self._directMap[keys + ':' + action]) {
            self._directMap[keys + ':' + action]({}, keys);
        }
        return self;
    };

    /**
     * resets the library back to its initial state.  this is useful
     * if you want to clear out the current keyboard shortcuts and bind
     * new ones - for example if you switch to another page
     *
     * @returns void
     */
    Mousetrap.prototype.reset = function() {
        var self = this;
        self._callbacks = {};
        self._directMap = {};
        return self;
    };

    /**
     * should we stop this event before firing off callbacks
     *
     * @param {Event} e
     * @param {Element} element
     * @return {boolean}
     */
    Mousetrap.prototype.stopCallback = function(e, element) {
        var self = this;

        // if the element has the class "mousetrap" then no need to stop
        if ((' ' + element.className + ' ').indexOf(' mousetrap ') > -1) {
            return false;
        }

        if (_belongsTo(element, self.target)) {
            return false;
        }

        // Events originating from a shadow DOM are re-targetted and `e.target` is the shadow host,
        // not the initial event target in the shadow tree. Note that not all events cross the
        // shadow boundary.
        // For shadow trees with `mode: 'open'`, the initial event target is the first element in
        // the event’s composed path. For shadow trees with `mode: 'closed'`, the initial event
        // target cannot be obtained.
        if ('composedPath' in e && typeof e.composedPath === 'function') {
            // For open shadow trees, update `element` so that the following check works.
            var initialEventTarget = e.composedPath()[0];
            if (initialEventTarget !== e.target) {
                element = initialEventTarget;
            }
        }

        // stop for input, select, and textarea
        return element.tagName == 'INPUT' || element.tagName == 'SELECT' || element.tagName == 'TEXTAREA' || element.isContentEditable;
    };

    /**
     * exposes _handleKey publicly so it can be overwritten by extensions
     */
    Mousetrap.prototype.handleKey = function() {
        var self = this;
        return self._handleKey.apply(self, arguments);
    };

    /**
     * allow custom key mappings
     */
    Mousetrap.addKeycodes = function(object) {
        for (var key in object) {
            if (object.hasOwnProperty(key)) {
                _MAP[key] = object[key];
            }
        }
        _REVERSE_MAP = null;
    };

    /**
     * Init the global mousetrap functions
     *
     * This method is needed to allow the global mousetrap functions to work
     * now that mousetrap is a constructor function.
     */
    Mousetrap.init = function() {
        var documentMousetrap = Mousetrap(document);
        for (var method in documentMousetrap) {
            if (method.charAt(0) !== '_') {
                Mousetrap[method] = (function(method) {
                    return function() {
                        return documentMousetrap[method].apply(documentMousetrap, arguments);
                    };
                } (method));
            }
        }
    };

    Mousetrap.init();

    // expose mousetrap to the global object
    window.Mousetrap = Mousetrap;

    // expose as a common js module
    if ( true && module.exports) {
        module.exports = Mousetrap;
    }

    // expose mousetrap as an AMD module
    if (true) {
        !(__WEBPACK_AMD_DEFINE_RESULT__ = (function() {
            return Mousetrap;
        }).call(exports, __webpack_require__, exports, module),
		__WEBPACK_AMD_DEFINE_RESULT__ !== undefined && (module.exports = __WEBPACK_AMD_DEFINE_RESULT__));
    }
}) (typeof window !== 'undefined' ? window : null, typeof  window !== 'undefined' ? document : null);


/***/ })

/******/ 	});
/************************************************************************/
/******/ 	// The module cache
/******/ 	var __webpack_module_cache__ = {};
/******/ 	
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/ 		// Check if module is in cache
/******/ 		var cachedModule = __webpack_module_cache__[moduleId];
/******/ 		if (cachedModule !== undefined) {
/******/ 			return cachedModule.exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = __webpack_module_cache__[moduleId] = {
/******/ 			// no module.id needed
/******/ 			// no module.loaded needed
/******/ 			exports: {}
/******/ 		};
/******/ 	
/******/ 		// Execute the module function
/******/ 		__webpack_modules__[moduleId](module, module.exports, __webpack_require__);
/******/ 	
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/ 	
/******/ 	// expose the modules object (__webpack_modules__)
/******/ 	__webpack_require__.m = __webpack_modules__;
/******/ 	
/************************************************************************/
/******/ 	/* webpack/runtime/chunk loaded */
/******/ 	(() => {
/******/ 		var deferred = [];
/******/ 		__webpack_require__.O = (result, chunkIds, fn, priority) => {
/******/ 			if(chunkIds) {
/******/ 				priority = priority || 0;
/******/ 				for(var i = deferred.length; i > 0 && deferred[i - 1][2] > priority; i--) deferred[i] = deferred[i - 1];
/******/ 				deferred[i] = [chunkIds, fn, priority];
/******/ 				return;
/******/ 			}
/******/ 			var notFulfilled = Infinity;
/******/ 			for (var i = 0; i < deferred.length; i++) {
/******/ 				var [chunkIds, fn, priority] = deferred[i];
/******/ 				var fulfilled = true;
/******/ 				for (var j = 0; j < chunkIds.length; j++) {
/******/ 					if ((priority & 1 === 0 || notFulfilled >= priority) && Object.keys(__webpack_require__.O).every((key) => (__webpack_require__.O[key](chunkIds[j])))) {
/******/ 						chunkIds.splice(j--, 1);
/******/ 					} else {
/******/ 						fulfilled = false;
/******/ 						if(priority < notFulfilled) notFulfilled = priority;
/******/ 					}
/******/ 				}
/******/ 				if(fulfilled) {
/******/ 					deferred.splice(i--, 1)
/******/ 					var r = fn();
/******/ 					if (r !== undefined) result = r;
/******/ 				}
/******/ 			}
/******/ 			return result;
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/compat get default export */
/******/ 	(() => {
/******/ 		// getDefaultExport function for compatibility with non-harmony modules
/******/ 		__webpack_require__.n = (module) => {
/******/ 			var getter = module && module.__esModule ?
/******/ 				() => (module['default']) :
/******/ 				() => (module);
/******/ 			__webpack_require__.d(getter, { a: getter });
/******/ 			return getter;
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/define property getters */
/******/ 	(() => {
/******/ 		// define getter functions for harmony exports
/******/ 		__webpack_require__.d = (exports, definition) => {
/******/ 			for(var key in definition) {
/******/ 				if(__webpack_require__.o(definition, key) && !__webpack_require__.o(exports, key)) {
/******/ 					Object.defineProperty(exports, key, { enumerable: true, get: definition[key] });
/******/ 				}
/******/ 			}
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/hasOwnProperty shorthand */
/******/ 	(() => {
/******/ 		__webpack_require__.o = (obj, prop) => (Object.prototype.hasOwnProperty.call(obj, prop))
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/make namespace object */
/******/ 	(() => {
/******/ 		// define __esModule on exports
/******/ 		__webpack_require__.r = (exports) => {
/******/ 			if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 				Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 			}
/******/ 			Object.defineProperty(exports, '__esModule', { value: true });
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/jsonp chunk loading */
/******/ 	(() => {
/******/ 		// no baseURI
/******/ 		
/******/ 		// object to store loaded and loading chunks
/******/ 		// undefined = chunk not loaded, null = chunk preloaded/prefetched
/******/ 		// [resolve, reject, Promise] = chunk loading, 0 = chunk loaded
/******/ 		var installedChunks = {
/******/ 			"/js/app": 0,
/******/ 			"css/filepond": 0,
/******/ 			"css/app": 0
/******/ 		};
/******/ 		
/******/ 		// no chunk on demand loading
/******/ 		
/******/ 		// no prefetching
/******/ 		
/******/ 		// no preloaded
/******/ 		
/******/ 		// no HMR
/******/ 		
/******/ 		// no HMR manifest
/******/ 		
/******/ 		__webpack_require__.O.j = (chunkId) => (installedChunks[chunkId] === 0);
/******/ 		
/******/ 		// install a JSONP callback for chunk loading
/******/ 		var webpackJsonpCallback = (parentChunkLoadingFunction, data) => {
/******/ 			var [chunkIds, moreModules, runtime] = data;
/******/ 			// add "moreModules" to the modules object,
/******/ 			// then flag all "chunkIds" as loaded and fire callback
/******/ 			var moduleId, chunkId, i = 0;
/******/ 			if(chunkIds.some((id) => (installedChunks[id] !== 0))) {
/******/ 				for(moduleId in moreModules) {
/******/ 					if(__webpack_require__.o(moreModules, moduleId)) {
/******/ 						__webpack_require__.m[moduleId] = moreModules[moduleId];
/******/ 					}
/******/ 				}
/******/ 				if(runtime) var result = runtime(__webpack_require__);
/******/ 			}
/******/ 			if(parentChunkLoadingFunction) parentChunkLoadingFunction(data);
/******/ 			for(;i < chunkIds.length; i++) {
/******/ 				chunkId = chunkIds[i];
/******/ 				if(__webpack_require__.o(installedChunks, chunkId) && installedChunks[chunkId]) {
/******/ 					installedChunks[chunkId][0]();
/******/ 				}
/******/ 				installedChunks[chunkId] = 0;
/******/ 			}
/******/ 			return __webpack_require__.O(result);
/******/ 		}
/******/ 		
/******/ 		var chunkLoadingGlobal = self["webpackChunk"] = self["webpackChunk"] || [];
/******/ 		chunkLoadingGlobal.forEach(webpackJsonpCallback.bind(null, 0));
/******/ 		chunkLoadingGlobal.push = webpackJsonpCallback.bind(null, chunkLoadingGlobal.push.bind(chunkLoadingGlobal));
/******/ 	})();
/******/ 	
/************************************************************************/
/******/ 	
/******/ 	// startup
/******/ 	// Load entry module and return exports
/******/ 	// This entry module depends on other loaded chunks and execution need to be delayed
/******/ 	__webpack_require__.O(undefined, ["css/filepond","css/app"], () => (__webpack_require__("./resources/assets/js/app.js")))
/******/ 	__webpack_require__.O(undefined, ["css/filepond","css/app"], () => (__webpack_require__("./resources/assets/scss/app.scss")))
/******/ 	var __webpack_exports__ = __webpack_require__.O(undefined, ["css/filepond","css/app"], () => (__webpack_require__("./resources/css/filepond.css")))
/******/ 	__webpack_exports__ = __webpack_require__.O(__webpack_exports__);
/******/ 	
/******/ })()
;