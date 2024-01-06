<div>
@if($can)
<pre class="font-mono">
    {{ $title }}
    {{ str_repeat('-', Str::length($title)) }}
    @foreach($this->data as $line)
    <span class="{{ $line['color'] }}">{{ $line['type'] }}</span> {{ $line['line']  }}
    @endforeach
</pre>
@endif
</div>