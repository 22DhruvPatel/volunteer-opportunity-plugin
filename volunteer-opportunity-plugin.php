<?php
/*
Plugin Name: Volunteer Opportunity Plugin
Description: A plugin to manage and list volunteer opportunity
Version: 1.0
Author: Dhruv Patel
*/


function volunteer_activate() {
  global $wpdb;

  $wpdb->query("CREATE TABLE wp_volunteer (
    id midiumint(9) NOT NULL AUTO_INCREMENT,
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


function volunteer_deactivate() {
  global $wpdb;
  $wpdb->query("DROP TABLE wp_volunteer");
}
register_deactivation_hook(__FILE__, 'volunteer_deactivate');




?>