import { AlbumRightsDTO, Photo } from "@/lycheeOrg/backend";
import { AlpineComponent } from "alpinejs";
import type Selection from "@/lycheeOrg/actions/selection";
import type AlbumActions from "@/lycheeOrg/actions/albumActions";
import type Keybindings from "@/lycheeOrg/actions/keybindings";
import AlbumFlagsView from "../../lycheeOrg/flags/albumFlags";
import PhotoFlagsView from "../../lycheeOrg/flags/photoFlags";
import PhotoLayout from "@/lycheeOrg/layouts/PhotoLayout";
import SwipeActions from "@/lycheeOrg/actions/swipeActions";

export interface PhotoArray {
	[key: string]: Photo;
}

export type AlbumView = AlpineComponent<{
	// Base URL for the history.
	base_url: string;

	// Wether we are in search or gallery mode
	isSearch: boolean;

	// selection Object: contains list of current photos & albums
	select: Selection;

	// set of actions
	actions: AlbumActions;

	// global keybindings
	keybinds: Keybindings;

	// Set of flags in the Album view
	albumFlags: AlbumFlagsView;

	// set of flags in the Photo view
	photoFlags: PhotoFlagsView;

	// Whether Login is open
	// TODO: double check me
	loginModalOpen: boolean;

	// Parent id: current album id or null
	parent_id: string | null;
	// Current rights applied to this album view
	rights: AlbumRightsDTO;

	// Photo id: current photo id or null
	photo_id: string | null;

	// Flag whether we are in full screen mode
	isFullscreen: boolean;

	// Photo layout information in album (justified, masonry etc.)
	photoLayout: PhotoLayout;

	// Swipe actions
	swiper: SwipeActions;

	toggleFullScreen: () => void;
	toggleNSFW: () => void;
	toggleDetails: () => void;

	handlePopState: (event: PopStateEvent) => void;
	handleContextPhoto: (event: MouseEvent) => void;
	handleClickPhoto: (event: MouseEvent) => void;
	handleContextAlbum: (event: MouseEvent) => void;
	handleKeydown: (event: KeyboardEvent) => void;

	goTo(photoId: string | null): void;
	history(obj: object, url: string): void;

	// Album actions (right click)
	moveAlbums: () => void;
	mergeAlbums: () => void;
	renameAlbums: () => void;
	deleteAlbums: () => void;
	donwloadAlbums: () => void;

	// Photo actions (right click)
	copyPhotosTo: () => void;
	movePhotos: () => void;
	renamePhotos: () => void;
	deletePhotos: () => void;
	tagPhotos: () => void;
	starPhotos: () => void;
	unstarPhotos: () => void;
	setCover: () => void;
	donwloadPhotos: () => void;

	// Photo View
	rotateOverlay: () => void;
	previous: () => void;
	next: () => void;
	toggleStar: () => void;
	movePhoto: () => void;
	deletePhoto: () => void;
	downloadPhoto: () => void;
	rotatePhotoCCW: () => void;
	rotatePhotoCW: () => void;
}>;

export type UploadEvent = Event & { detail: { progress: number } };

export type Livewire = {
	dispatch: (event: string) => void;
	close: () => void;
	upload: (filename: string, file: File, succes: (filename: string) => void, error: () => void, progress: (event: UploadEvent) => void) => void;
	set: (property: string, value: any) => void;
};

export type UploadView = AlpineComponent<{
	isDropping: boolean;
	chunkSize: number;
	hasErrorOccurred: boolean;
	upload_processing_limit: number;
	chnkStarts: number[];
	fileList: any;
	progress: number[];
	numChunks: number[];

	/**
	 * The number of requests which are "on the fly", i.e. for which a
	 * response has not yet completely been received.
	 *
	 * Note, that Lychee supports a restricted kind of "parallelism"
	 * which is limited by the configuration option
	 * `lychee.upload_processing_limit`:
	 * While always only a single file is uploaded at once, upload of the
	 * next file already starts after transmission of the previous file
	 * has been finished, the response to the previous file might still be
	 * outstanding as the uploaded file is processed at the server-side.
	 */
	outstandingResponsesCount: number;
	/**
	 * The latest (aka highest) index of a file which is being or has
	 * been uploaded to the server.
	 */
	latestFileIdx: number;
	/**
	 * Semaphore whether a file is currently being uploaded.
	 *
	 * This is used as a semaphore to serialize the upload transmissions
	 * between several instances of the method {@link process}.
	 */
	isUploadRunning: boolean;

	/**
	 * This callback is invoked when the last file has been processed.
	 *
	 * It closes the modal dialog or shows the close button and
	 * reloads the album.
	 */
	finish: (wire: Livewire, alpine: UploadView) => void;
	process: (fileIdx: number, wire: Livewire, alpine: UploadView) => void;
	complete: (wire: Livewire, alpine: UploadView) => void;
	/**
	 * Processes the upload and response for a single file.
	 *
	 * Note that up to `livewireUploadChunk` "instances" of
	 * this method can be "alive" simultaneously.
	 * The parameter `fileIdx` is limited by `latestFileIdx`.
	 *
	 * @param {number} fileIdx the index of the file being processed
	 * @param {Livewire} wire accessore to Livewire funtions
	 * @param {Alpine} alpine accessor to current Alpine object
	 */
	livewireUploadChunk: (fileIdx: number, wire: Livewire, alpine: UploadView) => void;
	start: (wire: Livewire, alpine: UploadView) => void;
}>;

export type DropboxView = AlpineComponent<{
	urlArea: string;
	progress: string;

	chooseFromDropbox: () => void;
	send: () => void;
}>;
