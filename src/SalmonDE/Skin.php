<?php
namespace SalmonDE;

use pocketmine\command\Command;
use pocketmine\command\Commandsender;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\TextFormat as TF;

class Skin extends PluginBase implements Listener
{

  public function onEnable(){
    @mkdir($this->getDataFolder());
    $this->saveResource('config.yml');
    $this->saveResource('skins.json');
    $this->getServer()->getPluginManager()->registerEvents($this, $this);
  }

  public function onCommand(CommandSender $sender, Command $cmd, $label, array $args){
      if(strtolower($cmd->getName()) == 'changeskin'){
          if(file_exists($this->getDataFolder().'skins.json')){
              $skins = json_decode(file_get_contents($this->getDataFolder().'skins.json'), true);
              if(isset($args[0])){
                  $skin = $args[0];
                  return true;
              }else{
                  $skin = 'Ich-Will-Einen-Skin';
              }
              if(isset($skins[$skin])){
                  if(isset($skins[$skin]['skindata'])){
                      if(isset($skins[$skin]['skinid'])){
                          $sender->setSkin(base64_decode($skins[$skin]['skindata']), $skins[$skin]['skinid']);
                          $sender->sendMessage(TF::GREEN.TF::BOLD.'Dein Skin wurde geändert!');
                          $sender->sendTip(TF::GREEN.TF::BOLD.'Dein Skin wurde geändert!');
                      }else{
                          $sender->sendMessage(TF::GOLD.'Dieser Skin ist fehlerhaft. Bitte erzähle SalmonDE davon!');
                      }
                  }else{
                      $sender->sendMessage(TF::GOLD.'Dieser Skin ist fehlerhaft. Bitte erzähle SalmonDE davon!');
                  }
              }else{
                  $sender->sendMessage(TF::RED.'Skin '.TF::AQUA.$skin.TF::RED.' wurde nicht gefunden!');
              }
          }else{
              $sender->sendMessage(TF::RED.'Keine Skins verfügbar!');
          }
      }elseif(strtolower($cmd->getName()) == 'listskins'){
          if(file_exists($this->getDataFolder().'skins.json')){
              $skins = json_decode(file_get_contents($this->getDataFolder().'skins.json'), true);
              foreach($skins as $skin){
                $sender->sendMessage(TF::GOLD.'Skinname: '.TF::GREEN.$skin['skinname'].TF::GOLD.', Skintyp: '.TF::AQUA.$skin['skinid']);
              }
          }else{
              $sender->sendMessage(TF::RED.'Keine Skins verfügbar!');
          }
      }
  }

  public function onJoin(PlayerJoinEvent $event){
      file_put_contents('test.txt', base64_encode($event->getPlayer()->getSkinData()));
      if($event->getPlayer()->getName() == $this->getConfig()->get('Owner')){
          $joinskin = $this->getConfig()->get('OwnerSkin');
      }elseif($event->getPlayer()->getSkinId() == 'Standard_Custom'){
          $joinskin = $this->getConfig()->get('MaleJoinSkin');
      }elseif($event->getPlayer()->getSkinId() == 'Standard_CustomSlim'){
          $joinskin = $this->getConfig()->get('FemaleJoinSkin');
      }
      if($joinskin !== 'NULL'){
          if(file_exists($this->getDataFolder().'skins.json')){
              $skins = json_decode(file_get_contents($this->getDataFolder().'skins.json'), true);
              if(isset($skins[$joinskin])){
                  if(isset($skins[$joinskin]['skindata'])){
                      if(isset($skins[$joinskin]['skinid'])){
                          $event->getPlayer()->setSkin(base64_decode($skins[$joinskin]['skindata']), $skins[$joinskin]['skinid']);
                          $sender->sendTip(TF::GREEN.TF::BOLD.'Dein Skin wurde geändert!');
                      }else{
                          $this->getLogger()->error(TF::RED.'Skin ID of '.TF::AQUA.$joinskin.TF::RED.' not found!');
                      }
                  }else{
                      $this->getLogger()->error(TF::RED.'Skin data of '.TF::AQUA.$joinskin.TF::RED.' not found!');
                  }
              }else{
                  $this->getLogger()->error(TF::RED.'Skin '.TF::AQUA.$joinskin.TF::RED.' not found!');
              }
          }else{
              $this->getLogger()->error(TF::RED.'skins.json file not found!');
          }
      }
  }
}
