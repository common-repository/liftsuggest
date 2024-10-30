<?php
/*
 * Plugin Name: LiftSuggest
 * Version: 1.1
 * Plugin URI: http://www.liftsuggest.com/
 * Description: Product recommendation Tool
 * Author: Tatvic Interactive
 * Author URI: http://www.liftsuggest.com/
 */

wp_enqueue_style( 'liftsuggest', '/wp-content/plugins/liftsuggest/css/style.css', false, false, 'all' );
wp_enqueue_script( 'jquery');

// Set Up admin page
if (is_admin()) {
    require_once("admin-page/display-liftsuggest.page.php");
    add_filter('wpsc_additional_pages', 'wpsc_add_modules_admin_pages',10, 2);

    function register_mysettings_before() {
    //register our settings
        register_setting( 'lift-settings-group', 'lift_enabled' );
        register_setting( 'lift-settings-group', 'lift_prod_enabled' );
        register_setting( 'lift-settings-group', 'lift_cart_enabled' );
        register_setting( 'lift-settings-group', 'lift_ub_enabled' );
        register_setting( 'lift-settings-group', 'lift_cart_btn' );
        register_setting( 'lift-settings-group', 'lift_token' );
        register_setting( 'lift-settings-group', 'lift_user_id' );
        register_setting( 'lift-settings-group', 'lift_domain' );
        register_setting( 'lift-settings-group', 'lift_message' );
        register_setting( 'lift-settings-group', 'lift_percentage' );
        register_setting( 'lift-settings-group', 'lift_ga_id' );
        register_setting( 'lift-settings-group', 'lift_ga_type' );
        register_setting( 'lift-settings-group', 'reco_on' );
        register_setting( 'lift-settings-group', 'no_of_reco','intval' );
    }
    function wpsc_add_modules_admin_pages($page_hooks, $base_page) {
        $page_hooks[] = add_submenu_page($base_page,__('LiftSuggest', 'wpsc'), __('LiftSuggest', 'wpsc'), 7, 'wpsc-liftsuggest','wpsc_display_liftsuggest_page');
        register_mysettings_before();
        return $page_hooks;
    }
}

$lift_enabled = get_option('lift_enabled');
$lift_prod_enabled = get_option('lift_prod_enabled');
$lift_cart_enabled = get_option('lift_cart_enabled');
$lift_ub_enabled = get_option('lift_ub_enabled');
global $wpdb, $wp_query, $wpsc_query, $wpsc_theme_path;
if($lift_enabled==1 && $lift_prod_enabled==1) {
    add_filter('wpsc_loop_end', 'show_reco_prod',11);    // Or add_filter('wpsc_product_addons', 'lift_showreco',12);
}elseif($lift_enabled==1 && $lift_prod_enabled==0) {
    add_filter('wpsc_loop_end', 'hide_reco_prod',11);    // Or add_filter('wpsc_product_addons', 'lift_showreco',12);
}
if($lift_enabled==1 && $lift_ub_enabled==1) {
    add_filter('wpsc_loop_end', 'show_ub_reco_prod',12);    // Or add_filter('wpsc_product_addons', 'lift_showreco',12);
}
if($lift_enabled==1 && $lift_cart_enabled==1) {
    add_filter('wpsc_before_form_of_shopping_cart', 'show_reco_cart',12);
}
if($lift_enabled==1) {
   add_action('wp_head','liftjs_code');    
}
// Recommendation Functions
// Show reco on cart page
function show_reco_cart() {
    $sku = get_SKUs();
    if($sku=="")
        $sku="SKUnotSet";

    $ga_id = get_option('lift_ga_id');
    if($ga_id=="") {
        echo "Please enter your Google Analytics Account Id in \"WP-Admin -> LiftSuggest Module Settings\" page. Also, Make sure that you have seleced appropriate Google Analytics Tracking Type.";
    }else {
        lift_showreco($sku,$cart=true);
    }

}

