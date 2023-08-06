<div>
    <input type="file" id="myFiles" multiple>
    @foreach( $uploads as $i=>$upl )
        <div class="mt-2 bg-blue-50 rounded-full pt-2 pr-4 pl-4 pb-2">
            <label class="flow-root">
                <div class="float-left">{{ $upl['fileName'] }}</div>
                <div class="float-right">{{ $upl['progress'] }}%</div>
            </label>
            <progress max="100" wire:model="uploads.{{$i}}.progress" class="h-1 w-full bg-neutral-200 dark:bg-neutral-60"/>
        </div>
    @endforeach
    <script>
        const filesSelector = document.querySelector('#myFiles');
        let chnkStarts=[];
        
        filesSelector.addEventListener('change', () => {
            const fileList = [...filesSelector.files];
            
            fileList.forEach((file, index) => {
                @this.set('uploads.'+index+'.fileName', file.name );
                @this.set('uploads.'+index+'.fileSize', file.size );
                @this.set('uploads.'+index+'.progress', 0 );
                chnkStarts[index] = 0;
                livewireUploadChunk( index, file );
            });
        }); 

        function livewireUploadChunk( index, file ){
            // End of chunk is start + chunkSize OR file size, whichever is greater
            const chunkEnd = Math.min(chnkStarts[index]+@js($chunkSize), file.size);
            const chunk    = file.slice(chnkStarts[index], chunkEnd);

            @this.upload('uploads.'+index+'.fileChunk',chunk,(n)=>{},()=>{},(e)=>{
                if( e.detail.progress == 100 ){
                    chnkStarts[index] = 
                    Math.min( chnkStarts[index] + @js($chunkSize), file.size );

                    if( chnkStarts[index] < file.size ){
                        let _time = Math.floor((Math.random() * 2000) + 1);
                        console.log('sleeping ',_time,'before next chunk upload');
                        setTimeout( livewireUploadChunk, _time, index, file );
                    }
                }
            });
        }

    </script>
</div>