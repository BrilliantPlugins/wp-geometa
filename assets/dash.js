jQuery(document).ready(function(){
	var wpgmmap = L.map('wpgmmap').setView([0,0],1);

	L.tileLayer('http://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
		maxZoom: 19,
		attribution: '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>'
	}).addTo(wpgmmap);
});
