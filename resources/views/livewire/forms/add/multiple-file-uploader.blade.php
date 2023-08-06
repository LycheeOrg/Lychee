<div x-data="{
    isDropping: false,
    isUploading: false,
    progress: 0,
    handleFileSelect(event) {
        if (event.target.files.length) {
            this.uploadFiles(event.target.files)
        }
    },
    handleFileDrop(event) {
        if (event.dataTransfer.files.length > 0) {
            this.uploadFiles(event.dataTransfer.files)
        }
    },
    uploadFiles(files) {
        const $this = this
        this.isUploading = true
        @this.uploadMultiple('files', files,
            function(success) { //upload was a success and was finished
                $this.isUploading = false
                $this.progress = 0
            },
            function(error) { //an error occured
                console.log('error', error)
            },
            function(event) { //upload progress was made
                $this.progress = event.detail.progress
            }
        )
    }
}" class="relative p-9 flex flex-col items-center justify-center"
    x-on:drop="isDropping = false" x-on:drop.prevent="handleFileDrop($event)" x-on:dragover.prevent="isDropping = true"
    x-on:dragleave.prevent="isDropping = false">
    <div class="absolute top-0 bottom-0 left-0 right-0 z-30 flex items-center justify-center bg-sky-500 opacity-90"
        x-show="isDropping">
        <span class="text-3xl text-white">Release file to upload!</span>
    </div>
    <label
        class="flex flex-col items-center justify-center hover:bg-dark-400 border border-dark-500 shadow cursor-pointer h-1/2 rounded-2xl p-6"
        for="file-upload" x-show="!isUploading">
        <h3 class="text-xl text-center">Click here to select files to upload</h3>
        <em class="italic text-slate-400">(Or drag files to the page)</em>
    </label>
    <div class="bg-dark-500 h-1 w-1/2 mt-3">
        <div class="bg-sky-500 h-1" style="transition: width 1s" :style="`width: ${progress}%;`" x-show="isUploading">
        </div>
    </div>

    @if (count($files) > 0)
        <ul class="mt-5 list-disc">
            @for ($i = 0; $i < count($files); $i ++)
            <li><img alt="thumb" src="{{ URL::asset($uploadedThumbs[$i]) }}" class="h-12" />{{ $files[$i]->getClientOriginalName() }}</li>
            @endfor
        </ul>
    @endif
    <input type="file" id="file-upload" multiple x-on:change="handleFileSelect" class="hidden" />

</div>
