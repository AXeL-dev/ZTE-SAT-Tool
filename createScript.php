<?php

	/* // don't need to use sessions anymore
	// starting session
	session_start();
	// if it's the first use (session var. not seted), or if we need to set a new filename
	if(isset($_GET['file']) && isset($_GET['show']) && ((!isset($_SESSION['filename']) || $_SESSION['filename'] != $_GET['file']) || (!isset($_SESSION['showfile']) || $_SESSION['showfile'] != $_GET['show']))) {
		$_SESSION['filename'] = htmlentities($_GET['file']); // saving filename
		$_SESSION['showfile'] = htmlentities($_GET['show']); // saving show file or not
	}
	else {
		if (!isset($_SESSION['filename']) || !isset($_SESSION['showfile'])) // if we don't have a stored filename, or the other param. we need
			header('Location: index.php'); // redirect to index
	}
	*/

	if (!isset($_GET['file']) || !isset($_GET['show'])) // if one of parameters is not seted
		header('Location: index.php'); // redirect to index
	else {
		$filename = htmlentities($_GET['file']); // saving filename
		$showfile = htmlentities($_GET['show']); // saving the value of show file
	}

	// sorry for using french comment in the next party of this file, because i have begin writing it before deciding of language to use
?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8" />
		<title>Zte SAT Tool</title>
		<link rel="icon" href="icon/favicon.ico" type="image/x-icon" />
		<link rel="stylesheet" href="createScript_style.css" />
		<link rel="stylesheet" href="header_style.css" />
		<link rel="stylesheet" href="jquery-linedtextarea.css" />
	</head>

	<body>
		<?php

			include('header.php');
			
			echo '<div id="body_container">';
			
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

			ini_set('memory_limit', $_COOKIE[$cookiename['limit']]); // set memory limit (to read big files)
			ini_set('max_execution_time', $_COOKIE[$cookiename['time']]); // set max execution time

			// Inclusion de la bibliothèque PHPExcel
			//include 'PHPExcel.php';
			include 'PHPExcel/IOFactory.php';
			
			function isExcel2007($fname) {
				$len = strlen($fname);
				if ($fname[$len - 1] == 'x') // if the last caracter of filename is 'x' from '.xlsx' , yes, it's 2007 excel file !
					return true;
				else
					return false;
			}
			
			$inputFileType = isExcel2007($filename) ? 'Excel2007' : 'Excel5'; // type du fichier
			//$inputFileName = 'Tamesna Indoor.xls'; // nom du fichier, le fichier doit être au format excel 2003
			$inputFileName = $filename;
			$inputFile = $_COOKIE[$cookiename['path']].$inputFileName; // default path + filename

			if (!file_exists($inputFile)) { // if file don't exists
				echo '<div class="box" id="fullbox">Sorry, cannot open file "<strong>'.$inputFile.'</strong>". Please check <a href="config.php">configuration</a>.</div>';
				return;
			}

			// Lecture du fichier Excel
			$objReader = PHPExcel_IOFactory::createReader($inputFileType);
			$objReader->setReadDataOnly(true);
			$objPHPExcel = $objReader->load($inputFile);

			// showning file if show param. == true
			if ($showfile == 'true') {
				// Ecriture au format HTML
				$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'HTML');

				// affichage du nom du fichier
				echo '<div class="box"><h2 class="boxtitle">File : '.$inputFileName.'<span class="closeicon">x</span></h2>';

				// affichage dans le navigateur
				$objWriter->save('php://output');
				//exit;

				echo '</div>'; // close box
			}

			// récupération des noms de colonnes
			$row = 1; // première ligne

			$worksheet = $objPHPExcel->getActiveSheet(); // feuille courante

			$lastColumn = $worksheet->getHighestColumn(); // index de la dernière colonne (== nombre de colonnes - 1)
			
			$columnsName = array(); // tableau pour y stocker les noms de colonnes

			for ($column = 'A'; $column <= $lastColumn; $column++) { // on parcours toute la première ligne
    			//array_push($columnsName, $worksheet->getCell($column.$row));
				$columnsName[$column] = $worksheet->getCell($column.$row);
			}

			$row++; // on passe à la ligne suivante, plus besoin de revenir sur la 1ère ligne vu qu'on a récupéré son contenu

			// functions to create html selects that contains excel file columns name
			function createSelectOfColumns($columnsName, $selectName) {
				$selectHtmlCode = "<select name='".$selectName."'>\n"; // this name will be used to send data in a form by get method
				
				foreach ($columnsName as $colName) {
					if (isset($_POST[$selectName]) && $colName == str_replace('[cmb]', '', $_POST[$selectName]))
						$selectHtmlCode .= "<option value='[cmb]".$colName."' selected>".$colName."</option>\n";
					else
						$selectHtmlCode .= "<option value='[cmb]".$colName."' >".$colName."</option>\n";
				}
				
				$selectHtmlCode .= "</select>\n";

				return $selectHtmlCode;
			}
			
			function createSelectWithSelectedColumn($columnsName, $selectName, $selectedColumnName) {
				$selectHtmlCode = "<select name='".$selectName."'>\n"; // this name will be used to send data in a form by get method
				
				foreach ($columnsName as $colName) {
					if ($colName == str_replace('[cmb]', '', $selectedColumnName))
						$selectHtmlCode .= "<option value='[cmb]".$colName."' selected>".$colName."</option>\n";
					else
						$selectHtmlCode .= "<option value='[cmb]".$colName."' >".$colName."</option>\n";
				}
				
				$selectHtmlCode .= "</select>\n";

				return $selectHtmlCode;
			}
