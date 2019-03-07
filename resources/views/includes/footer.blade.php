@if($infos['copyright_enable'] == '1')
<div id="footer" class="animate animate-up">
    <p id="home_copyright">{{ $locale['FOOTER_COPYRIGHT'] }} {{ $infos['owner'] }} &copy; {{ $infos['copyright_year'] }}</p>
</div>
@endif
