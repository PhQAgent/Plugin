<?php
namespace plugin;
use plugin\PluginBase;
use element\Message;
use element\ReplyMessage;
use utils\Curl;

class ShindanMaker extends PluginBase{
    
    public function onLoad(){
        $this->getServer()->getLogger()->info('ShindanMaker外部引用插件已加载!');
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

    public function onReceive(Message $message){
        if(strstr($message->getContent(), '我的二次元美少女形象')){
            $rs = $this->getshindan(162207, $message->getUser()->getNick($message->getGroup()));
            $msg = new ReplyMessage($message);
            $msg->setContent("$rs\n\n本外部数据由 ShindanMaker 提供");
            $this->send($msg);
            $this->getServer()->getLogger()->info($message->getUser()->getNick($message->getGroup()). ' 进行了 我的二次元美少女形象 测试');
        }

        if(strstr($message->getContent(), '今日关键词')){
            $rs = $this->getshindan(384482, $message->getUser()->getNick($message->getGroup()));
            $msg = new ReplyMessage($message);
            $msg->setContent("$rs\n\n本外部数据由 ShindanMaker 提供");
            $this->send($msg);
            $this->getServer()->getLogger()->info($message->getUser()->getNick($message->getGroup()). ' 进行了 今日关键词 测试');
        }

        if(strstr($message->getContent(), '娘化穿越到异世界')){
            $rs = $this->getshindan(635902, $message->getUser()->getNick($message->getGroup()));
            $msg = new ReplyMessage($message);
            $msg->setContent("$rs\n\n本外部数据由 ShindanMaker 提供");
            $this->send($msg);
            $this->getServer()->getLogger()->info($message->getUser()->getNick($message->getGroup()). ' 进行了 娘化穿越到异世界 测试');
        }

    }

}