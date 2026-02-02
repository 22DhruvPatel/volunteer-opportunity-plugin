<?php
/*
Plugin Name: Volunteer Opportunity Plugin
Description: A plugin to manage and list volunteer opportunity
Version: 1.0
Author: Dhruv Patel
*/



// Activation hook: Creates the database table on plugin activation
function volunteer_activate() {
  global $wpdb;

  // We use dbDelta to create the table, which is the standard WordPress way
  $table_name = $wpdb->prefix . 'volunteer';
  $charset_collate = $wpdb->get_charset_collate();

  $sql = "CREATE TABLE $table_name (
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
  ) $charset_collate;";

  require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
  dbDelta($sql);
}
register_activation_hook(__FILE__, 'volunteer_activate');

// Deactivation hook: Clears data but keeps the table structure
function volunteer_deactivate() {
  global $wpdb;
  $table_name = $wpdb->prefix . 'volunteer';
  $sql = "TRUNCATE TABLE $table_name";
  $wpdb->query($sql);
}
register_deactivation_hook(__FILE__, 'volunteer_deactivate');

// Uninstall hook: Completely removes the table from the database
function volunteer_uninstall() {
  global $wpdb;
  $table_name = $wpdb->prefix . 'volunteer';
  // Drop table when uninstalled the plugin
  $wpdb->query("DROP TABLE IF EXISTS $table_name");
}
register_uninstall_hook(__FILE__, 'volunteer_uninstall');




// Register the Admin Menu page
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

