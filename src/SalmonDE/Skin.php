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

  public $delay = 4;

  // These SkinIDs could be wrong and have to be checked again!
  public $femaleskinids = [
      'Standard_CustomSlim',
      'Standard_Alex',
      'CampfireTales_CampfireTalesTheLapisLady',
      'CampfireTales_CampfireTalesTheSham',
      'CampfireTales_CampfireTalesRancidAnne',
      'CampfireTales_CampfireTalesFarlander',
      'CampfireTales_CampfireTalesTheArisenRose',
      'CampfireTales_CampfireTalesSilksnatcher',
      'CampfireTales_CampfireTalesTheWellWisher',
      'Minecon_MineconAlexCape2011',
      'Minecon_MineconAlexCape2012',
      'Minecon_MineconAlexCape2013',
      'Minecon_MineconAlexCape2015',
      'Minecon_MineconAlexCape2016',
      'Villains_VillainsStrongholdSeer',
      'Villains_VillainsDungeonSpector',
      'Villains_VillainsEndergaunt',
      'Villains_VillainsLavaFiend',
      'Villains_VillainsSilverfishMonger',
      'Villains_VillainsSlymime',
      'Villains_VillainsSwindler',
      'Villains_VillainsTerrorSpawner',
      'Biome2_Biome2MushroomArcher',
      'Biome2_Biome2MushroomBrawler',
      'Biome2_Biome2MushroomExplorer',
      'Biome2_Biome2MushroomFarmer',
      'Biome2_Biome2MushroomHunter',
      'Biome2_Biome2NetherTamer',
      'Biome2_Biome2NetherEngineer',
      'Biome2_Biome2NetherExtinguisher',
      'Biome2_Biome2NetherFarmer',
      'Biome2_Biome2NetherGriefer',
      'Biome2_Biome2NetherMiner',
      'Redstone_RedstoneArtisan',
      'Redstone_RedstoneExperimenter',
      'Redstone_RedstoneTrapper',
      'Redstone_RedstoneMiner',
      'Redstone_RedstoneProgrammer',
      'Redstone_RedstoneProspector',
      'Redstone_RedstoneArchitect',
      'Redstone_RedstoneRailRider',
      'JTTW_JTTWGuanyin',
      'JTTW_JTTWBaigujing',
      'JTTW_JTTWScorpionDemon',
      'JTTW_JTTWPrincessIronFan',
      'JTTW_JTTWSpiderDemon',
      'JTTW_JTTWXuangzang',
      'Festive_FestiveSweaterAlex',
      'Festive_FestiveMrsClaus',
      'Festive_FestiveMotherChristmas',
      'Festive_FestiveTomte',
      'Festive_FestivePajamaKid',
      'Festive_FestiveSkiBibs',
      'Festive_FestiveGingerbreadCreeper',
      'PvPWarriors_TundraBrewer',
      'PvPWarriors_TundraGriefer',
      'PvPWarriors_TundraHunter',
      'PvPWarriors_ForestGriefer',
      'PvPWarriors_ForestEngineer',
      'PvPWarriors_ForestHunter',
      'PvPWarriors_ForestTamer',
      'PvPWarriors_ForestWoodbeast',
      'PvPWarriors_DesertTamer',
      'PvPWarriors_DesertArcher',
      'PvPWarriors_DesertBrawler',
      'PvPWarriors_DesertHusk',
      'Halloween_IronGolemCostume',
      'Halloween_EndermanCostume',
      'Halloween_GhastCostume',
      'Halloween_MooshroomCostume', // #BlameShoghicp ?
      'Halloween_PinkSheepCostume',
      'Halloween_ZombiePigmanCostume',
      'CityFolk_Barmaid',
      'TownFolk_Bandit',
      'TownFolk_Shopkeeper',
      'TownFolk_Witch'
  ];

  public $capes = [
      'Steve' => [
          'Minecon_MineconSteveCape2011',
          'Minecon_MineconSteveCape2012',
          'Minecon_MineconSteveCape2013',
          'Minecon_MineconSteveCape2015',
          'Minecon_MineconSteveCape2016',
          'Standard_Custom' // Just here to make the plugin able to remove capes (THIS IS NOT A CAPE)
      ],
      'Alex' => [
          'Minecon_MineconAlexCape2011',
          'Minecon_MineconAlexCape2012',
          'Minecon_MineconAlexCape2013',
          'Minecon_MineconAlexCape2015',
          'Minecon_MineconAlexCape2016',
          'Standard_CustomSlim' // Just here to make the plugin able to remove capes (THIS IS NOT A CAPE)
      ]
  ];

  public $capes2 = [
      'MineconCape2011',
      'MineconCape2012',
      'MineconCape2013',
      'MineconCape2015',
      'MineconCape2016',
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
    $this->capes2[] = $this->getMessages()['General']['Keiner'];
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
                          $this->getServer()->getScheduler()->scheduleDelayedTask(new ShowPlayerTask($this, $target), $this->delay);
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
                  if(in_array($target->getSkinId(), $this->femaleskinids)){
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
              return false;
          }
      }elseif(strtolower($cmd->getName()) == 'saveskin'){
          if(isset($args[0]) || count($args) > 1){
              if($this->getConfig()->get('TempSavePlayerSkins')){
                  $name = strtolower($args[0]);
                  if(isset($this->pskins[$name])){
                      $skinid = $this->getCapelessSkinId($this->pskins[$name]['skinid']);
                      if($skinid === 'Standard_CustomSlim'){
                          $gender = 'Female';
                      }else{
                          $skinid = 'Standard_Custom';
                          $gender = 'Male';
                      }
                      if(!isset($this->skins[$gender][$name])){
                          $this->skins[$gender][$name] = [
                              'skinname' => $args[0],
                              'skinid' => $skinid,
                              'skindata' => $this->pskins[$name]['skindata']
                          ];
                          file_put_contents($this->getDataFolder().'skins.json', json_encode($this->skins, JSON_PRETTY_PRINT));
                          $sender->sendMessage(TF::GOLD.str_replace(['{player}', '{gender}'], [$args[0], $this->getMessages()['General'][$gender]], $this->getMessages()['SaveSkin']['SkinSaved']));
                      }else{
                          $sender->sendMessage(TF::RED.$this->getMessages()['SaveSkin']['AlreadySaved']);
                      }
                  }else{
                      $sender->sendMessage(TF::RED.str_replace('{player}', $args[0], $this->getMessages()['ChangeSkin']['PlayerNotFound']));
                  }
              }else{
                  $sender->sendMessage(TF::RED.$this->getMessages()['SaveSkin']['Disabled']);
              }
          }else{
              return false;
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
      if(!$event->getPlayer()->hasPermission('skinchanger.bypass')){
          if($this->getConfig()->get('JoinSkins')){
              if(file_exists($this->getDataFolder().'skins.json')){
                  if(in_array($event->getPlayer()->getSkinId(), $this->femaleskinids)){
                      $joinskin = $this->skins['Female'][array_rand($this->skins['Female'])];
                  }else{
                      $joinskin = $this->skins['Male'][array_rand($this->skins['Male'])];
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
                              $this->getServer()->getScheduler()->scheduleDelayedTask(new ShowPlayerTask($this, $event->getPlayer()), $this->delay);
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
  }

  public function onJoin(PlayerJoinEvent $event){
      if($this->getConfig()->get('RemoveCapeOnJoin')){
          if(in_array($event->getPlayer()->getSkinId(), $this->capes['Steve'])){
              $event->getPlayer()->setSkin($event->getPlayer()->getSkinData(), 'Standard_Custom');
          }elseif(in_array($event->getPlayer()->getSkinId(), $this->capes['Alex'])){
              $event->getPlayer()->setSkin($event->getPlayer()->getSkinData(), 'Standard_CustomSlim');
          }
      }
      if($this->getConfig()->get('Rank-Specific-Capes') && ($pperms = $this->getServer()->getPluginManager()->getPlugin('PurePerms'))){
          $group = $pperms->getUserDataMgr()->getGroup($event->getPlayer())->getName();
          $groupcapes = $this->getConfig()->get('Rank-Capes');
          if(isset($groupcapes[$group])){
              if(in_array($event->getPlayer()->getSkinId(), $this->femaleskinids)){
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

  public function getCapelessSkinId($skinid){
      if(in_array($skinid, $this->capes['Alex'])){
          $skinid = 'Standard_CustomSlim';
      }elseif(in_array($skinid, $this->capes['Steve'])){
          $skindid = 'Standard_Custom';
      }
      return $skinid;
  }

  public function update(){
      $this->getServer()->getScheduler()->scheduleTask(new UpdaterTask($this, $this->getDescription()->getVersion()));
  }
}
