<?php
declare(strict_types=1);
namespace jasonwynn10\CR\form;

use pocketmine\form\CustomForm;
use pocketmine\form\element\Slider;
use pocketmine\form\Form;
use pocketmine\Player;

class MoneyGrantRequestForm extends CustomForm {
	/**
	 * MoneyGrantRequestForm constructor.
	 *
	 * @param string $requester
	 * @param float  $amount
	 */
	public function __construct(string $requester, float $amount) {
		$elements = [];
		$elements[] = new Slider("Change Granted amount", 0.00, $amount, 5.00, $amount);
		parent::__construct("Money Requested from ".$requester, $elements);
	}

	/**
	 * @param Player $player
	 *
	 * @return null|Form
	 */
	public function onClose(Player $player) : ?Form {
		return parent::onClose($player); //TODO: inform requester of denied payment
	}

	/**
	 * @param Player $player
	 *
	 * @return null|Form
	 */
	public function onSubmit(Player $player) : ?Form {
		$element = $this->getElement(0);

	}
}