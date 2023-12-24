<div class="w-full">
    <x-header.bar>
        <x-header.back @keydown.escape.window="$wire.back();" wire:click="back" />
        <x-header.title>{{ __('lychee.SHARING') }}</x-header.title>
    </x-header.bar>
    <div class="overflow-x-clip overflow-y-auto h-[calc(100vh-56px)]">
        <div class="settings_view max-w-3xl text-text-main-400 text-sm mx-auto">
            <div class="w-full mt-5">
                <p>
                    This page gives an overview and edit the sharing rights associated with albums.
                </p>
                <div class="w-full flex relative translate-y-2">
                    <div class="absolute top-0 right-2 peer">
                        <x-icons.iconic class=" text-neutral-200 px-0.5 w-5 h-5 mb-1" icon="question-mark" />
                    </div>
                    <ul
                        class="mt-1 p-4 peer-hover:block hidden top-4 right-4 absolute border-neutral-600 border border-solid bg-bg-700 z-20 drop-shadow">
                        <li class=""><x-icons.iconic class=" text-neutral-200 px-0.5 w-4 h-4 mb-1"
                                icon="fullscreen-enter" />
                            : grants full photo access</li>
                        <li class=""><x-icons.iconic class=" text-neutral-200 px-0.5 w-4 h-4 mb-1"
                                icon="data-transfer-download" /> : grants download</li>
                        <li class=""><x-icons.iconic class=" text-neutral-200 px-0.5 w-4 h-4 mb-1"
                                icon="data-transfer-upload" /> : grants upload</li>
                        <li class=""><x-icons.iconic class=" text-neutral-200 px-0.5 w-4 h-4 mb-1"
                                icon="pencil" />
                            : grants edit</li>
                        <li class=""><x-icons.iconic class=" text-neutral-200 px-0.5 w-4 h-4 mb-1"
                                icon="trash" />
                            : grants delete</li>
                    </ul>
                </div>
            </div>
            <div class="flex flex-col">
                <div class="w-full flex my-1">
                    <p class="w-full flex align-middle border-b border-solid border-neutral-600 pb-3 text-neutral-200">
                        <span class="h-4 w-56 inline-block mt-2.5 font-bold">{{ __('lychee.ALBUM_TITLE') }}</span>
                        <span class="h-4 w-56 inline-block mt-2.5 font-bold">{{ __('lychee.USERNAME') }}</span>
                        <span class="h-4 w-56 inline-block text-center">
                            <x-icons.iconic class=" fill-neutral-200 px-2 w-8 h-8 mb-2" icon="fullscreen-enter" />
                            <x-icons.iconic class=" fill-neutral-200 px-2 w-8 h-8 mb-2" icon="data-transfer-download" />
                            <x-icons.iconic class=" fill-neutral-200 px-2 w-8 h-8 mb-2" icon="data-transfer-upload" />
                            <x-icons.iconic class=" fill-neutral-200 px-2 w-8 h-8 mb-2" icon="pencil" />
                            <x-icons.iconic class=" fill-neutral-200 px-2 w-8 h-8 mb-2" icon="trash" />
                        </span>
                    </p>
                </div>
                @forelse ($this->perms as $perm)
                    <livewire:forms.album.share-with-line :$perm :key="$perm->id" :album_title="$perm->album->title" />
                @empty
                    <p class="text-center">
                        Sharing list is empty
                    </p>
                @endforelse
            </div>
        </div>
    </div>
</div>
