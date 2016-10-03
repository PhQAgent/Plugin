<?php
namespace plugin;
use phqagent\plugin\PluginBase;
use phqagent\message\Message;
use phqagent\console\MainLogger;
use phqagent\utils\Curl;

class ShindanMaker extends PluginBase{
    
    public function onLoad(){
        MainLogger::info('ShindanMaker外部引用插件已加载!');
    }

    private function getshindan($id, $nickname){
        $curl = new Curl();
        $html = $curl->setUrl("https://cn.shindanmaker.com/$id")->
        setEncPost([
            'u' => $nickname,
        ])->exec();
        $a = explode('<div class="result2">', $html);
        $b = explode('</div>', $a[1]);
        $rs = str_replace('&nbsp;', ' ', strip_tags($b[0]));
        $rs = str_replace("\n\n", "\n", $rs);
        return $rs;
    }

    public function onMessageReceive(Message $message){
        if(strstr($message->getContent(), '我的二次元美少女形象')){
            $rs = $this->getshindan(162207, $message->getUser()->getCard());
            MainLogger::info($message->getUser()->getCard(). ' 进行了 我的二次元美少女形象 测试');
            new Message($message, "$rs\n\n本外部数据由 ShindanMaker 提供", true);
        }

        if(strstr($message->getContent(), '今日关键词')){
            $rs = $this->getshindan(384482, $message->getUser()->getCard());
            MainLogger::info($message->getUser()->getCard(). ' 进行了 今日关键词 测试');
            new Message($message, "$rs\n\n本外部数据由 ShindanMaker 提供", true);
        }

        if(strstr($message->getContent(), '娘化穿越到异世界')){
            $rs = $this->getshindan(635902, $message->getUser()->getCard());
            MainLogger::info($message->getUser()->getCard(). ' 进行了 娘化穿越到异世界 测试');
            new Message($message, "$rs\n\n本外部数据由 ShindanMaker 提供", true);
        }

    }

}