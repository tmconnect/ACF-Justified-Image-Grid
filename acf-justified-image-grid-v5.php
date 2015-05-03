<?php

class acf_field_justified_image_grid extends acf_field {
	
	
	/*
	*  __construct
	*
	*  This function will setup the field type data
	*
	*  @type	function
	*  @date	5/03/2014
	*  @since	5.0.0
	*
	*  @param	n/a
	*  @return	n/a
	*/
	
	function __construct() {
		
		/*
		*  name (string) Single word, no spaces. Underscores allowed
		*/
		
		$this->name = 'justified_image_grid';
		
		
		/*
		*  label (string) Multiple words, can include spaces, visible when selecting a field type
		*/
		
		$this->label = __('Justified Image Grid', 'acf-jig');
		
		
		/*
		*  category (string) basic | content | choice | relational | jquery | layout | CUSTOM GROUP NAME
		*/
		
		$this->category = 'content';
		
		
		/*
		*  defaults (array) Array of default settings which are merged into the field object. These are used later in settings
		*/
		
		$this->defaults = array(
			'preview_size'		=> 'thumbnail',
			'library'			=> 'all',
			'min'				=> 0,
			'max'				=> 0,
			'row_height'		=> 120,
			'max_row_height'	=> 0,
			'lastrow'			=> 'nojustify',
			'fixed_height'		=> 'false',
			'show_captions'		=> 'true',
			'margin'			=> 5,
			'border'			=> 0,
			'backcolor'			=> '',
			'randomize'			=> 'false',
			'swipebox'			=> 'yes',
			'image_sizes'		=> '',
		);

		// Settings
		$this->settings = array(
			'justified_version'	=> '3.5.4',
			'justified_css' 	=> plugin_dir_url( __FILE__ ) . 'css/justifiedGallery.css',
			'justified_js'		=> plugin_dir_url( __FILE__ ) . 'js/jquery.justifiedGallery.js',
			'swipebox_css' 		=> plugin_dir_url( __FILE__ ) . 'js/swipebox/css/swipebox.css',
			'swipebox_js'		=> plugin_dir_url( __FILE__ ) . 'js/swipebox/js/jquery.swipebox.min.js'
		);
		
		/*
		*  l10n (array) Array of strings that are used in JavaScript. This allows JS strings to be translated in PHP and loaded via:
		*  var message = acf._e('justified_image_grid', 'error');
		*/
		
		$this->l10n = array(
			'select'		=> __("Add Image to Gallery",'acf'),
			'edit'			=> __("Edit Image",'acf'),
			'update'		=> __("Update Image",'acf'),
			'uploadedTo'	=> __("uploaded to this post",'acf'),
			'max'			=> __("Maximum selection reached",'acf')
		);

		// actions
		add_action('wp_ajax_acf/fields/gallery/get_jig_attachment',				array($this, 'ajax_get_jig_attachment'));
		add_action('wp_ajax_nopriv_acf/fields/gallery/get_jig_attachment',		array($this, 'ajax_get_jig_attachment'));
		
		add_action('wp_ajax_acf/fields/gallery/update_jig_attachment',			array($this, 'ajax_update_jig_attachment'));
		add_action('wp_ajax_nopriv_acf/fields/gallery/update_jig_attachment',	array($this, 'ajax_update_jig_attachment'));
		
		add_action('wp_ajax_acf/fields/gallery/get_jig_sort_order',				array($this, 'ajax_get_jig_sort_order'));
		add_action('wp_ajax_nopriv_acf/fields/gallery/get_jig_sort_order',		array($this, 'ajax_get_jig_sort_order'));

		// Enqueue icons style in the frontend
		add_action( 'wp_enqueue_scripts', array( $this, 'frontend_enqueue' ) );
				
		// do not delete!
		parent::__construct();
		
	}
	
