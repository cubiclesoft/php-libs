<?php
	// System profile information.
	// (C) 2023 CubicleSoft.  All Rights Reserved.

	class SystemProfile
	{
		public static function GetHostname()
		{
			$os = php_uname("s");
			$windows = (strtoupper(substr($os, 0, 3)) == "WIN");

			$result = false;

			if ($windows)
			{
				$cmd = self::FindExecutable("hostname.exe");
				if ($cmd !== false)  $result = trim(self::RunCommand(escapeshellarg($cmd)));

				if ($result === false)  $result = getenv("USERDOMAIN");
			}
			else
			{
				if (file_exists("/etc/hostname"))  $result = trim(file_get_contents("/etc/hostname"));

				if ($result === false)
				{
					$cmd = self::FindExecutable("hostname", "/bin");
					if ($cmd !== false)  $result = trim(self::RunCommand(escapeshellarg($cmd)));
				}
			}

			return $result;
		}

		public static function GetMachineID()
		{
			$os = php_uname("s");
			$windows = (strtoupper(substr($os, 0, 3)) == "WIN");

			$result = false;

			if ($windows)
			{
				$cmd = self::FindExecutable("reg.exe");
				if ($cmd !== false)
				{
					$result = trim(self::RunCommand(escapeshellarg($cmd) . " query HKLM\SOFTWARE\Microsoft\Cryptography /v MachineGuid /reg:64"));
					$pos = strrpos($result, " ");
					if ($pos !== false)  $result = substr($result, $pos + 1);
					else
					{
						$result = trim(self::RunCommand(escapeshellarg($cmd) . " query HKLM\SOFTWARE\Microsoft\Cryptography /v MachineGuid /reg:32"));
						$pos = strrpos($result, " ");
						if ($pos !== false)  $result = substr($result, $pos + 1);
					}
				}
			}
			else
			{
				if (file_exists("/etc/machine-id"))  $result = trim(file_get_contents("/etc/machine-id"));
				if ($result === false && file_exists("/etc/hostid"))  $result = trim(file_get_contents("/etc/hostid"));

				if ($result === false)
				{
					$ioreg = self::GetIORegPlatformDeviceOSX();

					if ($ioreg !== false && isset($ioreg["IOPlatformUUID"]))  $result = $ioreg["IOPlatformUUID"];
				}
			}

			return $result;
		}

		public static function GetMotherboardInfo()
		{
			$os = php_uname("s");
			$windows = (strtoupper(substr($os, 0, 3)) == "WIN");

			$result = false;

			if ($windows)
			{
				$cmd = self::FindExecutable("wmic.exe");
				if ($cmd !== false)
				{
					$result = trim(self::RunCommand(escapeshellarg($cmd) . " /locale:ms_409 baseboard get Manufacturer, Product, SerialNumber, Version"));
					$result = self::ExtractWMICResults($result, "Manufacturer");
					if ($result !== false)  $result = array("type" => "wmic", "data" => $result[0]);
				}
			}
			else
			{
				$cmd = self::FindExecutable("dmesg", "/bin");
				if ($cmd !== false)
				{
					$lines = explode("\n", trim(self::RunCommand(escapeshellarg($cmd))));

					$result = array();

					foreach ($lines as $line)
					{
						$pos = stripos($line, "DMI:");
						if ($pos !== false)  $result[] = trim(substr($line, $pos + 4));
					}

					if (count($result))  $result = array("type" => "dmesg", "data" => $result);
					else  $result = false;
				}

				if ($result === false)
				{
					$ioreg = self::GetIORegPlatformDeviceOSX();

					if ($ioreg !== false)
					{
						$keys = array("manufacturer", "product-name", "model", "board-id", "IOPlatformUUID", "IOPlatformSerialNumber", "serial-number");

						$fingerprint = array();

						foreach ($keys as $key)
						{
							if (isset($ioreg[$key]))  $fingerprint[$key] = $ioreg[$key];
						}

						$result = array("type" => "ioreg_osx", "data" => $ioreg, "fingerprint" => $fingerprint);
					}
				}
			}

			return $result;
		}

		public static function GetCPUInfo()
		{
			$os = php_uname("s");
			$windows = (strtoupper(substr($os, 0, 3)) == "WIN");

			$result = false;

			if ($windows)
			{
				$cmd = self::FindExecutable("wmic.exe");
				if ($cmd !== false)
				{
					$result = trim(self::RunCommand(escapeshellarg($cmd) . " /locale:ms_409 cpu get Caption, CpuStatus, Manufacturer, Name, NumberOfCores, NumberOfLogicalProcessors, ProcessorId"));
					$result = self::ExtractWMICResults($result, "Caption");
					if ($result !== false)  $result = array("type" => "wmic", "data" => $result);
				}
			}
			else
			{
				$cmd = self::FindExecutable("lscpu", "/usr/bin");
				if ($cmd !== false)
				{
					$lines = explode("\n", trim(self::RunCommand(escapeshellarg($cmd))));

					$result = array();

					$keys = array("architecture" => "architecture", "model name" => "model", "vendor id" => "vendor", "thread(s) per core" => "threads_per_core", "core(s) per socket" => "cores_per_socket", "socket(s)" => "sockets");

					foreach ($lines as $line)
					{
						$pos = strpos($line, ": ");
						if ($pos !== false)
						{
							$key = strtolower(trim(substr($line, 0, $pos)));
							if (isset($keys[$key]))  $result[$keys[$key]] = trim(substr($line, $pos + 1));
						}
					}

					if (!count($result))  $result = false;
					else
					{
						if (isset($result["cores_per_socket"]) && isset($result["sockets"]) && !isset($result["cores"]))  $result["cores"] = (string)((int)$result["cores_per_socket"] * (int)$result["sockets"]);
						if (isset($result["threads_per_core"]) && isset($result["cores"]) && !isset($result["threads"]))  $result["threads"] = (string)((int)$result["threads_per_core"] * (int)$result["cores"]);

						$result = array("type" => "lscpu", "data" => $result);
					}
				}

				if ($result === false)
				{
					$cmd = self::FindExecutable("sysctl", "/sbin");
					if ($cmd !== false)
					{
						$lines = explode("\n", trim(self::RunCommand(escapeshellarg($cmd) . " -a")));

						$keys = array("hw.machine" => "architecture", "hw.model" => "model", "hw.logicalcpu" => "threads", "hw.physicalcpu" => "cores", "kern.smp.threads_per_core" => "threads_per_core", "kern.smp.cores" => "cores");

						$result = array();

						foreach ($lines as $line)
						{
							$line = trim($line);

							$pos = strpos($line, ":");
							if ($pos !== false)
							{
								$key = substr($line, 0, $pos);
								if (isset($keys[$key]))  $result[$keys[$key]] = trim(substr($line, $pos + 1));
							}
						}

						if (!count($result))  $result = false;
						else
						{
							if (isset($result["threads_per_core"]) && isset($result["cores"]) && !isset($result["threads"]))  $result["threads"] = (string)((int)$result["threads_per_core"] * (int)$result["cores"]);

							$result = array("type" => "sysctl", "data" => $result);
						}
					}
				}
			}

			return $result;
		}

		public static function GetRAMInfo()
		{
			$os = php_uname("s");
			$windows = (strtoupper(substr($os, 0, 3)) == "WIN");

			$result = false;

			if ($windows)
			{
				$cmd = self::FindExecutable("wmic.exe");
				if ($cmd !== false)
				{
					$result = trim(self::RunCommand(escapeshellarg($cmd) . " /locale:ms_409 memorychip get Capacity, DeviceLocator, PartNumber, SerialNumber"));
					$result = self::ExtractWMICResults($result, "Capacity");
					if ($result !== false)  $result = array("type" => "wmic", "data" => $result);
				}
			}
			else
			{
				if (file_exists("/proc/meminfo"))
				{
					$lines = explode("\n", trim(file_get_contents("/proc/meminfo")));

					foreach ($lines as $line)
					{
						if (substr($line, 0, 9) === "MemTotal:")
						{
							$result = 1024 * (int)trim(substr($line, 9));

							if ($result !== false)  $result = array("type" => "proc_meminfo", "data" => $result);

							break;
						}
					}
				}

				if ($result === false)
				{
					$cmd = self::FindExecutable("vmstat", "/usr/bin");
					if ($cmd !== false)
					{
						$lines = explode("\n", trim(self::RunCommand(escapeshellarg($cmd) . " -s")));

						foreach ($lines as $line)
						{
							$line = trim($line);

							if (stripos($line, " total memory") !== false)
							{
								$result = 1024 * (int)$line;

								if ($result !== false)  $result = array("type" => "vmstat", "data" => $result);

								break;
							}
						}
					}
				}

				if ($result === false)
				{
					$cmd = self::FindExecutable("sysctl", "/sbin");
					if ($cmd !== false)
					{
						$mem = trim(self::RunCommand(escapeshellarg($cmd) . " hw.memsize"));

						if (substr($mem, 0, 11) === "hw.memsize:")  $result = (int)trim(substr($mem, 11));

						if ($result !== false)  $result = array("type" => "sysctl", "data" => $result);
					}
				}

				if ($result === false)
				{
					$cmd = self::FindExecutable("sysctl", "/sbin");
					if ($cmd !== false)
					{
						$mem = trim(self::RunCommand(escapeshellarg($cmd) . " hw.physmem"));

						if (substr($mem, 0, 11) === "hw.physmem:")  $result = (int)trim(substr($mem, 11));

						if ($result !== false)  $result = array("type" => "sysctl", "data" => $result);
					}
				}
			}

			return $result;
		}

		public static function GetGPUInfo()
		{
			$os = php_uname("s");
			$windows = (strtoupper(substr($os, 0, 3)) == "WIN");

			$result = false;

			if ($windows)
			{
				$cmd = self::FindExecutable("wmic.exe");
				if ($cmd !== false)
				{
					$result = trim(self::RunCommand(escapeshellarg($cmd) . " /locale:ms_409 path Win32_VideoController get Description, Name, PNPDeviceID"));
					$result = self::ExtractWMICResults($result, "Name");
					if ($result !== false)  $result = array("type" => "wmic", "data" => $result);
				}
			}
			else
			{
				$cmd = self::FindExecutable("lspci", "/usr/bin");
				if ($cmd !== false)
				{
					$lines = explode("\n", trim(self::RunCommand(escapeshellarg($cmd))));

					$result = array();

					foreach ($lines as $line)
					{
						$line = trim($line);

						if (stripos($line, "VGA") !== false)
						{
							$pos = strpos($line, " ");
							if ($pos !== false)  $line = trim(substr($line, $pos + 1));

							$result[] = $line;
						}
					}

					if (count($result))  $result = array("type" => "lspci", "data" => $result);
					else  $result = false;
				}

				if ($result === false)
				{
					$conf = self::GetPCIConfBSD();

					if ($conf !== false)
					{
						$result = array();

						foreach ($conf as $cinfo)
						{
							if (isset($cinfo["subclass"]) && strtolower($cinfo["subclass"]) === "vga")  $result[] = $cinfo["vendor"] . " " . $cinfo["device"];
						}

						if (count($result))  $result = array("type" => "pciconf_bsd", "data" => $result);
						else  $result = false;
					}
				}

				if ($result === false)
				{
					$cmd = self::FindExecutable("system_profiler", "/usr/sbin");
					if ($cmd !== false)
					{
						$lines = explode("\n", trim(self::RunCommand(escapeshellarg($cmd) . " SPDisplaysDataType")));

						$result = array();

						foreach ($lines as $line)
						{
							if (substr($line, 0, 4) === "    " && $line[4] !== " ")
							{
								$result[] = trim(rtrim($line, ":"));
							}
						}

						if (count($result))  $result = array("type" => "system_profiler_osx", "data" => $result);
						else  $result = false;
					}
				}
			}

			return $result;
		}

		public static function GetNICInfo()
		{
			$os = php_uname("s");
			$windows = (strtoupper(substr($os, 0, 3)) == "WIN");

			$result = false;

			if ($windows)
			{
				$cmd = self::FindExecutable("wmic.exe");
				if ($cmd !== false)
				{
					$result = trim(self::RunCommand(escapeshellarg($cmd) . " /locale:ms_409 nic get AdapterType, AdapterTypeId, GUID, MACAddress, Name, NetConnectionStatus, PNPDeviceID, ServiceName"));
					$result = self::ExtractWMICResults($result, "Name");
					if ($result !== false)
					{
						$fingerprint = array();

						foreach ($result as $nic)
						{
							if (substr($nic["PNPDeviceID"], 0, 4) === "PCI\\")  $fingerprint[] = $nic;
						}

						$result = array("type" => "wmic", "data" => $result, "fingerprint" => $fingerprint);
					}
				}
			}
			else
			{
				$ctype = false;
				$controllers = array();

				$cmd = self::FindExecutable("lspci", "/usr/bin");
				if ($cmd !== false)
				{
					$lines = explode("\n", trim(self::RunCommand(escapeshellarg($cmd))));

					foreach ($lines as $line)
					{
						$line = trim($line);

						if (stripos($line, "Ethernet") !== false || stripos($line, "Network") !== false)
						{
							$pos = strpos($line, " ");
							if ($pos !== false)  $line = trim(substr($line, $pos + 1));

							$controllers[] = $line;
						}
					}

					if (count($controllers))  $ctype = "lspci";
				}

				if (!count($controllers))
				{
					$conf = self::GetPCIConfBSD();

					if ($conf !== false)
					{
						foreach ($conf as $cinfo)
						{
							if (isset($cinfo["class"]) && strtolower($cinfo["class"]) === "network")  $controllers[] = $cinfo["vendor"] . " " . $cinfo["device"];
						}

						if (count($controllers))  $ctype = "pciconf_bsd";
					}
				}

				$ltype = false;
				$links = array();
				$macaddrs = array();

				$cmd = self::FindExecutable("ip", "/bin");
				if ($cmd !== false)
				{
					$lines = explode("\n", trim(self::RunCommand(escapeshellarg($cmd) . " address show")));

					$name = false;
					$link = array();

					foreach ($lines as $line)
					{
						$line = rtrim($line);

						if ($line !== "" && $line[0] !== " ")
						{
							if ($name !== false)
							{
								$links[$name] = $link;

								$name = false;
								$link = array();
							}

							$pos = strpos($line, ":");
							if ($pos !== false)
							{
								$pos2 = strpos($line, ":", $pos + 1);
								if ($pos2 !== false)
								{
									$name = trim(substr($line, $pos + 1, $pos2 - $pos - 1));
									$line = trim(substr($line, $pos2 + 1));

									$link = array($line);
								}
							}
						}
						else if ($name !== false)
						{
							$link[] = trim($line);
						}
					}

					if ($name !== false)  $links[$name] = $link;

					if (count($links))
					{
						$ltype = "ip";

						foreach ($links as $name => $link)
						{
							foreach ($link as $line)
							{
								$pieces = explode(" ", preg_replace('/\s+/', " ", $line));

								if ($pieces[0] === "link/ether")
								{
									$macaddrs[$name] = $pieces[1];

									break;
								}
							}
						}
					}
				}

				if (!count($links))
				{
					$cmd = self::FindExecutable("ifconfig", "/sbin");
					if ($cmd !== false)
					{
						$lines = explode("\n", trim(self::RunCommand(escapeshellarg($cmd) . " -a")));

						$name = false;
						$link = array();

						foreach ($lines as $line)
						{
							$line = rtrim($line);

							if ($line !== "" && $line[0] !== "\t")
							{
								if ($name !== false)
								{
									$links[$name] = $link;

									$name = false;
									$link = array();
								}

								$pos = strpos($line, ":");
								if ($pos !== false)
								{
									$name = trim(substr($line, 0, $pos));
									$line = trim(substr($line, $pos + 1));

									$link = array($line);
								}
							}
							else if ($name !== false)
							{
								$link[] = trim($line);
							}
						}

						if ($name !== false)  $links[$name] = $link;

						if (count($links))
						{
							$ltype = "ifconfig";

							foreach ($links as $name => $link)
							{
								foreach ($link as $line)
								{
									$pieces = explode(" ", preg_replace('/\s+/', " ", $line));

									if ($pieces[0] === "ether")
									{
										$macaddrs[$name] = $pieces[1];

										break;
									}
								}
							}
						}
					}
				}

				if ($ctype !== false || $ltype !== false)  $result = array("type" => "controllers_links", "data" => array("ctype" => $ctype, "cdata" => $controllers, "ltype" => $ltype, "ldata" => $links), "fingerprint" => array($controllers, $macaddrs));
			}

			return $result;
		}

		public static function GetDiskInfo()
		{
			$os = php_uname("s");
			$windows = (strtoupper(substr($os, 0, 3)) == "WIN");

			$result = false;

			if ($windows)
			{
				$cmd = self::FindExecutable("wmic.exe");
				if ($cmd !== false)
				{
					$result = trim(self::RunCommand(escapeshellarg($cmd) . " /locale:ms_409 diskdrive get Caption, InterfaceType, MediaLoaded, MediaType, Model, Name, SerialNumber, Signature, Size"));
					$result = self::ExtractWMICResults($result, "Name");
					if ($result !== false)
					{
						$fingerprint = array();

						foreach ($result as $disk)
						{
							if ($disk["InterfaceType"] !== "USB" && $disk["InterfaceType"] !== "1394")  $fingerprint[] = $disk;
						}

						$result = array("type" => "wmic", "data" => $result, "fingerprint" => $fingerprint);
					}
				}
			}
			else
			{
				$cmd = self::FindExecutable("lsblk", "/bin");
				if ($cmd !== false)
				{
					$result = @json_decode(trim(self::RunCommand(escapeshellarg($cmd) . " --json -O")), true);

					if (!is_array($result) || !isset($result["blockdevices"]))  $result = false;
					else
					{
						$fingerprint = array();

						$keys = array("model", "vendor", "label", "size", "fstype", "tran", "ptuuid", "uuid", "partuuid", "serial", "wwn");

						foreach ($result["blockdevices"] as $dev)
						{
							if (isset($dev["tran"]) && $dev["tran"] !== "usb" && (!isset($dev["hotplug"]) || !$dev["hotplug"]))
							{
								$path = (isset($dev["path"]) ? $dev["path"] : $dev["name"]);

								$fingerprint[$path] = array();

								foreach ($keys as $key)
								{
									if (isset($dev[$key]))  $fingerprint[$path][$key] = $dev[$key];
								}

								if (isset($dev["children"]))
								{
									foreach ($dev["children"] as $dev2)
									{
										$path = (isset($dev2["path"]) ? $dev2["path"] : $dev2["name"]);

										$fingerprint[$path] = array();

										foreach ($keys as $key)
										{
											if (isset($dev2[$key]))  $fingerprint[$path][$key] = $dev2[$key];
										}
									}
								}
							}
						}

						$result = array("type" => "lsblk_json", "data" => $result["blockdevices"], "fingerprint" => $fingerprint);
					}
				}

				if ($result === false)
				{
					$cmd = self::FindExecutable("sysctl", "/sbin");
					if ($cmd !== false)
					{
						$disks = trim(self::RunCommand(escapeshellarg($cmd) . " kern.disks"));

						if (substr($disks, 0, 11) === "kern.disks:")
						{
							$disks = explode(" ", trim(substr($disks, 11)));

							$result = array();

							foreach ($disks as $disk)
							{
								$result[$disk] = array();
							}

							$lines = explode("\n", trim(self::RunCommand(escapeshellarg($cmd) . " -a")));

							foreach ($lines as $line)
							{
								$pos = strpos($line, ":");
								if ($pos !== false)
								{
									$disk = substr($line, 0, $pos);

									if (isset($result[$disk]))
									{
										$result[$disk][] = trim(substr($line, $pos + 1));
									}
								}
							}

							if (count($result))  $result = array("type" => "sysctl", "data" => $result);
							else  $result = false;
						}
					}
				}

				if ($result === false)
				{
					$cmd = self::FindExecutable("diskutil", "/usr/sbin");
					if ($cmd !== false)
					{
						$lines = explode("\n", trim(self::RunCommand(escapeshellarg($cmd) . " list internal")));

						$disks = array();

						foreach ($lines as $line)
						{
							if ($line !== "" && $line[0] === "/")
							{
								$pos = strpos($line, " ");

								$disks[] = substr($line, 0, $pos);
							}
						}

						$result = array();

						foreach ($disks as $disk)
						{
							$lines = explode("\n", trim(self::RunCommand(escapeshellarg($cmd) . " info " . escapeshellarg($disk))));

							$result[$disk] = array();

							foreach ($lines as $line)
							{
								$pos = strpos($line, ":");
								if ($pos !== false)
								{
									$result[$disk][trim(substr($line, 0, $pos))] = trim(substr($line, $pos + 1));
								}
							}
						}

						if (count($result))  $result = array("type" => "diskutil", "data" => $result);
						else  $result = false;
					}
				}
			}

			return $result;
		}

		public static function GetOSInfo()
		{
			$os = php_uname("s");
			$windows = (strtoupper(substr($os, 0, 3)) == "WIN");

			$result = false;

			if ($windows)
			{
				$cmd = self::FindExecutable("wmic.exe");
				if ($cmd !== false)
				{
					$result = trim(self::RunCommand(escapeshellarg($cmd) . " /locale:ms_409 os get BootDevice, Caption, Manufacturer, Name, OSArchitecture, SerialNumber, SystemDevice, SystemDirectory, SystemDrive, Version, WindowsDirectory"));
					$result = self::ExtractWMICResults($result, "Name");
					if ($result !== false)  $result = array("type" => "wmic", "data" => $result[0]);
				}
			}
			else
			{
				if (file_exists("/etc/os-release"))
				{
					$lines = explode("\n", trim(file_get_contents("/etc/os-release")));

					$result = array();

					foreach ($lines as $line)
					{
						$line = trim($line);

						$pos = strpos($line, "=");
						if ($pos !== false)
						{
							$key = trim(substr($line, 0, $pos));
							$val = trim(substr($line, $pos + 1));

							if ($val[0] === "\"" && $val[strlen($val) - 1] === "\"")  $val = substr($val, 1, -1);

							$result[$key] = $val;
						}
					}

					if (count($result))  $result = array("type" => "nix_os_release", "data" => $result);
					else  $result = false;
				}

				if ($result === false && file_exists("/System/Library/CoreServices/SystemVersion.plist"))
				{
					$data = trim(file_get_contents("/System/Library/CoreServices/SystemVersion.plist"));

					$result = array();

					$pos = 0;
					while (($pos = stripos($data, "<key>", $pos)) !== false && ($pos2 = stripos($data, "</key>", $pos + 5)) !== false && ($pos3 = stripos($data, "<string>", $pos2 + 6)) !== false && ($pos4 = stripos($data, "</string>", $pos3 + 8)) !== false)
					{
						$key = substr($data, $pos + 5, $pos2 - $pos - 5);
						$val = substr($data, $pos3 + 8, $pos4 - $pos3 - 8);

						$result[$key] = $val;

						$pos = $pos4 + 9;
					}

					$cmd = self::FindExecutable("uname", "/usr/bin");
					if ($cmd !== false)
					{
						$result["Kernel"] = trim(self::RunCommand(escapeshellarg($cmd) . " -mrs"));
					}

					if (count($result))  $result = array("type" => "osx_sysver", "data" => $result);
					else  $result = false;
				}
			}

			return $result;
		}

		public static function GetProfile()
		{
			$ts = microtime(true);

			$result = array(
				"hostname" => SystemProfile::GetHostname(),
				"machine_id" => SystemProfile::GetMachineID(),
				"motherboard" => SystemProfile::GetMotherboardInfo(),
				"cpu" => SystemProfile::GetCPUInfo(),
				"ram" => SystemProfile::GetRAMInfo(),
				"gpu" => SystemProfile::GetGPUInfo(),
				"nic" => SystemProfile::GetNICInfo(),
				"disk" => SystemProfile::GetDiskInfo(),
				"os" => SystemProfile::GetOSInfo(),
			);

			$fingerprint = array();

			foreach ($result as $key => $info)
			{
				$val = $key . ":";

				if (!is_array($info))  $val .= json_encode($info, JSON_UNESCAPED_SLASHES);
				else  $val .= json_encode((isset($info["fingerprint"]) ? $info["fingerprint"] : $info["data"]), JSON_UNESCAPED_SLASHES);

				$fingerprint[] = $val;
			}

			$fingerprint = implode("|", $fingerprint);

			$result["fingerprint"] = hash("sha256", $fingerprint);

			$result["secs"] = microtime(true) - $ts;

			return $result;
		}

		protected static function ExtractWMICResults($data, $expectedheader)
		{
			$lines = explode("\n", $data);

			$line = trim(array_shift($lines));

			preg_match_all('/\w+/', $line, $matches, PREG_OFFSET_CAPTURE);

			$found = false;
			foreach ($matches[0] as $match)
			{
				if ($expectedheader === $match[0])  $found = true;
			}

			if (!$found)  return false;

			$y = count($matches[0]);

			$result = array();

			foreach ($lines as $line)
			{
				$line = rtrim($line);

				if ($line !== "")
				{
					$entry = array();

					for ($x = 0; $x < $y; $x++)
					{
						$entry[$matches[0][$x][0]] = trim($x + 1 < $y ? substr($line, $matches[0][$x][1], $matches[0][$x + 1][1] - $matches[0][$x][1]) : substr($line, $matches[0][$x][1]));
					}

					$result[] = $entry;
				}
			}

			return $result;
		}

		protected static function GetPCIConfBSD()
		{
			$result = false;

			$cmd = self::FindExecutable("pciconf", "/usr/sbin");
			if ($cmd !== false)
			{
				$lines = explode("\n", trim(self::RunCommand(escapeshellarg($cmd) . " -lv")));

				$devices = array();

				$name = false;
				$device = array();

				foreach ($lines as $line)
				{
					$line = rtrim($line);

					if ($line !== "" && $line[0] !== " ")
					{
						if ($name !== false)
						{
							$devices[$name] = $device;

							$name = false;
							$device = array();
						}

						$pos = strpos($line, ":\t");
						if ($pos !== false)
						{
							$name = trim(substr($line, 0, $pos));
							$device = array();
						}
					}
					else if ($name !== false)
					{
						$pos = strpos($line, "=");
						if ($pos !== false)
						{
							$key = trim(substr($line, 0, $pos));
							$val = trim(substr($line, $pos + 1));
							if ($val[0] === "'" && $val[strlen($val) - 1] === "'")  $val = substr($val, 1, -1);

							$device[$key] = $val;
						}
					}
				}

				if ($name !== false)  $devices[$name] = $device;

				if (count($devices))  $result = $devices;
			}

			return $result;
		}

		protected static function GetIORegPlatformDeviceOSX()
		{
			$result = false;

			$cmd = self::FindExecutable("ioreg", "/usr/sbin");
			if ($cmd !== false)
			{
				$lines = explode("\n", trim(self::RunCommand(escapeshellarg($cmd) . " -rd1 -c IOPlatformExpertDevice")));

				$result = array();

				foreach ($lines as $line)
				{
					$pos = strpos($line, "=");
					if ($pos !== false)
					{
						$key = trim(substr($line, 0, $pos));
						$val = trim(substr($line, $pos + 1));

						if ($key[0] === "\"" && $key[strlen($key) - 1] === "\"")
						{
							$key = substr($key, 1, -1);

							if ($val[0] === "(" && $val[strlen($val) - 1] === ")")  $val = substr($val, 1, -1);
							if ($val[0] === "<" && $val[strlen($val) - 1] === ">")  $val = substr($val, 1, -1);
							if ($val[0] === "\"" && $val[strlen($val) - 1] === "\"")  $val = substr($val, 1, -1);

							$result[$key] = $val;
						}
					}
				}
			}

			return $result;
		}

		protected static function FindExecutable($file, $path = false)
		{
			if ($path !== false && file_exists($path . "/" . $file))  return str_replace(array("\\", "/"), DIRECTORY_SEPARATOR, $path . "/" . $file);

			$paths = getenv("PATH");
			if ($paths === false)  return false;

			$paths = explode(PATH_SEPARATOR, $paths);
			foreach ($paths as $path)
			{
				$path = trim($path);
				if ($path !== "" && file_exists($path . "/" . $file))  return str_replace(array("\\", "/"), DIRECTORY_SEPARATOR, $path . "/" . $file);
			}

			return false;
		}

		protected static function RunCommand($cmd)
		{
			$os = php_uname("s");
			$windows = (strtoupper(substr($os, 0, 3)) == "WIN");

			// Avoid displaying a console flash on Windows via ProcessHelper.
			if ($windows && class_exists("ProcessHelper", false))
			{
				$options = array(
					"stdin" => false,
					"stdout" => true,
					"stderr" => false,
					"tcpstdout" => false
				);

				$result = ProcessHelper::StartProcess($cmd, $options);
				if ($result["success"])
				{
					$result2 = ProcessHelper::Wait($result["proc"], $result["pipes"]);

					proc_close($result["proc"]);

					if ($result2["success"])  return $result2["stdout"];
				}
			}

			$descriptors = array(
				0 => array("file", ($windows ? "NUL" : "/dev/null"), "r"),
				1 => array("pipe", "w"),
				2 => array("file", ($windows ? "NUL" : "/dev/null"), "w")
			);

			$proc = @proc_open($cmd, $descriptors, $pipes, NULL, NULL, array("suppress_errors" => true, "bypass_shell" => true));

			if (!is_resource($proc) || !isset($pipes[1]))  return false;

			if (isset($pipes[0]))  @fclose($pipes[0]);
			if (isset($pipes[2]))  @fclose($pipes[2]);

			$fp = $pipes[1];

			$result = "";

			while (!feof($fp))
			{
				$data = @fread($fp, 65536);
				if ($data !== false)  $result .= $data;
			}

			fclose($fp);

			@proc_close($proc);

			return $result;
		}
	}
?>