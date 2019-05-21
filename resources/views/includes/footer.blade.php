<div id="footer" class="animate animate-up">
    @if($infos['copyright_enable'] == '1')
        <p class="home_copyright">
            {{ $locale['FOOTER_COPYRIGHT'] }} {{ $infos['owner'] }} &copy; {{ $infos['copyright_year'] }}</p>
    @endif
    @if(isset($show_hosted_by) && $show_hosted_by && isset($infos['additional_footer_text']) && $infos['additional_footer_text'] != '')
        <p class="personal_text">{{ $infos['additional_footer_text'] }}</p>
    @endif
    @if(isset($show_hosted_by) && $show_hosted_by)
        <p class="hosted_by">{{ $locale['HOSTED_WITH_LYCHEE'] }}</p>
    @endif
</div>
