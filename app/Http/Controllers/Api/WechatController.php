<?php

namespace App\Http\Controllers\Api;

use App\WechatHandler\EventMessageHandler;
use App\WechatHandler\ImageMessageHandler;
use App\WechatHandler\MediaMessageHandler;
use App\WechatHandler\TextMessageHandler;
use EasyWeChat\Kernel\Messages\Message;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class WechatController extends Controller
{
    protected $app;
    public function __construct()
    {
        $this->app = app('wechat.official_account');
    }
    /**
     * 处理微信的请求消息
     *
     * @return string
     */
    public function serve()
    {
        $server = $this->app->server;
        //文字消息处理
        $server->push(TextMessageHandler::class, Message::TEXT);
        //事件消息处理
        $server->push(EventMessageHandler::class, Message::EVENT);
        //图片消息处理
        $server->push(ImageMessageHandler::class, Message::IMAGE);
        //同时处理多种类型的处理器
        $server->push(MediaMessageHandler::class, Message::VOICE|Message::VIDEO|Message::SHORT_VIDEO);
        return $server->serve();
    }

    /**
     * 更新微信菜单
     */
    public function menu_update()
    {
        $buttons = [
            [
                "type" => "click",
                "name" => "今日歌曲",
                "key"  => "V1001_TODAY_MUSIC"
            ],
            [
                "name"       => "菜单",
                "sub_button" => [
                    [
                        "type" => "view",
                        "name" => "搜索",
                        "url"  => "http://www.soso.com/"
                    ],
                    [
                        "type" => "view",
                        "name" => "视频",
                        "url"  => "http://v.qq.com/"
                    ],
                    [
                        "type" => "click",
                        "name" => "赞一下我们",
                        "key" => "V1001_GOOD"
                    ],
                ],
            ],
        ];
        $this->app->menu->create($buttons);
        $menus = $this->app->menu->get();
        dd($menus);
    }

    public function qrcode_temporary(Request $request)
    {
        $result = $this->app->qrcode->temporary($request->scene_id, $request->day * 24 * 3600);
        $url = $this->app->qrcode->url($result['ticket']);
        dd($url);
    }

    public function qrcode_forever(Request $request)
    {
        $result = $this->app->qrcode->forever($request->scene_id);
        $url = $this->app->qrcode->url($result['ticket']);
        dd($url);
    }
}
