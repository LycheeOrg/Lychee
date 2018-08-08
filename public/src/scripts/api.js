/**
 * @description This module communicates with Lychee's API
 */

api = {

	path    : 'api/',
	onError : null

}

api.post = function(fn, params, callback) {

	loadingBar.show()

	params = $.extend({ function: fn }, params)

	api_url = api.path.concat(fn)

	// alert(api_url)

	const success = (data) => {

		setTimeout(loadingBar.hide, 100)

		// Catch errors
		if (typeof data==='string' && data.substring(0, 7)==='Error: ') {
			api.onError(data.substring(7, data.length), params, data)
			return false
		}

		callback(data)

	}

	const error = (jqXHR, textStatus, errorThrown) => {

		api.onError('Server error or API not found.', params, errorThrown)

	}

	$.ajax({
		type: 'POST',
		// url: api.path,
		url: api_url,
		data: params,
		dataType: 'json',
		success,
		error
	})

}