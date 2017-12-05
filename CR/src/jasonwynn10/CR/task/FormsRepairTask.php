<?php
declare(strict_types=1);
namespace jasonwynn10\CR\task;

use jasonwynn10\CR\EventListener;
use jasonwynn10\CR\Main;
use pocketmine\network\mcpe\protocol\ModalFormRequestPacket;
use pocketmine\scheduler\PluginTask;

class FormsRepairTask extends PluginTask {
	/** @var string $formData */
	private $formData;
	/** @var int $formId */
	private $formId;
	/** @var string $player */
	private $player;

	/**
	 * FormsRepairTask constructor.
	 *
	 * @param Main   $owner
	 * @param string $player
	 * @param ModalFormRequestPacket   $form
	 */
	public function __construct(Main $owner, string $player, ModalFormRequestPacket $form) {
		parent::__construct($owner);
		$this->formData = $form->formData;
		$this->formId = $form->formId;
		$this->player = $player;
	}

	/**
	 * @param int $currentTick
	 */
	public function onRun(int $currentTick) {
		$player = $this->getOwner()->getServer()->getPlayerExact($this->player);
		if($player !== null and in_array($this->formId, EventListener::$sentForms)) {
			$pk = new ModalFormRequestPacket();
			$pk->formId = $this->formId;
			$pk->formData = $this->formData;
			$player->dataPacket($pk);
		}
	}
}