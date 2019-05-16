<?php

namespace Zero\Log;

Interface WriterInterface {
	public function write($level, $tag, $message, array $context = []);
}
