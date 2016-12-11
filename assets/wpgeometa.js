jQuery(document).ready(function(){
	window.wpgmmap = L.map('wpgmmap', {
		scrollWheelZoom: false
	}).setView([0,0],1);
	var wpgmlayer;

	wpgmmap.on('focus', function(e) { e.target.scrollWheelZoom.enable(); });
	wpgmmap.on('blur', function(e) { e.target.scrollWheelZoom.disable(); });

	L.tileLayer('//{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
		maxZoom: 19,
		attribution: '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>'
	}).addTo(wpgmmap);

	jQuery('.wpgmsampledata').on('click',function(e){
		var this_layer_info = jQuery(e.target).parent();
		var color = this_layer_info.data('color');

		jQuery('#yourdata-spinner').addClass('spinny');
		jQuery.getJSON(ajaxurl, {
			'action' : 'wpgm_get_sample_data',
			'type' : this_layer_info.data('type'),
			'meta_key' : this_layer_info.data('meta_key'),
			'subtype' : this_layer_info.data('subtype')
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
				jQuery('#yourdata-spinner').removeClass('spinny');
			},

			function(failure){
				jQuery('#yourdata-spinner').removeClass('spinny');
				console.log('Failure is not acceptable (yet).');
			});
	});

	jQuery('.wpgm-danger-action').on('click', function(e){
		var action_type = jQuery(e.target).data('action');
		var desc = jQuery(e.target).html();

		var conf_message = wpgmjs_strings.action_confirm_dialog.replace('%1$s',desc);
		if (confirm(conf_message) === true) {

			if ( action_type === 'show-dangerzone' ) {
				jQuery('.dragonactions').addClass('dangerous');
				jQuery(e.target).hide();
				return;
			}

			jQuery('.wpgm-danger-action').prop('disabled','disabled');
			jQuery('#danger-spinner').addClass('spinny');

			jQuery.get(ajaxurl, {
				'action' : 'wpgm_dangerzone',
				'action_type' : action_type
			}).then(
				function(success){
					jQuery('#wpgm-danger-results').html(success);
					jQuery('.wpgm-danger-action').prop('disabled','');
					jQuery('#danger-spinner').removeClass('spinny');
				}, function(failure) {
					jQuery('.wpgm-danger-action').prop('disabled','');
					jQuery('#danger-spinner').removeClass('spinny');
				}
			);
		}
	});

	jQuery('.wpgmtabctrl li').on('click', wpgmeta_change_tab);
	jQuery('.wpgmmenulink').on('click', wpgmeta_change_tab);
});

function wpgmeta_change_tab(e) {
	var target = jQuery(e.target).data('tab');
	jQuery('.wpgmtabctrl li.shown').removeClass('shown');
	jQuery('.wpgmtabctrl li[data-tab="' + target + '"]').addClass('shown');
	jQuery('.wpgmtab.shown').removeClass('shown');
	jQuery('.wpgmtab[data-tab="' + target + '"]').addClass('shown');

	if ( target === 'yourmeta' ) {
		wpgmmap.invalidateSize();
	}
	return false;
}
