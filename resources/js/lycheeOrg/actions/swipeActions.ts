import { AlbumView } from "@/data/views/types";
import TinyGesture from "tinygesture";

export default class SwipeActions {
	gesture: TinyGesture;
	listeners: any[];

	constructor() {
		// Options object is optional. These are the defaults.
		const options = {
			// Used to calculate the threshold to consider a movement a swipe. it is
			// passed type of 'x' or 'y'.
			threshold: (type: "x" | "y", _self: any) =>
				Math.max(
					25,
					Math.floor(
						type === "x"
							? 0.25 * (window.innerWidth || document.body.clientWidth)
							: 0.5 * (window.innerHeight || document.body.clientHeight),
					),
				),
			// Minimum velocity the gesture must be moving when the gesture ends to be
			// considered a swipe.
			velocityThreshold: 10,
			// Used to calculate the distance threshold to ignore the gestures velocity
			// and always consider it a swipe.
			disregardVelocityThreshold: (type: "x" | "y", self: any) =>
				Math.floor(0.5 * (type === "x" ? self.element.clientWidth : self.element.clientHeight)),
			// Point at which the pointer moved too much to consider it a tap or longpress
			// gesture.
			pressThreshold: 8,
			// If true, swiping in a diagonal direction will fire both a horizontal and a
			// vertical swipe.
			// If false, whichever direction the pointer moved more will be the only swipe
			// fired.
			diagonalSwipes: false,
			// The degree limit to consider a diagonal swipe when diagonalSwipes is true.
			// It's calculated as 45deg±diagonalLimit.
			diagonalLimit: 15,
			// Listen to mouse events in addition to touch events. (For desktop support.)
			mouseSupport: true,
		};

		const target = document.getElementsByTagName("body")[0];
		this.gesture = new TinyGesture(target, options);
		this.listeners = [];
	}

	register(view: AlbumView) {
		this.listeners.push(
			this.gesture.on("swipeleft", () => {
				if (view.photo_id !== null) {
					console.log("swipeleft");
					view.previous();
				}
			}),
		);
		this.listeners.push(
			this.gesture.on("swiperight", () => {
				if (view.photo_id !== null) {
					console.log("swiperight");
					view.next();
				}
			}),
		);
		this.listeners.push(
			this.gesture.on("swipeup", () => {
				if (view.photo_id !== null) {
					console.log("swipeup");
					view.goTo(null);
				}
			}),
		);
	}
}
