ACF - Justified Image Grid
==========================

With the ACF – Justified Image Grid field it’s easy to create an elegant and full responsive image gallery with various sizes of images, where all the images of a row to have (approximately) the same height.

![advanced custom field justified image grid](http://www.dreihochzwo.de/download/acf_justified_image_grid.jpg)

The plugin is based on the <a href="http://miromannino.github.io/Justified-Gallery/" title="Justified Gallery Homepage" target="_blank">Justified Gallery</a> jQuery plugin and use the great <a href="http://brutaldesign.github.io/swipebox/" title="Swipebox Homepage" target="_blank">Swipebox</a> jQuery plugin.

Ideally, Justified Gallery tries to show images without modifying its aspect ratio and without cropping them. But, when limited by the maximum row size it sometimes crop images a little bit to fill the grid.

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

This version works only with ACF 5.

Thanks to
<a href="http://miromannino.github.io/Justified-Gallery/" title="Justified Gallery Homepage" target="_blank">Miro Mannino</a> for the Justified Gallery and <a href="http://brutaldesign.github.io/swipebox/" title="Swipebox Homepage" target="_blank">Constantin Saguin</a> for the Swipebox
