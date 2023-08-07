<p class="font-mono">
    {{ $title }}
    {{ str_repeat('-', Str::length($title)) }}
    @forelse($this->data as $line)
    <span class="{{ $line['color'] }}">{{ $line['type'] }}</span> {{ $line['line']  }}
    @empty
    {{ $error_msg }}
    @endforelse
</p>