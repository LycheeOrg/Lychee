<script>
	// Dirty work around.
	const hashMatch = document.location.hash.replace("#", "").split("/");
	const albumID = hashMatch[0] ?? '';
	const photoID = hashMatch[1] ?? '';
	if (photoID !== '') {
		window.location = '{{ $gallery }}/' + albumID + '/' + photoID;
	} else if (albumID !== '') {
		window.location = '{{ $gallery }}/' + albumID;
	} else {
		window.location = '{{ $base }}';
	}
</script>