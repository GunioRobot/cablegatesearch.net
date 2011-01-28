<?php
include_once('cacher.php');
include_once("cablegate-functions.php");

$command = '';
if (isset($_REQUEST['command'])) {
	$command = $_REQUEST['command'];
	}

switch ($command) {
	case 'get_cable_content':
		header_cache(60*12);
		if (!isset($_REQUEST['id']) || !ctype_digit($_REQUEST['id'])) {exit("{}");}
		$cable_id = (int)$_REQUEST['id'];
		$cache_id = "cable_preview_{$cable_id}";
		if ( !db_output_compressed_cache($cache_id) ) {
			db_open_compressed_cache($cache_id);
			// -----
			$result = get_cable_content($cable_id);
			if (empty($result)) {db_cancel_cache();}
			echo json_encode(cp1252_to_utf8($result));
			// -----
			db_close_compressed_cache();
			}
		break;

	case 'get_cable_entries':
		header_cache(30);
		$raw_query = isset($_REQUEST['raw_query']) ? utf8_to_cp1252($_REQUEST['raw_query']) : '';
		$sort = isset($_REQUEST['sort']) && is_numeric($_REQUEST['sort']) ? max((int)min(floor((int)$_REQUEST['sort']),1),0) : 0;
		$year_upper_limit = $sort ? 2099 : 2010;
		$year_lower_limit = $sort ? 2010 : 1966;
		$yt = isset($_REQUEST['yt']) && is_numeric($_REQUEST['yt']) ? max(min((int)floor((int)$_REQUEST['yt']),$year_upper_limit),$year_lower_limit) : $year_upper_limit;
		$mt = isset($_REQUEST['mt']) && is_numeric($_REQUEST['mt']) ? max(min((int)floor((int)$_REQUEST['mt']),12),1) : 12;
		$offset = isset($_REQUEST['offset']) && ctype_digit($_REQUEST['offset']) ? (int)$_REQUEST['offset'] : 0;
		$limit = isset($_REQUEST['limit']) && ctype_digit($_REQUEST['limit']) ? (int)$_REQUEST['limit'] : 0;
		$cache_id = sprintf('searchlines_%d_%d-%02d_%d_%d_%s', $sort, $yt, $mt, $offset, $limit, get_canonical_query_name($raw_query));
		if ( !db_output_compressed_cache($cache_id) ) {
			db_open_compressed_cache($cache_id);
			// -----
			$result = get_cable_entries($raw_query,$sort,$yt,$mt,$offset,$limit);
			if (empty($result)) {db_cancel_cache();}
			echo $result;
			// -----
			db_close_compressed_cache();
			}
		break;

	default:
		exit('Invalid command');
	}
?>
