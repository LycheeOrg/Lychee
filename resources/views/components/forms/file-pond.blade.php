<div x-data x-init="
	FilePond.setOptions({
		credits: false,
		allowMultiple: {{ isset($attributes['multiples']) ? 'true' : 'false' }},
		server: {
			process: (fieldName, file, metadata, load, error, progress, abort, transfer, options) => {
				// fieldName is the name of the input field
				// file is the actual file object to send
				@this.upload('{{ $attributes['wire:model'] }}', file, load, error, progress);
				@this.removeUpload('{{ $attributes['wire:model'] }}', file.id, load)
			},
			revert: (uniqueFileId, load, error) => {
				@this.removeUpload('{{ $attributes['wire:model'] }}', uniqueFileId, load)
			}
		}
		{{-- allowImagePreview: {{ $attributes->has('allowFileTypeValidation') ? 'true' : 'false' }}, --}}
		{{-- imagePreviewMaxHeight: {{ $attributes->has('imagePreviewMaxHeight') ? $attributes->get('imagePreviewMaxHeight') : '256' }}, --}}
		{{-- allowFileTypeValidation: {{ $attributes->has('allowFileTypeValidation') ? 'true' : 'false' }}, --}}
		{{-- acceptedFileTypes: {!! $attributes->get('acceptedFileTypes') ?? 'null' !!}, --}}
		{{-- allowFileSizeValidation: {{ $attributes->has('allowFileSizeValidation') ? 'true' : 'false' }}, --}}
		{{-- maxFileSize: {!! $attributes->has('maxFileSize') ? "'".$attributes->get('maxFileSize')."'" : 'null' !!} --}}
	});
	FilePond.create($refs.input)">
<input type="file" x-ref="input"/>
</div>