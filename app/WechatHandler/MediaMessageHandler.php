<?php
namespace App\WechatHandler;

use EasyWeChat\Kernel\Contracts\EventHandlerInterface;
use Illuminate\Support\Facades\Log;

class MediaMessageHandler implements EventHandlerInterface
{
    public function handle($message = [])
    {
        Log::debug('MediaMessage');
        return '非常抱歉！小二暂时不能识别多媒体。';
    }
}