<div>
	<p>
		{{ $description }}
		<span class="select">
			<select wire:model="value">
			@foreach($this->options as $key => $option)
			@if (is_string($key))
				<option value="{{$key}}">{{ $option }}</option>
			@else
				<option>{{ $option }}</option>
			@endif
			@endforeach
			</select>
		</span>
	</p>
</div>