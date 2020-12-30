<?php
/**
 * Booster for WooCommerce - Module - Checkout Files Upload
 *
 * @version 5.2.0
 * @since   2.4.5
 * @author  Pluggabl LLC.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'WCJ_Checkout_Files_Upload' ) ) :

class WCJ_Checkout_Files_Upload extends WCJ_Module {

	/**
	 * Constructor.
	 *
	 * @version 5.2.0
	 * @since   2.4.5
	 * @todo    styling options
	 */
	function __construct() {

		$this->id         = 'checkout_files_upload';
		$this->short_desc = __( 'Checkout Files Upload', 'woocommerce-jetpack' );
		$this->desc       = __( 'Let customers upload files on (or after) the checkout (1 file allowed in free version).', 'woocommerce-jetpack' );
		$this->desc_pro   = __( 'Let customers upload files on (or after) the checkout.', 'woocommerce-jetpack' );
		$this->link_slug  = 'woocommerce-checkout-files-upload';
		parent::__construct();

		if ( $this->is_enabled() ) {
			$this->init_settings();
			add_action( 'add_meta_boxes', array( $this, 'add_file_admin_order_meta_box' ) );
			add_action( 'init', array( $this, 'process_checkout_files_upload' ) );
			if ( 'yes' === wcj_get_option( 'wcj_checkout_files_upload_remove_on_empty_cart', 'no' ) ) {
				add_action( 'woocommerce_cart_item_removed', array( $this, 'remove_files_on_empty_cart' ), PHP_INT_MAX, 2 );
			}
			$total_number = apply_filters( 'booster_option', 1, wcj_get_option( 'wcj_checkout_files_upload_total_number', 1 ) );
			for ( $i = 1; $i <= $total_number; $i++ ) {
				if ( 'disable' != ( $the_hook = wcj_get_option( 'wcj_checkout_files_upload_hook_' . $i, 'woocommerce_before_checkout_form' ) ) ) {
					add_action( $the_hook, array( $this, 'add_files_upload_form_to_checkout_frontend' ), wcj_get_option( 'wcj_checkout_files_upload_hook_priority_' . $i, 10 ) );
				}
				if ( 'yes' === wcj_get_option( 'wcj_checkout_files_upload_add_to_thankyou_' . $i, 'no' ) ) {
					add_action( 'woocommerce_thankyou',   array( $this, 'add_files_upload_form_to_thankyou_and_myaccount_page' ), PHP_INT_MAX, 1 );
				}
				if ( 'yes' === wcj_get_option( 'wcj_checkout_files_upload_add_to_myaccount_' . $i, 'no' ) ) {
					add_action( 'woocommerce_view_order', array( $this, 'add_files_upload_form_to_thankyou_and_myaccount_page' ), PHP_INT_MAX, 1 );
				}
			}
			add_action( 'woocommerce_checkout_order_processed',        array( $this, 'add_files_to_order' ), PHP_INT_MAX, 2 );
			add_action( 'woocommerce_after_checkout_validation',       array( $this, 'validate_on_checkout' ) );
			add_action( 'woocommerce_order_details_after_order_table', array( $this, 'add_files_to_order_display' ), PHP_INT_MAX );
			add_action( 'woocommerce_email_after_order_table',         array( $this, 'add_files_to_order_display' ), PHP_INT_MAX );
			add_filter( 'woocommerce_email_attachments',               array( $this, 'add_files_to_email_attachments' ), PHP_INT_MAX, 3 );
		}
	}

	/**
	 * init_settings.
	 *
	 * @version 3.9.0
	 * @since   3.8.0
	 * @todo    (dev) (maybe) init settings on demand only
	 */
	function init_settings() {
		$this->templates_settings = wcj_get_option( 'wcj_checkout_files_upload_templates', array() );
		$this->templates_settings = wp_parse_args( $this->templates_settings, array(
			'order_before'       => '',
			'order_item'         => sprintf( __( 'File: %s', 'woocommerce-jetpack' ), '%file_name%' ) . '<br>',
			'order_after'        => '',
			'order_image_style'  => 'width:64px;',
			'email_before'       => '',
			'email_item'         => sprintf( __( 'File: %s', 'woocommerce-jetpack' ), '%file_name%' ) . '<br>',
			'email_after'        => '',
		) );
		$this->additional_admin_emails_settings = wcj_get_option( 'wcj_checkout_files_upload_additional_admin_emails', array() );
		$this->additional_admin_emails_settings = wp_parse_args( $this->additional_admin_emails_settings, array(
			'actions'   => array(),
			'do_attach' => 'yes',
		) );
		$this->checkout_files_upload_notice_type = wcj_get_option( 'wcj_checkout_files_upload_notice_type', 'wc_add_notice' );
	}

	/**
	 * add_files_to_email_attachments.
	 *
	 * @version 2.7.0
	 * @since   2.5.5
	 */
	function add_files_to_email_attachments( $attachments, $status, $order ) {
		if (
			( 'new_order'                 === $status && 'yes' === wcj_get_option( 'wcj_checkout_files_upload_attach_to_admin_new_order',           'yes' ) ) ||
			( 'customer_processing_order' === $status && 'yes' === wcj_get_option( 'wcj_checkout_files_upload_attach_to_customer_processing_order', 'yes' ) )
		) {
			$total_files = get_post_meta( wcj_get_order_id( $order ), '_' . 'wcj_checkout_files_total_files', true );
			for ( $i = 1; $i <= $total_files; $i++ ) {
				$attachments[] = wcj_get_wcj_uploads_dir( 'checkout_files_upload' ) . '/' . get_post_meta( wcj_get_order_id( $order ), '_' . 'wcj_checkout_files_upload_' . $i, true );
			}
		}
		return $attachments;
	}

	/**
	 * add_files_to_order_display.
	 *
	 * @version 3.8.0
	 * @since   2.4.7
	 * @todo    (maybe) somehow add `%image%` to emails also
	 */
	function add_files_to_order_display( $order ) {
		$order_id    = wcj_get_order_id( $order );
		$html        = '';
		$total_files = get_post_meta( $order_id, '_' . 'wcj_checkout_files_total_files', true );
		$do_add_img  = false;
		if ( 'woocommerce_email_after_order_table' === current_filter() ) {
			$template_before    = $this->templates_settings['email_before'];
			$template_after     = $this->templates_settings['email_after'];
			$template           = $this->templates_settings['email_item'];
		} else {
			$template_before    = $this->templates_settings['order_before'];
			$template_after     = $this->templates_settings['order_after'];
			$template           = $this->templates_settings['order_item'];
			$do_add_img         = ( false !== strpos( $template, '%image%' ) );
			if ( $do_add_img ) {
				$img_style = $this->templates_settings['order_image_style'];
			}
		}
		for ( $i = 1; $i <= $total_files; $i++ ) {
			$real_file_name = get_post_meta( $order_id, '_' . 'wcj_checkout_files_upload_real_name_' . $i, true );
			if ( '' != $real_file_name ) {
				$img = '';
				if ( $do_add_img ) {
					$order_file_name = wcj_get_wcj_uploads_dir( 'checkout_files_upload' ) . '/' . get_post_meta( $order_id, '_' . 'wcj_checkout_files_upload_' . $i, true );
					if ( @is_array( getimagesize( $order_file_name ) ) ) {
						$link = add_query_arg( array( 'wcj_download_checkout_file' => $i, '_wpnonce' => wp_create_nonce( 'wcj_download_checkout_file' ), 'wcj_download_checkout_file_order_id' => $order_id ) );
						$img  = '<img style="' . $img_style . '" src="' . $link. '"> ';
					}
				}
				$html .= wcj_handle_replacements( array(
					'%file_name%' => $real_file_name,
					'%image%'     => $img,
					), $template );
			}
		}
		if ( '' != $html ) {
			echo $template_before . $html . $template_after;
		}
	}

	/**
	 * add_notice.
	 *
	 * @version 3.9.0
	 * @since   3.9.0
	 */
	function add_notice( $message, $notice_type = 'success' ) {
		if ( 'wc_add_notice' === $this->checkout_files_upload_notice_type ) {
			wc_add_notice( $message, $notice_type );
		} elseif ( 'wc_print_notice' === $this->checkout_files_upload_notice_type ) {
			wc_print_notice( $message, $notice_type );
		}
	}

	/**
	 * validate_on_checkout.
	 *
	 * @version 3.9.0
	 * @since   2.4.5
	 */
	function validate_on_checkout( $posted ) {
		$total_number = apply_filters( 'booster_option', 1, wcj_get_option( 'wcj_checkout_files_upload_total_number', 1 ) );
		for ( $i = 1; $i <= $total_number; $i++ ) {
			if (
				'yes' === wcj_get_option( 'wcj_checkout_files_upload_enabled_' . $i, 'yes' ) &&
				$this->is_visible( $i ) &&
				'disable' != wcj_get_option( 'wcj_checkout_files_upload_hook_' . $i, 'woocommerce_before_checkout_form' )
			) {
				if ( 'yes' === wcj_get_option( 'wcj_checkout_files_upload_required_' . $i, 'no' ) && null === wcj_session_get( 'wcj_checkout_files_upload_' . $i ) ) {
					// Is required
					$this->add_notice( wcj_get_option( 'wcj_checkout_files_upload_notice_required_' . $i, __( 'File is required!', 'woocommerce-jetpack' ) ), 'error' );
				}
				if ( null === wcj_session_get( 'wcj_checkout_files_upload_' . $i ) ) {
					continue;
				}
				$file_name = wcj_session_get( 'wcj_checkout_files_upload_' . $i );
				$file_name = $file_name['name'];
				$file_type = '.' . pathinfo( $file_name, PATHINFO_EXTENSION );
				if ( '' != ( $file_accept = wcj_get_option( 'wcj_checkout_files_upload_file_accept_' . $i, '' ) ) ) {
					// Validate file type
					$file_accept = explode( ',', $file_accept );
					if ( is_array( $file_accept ) && ! empty( $file_accept ) ) {
						if ( ! in_array( $file_type, $file_accept ) ) {
							$this->add_notice( sprintf( wcj_get_option( 'wcj_checkout_files_upload_notice_wrong_file_type_' . $i,
								__( 'Wrong file type: "%s"!', 'woocommerce-jetpack' ) ), $file_name ), 'error' );
						}
					}
				}
				if ( $this->is_extension_blocked( $file_type ) ) {
					$this->add_notice( sprintf( wcj_get_option( 'wcj_checkout_files_upload_notice_wrong_file_type_' . $i,
						__( 'Wrong file type: "%s"!', 'woocommerce-jetpack' ) ), $file_name ), 'error' );
				}
			}
		}
	}

	/**
	 * add_file_admin_order_meta_box.
	 *
	 * @version 2.4.5
	 * @since   2.4.5
	 */
	function add_file_admin_order_meta_box() {
		$screen   = 'shop_order';
		$context  = 'side';
		$priority = 'high';
		add_meta_box(
			'wc-jetpack-' . $this->id,
			__( 'Booster', 'woocommerce-jetpack' ) . ': ' . __( 'Uploaded Files', 'woocommerce-jetpack' ),
			array( $this, 'create_file_admin_order_meta_box' ),
			$screen,
			$context,
			$priority
		);
	}

	/**
	 * create_file_admin_order_meta_box.
	 *
	 * @version 3.4.0
	 * @since   2.4.5
	 */
	function create_file_admin_order_meta_box() {
		$order_id = get_the_ID();
		$html = '';
		$total_files = get_post_meta( $order_id, '_' . 'wcj_checkout_files_total_files', true );
		$files_exists = false;
		for ( $i = 1; $i <= $total_files; $i++ ) {
			$order_file_name = get_post_meta( $order_id, '_' . 'wcj_checkout_files_upload_'           . $i, true );
			$real_file_name  = get_post_meta( $order_id, '_' . 'wcj_checkout_files_upload_real_name_' . $i, true );
			if ( '' != $order_file_name ) {
				$files_exists = true;
				$html .= '<p><a href="' . add_query_arg(
					array(
						'wcj_download_checkout_file_admin' => $order_file_name,
						'wcj_checkout_file_number'         => $i,
					) ) . '">' . $real_file_name . '</a></p>';
			}
		}
		if ( ! $files_exists ) {
			$html .= '<p><em>' . __( 'No files uploaded.', 'woocommerce-jetpack' ) . '</em></p>';
		} else {
			$html .= '<p><a style="color:#a00;" href="' . add_query_arg( 'wcj_download_checkout_file_admin_delete_all', $order_id ) . '"' . wcj_get_js_confirmation() . '>' .
				__( 'Delete all files', 'woocommerce-jetpack' ) . '</a></p>';
		}
		echo $html;
	}

	/**
	 * is_extension_blocked.
	 *
	 * @version 3.2.3
	 * @since   3.2.3
	 */
	function is_extension_blocked( $ext ) {
		if ( 'no' === wcj_get_option( 'wcj_checkout_files_upload_block_files_enabled', 'yes' ) ) {
			return false;
		}
		$ext = strtolower( $ext );
		if ( strlen( $ext ) > 0 && '.' === $ext[0] ) {
			$ext = substr( $ext, 1 );
		}
		$blocked_file_exts = wcj_get_option( 'wcj_checkout_files_upload_block_files_exts',
			'bat|exe|cmd|sh|php|php0|php1|php2|php3|php4|php5|php6|php7|php8|php9|ph|ph0|ph1|ph2|ph3|ph4|ph5|ph6|ph7|ph8|ph9|pl|cgi|386|dll|com|torrent|js|app|jar|pif|vb|vbscript|wsf|asp|cer|csr|jsp|drv|sys|ade|adp|bas|chm|cpl|crt|csh|fxp|hlp|hta|inf|ins|isp|jse|htaccess|htpasswd|ksh|lnk|mdb|mde|mdt|mdw|msc|msi|msp|mst|ops|pcd|prg|reg|scr|sct|shb|shs|url|vbe|vbs|wsc|wsf|wsh|html|htm'
		);
		$blocked_file_exts = explode( '|', $blocked_file_exts );
		return in_array( $ext, $blocked_file_exts );
	}

	/**
	 * add_files_to_order.
	 *
	 * @version 3.4.0
	 * @since   2.4.5
	 */
	function add_files_to_order( $order_id, $posted ) {
		$upload_dir = wcj_get_wcj_uploads_dir( 'checkout_files_upload' );
		if ( ! file_exists( $upload_dir ) ) {
			mkdir( $upload_dir, 0755, true );
		}
		$total_number = apply_filters( 'booster_option', 1, wcj_get_option( 'wcj_checkout_files_upload_total_number', 1 ) );
		for ( $i = 1; $i <= $total_number; $i++ ) {
			if ( null !== wcj_session_get( 'wcj_checkout_files_upload_' . $i ) ) {
				$session_data       = wcj_session_get( 'wcj_checkout_files_upload_' . $i );
				$file_name          = $session_data['name'];
				$ext                = pathinfo( $file_name, PATHINFO_EXTENSION );
				$download_file_name = $order_id . '_' . $i . '.' . $ext;
				$file_path          = $upload_dir . '/' . $download_file_name;
				$tmp_file_name      = $session_data['tmp_name'];
				$file_data          = file_get_contents( $tmp_file_name );
				if ( ! $this->is_extension_blocked( $ext ) ) { // should already be validated earlier, but just in case...
					file_put_contents( $file_path, $file_data );
				}
				unlink( $tmp_file_name );
				wcj_session_set( 'wcj_checkout_files_upload_' . $i, null );
				update_post_meta( $order_id, '_' . 'wcj_checkout_files_upload_' . $i, $download_file_name );
				update_post_meta( $order_id, '_' . 'wcj_checkout_files_upload_real_name_' . $i, $file_name );
			}
		}
		update_post_meta( $order_id, '_' . 'wcj_checkout_files_total_files', $total_number );
	}

	/**
	 * remove_files_on_empty_cart.
	 *
	 * @version 3.9.0
	 * @since   3.6.0
	 */
	function remove_files_on_empty_cart( $cart_item_key, $cart ) {
		if ( $cart->is_empty() ) {
			wcj_session_maybe_start();
			$any_files_removed = false;
			for ( $i = 1; $i <= apply_filters( 'booster_option', 1, wcj_get_option( 'wcj_checkout_files_upload_total_number', 1 ) ); $i++ ) {
				if ( null != ( $session_data = wcj_session_get( 'wcj_checkout_files_upload_' . $i ) ) ) {
					$any_files_removed = true;
					if ( isset( $session_data['tmp_name'] ) ) {
						unlink( $session_data['tmp_name'] );
					}
					wcj_session_set( 'wcj_checkout_files_upload_' . $i, null );
				}
			}
			if ( $any_files_removed && 'yes' === wcj_get_option( 'wcj_checkout_files_upload_remove_on_empty_cart_add_notice', 'no' ) ) {
				$this->add_notice( wcj_get_option( 'wcj_checkout_files_upload_notice_remove_on_empty_cart', __( 'Files were successfully removed.', 'woocommerce-jetpack' ) ) );
			}
		}
	}

	/**
	 * get_order_full_file_name.
	 *
	 * @version 3.8.0
	 * @since   3.8.0
	 * @todo    use where needed
	 */
	function get_order_full_file_name( $order_id, $file_num ) {
		return wcj_get_wcj_uploads_dir( 'checkout_files_upload' ) . '/' . get_post_meta( $order_id, '_' . 'wcj_checkout_files_upload_' . $file_num, true );
	}

	/**
	 * process_checkout_files_upload.
	 *
	 * @version 3.9.0
	 * @since   2.4.5
	 * @todo    add option for admin to delete files one by one (i.e. not all at once)
	 * @todo    `$this->additional_admin_emails_settings` - more customization options, e.g.: admin email, subject, content, from
	 */
	function process_checkout_files_upload() {
		wcj_session_maybe_start();
		$admin_email             = wcj_get_option( 'admin_email' );
		$admin_email_subject     = __( 'Booster for WooCommerce: Checkout Files Upload: %action%', 'woocommerce-jetpack' );
		$admin_email_content     = __( 'Order ID: %order_id%; File name: %file_name%', 'woocommerce-jetpack' );
		$total_number            = apply_filters( 'booster_option', 1, wcj_get_option( 'wcj_checkout_files_upload_total_number', 1 ) );
		// Remove file
		for ( $i = 1; $i <= $total_number; $i++ ) {
			if ( isset( $_POST[ 'wcj_remove_checkout_file_' . $i ] ) ) {
				if ( isset( $_POST[ 'wcj_checkout_files_upload_order_id_' . $i ] ) ) {
					$order_id = $_POST[ 'wcj_checkout_files_upload_order_id_' . $i ];
					$order_file_name = get_post_meta( $order_id, '_' . 'wcj_checkout_files_upload_' . $i, true );
					if ( '' != $order_file_name ) {
						$file_path = wcj_get_wcj_uploads_dir( 'checkout_files_upload' ) . '/' . $order_file_name;
						unlink( $file_path );
						$file_name = get_post_meta( $order_id, '_' . 'wcj_checkout_files_upload_real_name_' . $i, true );
						$this->add_notice( sprintf( wcj_get_option( 'wcj_checkout_files_upload_notice_success_remove_' . $i,
							__( 'File "%s" was successfully removed.', 'woocommerce-jetpack' ) ), $file_name ) );
						delete_post_meta( $order_id, '_' . 'wcj_checkout_files_upload_' . $i );
						delete_post_meta( $order_id, '_' . 'wcj_checkout_files_upload_real_name_' . $i );
						if ( in_array( 'remove_file', $this->additional_admin_emails_settings['actions'] ) ) {
							wp_mail(
								$admin_email,
								wcj_handle_replacements( array(
									'%action%'    => __( 'File Removed', 'woocommerce-jetpack' ),
								), $admin_email_subject ),
								wcj_handle_replacements( array(
									'%order_id%'  => $_POST[ 'wcj_checkout_files_upload_order_id_' . $i ],
									'%file_name%' => $file_name,
								), $admin_email_content )
							);
						}
						do_action( 'wcj_checkout_files_upload', 'remove_file', $_POST[ 'wcj_checkout_files_upload_order_id_' . $i ], $file_name );
					}
				} else {
					$session_data = wcj_session_get( 'wcj_checkout_files_upload_' . $i );
					$file_name    = $session_data['name'];
					unlink( $session_data['tmp_name'] );
					wcj_session_set( 'wcj_checkout_files_upload_' . $i, null );
					$this->add_notice( sprintf( wcj_get_option( 'wcj_checkout_files_upload_notice_success_remove_' . $i,
						__( 'File "%s" was successfully removed.', 'woocommerce-jetpack' ) ), $file_name ) );
					do_action( 'wcj_checkout_files_upload', 'remove_file', false, $file_name );
				}
			}
		}
		// Upload file
		for ( $i = 1; $i <= $total_number; $i++ ) {
			if ( isset( $_POST[ 'wcj_upload_checkout_file_' . $i ] ) ) {
				$file_name = 'wcj_checkout_files_upload_' . $i;
				if ( isset( $_FILES[ $file_name ] ) && '' != $_FILES[ $file_name ]['tmp_name'] ) {
					// Validate
					$is_valid = true;
					$real_file_name = $_FILES[ $file_name ]['name'];
					$file_type      = '.' . pathinfo( $real_file_name, PATHINFO_EXTENSION );
					if ( '' != ( $file_accept = wcj_get_option( 'wcj_checkout_files_upload_file_accept_' . $i, '' ) ) ) {
						// Validate file type
						$file_accept = explode( ',', $file_accept );
						if ( is_array( $file_accept ) && ! empty( $file_accept ) ) {
							if ( ! in_array( $file_type, $file_accept ) ) {
								$this->add_notice( sprintf( wcj_get_option( 'wcj_checkout_files_upload_notice_wrong_file_type_' . $i,
									__( 'Wrong file type: "%s"!', 'woocommerce-jetpack' ) ), $real_file_name ), 'error' );
								$is_valid = false;
							}
						}
					}
					if ( $this->is_extension_blocked( $file_type ) ) {
						$this->add_notice( sprintf( wcj_get_option( 'wcj_checkout_files_upload_notice_wrong_file_type_' . $i,
							__( 'Wrong file type: "%s"!', 'woocommerce-jetpack' ) ), $real_file_name ), 'error' );
						$is_valid = false;
					}
					if ( $is_valid ) {
						// To session
						$tmp_dest_file = tempnam( sys_get_temp_dir(), 'wcj' );
						move_uploaded_file( $_FILES[ $file_name ]['tmp_name'], $tmp_dest_file );
						$session_data = $_FILES[ $file_name ];
						$session_data['tmp_name'] = $tmp_dest_file;
						wcj_session_set( $file_name, $session_data );
						$this->add_notice( sprintf( wcj_get_option( 'wcj_checkout_files_upload_notice_success_upload_' . $i,
							__( 'File "%s" was successfully uploaded.', 'woocommerce-jetpack' ) ), $_FILES[ $file_name ]['name'] ) );
						// To order
						if ( isset( $_POST[ 'wcj_checkout_files_upload_order_id_' . $i ] ) ) {
							$this->add_files_to_order( $_POST[ 'wcj_checkout_files_upload_order_id_' . $i ], null );
							if ( in_array( 'upload_file', $this->additional_admin_emails_settings['actions'] ) ) {
								$attachments = ( 'no' === $this->additional_admin_emails_settings['do_attach'] ?
									array() : array( $this->get_order_full_file_name( $_POST[ 'wcj_checkout_files_upload_order_id_' . $i ], $i ) ) );
								wp_mail(
									$admin_email,
									wcj_handle_replacements( array(
										'%action%'    => __( 'File Uploaded', 'woocommerce-jetpack' ),
									), $admin_email_subject ),
									wcj_handle_replacements( array(
										'%order_id%'  => $_POST[ 'wcj_checkout_files_upload_order_id_' . $i ],
										'%file_name%' => $_FILES[ $file_name ]['name'],
									), $admin_email_content ),
									'',
									$attachments
								);
							}
						}
						// Action
						do_action( 'wcj_checkout_files_upload', 'upload_file',
							( isset( $_POST[ 'wcj_checkout_files_upload_order_id_' . $i ] ) ? $_POST[ 'wcj_checkout_files_upload_order_id_' . $i ] : false ),
							$_FILES[ $file_name ]['name'] );
					}
				} else {
					$this->add_notice( wcj_get_option( 'wcj_checkout_files_upload_notice_upload_no_file_' . $i,
						__( 'Please select file to upload!', 'woocommerce-jetpack' ) ), 'notice' );
				}
			}
		}
		// Admin file download
		if ( isset( $_GET['wcj_download_checkout_file_admin'] ) ) {
			$tmp_file_name = wcj_get_wcj_uploads_dir( 'checkout_files_upload' ) . '/' . $_GET['wcj_download_checkout_file_admin'];
			$file_name     = get_post_meta( $_GET['post'], '_' . 'wcj_checkout_files_upload_real_name_' . $_GET['wcj_checkout_file_number'], true );
			if ( wcj_is_user_role( 'administrator' ) || is_shop_manager() ) {
				header( "Expires: 0" );
				header( "Cache-Control: must-revalidate, post-check=0, pre-check=0" );
				header( "Cache-Control: private", false );
				header( 'Content-disposition: attachment; filename=' . $file_name );
				header( "Content-Transfer-Encoding: binary" );
				header( "Content-Length: ". filesize( $tmp_file_name ) );
				readfile( $tmp_file_name );
				exit();
			}
		}
		// Admin all files delete
		if ( isset( $_GET['wcj_download_checkout_file_admin_delete_all'] ) && ( wcj_is_user_role( 'administrator' ) || is_shop_manager() ) ) {
			$order_id    = $_GET['wcj_download_checkout_file_admin_delete_all'];
			$total_files = get_post_meta( $order_id, '_' . 'wcj_checkout_files_total_files', true );
			for ( $i = 1; $i <= $total_files; $i++ ) {
				if ( '' != ( $order_file_name = get_post_meta( $order_id, '_' . 'wcj_checkout_files_upload_' . $i, true ) ) ) {
					unlink( wcj_get_wcj_uploads_dir( 'checkout_files_upload' ) . '/' . $order_file_name );
				}
				delete_post_meta( $order_id, '_' . 'wcj_checkout_files_upload_'           . $i );
				delete_post_meta( $order_id, '_' . 'wcj_checkout_files_upload_real_name_' . $i );
			}
			delete_post_meta( $order_id, '_' . 'wcj_checkout_files_total_files' );
			wp_safe_redirect( remove_query_arg( 'wcj_download_checkout_file_admin_delete_all' ) );
			exit;
		}
		// User file download
		if ( isset( $_GET['wcj_download_checkout_file'] ) && isset( $_GET['_wpnonce'] ) && ( false !== wp_verify_nonce( $_GET['_wpnonce'], 'wcj_download_checkout_file' ) ) ) {
			$i = $_GET['wcj_download_checkout_file'];
			if ( ! empty( $_GET['wcj_download_checkout_file_order_id'] ) ) {
				$order_id = $_GET['wcj_download_checkout_file_order_id'];
				if ( ! ( $order = wc_get_order( $order_id ) ) ) {
					return;
				}
				if ( isset( $_GET['key'] ) ) {
					// Thank you page
					if ( ! $order->key_is_valid( $_GET['key'] ) ) {
						return;
					}
				} else {
					// My Account
					if ( ! wcj_is_user_logged_in() || $order->get_customer_id() != wcj_get_current_user_id() ) {
						return;
					}
				}
				$order_file_name = get_post_meta( $order_id, '_' . 'wcj_checkout_files_upload_' . $i, true );
				$tmp_file_name   = wcj_get_wcj_uploads_dir( 'checkout_files_upload' ) . '/' . $order_file_name;
				$file_name       = get_post_meta( $order_id, '_' . 'wcj_checkout_files_upload_real_name_' . $i, true );
			} else {
				$session_data    = wcj_session_get( 'wcj_checkout_files_upload_' . $i );
				$tmp_file_name   = $session_data['tmp_name'];
				$file_name       = $session_data['name'];
			}
			header( "Expires: 0" );
			header( "Cache-Control: must-revalidate, post-check=0, pre-check=0" );
			header( "Cache-Control: private", false );
			header( 'Content-disposition: attachment; filename=' . $file_name );
			header( "Content-Transfer-Encoding: binary" );
			header( "Content-Length: ". filesize( $tmp_file_name ) );
			readfile( $tmp_file_name );
			exit();
		}
	}

	/**
	 * is_visible.
	 *
	 * @version 3.6.0
	 * @since   2.4.7
	 */
	function is_visible( $i, $order_id = 0 ) {

		if ( apply_filters( 'wcj_checkout_files_always_visible_on_empty_cart', false ) && 0 == $order_id && WC()->cart->is_empty() ) {
			// Added for "One Page Checkout" plugin compatibility.
			return true;
		}

		// Include by user role
		$user_roles = wcj_get_option( 'wcj_checkout_files_upload_show_user_roles_' . $i, '' );
		if ( ! empty( $user_roles ) && ! in_array( wcj_get_current_user_first_role(), $user_roles ) ) {
			return false;
		}

		// Exclude by user role
		$user_roles = wcj_get_option( 'wcj_checkout_files_upload_hide_user_roles_' . $i, '' );
		if ( ! empty( $user_roles ) && in_array( wcj_get_current_user_first_role(), $user_roles ) ) {
			return false;
		}

		// Include by product id
		$products_in = wcj_get_option( 'wcj_checkout_files_upload_show_products_in_' . $i );
		if ( ! empty( $products_in ) ) {
			$do_skip_by_products = true;
			if ( 0 != $order_id ) {
				$the_order = wc_get_order( $order_id );
				$the_items = $the_order->get_items();
			} else {
				$the_items = WC()->cart->get_cart();
			}
			foreach ( $the_items as $cart_item_key => $values ) {
				if ( in_array( $values['product_id'], $products_in ) ) {
					$do_skip_by_products = false;
					break;
				}
			}
			if ( $do_skip_by_products ) return false;
		}

		// Exclude by product id
		$products_in = wcj_get_option( 'wcj_checkout_files_upload_hide_products_in_' . $i );
		if ( ! empty( $products_in ) ) {
			if ( 0 != $order_id ) {
				$the_order = wc_get_order( $order_id );
				$the_items = $the_order->get_items();
			} else {
				$the_items = WC()->cart->get_cart();
			}
			foreach ( $the_items as $cart_item_key => $values ) {
				if ( in_array( $values['product_id'], $products_in ) ) {
					return false;
				}
			}
		}

		// Include by product category
		$categories_in = wcj_get_option( 'wcj_checkout_files_upload_show_cats_in_' . $i );
		if ( ! empty( $categories_in ) ) {
			$do_skip_by_cats = true;
			if ( 0 != $order_id ) {
				$the_order = wc_get_order( $order_id );
				$the_items = $the_order->get_items();
			} else {
				$the_items = WC()->cart->get_cart();
			}
			foreach ( $the_items as $cart_item_key => $values ) {
				$product_categories = get_the_terms( $values['product_id'], 'product_cat' );
				if ( empty( $product_categories ) ) continue;
				foreach( $product_categories as $product_category ) {
					if ( in_array( $product_category->term_id, $categories_in ) ) {
						$do_skip_by_cats = false;
						break;
					}
				}
				if ( ! $do_skip_by_cats ) break;
			}
			if ( $do_skip_by_cats ) return false;
		}

		// Exclude by product category
		$categories_in = wcj_get_option( 'wcj_checkout_files_upload_hide_cats_in_' . $i );
		if ( ! empty( $categories_in ) ) {
			if ( 0 != $order_id ) {
				$the_order = wc_get_order( $order_id );
				$the_items = $the_order->get_items();
			} else {
				$the_items = WC()->cart->get_cart();
			}
			foreach ( $the_items as $cart_item_key => $values ) {
				$product_categories = get_the_terms( $values['product_id'], 'product_cat' );
				if ( empty( $product_categories ) ) continue;
				foreach( $product_categories as $product_category ) {
					if ( in_array( $product_category->term_id, $categories_in ) ) {
						return false;
					}
				}
			}
		}

		// Include by product tag
		$tags_in = wcj_get_option( 'wcj_checkout_files_upload_show_tags_in_' . $i );
		if ( ! empty( $tags_in ) ) {
			$do_skip_by_tags = true;
			if ( 0 != $order_id ) {
				$the_order = wc_get_order( $order_id );
				$the_items = $the_order->get_items();
			} else {
				$the_items = WC()->cart->get_cart();
			}
			foreach ( $the_items as $cart_item_key => $values ) {
				$product_tags = get_the_terms( $values['product_id'], 'product_tag' );
				if ( empty( $product_tags ) ) continue;
				foreach( $product_tags as $product_tag ) {
					if ( in_array( $product_tag->term_id, $tags_in ) ) {
						$do_skip_by_tags = false;
						break;
					}
				}
				if ( ! $do_skip_by_tags ) break;
			}
			if ( $do_skip_by_tags ) return false;
		}

		// Exclude by product tag
		$tags_in = wcj_get_option( 'wcj_checkout_files_upload_hide_tags_in_' . $i );
		if ( ! empty( $tags_in ) ) {
			if ( 0 != $order_id ) {
				$the_order = wc_get_order( $order_id );
				$the_items = $the_order->get_items();
			} else {
				$the_items = WC()->cart->get_cart();
			}
			foreach ( $the_items as $cart_item_key => $values ) {
				$product_tags = get_the_terms( $values['product_id'], 'product_tag' );
				if ( empty( $product_tags ) ) continue;
				foreach( $product_tags as $product_tag ) {
					if ( in_array( $product_tag->term_id, $tags_in ) ) {
						return false;
					}
				}
			}
		}

		return true;
	}

	/**
	 * maybe_get_image.
	 *
	 * @version 3.7.0
	 * @since   3.7.0
	 */
	function maybe_get_image( $link, $i, $order_id = 0 ) {
		if ( 'yes' === wcj_get_option( 'wcj_checkout_files_upload_form_template_field_show_images', 'no' ) ) {
			if ( 0 != $order_id && isset( $_GET['key'] ) && ( $order = wc_get_order( $order_id ) ) && $order->key_is_valid( $_GET['key'] ) ) {
				$order_file_name = get_post_meta( $order_id, '_' . 'wcj_checkout_files_upload_' . $i, true );
				$tmp_file_name   = wcj_get_wcj_uploads_dir( 'checkout_files_upload' ) . '/' . $order_file_name;
			} else {
				$session_data    = wcj_session_get( 'wcj_checkout_files_upload_' . $i );
				$tmp_file_name   = $session_data['tmp_name'];
			}
			if ( @is_array( getimagesize( $tmp_file_name ) ) ) {
				return '<img style="' . wcj_get_option( 'wcj_checkout_files_upload_form_template_field_image_style', 'width:64px;' ) . '" src="' . $link. '"> ';
			}
		}
		return '';
	}

	/**
	 * get_the_form.
	 *
	 * @version 4.2.0
	 * @since   2.5.0
	 */
	function get_the_form( $i, $file_name, $order_id = 0 ) {
		$html = '';
		$html .= '<form enctype="multipart/form-data" action="" method="POST">';
		$html .= wcj_get_option( 'wcj_checkout_files_upload_form_template_before', '<table>' );
		if ( '' != ( $the_label = wcj_get_option( 'wcj_checkout_files_upload_label_' . $i, '' ) ) ) {
			$template = wcj_get_option( 'wcj_checkout_files_upload_form_template_label',
				'<tr><td colspan="2"><label for="%field_id%">%field_label%</label>%required_html%</td></tr>' );
			$required_html = ( 'yes' === wcj_get_option( 'wcj_checkout_files_upload_required_' . $i, 'no' ) ) ?
				'&nbsp;<abbr class="required" title="required">*</abbr>' : '';
			$html .= str_replace(
				array( '%field_id%', '%field_label%', '%required_html%' ),
				array( 'wcj_checkout_files_upload_' . $i, $the_label, $required_html ),
				$template
			);
		}
		if ( '' == $file_name ) {
			$field_html = '<input type="file" name="wcj_checkout_files_upload_' . $i . '" id="wcj_checkout_files_upload_' . $i .
				'" accept="' . wcj_get_option( 'wcj_checkout_files_upload_file_accept_' . $i, '' ) . '">';
			$button_html = '<input type="submit"' .
				' class="button alt"' .
				' style="width:100%;"' .
				' name="wcj_upload_checkout_file_' . $i . '"' .
				' id="wcj_upload_checkout_file_' . $i . '"' .
				' value="'      . wcj_get_option( 'wcj_checkout_files_upload_label_upload_button_' . $i, __( 'Upload', 'woocommerce-jetpack' ) ) . '"' .
				' data-value="' . wcj_get_option( 'wcj_checkout_files_upload_label_upload_button_' . $i, __( 'Upload', 'woocommerce-jetpack' ) ) . '">';
		} else {
			$link        = add_query_arg( array( 'wcj_download_checkout_file' => $i, '_wpnonce' => wp_create_nonce( 'wcj_download_checkout_file' ), 'wcj_download_checkout_file_order_id' => $order_id ) );
			$field_html  = '<a href="' . $link . '">' . $this->maybe_get_image( $link, $i, $order_id ) . $file_name . '</a>';
			$button_html = '<input type="submit"' .
				' class="button"' .
				' style="width:100%;"' .
				' name="wcj_remove_checkout_file_' . $i . '"' .
				' id="wcj_remove_checkout_file_' . $i . '"' .
				' value="'      . wcj_get_option( 'wcj_checkout_files_upload_label_remove_button_' . $i, __( 'Remove', 'woocommerce-jetpack' ) ) . '"' .
				' data-value="' . wcj_get_option( 'wcj_checkout_files_upload_label_remove_button_' . $i, __( 'Remove', 'woocommerce-jetpack' ) ) . '">';
		}
		$template = wcj_get_option( 'wcj_checkout_files_upload_form_template_field',
			'<tr><td style="width:50%;max-width:50vw;">%field_html%</td><td style="width:50%;">%button_html%</td></tr>' );
		$html .= str_replace(
			array( '%field_html%', '%button_html%' ),
			array( $field_html, $button_html ),
			$template
		);
		$html .= wcj_get_option( 'wcj_checkout_files_upload_form_template_after', '</table>' );
		if ( 0 != $order_id ) {
			$html .= '<input type="hidden" name="wcj_checkout_files_upload_order_id_' . $i . '" value="' . $order_id . '">';
		}
		$html .= '</form>';
		return $html;
	}

	/**
	 * add_files_upload_form_to_thankyou_and_myaccount_page.
	 *
	 * @version 2.5.6
	 * @since   2.5.0
	 */
	function add_files_upload_form_to_thankyou_and_myaccount_page( $order_id ) {
		$html = '';
		$total_number = apply_filters( 'booster_option', 1, wcj_get_option( 'wcj_checkout_files_upload_total_number', 1 ) );
		$current_filter = current_filter();
		for ( $i = 1; $i <= $total_number; $i++ ) {
			if ( 'yes' === wcj_get_option( 'wcj_checkout_files_upload_enabled_' . $i, 'yes' ) && $this->is_visible( $i, $order_id ) ) {
				if (
					( 'yes' === wcj_get_option( 'wcj_checkout_files_upload_add_to_thankyou_'  . $i, 'no' ) && 'woocommerce_thankyou'   === $current_filter ) ||
					( 'yes' === wcj_get_option( 'wcj_checkout_files_upload_add_to_myaccount_' . $i, 'no' ) && 'woocommerce_view_order' === $current_filter )
				) {
					$file_name = get_post_meta( $order_id, '_' . 'wcj_checkout_files_upload_real_name_' . $i, true );
					$html .= $this->get_the_form( $i, $file_name, $order_id );
				}
			}
		}
		echo $html;
	}

	/**
	 * add_files_upload_form_to_checkout_frontend.
	 *
	 * @version 2.5.2
	 * @since   2.4.5
	 */
	function add_files_upload_form_to_checkout_frontend() {
		$this->add_files_upload_form_to_checkout_frontend_all();
	}

	/**
	 * add_files_upload_form_to_checkout_frontend_all.
	 *
	 * @version 3.4.0
	 * @since   2.5.2
	 */
	function add_files_upload_form_to_checkout_frontend_all( $is_direct_call = false ) {
		$html = '';
		$total_number = apply_filters( 'booster_option', 1, wcj_get_option( 'wcj_checkout_files_upload_total_number', 1 ) );
		if ( ! $is_direct_call ) {
			$current_filter = current_filter();
			$current_filter_priority = wcj_current_filter_priority();
		}
		for ( $i = 1; $i <= $total_number; $i++ ) {
			$is_filter_ok = ( $is_direct_call ) ? true : (
				$current_filter === wcj_get_option( 'wcj_checkout_files_upload_hook_' . $i, 'woocommerce_before_checkout_form' ) &&
				$current_filter_priority == wcj_get_option( 'wcj_checkout_files_upload_hook_priority_' . $i, 10 )
			);
			if ( 'yes' === wcj_get_option( 'wcj_checkout_files_upload_enabled_' . $i, 'yes' ) && $is_filter_ok && $this->is_visible( $i ) ) {
				$session_data = wcj_session_get( 'wcj_checkout_files_upload_' . $i );
				$file_name = ( null !== $session_data ? $session_data['name'] : '' );
				$html .= $this->get_the_form( $i, $file_name );
			}
		}
		echo $html;
	}

}

endif;

return new WCJ_Checkout_Files_Upload();
