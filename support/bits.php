<?php
	// CubicleSoft PHP bit manipulation functions.
	// (C) 2018 CubicleSoft.  All Rights Reserved.

	// Treats a string as a bit stream for bit manipulation in PHP.
	class StringBitStream
	{
		private $data, $nextpos, $currchar, $bitsleft;

		public function __construct($data = "")
		{
			$this->Init($data);
		}

		public function Init($data = "")
		{
			$this->data = $data;
			$this->nextpos = 0;
			$this->bitsleft = 0;
		}

		public function ReadBits($numbits, $toint = true, $intsigned = true, $intdirforward = true)
		{
			$result = array();
			$x = 0;
			while ($x < $numbits)
			{
				// If there are no bits left, refresh the data.
				if (!$this->bitsleft)
				{
					if ($this->nextpos >= strlen($this->data))  return false;

					$this->currchar = ord(substr($this->data, $this->nextpos, 1));
					$this->nextpos++;
					$this->bitsleft = 8;
				}

				// Grab as many bits as possible.
				$diff = $numbits - $x;
				if ($diff > $this->bitsleft)  $diff = $this->bitsleft;

				// Process the data.
				$this->bitsleft -= $diff;
				$result[] = array($diff, ($this->currchar >> $this->bitsleft) & ((1 << $diff) - 1));

				$x += $diff;
			}

			if ($toint)  $result = $this->ConvertBitsToInt($result, $intsigned, $intdirforward);

			return $result;
		}

		public function ConvertBitsToInt($data, $signed = true, $dirforward = true)
		{
			$result = 0;

			if (!$dirforward)  $data = array_reverse($data);

			$numbits = 0;
			foreach ($data as $item)
			{
				$numbits += $item[0];
				$result = ($result << $item[0]) | $item[1];
			}

			if ($signed && ($result & (1 << ($numbits - 1))))  $result = $result | (-1 << $numbits);

			return $result;
		}

		public function GetBytePos()
		{
			return $this->nextpos - 1;
		}

		public function AlignBytes($base, $size)
		{
			$num = ($this->nextpos - $base) % $size;
			if ($num)  $this->nextpos += $size - $num;
			$this->bitsleft = 0;
		}
	}
?>