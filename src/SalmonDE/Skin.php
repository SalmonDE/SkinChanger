<?php
namespace SalmonDE;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\entity\Entity;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerLoginEvent;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\TextFormat as TF;
use SalmonDE\Tasks\CheckSkinTask;
use SalmonDE\Tasks\ShowPlayerTask;

class Skin extends PluginBase implements Listener
{

  public function onEnable(){
    @mkdir($this->getDataFolder());
    $this->saveResource('config.yml');
    $this->saveResource('skins.json');
    $this->getServer()->getPluginManager()->registerEvents($this, $this);
    $this->skins = json_decode(file_get_contents($this->getDataFolder().'skins.json'), true);
    $this->tasks = [];
  }

  public function onCommand(CommandSender $sender, Command $cmd, $label, array $args){
      if(strtolower($cmd->getName()) == 'changeskin'){
          if($sender instanceof Player){
              if(isset($args[0])){
                  if(!isset($this->tasks[strtolower($sender->getName())])){
                      if(isset($this->skins['Male'][strtolower($args[0])]) || isset($this->skins['Female'][strtolower($args[0])]) || isset($this->pskins[strtolower($args[0])])){
                          if(isset($args[1])){
                              $target = $this->getServer()->getPlayer($args[1]);
                              if($target instanceof Player){
                                  $target = $target;
                              }else{
                                  $sender->sendMessage(TF::RED.$args[1].' wurde nicht gefunden!');
                                  return true;
                              }
                          }else{
                              $target = $sender;
                          }
                          if(isset($this->skins['Male'][strtolower($args[0])])){
                              $skin = $this->skins['Male'][strtolower($args[0])];
                          }elseif(isset($this->skins['Female'][strtolower($args[0])])){
                              $skin = $this->skins['Female'][strtolower($args[0])];
                          }else{
                              $skin = $this->pskins[strtolower($args[0])];
                          }
                          $target->despawnFromAll();
                          $target->setSkin(base64_decode($skin['skindata']), $skin['skinid']);
                          $target->sendMessage(TF::GREEN.TF::BOLD.'Dein Skin wurde geändert!');
                          if($this->getConfig()->get('CheckSkin')){
                              $this->tasks[strtolower($target->getName())] = 1;
                              $this->getServer()->getScheduler()->scheduleDelayedTask(new CheckSkinTask($this, $target, $skin['skindata'], $skin['skinid']), 20 * $this->getConfig()->get('SkinCheckTime'));
                          }
                          $this->getServer()->getScheduler()->scheduleDelayedTask(new ShowPlayerTask($this, $target), 20);
                      }else{
                          $sender->sendMessage(TF::GOLD.'Sorry! Diesen Skin gibt es nicht! Prüfe bitte die Schreibweise: '.TF::AQUA.$args[0]);
                      }
                  }else{
                      $sender->sendMessage(TF::RED.'Entschuldige, anscheinend wird dein Skin momentan geprüft. Bitte warte eine gewisse Zeit, bis du deinen Skin wieder wechseln darfst!');
                  }
              }else{
                  $sender->sendMessage(TF::RED.'Du musst einen Skinnamen nennen! '."\n".TF::GOLD.'Du kannst auch den Skin eines Spielers benutzen, der auf dem Server spielt.'."\n".TF::GOLD.'Standard Skins findest du mit dem Befehl /skins');
                  return false;
              }
          }else{
              $sender->sendMessage(TF::RED.'Du musst ein Spieler sein, um diesen Befehl nutzen zu dürfen!');
          }
      }else{
          $sender->sendMessage(TF::GOLD.TF::BOLD.'Männlich');
          foreach($this->skins['Male'] as $skin){
              $sender->sendMessage(TF::AQUA.'Skinname: '.TF::GREEN.$skin['skinname'].TF::AQUA.', Skintyp: '.TF::GREEN.$skin['skinid']);
          }
          $sender->sendMessage(TF::GOLD.TF::BOLD.'Weiblich');
          foreach($this->skins['Female'] as $skin){
              $sender->sendMessage(TF::LIGHT_PURPLE.'Skinname: '.TF::GREEN.$skin['skinname'].TF::LIGHT_PURPLE.', Skintyp: '.TF::GREEN.$skin['skinid']);
          }
      }
      return true;
  }

  public function onLogin(PlayerLoginEvent $event){
      $this->pskins[strtolower($event->getPlayer()->getName())] = ['skindata' => base64_encode($event->getPlayer()->getSkinData()), 'skinid' => $event->getPlayer()->getSkinId()];
      if(!in_array($event->getPlayer()->getName(), $this->getConfig()->get('ServerTeam'))){
          if(!$event->getPlayer()->hasPermission('skinchanger.bypass')){
              if($this->getConfig()->get('JoinSkins')){
                  if(file_exists($this->getDataFolder().'skins.json')){
                      if($event->getPlayer()->getSkinId() == 'Standard_CustomSlim'){
                          $count = count($this->skins['Female']);
                          $num = mt_rand(0, $count - 1);
                          $joinskin = $this->skins['Female'][array_keys($this->skins['Male'])[$num]];
                      }else{
                          $count = count($this->skins['Male']);
                          $num = mt_rand(0,  $count - 1);
                          $joinskin = $this->skins['Male'][array_keys($this->skins['Male'])[$num]];
                      }
                      if(isset($joinskin)){
                          if(isset($joinskin['skindata'])){
                              if(isset($joinskin['skinid'])){
                                  $event->getPlayer()->despawnFromAll();
                                  $event->getPlayer()->setSkin(base64_decode($joinskin['skindata']), $joinskin['skinid']);
                                  $event->getPlayer()->sendTip(TF::GREEN.TF::BOLD.'Dein Skin wurde geändert!');
                                  if($this->getConfig()->get('CheckSkin')){
                                      $this->tasks[strtolower($event->getPlayer()->getName())] = 1;
                                      $this->getServer()->getScheduler()->scheduleDelayedTask(new CheckSkinTask($this, $event->getPlayer(), $joinskin['skindata'], $joinskin['skinid']), 20 * $this->getConfig()->get('SkinCheckTime'));
                                  }
                                  $this->getServer()->getScheduler()->scheduleDelayedTask(new ShowPlayerTask($this, $event->getPlayer()), 20);
                              }else{
                                  $this->getLogger()->error(TF::RED.'Skin ID of '.TF::AQUA.$joinskin['skinname'].TF::RED.' not found!');
                              }
                          }else{
                              $this->getLogger()->error(TF::RED.'Skin data of '.TF::AQUA.$joinskin['skinname'].TF::RED.' not found!');
                          }
                      }else{
                          $this->getLogger()->error(TF::RED.'Skin not found!');
                      }
                  }
              }
            }
        }else{
            $event->getPlayer()->sendPopup(TF::GOLD.TF::BOLD.'Willkommen zurück, Teammitglied '.$event->getPlayer()->getName().'!');
        }
  }
}