// Show reco on Product view page
function show_reco_prod() {
    global $wpdb, $wp_query, $wpsc_query, $wpsc_theme_path;
    $reco_on = get_option('reco_on');
    if($reco_on == 1)
        $sku = mysql_real_escape_string($wpsc_query->product['id']);
    else
        $sku = mysql_real_escape_string(wpsc_product_sku($wpsc_query->product['id']));
    $ga_id = get_option('lift_ga_id');
    if($sku=="") {
        $sku="SKUnotSet";
    }
    // Disable recommendations if Google Analytics Account id is provided by user
    if($ga_id=="") {
        echo "Please enter your Google Analytics Account Id in \"WP-Admin -> LiftSuggest Module Settings\" page. Also, Make sure that you have seleced appropriate Google Analytics Tracking Type.";
        if(isset($_GET["reco"]))
            $from_reco = 1;
    }else {
        lift_showreco($sku);
    }
}

// If Module enabled and Page view Reco is disabled
function hide_reco_prod() {
    global $wpdb, $wp_query, $wpsc_query, $wpsc_theme_path;
    $reco_on = get_option('reco_on');
    if($reco_on == 1)
        $sku = mysql_real_escape_string($wpsc_query->product['id']);
    else
        $sku = mysql_real_escape_string(wpsc_product_sku($wpsc_query->product['id']));
    $reco_type= "N";
    if(isset($_SESSION['reco_prods']) && $_SESSION['reco_prods']!=null) {
        if(in_array($sku,$_SESSION['reco_prods']))
            $reco_type= "R";
    }
    echo <<<_HTML_
			<div class="liftsuggest {act:'pv',sku:'{$sku}',reco:'{$reco_type}'}" style="display:none" ></div>
_HTML_;
}

function show_ub_reco_prod() {
    global $wpdb, $wp_query, $wpsc_query, $wpsc_theme_path;
    $reco_on = get_option('reco_on');
    if($reco_on == 1)
        $sku = mysql_real_escape_string($wpsc_query->product['id']);
    else
        $sku = mysql_real_escape_string(wpsc_product_sku($wpsc_query->product['id']));
    $ga_id = get_option('lift_ga_id');
    if($sku=="") {
        $sku="SKUnotSet";
    }
    // Disable recommendations if Google Analytics Account id is provided by user
    if($ga_id=="") {
        echo "Please enter your Google Analytics Account Id in \"WP-Admin -> LiftSuggest Module Settings\" page. Also, Make sure that you have seleced appropriate Google Analytics Tracking Type.";
        if(isset($_GET["reco"]))
            $from_reco = 1;
    }else {
         lift_showreco($sku,"",true);
    }
}
// Show recommendations
function lift_showreco($sku,$cart="",$ub=false) {
    if((wpsc_is_single_product() || $cart==true) && $sku != "") {
        $is_error = false;
        $lifttoken = mysql_real_escape_string(get_option('lift_token'));
        $liftuserid = mysql_real_escape_string(get_option('lift_user_id'));
        $liftdomain = mysql_real_escape_string(get_option('lift_domain'));
        $no_of_reco = mysql_real_escape_string(get_option('no_of_reco'));
        $add_to_cart = mysql_real_escape_string(get_option('lift_cart_btn'));
        $show_perc = mysql_real_escape_string(get_option('lift_percentage'));
        $custom_message = trim(mysql_real_escape_string(get_option('lift_message')));

        if($no_of_reco<=0 || $no_of_reco>15) {
            $no_of_reco=3;
            update_option('no_of_reco', 3);
        }

        if($ub==true) {
            $url = "http://www.liftsuggest.com/index.php/rest_c_ub/user/token/".$lifttoken."/custid/$liftuserid/prodsku/$sku/limit/$no_of_reco/format/json/domain/$liftdomain";
        }else {
            $url = "http://www.liftsuggest.com/index.php/rest_c/user/token/".$lifttoken."/custid/$liftuserid/prodsku/$sku/limit/$no_of_reco/format/json/domain/$liftdomain";
        }
        $curl_ob = curl_init();

        curl_setopt($curl_ob, CURLOPT_URL, $url);
        curl_setopt($curl_ob, CURLOPT_RETURNTRANSFER, 1);

        $response = curl_exec($curl_ob);

        curl_close($curl_ob);

        $result = array();
        $result = json_decode($response,true);
        
        if(isset($result['error'])) {
            $is_error = true;
        }
        if($is_error==false) {
            foreach ($result as $key=>$value) {
                if(is_array($value) && count($value)>0) {
                    foreach ($value as $key1 => $value1) {
                        if($key1 == "products") {
                            if($ub==true){
                                $reco = Array('sku'=>Array('sku'=>$value1[0]['sku'][0]));
                                //print_r($value1);
                            }else{
                                $reco = $value1;
                            }
                        }elseif($key1 == "popular_perc") {
                            $view_perc = $value1;
                        }
                    }
                }
            }
            // Set up variables for GA Tracking
            $reco_type= "N";
            if(isset($_SESSION['reco_prods']) && $_SESSION['reco_prods']!=null) {
                if(in_array($sku,$_SESSION['reco_prods'])){
                    $reco_type= "R";
                }
            }

            if(!isset($_SESSION['reco_prods'])) {
                $_SESSION['reco_prods'] = array();
            }

            foreach($reco as $key1=>$value1) {
                    $reco_group .= get_prod_info($value1['sku'],$cart,$add_to_cart,$ub);
            }

            if($reco_group != '') {
                if($ub==true) {
                    $header = <<<_HTML_
<div class="prod_container ubliftsuggest {act:'pv',sku:'{$sku}',reco:'UB{$reco_type}'}" >
_HTML_;
                }else if($cart==true) {
                        $header = <<<_HTML_
<div class="prod_container liftsuggest {act:'cv',sku:'{$sku}',reco:'{$reco_type}'}">
_HTML_;
                    }else {
                        $header = <<<_HTML_
<div class="prod_container liftsuggest {act:'pv',sku:'{$sku}',reco:'{$reco_type}'}">
_HTML_;
                    }
                $default_message = "Customers who bought above product(s) also bought these";

                if($show_perc == 1 && $custom_message != '') {
                    $custom_message = $view_perc . " " . $custom_message;
                } else if($show_perc == 0 && $custom_message == '') {
                        $custom_message = $default_message;
                    }else if($show_perc == 0 && $custom_message != '') {
                            $custom_message = $custom_message;
                        }  else {
                            $custom_message = $view_perc . " " . $default_message;
                        }
                if($ub==true) {
                    $header = $header."<div class='reco_msg'>What other customers bought after viewing this product?</div>";
                    echo $header . $reco_group . "</div><br clear:all;/>";
                }else {
                    $header = $header."<div class='reco_msg'>".$custom_message."</div>";
                    echo $header . $reco_group . "</div><br clear:all;/>";
                }                
            }
        }else {
            echo "<div class='error_msg'>Sorry, We are not able to generate recommendation for your store. There are some incorrect information provided in plugin configuration.Please Check your configurations in Admin panel</div>";
        }
    }
}

