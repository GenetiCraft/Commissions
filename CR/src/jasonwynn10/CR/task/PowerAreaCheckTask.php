<?php
declare(strict_types=1);
namespace jasonwynn10\CR\task;

use jasonwynn10\CR\Main;
use pocketmine\scheduler\PluginTask;

class PowerAreaCheckTask extends PluginTask {
	/**
	 * PowerAreaCheckTask constructor.
	 * @param Main $owner
	 */
	public function __construct(Main $owner) {
		parent::__construct($owner);
	}

	/**
	 * @param int $currentTick
	 */
	public function onRun(int $currentTick) {
		/** @noinspection PhpUndefinedMethodInspection */
		$this->getOwner()->checkPowerAreas();
	}
}