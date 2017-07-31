/**
 * Set up the leaflet data preview map.
 */
jQuery(document).on('leafletphp/loaded',function(){
	var wpgmlayer;

	window.wpgmleaflet.map.on('focus', function(e) { e.target.scrollWheelZoom.enable(); });
	window.wpgmleaflet.map.on('blur', function(e) { e.target.scrollWheelZoom.disable(); });

	jQuery('.wpgmsampledata').on('click',function(e){
		var this_layer_info = jQuery(e.target).parent();
		var color = this_layer_info.data('color');

		jQuery('#yourdata-spinner').addClass('spinny');
		jQuery.getJSON(window.ajaxurl, {
			'action' : 'wpgm_get_sample_data',
			'type' : this_layer_info.data('type'),
			'meta_key' : this_layer_info.data('meta_key'),
			'subtype' : this_layer_info.data('subtype')
		}).then(

			function(success){
				if ( window.wpgmleaflet.map.hasLayer( wpgmlayer ) ) {
					window.wpgmleaflet.map.removeLayer( wpgmlayer );
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
				window.wpgmleaflet.map.addLayer( wpgmlayer );	

				var bounds = wpgmlayer.getBounds();

				if ( bounds.isValid() ){
					window.wpgmleaflet.map.fitBounds( bounds );
				}
				jQuery('#yourdata-spinner').removeClass('spinny');
			},

			function(){
				jQuery('#yourdata-spinner').removeClass('spinny');
				// console.log('Failure is not acceptable (yet).');
			});
	});

	jQuery('.wpgm-danger-action').on('click', function(e){
		var action_type = jQuery(e.target).data('action');
		var desc = jQuery(e.target).html();

		var conf_message = window.wpgmjs_strings.action_confirm_dialog.replace('%1$s',desc);
		if (window.confirm(conf_message) === true) {

			if ( action_type === 'show-dangerzone' ) {
				jQuery('.dragonactions').addClass('dangerous');
				jQuery(e.target).hide();
				return;
			}

			jQuery('.wpgm-danger-action').prop('disabled','disabled');
			jQuery('#danger-spinner').addClass('spinny');

			jQuery.get(window.ajaxurl, {
				'action' : 'wpgm_dangerzone',
				'action_type' : action_type
			}).then(
				function(success){
					jQuery('#wpgm-danger-results').html(success);
					jQuery('.wpgm-danger-action').prop('disabled','');
					jQuery('#danger-spinner').removeClass('spinny');
				}, function() {
					jQuery('.wpgm-danger-action').prop('disabled','');
					jQuery('#danger-spinner').removeClass('spinny');
				}
			);
		}
	});
});

/**
 * Set up dashboard page.
 */
jQuery(document).ready(function(){
	jQuery('.wpgmtabctrl li').on('click', wpgmeta_change_tab_event );
	jQuery('.wpgmmenulink').on('click', wpgmeta_change_tab_event );
	jQuery('#wpgeometa_import').on('submit', wpgeometa_import_file );
	jQuery('#wpgm_import_configuration').on('change', 'select.wpgm_select_widget', wpgeometa_select_widget_handler );
	jQuery('#wpgm_import_configuration').on('click', 'input.wpgm_import_step_two', wpgeometa_import_step_two );

	/**
	 * Detect existing hash and change tabs.
	 */
	var thetab = jQuery('.wpgmtabctrl li[data-tab="' + document.location.hash.replace('#','') + '"]');
	if ( thetab.length > 0 ) {
		wpgeometa_change_tab( document.location.hash.replace('#','') );
	}
});

/**
 * Click handler for switching tabs.
 */
function wpgmeta_change_tab_event(e) {
	var target = jQuery(e.target).data('tab');
	wpgeometa_change_tab( target );
	return false;
}

/**
 * The actual tab change logic.
 */
function wpgeometa_change_tab(target) {
	var thetab = jQuery('.wpgmtabctrl li[data-tab="' + target + '"]');
	if ( thetab.length === 0 ) {
		return false;
	}

	jQuery('.wpgmtabctrl li.shown').removeClass('shown');
	jQuery('.wpgmtabctrl li[data-tab="' + target + '"]').addClass('shown');
	jQuery('.wpgmtab.shown').removeClass('shown');
	jQuery('.wpgmtab[data-tab="' + target + '"]').addClass('shown');

	if ( target === 'yourmeta' ) {
		window.wpgmleaflet.map.invalidateSize();
	}
	document.location.hash = target;
}

/**
 * Start the import file process.
 */
function wpgeometa_import_file(e) {
	jQuery('#wpgm_import_configuration').hide();
	var f = e.target;
	var action = jQuery(f).attr('action');
	var fd = new FormData(f);
	try {
		var submitted = jQuery.ajax({
			url: action,
			type: 'POST',
			data: fd,
			processData: false,
			contentType: false
		});
		submitted.then( wpgeometa_import_mapping_setup, wpgeometa_import_loop_failure );
	} catch (err){
		// Do nothing.
		// console.log(err);
	}
	return false;
}

/**
 * Handle initial upload success and lay out mapping configuration page.
 */
function wpgeometa_import_mapping_setup( success ) {

	jQuery('#wpgm_import_configuration').data( 'upload', success.data );

	var options = '<option value="">—</option>';
	for( var wptype in success.data.wp_types ) {
		options += '<option value="' + wptype + '">' + success.data.wp_types[wptype].label + '</option>';
	}
	jQuery('#wpgm_import_configuration select[name="importtype"]').html( options );
	jQuery('#wpgm_import_configuration select[name="importtype"]').on( 'change', wpgeometa_build_mapping_ui );

	jQuery('#wpgm_import_configuration').show();
}

function wpgeometa_build_mapping_ui() {
	var data = jQuery('#wpgm_import_configuration').data( 'upload' );

	var type = jQuery('#wpgm_import_configuration select[name="importtype"]').val();
	var type_data = data.wp_types[type];

	// Make the select widget.
	var select_widget = '<select class="wpgm_select_widget">';
	select_widget += '<option value=""><em>Ignore</em></option>';
	select_widget += '<option value="" data-isnew="true">Specify Field Name</option>';

	select_widget += '<optgroup label="Standard Fields">';
	for ( var f in type_data.fields ) {
		select_widget += '<option value="' + f +'">' + type_data.fields[f] + '</option>';
	}
	select_widget += '</optgroup>';

	select_widget += '<optgroup label="Meta Fields">';
	for ( var m = 0; m < type_data.meta.length; m++ ) {
		select_widget += '<option value="' + type_data.meta[m] +'">' + type_data.meta[m] + '</option>';
	}
	select_widget += '</optgroup>';
	select_widget += '</select><input type="text" class="wpgm_actual_value" placeholder="New Meta Key">';


	var field;
	var html = '';

	// Geometry Meta Name.
	html += '<label for="geometafield">Where should the geometry be stored? </label>';
	html += '<span>';
	if ( type_data.geometa.length > 0 ) {
		html += '<select class="wpgm_select_widget">';
		html += '<option value="">—</option>';
		html += '<option value="" data-isnew="true">New Meta Key</option>';
		html += '<optgroup label="Existing Geometa Fields">';

		for ( var g = 0; g < type_data.geometa.length; g++ ) {
			html += '<option value="' + type_data.geometa[g] + '">' + type_data.geometa[g] + '</option>';
		}

		html += '</optgroup>';
		html += '</select><input type="text" class="wpgm_actual_value" placeholder="New Meta Key">';
		html += '</span>';
	} else {
		html += '<input type="text" value="geom" class="wpgm_geometa_value" placeholder="New GeoMeta Key">';
	}
	html += '<br>';

	// Lookup Mapping.
	html += 'For updates look for ';
	html += '<select class="wpgm_upsert_value_field">';
	html += '<option value="">—</option>';
	for( var i = 0; i < data.geojson_fields.length; i++ ){
		field = data.geojson_fields[i].split('://')[1].trim('/').split('/');
		if ( field[0] === 'property' ) {
			html += '<option value="' + data.geojson_fields[i] + '">' + field[1] + '</option>';
		} else {                                                                                            
			html += '<option value="' + data.geojson_fields[i] + '">' + field[0] + '</option>';
		}
	}
	html += '</select>';
	html += ' in ';
	html += '<select class="wpgm_upsert_key_field">';
	html += '<option value="">—</option>';

	html += '<optgroup label="Standard Fields">';
	for ( f in type_data.fields ) {
		html += '<option value="' + f +'">' + type_data.fields[f] + '</option>';
	}
	html += '</optgroup>';

	html += '<optgroup label="Meta Fields">';
	for ( m = 0; m < type_data.meta.length; m++ ) {
		html += '<option value="' + type_data.meta[m] +'">' + type_data.meta[m] + '</option>';
	}
	html += '</optgroup>';

	html += '</select>';
	html += ' and if not found ';
	html += '<select class="wpgm_upsert_not_found">';
	html += '<option value="create">Create New Records</option>';
	html += '<option value="ignore">Ignore Data</option>';
	html += '</select>';

	// Build the mapping table.	
	html += '<table><tr><th>GeoJSON Field</th><th>Destination Field</th></tr>';
	for( i = 0; i < data.geojson_fields.length; i++ ){
		field = data.geojson_fields[i].split('://')[1].trim('/').split('/');
		if ( field[0] === 'property' ) {
			html += '<tr data-fieldname="' + field[1] + '" data-geojson="' + data.geojson_fields[i] + '"><td>' + field[1] + ' (property)</td><td>' + select_widget + '</td></tr>';
		} else {                                                                                            
			html += '<tr data-fieldname="' + field[0] + '" data-geojson="' + data.geojson_fields[i] + '"><td>' + field[0] + '</td><td>' + select_widget + '</td></tr>';
		}
	}
	html += '</table>';

	// Submit button.
	html += '<br><input type="submit" class="wpgm_import_step_two" value="Import"><br>';
	jQuery('#wpgm_mapping_area').html( html );
}

/**
 * Handle upload success loop.
 */
function wpgeometa_import_loop_success( success ) {
	if ( success.data.total === success.data.processed ) {
		jQuery( '#wpgm_import_progressbar .label' ).text( '100 %' );
		jQuery( '#wpgm_import_progressbar .colorbar' ).width( '100%' );
		return;
	}

	var percentage = parseInt(success.data.processed / success.data.total * 100, 10);

	jQuery( '#wpgm_import_progressbar .label' ).text( percentage + ' %' );
	jQuery( '#wpgm_import_progressbar .colorbar' ).width( percentage + '%' );
	
	jQuery.post( success.data.post_action, success.data ).then( wpgeometa_import_loop_success, wpgeometa_import_loop_failure );
}

/**
 * Handle upload failure.
 */
function wpgeometa_import_loop_failure() {
	jQuery( '#wpgm_import_progressbar .label' ).text( 'Upload Failed' );
	jQuery( '#wpgm_import_progressbar .colorbar' ).width( '0%' );
}

/**
 * Handle the GeoJSON importer select widget.
 */
function wpgeometa_select_widget_handler( e ) {
	var input_field = jQuery(e.target).parent().find('input[type="text"].wpgm_actual_value');
	if ( jQuery(e.target.selectedOptions).first().data('isnew') === true ) {
		input_field.val( jQuery(e.target).closest('tr').data('fieldname') );
		input_field.show();
	} else {
		input_field.hide();
		input_field.val( e.target.value );
	}
}

/**
 * Collect the mapping the user made and start the import loop.
*/
function wpgeometa_import_step_two() {


	jQuery( '#wpgm_import_progressbar .label' ).text( 0 + ' %' );
	jQuery( '#wpgm_import_progressbar .colorbar' ).width( 0 + '%' );

	var formdiv = jQuery('#wpgm_import_configuration');
	var data = formdiv.data( 'upload' );
	var mapping = {};

	mapping.geometa_key = formdiv.find('.wpgm_geometa_value').val();
	mapping.post_type = formdiv.find('select[name="importtype"]').val();
	mapping.upserts = {
		'search_field': formdiv.find('select.wpgm_upsert_value_field').val(),
		'value_field':  formdiv.find('select.wpgm_upsert_key_field').val(),
		'not_found_action': formdiv.find('select.wpgm_upsert_not_found').val()
	};
	mapping.fields = {};

	if ( mapping.geometa_key === '' ) {
		formdiv.find('.wpgm_geometa_value').addClass('error');
		return false;
	}

	var trs = formdiv.find('table tr');
	var val;
	for( var i = 1; i < trs.length; i++ ) {
		val = jQuery(trs[i]).find('input.wpgm_actual_value').val();
		if ( val === '' ) {
			continue;
		}
		if ( !val.match(/:\/\//) ) {
			val = 'standard://' + val;
		}
		mapping.fields[ jQuery(trs[i]).data('geojson') ] = val;
	}

	data.mapping = mapping;
	jQuery.post( data.post_action, data ).then( wpgeometa_import_loop_success, wpgeometa_import_loop_failure );
}
