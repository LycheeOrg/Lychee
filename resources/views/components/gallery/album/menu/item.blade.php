@props(['class' => 'w-full', 'tab'])
<li class="px-2 pt-3 pb-4 cursor-pointer font-bold text-center transition-colors ease-in-out select-none
    xl:border-l-2 max-xl:border-b-2 border-solid
    border-sky-500 text-sky-500 
    hover:border-sky-200 hover:text-sky-200
    {{ $class }}"
    x-bind:class="! detailsActiveTab === {{ $tab }} ? 'bg-sky-200/10' : ''"
    {{ $attributes }} x-on:click="detailsActiveTab = {{ $tab }}">
    {{ $slot }}
</li>
