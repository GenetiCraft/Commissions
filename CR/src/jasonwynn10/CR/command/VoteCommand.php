<?php
declare(strict_types=1);
namespace jasonwynn10\CR\command;

use jasonwynn10\CR\form\VoteForm;
use jasonwynn10\CR\Main;
use jasonwynn10\CR\task\DelayedFormTask;
use pocketmine\command\CommandSender;
use pocketmine\command\PluginCommand;
use pocketmine\Player;
use ProjectInfinity\PocketVote\PocketVote;

class VoteCommand extends PluginCommand {
	public function __construct(Main $plugin) {
		parent::__construct("vote", $plugin);
		$this->setUsage("/vote");
		$this->setDescription("Display a UI for ranks");
		$this->setPermission("cr.command.vote");
	}

	/**
	 * @param CommandSender $sender
	 * @param string        $commandLabel
	 * @param array         $args
	 *
	 * @return bool|mixed
	 */
	public function execute(CommandSender $sender, string $commandLabel, array $args) {
		if(parent::execute($sender, $commandLabel, $args)) {
			PocketVote::getPlugin()->getVoteManager()->expireVotes();
			if($sender instanceof Player and PocketVote::getPlugin()->getVoteManager()->hasVotes($sender->getName())) {
				/** @noinspection PhpParamsInspection */
				$this->getPlugin()->getServer()->getScheduler()->scheduleDelayedTask(new DelayedFormTask($this->getPlugin(), new VoteForm(), $sender), 20*3);
			}
			return true;
		}
		return false;
	}
}