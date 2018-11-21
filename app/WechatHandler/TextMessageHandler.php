<?php
namespace App\WechatHandler;

use App\Services\TencentAiService;
use EasyWeChat\Kernel\Contracts\EventHandlerInterface;
use EasyWeChat\Kernel\Messages\Text;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

class TextMessageHandler implements EventHandlerInterface
{
    protected $app;

    public function __construct()
    {
        $this->app = app('wechat.official_account');
    }
    public function handle($message = null)
    {
        Log::debug('TextMessage: '.json_encode($message).'--SESSION: '.session('qq'));
        switch ($message['Content']) {
            case 'xx':
                Redis::del('ai_'.$message['FromUserName']);
                return '(～￣▽￣)～  已结束当前会话。
1、智能闲聊请输入：xl
2、中英互译请输入：fy
3、结束请输入：xx
';
                break;
            case 'xl':
                Redis::set('ai_'.$message['FromUserName'], 'xl');
                Redis::expire('ai_'.$message['FromUserName'], 66);
                return '(～￣▽￣)～  我来啦...';
                break;
            case 'fy':
                Redis::set('ai_'.$message['FromUserName'], 'fy');
                Redis::expire('ai_'.$message['FromUserName'], 66);
                return 'o(*￣︶￣*)o  略懂略懂~';
                break;
            default:
                $re = TencentAiService::initApp();
                if (!$re) {
                    Log::error('Line: '.__LINE__.json_encode($re));
                    return '服务器出现故障，请稍后再试！';
                }
                if (Redis::get('ai_'.$message['FromUserName']) == 'xl') {
                    Redis::expire('ai_'.$message['FromUserName'], 66);
                    $params = array(
                        'question' => $message['Content'],
                        'session' => $message['FromUserName'],
                    );
                    $re = json_decode(TencentAiService::textchat($params));
                    if ($re->ret != 0) {
                        Log::error('Line: '.__LINE__.json_encode($re));
                        return '服务器出现故障，请稍后再试！';
                    }
                    return $re->data->answer;
                }elseif (Redis::get('ai_'.$message['FromUserName']) == 'fy') {
                    Redis::expire('ai_'.$message['FromUserName'], 66);
                    $params = array(
                        'type' => 0,
                        'text' => $message['Content'],
                    );
                    $re = json_decode(TencentAiService::texttrans($params));
                    if ($re->ret != 0) {
                        Log::error('Line: '.__LINE__.json_encode($re));
                        return '服务器出现故障，请稍后再试！';
                    }
                    return $re->data->trans_text;
                }else{
                    return '(～￣▽￣)～  o(*￣︶￣*)o。
1、智能闲聊请输入：xl
2、中英互译请输入：fy
3、结束请输入：xx
';
                }
                break;
        }
    }
}