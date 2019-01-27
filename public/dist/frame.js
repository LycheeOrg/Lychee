// doesn't work in older IEs
document.addEventListener('DOMContentLoaded', function(){
	document.getElementById("background").addEventListener('load', function () {
		start_blur();
	});
	document.getElementById("picture").addEventListener('load', function () {
		this.style.display = 'inline';
		document.body.classList.add("loaded");
	});
}, false);

function start_blur(){
	let img = document.getElementById('background');
	let canvas = document.getElementById('background_canvas');
	StackBlur.image(img,canvas,20);
	canvas.style.width = '100%';
	canvas.style.height = '100%';
	document.body.removeChild(img);
	setTimeout(function(){ next(); }, 30000);
}

function next() {
	document.body.classList.remove("loaded");
	setTimeout(function(){ location.reload(); }, 1000);
}