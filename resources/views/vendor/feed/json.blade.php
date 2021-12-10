{
    "version": "https://jsonfeed.org/version/1.1",
    "title": "{{ $meta['title'] }}",
    "home_page_url": "{{ config('app.url') }}",
    "feed_url": "{{ url($meta['link']) }}",
    "language": "{{ $meta['language'] }}",
@if(!empty($meta['image']))
    "icon": "{{ $meta['image'] }}",
@endif
    "items": [@foreach($items as $item){
            "id": "{{ url($item->id) }}",
            "title": "{{ $item->title }}",
            "url": "{{ url($item->link) }}",
            "content_html": "{!! str_replace('"', '\\"', $item->summary) !!}",
            "summary": "{!! str_replace('"', '\\"', $item->summary) !!}",
            "date_published": "{{ $item->timestamp() }}",
            "date_modified": "{{ $item->timestamp() }}",
            "authors": [{ "name": "{{ $item->authorName }}" }],
@if($item->__isset('image'))
            "image": "{{ url($item->image) }}",
@endif
@if($item->__isset('enclosure'))
            "attachments": [
                {
                    "url": "{{ url($item->enclosure) }}",
                    "mime_type": "{{ $item->enclosureType }}",
                    "size_in_bytes": {{ $item->enclosureLength }}
                }
            ],
@endif
            "tags": [ {!! implode(',', array_map(fn($c) => '"'.$c.'"', $item->category)) !!} ]
        }@if($item !== $items->last()),
@endif
        @endforeach

    ]
}
