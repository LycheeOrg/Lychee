import QRCode from "qrcode";
import type { Alpine } from "alpinejs";

export const qrBuilder = (Alpine: Alpine) =>
	Alpine.data("qrBuilder", () => ({
		qrCodeOpen: false,

		setQrCode(url: string): void {
			QRCode.toCanvas(
				document.getElementById("canvas"),
				url,
				{
					errorCorrectionLevel: "H",
					// fill: '#000000',
					// background: '#FFFFFF',
					// size: 300,
				},
				function (err: Error | null | undefined) {
					if (err) throw err;
				},
			);
		},
	}));
