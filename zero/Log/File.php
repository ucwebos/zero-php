<?php

namespace Zero\Log;

use Zero\Config;

class File implements WriterInterface {
	private $path;
	private $file;
	private $fp;
	private $date;

	public function __construct() {
		$this->path = Config::get('LOGGER.path') ?? ROOT_PATH . '/runtime/log';
	}

	/**
	 * @param $file
	 * @return bool
	 */
	public function setFile($file) {
		if (file_exists($file)) {
			if (!is_writable($file)) {
				return FALSE;
			}
			$this->file = $file;
			$this->fp   = NULL;
			return TRUE;
		}
		if (strpos($file, '/') === FALSE) {
			$this->file = $this->path . '/' . $file;
			$this->fp   = NULL;
			return TRUE;
		}
		$dir = dirname($file);
		if (file_exists($dir) && is_writable($dir)) {
			$this->file = $file;
			$this->fp   = NULL;
			return TRUE;
		}
		return FALSE;
	}

	public function write($level, $tag, $message, array $context = []) {
		if (!$this->fp || !is_resource($this->fp)|| date('Ymd') != $this->date) {
			$this->fp = fopen($this->getFile(), "a+");
		}
		$data = [
			'level'   => $level,
			'tag'     => $tag,
			'message' => $message,
			'context' => $context,
		];
		$str  = date('Y-m-d H:i:s') . ' --- ' . json_encode($data) . PHP_EOL;
		fwrite($this->fp, $str);
		fclose($this->fp);
	}

	private function getFile() {
		$this->date = date('Ymd');
		if ($this->file) {
			return $this->file;
		}
		return $this->path . '/' . date('Ymd') . '.log';
	}
}
