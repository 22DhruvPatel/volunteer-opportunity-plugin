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

  global $wpdb;
  $table_name = $wpdb->prefix . 'volunteer';
  $message = '';

  //DELETE ACTION
  if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $wpdb->delete($table_name, array('id' => intval($_GET['id'])));
    $message = "Opportunity Deleted.";
  }

  if (isset($_POST['submit'])) {
    // Validation: Ensure hours is an integer
    $hours = intval($_POST['hours']);
        
    // Sanitization for other fields
    $data = array(
      'position' => sanitize_text_field($_POST['position']),
      'organization' => sanitize_text_field($_POST['organization']),
      'type' => sanitize_text_field($_POST['type']),
      'email' => sanitize_email($_POST['email']),
      'description' => sanitize_textarea_field($_POST['description']),
      'location' => sanitize_text_field($_POST['location']),
      'hours' => $hours,
      'skills' => sanitize_text_field($_POST['skills'])
    );

    $wpdb->insert($table_name, $data);
    $message = "Opportunity Saved Successfully!";
  }


  ?>
  <div class="wrap">
    <h1>Volunteer Opportunities</h1>
    <?php if ($message) echo "<div class='updated notice is-dismissible'><p>$message</p></div>"; ?>

    <div style="background:#fff; padding:20px; border:1px solid #ccc; margin-bottom: 20px;">
      <h2>Add New Opportunity</h2>
      <form method="post">
        <table class="form-table">
          <tr>
            <th><label>Position</label></th>
            <td><input type="text" name="position" class="regular-text" required></td>
          </tr>
          <tr>
            <th><label>Organization</label></th>
            <td><input type="text" name="organization" class="regular-text" required></td>
          </tr>
          <tr>
            <th><label>Type</label></th>
            <td>
              <select name="type">
                <option value="One-time">One-time</option>
                <option value="Recurring">Recurring</option>
                <option value="Seasonal">Seasonal</option>
              </select>
            </td>
          </tr>
          <tr>
            <th><label>Email</label></th>
            <td><input type="email" name="email" class="regular-text" required></td>
          </tr>
          <tr>
            <th><label>Description</label></th>
            <td><textarea name="description" rows="3" class="large-text"></textarea></td>
          </tr>
          <tr>
            <th><label>Location</label></th>
            <td><input type="text" name="location" class="regular-text" required></td>
          </tr>
          <tr>
            <th><label>Hours</label></th>
            <td><input type="number" name="hours" class="small-text" required></td>
          </tr>
          <tr>
            <th><label>Skills</label></th>
            <td><input type="text" name="skills" class="large-text" placeholder="e.g. communication, coding"></td>
          </tr>
        </table>

        <p class="submit">
          <input type="submit" name="submit" value="Save Opportunity" class="button button-primary">
        </p>
      </form>
    </div>

    <h2>Existing Opportunities</h2>
      <table class="wp-list-table widefat fixed striped">
        <thead>
          <tr>
            <th>Position</th>
            <th>Organization</th>
            <th>Type</th>
            <th>Hours</th>
            <th>Location</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <?php
            // Fetch all results [cite: 153]
            $results = $wpdb->get_results("SELECT * FROM $table_name");
                  
            if ($results) {
              foreach ($results as $row) {
                $delete_link = admin_url('admin.php?page=volunteer_ops&action=delete&id=' . $row->id);
                echo "<tr>";
                echo "<td>" . esc_html($row->position) . "</td>";
                echo "<td>" . esc_html($row->organization) . "</td>";
                echo "<td>" . esc_html($row->type) . "</td>";
                echo "<td>" . esc_html($row->hours) . "</td>";
                echo "<td>" . esc_html($row->location) . "</td>";
                echo "<td><a href='$delete_link' style='color:red;' onclick=\"return confirm('Are you sure you want to delete this?')\">Delete</a></td>";
                echo "</tr>";
              }
            } else {
              echo "<tr><td colspan='6'>No volunteer opportunities found.</td></tr>";
            }
          ?>
        </tbody>
      </table>
  </div>
  <?php
}
 


?>