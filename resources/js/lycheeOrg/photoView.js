export default { photoView };

export function photoView(detailsOpen_val, isFullscreen_val, has_description_val, overlayType_val, canEdit_val = false) {
	return {
		detailsOpen: detailsOpen_val,
		isFullscreen: isFullscreen_val,
		has_description: has_description_val,
		overlayType: overlayType_val,
		editOpen: false,
		donwloadOpen: false,
		canEdit: canEdit_val,

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

			// f
			if (event.keyCode === 70) {
				this.silentToggle("isFullscreen");
				return;
			}

			// o
			if (event.keyCode === 79) {
				this.rotateOverlay();
				return;
			}

			// i
			if (event.keyCode === 73) {
				this.detailsOpen = !this.detailsOpen;
				this.editOpen = false;
				return;
			}

			if (!this.canEdit) {
				console.log("can't edit.");
				return;
			}

			// del (46) or backspace (8)
			if (event.ctrlKey && (event.keyCode === 46 || event.keyCode === 8)) {
				this.$wire.delete();
				return;
			}

			// e
			if (event.keyCode === 69) {
				this.detailsOpen = false;
				this.editOpen = !this.editOpen;
				return;
			}

			// m
			if (event.keyCode === 77) {
				this.$wire.move();
				return;
			}

			// s
			if (event.keyCode === 83) {
				this.$wire.set_star();
				return;
			}

			// ctrl + left arrow
			if (event.keyCode === 37 && event.ctrlKey) {
				this.$wire.rotate_ccw();
				return;
			}

			// ctrl + right arrow
			if (event.keyCode === 39 && event.ctrlKey) {
				this.$wire.rotate_cw();
				return;
			}
		},
	};
}
