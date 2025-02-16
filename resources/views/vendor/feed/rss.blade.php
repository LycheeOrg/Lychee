<?php echo /* Using an echo tag here so the `<? ... ?>` won't get parsed as short tags */
'<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL;
?>
<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">
    <channel>
        <atom:link href="{{ url($meta['link']) }}" rel="self" type="application/rss+xml" />
        <title><![CDATA[{{ $meta['title'] }}]]></title>
        <link><![CDATA[{{ url($meta['link']) }}]]></link>
@if(!empty($meta['image']))
        <image>
            <url>{{ $meta['image'] }}</url>
            <title><![CDATA[{{ $meta['title'] }}]]></title>
            <link><![CDATA[{{ url($meta['link']) }}]]></link>
        </image>
@endif
        <description><![CDATA[{{ $meta['description'] }}]]></description>
        <language>{{ $meta['language'] }}</language>
        <pubDate>{{ $meta['updated'] }}</pubDate>

        @foreach($items as $item)
            <item>
                <title><![CDATA[{{ $item->title }}]]></title>
                <link>{{ url($item->link) }}</link>
                <description><![CDATA[{!! $item->summary !!}]]></description>
                <author><![CDATA[{{ $item->authorName }}@if(!empty($item->authorEmail)) <{{ $item->authorEmail }}>@endif]]></author>
                <guid>{{ url($item->id) }}</guid>
                <pubDate>{{ $item->timestamp() }}</pubDate>
                @foreach($item->category as $category)
                    <category>{{ $category }}</category>
                @endforeach
				@if($item->__isset('enclosure'))
				<enclosure url="{{ url($item->enclosure) }}" length="{{ $item->enclosureLength }}" type="{{ $item->enclosureType }}" />
				@endif
            </item>
        @endforeach
    </channel>
</rss>