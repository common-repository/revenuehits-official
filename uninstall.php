<?php

if ( !defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit();
}
 
delete_option('revenuehits_show');    
delete_option('revenuehits_userid');    
delete_option('revenuehits_exclude_pages');    
delete_option('revenuehits_position_footer');    
delete_option('revenuehits_position_popup');    
delete_option('revenuehits_position_dialog');    
delete_option('revenuehits_position_notifier');
delete_option('revenuehits_position_shadow_box');    
delete_option('revenuehits_position_interstitial');
delete_option('revenuehits_homepage'); 
delete_option('revenuehits_categories'); 
delete_option('revenuehits_posts'); 
delete_option('revenuehits_other');
delete_option('revenuehits_script_dialog');
delete_option('revenuehits_script_footer');
delete_option('revenuehits_script_interstitial');
delete_option('revenuehits_script_shadow_box');
delete_option('revenuehits_script_popup');
delete_option('revenuehits_script_notifier');
delete_option('revenuehits_password');