import { Alpine } from "alpinejs";
import { UploadView, Livewire, UploadEvent } from "./types";

export const uploadView = (Alpine: Alpine) =>
	Alpine.data(
		"upload",
		// @ts-expect-error
		(chunkSize_val: number, parallelism_val: number = 3): UploadView => ({
			isDropping: false,
			chunkSize: chunkSize_val,
			hasErrorOccurred: false,
			upload_processing_limit: parallelism_val,
			chnkStarts: [],
			fileList: [],
			outstandingResponsesCount: 0,
			latestFileIdx: 0,
			isUploadRunning: false,

			finish(wire: Livewire, alpine: UploadView): void {
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

			process(fileIdx: number, wire: Livewire, alpine: UploadView): void {
				alpine.outstandingResponsesCount++;
				this.livewireUploadChunk(fileIdx, wire, alpine);
			},

			complete(wire: Livewire, alpine: UploadView): void {
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
			 */
			livewireUploadChunk(fileIdx: number, wire: Livewire, alpine: UploadView): void {
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
					(event: UploadEvent) => {
						console.log(event);
						if (event.detail.progress == 100) {
							alpine.chnkStarts[fileIdx] = Math.min(alpine.chnkStarts[fileIdx] + alpine.chunkSize, alpine.fileList[fileIdx].size);

							if (alpine.chnkStarts[fileIdx] < alpine.fileList[fileIdx].size) {
								let _time = Math.floor(Math.random() * 2000 + 1);
								console.log("sleeping ", _time, "before next chunk upload");
								setTimeout(alpine.livewireUploadChunk, _time, fileIdx, wire, alpine);
							}
						}
					},
				);
			},

			// @ts-expect-error
			start(wire: Livewire, alpine: UploadView = this): void {
				for (let index = 0; index < Math.min(alpine.upload_processing_limit, alpine.fileList.length); index++) {
					alpine.process(index, wire, alpine);
					alpine.latestFileIdx = index;
				}
			},
		}),
	);
