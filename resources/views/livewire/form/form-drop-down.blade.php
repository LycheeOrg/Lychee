<div>
	<p>
		{{ $description }}
		<span class="select">
			<select wire:model="value">
			@foreach($this->options as $key => $option)
			@if (is_string($key))
				{{-- key is a string = value, option is the description --}}
				<option value="{{$key}}" @if($key === $value) selected @endif>{{ $option }}</option>
			@else
				{{-- key is an integer, option is the value --}}
				<option @if($option === $value) selected @endif>{{ $option }}</option>
			@endif
			@endforeach
			</select>
		</span>
	</p>
</div>