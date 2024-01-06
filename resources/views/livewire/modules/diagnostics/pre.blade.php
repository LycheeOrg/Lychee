<div>
@if($can)
<pre class="font-mono">
    {{ $title }}
    {{ str_repeat('-', Str::length($title)) }}
    @foreach($this->data as $line)
    {{ $line }}
    @endforeach
</pre>
@endif
</div>