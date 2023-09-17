export default { upload };

export function upload(chunkSize_val, parallelism_val = 3) {
	return {
		isDropping: false,

		chunkSize: chunkSize_val,
		hasErrorOccurred: false,
		upload_processing_limit: parallelism_val,
		chnkStarts: [],
		fileList: [],

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
		 *
		 * @type {number}
		 */
		outstandingResponsesCount: 0,
		/**
		 * The latest (aka highest) index of a file which is being or has
		 * been uploaded to the server.
		 *
		 * @type {number}
		 */
		latestFileIdx: 0,
		/**
		 * Semaphore whether a file is currently being uploaded.
		 *
		 * This is used as a semaphore to serialize the upload transmissions
		 * between several instances of the method {@link process}.
		 *
		 * @type {boolean}
		 */
		isUploadRunning: false,

		/**
		 * This callback is invoked when the last file has been processed.
		 *
		 * It closes the modal dialog or shows the close button and
		 * reloads the album.
		 */
		finish(wire, alpine = this) {
			if (!alpine.hasErrorOccurred) {
				console.log("Success!");
				wire.dispatch("reloadPage");
				wire.close();
			} else {
				console.log("Well something went wrong...");
				wire.dispatch("reloadPage");
				// Error
			}
		},

		process(fileIdx, wire, alpine = this) {
			alpine.outstandingResponsesCount++;
			this.livewireUploadChunk(fileIdx, wire, alpine);
		},

		complete(wire, alpine = this) {
			alpine.outstandingResponsesCount--;

			// Start the next one if possible.
			if (alpine.outstandingResponsesCount < alpine.upload_processing_limit && alpine.latestFileIdx + 1 < alpine.fileList.length) {
				console.log("next file!");
				alpine.latestFileIdx++;
				alpine.process(alpine.latestFileIdx, wire, alpine);
			} else if (alpine.outstandingResponsesCount === 0 && alpine.latestFileIdx + 1 === alpine.fileList.length) {
				console.log("Finish");
				alpine.finish(wire, alpine);
			} else {
				console.log("Curent threads: " + alpine.outstandingResponsesCount);
				console.log("Current index: " + alpine.latestFileIdx);
				console.log("Number of files: " + alpine.fileList.length);
				console.log("waiting...");
			}
		},

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
		livewireUploadChunk(fileIdx, wire, alpine = this) {
			// End of chunk is start + chunkSize OR file size, whichever is greater
			const chunkEnd = Math.min(alpine.chnkStarts[fileIdx] + alpine.chunkSize, alpine.fileList[fileIdx].size);
			const chunk = alpine.fileList[fileIdx].slice(alpine.chnkStarts[fileIdx], chunkEnd);

			wire.upload(
				"uploads." + fileIdx + ".fileChunk",
				chunk,
				(success) => {
					alpine.complete(wire, alpine);
				},
				() => {
					alpine.hasErrorOccurred = true;
					alpine.complete(wire, alpine);
					wire.set("uploads." + fileIdx + ".stage", "error");
				},
				(event) => {
					console.log(event);
					if (event.detail.progress == 100) {
						alpine.chnkStarts[fileIdx] = Math.min(alpine.chnkStarts[fileIdx] + alpine.chunkSize, alpine.fileList[fileIdx].size);

						if (alpine.chnkStarts[fileIdx] < alpine.fileList[fileIdx].size) {
							let _time = Math.floor(Math.random() * 2000 + 1);
							console.log("sleeping ", _time, "before next chunk upload");
							setTimeout(alpine.livewireUploadChunk, _time, fileIdx, wire, alpine);
						}
					}
				}
			);
		},

		start(wire, alpine = this) {
			for (let index = 0; index < Math.min(alpine.upload_processing_limit, alpine.fileList.length); index++) {
				alpine.process(index, wire, alpine);
				alpine.latestFileIdx = index;
			}
		},
	};
}
