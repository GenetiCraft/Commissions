<?php
declare(strict_types=1);
namespace jasonwynn10\CR\form;

use jasonwynn10\CR\Main;
use pocketmine\form\Form;
use pocketmine\form\MenuForm;
use pocketmine\form\MenuOption;
use pocketmine\level\Position;
use pocketmine\Player;

class TeleportLocationForm extends MenuForm {
	/**
	 * StartingLocationForm constructor.
	 *
	 * @param string $kingdom
	 */
	public function __construct(string $kingdom) {
		$options = [];
		foreach(Main::getInstance()->getConfig()->getNested("Kingdoms.".$kingdom, []) as $name => $posArr) {
			$options[] = new MenuOption($name);
		}
		parent::__construct("Starting Location", "Choose a location to begin your adventure!", $options);
	}

	/**
	 * @param Player $player
	 *
	 * @return null|Form
	 */
	public function onClose(Player $player) : ?Form {
		return new self(Main::getInstance()->getPlayerKingdom($player));
	}

	/**
	 * @param Player $player
	 *
	 * @return null|Form
	 */
	public function onSubmit(Player $player) : ?Form {
		$plugin = Main::getInstance();
		$option = $this->getSelectedOption()->getText();
		$kingdom = $plugin->getPlayerKingdom($player);
		$posArr = $plugin->getConfig()->getNested("Kingdoms.".$kingdom.".".$option,[]);
		if(!empty($posArr)) {
			$level = $player->getServer()->getLevelByName($posArr["level"]); //TODO: check null
			$player->teleport(new Position($posArr["x"], $posArr["y"], $posArr["z"], $level));
			return null;
		}else{
			return new self($kingdom);
		}
	}
}