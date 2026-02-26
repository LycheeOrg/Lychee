import Constants from "@/services/constants";

export function useImageHelpers() {
	function isNotEmpty(link: string | null | undefined): link is string {
		return link !== "" && link !== null && link !== undefined;
	}

	function getPlayIcon(): string {
		return Constants.BASE_URL + "/img/play-icon.png";
	}

	function getPlaceholderIcon(): string {
		return Constants.BASE_URL + "/img/placeholder.png";
	}

	function getNoImageIcon(): string {
		return Constants.BASE_URL + "/img/no_images.svg";
	}

	function getPaswwordIcon(): string {
		return Constants.BASE_URL + "/img/password.svg";
	}

	return {
		isNotEmpty,
		getPlayIcon,
		getPlaceholderIcon,
		getNoImageIcon,
		getPaswwordIcon,
	};
}

export function useLtRorRtL() {
	function isLTR(): boolean {
		return document.documentElement.dir !== "rtl";
	}

	function isRTL(): boolean {
		return document.documentElement.dir === "rtl";
	}

	return {
		isLTR,
		isRTL,
	};
}

export const EmptyAlbumCallbacks = {
	setAsCover: () => {},
	toggleRename: () => {},
	toggleMerge: () => {},
	toggleMove: () => {},
	toggleDelete: () => {},
	toggleDownload: () => {},
	togglePin: () => {},
	toggleApplyRenamer: () => {},
};

export const EmptyPhotoCallbacks = {
	star: () => {},
	unstar: () => {},
	setAsCover: () => {},
	setAsHeader: () => {},
	toggleTag: () => {},
	toggleLicense: () => {},
	toggleRename: () => {},
	toggleCopyTo: () => {},
	toggleMove: () => {},
	toggleDelete: () => {},
	toggleDownload: () => {},
	toggleApplyRenamer: () => {},
};
