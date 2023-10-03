import "lazysizes";
import Mousetrap from "mousetrap";
import "mousetrap-global-bind";

import { loginWebAuthn, registerWebAuthn } from "./lycheeOrg/webauthn.js";
import { upload } from "./lycheeOrg/upload.js";
import { albumView } from "./lycheeOrg/albumView.js";
import { photoView } from "./lycheeOrg/photoView.js";
import { mapView } from "./lycheeOrg/mapView.js";
import { justify } from "./lycheeOrg/layouts/justifyLayout.js";
import { masonry } from "./lycheeOrg/layouts/masonryLayout.js";
import { grid } from "./lycheeOrg/layouts/gridLayout.js";
import { sidebarView } from "./lycheeOrg/sidebarView.js";

// Keyboard
Mousetrap.addKeycodes({
	18: "ContextMenu",
	179: "play_pause",
	227: "rewind",
	228: "forward",
});

document.addEventListener("alpine:init", () => {
	Alpine.data("loginWebAuthn", loginWebAuthn);
	Alpine.data("registerWebAuthn", registerWebAuthn);
	Alpine.data("upload", upload);
	Alpine.data("albumView", albumView);
	Alpine.data("photoView", photoView);
	Alpine.data("mapView", mapView);
	Alpine.data("sidebarView", sidebarView);
	Alpine.directive("masonry", masonry);
	Alpine.directive("justify", justify);
	Alpine.directive("grid", grid);
});
