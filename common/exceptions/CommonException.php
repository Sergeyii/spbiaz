<?php
/**
 * Created by PhpStorm.
 * User: filatov
 * Date: 20.06.2017
 * Time: 12:49
 */

namespace common\exceptions;


//use toris\logger\exceptions\Exception;

class CommonException extends \Exception
{
    public function __construct($message = "", array $data = [])
    {
        parent::__construct($message, $data);
    }
}