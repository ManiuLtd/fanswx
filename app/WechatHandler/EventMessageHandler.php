<?php
namespace App\WechatHandler;

use EasyWeChat\Kernel\Contracts\EventHandlerInterface;
use Illuminate\Support\Facades\Log;

class EventMessageHandler implements EventHandlerInterface
{
    protected $openid;
    public function handle($message = [])
    {
        $this->openid = $message['FromUserName'];
        switch ($message['Event']) {
            case 'subscribe'://订阅
                //扫描带参数的二维码  未订阅
                if (isset($message['EventKey'])) {
                    return $this->scan_subscribe(str_replace('qrscene_','',$message['EventKey']));
                }else {
                    //正常订阅
                    return 'Hello ━(*｀∀´*)ノ亻!。
1、智能闲聊请输入：xl
2、中英互译请输入：fy
3、结束请输入：xx
';
                }
                break;
            case 'SCAN':
                //扫描带参数的二维码  已订阅
                return $this->scan($message['EventKey']);
                break;
            case 'unsubscribe'://取消订阅
                return $this->unsubscribe();
                break;
            case 'LOCATION':
                return $this->location($message['Latitude'], $message['Longitude'], $message['Precision']);
                break;
            case 'CLICK':
                return $this->menu_click($message['EventKey']);
                break;
            case 'VIEW':
                return $this->menu_view($message['EventKey']);
                break;
            default:
                return '';
                break;
        }
    }

    /** 未订阅用户 扫描带参数的二维码
     * 二维码的参数值
     * @param $key
     */
    public function scan_subscribe($key)
    {
        return '未订阅用户 扫描带参数的二维码';
    }

    /** 已订阅用户 扫描带参数的二维码
     * 二维码的参数值
     * @param $key
     */
    public function scan($key)
    {
        return '已订阅用户 扫描带参数的二维码';
    }

    /** 取消订阅
     * @return string
     */
    public function unsubscribe()
    {
        return '';
    }

    /** 上报地理位置
     * 地理位置纬度
     * @param $latitude
     * 地理位置经度
     * @param $longitude
     * 地理位置精度
     * @param $precision
     * @return string
     */
    public function location($latitude, $longitude, $precision)
    {
        return '收到定位';
    }

    /** 自定义菜单事件
     * 自定义菜单接口中KEY值对应
     * @param $key
     * @return string
     */
    public function menu_click($key)
    {
        return '点击菜单事件';
    }

    /** 点击菜单跳转链接时的事件推送
     * 设置的跳转URL
     * @param $key
     * @return string
     */
    public function menu_view($key)
    {
        return '点击菜单链接';
    }
}