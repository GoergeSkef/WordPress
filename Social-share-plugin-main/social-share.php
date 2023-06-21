<?php

/**
*plugin name: social share
*Description: Adds Facebook and Twitter share buttons to posts
*Author: George Iskef
*Author URL: https://georgeiskef.com/
*Version: 1.0.0
*Text Domain: Social Share
*
*/


// Add Shortcode

    function social_share_buttons($content) {
        global $post;
        $post_url = get_permalink($post->ID);
        $post_title = urlencode(get_the_title());
        $post_caption = urlencode(get_the_post_thumbnail_caption());
        $facebook_url = 'https://www.facebook.com/sharer.php?u=' . $post_url;
        $twitter_url = 'https://twitter.com/intent/tweet?url=' . $post_url . '&text=' . $post_title;
        $instagram = 'https://www.instagram.com/share?url=' . $post_url . '&caption=' . $post_title;
        $linkedin = ' https://www.linkedin.com/sharing/share-offsite/?url=' . $post_url;
        //$whatsapp = 'https://wa.me/?text=' . $post_title; . '%20' . $post_url;
        $telegram = 'https://telegram.me/share/url?url=' . $post_url . '&text=' . $post_title;
        $buttons = '<div class="social-share-buttons">';
        $buttons .= '<a href="' . $facebook_url . '" target="_blank" style="width:80px; height: 80px margin: 50px; padding-right: 20px;" class="george"><img src="/wp-content/plugins/social-share/images/Facebook.png" style="width: 30px; height:30px; margin-right:15px;">Share on Facebook</a>';
        $buttons .= '<a href="' . $twitter_url . '" target="_blank" style="width:80px; height: 80px margin: 50px;"><img src="/wp-content/plugins/social-share/images/Twitter.png" style="width: 30px; height:30px; margin:15px;"> Share on Twitter</a>';
        $buttons .= '</div>';
        return $content . $buttons;
    }

    function load_assets()
    {
        wp_enqueue_style('Custom_Login',
            plugin_dir_url(__file__) . 'css/custom_Social_Share_style.css'
        );

        wp_enqueue_script('custom_login',
            plugin_dir_url(__file__) . 'js/custom_social_share.js'
        );
    }

    add_shortcode( 'social_share', 'social_share_buttons' );

    //Add assets (js, css, etc.)
    add_action('wp_enqueue_scripts', 'load_assets');
    
?>


