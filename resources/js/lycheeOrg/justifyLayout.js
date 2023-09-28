export default { useJustifyLayout };

export function useJustifyLayout(el) {
	const view = document.getElementById("lychee_view_content");
	const multiplier = view.scrollHeight > view.clientHeight ? 0 : 1;
	const containerWidth = parseFloat(getComputedStyle(el).width) - 20 * multiplier;
	const photoDefaultHeight = 320; // make this a parameter later
	const justifiedItems = [...el.childNodes].filter((gridItem) => gridItem.nodeType === 1);
	justifiedItems.forEach((e) => {
		e.dataset.width;
		e.dataset.height;
	});

	/** @type {number[]} */
	const ratio = justifiedItems.map(function (_photo) {
		const height = _photo.dataset.height;
		const width = _photo.dataset.width;
		return height > 0 ? width / height : 1;
	});
	const layoutGeometry = require("justified-layout")(ratio, {
		containerWidth: containerWidth,
		containerPadding: 0,
		targetRowHeight: photoDefaultHeight,
	});
	el.style.height = layoutGeometry.containerHeight + "px";
	justifiedItems.forEach((e, i) => {
		if (!layoutGeometry.boxes[i]) {
			// Race condition in search.find -- window content
			// and `photos` can get out of sync as search
			// query is being modified.
			return false;
		}
		e.style.top = layoutGeometry.boxes[i].top + "px";
		e.style.width = layoutGeometry.boxes[i].width + "px";
		e.style.height = layoutGeometry.boxes[i].height + "px";
		e.style.left = layoutGeometry.boxes[i].left + "px";
	});

	// 	const imgs = $(this)
	// 		.css({
	// 			top: layoutGeometry.boxes[i].top + "px",
	// 			width: layoutGeometry.boxes[i].width + "px",
	// 			height: layoutGeometry.boxes[i].height + "px",
	// 			left: layoutGeometry.boxes[i].left + "px",
	// 		})
	// 		.find(".thumbimg > img");

	// 	if (imgs.length > 0 && imgs[0].getAttribute("data-srcset")) {
	// 		imgs[0].setAttribute("sizes", layoutGeometry.boxes[i].width + "px");
	// 	}
	// });
}
