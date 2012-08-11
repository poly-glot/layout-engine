var wpLayoutWidgets;
var numColumns; //Store the Current Draggable number of columns

var is_saving;
var did_try_save_again;

var current_open_dialog;

(function($) 
{
		wpLayoutWidgets = 
		{
						init : function() 
						{
																	var rem, sidebars = $('div.widgets-sortables'), isRTL = !! ( 'undefined' != typeof isRtl && isRtl ),
																		margin = ( isRtl ? 'marginRight' : 'marginLeft' ), the_id;
															
																	$('#widgets-left').children('.widgets-holder-wrap').children('.sidebar-name').click(function(){
																		var c = $(this).siblings('.widgets-sortables'), p = $(this).parent();
																		
																		if ( !p.hasClass('closed') ) {
																			c.sortable('disable');
																			p.addClass('closed');
																		} else {
																			p.removeClass('closed');
																			c.sortable('enable').sortable('refresh');
																		}
																	});
															
																	$('#widgets-right').children('.widgets-holder-wrap').children('.sidebar-name').click(function() {
																		//$(this).parent().toggleClass('closed');
																	});
															
																	sidebars.each(function(){
																		if ( $(this).parent().hasClass('inactive') )
																			return true;
															
																		var h = 50, H = $(this).children('.widget').length;
																		h = h + parseInt(H * 48, 10);
																		$(this).css( 'minHeight', h + 'px' );
																	});
															
																	$('a.widget-action-configure').live('click', function()
																	{
																		widget = $(this).closest('div.widget');
																		current_open_dialog = widget;
																		
																		var link = $(this).attr('href');
																		link += '&runtime_id=' + $('.widget-runtime-id', widget).val();
																		link += '&pos=' + $(widget).index();
																		link += '&section=' + widget.closest('.widgets-sortables').attr('id');
																		link += '&TB_iframe=1&width=640&height=479';
																		
																		var t = $('.widget-name', widget).val();
																		
																		tb_show(t,link,false);
																		
																		return false;
																	});
																	
																	$('a.widget-action-resize').live('click', function(){
																		
																		widget = $(this).closest('div.widget');
																		
																		numColumns = parseInt(widget.data("columns")) ;
																		if(isNaN(numColumns)) numColumns = 1;
																		
																		widget.removeClass("column-" + numColumns);
																		
																		if($(this).hasClass("widget-action-resize-right"))
																		{
																			numColumns++;
																			if(numColumns > 3) numColumns = 3;	
																			
																		}else{
																			numColumns--;
																			if(numColumns < 1) numColumns = 1;																				
																		}
																		
																		widget.addClass("column-" + numColumns);
																		widget.data("columns", numColumns)
																		
																		//Capture this information for saving into database/options
																		$('.widget-columns', widget).val(numColumns);
																		
																		//Callback
																		var layout_box = widget.parents('.widgets-holder-wrap');
																		wpLayoutWidgets.save(layout_box);	
																		
																		return false;
																		
																	});
															
															
																	sidebars.children('.widget').each(function() {
																		wpLayoutWidgets.appendTitle(this);
																		if ( $('p.widget-error', this).length )
																			$('a.widget-action', this).click();
																	});
															
																	$('#widget-list').children('.widget').draggable({
																		connectToSortable: 'div.widgets-sortables',
																		handle: '> .widget-top > .widget-title',
																		distance: 2,
																		helper: 'clone',
																		zIndex: 5,
																		containment: 'document',
																		start: function(e,ui) {
																			ui.helper.find('div.widget-description').hide();
																			the_id = this.id;
																		},
																		stop: function(e,ui) {
																			if ( rem )
																				$(rem).hide();
																			
																			rem = '';
																		}
																	});
																	
																	//Fix the width overflow during dragging of object
																	$('#widgets-left .widget').hover(function()
																	{
																			$(this).data('actual-width', $(this).width());
																	});
															
																	sidebars.sortable({
																		placeholder: 'widget-placeholder',
																		items: '> .widget',
																		handle: '> .widget-top > .widget-title',
																		cursor: 'move',
																		distance: 2,
																		containment: 'document',
																		activate: function(e,ui) 
																		{
																			numColumns = parseInt(ui.item.data("columns")) ;
																			if(isNaN(numColumns)) numColumns = 1;	
																			
																			$("#widgets-left .widget-placeholder").removeClass('column-1 column-2 column-3');
																			$("#widgets-left .widget-placeholder").addClass("column-" + numColumns);																			
																			
																			var actualWidth = parseInt(ui.item.data('actual-width'));
																			if(!isNaN(actualWidth))
																			{
																				ui.item.css('width', actualWidth);
																			}																		
																		},
																		start: function(e,ui) {
																			
																			ui.item.css({margin:'', 'width':''});																			
																		},
																		stop: function(e,ui) {
																			
																			if ( ui.item.hasClass('ui-draggable') && ui.item.data('draggable') )
																				ui.item.draggable('destroy');
														
																			
																			//Layout Box
																			var layout_box = ui.item.parents('.widgets-holder-wrap');
																			
																			if ( ui.item.hasClass('deleting') ) {
																				ui.item.remove();
																				wpLayoutWidgets.save(layout_box);	
																				return;
																			}																			
																			
																			if(layout_box.length > 0)
																			{
																				//Is it new draggable object from right hand site? assign Runtime ID
																				if(ui.item.hasClass('widget-new'))
																				{
																							$.post( ajaxurl, { action : 'layout_manager_runtime_id' }, function(r)
																							{
																									$('.widget-runtime-id', ui.item).val(r);
																									wpLayoutWidgets.save(layout_box);
																							});
																				}else{
																					
																					//Update
																					wpLayoutWidgets.save(layout_box);
																				}
																				
																			}
																			
																			
																			
																		},
																		change: function(e, ui) 
																		{
																			if(numColumns > 0)
																			{
																					$("#widgets-left .widget-placeholder").removeClass('column-1 column-2 column-3');
																					$("#widgets-left .widget-placeholder").addClass("column-" + numColumns);
																			}
																							
																		},
																		receive: function(e, ui) {
																			var sender = $(ui.sender);
															
																			if ( !$(this).is(':visible') || this.id.indexOf('orphaned_widgets') != -1 )
																				sender.sortable('cancel');
															
																			if ( sender.attr('id').indexOf('orphaned_widgets') != -1 && !sender.children('.widget').length ) {
																				sender.parents('.orphan-sidebar').slideUp(400, function(){ $(this).remove(); });
																			}
																		}
																	}).sortable('option', 'connectWith', 'div.widgets-sortables').parent().filter('.closed').children('.widgets-sortables').sortable('disable');
															
																	$('#available-widgets').droppable({
																		tolerance: 'pointer',
																		accept: function(o){
																			return $(o).parent().attr('id') != 'widget-list';
																		},
																		drop: function(e,ui) {
																			ui.draggable.addClass('deleting');
																			$('#removing-widget').hide().children('span').html('');
																		},
																		over: function(e,ui) {
																			ui.draggable.addClass('deleting');
																			$('div.widget-placeholder').hide();
															
																			if ( ui.draggable.hasClass('ui-sortable-helper') )
																				$('#removing-widget').show().children('span')
																				.html( ui.draggable.find('div.widget-title').children('h4').html() );
																		},
																		out: function(e,ui) {
																			ui.draggable.removeClass('deleting');
																			$('div.widget-placeholder').show();
																			$('#removing-widget').hide().children('span').html('');
																		}
																	});
																	
																	
																	//Color picker support
																	$(document).mousedown( function() {
																		$('.colorPickerDiv').hide();
																	});
																	
																	$('.default-color').on('click',function(e)
																	{
																		var that = $(this).parents('.le_color_picker');
																		$('.link-color', that).val($(this).data('color'));
																		$('.link-color-example', that).css('background-color', $(this).data('color'));			
																		e.preventDefault();
																		return false;
																	});
																	
																	$('.le_color_picker').each(function()
																	{
																		var id = $(this).attr('id');
																		if(id)
																		{
																			var that = $(this);
																			var colorPickerDiv = $('.colorPickerDiv', $(this));
																			
																			//default color
																			$('.link-color-example', that).css('background-color', $('.link-color', that).val());	
																			
																			$('.pickcolor', that).click( function(e) {
																				colorPickerDiv.show();
																				e.preventDefault();
																			});																			
																			
																			var le_farbtastic = $.farbtastic('#' + colorPickerDiv.attr('id'), function(a)
																			{
																				le_farbtastic.setColor(a);
																				$('.link-color', that).val(a);
																				$('.link-color-example', that).css('background-color', a);																		
																			});
																		}
																	});
																	
						},
						
						saveOrder : function(sb) 
						{
							
						},
						
						save : function(layout_box) 
						{
							if(!is_saving)
							{
											if(layout_box && layout_box.length > 0)
											{
												$('.ajax-feedback', layout_box).css('visibility', 'visible');
											}
											
											//Compose PostForm Data
											var layout = $('#layout').val();
											var formData = [];
											formData.push({'name' : 'action', 'value' : $('#action').val() });
											formData.push({'name' : '_wpnonce_layout_manager_admin', 'value': $('#_wpnonce_layout_manager_admin').val() });
											formData.push({'name' : 'layout', 'value' : layout });
											
											
											$('#widgets-left').children().each(function()
											{
														var block = $(this).attr('rel');
														var i = 0;
														$('.widget', $(this)).each(function()
														{
															var name = "data[" + layout + "][" + block + "][" + i + "]";
															
															$(':input', $('.widget-form', $(this))).each(function()
															{
																var field_name = name + "[" + $(this).attr('name') + "]";
																
																formData.push({'name' : field_name, 'value' : $(this).val() });
															});
															
															i++;
														}); //end of widget loop
											});
											
											$.post( ajaxurl, formData, function(r)
											{
															$('.ajax-feedback').css('visibility', 'hidden');
															
															is_saving = false;
															
															//Did user try to do more changes? then try to submit now
															if(did_try_save_again)
															{
																wpLayoutWidgets.save(layout_box);	
																did_try_save_again = false;
															}
											});
											
											
											is_saving = true;								
							}else{
								did_try_save_again = true;
							}
							
						},
						
						appendTitle : function(widget) 
						{
											var title = $('input[id*="-title"]', widget).val() || '';
									
											if ( title )
											{
												title = ': ' + title.replace(/<[^<>]+>/g, '').replace(/</g, '&lt;').replace(/>/g, '&gt;');
												$(widget).children('.widget-top').children('.widget-title').children().children('.in-widget-title').html(title);
											}
						},
					
						resize : function() 
						{
											$('div.widgets-sortables').each(function(){
												if ( $(this).parent().hasClass('inactive') )
													return true;
									
												var h = 50, H = $(this).children('.widget').length;
												h = h + parseInt(H * 48, 10);
												$(this).css( 'minHeight', h + 'px' );
											});
						},
					
						fixLabels : function(widget) 
						{
											widget.children('.widget-inside').find('label').each(function(){
												var f = $(this).attr('for');
												if ( f && f == $('input', this).attr('id') )
													$(this).removeAttr('for');
											});
						},
					
						close : function(widget) 
						{
											widget.children('.widget-inside').slideUp('fast', function(){
												widget.css({'width':'', margin:''});
											});
						}						
		}
		
		$(document).ready(function($)
		{ 
				wpLayoutWidgets.init(); 
		});
		
		window.send_to_layout = function(title) 
		{
			if(current_open_dialog)
			{
				$('.in-widget-title', current_open_dialog).html(title);
				current_open_dialog = null;
			}
			
			tb_remove();
		}
		
		window.close_popup = function()
		{
			current_open_dialog = null;
			tb_remove();
		}

})(jQuery);