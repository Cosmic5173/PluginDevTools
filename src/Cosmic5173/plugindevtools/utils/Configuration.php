<?php

namespace Cosmic5173\plugindevtools\utils;

use Cosmic5173\plugindevtools\Loader;
use pocketmine\utils\Config;

final class Configuration {

	private string $updateDir;
	private string $startFile;
	private bool $doTransfer;
	private string $transferAddress;
	private string $address;
	private int $port;

	public function __construct(Config $config) {
		$this->updateDir = $config->get("update-dir");
		$this->startFile = $config->get("start-file");
		$this->doTransfer = $config->get("do-transfer");
		$this->transferAddress = $config->get("transfer-server");

		$this->updateDir = Loader::getInstance()->getDataFolder() . $this->updateDir;

		$split = explode(":", $this->transferAddress);
		if (count($split) != 2) {
			$this->doTransfer = false;
			Loader::getInstance()->getLogger()->warning("Invalid transfer server address. Transfer disabled.");
		} else {
			$this->address = $split[0];
			$this->port = intval($split[1]);
		}
	}

	public function getUpdateDir() : string {
		return $this->updateDir;
	}

	public function getStartFile() : string {
		return $this->startFile;
	}

	public function doTransfer() : bool {
		return $this->doTransfer;
	}

	public function getTransferAddress() : string {
		return $this->transferAddress;
	}

	public function getAddress() : string {
		return $this->address;
	}

	public function getPort() : int {
		return $this->port;
	}
}