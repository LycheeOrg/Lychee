<div 
	id="redirectData"
	data-gallery="{{ $gallery }}"
	data-redirect="{{ $base }}"
></div>
<script>
	// Dirty work around.
	const hashMatch = document.location.hash.replace("#", "").split("/");
	const albumID = hashMatch[0] ?? '';
	const photoID = hashMatch[1] ?? '';
	const elem = document.getElementById('redirectData');
	const gallery = elem.dataset.gallery;
	const base = elem.dataset.redirect;

	if (photoID !== '') {
		window.location = gallery + '/' + albumID + '/' + photoID;
	} else if (albumID !== '') {
		window.location = gallery + '/' + albumID;
	} else {
		window.location = base;
	}
</script>
