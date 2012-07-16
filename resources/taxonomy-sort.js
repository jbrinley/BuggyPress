jQuery(document).ready( function($) {
	var taxonomy = $('#posts-filter input[name="taxonomy"]').val();

	// find the range in which we should be working
	var min = 0;
	var max = 0;
	$('#the-list td.bp_sort_order input').hide().each( function() {
		$(this).before('<span class="sort-handle"></span>');
		var val = $(this).val();
		if ( val < min ) {
			min = val;
		}
		if ( val > max ) {
			max = val;
		}
	});

	$('#the-list').sortable({
		axis: 'y',
		items: 'tr',
		handle: 'td.bp_sort_order',
		update: function( event, ui ) {
			var value = min;
			var data = {
				action: 'buggpress_taxonomy_sort',
				taxonomy: taxonomy,
				terms: {}
			};
			$('#the-list td.bp_sort_order input').each( function() {
				$(this).val(value++);
				var name = $(this).attr('name');
				var term_id = $(this).parents('tr').find('.check-column input').val();
				data.terms[term_id] = $(this).val();
			});
			$.post(
				ajaxurl,
				data
			);
		}
	});
});