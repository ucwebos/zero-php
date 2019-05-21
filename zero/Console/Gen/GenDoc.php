<?php
/**
 * Created by PhpStorm.
 * User: skj
 * Date: 2018/12/7
 * Time: 上午11:51
 */

namespace Zero\Console\Gen;

/**
 * 文档生成器
 * Class GenDoc
 * @package Zero\Console\Gen
 */
class GenDoc implements GenInterface {
	//配置的变量
	protected $protoPath  = '';
	protected $pluginPath = '';
	protected $outPath    = '';
	protected $testAPI    = '';
	protected $docPath    = '';
	//运行时变量
	protected $data             = [];
	protected $types            = [];
	protected $messages         = [];
	protected $methods          = [];
	protected $requestMessages  = [];
	protected $responseMessages = [];


	public function gen($args) {

		$table    = $args['proto'] ?? '';
		$database = $args['db'] ?? '';
		if (!$database || !$table) {
			die("Please input args like db={database} t={table or ALL} -o={outPath} ");
		}

		if (!$this->protoPath || !$this->pluginPath||$this->outPath||$this->docPath){
			die('请设置正确的配置 ! ');
		}

		$proto = $args['proto'] ?? '';
		if (!$proto) {
			die('请设置正确的proto文件路径 !');
		}
		if (strpos($proto, '/') != 0) {
			$proto = ROOT_PATH . '/' . $proto;
		}
		if (is_dir($proto)) {
			$proto = $proto . '*.proto';
		}
		echo $proto . PHP_EOL;

		$this->parseProto($proto);

		$doc = '';
		//拼header头
		$header = ROOT_PATH . '/docs/header.md';
		if (file_exists($header)) {
			$doc .= file_get_contents($header);
		}
		$date = date('Ymd');
		$doc  .= <<<DOC
# API接口文档—V{$date}

> 根据Protobuf定义生成


DOC;
		foreach ($this->methods as $item) {
			$doc .= $this->itemTPL($item);
		}

		$doc = $doc . PHP_EOL . "# 附录[对象格式]" . PHP_EOL;
		$doc .= $this->messagesMD();

		//生成md文件
		file_put_contents(ROOT_PATH . '/docs/doc.md', $doc);
	}

	protected function itemTPL($item) {

		$description = $item['description'] ?? '';

		preg_match('#\[(.*)\]#', $description, $hm);

		$uriStr = $hm[1] ?? '';

		$method = 'POST';
		$route  = $uriStr;
		$url    = $this->testAPI = $uriStr;
		$module = '';
		$name   = $item['name'] ?? '';

		$desc = trim(preg_replace('#\[(.*)\]#', '', $description));
		if ($desc) {
			$name .= '[' . $desc . ']';
		}

		$requestType              = $item['requestType'] ?? '';
		$this->requestMessages[]  = $requestType;
		$responseType             = $item['responseType'] ?? '';
		$this->responseMessages[] = $responseType;
		if (in_array($requestType, $this->types)) {
			die('requestType must be a message !');
		}

		$paramsMD = $this->getParamsMD($requestType);
		$rspMD    = $this->getRspMD($responseType);
//		$rspJSON  = $this->rspJSON($responseType);

		$tpl = <<<DOC

## {$name}

> 路径：{$route}

* 测试地址：($url)
* 请求方式：`{$method}`
* 请求参数：


|字段|说明|是否必须|类型|
|---|---|---|---|
{$paramsMD}


* 响应数据： 

|字段|说明|类型|
|---|---|---|
{$rspMD}


DOC;

		//``` json
		//{$rspJSON}
		//```
		if ($module) {
			$tpl = "# {$module}" . PHP_EOL . $tpl;
		}
		return $tpl;
	}

	protected function rspJSON($requestType) {

	}

	protected function getParamsMD($requestType) {
		$message = $this->messages[$requestType];
		$str     = '';
		foreach ($message as $item) {
			$description = $item['description'];
			$requiredLab = "否";
			if (strpos($description, '[required]') !== FALSE) {
				$requiredLab = '是';
			}
			$filed     = $item['name'];
			$desc      = str_replace('[required]', '', $description);
			$filedType = $item['type'];
			$label     = $item['label'] ?? '';
			if ($label != '' && $label == "repeated") {
				//处理map
				if ($mapType = $this->parseMap($filedType)) {
					$filedType = $mapType;
				} else {
					$filedType = "array[$filedType]";
				}
			}
			$str .= "| `{$filed}` |{$desc}|`{$requiredLab}`|{$filedType}|" . PHP_EOL;
		}

		return $str;
	}

	protected function getRspMD($requestType) {
		$message = $this->messages[$requestType];
		$str     = '';
		foreach ($message as $item) {
			$description = $item['description'];
			$filed       = $item['name'];
			$desc        = str_replace('[required]', '', $description);
			$filedType   = $item['type'];
			$label       = $item['label'] ?? '';
			if ($label != '' && $label == "repeated") {
				//处理map
				if ($mapType = $this->parseMap($filedType)) {
					$filedType = $mapType;
				} else {
					$filedType = "array[$filedType]";
				}
			}
			$str .= "| `{$filed}` |{$desc}|{$filedType}|" . PHP_EOL;
		}

		return $str;
	}

	protected function messagesMD() {

		$md = '';
		foreach ($this->messages as $k => $message) {
			if (in_array($k, $this->requestMessages) || in_array($k, $this->responseMessages)) {
				continue;
			}
			if ($this->parseMap($k)) {
				continue;
			}
			$rspMD = $this->getRspMD($k);
			$md    .= <<<DOC
## {$k} 

|字段|说明|类型|
|---|---|---|
{$rspMD}

DOC;
		}
		return $md;
	}

	protected function parseMap($filedType) {
		if (in_array($filedType, $this->types)) {
			return '';
		}
		if (!strpos($filedType, 'Entry')) {
			return '';
		}
		$fields = $this->messages[$filedType];
		if (count($fields) != 2) {
			return '';
		}
		if ($fields[0]['name'] == 'key') {
			$tK = $fields[0]['type'] ?? '';
			$tV = $fields[1]['type'] ?? '';
			return " map<{$tK}, $tV>";
		}
	}

	protected function parseNote() {

	}

	protected function parseProto($proto) {

		$cmd = "protoc \
  -I=$this->protoPath \
  --plugin=protoc-gen-doc=$this->pluginPath \
  --doc_out=$this->outPath \
  --doc_opt=json,data.json \
  $proto";
		exec($cmd);

		//解析json
		$jsonFile   = $this->outPath . 'data.json';
		$jsonStr    = file_get_contents($jsonFile);
		$this->data = json_decode($jsonStr, TRUE);

		$scalarValueTypes = $this->data['scalarValueTypes'] ?? [];
		$this->types      = array_column($scalarValueTypes, 'protoType');

		$messages = $this->data['files'][0]['messages'] ?? [];

		foreach ($messages as $message) {
			// todo map
			$this->messages[$message['name']] = $message['fields'];
		}

		$methods = $this->data['files'][0]['services'][0]['methods'] ?? [];
		foreach ($methods as $method) {
			$this->methods[$method['name']] = $method;
		}
	}
}
