<?php
namespace plugin;
use phqagent\plugin\PluginBase;
use phqagent\console\MainLogger;
use phqagent\console\CommandManager;
use phqagent\utils\Curl;
use phqagent\utils\YAML;
use phqagent\element\FriendList;
use phqagent\message\Message;

class Weather extends PluginBase{
    
    const TIMEOUT = 30;//Test usage
    private $config;
    private $last;
    private $user = [];
    private $message = [];

    public function onLoad(){
        $file = $this->getDataDir() . 'config.yaml';
        try{
            $yaml = new YAML($file);
        }catch(\Exception $t){
            if($t->getMessage() == YAML::FILE_NOT_FOUND){
                $default = [
                    'user' => [
                        'demo' => '太原'
                    ],

                    'message' =>[
                        '今天有雨，要记得带伞啊',
                        '我听说今天会下雨，要是回不来的话，给我打个电话去接你吧'
                    ]

                ];
                MainLogger::warning('初始化YAML配置文件');
                $yaml = new YAML($default, YAML::ARRAY);
                $yaml->save($file);
            }
            if($t->getMessage() == YAML::BROKEN){
                MainLogger::warning('YAML配置文件损坏，插件停止加载');
                return false;
            }
        }
        $this->config = $yaml;
        $this->user = $yaml->get('user');
        $this->message = $yaml->get('message');
        MainLogger::warning('尝试申请回调钩子');
        $this->registerCallback();
        $this->last = time();
        CommandManager::register('天气提醒', $this);
        CommandManager::register('天气语句', $this);
        MainLogger::success('天气变化提醒插件已加载');
    }

    public function onShutdown(){
        $this->config->set('user', $this->user);
        $this->config->set('message', $this->message);
        MainLogger::alert('天气插件已关闭');
    }

    public function onCallback($type){
        $hr = date('G');
        if($hr < 5 or $hr > 21){
            return ;
        }
        if((time() - $this->last) > Weather::TIMEOUT){
            foreach($this->user as $nick => $city){
                foreach($this->getWeather($city)['1d'] as $weather){
                    if(strstr($weather, '雨')){
                        if($user = FriendList::getUserbyMark($nick)){
                            $message = $this->generateTodayAmeMessage();
                            new Message($user, $message, true);
                            break ;
                        }
                    }
                }
            }
            $this->last = time();
        }
    }

    private function generateTodayAmeMessage(){
        $message = $this->config->get('message');
        return $message[mt_rand(0, count($message) - 1)];
    }

    public function onCall($server, $arg){
        if($arg[0] == '天气提醒'){
            if(!isset($arg[1]) || !isset($arg[2]) || !isset($arg[3])){
                MainLogger::warning('缺少必要参数 请使用 天气提醒 [添加/删除] [名片] [城市]');
            }
            if($arg[1] == '添加'){
                $this->user[$arg[2]] = $arg[3];
                MainLogger::success('对' . $arg[2] . '进行天气提醒');
            }
            if($arg[1] == '删除'){
                if(isset($this->user[$arg[2]])){
                    unset($this->user[$arg[2]]);
                    MainLogger::success('对' . $arg[2] . '取消天气提醒');
                }
                MainLogger::warning($arg[2] . '不存在');
            }
        }elseif($arg[0] == '天气语句'){
            if(!isset($arg[1]) || !isset($arg[2])){
                MainLogger::warning('缺少必要参数 请使用 天气语句 [添加/删除/列出] [提示语句/提示语句ID]');
            }
            if($arg[1] == '添加'){
                $this->message[] = $arg[2];
                MainLogger::success('已添加天气提醒语句: ' . $arg[2]);
            }
            if($arg[1] == '列出'){
                print_r($this->message);
            }
            if($arg[1] == '删除'){
                if(isset($this->message[$arg[2]])){
                    unset($this->message[$arg[2]]);
                    MainLogger::success('已删除天气提醒语句: ' . $arg[2]);
                }
                MainLogger::warning('天气提醒语句' . $arg[2] . '不存在');
            }
        }

    }

    private function getWeather(){
        $html = (new Curl)->
                setUrl('http://www.weather.com.cn/weather1d/101100101.shtml')->
                returnHeader(false)->
                exec();
        preg_match('/hour3data=(.*)$/m', $html, $json);
        $data = json_decode($json[1], true);
        return $data;
    }

    const citycode = [];

}