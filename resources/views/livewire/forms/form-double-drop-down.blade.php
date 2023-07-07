<div class="my-7">
    <p class="w-full">
        {{ $begin }}
		<x-forms.dropdown wire:model="value1" :options="$this->options1" />
        {{ $middle }}
		<x-forms.dropdown wire:model="value2" :options="$this->options2" />
        {{ $end }}
    </p>
</div>
