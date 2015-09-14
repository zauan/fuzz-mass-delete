<?php
/*
 * Plugin Name: Fuzz mass delete
 * Plugin URI:
 * Description: Removes all content from a wordpress installation
 * Author: ThemeFuzz
 * Version: 1.0.0
 */

class Fuzz_Mass_Delete{
	function __construct(){

		add_action( 'admin_menu', array( &$this, 'register_admin_menu' ) );
		add_action( 'init', array( &$this, 'check_delete' ), 9999 );
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
			<a href="<?php print wp_nonce_url(admin_url('options.php?page=fuzz-mass-delete'), 'doing_something', 'fuzz_mass_delete');?>">Delete all content</a>
		</div>

	<?php

		echo '<form>';
	}
}

new Fuzz_Mass_Delete;