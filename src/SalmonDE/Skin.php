<?php
namespace SalmonDE;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\TextFormat as TF;
use SalmonDE\Tasks\CheckSkinTask;

class Skin extends PluginBase implements Listener
{

  public function onEnable(){
    @mkdir($this->getDataFolder());
    $this->saveResource('config.yml');
    $this->saveResource('skins.json');
    $this->getServer()->getPluginManager()->registerEvents($this, $this);
  }

  public function onJoin(PlayerJoinEvent $event){
      if($this->getConfig()->get('JoinSkins')){
          if(file_exists($this->getDataFolder().'skins.json')){
              $skins = json_decode(file_get_contents($this->getDataFolder().'skins.json'), true);
              if($event->getPlayer()->getName() == $this->getConfig()->get('Owner')){
                  $owner = $this->getConfig()->get('Owner');
                  $joinskin = $skins[$owner];
              }elseif($event->getPlayer()->getSkinId() == 'Standard_CustomSlim'){
                  $num = mt_rand(1, count($skins['Female']));
                  $joinskin = $skins['Female'][$num];
              }else{
                  $num = mt_rand(1, count($skins['Male']));
                  $joinskin = $skins['Male'][$num];
              }
              if(isset($joinskin)){
                  if(isset($joinskin['skindata'])){
                      if(isset($joinskin['skinid'])){
                          $event->getPlayer()->setSkin(base64_decode($joinskin['skindata']), $joinskin['skinid']);
                          $event->getPlayer()->sendTip(TF::GREEN.TF::BOLD.'Dein Skin wurde geändert!');
                          if($this->getConfig()->get('CheckJoinSkin')){
                              $this->getServer()->getScheduler()->scheduleDelayedTask(new CheckSkinTask($this, $event->getPlayer(), $joinskin['skindata'], $joinskin['skinid']), 20 * $this->getConfig()->get('SkinCheckTime'));
                          }
                      }else{
                          $this->getLogger()->error(TF::RED.'Skin ID of '.TF::AQUA.$joinskin['skinname'].TF::RED.' not found!');
                      }
                  }else{
                      $this->getLogger()->error(TF::RED.'Skin data of '.TF::AQUA.$joinskin['skinname'].TF::RED.' not found!');
                  }
              }else{
                  $this->getLogger()->error(TF::RED.'Skin not found!');
              }
      }else{
          $this->getLogger()->error(TF::RED.'skins.json file not found!');
      }
    }
  }
}
