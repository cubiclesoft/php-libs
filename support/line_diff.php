<?php
	// Line diff calculation class.
	// (C) 2019 CubicleSoft.  All Rights Reserved.

	class LineDiff
	{
		const UNMODIFIED = 0;
		const DELETED = 1;
		const INSERTED = 2;

		public static function Compare($left, $right, $options = array())
		{
			if (is_string($left))  $left = preg_split('/\R/', $left);
			if (is_string($right))  $right = preg_split('/\R/', $right);

			if (!isset($options["ltrim"]))  $options["ltrim"] = false;
			if (!isset($options["rtrim"]))  $options["rtrim"] = false;
			if (!isset($options["ignore_whitespace"]))  $options["ignore_whitespace"] = false;
			if (!isset($options["ignore_case"]))  $options["ignore_case"] = false;

			// Step 1.  Precalculate all anchor counts.
			$leftanchors = array();
			foreach ($left as $line)
			{
				$line = self::TransformLine($line, $options);

				if (!isset($leftanchors[$line]))  $leftanchors[$line] = 0;
				$leftanchors[$line]++;
			}

			$rightanchors = array();
			foreach ($right as $line)
			{
				$line = self::TransformLine($line, $options);

				if (!isset($rightanchors[$line]))  $rightanchors[$line] = 0;
				$rightanchors[$line]++;
			}

			// Step 2.  Use a state engine to plan out and generate the line-by-line diff.
			$diff = array();
			$lx = 0;
			$rx = 0;
			$ly = count($left);
			$ry = count($right);
			$state = "unmodified";
			$nextstate = false;
			while ($lx < $ly || $rx < $ry)
			{
				switch ($state)
				{
					case "unmodified":
					{
						// Unmodified.
						if ($lx >= $ly)
						{
							$state = "add";

							break;
						}

						if ($rx >= $ry)
						{
							$state = "remove";

							break;
						}

						$leftline = self::TransformLine($left[$lx], $options);
						$rightline = self::TransformLine($right[$rx], $options);
//echo $state . " | " . $lx . ", " . $rx . " | " . $leftline . " | " . $rightline . "\n";

						if ($leftline === $rightline)
						{
//echo "UNMODIFIED | " . $right[$rx] . "\n";
							$diff[] = array($right[$rx], self::UNMODIFIED);

							$leftanchors[$leftline]--;
							$rightanchors[$rightline]--;

							if (!$leftanchors[$leftline])  unset($leftanchors[$leftline]);
							if (!$rightanchors[$rightline])  unset($rightanchors[$rightline]);

							$lx++;
							$rx++;
						}
						else if (isset($rightanchors[$leftline]) && !isset($leftanchors[$rightline]))
						{
//echo "INSERTED | " . $right[$rx] . "\n";
							$diff[] = array($right[$rx], self::INSERTED);

							$rightanchors[$rightline]--;

							if (!$rightanchors[$rightline])  unset($rightanchors[$rightline]);

							$rx++;

							$state = "add";
						}
						else
						{
//echo "DELETED | " . $left[$lx] . "\n";
							$diff[] = array($left[$lx], self::DELETED);

							$leftanchors[$leftline]--;

							if (!$leftanchors[$leftline])  unset($leftanchors[$leftline]);

							$lx++;

							$state = "remove";
						}

						break;
					}
					case "remove":
					{
						// Remove.
						if ($lx >= $ly)
						{
							$state = "add";

							break;
						}

						$leftline = self::TransformLine($left[$lx], $options);
//echo $state . " | " . $lx . ", " . $rx . " | " . $leftline . " | " . $rightline . "\n";

						// Be greedy about deletions where the right side is unique and continues on in a bit on the left.
						if (!isset($rightanchors[$leftline]) || ($rightline !== false && $leftline !== $rightline && isset($leftanchors[$rightline]) && $leftanchors[$rightline] == 1))
						{
//echo "DELETED | " . $left[$lx] . "\n";
							$diff[] = array($left[$lx], self::DELETED);

							$leftanchors[$leftline]--;

							if (!$leftanchors[$leftline])  unset($leftanchors[$leftline]);

							$lx++;
						}
						else if ($leftline === $rightline)
						{
//echo "UNMODIFIED | " . $right[$rx] . "\n";
							$diff[] = array($right[$rx], self::UNMODIFIED);

							$leftanchors[$leftline]--;
							$rightanchors[$rightline]--;

							if (!$leftanchors[$leftline])  unset($leftanchors[$leftline]);
							if (!$rightanchors[$rightline])  unset($rightanchors[$rightline]);

							$lx++;
							$rx++;

							$state = "unmodified";
						}
						else
						{
//echo "INSERTED | " . $right[$rx] . "\n";
							$diff[] = array($right[$rx], self::INSERTED);

							$rightanchors[$rightline]--;

							if (!$rightanchors[$rightline])  unset($rightanchors[$rightline]);

							$rx++;

							$state = "add";
						}

						break;
					}
					case "add":
					{
						// Add.
						if ($rx >= $ry)
						{
							$state = "remove";

							break;
						}

						$rightline = self::TransformLine($right[$rx], $options);
//echo $state . " | " . $lx . ", " . $rx . " | " . $leftline . " | " . $rightline . "\n";

						// Be greedy about insertions where the left side is unique and continues on in a bit on the right.
						if (!isset($leftanchors[$rightline]) || ($leftline !== false && $leftline !== $rightline && isset($rightanchors[$leftline]) && $rightanchors[$leftline] == 1))
						{
//echo "INSERTED | " . $right[$rx] . "\n";
							$diff[] = array($right[$rx], self::INSERTED);

							$rightanchors[$rightline]--;

							if (!$rightanchors[$rightline])  unset($rightanchors[$rightline]);

							$rx++;
						}
						else if ($leftline === $rightline)
						{
//echo "UNMODIFIED | " . $right[$rx] . "\n";
							$diff[] = array($right[$rx], self::UNMODIFIED);

							$leftanchors[$leftline]--;
							$rightanchors[$rightline]--;

							if (!$leftanchors[$leftline])  unset($leftanchors[$leftline]);
							if (!$rightanchors[$rightline])  unset($rightanchors[$rightline]);

							$lx++;
							$rx++;

							$state = "unmodified";
						}
						else
						{
//echo "DELETED | " . $left[$lx] . "\n";
							$diff[] = array($left[$lx], self::DELETED);

							$leftanchors[$leftline]--;

							if (!$leftanchors[$leftline])  unset($leftanchors[$leftline]);

							$lx++;

							$state = "remove";
						}

						break;
					}
				}
			}

			// Step 3.  Consolidate diff chunks separated only by empty lines.  Results in slightly larger, but generally more readable diffs.
			// Step 4.  Convert trailing perfect matches to unmodified lines.
			$consolidate = (!isset($options["consolidate"]) || $options["consolidate"]);
			$diff2 = array();
			$x = 0;
			$y = count($diff);
			while ($x < $y)
			{
				if ($diff[$x][1] === self::UNMODIFIED)
				{
					$diff2[] = $diff[$x];

					$x++;
				}
				else
				{
					// Find the end of the current chunk delimited by sequential unmodified lines of whitespace.
					$x2 = $x + 1;

					do
					{
						for (; $x2 < $y && $diff[$x2][1] !== self::UNMODIFIED; $x2++)
						{
						}

						for ($num = 0; $consolidate && $x2 + $num < $y && $diff[$x2 + $num][1] === self::UNMODIFIED; $num++)
						{
							if (trim($diff[$x2 + $num][0]) !== "")
							{
								$num = 0;

								break;
							}
						}

						if (!$num)  break;

						if ($x2 + $num < $y)  $x2 += $num;
						else  break;

					} while ($x2 < $y);

					// Removals.
					$diff3 = array();
					for ($x3 = $x; $x3 < $x2; $x3++)
					{
						if ($diff[$x3][1] !== self::INSERTED)  $diff3[] = array($diff[$x3][0], self::DELETED);
					}

					// Inserts.
					$diff4 = array();
					for ($x3 = $x; $x3 < $x2; $x3++)
					{
						if ($diff[$x3][1] !== self::DELETED)  $diff4[] = array($diff[$x3][0], self::INSERTED);
					}

					// Convert extraneous inserts and deletes to unmodifieds.
					$x3 = count($diff3);
					$x4 = $y2 = count($diff4);
					while ($x3 && $x4 && self::TransformLine($diff3[$x3 - 1][0], $options) === self::TransformLine($diff4[$x4 - 1][0], $options))
					{
						$x3--;
						$x4--;
					}

					for ($z = 0; $z < $x3; $z++)  $diff2[] = $diff3[$z];
					for ($z = 0; $z < $x4; $z++)  $diff2[] = $diff4[$z];
					for (; $z < $y2; $z++)  $diff2[] = array($diff4[$z][0], self::UNMODIFIED);

					$x = $x2;
				}
			}

			return $diff2;
		}

		protected static function TransformLine($line, $options)
		{
			if ($options["ltrim"])  $line = ltrim($line);
			if ($options["rtrim"])  $line = rtrim($line);
			if ($options["ignore_whitespace"])  $line = str_replace(array(" ", "\t", "\r", "\n", "\0"), "", $line);
			if ($options["ignore_case"])  $line = (function_exists("mb_strtolower") ? mb_strtolower($line) : strtolower($line));

			return $line;
		}
	}
?>