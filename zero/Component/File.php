<?php

namespace Zero\Component;

class File {
	/**
	 * 判断文件是否存在
	 * Determine if a file or directory exists.
	 *
	 * @param string $path
	 * @return bool
	 */
	public static function exists($path) {
		return file_exists($path);
	}

	/**
	 * 获取文件的内容
	 * Get the contents of a file.
	 *
	 * @param string $path
	 * @param bool   $lock
	 * @return string
	 *
	 */
	public static function get($path, $lock = FALSE) {
		if (self::isFile($path)) {
			return $lock ? self::sharedGet($path) : file_get_contents($path);
		}
		return '';
	}

	/**
	 * 获取共享访问文件的内容
	 * Get contents of a file with shared access.
	 *
	 * @param string $path
	 * @return string
	 */
	public static function sharedGet($path) {
		$contents = '';
		$handle   = fopen($path, 'rb');
		if ($handle) {
			try {
				if (flock($handle, LOCK_SH)) {
					clearstatcache(TRUE, $path);
					$contents = fread($handle, self::size($path) ?: 1);
					flock($handle, LOCK_UN);
				}
			} finally {
				fclose($handle);
			}
		}

		return $contents;
	}

	/**
	 * 获取文件的MD5哈希值
	 * Get the MD5 hash of the file at the given path.
	 *
	 * @param string $path
	 * @return string
	 */
	public static function hash($path) {
		return md5_file($path);
	}

	/**
	 * 写文件
	 * Write the contents of a file.
	 *
	 * @param string $path
	 * @param string $contents
	 * @param bool   $lock
	 * @return int
	 */
	public static function put($path, $contents, $lock = FALSE) {
		if ($lock) {
			return file_put_contents($path, $contents, LOCK_EX);
		} else {
			return file_put_contents($path, $contents);
		}
	}

	/**
	 * 在文件开头追加内容
	 * Prepend to a file.
	 *
	 * @param string $path
	 * @param string $data
	 * @param bool   $lock
	 * @return int
	 */
	public static function prepend($path, $data, $lock = FALSE) {
		if (self::exists($path)) {
			return self::put($path, $data . self::get($path), $lock);
		}
		return self::put($path, $data, $lock);
	}

	/**
	 * 在文件开头追加内容+换行
	 * Prepend to a file.
	 *
	 * @param string $path
	 * @param string $data
	 * @param bool   $lock
	 * @return int
	 */
	public static function prependLn($path, $data, $lock = FALSE) {
		if (self::exists($path)) {
			return self::put($path, $data . PHP_EOL . self::get($path), $lock);
		}
		return self::put($path, $data . PHP_EOL, $lock);
	}

	/**
	 * 追加写文件
	 * Append to a file.
	 *
	 * @param string $path
	 * @param string $data
	 * @param bool   $lock
	 * @return int
	 */
	public static function append($path, $data, $lock = FALSE) {
		if ($lock) {
			return file_put_contents($path, $data, FILE_APPEND | LOCK_EX);
		} else {
			return file_put_contents($path, $data, FILE_APPEND);
		}
	}

	/**
	 * 追加写文件+换行
	 * Append to a file.
	 *
	 * @param string $path
	 * @param string $data
	 * @param bool   $lock
	 * @return int
	 */
	public static function appendLn($path, $data, $lock = FALSE) {
		if ($lock) {
			return file_put_contents($path, $data . PHP_EOL, FILE_APPEND | LOCK_EX);
		} else {
			return file_put_contents($path, $data . PHP_EOL, FILE_APPEND);
		}
	}

	/**
	 * 转换成json格式追加写文件+换行
	 * Append to a file.
	 *
	 * @param string $path
	 * @param string $data
	 * @param bool   $lock
	 * @return int
	 */
	public static function appendToJsonLn($path, $data, $lock = FALSE) {
		if ($lock) {
			return file_put_contents($path, json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . PHP_EOL, FILE_APPEND | LOCK_EX);
		} else {
			return file_put_contents($path, json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . PHP_EOL, FILE_APPEND);
		}
	}

