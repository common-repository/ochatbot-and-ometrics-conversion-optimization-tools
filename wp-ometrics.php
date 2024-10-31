<?php
	/**
		* Plugin Name: Ochatbot - AI Chatbot for eCommerce & Support
		* Plugin URI: https://www.ometrics.com/support/ometrics-wordpress-plugin/
		* Version: 1.2.02
		* Author: Ometrics, LLC
		* Author URI: https://www.ometrics.com/
		* WooCommerce optional - if used requires at least: 3.6.0
		* WooCommerce tested up to: 8.4.0
		* WordPress tested up to: 6.4.2
		* Description: Increase eCommerce sales and leads with Ochatbot - a free AI Chatbot.
		*  Integrates the Ometrics widget script into your WordPress site
		*  which allows all of the Ometrics tools (chatbot and other conversion
		*  optimization tools) to be used on your site.
		*  For WooCommerce sites, it also adds integration for conversion tracking,
		*  abandoned cart processing and order status tracking.
		*
		* License: GPL3
	*/
	
	/*  Copyright 2024 Ometrics
		
	*/
	
	if ( ! defined( 'ABSPATH' ) ) {
		exit; // Exit if accessed directly
	}
	
	/**
		* Current plugin version.
	*/
	if (!defined('WP_OMETRICS_VERSION'))
	define( 'WP_OMETRICS_VERSION', '1.2.02' );
	
	register_uninstall_hook(__FILE__, 'ometricsUninstall');
	
	function ometricsUninstall() {
		delete_option('ometrics_id');
		delete_option('ometrics_token');
		delete_option('ometrics_agent');
		delete_option('wp-ometrics_welcome_dismissed_key');
	}
	
	/**
		* WPOmetrics Class
	*/
	class WPOmetrics {
		/**
			* Constructor
		*/
		public function __construct() {
			
			// Plugin Details
			$this->plugin               = new stdClass;
			$this->plugin->name         = 'wp-ometrics'; // Plugin Name
			$this->plugin->displayName  = 'Ochatbot - AI Chatbot for eCommerce & Support'; // Plugin Name
			$this->plugin->version      = WP_OMETRICS_VERSION;
			$this->plugin->folder       = plugin_dir_path( __FILE__ );
			$this->plugin->url          = plugin_dir_url( __FILE__ );
			$this->plugin->db_welcome_dismissed_key = 'wp-ometrics_welcome_dismissed_key';
			
			//internal api version
			$this->ometrics_api_version = 'v1';
			//woocommerce api version
			$this->wp_api_version = 'v2';
			
			//review class
			require_once plugin_dir_path( __FILE__ ) .  '/includes/class-wpometrics_reviews.php';
			WPOmetrics_Modules_Reviews::init();
			
			// Hooks
			add_action( 'admin_init', array( $this, 'registerSettings' ) );
			add_action( 'admin_menu', array( $this, 'adminPanelsAndMetaBoxes' ) );
			add_action( 'admin_notices', array( $this, 'dashboardNotices' ) );
			add_action( 'wp_ajax_' . $this->plugin->name . '_dismiss_dashboard_notices', array( $this, 'dismissDashboardNotices' ) );
			
			// Frontend Hooks
			add_action( 'wp_head', array( $this, 'frontendHeader' ) );
			
			//conversion tracker
			add_action( 'woocommerce_thankyou', array( $this, 'checkoutComplete' ) );
			
			//custom API endpoints
			add_action( 'rest_api_init', array($this, 'registerRoutes')  );
			
			add_filter( 'woocommerce_rest_check_permissions', array( $this, 'allowRestCallForAuthenticatedPlugin' ), 90, 4 );
			add_action( 'wp_ajax_ometrics_submit', array($this, 'submitOmetricsSettings') );
			
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
			
		}
		
		/**
			* Enqueue admin scripts/styles
		*/
		function enqueue_scripts() {
			wp_enqueue_style( 'ometrics_admin_css', plugin_dir_url( __FILE__ ) .  'assets/css/admin.css', array(),  WP_OMETRICS_VERSION);
			wp_enqueue_script( 'ometrics_admin_js', plugin_dir_url( __FILE__ ) .  'assets/js/ometrics-settings.js', array(),  WP_OMETRICS_VERSION );
		}
		
		function registerRoutes() {
			register_rest_route( 'wp-ometrics/v1', '/order/(?P<order_id>.+)', array(
			'methods' => 'GET',
			'permission_callback' => array( $this, 'ometricsPermissionCheck' ),
			'callback' => array($this, 'ochatbotOrderStatus'),
			) );
			
			register_rest_route( 'wp-ometrics/v1', '/conversion', array(
			'methods' => 'GET',
			'permission_callback' => array( $this, 'ometricsPermissionCheck' ),
			'callback' => array($this, 'ochatbotConversion'),
			) );
			
			register_rest_route( 'wp-ometrics/v1', '/products', array(
			'methods' => 'GET',
			'permission_callback' => array( $this, 'ometricsPermissionCheck' ),
			'callback' => array($this, 'ochatbotProductList'),
			) );
			
			register_rest_route( 'wp-ometrics/v1', '/products/(?P<product_id>[\d]+)/variations', array(
			'methods' => 'GET',
			'permission_callback' => array( $this, 'ometricsPermissionCheck' ),
			'callback' => array($this, 'ochatbotProductVariation'),
			) );
			
			register_rest_route( 'wp-ometrics/v1', '/pages', array(
			'methods' => 'GET',
			'permission_callback' => array( $this, 'ometricsPermissionCheck' ),
			'callback' => array($this, 'ochatbotPageList'),
			) );
			
			register_rest_route( 'wp-ometrics/v1', '/posts', array(
			'methods' => 'GET',
			'permission_callback' => array( $this, 'ometricsPermissionCheck' ),
			'callback' => array($this, 'ochatbotPostList'),
			) );
			
		}
		
		function submitOmetricsSettings() {
			// Check nonce
			if (!check_ajax_referer( $this->plugin->name, $this->plugin->name.'_nonce' )) {
				$data = array (
				'error' => "Error saving data.  Please try again or <a href='https://www.ometrics.com/user/support'>contact Ometrics technical support</a>.",
				);
				
				wp_send_json($data);
			}
			else {
				//Proxy request to Ometrics (fixes CORS errors in ajax request)
				$url = '';
				if ($_REQUEST['type'] == 'connect') {
					$url = "https://www.ometrics.com/login/connectWPAccount";
				}
				elseif ($_REQUEST['type'] == 'disconnect') {
					$url = "https://www.ometrics.com/login/disconnectWPAccount";
				}
				elseif ($_REQUEST['type'] == 'forgot') {
					$url = 'https://www.ometrics.com/login/forgotWPPassword';
				}
				elseif ($_REQUEST['type'] == 'register') {
					$url = 'https://www.ometrics.com/user_registration/create_user_wp';
				}
				elseif ($_REQUEST['type'] == 'email') {
					$url = 'https://www.ometrics.com/user_registration/email_exists';
				}
				elseif ($_REQUEST['type'] == 'getStatus') {
					$url = 'https://www.ometrics.com/ochatbot_edit/getStatus';
				}
				elseif ($_REQUEST['type'] == 'setStatus') {
					$url = 'https://www.ometrics.com/ochatbot_edit/setStatus';
				}
				
				if (!$url) {
					$data = array (
					'error' => "Invalid Form. Please <a href='https://www.ometrics.com/user/support'>contact Ometrics technical support</a> or email us at <a href='support@ometrics.com'>support@ometrics.com</a>.",
					);
				}
				else {
					//$dataToSend = json_encode($_REQUEST);
					//initialize a new curl object
					$ch = curl_init();
					
					curl_setopt( $ch, CURLOPT_URL, trim($url) );
					curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, FALSE );
					curl_setopt( $ch, CURLOPT_RETURNTRANSFER, TRUE );
					
					curl_setopt( $ch, CURLOPT_POST, TRUE );

				
					//curl_setopt( $ch, CURLOPT_POSTFIELDS, $_REQUEST);
					/*$data = array(
						'email' => $_POST['email'],
						'password' => $_POST['password'],
						'domain' => $_POST['domain'],
						'woo_commerce' => $_POST['woo_commerce'],
						'type' => $_POST['type'],
						'action' => $_POST['action']
					);*/
						
					//$dataToSend = http_build_query($data); 
					
					curl_setopt( $ch, CURLOPT_POSTFIELDS, $_REQUEST);
					//curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/x-www-form-urlencoded'));
					
					//curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:multipart/form-data'));
					//curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
					
					//return response instead of outputting
					curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
					
					//execute the POST request
					$result = curl_exec($ch);
					
					//log_message('error','******** result '.print_r($result, true));
					
					if ( FALSE === $result ) {
						//Log::error("****** Ometrics Post Error = ". curl_error($ch));
						//log_message('error','******** ConnectWPAccount error - error:'.curl_error($ch));
						//return;//----------------------->
						$data = array (
							'error' => __( "Internal Error, please Please <a href='https://www.ometrics.com/user/support'>contact Ometrics technical support</a> or email us at <a href='support@ometrics.com'>support@ometrics.com</a>.", $this->plugin->name ),
						);
					}
					else {
						//Log::info("*** Ometrics Post OK = ". print_r($result, true));
						if ($_REQUEST['type'] == 'email') {
							//get result data
							$res = new \stdClass();
							if (trim($result) == '1' || trim($result) == '0') {
								$res->success = 'Successfully checked email.';
								$res->result = trim($result) == '1' ? '1' : '0';
							}
							else {
								$res->error = "Error getting email info.";
								$res->result = '0';
							}
						}
						else {
							$res = json_decode($result);
							if (json_last_error() !== JSON_ERROR_NONE) {
								$data = array (
									'error' => __( "Internal Error (".json_last_error_msg()."), please Please <a href='https://www.ometrics.com/user/support'>contact Ometrics technical support</a> or email us at <a href='support@ometrics.com'>support@ometrics.com</a>.", $this->plugin->name ),
								);
								wp_send_json($data);
								return;
							}
							//$res = $res->data;
						}
						
						if (isset($res->error) && $res->error) {
							//log_message('error','******** Ometrics post error - error:'. print_r($result, true));
							//Log::info("*********** Ometrics Post ERROR = ". print_r($result, true));
							$data = array (
								'error' => $res->error,
							);
						}
						else {
							//sucess
							// Save the ometrics id and token if returned
							if ($res->ometrics_id && $res->ometrics_token && $res->ometrics_agent) {
								update_option( 'ometrics_id', sanitize_text_field($res->ometrics_id) );
								update_option( 'ometrics_token', sanitize_text_field($res->ometrics_token) );
								update_option( 'ometrics_agent', sanitize_text_field($res->ometrics_agent) );
								update_option( $this->plugin->db_welcome_dismissed_key, 1 );
							}
							else {
								if ($res->ometrics_id) {
									update_option( 'ometrics_id', sanitize_text_field($res->ometrics_id) );
								}
								if ($res->ometrics_token) {
									update_option( 'ometrics_token', sanitize_text_field($res->ometrics_token) );
								}
								if ($res->ometrics_agent) {
									update_option( 'ometrics_agent', sanitize_text_field($res->ometrics_agent) );
								}
							}
							
							//disconnect - clear saved fields
							if ($_REQUEST['type'] == 'disconnect') {
								update_option( 'ometrics_id', '' );
								update_option( 'ometrics_token', '' );
								update_option( 'ometrics_agent', '' );
							}
							
							$data = array (
							'success' => $res->success,
							);
							//forgot passsword
							if ($res->no_account && $res->no_account == 1) {
								$data['no_account'] = 1;
							}
							//agent and status
							if ($res->ometrics_agent) {
								$data['ometrics_agent'] = $res->ometrics_agent;
							}
							if ($res->agent_status) {
								$data['agent_status'] = $res->agent_status;
							}
							if ($res->ometrics_id) {
								$data['ometrics_id'] = $res->ometrics_id;
							}
							if ($res->ometrics_token) {
								$data['ometrics_token'] = $res->ometrics_token;
							}
							if ($res->result) {
								$data['result'] = $res->result;
							}
						}
					}
				}
				
				wp_send_json($data);
				
			}
			
		}
		/**
			* Show relevant notices for the plugin
		*/
		function dashboardNotices() {
			global $pagenow;
			
			if ( !get_option( $this->plugin->db_welcome_dismissed_key ) ) {
				if ( ! ( $pagenow == 'options-general.php' && isset( $_GET['page'] ) && $_GET['page'] == 'wp-ometrics' ) ) {
					$setting_page = admin_url( 'options-general.php?page=' . $this->plugin->name );
					// load the notices view
					include_once( plugin_dir_path( __FILE__ ) . '/views/dashboard-notices.php' );
				}
			}
		}
		
		/**
			* Dismiss the welcome notice for the plugin
		*/
		function dismissDashboardNotices() {
			check_ajax_referer( $this->plugin->name . '-nonce', 'nonce' );
			// user has dismissed the welcome notice
			update_option( $this->plugin->db_welcome_dismissed_key, 1 );
			exit;
		}
		
		/**
			* Register Settings
		*/
		function registerSettings() {
			$plugin = plugin_basename(__FILE__);
			
			add_filter( "plugin_action_links_$plugin", array($this, 'add_plugin_links') );
			
			// Embed the Script on our Plugin's Option Page Only
			if ( isset($_GET['page']) && $_GET['page'] == 'wp-ometrics' ) {
				wp_enqueue_script('jquery');
				wp_enqueue_script( 'jquery-form' );
			}
			$args = array(
			'type' => 'string',
			'sanitize_callback' => 'sanitize_text_field',
			'default' => NULL,
			);
			register_setting( $this->plugin->name, 'ometrics_id', $args );
			register_setting( $this->plugin->name, 'ometrics_token', $args );
			register_setting( $this->plugin->name, 'ometrics_agent', $args );
		}
		
		public function add_plugin_links( $current_links ) {
			$additional = array(
  			'settings' => sprintf(
			'<a href="options-general.php?page=wp-ometrics">%s</a>',
			esc_html__( 'Settings', 'wp-ometrics' )
  			)
			);
			return array_merge( $additional, $current_links );
		}
		/**
			* Register the plugin settings panel
		*/
		function adminPanelsAndMetaBoxes() {
			add_submenu_page( 'options-general.php', $this->plugin->displayName, 'Ometrics Ochatbot', 'manage_options', $this->plugin->name, array( &$this, 'adminPanelConnect' ) );
		}
		
		/**
			* Output the Administration Panel
			* If no data saved, show Ometrics login page
			*		Login page - login to Ometrics and retrieve ometrics id and token
			* If data saved, show button to disconnect from Ometrics
			*		- Remove ometrics id and token
			* Save Ometrics ID and Token data from the Administration Panel into a WordPress option
		*/
		function adminPanelConnect() {
			// only admin user can access this page
			if ( !current_user_can( 'administrator' ) ) {
				echo '<p>' . __( 'Sorry, you are not allowed to access this page.', $this->plugin->name ) . '</p>';
				return;
			}
			
			// Save Settings
			if ( isset( $_REQUEST['submit'] ) ) {
				// Check nonce
				if ( !isset( $_REQUEST[$this->plugin->name.'_nonce'] ) ) {
					// Missing nonce
					$this->errorMessage = __( 'nonce field is missing. Settings NOT saved.', $this->plugin->name );
					} elseif ( !wp_verify_nonce( $_REQUEST[$this->plugin->name.'_nonce'], $this->plugin->name ) ) {
					// Invalid nonce
					$this->errorMessage = __( 'Invalid nonce specified. Settings NOT saved.', $this->plugin->name );
					} else {
					// Save
					// $_REQUEST has already been slashed by wp_magic_quotes in wp-settings
					// so do nothing before saving
					update_option( 'ometrics_id', sanitize_text_field($_REQUEST['ometrics_id']) );
					update_option( 'ometrics_token', sanitize_text_field($_REQUEST['ometrics_token']) );
					update_option( 'ometrics_agent', sanitize_text_field($_REQUEST['ometrics_agent']) );
					update_option( $this->plugin->db_welcome_dismissed_key, 1 );
					$this->message = __( 'Settings Saved.', $this->plugin->name );
				}
			}
			
			// Get latest settings
			$this->settings = array(
			'ometrics_id' => esc_html( wp_unslash( get_option( 'ometrics_id' ) ) ),
			'ometrics_token' => esc_html( wp_unslash( get_option( 'ometrics_token' ) ) ),
			'ometrics_agent' => esc_html( wp_unslash( get_option( 'ometrics_agent' ) ) ),
			);
			
			// Load Settings Form
			include_once( plugin_dir_path( __FILE__ ) . '/views/settingsConnect.php' );
		}
		
		/**
			* Outputs script to the frontend header
		*/
		function frontendHeader() {
			if ( !(is_admin() || is_feed() || is_robots() || is_trackback()) && ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) ) {
				$this->output( 'ometrics_id' );
				if ( class_exists( 'woocommerce' ) ) {
					//ajax remove item - don't process the cart update, wait for a page refresh
					if (!isset($_GET['removed_item'])) {
						$this->cartUpdate();
					}
				}
			}
		}
		
		public function checkoutComplete( $order_id ) {
			
			//Get order info
			$order = wc_get_order( $order_id );
			
			// bail out if not a valid instance
			if ( ! is_a( $order, 'WC_Order' ) ) {
				return;
			}
			
			$order_total    = $order->get_total() ? $order->get_total() : 0;
			$order_number   = $order->get_order_number();
			$items = $order->get_items();
			$num_items = $items ? count($items) : 0;
			
			// Output
		?>
		<!-- Ometrics - conversion tracking -->
		<script>
			window.ometricsAPI = window.ometricsAPI || [];
			
			window.ometricsAPI.push('o_trackEvent', 'purchase', {'revenue': <?php echo esc_js($order_total); ?>, num_items: <?php echo esc_js($num_items); ?> , transaction_id: '<?php echo esc_js($order_number); ?>' });
		</script>
		<!-- Ometrics - end conversion tracking -->
		<?php
		}
		
		/**
			* Outputs the given setting, if conditions are met
			*
			* @param string $setting Setting Name
			* @return output
		*/
		function output( $setting ) {
			// Ignore admin, feed, robots or trackbacks
			if ( is_admin() || is_feed() || is_robots() || is_trackback() ) {
				return;
			}
			
			// Get meta
			$meta = get_option( $setting );
			if ( empty( $meta ) ) {
				return;
			}
			if ( trim( $meta ) == '' ) {
				return;
			}
			
			// Output
		?>
		<script type="text/javascript" async src="https://ochatbot.ometrics.com/shopifyometrics/js/<?php echo esc_js(wp_unslash( $meta ));?>/ometrics.js"></script>
		<?php
		}
		
		function cartUpdate() {
			
			$upsellItem = '';
			$cart = WC()->cart->get_cart();
			$cartProcessed;
			if ($cart) {
				$cartProcessed = new stdClass;
				//preprocess cart - so, it's just the items, not shipping, tax, etc.
				$cartProcessed->subtotal_ex_tax = WC()->cart->subtotal_ex_tax;
				$cartProcessed->count = WC()->cart->get_cart_contents_count();
				$cartProcessed->items = [];
				//items
				foreach ( $cart as $cart_item_key => $cart_item ) {
					$item = new stdClass;
					$product = $cart_item['data'];
					if( version_compare( WC_VERSION, '3.0', '<' ) ){
						$product_id = $product->id; // Before version 3.0
					}
					else {
						$product_id = $product->get_id(); // For version 3 or more
					}
					$item->quantity = $cart_item['quantity'];
					$item->price = $product->get_price();
					$subtotal =  $item->price * $cart_item['quantity'];
					$item->url = $product->get_permalink( $cart_item );
					$item->image = wp_get_attachment_image_url( $product->get_image_id(), 'thumbnail' );
					$item->id = $product_id;
					$item->quantity = $cart_item['quantity'];
					$item->variant_id = $cart_item['variation_id'];
					$item->key = $cart_item['key'];
					$item->title = $product->get_name();
					
					$item->original_price = $item->price;
					$item->discounted_price = $item->price;
					$item->line_price = $subtotal;
					$item->original_line_price = $subtotal;
					$item->total_discount = 0;
					$item->sku = $product->get_sku();
					$item->grams = 0;
					$item->vendor = '';
					$item->taxable = 0;
					$item->product_id = $cart_item['product_id'];
					$item->final_price = $item->price;
					$item->final_line_price = $subtotal;
					$item->handle = '';
					$item->requires_shipping = 0;
					$cartProcessed->items[] = $item;
				}
			}
			
			$savedCart = WC()->session->get( 'ometricsCart' );
			
			if (!isset($cartProcessed) && $savedCart) {
				//no cart, but there was a saved cart
				//javascript to remove abandoned cart info from local storage
				WC()->session->set( 'ometricsCart' , null );
				// Output
			?>
			<script type="text/javascript">
				if (window.timerOchatbotCart)  clearInterval(window.timerOchatbotCart);
				window.timerOchatbotCart = setInterval(function() {
					if (typeof chatId !== 'undefined') {
						clearInterval(window.timerOchatbotCart);
						//clear cart
						window.localStorage.removeItem('ometricsCart'+chatId);
						window.localStorage.removeItem('ometricsCartDate'+chatId);
					}
				}, 100);
				
			</script>
			<?php
			}
			elseif (isset($cartProcessed) && $cartProcessed != $savedCart) {
				// cart has change from saved cart
				//javascript to update abandoned cart in local storage
				WC()->session->set( 'ometricsCart' , $cartProcessed );
				//check if an item has been added
				foreach ( $cartProcessed->items as $cart_item_key => $cart_item ) {
					//check if this item is in the saved cart
					$itemFound = false;
					if (isset($savedCart->items)) {
						foreach ( $savedCart->items as $saved_cart_item_key => $saved_cart_item ) {
							//check if this item is in the saved cart
							if ($saved_cart_item->id == $cart_item->id) {
								$itemFound = true;
								//write item to js for upsell
								break;
							}
							
						}
					}
					if (!$itemFound) {
						//didn't find this item - so, upsell it and then exit out of loop
						$upsellItem = $cart_item->id;
						break;
					}
				}
				// Output
			?>
			<script type="text/javascript">
				try {
					
					var cart = {};
					cart.total_price = <?php echo esc_js($cartProcessed->subtotal_ex_tax ? $cartProcessed->subtotal_ex_tax : 0); ?>;
					cart.item_count = <?php echo esc_js($cartProcessed->count ? $cartProcessed->count : 0); ?>;
					cart.items = [];
					//items
					<?php
						foreach ( $cartProcessed->items as $cart_item_key => $cart_item ) {
						?>
						var newItem = {};
						newItem.id = <?php echo esc_js($cart_item->id); ?>;
						newItem.quantity = <?php echo esc_js($cart_item->quantity); ?>;
						newItem.variant_id = <?php echo esc_js($cart_item->variant_id); ?>;
						newItem.key = '<?php echo esc_js($cart_item->key); ?>';
						newItem.title = '<?php echo esc_js($cart_item->title); ?>';
						newItem.price = <?php echo esc_js($cart_item->price); ?>;
						newItem.original_price = <?php echo esc_js($cart_item->price); ?>;
						newItem.discounted_price = <?php echo esc_js($cart_item->price); ?>;
						newItem.line_price = <?php echo esc_js($cart_item->line_price); ?>;
						newItem.original_line_price = <?php echo esc_js($cart_item->original_line_price); ?>;
						newItem.total_discount = 0;
						newItem.sku = '<?php echo esc_js($cart_item->sku); ?>';
						newItem.grams = 0;
						newItem.vendor = '';
						newItem.taxable = 0;
						newItem.product_id = <?php echo esc_js($cart_item->product_id); ?>;
						newItem.final_price = <?php echo esc_js($cart_item->price); ?>;
						newItem.final_line_price = <?php echo esc_js($cart_item->final_line_price); ?>;
						newItem.url = '<?php echo esc_js($cart_item->url); ?>';
						newItem.image = '<?php echo esc_js($cart_item->image); ?>';
						newItem.handle = '';
						newItem.requires_shipping = 0;
						
						cart.items.push(newItem);
						<?php
						}
					?>
					var wooUpsellItem = '<?php echo esc_js($upsellItem); ?>';
					if (window.timerOchatbotCart)  clearInterval(window.timerOchatbotCart);
					window.timerOchatbotCart = setInterval(function() {
						if (typeof chatId !== 'undefined') {
							clearInterval(window.timerOchatbotCart);
							//create new cart in local storage for this ochatbot
							window.localStorage.setItem('ometricsCart'+chatId, JSON.stringify(cart));
							d = new Date();
							window.localStorage.setItem('ometricsCartDate'+chatId, d.getTime());
							//
						}
					}, 100);
				}
				catch (e) {
					console.log("error adding to ometrics cart: ", e.message);
				}
			</script>
			<?php
			}
		}
		
		/**
			* Check if a given request has access to get items
			*
			* @param WP_REST_Request $request Full data about the request.
			* @return WP_Error|bool
		*/
		public function ometricsPermissionCheck( WP_REST_Request $request ) {
			//check token and id match
			if(!$request->get_param('ometrics_token') || !$request->get_param('ometrics_id') || $request->get_param('ometrics_token') !== get_option( 'ometrics_token' ) || $request->get_param('ometrics_id') !== get_option( 'ometrics_id' )) {
				return false;
			}
			
			//authenticated ochatbot api call - so, allow
			return true;
		}
		
		/**
			* Get the order status by id
			*
			* @param array $data Options for the function.
			* @return string|null order status data
		*/
		public function ochatbotOrderStatus(WP_REST_Request $request) {
			$orderNumber = $request['order_id'];
			//handle sequential order number plugin -
			if ( function_exists( 'wc_sequential_order_numbers' ) ) {
				// Try to convert number for Sequential Order Number.
				$orderId = wc_sequential_order_numbers()->find_order_by_order_number( $orderNumber );
				
				} elseif ( function_exists( 'wc_seq_order_number_pro' ) ) {
				// Try to convert number for Sequential Order Number Pro.
				$orderId = wc_seq_order_number_pro()->find_order_by_order_number( $orderNumber );
				
				} else {
				// Default to not converting order number.
				$orderId = $orderNumber;
			}
			
			if ( 0 === $orderId ) {
				$orderId = $orderNumber;
			}
			
			//lookup the order
			$order = wc_get_order( $orderId );
			
			if ( false === $order || ! is_object( $order ) ) {
				//order not found -
				//return the not found response
				return new WP_Error( 'no_order', 'Invalid Order', array( 'status' => 404 ) );
			}
			
			// Get real order ID from order object.
			$order_id = version_compare( WC_VERSION, '3.0.0', '<' ) ? $order->id : $order->get_id();
			if ( empty( $order_id ) ) {
				//order not found -
				//return the not found response
				return new WP_Error( 'no_order', 'Invalid Order', array( 'status' => 404 ) );
				
			}
			
			//Shipment Tracking Plugins
			// We can fetch tracking information for orders if the site has a shipment tracking plugin.
			//Currently supporting only "Shipment Tracking" from https://docs.woocommerce.com/document/shipment-tracking/
			$trackingInfo = [];
			
			// if custom meta exists: _wc_shipment_tracking_items
			$shipment_data = $order->get_meta('_wc_shipment_tracking_items');
			if ($shipment_data) {
				$trackingInfo = $shipment_data;
			}
			
			//get the order status and notes (and, optionally, the tracking number)
			$data = array('order_number' => $order->get_order_number(),
			'order_date' => wc_format_datetime( $order->get_date_created() ),
			'order_status' => wc_get_order_status_name( $order->get_status() ),
			'order_notes' => $order->get_customer_order_notes(),
			'tracking_info' => $trackingInfo,
			);
			
			// Create the response object
			$response = new WP_REST_Response( $data );
			
			return $response;
		}
		
		/**
			* Track orders and sales for Ochatbot in the plugin
			*
			* @param array $data Options for the function.
			* @return null
		*/
		public function ochatbotConversion(WP_REST_Request $request) {
			if (isset($request['revenue'])) {
				$revenue = $request['revenue'];
				//check for valid number
				if (!is_numeric($revenue)) {
					$revenue = 0;
				}
				$orders = get_option( 'wpometrics_orders', false );
				
				if ( ! $orders ) {
					$orders = 1;
					update_option( 'wpometrics_orders', $orders );
				}
				else {
					update_option( 'wpometrics_orders', $orders+1 );
				}
				//revenue
				$savedRevenue = get_option( 'wpometrics_revenue', false );
				
				if ( ! $savedRevenue ) {
					update_option( 'wpometrics_revenue', $revenue );
				}
				else {
					update_option( 'wpometrics_revenue', $savedRevenue + $revenue );
				}
			}
			if (isset($request['lead'])) {
				$leads = get_option( 'wpometrics_leads', false );
				
				if ( ! $leads ) {
					update_option( 'wpometrics_leads', 1 );
				}
				else {
					update_option( 'wpometrics_leads', $leads+1 );
				}
			}
			
			// Create the response object - empty
			$response = new WP_REST_Response();
			return $response;
		}
		
		public function allowRestCallForAuthenticatedPlugin($permission,  $context,  $object_id,  $type) {
			//allow reading products, variations, pages & posts for authenticated call
			global $wp;
			
			$url = home_url( $wp->request );
			if (!preg_match('/'.$this->plugin->name.'\/'.$this->ometrics_api_version.'\/products|'.$this->plugin->name.'\/'.$this->ometrics_api_version.'\/products\/[\d]+\/variations|'.$this->plugin->name.'\/'.$this->ometrics_api_version.'\/pages|'.$this->plugin->name.'\/'.$this->ometrics_api_version.'\/posts/', $url)) {
				return $permission;
			}
			//check token and id match the saved options
			if(!$_GET['ometrics_token'] || !$_GET['ometrics_id'] || $_GET['ometrics_token'] !== get_option( 'ometrics_token' ) || $_GET['ometrics_id'] !== get_option( 'ometrics_id' ) ) {
				return $permission;
			}
			
			//authenticated ochatbot api call - so, allow
			return true;
		}
		
		public function ochatbotProductList(WP_REST_Request $request) {
			//permissions have already been checked in the permission callback
			
			//call the existing product list api with the posted parameters
			$productRequest = new WP_REST_Request( 'GET', '/wc/'.$this->wp_api_version.'/products' );
			
			$productRequest->set_query_params( $request->get_query_params() );
			
			return rest_do_request( $productRequest );
			
		}
		
		public function ochatbotProductVariation(WP_REST_Request $request) {
			if (isset($request['product_id'])) {
				$productId = sanitize_text_field($request['product_id']);
				//permissions have already been checked in the permission callback
				
				//call the existing product list api with the posted parameters
				$productRequest = new WP_REST_Request( 'GET', '/wc/'.$this->wp_api_version.'/products/'.$productId.'/variations' );
				
				$productRequest->set_query_params( $request->get_query_params() );
				
				return rest_do_request( $productRequest );
			}
		}
		
		public function ochatbotPageList(WP_REST_Request $request) {
			//permissions have already been checked in the permission callback

			//call the existing product list api with the posted parameters
			$pageRequest = new WP_REST_Request( 'GET', '/wp/'.$this->wp_api_version.'/pages' );

			$pageRequest->set_query_params( $request->get_query_params() );

			return rest_do_request( $pageRequest );

		}
		
		public function ochatbotPostList(WP_REST_Request $request) {
			//permissions have already been checked in the permission callback

			//call the existing product list api with the posted parameters
			$postRequest = new WP_REST_Request( 'GET', '/wp/'.$this->wp_api_version.'/posts' );

			$postRequest->set_query_params( $request->get_query_params() );

			return rest_do_request( $postRequest );

		}
		
	}
	
	$ometrics = new WPOmetrics();
