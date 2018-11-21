<?php
namespace App\Services;

class TencentAiService
{
	const CHUNK_SIZE = 6400;
    const API_URL_PATH = 'https://api.ai.qq.com/fcgi-bin/';

    private static $app_id;
    private static $app_key;

    /**
     * initApp: 初始化应用
     * @param string $ai
     */
    public static function initApp()
    {
        $conf = config('ai.qq');
        if ($conf == null) {
            return false;
        }else {
            self::$app_id = $conf['app_id'];
            self::$app_key = $conf['app_key'];
            return true;
        }
    }
    /**
     * texttrans ：调用文本翻译（AI Lab）接口
     * @param $params
     * - $params：type-翻译类型；text-待翻译文本。（详见http://ai.qq.com/doc/nlptrans.shtml）
     * @return bool|mixed|string
     * - $response: ret-返回码；msg-返回信息；data-返回数据（调用成功时返回）；http_code-Http状态码（Http请求失败时返回）
     */
    public static function texttrans($params)
	{
		$params['sign'] = self::getReqSign($params);
		$url = self::API_URL_PATH . '/nlp/nlp_texttrans';
		$response = HttpUtilService::doHttpPost($url, $params);
		return $response;
	}

    /**
     * textchat: 基础闲聊接口
     * @param $params
     * - $params：session-会话标识（应用内唯一）; question-用户输入的聊天内容。（详见https://ai.qq.com/doc/nlpchat.shtml）
     * @return bool|mixed|string
     * - $response: ret-返回码；msg-返回信息；data-返回数据（调用成功时返回）；http_code-Http状态码（Http请求失败时返回）
     */
    public static function textchat($params)
    {
        $params['sign'] = self::getReqSign($params);
        $url = self::API_URL_PATH . '/nlp/nlp_textchat';
        $response = HttpUtilService::doHttpPost($url, $params);
        return $response;
    }

    /**
     * generalocr：调用通用OCR识别接口
     * @param $params
     *  - $params：image-待识别图片。（详见http://ai.qq.com/doc/ocrgeneralocr.shtml）
     * @return bool|mixed|string
     *  - $response: ret-返回码；msg-返回信息；data-返回数据（调用成功时返回）；http_code-Http状态码（Http请求失败时返回）
     */
    public static function generalocr($params)
	{
		if (!self::_is_base64($params['image']))
		{
		    $params['image'] = base64_encode($params['image']);
		}
		$params['sign'] = self::getReqSign($params);
		$url = self::API_URL_PATH . '/ocr/ocr_generalocr';
		$response = HttpUtilService::doHttpPost($url, $params);
		return $response;
	}

    /**
     * wxasrs：调用语音识别-流式版(WeChat AI)接口
     * @param $params
     *  - $params：speech-待识别的整段语音，不需分片；
     *      format-语音格式；
     *      rate-音频采样率编码；
     *      bits-音频采样位数；
     *      speech_id-语音ID。（详见http://ai.qq.com/doc/aaiasr.shtml）
     * @return bool|mixed|string
     *  - $response: ret-返回码；msg-返回信息；data-返回数据（调用成功时返回）；http_code-Http状态码（Http请求失败时返回）
     */
    public static function wxasrs($params)
	{
		$speech = self::_is_base64($params['speech']) ? base64_decode($params['speech']) : $params['speech'];
		unset($params['speech']);
		$speech_len = strlen($speech);
		$total_chunk = ceil($speech_len / self::CHUNK_SIZE);
        $params['cont_res'] = 0;
		for($i = 0; $i < $total_chunk; ++$i)
		{
			$chunk_data = substr($speech, $i * self::CHUNK_SIZE, self::CHUNK_SIZE);
			$params['speech_chunk'] = base64_encode($chunk_data);
			$params['len']          = strlen($chunk_data);
		    $params['seq']          = $i * self::CHUNK_SIZE;
		    $params['end']          = ($i == ($total_chunk-1)) ? 1 : 0;
			$response = self::wxasrs_perchunk($params);
		}
		return $response;
	}

    /**
     * wxasrs_perchunk：调用语音识别-流式版(WeChat AI)接口
     * @param $params
     *  - $params：speech_chunk-待识别的语音分片；
     *      seq-语音分片所在语音流的偏移量；
     *      len-分片长度；
     *      end-是否结束分片；
     *      cont_res-是否获取中间识别结果；
     *      format-语音格式；
     *      rate-音频采样率编码；
     *      bits-音频采样位数；
     *      speech_id-语音ID。（详见http://ai.qq.com/doc/aaiasr.shtml）
     * @return bool|mixed|string
     *  - $response: ret-返回码；msg-返回信息；data-返回数据（调用成功时返回）；http_code-Http状态码（Http请求失败时返回）
     */
    public static function wxasrs_perchunk($params)
	{
		if (!self::_is_base64($params['speech_chunk']))
		{
		    $params['speech_chunk'] = base64_encode($params['speech_chunk']);
		}
		$params['sign'] = self::getReqSign($params);
		$url = self::API_URL_PATH . '/aai/aai_wxasrs';
		$response = HttpUtilService::doHttpPost($url, $params);
		return $response;
	}

    /**
     * _is_base64：判断一个字符串是否经过base64
     * @param $str
     *  - $str：待判断的字符串
     * @return bool
     *  - 该字符串是否经过base64（true/false）
     */
    private static function _is_base64($str)
    {
        return $str == base64_encode(base64_decode($str)) ? true : false;
    }

    /**
     * getReqSign：根据 接口请求参数 和 应用密钥 计算 请求签名
     * @param $params
     *  - $params：接口请求参数（特别注意：不同的接口，参数对一般不一样，请以具体接口要求为准）
     * @return string
     *  - 签名结果
     */
    protected static function getReqSign(&$params)
    {
        // 0. 补全基本参数
        $params['app_id'] = self::$app_id;

        if (!isset($params['nonce_str']))
        {
            $params['nonce_str'] = uniqid("{$params['app_id']}_");
        }

        if (!isset($params['time_stamp']))
        {
            $params['time_stamp'] = time();
        }

        // 1. 字典升序排序
        ksort($params);

        // 2. 拼按URL键值对
        $str = '';
        foreach ($params as $key => $value)
        {
            if ($value !== '')
            {
                $str .= $key . '=' . urlencode($value) . '&';
            }
        }

        // 3. 拼接app_key
        $str .= 'app_key=' . self::$app_key;

        // 4. MD5运算+转换大写，得到请求签名
        $sign = strtoupper(md5($str));
        return $sign;
    }

}