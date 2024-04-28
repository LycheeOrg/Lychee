@props(['class' => 'w-full', 'tab'])
<li class="px-2 pt-3 pb-4 cursor-pointer font-bold text-center transition-colors ease-in-out select-none
    xl:border-l-2 max-xl:border-b-2 border-solid
    border-primary-500 text-primary-500 
    hover:border-primary-200 hover:text-primary-200
    {{ $class }}"
    x-bind:class="! albumFlags.activeTab === {{ $tab }} ? 'bg-primary-200/10' : ''"
    {{ $attributes }} x-on:click="albumFlags.activeTab = {{ $tab }}">
    {{ $slot }}
</li>