	/**
	 *  frontend_enqueue()
	 *
	 *  @since	1.0.0
	 */
	function frontend_enqueue() {
		// Register Justidied Gallery script and css
		wp_enqueue_style ( 'dhz_justified_css',	$this->settings['justified_css'], array(), '1.0' );
		wp_enqueue_script( 'dhz_justified',		$this->settings['justified_js'], array( 'jquery' ), $this->settings['justified_version'], true );
		// Register Swipebox script and css
		wp_enqueue_style ( 'dhz_swipe_css',		$this->settings['swipebox_css'], array(), '1.3.0.2' );
		wp_enqueue_script( 'dhz_swipe_js',		$this->settings['swipebox_js'], array( 'jquery' ), '1.3.0.2', true );
	}

	/*
	*  ajax_get_jig_attachment
	*
	*  description
	*
	*  @type	function
	*  @date	13/12/2013
	*  @since	5.0.0
	*
	*  @param	$post_id (int)
	*  @return	$post_id (int)
	*/
	
	function ajax_get_jig_attachment() {
	
		// options
		$options = acf_parse_args( $_POST, array(
			'post_id'		=>	0,
			'id'			=>	0,
			'field_key'		=>	'',
			'nonce'			=>	'',
		));
		
		// validate
		if( ! wp_verify_nonce($options['nonce'], 'acf_nonce') ) {			
			die();
		}
		
		if( empty($options['id']) ) {		
			die();
		}		
		
		// load field
		$field = acf_get_field( $options['field_key'] );
		
		if( !$field ) {		
			die();
		}		
		
		// render
		$this->render_jig_attachment( $options['id'], $field );
		die;
		
	}
	
	
	/*
	*  ajax_update_jig_attachment
	*
	*  description
	*
	*  @type	function
	*  @date	13/12/2013
	*  @since	5.0.0
	*
	*  @param	$post_id (int)
	*  @return	$post_id (int)
	*/
	
	function ajax_update_jig_attachment() {
		
		
		// validate
		if( ! wp_verify_nonce($_REQUEST['nonce'], 'acf_nonce') ) {
			wp_send_json_error();
		}		
		
		if( empty($_REQUEST['attachments']) ) {		
			wp_send_json_error();
		}		
		
		foreach( $_REQUEST['attachments'] as $id => $changes ) {			
			if ( ! current_user_can( 'edit_post', $id ) )
				wp_send_json_error();
				
			$post = get_post( $id, ARRAY_A );
		
			if ( 'attachment' != $post['post_type'] )
				wp_send_json_error();
		
			if ( isset( $changes['title'] ) )
				$post['post_title'] = $changes['title'];
		
			if ( isset( $changes['caption'] ) )
				$post['post_excerpt'] = $changes['caption'];
		
			if ( isset( $changes['description'] ) )
				$post['post_content'] = $changes['description'];
		
			if ( isset( $changes['alt'] ) ) {
				$alt = wp_unslash( $changes['alt'] );
				if ( $alt != get_post_meta( $id, '_wp_attachment_image_alt', true ) ) {
					$alt = wp_strip_all_tags( $alt, true );
					update_post_meta( $id, '_wp_attachment_image_alt', wp_slash( $alt ) );
				}
			}			
			
			// save post
			wp_update_post( $post );
			
			// save meta
			acf_save_post( $id );
		}
		
		wp_send_json_success();
			
	}
	
	
	/*
	*  ajax_get_jig_sort_order
	*
	*  description
	*
	*  @type	function
	*  @date	13/12/2013
	*  @since	5.0.0
	*
	*  @param	$post_id (int)
	*  @return	$post_id (int)
	*/
	