// Main function to render the Admin Page (Handle CRUD operations)
function volunteer_ops_page_html() {
    // Security Check: Ensure user has permission to manage options
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }

    global $wpdb;
    $table_name = $wpdb->prefix . 'volunteer';
    $message = '';

    // Initialize variables for the form (empty by default)
    $entry_id = 0;
    $position = '';
    $organization = '';
    $type = 'One-time';
    $email = '';
    $description = '';
    $location = '';
    $hours = '';
    $skills = '';

    //DELETE LOGIC
    if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
        $wpdb->delete($table_name, array('id' => intval($_GET['id'])));
        $message = "Opportunity Deleted.";
    }

    //EDIT LOGIC (Pre-fill form)
    if (isset($_GET['action']) && $_GET['action'] == 'edit' && isset($_GET['id'])) {
        $entry_id = intval($_GET['id']);
        // Fetch existing data to populate the input fields
        $result = $wpdb->get_row("SELECT * FROM $table_name WHERE id = $entry_id");
        if ($result) {
            $position = $result->position;
            $organization = $result->organization;
            $type = $result->type;
            $email = $result->email;
            $description = $result->description;
            $location = $result->location;
            $hours = $result->hours;
            $skills = $result->skills;
        }
    }

    //SAVE LOGIC (Insert or Update)
    if (isset($_POST['submit'])) {
        // Sanitize and Validate inputs
        $hours = intval($_POST['hours']);
        
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

        // Check if we are Updating an existing entry or Creating a new one
        if (isset($_POST['entry_id']) && $_POST['entry_id'] != 0) {
            // Update existing record
            $wpdb->update($table_name, $data, array('id' => intval($_POST['entry_id'])));
            $message = "Opportunity Updated Successfully!";
        } else {
            // Insert new record
            $wpdb->insert($table_name, $data);
            $message = "Opportunity Saved Successfully!";
            
            // Reset form variables after successful save
            $entry_id = 0; $position = ''; $organization = ''; $email = ''; 
            $description = ''; $location = ''; $hours = ''; $skills = '';
        }
    }

    ?>
    <div class="wrap">
        <h1>Volunteer Opportunities</h1>
        <?php if ($message) echo "<div class='updated notice is-dismissible'><p>$message</p></div>"; ?>

        <div style="background:#fff; padding:20px; border:1px solid #ccc; margin-bottom: 20px;">
            <h2><?php echo ($entry_id != 0) ? 'Edit Opportunity' : 'Add New Opportunity'; ?></h2>
            <form method="post">
                <input type="hidden" name="entry_id" value="<?php echo esc_attr($entry_id); ?>">
                <table class="form-table">
                    <tr>
                        <th><label>Position</label></th>
                        <td><input type="text" name="position" class="regular-text" value="<?php echo esc_attr($position); ?>" required></td>
                    </tr>
                    <tr>
                        <th><label>Organization</label></th>
                        <td><input type="text" name="organization" class="regular-text" value="<?php echo esc_attr($organization); ?>" required></td>
                    </tr>
                    <tr>
                        <th><label>Type</label></th>
                        <td>
                            <select name="type">
                                <option value="One-time" <?php selected($type, 'One-time'); ?>>One-time</option>
                                <option value="Recurring" <?php selected($type, 'Recurring'); ?>>Recurring</option>
                                <option value="Seasonal" <?php selected($type, 'Seasonal'); ?>>Seasonal</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th><label>Email</label></th>
                        <td><input type="email" name="email" class="regular-text" value="<?php echo esc_attr($email); ?>" required></td>
                    </tr>
                    <tr>
                        <th><label>Description</label></th>
                        <td><textarea name="description" rows="3" class="large-text"><?php echo esc_textarea($description); ?></textarea></td>
                    </tr>
                    <tr>
                        <th><label>Location</label></th>
                        <td><input type="text" name="location" class="regular-text" value="<?php echo esc_attr($location); ?>" required></td>
                    </tr>
                    <tr>
                        <th><label>Hours</label></th>
                        <td><input type="number" name="hours" class="small-text" value="<?php echo esc_attr($hours); ?>" required></td>
                    </tr>
                    <tr>
                        <th><label>Skills</label></th>
                        <td><input type="text" name="skills" class="large-text" value="<?php echo esc_attr($skills); ?>" placeholder="e.g. communication, coding"></td>
                    </tr>
                </table>

                <p class="submit">
                    <input type="submit" name="submit" value="<?php echo ($entry_id != 0) ? 'Update Opportunity' : 'Save Opportunity'; ?>" class="button button-primary">
                    <?php if ($entry_id != 0) : ?>
                        <a href="<?php echo admin_url('admin.php?page=volunteer_ops'); ?>" class="button">Cancel</a>
                    <?php endif; ?>
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
                $results = $wpdb->get_results("SELECT * FROM $table_name");
                
                if ($results) {
                    foreach ($results as $row) {
                        $edit_link = admin_url('admin.php?page=volunteer_ops&action=edit&id=' . $row->id);
                        $delete_link = admin_url('admin.php?page=volunteer_ops&action=delete&id=' . $row->id);
                        echo "<tr>";
                        echo "<td>" . esc_html($row->position) . "</td>";
                        echo "<td>" . esc_html($row->organization) . "</td>";
                        echo "<td>" . esc_html($row->type) . "</td>";
                        echo "<td>" . esc_html($row->hours) . "</td>";
                        echo "<td>" . esc_html($row->location) . "</td>";
                        echo "<td>
                                <a href='$edit_link' class='button button-small'>Edit</a> 
                                <a href='$delete_link' class='button button-small button-link-delete' onclick=\"return confirm('Are you sure you want to delete this?')\">Delete</a>
                              </td>";
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

  // Logic: Filter by Hours
  if (isset($atts['hours'])) {
    $hours_val = intval($atts['hours']);
    $filter_clauses[] = "hours < $hours_val";
    $use_colors = false; // Disable colors if filtering by hours
  }

  // Logic: Filter by Type 
  if (isset($atts['type'])) {
    $type_val = sanitize_text_field($atts['type']);
    $filter_clauses[] = "type = '$type_val'";
    $use_colors = false; // Disable colors if filtering by type
  }

  // Build SQL Query dynamically
  $sql = "SELECT * FROM $table_name";
  if (!empty($filter_clauses)) {
    $sql .= " WHERE " . implode(' AND ', $filter_clauses);
  }
    
  $results = $wpdb->get_results($sql);

  // Output Buffer Start (to return HTML string)
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