<pre>
    {{ $title }}
    {{ str_repeat('-', Str::length($title)) }}
    @forelse($this->data as $line)
    {{ $line }}
    @empty
    {{ $error_msg }}
    @endforelse
</pre>