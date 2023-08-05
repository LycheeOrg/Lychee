@props(['back' => '$wire.back()'])
<x-header.button @keydown.escape.window="{{ $back }}" wire:click="back" icon="chevron-left" />