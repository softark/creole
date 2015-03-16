<?php
/**
 * @copyright Copyright (c) 2015 Nobuo Kihara
 * @license https://github.com/softark/creole/blob/master/LICENSE
 * @link https://github.com/softark/creole#readme
 */

namespace softark\creole\inline;

/**
 * Adds inline code elements
 */
trait CodeTrait
{
	/**
	 * Parses an inline code span: {{{ ... }}}
	 * @marker {{{
	 */
	protected function parseInlineCode($text)
	{
		if (preg_match('/^{{{(.*?}*)}}}/s', $text, $matches)) {
			return [
				[
					'inlineCode',
					$matches[1],
				],
				strlen($matches[0])
			];
		}
		return [['text', $text[0]], 1];
	}

	protected function renderInlineCode($block)
	{
		return '<code>' . htmlspecialchars($block[1], ENT_NOQUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</code>';
	}
}
