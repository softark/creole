<?php
/**
 * @copyright Copyright (c) 2015 Nobuo Kihara
 * @license https://github.com/softark/creole/blob/master/LICENSE
 * @link https://github.com/softark/creole#readme
 */

namespace softark\creole\block;

/**
 * Adds the list blocks
 */
trait ListTrait
{
	/**
	 * @var int the current depth of the nested lists
	 */
	private $_listDepth = 1;

	/**
	 * @var array the types of the nested lists
	 */
	private $_nestedListTypes = [];

	/**
	 * Identify a line as the beginning of an ordered list.
	 * It should start with '#', with leading white spaces permitted.
	 */
	protected function identifyOl($line)
	{
		return preg_match('/^\s*#{' . $this->_listDepth . '}[^#]+/', $line);
	}

	/**
	 * Identify a line as the beginning of an unordered list.
	 * It should start with '*', with leading white spaces permitted.
	 */
	protected function identifyUl($line)
	{
		return preg_match('/^\s*\*{' . $this->_listDepth . '}[^\*]+/', $line);
	}

	/**
	 * check if a line is an item that belongs to a parent list
	 * @param $line
	 * @return bool true if the line is an item of a parent list
	 */
	protected function isParentItem($line)
	{
		if ($this->_listDepth === 1 || ($marker = $line[0]) !== '*' && $marker !== '#') {
			return false;
		}
		$depthMax = $this->_listDepth - 1;
		if (preg_match('/^(#{1,' . $depthMax . '})[^#]+/', $line, $matches)) {
			return $this->_nestedListTypes[strlen($matches[1])] === 'ol';
		}
		if (preg_match('/^(\*{1,' . $depthMax . '})[^\*]+/', $line, $matches)) {
			return $this->_nestedListTypes[strlen($matches[1])] === 'ul';
		}
		return false;
	}

	/**
	 * check if a line is an item that belongs to a sibling list
	 * @param $line
	 * @return bool true if the line is an item of a sibling list
	 */
	protected function isSiblingItem($line)
	{
		$siblingMarker = $this->_nestedListTypes[$this->_listDepth] == 'ol' ? '*' : '#';
		if ($line[0] !== $siblingMarker) {
			return false;
		}
		return
			($siblingMarker === '#' && preg_match('/^#{' . $this->_listDepth . '}[^#]+/', $line)) ||
			($siblingMarker === '*' && preg_match('/^\*{' . $this->_listDepth . '}[^\*]+/', $line));
	}

	/**
	 * Consume lines for an ordered list
	 */
	protected function consumeOl($lines, $current)
	{
		// consume until newline

		$block = [
			'list',
			'list' => 'ol',
			'items' => [],
		];
		return $this->consumeList($lines, $current, $block, 'ol');
	}

	/**
	 * Consume lines for an unordered list
	 */
	protected function consumeUl($lines, $current)
	{
		// consume until newline

		$block = [
			'list',
			'list' => 'ul',
			'items' => [],
		];
		return $this->consumeList($lines, $current, $block, 'ul');
	}

	private function consumeList($lines, $current, $block, $type)
	{
		$this->_nestedListTypes[$this->_listDepth] = $type;
		$item = 0;
		$pattern = $type === 'ul' ? '/^\*{' . $this->_listDepth . '}([^\*]+.*|)$/' : '/^#{' . $this->_listDepth . '}([^#]+.*|)$/';
		for ($i = $current, $count = count($lines); $i < $count; $i++) {
			$line = ltrim($lines[$i]);
			// A list ends with a blank new line, other block elements, a parent item, or a sibling list.
			if ($line === '' ||
				$this->identifyHeadline($line) ||
                $this->identifyHr($line) ||
				$this->identifyTable($line) ||
				$this->identifyCode($line) ||
				$this->isParentItem($line) ||
				$this->isSiblingItem($line)
			) {
				// list ended
				$i--;
				break;
			}
			if (preg_match($pattern, $line)) {
				// match list marker on the beginning of the line ... the next item begins
				$line = ltrim(substr($line, $this->_listDepth));
				$block['items'][++$item][] = $line;
			} else {
				// child list?
				$this->_listDepth++;
				if ($this->identifyOl($line)) {
					list($childBlock, $i) = $this->consumeOl($lines, $i);
					$block['items'][$item][] = $childBlock;
				} elseif ($this->identifyUl($line)) {
					list($childBlock, $i) = $this->consumeUl($lines, $i);
					$block['items'][$item][] = $childBlock;
				} else {
					// the continuing content of the current item
					$line = ltrim($line);
					$block['items'][$item][] = $line;
				}
				$this->_listDepth--;
			}
		}

		foreach($block['items'] as $itemId => $itemLines) {
			$content = [];
			$texts = [];
			foreach ($itemLines as $line) {
				if (!isset($line['list'])) {
					$texts[] = $line;
				} else {
					// child list
					if (!empty($texts)) {
						// text before child list
						$content = array_merge($content, $this->parseInline(implode("\n", $texts)));
						$texts = [];
					}
					$content[] = $line;
				}
			}
			if (!empty($texts)) {
				$content = array_merge($content, $this->parseInline(implode("\n", $texts)));
			}
			$block['items'][$itemId] = $content;
		}

		return [$block, $i];
	}

	/**
	 * Renders a list
	 */
	protected function renderList($block)
	{
		$type = $block['list'];
		$output = "<$type>\n";
		foreach ($block['items'] as $item => $itemLines) {
			$output .= '<li>' . $this->renderAbsy($itemLines). "</li>\n";
		}
		return $output . "</$type>\n";
	}

	abstract protected function parseInline($text);
	abstract protected function renderAbsy($absy);
    abstract protected function identifyHeadline($line);
    abstract protected function identifyHr($line);
    abstract protected function identifyTable($line);
    abstract protected function identifyCode($line);
}
