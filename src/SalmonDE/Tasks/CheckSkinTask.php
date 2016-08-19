<?php
namespace SalmonDE\Tasks;

use pocketmine\scheduler\PluginTask;
use pocketmine\Server;
use pocketmine\utils\TextFormat as TF;

class CheckSkinTask extends PluginTask
{
  public function __construct($owner, $player, $skindata, $skinid){
      $this->owner = $owner;
      parent::__construct($owner);
      $this->player = $player;
      $this->skindata = $skindata;
      $this->skinid = $skinid;
  }

  public function onRun($currenttick){
      if(base64_encode($this->player->getSkinData()) == $this->skindata){
          if(!$this->player->getSkinId() == $this->skinid){
              $this->player->kick(TF::AQUA.'Entschuldige, du wurdest gekickt, da dein Skin Format vom Server nicht geändert werden konnte.'."\n".TF::AQUA.'☹', false);
          }else{
              $this->player->sendTip(TF::GREEN.TF::BOLD.'Du hast den Skin Check bestanden! :)');
          }
      }else{
          $this->player->kick(TF::AQUA.'Entschuldige, du wurdest gekickt, da dein Skin vom Server nicht geändert werden konnte.'."\n".TF::AQUA.'☹', false);
      }
  }
}
