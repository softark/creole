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
     * @var callable output filter
     * Defaults to null.
     */
	public $rawHtmlFilter = null;

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
        $output = $block['content'];
        if (is_callable($this->rawHtmlFilter, true)) {
            $output = call_user_func($this->rawHtmlFilter, $output);
        }
		return $output . "\n";
	}
}
