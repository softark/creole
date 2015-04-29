<?php
/**
 * @copyright Copyright (c) 2015 Nobuo Kihara
 * @license https://github.com/softark/creole/blob/master/LICENSE
 * @link https://github.com/softark/creole#readme
 */

namespace softark\creole\block;

/**
 * Adds the raw html blocks
 */
trait RawHtmlTrait
{
	/**
	 * @var bool whether to support raw html blocks.
	 * Defaults to `false`.
	 */
	public $useRawHtml = false;

	/**
	 * @var string cache path for the HTMLPurifier.
	 * Defaults to null, meaning the default inner cache path.
	 */
	public $htmlPurifierCachePath = null;

	/**
	 * identify a line as the beginning of a raw html block.
	 */
	protected function identifyRawHtml($line)
	{
		return $this->useRawHtml && (strcmp(rtrim($line), '<<<') === 0);
	}

	/**
	 * Consume lines for a raw html block
	 */
	protected function consumeRawHtml($lines, $current)
	{
		// consume until >>>
		$content = [];
		for ($i = $current + 1, $count = count($lines); $i < $count; $i++) {
			$line = rtrim($lines[$i]);
			if (strcmp($line, '>>>') !== 0) {
				$content[] = $line;
			} else {
				break;
			}
		}
		$block = [
			'rawHtml',
			'content' => implode("\n", $content),
		];
		return [$block, $i];
	}

	/**
	 * Renders a raw html block
	 */
	protected function renderRawHtml($block)
	{
		$config = \HTMLPurifier_Config::createDefault();
		if (isset($this->htmlPurifierCachePath)) {
			$config->set('Cache.SerializerPath', $this->htmlPurifierCachePath);
		}
		$purifier = new \HTMLPurifier($config);
		$clean_html = $purifier->purify($block['content']);

		return $clean_html . "\n";
	}
}
