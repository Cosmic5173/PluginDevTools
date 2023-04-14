<?php

namespace Cosmic5173\plugindevtools\command;

use Cosmic5173\plugindevtools\Loader;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\plugin\Plugin;
use pocketmine\plugin\PluginOwned;

class RestartCommand extends Command implements PluginOwned {

	public function __construct() {
		parent::__construct("restart", "Restarts the server to apply changes in configuration files.", "/restart", ["r"]);
		$this->setPermission("plugindevtools.command.restart");
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args) {
		if (!$this->testPermission($sender)) return;

		$sender->sendMessage("Restarting server...");
		Loader::getInstance()->restart();
	}

	public function getOwningPlugin() : Plugin {
		return Loader::getInstance();
	}

}