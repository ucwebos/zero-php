<?php

namespace Zero\Console\Job\Cron;

class CronRule {
	protected $type;
	protected $number = 0;
	protected $hour   = 0;
	protected $min    = 0;
	protected $sec    = 0;
	protected $start  = '';
	protected $end    = '';
	protected $lock   = FALSE;

	public function __construct($number) {
		$this->number = $number;
	}

	public function toString() {
		$data = [
			'lock'   => $this->lock,
			'type'   => $this->type,
			'number' => $this->number,
			'hour'   => $this->hour,
			'min'    => $this->min,
			'sec'    => $this->sec,
			'start'  => $this->start,
			'end'    => $this->end,
		];
		return json_encode($data);
	}

	public function lock() {
		$this->lock = TRUE;
	}
}

class Day extends CronRule {
	protected $type = 'day';

	/**
	 * H:i:s
	 * @param string $at
	 * @return $this
	 */
	public function at(string $at = "0:0:0") {
		list($this->hour, $this->min, $this->sec) = $this->parseAt($at);
		return $this;
	}

	/**
	 * H:i:s ~ H:i:s
	 * @param $start
	 * @param $end
	 * @return $this
	 */
	public function between($start, $end) {
		$this->start = implode(':', $this->parseAt($start));
		$this->end   = implode(':', $this->parseAt($end));
		return $this;
	}

	/**
	 * @param $at
	 * @return array
	 */
	private function parseAt($at) {
		$arr   = [0, 0, 0];
		$items = explode(':', $at);
		foreach ($items as $k => $item) {
			$arr[$k] = abs(intval($item));
		}
		$arr[0] = $arr[0] > 23 ? 23 : $arr[0];
		$arr[1] = $arr[1] > 59 ? 59 : $arr[1];
		$arr[2] = $arr[2] > 59 ? 59 : $arr[2];

		return $arr;
	}
}

class Hour extends CronRule {
	protected $type = 'hour';

	/**
	 * 0 ～ 59
	 * @param int $atMin
	 * @return $this
	 */
	public function atMin(int $atMin) {
		$atMin     = abs($atMin);
		$this->min = $atMin > 59 ? 59 : $atMin;
		return $this;
	}

	/**
	 * @param int $atSec
	 * @return $this
	 */
	public function atSec(int $atSec) {
		$atSec     = abs($atSec);
		$this->sec = $atSec > 59 ? 59 : $atSec;
		return $this;
	}
}

class Min extends CronRule {
	protected $type = 'min';

	/**
	 * 0 ～ 59
	 * @param int $atSec
	 * @return $this
	 */
	public function atSec(int $atSec) {
		$atSec     = abs($atSec);
		$this->sec = $atSec > 59 ? 59 : $atSec;
		return $this;
	}
}

class Sec extends CronRule {
	protected $type = 'sec';
}

class Cron {
	/**
	 * 每「多少」天
	 * @param int $day 天数|默认每天
	 * @return Day
	 */
	public static function day(int $day = 1) {
		return new Day($day);
	}

	/**
	 * 每「多少」小时
	 * @param int $hour 小时数|默认每小时
	 * @return Hour
	 */
	public static function hour(int $hour = 1) {
		return new Hour($hour);
	}

	/**
	 * 每「多少」分钟
	 * @param int $min 分钟数|默认每分钟
	 * @return Min
	 */
	public static function min(int $min = 1) {
		return new Min($min);
	}

	/**
	 * 每「多少」秒
	 * @param int $sec 秒数|默认每秒
	 * @return Sec
	 */
	public static function sec(int $sec = 1) {
		return new Sec($sec);
	}
}
