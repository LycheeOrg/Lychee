@props(['title', 'class' => '', 'disabled' => false])
<span class="inline-block {{ $class }}" title="{{ $title }}">
    <label>
        <input {{ $attributes }} type="checkbox" class="absolute m-0 opacity-0" @disabled($disabled)>
        <span class="checkbox inline-block w-4 h-4 mt-1.5 mx-2 bg-black/50 rounded-sm">
            <x-icons.iconic class=" fill-primary-500 opacity-0 p-0.5 w-full h-full scale-0 mb-2" icon="check" />
        </span>
    </label>
</span>
