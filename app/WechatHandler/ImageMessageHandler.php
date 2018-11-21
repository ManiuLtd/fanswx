<?php
namespace App\WechatHandler;

use EasyWeChat\Kernel\Contracts\EventHandlerInterface;
use Illuminate\Support\Facades\Log;

class ImageMessageHandler implements EventHandlerInterface
{
    public function handle($message = [])
    {
        Log::debug('ImageMessage');
        return '非常抱歉！小二暂时不能识别图片。';
    }
}