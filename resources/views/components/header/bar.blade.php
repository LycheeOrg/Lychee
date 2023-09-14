@props(['class' => 'h-14'])
<header id="lychee_toolbar_container"
    class="px-2 w-full flex-none bg-gradient-to-b from-dark-700 to-dark-800 border-b border-b-solid border-b-neutral-900 {{ $class }}" {{ $attributes }}>
    <div class="flex w-full items-center box-border">
        {{ $slot }}
    </div>
</header>
