<div>
    <div class="p-9"
    x-init="const filesSelector = document.querySelector('#myFiles');
    let chnkStarts = [];
    
    filesSelector.addEventListener('change', () => {
        const fileList = [...filesSelector.files];
    
        fileList.forEach((file, index) => {
            @this.set('uploads.' + index + '.fileName', file.name);
            @this.set('uploads.' + index + '.fileSize', file.size);
            @this.set('uploads.' + index + '.lastModified', file.lastModified);
            @this.set('uploads.' + index + '.stage', 'uploading');
            @this.set('uploads.' + index + '.progress', 0);
            chnkStarts[index] = 0;
            livewireUploadChunk(index, file);
        });
    });
    
    function livewireUploadChunk(index, file) {
        // End of chunk is start + chunkSize OR file size, whichever is greater
        const chunkEnd = Math.min(chnkStarts[index] + @js($chunkSize), file.size);
        const chunk = file.slice(chnkStarts[index], chunkEnd);
    
        @this.upload('uploads.' + index + '.fileChunk', chunk, (n) => {}, () => {}, (e) => {
            if (e.detail.progress == 100) {
                chnkStarts[index] =
                    Math.min(chnkStarts[index] + @js($chunkSize), file.size);
    
                if (chnkStarts[index] < file.size) {
                    let _time = Math.floor((Math.random() * 2000) + 1);
                    console.log('sleeping ', _time, 'before next chunk upload');
                    setTimeout(livewireUploadChunk, _time, index, file);
                }
            }
        });
    }">
        @if (count($uploads) === 0)
            <input type="file" id="myFiles" multiple>
        @else
            <h1 class="text-center text-white text-lg font-bold">{{ __('lychee.UPLOAD_UPLOADING') }}</h1>
            <div class="overflow-y-auto rounded max-h-[20rem]">
                @foreach ($uploads as $i => $upl)
                    <div class="pt-2 pr-4 pl-4 pb-2 bg-dark-800">
                        <label class="flow-root">
                            <div class="float-left">{{ $upl['fileName'] }}</div>
                            @switch($upl['stage'])
                                @case('uploading')
                                <div class="float-right">{{ $upl['progress'] }}%</div>
                                @break
                                @case('processing')
                                <div class="float-right">{{ __('lychee.UPLOAD_PROCESSING') }}</div>
                                @break
                                @case('processing')
                                <div class="float-right">{{ __('lychee.UPLOAD_PROCESSING') }}</div>
                                @break
                                @case('skipped')
                                <div class="float-right">{{ __('lychee.UPLOAD_SKIPPED') }}</div>
                                @break
                                @default
                                <div class="float-right">{{ $upl['stage'] }}</div>
                            @endswitch
                        </label>
                        <div class="h-1 w-full bg-neutral-800">
                            <div class="h-1 bg-sky-400" style="width: {{ $upl['progress'] }}%"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>