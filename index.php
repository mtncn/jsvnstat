<?php
	require 'settings.php';
	$interfaces = getAvailableInterfaces();
	$interface = getFromArgOrCookie('interface', $interface, $interfaces);
	$available_themes = array('default', 'nox');
	$theme = getFromArgOrCookie('theme', $theme, $available_themes);
	// Update and get data from vnstat. This assumes that vnstat is executable on the server and that a database exists
	$lines = explode("\n", getCmdOutput("vnstat --dumpdb -i $interface"));
	$info = array(); $hour = array(); $day = array(); $month = array(); $top10 = array();
	// Fill the arrays above with the appropriate data from the vnstat output
	$current_hour = date("G");
	foreach ($lines as $line) {
		$line = explode(";", $line);
		switch ($line[0]) {
			case "d": $day[$line[1]] = $line; break;
			case "m": $month[$line[1]] =  $line; break;
			case "t": $top10[$line[1]] = $line; break;
			case "h": $hour[(24 - $line[1] + $current_hour)%24] = $line; break;
			default: $info[$line[0]] = $line[1];
		}
	}
?>
<!DOCTYPE html>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<title>jsvnstat - interactive network traffic analysis</title>
		<link href="css/<?=$theme?>.css" rel="stylesheet" type="text/css" />
		<!--[if IE]><script language="javascript" type="text/javascript" src="js/excanvas.min.js"></script><![endif]-->
		<script language="javascript" type="text/javascript" src="js/jquery.min.js"></script>
		<script language="javascript" type="text/javascript" src="js/jquery.flot.min.js"></script>
		<script language="javascript" type="text/javascript" src="js/radio.js"></script>
		<script language="javascript" type="text/javascript" src="js/main.js"></script>
		<script language="javascript" type="text/javascript">
			datasets = {
				"hourstx": {label: "KB TX", color: "<?=$tx_color?>", shadowSize: 5,	data: <?=getFlotArray($hour, 4);?>},
				"hoursrx": {label: "KB RX", color: "<?=$rx_color?>", shadowSize: 5, data: <?=getFlotArray($hour, 3);?>},
				"daystx": {label: "MB TX", color: "<?=$tx_color?>", shadowSize: 5, data: <?=getFlotArray($day, 4);?>},
				"daysrx": {label: "MB RX", color: "<?=$rx_color?>", shadowSize: 5, data: <?=getFlotArray($day, 3);?>},
				"monthstx": {label: "MB TX", color: "<?=$tx_color?>", shadowSize: 5, data: <?=getFlotArray($month, 4);?>},
				"monthsrx": {label: "MB RX", color: "<?=$rx_color?>", shadowSize: 5, data: <?=getFlotArray($month, 3);?>},
				"top10tx": {label: "MB TX", color: "<?=$tx_color?>", shadowSize: 5, data: <?=getFlotArray($top10, 4, 1);?>},
				"top10rx": {label: "MB RX", color: "<?=$rx_color?>", shadowSize: 5, data: <?=getFlotArray($top10, 3, 1);?>}
			};
			graph_type = "<?=$graph_type?>";
			key = "<?=$time_type?>";
		</script>
	</head>
	<body>
		<div id="header">
			<h1>jsvnstat</h1>
		</div>
		<div id="leftcolumn">
			<p id="choices">
				<label class="hiddenJS" for="r1">
					<input class="hiddenJS" type="radio" name="group1" id="r1" value="hours" <?php if ($time_type == "hours") {echo "checked";} ?> />
					Hours&nbsp;&nbsp;&nbsp;</label><br /><br />
				<label class="hiddenJS" for="r2">
					<input class="hiddenJS" type="radio" name="group1" id="r2" value="days" <?php if ($time_type == "days") {echo "checked";} ?> />
					Days&nbsp;&nbsp;&nbsp;&nbsp;</label><br /><br />
				<label class="hiddenJS" for="r3">
					<input class="hiddenJS" type="radio" name="group1" id="r3" value="months" <?php if ($time_type == "months") {echo "checked";} ?> />
					Months&nbsp;&nbsp;</label><br /><br />
				<label class="hiddenJS" for="r4">
					<input class="hiddenJS" type="radio" name="group1" id="r4" value="top10" <?php if ($time_type == "top10") {echo "checked";} ?> />
					Top&nbsp;10&nbsp;&nbsp;</label><br /><br />
			</p>
			<p id="types">
				<label class="hiddenJS" for="g1">
					<input class="hiddenJS" type="radio" name="group2" id="g1" value="lines" <?php if ($graph_type == "lines") {echo "checked";} ?> />
					Lines</label>
				<label class="hiddenJS" for="g2">
					<input class="hiddenJS" type="radio" name="group2" id="g2" value="bars" <?php if ($graph_type == "bars") {echo "checked";} ?> />
					Bars&nbsp;</label>
			</p>
			<br />
			<h3>Interface</h3>
			<?php if ($enabled_dropdowns['interface']) { ?>
			<select onchange="window.location = '?interface='+$(this).val();">
				<?php
					foreach($interfaces as $nif) {
						echo '<option value="'.$nif.'" '.(($nif == $interface)?'selected="selected"':'').'>'.$nif.'</option>';
					}
				?>
			</select>
			<?php } else {echo $interface;} ?>
			<br /><br />
			<h3>Total</h3>
			<attr title="<?=round($info['totaltx'], 0);?> MB">TX: <?=round($info['totaltx']/1024, 0);?> GB</attr><br />
			<attr title="<?=round($info['totalrx'], 0);?> MB">RX: <?=round($info['totalrx']/1024, 0);?> GB</attr><br />
			<br />
			<h3>Uptime</h3>
			<attr title="since <?=date($date_format['uptime'], $info['btime'])?>">
				<?=floor((time() - $info['btime']) / 3600);?>h <?=floor(((time() - $info['btime']) / 60) % 60);?>min</attr><br />
			<br />
			<h3>Database</h3>
			Created:<br /><?=date("d.m.Y H:i:s", $info['created']);?><br />
			Last update:<br /><?=date("d.m.Y H:i:s", $info['updated']);?><br />
			<br />
			<?php if ($enabled_dropdowns['theme']) { ?>
			<h3>Theme</h3>
			<select onchange="window.location = '?theme='+$(this).val();">
				<?php
					foreach($available_themes as $aTheme) {
						echo '<option value="'.$aTheme.'" '.(($aTheme == $theme)?'selected="selected"':'').'>'.$aTheme.'</option>';
					}
				?>
			</select>
			<br /><br />
			<?php } ?>
			<h3><a href="" onclick="window.location.reload(false);">&rarr; reload</a></h3>
		</div>
		<div id="content">
		<?php
		// check if we have all the data we need, and if not warn the user
		if (sizeof($hour) != 24 || sizeof($day) != 30 || sizeof($month) != 12 || sizeof($top10) != 10) { ?>
			<p class="warning">Failed to retrieve data from vnstat!</p><br />
					<small>Ensure that:<br />
					<ul style="margin-left: 30px;">
						<li>vnstat is installed</li>
						<li>vnstat is executable (check php security settings)</li>
						<li>vnstat has a database (if not: vnstat -u -i <?=$interface?>)</li>
					</ul></small>
		<?php } ?>
			<div id="placeholder"></div>
			<br />
			<div id="tables">
			<?=getGraphTable($hour, 'hours', $date_format['hours'], "MB",$precision);?>
			<?=getGraphTable($day, 'days', $date_format['days'], "GB", $precision);?>
			<?=getGraphTable($month, 'months', $date_format['months'], "GB", $precision);?>
			<?=getGraphTable($top10, 'top10', $date_format['top10'], "GB", $precision, 1);?>
			</div>
		</div>
		<div id="footer">
			jsvnstat v2.0 was created by <a href="http://www.rakudave.ch">rakudave</a> for <a href="http://humdi.net/vnstat">vnstat</a>, using <a href="http://code.google.com/p/flot/">flot</a> and <a href="http://www.chriserwin.com/scripts/crir">crir</a>, and is available under the <a href="http://en.wikipedia.org/wiki/Gplv3#Version_3">GPLv3</a>
		</div>
	</body>
