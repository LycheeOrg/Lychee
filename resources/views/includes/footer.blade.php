@if($infos['copyright_enable'] == '1')
<div id="footer" class="animate animate-up">
    <p id="home_copyright">All images on this website are subject to Copyright by {{ $infos['owner'] }} &copy; {{ $infos['copyright_year'] }}</p>
</div>
@endif
