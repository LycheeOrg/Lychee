@foreach($feeds as $name => $feed)
    <link rel="alternate" type="{{ \Spatie\Feed\Helpers\FeedContentType::forLink($feed['format'] ?? 'atom') }}" href="{{ route("feeds.{$name}") }}" title="{{ $feed['title'] }}">
@endforeach
