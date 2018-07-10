<?php
/**
 * Created by PhpStorm.
 * User: filatov
 * Date: 29.06.2017
 * Time: 13:23
 */

namespace common\helpers;


class TransliteratorHelper extends \dosamigos\transliterator\TransliteratorHelper
{
    public static function process($string, $unknown = '', $language = 'ru')
    {
        $str = parent::process($string, $unknown, $language);
        return strtolower(str_replace(' ', '_', $str));
    }
}