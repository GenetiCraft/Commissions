<?php
declare(strict_types=1);
namespace jasonwynn10\CR\form;

use jasonwynn10\CR\Main;
use pocketmine\form\CustomForm;
use pocketmine\form\element\Dropdown;
use pocketmine\form\element\Label;
use pocketmine\form\element\Toggle;
use pocketmine\form\Form;
use pocketmine\IPlayer;
use pocketmine\Player;

class KingdomInformationForm extends CustomForm {
	/**
	 * KingdomInformationForm constructor.
	 *
	 * @param IPlayer $player
	 */
	public function __construct(IPlayer $player) {
		$plugin = Main::getInstance();
		$kingdom = $plugin->getPlayerKingdom($player);
		$elements = [];
		$elements[] = new Label("Kingdom Leader:  ".$plugin->getKingdomLeader($kingdom));
		$elements[] = new Label("Kingdom Power:  ".$plugin->getKingdomPower($kingdom));
		$elements[] = new Label("Kingdom Booty:  ".$plugin->getKingdomMoney($kingdom));
		$elements[] = new Toggle("Request Money?");
		$elements[] = new Dropdown("Kingdom Members", $plugin->getKingdomMembers($kingdom));
		parent::__construct("Kingdom Information", $elements);
	}

	/**
	 * @param Player $player
	 *
	 * @return null|Form
	 */
	public function onSubmit(Player $player) : ?Form {
		$option = $this->getElement(3);
		if($option->getValue()) {
			return new MoneyRequestForm($player);
		}
		return null;
	}
}