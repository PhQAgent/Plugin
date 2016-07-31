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

    public function onReceive(){
        if(strstr($this->getMessage(), '!stats ')){
            $arg = explode('!stats ', $this->getMessage());
            $arg = isset($arg[1]) ? $arg[1] : '';
            $this->reply("{$arg}\n{$this->getStats(0, $arg)}");
        }

        if(strstr($this->getMessage(), '!taiko ')){
            $arg = explode('!taiko ', $this->getMessage());
            $arg = isset($arg[1]) ? $arg[1] : '';
            $this->reply("{$arg}\n{$this->getStats(1, $arg)}");
        }

        if(strstr($this->getMessage(), '!ctb ')){
            $arg = explode('!ctb ', $this->getMessage());
            $arg = isset($arg[1]) ? $arg[1] : '';
            $this->reply("{$arg}\n{$this->getStats(2, $arg)}");
        }
        
        if(strstr($this->getMessage(), '!mania ')){
            $arg = explode('!mania ', $this->getMessage());
            $arg = isset($arg[1]) ? $arg[1] : '';
            $this->reply("{$arg}\n{$this->getStats(3, $arg)}");
        }
    }

}