<?php
/**
 * @copyright Copyright (c) 2015 Nobuo Kihara
 * @license https://github.com/softark/creole/blob/master/LICENSE
 * @link https://github.com/softark/creole#readme
 */

namespace softark\creole\block;

/**
 * Adds horizontal rules
 */
trait RuleTrait
{
    /**
     * identify a line as a horizontal rule.
     * The exact string of '----', with possible white spaces before and/or after it
     */
    protected function identifyHr($line)
    {
        return preg_match('/^\s*----\s*$/', $line);
    }

    /**
     * Consume a horizontal rule
     */
    protected function consumeHr($lines, $current)
    {
        return [['hr'], $current];
    }

    /**
     * Renders a horizontal rule
     */
    protected function renderHr($block)
    {
        return $this->html5 ? "<hr>\n" : "<hr />\n";
    }

} 