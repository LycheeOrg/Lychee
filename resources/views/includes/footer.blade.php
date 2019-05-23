<div id="footer" class="animate animate-up hide_footer">

    <!-- socials -->
    @if($page_config['display_socials'])
    <div id="home_socials" class="animate animate-up">
        @include('includes.socials')
    </div>
    @endif

    @if($infos['copyright_enable'] == '1')
        <p class="home_copyright">
            {{ $locale['FOOTER_COPYRIGHT'] }} {{ $infos['owner'] }} &copy; {{ $infos['copyright_year'] }}</p>
    @endif

    @if($infos['additional_footer_text'] != '')
        <p class="personal_text">{{ $infos['additional_footer_text'] }}</p>
    @endif


    @if($page_config['show_hosted_by'])
        <p class="hosted_by"><a target="_blank" href="https://LycheeOrg.github.io">{{ $locale['HOSTED_WITH_LYCHEE'] }}</a></p>
    @endif
</div>
