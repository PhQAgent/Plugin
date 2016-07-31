<?php
namespace plugin;
use plugin\PluginBase;
use element\Message;
use element\ReplyMessage;

class MP extends PluginBase{

    private $map;

    public function onLoad(){
        $this->getServer()->getLogger()->info('MultiPlayer提议插件开启');
    }

    private function createMP($gn){
        if(trim($gn) == ''){
            return "请输入 !mp 提议名字 创建或参加一个MultiPlayer提议";
        }
        if(isset($this->map[$this->getFrom()][$gn])){
            $data = $this->map[$this->getFrom()][$gn];
            if(((int)time() - $data['time']) > 600){
                unset($this->map[$this->getFrom()][$gn]);
                goto create;
            }else{
                if($data['owner'] == $this->getAccount()){
                    $payload = '';
                    foreach($data['player'] as $player){
                        $payload .= "$player\n";
                    }
                    $count = count($data['player']);
                    return "参与您的: $gn 的玩家有\n{$payload}共{$count}人";
                }else{
                    $this->map[$this->getFrom()][$gn]['player'][$this->getAccount()] = $this->getNickName();
                    return "您已报名参加: $gn";
                }
            }
        }else{
            create:
            $this->map[$this->getFrom()][$gn] = [
                'time' => time(),
                'owner' => $this->getAccount(),
                'player' => []
            ];
            return "创建MultiPlayer提议: {$gn} 成功!\n请再次输入该指令查看参加人员\n想要参加的群员请输入 !mp {$gn} 参加";
        }
    }

    public function onReceive(){
        if(strstr($this->getMessage(), '!mp')){
            $arg = explode('!mp ', $this->getMessage());
            $arg = isset($arg[1]) ? $arg[1] : '';
            $this->reply("{$this->createMP($arg)}");
        }
    }

}