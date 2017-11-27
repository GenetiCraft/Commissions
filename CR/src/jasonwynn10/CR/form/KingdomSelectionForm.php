<?php
declare(strict_types=1);
namespace jasonwynn10\CR\form;

use jasonwynn10\CR\Main;
use pocketmine\form\Form;
use pocketmine\form\MenuForm;
use pocketmine\form\MenuOption;
use pocketmine\Player;

class KingdomSelectionForm extends MenuForm {
	public function __construct(Main $plugin) {
		$options = [];
		foreach($plugin->getKingdoms() as $kingdom => $locations) {
			$options[] = new MenuOption($kingdom);
		}
		parent::__construct("Kingdom Selection", "Choose a kingdom to start!", $options);
	}
	public function onClose(Player $player) : ?Form {
		$player->kick("You must choose a kingdom to start!", false);
		return null;
	}
	public function onSubmit(Player $player) : ?Form {
		$option = $this->getSelectedOptionIndex(); //TODO

		return new StartingLocationForm();
	}
}