function get_prod_info($sku,$cart="",$cart_btn="",$ub="") {
    global $wpdb;
    $prod_id = $wpdb->get_var("SELECT product_id FROM ".WPSC_TABLE_PRODUCTMETA." WHERE `meta_value`='{$sku}' AND `meta_key`='sku' LIMIT 1");
    if($prod_id!='' || $prod_id!=null) {
        $qry = "SELECT * FROM ".WPSC_TABLE_PRODUCT_LIST." WHERE id=$prod_id";
        $file_data = $wpdb->get_row($qry);

        $product_image = $wpdb->get_var("SELECT `image` FROM `".WPSC_TABLE_PRODUCT_IMAGES."` WHERE `id`=$file_data->image LIMIT 1");
        $category_path = WPSC_IMAGE_DIR.basename($product_image);
        $category_url = WPSC_IMAGE_URL.basename($product_image);
        $produrl = wpsc_product_url($prod_id);
        if(file_exists($category_path) && is_file($category_path)) {
            $prod_img = "<a href=".$produrl."><img src='".$category_url."' alt='".$file_data->name."' title='".$file_data->name."' width=75 height=75 /></a>";
        } else {
            $image_path = WPSC_URL."/images/no-image-uploaded.gif";
            $prod_img = "<a href=".$produrl."><img src='".$image_path."' alt='".$file_data->name."' title='".$file_data->name."' width=75 height=75 /></a>";
        }

        $prod_name = "<a href=".$produrl.">".$file_data->name."</a>";

        $prod_price = get_prod_price($file_data);
        //$add_to_cart = wpsc_add_to_cart_button($prod_id);
        if($cart_btn==1) {
            $add_to_cart = cust_wpsc_add_to_cart_button($prod_id,$cart,true);
        }
        //            if(isset($_SESSION['reco_prods']))
        if(!in_array($sku,$_SESSION['reco_prods'])) {
            array_push($_SESSION['reco_prods'],$sku);
        }
        if($ub==true) {
            $reco_type="UBR";
        }else {
            $reco_type="R";
        }
        $output = <<<_html_
			<div class="individual_prod lsrecommendations {act:'oc',sku:'{$sku}',reco:'{$reco_type}'}">
			<div class="prod_img">$prod_img</div>
			<div class="prod_name">$prod_name</div>
			<div class="prod_price">Price: $prod_price</div>
_html_;
        if($cart_btn==1) {
            $output .= <<<_html_
			<div class="add_to_cart">$add_to_cart</div>
_html_;
        }
        $output .= <<<_html_
            </div>
_html_;
    }
    return $output;
}

