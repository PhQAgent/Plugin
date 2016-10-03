<?php
namespace plugin;
use phqagent\plugin\PluginBase;
use phqagent\message\Message;
use phqagent\console\MainLogger;

class MP extends PluginBase{

    private $map;

    public function onLoad(){
        MainLogger::info('MultiPlayer提议插件开启');
    }

    private function createMP($message, $gn){
        if(trim($gn) == ''){
            return "请输入 !mp 提议名字 创建或参加一个MultiPlayer提议";
        }
        if(isset($this->map[$message->getGroup()->getUin()][$gn])){
            $data = $this->map[$message->getGroup()->getUin()][$gn];
            if(((int)time() - $data['time']) > 600){
                unset($this->map[$message->getGroup()->getUin()][$gn]);
                goto create;
            }else{
                if($data['owner'] == $message->getUser()->getUin()){
                    $payload = '';
                    foreach($data['player'] as $player){
                        $payload .= "$player\n";
                    }
                    $count = count($data['player']);
                    return "参与 " . $message->getUser()->getCard() . " 的: $gn 的成员有\n{$payload}\n共{$count}人";
                }else{
                    $this->map[$message->getGroup()->getUin()][$gn]['player'][$message->getUser()->getUin()] = $message->getUser()->getCard();
                    return $message->getUser()->getCard() . " 已报名参加: $gn";
                }
            }
        }else{
            create:
            $this->map[$message->getGroup()->getUin()][$gn] = [
                'time' => time(),
                'owner' => $message->getUser()->getUin(),
                'player' => []
            ];
            return "创建MultiPlayer提议: {$gn} 成功!\n创建者再次输入该指令可查看参加人员\n想要参加的群员请输入:\n!mp {$gn}\n参加MP";
        }
    }

    public function onMessageReceive(Message $message){
        if(strstr($message->getContent(), '!mp')){
            $arg = explode('!mp ', $message->getContent());
            $arg = isset($arg[1]) ? $arg[1] : '';
            $rs = $this->createMP($message, $arg);
            MainLogger::info($message->getUser()->getcard() . ' 创建/参加了MP ' . $arg);
            new Message($message, $rs, true);
   	    }
    }

}