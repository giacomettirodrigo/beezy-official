jQuery(document).ready(function($){
	
	// robots.txt
	$("#siteseo_googlebots, #siteseo_bingbots, #siteseo_yandex_bots, #siteseo_semrushbot, #siteseo_rss_feeds, #siteseo_gptbots, #siteseo_link_sitemap, #siteseo_wp_rule, #siteseo_majesticsbots, #siteseo_ahrefsbot, #siteseo_mangools, #siteseo_google_ads_bots, #siteseo_google_img_bot").on("click", function(){
		let currentVal = $("#siteseo_robots_file_content").val();
		let tagVal = $(this).attr("data-tag");
		$("#siteseo_robots_file_content").val(currentVal + "\n" + tagVal);
	});
	
	// htaccess
	$('#siteseo_block_dir, #siteseo_wp_config, #siteseo_error_300').on('click', function(){
		let currentVal = $("#siteseo_htaccess_file").val();
		let tagVal = $(this).attr("data-tag");
		$("#siteseo_htaccess_file").val(currentVal + "\n" + tagVal);
	});
	
	$('#siteseopro-pagespeed-results .siteseo-metabox-tab-label').click(function(){
		$('.siteseo-metabox-tab-label').removeClass('siteseo-metabox-tab-label-active');
		$('.siteseo-metabox-tab').hide();

		$(this).addClass('siteseo-metabox-tab-label-active');

		var activeTab = $(this).data('tab');
		$('.' + activeTab).show();
	});
	
	$('input[name="ps_device_type"]').on('change', function(){
		jEle = jQuery(this),
		val = jEle.val();
		
		if(val == 'mobile'){
			jQuery('#siteseo-ps-mobile').css('display', 'flex');
			jQuery('#siteseo-ps-mobile').find('.siteseo-metabox-tab-label:first-child').trigger('click');
			jQuery('#siteseo-ps-desktop').hide();
		} else {
			jQuery('#siteseo-ps-mobile').hide();
			jQuery('#siteseo-ps-desktop').css('display', 'flex');
			jQuery('#siteseo-ps-desktop').find('.siteseo-metabox-tab-label:first-child').trigger('click');
		}
		
	});

    $('#siteseopro-pagespeed-btn').on('click', function(){
		$('#siteseopro-pagespeed-results').empty();
    let spinner = $(this).next(),
		input = $(this).closest('div').find('input');

    spinner.addClass('is-active'),

		siteseo_pagespeed_request(input.val(), true);
		siteseo_pagespeed_request(input.val(), false);
    });

	$('#siteseopro-clear-Page-speed-insights').on('click', function(){
		$.ajax({
			url: siteseo_pro.ajax_url,
			type: 'POST',
			data: {
				action: 'siteseo_pro_pagespeed_insights_remove_results',
				nonce: siteseo_pro.nonce
			},
			success: function(response){
				$('#siteseopro-pagespeed-results').empty();
			}
		});

	});

	$('.siteseo-audit-title').next('.description').hide();

    $('.siteseo-audit-title').on('click', function(e){
        var description = $(this).next('.description');
        var icon = $(this).find(".toggle-icon");

        if(description.is(':visible')){
			description.hide();
			icon.addClass('class', 'toggle-icon dashicons dashicons-arrow-up-alt2');
        } else {
			description.show();
			icon.addClass('class', 'toggle-icon dashicons dashicons-arrow-down-alt2');
        }
    });
	
	//htaccess
	$('#siteseo_htaccess_btn').on('click', function(){
        event.preventDefault();
		
		let spinner = $(event.target).next('.spinner');

		if(spinner.length){
			spinner.addClass('is-active');
		}

        let htaccess_code = $('#siteseo_htaccess_file').val(),
        htaccess_enable = $('#siteseo_htaccess_enable').is(':checked') ? 1 : 0;

        $.ajax({
        
            url : siteseo_pro.ajax_url,
			method: 'POST',
            data: {
                action: 'siteseo_pro_update_htaccess',
                htaccess_code: htaccess_code,
                htaccess_enable: htaccess_enable,
                _ajax_nonce : siteseo_pro.nonce
            },
            success: function(res){
				if(spinner.length){
					spinner.removeClass('is-active');
				}
				
				if(res.success){
					alert(res.data);
					return;
				}
				
				if(res.data){
					alert(res.data)
					return;
				}

				alert('Something went wrong, updating the file');
            }
        });
    });
	
	// Csv download
	$('#siteseo-export-csv').on('click', function(event){
		event.preventDefault();
        
		$.ajax({
			method: 'POST',
			url: siteseo_pro.ajax_url,
			data: {
				action: 'siteseo_pro_export_redirect_csv',
				_ajax_nonce: siteseo_pro.nonce
			},
            
			beforeSend: function(){
				$('#siteseo-export-csv').prop('disabled', true);
			},
			xhrFields:{
				responseType: 'blob'
			},
			success: function(response, status, xhr){
                
        var filename = 'siteseo-redirect-data-' + new Date().toISOString().slice(0,10) + '.csv';
				var disposition = xhr.getResponseHeader('Content-Disposition');
                if(disposition){
					var match = disposition.match(/filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/);
					if(match && match[1]){
                        filename = match[1].replace(/['"]/g, '');
					}
				}
                
			  var blob = new Blob([response], { type: 'text/csv' });
				var url = window.URL.createObjectURL(blob);
				var a = document.createElement('a');
				a.href = url;
				a.download = filename;
				document.body.appendChild(a);
				a.click();
				window.URL.revokeObjectURL(url);
				document.body.removeChild(a);
			},
			error: function(){
				alert('Error connecting to the server');
			},
			complete: function(){
				$('#siteseo-export-csv').prop('disabled', false);
			}
		});
	});
	
	// Clear all redirect logs
	$('#siteseo_redirect_all_logs').on('click', function(){
		event.preventDefault();
		
		if(!confirm('Are you sure you want to clear all logs?')){
			return;
		}

		let spinner = $(event.target).next('.spinner');

		if(spinner.length){
			spinner.addClass('is-active');
		}

		$.ajax({
			method: 'POST',
			url: siteseo_pro.ajax_url,
			data: {
				action: 'siteseo_pro_clear_all_logs',
				_ajax_nonce: siteseo_pro.nonce
			},
			success: function(res){
				if(spinner.length){
					spinner.removeClass('is-active');
				}

				if(res.success){
					alert(res.data);
					window.location.reload();
					return;
				}
				alert('Unable to clear logs.');
			},
			error: function(){
				alert('Error clearing logs.');
				if(spinner.length){
					spinner.removeClass('is-active');
				}
			}
		});
	});
	
	// update robots file
	$('#siteseo-update-robots').on('click', function(){
		event.preventDefault();
	
		let spinner = $(event.target).next('.spinner');

		if(spinner.length){
			spinner.addClass('is-active');
		}

		$.ajax({
			method : 'POST',
			url : siteseo_pro.ajax_url,
			data : {
				action : 'siteseo_pro_update_robots',
				robots : $('#siteseo_robots_file_content').val(),
				_ajax_nonce : siteseo_pro.nonce
			},
			success: function(res){
				
				if(spinner.length){
					spinner.removeClass('is-active');
				}

				if(res.success){
					alert(res.data);
					window.location.reload();
					return;
				}

				if(res.data){
					alert(res.data);
					return;
				}
				
				alert('Unable to create the robots.txt file');
			}
		});
	});
	
	$('#select-all-logs').on('click', function(){
		$('.log-selector').prop('checked', this.checked);
	});
	
	//Delete specific recoder 
	$('#siteseo-remove-selected-log').on('click', function(){
		var selectedIds = [];
		
		$('.log-selector:checked').each(function(){
			selectedIds.push($(this).val());
		});
		
		if(selectedIds.length === 0){
			alert('Please select at least one log to delete');
			return;
		}
		
		if(!confirm('Are you sure you want to delete the selected logs?')){
			return;
		}
		
		$.ajax({
			type : 'POST',
			url: siteseo_pro.ajax_url,
			data:{
				action: 'siteseo_pro_remove_selected_logs',
				ids: selectedIds,
				_ajax_nonce: siteseo_pro.nonce
			},
			success: function(response){
				if(response.success){
					
					$('.log-selector:checked').closest('tr').remove();
					alert('Selected logs deleted successfully');
				}else{
					alert('Error: ' + response.data);
				}
			},
			error: function(){
				alert('Failed to delete logs. Please try again.');
			}
		});
	});
	
	// Delete robots txt file
	$('#siteseopro-delete-robots-txt').on('click', function(e){
    e.preventDefault();
		$.ajax({
      type: 'POST',
			url: siteseo_pro.ajax_url,
			data: {
        action: 'siteseo_pro_delete_robots_txt',
			  _ajax_nonce: siteseo_pro.nonce
			},
			success: function(response){
				
        if(response.success){
					location.reload();
				} else{
					alert(response.data);
				}
			},
			error: function(xhr, status, error){
				alert('An error occurred: ' + error);
			}
		});
	});

	// handel ajax toggle
  $('.siteseo-toggleSw').on('click', function(){
    const $toggle = $(this);
    const toggleKey = $toggle.data('toggle-key');
    const action = $toggle.data('action');

    saveToggle($toggle, toggleKey, action);
	});

  function saveToggle($toggle, toggleKey, action){
    const $container = $toggle.closest('.siteseo-toggleCnt');
    const $stateText = $container.find(`.toggle_state_${toggleKey}`);
    const $input = $(`#${toggleKey}`);

    $container.addClass('loading');
    $toggle.toggleClass('active');

    const newValue = $toggle.hasClass('active') ? '1' : '0';
    $input.val(newValue);
    $stateText.text($toggle.hasClass('active') ? 'Click to disable this feature' : 'Click to enable this feature');

    $.ajax({
    url: ajaxurl,
    type: 'POST',
    data: {
    	action: action,
    	toggle_value: newValue,
    	nonce: $toggle.data('nonce')
    },
    success: function(response){
    	if(response.success){
    		// Show the custom toast message
    		ToastMsg('Your settings have been saved.');
        if(response.data.reload){
          location.reload();
        }
    	} else{
    		console.error('Failed to save toggle state');
    		toggleError($toggle, $input, $stateText);
    		ToastMsg(response.data.message || 'Failed to save toggle state', 'error');
    	}
    },
    error: function() {
    	console.error('Ajax request failed');
    	toggleError($toggle, $input, $stateText);
    	ToastMsg('Unable to save settings', 'error');
    },
    complete: function() {
    $container.removeClass('loading');
			}
		});
	}
	
	//toast
	function ToastMsg(message, type = 'success') {

		const toast = $('<div>')
			.addClass('siteseo-toast')
			.addClass(type) 
			.html(`<span class="dashicons dashicons-yes"></span> ${message}`);

		$('body').append(toast); 

		// 3 seconds
		toast.fadeIn(300).delay(3000).fadeOut(300, function () {
			toast.remove();
		});
	}

	// error hadeler
	function toggleError($toggle, $input, $stateText) {
		$toggle.toggleClass('active');
		$input.val($toggle.hasClass('active') ? '1' : '0');
		$stateText.text($toggle.hasClass('active') ? 'Disable' : 'Enable');
	}

	// media uploader for image logo 
	$('#siteseopro_structured_data_upload_img').click(function(e) {
		var mediaUploader;
		e.preventDefault();
		
		if (mediaUploader) {
			mediaUploader.open();
			return;
		}

		
		mediaUploader = wp.media.frames.file_frame = wp.media({
			title: 'Media',
			button: {
				text: 'Select'
			},
			multiple: false
		});

		
		mediaUploader.on('select', function() {
			var attachment = mediaUploader.state().get('selection').first().toJSON();
			$('#structured_data_image_url').val(attachment.url);
		});
		
		mediaUploader.open();

	});

	$('#siteseo-sidebar-refersh-tokens').on('click', function(e){
        e.preventDefault();
        let token_container = $(this).closest(".siteseo-ai-token-count");
        $('.siteseo-sidebar-ai-tokens').fadeOut(200).fadeIn(200);
        
        $.ajax({
            url: siteseo_pro.ajax_url,
            method: "POST",
            data: {
                action: "siteseo_pro_refresh_tokens",
                nonce: siteseo_pro.nonce,
            },
            success: function(res){
                if(!res.success){
                    if(res.data){
                        alert(res.data);
                        return;
                    }
                    alert("Something went wrong fetching token data");
                    return;
                }
                update_dashbord_ai_tokens(res.data);
            },
            error: function(){
                alert("Error refreshing tokens");
            }
        });
    });
    
    function update_dashbord_ai_tokens(remaining_tokens){
        let formatted_tokens = remaining_tokens < 0 ? 0 : parseInt(remaining_tokens).toLocaleString('en'),
        token_badge = $('.siteseo-ai-token-badge');
        
        // Update the tokens count text
        $('.siteseo-sidebar-ai-tokens').text('Tokens Remaining ' + formatted_tokens);
        
        // If you want to add a badge (though it's not in your original HTML)
        if(token_badge.length === 0){
            $('.siteseo-ai-token-count').prepend(
                '<span class="siteseo-ai-token-badge">' + formatted_tokens + '</span>'
            );
        } else{
            token_badge.text(formatted_tokens);
        }
    }
	
	/** global schema modal **/
	$('#siteseo-auto-schema-modal').dialog({
		autoOpen: false,
		modal: true,
		width: '40%',
		minWidth: 500,
		maxWidth: 600,
		closeOnEscape: false,
		dialogClass: 'siteseo-modal',
		draggable: false,
		resizable: false,
		position: { my: "center", at: "center", of: window },
		buttons: {
			"submit": {
				text: "Save",
				class: "save-schema-btn",
				click: function(){
					save_schema();
				}
			}
		}, 
		open: function(){
			// add spinner in footer
			let $btn = $(this).parent().find('.save-schema-btn');
			if(!$btn.next('.spinner').length){
				$btn.after('<span class="spinner" style="margin-left:130px; position:absolute;"></span>');
			}
		}	 
	});
	
  /** Add events **/
	$('#siteseo-auto-schema-modal').on('dialogopen', function() {
		// Add schema rule event
		$('.siteseo-add-schema-rule').off('click').on('click', function(e){
			e.preventDefault();
			let $row = $(this).closest('tr'),
			$container = $row.find('.siteseo-schema-rule-container'),
			$first = $container.find('select').first();

			let $clone = $first.clone();
			let $wrapper = $('<div class="siteseo-schema-rule" style="margin-top:5px; display:flex; align-items:center; gap:5px;"></div>');
			$wrapper.append($clone);
			$wrapper.append('<span title="Delete Rule" class="dashicons dashicons-trash siteseo-remove-schema-rule"></span>');
			$container.append($wrapper);

			// delete rule event as new elements are added
			bind_remove_rule();
		});

		// Delete rule event
		bind_remove_rule();

		// Specific targets change event
		bind_specific_targets_change();
	});
	
	$('#siteseo-auto-schema').on('click', function(e){
		e.preventDefault();

		// Open modal
		$('#siteseo-auto-schema-modal').dialog('open');

		// Clear all inputs and selects
		$('input[name="schema_id"]').val('');
		$('input[name="schema_name"]').val('');
		$('select[name="schema_type"]').val('None').trigger('change');

		// Clear dynamically added rules
		$('.siteseo-schema-rule-container').each(function(){
			let $first = $(this).find('select').first().clone();
			$(this).empty().append($first);
		});

		$('.siteseo-specific-input').remove();

		// Clear schema properties container
		$('#siteseo-schema-properties-show').empty();

		// Set modal title
    $('#siteseo-auto-schema-modal').dialog('option', 'title', 'Add Schema');
	});

	/**loads global schema **/
	$('#siteseo_schema_type').change(function(e){
		let schema_type = $(this).val();
		let container = $('#siteseo-schema-properties-show');

		container.empty(); // Clear previous properties every time

		if(schema_type && siteseo_pro.schema[schema_type]){

			if(schema_type === 'BreadcrumbList' || schema_type === 'SearchAction'){
				$('#siteseo-schema-properties-container').hide();
			} else {
				load_schema_properties(schema_type);
				$('#siteseo-schema-properties-container').show();
			}

		} else{
			$('#siteseo-schema-properties-container').hide();
		}
	});
	
	function load_schema_properties(schema_type, existing_properties){
		let properties = existing_properties && Object.keys(existing_properties).length ? existing_properties : siteseo_pro.schema[schema_type];

		let container = $('#siteseo-schema-properties-show');
		container.empty();

		function process_properties(propObj, parent_key = '', depth = 0) {
			$.each(propObj, function(label, variable) {
				let full_key = parent_key ? `${parent_key}[${label}]` : label;

				if(typeof variable === 'object' && !Array.isArray(variable)){
					// nested object (sub-field group)
					let group_row = `
						<tr class="schema-property-row">
							<th scope="row" colspan="2">
								<strong>${label}</strong>
							</th>
						</tr>`;
					container.append(group_row);

					// Process nested properties
					process_properties(variable, full_key, depth + 1);
				} else {
					// This is a regular property
					let value = variable;

					// Try to get existing value from nested structure
					if(existing_properties){
						let keys = full_key.split('[').map(k => k.replace(']', ''));
						let current = existing_properties;

						for(let i = 0; i < keys.length; i++){
							if(current && current[keys[i]] !== undefined){
								current = current[keys[i]];
							} else {
								current = '';
								break;
							}
						}

						if(current !== undefined && current !== ''){
							value = current;
						} else if(existing_properties[label] !== undefined){
							// Fallback to flat structure
							value = existing_properties[label];
						}
					}

					let row = `
						<tr class="schema-property-row">
							<th scope="row">${label}</th>
							<td class="wrap-tags">
								<input class="siteseo_add_properties" value="${value}" type="text" name="schema_properties${parent_key ? '[' + parent_key + ']' : ''}[${label}]">
								<p class="description">Type # to view variables suggestions</p>
							</td>
						</tr>`;
					
					container.append(row);
				}
			});
		}

		// Start processing from the root properties
		process_properties(properties);
		
		// Keyup event
		bind_property_keyup();
	}
	
	function bind_property_keyup(){
		$('.siteseo_add_properties').off('keyup').on('keyup', function(e){
			e.preventDefault();
			e.stopPropagation();

			let $this = $(this),
			wrapper = $this.closest('.wrap-tags');
			val = $this.val(),
			suggestion_box = $('.siteseo-suggestions-wrapper .siteseo-suggetion').first().clone(true);
			
			wrapper.find('.siteseo-suggetion').remove();
			if(val.includes('#')){
				$this.after(suggestion_box);
				suggestion_box.show();
			} else{
				wrapper.find('.siteseo-suggetion').hide();
			}

		});
	}
	
	function save_schema(){
		let spinner = $('.save-schema-btn').next('.spinner');
		
		if(spinner.length){
			spinner.addClass('is-active');
		}
		
		$.ajax({
			url: siteseo_pro.ajax_url,
			type: 'POST',
			data: {
				action: 'siteseo_pro_save_schema',
				schema_data: $('#siteseo-schema-form').find(':input').serialize(),
				nonce: siteseo_pro.nonce
			},
			success: function(response){
				
				if(spinner.length){
					spinner.removeClass('is-active');
				}

				if(response.success){
					$('#siteseo-auto-schema-modal').dialog("close");
					
					let schema = response.data; // {id, name, type}
					$('p:contains("No schemas added yet.")').remove();

					// Ensure table exists
					let $table = $('.siteseo-history-table');
					if(!$table.length){
						let table_html = `
							<h3>Manage Schema</h3>
							<table class="wp-list-table widefat fixed striped siteseo-history-table">
								<thead>
									<tr>
										<th>Schema Name</th>
										<th>Schema Type</th>
										<th>Actions</th>
									</tr>
								</thead>
								<tbody></tbody>
							</table>`;
						$('#siteseo-auto-schema').after(table_html);
						
						$table = $('.siteseo-history-table');
					}

					// Add row data
					let $tbody = $table.find('tbody'),
					$existing_row = $tbody.find(`tr[data-id="${schema.id}"]`);

					let row_html = `
							<tr data-id="${schema.id}">
							<td>${schema.name}</td>
							<td>${schema.type}</td>
							<td>
								<span class="siteseo-action siteseo-edit-schema">
									<span class="dashicons dashicons-edit"></span>
									<label>Edit</label>
								</span>
								<span class="siteseo-action siteseo-delete-schema">
									<span class="dashicons dashicons-trash"></span>
									<label>Delete</label>
								</span>
								<span class="spinner"></span>
							</td>
						</tr>`;

					if($existing_row.length){
						$existing_row.replaceWith(row_html);
					} else{
						$tbody.append(row_html);
					}
					
				} else{
					alert('Error saving schema: ' + response.data);
				}
			},
			error: function(xhr, status, error){
				alert('AJAX error: ' + error);
			}
		});
	}
	
	// delete schema ajax
	$('#tab_auto_schema').on('click', '.siteseo-delete-schema', function(e){
        e.preventDefault();
        if(!confirm('Are you sure you want to delete this schema?')) return;
        
        let id = $(this).closest('tr').data('id'),
        spinner = $(this).next();
		
        if(spinner.length){
          spinner.addClass('is-active');
        }
		
        $.ajax({
            url: siteseo_pro.ajax_url,
            type: 'POST',
            data: {
                action: 'siteseo_pro_delete_schema',
                id: id,
                nonce: siteseo_pro.nonce
            },
            success: function(response){
				
                if(spinner.length){
                  spinner.removeClass('is-active');
                }
				
                if(response.success){
                    $('tr[data-id="' + id + '"]').remove();
                    if ($('#siteseo-schema-list tr').length === 0){
                        $('#siteseo-schema-list').html('<tr><td colspan="3">No schemas found.</td></tr>');
                    }
                }
            }
        });
    });
	
	// Edit schema -
	$('#tab_auto_schema').on('click', '.siteseo-edit-schema', function(e){
		e.preventDefault();
		
		let id = $(this).closest('tr').data('id'),
		spinner = $('#siteseo-auto-schema-modal .siteseo-modal-loads-data .spinner'),
		loading = $('#siteseo-auto-schema-modal .siteseo-modal-loads-data').show();
		loader_text = $('.siteseo-modal-loads-data .siteseo-loading-text');
		
		spinner.addClass('is-active');
		loader_text.text('Loading schema data, please wait...');
		
		$('#siteseo-auto-schema-modal').dialog("option", "title", "Edit Schema");
		$('#siteseo-auto-schema-modal').dialog("open");
		$('#siteseo-schema-form').hide();
		
		$.ajax({
			url: siteseo_pro.ajax_url,
			type: 'POST',
			data: {
				action: 'siteseo_pro_get_schema',
				id: id,
				nonce: siteseo_pro.nonce
			},
			success: function(response){
				spinner.removeClass('is-active');
				loading.hide();
        
				if(response.success){
					
					$('#siteseo-schema-form').show();
					let schema = response.data;
					
					// Fill the form with schema data
					$('input[name="schema_id"]').val(schema.id);
					$('input[name="schema_name"]').val(schema.name);
					$('select[name="schema_type"]').val(schema.type).trigger('change');

					// Set display rules
					if(schema.display_on && schema.display_on.length){
						let $first = $('select[name="display_on[]"]').first();
						
						// Clear existing rules first
						let $container = $first.closest('.siteseo-schema-rule-container');
						$container.empty().append($first);
						
						// Add all rules
						for(let i = 0; i < schema.display_on.length; i++){
							let rule = schema.display_on[i];
							let value, targets;
							
							// Check if it's a specific target object or string
							if(typeof rule === 'object' && rule.type === 'specific_targets'){
								value = 'specific_targets';
								targets = rule.targets;
							} else{
								value = rule;
								targets = '';
							}
							
							let $select;
							if(i === 0){
								$select = $first;
								$select.val(value);
							} else{
								$select = $first.clone().val(value);
								let $wrapper = $('<div class="siteseo-schema-rule" style="margin-top:5px; display:flex; align-items:center; gap:5px;"></div>');
								$wrapper.append($select);
								$wrapper.append('<span title="Delete Rule" class="dashicons dashicons-trash siteseo-remove-schema-rule"></span>');
								$container.append($wrapper);
							}
							
							// Add specific targets input if needed
							if(value === 'specific_targets'){
								let $input = $('<input type="text" name="specific_targets[]" class="siteseo-specific-input" placeholder="Enter specific IDs or URLs" style="margin-top:5px; width:100%;" value="' + targets + '">');
								$select.after($input);
							}
						}
					}

					if(schema.display_not_on && schema.display_not_on.length){
						let $first = $('select[name="display_not_on[]"]').first();
						
						// Clear existing rules first
						let $container = $first.closest('.siteseo-schema-rule-container');
						$container.empty().append($first);
						
						// Add all rules
						for(let i = 0; i < schema.display_not_on.length; i++){
							let rule = schema.display_not_on[i];
							let value, targets;
							
							// Check if it's a specific target object or string
							if(typeof rule === 'object' && rule.type === 'specific_targets'){
								value = 'specific_targets';
								targets = rule.targets;
							} else{
								value = rule;
								targets = '';
							}
							
							let $select;
							if(i === 0){
								$select = $first;
								$select.val(value);
							} else{
								$select = $first.clone().val(value);
								let $wrapper = $('<div class="siteseo-schema-rule" style="margin-top:5px; display:flex; align-items:center; gap:5px;"></div>');
								$wrapper.append($select);
								$wrapper.append('<span title="Delete Rule" class="dashicons dashicons-trash siteseo-remove-schema-rule"></span>');
								$container.append($wrapper);
							}
							
							// Add specific targets input if needed
							if(value === 'specific_targets'){
								let $input = $('<input type="text" name="specific_targets_not[]" class="siteseo-specific-input" placeholder="Enter specific IDs or URLs" style="margin-top:5px; width:100%;" value="' + targets + '">');
								$select.after($input);
							}
						}
					}
					
					// Load properties
					load_schema_properties(schema.type, schema.properties);
				}
			}
		});
	});
	
	function bind_remove_rule(){
		$('#siteseo-auto-schema-modal').off('click', '.siteseo-remove-schema-rule').on('click', '.siteseo-remove-schema-rule', function(e){
			e.preventDefault();
			$(this).closest('.siteseo-schema-rule').remove();
		});
	}
	
	// Show input when "specific_targets" selected
	function bind_specific_targets_change(){
		
		$('#siteseo-auto-schema-modal').off('change', 'select[name="display_on[]"], select[name="display_not_on[]"]').on('change', 'select[name="display_on[]"], select[name="display_not_on[]"]', function(){
			let $select = $(this),
			value = $select.val(),
			is_display_on = $select.attr('name') === 'display_on[]',
			input_name = is_display_on ? 'specific_targets[]' : 'specific_targets_not[]';
			
			$select.siblings('.siteseo-specific-input').remove();

			if(value === 'specific_targets'){
				let $input = $('<input type="text" name="' + input_name + '" class="siteseo-specific-input" placeholder="Enter specific IDs or URLs" style="margin-top:5px; width:100%;">');
				$select.after($input);
			}
		});
	}

});

async function siteseo_pagespeed_request(url, is_mobile = false){
	jQuery.ajax({
		url: siteseo_pro.ajax_url,
		type: 'POST',
		data: {
			action: 'siteseo_pro_get_pagespeed_insights',
			is_mobile : is_mobile,
			test_url : url,
			nonce: siteseo_pro.nonce
		},
		success: function(response){
			if(!response.success){
				alert(response.data ?? 'Something went wrong');
				return;
			}

			if(siteseo_pro.pagespeed_response){
				//spinner.removeClass('is-active');
				location.reload(true);
				return;
			}

			siteseo_pro['pagespeed_response'] = true;
		}
	});	

	
}