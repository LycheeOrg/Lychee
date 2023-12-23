<div class="text-neutral-200 text-sm p-9 text-center max-w-3xl">
	<p class="mb-4 text-center">{{ sprintf("Are you sure you want to transfer the ownership of album “%s” and all the photos in contains?
	You access to this album and will be lost.", $title) }}<br>{{ "This action can’t be undone!" }}</p>
	<div class="mt-4 h-12">
		<span class="font-bold">{{ "Transfer to" }}</span>
		<x-forms.dropdown class="mx-2" :options="$this->users" wire:model='username'/>
	</div>
	<x-forms.buttons.danger class="rounded-md w-full" wire:click='transfer'>
		{{ "Transfer ownership of album and photos" }}
	</x-forms.buttons.danger>
</div>