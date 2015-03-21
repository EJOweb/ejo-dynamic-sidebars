<?php
/**
 * Dynamic Sidebars
 * Give user the option to chose sidebar on per-page base.
 */

add_action( 'add_meta_boxes', 'ejo_add_dynamic_sidebar_metabox' );
// add_action( 'save_post', 'ejo_save_dynamic_sidebar' ); // save the custom fields
add_action( 'pre_post_update', 'ejo_save_dynamic_sidebar' ); // save the custom fields. Save_post hook doesn't seem to be called when nog changing the post

// Add Dynamic sidebar metabox
function ejo_add_dynamic_sidebar_metabox() 
{
	add_meta_box( 'ejo_dynamic_sidebar_metabox', 'Kies de zijbalk', 'ejo_render_dynamic_sidebar_metabox', 'page', 'side', 'default' );
}

// The dynamic sidebar metabox
function ejo_render_dynamic_sidebar_metabox( $post ) 
{
	// Noncename needed to verify where the data originated
	wp_nonce_field( 'ejo-dynamic-sidebar-metabox-' . $post->ID, 'ejo-dynamic-sidebar-meta-nonce' );

	global $wp_registered_sidebars;
?>
	<p>
		<select name="ejo-dynamic-sidebar">
<?php
		$selected_sidebar = get_post_meta( $post->ID, '_ejo-dynamic-sidebar', true );
		$selected_sidebar = (!empty($selected_sidebar)) ? $selected_sidebar : 'sidebar-primary';

		
		foreach ($wp_registered_sidebars as $sidebar_id => $sidebar) {
			//* if registered sidebar is a sidebar and not an other widget-area
			if (strpos($sidebar_id,'sidebar') !== false) {
				$selected = ($sidebar_id == $selected_sidebar) ? 'selected=selected' : '';
				echo '<option value="' . $sidebar_id . '" ' . $selected . '>' . $sidebar['name'] . '</option>';
			}			
		}
?>
			<option value="no-sidebar" <?php echo ($selected_sidebar == 'no-sidebar') ? 'selected=selected' : ''; ?>>-- Geen Zijbalk --</option>
		</select>
	</p>
<?php
}

// Manage saving Metabox Data
function ejo_save_dynamic_sidebar($post_id) 
{
	//* Don't try to save the data under autosave, ajax, or future post.
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
		return;
	if ( defined( 'DOING_AJAX' ) && DOING_AJAX )
		return;
	if ( defined( 'DOING_CRON' ) && DOING_CRON )
		return;

	//* Don't save if WP is creating a revision (same as DOING_AUTOSAVE?)
	if ( wp_is_post_revision( $post_id ) )
		return;

	//* Check that the user is allowed to edit the post
	if ( ! current_user_can( 'edit_post', $post_id ) )
		return;

	// Verify where the data originated
	if ( !isset($_POST['ejo-dynamic-sidebar-meta-nonce']) || !wp_verify_nonce( $_POST['ejo-dynamic-sidebar-meta-nonce'], 'ejo-dynamic-sidebar-metabox-' . $post_id ) )
		return;

	$meta_key = '_ejo-dynamic-sidebar';

	log_it( $post_id . ': ' . $meta_key . ' > ' . $_POST['ejo-dynamic-sidebar'] );

	if ( isset( $_POST['ejo-dynamic-sidebar'] ) )
		update_post_meta( $post_id, $meta_key, $_POST['ejo-dynamic-sidebar'] );
}
