<?php
/**
 * Booster for WooCommerce - PDF Invoicing - Header
 *
 * @version 2.8.0
 * @author  Pluggabl LLC.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'WCJ_PDF_Invoicing_Header' ) ) :

class WCJ_PDF_Invoicing_Header extends WCJ_Module {

	/**
	 * Constructor.
	 *
	 * @version 2.3.0
	 */
	function __construct() {
		$this->id         = 'pdf_invoicing_header';
		$this->parent_id  = 'pdf_invoicing';
		$this->short_desc = __( 'Header', 'woocommerce-jetpack' );
		$this->desc       = '';
		parent::__construct( 'submodule' );
	}

}

endif;

return new WCJ_PDF_Invoicing_Header();
