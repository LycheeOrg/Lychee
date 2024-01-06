<div>
@if($can)
<pre class="font-mono">
    {{ $this->title }}
    {{ str_repeat('-', Str::length($this->title)) }}
    @foreach($this->data as $line)
    {{ $line }}
    @endforeach
</pre>
@endif
</div>