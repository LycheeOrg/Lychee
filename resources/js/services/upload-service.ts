import axios, { AxiosProgressEvent, AxiosRequestConfig, type AxiosResponse } from "axios";
import Constants from "./constants";

export type UploadData = {
	album_id: string | null;
	file_last_modified_time: number | null;
	file: Blob;
	meta: App.Http.Resources.Editable.UploadMetaResource;

	onUploadProgress: (e: AxiosProgressEvent) => void;
};

const UploadService = {
	upload(info: UploadData): Promise<AxiosResponse<App.Http.Resources.Editable.UploadMetaResource>> {
		const formData = new FormData();

		formData.append("file", info.file, info.meta.file_name);
		formData.append("file_name", info.meta.file_name);
		formData.append("album_id", info.album_id ?? "");
		formData.append("file_last_modified_time", info.file_last_modified_time?.toString() ?? "");
		formData.append("uuid_name", info.meta.uuid_name ?? "");
		formData.append("extension", info.meta.extension ?? "");
		formData.append("chunk_number", info.meta.chunk_number?.toString() ?? "");
		formData.append("total_chunks", info.meta.total_chunks?.toString() ?? "");

		const config: AxiosRequestConfig<FormData> = {
			onUploadProgress: info.onUploadProgress,
			headers: {
				"Content-Type": "application/json",
			},
			transformRequest: [(data) => data],
		};

		return axios.post(`${Constants.API_URL}Photo`, formData, config);
	},
};

export default UploadService;

// /**
//  * @param {string} title
//  * @param {string} [text=""]
//  * @returns {void}
//  */
// upload.notify = function (title, text = "") {
// 	if (text === "") text = lychee.locale["UPLOAD_MANAGE_NEW_PHOTOS"];

// 	if (!window.webkitNotifications) return;

// 	if (window.webkitNotifications.checkPermission() !== 0) window.webkitNotifications.requestPermission();

// 	if (window.webkitNotifications.checkPermission() === 0 && title) {
// 		let popup = window.webkitNotifications.createNotification("", title, text);
// 		popup.show();
// 	}
// };

// upload.start = {
// 	/**
// 	 * @param {(FileList|File[])} files
// 	 */
// 	local: function (files) {
// 		if (files.length <= 0) return;

// 		const albumID = album.getID();
// 		let hasErrorOccurred = false;
// 		let hasWarningOccurred = false;
// 		/**
// 		 * The number of requests which are "on the fly", i.e. for which a
// 		 * response has not yet completely been received.
// 		 *
// 		 * Note, that Lychee supports a restricted kind of "parallelism"
// 		 * which is limited by the configuration option
// 		 * `lychee.upload_processing_limit`:
// 		 * While always only a single file is uploaded at once, upload of the
// 		 * next file already starts after transmission of the previous file
// 		 * has been finished, the response to the previous file might still be
// 		 * outstanding as the uploaded file is processed at the server-side.
// 		 *
// 		 * @type {number}
// 		 */
// 		let outstandingResponsesCount = 0;
// 		/**
// 		 * The latest (aka highest) index of a file which is being or has
// 		 * been uploaded to the server.
// 		 *
// 		 * @type {number}
// 		 */
// 		let latestFileIdx = 0;
// 		/**
// 		 * Semaphore whether a file is currently being uploaded.
// 		 *
// 		 * This is used as a semaphore to serialize the upload transmissions
// 		 * between several instances of the method {@link process}.
// 		 *
// 		 * @type {boolean}
// 		 */
// 		let isUploadRunning = false;
// 		/**
// 		 * Semaphore whether a further upload shall be cancelled on the next
// 		 * occasion.
// 		 *
// 		 * @type {boolean}
// 		 */
// 		let shallCancelUpload = false;

// 		/**
// 		 * This callback is invoked when the last file has been processed.
// 		 *
// 		 * It closes the modal dialog or shows the close button and
// 		 * reloads the album.
// 		 */
// 		const finish = function () {
// 			window.onbeforeunload = null;

