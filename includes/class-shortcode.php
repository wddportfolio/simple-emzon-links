<?php

// If WordPress Gutenberg is not available, do not run.
if ( ! defined( 'ABSPATH' ) ) {
	return;
}


/**
 * Shortcode class.
 *
 * @since 0.1.0
 *
 * Class SALFWP_Shortcodes
 */
class SALFWP_Shortcodes {

	/**
	 * Contains an instance of this shortcode, if available.
	 *
	 * @since  0.1.0
	 * @var    SALFWP_Shortcodes $_instance If available, contains an instance of this shortcode.
	 */
	private static $_instance = null;

	/**
	 * Handle of primary shortcode.
	 *
	 * @since 0.1.0
	 * @var   string
	 */
	public $shortcode = 'salfwp';

	/**
	 * Register shortcode.
	 *
	 * @since  0.1.0
	 *
	 * @uses   SALFWP_Shortcodes::register_shortcode_type()
	 */
	function __construct()
	{
		add_shortcode( $this->shortcode, array($this, 'render_shortcode') );
	}

	/**
	 * Get instance of this class.
	 *
	 * @since  0.1.0
	 *
	 * @return SALFWP_Shortcodes
	 */
	public static function getInstance() {

		if ( null === self::$_instance ) {
			self::$_instance = new self;
		}

		return self::$_instance;

	}


	/**
	 * Display shortcode contents on frontend.
	 *
	 * @since  0.1.0
	 *
	 * @param array $attributes Block attributes.
	 *
	 * @return string
	 */
	public function render_shortcode( $attributes = array() ) {

		$atts = shortcode_atts( array(
	        'product_id'  => ''
	    ), $attributes, 'salfwp' );

	    extract($atts);

		$output = '';
		if ( !empty($product_id) )
		{
			$output .= '<div class="salfwp-products salfwp-list">
				<div class="salfw-products-inner">';
				$output .= self::get_product($product_id);
				$output .= '
				</div>
			</div>';
		}

		return $output;

		
	}

	private function get_product($productId) {
		
		// Sanitize Product ID
		$productId = sanitize_text_field( $productId );

		//Setting up cache request
		$this->cacheInstance = 'salfwp-' . $productId ;

		//Setting up Output
		$output = '';

		// delete_transient( $this->cacheInstance );

		$settings = $GLOBALS['SALFWP']->getSettings();

		// Choose a Lagacy or v5 Product Advertisement API
		if ( empty($settings['salfwp-api-mode']) || $settings['salfwp-api-mode']=="paapi5" ){
			require_once ( SALFWP_PATH . '/includes/api/paapi-v5.php' );
		}

		//Get from Instagram and Save it in Transient
		if ( false === ( $product = get_transient( $this->cacheInstance ) ) ) {

			// Choose a Lagacy or v5 Product Advertisement API
			if ( empty($settings['salfwp-api-mode']) || $settings['salfwp-api-mode']=="paapi5" ){

				// Getting Setting Values and Common Data
				$AWSAccessKeyId 	= trim( $settings['salfwp-access-key-id'] );
				$SecretAccessKey 	= trim( $settings['salfwp-access-key-secret'] );
				$AssociateTag		= trim( $settings['salfwp-tag-id'] );

				# Making a request class
				$papi = new AmazonPAApi();

				# Please add your access key here
				$papi->setAccessKey( $AWSAccessKeyId );

				# Please add your secret key here
				$papi->setSecretKey( $SecretAccessKey );

				# Please add your partner tag here
				$papi->setPartnerTag( $AssociateTag );

				# Please add your partner tag here
				$papi->setItem([$productId]);

				$product = $papi->getResults();
			}else{
				$product = $this->getProductRequest( $productId );
			}

			// do not set an empty transient - should help catch private or empty accounts.
			if ( $product['success']==true && !empty( $product ) ) {
			 	set_transient( $this->cacheInstance, $product, apply_filters( 'salfwp_cache_time', HOUR_IN_SECONDS * 2 ) );
			}
		}

		//If contain a WP Error Instance
		if ( !$product['success'] ) {
			$output = $product['message'];
		}else{
			$product = $product['data'];
			$output = '
			<div class="salfwp-product">
				<div class="salfwp-inner">
					<div class="salfwp-image">
						<a href="'.$product['DetailPageURL'].'" rel="nofollow" target="_blank"><img src="' . $product['Image'] . '"></a>
					</div>
					<div class="salfwp-info">
						<h3 class="salfwp-title"><a href="'.$product['DetailPageURL'].'" rel="nofollow" target="_blank">' . $product['Title'] . '</a></h3>
						<div class="salfwp-price">'.(empty($product['ListPrice'])?'&nbsp;':$product['ListPrice']).'</div>
						<a href="'.$product['DetailPageURL'].'" rel="nofollow" target="_blank" class="salfwp-button">'.esc_html__('Buy Now', 'salfwp').'</a>
					</div>
				</div>
			</div>
			';
		}
		return $output;
	}

