<?php
/**
 * Plugin Name: Custom Cookie Consent
 * Plugin URI: https://ginx.se
 * Description: Cookie consent plugin for displaying cookie consent banner and managing cookie preferences.
 * Version: 1.0
 * Author: George Iskef
 * Author URI: https://georgeiskef.com
 */
 
 function set_cookie_consent_preference() {
    if (isset($_POST['cookie_consent'])) {
        $consent = $_POST['cookie_consent'] === 'true' ? 'accepted' : 'declined';
        setcookie('cookie_consent_preference', $consent, time() + 86400 * 365, '/');
    }
}
add_action('init', 'set_cookie_consent_preference');

function get_cookie_consent_preference() {
    if (isset($_COOKIE['cookie_consent_preference'])) {
        return $_COOKIE['cookie_consent_preference'];
    }
    return 'unset';
}

function display_cookie_consent_popup() {
    $consent = get_cookie_consent_preference();
    if ($consent === 'unset') {
        // Display the consent banner
        include(plugin_dir_path(__FILE__) . 'templates/cookie-consent-popup.php');
    }
}
add_action('wp_footer', 'display_cookie_consent_popup');






 // Enqueue necessary scripts and styles
 function myplugin_enqueue_scripts() {
    // Enqueue JavaScript file
    wp_enqueue_script('custom-script', plugin_dir_url(__FILE__) . 'js/cookies-consent.js', array(), '1.0.0', true);
  
    // Enqueue CSS file
    wp_enqueue_style('myplugin-style', plugin_dir_url(__FILE__) . 'css/cookie-consent.css');
  }
  add_action('wp_enqueue_scripts', 'myplugin_enqueue_scripts');


// Handle form submission
function myplugin_handle_form_submission() {
    if ($_POST['action'] === 'myplugin_save_preferences') {
      // Process and save the selected cookie preferences
      $analytics = isset($_POST['analytics']) ? $_POST['analytics'] : '';
      $marketing = isset($_POST['marketing']) ? $_POST['marketing'] : '';
  
      // Save the cookie preferences to the server
      // Replace the following code with your own logic to handle the cookie preferences
  
      // Example: Set the essential cookie
      setcookie('essential_cookie', 'yes', time() + 86400, '/'); // Set essential cookie for 24 hours
  
      // Example: Set the analytics cookie if selected
      if ($analytics === 'yes') {
        setcookie('analytics_cookie', 'yes', time() + 2592000, '/'); // Set analytics cookie for 30 days
      }
  
      // Example: Set the marketing cookie if selected
      if ($marketing === 'yes') {
        setcookie('marketing_cookie', 'yes', time() + 2592000, '/'); // Set marketing cookie for 30 days
      }
    }
  }


/* Replace the names and see if this works better.

// Handle form submission
function myplugin_handle_form_submission() {
  if ($_POST['action'] === 'myplugin_save_preferences') {
    // Process and save the selected cookie preferences
    $analytics = isset($_POST['analytics']) ? $_POST['analytics'] : '';
    $marketing = isset($_POST['marketing']) ? $_POST['marketing'] : '';

    // Save the cookie preferences to the server
    // Replace the following code with your own logic to handle the cookie preferences

    // Example: Set the essential cookie
    setcookie('essential_cookie', 'yes', time() + 86400, '/'); // Set essential cookie for 24 hours

    // Example: Set the analytics cookie if selected
    if ($analytics === 'yes') {
      setcookie('analytics_cookie', 'yes', time() + 2592000, '/'); // Set analytics cookie for 30 days
    } else {
      setcookie('analytics_cookie', 'no', time() - 3600, '/'); // Remove analytics cookie
    }

    // Example: Set the marketing cookie if selected
    if ($marketing === 'yes') {
      setcookie('marketing_cookie', 'yes', time() + 2592000, '/'); // Set marketing cookie for 30 days
    } else {
      setcookie('marketing_cookie', 'no', time() - 3600, '/'); // Remove marketing cookie
    }
  }
}

*/


/*


 add_action('wp_ajax_myplugin_save_preferences', 'myplugin_handle_form_submission');
 add_action('wp_ajax_nopriv_myplugin_save_preferences', 'myplugin_handle_form_submission');
 

 //I don't complely understnad the usefullness of that

 // Initialize the cookies popup
 function myplugin_initialize_cookies_popup() {
   if (!isset($_COOKIE['myplugin_cookies_consent']) && !isset($_COOKIE['myplugin_cookies_preferences'])) {
     // Popup is displayed if the cookies consent and preferences are not set
     wp_enqueue_script('myplugin-script');
   }
 }
 add_action('wp_enqueue_scripts', 'myplugin_initialize_cookies_popup');
 
*/



?>



