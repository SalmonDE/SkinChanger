<?php
namespace SalmonDE;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\entity\Entity;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerLoginEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\TextFormat as TF;
use SalmonDE\Tasks\CheckSkinTask;
use SalmonDE\Tasks\RankCapeTask;
use SalmonDE\Tasks\ShowPlayerTask;
use SalmonDE\Updater\CheckVersionTask;
use SalmonDE\Updater\UpdaterTask;

class Skin extends PluginBase implements Listener
{

  public $capes = [
      'Steve' => [
          'Minecon_MineconSteveCape2011',
          'Minecon_MineconSteveCape2012',
          'Minecon_MineconSteveCape2013',
          'Minecon_MineconSteveCape2015',
          'Minecon_MineconSteveCape2016',
      ],
      'Alex' => [
          'Minecon_MineconAlexCape2011',
          'Minecon_MineconAlexCape2012',
          'Minecon_MineconAlexCape2013',
          'Minecon_MineconAlexCape2015',
          'Minecon_MineconAlexCape2016'
      ]
  ];

  public $capes2 = [
      'MineconCape2011',
      'MineconCape2012',
      'MineconCape2013',
      'MineconCape2015',
      'MineconCape2016'
  ];

  public function onEnable(){
    @mkdir($this->getDataFolder());
    $this->saveResource('config.yml');
    $this->getServer()->getScheduler()->scheduleAsyncTask(new CheckVersionTask($this));
    $this->saveResource('skins.json');
    if(!file_exists($this->getDataFolder().'messages.ini')){
        $this->saveResource(strtolower($this->getConfig()->get('Language')).'.ini');
        rename($this->getDataFolder().strtolower($this->getConfig()->get('Language')).'.ini', $this->getDataFolder().'messages.ini');
    }
    $this->getServer()->getPluginManager()->registerEvents($this, $this);
    $this->skins = json_decode(file_get_contents($this->getDataFolder().'skins.json'), true);
    $this->tasks = [];
  }

