jQuery(document).ready(function(){
	var wpgmmap = L.map('wpgmmap').setView([0,0],1);
	var wpgmlayer;

	L.tileLayer('http://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
		maxZoom: 19,
		attribution: '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>'
	}).addTo(wpgmmap);

	jQuery('.wpgmsampledata').on('click',function(e){
		var this_layer_info = jQuery(e.target).parent();
		var color = this_layer_info.data('color');
		jQuery.getJSON(ajaxurl, {
			'action' : 'wpgm_get_sample_data',
			'type' : this_layer_info.data('type'),
			'meta_key' : this_layer_info.data('meta_key')
		}).then(

			function(success){
				if ( wpgmmap.hasLayer( wpgmlayer ) ) {
					wpgmmap.removeLayer( wpgmlayer );
				}
				wpgmlayer = L.geoJSON(success,{
					style: { 
						color : color
					},
					pointToLayer: function (feature, latlng) {
						return L.circleMarker(latlng, {
							radius: 8,
							color: color
						});
					},
					onEachFeature: function(feature, layer) {
						// does this feature have a property named popupContent?
						if ( feature.title !== undefined ) {
							layer.bindPopup( feature.title );
						}	
					}
				});
				wpgmmap.addLayer( wpgmlayer );	

				var bounds = wpgmlayer.getBounds();

				if ( bounds.isValid() ){
					wpgmmap.fitBounds( bounds );
				}
			},

			function(failure){
				console.log('Failure is not acceptable (yet).');
			});
	});
});