// 			$("#upload_files").val("");

// 			if (!hasErrorOccurred && !hasWarningOccurred) {
// 				// Success
// 				upload.closeProgressReportDialog();
// 				upload.notify(lychee.locale["UPLOAD_COMPLETE"]);
// 			} else if (!hasErrorOccurred && hasWarningOccurred) {
// 				// Warning
// 				upload.showProgressReportCloseButton();
// 				upload.notify(lychee.locale["UPLOAD_COMPLETE"]);
// 			} else {
// 				// Error
// 				upload.showProgressReportCloseButton();
// 				if (shallCancelUpload) {
// 					const row = upload.buildReportRow(lychee.locale["UPLOAD_GENERAL"]);
// 					row.status.textContent = lychee.locale["UPLOAD_CANCELLED"];
// 					row.status.classList.add("warning");
// 					upload._dom.reportList.appendChild(row.listEntry);
// 				}
// 				upload.notify(lychee.locale["UPLOAD_COMPLETE"], lychee.locale["UPLOAD_COMPLETE_FAILED"]);
// 			}

// 			album.reload();
// 		};

// 		/**
// 		 * Processes the upload and response for a single file.
// 		 *
// 		 * Note that up to `lychee.upload_processing_limit` "instances" of
// 		 * this method can be "alive" simultaneously.
// 		 * The parameter `fileIdx` is limited by `latestFileIdx`.
// 		 *
// 		 * @param {number} fileIdx the index of the file being processed
// 		 */
// 		const process = function (fileIdx) {
// 			/**
// 			 * The upload progress of the file with index `fileIdx` so far.
// 			 *
// 			 * @type {number}
// 			 */
// 			let uploadProgress = 0;

// 			/**
// 			 * A function to be called when the upload has transmitted more data.
// 			 *
// 			 * This method updates the upload percentage counter in the dialog.
// 			 *
// 			 * If the progress equals 100%, i.e. if the upload has been
// 			 * completed, this method
// 			 *
// 			 *  - unsets the semaphore for a running upload,
// 			 *  - scrolls the dialog such that the file with index `fileIdx`
// 			 *    becomes visible, and
// 			 *  - changes the status text to "Upload processing".
// 			 *
// 			 * After the current upload has reached 100%, this method starts a
// 			 * new upload, if
// 			 *
// 			 *  - there are more files to be uploaded,
// 			 *  - no other upload is currently running, and
// 			 *  - the number of outstanding responses does not exceed the
// 			 *    processing limit of Lychee.
// 			 *
// 			 * @param {ProgressEvent} e
// 			 * @this XMLHttpRequest
// 			 */
// 			const onUploadProgress = function (e) {
// 				if (e.lengthComputable !== true) return;

// 				// Calculate progress
// 				const progress = ((e.loaded / e.total) * 100) | 0;

// 				// Set progress when progress has changed
// 				if (progress > uploadProgress) {
// 					uploadProgress = progress;
// 					const row = upload._dom.progressRowsByPath.get(files[fileIdx].name);
// 					row.listEntry.scrollIntoView(upload.SCROLL_OPTIONS);
// 					row.status.textContent = "" + uploadProgress + "%";

// 					if (progress >= 100) {
// 						row.status.textContent = lychee.locale["UPLOAD_PROCESSING"];
// 						isUploadRunning = false;

// 						// Start a new upload, if there are still pending
// 						// files
// 						if (
// 							!isUploadRunning &&
// 							!shallCancelUpload &&
// 							(outstandingResponsesCount < lychee.upload_processing_limit || lychee.upload_processing_limit === 0) &&
// 							latestFileIdx + 1 < files.length
// 						) {
// 							latestFileIdx++;
// 							process(latestFileIdx);
// 						}
// 					}
// 				}
// 			};

