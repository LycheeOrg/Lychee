<div class="w-full">
    <x-header.bar class="h-14 select-none">
        <x-header.back @keydown.escape.window="$wire.back();" wire:click="back" />
        <x-header.title>{{ __('maintenance.title') }}</x-header.title>
    </x-header.bar>
    <div class="overflow-x-clip overflow-y-auto h-[calc(100vh-56px)] pt-4">
        <div class="settings_view max-w-3xl text-text-main-400 text-sm mx-auto mb-9">
            <div class="w-full mt-5">
                <p class="text-center">
                    {{ __('maintenance.description') }}
                </p>
            </div>
            <div class=" mt-9 grid grid-cols-1 gap-4 sm:grid-cols-2 sm:items-stretch md:grid-cols-3 md:gap-8 w-full">
                <livewire:modules.maintenance.update />
                <livewire:modules.maintenance.optimize />
                <livewire:modules.maintenance.cleaning :path="config('filesystems.disks.extract-jobs.root')" />
                <livewire:modules.maintenance.cleaning :path="config('filesystems.disks.image-jobs.root')" />
                <livewire:modules.maintenance.cleaning :path="config('filesystems.disks.livewire-upload.root')" />
                <livewire:modules.maintenance.fix-jobs />
                <livewire:modules.maintenance.fix-tree />
                <livewire:modules.maintenance.gen-size-variants :type="\App\Enum\SizeVariantType::SMALL->value" />
                <livewire:modules.maintenance.gen-size-variants :type="\App\Enum\SizeVariantType::SMALL2X->value" />
                <livewire:modules.maintenance.gen-size-variants :type="\App\Enum\SizeVariantType::MEDIUM->value" />
                <livewire:modules.maintenance.gen-size-variants :type="\App\Enum\SizeVariantType::MEDIUM2X->value" />
                <livewire:modules.maintenance.missing-file-sizes />
            </div>
        </div>
    </div>
</div>