function get_prod_price($file_data) {
    global $wpsc_query;
    $price = calculate_product_price($file_data->id, $file_data->first_variations, true);
    if(($file_data->product['special_price'] > 0) && (($file_data->product['price'] - $file_data->product['special_price']) >= 0) && ($variations_output[1] === null)) {
        $output = nzshpcrt_currency_display($price, $file_data->product['notax'],true,$file_data->product['id']);
    } else {
        $output = nzshpcrt_currency_display($price, $file_data->product['notax'], true);
    }
    $output = apply_filters('wpsc_price_display_changer', $output);
    return $output;
}

function get_SKUs() {
    global $wpsc_cart;
    $reco_on = get_option('reco_on');
    $sku = "";
    foreach($wpsc_cart->cart_items as $crt) {
    //                  //$prod_sku = wpsc_product_sku($crt->product_id);
    //                  $prod_sku = $crt->product_id;
        if($reco_on == 1)
            $prod_sku = $crt->product_id;
        else
            $prod_sku = wpsc_product_sku($crt->product_id);
        if($prod_sku != "") {
            if($sku == "")
                $sku .= $prod_sku;
            else
                $sku .= ",".$prod_sku;
        }
    }
    return $sku;
}

function cust_wpsc_add_to_cart_button($product_id,$place ,$replaced_shortcode = false) {
    global $wpdb;
    if ($product_id > 0) {
        if(function_exists('wpsc_theme_html')) {
            $product = $wpdb->get_row("SELECT * FROM ".WPSC_TABLE_PRODUCT_LIST." WHERE id = ".$product_id." LIMIT 1", ARRAY_A);
            //this needs the results from the product_list table passed to it, does not take just an ID
            $wpsc_theme = wpsc_theme_html($product);
        }

        // grab the variation form fields here
        $variations_processor = new nzshpcrt_variations;
        $variations_output = $variations_processor->display_product_variations($product_id,false, false, false);

        if($place==true)
            $output .= "<form onsubmit='submitform(this);'  action='' method='post'>";
        else
            $output .= "<form onsubmit='submitform(this);return false;'  action='' method='post'>";

        if($variations_output != '') { //will always be set, may sometimes be an empty string
            $output .= "           <p>".$variations_output."</p>";
        }
        $output .= "<input type='hidden' name='wpsc_ajax_action' value='add_to_cart' />";
        $output .= "<input type='hidden' name='product_id' value='".$product_id."' />";
        $output .= "<input type='hidden' name='item' value='".$product_id."' />";
        if(isset($wpsc_theme) && is_array($wpsc_theme) && ($wpsc_theme['html'] !='')) {
            $output .= $wpsc_theme['html'];
        } else {
            $output .= "<input type='submit' id='product_".$product['id']."_submit_button' class='wpsc_buy_button' name='Buy' value='".__('Add To Cart', 'wpsc')."'  />";
        }
        $output .= '</form>';
        if($replaced_shortcode == true) {
            return $output;
        } else {
            echo $output;
        }
    }
}

function liftjs_code($pagetracker_type=1) {
// Retrive Google Analytics Type and Google Analytics Id
    $ga_type = get_option('lift_ga_type');
    $ga_acc = get_option('lift_ga_id');
    // JS request to liftsuggest
    echo <<<_HTML_
<script type="text/javascript">
window.onload = function() {
    var s = document.createElement('script');
    s.type = 'text/javascript';
    s.async = true;
    s.src = 'http://www.liftsuggest.com/js/liftsuggest.js?gv={$ga_type}&uaid={$ga_acc}'; //liftsuggest tracking script for GA
    var x = document.getElementsByTagName('script')[0];
    x.parentNode.insertBefore(s, x);
}
</script>
_HTML_;
}

?>