<?php
require_once '../../../../wp-config.php';
global $wpdb;

$display = '';
$pl_set = '';

// display live scores
if(isset($_GET['rel'])) {
    $rel = $_GET['rel'];
}
if($rel != 0)
    $results = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "livescore WHERE pl_active=1 AND pl_id=" . $rel . " ORDER BY pl_datetime DESC LIMIT 1", ARRAY_A);
else
    $results = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "livescore WHERE pl_active=1 ORDER BY pl_datetime DESC", ARRAY_A);

$pl_is_active = '<div class="pl-active-live">' . __('LIVE', 'pl') . '</div>';

$display .= '<div class="pl-header"><span class="pl-category">' . get_option('pl_label_live') . '</span></div>';

foreach($results as $row) {
	if($row['pl_set'] != 0) $pl_set = ' <span class="pl-set">' . __('SET', 'pl') . ' ' . $row['pl_set'] . '</span>';
	$display .= '<div id="row" class="pl-section">';
		$display .= '<div class="pl-details"><small>' . $row['pl_sport'] . ', ' . $row['pl_location'] . '</small><br>' . $row['pl_datetime'] . $pl_is_active . '</div>';
		$display .= '<div class="pl-column-large">' . $row['pl_team1'] . '</div><div class="pl-column-small">' . $row['pl_score1'] . $pl_set . '</div>';
		$display .= '<div class="pl-column-large">' . $row['pl_team2'] . '</div><div class="pl-column-small">' . $row['pl_score2'] . $pl_set . '</div>';

		// begin display goals
        $goals_results = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "livescore_goals WHERE goal_match_id = " . $row['pl_id'], ARRAY_A);
		if($wpdb->num_rows > 0) {
			$display .= '<div class="pl-goals-container">';
				$display .= '<div class="pl-details"><small>Match details</small></div>';
                foreach($goals_results as $goals_row) {
					$display .= '<div class="pl-column-large pl-opacity">' . $goals_row['goal_time'] . ' ' . $goals_row['goal_scorer'] . '</div><div class="pl-column-small pl-opacity">&rarr; ' . $goals_row['goal_team_name'] . '</div>';
				}
			$display .= '</div>';
		}
		// end display goals

	$display .= '</div>';
	$pl_set = '';
}

// display upcoming scores
$results = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "livescore WHERE pl_active=2 ORDER BY pl_datetime DESC", ARRAY_A);

$pl_is_active = '<div class="pl-active-upcoming">&raquo;</div>';

$display .= '<div class="pl-header"><span class="pl-category">' . get_option('pl_label_upcoming') . '</span></div>';
foreach($results as $row) {
	$display .= '<div id="row" class="pl-section">';
		$display .= '<div class="pl-details"><small>' . $row['pl_sport'] . ', ' . $row['pl_location'] . '</small><br>' . $row['pl_datetime'] . $pl_is_active . '</div>';
		$display .= '<div class="pl-column-large">' . $row['pl_team1'] . '</div><div class="pl-column-small">?</div>';
		$display .= '<div class="pl-column-large">' . $row['pl_team2'] . '</div><div class="pl-column-small">?</div>';
	$display .= '</div>';
}

// display link to archive
if(get_option('pl_archive_link') != '')
	$display .= '<div class="pl-archive-link"><a href="' . get_option('pl_archive_link') . '">' . __('Archive', 'pl') . '</a> &raquo;</div>';

// banner ad
// $display .= '<div class="pl-ad-link"></div>';

$display .= '<div class="pl-archive-link">' . get_option('pl_copyright_line') . '</div>';

echo $display;
