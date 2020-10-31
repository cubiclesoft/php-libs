<?php
	// Natural language builder/generator.  Dynamically generates content based on input rules and data.
	// (C) 2020 CubicleSoft.  All Rights Reserved.

	class NaturalLanguage
	{
		public static function CalculateUniqueStates(&$rules)
		{
			if (isset($rules[""]) && isset($rules[""]["type"]) && $rules[""]["type"] === "if" && isset($rules[""]["rules"]))  $result = count($rules[""]["rules"]) + (isset($rules[""]["randomize"]) && $rules[""]["randomize"] && isset($rules[""]["matches"]) && $rules[""]["matches"] > 1 && count($rules[""]["rules"]) > 1 ? (count($rules[""]["rules"]) - 1) * min($rules[""]["matches"], count($rules[""]["rules"])) : 0);
			else  return 0;

			foreach ($rules as $rkey => $rule)
			{
				if ($rkey === "" || !isset($rule["type"]))  continue;

				if ($rule["type"] === "if" && isset($rule["rules"]))  $result += count($rule["rules"]) + (isset($rule["randomize"]) && $rule["randomize"] && isset($rule["matches"]) && $rule["matches"] > 1 && count($rule["rules"]) > 1 ? (count($rule["rules"]) - 1) * min($rule["matches"], count($rule["rules"])) : 0);
			}

			return $result;
		}

		public static function Generate($data, &$rules, $options = array())
		{
			if (!is_array($data))  return array("success" => false, "error" => self::NLBTranslate("Invalid data specified.  Expected array."), "errorcode" => "invalid_data");
			if (!is_array($rules) || !isset($rules[""]))  return array("success" => false, "error" => self::NLBTranslate("Invalid rules specified.  Expected root rule."), "errorcode" => "invalid_rules");

			$options["used_data"] = array();
			$options["used_rules"] = array();

			$result = self::ProcessRule($data, $rules, "", $options);

			$result["used_data"] = $options["used_data"];
			$result["used_rules"] = $options["used_rules"];

			return $result;
		}

		protected static function ProcessRule(&$data, &$rules, $rkey, &$options)
		{
			if (!isset($rules[$rkey]))  return array("success" => false, "error" => self::NLBTranslate("The rule '%s' does not exist.", $rkey), "errorcode" => "invalid_rule_reference");
			if (isset($options["used_rules"][$rkey]))  return array("success" => false, "error" => self::NLBTranslate("Probable infinite loop detected in rules at '%s'.", $rkey), "errorcode" => "infinite_loop_detected");

			$options["used_rules"][$rkey] = true;

			$rule = $rules[$rkey];
			if (!isset($rule["type"]))  return array("success" => false, "error" => self::NLBTranslate("Rule '%s' is missing a 'type'.", $rkey), "errorcode" => "missing_rule_type");

			if ($rule["type"] === "data")
			{
				// Handle data output.
				if (!isset($rule["key"]))  return array("success" => false, "error" => self::NLBTranslate("Rule '%s' is missing a data 'key' for the 'data' type.", $rkey), "errorcode" => "missing_rule_key");
				if (!isset($data[$rule["key"]]))  return array("success" => false, "error" => self::NLBTranslate("The expected data field '%s' does not exist as specified by rule '%s'.", $rule["key"], $rkey), "errorcode" => "missing_data_field");

				if (!isset($options["used_data"][$rule["key"]]))  $options["used_data"][$rule["key"]] = 0;
				$options["used_data"][$rule["key"]]++;

				$val = $data[$rule["key"]];

				// Format the value.
				if (isset($rule["format"]))
				{
					if ($rule["format"] === "number")
					{
						$val = number_format((double)$val, (isset($rule["decimals"]) ? (int)$rule["decimals"] : 0), (isset($rule["decpoint"]) && is_string($rule["decpoint"]) ? $rule["decpoint"] : "."), (isset($rule["separator"]) && is_string($rule["separator"]) ? $rule["separator"] : ","));
					}
					else if ($rule["format"] === "date" || $rule["format"] === "time")
					{
						if (!isset($rule["date"]) || !is_string($rule["date"]))  return array("success" => false, "error" => self::NLBTranslate("The 'date' format string does not exist as specified by rule '%s'.", $rkey), "errorcode" => "missing_date");

						$ts = strtotime(($rule["format"] === "time" ? date("Y") . "-01-01 " : "") . $val);

						$val = call_user_func((isset($rule["gmt"]) && $rule["gmt"] === true ? "gmdate" : "date"), $rule["date"], $ts);
					}
				}

				// Apply text case changes.
				if (isset($rule["case"]))
				{
					$cases = explode(",", $rule["case"]);
					foreach ($cases as $case)
					{
						$case = trim($case);

						if ($case === "lower")  $val = strtolower($val);
						else if ($case === "upper")  $val = strtoupper($val);
						else if ($case === "words")  $val = ucwords($val);
						else if ($case === "first")  $val = ucfirst($val);
					}
				}

				// Run string replacements.
				if (isset($rule["replace"]) && is_array($rule["replace"]))  $val = str_replace(array_keys($rule["replace"]), array_values($rule["replace"]), $val);

				return array("success" => true, "value" => $val);
			}
			else if ($rule["type"] === "if")
			{
				// Handle if conditionals.
				if (!isset($rule["rules"]) || !is_array($rule["rules"]))  return array("success" => false, "error" => self::NLBTranslate("Rule '%s' is missing 'rules' for the 'if' type.", $rkey), "errorcode" => "missing_if_rules");

				// Reorder rules randomly as desired.
				if (isset($rule["randomize"]) && $rule["randomize"])  shuffle($rule["rules"]);

				$val = "";
				$matched = 0;
				foreach ($rule["rules"] as $rule2)
				{
					$valid = true;

					if (isset($rule2["cond"]))
					{
						$result = self::ParseConditional($rule2["cond"]);
						if (!$result["success"])  return $result;

						$result = self::RunConditionalCheck($result["tokens"], $data, $options);
						if (!$result["success"])  return $result;

						$valid = $result["value"];

						$options["used_data"] = $result["used_data"];
					}

					if ($valid)
					{
						if (isset($rule2["output"]))
						{
							if (is_string($rule2["output"]))  $rule2["output"] = array($rule2["output"]);

							foreach ($rule2["output"] as $output)
							{
								if (substr($output, 0, 1) === "@")
								{
									$result = self::ProcessRule($data, $rules, substr($output, 1), $options);
									if (!$result["success"])  return $result;

									$val .= $result["value"];
								}
								else if (substr($output, 0, 2) === "[[" && substr($output, -2) === "]]")
								{
									$dkey = substr($output, 2, -2);
									if (!isset($data[$dkey]))  return array("success" => false, "error" => self::NLBTranslate("The expected data field '%s' does not exist as specified by rule '%s'.", $dkey, $rkey), "errorcode" => "missing_data_field");

									$val .= $data[$dkey];
								}
								else
								{
									$val .= $output;
								}
							}
						}

						$matched++;
						if (isset($rule["matches"]) && $rule["matches"] > 0 && $matched >= $rule["matches"])  break;
					}
				}

				return array("success" => true, "value" => $val);
			}
			else
			{
				return array("success" => false, "error" => self::NLBTranslate("Rule type '%s' is invalid.  Expected 'data' or 'if'.", $rule["type"]), "errorcode" => "invalid_rule_type");
			}
		}

		public static function MakeConditional($tokens)
		{
			$result = "";
			foreach ($tokens as $token)
			{
				if ($token[0] === "grp_s" || $token[0] === "grp_e")
				{
					if ($token[0] === "grp_s" && $result !== "" && $result[strlen($result) - 1] !== "(")  $result .= " ";

					$result .= $token[1];
				}
				else if ($token[0] !== "space")
				{
					if ($result !== "" && $result[strlen($result) - 1] !== "(")  $result .= " ";

					if ($token[0] === "var")
					{
						if (preg_match('/^[A-Za-z0-9_]+$/', $token[1]))  $result .= $token[1];
						else  $result .= "[[" . $token[1] . "]]";
					}
					else if ($token[0] === "op" || $token[0] === "lop" || $token[0] === "cond")
					{
						$result .= $token[1];
					}
					else if ($token[0] === "val")
					{
						if (is_numeric($token[1]))  $result .= $token[1];
						else  $result .= "\"" . str_replace("\"", "\\\"", $token[1]) . "\"";
					}
				}
			}

			return $result;
		}

		public static function ParseConditional($cond, $keepspaces = false)
		{
			$a = ord("A");
			$a2 = ord("a");
			$z = ord("Z");
			$z2 = ord("z");
			$zero = ord("0");
			$nine = ord("9");
			$underscore = ord("_");

			$tokens = array();
			$lasttoken = false;
			$allowcond = true;
			$cx = 0;
			$cy = strlen($cond);
			$depth = 0;
			while ($cx < $cy)
			{
				if ($cond[$cx] === "[" && $cx + 1 < $cy && $cond[$cx + 1] === "[")
				{
					// Handle [[variable]].
					if ($lasttoken !== false && $lasttoken[0] !== "op" && $lasttoken[0] !== "lop" && $lasttoken[0] !== "cond" && $lasttoken[0] !== "grp_s")  return array("success" => false, "error" => self::NLBTranslate("Found invalid variable start at position %u in '%s'.", $cx + 1, $cond), "errorcode" => "invalid_variable_start");

					$pos = strpos($cond, "]]", $cx + 2);
					if ($pos === false)  $pos = $cy;

					$lasttoken = array("var", substr($cond, $cx + 2, $pos - $cx - 2));
					$tokens[] = $lasttoken;

					$cx = $pos + 2;
				}
				else if (($cond[$cx] === "&" || $cond[$cx] === "|") && $cx + 1 < $cy && $cond[$cx] === $cond[$cx + 1])
				{
					// Handle && and ||.
					if ($lasttoken !== false && $lasttoken[0] !== "var" && $lasttoken[0] !== "val" && $lasttoken[0] !== "grp_e")  return array("success" => false, "error" => self::NLBTranslate("Found invalid operand at position %u in '%s'.", $cx + 1, $cond), "errorcode" => "invalid_operand");

					$lasttoken = array("lop", $cond[$cx] . $cond[$cx + 1]);
					$tokens[] = $lasttoken;

					$allowcond = true;
					$cx += 2;
				}
				else if ($cond[$cx] === "+" || $cond[$cx] === "-" || $cond[$cx] === "*" || $cond[$cx] === "/" || $cond[$cx] === "%" || $cond[$cx] === "&" || $cond[$cx] === "^" || $cond[$cx] === "|")
				{
					// Handle +, -, *, /, %, &, ^, |.
					if ($lasttoken !== false && $lasttoken[0] !== "var" && $lasttoken[0] !== "val" && $lasttoken[0] !== "grp_e")  return array("success" => false, "error" => self::NLBTranslate("Found invalid operand at position %u in '%s'.", $cx + 1, $cond), "errorcode" => "invalid_operand");

					$lasttoken = array("op", $cond[$cx]);
					$tokens[] = $lasttoken;

					$cx++;
				}
				else if (($cond[$cx] === "<" || $cond[$cx] === ">") && $cx + 1 < $cy && $cond[$cx] === $cond[$cx + 1])
				{
					// Handle <<, >>.
					if ($lasttoken !== false && $lasttoken[0] !== "var" && $lasttoken[0] !== "val" && $lasttoken[0] !== "grp_e")  return array("success" => false, "error" => self::NLBTranslate("Found invalid operand at position %u in '%s'.", $cx + 1, $cond), "errorcode" => "invalid_operand");

					$lasttoken = array("op", $cond[$cx] . $cond[$cx + 1]);
					$tokens[] = $lasttoken;

					$cx += 2;
				}
				else if (($cond[$cx] === "!" || $cond[$cx] === "=" || $cond[$cx] === "<" || $cond[$cx] === ">") && $cx + 1 < $cy && $cond[$cx + 1] === "=")
				{
					// Handle !=, ==, <=, >=.
					if (!$allowcond || ($lasttoken !== false && $lasttoken[0] !== "var" && $lasttoken[0] !== "val" && $lasttoken[0] !== "grp_e"))  return array("success" => false, "error" => self::NLBTranslate("Found invalid condition at position %u in '%s'.", $cx + 1, $cond), "errorcode" => "invalid_condition");

					$lasttoken = array("cond", $cond[$cx] . $cond[$cx + 1]);
					$tokens[] = $lasttoken;

					$allowcond = false;
					$cx += 2;
				}
				else if ($cond[$cx] === "<" || $cond[$cx] === ">")
				{
					// Handle < and >.
					if (!$allowcond || ($lasttoken !== false && $lasttoken[0] !== "var" && $lasttoken[0] !== "val" && $lasttoken[0] !== "grp_e"))  return array("success" => false, "error" => self::NLBTranslate("Found invalid condition at position %u in '%s'.", $cx + 1, $cond), "errorcode" => "invalid_condition");

					$lasttoken = array("cond", $cond[$cx]);
					$tokens[] = $lasttoken;

					$allowcond = false;
					$cx++;
				}
				else if ($cond[$cx] === "(")
				{
					// Handle (.
					if ($lasttoken !== false && $lasttoken[0] !== "op" && $lasttoken[0] !== "lop" && $lasttoken[0] !== "cond" && $lasttoken[0] !== "grp_s")  return array("success" => false, "error" => self::NLBTranslate("Found invalid opening parenthesis at position %u in '%s'.", $cx + 1, $cond), "errorcode" => "invalid_group_start");

					$lasttoken = array("grp_s", $cond[$cx]);
					$tokens[] = $lasttoken;

					$depth++;
					$cx++;
				}
				else if ($cond[$cx] === ")")
				{
					// Handle ).
					if (!$depth)  return array("success" => false, "error" => self::NLBTranslate("Found unexpected closing parenthesis at position %u in '%s'.", $cx + 1, $cond), "errorcode" => "invalid_group_end");
					if ($lasttoken !== false && $lasttoken[0] !== "var" && $lasttoken[0] !== "val" && $lasttoken[0] !== "grp_e")  return array("success" => false, "error" => self::NLBTranslate("Found invalid closing parenthesis at position %u in '%s'.", $cx + 1, $cond), "errorcode" => "invalid_group_end");

					$lasttoken = array("grp_e", $cond[$cx]);
					$tokens[] = $lasttoken;

					$depth--;
					$cx++;
				}
				else if ($cond[$cx] === " " || $cond[$cx] === "\t" || $cond[$cx] === "\r" || $cond[$cx] === "\n" || $cond[$cx] === "\0")
				{
					// Handle space tokens.
					if ($keepspaces)
					{
						for ($cx2 = $cx + 1; $cx2 < $cy && ($cond[$cx2] === " " || $cond[$cx2] === "\t" || $cond[$cx2] === "\r" || $cond[$cx2] === "\n" || $cond[$cx2] === "\0"); $cx2++)
						{
						}

						$tokens[] = array("space", substr($cond, $cx, $cx2 - $cx));

						$cx = $cx2 - 1;
					}

					$cx++;
				}
				else if ((ord($cond[$cx]) >= $zero && ord($cond[$cx]) <= $nine) || $cond[$cx] === ".")
				{
					// Handle numeric values.
					if ($lasttoken !== false && $lasttoken[0] !== "op" && $lasttoken[0] !== "lop" && $lasttoken[0] !== "cond" && $lasttoken[0] !== "grp_s")  return array("success" => false, "error" => self::NLBTranslate("Found invalid numeric start at position %u in '%s'.", $cx + 1, $cond), "errorcode" => "invalid_numeric_start");

					$dot = false;
					$e = false;
					for ($cx2 = $cx; $cx2 < $cy && ((ord($cond[$cx2]) >= $zero && ord($cond[$cx2]) <= $nine) || (!$dot && $cond[$cx2] === ".") || (!$e && ($cond[$cx2] === "e" || $cond[$cx2] === "E"))); $cx2++)
					{
						if (!$dot && $cond[$cx2] === ".")  $dot = true;
						if (!$e && ($cond[$cx2] === "e" || $cond[$cx2] === "E"))  $e = true;
					}

					$lasttoken = array("val", substr($cond, $cx, $cx2 - $cx));
					$tokens[] = $lasttoken;

					$cx = $cx2;
				}
				else if ($cond[$cx] === "\"" || $cond[$cx] === "'")
				{
					// Handle quoted strings.
					if ($lasttoken !== false && $lasttoken[0] !== "op" && $lasttoken[0] !== "lop" && $lasttoken[0] !== "cond" && $lasttoken[0] !== "grp_s")  return array("success" => false, "error" => self::NLBTranslate("Found invalid string start at position %u in '%s'.", $cx + 1, $cond), "errorcode" => "invalid_string_start");

					$str = "";
					for ($cx2 = $cx + 1; $cx2 < $cy && $cond[$cx2] !== $cond[$cx]; $cx2++)
					{
						if ($cond[$cx2] === "\\")  $cx2++;

						$str .= $cond[$cx2];
					}

					$lasttoken = array("val", $str);
					$tokens[] = $lasttoken;

					$cx = $cx2 + 1;
				}
				else
				{
					// Handle other values as variables.
					if ($lasttoken !== false && $lasttoken[0] !== "op" && $lasttoken[0] !== "lop" && $lasttoken[0] !== "cond" && $lasttoken[0] !== "grp_s")  return array("success" => false, "error" => self::NLBTranslate("Found invalid variable start at position %u in '%s'.", $cx + 1, $cond), "errorcode" => "invalid_variable_start");

					for ($cx2 = $cx; $cx2 < $cy; $cx2++)
					{
						$tempchr = ord($cond[$cx2]);
						if (!(($tempchr >= $a && $tempchr <= $z) || ($tempchr >= $a2 && $tempchr <= $z2) || ($tempchr >= $zero && $tempchr <= $nine) || $tempchr === $underscore))  break;
					}

					$lasttoken = array("var", substr($cond, $cx, $cx2 - $cx));
					$tokens[] = $lasttoken;

					$cx = $cx2;
				}
			}

			if ($depth)  return array("success" => false, "error" => self::NLBTranslate("Expected closing parenthesis at position %u in '%s'.", $cy + 1, $cond), "errorcode" => "missing_group_end");

			if ($lasttoken === false || ($lasttoken[0] !== "var" && $lasttoken[0] !== "val" && $lasttoken[0] !== "grp_e"))  return array("success" => false, "error" => self::NLBTranslate("Expected variable, value, or closing parenthesis at position %u in '%s'.", $cy + 1, $cond), "errorcode" => "missing_var_val_paren");

			return array("success" => true, "tokens" => $tokens);
		}

		protected static function ReduceConditionalCheckStacks(&$valstack, &$parenstack, &$opstack)
		{
			$destsize = (count($parenstack) ? $parenstack[count($parenstack) - 1] : 0);

			// Resolve *, /, and %.
			$opsused = array();
			$x = $destsize;
			$y = count($opstack);
			while ($x < $y)
			{
				if ($opstack[$x] !== "*" && $opstack[$x] !== "/" && $opstack[$x] !== "%")
				{
					$opsused[$opstack[$x]] = true;

					$x++;
				}
				else
				{
					if ($opstack[$x] === "*")  $valstack[$x] *= $valstack[$x + 1];
					else if ($opstack[$x] === "/")  $valstack[$x] /= $valstack[$x + 1];
					else if ($opstack[$x] === "%")  $valstack[$x] %= $valstack[$x + 1];

					array_splice($valstack, $x + 1, 1);
					array_splice($opstack, $x, 1);

					$y--;
				}
			}

			// Resolve + and -.
			if (isset($opsused["+"]) || isset($opsused["-"]))
			{
				unset($opsused["+"]);
				unset($opsused["-"]);

				$x = $destsize;
				while ($x < $y)
				{
					if ($opstack[$x] !== "+" && $opstack[$x] !== "-")  $x++;
					else
					{
						if ($opstack[$x] === "+")  $valstack[$x] += $valstack[$x + 1];
						else if ($opstack[$x] === "-")  $valstack[$x] -= $valstack[$x + 1];

						array_splice($valstack, $x + 1, 1);
						array_splice($opstack, $x, 1);

						$y--;
					}
				}
			}

			// Resolve << and >>.
			if (isset($opsused["<<"]) || isset($opsused[">>"]))
			{
				unset($opsused["<<"]);
				unset($opsused[">>"]);

				$x = $destsize;
				while ($x < $y)
				{
					if ($opstack[$x] !== "<<" && $opstack[$x] !== ">>")  $x++;
					else
					{
						if ($opstack[$x] === "<<")  $valstack[$x] <<= $valstack[$x + 1];
						else if ($opstack[$x] === ">>")  $valstack[$x] >>= $valstack[$x + 1];

						array_splice($valstack, $x + 1, 1);
						array_splice($opstack, $x, 1);

						$y--;
					}
				}
			}

			// Resolve <, <=, >, >=.
			if (isset($opsused["<"]) || isset($opsused["<="]) || isset($opsused[">"]) || isset($opsused[">="]))
			{
				unset($opsused["<"]);
				unset($opsused["<="]);
				unset($opsused[">"]);
				unset($opsused[">="]);

				$x = $destsize;
				while ($x < $y)
				{
					if ($opstack[$x] !== "<" && $opstack[$x] !== "<=" && $opstack[$x] !== ">" && $opstack[$x] !== ">=")  $x++;
					else
					{
						if ($opstack[$x] === "<")  $valstack[$x] = $valstack[$x] < $valstack[$x + 1];
						else if ($opstack[$x] === "<=")  $valstack[$x] = $valstack[$x] <= $valstack[$x + 1];
						else if ($opstack[$x] === ">")  $valstack[$x] = $valstack[$x] > $valstack[$x + 1];
						else if ($opstack[$x] === ">=")  $valstack[$x] = $valstack[$x] >= $valstack[$x + 1];

						array_splice($valstack, $x + 1, 1);
						array_splice($opstack, $x, 1);

						$y--;
					}
				}
			}

			// Resolve == and !=.
			if (isset($opsused["=="]) || isset($opsused["!="]))
			{
				unset($opsused["=="]);
				unset($opsused["!="]);

				$x = $destsize;
				while ($x < $y)
				{
					if ($opstack[$x] !== "==" && $opstack[$x] !== "!=")  $x++;
					else
					{
						if ($opstack[$x] === "==")  $valstack[$x] = $valstack[$x] == $valstack[$x + 1];
						else if ($opstack[$x] === "!=")  $valstack[$x] = $valstack[$x] != $valstack[$x + 1];

						array_splice($valstack, $x + 1, 1);
						array_splice($opstack, $x, 1);

						$y--;
					}
				}
			}

			// Resolve &.
			if (isset($opsused["&"]))
			{
				unset($opsused["&"]);

				$x = $destsize;
				while ($x < $y)
				{
					if ($opstack[$x] !== "&")  $x++;
					else
					{
						$valstack[$x] = $valstack[$x] & $valstack[$x + 1];

						array_splice($valstack, $x + 1, 1);
						array_splice($opstack, $x, 1);

						$y--;
					}
				}
			}

			// Resolve ^.
			if (isset($opsused["^"]))
			{
				unset($opsused["^"]);

				$x = $destsize;
				while ($x < $y)
				{
					if ($opstack[$x] !== "^")  $x++;
					else
					{
						$valstack[$x] = $valstack[$x] ^ $valstack[$x + 1];

						array_splice($valstack, $x + 1, 1);
						array_splice($opstack, $x, 1);

						$y--;
					}
				}
			}

			// Resolve |.
			if (isset($opsused["|"]))
			{
				unset($opsused["|"]);

				$x = $destsize;
				while ($x < $y)
				{
					if ($opstack[$x] !== "|")  $x++;
					else
					{
						$valstack[$x] = $valstack[$x] | $valstack[$x + 1];

						array_splice($valstack, $x + 1, 1);
						array_splice($opstack, $x, 1);

						$y--;
					}
				}
			}

			return (count($opsused) == 0);
		}

		public static function RunConditionalCheck($tokens, &$data, $options = array())
		{
			if (is_string($tokens))
			{
				$result = self::ParseConditional($tokens);
				if (!$result["success"])  return $result;

				$tokens = $result["tokens"];
			}

			if (!isset($options["used_data"]))  $options["used_data"] = array();

			$valstack = array();
			$parenstack = array();
			$opstack = array();
			$skipops = false;

			foreach ($tokens as $token)
			{
				if ($skipops && $token[0] !== "grp_e")  continue;

				if ($token[0] === "var")
				{
					if (!isset($data[$token[1]]))  return array("success" => false, "error" => self::NLBTranslate("The expected data field '%s' does not exist.", $token[1]), "errorcode" => "missing_data_field");

					if (!isset($options["used_data"][$token[1]]))  $options["used_data"][$token[1]] = 0;
					$options["used_data"][$token[1]]++;

					$val = $data[$token[1]];

					$valstack[] = $val;
				}
				else if ($token[0] === "val")
				{
					$valstack[] = $token[1];
				}
				else if ($token[0] === "op" || $token[0] === "cond")
				{
					$opstack[] = $token[1];
				}
				else if ($token[0] === "grp_s")
				{
					$parenstack[] = count($opstack);
				}
				else if ($token[0] === "grp_e")
				{
					// Process closing parenthesis.
					if (!self::ReduceConditionalCheckStacks($valstack, $parenstack, $opstack))  return array("success" => false, "error" => self::NLBTranslate("An unexpected/invalid operator was encountered in the tokens."), "errorcode" => "unexpected_operator");

					array_pop($parenstack);

					$skipops = false;
				}
				else if ($token[0] === "lop")
				{
					// Process logical operation.
					if (!self::ReduceConditionalCheckStacks($valstack, $parenstack, $opstack))  return array("success" => false, "error" => self::NLBTranslate("An unexpected/invalid operator was encountered in the tokens."), "errorcode" => "unexpected_operator");

					if ($token[1] === "||" && count($valstack) && $valstack[count($valstack) - 1] != 0)  $skipops = true;

					// Reduce the value stack by one.
					if (count($valstack))  array_pop($valstack);
				}
			}

			if (count($parenstack))  return array("success" => false, "error" => self::NLBTranslate("Invalid tokens due to unexpected parenthesis count."), "errorcode" => "invalid_tokens");

			if (!self::ReduceConditionalCheckStacks($valstack, $parenstack, $opstack))  return array("success" => false, "error" => self::NLBTranslate("An unexpected/invalid operator was encountered in the tokens."), "errorcode" => "unexpected_operator");

			return array("success" => true, "value" => (count($valstack) && $valstack[0] != 0), "used_data" => $options["used_data"]);
		}

		protected static function NLBTranslate()
		{
			$args = func_get_args();
			if (!count($args))  return "";

			return call_user_func_array((defined("CS_TRANSLATE_FUNC") && function_exists(CS_TRANSLATE_FUNC) ? CS_TRANSLATE_FUNC : "sprintf"), $args);
		}
	}
?>