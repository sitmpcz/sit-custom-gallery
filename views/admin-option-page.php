<?php

$objects = get_option("sitg_objects");
$debug = get_option("sitg_debug");
$max_upload_size = (int) get_option("sitg_max_upload_size");
$max_upload_size = ( $max_upload_size > 32 ) ? 25 : $max_upload_size;

?>
<div class="wrap">
	<h1>Nastavení galerie</h1>
	<form method="post" action="options.php">
		<?php
		settings_fields("sitg_options");
		do_settings_sections("sitg_options");
		?>
		<table class="form-table">
			<tr valign="top">
				<th scope="row">Post Types</th>
				<td>
					<?php
					$post_types = get_post_types( array (
						'show_ui' => true,
						'show_in_menu' => true,
					), 'objects' );

					foreach ( $post_types  as $post_type ) {
						if ( $post_type->name == 'attachment' ) continue;
						?>
						<input type="checkbox" name="sitg_objects[]" value="<?php echo $post_type->name; ?>" id="<?php echo $post_type->name; ?>" <?php if ( isset( $objects ) && is_array( $objects ) ) { if ( in_array( $post_type->name, $objects ) ) { echo 'checked="checked"'; } } ?>>
                        <label for="<?php echo $post_type->name; ?>">
                            <?php echo $post_type->label; ?>
                        </label><br>
						<?php
					}
					?>
				</td>
			</tr>
            <tr valign="top">
                <th scope="row">Velikost souborů [MB]</th>
                <td>
                    <input type="number" name="sitg_max_upload_size" value="<?php echo $max_upload_size; ?>" class="small-text">
                    <p class="description">Maximální velikost je omezena na 32 MB</p>
                </td>
            </tr>
			<tr valign="top">
				<th scope="row">Zapnout debug okno</th>
				<td><input type="checkbox" name="sitg_debug" value="1" <?php if ( $debug == 1 ) { echo 'checked="checked"'; } ?>></td>
			</tr>
		</table>
		<?php
		submit_button();
		?>
	</form>
</div>