	/**
	 * 遍历文件内容
	 * @param string $path
	 * @param int    $iNum 单次数量
	 * @return \Generator
	 */
	public static function fileIterator($path, $iNum = 1000) {

		if (file_exists($path)) {
			$file  = fopen($path, "r");
			$i     = 0;
			$items = [];
			while (!feof($file)) {
				$item = trim(fgets($file));
				if ($item) {
					$items[] = $item;
					if (count($items) >= $iNum) {
						yield $items;
						$items = [];
					}
				}
				$i++;
			}
			if ($items) {
				yield $items;
			}
		}
	}

	/**
	 * 分页获取文件内容
	 * @param string $path
	 * @param int    $page
	 * @param int    $size
	 * @return array
	 */
	public static function fileLimit($path, $page = 1, $size = 20) {
		if (file_exists($path)) {
			$start = ($page - 1) * $size;
			$file  = fopen($path, "r");
			$i     = 0;
			$ii    = 0;
			$items = [];
			while (!feof($file)) {
				$item = trim(fgets($file));
				if ($i >= $start && $item) {
					$items[] = $item;
					if ($ii >= $size) {
						break;
					}
					$ii++;
				}
				$i++;
			}

			return $items;
		}
		return [];
	}

	/**
	 * 删除给定路径的文件或文件数组
	 * Delete the file at a given path.
	 *
	 * @param string|array $paths
	 * @return bool
	 */
	public static function delete($paths) {
		$paths   = is_array($paths) ? $paths : func_get_args();
		$success = TRUE;
		foreach ($paths as $path) {
			if (!@unlink($path)) {
				$success = FALSE;
			}
		}
		return $success;
	}

	/**
	 * 移动文件
	 * Move a file to a new location.
	 *
	 * @param string $path
	 * @param string $target
	 * @return bool
	 */
	public static function move($path, $target) {
		return rename($path, $target);
	}

	/**
	 * 拷贝文件
	 * Copy a file to a new location.
	 *
	 * @param string $path
	 * @param string $target
	 * @return bool
	 */
	public static function copy($path, $target) {
		return copy($path, $target);
	}

	/**
	 * 创建到目标文件或目录的硬链接
	 * Create a hard link to the target file or directory.
	 *
	 * @param string $target
	 * @param string $link
	 * @return bool
	 */
	public static function link($target, $link) {
		if (!windows_os()) {
			return symlink($target, $link);
		}
		$mode = self::isDir($target) ? 'J' : 'H';
		exec("mklink /{$mode} \"{$link}\" \"{$target}\"");
		return TRUE;
	}

	/**
	 * 从文件路径中提取文件名（文件名不包括后缀）
	 * Extract the file name from a file path.
	 *
	 * @param string $path
	 * @return string
	 */
	public static function name($path) {
		return pathinfo($path, PATHINFO_FILENAME);
	}

	/**
	 * 从文件路径中提取尾部名称（文件名包括后缀）
	 * Extract the trailing name component from a file path.
	 *
	 * @param string $path
	 * @return string
	 */
	public static function basename($path) {
		return pathinfo($path, PATHINFO_BASENAME);
	}

	/**
	 * 从文件路径中提取父目录
	 * Extract the parent directory from a file path.
	 *
	 * @param string $path
	 * @return string
	 */
	public static function dirname($path) {
		return pathinfo($path, PATHINFO_DIRNAME);
	}

	/**
	 * 从文件路径中提取文件扩展名
	 * Extract the file extension from a file path.
	 *
	 * @param string $path
	 * @return string
	 */
	public static function extension($path) {
		return pathinfo($path, PATHINFO_EXTENSION);
	}

