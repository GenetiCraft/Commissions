<?php
declare(strict_types=1);
namespace jasonwynn10\CR\command;

use jasonwynn10\CR\form\KingdomWarpForm;
use jasonwynn10\CR\Main;
use jasonwynn10\CR\task\DelayedFormTask;
use pocketmine\command\CommandSender;
use pocketmine\command\PluginCommand;
use pocketmine\Player;

class WarpMeCommand extends PluginCommand {
	/**
	 * WarpMeCommand constructor.
	 *
	 * @param Main $plugin
	 */
	public function __construct(Main $plugin) {
		parent::__construct("warpme", $plugin);
		$this->setUsage("/warpme");
		$this->setDescription("Shows a UI for the kingdoms a player can warp to");
		$this->setPermission("cr.command.warpme");
	}

	/**
	 * @param CommandSender $sender
	 * @param string        $commandLabel
	 * @param array         $args
	 *
	 * @return bool|mixed
	 */
	public function execute(CommandSender $sender, string $commandLabel, array $args) : bool {
		if($this->testPermission($sender)) {
			if($sender instanceof Player) {
				/** @noinspection PhpParamsInspection */
				$this->getPlugin()->getServer()->getScheduler()->scheduleDelayedTask(new DelayedFormTask($this->getPlugin(), new KingdomWarpForm(), $sender), 20*3);
			}
			return true;
		}
		return false;
	}
}