?>
			<div class="box">
				<h2 class="boxtitle">Script configuration<input id="edit" class="pushbutton" type="button" value="Edit" /><input id="newLine" class="pushbutton" type="button" value="New Line" /></h2>
				<form id="script_config" action="createScript.php<?php echo '?file='.$filename.'&show='.$showfile;  ?>" method="post">
					<?php
						if (count($_POST) > 0) {
							// we get values
							$params = array_values($_POST);
							$txtAndCmbCount = 1;
							
							for ($i = 0; $i < count($params); $i++) {
								if (strpos($params[$i],'[cmb]') !== false) // if comboBox, we create the combobox
									echo createSelectWithSelectedColumn($columnsName, 'cmb'.$txtAndCmbCount++, $params[$i]);
								else
								{
									if ($params[$i] == '[\n]') // gestion des sauts de lignes
										echo "<br class='backToLine' />";
									else { // we create a textbox
										$UpperCaseLettersCount = strlen(preg_replace('![^A-Z]+!', '', $params[$i]));
										$txtWidth = ($UpperCaseLettersCount * 8) + ((strlen($params[$i]) - $UpperCaseLettersCount) * 7.2) + 5; // 7.2px is approximatively the width of one normal letter, +8 for upperCaseLetters, +5 of right margin
										echo "<input name='txt".$txtAndCmbCount++."' type='text' class='txtBoxs' value='".$params[$i]."' style='width: ".$txtWidth."px; margin-right: 5px;' readonly />";
									}
								}
							}
						}
						else
						{
					?>
							<input name="txt1" type="text" class="txtBoxs" value="<?php echo isset($_POST['txt1']) ? $_POST['txt1'] : 'interface vdsl_'; ?>" style="width: 105px;" readonly />
							<?php echo createSelectOfColumns($columnsName, 'cmb1'); ?>
							<br class="backToLine" />
							<input name="txt2" type="text" class="txtBoxs" value="<?php echo isset($_POST['txt2']) ? $_POST['txt2'] : 'pvc 1 enable'; ?>" style="width: 105px;" readonly />
							<br class="backToLine" />
							<input name="txt3" type="text" class="txtBoxs" value="<?php echo isset($_POST['txt3']) ? $_POST['txt3'] : 'port-location format dsl-forum'; ?>" style="width: 290px;" readonly />
							<br class="backToLine" />
							<input name="txt4" type="text" class="txtBoxs" value="<?php echo isset($_POST['txt4']) ? $_POST['txt4'] : 'port-location sub-option remote-id enable'; ?>" style="width: 290px;" readonly />
							<br class="backToLine" />
							<input name="txt5" type="text" class="txtBoxs" value="<?php echo isset($_POST['txt5']) ? $_POST['txt5'] : 'port-location sub-option remote-id name '; ?>" style="width: 290px;" readonly />
							<?php echo createSelectOfColumns($columnsName, 'cmb2'); ?>
							<br class="backToLine" />
							<input name="txt6" type="text" class="txtBoxs" value="<?php echo isset($_POST['txt6']) ? $_POST['txt6'] : 'port-location format dsl-forum pvc 1'; ?>" style="width: 330px;" readonly />
							<br class="backToLine" />
							<input name="txt7" type="text" class="txtBoxs" value="<?php echo isset($_POST['txt7']) ? $_POST['txt7'] : 'port-location sub-option remote-id enable pvc 1'; ?>" style="width: 330px;" readonly />
							<br class="backToLine" />
							<input name="txt8" type="text" class="txtBoxs" value="<?php echo isset($_POST['txt8']) ? $_POST['txt8'] : 'port-location sub-option remote-id name '; ?>" style="width: 290px;" readonly />
							<?php echo createSelectOfColumns($columnsName, 'cmb3'); ?>
							<input name="txt9" type="text" class="txtBoxs" value="<?php echo isset($_POST['txt9']) ? $_POST['txt9'] : ' pvc 1'; ?>" style="width: 45px;" readonly />
							<br class="backToLine" />
							<input name="txt10" type="text" class="txtBoxs" value="<?php echo isset($_POST['txt10']) ? $_POST['txt10'] : 'pppoe-plus enable pvc 1'; ?>" style="width: 180px;" readonly />
							<br class="backToLine" />
							<input name="txt11" type="text" class="txtBoxs" value="<?php echo isset($_POST['txt11']) ? $_POST['txt11'] : 'vdsl2 base-profile ADSL2'; ?>" style="width: 180px;" readonly />
							<br class="backToLine" />
							<input name="txt12" type="text" class="txtBoxs" value="<?php echo isset($_POST['txt12']) ? $_POST['txt12'] : 'vdsl2 service-profile '; ?>" style="width: 150px;" readonly />
							<?php echo createSelectOfColumns($columnsName, 'cmb4'); ?>
							<br class="backToLine" />
							<input name="txt13" type="text" class="txtBoxs" value="<?php echo isset($_POST['txt13']) ? $_POST['txt13'] : 'exit'; ?>" style="width: 150px;" readonly />
							<br class="backToLine" />
					<?php
						}
					?>
					<br id="lastLine" /><br /><br />
					<center><input id="generate" class="pushbutton" type="submit" value="Generate Script" /></center>
				</form>
			</div>
			<div class="box">
				<h2 class="boxtitle">Script</h2>
				<form method="post" action="goTelnet.php">
					<textarea id="script_container" name="script"><?php
							if (count($_POST) > 0) { // si les params ont été envoyés
								
								$params = array_values($_POST);
								$errors = array(); // erreurs survenu lors de la génération du script

								// récupération du nombre de ligne de la feuille Excel
								$lastRow = $objPHPExcel->getActiveSheet()->getHighestRow();

								// on enlève les messages d'erreur (de conversion string to int en dessous par exemple)
								//error_reporting(E_ERROR | E_PARSE); // je n'en ai plus besoin vu que j'ai crée ma fonction de convertion (qui gère ces erreurs)
								
								// fonction de convertion + gestion d'erreur
								function tryToConvert($prevValue, $value, $row, &$errors) {
									// if empty value
									if ($value == '') {
										$errors[] = '[Error : empty value in line '.$row.']';
										return $errors[count($errors) - 1];
									}
									// 1 - try to convert string format x-x-x to format x/x/x
									else if (strpos($prevValue,'interface vdsl') !== false) {
										
										// 1st split
										$splited = explode('-', $value); // split by '-' (explode() will return the full value if error)
										$splitedCount = count($splited);

										if ($splitedCount <= 3) { // if we don't have 3 results minimum
											// 2nd split
											$splited = explode('/', $value); // split by '/'
											$splitedCount = count($splited); // update the count of splited values
										}
										
										if ($splitedCount < 3 || !is_numeric($splited[0])) { // if the 1st/2nd splits don't work ==> because of wrong format or first value is not numeric
											$errors[] = '[Error, cannot convert "'.$value.'" to interface vdsl format in line '.$row.']';
											return $errors[count($errors) - 1];
										}

										if ($splitedCount > 3) // if we have more than 3 splited values
											unset($splited[0]); // we remove the first value
										
										return join('/', $splited); // we join them by slashs '/'
									} // end of 1st else if
									// 2 - try to convert mégabits to kilobits
									else if (strpos($prevValue,'service-profile') !== false) {
										
										// vérification des profiles ADSL
										$profiles_array = array("/128K", "/256K", "/512K", "/1024K", "/2048K", "/4096K", "/8100K", "/12000K", "/21400K", "BTV_IN2M", "BTV_IN4M", "BTV_IN8M", "BTV_IN12M", "PROFILE-TV", "DP-1VOIP-2M", "DP-1VOIP-4M", "DP-1VOIP-8M", "DP-1VOIP-12M", "TP-1VOIP-2M", "TP-1VOIP-4M", "TP-1VOIP-8M", "TP-1VOIP-12M", "TP-1VOIP-12M");
										
										if (in_array($value, $profiles_array)) // si valeur trouvé ds le tableau
											return $value;
										else if (in_array('/'.$value, $profiles_array)) // si nn si '/' + valeur trouvé
											return '/'.$value;
										else if (in_array('/'.$value.'K', $profiles_array)) // si nn si '/' + valeur + 'K' trouvé
											return '/'.$value.'K';
										else if (in_array('/'.round((int)((string)$value), -2).'K', $profiles_array)) // si nn si, on arrondit les 2 derniers chiffres du nombre (car les profiles 8096, 8092,.. n'existe pas), dans le cas de 8096 cela donnera 8100 =>(implique) profile existant
											return '/'.round((int)((string)$value), -2).'K';
										else if (in_array('/'.round((int)((string)$value), -3).'K', $profiles_array)) // si nn si, on arrondit les 3 derniers chiffres du nombre (car les profiles 12288, 12300.. n'existe pas), dans le cas de 12300 cela donnera 12000 =>(implique) profile existant
											return '/'.round((int)((string)$value), -3).'K';
										
										// si echec de vérification, essai de convertion megabits => kilobits
										
										if (strpos($value,'b/s') !== false || strpos($value,'B/S') !== false) { // if it's a value in Mégabits/or kilobits (why converting kilobits values? => to remove 'kb/s' strings for example..)
											
											$value = (string) $value; // convertion en chaine de caractère (pr eviter une erreur qui dit que ce n'est pas une chaine, enlevez le cast et vous verrez l'erreur)
											$len = strlen($value);
											$converted = ''; // contiendra le resultat

											// on parcourt notre chaine/valeur
											for ($i = 0; $i < $len; $i++) {
												if (is_numeric($value[$i])) // si c'est une valeur/caratère numérique
													$converted .= $value[$i]; // on l'ajoute au résultat
												else { // si nn
													if ($converted != '') { // si resultat non vide, on renvoie le résultat en kilobits
														if ($converted % 128 != 0) // si le résultat n'est pas en kolibits (en mégabits) (pq 128, car c le débit le plus petit a mon avis, et parce que si on divise n'importe quel débit/nombre plus grand que lui sur ce nombre (128) ça donnera 0 comme reste)
															$converted = (int) $converted * 1024; // megabits => kilobits
														
														// conversion fini , on vérifie le résultat
														if (in_array('/'.$converted.'K', $profiles_array)) // si valeur trouvé ds le tableau
															return '/'.$converted.'K';
														else if ($converted == 8192) // si nn si 8 / 12 / 20 méga (car leur profile est différent/n'existe pas ds le tableau des profiles) (*1024 pr obtenir la valeur en kb)
															return '/8100K';
														else if ($converted == 12288)
															return '/12000K';
														else if ($converted == 20480)
															return '/21400K';
													}
												}
											}
										}
										
										// si on arrive içi c'est qu'on a tt vérifié/tt parcouru ds la boucle ss trouvé de nombre non numéric (sans passer par le else)
										$errors[] = '[Error, cannot convert "'.$value.'" to service-profile format in line '.$row.']';
										return $errors[count($errors) - 1];
									} // end of 2nd else if
									else
										return $value; // no convertion needed
								}

								// on parcourt le fichier/feuille actuelle ligne par ligne
								for ($row; $row <= $lastRow; $row++) { // row égal à 2 au début normalement puisqu'on la incrémenter juste en haut
									// écriture du script
									$script_part = ""; // initialisation
									for ($i = 0; $i < count($params); $i++) {
										if (strpos($params[$i],'[cmb]') !== false) // si c'est une valeur de comboBox, on enleve '[cmb]' et on récupère la valeur de la colonne
											$script_part .= tryToConvert($params[$i - 1], $worksheet->getCell(array_search(str_replace('[cmb]', '', $params[$i]), $columnsName).$row), $row, $errors);
										else
										{
											if ($params[$i] == '[\n]') // gestion des sauts de lignes
												$script_part .= "\n";
											else
												$script_part .= $params[$i];
										}
									}
									
									// N.B : '\n' affiche le caractère '\n' tandis que "\n" fait un retour à la ligne, quand a array_search() (au lieu de keyOf() que j'ai crée), elle retourne la clé de la valeur cherchée dans le tableau spécifié
									echo strpos($script_part, '[Error') === false ? $script_part : '';
								}
							}
					?></textarea>
					<textarea id="error_container" readonly><?php
						
						// affichage des erreurs
						if (isset($errors) && count($errors) > 0) {
							for ($i = 0; $i < count($errors) - 1; $i++) // le -1 pr ne pas écrire la dernière ligne dans cette boucle
								echo $errors[$i]."\n";
							echo $errors[$i]; // pr éviter que la dernière ligne aie un saut de ligne
						}
						
					?></textarea>
					<span id="error_field" ><span></span><img src="icon/shield-error.png" /></span>
					<span id="success_field" ><img src="icon/success.png" /></span>
					<input id="save" class="pushbutton" type="button" value="Save Script" />
					<input id="execute" class="pushbutton" type="submit" value="Execute Script" />
				</form>
			</div>
		</div>
		<script src="jquery.js"></script>
		<script src="jquery-linedtextarea.js"></script>
		<script>
			$(document).ready(function () { // DOM is already loaded
				
				// php commenté en français et js en anglais, ms mdr..pathétique je vien de remarquer..
				
				// variables globales
				var scriptLinesNumber = <?php echo count($_POST) > 0 ? $txtAndCmbCount - 1 : 13; ?>,
					editDeleteIcon = '<span class="deleteicon" onclick="while($(this).next().attr(\'name\') != undefined) { $(this).next().remove(); } $(this).next().remove(); /* remove <br> */ $(this).remove(); /* remove deleteicon */">x</span>';
				
				// Adding lines number to script textarea & Showing script generation errors/success & color with red errors line
				<?php
					if ( isset($errors) ) {
						if (count($errors) > 0) {
							echo "$('#script_container').css('height', '300px').linedtextarea();";
							echo "$('#error_container').css('height', '63px').css('display', 'inline-block').linedtextarea({lineColor: 'red'});";
							echo "$('#error_field span').html('".count($errors)."');";
							echo "$('#error_field').css('display', 'inline-block');";
						}
						else {
							echo "$('#script_container').linedtextarea();";
							echo "$('#success_field').css('display', 'inline-block');";
						}
					}
					else
						echo "$('#script_container').linedtextarea();";
				?>
				
				// function saveFile
				function saveFile(fileName, fileTextContent) {
					var tmpA = document.createElement('a');
					tmpA.setAttribute('href', 'data:text/plain;charset=utf-8,' + encodeURIComponent(fileTextContent));
					tmpA.setAttribute('download', fileName);
					document.body.appendChild(tmpA);
					tmpA.click();
					document.body.removeChild(tmpA);
				}
				
				// event click of save script button
				$('#save').click(function () {
					//alert($('#script_container').val());
					if ($('#script_container').val() != '') {
						var currentdate = new Date(),
							datetime = "-" + currentdate.getDate() + "-"
										+ (currentdate.getMonth()+1)  + "-"
										+ currentdate.getFullYear() + "-"
										+ currentdate.getHours() + "-"
										+ currentdate.getMinutes() + "-"
										+ currentdate.getSeconds();
						saveFile('script' + datetime + '.txt', $('#script_container').text());
					}
					else
						alert('nothing to save!');
				});
				
				// event click of execute button
				$('#execute').click(function () {
					if ($('#script_container').val() == '') {
						alert('nothing to execute!');
						return false;
					}
					else // else we add some commands (conf-t, ..) before executing
						$('#script_container').val('conf-t\n' + $('#script_container').val() + 'wc\nexit');
				});
				
				<?php
					if (count($_POST) == 0)
					{
				?>

					// event change of select 'cmb2'
					$('select[name="cmb2"]').change(function () {
						$('select[name="cmb3"]').val($(this).val()); // we change the value of her sister too (we make them synchronised)
					});
					
				<?php
					}
				?>

				// event click of close button
				$('.closeicon').click(function () {
					$(this).parent().parent().fadeOut(300, function () {
						$(this).remove(); // we remove the full box who countains the closeicon
						var newAction = $('#script_config').attr('action').replace('true', 'false'); // we replace the param show = true by false
						$('#script_config').attr('action', newAction);
					});
				});
				
				// event click of Edit button
				$('#edit').click(function () {
					if ($(this).val() == 'Edit') {
						// for all textBoxs, we add delete icon, we remove readonly attr. & we change class
						$('#script_config input[type=text]').each(function () {
							if ($(this).prev().attr('name') == undefined) // if it's the line begining (not just a 2nd textbox => name = undefined)
								$(this).before(editDeleteIcon);
							// i have wrote all the jquery code inside the span balise above (moved to editDeleteIcon var), i know it's not good but i need to hurry up to finish this quickly
							$(this).removeAttr('readonly').removeClass('txtBoxs').addClass('editBoxs');
						});
						$(this).val('Save').css('border-color', 'rgba(82, 168, 236, 0.75)').css('box-shadow', '0 0 8px rgba(82, 168, 236, 0.5)');
						$('#generate').attr('disabled','disabled');
						$('#newLine').css('display', 'inline-block');
					}
					else {
						// we rechange class & readd readonly.. + we change textBoxs width
						$('#script_config .editBoxs').each(function () {
							if ($(this).prev().attr('name') == undefined) // we remove delete icon
								$(this).prev().remove();
							var UpperCaseLettersCount = $(this).val().replace(/[^A-Z]/g, "").length;
							$(this).attr('readonly', true).removeClass('editBoxs').addClass('txtBoxs').css('width', (UpperCaseLettersCount * 8) + (($(this).val().length - UpperCaseLettersCount) * 7.2) + 5); // 7.2px is approximatively the width of one normal letter, +8 for upperCaseLetters, +5 of right margin
						});
						$(this).val('Edit').css('border-color', '#A9B6BD').css('box-shadow', 'none');
						$('#generate').removeAttr('disabled');
						$('#newLine').css('display', 'none');
					}
				});
				
				// event click of newLine button
				$('#newLine').click(function () {
					// new Line Box
					var newLineBox = `<div id='newLineContainer' class='box' >
										<h2 class='boxtitle'>Choose a new line<span class="closeicon" onclick="$(this).parent().parent().fadeOut(300, function () { $(this).remove(); $('#body_container').removeClass('disable_all'); });">x</span></h2>
										<input name='line_type' type='radio' value='1' checked /><input type='text' class='editBoxs' style='width: 200px;' readonly />
										<br />
										<input name='line_type' type='radio' value='2' /><input type='text' class='editBoxs' style='width: 200px; margin-right: 5px;' readonly /><?php echo createSelectOfColumns($columnsName, 'lt1'); ?>
										<br />
										<input name='line_type' type='radio' value='3' /><input type='text' class='editBoxs' style='width: 200px; margin-right: 5px;' readonly /><?php echo createSelectOfColumns($columnsName, 'lt2'); ?><input type='text' class='editBoxs' style='width: 150px;' readonly />
										<br /><br />
										<center><img id="success_icon" src="icon/success.png" style="display: none; position: absolute; bottom: 20px; left: 20px;" /><input id="addLine" class="pushbutton" type="button" value="Add Line" /></center>
									  </div>`;
					$('body').append(newLineBox);
					$('#body_container').addClass('disable_all');
					
					// add Line button click
					$('#addLine').click(function () {
						// we get & add the choosed line type to script configuration form
						var lineType = $('input[name="line_type"]:checked').val(),
							comboBox = `<?php echo createSelectOfColumns($columnsName, 'default'); ?>`;
						switch(lineType) {
							case '1':
								$('#lastLine').before(editDeleteIcon + "<input name='txt" + ++scriptLinesNumber + "' type='text' class='editBoxs' style='width: 200px;' />" + "<br class='backToLine' />");
							break;
							case '2':
								$('#lastLine').before(editDeleteIcon + "<input name='txt" + ++scriptLinesNumber + "' type='text' class='editBoxs' style='width: 200px; margin-right: 5px;' />" + comboBox.replace("name='default'", "name='cmb" + ++scriptLinesNumber + "'") + "<br class='backToLine' />");
							break;
							case '3':
								$('#lastLine').before(editDeleteIcon + "<input name='txt" + ++scriptLinesNumber + "' type='text' class='editBoxs' style='width: 200px; margin-right: 5px;' />" + comboBox.replace("name='default'", "name='cmb" + ++scriptLinesNumber + "'") + "<input name='txt" + ++scriptLinesNumber + "' type='text' class='editBoxs' style='width: 150px;' />" + "<br class='backToLine' />");
						}
						
						$("#success_icon").fadeIn(400).fadeOut(400);
					});
				});

				// event click of generate button
				$('#generate').click(function () {
					// we add backLine textBoxs before sending
					$('#script_config br[class="backToLine"]').each(function() {
						$(this).before("<input name='btl" + ++scriptLinesNumber + "' class='txtBoxs' value='[\\n]' style='display : none;' />");
					});
					//return false;
					// removing old loading icons if exists
					$('.loadicon').remove();
					// adding loading icon
					//$(this).after($("<img src='icon/load.gif' />"));
					$(this).after($('<span class="loadicon"></span>'));
				});

				// apply style to all Excel Sheet lines having a class who contains the string 'style'
				$('td[class*="style"]').css({
					'padding' : '5px 10px 0px',
					'font-family' : 'Helvetica,Arial,sans-serif',
					'font-size' : '14px',
					'border' : '1px solid #A9CCD1',
					'text-align' : 'center',
					'vertical-align' : 'middle'
				});
			});
		</script>
	</body>
</html>
