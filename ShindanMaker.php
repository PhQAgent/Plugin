<?php
use plugin\PluginBase;
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
        preg_match('/<textarea id=\"copy_text_140\" class=\"form-control\" .*>(.*)\n/iU', $html, $tgt);
        return $tgt[1];
    }

    public function onReceive(){
        if(strstr($this->getMessage(), '我的二次元美少女形象')){
            $this->reply("{$this->getshindan(162207, $this->getNickName())}\n\n本外部数据由 ShindanMaker 提供");
        }

        if(strstr($this->getMessage(), '今日关键词')){
            $this->reply("{$this->getshindan(384482, $this->getNickName())}\n\n本外部数据由 ShindanMaker 提供");
        }
    }

}