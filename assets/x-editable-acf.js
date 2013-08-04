jQuery(document).ready(function() {
	
	jQuery('.x-editable-element').each( function() {
	
		var el = jQuery(this),
			action = "xeditable_meta_handler",
			nonce = el.data('nonce'),
			inputType = el.data('type'),
			acfType = el.data('acf_type');
		
		
		// used for success callbacks
		
		var name = el.data('name'),
			objectID = el.data('pk'),
			objName = el.data('object_name');
		
		
		if ( el.hasClass('single-value') ) {
			
			var isSingle = true;
		}
		
		var into = '#' + name + '-' + objectID + '-content';
		
		
		// TEXTAREA inputs
		if ( inputType == 'textarea' ) {
			
			el.editable({
				url: xeditable.ajaxurl,
				params: {
					action: action,
					nonce: nonce,
					acf_type: acfType,
					object_name: objName,
				},
				success: function() {
					jQuery(element).trigger('xe/success/meta', [into, objectID, name, objName, isSingle, inputType]);
				}	
			});
			
		}
		
		
		// TAXONOMY field-type
		else if ( acfType == 'taxonomy' ) {
			
			// extra post/success vars
			var name = el.data('name'),
				tax = el.data('taxonomy');
			
			// set success function vars
			if ( el.data('external') == 1 ) {
				
				var asUl = true,
					display = false,
					autotext = 'never';
					
			} 
			else {
			
				var into = el.attr('id'),
					display = true,
					autotext = 'auto';
			}
			
			jQuery(this).editable({
				url: xeditable.ajaxurl, 
				params: {
					action: 'xeditable_acf_taxonomy',
					nonce: nonce,
					issingle: isSingle,
					tax: tax,
					object_name: objName,
				},
				display: display,
				source: xeditable.ajaxurl+'?action=xeditable_tax_options&tax='+tax,
				success: function() {
					
					jQuery(into).trigger('xe/success/terms', [into, objectID, tax, asUl]);
					
				}
			});
			
		}
		
		// USER field-type
		else if ( acfType == 'user' ) {
			
			var name = el.data('name'),
				userRole = el.data('role');
			
			// set success function vars
			if ( el.data('external') == 1 )
				var asUl = true;
			
			else 
				var into = el.attr('id');
			
			
			jQuery(this).editable({
				url: xeditable.ajaxurl, 
				params: {
					action: action,
					nonce: nonce,
					acf_type: acfType,
					issingle: isSingle,
					object_name: objName,
				},
				display: false,
				source: xeditable.ajaxurl+'?action=xeditable_user_options&role='+userRole,
				success: function() { 
					load_xe_field(into, objectID, name, objName, isSingle)
				}
			});
			
		}
		
		// everything else
		else {
			
			if ( el.data('external') != 1 )	
				var into = el.attr('id');
			
			jQuery(this).editable({
				url: xeditable.ajaxurl,
				params: {
					action: action,
					nonce: nonce,
					acf_type: acfType,
					object_name: objName,
				},
				success: function() {
					jQuery(into).trigger('xe/success/meta', [into, objectID, name, objName, isSingle, inputType]);
				}
			});	
		
		}
		
			
	});
	
	jQuery(this).on('xe/success/terms', function(event, element, objectID, tax, ul) {
		
		load_xe_terms(element, objectID, tax, ul);
	
	});
	
	jQuery(this).on('xe/success/meta', function(event, element, objectID, name, objName, single, input){
		
		load_xe_field(element, objectID, name, objName, single);
	
	});
	
	// AJAX Post Meta loader function
	function load_xe_field(into, objectID, field, objectName, isSingle) {
		
		if ( ! isSingle ) {
			var isSingle = false;	
		}
		
		jQuery.ajax({
			type: 'POST',
			url: xeditable.ajaxurl,
			data: {
				action: 'xeditable_meta_load',
				field: field,
				post_id: objectID,
				object_name: objectName,
				single: isSingle,
			},
			success: function(data, textStatus, XMLHttpRequest){
				jQuery(into).hide().html(data).slideDown(450);	
			}
		});	
	}
	// AJAX Post Terms loader function
	function load_xe_terms(into, objectID, tax, ul) {
		
		jQuery.ajax({
			type: 'POST',
			url: xeditable.ajaxurl,
			data: {
				action: 'xeditable_term_load',
				tax: tax,
				object_id: objectID,
				as_ul: ul,
			},
			success: function(data, textStatus, XMLHttpRequest){
				jQuery(into).hide().html(data).slideDown(600);	
			}
		});	
	}

});