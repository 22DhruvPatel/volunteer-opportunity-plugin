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
 


function volunteer_shortcode_func($atts = [], $content = null) {
  global $wpdb;
  $table_name = $wpdb->prefix . 'volunteer';

  // Normalize attributes to lowercase
  $atts = array_change_key_case((array)$atts, CASE_LOWER);

  // Initialize logic variables
  $use_colors = true; // Default: Colors are ON
  $filter_clauses = [];

  // Filter by Hours (Requirement: < supplied number)
  if (isset($atts['hours'])) {
    $hours_val = intval($atts['hours']);
    $filter_clauses[] = "hours < $hours_val";
    $use_colors = false; // Requirement: Coloring only occurs if NO parameters are used
  }

  // Filter by Type (Requirement: match supplied type)
  if (isset($atts['type'])) {
    $type_val = sanitize_text_field($atts['type']);
    $filter_clauses[] = "type = '$type_val'";
    $use_colors = false; // Requirement: Coloring only occurs if NO parameters are used
  }

  // Build SQL Query
  $sql = "SELECT * FROM $table_name";
  if (!empty($filter_clauses)) {
    $sql .= " WHERE " . implode(' AND ', $filter_clauses);
  }
    
  $results = $wpdb->get_results($sql);

  // Output Buffer Start
  ob_start();

  // Inline CSS for the table styles
  echo '<style>
    .vol-table { width: 100%; border-collapse: collapse; margin-top: 15px; border: 1px solid #ddd; }
    .vol-table th, .vol-table td { border: 1px solid #ddd; padding: 10px; text-align: left; }
    .vol-table th { background-color: #f2f2f2; }
    .vol-green { background-color: #90EE90; } /* Green: < 10 hours */
    .vol-yellow { background-color: #FFFFE0; } /* Yellow: 10-100 hours */
    .vol-red { background-color: #FF7F7F; }    /* Red: > 100 hours */
    </style>';

  echo '<table class="vol-table">';
  echo '<thead><tr>
    <th>Position</th>
    <th>Organization</th>
    <th>Type</th>
    <th>Location</th>
    <th>Hours</th>
    <th>Contact</th>
    <th>Skills</th>
  </tr></thead><tbody>';

  if ($results) {
    foreach ($results as $row) {
      $row_class = '';

      // Apply Coloring Logic ONLY if no filters are active
      if ($use_colors) {
        if ($row->hours < 10) {
          $row_class = 'vol-green';
        } elseif ($row->hours >= 10 && $row->hours <= 100) {
          $row_class = 'vol-yellow';
        } elseif ($row->hours > 100) {
          $row_class = 'vol-red';
        }
      }

      echo '<tr class="' . $row_class . '">';
      echo '<td>' . esc_html($row->position) . '</td>';
      echo '<td>' . esc_html($row->organization) . '</td>';
      echo '<td>' . esc_html($row->type) . '</td>';
      echo '<td>' . esc_html($row->location) . '</td>';
      echo '<td>' . esc_html($row->hours) . '</td>';
      echo '<td><a href="mailto:' . esc_attr($row->email) . '">Email</a></td>';
      echo '<td>' . esc_html($row->skills) . '</td>';
      echo '</tr>';
    }
  } else {
    echo '<tr><td colspan="7">No opportunities found matching your criteria.</td></tr>';
  }

  echo '</tbody></table>';

  // Return the buffer content
  return ob_get_clean();
}
add_shortcode('volunteer', 'volunteer_shortcode_func');

?>