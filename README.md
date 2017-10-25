ACF - Justified Image Grid
==========================

With the ACF – Justified Image Grid field it’s easy to create an elegant and full responsive image gallery with various sizes of images, where all the images of a row to have (approximately) the same height.

![advanced custom field justified image grid](http://www.dreihochzwo.de/download/acf_justified_image_grid.jpg)

The plugin is based on the <a href="http://miromannino.github.io/Justified-Gallery/" title="Justified Gallery Homepage" target="_blank">Justified Gallery</a> jQuery plugin and use the great <a href="http://brutaldesign.github.io/swipebox/" title="Swipebox Homepage" target="_blank">Swipebox</a> jQuery plugin.

<a href="http://www.dreihochzwo.de/wordpress-plugins/advanced-custom-fields-addon-justified-image-grid/" title="Demo &amp; Info" target="_blank">Demo &amp; Info</a>

Ideally, Justified Gallery tries to show images without modifying its aspect ratio and without cropping them. But, when limited by the maximum row size it sometimes crops images a little bit to fill the grid.

The field settings allow an individual display of the grid

* Row height for the images
* Maximum Row height for the images
* How to handle the last row
* Make all row the exact same height
* Show or hide image captions
* Set the width of the grid border and the image margins
* Select an individual color for the border and the margins
* Randomize the images
* Select your preferred images sizes
* Use the Swipebox jQuery plugin for large image view

![advanced custom field justified image grid](http://www.dreihochzwo.de/download/acf_justified_image_grid_settings.jpg)

In the settings you can select the image sizes used by the plugin. All listed image sizes are non-cropped sizes with an unlimited width (witdh = 9999px). This feature is used for performance so the plugin can use different image sizes for displaying the images. It’s best to setup image sizes with a height of 110% - 120% of both row heights.

At least the plugin support the mobile-friedly Swipebox jQuery lightbox plugin to show the full size images.

Images are added to the grid by the Gallery Field of ACF which is used by the plugin. This gives you the possibility to easily add and arrange images for the grid.

![advanced custom field justified image grid field settings](http://www.dreihochzwo.de/download/acf-justified-image-grid-gallery-field.jpg)

### New in version 1.1.0

There are 4 new functions to display one specific image from grid or to get infos of this image; such as image ID, image url or image src (similar to the WP function `wp_get_attachment_image_src`).

### jig_image()

`jig_image( $field, $image_number, $size, $post_id )`

Diplays the specific image

### jig_get_image_id()

`jig_get_image_id( $field, $image_number, $size, $post_id )`

Returns the ID of the specific image

### jig_get_image_scr()

`jig_get_image_src( $field, $image_number, $size, $post_id )`

Returns an array for the specific image

* [0] => url
* [1] => width
* [2] => height
* [3] => boolean: true if $url is a resized image, false if it is the original or if no image is available.

### jig_get_image_url()

`jig_get_image_url( $field, $image_number, $size, $post_id )`

Returns the URL of the specific image

These are the parameters for each function:

**$field**<br/>
(string) (required) The ACF field that hold the acf_justified_image_grid.<br/>
Default: None

**$image_number**<br/>
(integer) (optional) The number of the image that should be display. No array counting - the numbering starts at 1. If the number is greater than the number of images in the grid the functions use the first image of the grid as a fallback.<br/>
Default: 1

**$size**<br/>
(string) (optional) Any of the standard image sizes (thumbnail, medium, large, full, or post-thumbnail (if the theme supports the post thumbnail)) or any of the defined image sizes of the theme.<br/>
Default: thumbnail

**$post_id**<br/>
(integer) (optional) Post ID. If null, the current post will be used.<br/>
Default: null

Each function returns false if no image/ image value is found.

Thanks to
<a href="http://miromannino.github.io/Justified-Gallery/" title="Justified Gallery Homepage" target="_blank">Miro Mannino</a> for the Justified Gallery and <a href="http://brutaldesign.github.io/swipebox/" title="Swipebox Homepage" target="_blank">Constantin Saguin</a> for the Swipebox


### Compatibility

This version works only with ACF 5.


### Changelog
**1.2.0**
* Fixed admin gallery functions

**1.1.1**
* Version only for ACF 5.2.7
* Fixed delete icon on gallery overview

**1.1.0**
* Version only for ACF 5.2.7
* New: Functions to get an image or image infos of specific image of the grid

**1.0.7**
* Version only for ACF 5.2.7
* Fixed: Error adding images

**1.0.6**
* Version only for ACF 5.2.7
* Fixed: Error adding images

**1.0.5**
* Add possibility to use more than one image grid within one ACF field group and therefore on the same post/page

**1.0.4**
* Better handling for alt tags for images

**1.0.3**
* Small bugfixes

**1.0.2**
* Small bugfixes

**1.0.1**
* Update for effective image loading 

**1.0.0**
* First release