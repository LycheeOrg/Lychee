<div class="basicModalContainer transition-opacity duration-1000 ease-in animate-fadeIn
    bg-black/80 z-50 fixed flex items-center justify-center w-full h-full top-0 left-0 box-border opacity-100"
    data-closable="true"
    x-data="{ shortCutsOpen: false }"
    @keydown.window="if (event.keyCode === 191 && event.shiftKey) { shortCutsOpen = true }"
    @keydown.escape.window="shortCutsOpen = false"
    x-cloak
    x-show="shortCutsOpen">
    <div class="basicModal transition-opacity ease-in duration-1000
        opacity-100 bg-gradient-to-b from-dark-300 to-dark-400
        relative w-[500px] text-sm rounded-md text-neutral-400 animate-moveUp overflow-hidden"
        role="dialog" x-on:click.away="shortCutsOpen = !shortCutsOpen">
        <h1 class="text-center text-white text-xl font-bold w-full border-b border-b-black/20 p-3">Keyboard shortcuts</h1>
        <div class="flex flex-wrap gap-0.5 justify-center align-top text-white/80 max-h-[80vh] overflow-y-auto">
            <x-help.table>
                <x-help.head>Site-wide Shortcuts</x-help.head>
                <x-help.cell>Back/Cancel</x-help.cell>
                <x-help.cell class="text-right"><x-help.kbd>Esc</x-help.kbd></x-help.cell>
                <x-help.cell>Confirm</x-help.cell>
                <x-help.cell class="text-right"><x-help.kbd>Enter</x-help.kbd></x-help.cell>
                <x-help.cell>Show this modal</x-help.cell>
                <x-help.cell class="text-right"><x-help.kbd>Shift</x-help.kbd> <x-help.kbd>?</x-help.kbd></x-help.cell>
                <x-help.cell>Login</x-help.cell>
                <x-help.cell class="text-right"><x-help.kbd>l</x-help.kbd></x-help.cell>
                <x-help.cell>Login with U2F</x-help.cell>
                <x-help.cell class="text-right"><x-help.kbd>k</x-help.kbd></x-help.cell>
            </x-help.table>

            <x-help.table>
                <x-help.head>Albums Shortcuts</x-help.head>
                <x-help.cell>New album</x-help.cell>
                <x-help.cell class="text-right"><x-help.kbd>n</x-help.kbd></x-help.cell>
                <x-help.cell>Upload photos</x-help.cell>
                <x-help.cell class="text-right"><x-help.kbd>u</x-help.kbd></x-help.cell>
                <x-help.cell>Search</x-help.cell>
                <x-help.cell class="text-right"><x-help.kbd>/</x-help.kbd></x-help.cell>
                <x-help.cell>Toggle Sensitive albums</x-help.cell>
                <x-help.cell class="text-right"><x-help.kbd>h</x-help.kbd></x-help.cell>
                <x-help.cell>Select all albums</x-help.cell>
                <x-help.cell class="text-right"><x-help.kbd>ctrl</x-help.kbd> <x-help.kbd>a</x-help.kbd></x-help.cell>
            </x-help.table>

            <x-help.table>
                <x-help.head>Album Shortcuts</x-help.head>
                <x-help.cell>New album</x-help.cell>
                <x-help.cell class="text-right"><x-help.kbd>n</x-help.kbd></x-help.cell>
                <x-help.cell>Upload photos</x-help.cell>
                <x-help.cell class="text-right"><x-help.kbd>u</x-help.kbd></x-help.cell>
                <x-help.cell>Rename album</x-help.cell>
                <x-help.cell class="text-right"><x-help.kbd>r</x-help.kbd></x-help.cell>
                <x-help.cell>Set description</x-help.cell>
                <x-help.cell class="text-right"><x-help.kbd>d</x-help.kbd></x-help.cell>
                <x-help.cell>Toggle full screen</x-help.cell>
                <x-help.cell class="text-right"><x-help.kbd>f</x-help.kbd></x-help.cell>
                <x-help.cell>Toggle panel</x-help.cell>
                <x-help.cell class="text-right"><x-help.kbd>i</x-help.kbd></x-help.cell>
                <x-help.cell>Search</x-help.cell>
                <x-help.cell class="text-right"><x-help.kbd>/</x-help.kbd></x-help.cell>
                <x-help.cell>Toggle Sensitive albums</x-help.cell>
                <x-help.cell class="text-right"><x-help.kbd>H</x-help.kbd></x-help.cell>
                <x-help.cell>Delete album</x-help.cell>
                <x-help.cell class="text-right"><x-help.kbd>ctrl</x-help.kbd> <x-help.kbd>dell</x-help.kbd></x-help.cell>
                <x-help.cell>Select all albums or photos</x-help.cell>
                <x-help.cell class="text-right"><x-help.kbd>ctrl</x-help.kbd> <x-help.kbd>a</x-help.kbd></x-help.cell>
            </x-help.table>

            <x-help.table>
                <x-help.head>Photo Shortcuts</x-help.head>
                <x-help.cell>Previous photo</x-help.cell>
                <x-help.cell class="text-right"><x-help.kbd>&leftarrow;</x-help.kbd></x-help.cell>
                <x-help.cell>Next photo</x-help.cell>
                <x-help.cell class="text-right"><x-help.kbd>&rightarrow;</x-help.kbd></x-help.cell>
                {{-- <!-- Still unsure about those -->
                <x-help.cell class="text-right"><x-help.kbd>r</x-help.kbd></x-help.cell>
                <x-help.cell>Rename album</x-help.cell>
                <x-help.cell class="text-right"><x-help.kbd>d</x-help.kbd></x-help.cell>
                <x-help.cell>Set description</x-help.cell>
                <x-help.cell class="text-right"><x-help.kbd>t</x-help.kbd></x-help.cell>
                <x-help.cell>Set tags</x-help.cell>
                --}}
                {{-- <!-- For later -->
                <x-help.cell class="text-right"><x-help.kbd>1</x-help.kbd></x-help.cell>
                <x-help.cell>Rate 1 star</x-help.cell>
                <x-help.cell class="text-right"><x-help.kbd>2</x-help.kbd></x-help.cell>
                <x-help.cell>Rate 2 star</x-help.cell>
                <x-help.cell class="text-right"><x-help.kbd>3</x-help.kbd></x-help.cell>
                <x-help.cell>Rate 3 star</x-help.cell>
                <x-help.cell class="text-right"><x-help.kbd>4</x-help.kbd></x-help.cell>
                <x-help.cell>Rate 4 star</x-help.cell>
                <x-help.cell class="text-right"><x-help.kbd>5</x-help.kbd></x-help.cell>
                <x-help.cell>Rate 5 star</x-help.cell>
                --}}
                <x-help.cell>Star photo</x-help.cell>
                <x-help.cell class="text-right"><x-help.kbd>s</x-help.kbd></x-help.cell>
                <x-help.cell>Cycle overlay mode</x-help.cell>
                <x-help.cell class="text-right"><x-help.kbd>o</x-help.kbd></x-help.cell>
                <x-help.cell>Show information</x-help.cell>
                <x-help.cell class="text-right"><x-help.kbd>i</x-help.kbd></x-help.cell>
                <x-help.cell>Toggle full screen</x-help.cell>
                <x-help.cell class="text-right"><x-help.kbd>f</x-help.kbd></x-help.cell>
                <x-help.cell>Rotate counter clock wise</x-help.cell>
                <x-help.cell class="text-right"><x-help.kbd>ctrl</x-help.kbd> <x-help.kbd>&leftarrow;</x-help.kbd></x-help.cell>
                <x-help.cell>Rotate clockwise</x-help.cell>
                <x-help.cell class="text-right"><x-help.kbd>ctrl</x-help.kbd> <x-help.kbd>&rightarrow;</x-help.kbd></x-help.cell>
                <x-help.cell>Delete the photo</x-help.cell>
                <x-help.cell class="text-right"><x-help.kbd>delete</x-help.kbd> or <x-help.kbd>BckSpace</x-help.kbd></x-help.cell>
            </x-help.table>
        </div>
        <div class="basicModal__buttons">
            <x-forms.buttons.cancel
                x-on:click="shortCutsOpen = false"
                class="border-t border-t-black/20 w-full hover:bg-white/[.02]">
                {{ __('lychee.CLOSE') }}</x-forms.buttons.cancel>
        </div>
    </div>
</div>
