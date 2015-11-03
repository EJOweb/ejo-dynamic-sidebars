<?php
// Save Theme options
if (isset($_POST['submit']) ) {

	//* Update post type sidebar
	if ( isset($_POST['ejo-post-type-sidebar']) ) {

		//* Store header & footer scripts
		update_option( 'ejo_dynamic_sidebars', $_POST['ejo-post-type-sidebar'] );
	}
}

$ejo_post_type_sidebar = get_option( 'ejo_dynamic_sidebars', '' );
?>

<div class="postbox">
	<h3 class="hndle">Zijbalken</h3>

	<div class="inside">
		<table class="form-table ejo-dynamic-sidebars">
			<tbody>

			<?php
			//* Get all registerd sidebars
			global $wp_registered_sidebars;

			$sidebars = array();

			foreach ($wp_registered_sidebars as $sidebar_id => $sidebar) {

				//* if registered widget-area has 'sidebar' in it's name
				if (strpos($sidebar_id,'sidebar') !== false) {
					$sidebars[$sidebar_id]['name'] = $sidebar['name'];
					$sidebars[$sidebar_id]['description'] = $sidebar['description'];
				}
			}

			//* Default post types
			$post_types = array( 
				'post' => 'post',
				'page' => 'page'
			);

			//* Combine default post-types with public custom post-types
			$post_types = $post_types + get_post_types( array(
				'public'   => true,
				'_builtin' => false
			)); 

			//* Add metabox for each custom post type
			foreach ( $post_types as $post_type ) : ?>
				<tr>
					<th>
						<?php echo $post_type; ?>
					</th>
					<td>
						<select name="ejo-post-type-sidebar[<?php echo $post_type; ?>]">
							<option value="">Selecteer een zijbalk</option>
							<?php
							foreach ($sidebars as $sidebar_id => $sidebar) {
								
								//* If there is a saved sidebar for this post_type, check if current sidebar is selected
								$selected = isset($ejo_post_type_sidebar[$post_type]) ? selected( $sidebar_id, $ejo_post_type_sidebar[$post_type], false) : '';

								echo '<option value="' . $sidebar_id . '" ' . $selected . '>' . $sidebar['name'] . '</option>';
							}
							?>
						</select>
					</td>
				</tr>
			<?php endforeach; ?>

			</tbody>
		</table>

	</div><!-- END inside -->

</div><!-- END postbox -->
