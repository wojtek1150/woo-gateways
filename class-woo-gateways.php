<?php
/**
 * Plugin Name: Disable gateways by currency for WOOCS
 * Description: This plugin lets you easily disable checkout gateway for some currencies.
 * Author: Wojciech Parys
 * Version: 1.0
 */

class WP_Gateways {
	protected $woocs;

	public function __construct() {
		global $WOOCS;

		if ( isset( $WOOCS ) ) {
			$this->woocs = $WOOCS;

			add_filter( 'woocommerce_available_payment_gateways', array( $this, 'filter_gateways' ), 10, 1 );

		} else {
			add_action( 'admin_notices', array( $this, 'woocs_not_found' ) );
		}

		// ADD option page
		$this->add_acf_options_page();

	}

	/**
	 * Filter method
	 *
	 * @param $gateways
	 *
	 * @return mixed
	 */
	function filter_gateways( $gateways ) {
		$current = $this->woocs->current_currency;

		if ( have_rows( 'gateways_to_disable', 'options' ) ): while ( have_rows( 'gateways_to_disable', 'options' ) ): the_row();
			$gateway = get_sub_field( 'gateway_id' );
			if ( have_rows( 'currency_codes' ) ): while ( have_rows( 'currency_codes' ) ): the_row();
				if ( get_sub_field( 'currency_code' ) == $current ) {
					unset( $gateways[ $gateway ] );
				}
			endwhile; endif;
		endwhile; endif;

		return $gateways;
	}

	/**
	 * Get ACF field
	 *
	 * @param $field
	 *
	 * @return mixed|null|void
	 */
	public static function getSetting( $field ) {
		switch_to_blog( 1 );

		$setting = false;
		if ( function_exists( 'get_field' ) ) {
			$setting = get_field( $field, 'option' );
		}

		restore_current_blog();

		return $setting;
	}

	/**
	 * Add ACF options page
	 */
	public function add_acf_options_page() {
		if ( function_exists( 'acf_add_options_page' ) ) {
			acf_add_options_page( array(
				'page_title' => __( 'Gateways', 'gateways' ),
				'menu_title' => __( 'Gateways', 'gateways' ),
				'icon_url'   => 'dashicons-cart',
				'menu_slug'  => 'gateways-settings',
				'capability' => 'edit_posts',
				'redirect'   => false,
				'position'   => 2
			) );
			if( function_exists('acf_add_local_field_group') ):

				acf_add_local_field_group(array (
					'key' => 'group_591ac5ec20a48',
					'title' => 'Gateways',
					'fields' => array (
						array (
							'key' => 'field_591ac6c321a85',
							'label' => 'Gateways to disable',
							'name' => 'gateways_to_disable',
							'type' => 'repeater',
							'instructions' => '',
							'required' => 0,
							'conditional_logic' => 0,
							'wrapper' => array (
								'width' => '',
								'class' => '',
								'id' => '',
							),
							'collapsed' => '',
							'min' => 0,
							'max' => 0,
							'layout' => 'table',
							'button_label' => 'Add gateway',
							'sub_fields' => array (
								array (
									'key' => 'field_591ac6e621a86',
									'label' => 'Gateway ID',
									'name' => 'gateway_id',
									'type' => 'text',
									'instructions' => 'You can get gateway ID from <a href="admin.php?page=wc-settings&tab=checkout" target="_blank">here</a>',
									'required' => 0,
									'conditional_logic' => 0,
									'wrapper' => array (
										'width' => '',
										'class' => '',
										'id' => '',
									),
									'default_value' => '',
									'placeholder' => '',
									'prepend' => '',
									'append' => '',
									'maxlength' => '',
								),
								array (
									'key' => 'field_591ac73021a87',
									'label' => 'Currency codes',
									'name' => 'currency_codes',
									'type' => 'repeater',
									'instructions' => '',
									'required' => 0,
									'conditional_logic' => 0,
									'wrapper' => array (
										'width' => '',
										'class' => '',
										'id' => '',
									),
									'collapsed' => '',
									'min' => 0,
									'max' => 0,
									'layout' => 'table',
									'button_label' => 'Add currency',
									'sub_fields' => array (
										array (
											'key' => 'field_591ac7b921a88',
											'label' => 'Currency code',
											'name' => 'currency_code',
											'type' => 'text',
											'instructions' => '',
											'required' => 0,
											'conditional_logic' => 0,
											'wrapper' => array (
												'width' => '',
												'class' => '',
												'id' => '',
											),
											'default_value' => '',
											'placeholder' => '',
											'prepend' => '',
											'append' => '',
											'maxlength' => '',
										),
									),
								),
							),
						),
					),
					'location' => array (
						array (
							array (
								'param' => 'options_page',
								'operator' => '==',
								'value' => 'gateways-settings',
							),
						),
					),
					'menu_order' => 0,
					'position' => 'normal',
					'style' => 'default',
					'label_placement' => 'top',
					'instruction_placement' => 'label',
					'hide_on_screen' => '',
					'active' => 1,
					'description' => '',
				));

			endif;

		} else {
			add_action( 'admin_notices', array( $this, 'acf_not_found' ) );
		}
	}

	/**
	 * Check for WOOCS installed
	 */
	public function woocs_not_found() {
		$class   = 'notice notice-error';
		$message = __( 'Woocommerce currency switcher plugin not found', 'gateways' );

		printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), esc_html( $message ) );
	}

	/**
	 * Check for ACF PRO installed
	 */
	public function acf_not_found() {
		$class   = 'notice notice-error';
		$message = __( 'ACF PRO plugin not found', 'gateways' );

		printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), esc_html( $message ) );
	}

}

new WP_Gateways();