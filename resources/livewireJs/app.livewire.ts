import "lazysizes";

import { webauthn } from "./data/webauthn";
import { views } from "./data/views";
import { panels } from "./data/panel";
import { qrBuilder } from "./data/qrcode/qrBuilder";

// suggested in the Alpine docs:
// make Alpine on window available for better DX
window.Alpine = Alpine;

document.addEventListener("alpine:init", () => {
	[...webauthn, ...views, ...panels].forEach(Alpine.plugin);
	Alpine.plugin(qrBuilder);

	Alpine.store("photo", undefined);
	Alpine.store("photos", []);
	Alpine.store("photoIDs", []);
	Alpine.store("albumIDs", []);
});
