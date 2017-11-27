<?php
declare(strict_types=1);
namespace jasonwynn10\CR\form;

use pocketmine\form\MenuForm;

class StartingLocationForm extends MenuForm{
	public function __construct() {
		parent::__construct("Starting Location", "Choose a location to begin your adventure!", [
			//
		]); //TODO
	}
}