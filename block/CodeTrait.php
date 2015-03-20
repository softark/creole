<?php
/**
 * @copyright Copyright (c) 2015 Nobuo Kihara
 * @license https://github.com/softark/creole/blob/master/LICENSE
 * @link https://github.com/softark/creole#readme
 */

namespace softark\creole\block;

/**
 * Adds the code blocks
 */
trait CodeTrait
{
	/**
	 * identify a line as the beginning of a code block.
	 */
	protected function identifyCode($line)
	{
		return strcmp(rtrim($line), '{{{') === 0;
	}

	/**
	 * Consume lines for a code block
	 */
	protected function consumeCode($lines, $current)
	{
		// consume until }}}
		$content = [];
		for ($i = $current + 1, $count = count($lines); $i < $count; $i++) {
			$line = rtrim($lines[$i]);
			if (strcmp($line, '}}}') !== 0) {
				$content[] = $line;
			} else {
				break;
			}
		}
		$block = [
			'code',
			'content' => implode("\n", $content),
		];
		return [$block, $i];
	}

	/**
	 * Renders a code block
	 */
	protected function renderCode($block)
	{
		return "<pre><code>" . htmlspecialchars($block['content'] . "\n", ENT_NOQUOTES | ENT_SUBSTITUTE, 'UTF-8') . "</code></pre>\n";
	}
}
