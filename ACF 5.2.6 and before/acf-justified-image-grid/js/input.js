(function($){
	
	// comon
	acf.pro = acf.model.extend({
		
		actions: {
			'refresh': 	'refresh',
		},
		
		filters: {
			'get_fields' : 'get_fields',
		},
		
		get_fields : function( $fields ){
			
			// remove clone fields
			$fields = $fields.not('.acf-clone .acf-field');
			
			// return
			return $fields;
		
		},
		
		
		/*
		*  refresh
		*
		*  This function will run when acf detects a refresh is needed on the UI
		*  Most commonly after ready / conditional logic change
		*
		*  @type	function
		*  @date	10/11/2014
		*  @since	5.0.9
		*
		*  @param	n/a
		*  @return	n/a
		*/
		
		refresh: function(){
			
			// reference
			var self = this;
			
			
			// loop over all table layouts
			$('.acf-input-table.table-layout').each(function(){
				
				// vars
				var $table = $(this);
				
				
				// loop over th
				$table.find('> thead th.acf-th').each(function(){
					
					// vars
					var $th = $(this),
						$td = $table.find('> tbody > tr > td[data-key="' + $th.attr('data-key') + '"]');
					
					
					// clear class
					$td.removeClass('appear-empty');
					$th.removeClass('hidden-by-conditional-logic');
					
					
					// remove clone if needed
					if( $td.length > 1 ) {
						
						$td = $td.not(':last');
						
					}
					
					
					// add classes
					if( $td.not('.hidden-by-conditional-logic').length == 0 ) {
						
						$th.addClass('hidden-by-conditional-logic');
						
					} else {
						
						$td.addClass('appear-empty');
						
					}
					
				});
				
				
				// render table widths
				self.render_table( $table );
				
			});
			
		},
		
		render_table : function( $table ){
			
			//console.log( 'render_table %o', $table);
			// bail early if table is row layout
			if( $table.hasClass('row-layout') ) {
			
				return;
				
			}
			
			
			// vars
			var $th = $table.find('> thead > tr > th'),
				available_width = 100,
				count = 0;
			
			
			// accomodate for order / remove
			if( $th.filter('.order').exists() ) {
				
				available_width = 93;
				
			}
			
			
			// clear widths
			$th.removeAttr('width');
			
			
			// update $th
			$th = $th.not('.order, .remove, .hidden-by-conditional-logic');
				
			
			// set custom widths first
			$th.filter('[data-width]').each(function(){
				
				// bail early if hit limit
				if( (count+1) == $th.length ) {
					
					return false;
					
				}
				
				
				// increase counter
				count++;
				
				
				// vars
				var width = parseInt( $(this).attr('data-width') );
				
				
				// remove from available
				available_width -= width;
				
				
				// set width
				$(this).attr('width', width + '%');
				
			});
			
			
			// set custom widths first
			$th.not('[data-width]').each(function(){
				
				// bail early if hit limit
				if( (count+1) == $th.length ) {
					
					return false;
					
				}
				
				
				// increase counter
				count++;
				
				
				// cal width
				var width = available_width / $th.length;
				
				
				// set width
				$(this).attr('width', width + '%');
				
			});
			
		}
		
	});

})(jQuery);