	/**
	 * 获取给定文件的文件类型(fifo，char，dir，block，link，file 和 unknown)
	 * Get the file type of a given file.
	 *
	 * @param string $path
	 * @return string
	 */
	public static function type($path) {
		return filetype($path);
	}

	/**
	 * 获取给定文件的MIME类型 (例如 text/plain 或 application/octet-stream)
	 * Get the mime-type of a given file.
	 *
	 * @param string $path
	 * @return string|false
	 */
	public static function mimeType($path) {
		return finfo_file(finfo_open(FILEINFO_MIME_TYPE), $path);
	}

	/**
	 * 获取给定文件的文件大小(字节数)
	 * Get the file size of a given file.
	 *
	 * @param string $path
	 * @return int
	 */
	public static function size($path) {
		return filesize($path);
	}

	/**
	 * 获取文件的最后修改时间
	 * Get the file's last modification time.
	 *
	 * @param string $path
	 * @return int
	 */
	public static function lastModified($path) {
		return filemtime($path);
	}

	/**
	 * 判断是否是文件夹
	 * Determine if the given path is a directory.
	 *
	 * @param string $directory
	 * @return bool
	 */
	public static function isDir($directory) {
		return is_dir($directory);
	}

	/**
	 * 判断路径是否可读
	 * Determine if the given path is readable.
	 *
	 * @param string $path
	 * @return bool
	 */
	public static function isReadable($path) {
		return is_readable($path);
	}

	/**
	 * 判断路径是否可写
	 * Determine if the given path is writable.
	 *
	 * @param string $path
	 * @return bool
	 */
	public static function isWritable($path) {
		return is_writable($path);
	}

	/**
	 * 判断是否是文件
	 * Determine if the given path is a file.
	 *
	 * @param string $file
	 * @return bool
	 */
	public static function isFile($file) {
		return is_file($file);
	}

	/**
	 * 查找匹配的路径
	 * Find path names matching a given pattern.
	 *
	 * @param string $pattern
	 * @param int    $flag 1:file 2:dir
	 * @return array
	 */
	public static function glob($pattern, $flag = 0) {
		$paths = glob($pattern);
		if ($flag == 1) {
			$files = [];
			foreach ($paths as $path) {
				if (self::isFile($path)) {
					$files[] = $path;
				}
			}
			return $files;
		} elseif ($flag == 2) {
			$dirs = [];
			foreach ($paths as $path) {
				if (self::isDir($path)) {
					$dirs[] = $path;
				}
			}
			return $dirs;
		}
		return $paths;
	}

	/**
	 * 获取目录中的所有文件
	 * Get an array of all files in a directory.
	 *
	 * @param string $directory
	 * @param bool   $asName 结果为文件名(默认为路径)
	 * @return array
	 */
	public static function files($directory, $asName = FALSE) {

		if (is_dir($directory)) {
			$iterator = new \FilesystemIterator($directory, $asName ? \FilesystemIterator::KEY_AS_FILENAME : \FilesystemIterator::KEY_AS_PATHNAME);
			$files    = [];
			foreach ($iterator as $key => $item) {
				if (self::isFile($item->getPathname())) {
					$files[] = $key;
				}
			}
			return $files;
		}
		return [];
	}

	/**
	 * 获取目录中的所有文件（递归）
	 * Get all of the files from the given directory (recursive).
	 *
	 * @param string $directory
	 * @param bool   $asName 结果为文件名(默认为路径)
	 * @param int    $flag   1:不显示空目录 2:只取文件列表不需要树结构
	 * @return array
	 */
	public static function allFiles($directory, $asName = FALSE, $flag = 0) {
		if (is_dir($directory)) {
			$iterator = new \FilesystemIterator($directory, $asName ? \FilesystemIterator::KEY_AS_FILENAME : \FilesystemIterator::KEY_AS_PATHNAME);
			$files    = [];
			foreach ($iterator as $key => $item) {
				if (self::isDir($item->getPathname())) {
					$_files = self::allFiles($item->getPathname(), $asName, $flag);
					if ($flag == 2) {
						$files = array_merge($files, $_files);
					} else {
						if ($flag == 0 || $_files) {
							$files[$key] = $_files;
						}
					}
				} else {
					if ($flag == 2) {
						$files[] = $key;
					} else {
						$files[$key] = $key;
					}
				}
			}
			return $files;
		}
		return [];
	}

