<?php
/**
Plugin Name: Strong Testimonials Reset
Description: Leave No Trace
Author: Chris Dillon
Version: 1.1
Text Domain: strong-testimonials-reset
Requires: 3.0 or higher
License: GPLv3 or later


Copyright 2015  Chris Dillon  chris@wpmission.com

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

class Strong_Testimonials_Reset {

	public $actions;

	public function __construct() {
		load_plugin_textdomain( 'strong-testimonials-reset', false, dirname( plugin_basename( __FILE__ ) ) . '/lang' );
		add_action( 'admin_enqueue_scripts', array( $this, 'load_scripts' ) );
		add_action( 'admin_menu', array( $this, 'add_options_page' ), 20 );
		add_action( 'load-tools_page_reset-strong-testimonials', array( $this, 'reset_page' ) );

		$this->set_actions();

		foreach ( $this->actions as $action => $ops ) {
			add_action( "strong_reset_$action", array( $this, $ops['method'] ) );
		}
	}

	public function set_actions() {
		$this->actions = array(
			'update'      => array(
				'method'  => 'trigger_update',
				'label'   => 'Trigger update process',
				'success' => __( 'Update triggered successfully.', 'strong-testimonials-reset' ),
			),
			'options'     => array(
				'method'  => 'delete_options',
				'label'   => 'Delete settings',
				'success' => __( 'Settings deleted successfully.', 'strong-testimonials-reset' ),
			),
			'tables'      => array(
				'method'  => 'drop_tables',
				'label'   => 'Drop tables',
				'success' => __( 'Tables dropped successfully.', 'strong-testimonials-reset' ),
			),
			'pointers'    => array(
				'method'  => 'reset_pointers',
				'label'   => 'Reset pointers',
				'success' => __( 'Pointers reset successfully.', 'strong-testimonials-reset' ),
			),
			'reset-order' => array(
				'method'  => 'reset_order',
				'label'   => 'Reset order',
				'success' => __( 'Order reset successfully.', 'strong-testimonials-reset' ),
			),
			'transients'  => array(
				'method'  => 'delete_transients',
				'label'   => 'Delete transients',
				'success' => __( 'Transients deleted successfully.', 'strong-testimonials-reset' ),
			),
			'reactivate'  => array(
				'method'  => 'reactivate_plugin',
				'label'   => 'Deactivate & reactivate',
				'success' => __( 'Reactivated successfully.', 'strong-testimonials-reset' ),
			),
		);

	}

	public function load_scripts() {
		//wp_enqueue_style( 'reset-admin-style', 'css/admin.css', array(), null );
	}

	public function reset_page() {
		if ( isset( $_REQUEST['confirm'] ) && 'yes' == $_REQUEST['confirm'] ) {

			$args = array(
				'reset'   => $_REQUEST['reset'],
				'page'    => $_REQUEST['page'],
				'confirm' => false,
				'success' => true
			);

			do_action( 'strong_reset_' . $_REQUEST['reset'] );

			$goback = add_query_arg( $args, wp_get_referer() );
			wp_redirect( $goback );
			exit;

		}
	}

	public function add_options_page() {
		add_submenu_page( 'tools.php',
			__( 'Reset Strong Testimonials', 'strong-testimonials-reset' ),
			__( 'Reset Strong Testimonials', 'strong-testimonials-reset' ),
			'manage_options',
			'reset-strong-testimonials',
			array( $this, 'options_page' ) );
	}

	public function options_page() {
		?>
		<div class="wrap">
			<h2>Reset</h2>
			<?php
			if ( isset( $_REQUEST['success'] ) ) {
				printf( '<div id="message" class="updated notice is-dismissible"><p>%s</p></div>', $this->actions[$_REQUEST['reset']]['success'] );
			}

			foreach ( $this->actions as $action => $ops ) {
				$args = array( 'page' => $_REQUEST['page'], 'reset' => $action, 'confirm' => 'yes' );
				echo '<p><a href="' . add_query_arg( $args, admin_url( 'tools.php' ) ) . '">' . $ops['label'] . '</a></p>';
			}

		?>

		</div>
	<?php
	}

	public function trigger_update() {
		update_option( 'wpmtst_plugin_version', '1.25.2' );
		update_option( 'wpmtst_db_version', '0' );
	}

	public function delete_options() {
		global $wpdb;
		$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE 'wpmtst_%'" );
	}

	public function reset_pointers() {
		$dismissed = explode( ',', (string) get_user_meta( get_current_user_id(), 'dismissed_wp_pointers', true ) );
		$keep = array();
		foreach ( $dismissed as $key => $pointer ) {
			if ( 'wpmtst' != substr( $pointer, 0, 6 ) ) {
				$keep[] = $pointer;
			}
		}
		update_user_meta( get_current_user_id(), 'dismissed_wp_pointers', implode( ',', $keep ) );
	}

	public function drop_tables() {
		global $wpdb;
		$table = $wpdb->prefix . 'strong_views';
		$wpdb->query( "DROP TABLE IF EXISTS {$table}" );
	}

	public function reset_order() {
		global $wpdb;
		$wpdb->query( "UPDATE {$wpdb->posts} SET menu_order = 0 WHERE post_type = 'wpm-testimonial'" );
	}

	public function delete_transients() {
		delete_transient( 'wpmtst_order_query' );
	}

	public function reactivate_plugin() {
		deactivate_plugins( 'strong-testimonials/strong-testimonials.php' );
		activate_plugin( 'strong-testimonials/strong-testimonials.php' );
	}

}

new Strong_Testimonials_Reset();