(function($){
	
	acf.fields.gallery = acf.field.extend({
		
		type: 'justified_image_grid',
		$el: null,
		
		actions: {
			'ready':	'initialize',
			'append':	'initialize',
			'submit':	'close_sidebar'
		},
		
		events: {
			'click .acf-gallery-attachment': 		'select_attachment',
			'click .remove-attachment':				'remove_attachment',
			'click .edit-attachment':				'edit_attachment',
			'click .update-attachment': 			'update_attachment',
			'click .add-attachment':				'add_attachment',
			'click .close-sidebar':					'close_sidebar',
			'change .acf-gallery-side input':		'update_attachment',
			'change .acf-gallery-side textarea':	'update_attachment',
			'change .acf-gallery-side select':		'update_attachment',
			'change .bulk-actions':					'sort'
		},
		
		focus: function(){
			
			this.$el = this.$field.find('.acf-gallery').first();
			this.$values = this.$el.children('.values');
			this.$clones = this.$el.children('.clones');
			
			
			// get options
			this.o = acf.get_data( this.$el );
			
			
			// min / max
			this.o.min = this.o.min || 0;
			this.o.max = this.o.max || 0;
			
		},
		
		get_attachment : function( id ){
			
			// defaults
			id = id || '';
			
			
			// vars
			var selector = '.acf-gallery-attachment';
			
			
			// update selector
			if( id === 'active' ) {
				
				selector += '.active';
				
			} else if( id ) {
				
				selector += '[data-id="' + id  + '"]';
				
			}
			
			
			// return
			return this.$el.find( selector );
			
		},
		
		count : function(){
			
			return this.get_attachment().length;
			
		},

		initialize : function(){
			
			// reference
			var self = this,
				$field = this.$field;
				
					
			// sortable
			this.$el.find('.acf-gallery-attachments').unbind('sortable').sortable({
				
				items					: '.acf-gallery-attachment',
				forceHelperSize			: true,
				forcePlaceholderSize	: true,
				scroll					: true,
				
				start : function (event, ui) {
					
					ui.placeholder.html( ui.item.html() );
					ui.placeholder.removeAttr('style');
								
					acf.do_action('sortstart', ui.item, ui.placeholder);
					
	   			},
	   			
	   			stop : function (event, ui) {
				
					acf.do_action('sortstop', ui.item, ui.placeholder);
					
	   			}
			});
			
			
			// resizable
			this.$el.unbind('resizable').resizable({
				handles : 's',
				minHeight: 200,
				stop: function(event, ui){
					
					acf.update_user_setting('gallery_height', ui.size.height);
				
				}
			});
			
			
			// resize
			$(window).on('resize', function(){
				
				self.doFocus( $field ).resize();
				
			});
			
			
			// render
			this.render();
			
			
			// resize
			this.resize();
					
		},

		render : function() {
			
			// vars
			var $select = this.$el.find('.bulk-actions'),
				$a = this.$el.find('.add-attachment');
			
			
			// disable select
			if( this.o.max > 0 && this.count() >= this.o.max ) {
			
				$a.addClass('disabled');
				
			} else {
			
				$a.removeClass('disabled');
				
			}
			
		},
		
		sort: function( e ){
			
			// vars
			var sort = e.$el.val();
			
			
			// validate
			if( !sort ) {
			
				return;
				
			}
			
			
			// vars
			var data = acf.prepare_for_ajax({
				action		: 'acf/fields/gallery/get_sort_order',
				field_key	: acf.get_field_key(this.$field),
				post_id		: acf.get('post_id'),
				ids			: [],
				sort		: sort
			});
			
			
			// find and add attachment ids
			this.get_attachment().each(function(){
				
				data.ids.push( $(this).attr('data-id') );
				
			});
			
			
			// get results
		    var xhr = $.ajax({
		    	url			: acf.get('ajaxurl'),
				dataType	: 'json',
				type		: 'post',
				cache		: false,
				data		: data,
				context		: this,
				success		: this.sort_success
			});
			
		},
		
		sort_success : function( json ) {
		
			// validate
			if( !acf.is_ajax_success(json) ) {
			
				return;
				
			}
			
			
			// reverse order
			json.data.reverse();
			
			
			// loop over json
			for( i in json.data ) {
				
				var id = json.data[ i ],
					$attachment = this.get_attachment(id);
				
				
				// prepend attachment
				this.$el.find('.acf-gallery-attachments').prepend( $attachment );
				
			};
			
		},
		
		clear_selection : function(){
			
			this.get_attachment().removeClass('active');
			
		},
		
		select_attachment: function( e ){
			
			// vars
			var $attachment = e.$el;
			
			
			// bail early if already active
			if( $attachment.hasClass('active') ) {
				
				return;
				
			}
			
			
			// vars
			var id = $attachment.attr('data-id');
			
			
			// clear selection
			this.clear_selection();
			
			
			// add selection
			$attachment.addClass('active');
			
			
			// fetch
			this.fetch( id );
			
			
			// open sidebar
			this.open_sidebar();
			
		},
		
		open_sidebar : function(){
			
			// add class
			this.$el.addClass('sidebar-open');
			
			
			// hide bulk actions
			this.$el.find('.bulk-actions').hide();
			
			
			// animate
			this.$el.find('.acf-gallery-main').animate({ right : 350 }, 250);
			this.$el.find('.acf-gallery-side').animate({ width : 349 }, 250);
			
		},
		
		close_sidebar : function(){
			
			// remove class
			this.$el.removeClass('sidebar-open');
			
			
			// vars
			var $select = this.$el.find('.bulk-actions');
			
			
			// deselect attachmnet
			this.clear_selection();
			
			
			// disable sidebar
			this.$el.find('.acf-gallery-side').find('input, textarea, select').attr('disabled', 'disabled');
			
			
			// animate
			this.$el.find('.acf-gallery-main').animate({ right : 0 }, 250);
			this.$el.find('.acf-gallery-side').animate({ width : 0 }, 250, function(){
				
				$select.show();
				
				$(this).find('.acf-gallery-side-data').html( '' );
				
			});
			
		},
		
		fetch : function( id ){
			
			// vars
			var data = acf.prepare_for_ajax({
				action		: 'acf/fields/gallery/get_attachment',
				field_key	: acf.get_field_key(this.$field),
				nonce		: acf.get('nonce'),
				post_id		: acf.get('post_id'),
				id			: id
			});
			
			
			// abort XHR if this field is already loading AJAX data
			if( this.$el.data('xhr') ) {
			
				this.$el.data('xhr').abort();
				
			}
			
			
			// get results
		    var xhr = $.ajax({
		    	url			: acf.get('ajaxurl'),
				dataType	: 'html',
				type		: 'post',
				cache		: false,
				data		: data,
				context		: this,
				success		: this.render_fetch
			});
			
			
			// update el data
			this.$el.data('xhr', xhr);
			
		},
		
		render_fetch : function( html ){
			
			// bail early if no html
			if( !html ) {
				
				return;	
				
			}
			
			
			// vars
			var $side = this.$el.find('.acf-gallery-side-data');
			
			
			// render
			$side.html( html );
			
			
			// remove acf form data
			$side.find('.compat-field-acf-form-data').remove();
			
			
			// detach meta tr
			var $tr = $side.find('> .compat-attachment-fields > tbody > tr').detach();
			
			
			// add tr
			$side.find('> table.form-table > tbody').append( $tr );			
			
			
			// remove origional meta table
			$side.find('> .compat-attachment-fields').remove();
			
			
			// setup fields
			acf.do_action('append', $side);
			
		},
		
		update_attachment: function(){
			
			// vars
			var $a = this.$el.find('.update-attachment')
				$form = this.$el.find('.acf-gallery-side-data'),
				data = acf.serialize_form( $form );
				
				
			// validate
			if( $a.attr('disabled') ) {
			
				return false;
				
			}
			
			
			// add attr
			$a.attr('disabled', 'disabled');
			$a.before('<i class="acf-loading"></i>');
			
			
			// append AJAX action		
			data.action = 'acf/fields/gallery/update_attachment';
			
			
			// prepare for ajax
			acf.prepare_for_ajax(data);
			
			
			// ajax
			$.ajax({
				url			: acf.get('ajaxurl'),
				data		: data,
				type		: 'post',
				dataType	: 'json',
				complete	: function( json ){
					
					$a.removeAttr('disabled');
					$a.prev('.acf-loading').remove();
					
				}
			});
			
		},
		
		add : function( a ){
			
			// validate
			if( this.o.max > 0 && this.count() >= this.o.max ) {
			
				acf.validation.add_warning( this.$field, acf._e('gallery', 'max'));
				
				return;
				
			}
			
			
			// vars
			var thumb_url = a.url,
				thumb_class = 'acf-gallery-attachment acf-soh',
				filename = '',
				name = this.$el.find('[data-name="ids"]').attr('name');

			
			// title
			if( a.type !== 'image' && a.filename ) {
				
				filename = '<div class="filename">' + a.filename + '</div>';
				
			}
			
			
			// icon
			if( !thumb_url ) {
				
				thumb_url = a.icon;
				thumb_class += ' is-mime-icon';
				
			}
			
			
			// html
			var html = [
			'<div class="' + thumb_class + '" data-id="' + a.id + '">',
				'<input type="hidden" value="' + a.id + '" name="' + name + '[]">',
				'<div class="margin" title="' + a.filename + '">',
					'<div class="thumbnail">',
						'<img src="' + thumb_url + '">',
					'</div>',
					filename,
				'</div>',
				'<div class="actions acf-soh-target">',
					'<a href="#" class="acf-icon dark remove-attachment" data-id="' + a.id + '">',
						'<i class="acf-sprite-delete"></i>',
					'</a>',
				'</div>',
			'</div>'].join('');
			
			
			// append
			this.$el.find('.acf-gallery-attachments').append( html );
			
			
			// render
			this.render();
			
		},
		
		edit_attachment:function( e ){
			
			// reference
			var self = this;
			
			
			// vars
			var id = acf.get_data(e.$el, 'id');
			
			
			// popup
			var frame = acf.media.popup({
				
				title:		acf._e('image', 'edit'),
				button:		acf._e('image', 'update'),
				mode:		'edit',
				id:			id,
				select:		function( attachment ){
					
					// override url
					if( acf.isset(attachment, 'attributes', 'sizes', self.o.preview_size, 'url') ) {
			    	
				    	attachment.url = attachment.attributes.sizes[ self.o.preview_size ].url;
				    	
			    	}
			    	
			    	
			    	// update image
			    	self.get_attachment(id).find('img').attr( 'src', attachment.url );
				 	
				 	
				 	// render sidebar
					self.fetch( id );
					
				}
			});
						
		},
		
		remove_attachment: function( e ){
			
			// prevent event from triggering click on attachment
			e.stopPropagation();
			
			
			// vars
			var id = acf.get_data(e.$el, 'id');
			
			
			// deselect attachmnet
			this.clear_selection();
			
			
			// update sidebar
			this.close_sidebar();
			
			
			// remove image
			this.get_attachment(id).remove();
			
			
			// render
			this.render();
			
			
		},
		
		render_collection : function( frame ){
			
			var self = this;
			
			
			// Note: Need to find a differen 'on' event. Now that attachments load custom fields, this function can't rely on a timeout. Instead, hook into a render function foreach item
			
			// set timeout for 0, then it will always run last after the add event
			setTimeout(function(){
			
			
				// vars
				var $content	= frame.content.get().$el
					collection	= frame.content.get().collection || null;
					

				
				if( collection ) {
					
					var i = -1;
					
					collection.each(function( item ){
					
						i++;
						
						var $li = $content.find('.attachments > .attachment:eq(' + i + ')');
						
						
						// if image is already inside the gallery, disable it!
						if( self.get_attachment(item.id).exists() ) {
						
							item.off('selection:single');
							$li.addClass('acf-selected');
							
						}
						
					});
					
				}
			
			
			}, 10);

				
		},
		
		add_attachment: function( e ){
			
			// validate
			if( this.o.max > 0 && this.count() >= this.o.max ) {
			
				acf.validation.add_warning( this.$field, acf._e('gallery', 'max'));
				
				return;
				
			}
			
			
			// vars
			var preview_size = this.o.preview_size;
			
			
			// reference
			var self = this;
			
			
			// popup
			var frame = acf.media.popup({
				
				title:		acf._e('gallery', 'select'),
				mode:		'select',
				type:		'',
				field:		acf.get_field_key(this.$field),
				multiple:	'add',
				library:	this.o.library,
				mime_types: this.o.mime_types,
				
				select: function( attachment, i ) {
					
					// vars
					var atts = attachment.attributes;
					
					
					// is image already in gallery?
					if( self.get_attachment(atts.id).exists() ) {
					
						return;
						
					}
					
					//console.log( attachment );
			    	
			    	// vars
			    	var a = {
				    	id:			atts.id,
				    	type:		atts.type,
				    	icon:		atts.icon,
				    	filename:	atts.filename,
				    	url:		''
			    	};
			    	
			    	
			    	// type
			    	if( a.type === 'image' ) {
				    	
				    	a.url = acf.maybe_get(atts, 'sizes', preview_size, 'url') || atts.url;
				    	
			    	} else {
				    	
				    	a.url = acf.maybe_get(atts, 'thumb', 'src') || '';
				    	
				    }
				    
				    
			    	// add file to field
			        self.add( a );
					
				}
			});
			
			
			// modify DOM
			frame.on('content:activate:browse', function(){
				
				self.render_collection( frame );
				
				frame.content.get().collection.on( 'reset add', function(){
				    
					self.render_collection( frame );
				    
			    });
				
			});
			
		},
		
		resize : function(){
			
			// vars
			var min = 100,
				max = 175,
				columns = 4,
				width = this.$el.width();
			
			
			// get width
			for( var i = 0; i < 10; i++ ) {
			
				var w = width/i;
				
				if( min < w && w < max ) {
				
					columns = i;
					break;
					
				}
				
			}
						
			
			// update data
			this.$el.attr('data-columns', columns);
		}
		
	});
	
})(jQuery);