	function ajax_get_jig_sort_order() {
		
		// vars
		$r = array();
		$order = 'DESC';
		$args = acf_parse_args( $_POST, array(
			'ids'			=>	0,
			'sort'			=>	'date',
			'field_key'		=>	'',
			'nonce'			=>	'',
		));
		
		// validate
		if( ! wp_verify_nonce($args['nonce'], 'acf_nonce') ) {		
			wp_send_json_error();
		}		
		
		// reverse
		if( $args['sort'] == 'reverse' ) {		
			$ids = array_reverse($args['ids']);
			wp_send_json_success($ids);
		}		
		
		if( $args['sort'] == 'title' ) {			
			$order = 'ASC';
		}		
		
		// find attachments (DISTINCT POSTS)
		$ids = get_posts(array(
			'post_type'		=> 'attachment',
			'numberposts'	=> -1,
			'post_status'	=> 'any',
			'post__in'		=> $args['ids'],
			'order'			=> $order,
			'orderby'		=> $args['sort'],
			'fields'		=> 'ids'		
		));
		
		// success
		if( !empty($ids) ) {		
			wp_send_json_success($ids);
		}
		// failure
		wp_send_json_error();
	}
	
	
	/*
	*  render_jig_attachment
	*
	*  description
	*
	*  @type	function
	*  @date	13/12/2013
	*  @since	5.0.0
	*
	*  @param	$post_id (int)
	*  @return	$post_id (int)
	*/
	
	function render_jig_attachment( $id = 0, $field ) {
		
		// vars
		$attachment = wp_prepare_attachment_for_js( $id );
		$thumb = '';
		$prefix = "attachments[{$id}]";
		$compat = get_compat_media_markup( $id );
		$dimentions = '';
		
		// thumb
		if( isset($attachment['thumb']['src']) ) {			
			// video
			$thumb = $attachment['thumb']['src'];
		} elseif( isset($attachment['sizes']['thumbnail']['url']) ) {			
			// image
			$thumb = $attachment['sizes']['thumbnail']['url'];
		} elseif( $attachment['type'] === 'image' ) {			
			// svg
			$thumb = $attachment['url'];
		} else {			
			// fallback (perhaps attachment does not exist)
			$thumb = $attachment['icon'];
		}
		
		// dimentions
		if( $attachment['type'] === 'audio' ) {			
			$dimentions = __('Length', 'acf') . ': ' . $attachment['fileLength'];
		} elseif( !empty($attachment['width']) ) {			
			$dimentions = $attachment['width'] . ' x ' . $attachment['height'];
		}
		
		if( $attachment['filesizeHumanReadable'] ) {			
			$dimentions .=  ' (' . $attachment['filesizeHumanReadable'] . ')';
		}
		
		?>
		<div class="acf-gallery-side-info acf-cf">
			<img src="<?php echo $thumb; ?>" alt="<?php echo $attachment['alt']; ?>" />
			<p class="filename"><strong><?php echo $attachment['filename']; ?></strong></p>
			<p class="uploaded"><?php echo $attachment['dateFormatted']; ?></p>
			<p class="dimensions"><?php echo $dimentions; ?></p>
			<p class="actions"><a href="#" class="edit-attachment" data-id="<?php echo $id; ?>"><?php _e('Edit', 'acf'); ?></a> <a href="#" class="remove-attachment" data-id="<?php echo $id; ?>"><?php _e('Remove', 'acf'); ?></a></p>
		</div>
		<table class="form-table">
			<tbody>
				<?php 
				
				acf_render_field_wrap(array(
					//'key'		=> "{$field['key']}-title",
					'name'		=> 'title',
					'prefix'	=> $prefix,
					'type'		=> 'text',
					'label'		=> 'Title',
					'value'		=> $attachment['title']
				), 'tr');
				
				acf_render_field_wrap(array(
					//'key'		=> "{$field['key']}-caption",
					'name'		=> 'caption',
					'prefix'	=> $prefix,
					'type'		=> 'textarea',
					'label'		=> 'Caption',
					'value'		=> $attachment['caption']
				), 'tr');
				
				acf_render_field_wrap(array(
					//'key'		=> "{$field['key']}-alt",
					'name'		=> 'alt',
					'prefix'	=> $prefix,
					'type'		=> 'text',
					'label'		=> 'Alt Text',
					'value'		=> $attachment['alt']
				), 'tr');
				
				acf_render_field_wrap(array(
					//'key'		=> "{$field['key']}-description",
					'name'		=> 'description',
					'prefix'	=> $prefix,
					'type'		=> 'textarea',
					'label'		=> 'Description',
					'value'		=> $attachment['description']
				), 'tr');
				
				?>
			</tbody>
		</table>
		<?php echo $compat['item']; ?>
		
		<?php		
	}
	
	
	/*
	*  render_field_settings()
	*
	*  Create extra options for your field. This is rendered when editing a field.
	*  The value of $field['name'] can be used (like bellow) to save extra data to the $field
	*
	*  @type	action
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	$field	- an array holding all the field's data
	*/
	
