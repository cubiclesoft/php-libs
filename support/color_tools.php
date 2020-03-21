<?php
	// Color space tools.
	// (C) 2020 CubicleSoft.  All Rights Reserved.

	class ColorTools
	{
		protected static $nearestgridcache = array();
		protected static $nearestgridcache2 = false;

		public static function LimitRGB(&$r, &$g, &$b)
		{
			$r = (int)$r;
			if ($r < 0)  $r = 0;
			if ($r > 255)  $r = 255;

			$g = (int)$g;
			if ($g < 0)  $g = 0;
			if ($g > 255)  $g = 255;

			$b = (int)$b;
			if ($b < 0)  $b = 0;
			if ($b > 255)  $b = 255;
		}

		public static function ConvertRGBtoHSB($r, $g, $b)
		{
			self::LimitRGB($r, $g, $b);

			$minrgb = min($r, $g, $b);
			$maxrgb = max($r, $g, $b);
			$diff = $maxrgb - $minrgb;
			$maxrgb /= 255.0;

			$brightness = $maxrgb * 100.0;

			if (!$diff)  return array("h" => 0, "s" => 0, "b" => $brightness);

			$diff /= 255.0;

			$saturation = ($diff / $maxrgb) * 100.0;

			if ($r === $minrgb)  $hue = 3 - ((($g - $b) / 255.0) / $diff);
			else if ($b === $minrgb)  $hue = 1 - ((($r - $g) / 255.0) / $diff);
			else  $hue = 5 - ((($b - $r) / 255.0) / $diff);

			$hue *= 60.0;

			return array("h" => $hue, "s" => $saturation, "b" => $brightness);
		}

		public static function LimitHSB(&$h, &$s, &$b)
		{
			while ($h < 0)  $h += 360;
			while ($h >= 360)  $h -= 360;

			if ($s < 0)  $s = 0;
			if ($s > 100) $s = 100;

			if ($b < 0)  $b = 0;
			if ($b > 100)  $b = 100;
		}

		public static function ConvertHSBToRGB($h, $s, $v)
		{
			self::LimitHSB($h, $s, $v);

			$h /= 60.0;
			$s /= 100.0;
			$v /= 100.0;

			if ($s < 0.00001)
			{
				$c = (int)($v * 255.0);

				return array("r" => $c, "g" => $c, "b" => $c);
			}

			$x = (int)$h;

			$n1 = $v * (1.0 - $s);
			$n2 = $v * (1.0 - ($s * ($h - $x)));
			$n3 = $v * (1.0 - ($s * (1.0 - $h + $x)));

			switch ($x)
			{
				case 0:  $r = $v;  $g = $n3;  $b = $n1;  break;
				case 1:  $r = $n2;  $g = $v;  $b = $n1;  break;
				case 2:  $r = $n1;  $g = $v;  $b = $n3;  break;
				case 3:  $r = $n1;  $g = $n2;  $b = $v;  break;
				case 4:  $r = $n3;  $g = $n1;  $b = $v;  break;
				case 5:  $r = $v;  $g = $n1;  $b = $n2;  break;
			}

			$r = (int)($r * 255.0);
			$g = (int)($g * 255.0);
			$b = (int)($b * 255.0);

			return array("r" => $r, "g" => $g, "b" => $b);
		}

		public static function ConvertRGBToXYZ($r, $g, $b)
		{
			self::LimitRGB($r, $g, $b);

			$r = $r / 255.0;
			$g = $g / 255.0;
			$b = $b / 255.0;

			$r = ($r > 0.04045 ? pow((($r + 0.055) / 1.055), 2.4) : $r / 12.92) * 100.0;
			$g = ($g > 0.04045 ? pow((($g + 0.055) / 1.055), 2.4) : $g / 12.92) * 100.0;
			$b = ($b > 0.04045 ? pow((($b + 0.055) / 1.055), 2.4) : $b / 12.92) * 100.0;

			// Observer = 2 degrees, Illuminant = D65.
			$x = ($r * 0.4124) + ($g * 0.3576) + ($b * 0.1805);
			$y = ($r * 0.2126) + ($g * 0.7152) + ($b * 0.0722);
			$z = ($r * 0.0193) + ($g * 0.1192) + ($b * 0.9505);

			return array("x" => $x, "y" => $y, "z" => $z);
		}

		public static function ConvertXYZToCIELab($x, $y, $z)
		{
			// Observer = 2 degrees, Illuminant = D65.
			$x /= 95.047;
			$y /= 100.0;
			$z /= 108.883;

			$x = ($x > 0.008856 ? pow($x, 1.0 / 3.0) : (7.787 * $x) + (16.0 / 116.0));
			$y = ($y > 0.008856 ? pow($y, 1.0 / 3.0) : (7.787 * $y) + (16.0 / 116.0));
			$z = ($z > 0.008856 ? pow($z, 1.0 / 3.0) : (7.787 * $z) + (16.0 / 116.0));

			$lum = (116 * $y) - 16;
			$a = 500 * ($x - $y);
			$b = 200 * ($y - $z);

			return array("l" => $lum, "a" => $a, "b" => $b);
		}

		public static function ConvertRGBToCIELab($r, $g, $b)
		{
			$xyz = self::ConvertRGBToXYZ($r, $g, $b);

			return self::ConvertXYZToCIELab($xyz["x"], $xyz["y"], $xyz["z"]);
		}

		// Uses Delta E CIE.
		public static function GetDistance($lab1, $lab2)
		{
			$ldiff = $lab2["l"] - $lab1["l"];
			$adiff = $lab2["a"] - $lab1["a"];
			$bdiff = $lab2["b"] - $lab1["b"];

			$ldiff *= $ldiff;
			$adiff *= $adiff;
			$bdiff *= $bdiff;

			return sqrt($ldiff + $adiff + $bdiff);
		}

		// Calculate the maximum allowable saturation given the foreground hue, foreground brightness, and background brightness.
		// When the background is too dark, the foreground text color can be oversaturated.  Oversaturated text colors lead to increased eyestrain.
		public static function GetMaxSaturation($fg_h, $fg_b, $bg_b)
		{
			$bthreshold = 15.0;

			if ($bg_b >= $bthreshold)  return 101.0;

			// At 100 brightness, the maximum saturation for hue 0 (also 120, 180, and 300) and hue 60 (also 240).
			// Hue 60 (yellow) and 240 (blue) are more easily oversaturated than other colors.
			$h0fixedsat = 66;
			$h60fixedsat = 50;

			// Adjust the maximum saturation based on the distance to the threshold.
			$h0fixedsat += (100.0 - $h0fixedsat) * $bg_b / $bthreshold;
			$h60fixedsat += (100.0 - $h60fixedsat) * $bg_b / $bthreshold;

			// Based on the foreground hue at each of the 6 transition points (0, 60, 120, 180, 240, 300), calculate an interpolated radius.
			// y = mx + b
			if ($fg_h >= 180.0)  $fg_h -= 180.0;

			if ($fg_h >= 0 && $fg_h < 60)  $fixedsat = (($h60fixedsat - $h0fixedsat) / 60.0) * $fg_h + $h0fixedsat;
			else if ($fg_h >= 60 && $fg_h < 120)  $fixedsat = (($h0fixedsat - $h60fixedsat) / 60.0) * ($fg_h - 60.0) + $h60fixedsat;
			else if ($fg_h >= 120 && $fg_h < 180)  $fixedsat = $h0fixedsat;

			// If the foreground brightness is below the calculated fixed saturation threshold, then bail out.
			if ($fg_b <= $fixedsat)  return 101.0;

			// Calculate the bottom radius ^ 2 of a half-circle with an origin point at 200% saturation and 200% brightness that intersects precisely at the fixed saturation and 100% brightness point.
			// (100 - 200) ^ 2 = 10000
			$radius2 = 10000.0 + (($fixedsat - 200.0) * ($fixedsat - 200.0));

			// Using the radius ^ 2, calculate the point on the circle that represents the maximum saturation for the input brightness level.
			$result = (int)(200.0 - sqrt($radius2 - (($fg_b - 200.0) * ($fg_b - 200.0))) + 1.0);

			return $result;
		}

		// Calculate the minimum allowable brightness given the background brightness.
		// When the background is too dark, the foreground text can be too dark and blend in.  Insufficient brightness leads to increased eyestrain.
		public static function GetMinBrightness($bg_b)
		{
			$max = 66.0;

			if ($bg_b >= $max)  return 0.0;

			$result = $max - $bg_b;

			return $result;
		}

		// Uses various techniques and functions to create a limited palette of foreground colors suitable for readable text based on the specified RGB palette and background color.
		public static function GetReadableTextForegroundColors($palette, $bg_r, $bg_g, $bg_b)
		{
			$bghsb = self::ConvertRGBtoHSB($bg_r, $bg_g, $bg_b);
			$bglab = self::ConvertRGBToCIELab($bg_r, $bg_g, $bg_b);
			$minbright = self::GetMinBrightness($bghsb["b"]);

			// Minimum distance values for luminosity, perceptual color distance, and the average between the two.
			$minldist = 41.0;
			$mindist = 41.0;
			$minavgdist = 50.0;

			$result = array();
			foreach ($palette as $key => $rgb)
			{
				$rgb = array_values($rgb);
				$r = $rgb[0];
				$g = $rgb[1];
				$b = $rgb[2];

				$hsb = self::ConvertRGBtoHSB($r, $g, $b);

				// Make sure there is a sufficient brightness differential between the background color and the palette color.  Improves overall contrast.
				if ($hsb["b"] >= $minbright && ($bghsb["b"] >= 66.0 || abs($hsb["b"] - $bghsb["b"]) > 10.0))
				{
					// Disallow the color if it is oversaturated for the background.
					// Helps eliminate most text glow/halo effects on very dark backgrounds.
					$maxsat = self::GetMaxSaturation($hsb["h"], $hsb["b"], $bghsb["b"]);

					if ($hsb["s"] <= $maxsat)
					{
						// Calculate the perceptual color distance.  The minimum thresholds improve contrast between any two colors.
						$lab = self::ConvertRGBToCIELab($r, $g, $b);
						$ldist = abs($bglab["l"] - $lab["l"]);
						$dist = self::GetDistance($lab, $bglab);

						if ($ldist >= $minldist && $dist >= $mindist && (($ldist + $dist) / 2.0) >= $minavgdist)
						{
							$result[$key] = $rgb;
						}
					}
				}
			}

			return $result;
		}

		// Finds the nearest color index in a RGB palette that matches the requested color.
		// This function uses HSB instead of CIE-Lab since this function is intended to be called after GetReadableTextForegroundColors() and results in more consistent color accuracy.
		public static function FindNearestPaletteColorIndex($palette, $r, $g, $b)
		{
			$hsb1 = self::ConvertRGBToHSB($r, $g, $b);

			$result = false;
			$founddist = false;
			foreach ($palette as $key => $rgb)
			{
				$rgb = array_values($rgb);
				$r = $rgb[0];
				$g = $rgb[1];
				$b = $rgb[2];

				$hsb2 = self::ConvertRGBToHSB($r, $g, $b);

				$hdiff = min(abs($hsb1["h"] - $hsb2["h"]), abs($hsb1["h"] - $hsb2["h"] + ($hsb1["h"] < $hsb2["h"] ? -360.0 : 360.0))) * 1.2;
				$sdiff = ($hsb1["s"] - $hsb2["s"]) * 1.5;
				$bdiff = $hsb1["b"] - $hsb2["b"];
				if ($hsb1["b"] < $hsb2["b"])  $bdiff *= 2.0;

				$hdiff *= $hdiff;
				$sdiff *= $sdiff;
				$bdiff *= $bdiff;

				$dist = $hdiff + $sdiff + $bdiff;

				if ($result === false || $founddist >= $dist)
				{
					$result = $key;
					$founddist = $dist;
				}
			}

			return $result;
		}

		// Finds the nearest RGB color that will produce readable text based on the desired foreground color and the specified background color.
		public static function FindNearestReadableTextColor($fg_r, $fg_g, $fg_b, $bg_r, $bg_g, $bg_b)
		{
			// First check the input colors themselves to determine compatability (fastest).
			$palette = array(array($fg_r, $fg_g, $fg_b));
			$palette = self::GetReadableTextForegroundColors($palette, $bg_r, $bg_g, $bg_b);
			if (count($palette))  return $palette[0];

			// The colors are not compatible.  Generate a palette containing decreased saturation and increased brightness options at the current hue (slower).
			$hsb = self::ConvertRGBToHSB($fg_r, $fg_g, $fg_b);
			$palette = array();
			for ($b = $hsb["b"]; $b < 100; $b += 5)
			{
				for ($s = 0; $s < $hsb["s"]; $s+= 5)
				{
					$palette[] = array_values(self::ConvertHSBToRGB($hsb["h"], $s, $b));
				}

				$palette[] = array_values(self::ConvertHSBToRGB($hsb["h"], $hsb["s"], $b));
			}

			for ($s = 0; $s < $hsb["s"]; $s+= 5)
			{
				$palette[] = array_values(self::ConvertHSBToRGB($hsb["h"], $s, 100));
			}

			$palette = self::GetReadableTextForegroundColors($palette, $bg_r, $bg_g, $bg_b);
			$x = self::FindNearestPaletteColorIndex($palette, $fg_r, $fg_g, $fg_b);

			if ($x !== false)  return $palette[$x];

			// Generate a coarse palette containing 3,087 nearby colors in the desired hue to attempt to find a compatible color (slower still).
			// 7 hues * 21 * 21 = 3087
			$palette = array();
			for ($x = $hsb["h"] - 15; $x <= $hsb["h"] + 15; $x += 5)
			{
				$h = ($x < 0 ? $x + 360.0 : $x);

				// Cache in RAM to save future pre-calculation steps.
				if (!isset(self::$nearestgridcache[$h]))
				{
					// 21x21 sampling grid.
					$colors = array();
					for ($b = 0; $b <= 100; $b += 5)
					{
						for ($s = 0; $s <= 100; $s += 5)
						{
							$colors[] = array_values(self::ConvertHSBToRGB($h, $s, $b));
						}
					}

					self::$nearestgridcache[$h] = $colors;
				}

				foreach (self::$nearestgridcache[$h] as $c)  $palette[] = $c;
			}

			$palette = self::GetReadableTextForegroundColors($palette, $bg_r, $bg_g, $bg_b);
			$x = self::FindNearestPaletteColorIndex($palette, $fg_r, $fg_g, $fg_b);

			if ($x !== false)  return $palette[$x];

			// Failed to find any matching colors.  Generate another, coarser global palette of 4,356 colors (slowest).
			// 36 * 11 * 11 = 4356.
			if (self::$nearestgridcache2 === false)
			{
				self::$nearestgridcache2 = array();

				for ($h = 0; $h < 360; $h += 10)
				{
					for ($b = 0; $b <= 100; $b += 10)
					{
						for ($s = 0; $s <= 100; $s += 10)
						{
							self::$nearestgridcache2[] = array_values(self::ConvertHSBToRGB($h, $s, $b));
						}
					}
				}
			}

			$palette = self::GetReadableTextForegroundColors(self::$nearestgridcache2, $bg_r, $bg_g, $bg_b);
			$x = self::FindNearestPaletteColorIndex($palette, $fg_r, $fg_g, $fg_b);

			return $palette[$x];
		}

		// Returns the RGB value as a hex string.
		public static function ConvertToHex($r, $g, $b, $prefix = "#")
		{
			self::LimitRGB($r, $g, $b);

			return $prefix . sprintf("%02X%02X%02X", $r, $g, $b);
		}

		// Returns the hex string RGB value as an array.
		public static function ConvertFromHex($str)
		{
			$str = preg_replace('/[^0-9a-f]/', "", strtolower($str));
			if (strlen($str) == 3)  $str = $str[0] . $str[0] . $str[1] . $str[1] . $str[2] . $str[2];
			while (strlen($str) < 6)  $str .= "0";

			return array("r" => hexdec(substr($str, 0, 2)), "g" => hexdec(substr($str, 2, 2)), "b" => hexdec(substr($str, 4, 2)));
		}
	}
?>