<?php
/*
Uploadify
Copyright (c) 2012 Reactive Apps, Ronnie Garcia
Released under the MIT License <http://www.opensource.org/licenses/mit-license.php> 
*/

// Define a destination
$targetFolder = '/uploads'; // Relative to the root

if (!empty($_FILES)) {
	$tempFile = $_FILES['Filedata']['tmp_name'];
	// $targetPath = $_SERVER['DOCUMENT_ROOT'] . $targetFolder;
	$targetPath ="../uploads";
	$name=explode(".", $_FILES['Filedata']['name']);
	$newname=time().".".$name[1];
	$targetFile = rtrim($targetPath,'/') . '/' . $newname;
	
	// Validate the file type
	$fileTypes = array('jpg','jpeg','gif','png'); // File extensions
	$fileParts = pathinfo($_FILES['Filedata']['name']);
	$ext=strtolower($fileParts['extension']) ;
	// if (in_array($ext,$fileTypes)) {
		move_uploaded_file($tempFile,$targetFile);
		echo $newname;
		// echo $targetFile;
	// } else {

		// echo 'Invalid file type.'.var_export($fileParts,true);
	// }




	// $tempFile = $_FILES['Filedata']['tmp_name'];
	// $targetPath = $_SERVER['DOCUMENT_ROOT'] . $targetFolder;
	// $name=explode(".", $_FILES['Filedata']['name']);
	// $newname=time().".".$name[1];
	// $targetFile = rtrim($targetPath,'/') . '/' .$newname ;
	
	// // Validate the file type
	// $fileTypes = array('jpg','jpeg','gif','png'); // File extensions
	// $fileParts = pathinfo($_FILES['Filedata']['name']);
	
	// if (in_array($fileParts['extension'],$fileTypes)) {
	// 	move_uploaded_file($tempFile,$targetFile);
	// 	echo $newname;
	// } else {
	// 	echo 'Invalid file type.';
	// }
}
?>