	function render_field_settings( $field ) {
		
		// min / max
		$field['min'] = empty($field['min']) ? '' : $field['min'];
		$field['max'] = empty($field['max']) ? '' : $field['max'];
		$field['row_height'] = empty($field['row_height']) ? '120' : $field['row_height'];
		$field['max_row_height'] = empty($field['max_row_height']) ? '0' : $field['max_row_height'];
		$field['lastrow'] = empty($field['lastrow']) ? 'nojustify' : $field['lastrow'];
		$field['fixed_height'] = empty($field['fixed_height']) ? 'false' : $field['fixed_height'];
		$field['show_captions'] = empty($field['show_captions']) ? 'true' : $field['show_captions'];
		$field['margin'] = empty($field['margin']) ? '5' : $field['margin'];
		$field['border'] = empty($field['border']) ? '0' : $field['border'];
		$field['backcolor'] = empty($field['backcolor']) ? '' : $field['backcolor'];
		$field['randomize'] = empty($field['randomize']) ? 'false' : $field['randomize'];
		$field['swipebox'] = empty($field['swipebox']) ? 'true' : $field['swipebox'];
		
		// min
		acf_render_field_setting( $field, array(
			'label'			=> __('Minimum Selection','acf'),
			'instructions'	=> '',
			'type'			=> 'number',
			'name'			=> 'min',
			'placeholder'	=> '0',
		));
		
		// max
		acf_render_field_setting( $field, array(
			'label'			=> __('Maximum Selection','acf'),
			'instructions'	=> '',
			'type'			=> 'number',
			'name'			=> 'max',
			'placeholder'	=> '0',
		));
		
		// preview_size
		acf_render_field_setting( $field, array(
			'label'			=> __('Preview Size','acf'),
			'instructions'	=> __('Shown when entering data','acf'),
			'type'			=> 'select',
			'name'			=> 'preview_size',
			'choices'		=> acf_get_image_sizes()
		));
		
		// row_height
		acf_render_field_setting( $field, array(
			'label'			=> __('Row Height','acf-jig'),
			'instructions'	=> __('The approximately height of rows in pixel.','acf-jig'),
			'type'			=> 'number',
			'name'			=> 'row_height',
		));
		
		// max_row_height
		acf_render_field_setting( $field, array(
			'label'			=> __('Max. Row Height','acf-jig'),
			'instructions'	=> __('The maximum row height in pixel. Negative value to haven\'t limits. Zero to have a limit of 1.5 x rowHeight.','acf-jig'),
			'type'			=> 'number',
			'name'			=> 'max_row_height',
		));
		
		// lastrow
		acf_render_field_setting( $field, array(
			'label'			=> __('Last Row','acf-jig'),
			'instructions'	=> __('Decide if you want to justify the last row or not, or to hide the row if it can\'t be justified','acf-jig'),
			'type'			=> 'radio',
			'name'			=> 'lastrow',
			'layout'		=> 'horizontal',
			'choices' 		=> array(
				'justify'		=> __('Justify', 'acf'),
				'nojustify'		=> __('No Justify', 'acf'),
				'hide'			=> __('Hide', 'acf')
			)
		));
		
		// fixed_height
		acf_render_field_setting( $field, array(
			'label'			=> __('Fixed height Rows','acf-jig'),
			'instructions'	=> __('Decide if you want to have a fixed height. This mean that all the rows will be exactly with the specified rowHeight. Depending on aspect ratio the images could be cropped.','acf-jig'),
			'type'			=> 'radio',
			'name'			=> 'fixed_height',
			'layout'		=> 'horizontal',
			'choices' 		=> array(
				'true'			=> __('Yes', 'acf'),
				'false'			=> __('No', 'acf')
			)
		));
		
		// show_captions
		acf_render_field_setting( $field, array(
			'label'			=> __('Show Captions','acf-jig'),
			'instructions'	=> __('Show image captions on hover.','acf-jig'),
			'type'			=> 'radio',
			'name'			=> 'show_captions',
			'layout'		=> 'horizontal',
			'choices' 		=> array(
				'true'			=> __('Yes', 'acf'),
				'false'			=> __('No', 'acf')
			)
		));
		
		// margin
		acf_render_field_setting( $field, array(
			'label'			=> __('Margin Width','acf-jig'),
			'instructions'	=> __('Set the margin size for the grid','acf-jig'),
			'type'			=> 'number',
			'name'			=> 'margin',
			'placeholder'	=> '0',
		));
		
		// border
		acf_render_field_setting( $field, array(
			'label'			=> __('Border Width','acf-jig'),
			'instructions'	=> __('Set the border size of the grid. With a negative value the border will be the same as the margins.','acf-jig'),
			'type'			=> 'number',
			'name'			=> 'border',
			'placeholder'	=> '0',
		));
		
		// backcolor
		acf_render_field_setting( $field, array(
			'label'			=> __('Margin & Border Color','acf'),
			'instructions'	=> __('Set the color for the margin and the border. Leave blank to make it transparent.','acf-jig'),
			'type'			=> 'text',
			'name'			=> 'backcolor',
			'placeholder'	=> '#FFFFFF'
		));
		
		// randomize
		acf_render_field_setting( $field, array(
			'label'			=> __('Randomize Images','acf-jig'),
			'instructions'	=> __('Automatically randomize or not the order of photos.','acf-jig'),
			'type'			=> 'radio',
			'name'			=> 'randomize',
			'layout'		=> 'horizontal',
			'choices' 		=> array(
				'true'			=> __('Yes', 'acf'),
				'false'			=> __('No', 'acf')
			)
		));
		
		// image_sizes
		acf_render_field_setting( $field, array(
			'label'			=> __('Image Sizes','acf-jig'),
			'instructions'	=> __('Select the image sizes used by the grid. You will only see image size that defined with a width of \'9999 Pixel\'. Make sure that the correct sizes are defined. ','acf-jig'),
			'type'			=> 'checkbox',
			'name'			=> 'image_sizes',
			'choices'	=> get_image_sizes()
		));
		
		// swipebox
		acf_render_field_setting( $field, array(
			'label'			=> __('Swipebox','acf-jig'),
			'instructions'	=> __('Do you want to use the Swipebox - a touchable jQuery lightbox?','acf-jig'),
			'type'			=> 'radio',
			'name'			=> 'swipebox',
			'layout'		=> 'horizontal',
			'choices' 		=> array(
				'yes'			=> __('Yes', 'acf'),
				'no'			=> __('No', 'acf')
			)
		));
		
		// library
		acf_render_field_setting( $field, array(
			'label'			=> __('Library','acf'),
			'instructions'	=> __('Limit the media library choice','acf'),
			'type'			=> 'radio',
			'name'			=> 'library',
			'layout'		=> 'horizontal',
			'choices' 		=> array(
				'all'			=> __('All', 'acf'),
				'uploadedTo'	=> __('Uploaded to post', 'acf')
			)
		));
	}
	
