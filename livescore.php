<?php
/*
Plugin Name: Personal Livescore
Plugin URI: https://getbutterfly.com/wordpress-plugins/personal-livescore/
Description: This plugin allows the administrator to run and maintain a livescore system without the need to sign up for various web services or feeds. The plugin is completely standalone.
Author: Ciprian Popescu
Author URI: https://getbutterfly.com/
Version: 3.1.0

Personal Livescore
Copyright (C) 2013-2018 Ciprian Popescu (getbutterfly@gmail.com)

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

define('PL_PLUGIN_URL', WP_PLUGIN_URL . '/' . dirname(plugin_basename(__FILE__)));

include 'includes/livescore-content.php';

// plugin localization
$plugin_dir = basename(dirname(__FILE__));
load_plugin_textdomain('pl', false, $plugin_dir . '/languages');

function setOptionsPL() {
    global $wpdb;

    $table_name = $wpdb->prefix . 'livescore';
    if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
        $sql = "CREATE TABLE " . $table_name . " (
			`pl_id` int(11) NOT NULL AUTO_INCREMENT,
			`pl_datetime` datetime NOT NULL,
			`pl_sport` text NOT NULL,
			`pl_location` text NOT NULL,
			`pl_team1` text NOT NULL,
			`pl_team2` text NOT NULL,
			`pl_set` int(11) NOT NULL DEFAULT '0',
			`pl_score1` int(11) NOT NULL DEFAULT '0',
			`pl_score2` int(11) NOT NULL DEFAULT '0',
			`pl_active` tinyint(4) NOT NULL DEFAULT '1',
			PRIMARY KEY (`pl_id`)
		);";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta($sql);
	}

	$table_name = $wpdb->prefix . 'livescore_goals';
	if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
		$sql = "CREATE TABLE " . $table_name . " (
			`goal_id` int(11) NOT NULL AUTO_INCREMENT,
			`goal_match_id` int(11) NOT NULL,
			`goal_team_name` text NOT NULL,
			`goal_scorer` text NOT NULL,
			`goal_time` text NOT NULL,
			UNIQUE KEY `goal_id` (`goal_id`)
		);";
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta($sql);
	}

	add_option('pl_showembed', 1);
	add_option('pl_archive_link', '');

	add_option('pl_label_live', 'Live now');
	add_option('pl_label_upcoming', 'Upcoming matches');
	add_option('pl_label_archive', 'Archive');
	add_option('pl_copyright_line', 'Livescore Widget &copy;2017 <a href="#">Your Site</a>');

	add_option('pl_archive_limit', 10);
	add_option('pl_refresh_interval', 5);
}

register_activation_hook(__FILE__, 'setOptionsPL');

function unsetOptionsPL() {
	global $wpdb;
	$wpdb->query("DROP TABLE IF EXISTS " . $wpdb->prefix . "livescore");

	delete_option('pl_showembed');
	delete_option('pl_archive_link');
	delete_option('pl_label_live');
	delete_option('pl_label_upcoming');
	delete_option('pl_label_archive');
	delete_option('pl_copyright_line');
	delete_option('pl_archive_limit');
	delete_option('pl_refresh_interval');
}

register_uninstall_hook(__FILE__, 'unsetOptionsPL');
####

#### ADMIN STYLES ####
function load_livescore_wp_admin_style() {
	wp_enqueue_script('pl-calendar-script', plugins_url('/js/jCalendar.js', __FILE__));
	wp_enqueue_style('pl-calendar', PL_PLUGIN_URL . '/css/jCalendar.css', false, '1.0.0');
}
add_action('admin_enqueue_scripts', 'load_livescore_wp_admin_style');

#### ADMIN OPTIONS ####
function personal_livescore() {
	add_options_page('Livescore', 'Livescore', 'manage_options', 'pl', 'livescoreContent');
}
add_action('admin_menu', 'personal_livescore');

function livescoreContent() {
	global $wpdb;

    $table_name = $wpdb->prefix . 'livescore';
	?>
	<div class="wrap">
		<h2>Personal Livescore</h2>

		<?php
		$active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'gb_settings';
		if(isset($_GET['tab']))
			$active_tab = $_GET['tab'];
		?>
		<h2 class="nav-tab-wrapper">
			<a href="?page=pl&amp;tab=gb_settings" class="nav-tab <?php echo $active_tab == 'gb_settings' ? 'nav-tab-active' : ''; ?>"><?php _e('Settings', 'pl'); ?></a>
			<a href="?page=pl&amp;tab=gb_livescore_manager" class="nav-tab <?php echo $active_tab == 'gb_livescore_manager' ? 'nav-tab-active' : ''; ?>"><?php _e('Livescore Manager', 'pl'); ?></a>
			<a href="?page=pl&amp;tab=gb_livescore_upcoming" class="nav-tab <?php echo $active_tab == 'gb_livescore_upcoming' ? 'nav-tab-active' : ''; ?>"><?php _e('Livescore Manager (Upcoming)', 'pl'); ?></a>
			<a href="?page=pl&amp;tab=gb_livescore_archive" class="nav-tab <?php echo $active_tab == 'gb_livescore_archive' ? 'nav-tab-active' : ''; ?>"><?php _e('Archive', 'pl'); ?></a>
			<a href="?page=pl&amp;tab=gb_goal_manager" class="nav-tab <?php echo $active_tab == 'gb_goal_manager' ? 'nav-tab-active' : ''; ?>"><?php _e('Goal Manager', 'pl'); ?></a>
		</h2>

		<?php if($active_tab == 'gb_settings') { ?>
			<?php
			if(isset($_POST['gb_save'])) {
				update_option('pl_showembed', $_POST['pl_showembed']);
				update_option('pl_archive_link', $_POST['pl_archive_link']);

				update_option('pl_label_live', $_POST['pl_label_live']);
				update_option('pl_label_upcoming', $_POST['pl_label_upcoming']);
				update_option('pl_label_archive', $_POST['pl_label_archive']);
				update_option('pl_copyright_line', stripslashes_deep($_POST['pl_copyright_line']));

				update_option('pl_archive_limit', stripslashes_deep($_POST['pl_archive_limit']));
				update_option('pl_refresh_interval', stripslashes_deep($_POST['pl_refresh_interval']));

				echo '<div class="notice notice-success is-dismissible"><p>Settings updated successfully!</p></div>';
			}
			?>
			<h2><b>Personal Livescore</b> Description</h2>
			<p>
                This plugin allows the administrator to run and maintain a livescore system without the need to sign up for various web services or feeds. The plugin is completely standalone.<br>
                For more information and updates, visit the <a href="https://getbutterfly.com/" rel="external">official web site</a>
            </p>
            <hr>

            <h3><?php _e('Livescore Settings', 'pl'); ?></h3>
            <form method="post">
				<?php settings_fields('pl_options'); ?>
				<table class="form-table">
					<tr valign="top">
						<th scope="row"><legend>Livescore refresh interval</legend></th>
						<td>
							<input type="number" min="1" name="pl_refresh_interval" id="pl_refresh_interval" value="<?php echo get_option('pl_refresh_interval'); ?>">
							<span class="description">Livescore refresh interval (default is 5 second). Increase for high traffic websites.</span>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><legend>Header label (live games)</legend></th>
						<td>
							<input type="text" name="pl_label_live" id="pl_label_live" value="<?php echo get_option('pl_label_live'); ?>">
							<span class="description">Header text to display for live games.</span>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><legend>Header label (upcoming games)</legend></th>
						<td>
							<input type="text" name="pl_label_upcoming" id="pl_label_upcoming" value="<?php echo get_option('pl_label_upcoming'); ?>">
							<span class="description">Header text to display for upcoming games.</span>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><legend>Header label (archived games)</legend></th>
						<td>
							<input type="text" name="pl_label_archive" id="pl_label_archive" value="<?php echo get_option('pl_label_archive'); ?>">
							<span class="description">Header text to display for archived games.</span>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><legend>Copyright line</legend></th>
						<td>
							<input class="large-text" type="text" name="pl_copyright_line" id="pl_copyright_line" value="<?php echo esc_textarea(get_option('pl_copyright_line')); ?>">
							<span class="description">Text to display as copyright. HTML tags are allowed.</span>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><legend>Link to archive</legend></th>
						<td>
							<input class="large-text" type="text" name="pl_archive_link" id="pl_archive_link" value="<?php echo esc_textarea(get_option('pl_archive_link')); ?>">
							<span class="description">Link to archived scores. Uses a page with shortcode. Leave blank to disable.</span>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><legend>Archived scores limit</legend></th>
						<td>
							<input type="number" min="0" max="9999" name="pl_archive_limit" id="pl_archive_limit" value="<?php echo esc_textarea(get_option('pl_archive_limit')); ?>">
							<span class="description">How many archived links to display on the archive page.</span>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><legend>Show EMBED code below livescore</legend></th>
						<td>
							<?php echo get_option('pl_showembed') == '1' ? '<input type="checkbox" name="pl_showembed" id="pl_showembed" value="1" checked>' : '<input type="checkbox" name="pl_showembed" id="pl_showembed" value="1">'; ?>
							<label for="pl_showembed">Show EMBED code below livescore widget</label>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><input class="button-primary" type="submit" name="gb_save" value="Save Options" /></th>
						<td></td>
					</tr>
				</table>
			</form>
	
            <p>A game/match status is <b>0</b> if the game has passed (archive), <b>1</b> if the game is active (live) or <b>2</b> if the game is in the future (upcoming). A status of <b>3</b> indicates a future match, but hidden from widget view.</p>
			<p>Use <code>&lt;?php echo do_shortcode('[livescore]'); ?&gt;</code> to display the livescore widget anywhere.</p>
		<?php } if($active_tab == 'gb_livescore_manager') { ?>
			<?php
            global $wpdb;

            if(isset($_POST['gb_livescore_go'])) {
				$pl_id 			= $_POST['pl_id'];
				$pl_datetime 	= $_POST['pl_datetime'];
				$pl_sport 		= $_POST['pl_sport'];
				$pl_location 	= $_POST['pl_location'];
				$pl_team1 		= $_POST['pl_team1'];
				$pl_team2 		= $_POST['pl_team2'];
				$pl_score1 		= $_POST['pl_score1'];
				$pl_score2 		= $_POST['pl_score2'];
				$pl_set 		= $_POST['pl_set'];
				$pl_active 		= $_POST['pl_active'];

				$sql = "UPDATE $table_name SET 
					pl_datetime = '$pl_datetime',
					pl_sport = '$pl_sport',
					pl_location = '$pl_location',
					pl_team1 = '$pl_team1',
					pl_team2 = '$pl_team2',
					pl_score1 = '$pl_score1',
					pl_score2 = '$pl_score2',
					pl_set = '$pl_set',
					pl_active = '$pl_active'
				WHERE pl_id = '$pl_id'";

				$wpdb->query($sql);

				echo '<div class="notice notice-success is-dismissible"><p>Score updated successfully!</p></div>';
			}
			if(isset($_POST['gb_livescore_new'])) {
				$pl_datetime 	= $_POST['pl_datetime'];
				$pl_sport 		= $_POST['pl_sport'];
				$pl_location 	= $_POST['pl_location'];
				$pl_team1 		= $_POST['pl_team1'];
				$pl_team2 		= $_POST['pl_team2'];
				$pl_score1 		= $_POST['pl_score1'];
				$pl_score2 		= $_POST['pl_score2'];
				$pl_set 		= $_POST['pl_set'];
				$pl_active 		= $_POST['pl_active'];

				$sql = "INSERT INTO $table_name (pl_datetime, pl_sport, pl_location, pl_team1, pl_team2, pl_score1, pl_score2, pl_set, pl_active) VALUES ('$pl_datetime', '$pl_sport', '$pl_location', '$pl_team1', '$pl_team2', '$pl_score1', '$pl_score2', '$pl_set', '$pl_active')";
				$wpdb->query($sql);

				echo '<div class="notice notice-success is-dismissible"><p>Score added successfully!</p></div>';
			}
			?>
            <h3><?php _e('Livescore Manager'); ?></h3>
            <table class="wp-list-table widefat posts" cellpadding="0">
				<tbody>
					<?php
					echo '<form method="post">';
						echo '<tr>';
							echo '<td></td>';
							echo '<td>
								<input type="datetime" name="pl_datetime" id="pl_datetime" placeholder="Date &amp; Time" size="16">
								<label><span class="dashicons dashicons-calendar-alt pl-calendar" onclick="viewCalendar(\'jcalendar_parent\', \'pl_datetime\')"></span></label><br>
								<div id="jcalendar_parent" class="jcalendar_parent" style="z-index: 999"></div>
                                <input type="text" name="pl_sport" placeholder="Sport" size="12"><label>@</label>
                                <input type="text" name="pl_location" placeholder="Location" size="12">
                            </td>';
							echo '<td>
								<input type="text" name="pl_team1" placeholder="Team 1" size="12">
                                <input type="number" min="0" max="999" step="1" name="pl_score1" value="0">
                                <br>
								<input type="text" name="pl_team2" placeholder="Team 2" size="12">
                                <input type="number" min="0" max="999" step="1" name="pl_score2" value="0">
							</td>';
							echo '<td>
                                <input type="number" min="0" max="999" step="1" name="pl_set" value="0">
                            </td>';
							echo '<td>
                                <select name="pl_active">
                                    <option value="0">Archived</option>
                                    <option value="1" selected>Live</option>
                                    <option value="2">Upcoming</option>
                                    <option value="3">Upcoming (hidden)</option>
                                </select>
                            </td>';

                            echo '<td><input type="submit" name="gb_livescore_new" value="Add" class="button button-primary"></td>';
                        echo '</tr>';
				    echo '</form>';
					?>
				</tbody>
            </table>
            <hr>

            <table class="wp-list-table widefat posts" cellpadding="0">
				<thead>
					<tr>
						<th scope="col">ID</th>
						<th scope="col" class="manage-column asc">Date &amp; Time<br>Sport/Location</th>
						<th scope="col" class="manage-column">Teams/Score</th>
						<th scope="col" class="manage-column">Set<br><small>(optional)</small></th>
						<th scope="col" class="manage-column">Status</th>
						<th scope="col" class="manage-column"></th>
					</tr>
				</thead>
				<tbody>
					<?php
					// BEGIN PAGINATION HEAD
					$pr = 10; // rows per page
					$show = isset($_GET['show']) ? (int) $_GET['show'] : 1;

                    $pages = $wpdb->get_results("SELECT * FROM $table_name WHERE pl_active = 1", ARRAY_A);
					$pages = $wpdb->num_rows;
					$numpages = $pages;
					$pages = ceil($pages / $pr);

					$querystring = '';
					foreach($_GET as $key => $value) {
						if($key != 'show') $querystring .= "$key=$value&amp;";
					}
					// END PAGINATION HEAD

                    $results = $wpdb->get_results("SELECT * FROM $table_name WHERE pl_active = 1 LIMIT " . (($show - 1) * $pr) . ', ' . $pr, ARRAY_A);
					foreach($results as $row) {
						echo '<form method="post">';
							echo '<tr>';
								echo '<td>' . $row['pl_id'] . '</td>';
								echo '<td>
                                    <input type="datetime" name="pl_datetime" value="' . $row['pl_datetime'] . '" size="16"><br>
                                    <input type="text" name="pl_sport" value="' . $row['pl_sport'] . '" size="12"><label>@</label>
                                    <input type="text" name="pl_location" value="' . $row['pl_location'] . '" size="12">
                                </td>';
								echo '<td>
									<input type="text" name="pl_team1" value="' . $row['pl_team1'] . '" size="12">
                                    <input type="number" min="0" max="999" step="1" name="pl_score1" value="' . $row['pl_score1'] . '"><br>
									<input type="text" name="pl_team2" value="' . $row['pl_team2'] . '" size="12">
                                    <input type="number" min="0" max="999" step="1" name="pl_score2" value="' . $row['pl_score2'] . '">
								</td>';
								echo '<td>
                                    <input type="number" min="0" max="999" step="1" name="pl_set" value="' . $row['pl_set'] . '">
                                </td>';

                                $matchStatus = 'Live';

                                if ((int) $row['pl_active'] === 0) {
                                    $matchStatus = 'Live';
                                } else if ((int) $row['pl_active'] === 2) {
                                    $matchStatus = 'Upcoming';
                                } else if ((int) $row['pl_active'] === 3) {
                                    $matchStatus = 'Upcoming (hidden)';
                                }

                                echo '<td>
                                    <select name="pl_active">
                                        <option value="' . $row['pl_active'] . '">' . $matchStatus . '</option>
                                        <option value="0">Archived</option>
                                        <option value="1">Live</option>
                                        <option value="2">Upcoming</option>
                                        <option value="3">Upcoming (hidden)</option>
                                    </select>
                                </td>';
                                echo '<td>
								    <input type="hidden" name="pl_id" value="' . $row['pl_id'] . '">
									<input type="submit" name="gb_livescore_go" value="Update" class="button button-primary">
									<a class="button button-secondary" href="' . admin_url('options-general.php?page=pl&tab=gb_goal_manager&matchid=' . $row['pl_id']) . '">Add Goal</a>
								</td>';
				            echo '</tr>';
				        echo '</form>';
				    }
					?>
				</tbody>
            </table>
			<?php
			// BEGIN PAGINATION DISPLAY
			echo '<div class="tablenav">
				<div class="tablenav-pages">
					<span class="displaying-num">' . $numpages . ' items</span>
					<span class="pagination-links">';
						for($i = 1; $i <= $pages; $i++) {
							echo '<a ' . ($i == $show ? 'class="disabled" ' : ' ');
							echo 'href="?' . $querystring . 'show=' . $i;
							echo '">' . $i . '</a> ';
						}
						echo '</span>
				</div>
			</div>';
			// END PAGINATION DISPLAY
			?>
			<p><small><b>Note:</b> Delete games by archiving them and removing them from the <b>Archive</b> tab.</small></p>
		<?php } if($active_tab == 'gb_goal_manager') { ?>
			<?php
			if(isset($_GET['matchid'])) {
				$match_id = (int)$_GET['matchid'];
                $team_row = $wpdb->get_row("SELECT * FROM $table_name WHERE pl_id = $match_id", ARRAY_A);

				if(isset($_POST['gb_goal_edit'])) {
					$pl_goalscorer 	= $_POST['pl_goalscorer'];
					$pl_goaltime 	= $_POST['pl_goaltime'];
					$pl_goalteam 	= $_POST['pl_goalteam'];
					$goal_id 		= $_POST['goal_id'];

					$sql = "UPDATE " . $wpdb->prefix . "livescore_goals SET goal_team_name = '$pl_goalteam', goal_scorer = '$pl_goalscorer', goal_time = '$pl_goaltime' WHERE goal_id = '$goal_id'";

					$wpdb->query($sql);

					echo '<div class="notice notice-success is-dismissible"><p>Goal updated successfully!</p></div>';
				}
				if(isset($_POST['gb_goal_delete'])) {
					$goal_id 		= $_POST['goal_id'];

					$sql = "DELETE FROM " . $wpdb->prefix . "livescore_goals WHERE goal_id = '$goal_id'";

					$wpdb->query($sql);

					echo '<div class="notice notice-success is-dismissible"><p>Goal updated successfully!</p></div>';
				}
				if(isset($_POST['gb_livescore_goal'])) {
					$pl_goalscorer 	= $_POST['pl_goalscorer'];
					$pl_goaltime 	= $_POST['pl_goaltime'];
					$pl_goalteam 	= $_POST['pl_goalteam'];

					$sql = "INSERT INTO " . $wpdb->prefix . "livescore_goals (goal_match_id, goal_team_name, goal_scorer, goal_time) VALUES ('$match_id', '$pl_goalteam', '$pl_goalscorer', '$pl_goaltime')";
					$wpdb->query($sql);

					echo '<div class="notice notice-success is-dismissible"><p>Goal added successfully!</p></div>';
				}
				?>
                <h3><?php _e('Goal Manager'); ?></h3>
                <?php
				echo '<form method="post">';
				    echo '<input type="text" name="pl_goalscorer" placeholder="Goal Scorer" size="32"> ';
					echo '<input type="text" name="pl_goaltime" placeholder="Goal Time" size="8"> ';
					echo '<select name="pl_goalteam">
						<option value="' . $team_row['pl_team1'] . '">' . $team_row['pl_team1'] . '</option>
						<option value="' . $team_row['pl_team2'] . '">' . $team_row['pl_team2'] . '</option>
					</select> ';
					echo '<input type="submit" name="gb_livescore_goal" value="Goal!" class="button button-primary">';
				echo '</form>';
				?>

                <table class="wp-list-table widefat posts" cellpadding="0">
                    <thead>
						<tr>
							<th scope="col" class="manage-column asc">Goal Time</th>
							<th scope="col" class="manage-column">Goal Scorer</th>
							<th scope="col" class="manage-column">Team</th>
							<th scope="col" class="manage-column"></th>
						</tr>
					</thead>
					<tbody>
					   <?php
						$results = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "livescore_goals WHERE goal_match_id = $match_id", ARRAY_A);
						foreach($results as $row) {
				            echo '<form method="post">';
                                echo '<tr>';
								    echo '<td><input type="text" name="pl_goaltime" value="' . $row['goal_time'] . '" size="8"></td>';
									echo '<td><input type="text" name="pl_goalscorer" value="' . $row['goal_scorer'] . '" size="64"></td>';
									echo '<td><input type="text" name="pl_goalteam" value="' . $row['goal_team_name'] . '" size="32"></td>';

									echo '<td>
										<input type="hidden" name="goal_id" value="' . $row['goal_id'] . '">
										<input type="submit" name="gb_goal_edit" value="Edit" class="button button-primary">
										<input type="submit" name="gb_goal_delete" value="Delete" class="button button-secondary">
									</td>';
                                echo '</tr>';
				            echo '</form>';
				        }
						?>
				    </tbody>
				</table>
			<?php } else { // if match_id is not set ?>
                <h3><?php _e('Goal Manager'); ?></h3>
                <p><b>No team selected!</b></p>
				<p>Please select a team from <b>Livescore Manager</b> and press the &rarr; button to add/manage goals.</p>
            <?php } ?>
		<?php } if($active_tab == 'gb_livescore_upcoming') { ?>
			<?php
            global $wpdb;

            if(isset($_POST['gb_livescore_go'])) {
				$pl_id 			= $_POST['pl_id'];
				$pl_datetime 	= $_POST['pl_datetime'];
				$pl_sport 		= $_POST['pl_sport'];
				$pl_location 	= $_POST['pl_location'];
				$pl_team1 		= $_POST['pl_team1'];
				$pl_team2 		= $_POST['pl_team2'];
				$pl_score1 		= $_POST['pl_score1'];
				$pl_score2 		= $_POST['pl_score2'];
				$pl_set 		= $_POST['pl_set'];
				$pl_active 		= $_POST['pl_active'];

				$sql = "UPDATE $table_name SET 
					pl_datetime = '$pl_datetime',
					pl_sport = '$pl_sport',
					pl_location = '$pl_location',
					pl_team1 = '$pl_team1',
					pl_team2 = '$pl_team2',
					pl_score1 = '$pl_score1',
					pl_score2 = '$pl_score2',
					pl_set = '$pl_set',
					pl_active = '$pl_active'
				WHERE pl_id = '$pl_id'";

				$wpdb->query($sql);

				echo '<div class="notice notice-success is-dismissible"><p>Score updated successfully!</p></div>';
			}
			?>
            <h3><?php _e('Livescore Manager (Upcoming)'); ?></h3>
            <table class="wp-list-table widefat posts" cellpadding="0">
				<thead>
					<tr>
						<th scope="col" class="manage-column asc">Date &amp; Time<br>Sport/Location</th>
						<th scope="col" class="manage-column">Teams</th>
						<th scope="col" class="manage-column">Score/Set</th>
						<th scope="col" class="manage-column">Status</th>
						<th scope="col" class="manage-column"></th>
					</tr>
				</thead>
				<tbody>
					<?php
					// BEGIN PAGINATION HEAD
					$pr = 10; // rows per page
					$show = isset($_GET['show']) ? (int) $_GET['show'] : 1;

                    $pages = $wpdb->get_results("SELECT * FROM $table_name WHERE pl_active IN (2, 3)", ARRAY_A);
					$pages = $wpdb->num_rows;
					$numpages = $pages;
					$pages = ceil($pages / $pr);

					$querystring = '';
					foreach($_GET as $key => $value) {
						if($key != 'show') $querystring .= "$key=$value&amp;";
					}
					// END PAGINATION HEAD

					$pl_visibility = '';
                    $results = $wpdb->get_results("SELECT * FROM $table_name WHERE pl_active IN (2, 3) LIMIT " . (($show - 1) * $pr) . ', ' . $pr, ARRAY_A);
					foreach($results as $row) {
						// check for upcoming but hidden (status: 3)
						if($row['pl_active'] == 3) $pl_visibility = ' style="opacity: 0.6;"';
						else $pl_visibility = '';

						echo '<form method="post">';
							echo '<tr' . $pl_visibility . '>';
								echo '<td>
                                    <input type="datetime" name="pl_datetime" value="' . $row['pl_datetime'] . '"><br>
                                    <input type="text" name="pl_sport" value="' . $row['pl_sport'] . '" size="12"><label>@</label>
                                    <input type="text" name="pl_location" value="' . $row['pl_location'] . '" size="12">
                                </td>';
								echo '<td>
									<input type="text" name="pl_team1" value="' . $row['pl_team1'] . '" size="12"><label>vs</label><br>
									<input type="text" name="pl_team2" value="' . $row['pl_team2'] . '" size="12">
								</td>';
								echo '<td>
									<input type="number" min="0" max="999" step="1" name="pl_score1" value="' . $row['pl_score1'] . '"><label>:</label>
									<input type="number" min="0" max="999" step="1" name="pl_score2" value="' . $row['pl_score2'] . '"><label>/</label>
                                    <input type="number" min="0" max="999" step="1" name="pl_set" value="' . $row['pl_set'] . '">
                                </td>';
								echo '<td><input type="number" min="0" max="3" step="1" name="pl_active" value="' . $row['pl_active'] . '"></td>';

								echo '<td>
									<input type="hidden" name="pl_id" value="' . $row['pl_id'] . '">
									<input type="submit" name="gb_livescore_go" value="Go" class="button button-primary">
								</td>';
                            echo '</tr>';
				        echo '</form>';
				    }
					?>
				</tbody>
            </table>
            <?php
			// BEGIN PAGINATION DISPLAY
			echo '<div class="tablenav">
				<div class="tablenav-pages">
					<span class="displaying-num">' . $numpages . ' items</span>
					<span class="pagination-links">';
						for($i = 1; $i <= $pages; $i++) {
							echo '<a ' . ($i == $show ? 'class="disabled" ' : ' ');
							echo 'href="?' . $querystring.'show=' . $i;
							echo '">' . $i . '</a> ';
						}
					echo '</span>
				</div>
			</div>';
			// END PAGINATION DISPLAY
			?>
		<?php } if($active_tab == 'gb_livescore_archive') { ?>
			<?php
			if(isset($_POST['gb_livescore_del'])) {
				$pl_id = $_POST['pl_id'];
                $wpdb->query($wpdb->prepare("DELETE FROM $table_name WHERE pl_id = %d", $pl_id));

				echo '<div class="notice notice-success is-dismissible"><p>Score deleted successfully!</p></div>';
			}
			?>
            <h3><?php _e('Archive'); ?></h3>
            <table class="wp-list-table widefat posts" cellpadding="0">
				<thead>
					<tr>
						<th scope="col" class="manage-column asc">Date &amp; Time</th>
						<th scope="col" class="manage-column">Sport</th>
						<th scope="col" class="manage-column">Location</th>
						<th scope="col" class="manage-column">Teams</th>
						<th scope="col" class="manage-column">Score</th>
						<th scope="col" class="manage-column">Set</th>
						<th scope="col" class="manage-column"></th>
					</tr>
                </thead>
                <tbody>
                    <?php
					// BEGIN PAGINATION HEAD
					$pr = 10; // rows per page
					$show = isset($_GET['show']) ? (int) $_GET['show'] : 1;

                    $pages = $wpdb->get_results("SELECT * FROM $table_name WHERE pl_active = 0", ARRAY_A);
					$pages = $wpdb->num_rows;
					$numpages = $pages;
					$pages = ceil($pages / $pr);

					$querystring = '';
					foreach($_GET as $key => $value) {
						if($key != 'show') $querystring .= "$key=$value&amp;";
					}
					// END PAGINATION HEAD

                    $results = $wpdb->get_results("SELECT * FROM $table_name WHERE pl_active = 0 LIMIT " . (($show - 1) * $pr) . ', ' . $pr, ARRAY_A);
					foreach($results as $row) {
						echo '<form method="post">';
							echo '<tr>';
								echo '<td>' . $row['pl_datetime'] . '</td>';
								echo '<td>' . $row['pl_sport'] . '</td>';
								echo '<td>' . $row['pl_location'] . '</td>';
								echo '<td>' . $row['pl_team1'] . ' vs ' . $row['pl_team2'] . '</td>';
								echo '<td>' . $row['pl_score1'] . ':' . $row['pl_score2'] . '</td>';
								echo '<td>SET ' . $row['pl_set'] . '</td>';
								echo '<td>
									<input type="hidden" name="pl_id" value="' . $row['pl_id'] . '">
									<input type="submit" name="gb_livescore_del" value="Delete" class="button button-primary">
								</td>';
                            echo '</tr>';
				        echo '</form>';
				    }
					?>
				</tbody>
            </table>
			<?php
			// BEGIN PAGINATION DISPLAY
			echo '<div class="tablenav">
				<div class="tablenav-pages">
					<span class="displaying-num">' . $numpages . ' items</span>
					<span class="pagination-links">';
						for($i = 1; $i <= $pages; $i++) {
							echo '<a ' . ($i == $show ? 'class="disabled" ' : ' ');
							echo 'href="?' . $querystring . 'show=' . $i;
							echo '">' . $i . '</a> ';
						}
					echo '</span>
				</div>
            </div>';
			// END PAGINATION DISPLAY
			?>
		<?php } ?>
	</div>	
	<?php
}
####


function livescore_display($atts) {
	extract(shortcode_atts(array(
		'mode' => 'full',
		'id' => 0,
	), $atts));

	global $wpdb;

	$display = '';

	if ((string) $mode === 'archive') {
		// Display live scores
        $results = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "livescore WHERE pl_active=0 ORDER BY pl_datetime DESC LIMIT " . get_option('pl_archive_limit'), ARRAY_A);

		$display .= '<div class="pl_archive">
			<div class="pl-header"><span class="pl-category">' . get_option('pl_label_archive') . '</span></div>';

            foreach ($results as $row) {
				if ((int) $row['pl_set'] !== 0) {
                    $pl_set = ' <span class="pl-set">SET ' . $row['pl_set'] . '</span>';
                }

                $display .= '<div id="row">
                    <div class="pl-details">' . $row['pl_sport'] . ', ' . $row['pl_location'] . ', ' . $row['pl_datetime'] . '</div>
                    <div class="pl-column-large">' . $row['pl_team1'] . '</div><div class="pl-column-small">' . $row['pl_score1'] . $pl_set . '</div>
                    <div class="pl-column-large">' . $row['pl_team2'] . '</div><div class="pl-column-small">' . $row['pl_score2'] . $pl_set . '</div>
                </div>';

                $pl_set = '';
            }
        $display .= '</div>';
    } else {
        $display .= '<div id="online" class="pl" rel="' . $id . '">' . __('Livescore is loading...', 'pl') . '</div>';

        if ((int) get_option('pl_showembed') === 1) {
            $display .= '<div id="online-embed-code"><textarea rows="4">&lt;iframe src="' . PL_PLUGIN_URL . '/livescore-embed.php" width="100%" height="600"&gt;&lt;/iframe&gt;</textarea></div>';
        }
    }

    return $display;
}
add_shortcode('livescore', 'livescore_display');

function livescore_enqueue() {
    $pl_refresh_interval = (int) (get_option('pl_refresh_interval') * 1000);

    echo '<script>
    jQuery(document).ready(function() {
        var rel = jQuery(".pl").attr("rel");
        jQuery("#online").load("' . PL_PLUGIN_URL . '/includes/pl-refresh.php?rel=" + rel);
        var refreshId = setInterval(function() {
            jQuery("#online").load("' . PL_PLUGIN_URL . '/includes/pl-refresh.php?randval=" + Math.random() + "&rel=" + rel);
        }, ' . $pl_refresh_interval . ');
    });
    </script>';
}

add_action('wp_head', 'livescore_enqueue');
add_action('wp_enqueue_scripts', 'livescore_enqueue_styles');

function livescore_enqueue_styles() {
    wp_enqueue_style('livescore', plugins_url('css/style.css', __FILE__));
}
