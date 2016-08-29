<?php
    /**
     * Plugin Name: WBS Deny Shipping Classes
     * Version: 1.7.0.7
     * Depends: Weight Based Shipping for WooCommerce
     * Text Domain: woowbs-deny-classes
     */
     function wbswoo_init() {
          load_plugin_textdomain( 'woowbs-deny-classes', false, 'woowbs-deny-classes/languages' );
        }
        add_action('init', 'wbswoo_init');
    class WBS_Deny_Classes
    {
       
        const DENIED_ROW_CLASS = 'cart_item_denied';

        public function __construct()
        {
            add_action('init', array($this, 'init'));

            add_action('woocommerce_before_template_part', array($this, 'extendShippingMethodsTemplate'), 10, 4);

            global $wc_map_deprecated_filters;
            $cartItemClassFilter = (isset($wc_map_deprecated_filters['woocommerce_cart_item_class'])
                ? 'woocommerce_cart_item_class'
                : 'woocommerce_cart_table_item_class');
            add_filter($cartItemClassFilter, array($this, 'markDeniedCartItems'), 10, 3);

            add_action('wp_head', array($this, 'outputStyles'));
        }

        public function init()
        {
            if (!$this->canWork()) {
                return;
            }

            if (is_admin()) {
                foreach (WBS_Profile_Manager::instance()->profiles() as $profile) {
                    add_filter("woocommerce_settings_api_form_fields_{$profile->id}", array($this, 'injectFormFields'));
                }
            }

            add_action('woocommerce_product_meta_end', array($this, 'showWarning'));
        }

        public function injectFormFields($formFields)
        {
            if (!$this->canWork()) {
                return $formFields;
            }

            $shippingClasses = array();
            foreach (WC()->shipping->get_shipping_classes() as $class) {
                $shippingClasses[$class->slug] = $class->name;
            }

            $formFields['wbsdc_classes'] = array(
                'title'         => 'Deny Shipping Classes',
                'type'          => 'multiselect',
                'class'         => 'chosen_select',
                'options'       => $shippingClasses,
                'custom_attributes' => array (
                'data-placeholder' => _e( 'Select some shipping classes', 'wbswoo' )
                )
            );

            $formFields['wbsdc_message'] = array(
                'title' => 'Deny Message',
                'type' => 'textarea',
                'default' => _e( 'One  or more items in you cart cannot be shipped to the selected destination', 'wbswoo' )
            );

            $formFields['wbsdc_warning'] = array(
                'title'     => 'Warning Message',
                'type'      => 'textarea',
                'default'   => _e( 'This1 product can only be shipped in HCMC', 'wbswoo' )
            );

            return $formFields;
        }

        public function showWarning()
        {
            if (is_checkout()) return;

            global $product;

            if (!($product instanceof WC_Product)) {
                return;
            }

            $shippingClass = $product->get_shipping_class();
            if (!$shippingClass) {
                return;
            }

            foreach (WBS_Profile_Manager::instance()->profiles() as $profile) {
                $deniedClasses = $profile->get_option('wbsdc_classes');
               // $warningMessage = $profile->get_option('wbsdc_warning');
                 $warningMessage =  'This product can only be shipped in HCMC'; // english here
                 $warningmessage_custom = 'Sản phẩm này chỉ có thể giao ở Tp HCM'; //vietnamese here
                    $r_check = get_permalink();
                        if (strpos($r_check, '/vi/') !== false) {
                            $warningMessage = $warningmessage_custom;
                        }
                if ($deniedClasses && $warningMessage && in_array($shippingClass, $deniedClasses, true)) {
                    wc_get_template("notices/error.php", array(
                        'messages' => array($warningMessage),
                    ));
                    return;
                }
            }
        }

        public function extendShippingMethodsTemplate($templateName)
        {
            if ($templateName !== 'cart/cart-shipping.php' || !$this->canWork()) {
                return;
            }

            $deniedCartItemIds = array();
            $errorMessage = $this->checkCartContainsDeniedClasses($deniedCartItemIds);
            if ($errorMessage) {
                echo '<span class="wwdc-error">';
                    wc_get_template("notices/error.php", array(
                        'messages' => array($errorMessage),
                    ));
                echo '</span>';
            }

            $rowNumbers = join(',', array_map('intval', array_keys($deniedCartItemIds)));
            $checkoutPageUrl = get_permalink(wc_get_page_id('checkout'));
            echo
                '<script>
                    (function($) {

                        $(".wwdc-error-remove").remove();
                        $(".wwdc-error").addClass("wwdc-error-remove");

                        setTimeout(function() {
                            var deniedRowNumbers = [' . $rowNumbers . '];

                            jQuery(".shop_table.cart tbody tr").each(function(idx) {
                                jQuery(this)[deniedRowNumbers.indexOf(idx) > -1 ? "addClass" : "removeClass"]("' . self::DENIED_ROW_CLASS . '");
                            });

                            var $checkoutButtons =
                                $("input[type=submit].alt#place_order");

                            $checkoutButtons.addClass("-checkout-button");

                            $checkoutButtons.attr("disabled", deniedRowNumbers.length > 0);
                            $checkoutButtons[deniedRowNumbers.length > 0 ? "addClass" : "removeClass"]("disabled");

                            $checkoutButtons.each(function() {
                                if (deniedRowNumbers.length > 0) {
                                    var href = $(this).attr("href");
                                    if (href) {
                                        $(this).data("href-backup", href);
                                        $(this).removeAttr("href");
                                    }
                                }
                                else {
                                    var hrefBackup = $(this).data("href-backup");
                                    if (hrefBackup && !$(this).attr("href")) {
                                        $(this).attr("href", hrefBackup);
                                    }
                                }
                            });
                        }, 100);
                    })(jQuery)
                </script>';
        }

        public function markDeniedCartItems($itemRowClass, $cartItem, $cartItemKey)
        {
            if (!$this->canWork()) {
                return $itemRowClass;
            }

            static $deniedCartItemIds;
            if (!isset($deniedCartItemIds)) {
                $this->checkCartContainsDeniedClasses($deniedCartItemIds);
            }

            if (in_array($cartItemKey, $deniedCartItemIds, true)) {
                $itemRowClass .= ' '.self::DENIED_ROW_CLASS;
            }

            return $itemRowClass;
        }

        public function outputStyles()
        {
            if (!$this->canWork()) {
                return;
            }

            echo "<style>.cart_item_denied { background-color: #EAB8BA } </style>";
        }

        private function checkCartContainsDeniedClasses(&$deniedCartItemIds = null)
        {
            $message = null;
            $deniedCartItemIds = array();

            $choosenShippingMethodId = @WC()->session->chosen_shipping_methods[0];
            if (!$choosenShippingMethodId) {
                return $message;
            }

            $choosenProfile = null; {
                foreach (WBS_Profile_Manager::instance()->profiles() as $profile) {
                    if ($profile->id === $choosenShippingMethodId) {
                        $choosenProfile = $profile;
                        break;
                    }
                }

                if ($choosenProfile == null) {
                    return $message;
                }
            }

            $deniedClasses = array_filter((array)$choosenProfile->get_option('wbsdc_classes'));
            if (!$deniedClasses) {
                return $message;
            }

            $idx = 0;
            foreach (WC()->cart->get_cart() as $id => $item) {
                /** @var WC_Product_Simple $product */
                $product = $item['data'];
                $class = $product->get_shipping_class();

                if (in_array($class, $deniedClasses, true)) {

                    if (!isset($message)) {
                        $message_custom = 'Một trong số sản phẩm bạn chọn (đánh dấu đỏ) không thể giao hàng tới địa điểm của bạn, vui lòng quay lại giỏ hàng để thay đổi.'; //vietnamese here
                       $message = 'One or more items in you cart (in red) cannot be shipped to the selected destination. Please use Return to Cart button and edit order.'; //english here
                       // $message = $choosenProfile->get_option('wbsdc_message');
                        $r_check = get_permalink();
                        if (strpos($r_check, '/vi/') !== false) {
                            $message = $message_custom;
                        }
                        // $message = $message_custom;
                    }

                    if (func_num_args() === 0) {
                        break;
                    }

                    $deniedCartItemIds[$idx] = $id;
                }

                $idx++;
            }

            return $message;
        }

        private function canWork() {
            return class_exists('WooCommerce') && class_exists('WBS_Profile_Manager');
        }
    }

    new WBS_Deny_Classes();
?>