</html>

<?php
	// Turns a php array into a flot-readable array. This new array has only two columns -> index  denotes 2. column
	function getFlotArray($array2D, $index, $offset = 0) {
		$out = "[";
	    for ($i = 0; $i < sizeof($array2D); ++$i) {
			$out .= "[".($i+$offset).",".$array2D[$i][$index]."],";
		}
		return substr($out, 0, -1)."]";
	}

	// generate a TX/RX table for display below the graph
	function getGraphTable($array2d, $name, $dateformat, $unit, $precision, $offset = 0) {
		$class = '';
		$out = '
		<div id="'.$name.'_table">
			<table class="graph">
				<thead><tr><th>Time</th><th>TX</th><th>RX</th><th>Ratio</th><th>Total</th></tr></thead><tbody>';
					for ($i = 0; $i < sizeof($array2d); ++$i) {
						if (($i % 2) == 1) {$class = "odd";} else {$class="even";}
						$out .= '
						<tr id="'.$name.'_'.($i + $offset).'" class="'.$class.'">';
							if ($array2d[$i][2] != 0) {
								$out .= '<td class="time">'.date($dateformat, $array2d[$i][2]).'</td>';
							} else {
								$out .= '<td class="time">-</td>';
							}
						$out .=	'<td>'.sprintf('%.'.$precision.'f', $array2d[$i][4] / 1024).' '.$unit.'</td>
							<td>'.sprintf('%.'.$precision.'f', $array2d[$i][3] / 1024).' '.$unit.'</td>
							<td>'.sprintf('%.'.$precision.'f', $array2d[$i][4] / ($array2d[$i][3] + 0.001)).'</td>
							<td>'.sprintf('%.'.$precision.'f', ($array2d[$i][3] + $array2d[$i][4]) / 1024).' '.$unit.'</td>
						</tr>';
					}
		$out .= '</tbody>
			</table>
		</div>';
		return $out;
	}

	// executes a command and returns the output
	function getCmdOutput($command) {
		$fd = popen($command, "r");
		$buffer = '';
		while (!feof($fd)) $buffer .= fgets($fd);
		pclose($fd);
		return $buffer;
	}

	function getAvailableInterfaces() {
		$interfaces = explode(": ", getCmdOutput("vnstat --iflist"));
		$interfaces = explode(" ", trim(str_replace('lo', '', $interfaces[1])));
		natsort($interfaces);
		return $interfaces;
	}

	function getFromArgOrCookie($name, $current_value, $acceptable_values) {
		if ($_GET[$name] && in_array($_GET[$name], $acceptable_values)) $value = $_GET[$name];
		else if ($_COOKIE[$name] && in_array($_COOKIE[$name], $acceptable_values)) $value = $_COOKIE[$name];
		else $value = $current_value?$current_value:$acceptable_values[0];
		setcookie($name, $value);
		return $value;
	}
?>
