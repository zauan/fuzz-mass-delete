<?php
/*
 * Plugin Name: Fuzz mass delete
 * Plugin URI:
 * Description: Removes all content from a wordpress installation
 * Author: ThemeFuzz
 * Version: 1.0
 */

class Fuzz_Mass_Delete{
	function __construct(){

		add_action( 'admin_menu', array( &$this, 'register_admin_menu' ) );
		add_action( 'init', array( &$this, 'check_delete' ), 9999 );
		add_action( 'init', array( &$this, 'check_reset_options' ), 9999 );
	}

	function check_reset_options(){
		if ( ! isset($_GET['fuzz_reset_theme_options']) || ! wp_verify_nonce($_GET['fuzz_reset_theme_options'], 'doing_something')) {
			return 'Nonce not good';
		}

		$options_field = ZN()->theme_data['options_prefix'];
		$saved_values = array();

		$file =  dirname( __FILE__ ) ."/theme_options.txt";
		if( ! is_file( $file ) ) {
			include( THEME_BASE.'/template_helpers/options/theme-options.php');

			foreach ( $admin_options as $key => $option ) {

				if( !empty( $option['std'] ) )
				{
					$saved_values[$option['parent']][$option['id']] = $option['std'];
				}

			}
		}
		else{
			$data = file_get_contents( $file );
			$saved_values = json_decode( $data, true );
		}

		update_option( $options_field , $saved_values );
		generate_options_css();
		ZN()->pagebuilder->refresh_pb_data();


	}


	function check_delete(){
		if ( ! isset($_GET['fuzz_mass_delete']) || ! wp_verify_nonce($_GET['fuzz_mass_delete'], 'doing_something')) {
			return 'Nonce not good';
		}

		$all_post_types = get_post_types();

		foreach( $all_post_types as $post_type ){
			$args = array(
				'posts_per_page'   => -1,
				'orderby'          => 'date',
				'order'            => 'DESC',
				'post_type'        => $post_type,
				'post_status'      => 'any'
			);

			$posts_array = get_posts( $args );

			foreach( $posts_array as $post ){
				if( $post_type == 'attachment' ){
					wp_delete_attachment( $post->ID, true );
				}
				else{
					wp_delete_post( $post->ID, true );
				}

			}
		}

		$taxonomies = get_taxonomies();

		foreach( $taxonomies as $taxonomy ){
			$terms = get_terms( $taxonomy, array( 'fields' => 'ids', 'hide_empty' => false ) );
			foreach ( $terms as $value ) {
				wp_delete_term( $value, $taxonomy );
			}
		}

		return false;

	}

	function register_admin_menu(){
		add_menu_page( 'Fuzz Mass Delete', 'Fuzz Mass Delete', 'install_plugins', 'fuzz-mass-delete', array( &$this, 'admin_page' ) );
	}

	function admin_page(){
		?>
		<div class="wrap">
			<h2>Your Plugin Page Title</h2>
			<a href="<?php print wp_nonce_url(admin_url('options.php?page=fuzz-mass-delete'), 'doing_something', 'fuzz_mass_delete');?>">Delete all content</a></br>
			<a href="<?php print wp_nonce_url(admin_url('options.php?page=fuzz-mass-delete'), 'doing_something', 'fuzz_reset_theme_options');?>">Reset theme options</a></br>
		</div>

	<?php

		echo '<form>';
	}
}

new Fuzz_Mass_Delete;
