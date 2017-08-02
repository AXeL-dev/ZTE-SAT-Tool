<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8" />
		<title>ZTE SAT Tool</title>
		<link rel="icon" href="icon/favicon.ico" type="image/x-icon" />
		<link rel="stylesheet" href="header_style.css" />
		<link rel="stylesheet" href="goTelnet_style.css" />
	</head>
	
	<body>
		<?php 
			// include header
			include('header.php');
			
			// save cookies name in array for simple using and updating
			$cookiename = ['path' => 'defaultpath', 'limit' => 'memorylimit', 'time' => 'maxexectime'];

			// if cookies are not seted (just one to test)
			if (empty($_COOKIE[$cookiename['limit']])) {
				echo "<div class='box' id='fullbox'>Sorry, it's seems like <strong>cookies are disabled</strong>. Please check <a href='config.php'>configuration</a>.</div>";
				return;
			}
			
			// function who return error message when php shutdown (because of errors..)
			function shutdown()
			{
				$a = error_get_last();
				if($a != null)
					echo "<div class='box' id='fullbox'>Error : <strong>".print_r($a, true)."</strong>. Please check <a href='config.php'>configuration</a>.</div>";

			}
			
			register_shutdown_function('shutdown');
			
			ini_set('max_execution_time', $_COOKIE[$cookiename['time']]); // set max execution time
			
			// if we have all informations we need to use telnet
			if (isset($_POST['serverIp']) && isset($_POST['login']) && isset($_POST['pass']) && isset($_POST['msanIp']) && isset($_POST['cnx'])) {
				
				$serverIp = htmlentities($_POST['serverIp']);
				$login = htmlentities($_POST['login']);
				$pass = htmlentities($_POST['pass']);
				$msanIp = htmlentities($_POST['msanIp']);
				$script = htmlentities($_POST['script']);
				$cnx = htmlentities($_POST['cnx']);
				
				//echo $serverIp.'-'.$login.'-'.$pass.'-'.$msanIp;
				
				// including phpTelnet lib
				require_once "PHPTelnet.php";
				
				$telnet = new PHPTelnet();
				$telnet->show_connect_error = 0;
				
				// Turn off all php error reporting
				error_reporting(0);
				
				// connecting
				if ($cnx == 'L') // if Local Connexion checked
					$serverIp = $msanIp; // change Server IP to Msan IP
				
				$result = $telnet->Connect($serverIp, $login, $pass);
				$telnet_result = '[PHP Telnet] Connecting to '.$serverIp."...\n"; // j'ai mis des "" pr que le \n marche
			
				switch($result) {
					case 0: // success connecting
						$telnet_result .= '[PHP Telnet] Connected successfully to '.$serverIp."\n";

						/*
						if ($cnx == 'R') { // if Remote Connexion
							// bash command
							$cmd = 'bash';
							$telnet->DoCommand($cmd, $result);
							$telnet_result .= '[PHP Telnet] '.$result."\n";
					
							// telnet msan now
							$cmd = 'telnet '.$msanIp;
							$telnet->DoCommand($cmd, $result);
							$telnet_result .= '[PHP Telnet] '.$result."\n";
						}
						*/
					
						// executing script line by line
						foreach(preg_split("/((\r?\n)|(\r\n?))/", $script) as $line) {
							$telnet->DoCommand($line, $result);
							$telnet_result .= '[PHP Telnet] '.$result."\n";
						}
					
						// Disconnect
						//$telnet->Disconnect();
						$telnet->Disconnect(0); // 0 => don't send exit command
						$telnet_result .= "[PHP Telnet] Disconnected.";
						break;
					case 1:
						$telnet_result .= '[PHP Telnet] Connect failed: Unable to open network connection';
						break;
					case 2:
						$telnet_result .= '[PHP Telnet] Connect failed: Unknown host';
						break;
					case 3:
						$telnet_result .= '[PHP Telnet] Connect failed: Login failed';
						break;
					case 4:
						$telnet_result .= '[PHP Telnet] Connect failed: Your PHP version does not support PHP Telnet';
						break;
				}
			}
		?>
		<form method="post" action="goTelnet.php">
			<div class="box">
				<h2 class="boxtitle">Script<input id="browse" type="file" /><input id="open" class="pushbutton" type="button" value="Open" /></h2>
				<textarea id="script_container" name="script"><?php
					if (isset($_POST['script']))
						echo htmlentities($_POST['script']); // pas vrm besoin de htmlentities içi vu que meme si on écrit des balises html dans un textarea, il les affichera sous forme de texte (je vien de remarquer que j'ai utiliser du français pr ce commentaire, apres 1 jr de l'avoir écrit lol)
				?></textarea>
			</div>
			<div class="box">
				<h2 class="boxtitle">Telnet</h2>
				<label><input type="radio" value="L" name="cnx" class="radiobtncnx" <?php if (!isset($_POST['cnx']) || (isset($_POST['cnx']) && $_POST['cnx'] == 'L')) echo 'checked'; ?> />Local Connexion</label>
				<label><input type="radio" value="R" name="cnx" class="radiobtncnx" <?php if (isset($_POST['cnx']) && $_POST['cnx'] == 'R') echo 'checked'; ?> />Remote Connexion</label>
				<br /><br />
				<div id="for_remote_cnx" <?php if (!isset($_POST['cnx']) || (isset($_POST['cnx']) && $_POST['cnx'] == 'L')) echo 'style="display: none;"'; ?>>
					<span class="libelle">Server IP :</span>
					<select class="combobox" id="serverIp" name="serverIp">
						<option value="10.11.11.1" <?php if (isset($_POST['serverIp']) && ($_POST['serverIp'] == '10.11.11.1')) echo 'selected'; ?>>10.11.11.1</option>
						<option value="10.11.12.1" <?php if (isset($_POST['serverIp']) && ($_POST['serverIp'] == '10.11.12.1')) echo 'selected'; ?>>10.11.12.1</option>
						<option value="127.0.0.1" <?php if (isset($_POST['serverIp']) && ($_POST['serverIp'] == '127.0.0.1')) echo 'selected'; ?>>127.0.0.1</option>
					</select>
					<br /><br />
				</div>
				<span class="libelle">Login :</span>
				<input class="textbox" id="login" name="login" type="text" value="<?php echo isset($_POST['login']) ? htmlentities($_POST['login']) : 'zte'; ?>" />
				<br /><br />
				<span class="libelle">Password :</span>
				<input class="textbox" id="pass" name="pass" type="password" value="<?php echo isset($_POST['pass']) ? htmlentities($_POST['pass']) : 'zte'; ?>" />
				<br /><br />
				<span class="libelle">MSAN IP :</span>
				<input class="textbox" id="msanIp" name="msanIp" type="text" value="<?php echo isset($_POST['msanIp']) ? htmlentities($_POST['msanIp']) : '136.1.1.100'; ?>" />
				<br /><br />
				<center><input id="execute" class="pushbutton" type="submit" value="Execute Script" /></center>
				<br />
				<textarea id="telnet_result" readonly ><?php
				
					// if we have a telnet result(s), we show them
					if (isset($telnet_result))
						echo $telnet_result;
						
				?></textarea>
				<div id="remote_cnx_info" <?php if (!isset($_POST['cnx']) || (isset($_POST['cnx']) && $_POST['cnx'] == 'L')) echo 'style="display: none;"'; ?>>
					<br />
					<span class="numbericon">1</span><span>Generate & save the executable script.</span>
					<br /><br />
					<span class="numbericon">2</span><span>Launch the executable script.</span>
					<br /><br /><br />
					<center><span class="warningicon">!</span><span>Make sure telnet client is activated on Windows.</span></center>
				</div>
			</div>
		</form>
	
		<script src="jquery.js"></script>
		<script>
			$(document).ready(function () { // DOM loaded
				
				// event click on button open
				$('#open').click(function () {
					$('#browse').click();
				});
				
				// event change of browse (input file type)
				$('#browse').change(function () {
					// if a text file have been choosed
					if (this.files[0].name.substr(this.files[0].name.lastIndexOf('.') + 1) == 'txt') {
						// read the file
						var fr = new FileReader();
						fr.onload = function (e) {
							// show the content of file in script textarea
							$('#script_container').val('conf-t\n' + e.target.result + 'wc\nexit');
						};
						fr.readAsText(this.files[0]);
					}
					else
						alert('Please choose a text file!');
				});
				
				// event change of connexion type radio buttons
				$('.radiobtncnx').change(function () {
					$('#for_remote_cnx').slideToggle('slow');
					if ($(this).val() == 'R') { // if Remote Connexion checked
						$('#telnet_result').hide();
						$('#msanIp').val('');
						
						$('#execute').val('Generate Executable Script').attr('type', 'button');
						$('#remote_cnx_info').show();
					}
					else {
						$('#remote_cnx_info').hide();
						$('#telnet_result').css('height', '145px').show().animate({ 'height' : '+=50' }, 'slow');
						$('#msanIp').val('136.1.1.100');
						// these values are already seted by default but we can update them here..
						//$('#login').val('zte');
						//$('#pass').val('zte');
						
						$('#execute').val('Execute Script').attr('type', 'submit');
					}
				});
				
				// event click on button execute script
				$('#execute').click(function () {
					//alert($('#script_container').val() + " - " + $('#script_container').text() + " - " + $('#script_container').html());
					if ($('#script_container').val() == '') {
						alert('nothing to execute!');
						return false; // stop sending to server
					}
					else {
						// remove all icons
						$('.icon').each(function () {
							$(this).remove();
						});
						
						// check for empty textbox
						var emptyOrError = false;
						
						$('.textbox').each(function () {
							if ($(this).val() == '') {
								$(this).after($('<img class="icon" src="icon/error.png" />'));
								emptyOrError = true;
							}
							else if ($(this).attr('id') == 'msanIp') { // if ip adress, we need to check it
								var ip = $(this).val().split('.');
								if (ip.length != 4) { // if ip adress is not full
									$(this).after($('<img class="icon" src="icon/error.png" />'));
									emptyOrError = true;
								}
								else { // check if ip parts are numeric/not empty/> 0 and <= 255
									for (var i = 0; i < ip.length; i++) {
										//alert(parseInt(ip[i]));
										if (ip[i] == '' || isNaN(ip[i]) || parseInt(ip[i]) < 0 || parseInt(ip[i]) > 255) {
											$(this).after($('<img class="icon" src="icon/error.png" />'));
											emptyOrError = true;
											break;
										}
									}
								}
							}
						});
						
						if (emptyOrError)
							return false; // stop sending to server)
					}
					
					// removing old loading icons if exists
					$('.loadicon').remove();
					
					// adding loading icon (when all's good)
					//$(this).after($("<img src='icon/load.gif' />"));
					$(this).after($('<span class="loadicon"></span>'));
					
					// if we need to generate executable script
					if ($(this).val() == 'Generate Executable Script') {
						
						// creating executable script
						var vitesse_script = 50, // 1 commande chaque 50 milliseconde
							wait_cmd = '\nWScript.Sleep ' + vitesse_script + '\n',
							wait_for_server = '\nWScript.Sleep 2000\n',
							exec_script_text = 'set OBJECT=WScript.CreateObject("WScript.Shell")\nOBJECT.run "cmd"\nWScript.Sleep 500\n'; // à l'ouverture de la cmd on attend 500 ms
							
						exec_script_text += 'OBJECT.SendKeys "telnet ' + $('#serverIp').val() + '{ENTER}"' + wait_for_server;
						
						exec_script_text += 'OBJECT.SendKeys "' + $('#login').val() + '{ENTER}"' + wait_cmd;
						
						exec_script_text += 'OBJECT.SendKeys "' + $('#pass').val() + '{ENTER}"' + wait_cmd;
						
						exec_script_text += 'OBJECT.SendKeys "bash{ENTER}"' + wait_cmd;
						
						exec_script_text += 'OBJECT.SendKeys "telnet ' + $('#msanIp').val() + '{ENTER}"' + wait_for_server;
						
						exec_script_text += 'OBJECT.SendKeys "' + $('#login').val() + '{ENTER}"' + wait_cmd;
						
						exec_script_text += 'OBJECT.SendKeys "' + $('#pass').val() + '{ENTER}"' + wait_cmd;
						
						var script_lines = $('#script_container').val().split('\n');
						
						for (var i = 0; i < script_lines.length; i++) {
							exec_script_text += 'OBJECT.SendKeys "' + script_lines[i] + '{ENTER}"' + wait_cmd;
						}
						
						// saving executable script
						var currentdate = new Date(),
							datetime = "-" + currentdate.getDate() + "-"
										+ (currentdate.getMonth()+1)  + "-"
										+ currentdate.getFullYear() + "-"
										+ currentdate.getHours() + "-"
										+ currentdate.getMinutes() + "-"
										+ currentdate.getSeconds();
						saveFile('executable_script' + datetime + '.vbs', exec_script_text);
						
						// removing load icon
						$('.loadicon').remove();
					}
				});
				
				// function saveFile
				function saveFile(fileName, fileTextContent) {
					var tmpA = document.createElement('a');
					tmpA.setAttribute('href', 'data:text/plain;charset=utf-8,' + encodeURIComponent(fileTextContent));
					tmpA.setAttribute('download', fileName);
					document.body.appendChild(tmpA);
					tmpA.click();
					document.body.removeChild(tmpA);
				}
				
			});
		</script>
	</body>
</html>
