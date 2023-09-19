// import Livewire from

export default { photoView };

export function photoView(detailsOpen_val, isFullscreen_val, has_description_val, overlayType_val) {
	return {
		detailsOpen: detailsOpen_val,
		isFullscreen: isFullscreen_val,
		has_description: has_description_val,
		overlayType: overlayType_val,
		editOpen: false,
		donwloadOpen: false,

		silentToggle(elem) {
			this[elem] = !this[elem];

			this.$wire.silentUpdate();
		},

		rotateOverlay() {
			switch (this.overlayType) {
				case "exif":
					this.overlayType = "date";
					break;
				case "date":
					if (this.has_description) {
						this.overlayType = "description";
					} else {
						this.overlayType = "none";
					}
					break;
				case "description":
					this.overlayType = "none";
					break;
				default:
					this.overlayType = "exif";
			}
		},

		handleKeydown(event) {
			const skipped = ["TEXTAREA", "INPUT", "SELECT"];

			if (skipped.includes(document.activeElement.nodeName)) {
				console.log("skipped: " + document.activeElement.nodeName);
				return;
			}
			console.log(document.activeElement.nodeName);

			// del (46) or backspace (8)
			if (event.ctrlKey && (event.keyCode === 46 || event.keyCode === 8)) {
				this.$wire.delete();
			}

			// i
			if (event.keyCode === 73) {
				this.detailsOpen = !this.detailsOpen;
				this.editOpen = false;
			}

			// e
			if (event.keyCode === 69) {
				this.detailsOpen = false;
				this.editOpen = !this.editOpen;
			}

			// f
			if (event.keyCode === 70) {
				this.silentToggle("isFullscreen");
			}

			// m
			if (event.keyCode === 77) {
				this.$wire.move();
			}

			// o
			if (event.keyCode === 79) {
				this.rotateOverlay();
			}

			// s
			if (event.keyCode === 83) {
				this.$wire.set_star();
			}

			// left arrow
			if (event.keyCode === 37 && event.ctrlKey) {
				this.$wire.rotate_ccw();
			}

			// right arrow
			if (event.keyCode === 39 && event.ctrlKey) {
				this.$wire.rotate_cw();
			}
		},
	};
}