	/**
	 * 获取目录中的所有目录
	 * Get all of the directories within a given directory.
	 *
	 * @param string $directory
	 * @param bool   $asName 结果为文件名(默认为路径)
	 * @return array
	 */
	public static function directories($directory, $asName = FALSE) {
		if (is_dir($directory)) {
			$iterator    = new \FilesystemIterator($directory, $asName ? \FilesystemIterator::KEY_AS_FILENAME : \FilesystemIterator::KEY_AS_PATHNAME);
			$directories = [];
			foreach ($iterator as $key => $item) {
				if (self::isDir($item->getPathname())) {
					$directories[] = $key;
				}
			}

			return $directories;
		}
		return [];
	}

	/**
	 * 创建文件夹
	 * Create a directory.
	 *
	 * @param string $path
	 * @param int    $mode
	 * @param bool   $recursive 是否递归
	 * @param bool   $force
	 * @return bool
	 */
	public static function makeDir($path, $mode = 0755, $recursive = TRUE, $force = FALSE) {
		if ($force) {
			return @mkdir($path, $mode, $recursive);
		}
		return mkdir($path, $mode, $recursive);
	}

	/**
	 * 移动文件夹
	 * Move a directory.
	 *
	 * @param string $from
	 * @param string $to
	 * @param bool   $overwrite
	 * @return bool
	 */
	public static function moveDir($from, $to, $overwrite = FALSE) {
		if ($overwrite && self::isDir($to)) {
			if (!self::deleteDir($to)) {
				return FALSE;
			}
		}
		return @rename($from, $to) === TRUE;
	}

	/**
	 * 将目录从一个位置复制到另一个位置
	 * Copy a directory from one location to another.
	 *
	 * @param string $directory
	 * @param string $destination
	 * @param int    $options
	 * @return bool
	 */
	public static function copyDir($directory, $destination, $options = NULL) {
		if (!self::isDir($directory)) {
			return FALSE;
		}

		$options = $options ?: \FilesystemIterator::SKIP_DOTS;

		if (!is_dir($destination)) {
			self::makeDir($destination, 0777, TRUE);
		}

		$iterator = new \FilesystemIterator($directory, $options);
		foreach ($iterator as $key => $item) {
			$target = $destination . '/' . $item->getBasename();
			if ($item->isDir()) {
				$path = $item->getPathname();
				if (!self::copyDir($path, $target, $options)) {
					return FALSE;
				}
			} else {
				if (!self::copy($item->getPathname(), $target)) {
					return FALSE;
				}
			}
		}

		return TRUE;
	}

	/**
	 * 删除文件夹
	 * Recursively delete a directory.
	 *
	 * The directory itself may be optionally preserved.
	 *
	 * @param string $directory
	 * @param bool   $preserve 是否保留文件夹本身
	 * @return bool
	 */
	public static function deleteDir($directory, $preserve = FALSE) {
		if (!is_dir($directory)) {
			return FALSE;
		}

		$iterator = new \FilesystemIterator($directory);
		foreach ($iterator as $key => $item) {
			if ($item->isDir() && !$item->isLink()) {
				self::deleteDir($item->getPathname());
			} else {
				self::delete($item->getPathname());
			}
		}
		if (!$preserve) {
			@rmdir($directory);
		}
		return TRUE;
	}

	/**
	 * 清空文件夹
	 * Empty the specified directory of all files and folders.
	 *
	 * @param string $directory
	 * @return bool
	 */
	public static function cleanDir($directory) {
		return self::deleteDir($directory, TRUE);
	}
}

