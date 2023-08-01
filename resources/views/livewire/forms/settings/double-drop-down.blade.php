<div class="my-7">
    <p class="w-full">
        {{ $begin }}
		<x-forms.dropdown wire:model.live="value1" :options="$this->options1" />
        {{ $middle }}
		<x-forms.dropdown wire:model.live="value2" :options="$this->options2" />
        {{ $end }}
    </p>
</div>
