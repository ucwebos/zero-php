<?php
/**
 * Created by PhpStorm.
 * User: skj
 * Date: 2018/12/7
 * Time: 上午11:51
 */

namespace Zero\Console\Gen;

use Zero\Config;
use Zero\IBootstrap;
use Zero\Route\Route;
use Zero\Support\Parsedown;

/**
 * 文档生成器
 * Class GenDoc
 * @package Zero\Console\Gen
 */
class GenDoc implements GenInterface {
	protected $docsPath;
	protected $testApi;

	public function gen($args) {
		$this->docsPath = ROOT_PATH . '/docs/';
		$this->testApi  = $args['api'] ?? 'http://localhost:7770';

		Config::load();
		$tag       = $args['srv'] ?? 'MAIN';
		$config    = Config::get('SERVER.' . $tag);
		$bootstrap = $config['bootstrap'] ?? '';
		if (!class_exists($bootstrap)) {
			die("not found class [SERVER.$tag.bootstrap]!");
		}
		$boot = new $bootstrap;
		if (!$boot instanceof IBootstrap) {
			die("the class [{$bootstrap}] unrealized interface [IBootstrap]");
		}

		$r = $boot->route(new Route());

		$groups = [];
		foreach ($r->getRoutes() as $method => $routes) {
			foreach ($routes as $route => $handle) {
				$route     = trim($route, '/');
				$groupEnd  = strpos($route, '/');
				$group     = substr($route, 0, $groupEnd);
				$groupPath = $this->docsPath . '/' . $group;
				if (!file_exists($groupPath)) {
					@mkdir($this->docsPath . '/' . $group);
				}
				$tpl              = $this->itemTPL($method, $route);
				$filename         = str_replace('/', '_', trim(substr($route, $groupEnd), '/'));
				$groups[$group][] = $filename;
				$file             = $groupPath . '/' . $filename . '.md';
				if (!file_exists($file)) {
					file_put_contents($file, $tpl);
				}
			}
		}
		$sidebar = "* [说明](header.md)" . PHP_EOL;
		$cmtMap  = $this->sidebars();
		asort($groups);
		foreach ($groups as $group => $files) {
			$groupUp = strtoupper($group);
			$sidebar .= "* {$groupUp}" . PHP_EOL;
			foreach ($files as $file) {
				$name = $file;
				if ($cmtMap) {
					$name = $cmtMap["{$group}/{$file}.md"] ?? $file;
				}
				$sidebar .= "	* [{$name}]({$group}/{$file}.md)" . PHP_EOL;
			}
		}
		file_put_contents($this->docsPath . '/_sidebar.md', $sidebar);
		echo "SUCCESS!";
		exit;
	}

	protected function itemTPL($method, $route) {


		$url = trim($this->testApi, '/') . '/' . $route;

		$tpl = <<<DOC

> 路径：{$route}

* 测试地址：($url)
* 请求方式：`{$method}`
* 请求参数：


|字段|说明|是否必须|类型|
|---|---|---|---|
|`params`|参数1|`是`|string|


* 响应数据： 

|字段|说明|类型|
|---|---|---|
|`info`|响应信息|object|

* 响应示例
``` json
	{json}
```
DOC;
		return $tpl;
	}

	protected function getParamsMD($message) {
		$str = '';
		if (!$message) {
			return $str;
		}
		foreach ($message as $filed => $item) {
			$requiredLab = "否";
			if (isset($item['required']) && $item['required']) {
				$requiredLab = '是';
			}

			$desc      = $item['comment'];
			$filedType = $item['type'];
			$str       .= "| `{$filed}` |{$desc}|`{$requiredLab}`|{$filedType}|" . PHP_EOL;
		}
		return $str;
	}

	protected function getRspMD($message) {
		$str = '';
		foreach ($message as $filed => $item) {
			$desc      = $item['comment'];
			$filedType = $item['type'];
			$str       .= "| `{$filed}` |{$desc}|{$filedType}|" . PHP_EOL;
			if (isset($item['item'])) {
				$str .= $this->getChildMD($filedType, $item['item'], $filed);
			}
		}

		return $str;
	}

	protected function getChildMD($pType, $item, $parentFiled) {
		$str = '';
		foreach ($item as $k => $v) {
			$desc  = $v['comment'];
			$fType = $v['type'];
			$filed = "{$parentFiled}.{$k}";
			if ($pType == 'array') {
				$filed = "{$parentFiled}.[i].{$k}";
			}
			$str .= "| `$filed` |{$desc}|{$fType}|" . PHP_EOL;
			if (isset($v['item'])) {
				$str .= $this->getChildMD($fType, $v['item'], $filed);
			}
		}
		return $str;
	}

	protected function sidebars() {
		$map       = [];
		$doc       = file_get_contents($this->docsPath . '/_sidebar.md');
		$parsedown = new Parsedown();
		$r         = $parsedown->parse($doc);
		$xml       = simplexml_load_string($r, 'SimpleXMLElement', LIBXML_NOCDATA);
		$lis       = $xml->xpath('/ul/li/ul/li');
		foreach ($lis as $li) {
			$html = $li->asXML();
			$dom  = new \DOMDocument;
			$dom->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'));
			$nodes = $dom->getElementsByTagName('li');
			/**
			 * @var $node \DOMNode
			 */
			foreach ($nodes as $node) {
				$href = $node->firstChild->attributes->getNamedItem('href')->textContent;
				$node->nodeValue;
				$map[$href] = $node->nodeValue;
			}
		}

		return $map;
	}
}
