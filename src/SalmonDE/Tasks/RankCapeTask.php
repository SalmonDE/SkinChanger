<?php
namespace SalmonDE\Tasks;

use pocketmine\Player;
use pocketmine\scheduler\PluginTask;

class RankCapeTask extends PluginTask
{

    public function __construct($owner, Player $player, $skinid){
        parent::__construct($owner);
        $this->player = $player;
        $this->skinid = $skinid;
    }

    public function onRun($currenttick){
        $this->player->setSkin($this->player->getSkinData(), $this->skinid);
    }
}
