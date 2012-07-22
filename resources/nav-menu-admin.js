/**
 * Handle the BuggyPress nav menu meta box
 */
jQuery(document).ready( function($) {
	$('#submit-buggypress-menu-items').click( function( event ) {
		event.preventDefault();
		var to_add = [];
		$('#buggypress-menu-item-checklist li :checked').each( function() {
			to_add.push($(this).val());
		});
		$.post( ajaxurl,
			{
				action: 'buggypress_add_to_menu',
				buggypress_nonce: BuggyPressMenu.nonce,
				menu_items: to_add
			},
			function( response ) {
				$('#menu-to-edit').append(response).hideAdvancedMenuItemFields();
			}
		);
	});
});