<?php
/**
 * Page d'acceuil de ZTE SAT (Script & Telnet) Tool
 * 
 * @author : Anass Denna
 * @Creation Date : 14/03/2015
 * @version : 1.0
 * @email : anass_denna@hotmail.fr
 *
 */
	// set upload max files size (don't work, must change php.ini file)
	//ini_set('post_max_size', '64M');
	//ini_set('upload_max_filesize', '64M');
 
	// force the browser to refresh this page (after a go back for example)
	// any valid date in the past
	header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
	// always modified right now
	header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
	// HTTP/1.1
	header("Cache-Control: private, no-store, max-age=0, no-cache, must-revalidate, post-check=0, pre-check=0");
	// HTTP/1.0
	header("Pragma: no-cache");

    /* don't use this anymore, because that clear the statusbars too, and we don't realy need it, because session will be destroyed after time out or closing browser */
    // starting session
    //session_start();
	
    // delete obsolete session variable 'filename'
    //unset($_SESSION['filename']);

	// we set 3 cookie to remember the default excel files path & memory limit .. if don't exists (must be here and in config.php too)
	$defaultpath = 'files/';
	$defaultmemorylimit = '256';
	$defaultmaxtime = '300'; // 300 seconds = 5 minutes

	$cookiename = ['path' => 'defaultpath', 'limit' => 'memorylimit', 'time' => 'maxexectime'];

	if (empty($_COOKIE[$cookiename['path']]))
		setcookie($cookiename['path'],
		  		  $defaultpath,
		  		  time() + (10 * 365 * 24 * 3600) // expire in 10 years
		);

	if (empty($_COOKIE[$cookiename['limit']]))
		setcookie($cookiename['limit'],
		  		  $defaultmemorylimit.'M', // we add 'M' here (mégabits)
		  		  time() + (10 * 365 * 24 * 3600) // expire in 10 years
		);

	if (empty($_COOKIE[$cookiename['time']]))
		setcookie($cookiename['time'],
		  		  $defaultmaxtime,
		  		  time() + (10 * 365 * 24 * 3600) // expire in 10 years
		);
?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8" />
		<title>ZTE SAT Tool</title>
		<link rel="icon" href="icon/favicon.ico" type="image/x-icon" />
		<link rel="stylesheet" href="index_style.css" />
		<link rel="stylesheet" href="header_style.css" />
	</head>

	<body>
		<?php
			include('header.php');
		?>
		<div class="box">
			<div id="dragandrop">Drag & Drop Your Excel File Here</div>
			<span class="bodyicon">Or</span>
			<input type="button" id="choose" value="Choose Your Excel File" />
			<input id="browse" type="file" multiple />
			<?php
				function createStatusbar($filename, $filesize, $rowCount) {
					$row = 'odd';
     				if($rowCount % 2 == 0) $row = 'even';
     				$statusbar = '<div class="statusbar '.$row.'">'; // open div
     				$statusbar .= '<div class="filename">'.$filename.'</div>';
     				$statusbar .= '<div class="filesize">'.$filesize.'</div>';
	 				$statusbar .= '<a class="createScript" href="createScript.php?file='.$filename.'&show=false">Create Script</a>';
	 				$statusbar .= '<div class="delete">Delete</div>';
	 				$statusbar .= '<span class="showFileLabel"><input class="showFile" type="checkbox" />Show File</span>';
					$statusbar .= '</div>'; // close div

					return $statusbar;
				}

				// set saved files (from cookie)
				if (!empty($_COOKIE['files'])) {
					$files = explode('|', $_COOKIE['files']);
					$rowCount = 0;
					for ($i = count($files) - 2; $i >= 0; $i--) { // count() - 2 becasue we begin from 0 (-1) & array files will always have a free last case (1|2|free case)
						$rowCount++;
						$data = explode(':', $files[$i]); // split file name and size
						echo createStatusbar($data[0], $data[1], $rowCount);
					}
				}
			?>
		</div>

		<!-- including jquery & dragandrop js -->
		<script src="jquery.js"></script>
		<script src="dragandrop.js"></script>
		<script>
			// function to verify the files have been droped/choosed
			function isExcelFile(fileName) {
				var extension = fileName.split('.').pop(); // got extension
				if (extension != 'xls' && extension != 'xlsx') // if it's not an excel file
					return false;
				else
					return true;
			}
			
			// when DOM is loaded
			$(document).ready(function () {
				
				// event click on the button choose..
				$('#choose').click(function () {
					$('#browse').click();
				});

				// event. change (when use choose a file)
				$('#browse').change(function () {
					// check & upload file/files
					handleFileUpload(this.files, $('#browse'));
				});

				// event click button delete
				$('.delete').click(function () {
					var filesize = $(this).prev().prev(), // we get element of filesize
						filename = filesize.prev().html(); // value of filename

					filesize = filesize.html(); // we get value of filesize

					// with jquery better
					$(this).parent().fadeOut(300, function () {
						$(this).remove();
						removeFromCookie('files', filename, filesize);
					});
				});

				// when check Show File
				$('.showFile').change(function () {
					var createScriptAnchor = $(this).parent().prev().prev(), // recherche iéarchique / using DOM tree
						filename = createScriptAnchor.prev().prev().html();

					if ($(this).is(':checked')) {
						createScriptAnchor.attr('href', 'createScript.php?file=' + filename + '&show=true');
						alert('Showing files having size greater than 1MB may freez the browser!');
					}
					else
						createScriptAnchor.attr('href', 'createScript.php?file=' + filename + '&show=false');
				});

				// event click on anchor create Script
				$('.createScript').click(function () {
					// delete loading images if exists
					$('.loadicon').remove();
					// adding loading image
					//$(this).before($("<img id="loadicon" src='icon/load.gif' />"));
					$(this).before($('<span class="loadicon"></span>'));
				});
			});
		</script>
	</body>
</html>
