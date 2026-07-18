<?php
/**
 * domain_shop 插件 - DNSPod API 适配器
 * 文档：https://cloud.tencent.com/document/product/1427
 *
 * 鉴权：API Token（ID + Secret），HTTP Header：LoginToken
 * 签名：无签名要求，Token 直接传递
 */
if (!defined('IN_CRONLITE')) exit;

class DomainShop_DNSPod
{
	private $apiId;
	private $apiSecret;
	private $endpoint = 'https://dnsapi.cn/';
	private $lastErr = '';

	public function __construct($apiId, $apiSecret)
	{
		$this->apiId = (string)$apiId;
		$this->apiSecret = (string)$apiSecret;
	}

	public function lastError()
	{
		return $this->lastErr;
	}

	/**
	 * 取域名列表
	 * @return array [['name'=>..., 'id'=>...], ...]
	 */
	public function listDomains()
	{
		$res = $this->call('Domain.List', []);
		if (empty($res['ok'])) return [];
		$body = $res['body'];
		$arr = json_decode($body, true);
		if (!is_array($arr) || ($arr['status']['code'] ?? '0') !== '1') {
			$this->lastErr = $arr['status']['message'] ?? 'Domain.List 失败';
			return [];
		}
		$domains = $arr['domains'] ?? [];
		$out = [];
		foreach ($domains as $d) {
			$out[] = ['id' => (string)$d['id'], 'name' => $d['name'] ?? ''];
		}
		return $out;
	}

	/**
	 * 取某域名下记录列表
	 */
	public function listRecords($domain)
	{
		$res = $this->call('Record.List', ['domain' => $domain]);
		if (empty($res['ok'])) return [];
		$body = $res['body'];
		$arr = json_decode($body, true);
		if (!is_array($arr) || ($arr['status']['code'] ?? '0') !== '1') {
			$this->lastErr = $arr['status']['message'] ?? 'Record.List 失败';
			return [];
		}
		return $arr['records'] ?? [];
	}

	/**
	 * 创建记录
	 * @return string|false 远程记录 ID，失败返回 false
	 */
	public function createRecord($domain, $name, $type, $value, $ttl = 600, $mx = 0)
	{
		$params = [
			'domain' => $domain,
			'sub_domain' => $name,
			'record_type' => strtoupper($type),
			'record_line' => '默认',
			'value' => $value,
			'ttl' => (int)$ttl,
		];
		if (strtoupper($type) === 'MX') $params['mx'] = (int)$mx;

		$res = $this->call('Record.Create', $params);
		if (empty($res['ok'])) {
			$this->lastErr = 'HTTP 请求失败：' . ($res['error'] ?? '');
			return false;
		}
		$arr = json_decode($res['body'], true);
		if (!is_array($arr) || ($arr['status']['code'] ?? '0') !== '1') {
			$this->lastErr = $arr['status']['message'] ?? 'Record.Create 失败';
			return false;
		}
		return (string)($arr['record']['id'] ?? '');
	}

	/**
	 * 更新记录
	 */
	public function updateRecord($domain, $recordId, $name, $type, $value, $ttl = 600, $mx = 0)
	{
		$params = [
			'domain' => $domain,
			'record_id' => $recordId,
			'sub_domain' => $name,
			'record_type' => strtoupper($type),
			'record_line' => '默认',
			'value' => $value,
			'ttl' => (int)$ttl,
		];
		if (strtoupper($type) === 'MX') $params['mx'] = (int)$mx;

		$res = $this->call('Record.Modify', $params);
		if (empty($res['ok'])) return false;
		$arr = json_decode($res['body'], true);
		if (!is_array($arr) || ($arr['status']['code'] ?? '0') !== '1') {
			$this->lastErr = $arr['status']['message'] ?? 'Record.Modify 失败';
			return false;
		}
		return true;
	}

	/**
	 * 删除记录
	 */
	public function deleteRecord($domain, $recordId)
	{
		$res = $this->call('Record.Remove', ['domain' => $domain, 'record_id' => $recordId]);
		if (empty($res['ok'])) return false;
		$arr = json_decode($res['body'], true);
		if (!is_array($arr) || ($arr['status']['code'] ?? '0') !== '1') {
			$this->lastErr = $arr['status']['message'] ?? 'Record.Remove 失败';
			return false;
		}
		return true;
	}

	/**
	 * 调用 DNSPod API
	 * 优先使用 mnbt_http_post（带安全策略），失败回退到 cURL
	 */
	private function call($action, array $params)
	{
		$url = $this->endpoint . $action;
		$body = http_build_query($params, '', '&');
		$headers = [
			'Content-Type: application/x-www-form-urlencoded',
			'User-Agent: MNBT-DomainShop/1.0',
		];

		// 用插件引擎的 HTTP 出站函数（有内网/协议安全策略）
		if (function_exists('mnbt_http_post')) {
			$res = mnbt_http_post($url, $body, [
				'timeout' => 15,
				'headers' => $headers,
			]);
			if (!empty($res['ok'])) {
				return ['ok' => true, 'body' => $res['body'] ?? ''];
			}
			$this->lastErr = $res['error'] ?? 'mnbt_http_post 失败';
			// DNSPod 是公网 API，不会触发内网限制；如果失败回退到 cURL
		}

		// 回退：原生 cURL
		if (!function_exists('curl_init')) {
			$this->lastErr = 'PHP 未启用 cURL 扩展';
			return ['ok' => false, 'error' => $this->lastErr];
		}
		$ch = curl_init($url);
		curl_setopt_array($ch, [
			CURLOPT_POST => true,
			CURLOPT_POSTFIELDS => $params,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_TIMEOUT => 15,
			CURLOPT_HTTPHEADER => ['Expect:'],
		]);
		// 注意：DNSPod API Token 通过 user token 字段传递
		$params['login_token'] = $this->apiId . ',' . $this->apiSecret;
		$params['format'] = 'json';
		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params, '', '&'));
		$body = curl_exec($ch);
		$err = curl_error($ch);
		$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);
		if ($body === false) {
			$this->lastErr = 'cURL 错误：' . $err;
			return ['ok' => false, 'error' => $this->lastErr];
		}
		return ['ok' => true, 'body' => $body];
	}
}
