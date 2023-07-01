@props(['class', 'icon', 'classIcon' => ''])
<a class='badge {{ $class }}'><svg class='iconic {{ $classIcon }}'><use xlink:href='#{{ $icon }}' /></svg></a>