	/*
	*  render_field()
	*
	*  Create the HTML interface for your field
	*
	*  @param	$field - an array holding all the field's data
	*
	*  @type	action
	*  @since	3.6
	*  @date	23/01/13
	*/
	
	function render_field( $field ) {

		// enqueue
		acf_enqueue_uploader();
		
		// vars
		$posts = array();
		$atts = array(
			'id'				=> $field['id'],
			'class'				=> "acf-gallery {$field['class']}",
			'data-preview_size'	=> $field['preview_size'],
			'data-library'		=> $field['library'],
			'data-min'			=> $field['min'],
			'data-max'			=> $field['max'],
			'row_height'		=> $field['row_height'],
			'max_row_height'	=> $field['max_row_height'],
			'lastrow'			=> $field['lastrow'],
			'fixed_height'		=> $field['fixed_height'],
			'show_captions'		=> $field['show_captions'],
			'margin'			=> $field['margin'],
			'border'			=> $field['border'],
			'backcolor'			=> $field['backcolor'],
			'randomize'			=> $field['randomize'],
			'swipebox'			=> $field['swipebox'],
			'image_sizes'		=> $field['image_sizes'],
		);
		
		// set gallery height
		$height = acf_get_user_setting('gallery_height', 400);
		$height = max( $height, 200 ); // minimum height is 200
		$atts['style'] = "height:{$height}px";
		
		// load posts
		if( !empty($field['value']) ) {
					
			// force value to array
			$field['value'] = acf_force_type_array( $field['value'] );
			
			// convert values to int
			$field['value'] = array_map('intval', $field['value']);
			
			// load posts in 1 query to save multiple DB calls from following code
			$posts = get_posts(array(
				'posts_per_page'	=> -1,
				'post_type'			=> 'attachment',
				'post_status'		=> 'any',
				'post__in'			=> $field['value'],
				'orderby'			=> 'post__in'
			));
		}
		
		?>
<div <?php acf_esc_attr_e($atts); ?>>
	
	<div class="acf-hidden">
		<input type="hidden" <?php acf_esc_attr_e(array( 'name' => $field['name'], 'value' => '', 'data-name' => 'ids' )); ?> />
	</div>
	
	<div class="acf-gallery-main">
		
		<div class="acf-gallery-attachments">
			
			<?php if( !empty($posts) ): ?>
			
				<?php foreach( $posts as $post ): 
					
					// vars
					$type = acf_maybe_get(explode('/', $post->post_mime_type), 0);
					$thumb_id = $post->ID;
					$thumb_url = '';
					$thumb_class = 'acf-gallery-attachment acf-soh';
					$filename = wp_basename($post->guid);
					
					
					// thumb
					if( $type === 'image' || $type === 'audio' || $type === 'video' ) {
						
						// change $thumb_id
						if( $type === 'audio' || $type === 'video' ) {
							
							$thumb_id = get_post_thumbnail_id( $post->ID );
							
						}
						
						
						// get attachment
						if( $thumb_id ) {
							
							$thumb_url = wp_get_attachment_image_src( $thumb_id, $field['preview_size'] );
							$thumb_url = acf_maybe_get( $thumb_url, 0 );
						
						}
						
					}
					
					
					// fallback
					if( !$thumb_url ) {
						
						$thumb_url = wp_mime_type_icon( $post->ID );
						$thumb_class .= ' is-mime-icon';
						
					}
					
					?>
					<div class="<?php echo $thumb_class; ?>" data-id="<?php echo $post->ID; ?>">
						<input type="hidden" name="<?php echo $field['name']; ?>[]" value="<?php echo $post->ID; ?>" />
						<div class="margin" title="<?php echo $filename; ?>">
							<div class="thumbnail">
								<img src="<?php echo $thumb_url; ?>"/>
							</div>
							<?php if( $type !== 'image' ): ?>
							<div class="filename"><?php echo acf_get_truncated($filename, 18); ?></div>
							<?php endif; ?>
						</div>
						<div class="actions acf-soh-target">
							<a class="acf-icon dark remove-attachment" data-id="<?php echo $post->ID; ?>" href="#">
								<i class="acf-sprite-delete"></i>
							</a>
						</div>
					</div>
					
				<?php endforeach; ?>
				
			<?php endif; ?>
			
			
		</div>
		
		<div class="acf-gallery-toolbar">
			
			<ul class="acf-hl">
				<li>
					<a href="#" class="acf-button blue add-attachment"><?php _e('Add to gallery', 'acf'); ?></a>
				</li>
				<li class="acf-fr">
					<select class="bulk-actions">
						<option value=""><?php _e('Bulk actions', 'acf'); ?></option>
						<option value="date"><?php _e('Sort by date uploaded', 'acf'); ?></option>
						<option value="modified"><?php _e('Sort by date modified', 'acf'); ?></option>
						<option value="title"><?php _e('Sort by title', 'acf'); ?></option>
						<option value="reverse"><?php _e('Reverse current order', 'acf'); ?></option>
					</select>
				</li>
			</ul>
			
		</div>
		
	</div>
	
	<div class="acf-gallery-side">
	<div class="acf-gallery-side-inner">
			
		<div class="acf-gallery-side-data"></div>
						
		<div class="acf-gallery-toolbar">
			
			<ul class="acf-hl">
				<li>
					<a href="#" class="acf-button close-sidebar"><?php _e('Close', 'acf'); ?></a>
				</li>
				<li class="acf-fr">
					<a class="acf-button blue update-attachment"><?php _e('Update', 'acf'); ?></a>
				</li>
			</ul>
			
		</div>
		
	</div>	
	</div>
	
</div>
		<?php
		
	}
	
		
	/*
	*  input_admin_enqueue_scripts()
	*
	*  This action is called in the admin_enqueue_scripts action on the edit screen where your field is created.
	*  Use this action to add CSS + JavaScript to assist your render_field() action.
	*
	*  @type	action (admin_enqueue_scripts)
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	n/a
	*  @return	n/a
	*/	
	
