<?php
	// CubicleSoft command-line functions.
	// (C) 2016 CubicleSoft.  All Rights Reserved.

	class CLI
	{
		public static function ParseCommandLine($options, $args = false)
		{
			if (!isset($options["shortmap"]))  $options["shortmap"] = array();
			if (!isset($options["rules"]))  $options["rules"] = array();

			// Clean up shortmap and rules.
			foreach ($options["shortmap"] as $key => $val)
			{
				if (!isset($options["rules"][$val]))  unset($options["shortmap"][$key]);
			}
			foreach ($options["rules"] as $key => $val)
			{
				if (!isset($val["arg"]))  $options["rules"][$key]["arg"] = false;
				if (!isset($val["multiple"]))  $options["rules"][$key]["multiple"] = false;
			}

			if ($args === false)  $args = $_SERVER["argv"];
			else if (is_string($args))
			{
				$args2 = $args;
				$args = array();
				$inside = false;
				$currarg = "";
				$y = strlen($args2);
				for ($x = 0; $x < $y; $x++)
				{
					$currchr = substr($args2, $x, 1);

					if ($inside === false && $currchr == " " && $currarg != "")
					{
						$args[] = $currarg;
						$currarg = "";
					}
					else if ($currchr == "\"" || $currchr == "'")
					{
						$inside = ($inside === false ? $currchr : false);
					}
					else if ($currchr == "\\" && $x < $y - 1)
					{
						$x++;
						$currarg .= substr($args2, $x, 1);
					}
					else if ($inside !== false || $currchr != " ")
					{
						$currarg .= $currchr;
					}
				}

				if ($currarg != "")  $args[] = $currarg;
			}

			$result = array("success" => true, "file" => array_shift($args), "opts" => array(), "params" => array());

			// Look over shortmap to determine if options exist that are one byte (flags) and don't have arguments.
			$chrs = array();
			foreach ($options["shortmap"] as $key => $val)
			{
				if (isset($options["rules"][$val]) && !$options["rules"][$val]["arg"])  $chrs[$key] = true;
			}

			$y = count($args);
			for ($x = 0; $x < $y; $x++)
			{
				$arg = $args[$x];

				// Attempt to process an option.
				$opt = false;
				$optval = false;
				if (substr($arg, 0, 1) == "-")
				{
					$pos = strpos($arg, "=");
					if ($pos === false)  $pos = strlen($arg);
					else  $optval = substr($arg, $pos + 1);
					$arg2 = substr($arg, 1, $pos - 1);

					if (isset($options["rules"][$arg2]))  $opt = $arg2;
					else if (isset($options["shortmap"][$arg2]))  $opt = $options["shortmap"][$arg2];
					else if ($x == 0)
					{
						// Attempt to process as a set of flags.
						$y2 = strlen($arg2);
						if ($y2 > 0)
						{
							for ($x2 = 0; $x2 < $y2; $x2++)
							{
								$currchr = substr($arg2, $x2, 1);

								if (!isset($chrs[$currchr]))  break;
							}

							if ($x2 == $y2)
							{
								for ($x2 = 0; $x2 < $y2; $x2++)
								{
									$opt = $options["shortmap"][substr($arg2, $x2, 1)];

									if (!$options["rules"][$opt]["multiple"])  $result["opts"][$opt] = true;
									else
									{
										if (!isset($result["opts"][$opt]))  $result["opts"][$opt] = 0;
										$result["opts"][$opt]++;
									}
								}

								continue;
							}
						}
					}
				}

				if ($opt === false)
				{
					// Is a parameter.
					if (substr($arg, 0, 1) === "\"" || substr($arg, 0, 1) === "'")  $arg = substr($arg, 1);
					if (substr($arg, -1) === "\"" || substr($arg, -1) === "'")  $arg = substr($arg, 0, -1);

					$result["params"][] = $arg;
				}
				else if (!$options["rules"][$opt]["arg"])
				{
					// Is a flag by itself.
					if (!$options["rules"][$opt]["multiple"])  $result["opts"][$opt] = true;
					else
					{
						if (!isset($result["opts"][$opt]))  $result["opts"][$opt] = 0;
						$result["opts"][$opt]++;
					}
				}
				else
				{
					// Is an option.
					if ($optval === false)
					{
						$x++;
						if ($x == $y)  break;
						$optval = $args[$x];
					}

					if (substr($optval, 0, 1) === "\"" || substr($optval, 0, 1) === "'")  $optval = substr($optval, 1);
					if (substr($optval, -1) === "\"" || substr($optval, -1) === "'")  $optval = substr($optval, 0, -1);

					if (!$options["rules"][$opt]["multiple"])  $result["opts"][$opt] = $optval;
					else
					{
						if (!isset($result["opts"][$opt]))  $result["opts"][$opt] = array();
						$result["opts"][$opt][] = $optval;
					}
				}
			}

			return $result;
		}

		// Gets a line of input from the user.  If the user supplies all information via the command-line, this could be entirely automated.
		public static function GetUserInputWithArgs(&$args, $question, $default, $noparamsoutput = "", $suppressoutput = false)
		{
			$outputopts = false;
			if (!count($args["params"]) && $noparamsoutput != "")
			{
				echo "\n" . $noparamsoutput . "\n";

				$suppressoutput = false;
				$outputopts = true;
			}

			do
			{
				if (!$suppressoutput)  echo $question . ($default !== false ? " [" . $default . "]" : "") . ":  ";

				if (count($args["params"]))
				{
					$line = array_shift($args["params"]);
					if ($line === "")  $line = $default;
					if (!$suppressoutput)  echo $line . "\n";
				}
				else if (function_exists("readline") && function_exists("readline_add_history"))
				{
					$line = trim(readline());
					if ($line === "")  $line = $default;
					if ($line !== "")  readline_add_history($line);
				}
				else
				{
					$line = fgets(STDIN);
					$line = trim($line);
					if ($line === "")  $line = $default;
				}

				if ($line === false)
				{
					echo "Please enter a value.\n";

					if (!$outputopts && !count($args["params"]) && $noparamsoutput != "")
					{
						echo "\n" . $noparamsoutput . "\n";

						$outputopts = true;
					}

					$suppressoutput = false;
				}
			} while ($line === false);

			return $line;
		}

		// Obtains a valid line of input.  If the user supplies all information via the command-line, this could be entirely automated.
		public static function GetLimitedUserInputWithArgs(&$args, $question, $default, $allowedoptionsprefix, $allowedoptions, $loop = true, $suppressoutput = false)
		{
			$noparamsoutput = $allowedoptionsprefix . "\n\n";
			$size = 0;
			foreach ($allowedoptions as $key => $val)
			{
				if ($size < strlen($key))  $size = strlen($key);
			}

			foreach ($allowedoptions as $key => $val)
			{
				$newtab = str_repeat(" ", 2 + $size + 3);
				$noparamsoutput .= "  " . $key . ":" . str_repeat(" ", $size - strlen($key)) . "  " . str_replace("\n\t", "\n" . $newtab, $val) . "\n";
			}

			$noparamsoutput .= "\n";

			if ($default === false && count($allowedoptions) == 1)
			{
				reset($allowedoptions);
				$default = key($allowedoptions);
			}

			do
			{
				$result = self::GetUserInputWithArgs($args, $question, $default, $noparamsoutput, $suppressoutput);
				$result2 = false;
				foreach ($allowedoptions as $key => $val)
				{
					if (!strcasecmp($key, $result) || !strcasecmp($val, $result))  $result2 = $key;
				}
				if ($loop && $result2 === false && !$suppressoutput)  echo "Invalid option selected.\n";

				$noparamsoutput = "";
			} while ($loop && $result2 === false);

			return $result2;
		}

		// Obtains Yes/No style input.
		public static function GetYesNoUserInputWithArgs(&$args, $question, $default, $suppressoutput = false)
		{
			$default = (substr(strtoupper(trim($default)), 0, 1) === "Y" ? "Y" : "N");

			$result = self::GetUserInputWithArgs($args, $question, $default, $suppressoutput);
			$result = (substr(strtoupper(trim($result)), 0, 1) === "Y");

			return $result;
		}

		// Tracks messages for a command-line interface app.
		private static $messages = array();

		public static function LogMessage($msg, $data = null)
		{
			if (isset($data))  $msg .= "\n\t" . trim(str_replace("\n", "\n\t", json_encode($data, JSON_PRETTY_PRINT)));

			self::$messages[] = $msg;

			fwrite(STDERR, $msg . "\n");
		}

		public static function DisplayError($msg, $result = false, $exit = true)
		{
			self::LogMessage(($exit ? "[Error] " : "") . $msg);

			if ($result !== false && is_array($result) && isset($result["error"]) && isset($result["errorcode"]))  self::LogMessage("[Error] " . $result["error"] . " (" . $result["errorcode"] . ")", (isset($result["info"]) ? $result["info"] : null));

			if ($exit)  exit();
		}

		public static function GetLogMessages($filters = array())
		{
			if (is_string($filters))  $filters = array($filters);

			$result = array();
			foreach (self::$messages as $message)
			{
				$found = (!count($filters));
				foreach ($filters as $filter)
				{
					if (preg_match($filter, $message))  $found = true;
				}

				if ($found)  $result[] = $message;
			}

			return $result;
		}

		public static function ResetLogMessages()
		{
			self::$messages = array();
		}
	}
?>