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
  );
}
add_action('admin_menu', 'volunteer_admin_menu');

function volunteer_ops_page_html() {
  global $wpdb;

  if(isset($_POST['submit'])) {
    $table_name = 'wp_volunteer';

    $wpdb->insert(
      $table_name,
      array(
        'position' => $_POST['position'],
        'hours' => $_POST['hours']
      )
    );
    echo '<div class="updated"><p>Saved!</p></div/>';
  }

  ?>
  <div class="wrap">
    <h1>Volunteer Opportunities</h1>
    <form method="post">
      <label>Position Name:</label><input type="text" name="position"><br>
      <label>Hours:</label><input type="text" name="hours"><br>
      <input type="submit" name="submit" value="Save" class="button button-primary">
    </form>
  </div>
  <?php>
}
 


?>