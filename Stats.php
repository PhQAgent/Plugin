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
        $html_o = $curl->setUrl("https://osu.ppy.sh/u/$player")->exec();
        preg_match('/var userId = (.*);/iU', $html_o, $userid);
        if(!isset($userid[1]) or (trim($userid[1]) == null)){
            return false;
        }
        $ua = explode('<div class="profile-username">', $html_o);
        $ub = explode('</div>', $ua[1]);
        $username = $ub[0];
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
        preg_match('/<div title=\'Arrived\'>.*datetime.*>(.*)</iU', $html_o, $at);
        $at = $this->timediff(strtotime($at[1]), time());
        preg_match('/<div title=\'Last Active\'>.*datetime.*>(.*)</iU', $html_o, $la);
        $la = $this->timediff(strtotime($la[1]), time());
        preg_match('/Hit Accuracy<\/b>: (.*)%/iU', $html, $acc);
        $acc = $acc[1];
        preg_match('/Play Count<\/b>: (.*)<\/div>/iU', $html, $pc);
        $pc = $pc[1];
        preg_match('/Play Time<\/b>: (.*)<\/div>/iU', $html, $pt);
        $pt = $pt[1];
        preg_match('/Current Level<\/b>: (.*)<\/div>/iU', $html, $cv);
        $cv = $cv[1];
        preg_match('/Total Hits<\/b>: (.*)<\/div>/iU', $html, $tth);
        $tth = $tth[1];
        preg_match('/Maximum Combo<\/b>: (.*)<\/div>/iU', $html, $mc);
        $mc = $mc[1];
        preg_match('/X.png.*width.*>(.*)</iU', $html, $ss);
        $ss = $ss[1];
        preg_match('/S.png.*width.*>(.*)</iU', $html, $s);
        $s = $s[1];
        preg_match('/A.png.*width.*>(.*)</iU', $html, $a);
        $a = $a[1];
        $strat = '';
        foreach($at as $key => $value){
            if($value > 0){
                if($value > 1) $key_r = $key . 's';
                $strat .= "$value $key_r ";
                if($key == 'year' and $value >= 2) break;
                if($key == 'day' and $value >= 15) break;
                if($key == 'min' and $value >= 10) break;
            }
        }
        $strla = '';
        foreach($la as $key => $value){
            if($value > 0){
                if($value > 1) $key_r = $key .'s';
                $strla .= "$value $key_r ";
                if($key == 'year' and $value >= 2) break;
                if($key == 'day' and $value >= 15) break;
                if($key == 'min' and $value >= 10) break;
            }
        }
        $modemap = ['Std', 'Taiko', 'Ctb', 'Mania'];
        $mode = $modemap[$m];
        return "Account: $username\nMode: $mode\nPerformance: {$pp}pp\nRank: #{$rank}\nHit Accuracy: {$acc}%\nPlay Count: {$pc}\nPlay Time: {$pt}\nCurrent Level: {$cv}\nTotal Hits: {$tth}\nMaximum Combo: {$mc}\nSS: {$ss} S: {$s} A: {$a}";
    }

    public function timediff($a, $b){  
        $diff = max([$a, $b]) - min([$a, $b]);
        $days = (int)($diff / 86400);
        $years = (int)($days / 365);
        $days = $years % 365;
        $remain = $diff % 86400;
        $hours = (int)($remain / 3600);
        $remain = $remain % 3600;
        $mins = (int)($remain / 60);
        $secs = $remain % 60;
        return [
            'year' => $years,
            'day' => $days,
            'hour' => $hours,
            'min' => $mins,
            'sec' => $secs
        ];
    }

    public function onReceive(Message $message){
        if(strstr($message->getContent(), '!stats ')){
            $arg = explode('!stats ', $message->getContent());
            $arg = isset($arg[1]) ? $arg[1] : '';
            $rs = $this->getStats(0, $arg);
            if($rs){
                $msg = $rs;
            }else{
                $msg = "账户 $arg 不存在";
            }
            $this->getServer()->getLogger()->info($message->getUser()->getNick($message->getGroup()). ' 请求了 '.$arg.' 的 std 模式排名');
            $this->send((new ReplyMessage($message))->setContent($msg));
        }

        if(strstr($message->getContent(), '!taiko ')){
            $arg = explode('!taiko ', $message->getContent());
            $arg = isset($arg[1]) ? $arg[1] : '';
            $rs = $this->getStats(1, $arg);
            if($rs){
                $msg = $rs;
            }else{
                $msg = "账户 $arg 不存在";
            }
            $this->getServer()->getLogger()->info($message->getUser()->getNick($message->getGroup()). ' 请求了 '.$arg.' 的 taiko 模式排名');
            $this->send((new ReplyMessage($message))->setContent($msg));
        }

        if(strstr($message->getContent(), '!ctb ')){
            $arg = explode('!ctb ', $message->getContent());
            $arg = isset($arg[1]) ? $arg[1] : '';
            $rs = $this->getStats(2, $arg);
            if($rs){
                $msg = $rs;
            }else{
                $msg = "账户 $arg 不存在";
            }
            $this->getServer()->getLogger()->info($message->getUser()->getNick($message->getGroup()). ' 请求了 '.$arg.' 的 ctb 模式排名');
            $this->send((new ReplyMessage($message))->setContent($msg));
        }
        
        if(strstr($message->getContent(), '!mania ')){
            $arg = explode('!mania ', $message->getContent());
            $arg = isset($arg[1]) ? $arg[1] : '';
            $rs = $this->getStats(3, $arg);
            if($rs){
                $msg = $rs;
            }else{
                $msg = "账户 $arg 不存在";
            }
            $this->getServer()->getLogger()->info($message->getUser()->getNick($message->getGroup()). ' 请求了 '.$arg.' 的 mania 模式排名');
            $this->send((new ReplyMessage($message))->setContent($msg));
        }
    }

}