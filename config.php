<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8" />
		<title>ZTE SAT Tool</title>
		<link rel="icon" href="icon/favicon.ico" type="image/x-icon" />
		<link rel="stylesheet" href="header_style.css" />
		<link rel="stylesheet" href="config_style.css" />
	</head>

	<body>
		<?php

			// we set 3 cookie to remember the default excel files path & memory limit .. (if don't exists)
			$defaultpath = 'files/';
			$defaultmemorylimit = '256';
			$defaultmaxtime = '300'; // 300 seconds = 5 minutes

			$cookiename = ['path' => 'defaultpath', 'limit' => 'memorylimit', 'time' => 'maxexectime'];

			if (empty($_COOKIE[$cookiename['path']])) {
				setcookie($cookiename['path'],
				  		  $defaultpath,
  				  		  time() + (10 * 365 * 24 * 3600) // expire in 10 years
				);
				
				$_COOKIE[$cookiename['path']] = $defaultpath; // without that we need a refresh of the page to read cookie
			}

			if (empty($_COOKIE[$cookiename['limit']])) {
				setcookie($cookiename['limit'],
				  		  $defaultmemorylimit.'M', // we add 'M' here (mégabits)
  				  		  time() + (10 * 365 * 24 * 3600) // expire in 10 years
				);
				
				$_COOKIE[$cookiename['limit']] = $defaultmemorylimit.'M';
			}

			if (empty($_COOKIE[$cookiename['time']])) {
				setcookie($cookiename['time'],
				  		  $defaultmaxtime,
  				  		  time() + (10 * 365 * 24 * 3600) // expire in 10 years
				);
				
				$_COOKIE[$cookiename['time']] = $defaultmaxtime;
			}

			// include header
			include('header.php');

			/*
			// opening config file / or creating if not exist
			$configFile = fopen("config.zst", "r"); // '.zst' => Zte Script & Telnet Tool
			// read content of the file
			$fsize = filesize("config.zst");
			if ($fsize > 0)
				$path = fread($configFile, $fsize);
			else {echo 'here';
				$path = '';} // initialise it with empty string
			// close config file
			fclose($configFile);

			function isDirExist($dir) {
				// Open a known directory
				if (is_dir($dir)) {
					if ($dh = opendir($dir)) {
						//while (($file = readdir($dh)) !== false) {
							//echo "filename: $file : filetype: " . filetype($dir . $file) . "\n";
						//}
						closedir($dh);
						return true;
					}
					//else // the return false under/after this, will do this job
						//return false;
				}

				return false;
			}
			*/
		?>
		<div class="box">
			<h2 class="boxtitle">Configuration</h2>
			<br />
			<span class="libelle">Default Uploaded Excel Files Path : </span>
			<input class="textbox" id="defaultpath" type="text" value="<?php echo $_COOKIE[$cookiename['path']]; ?>" />
			<br /><br />
			<span class="libelle">Memory limit : </span>
			<input class="textbox" id="memorylimit" type="text" value="<?php echo substr($_COOKIE[$cookiename['limit']], 0, -1); ?>" /> <!-- we remove 'M' -->
			<span>Mégabits</span>
			<br /><br />
			<span class="libelle">Max execution time : </span>
			<input class="textbox" id="maxtime" type="text" value="<?php echo $_COOKIE[$cookiename['time']]; ?>" />
			<span>Seconds</span>
			<br /><br />
			<span class="bodyicon">!</span><span>Make sure cookies are enabled on your browser.</span>
			<br /><br />
			<center>
				<input class="pushbutton" id="savechanges" type="button" value="Save Changes" />
				<input class="pushbutton" id="setdefault" type="button" value="Set to default values" />
				<input class="pushbutton" id="reset" type="button" value="Reset" />
			</center>
			<br />
		</div>

		<script src="jquery.js"></script>
		<script>
			$(document).ready(function () {

				// this function return the value of a cookie
				function getCookieValue(NameOfCookie) {
					if (document.cookie.length > 0) {
						begin = document.cookie.indexOf(NameOfCookie+"=");
						if (begin != -1) {
							begin += NameOfCookie.length+1;
							end = document.cookie.indexOf(";", begin);
							if (end == -1) end = document.cookie.length;
							return unescape(document.cookie.substring(begin, end));
						}
					}
					return null;
				}

				// js function to create cookie
				function createCookieFor10years(name, value) {
					// create/update cookie
					var date = new Date();
					date.setTime(date.getTime()+(10 * 365 * 24 * 3600));
					var expires = "; expires="+date.toGMTString();
					document.cookie = name+"="+value+expires+";";
					// check for changes
					//alert(value);
					if (getCookieValue(name) == value)
						return true;
					else
						return false;
				}

				// event click on the button save changes
				$('#savechanges').click(function () {
					if ($('#defaultpath').val() == '' || !createCookieFor10years('<?php echo $cookiename["path"]; ?>', $('#defaultpath').val()))
						alert('Error Saving Files Path..');
					else if ($('#memorylimit').val() == '' || isNaN($('#memorylimit').val()) || !createCookieFor10years('<?php echo $cookiename["limit"]; ?>', $('#memorylimit').val() + 'M'))
						alert('Error Saving Memory limit..');
					else if ($('#maxtime').val() == '' || isNaN($('#maxtime').val()) || !createCookieFor10years('<?php echo $cookiename["time"]; ?>', $('#maxtime').val()))
						alert('Error Saving Max execution time..');
					else
						alert('Changes Saved!');
				});
				
				// event click on the button set to default value
				$('#setdefault').click(function () {
					$('#defaultpath').val('<?php echo $defaultpath; ?>');
					$('#memorylimit').val('<?php echo $defaultmemorylimit; ?>');
					$('#maxtime').val('<?php echo $defaultmaxtime; ?>');
				});
				
				// event click on the button reset
				$('#reset').click(function () {
					createCookieFor10years('files', ''); // we reset the cookie that contains files name & size (for 10 years or not, this is not a problem, because it will be rewrited if we have a new uploaded file)
					window.location.replace("index.php"); // redirect to index (to see changes)
				});
			});
		</script>
	</body>
</html>
