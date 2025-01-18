<div id="footer" class="animate animate-up hide_footer">

    <!-- socials -->
    @if($page_config['display_socials'])
    <div id="home_socials" class="animate animate-up">
		@if($infos['facebook'] != '')
			<a href="{{ $infos['facebook'] }}" class="socialicons" id="facebook" target="_blank" rel="noopener"></a>
		@endif
		@if($infos['flickr'] != '')
			<a href="{{ $infos['flickr'] }}" class="socialicons" id="flickr" target="_blank" rel="noopener"></a>
		@endif
		@if($infos['twitter'] != '')
			<a href="{{ $infos['twitter'] }}" class="socialicons" id="twitter" target="_blank" rel="noopener"></a>
		@endif
		@if($infos['instagram'] != '')
			<a href="{{ $infos['instagram'] }}" class="socialicons" id="instagram" target="_blank" rel="noopener"></a>
		@endif
		@if($infos['youtube'] != '')
			<a href="{{ $infos['youtube'] }}" class="socialicons" id="youtube" target="_blank" rel="noopener"></a>
		@endif
		<div style="clear: both;"></div>
	</div>
    @endif

    @if($infos['copyright_enable'] == '1')
        <p class="home_copyright">
			{!! sprintf(__('lychee.FOOTER_COPYRIGHT'), $infos['owner'], $infos['copyright_year']) !!}</p>
    @endif

    @if($infos['additional_footer_text'] != '')
        <p class="personal_text">{!! $infos['additional_footer_text'] !!}</p>
    @endif


    @if($page_config['show_hosted_by'])
        <p class="hosted_by"><a rel="noopener noreferrer" target="_blank" href="https://lycheeorg.dev" tabindex="-1">{{ __('lychee.HOSTED_WITH_LYCHEE') }}</a></p>
    @endif
</div>
