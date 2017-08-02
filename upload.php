<?php
	$error = NULL;
	$filename = NULL;
	$output_dir = isset($_COOKIE['defaultpath']) ? $_COOKIE['defaultpath'] : "files/";

	if (isset($_FILES['uploadFile']) && $_FILES['uploadFile']['error'] === 0) {
		$filename = $_FILES['uploadFile']['name'];
		//chmod($filename, 0777);
		
		// On déplace le fichier depuis le répertoire temporaire vers le répertoire de stockage
		if (@move_uploaded_file($_FILES['uploadFile']['tmp_name'], $output_dir.$filename)) { // Si ça fonctionne
			$error = 'OK';
		}
		else { // Si ça ne fonctionne pas
			$error = "Échec de l'enregistrement !";
		}
	}
	else {
		$error = 'Aucun fichier réceptionné !';
	}
	
	//echo $error;

