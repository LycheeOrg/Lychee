@props(['options', 'class' => 'mx-1'])
<span class="relative w-max my-[1px] text-white rounded overflow-hidden bg-black/30 inline-block text-2xs align-middle
after:content-['≡'] after:absolute after:text-sky-400 after:right-2 after:top-0 after:font-bold after:text-lg after:-mt-1
after:pointer-events-none {{ $class }}">
    <select class="m-0 py-1 w-[120%] text-white bg-transparent text-2xs px-2" {{ $attributes }}>
    @foreach($options as $key => $option)
        <option class="text-neutral-800" @if (is_string($key)) value="{{ $key }}" @endif>
            {{ $option }}
        </option>
    @endforeach
    </select>
</span>
