<?php
	// This runs the main updater.  Requires PHP and git on the path and repo commit access.  That last part you, of course, don't have.
	// (C) 2016 CubicleSoft.  All Rights Reserved.

	if (!isset($_SERVER["argc"]) || !$_SERVER["argc"])
	{
		echo "This file is intended to be run from the command-line.";

		exit();
	}

	function DeleteDirectory($path)
	{
		if (substr($path, -1) == "/")  $path = substr($path, 0, -1);

		$dir = opendir($path);
		if ($dir)
		{
			while (($file = readdir($dir)) !== false)
			{
				if ($file != "." && $file != "..")
				{
					if (is_link($path . "/" . $file) || is_file($path . "/" . $file))  unlink($path . "/" . $file);
					else
					{
						DeleteDirectory($path . "/" . $file);
						rmdir($path . "/" . $file);
					}
				}
			}

			closedir($dir);
		}
	}

	$rootpath = str_replace("\\", "/", dirname(__FILE__));
	$srcpath = $rootpath . "/repos";
	$destpath = $rootpath . "/support";

	if (!is_dir($srcpath))  mkdir($srcpath);
	if (!is_dir($destpath))  mkdir($destpath);

	// Always do a full rebuild.
	DeleteDirectory($destpath);

	// Walk the registered repo list.
	$dir = opendir($srcpath);
	if ($dir)
	{
		while (($file = readdir($dir)) !== false)
		{
			if ($file !== "." && $file !== "..")
			{
				// Update the repo.
				chdir($srcpath . "/" . $file);
				system("git pull");
			}
		}

		closedir($dir);
	}

	$excludefiles = array(
		"." => true,
		".." => true,
		".git" => true,
		"Net" => true,
	);

	function GetPHPFiles(&$result, $path)
	{
		global $excludefiles;

		$dir = opendir($path);
		if ($dir)
		{
			while (($file = readdir($dir)) !== false)
			{
				if (!isset($excludefiles[$file]))
				{
					$filename = $path . "/" . $file;
					if (is_dir($filename))  GetPHPFiles($result, $filename);
					else if (substr($filename, -4) === ".php")
					{
						$fp = fopen($filename, "rb");

						while (($line = fgets($fp)) !== false)
						{
							$line = trim($line);

							if (substr($line, 0, 6) === "class ")
							{
								$line = trim(substr($line, 6));
								$pos = strpos($line, " ");
								if ($pos === false)  $pos = strlen($line);
								$name = substr($line, 0, $pos);

								if (isset($result[$name]) && filemtime($result[$name]) < filemtime($filename))  unset($result[$name]);

								if (!isset($result[$name]))  $result[$name] = $filename;
							}
						}

						fclose($fp);
					}
				}
			}

			closedir($dir);
		}
	}

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

	// Net_DNS2
	mkdir($destpath . "/Net");
	copy($srcpath . "/ultimate-email/support/Net/DNS2.php", $destpath . "/Net/DNS2.php");
	copy($srcpath . "/ultimate-email/support/Net/license.txt", $destpath . "/Net/license.txt");

	// CA certificates.
	copy($srcpath . "/ultimate-web-scraper/support/cacert.pem", $destpath . "/cacert.pem");

	// Generate README.
	$classes = json_decode(file_get_contents($rootpath . "/readme_src/classes.json"), true);
	$includedclasses = array();
	$otherclasses = array();
	foreach ($classes as $name => $details)
	{
		$line = "* " . $name . " - " . $details;

		if (!isset($files[$name]))  $otherclasses[] = "Class not included.  Possible bug.  " . $line;
		else
		{
			$filename = $files[$name];
			$pos = strrpos($filename, "/");
			$filename = substr($filename, $pos + 1);

			$includedclasses[] = $line . "  (support/" . $filename . ")";
			unset($files[$name]);
		}
	}

	foreach ($files as $name => $filename)
	{
		$pos = strrpos($filename, "/");
		$filename = substr($filename, $pos + 1);

		$line = "* " . $name . " - Internal or undocumented class.  (support/" . $filename . ")";

		$otherclasses[] = $line;
	}

	$sources = array();
	$dir = opendir($srcpath);
	if ($dir)
	{
		while (($file = readdir($dir)) !== false)
		{
			if ($file !== "." && $file !== "..")  $sources[] = "* https://github.com/cubiclesoft/" . $file;
		}

		closedir($dir);
	}

	$data = file_get_contents($rootpath . "/readme_src/README.md");
	$data = str_replace("@INCLUDECLASSES@", implode("\n", $includedclasses), $data);
	$data = str_replace("@OTHERCLASSES@", implode("\n", $otherclasses), $data);
	$data = str_replace("@SOURCES@", implode("\n", $sources), $data);

	file_put_contents($rootpath . "/README.md", $data);

	chdir($rootpath);
	ob_start();
	system("git status");
	$data = ob_get_contents();
	ob_end_flush();

	if (stripos($data, "Changes not staged for commit:") !== false || stripos($data, "Untracked files:") !== false)
	{
		// Commit all the things.
		system("git add -A");
		system("git commit -m \"Updated.\"");
		system("git push origin master");

		// Tag the new release.
		$ver = (int)@file_get_contents($rootpath . "/ver.dat");
		system("git tag -a 1.0." . $ver . " -m \"1.0." . $ver . "\"");
		system("git push --tags origin master");
		$ver++;
		file_put_contents($rootpath . "/ver.dat", (string)$ver);
	}
?>