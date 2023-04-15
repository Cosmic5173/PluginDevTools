<?php

namespace Cosmic5173\plugindevtools;

use Cosmic5173\plugindevtools\command\RestartCommand;
use Cosmic5173\plugindevtools\utils\Configuration;
use pocketmine\plugin\PluginBase;
use pocketmine\scheduler\Task;
use pocketmine\scheduler\TaskHandler;
use pocketmine\Server;

class Loader extends PluginBase {

	private static Loader $instance;
	private Configuration $config;
	private ?TaskHandler $task = null;

	public static function getInstance() : Loader {
		return self::$instance;
	}

	protected function onLoad() : void {
		self::$instance = $this;
		$this->saveDefaultConfig();

		// Create update directory if it doesn't exist.
		@mkdir($this->getDataFolder() . $this->getConfig()->get("update-dir"));
	}

	protected function onEnable() : void {
		$this->config = new Configuration($this->getConfig());

		$this->getServer()->getCommandMap()->register("plugindevtools", new RestartCommand());

		// Create task which will be checking for updates.
		$this->task = $this->getScheduler()->scheduleRepeatingTask(new class() extends Task {

			public function onRun() : void {
				Loader::getInstance()->checkForUpdate();
			}

		}, 20);
	}

	protected function onDisable() : void {
		$this->task->cancel();
	}

	public function checkForUpdate(bool $update = true) : void {
		// Get all files in the update directory.
		$files = [];
		foreach (scandir($this->config->getUpdateDir()) as $file) {
			if ($file !== "." && $file !== ".." && str_ends_with($file, ".phar")) {
				$files[] = $file;
			}
		}

		// Find available updates.
		$count = count($files);
		if ($count < 1) return;
		if (!$update) {
			$this->getLogger()->notice("Found $count update(s).");
			return;
		}

		// Restart the server.
		$this->getLogger()->notice("Found $count update(s). Preparing to update server...");
		$this->restart(function () use ($files) {
			// Copy file to plugins directory.
			$targetDir = Server::getInstance()->getPluginPath();

			foreach ($files as $file) {
				@copy("{$this->config->getUpdateDir()}/$file", "$targetDir/$file");
				@unlink("{$this->config->getUpdateDir()}/$file");
			}

			// Start the server.
			system($this->config->getStartFile());
		});
	}

	public function restart(?\Closure $shutdownFunction = null) : void {
		if ($shutdownFunction === null) {
			$shutdownFunction = function () {
				// Start the server.
				system($this->config->getStartFile());
			};
		}

		// Add a shutdown hook to start the server.
		register_shutdown_function($shutdownFunction);

		// Check if we should transfer players.
		// If so, transfer all the players.
		if ($this->config->doTransfer())
			foreach (Server::getInstance()->getOnlinePlayers() as $player)
				$player->transfer($this->config->getAddress(), $this->config->getPort(), "Server Update");

		// Shutdown this server instance.
		Server::getInstance()->shutdown();
	}

}