	function input_admin_enqueue_scripts() {		
		$dir = plugin_dir_url( __FILE__ );
		// register & include JS
		wp_register_script( 'acf-input-justified_image_grid', "{$dir}js/input.js" );
		wp_enqueue_script('acf-input-justified_image_grid');
	}
	
	
	function format_value( $value, $post_id, $field ) {
		
		// bail early if no value
		if( empty($value) ) {			
			return $value;
		}
		
		// force value to array
		$value = acf_force_type_array( $value );
		
		// convert values to int
		$value = array_map('intval', $value);
		
		// load posts in 1 query to save multiple DB calls from following code
		$posts = get_posts(array(
			'posts_per_page'	=> -1,
			'post_type'			=> 'attachment',
			'post_status'		=> 'any',
			'post__in'			=> $value,
			'orderby'			=> 'post__in'
		));
		
		// reset value
		$value = array();
		
		// populate value
		foreach( $posts as $post ) {
			$value['images'][] = acf_get_attachment( $post );
		}

		$row_height		= $field['row_height'];
		$max_row_height	= $field['max_row_height'];
		$lastrow		= $field['lastrow'];
		$fixed_height	= $field['fixed_height'];
		$show_captions	= $field['show_captions'];
		$margin			= $field['margin'];
		$border			= $field['border'];
		$backcolor		= $field['backcolor'];
		$randomize		= $field['randomize'];
		$swipebox		= $field['swipebox'];
		$image_sizes	= $field['image_sizes'];
		$swipebox		= $field['swipebox'];
		
		ob_start(); ?>

		<script type="text/javascript">
			jQuery(document).ready(function(){
				var i = 0;
				var swipebox = '<?php echo $swipebox; ?>';
				jQuery('.image-container').each(function (index, el) {
					i++;
					jQuery(el).justifiedGallery({
						rel: 'gallery' + i,
						cssAnimation		: true,
						rowHeight			: <?php echo $row_height; ?>,
						maxRowHeight		: <?php echo $max_row_height; ?>,
						lastRow				: '<?php echo $lastrow; ?>',
						fixedHeight			: <?php echo $fixed_height; ?>,
						captions			: <?php echo $show_captions; ?>,
						margins				: <?php echo $margin; ?>,
						border				: <?php echo $border; ?>,
						randomize			: <?php echo $randomize; ?>,
					});
				}).on('jg.complete', function () {
					if (i === 1 && swipebox === 'yes' ) jQuery('a.swipebox').swipebox(
					); //swipebox, wants to be called only once to work properly
				});

			});
		</script>
		<?php echo '<div class="image-container"' . ($backcolor != '' ? 'style="background:' . $backcolor . ';"' : '') .'>';
			foreach ($value['images'] as $image) {
				$size_str = array();
				if ( $image_sizes ) {
					$i = 0;
					foreach ($image_sizes as $image_size) {
						if ( $i++ == 0 ) {
							$small_image        = $image["sizes"][$image_size];
							$small_image_width  = $image["sizes"][$image_size . '-width'];
							$small_image_height = $image["sizes"][$image_size . '-height'];
						}
						$size_str[] = '{"width" : "' . $image["sizes"][$image_size . '-width'] . '", "height" : "' . $image["sizes"][$image_size . '-height'] . '"}';
					}
				} else {
					$small_image        = $image["url"];
					$small_image_width  = $image["width"];
					$small_image_height = $image["height"];
				}
				$img_sizes = '[' . implode(",", $size_str) . ']';
			
				$img_title =  !$image["alt"] ? esc_attr($image["title"]) : esc_attr($image["alt"]);
				
				echo "<figure>";
						echo "<img src='" . $small_image . "' width='" . $small_image_width . "' height='" . $small_image_height . "' alt='" . $img_title . "' data-sizes='" . $img_sizes . "' data-url='" . $image["url"] . "' />";
						echo "<figcaption>";
							if ( $show_captions == 'true' ) {
									echo '<div>';
										echo "<p>" . esc_attr($image["title"]) . "</p>";
									echo '</div>';
							}
							if ( $swipebox == 'yes' ) {
								echo "<a class='swipebox' href='" . $image["url"] . "' data-title='" . esc_attr($image["title"]) . "'></a>";
							}
						echo "</figcaption>";
				echo "</figure>";
			}		
		echo '</div>';

		$output = ob_get_contents();

		ob_end_clean();

		return $output;

	}
	
	
	/*
	*  validate_value
	*
	*  description
	*
	*  @type	function
	*  @date	11/02/2014
	*  @since	5.0.0
	*
	*  @param	$post_id (int)
	*  @return	$post_id (int)
	*/
	
