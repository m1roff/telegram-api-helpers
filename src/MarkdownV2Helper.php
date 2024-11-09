<?php

declare(strict_types=1);

namespace M1roff\Telegram\Helper;

class MarkdownV2Helper
{
    public static function escapeMarkdownV2(string $message): string
    {
        // Escape characters in the general context
        $escapedMessage = preg_replace_callback('/(.)/', static function ($matches) {
            $specialChars = [
                '_',
                '*',
                '[',
                ']',
                '(',
                ')',
                '~',
                '`',
                '>',
                '#',
                '+',
                '-',
                '=',
                '|',
                '{',
                '}',
                '.',
                '!',
            ];
            $char = $matches[0];
            // If the character is a Markdown special character
            if (in_array($char, $specialChars, true)) {
                return '\\'.$char;
            }

            return $char;
        }, $message);

        // Additionally, escape within pre and code blocks for the characters ‘`’ and ‘\’.
        $escapedMessage = preg_replace_callback('/(`+)(.*?)(`+)/', function ($matches) {
            $codeBlockContent = preg_replace('/([`\\\\])/', '\\\\$1', $matches[2]);

            return $matches[1].$codeBlockContent.$matches[3];
        }, $escapedMessage);

        // Escape within links and custom emojis for the characters ')' and '\'
        return preg_replace_callback('/$begin:math:display$(.*?)$end:math:display$$begin:math:text$(.*?)$end:math:text$/', function ($matches) {
            $linkContent = preg_replace('/([)\\\\])/', '\\\\$1', $matches[2]);

            return '['.$matches[1].']('.$linkContent.')';
        }, $escapedMessage);
    }

    public static function makeBlockQuotation(string $text): string
    {
        // Split the text into an array of lines
        $lines = explode(PHP_EOL, $text);

        // Add the ">" symbol to all lines
        $lines = array_map(static fn ($line) => '>'.self::escapeMarkdownV2($line), $lines);

        // Join the lines back into text
        return implode(PHP_EOL, $lines);
    }

    public static function makeExpandableBlockQuotation(string $text): string
    {
        return str_replace(
            '{text}',
            self::makeBlockQuotation($text),
            '**{text}||',
        );
    }
}
