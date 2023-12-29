<div class="hidden" style="font-size: 24px; height: 100vh;">
	<h1>If you can read me, it means that you misconfigured Lychee.</h1>
	<p style="font-size: 20px;">Please check that:
	<ul>
		<li style="font-size: 20px; margin-bottom: 10px;">your
			<pre style="font-size: 16px; display:inline-block; margin: 0;">APP_URL</pre> is properly set to the correct url.<br>
			For example:
			<pre style="font-size: 16px; display:inline-block; margin: 0;">APP_URL=https://lychee.example.com</pre>
		</li>
		<li style="font-size: 20px; margin-bottom: 10px;">if you are working behind a reverse proxy, that
			<pre style="font-size: 16px; display:inline-block; margin: 0;">TRUSTED_PROXIES</pre> is set to the forwarding ip.<br>
			For example:
			<pre style="font-size: 16px; display:inline-block; margin: 0;">TRUSTED_PROXIES=*</pre><br>
			Note that the wildcard value (<pre style="font-size: 16px; display:inline-block; margin: 0;">*</pre>) is a very <b>insecure</b> option and not recommended.
		</li>
		<li style="font-size: 20px; margin-bottom: 10px;">if you are working behind an apache reverse proxy, that
			the forwarding headers are properly set.<br>
			For example:
			<pre style="font-size: 16px; display:inline-block; margin: 0;">RequestHeader set X-Forwarded-Proto https</pre> is set.
		</li>
		<li style="font-size: 20px; margin-bottom: 10px;">if the CSP is blocking your assets because serving http,
			you need to force https.<br>
			This is done by setting
			<pre style="font-size: 16px; display:inline-block; margin: 0;">APP_FORCE_HTTPS=true</pre>
		</li>
	</ul>
	</p>
</div>