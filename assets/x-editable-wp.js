jQuery(document).ready(function() { 
	
	/*
	jQuery('#focus-list-submit').submit( function() {
		
		var values = jQuery(#form_id_to_target).serialize();
		
		// then send values (i.e. data: values) with dataType: 'json'
			
	});
	
	jQuery.find('input.focus-list-checkbox').each( function() {
			
		ids.push(jQuery(this).attr('data-post_id'));
			
	});
		
	var result = ids.join(',');
		
	$(this).attr('value', result);
	
	*/	
	
	jQuery('.x-editable-element').each( function() {
	
		var inputType = jQuery(this).data('type');
		var acfType = jQuery(this).data('acf_type');
		var nonce = jQuery(this).data('nonce');
				
		var action = "xeditable_meta_handler";
	

		// datepicker input
		if ( inputType == 'date' ) {
			
			jQuery(this).editable({
				url: xeditable.ajaxurl,
				params: {
					action: action,
					nonce: nonce
				},
				// datepicker options
				format: 'yyyy-mm-dd',
				viewformat: 'M d, yyyy',
				datepicker: {
					weekStart: 0
				}
			});		
		
		}
		
		// TEXTAREA inputs
		else if ( inputType == 'textarea' ) {
			
			// set success function vars
			var objectId = jQuery(this).data('pk');
			var name = jQuery(this).data('name');
			
			jQuery(this).editable({
				url: xeditable.ajaxurl,
				params: {
					action: action,
					nonce: nonce,
					acf_type: acfType,
				},
				success: function() { 
					load_xe_field(objectId, name, jQuery('html').find( '#' + name + '-content' ) )
				}	
			});
			
		}
		
		// TAXONOMY field-type
		if ( acfType == 'taxonomy' ) {
			
			// extra post/success vars
			var objectId = jQuery(this).data('pk');
			var name = jQuery(this).data('name');
			var tax = jQuery(this).data('taxonomy');
			
			if ( jQuery(this).hasClass('single-value') ) {
				var singleValue = true;
			}
			
			// set success function vars
			if ( jQuery(this).hasClass('values-external') ) {
				var into = jQuery('html').find( '#' + name + '-content' );
				var asUl = true;
			} else { 
				var into = jQuery(this);
			}
			
			jQuery(this).editable({
				url: xeditable.ajaxurl, 
				params: {
					action: 'xeditable_acf_taxonomy',
					nonce: nonce,
					issingle: singleValue,
					tax: tax
				},
				display: false,
				source: xeditable.ajaxurl+'?action=xeditable_tax_options&tax='+tax,
				success: function() {
					load_xe_terms(objectId, tax, into, asUl);
				}
			});
			
		}
		
		// USER field-type
		else if ( acfType == 'user' ) {
			
			var userRole = jQuery(this).data('role');
			
			if ( jQuery(this).hasClass('single-value') ) {
				var singleValue = true;
			}
			
			jQuery(this).editable({
				url: xeditable.ajaxurl, 
				params: {
					action: action,
					nonce: nonce,
					acf_type: acfType,
					issingle: singleValue,
					//something
				},
				source: xeditable.ajaxurl+'?action=xeditable_user_options&role='+userRole,
				
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
				}	
			});	
		
		}
		
			
	});
	
	
	jQuery('.x-editable-tax').each( function() {
		
		var action = "xeditable_tax_handler";
		
		var inputType = jQuery(this).data('type');
		var nonce = jQuery(this).data('nonce');		
		var name = jQuery(this).data('name');
		var object_id = jQuery(this).data('pk');
				
		if (jQuery(this).hasClass('single-value')) {
			var issingle = true;
		}
		if ( 'typeahead' == inputType ) {

			jQuery(this).editable({
				url: xeditable.ajaxurl, 
				params: {
					action: action,
					nonce: nonce,
					issingle: issingle
				},
				autotext: 'never',
				display: false,
				// define select options
				source: xeditable.ajaxurl+'?action=xeditable_tax_options&string=true&tax='+name,
				success: function() { 
					if ( jQuery(this).hasClass('values-external') ) {
						var into = jQuery('html').find( '#'+name+'-content' );
						load_xe_terms(object_id, tax, into);
					}
					else {
						var into = jQuery(this);
						load_xe_terms_inline(object_id, name, into);
					}
				}
			});	
			
		}
		else {
							
			jQuery(this).editable({
				url: xeditable.ajaxurl, 
				params: {
					action: action,
					nonce: nonce,
					issingle: issingle
				},
				display: false,
				// define select options
				source: xeditable.ajaxurl+'?action=xeditable_tax_options&tax='+name,
				success: function() { 
					if ( jQuery(this).hasClass('values-external') ) {
						var into = jQuery('html').find( '#'+name+'-content' );
						load_xe_terms(object_id, name, into);
					}
					else {
						var into = jQuery(this);
						load_xe_terms_inline(object_id, name, into);
					}
				}
			});	
		
		}
		
	});
	
	// AJAX Post Meta loader function
	function load_xe_field(post_id, field, into) {
		
		jQuery.ajax({
			type: 'POST',
			url: xeditable.ajaxurl,
			data: {
				action: 'xeditable_meta_load',
				field: field,
				post_id: post_id
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
				as_ul: ul
			},
			success: function(data, textStatus, XMLHttpRequest){
				jQuery(into).html(data);	
			}
		});	
	}
	
	function load_xe_terms_inline(object_id, tax, into) {
		jQuery.ajax({
			type: 'POST',
			url: xeditable.ajaxurl,
			data: {
				action: 'xeditable_term_load',
				tax: tax,
				object_id: object_id,
				inline: 'true'
			},
			success: function(data, textStatus, XMLHttpRequest){
				jQuery(into).html(data);	
			}
		});	
	}

});