// 			/**
// 			 * A function to be called when a response has been received.
// 			 *
// 			 * This method updates the status of the affected file.
// 			 *
// 			 * @this XMLHttpRequest
// 			 */
// 			const onLoaded = function () {
// 				const row = upload._dom.progressRowsByPath.get(files[fileIdx].name);
// 				/** @type {?LycheeException} */
// 				const lycheeException = this.status >= 400 ? this.response : null;

// 				switch (this.status) {
// 					case 200:
// 					case 201:
// 					case 204:
// 						row.status.textContent = lychee.locale["UPLOAD_FINISHED"];
// 						row.status.classList.add("success");
// 						break;
// 					case 409:
// 						row.status.textContent = lychee.locale["UPLOAD_SKIPPED"];
// 						row.status.classList.add("warning");
// 						row.notice.textContent = lycheeException ? lycheeException.message : lychee.locale["UPLOAD_ERROR_UNKNOWN"];
// 						hasWarningOccurred = true;
// 						break;
// 					case 413:
// 						row.status.textContent = lychee.locale["UPLOAD_FAILED"];
// 						row.status.classList.add("error");
// 						row.notice.textContent = lychee.locale["UPLOAD_ERROR_POSTSIZE"];
// 						hasErrorOccurred = true;
// 						api.onError(this, { albumID: albumID }, lycheeException);
// 						break;
// 					default:
// 						row.status.textContent = lychee.locale["UPLOAD_FAILED"];
// 						row.status.classList.add("error");
// 						row.notice.textContent = lycheeException ? lycheeException.message : lychee.locale["UPLOAD_ERROR_UNKNOWN"];
// 						hasErrorOccurred = true;
// 						api.onError(this, { albumID: albumID }, lycheeException);
// 						break;
// 				}
// 			};

// 			/**
// 			 * A function to be called when any response has been received
// 			 * (after specific success and error callbacks have been executed)
// 			 *
// 			 * This method starts a new upload, if
// 			 *
// 			 *  - there are more files to be uploaded,
// 			 *  - no other upload is currently running, and
// 			 *  - the number of outstanding responses does not exceed the
// 			 *    processing limit of Lychee.
// 			 *
// 			 * This method calls {@link finish}, if
// 			 *
// 			 *  - the process shall be cancelled or no more files are left for processing,
// 			 *  - no upload is running anymore, and
// 			 *  - no response is outstanding
// 			 *
// 			 * @this XMLHttpRequest
// 			 */
// 			const onComplete = function () {
// 				outstandingResponsesCount--;

// 				if (
// 					!isUploadRunning &&
// 					!shallCancelUpload &&
// 					(outstandingResponsesCount < lychee.upload_processing_limit || lychee.upload_processing_limit === 0) &&
// 					latestFileIdx + 1 < files.length
// 				) {
// 					latestFileIdx++;
// 					process(latestFileIdx);
// 				}

// 				if ((shallCancelUpload || latestFileIdx + 1 === files.length) && !isUploadRunning && outstandingResponsesCount === 0) {
// 					finish();
// 				}
// 			};

// 			const formData = new FormData();
// 			const xhr = new XMLHttpRequest();

// 			// For form data, a `null` value is indicated by the empty
// 			// string `""`. Form data falsely converts the value `null` to the
// 			// literal string `"null"`.
// 			formData.append("albumID", albumID ? albumID : "");
// 			formData.append("fileLastModifiedTime", files[fileIdx].lastModified);
// 			formData.append("file", files[fileIdx]);

// 			// We must not use the `onload` event of the `XMLHttpRequestUpload`
// 			// object.
// 			// Instead, we only use the `onprogress` event and check within
// 			// the event handler if the progress counter reached 100%.
// 			// The reason is that `upload.onload` is not immediately called
// 			// after the browser has completed the upload (as the name
// 			// suggests), but only after the browser has already received the
// 			// response header.
// 			// For our purposes this is too late, as this way we would never
// 			// show the "processing" status, during which the backend has
// 			// received the upload, but has not yet started to send a response.
// 			xhr.upload.onprogress = onUploadProgress;
// 			xhr.onload = onLoaded;
// 			xhr.onloadend = onComplete;
// 			xhr.responseType = "json";
// 			xhr.open("POST", "api/Photo::add");
// 			xhr.setRequestHeader("X-XSRF-TOKEN", csrf.getCSRFCookieValue());
// 			xhr.setRequestHeader("Accept", "application/json");

