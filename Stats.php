<?php
namespace plugin;
use plugin\PluginBase;
use element\Message;
use element\ReplyMessage;
use utils\Curl;

class Stats extends PluginBase{

    public function onLoad(){
        $this->getServer()->getLogger()->info('ppy的恶俗成绩查询插件已加载!');
    }

    private function getStats($m, $player){
        $curl = new Curl();
        $html = $curl->setUrl("https://osu.ppy.sh/u/$player")->exec();
        preg_match('/var userId = (.*);/iU', $html, $userid);
        if(!isset($userid[1]) or (trim($userid[1]) == null)){
            return false;
        }
        $userid = $userid[1];
        $html = $curl->setUrl("https://osu.ppy.sh/pages/include/profile-general.php")->
        setGet([
            'u' => $userid,
            'm' => $m,
        ])->
        exec();
        if(strstr($html, 'No information recorded')){
            return false;
        }
        if(strstr($html, 'This user has not played enough')){
            $pp = 'N/A';
            $rank = 'N/A';
        }else{
            preg_match('/Performance<\/a>: (.*)pp/iU', $html, $pp);
            $pp = $pp[1];
            preg_match('/pp \(#(.*)\)/iU', $html, $rank);
            $rank = $rank[1];
        }
        preg_match('/Hit Accuracy<\/b>: (.*)%/iU', $html, $acc);
        $acc = $acc[1];
        preg_match('/Total Hits<\/b>: (.*)<\/div>/iU', $html, $tth);
        $tth = $tth[1];
        preg_match('/Play Time<\/b>: (.*)<\/div>/iU', $html, $pt);
        $pt = $pt[1];
        return "Performance: {$pp}pp\nRank: #{$rank}\nHit Accuracy: {$acc}%\nPlay Time: {$pt}\nTotal Hits: {$tth}";
    }

    public function onReceive(Message $message){
        if(strstr($message->getContent(), '!stats ')){
            $arg = explode('!stats ', $message->getContent());
            $arg = isset($arg[1]) ? $arg[1] : '';
            $msg = "{$arg}\n{$this->getStats(0, $arg)}";
            $this->getServer()->getLogger()->info($message->getUser()->getNick($message->getGroup()). ' 请求了 '.$arg.' 的 std 模式排名');
            $this->send((new ReplyMessage($message))->setContent($msg));
        }

        if(strstr($message->getContent(), '!taiko ')){
            $arg = explode('!taiko ', $message->getContent());
            $arg = isset($arg[1]) ? $arg[1] : '';
            $msg = "{$arg}\n{$this->getStats(1, $arg)}";
            $this->getServer()->getLogger()->info($message->getUser()->getNick($message->getGroup()). ' 请求了 '.$arg.' 的 taiko 模式排名');
            $this->send((new ReplyMessage($message))->setContent($msg));
        }

        if(strstr($message->getContent(), '!ctb ')){
            $arg = explode('!ctb ', $message->getContent());
            $arg = isset($arg[1]) ? $arg[1] : '';
            $msg = "{$arg}\n{$this->getStats(2, $arg)}";
            $this->getServer()->getLogger()->info($message->getUser()->getNick($message->getGroup()). ' 请求了 '.$arg.' 的 ctb 模式排名');
            $this->send((new ReplyMessage($message))->setContent($msg));
        }
        
        if(strstr($message->getContent(), '!mania ')){
            $arg = explode('!mania ', $message->getContent());
            $arg = isset($arg[1]) ? $arg[1] : '';
            $msg = "{$arg}\n{$this->getStats(3, $arg)}";
            $this->getServer()->getLogger()->info($message->getUser()->getNick($message->getGroup()). ' 请求了 '.$arg.' 的 mania 模式排名');
            $this->send((new ReplyMessage($message))->setContent($msg));
        }
    }

}