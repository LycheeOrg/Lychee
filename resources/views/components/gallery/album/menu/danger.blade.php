@props(['class' => 'w-full','tab'])
<li class="px-2 pt-3 pb-4 cursor-pointer font-bold text-center transition-colors ease-in-out select-none
    xl:border-l-2 max-xl:border-b-2 border-solid border-red-800
    hover:bg-red-800 hover:text-text-main-0 {{ $class }}"
    x-bind:class="! albumFlags.activeTab === {{ $tab }} ? 'text-text-main-0 bg-red-800' : 'text-red-800'"
    {{ $attributes }} x-on:click="albumFlags.activeTab = {{ $tab }}">
    {{ $slot }}
</li>
