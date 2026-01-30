<?php
/*
Plugin Name: Volunteer Opportunity Plugin
Description: A plugin to manage and list volunteer opportunity
Version: 1.0
Author: Dhruv Patel
*/

//activation hook
function volunteer_activate() {
  global $wpdb;

  $wpdb->query("CREATE TABLE wp_volunteer (
    id mediumint(9) NOT NULL AUTO_INCREMENT,
    position tinytext NOT NULL,
    organization tinytext NOT NULL,
    type tinytext NOT NULL,
    email tinytext NOT NULL,
    description text NOT NULL,
    location tinytext NOT NULL,
    hours int(11) NOT NULL,
    skills text NOT NULL,
    PRIMARY KEY (id)
  );");
}
register_activation_hook(__FILE__, 'volunteer_activate');

//deactivation hook
function volunteer_deactivate() {
  global $wpdb;
  $wpdb->query("DROP TABLE wp_volunteer");
}
register_deactivation_hook(__FILE__, 'volunteer_deactivate');


//admin menu page
function volunteer_admin_menu() {
  add_menu_page (
    'Volunteer Opportunities',
    'Volunteer',
    'manage_options',
    'volunteer_ops',
    'volunteer_ops_page_html'
  )
}
add_action('admin_menu', 'vlunteer_admin_menu');

?>