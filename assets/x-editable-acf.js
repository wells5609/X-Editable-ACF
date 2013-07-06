jQuery(document).ready(function() {
	
	jQuery('.x-editable-element').each( function() {
	
		var action = "xeditable_meta_handler";
		var nonce = jQuery(this).data('nonce');	
		
		var inputType = jQuery(this).data('type');
		var acfType = jQuery(this).data('acf_type');
		
		// used for success callbacks
		var name = jQuery(this).data('name');
		var objectId = jQuery(this).data('pk');
		var objectName = jQuery(this).data('object_name');
		
		
		// TEXTAREA inputs
		if ( inputType == 'textarea' ) {
			
			jQuery(this).editable({
				url: xeditable.ajaxurl,
				params: {
					action: action,
					nonce: nonce,
					acf_type: acfType,
					object_name: objectName,
				},
				success: function() { 
					load_xe_field(objectId, name, jQuery('html').find( '#' + name + '-content' ), objectName )
				}	
			});
			
		}
		
		// TAXONOMY field-type
		else if ( acfType == 'taxonomy' ) {
			
			// extra post/success vars
			var objectId = jQuery(this).data('pk');
			var name = jQuery(this).data('name');
			var tax = jQuery(this).data('taxonomy');
			
			if ( jQuery(this).hasClass('single-value') ) {
				var singleValue = true;
			}
			
			// set success function vars
			if ( jQuery(this).data('external') == 1 ) {
				var into = jQuery('html').find( '#' + name + '-content' );
				var asUl = true;
				var display = false;
				var autotext = 'never';
			} else { 
				var into = jQuery(this);
				var display = true;
				var autotext = 'auto';
			}
			
			jQuery(this).editable({
				url: xeditable.ajaxurl, 
				params: {
					action: 'xeditable_acf_taxonomy',
					nonce: nonce,
					issingle: singleValue,
					tax: tax,
					object_name: objectName,
				},
				display: display,
				source: xeditable.ajaxurl+'?action=xeditable_tax_options&tax='+tax,
				success: function() {
					load_xe_terms(objectId, tax, into, asUl);
				}
			});
			
		}
		
		// USER field-type
		else if ( acfType == 'user' ) {
			
			var objectId = jQuery(this).data('pk');
			var name = jQuery(this).data('name');
			var userRole = jQuery(this).data('role');
			
			if ( jQuery(this).hasClass('single-value') ) {
				var singleValue = true;
			}
			// set success function vars
			if ( jQuery(this).data('external') == 1 ) {
				var into = jQuery('html').find( '#' + name + '-content' );
				var asUl = true;
			} else { 
				var into = jQuery(this);
			}
			
			jQuery(this).editable({
				url: xeditable.ajaxurl, 
				params: {
					action: action,
					nonce: nonce,
					acf_type: acfType,
					issingle: singleValue,
					object_name: objectName,
				},
				display: false,
				source: xeditable.ajaxurl+'?action=xeditable_user_options&role='+userRole,
				success: function() { 
					load_xe_field(objectID, name, into, objectName)
				}
			});
		}
		
		// everything else (e.g. text)
		else {
		
			jQuery(this).editable({
				url: xeditable.ajaxurl,
				params: {
					action: action,
					nonce: nonce,
					acf_type: acfType,
					object_name: objectName,
				}	
			});	
		
		}
		
			
	});
	
		
	// AJAX Post Meta loader function
	function load_xe_field(post_id, field, into, objName) {
		
		jQuery.ajax({
			type: 'POST',
			url: xeditable.ajaxurl,
			data: {
				action: 'xeditable_meta_load',
				field: field,
				post_id: post_id,
				object_name: objName,
			},
			success: function(data, textStatus, XMLHttpRequest){
				jQuery(into).html(data);	
			}
		});	
	}
	// AJAX Post Terms loader function
	function load_xe_terms(object_id, tax, into, ul) {
		
		jQuery.ajax({
			type: 'POST',
			url: xeditable.ajaxurl,
			data: {
				action: 'xeditable_term_load',
				tax: tax,
				object_id: object_id,
				as_ul: ul,
			},
			success: function(data, textStatus, XMLHttpRequest){
				jQuery(into).html(data);	
			}
		});	
	}

});