<?php
/**
 * @copyright Copyright (c) 2015 Nobuo Kihara
 * @license https://github.com/softark/creole/blob/master/LICENSE
 * @link https://github.com/softark/creole#readme
 */

namespace softark\creole\block;

/**
 * Adds the headline blocks
 */
trait HeadlineTrait
{
    /**
     * identify a line as a headline
     * A headline always starts with a '=', with leading white spaces permitted.
     */
    protected function identifyHeadline($line)
    {
        $line = ltrim($line);
        return (
            // heading with =
            isset($line[0]) && $line[0] === '='
        );
    }

    /**
     * Consume lines for a headline
     */
    protected function consumeHeadline($lines, $current)
    {
        $line = trim($lines[$current]);
        $level = 1;
        while (isset($line[$level]) && $line[$level] === '=' && $level < 6) {
            $level++;
        }
        $block = [
            'headline',
            // parse headline content. The leading and trailing '='s are removed.
            'content' => $this->parseInline(trim($line, " \t=")),
            'level' => $level,
        ];
        return [$block, $current];
    }

    /**
     * Renders a headline
     */
    protected function renderHeadline($block)
    {
        $tag = 'h' . $block['level'];
        return "<$tag>" . $this->renderAbsy($block['content']) . "</$tag>\n";
    }

    abstract protected function parseInline($text);

    abstract protected function renderAbsy($absy);
}
