var wpLayoutWidgetForm;

(function($) 
{
		wpLayoutWidgetForm = 
		{
						resetForm: function()
						{
							var form = $('#sidebar_form');
							
			   				$('#submitButton',form).val(objectL10n.Add);
			   				$('.ajax-feedback').css('visibility', 'hidden');
			   				
			   				//Resetting form
			   				$('.resetable:input',form).val('').removeAttr('checked').removeAttr('selected');
			   				
			   				//Hide Advance Options
			   				if($('#add_new_advance').is(":visible"))
			   				{
			   					$('a#advance-options').html(objectL10n.HideAdvanceOptions);
			   					$('#add_new_advance').slideToggle('fast');
			   				}							
						},
						
						init : function() 
						{
										//Show Hidden Form
										$('a#advance-options').click(function()
										{
											var a_link = $(this);
											$('#add_new_advance').slideToggle('fast', function() {
													if($(this).is(":visible"))
													{
														a_link.html(objectL10n.HideAdvanceOptions);
													}else{
														a_link.html(objectL10n.AdvanceOptions);
													}
											});
											
											return false;
										});
										
										//On Form Submission
										$('#sidebar_form').submit(function()
										{
											var form = $(this);
			
											var offset = $(this).offset();
			
											$('.ajax-feedback').css('left', form.left + 10);
											$('.ajax-feedback').css('top', form.top);
											$('.ajax-feedback').css('visibility', 'visible');		

											$.post(ajaxurl, form.serialize(),
													 function(data)
													 {
																	if((data.success) && (data.success.length > 0))
																	{
																		$('#message').empty();
																		$.each(data.success, function(index, value) 
																		{ 
																			$('#message').append('<p>' + value + '</p>');
																		});
																		
																		$('#message').fadeIn();
																		
																		setTimeout(function(){
																			$('#message').fadeOut();
																		}, 5000);
																		
																		//Appending New Row
																		var id = $('#id').val();
																		if(!id || id.length == 0)
																		{
																			var newLi = $('#widget_list li:last-child').clone();
																			$('h3 a span', newLi).html(data.data.name);
																			$('h3 a', newLi).attr('rel',data.data.id);
																			
																			$('#widget_list').append(newLi);
																		}
																	}
																	
																	wpLayoutWidgetForm.resetForm();
													   				
													 }, "json");
											
											return false;
										});
										
										//Enable Sorting
										if($( "#widget_list ul").hasClass('sortable'))
										{
												$( "#widget_list ul" ).sortable({
													placeholder: 'widget-theme-placeholder',
													items: '> li',
													handle: '> .drag-widget',
													cursor: 'move',
													distance: 2,
													containment: 'document',											
													stop: function(e,ui)
													{
														 var postData = {};
														 postData.action = $('#action').val();
														  postData._wpnonce_layout_manager_admin = $('#_wpnonce_layout_manager_admin').val();
														 postData.sort = true;
														 postData.ids = [];
														 
														 $( "#widget_list ul li" ).each(function()
														 {
															 postData.ids.push($('h3 a', $(this)).attr('rel'));
														 });
														
														 
														 $.post(ajaxurl, postData,
														 function(data)
														 {
															 	
														 });
														 
													}
												});
										}
										
										//On Select
										$('a.selectable').live('click',function()
										{
												var win = window.dialogArguments || opener || parent || top;
												var title = $(this).text();
												
											
												 var postData = {};
												 postData.action = 'layout_manager_blockitem_arguments_save';
												  postData._wpnonce_layout_manager_admin = $('#_wpnonce_layout_manager_admin').val();
												 postData.update = true;
												 
												 postData.runtime_id = $('#runtime_id').val();

												 postData.args = {};
												 postData.args.id = $(this).attr('rel');
												 postData.args.title = title;
													 
												 $.post(ajaxurl, postData,
												 function(data)
												 {
													 win.send_to_layout(title);
														
												 }, "json");													
												
												return false;
										});										
										
										//On Delete
										$('#widget_list li .options a.delete').live('click',function()
										{
											 		var li = $(this).closest('li');
											 		li.append($('#delete_confirm'));
											 		$('#delete_confirm').show();
											 		
											 		$('#delete_confirm').animate({
											 			'left' : '0px'
													}, 500);
											 		
											 		return false;
										});
										
										$('#confirm_no').click(function()
										{
									 		var li = $(this).closest('li');
			
									 		$('#delete_confirm').animate({
									 			'left' : '-700px'
											}, 500);
									 		
									 		return false;
										});
										
										$('#confirm_yes').click(function()
										{
											 		var li = $(this).closest('li');
											 		$('#sidebar_form').append($('#delete_confirm'));
											 		$('#delete_confirm').css('left','-700px');
											 		$('#delete_confirm').hide();
			
											 		li.fadeOut('fast',function()
											 		{
											 			$(this).remove();
											 		});
											 		
											 		 //Reset Form
											 		wpLayoutWidgetForm.resetForm();
											 		
													 var postData = {};
													 postData.action = $('#action').val();
													 postData._wpnonce_layout_manager_admin = $('#_wpnonce_layout_manager_admin').val();
													 postData.delete = true;
													 postData.id = $('h3 a', li).attr('rel');
														 
													 $.post(ajaxurl, postData,
													 function(data)
													 {
															if((data.success) && (data.success.length > 0))
															{
																$('#message').empty();
																$.each(data.success, function(index, value) 
																{ 
																	$('#message').append('<p>' + value + '</p>');
																});
																
																$('#message').fadeIn();
																
																setTimeout(function(){
																	$('#message').fadeOut();
																}, 5000);
															}
															
													 }, "json");			
													
													return false;
										});		
										
										
										//Modify existing widget
										$('#widget_list li a.configure').live('click',function()
										{
											var li = $(this).closest('li');
											var offset = $(this).offset();
			
											$('.ajax-feedback').css('left', offset.left + 10 + $(this).width());
											$('.ajax-feedback').css('top', offset.top);
											$('.ajax-feedback').css('visibility', 'visible');
											
											 var postData = {};
											 postData.action = $('#action').val();
											  postData._wpnonce_layout_manager_admin = $('#_wpnonce_layout_manager_admin').val();
											 postData.get = true;
											 postData.id = $('h3 a', li).attr('rel')
												 
											 $.post(ajaxurl, postData,
											 function(data)
											 {
											   				for(k in data)
											   				{
											   					$("#sidebar_form :input[name='" + k + "']").val(data[k]);
											   				}
											   				
											   				$('#sidebar_form #submitButton').val(objectL10n.Edit);
											   				$("#sidebar_form :input[name='name']").focus();
											   				
											   				$('.ajax-feedback').css('visibility', 'hidden');
											 }, "json");			
											
											return false;
										});
										
										//Setup default values for dynamic sidebar widgets
										$('a#setup_default_markup').click(function()
										{
											$('#before_widget').val('<aside id="%1$s" class="widget %2$s">');
											$('#after_widget').val('</aside>');
											$('#before_title').val('<h3 class="widgettitle">');
											$('#after_title').val('</h3>');
											
											return false;
										});	
										
										
										//Select widget);
										$('input#select').click(function(e)
										{
											 			e.preventDefault();
											 			
											 			var win = window.dialogArguments || opener || parent || top;
											 			var wordpress_widget_id  = $('#wordpress_widget_id').val();
											 			var title = $("#wordpress_widget_id option[value='" + wordpress_widget_id + "']").text();
														 
														if((wordpress_widget_id) && (title))
														{
															 title = title.split(' :: ');
															 title = title[1];
															 
															 //Saving widget into layout
															 var postData = {};
															 postData.action = 'layout_manager_blockitem_arguments_save';
															  postData._wpnonce_layout_manager_admin = $('#_wpnonce_layout_manager_admin').val();
															 postData.update = true;
															 
															 postData.runtime_id = $('#runtime_id').val();
			
															 postData.args = {};
															 postData.args.id = wordpress_widget_id;
															 postData.args.title = title;
																 
															 $.post(ajaxurl, postData,
															 function(data)
															 {
																 win.send_to_layout(title);
																	
															 }, "json");												
															
																								
														}
														
														return false;
											
										});
										
										//End of Select Widget
										
										//Widget Modification
										$('#widget_link_modify').click(function(e)
										{
												var wordpress_widget_id  = $('#wordpress_widget_id').val();
												if(wordpress_widget_id)
												{
													$('#redirect_wordpress_widget_form').submit();
												}
											
												return false;
										});
										
										
										$('.widget-control-save').click(function(e)
										{
											e.preventDefault();
											
											//Showing Ajax
											var offset = $(this).offset();
											$('.ajax-feedback').css('left', offset.left - 5 - $(this).width());
											$('.ajax-feedback').css('top', offset.top + ($(this).height() / 2));
											$('.ajax-feedback').css('visibility', 'visible');											
											
											var formParams = $('#callback_modify_wordpress_widget_form').serializeArray();
											formParams.push({"name" : "action", "value" : "layout_manager_blockitem_widget_save"});
											formParams.push({"name" : "_wpnonce_layout_manager_admin", "value" : $('#_wpnonce_layout_manager_admin').val()});
											formParams.push({"name" : "runtime_id", "value" : $('#runtime_id').val()});
											
											 $.post(ajaxurl, formParams,
													 function(data)
													 {
												 			$('.ajax-feedback').css('visibility', 'hidden');
													 }, "json");	
											
											return false;
										});
										
										$('.widget-control-remove').click(function(e)
										{
											e.preventDefault();
											
											if($('#wordpress_widget_id option').length > 1)
											{
												//Ajax indication
												var offset = $(this).offset();
												$('.ajax-feedback').css('left', offset.left + 5 + $(this).width());
												$('.ajax-feedback').css('top', offset.top + ($(this).height() / 2));
												$('.ajax-feedback').css('visibility', 'visible');												
												
												var formParams = $('#callback_modify_wordpress_widget_form').serializeArray();
												formParams.push({"name" : "action", "value" : "save-widget"});
												formParams.push({"name" : "savewidgets", "value" : $('#savewidgets').val()});	
												formParams.push({"name" : "delete_widget", "value" : 1});	
												
												 		$.post(ajaxurl, formParams,
														 function(data)
														 {
													 			$('.ajax-feedback').css('visibility', 'hidden');
													 			
													 			var removed_widget_id =  $('#callback_modify_wordpress_widget_form input[name="widget-id"]').val();
													 			$("#wordpress_widget_id option[value='" + removed_widget_id + "']").remove();
													 			
													 			//Moving user to new widget
													 			var new_widget_id = $("#wordpress_widget_id option:first-child").attr('value');
													 			console.log(new_widget_id);
													 			$('#wordpress_widget_id').val(new_widget_id);
													 			
													 			$('#redirect_wordpress_widget_form').submit();
													 			
														 });													
											}else{
												alert("You cannot remove all instances of widget.")
											}
											
											return false;
										});		
										
										//Close widget window
										$('.widget-control-close').click(function(e)
										{
													e.preventDefault();	
													var win = window.dialogArguments || opener || parent || top;
													
													if(win && (typeof win.close_popup == "function"))
													{
														win.close_popup();
													}else{
														window.close();
													}
													
													return false;
										});
										
										//Adding new widget
										$('#widget_link_add').click(function(e)
										{
											e.preventDefault();
											
											if($('#widget_add_new_form').length > 0)
											{
														//Showing Ajax
														var offset = $(this).offset();
														$('.ajax-feedback').css('left', offset.left - 5 - $(this).width());
														$('.ajax-feedback').css('top', offset.top + ($(this).height() / 2));
														$('.ajax-feedback').css('visibility', 'visible');												
														
														var formParams = $('#widget_add_new_form').serializeArray();
														
														 $.post(ajaxurl, formParams,
																 function(data)
																 {
															 			$('.ajax-feedback').css('visibility', 'hidden');
															 			
															 			//Moving user to new widget
															 			var new_widget_id = $("#widget_add_new_form input[name='id_base']").val() + '-' + $("#widget_add_new_form input[name='multi_number']").val();
															 			$('#wordpress_widget_id').append('<option value="' + new_widget_id + '">new widget</option>');
															 			$('#wordpress_widget_id').val(new_widget_id);
															 			
															 			$('#redirect_wordpress_widget_form').submit();
															 			
																 }, "json");												
											}
	
											
											return false;
										});												
										
										
										
										//Shortcode
										$('#shortcode_save').click(function()
										{
											 var win = window.dialogArguments || opener || parent || top;
											 var title = $('#shortcode_title').val();
											 var shortcode_text = $('#shortcode_text').val();
											 
											 if(shortcode_text.length > 0)
											 {
														 //Saving widget into layout
														 var postData = {};
														 postData.action = 'layout_manager_blockitem_arguments_save';
														  postData._wpnonce_layout_manager_admin = $('#_wpnonce_layout_manager_admin').val();
														 postData.update = true;
														 postData.title = title; 
														 postData.runtime_id = $('#runtime_id').val();
			
														 postData.args = {};
														 postData.args.title = title;
														 postData.args.text = shortcode_text;
															 
														 $.post(ajaxurl, postData,
														 function(data)
														 {
															 win.send_to_layout(title);
																
														 }, "json");	
											 }
											 
											return false;
										});
										
						}
		}
		
		$(document).ready(function($)
		{ 
						wpLayoutWidgetForm.init(); 
		});		
		
})(jQuery);