<?php
/**
 * Author: Pavel Naumenko
 */


/**
 * Class JsonParser
 */
class JsonParser
{
    public function parse(string $text): array
    {
        $data = json_decode($text, true);

        if (json_last_error() === JSON_ERROR_NONE) {
            return $data;
        }

        return [];
    }
}
