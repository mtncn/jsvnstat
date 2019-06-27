<?php
	$interface = '';	    /* Default interface to monitor (e.g. eth0 or wifi0), leave empty for first one */
	$graph_type = 'lines';	/* Default look of the graph (one of: lines, bars)*/
	$time_type = 'days';	/* Default time frame (one of: 'hours', 'days', 'months', 'top10') */
	$tx_color = '#00ff00';	/* TX graph color, default is #00ff00 */
	$rx_color = '#ff0000';	/* RX graph color, default is #ff0000 */
	$theme = 'default';     /* Default CSS theme to use (one of: 'default', 'nox') */
	$precision = 2;		    /* Number of decimal digits to display in table, default is 2 (e.g. 2 = 0.00, 3 = 0.000, etc...) */
	//date_default_timezone_set('Europe/Berlin'); // depending on your php settings you might want to explicitly set this to your TZ
	$date_format = array(   /* date formats shown in tables and sidebar, see php's date() for reference */
		'hours' => 'H:00',
		'days'  => 'D, d.m.Y',
		'months'=> 'M Y',
		'top10' => 'd.m.Y',
		'uptime'=> 'd.m.Y, H:i'
	);
	$enabled_dropdowns = array(
		'interface' => true,
		'theme' => true
	);
?>