// 			outstandingResponsesCount++;
// 			isUploadRunning = true;
// 			xhr.send(formData);
// 		};

// 		window.onbeforeunload = function () {
// 			return lychee.locale["UPLOAD_IN_PROGRESS"];
// 		};

// 		upload.showProgressReportDialog(
// 			lychee.locale["UPLOAD_UPLOADING"],
// 			files,
// 			function () {
// 				// Upload first file
// 				basicModal.showCancelButton();
// 				process(0);
// 			},
// 			function () {
// 				shallCancelUpload = true;
// 				hasErrorOccurred = true;
// 			},
// 		);
// 	},
// };

// /**
//  * @param {(FileList|File[])} files
//  *
//  * @returns {void}
//  */
// upload.uploadTrack = function (files) {
// 	const albumID = album.getID();
// 	if (files.length <= 0 || albumID === null) return;

// 	const runUpload = function () {
// 		// Only a single track can be uploaded at once, hence the only
// 		// file is at position 0.
// 		const row = upload._dom.progressRowsByPath.get(files[0].name);

// 		/**
// 		 * A function to be called when a response has been received.
// 		 *
// 		 * It closes the modal dialog or shows the close button and
// 		 * reloads the album.
// 		 *
// 		 * @this XMLHttpRequest
// 		 */
// 		const finish = function () {
// 			/** @type {?LycheeException} */
// 			const lycheeException = this.status >= 400 ? this.response : null;
// 			let errorText = "";
// 			let statusText;
// 			let statusClass;

// 			$("#upload_track_file").val("");

// 			switch (this.status) {
// 				case 200:
// 				case 201:
// 				case 204:
// 					statusText = lychee.locale["UPLOAD_FINISHED"];
// 					statusClass = "success";
// 					break;
// 				case 413:
// 					statusText = lychee.locale["UPLOAD_FAILED"];
// 					errorText = lychee.locale["UPLOAD_ERROR_POSTSIZE"];
// 					statusClass = "error";
// 					break;
// 				default:
// 					statusText = lychee.locale["UPLOAD_FAILED"];
// 					errorText = lycheeException ? lycheeException.message : lychee.locale["UPLOAD_ERROR_UNKNOWN"];
// 					statusClass = "error";
// 					break;
// 			}

// 			row.status.textContent = statusText;

// 			if (errorText !== "") {
// 				row.notice.textContent = errorText;

// 				api.onError(this, { albumID: albumID }, lycheeException);
// 				upload.showProgressReportCloseButton();
// 				upload.notify(lychee.locale["UPLOAD_COMPLETE"], lychee.locale["UPLOAD_COMPLETE_FAILED"]);
// 			} else {
// 				upload.closeProgressReportDialog();
// 				upload.notify(lychee.locale["UPLOAD_COMPLETE"]);
// 			}

// 			album.reload();
// 		}; // finish

// 		row.status.textContent = lychee.locale["UPLOAD_UPLOADING"];

// 		const formData = new FormData();
// 		const xhr = new XMLHttpRequest();

// 		formData.append("albumID", albumID);
// 		formData.append("file", files[0]);

// 		xhr.onload = finish;
// 		xhr.responseType = "json";
// 		xhr.open("POST", "api/Album::setTrack");
// 		xhr.setRequestHeader("X-XSRF-TOKEN", csrf.getCSRFCookieValue());
// 		xhr.setRequestHeader("Accept", "application/json");

// 		xhr.send(formData);
// 	}; // runUpload

// 	upload.showProgressReportDialog(lychee.locale["UPLOAD_UPLOADING"], files, runUpload);
// };
