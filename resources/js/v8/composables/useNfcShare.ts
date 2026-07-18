/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

import { trans } from "laravel-vue-i18n";
import { useAppToast } from "@/v8/composables/useAppToast";

/**
 * Minimal ambient typing for the Web NFC API. Not part of TypeScript's bundled
 * DOM lib yet (https://developer.mozilla.org/en-US/docs/Web/API/Web_NFC_API),
 * so only the members Lychee actually calls are declared here.
 */
interface NDEFRecordInit {
	recordType: string;
	data?: string;
}

interface NDEFMessageInit {
	records: NDEFRecordInit[];
}

declare class NDEFReader {
	write(message: string | NDEFMessageInit, options?: { overwrite?: boolean; signal?: AbortSignal }): Promise<void>;
}

export function isNfcShareSupported(): boolean {
	return typeof window !== "undefined" && "NDEFReader" in window;
}

/**
 * Shares a URL over NFC by writing it to a tag as soon as the device is
 * tapped against one. Web NFC has no phone-to-phone "beam" primitive, so this
 * is the closest equivalent that works fully offline (no server round-trip).
 */
export function useNfcShare() {
	const toast = useAppToast();

	async function shareUrlViaNfc(url: string): Promise<void> {
		if (!isNfcShareSupported()) {
			toast.add({ severity: "error", summary: trans("dialogs.share_nfc.not_supported"), life: 4000 });
			return;
		}

		try {
			const reader = new NDEFReader();
			toast.add({ severity: "info", summary: trans("dialogs.share_nfc.prompt"), life: 4000 });
			await reader.write({ records: [{ recordType: "url", data: url }] });
			toast.add({ severity: "success", summary: trans("dialogs.share_nfc.success"), life: 3000 });
		} catch {
			toast.add({ severity: "error", summary: trans("dialogs.share_nfc.error"), life: 4000 });
		}
	}

	return { shareUrlViaNfc };
}