	private function getProductRequest( $productId )
	{

		$settings = $GLOBALS['SALFWP']->getSettings();

		if (
			( isset( $settings['salfwp-access-key-id'] ) && !empty(trim($settings['salfwp-access-key-id'])) ) &&
			( isset( $settings['salfwp-access-key-secret'] ) && !empty(trim($settings['salfwp-access-key-secret'])) ) &&
			( isset( $settings['salfwp-tag-id'] ) && !empty(trim($settings['salfwp-tag-id'])) ) &&
			( !empty( trim( $productId ) ) )
		){

			// Getting Setting Values and Common Data
			$AWSAccessKeyId 	= trim( $settings['salfwp-access-key-id'] );
			$SecretAccessKey 	= trim( $settings['salfwp-access-key-secret'] );
			$AssociateTag		= trim( $settings['salfwp-tag-id'] );
			$Timestamp 			= gmdate("Y-m-d\TH:i:s") . ".000Z"; 
			$ResponseGroup 		= "Images,ItemAttributes,Offers,OfferFull,Reviews";

			// Making Request Params
			$params["AWSAccessKeyId"] 	= $AWSAccessKeyId;
			$params["AssociateTag"] 	= $AssociateTag;
			$params["Condition"] 		= "All";
			$params["ItemId"] 			= rawurlencode($productId);
			$params["Operation"] 		= "ItemLookup";
			$params["ResponseGroup"] 	= rawurlencode($ResponseGroup);
			$params["Service"] 			= "AWSECommerceService";
			$params["Timestamp"] 		= rawurlencode($Timestamp);
			$params["Version"] 			= "2013-08-01";
			$params["MerchantId"] 		= 'All';
			
			// Sorting Request Params
			ksort($params);
			
			// Preparing Query String
			$canonicalized_query = array();
			foreach ($params as $param=>$value){
				$param = str_replace("%7E", "~", $param);
				$value = str_replace("%7E", "~", $value);
				$canonicalized_query[] = $param."=".$value;
			}
			$String = implode('&', $canonicalized_query);

			// Generating Hash
			$PrependString = "GET\nwebservices.amazon.com\n/onca/xml\n" . $String;
			$Signature = rawurlencode(base64_encode(hash_hmac("sha256", $PrependString, $SecretAccessKey, True))); 

			// Finalize URL
			$SignedRequest = "http://webservices.amazon.com/onca/xml?" . $String . "&Signature=" . $Signature;
			// die ($SignedRequest);

			// Making Request
			$request = wp_remote_get( $SignedRequest, array( 
				'timeout' => 120, 
				'httpversion' => '1.1',
	            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/50.0.2661.87 Safari/537.36',
	            'Origin' => 'https://webservices.amazon.com',
	            'Referer' => 'https://webservices.amazon.com',
	            'Connection' => 'close',
			) );

			// Body
			$body    = wp_remote_retrieve_body( $request );

			if ( !is_wp_error( $body ) ) {
				// Parsing XML
				$bodyXML = simplexml_load_string($body);

				if (isset($bodyXML->Error)){

					$code = $text = '';
					if ( isset($bodyXML->Error->Code) ){
						$code = $bodyXML->Error->Code;
					}
					if ( isset($bodyXML->Error->Message) ){
						$text = $bodyXML->Error->Message;
					}

					$message = '<div class="salfwp-error">';
					$message .= sprintf("<strong>%s</strong>: %s", $code, $text);
					$message .= '</div>';
					
					return array( 'success' => false, 'message' => $message);
				}

				if ( isset($bodyXML->Items) && isset($bodyXML->Items->Item) ) {

					//Getting Product Item
					$item = $bodyXML->Items->Item;
					
					return array(
						'success' => true,
						'data'	  => array(
							'ASIN' => (string) $item->ASIN,
							'ParentASIN' => (string) $item->ParentASIN,
							'Title'			=> (string) $item->ItemAttributes->Title,
							'DetailPageURL' => (string) $item->DetailPageURL,
							'ListPrice' => (string) $item->ItemAttributes->ListPrice->FormattedPrice,
							'Image' => (string) $item->LargeImage->URL,
						)
					);
				}

				if ( isset($bodyXML->Items) && !isset($bodyXML->Items->Item) && isset($bodyXML->Items->Request->Errors) ) {

					$message = '<div class="salfwp-error">';

					$error = $bodyXML->Items->Request->Errors->Error;

					if ( isset($error->Code) ){
						$message .= '<strong>Code: <strong>' . $error->Code;
					}
					if ( isset($error->Message) ){
						$message .= '<strong>Message: <strong>' . $error->Message;
					}
					$message .= '</div>';
					
					return array( 'success' => false, 'message' => $message);
				}
			}
			return array( 'success' => false, 'message' => esc_html__('Something went wrong.', 'salfwp'));
		}else{
			return array( 'success' => false, 'message' => esc_html__('Item or Product Advertisement API not Configured', 'salfwp'));
		}
	}
}

$GLOBALS['SALFWP_Shortcodes'] = SALFWP_Shortcodes::getInstance();