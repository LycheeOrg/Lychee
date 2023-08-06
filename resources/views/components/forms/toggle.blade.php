<label class="switch relative inline-block w-[42px] h-[22px] -bottom-0.5 leading-6">
    <input class="opacity-0 w-0 h-0" {{ $attributes }} type="checkbox">
    {{-- slider ensure that we have a toggle --}}
    <span class="slider absolute block cursor-pointer top-0 left-0 right-0 bottom-0 border border-solid border-black/20 bg-black/30 rounded-3xl
    before:absolute before:content-[''] before:bg-sky-500 before:w-[14px] before:h-[14px] before:left-[3px] before:bottom-[3px] before:rounded-3xl"></span>
</label>
