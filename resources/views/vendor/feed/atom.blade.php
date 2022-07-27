<?php echo /* Using an echo tag here so the `<? ... ?>` won't get parsed as short tags */
	'<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL;
?>
<feed xmlns="http://www.w3.org/2005/Atom" xml:lang="{{ $meta['language'] }}">
    @foreach($meta as $key => $metaItem)
        @if($key === 'link')
            <{{ $key }} href="{{ url($metaItem) }}" rel="self"></{{ $key }}>
        @elseif($key === 'title')
            <{{ $key }}><![CDATA[{{ $metaItem }}]]></{{ $key }}>
        @elseif($key === 'description')
            <subtitle>{{ $metaItem }}</subtitle>
        @elseif($key === 'language')
        @elseif($key === 'image')
@if(!empty($metaItem))
            <logo>{!! $metaItem !!}</logo>
@else

@endif
        @else
            <{{ $key }}>{{ $metaItem }}</{{ $key }}>
        @endif
    @endforeach
    @foreach($items as $item)
        <entry>
            <title><![CDATA[{{ $item->title }}]]></title>
            <link rel="alternate" href="{{ url($item->link) }}" />
            <id>{{ url($item->id) }}</id>
            <author>
                <name><![CDATA[{{ $item->authorName }}]]></name>
@if(!empty($item->authorEmail))
                <email><![CDATA[{{ $item->authorEmail }}]]></email>

@endif
            </author>
            <summary type="html">
                <![CDATA[{!! $item->summary !!}]]>
            </summary>
            @if($item->__isset('enclosure'))
            <link href="{{ url($item->enclosure) }}" length="{{ $item->enclosureLength }}" type="{{ $item->enclosureType }}" />
            @endif
            @foreach($item->category as $category)
            <category term="{{ $category }}" />
            @endforeach
            <updated>{{ $item->timestamp() }}</updated>
        </entry>
    @endforeach
</feed>
