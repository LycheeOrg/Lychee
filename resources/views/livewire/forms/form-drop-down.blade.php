<div class="my-4">
	<p>
		{{ $description }}
		<span class="select relative my-[1px] mx-1 w-max text-white rounded overflow-hidden bg-black/30 inline-block text-2xs align-middle
		after:content-['≡'] after:absolute after:text-sky-400 after:right-2 after:top-0 after:font-bold after:text-lg after:-mt-1
		after:pointer-events-none">
			<select class="m-0 py-1 px-2 w-[120%] text-white bg-transparent text-2xs" wire:model="value">
			@foreach($this->options as $key => $option)
				<option class="text-neutral-800" @if (is_string($key)) value="{{ $key }}" @endif>
					{{ $option }}
				</option>
			@endforeach
			</select>
		</span>
	</p>
</div>