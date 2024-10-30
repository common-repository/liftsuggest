<?php
/**
 * WP eCommerce edit and add liftsuggest group page functions
 *
 * These are the main WPSC Admin functions
 *
 * @package wp-e-commerce
 * @since 3.7
 */

function wpsc_display_liftsuggest_page() {
    	$columns = array(
		'title' => __('Name', 'wpsc'),
		'edit' => __('Edit', 'wpsc'),
	);
        lift_settings_page();
}

function lift_settings_page() {
?>
<div class="wrap">
<h2>LiftSuggest</h2>

<form method="post" action="options.php">
    <?php settings_fields( 'lift-settings-group' );
    $no_of_reco = get_option('no_of_reco');
    if($no_of_reco<=0 || $no_of_reco>20){
         update_option('no_of_reco', 3);
    }

    if(get_option('lift_enabled')==1){
        $selected_yes = "selected='selected'";
        $selected_no = "";
    }else{
        $selected_yes = "";
        $selected_no = "selected='selected'";
    }

    if(get_option('lift_prod_enabled')==1){
        $prod_yes = "selected='selected'";
        $prod_no = "";
    }else{
        $prod_yes = "";
        $prod_no = "selected='selected'";
    }

    if(get_option('lift_ub_enabled')==1){
        $ub_yes = "selected='selected'";
        $ub_no = "";
    }else{
        $ub_yes = "";
        $ub_no = "selected='selected'";
    }

    if(get_option('reco_on')==0){
        $sku_yes = "selected='selected'";
        $sku_no = "";
    }else{
        $sku_yes = "";
        $sku_no = "selected='selected'";
    }

    if(get_option('lift_cart_enabled')==1){
        $cart_yes = "selected='selected'";
        $cart_no = "";
    }else{
        $cart_yes = "";
        $cart_no = "selected='selected'";
    }

    if(get_option('lift_cart_btn')==1){
        $cart_btn_yes = "selected='selected'";
        $cart_btn_no = "";
    }else{
        $cart_btn_yes = "";
        $cart_btn_no = "selected='selected'";
    }

    if(get_option('lift_percentage')==1){
        $percentage_yes = "selected='selected'";
        $percentage_no = "";
    }else{
        $percentage_yes = "";
        $percentage_no = "selected='selected'";
    }

    if(get_option('lift_ga_type')==1){
        $ga_type_1 = "selected='selected'";
        $ga_type_0 = "";
    }elseif(get_option('lift_ga_type')==0){
        $ga_type_1 = "";
        $ga_type_0 = "selected='selected'";
    }
    ?>
    <table class="form-table">

        <tr valign="top">
            <th scope="row" colspan="2"><h3>LiftSuggest Module Settings</h3>Following settings are mandetory for enble module
            </th>
        </tr>

        <tr valign="top">
        <th scope="row">Enable LiftSuggest</th>
        <td><?php   echo "<select name='lift_enabled'>";
                    echo "<option value='1' $selected_yes>Yes</option>";
                    echo "<option value='0' $selected_no>No</option>";
                    echo "</select>";                    
            ?>
        </td>
        </tr>

        <tr valign="top">
        <th scope="row">LiftSuggest Token</th>
        <td><input type="text" name="lift_token" value="<?php echo get_option('lift_token'); ?>" /><br />Enter Token which you recieved in mail while registration with LiftSuggest</td>
        </tr>

        <tr valign="top">
        <th scope="row">LiftSuggest UserId</th>
        <td><input type="text" name="lift_user_id" value="<?php echo get_option('lift_user_id'); ?>" /><br />Enter UserId which you recieved in mail while registration with LiftSuggest</td>
        </tr>

        <tr valign="top">
        <th scope="row">No. of Reco</th>
        <td><input type="text" name="no_of_reco" value="<?php echo get_option('no_of_reco'); ?>" /><br />Must be between 1 and 20</td>
        </tr>

        <tr valign="top">
        <th scope="row">Domain Name</th>
        <td><input type="text" name="lift_domain" value="<?php echo get_option('lift_domain'); ?>" /></td>
        </tr>

        <tr valign="top">
        <th scope="row">Product have</th>
        <td><?php   echo "<select name='reco_on'>";
                    echo "<option value='0' $sku_yes>Stock Keeping Unit(SKU)</option>";
                    echo "<option value='1' $sku_no>Product Id</option>";
                    echo "</select>";
            ?><br />Show percentage with recommendation header
        </td>
        </tr>

        <tr valign="top">
            <th scope="row" colspan="2"><h3>Google Analytics Settings</h3></th>
        </tr>

        <tr valign="top">
        <th scope="row">Google Analytics Tracking Type</th>
        <td><?php   echo "<select name='lift_ga_type'>";
                    echo "<option value='1' $ga_type_1>Traditional</option>";
                    echo "<option value='0' $ga_type_0>Asynchronous</option>";
                    echo "</select>";
            ?><br />Show percentage with recommendation header
        </td>
        </tr>

        <tr valign="top">
        <th scope="row">Google Analytics Id</th>
        <td><input type="text" name="lift_ga_id" value="<?php echo get_option('lift_ga_id'); ?>" /></td>
        </tr>

        <tr valign="top">
            <th scope="row" colspan="2"><h3>Recommendation Settings</h3></th>
        </tr>

        <tr valign="top">
        <th scope="row">Show Percentage</th>
        <td><?php   echo "<select name='lift_percentage'>";
                    echo "<option value='1' $percentage_yes>Yes</option>";
                    echo "<option value='0' $percentage_no>No</option>";
                    echo "</select>";
            ?><br />Show percentage with recommendation header
        </td>
        </tr>

        <tr valign="top">
        <th scope="row">Custom Message</th>
        <td><input type="text" name="lift_message" value="<?php echo get_option('lift_message'); ?>" />
        <br />Shows Custom Message on recommendation header. Keep blank for display default message or enter your custom message
        </td>
        </tr>

        <tr valign="top">
        <th scope="row">Enable LiftSuggest in Product-View Page</th>
        <td><?php   echo "<select name='lift_prod_enabled'>";
                    echo "<option value='1' $prod_yes>Yes</option>";
                    echo "<option value='0' $prod_no>No</option>";
                    echo "</select>";
            ?><br />Show recommendation on Product-view page
        </td>
        </tr>

        <tr valign="top">
        <th scope="row">Enable LiftSuggest in Shopping Cart Page</th>
        <td><?php   echo "<select name='lift_cart_enabled'>";
                    echo "<option value='1' $cart_yes>Yes</option>";
                    echo "<option value='0' $cart_no>No</option>";
                    echo "</select>";
            ?><br />Show recommendation on Shopping-Cart page
        </td>
        </tr>

        <tr valign="top">
        <th scope="row">Enable LiftSuggest Ultimate Bought in Product Page</th>
        <td><?php   echo "<select name='lift_ub_enabled'>";
                    echo "<option value='1' $ub_yes>Yes</option>";
                    echo "<option value='0' $ub_no>No</option>";
                    echo "</select>";
            ?><br />Show Ultimate Bought recommendation on Product page
        </td>
        </tr>

        <tr valign="top">
        <th scope="row">Show Add to Cart Button</th>
        <td><?php   echo "<select name='lift_cart_btn'>";
                    echo "<option value='1' $cart_btn_yes>Yes</option>";
                    echo "<option value='0' $cart_btn_no>No</option>";
                    echo "</select>";
            ?><br />Show "Add to cart" button with recommendations
        </td>
        </tr>
    </table>

    <p class="submit">
    <input type="submit" class="button-primary" value="<?php _e('Update Changes') ?>" />
    </p>

</form>
</div>
<?php } ?>