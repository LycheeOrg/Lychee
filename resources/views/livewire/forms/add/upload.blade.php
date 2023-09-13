<div>
    <div class="p-9" x-data="upload({{ $chunkSize }}, {{ $parallelism }})" x-on:drop="isDropping = false"
        x-on:drop.prevent="($event) => {
        if ($event.dataTransfer.files.length > 0) {
            fileList = $event.dataTransfer.files;
            fileList.forEach((file, index) => {
                @this.set('uploads.' + index + '.fileName', file.name);
                @this.set('uploads.' + index + '.fileSize', file.size);
                @this.set('uploads.' + index + '.lastModified', file.lastModified);
                @this.set('uploads.' + index + '.stage', 'uploading');
                @this.set('uploads.' + index + '.progress', 0);
                chnkStarts[index] = 0;
            });
            
            start(@this);
        }
    }"
        x-on:dragover.prevent="isDropping = true" x-on:dragleave.prevent="isDropping = false">
        @if (count($uploads) === 0)
            <div class="absolute top-0 bottom-0 left-0 right-0 z-30 flex items-center justify-center bg-sky-500 opacity-90"
                x-show="isDropping">
                <span class="text-3xl text-white">Release file to upload!</span>
            </div>
            <label
                class="flex flex-col items-center justify-center hover:bg-dark-400 border border-dark-500 shadow cursor-pointer h-1/2 rounded-2xl p-6"
                for="myFiles">
                <h3 class="text-xl text-center">Click here to select files to upload</h3>
                <em class="italic text-slate-400">(Or drag files to the page)</em>
            </label>
            <input type="file" id="myFiles" multiple
                x-on:change="
                fileList = [...$el.files];
                fileList.forEach((file, index) => {
                    @this.set('uploads.' + index + '.fileName', file.name);
                    @this.set('uploads.' + index + '.fileSize', file.size);
                    @this.set('uploads.' + index + '.lastModified', file.lastModified);
                    @this.set('uploads.' + index + '.stage', 'uploading');
                    @this.set('uploads.' + index + '.progress', 0);
                    chnkStarts[index] = 0;
                });
                
                start(@this);
            "
                class="hidden">
        @else
            <h1 class="text-center text-white text-lg font-bold">{{ __('lychee.UPLOAD_UPLOADING') }}</h1>
            <div class="overflow-y-auto rounded max-h-[20rem]">
                @foreach ($uploads as $i => $upl)
                    <div class="pt-2 pr-4 pl-4 pb-2 bg-dark-800">
                        <label class="flow-root">
                            <div class="float-left">{{ $upl['fileName'] }}</div>
                            @switch($upl['stage'])
                                @case('uploading')
                                    <div class="float-right text-sky-400">{{ $upl['progress'] }}%</div>
                                @break

                                @case('processing')
                                    <div class="float-right text-green-800">{{ __('lychee.UPLOAD_PROCESSING') }}</div>
                                @break

                                @case('done')
                                    <div class="float-right text-green-600">{{ __('lychee.UPLOAD_FINISHED') }}</div>
                                @break

                                @case('skipped')
                                    <div class="float-right text-orange-400">{{ __('lychee.UPLOAD_SKIPPED') }}</div>
                                @break

                                @case('error')
                                    <div class="float-right text-red-600 font-bold">{{ __('lychee.UPLOAD_FAILED') }}</div>
                                @break

                                @default
                                    @dd($upl['stage'])
                                    <div class="float-right">{{ $upl['stage'] }}</div>
                            @endswitch
                        </label>
                        @if ($upl['stage'] === 'error')
                        <div class="h-1 w-full bg-red-700"></div>
                        <div class="text-neutral-400 text-left">
                            {{ __('lychee.UPLOAD_FAILED_ERROR') }}
                        </div>
                        @elseif($upl['stage'] === 'done')
                        <div class="h-1 w-full bg-green-600"></div>
                        @else
                        <div class="h-1 w-full bg-neutral-800">
                            <div class="h-1 bg-sky-400" style="width: {{ $upl['progress'] }}%"></div>
                        </div>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
