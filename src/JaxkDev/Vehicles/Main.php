<?php
/*
 * Vehicles, PocketMine-MP Plugin.
 *
 * Licensed under the Open Software License version 3.0 (OSL-3.0)
 * Copyright (C) 2019-2020 JaxkDev
 *
 * Twitter :: @JaxkDev
 * Discord :: JaxkDev#8860
 * Email   :: JaxkDev@gmail.com
 */

declare(strict_types=1);

namespace JaxkDev\Vehicles;

use pocketmine\command\Command;
use pocketmine\plugin\PluginBase;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat as C;

use JaxkDev\Vehicles\Vehicles\Vehicle;
use JaxkDev\Vehicles\Exceptions\DesignException;
use JaxkDev\Vehicles\Exceptions\VehicleException;

class Main extends PluginBase{

	private static $instance;

	/** @var String|Vehicle[] */
	public static $inVehicle = [];

	public $prefix = C::GRAY."[".C::AQUA."Vehicles".C::GRAY."] ".C::GOLD."> ".C::RESET;

	/** @var CommandHandler */
	private $commandHandler;

	/** @var EventHandler */
	private $eventHandler;

	/** @var Factory */
	public $factory;

	/** @var String|String[] */
	public $interactCommands = [];

	public function onLoad(): void{
		self::$instance = $this;
		$this->getLogger()->debug("Loading all resources...");

		$this->saveResource("Vehicles/BasicCar.json");
		$this->saveResource("Designs/Design_Manifest.json");

		//Add handlers and others here.
		$this->commandHandler = new CommandHandler($this);
		$this->factory = new Factory($this);
		$this->eventHandler = new EventHandler($this);

		//Load any that need to be loaded.
		$this->getLogger()->debug("Registering designs...");
		try{
			$this->factory->registerDesigns();
		} catch (DesignException $e){
			$this->getLogger()->debug("Failed to register designs on load, below contains the error (often including a user friendly reason).");
			$this->getLogger()->critical($e->getMessage());
			$this->getServer()->getPluginManager()->disablePlugin($this);
		}

		$this->getLogger()->debug("Registering vehicles...");
		try{
			$this->factory->registerVehicles();
		} catch (VehicleException $e){
			$this->getLogger()->debug("Failed to register vehicles on load, below contains the error (often including a user friendly reason).");
			$this->getLogger()->critical($e->getMessage());
			$this->getServer()->getPluginManager()->disablePlugin($this);
		}
	}

	public function onEnable(): void{
		$this->getServer()->getPluginManager()->registerEvents($this->eventHandler, $this);
	}

	public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool{
		$this->commandHandler->handleCommand($sender, $args);
		return true;
	}

	public static function getInstance() : self{
		return self::$instance;
	}
}