	function validate_value( $valid, $value, $field, $input ){
		
		if( empty($value) || !is_array($value) ) {
		
			$value = array();
			
		}
		
		
		if( count($value) < $field['min'] ) {
		
			$valid = _n( '%s requires at least %s selection', '%s requires at least %s selections', $field['min'], 'acf' );
			$valid = sprintf( $valid, $field['label'], $field['min'] );
			
		}
		
				
		return $valid;
		
	}
	
}

// create field
new acf_field_justified_image_grid();

function get_image_sizes() {
	global $_wp_additional_image_sizes;

	$sizes = array();
	$get_intermediate_image_sizes = get_intermediate_image_sizes();

	// Create the full array with sizes and crop info
	foreach( $get_intermediate_image_sizes as $_size ) {
		if ( in_array( $_size, array( 'thumbnail', 'medium', 'large' ) ) ) {
			$sizes[ $_size ]['width'] = get_option( $_size . '_size_w' );
			$sizes[ $_size ]['height'] = get_option( $_size . '_size_h' );
			$sizes[ $_size ]['crop'] = (bool) get_option( $_size . '_crop' );
		} elseif ( isset( $_wp_additional_image_sizes[ $_size ] ) ) {
			$sizes[ $_size ] = array( 
				'width' => $_wp_additional_image_sizes[ $_size ]['width'],
				'height' => $_wp_additional_image_sizes[ $_size ]['height'],
				'crop' =>  $_wp_additional_image_sizes[ $_size ]['crop']
			);
		}
	}

	if (function_exists('cmp')) {
		} else {
		function cmp($a, $b) {
			if ($a['height'] == $b['height']) {
				return 0;
			}
			return ($a['height'] < $b['height']) ? -1 : 1;
		}
	}

	uasort($sizes, "cmp");

	foreach ($sizes as $key => $value) {
		if ( !$value['crop'] == 1 && $value['width'] == '9999') {
			$imagessizes[$key] = ucwords($key) . " (" . $value['width'] . " x " . $value['height'] . ") ";
		}
	}

	return $imagessizes;
}
?>