  public function getMessages(){
      if(file_exists($this->getDataFolder().'messages.ini')){
          return parse_ini_file($this->getDataFolder().'messages.ini', true);
      }else{
          return null;
      }
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
                                  $sender->sendMessage(TF::RED.str_replace('{player}', $args[1], $this->getMessages()['ChangeSkin']['PlayerNotFound']));
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
                          $target->sendMessage(TF::GREEN.TF::BOLD.$this->getMessages()['ChangeSkin']['SkinChanged']);
                          if($this->getConfig()->get('CheckSkin')){
                              $this->tasks[strtolower($target->getName())] = 1;
                              $this->getServer()->getScheduler()->scheduleDelayedTask(new CheckSkinTask($this, $target, ['SkinData' => $skin['skindata'], 'SkinID' => $skin['skinid']]), 20 * $this->getConfig()->get('SkinCheckTime'));
                          }
                          $this->getServer()->getScheduler()->scheduleDelayedTask(new ShowPlayerTask($this, $target), 20);
                      }else{
                          $sender->sendMessage(TF::GOLD.str_replace('{skin}', $args[0], $this->getMessages()['ChangeSkin']['SkinNotFound']));
                      }
                  }else{
                      $sender->sendMessage(TF::RED.$this->getMessages()['ChangeSkin']['SkinInCheck']);
                  }
              }else{
                  $sender->sendMessage(TF::RED.$this->getMessages()['ChangeSkin']['SkinNameMissing']);
                  return false;
              }
          }else{
              $sender->sendMessage(TF::RED.$this->getMessages()['General']['SenderMustBePlayer']);
          }
      }elseif(strtolower($cmd->getName()) == 'changecape'){
          if(isset($args[0])){
              if(in_array($args[0], $this->capes2)){
                  if(isset($args[1])){
                      $player = $this->getServer()->getPlayer($args[1]);
                      if($player instanceof Player){
                          $target = $player;
                      }else{
                          $target = $sender;
                      }
                  }else{
                      $target = $sender;
                  }
                  if($target->getSkinId() == 'Standard_CustomSlim' || $target->getSkinId() == 'Standard_Alex'){
                      $cape = $this->getCape($args[0], 'Alex');
                  }else{
                      $cape = $this->getCape($args[0], 'Steve');
                  }
                  $target->setSkin($target->getSkinData(), $cape);
                  $target->sendMessage(TF::GREEN.$this->getMessages()['ChangeCape']['CapeChanged']);
              }else{
                  $sender->sendMessage(TF::RED.$this->getMessages()['ChangeCape']['CapeNotFound']);
              }
          }else{
              $sender->sendMessage(TF::GOLD.$this->getMessages()['ChangeCape']['CapesAvailable']);
              foreach($this->capes2 as $cape){
                  $sender->sendMessage(TF::LIGHT_PURPLE.str_replace('{cape}', $cape, $this->getMessages()['ChangeCape']['Cape']));
              }
          }
      }else{
          $sender->sendMessage(TF::GOLD.TF::BOLD.$this->getMessages()['General']['Male']);
          foreach($this->skins['Male'] as $skin){
              $sender->sendMessage(TF::AQUA.str_replace(['{skin}', '{id}'], [$skin['skinname'], $skin['skinid']], $this->getMessages()['Skins']['Skin']));
          }
          $sender->sendMessage(TF::GOLD.TF::BOLD.$this->getMessages()['General']['Female']);
          foreach($this->skins['Female'] as $skin){
              $sender->sendMessage(TF::LIGHT_PURPLE.str_replace(['{skin}', '{id}'], [$skin['skinname'], $skin['skinid']], $this->getMessages()['Skins']['Skin']));
          }
      }
      return true;
  }

  public function onLogin(PlayerLoginEvent $event){
      if($this->getConfig()->get('TempSavePlayerSkins')){
          $this->pskins[strtolower($event->getPlayer()->getName())] = ['skindata' => base64_encode($event->getPlayer()->getSkinData()), 'skinid' => $event->getPlayer()->getSkinId()];
      }
      if(!in_array($event->getPlayer()->getName(), $this->getConfig()->get('ServerTeam'))){
          if(!$event->getPlayer()->hasPermission('skinchanger.bypass')){
              if($this->getConfig()->get('JoinSkins')){
                  if(file_exists($this->getDataFolder().'skins.json')){
                      if($event->getPlayer()->getSkinId() == 'Standard_CustomSlim' || $event->getPlayer()->getSkinId() == 'Standard_Alex'){
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
                                  $event->getPlayer()->sendTip(TF::GREEN.TF::BOLD.$this->getMessages()['ChangeSkin']['SkinChanged']);
                                  if($this->getConfig()->get('CheckSkin')){
                                      $this->tasks[strtolower($event->getPlayer()->getName())] = 1;
                                      $this->getServer()->getScheduler()->scheduleDelayedTask(new CheckSkinTask($this, $event->getPlayer(), ['SkinData' => $joinskin['skindata'], 'SkinID' => $joinskin['skinid']]), 20 * $this->getConfig()->get('SkinCheckTime'));
                                  }
                                  $this->getServer()->getScheduler()->scheduleDelayedTask(new ShowPlayerTask($this, $event->getPlayer()), 20);
                              }else{
                                  $this->getLogger()->error(TF::RED.str_replace('{skin}', $joinskin['skinid'], $this->getMessages()['General']['SkinIDNotFound']));
                              }
                          }else{
                              $this->getLogger()->error(TF::RED.str_replace('{skin}', $joinskin['skinname'], $this->getMessages()['General']['SkinDataNotFound']));
                          }
                      }else{
                          $this->getLogger()->error(TF::RED.$this->getMessages()['General']['SkinNotFound']);
                      }
                  }
              }
            }
        }else{
            $event->getPlayer()->sendPopup(TF::GOLD.TF::BOLD.str_replace('{player}', $event->getPlayer()->getName(), $this->getMessages()['General']['WelcomeBackTeamMember']));
        }
  }

  public function onJoin(PlayerJoinEvent $event){
      if($this->getConfig()->get('RemoveCapeOnJoin')){
          if(in_array($event->getPlayer()->getSkinId(), $this->capes['Steve'])){
              $event->getPlayer()->setSkin($event->getPlayer()->getSkinData(), 'Standard_Costum');
          }elseif(in_array($event->getPlayer()->getSkinId(), $this->capes['Alex'])){
              $event->getPlayer()->setSkin($event->getPlayer()->getSkinData(), 'Standard_CostumSlim');
          }
      }
      if($this->getConfig()->get('Rank-Specific-Capes') && ($pperms = $this->getServer()->getPluginManager()->getPlugin('PurePerms'))){
          $group = $pperms->getUserDataMgr()->getGroup($event->getPlayer())->getName();
          if(@$this->getConfig()->get('Rank-Capes')[$group]){
              if($event->getPlayer()->getSkinId() == 'Standard_CostumSlim' || $event->getPlayer()->getSkinId() == 'Standard_Alex'){
                  $this->getServer()->getScheduler()->scheduleDelayedTask(new RankCapeTask($this, $event->getPlayer(), $this->getCape($this->getConfig()->get('Rank-Capes')[$group], 'Alex')), 40);
                  if(isset($this->pskins[strtolower($event->getPlayer()->getName())])){
                      $this->pskins[strtolower($event->getPlayer()->getName())]['skinid'] = $this->getCape($this->getConfig()->get('Rank-Capes')[$group], 'Alex');
                  }
              }else{
                  $this->getServer()->getScheduler()->scheduleDelayedTask(new RankCapeTask($this, $event->getPlayer(), $this->getCape($this->getConfig()->get('Rank-Capes')[$group], 'Steve')), 40);
                  if(isset($this->pskins[strtolower($event->getPlayer()->getName())])){
                      $this->pskins[strtolower($event->getPlayer()->getName())]['skinid'] = $this->getCape($this->getConfig()->get('Rank-Capes')[$group], 'Steve');
                  }
              }
          }
      }
  }

  public function onQuit(PlayerQuitEvent $event){
      if(isset($this->pskins[strtolower($event->getPlayer()->getName())])){
          unset($this->pskins[strtolower($event->getPlayer()->getName())]);
      }
  }

  public function getCape($cape, $skinid){
      return str_replace($this->capes2, $this->capes[$skinid], $cape);
  }

  public function update(){
      $this->getServer()->getScheduler()->scheduleTask(new UpdaterTask($this, $this->getDescription()->getVersion()));
  }
}
