export default class AlbumFlags {
	isDetailsOpen: boolean;
	activeTab: number;
	isSharingLinksOpen: boolean;
	areNsfwVisible: boolean;

	constructor(areNsfwVisible: boolean) {
		this.isDetailsOpen = false;
		this.activeTab = 0;
		this.isSharingLinksOpen = false;
		this.areNsfwVisible = areNsfwVisible;
	}
}
