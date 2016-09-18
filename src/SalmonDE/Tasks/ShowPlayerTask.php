<?php
namespace SalmonDE\Tasks;

use pocketmine\Player;
use pocketmine\scheduler\PluginTask;

class ShowPlayerTask extends PluginTask
{

    public function __construct($owner, Player $player){
        parent::__construct($owner);
        $this->player = $player;
    }

    public function onRun($currenttick){
        $this->player->spawnToAll();
    }
}
