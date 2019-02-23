<?php

namespace Swoft\Console\Advanced\Formatter;

use Swoft\Console\Advanced\MessageFormatter;
use Swoft\Console\Helper\FormatUtil;
use Swoft\Console\Helper\Show;
use Toolkit\Cli\Cli;

/**
 * Class Tree
 * @package Swoft\Console\Advanced\Formatter
 */
class Tree extends MessageFormatter
{
    /** @var int */
    private $counter = 0;

    /** @var bool */
    private $started = false;

    /**
     * Render data like tree
     * ├ ─ ─
     * └ ─
     * @param array $data
     * @param array $opts
     */
    public static function show(array $data, array $opts = []): void
    {
        static $counter = 0;
        static $started = 1;

        if ($started) {
            $started = 0;
            $opts    = \array_merge([
                // 'char' => Cli::isSupportColor() ? '─' : '-', // ——
                'char'        => '-',
                'prefix'      => Cli::isSupportColor() ? '├' : '|',
                'leftPadding' => '',
            ], $opts);

            $opts['_level']   = 1;
            $opts['_is_main'] = true;

            Show::startBuffer();
        }

        foreach ($data as $key => $value) {
            if (\is_scalar($value)) {
                $counter++;
                $leftString = $opts['leftPadding'] . \str_pad($opts['prefix'], $opts['_level'] + 1, $opts['char']);

                Show::write($leftString . ' ' . FormatUtil::typeToString($value));
            } elseif (\is_array($value)) {
                $newOpts             = $opts;
                $newOpts['_is_main'] = false;
                $newOpts['_level']++;

                self::show($value, $newOpts);
            }
        }

        if ($opts['_is_main']) {
            Show::write('node count: ' . $counter);
            // var_dump('f');
            Show::flushBuffer();

            // reset.
            $counter = $started = 0;
        }
    }
}
