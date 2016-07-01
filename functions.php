<?php
	// Baseline shared functions for other automated repos to utilize.
	// (C) 2016 CubicleSoft.  All Rights Reserved.

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

	function GitRepoChanged($rootpath)
	{
		chdir($rootpath);
		ob_start();
		system("git status");
		$data = ob_get_contents();
		ob_end_flush();

		return (stripos($data, "Changes not staged for commit:") !== false || stripos($data, "Untracked files:") !== false);
	}

	function GitPull($srcpath)
	{
		$updated = 0;

		$dir = opendir($srcpath);
		if ($dir)
		{
			while (($file = readdir($dir)) !== false)
			{
				if ($file !== "." && $file !== "..")
				{
					// Update the repo.
					chdir($srcpath . "/" . $file);
					ob_start();
					system("git pull");
					$data = ob_get_contents();
					ob_end_flush();

					if (stripos($data, "Already up-to-date.") === false)  $updated++;
				}
			}

			closedir($dir);
		}

		return $updated;
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

	function GetReadmeClassesAndSources($classes, &$includedclasses, &$otherclasses, &$sources, $nameprefix, $files, $fileprefix, $srcpath)
	{
		foreach ($classes as $name => $details)
		{
			$line = "* " . $nameprefix . $name . " - " . $details;

			if (!isset($files[$name]))  $otherclasses[] = "Class not included.  Possible bug.  " . $line;
			else
			{
				$filename = $files[$name];
				$pos = strrpos($filename, "/");
				$filename = substr($filename, $pos + 1);

				$includedclasses[] = $line . "  (" . $fileprefix . $filename . ")";
				unset($files[$name]);
			}
		}

		foreach ($files as $name => $filename)
		{
			$pos = strrpos($filename, "/");
			$filename = substr($filename, $pos + 1);

			$line = "* " . $nameprefix . $name . " - Internal or undocumented class.  (" . $fileprefix . $filename . ")";

			$otherclasses[] = $line;
		}

		$dir = opendir($srcpath);
		if ($dir)
		{
			while (($file = readdir($dir)) !== false)
			{
				if ($file !== "." && $file !== "..")  $sources[] = "* https://github.com/cubiclesoft/" . $file;
			}

			closedir($dir);
		}
	}

	function GenerateReadme($classesfile, $srcreadme, $destreadme, $nameprefix, $files, $fileprefix, $srcpath)
	{
		$classes = json_decode(file_get_contents($classesfile), true);
		$includedclasses = array();
		$otherclasses = array();
		$sources = array();
		GetReadmeClassesAndSources($classes, $includedclasses, $otherclasses, $sources, $nameprefix, $files, $fileprefix, $srcpath);

		$data = file_get_contents($srcreadme);
		$data = str_replace("@INCLUDECLASSES@", implode("\n", $includedclasses), $data);
		$data = str_replace("@OTHERCLASSES@", implode("\n", $otherclasses), $data);
		$data = str_replace("@SOURCES@", implode("\n", $sources), $data);

		file_put_contents($destreadme, $data);
	}

	function CommitRepo($rootpath)
	{
		if (GitRepoChanged($rootpath))
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
	}
?>