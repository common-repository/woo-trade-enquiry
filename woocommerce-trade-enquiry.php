<?php

/**
 * Plugin Name: Woocommerce Trade Enquiry
 * Plugin URI: https://www.votechnolabs.com/website-development/
 * Description: This plugin use for enquiry save and showing on admin panel. Very use full for woocommerce product  for enquiry.
 * Version: 1.0.0
 * Requires at least: 4.7
 * Tested up to: 4.9.6
 * Tags: woocommerce,enquiry for wooCommerce,enquiry
 * Author: Votechnolabs
 * Author URI: http://votechnolabs.com
 * License: GPL2
 
 */

define( 'WTE_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

add_action('wp_head', 'wte_display_btn_func', 11);

function wte_display_btn_func()
{

    $form_init_data = get_option('solo_form_data');

    // if (!empty($form_init_data)) {
    if (isset($form_init_data['show_after_summary'])) {
        if ($form_init_data['show_after_summary'] == 'after_add_cart') {
            //show ask button after a single product summary add to cart
            add_action('woocommerce_single_product_summary', 'wte_ask_about_product_button', 30);
        } elseif ($form_init_data['show_after_summary'] == 'after_product_summary') {
                add_action('woocommerce_after_single_product_summary', 'wte_ask_about_product_button');
        }
    } else {
        add_action('woocommerce_single_product_summary', 'wte_ask_about_product_button', 30);
    }
        
}

function wte_ask_about_product_button()
{
    $form_data = get_option('solo_form_data');
    global $isProduct;
    $isProduct = true;
    ?>
     <div id="enquiry">
            <input type="button" name="contact" value="<?php echo empty($form_data['custom_label']) ?
            __('Make an enquiry for this product', 'woocommerce-trade-enquiry'): $form_data['custom_label'];?>" class="contact wpi-button single_add_to_cart_button button alt" />
     </div>
        
<?php }

add_action('wp_footer', 'wte_ask_about_product');

function wte_ask_about_product()
{
    if(!is_product())
    {
        return;
    }

    $form_data = get_option('solo_form_data');
    global $wpdb,$post, $isProduct;
    $query = "select user_email from {$wpdb->posts} as p join {$wpdb->users} as u on p.post_author=u.ID where p.ID=%d";
    $authorEmail = $wpdb->get_var($wpdb->prepare($query, $post->ID));

    ?>
     <!-- Page styles -->
        <?php
           // 
        if (is_product()) {
			
            wp_enqueue_style("solo-juery-css", plugins_url( '/css/solo-jquery-ui.css', __FILE__ ));
            if (isset($form_data[ 'user_custom_css' ])) {
                wp_add_inline_style('solo-juery-css', $form_data[ 'user_custom_css' ]);
            }
        }
        ?>
    <?php if ($isProduct/*&&(!empty($form_data['show_at_page_end']))||(!empty($form_data['show_after_summary']))*/) { ?>
    <div id="contact-form" title="<?php _e("Product Enquiry", "woocommerce-trade-enquiry");?>" style="display:none;">
    <form id="enquiry-form" action="#" method="POST">
    <label id="solo_product_name" for='product_name'> <?php echo get_the_title();?> </label>
        <div class="solo-pef-form-row">
        <input type="hidden" name="solo_product_id" value="<?php echo $post->ID ?>" />
        <label for='contact-name'>*<?php _e("Name", "woocommerce-trade-enquiry");?>:</label>
        <input type='hidden' name='author_email' id='author_email' value='<?php echo $authorEmail ?>'>
        <input type='text' id='contact-name' class='contact-input' name='solo_customer_name' value=""/>
        </div>
        <div class="solo-pef-form-row">
        <label for='contact-email'>*<?php _e("Email", "woocommerce-trade-enquiry");?>:</label>
        <input type='text' id='contact-email' class='contact-input' name='solo_customer_email'  />
        </div>
        <div class="solo-pef-form-row">
        <label for='contact-subject'><?php _e("Subject", "woocommerce-trade-enquiry");?>:</label>
        <input type='text' id='contact-subject' class='contact-input' name='solo_subject' value=''  />
        </div>
        <div class="solo-pef-form-row">
        <label for='contact-message'>*<?php _e("Enquiry", "woocommerce-trade-enquiry");?>:</label>
        <textarea id='contact-message' class='contact-input' name='solo_enquiry' cols='40' rows='4' style="resize:none"></textarea>
        </div>
      

        <div id="errors"></div>
        <div class="solo-enquiry-action-btns">
        <button id="send-btn" type='submit' class='contact-send contact-button' ><?php _e("Send", "woocommerce-trade-enquiry");?></button>
        <button id="cancel" type='button' class='contact-cancel contact-button' ><?php _e("Cancel", "woocommerce-trade-enquiry");?></button>
        </div>
        <?php echo wp_nonce_field('enquiry_action', 'product_enquiry', true, false); ?>
        
  </form>
  

  </div>
  <!-- preload the images -->
        
        <div id="loading" style='display:none'>
            <div id="send_mail"><p><?php _e("Sending...", "woocommerce-trade-enquiry");?></p>
            <img src='<?php echo plugins_url( '/img/contact/loading.gif', __FILE__ ); ?>' alt='' />
            </div>
        </div> 
        <?php
        unset($isProduct);
}
        ?>
    <!-- Load JavaScript files -->
    <?php
    
   
    wp_enqueue_script("jquery-ui-dialog", array("jquery"));
    wp_enqueue_script("solo-validate", plugins_url( '/js/solo_jquery.validate.min.js', __FILE__ ));
    
    wp_enqueue_script("solo-contact", plugins_url( '/js/contact.js', __FILE__ ), array("jquery"));
    wp_localize_script(
        'solo-contact',
        'object_name',
        array('ajaxurl' => admin_url('admin-ajax.php'),
                'product_name'=>get_the_title(),
                'solo_customer_name' => __('Name is required.', 'woocommerce-trade-enquiry'),
                'solo_customer_email'=>__('Enter valid Email Id.', 'woocommerce-trade-enquiry'),
                'solo_enquiry' => __(
                    'Enquiry length must be atleast 10 characters.',
                    'woocommerce-trade-enquiry'
                ) )
    );
}



add_action('admin_menu', 'wte_create_ask_product_menu');


function wte_create_ask_product_menu()
{
    
    if (is_plugin_active('woocommerce/woocommerce.php')) {
      
	  
	   
	    add_menu_page("", "Trade Enquiry", 'manage_options', "solo-trade", "wte_list_enquiry");
	
				
		 
		 add_submenu_page("solo-trade", "Add New", "Add Enquiry", 'manage_options', "add-enquiry", "wte_enquiry_form");
		 
		 add_submenu_page("solo-trade", "Settings", "Settings", 'manage_options', "enquiry-settings", "wte_add_ask_product_settings");
		 
    
	   

    }
}

function wte_enquiry_form()
{
	 global $wpdb;
    $table_name = $wpdb->prefix . 'trade'; // do not forget about tables prefix
    $message = '';
    $notice = '';
    // this is default $item which will be used for new records
    $default = array(
        'id' => 0,
        'product_name' => '',
        'product_url' => '',
         'customer_name' => '',
		 'customer_email' => '',
		 'subject' => '',
		 'message' => '',
		 
    );
    // here we are verifying does this request is post back and have correct nonce
    if ( isset($_REQUEST['nonce']) && wp_verify_nonce($_REQUEST['nonce'], basename(__FILE__))) {
        // combine our default item with request params
        $item = shortcode_atts($default, $_REQUEST);
        // validate data, and if all ok save item to database
        // if id is zero insert otherwise update
        $item_valid = wte_validate_person($item);
        if ($item_valid === true) {
            if ($item['id'] == 0) {
                $result = $wpdb->insert($table_name, $item);
                $item['id'] = $wpdb->insert_id;
                if ($result) {
                    $message = __('Enquiry  was successfully saved', 'solo_trade');
                } else {
                    $notice = __('There was an error while saving item', 'solo_trade');
                }
            } else {
                $result = $wpdb->update($table_name, $item, array('id' => $item['id']));
                if ($result) {
                    $message = __('Enquiry  was successfully updated', 'solo_trade');
                } else {
                    $notice = __('There was an error while updating Enquiry ', 'solo_trade');
                }
            }
        } else {
            // if $item_valid not true it contains error message(s)
            $notice = $item_valid;
        }
    }
    else {
        // if this is not post back we load item to edit or give new one to create
        $item = $default;
        if (isset($_REQUEST['id'])) {
            $item = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", sanitize_text_field($_REQUEST['id'])), ARRAY_A);
            if (!$item) {
                $item = $default;
                $notice = __('Item not found', 'solo_trade');
            }
        }
    }
    // here we adding our custom meta box
    add_meta_box('solo_form_meta_box', 'Enquiry data', 'wte_form_meta_box_handler', 'enquiry_form', 'normal', 'default');
    ?>
<div class="wrap">
    <div class="icon32 icon32-posts-post" id="icon-edit"><br></div>
    <h2><?php _e('Enquiry', 'solo_trade')?> <a class="add-new-h2"
                                href="<?php echo get_admin_url(get_current_blog_id(), 'admin.php?page=solo-trade');?>"><?php _e('back to list', 'solo_trade')?></a>
    </h2>

    <?php if (!empty($notice)): ?>
    <div id="notice" class="error"><p><?php echo $notice ?></p></div>
    <?php endif;?>
    <?php if (!empty($message)): ?>
    <div id="message" class="updated"><p><?php echo $message ?></p></div>
    <?php endif;?>

    <form id="form" method="POST">
        <input type="hidden" name="nonce" value="<?php echo wp_create_nonce(basename(__FILE__))?>"/>
        <?php /* NOTICE: here we storing id to determine will be item added or updated */ ?>
        <input type="hidden" name="id" value="<?php echo $item['id'] ?>"/>

        <div class="metabox-holder" id="poststuff">
            <div id="post-body">
                <div id="post-body-content">
                    <?php /* And here we call our custom meta box */ ?>
                    <?php do_meta_boxes('enquiry_form', 'normal', $item); ?>
                    <input type="submit" value="<?php _e('Save', 'solo_trade')?>" id="submit" class="button-primary" name="submit">
                </div>
            </div>
        </div>
    </form>
</div>

<?php
}

function wte_validate_person($item)
{
    $messages = array();
    if (empty($item['product_name'])) $messages[] = __('Product Name is required', 'solo_trade');
    if (empty($item['product_url'])) $messages[] = __('Product URL is required', 'solo_trade');
    if (empty($item['customer_name'])) $messages[] = __('Customer Name is required', 'solo_trade');
	 if (empty($item['customer_email'])) $messages[] = __('Customer Email is required', 'solo_trade');
	  if (empty($item['subject'])) $messages[] = __('Subject is required', 'solo_trade');
	  if (empty($item['message'])) $messages[] = __('Message Name is required', 'solo_trade');
   
    if (empty($messages)) return true;
    return implode('<br />', $messages);
}
function wte_form_meta_box_handler($item)
{
    ?>

<table cellspacing="2" cellpadding="5" style="width: 100%;" class="form-table">
    <tbody>
    <tr class="form-field">
        <th valign="top" scope="row">
            <label for="product_name"><?php _e('Product Name', 'solo_trade')?></label>
        </th>
        <td>
            <input id="product_name" name="product_name" type="text" style="width: 95%" value="<?php echo esc_attr($item['product_name'])?>"
                   size="50" class="code" placeholder="<?php _e('Product name', 'solo_trade')?>" required>
        </td>
    </tr>
    <tr class="form-field">
        <th valign="top" scope="row">
            <label for="product_url"><?php _e('Product Url', 'solo_trade')?></label>
        </th>
        <td>
            <input id="product_url" name="product_url" type="text" style="width: 95%" value="<?php echo esc_attr($item['product_url'])?>"
                   size="50" class="code" placeholder="<?php _e('Product Url', 'solo_trade')?>" >
        </td>
    </tr>
    <tr class="form-field">
        <th valign="top" scope="row">
            <label for="customer_name"><?php _e('Customer Name', 'solo_trade')?></label>
        </th>
        <td>
            <input id="customer_name" name="customer_name" type="text" style="width: 95%" value="<?php echo esc_attr($item['customer_name'])?>"
                   size="50" class="code" placeholder="<?php _e('Customer Name', 'solo_trade')?>" required>
        </td>
    </tr>
	 <tr class="form-field">
        <th valign="top" scope="row">
            <label for="customer_email"><?php _e('Customer Email', 'solo_trade')?></label>
        </th>
        <td>
            <input id="customer_email" name="customer_email" type="email" style="width: 95%" value="<?php echo esc_attr($item['customer_email'])?>"
                   size="50" class="code" placeholder="<?php _e('Customer Email', 'solo_trade')?>" required>
        </td>
    </tr>
	
	 <tr class="form-field">
        <th valign="top" scope="row">
            <label for="subject"><?php _e('Subject', 'solo_trade')?></label>
        </th>
        <td>
            <input id="subject" name="subject" type="text" style="width: 95%" value="<?php echo esc_attr($item['subject'])?>"
                   size="50" class="code" placeholder="<?php _e('Subject', 'solo_trade')?>" required>
        </td>
    </tr>
	
	 <tr class="form-field">
        <th valign="top" scope="row">
            <label for="message"><?php _e('Message', 'solo_trade')?></label>
        </th>
        <td>
            <textarea id="message" name="message"  style="width: 95%"  rows="6" placeholder="Message" required ><?php echo esc_attr($item['message']);?></textarea>
                  
        </td>
    </tr>
	
    </tbody>
</table>
<?php
}

function wte_list_enquiry()
{
	
require_once( WTE_PLUGIN_DIR . 'enquiry-list.php' );
	
	global $wpdb;
    $table = new WTE_List_Table();
    $table->prepare_items();
    $message = '';
    if ('delete' === $table->current_action()) {
        $message = '<div class="updated below-h2" id="message"><p>' . sprintf(__('Items deleted: %d', 'solo_trade'), count($_REQUEST['id'])) . '</p></div>';
    }
    ?>
<div class="wrap">

    <div class="icon32 icon32-posts-post" id="icon-edit"><br></div>
    <h2><?php _e('Enquiries', 'solo_trade')?> <a class="add-new-h2"
                                 href="<?php echo get_admin_url(get_current_blog_id(), 'admin.php?page=add-enquiry');?>"><?php _e('Add new', 'solo_trade')?></a>
    </h2>
    <?php echo $message; ?>

    <form id="enquiries-table" method="GET">
        <input type="hidden" name="page" value="<?php echo esc_attr($_REQUEST['page']); ?>"/>
		
		<?php
		 //$table->search_box( 'search', 'search_id' );

         $table->display() ?>
    </form>

</div>
	<?php
	
	
}

function wte_add_ask_product_settings()
{

    wp_enqueue_script('solo_bootstrap_js',plugins_url( '/js/bootstrap.min.js', __FILE__ ) , array('jquery'));
    
    wp_enqueue_style('solo_bootstrap_css',plugins_url( '/css/bootstrap.min.css', __FILE__ ));
    //settings page
    wp_enqueue_script('solo_wpi_validation', plugins_url( '/js/solo_jquery.validate.min.js', __FILE__ ));
 
    
    ?>
    
      <div class="wrap solo_leftwrap">
       
    
    

        <h2><?php _e("Product Enquiry", "woocommerce-trade-enquiry");?></h2>
<br />
    <?php
    if (isset($_GET[ 'tab' ])) {
            $active_tab = sanitize_text_field($_GET[ 'tab' ]);
    } else {
            $active_tab = 'form';
    }
    
    $dashboard_tabs = array(
        'form'      => __("Enquiry Settings", "woocommerce-trade-enquiry"),
       
    );
    $dashboard_tabs = apply_filters('product_enquiry_dashboard_tab_titles', $dashboard_tabs);
        ?>
            <h2 class="nav-tab-wrapper">  
                <?php
                foreach ($dashboard_tabs as $single_tab_slug => $single_tab_title) { ?>
                        <a href="admin.php?page=woocommerce-trade-enquiry&tab=<?php echo $single_tab_slug ?>" class="nav-tab <?php echo $active_tab == $single_tab_slug ? 'nav-tab-active' : ''; ?>">
                            <?php echo $single_tab_title; ?>
                        </a>
                    <?php
                }
                ?>
               
            </h2>  
    
    <?php 
	
	if ($active_tab === 'form') {
    wp_enqueue_script('solo-trade-tip',plugins_url( '/js/tooltip.js', __FILE__ ), array('jquery'));
    wp_enqueue_script('solo-trade-settings',plugins_url( '/js/settings.js', __FILE__ ), array('jquery'));
    wp_enqueue_style('woocommerce_admin_styles', plugin_dir_url((dirname(__FILE__))) . 'woocommerce/assets/css/admin.css');
    wp_enqueue_script('jquery-tiptip', array( 'jquery' ));

    ?>
 <form name="ask_product_form" id="ask_product_form" method="POST" action="options.php" style="background: #fff; padding: 10px 15px 0 15px;">
    <?php
    settings_fields('solo_form_options');
    $default_vals =   array('show_after_summary'=>'after_add_cart');
    $form_data = get_option('solo_form_data', $default_vals);
    if (!isset($form_data['show_after_summary'])) {
        $form_data['show_after_summary'] = "after_add_cart";
    }
?>
<div id="ask_abt_product_panel">
<fieldset>
<?php echo '<legend>'. __("Emailing Information", 'woocommerce-trade-enquiry').'</legend>'; ?>
<div class="fd">
<div class='left_div'>
<label for="solo_user_email"><?php _e(" Recipient's Email ", "woocommerce-trade-enquiry");?> </label> 
</div>
<div class='right_div'>
<?php
$helptip = __('You can add multiple email addresses separated by comma', 'woocommerce-trade-enquiry');
echo wte_soloHelpTip($helptip);
?>
<input type="text" class="solo_wpi_input solo_wpi_text" name="solo_form_data[user_email]" id="solo_user_email" value="<?php echo empty($form_data['user_email']) ? get_option('admin_email') : $form_data['user_email'];?>" />
<span class='email_error'> </span>
    </div>
    <div class='clear'></div>
    </div >
    <div class="fd">
    <div class='left_div'>
<label for="solo_default_sub"> <?php _e("Default Subject ", "woocommerce-trade-enquiry");?> </label>
    </div>
    <div class='right_div'>
    <?php
    $helptip = __('Subject to be used if customer leaves subject field blank', 'woocommerce-trade-enquiry');
    echo wte_soloHelpTip($helptip);
    ?>      
<input type="text" class="solo_wpi_input solo_wpi_text" name="solo_form_data[default_sub]" id="solo_default_sub" value="<?php echo empty($form_data['default_sub']) ? __('Enquiry for a product from ', 'woocommerce-trade-enquiry').get_bloginfo('name') : $form_data['default_sub'];?>"  />

    </div>
    <div class='clear'></div>
    </div>
    <div class="fd" style="display:none;">
    <div class='left_div'>
<label for="send-mail-to-admin"> <?php _e("Send mail to Admin ", "woocommerce-trade-enquiry");?> </label>
    </div>
    <div class='right_div'>
    <?php
    $helptip = __('When checked, sends the enquiry email to the Admin email address specified under Settings -> General', 'woocommerce-trade-enquiry');
    echo wte_soloHelpTip($helptip);
    ?>      
<input name="solo_form_data[send_mail_to_admin]" type="checkbox" class="solo_wpi_input solo_wpi_checkbox" value="1" <?php checked(1, isset($form_data[ 'send_mail_to_admin' ]) ? $form_data[ 'send_mail_to_admin' ] : 0);
?> id="send-mail-to-admin" />
</div>
<div class='clear'></div>
</div>

<div class="fd" style="display:none;">
    <div class='left_div'>
        <label for="send-mail-to-product-author"> <?php _e("Send mail to Product Author ", "woocommerce-trade-enquiry");?> </label>
    </div>
    <div class='right_div'>
    <?php
    $helptip = __('When checked, sends enquiry email to author/owner of the Product', 'woocommerce-trade-enquiry');
    echo wte_soloHelpTip($helptip);
    ?>      
<input name="solo_form_data[send_mail_to_author]" type="checkbox" class="solo_wpi_input solo_wpi_checkbox" value="1" <?php checked(1, isset($form_data[ 'send_mail_to_author' ]) ? $form_data[ 'send_mail_to_author' ] : 0); ?> id="send-mail-to-product-author" />
</div>
<div class='clear'></div>
</div>

<div class="fd" style="display:none;">
    <div class='left_div'>
        <label for="deactivate-nonce"> <?php _e("Disable Nonce ", "woocommerce-trade-enquiry");?> </label>
    </div>
    <div class='right_div'>
    <?php
        $helptip = __('Check this option if your enquiry system does not work properly or displays an “Unauthorised Enquiry” error. Note: In all other cases, we advise you to keep it  unchecked.', 'woocommerce-trade-enquiry');
        echo wte_soloHelpTip($helptip);
    ?>      
        <input name="solo_form_data[deactivate_nonce]" type="checkbox" class="solo_wpi_input solo_wpi_checkbox" value="1" <?php checked(1, isset($form_data[ 'deactivate_nonce' ]) ? $form_data[ 'deactivate_nonce' ] : 0); ?> id="deactivate-nonce" />
    </div>
    <div class='clear'></div>
</div>


</fieldset>
<!-- <br/> -->
<fieldset>
    
<?php echo '<legend>'. __("Form Options", 'woocommerce-trade-enquiry').'</legend>'; ?>
    <div class="fd">
    <div class='left_div'>
    <label for="custom_label"> <?php _e("Enquiry Button Label", "woocommerce-trade-enquiry");?> </label> 
</div>
    <div class='right_div'>
    <?php
    $helptip = __('Add custom label for enquiry button', 'woocommerce-trade-enquiry');
    echo wte_soloHelpTip($helptip);
    ?>
    <input type="text" class="solo_wpi_input solo_wpi_text" name="solo_form_data[custom_label]" value="<?php echo empty($form_data['custom_label']) ? __('Make an enquiry for this product', 'woocommerce-trade-enquiry') : $form_data['custom_label'];?>" id="custom_label"  />
</div>
    <div class='clear'></div>
</div>
  
        
    
    

    
    
    
     
      
  
       
     <p>
    <input type="submit" class="solo_wpi_input button-primary" value="Save Changes" id="solo_ask_button" />
        </p>
        
    <br/>
     </form>
     
 <script type="text/javascript">
    jQuery(document).ready(
       function($)
       {
        $("#ask_product_form").validate();
                    
        if($("#manual_css").is(':checked')) {
    $("#Other_Settings").show();
        }
        else{
   $("#Other_Settings").hide(); 
        }
                    
        $("#theme_css").click(function(){$("#Other_Settings").hide();});
        $("#manual_css").click(function(){$("#Other_Settings").show();});
    }
    );
    </script>
     
        <?php             }
        do_action('product_enquiry_tab_content', $active_tab);
        ?>
    </div>
        <?php
      //add styles for settings page
       	
		 wp_enqueue_style("solo-admin-css", plugins_url( '/css/wpi_admin.css', __FILE__ ));
      
      //include solostream sidebar
    
        $plugin_data  = get_plugin_data(__FILE__);
        $plugin_name = $plugin_data['Name'];
        $solo_plugin_slug = 'woocommerce-trade-enquiry';
    
   
}

add_action('wp_ajax_solo_send', 'wte_contact_email');
add_action('wp_ajax_nopriv_solo_send', 'wte_contact_email');
function wte_contact_email()
{
    $form_data = get_option('solo_form_data');

    if (isset($form_data['deactivate_nonce']) && $form_data['deactivate_nonce'] == 1) {
        $nonce = true;
    } else {
        $nonce = check_ajax_referer('enquiry_action', 'security', false);
    }
         
    if (isset($_POST['security']) && $nonce) {
        $name = isset($_POST['solo_name']) ? sanitize_text_field($_POST['solo_name']) : "";
        $to = isset($_POST['solo_emailid']) ? sanitize_text_field($_POST['solo_emailid']) : "";
        $subject = (isset($_POST['solo_subject'])&&!empty($_POST['solo_subject'])) ? sanitize_text_field($_POST['solo_subject']) :sanitize_text_field($form_data['default_sub']);
        $message = isset($_POST['solo_enquiry']) ?  sanitize_textarea_field($_POST['solo_enquiry']) : "";
        $cc = isset($_POST['solo_cc']) ? sanitize_text_field($_POST['solo_cc']) : "";
        $product_name=isset($_POST['solo_product_name']) ? sanitize_text_field($_POST['solo_product_name']) : "";
        $product_url = isset($_POST['solo_product_url']) ? sanitize_text_field($_POST['solo_product_url']) : "";
        $admin_email=get_option('admin_email');
        $site_name =get_bloginfo('name');
        $recipient_emails = array();
        $recipient_email = array();
        $enabledTermsCond = true;
        if ($form_data[ 'user_email' ] != '') {
            $recipient_email = explode(',', $form_data[ 'user_email' ]);
            $recipient_email = array_map('trim', $recipient_email);
        }
        foreach ($recipient_email as $singleEmail) {
            array_push($recipient_emails, $singleEmail);
        }
        if (isset($form_data['send_mail_to_admin'])) {
            array_push($recipient_emails, $admin_email);
        }

        $authorEmail = sanitize_text_field($_POST['uemail']);

    //$group_emails = array($recipient_email,$admin_email);
        $headers = "Reply-To: $to \n";
    //UTF-8
        if (function_exists('mb_encode_mimeheader')) {
            $subject = mb_encode_mimeheader($subject, "UTF-8", "B", "\n");
        } else {
            // you need to enable mb_encode_mimeheader or risk
            // getting emails that are not UTF-8 encoded
        }
        $headers .= "MIME-Version: 1.0\n";
        $headers .= "Content-type: text/html; charset=utf-8\n";
        // $headers .= "Content-Transfer-Encoding: quoted-printable\n";
    
    // Set and wordwrap message body
        $body = __('Product Enquiry from', 'woocommerce-trade-enquiry')." <strong>". $site_name . "</strong> <br /><br />";
        $body .= "<strong>".__('Product Name:', 'woocommerce-trade-enquiry')."</strong> '". $product_name ."'<br /><br />";
        $body .= "<strong>".__('Product URL:', 'woocommerce-trade-enquiry')."</strong> ". $product_url ."<br /><br />";
        $body .= "<strong>".__('Customer Name:', 'woocommerce-trade-enquiry')."</strong> ". $name ."<br /><br />";
        $body .= "<strong>".__('Customer Email:', 'woocommerce-trade-enquiry')."</strong> ". $to ."<br /><br />";
        $body .= "<strong>".__('Message:', 'woocommerce-trade-enquiry')."</strong> <br />". $message;
		
		
		global $wpdb;
       //$trade_table = $wpdb->prefix."trade";
	   
	   $wpdb->query( $wpdb->prepare("INSERT INTO ".$wpdb->prefix."trade(product_name,product_url,customer_name,customer_email,subject,message,created_at)	VALUES (%s,%s,%s,%s,%s,%s,%s)",array(
		$product_name, 
		$product_url, 
		$name,
		$to,
		$subject,
		$message,
		current_time( 'mysql' )) 
) );

	

        if(!empty($enabledTermsCond))
        {
            $body .= "<br /><br />".sprintf(__('%s accepted the enquiry terms and conditions', 'woocommerce-trade-enquiry'), $to);
        }

        $adminMailBody = $body;
      
        $adminMailBody = wordwrap($adminMailBody, 100);
        $recipient_emails = array_unique($recipient_emails);
        $send_mail = false;
        foreach ($recipient_emails as $recipient_email) {
            $send_mail=wp_mail($recipient_email, $subject, $adminMailBody, $headers); //or  die(__("Unfortunately, a server issue prevented delivery of your message.","woocommerce-trade-enquiry"));
        }
        if (isset($form_data['send_mail_to_author']) && isset($authorEmail) && !in_array($authorEmail, $recipient_emails)) {
            wp_mail($authorEmail, $subject, $body, $headers);
        }
    
        if ($send_mail) {
            _e("Enquiry was sent successfully", "woocommerce-trade-enquiry");
            if ($cc == 1) {
                wp_mail($to, $subject, wordwrap($body, 100), $headers);
            }
        } else {
            _e("Unfortunately, a server issue prevented delivery of your message.", "woocommerce-trade-enquiry");
        }
        die();
    }
    
    die(__("Unauthorized access.", "woocommerce-trade-enquiry"));
}

add_action('admin_init', 'wte_reg_form_settings');

function wte_reg_form_settings()
{
    //register plugin settings
    register_setting('solo_form_options', 'solo_form_data');
}

add_action('admin_footer', 'wte_action_javascript'); // Write our JS below here

function wte_action_javascript()
{
    if (isset($_GET["page"]) && $_GET["page"] == "woocommerce-trade-enquiry") { ?>
    <script type="text/javascript" >
    jQuery(document).ready(function($) {
    jQuery(".solo-req-button").click(function() {
    var data = {};
    data.email = $(this).siblings(".solo-req-text").val();
    data.id = $(this).siblings( ".id" ).val();
    data.updates = $(this).siblings(".solo-req-confirm").prop("checked");
    data.action = "wte_submit_action";
    var email = $(this).siblings(".solo-req-text").val();
    var txt = /^([a-zA-Z0-9_\.\-\+])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/;
    if (!txt.test(email)) {
    $(this).siblings("input.solo-req-text").css('border','1px solid red');
    e.preventDefault();
    } else {
    $(this).siblings("input.solo-req-text").css('border','1px solid #ccc');
    }
    var id = $(this).siblings( ".id" ).val();
    var loading = $(this).siblings(".loading").show();
    // since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
    $.post(ajaxurl, data, function(response) {
    //alert('Got this from the server: ' + response + '<br>id is :' + ids);
    var id = jQuery.trim(response);
    //jQuery(this).siblings('.solo-req-title').hide();
    jQuery('.loading').hide();
    alert('Your request has been sent');
    jQuery(id).find("div.loading").hide();
    });
    });
    });
    </script>
    <?php
    }
}

add_action('wp_ajax_wte_submit_action', 'wte_pe_action_callback');

function wte_pe_action_callback()
{
    global $wpdb; // this is how you get access to the database
    $email = sanitize_email($_POST['email']);
    $id = sanitize_text_field($_POST['id']);
    $updates = sanitize_text_field($_POST['updates']);
    $subscribes_message = "";
    $subscribes_message_client = "";
    if ($updates == "true") {
        $subscribes_message = __("And Congrats! They have subscribed to your newsletter too!", 'woocommerce-trade-enquiry');
        $subscribes_message_client =__("We'll keep you updated with the latest developments.", 'woocommerce-trade-enquiry');
    } else {
    //$subscribes_message = "Oh shoot! They haven't subscribed.";
    //$subscribes_message_client = "To stay up-to-date with the latest developments, do subscribe to our newsletter!";
    }
    
    echo "." . $id;
    die(); // this is required to terminate immediately and return a proper response
}

function wte_checkIfNoData($fileData, $current_response_code, $valid_response_code)
{
    if ($fileData == null || ! in_array($current_response_code, $valid_response_code)) {
        $GLOBALS[ 'solo_server_null_response' ] = true;
        return false;
    }
    return true;
}

function wte_soloHelpTip($helptip)
{
    $wooVersion = WC_VERSION;
    $wooVersion = floatval($wooVersion);
    if ($wooVersion < 2.5) {
        return '<img class="help_tip" data-tip="' . esc_attr($helptip) . '" src="' . WC()->plugin_url() . '/assets/images/help.png" height="16" width="16" />';
    } else {
        return wc_help_tip($helptip);
    }
}



//function to run on activation
function wte_woocommerce_activate() {
  
    if( !class_exists( 'WooCommerce' ) ) {
        deactivate_plugins( plugin_basename( __FILE__ ) );
        wp_die( __( 'Please install and Activate WooCommerce.', 'woocommerce-trade-enquiry' ), 'Plugin dependency check', array( 'back_link' => true ) );
    }
	
	global $wpdb;
	

	$table_name = $wpdb->prefix . 'trade';
	
	$charset_collate = $wpdb->get_charset_collate();

	 $sql = "CREATE TABLE $table_name (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_name` varchar(255) NOT NULL,
  `product_url` text NOT NULL,
  `customer_name` varchar(255) NOT NULL,
  `customer_email` varchar(255) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY  (id)
   
	) $charset_collate;";

	
	
	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	dbDelta( $sql );
	
	
}

//sets up activation hook
register_activation_hook(__FILE__, 'wte_woocommerce_activate');

add_action( 'woocommerce_after_shop_loop_item', 'wte_remove_loop_button', 1 );

function wte_remove_loop_button()
{
if( is_product_category() || is_shop()) { 
remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart');
}
}


?>