(function($) {
	var id, row_height, max_row_height, lastrow, fixed_height, show_captions, margin, border, randomize, swipebox;

	$('.image-container').each(function (index, el) {
		id = $(this).data('id');
		row_height = $(this).data('row_height');
		max_row_height = $(this).data('max_row_height');
		lastrow = $(this).data('lastrow');
		fixed_height = $(this).data('fixed_height');
		show_captions = $(this).data('show_captions');
		margin = $(this).data('margin');
		border = $(this).data('border');
		randomize = $(this).data('randomize');
		swipebox = $(this).data('swipebox');

		$(el).justifiedGallery({
			rel: 'gallery' + id,
			cssAnimation		: true,
			rowHeight			: row_height,
			maxRowHeight		: max_row_height,
			lastRow				: lastrow,
			fixedHeight			: fixed_height,
			captions			: show_captions,
			margins				: margin,
			border				: border,
			randomize			: randomize,
		});
	});

	if ( swipebox === 'yes' ) $('a.swipebox').swipebox();

}(jQuery));