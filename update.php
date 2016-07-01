<?php
	// This runs the main updater.  Requires PHP and git on the path and repo commit access.  That last part you, of course, don't have.
	// (C) 2016 CubicleSoft.  All Rights Reserved.

	if (!isset($_SERVER["argc"]) || !$_SERVER["argc"])
	{
		echo "This file is intended to be run from the command-line.";

		exit();
	}

	$rootpath = str_replace("\\", "/", dirname(__FILE__));

	require_once $rootpath . "/functions.php";

	$srcpath = $rootpath . "/repos";
	$destpath = $rootpath . "/support";

	if (!is_dir($srcpath))  mkdir($srcpath);
	if (!is_dir($destpath))  mkdir($destpath);

	// Always do a full rebuild.
	DeleteDirectory($destpath);

	// Update the registered repo list.
	GitPull($srcpath);

	// Retrieve a list of all PHP files that contain 'class' + a name.
	$files = array();
	GetPHPFiles($files, $srcpath);

	// Generate final file set.
	foreach ($files as $name => $filename)
	{
		$data = file_get_contents($filename);

		$pos = strrpos($filename, "/");
		$name = substr($filename, $pos + 1);

		file_put_contents($destpath . "/" . $name, $data);
	}

	// Net_DNS2.
	mkdir($destpath . "/Net");
	copy($srcpath . "/ultimate-email/support/Net/DNS2.php", $destpath . "/Net/DNS2.php");
	copy($srcpath . "/ultimate-email/support/Net/license.txt", $destpath . "/Net/license.txt");

	// CA certificates.
	copy($srcpath . "/ultimate-web-scraper/support/cacert.pem", $destpath . "/cacert.pem");

	// Generate README.
	GenerateReadme($rootpath . "/readme_src/classes.json", $rootpath . "/readme_src/README.md", $rootpath . "/README.md", "", $files, "support/", $srcpath);

	CommitRepo($rootpath);
?>