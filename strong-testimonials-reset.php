<?php
/**
 * Plugin Name: Strong Testimonials Reset
 * Description: Leave No Trace
 * Author: Chris Dillon
 * Version: 1.8
 * Text Domain: strong-testimonials-reset
 * Requires: 3.7 or higher
 * License: GPLv3 or later
 *
 * Copyright 2015-2018  Chris Dillon  chris@strongplugins.com
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */


// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;


class Strong_Testimonials_Reset {

	public $actions;

	public function __construct() {

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
			'repair'      => array(
				'method'  => 'repair_fields',
				'label'   => 'Repair custom fields',
				'success' => __( 'Repair triggered successfully.', 'strong-testimonials-reset' ),
			),
			'options'     => array(
				'method'  => 'delete_options',
				'label'   => 'Delete all settings',
				'success' => __( 'Settings deleted successfully.', 'strong-testimonials-reset' ),
			),
			'addons'      => array(
				'method'  => 'unset_addons',
				'label'   => 'Delete add-on info',
				'success' => __( 'Add-on info deleted successfully.', 'strong-testimonials-reset' ),
			),
			'addon_review_markup' => array(
				'method'  => 'unset_addon_review_markup',
				'label'   => 'Delete info for <b>Review Markup</b> add-on',
				'success' => __( 'Add-on info deleted successfully.', 'strong-testimonials-reset' ),
			),
			'addon_assignment' => array(
				'method'  => 'unset_addon_assignment',
				'label'   => 'Delete info for <b>Assignment</b> add-on',
				'success' => __( 'Add-on info deleted successfully.', 'strong-testimonials-reset' ),
			),
			'drop-tables'      => array(
				'method'  => 'drop_tables',
				'label'   => 'Drop tables',
				'success' => __( 'Tables dropped successfully.', 'strong-testimonials-reset' ),
			),
			'add-tables'      => array(
				'method'  => 'Add_tables',
				'label'   => 'Add tables',
				'success' => __( 'Tables added successfully.', 'strong-testimonials-reset' ),
			),
			'pointers'    => array(
				'method'  => 'reset_pointers',
				'label'   => 'Reset pointers',
				'success' => __( 'Pointers reset successfully.', 'strong-testimonials-reset' ),
			),
			'order' => array(
				'method'  => 'reset_order',
				'label'   => 'Reset order',
				'success' => __( 'Order reset successfully.', 'strong-testimonials-reset' ),
			),
			'custom_order' => array(
				'method'  => 'reset_custom_order',
				'label'   => 'Reset custom order',
				'success' => __( 'Custom order reset successfully.', 'strong-testimonials-reset' ),
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
		update_option( 'wpmtst_plugin_version', '2.0' );
		update_option( 'wpmtst_db_version', '1' );
        //delete_option( 'wpmtst_history' );
		$history = get_option( 'wpmtst_history' );
        unset( $history['2.28_new_update_process'] );
        unset( $history['2.29_captcha_options_changed'] );
        unset( $history['2.30_new_template_structure'] );
        update_option( 'wpmtst_history', $history );

		// add-ons
		$addons = get_option( 'wpmtst_addons' );
		if ( isset( $addons['multiple-forms']['version'] ) ) {
			unset( $addons['multiple-forms']['version'] );
		}
		if ( isset( $addons['properties']['version'] ) ) {
			unset( $addons['properties']['version'] );
		}
		if ( isset( $addons['review-markup']['version'] ) ) {
			unset( $addons['review-markup']['version'] );
		}
	}

	public function repair_fields() {
		if ( method_exists( 'Strong_Testimonials_Defaults', 'get_fields' ) ) {
			$fields = Strong_Testimonials_Defaults::get_fields();
		} elseif ( function_exists( 'wpmtst_get_default_fields' ) ) {
			$fields = wpmtst_get_default_fields();
		} else {
			$fields = get_option( 'wpmtst_fields' );
		}

		$custom_forms = get_option( 'wpmtst_custom_forms' );

        foreach ( $custom_forms as $form_id => $form_properties ) {

            foreach ( $form_properties['fields'] as $key => $form_field ) {
                /*
                 * Merge in new default.
                 * Custom fields are in display order (not associative) so we must find them by `input_type`.
                 * @since 2.21.0 Using default fields instead of default form as source
                 */
                foreach ( $fields['field_types'] as $field_type_group_key => $field_type_group ) {
                    foreach ( $field_type_group as $field_type_key => $field_type_field ) {
                        if ( $field_type_field['input_type'] == $form_field['input_type'] ) {
                            if ( isset( $field_type_field['show_mailchimp_option'] ) ) {
	                            $form_field['show_mailchimp_option'] = $field_type_field['show_mailchimp_option'];
                            }
                            break;
                        }
                    }
                }
                $custom_forms[ $form_id ]['fields'][ $key ] = $form_field;
            }

        }
        update_option( 'wpmtst_fields', $fields );
        update_option( 'wpmtst_custom_forms', $custom_forms );
	}

	public function delete_options() {
		global $wpdb;
		$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE 'wpmtst_%'" );
	}

	public function unset_addons() {
		global $wpdb;
		$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE 'wpmtst_addons'" );
	}

	public function unset_addon_review_markup() {
		global $wpdb;
		$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE 'wpmtst_review_markup%'" );
		// TODO Only remove review markup errors and notices instead of deleting records.
		$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE 'wpmtst_config_errors'" );
		$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE 'wpmtst_admin_notices'" );
	}

	public function unset_addon_assignment() {
		global $wpdb;

		$addons = get_option( 'wpmtst_addons' );
		unset( $addons['assignment'] );
		update_option( 'wpmtst_addons', $addons );

		$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE 'wpmtst_assignment%'" );
		$wpdb->query( "DELETE FROM {$wpdb->postmeta} WHERE meta_key LIKE 'strong_assignment%'" );

		update_option( 'strong_testimonials_assignment_init', 1 );
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

	public function add_tables() {
		global $wpdb;

		$charset_collate = $wpdb->get_charset_collate();

		$table_name = $wpdb->prefix . 'strong_views';

		$sql = "CREATE TABLE $table_name (
			id mediumint(9) NOT NULL AUTO_INCREMENT,
			name varchar(100) NOT NULL,
			value text NOT NULL,
			PRIMARY KEY  (id)
		) $charset_collate;";

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		$result = dbDelta( $sql );

		if ( $result && function_exists( 'q2' ) ) {
			//q2( $result, __FUNCTION__ );
		}

		update_option( 'wpmtst_db_version', '1.0' );
	}

	public function reset_order() {
		global $wpdb;
		$wpdb->query( "UPDATE {$wpdb->posts} SET menu_order = 0 WHERE post_type = 'wpm-testimonial'" );
	}

	public function reset_custom_order() {
		global $wpdb;
		$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE 'wpmtst_custom_order%'" );
		$wpdb->query( "DELETE FROM {$wpdb->postmeta} WHERE meta_key LIKE 'testimonial_score%'" );
		//$wpdb->query( "DELETE FROM {$wpdb->postmeta} WHERE meta_key LIKE 'testimonial_complete%'" );
	}

	public function delete_transients() {
		delete_transient( 'wpmtst_order_query' );
		delete_transient( 'wpmtst_update_in_progress' );
	}

	public function reactivate_plugin() {
		deactivate_plugins( 'strong-testimonials/strong-testimonials.php' );
		activate_plugin( 'strong-testimonials/strong-testimonials.php' );
	}

}

new Strong_Testimonials_Reset();
