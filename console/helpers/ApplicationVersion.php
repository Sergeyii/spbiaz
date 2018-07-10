<?php
/**
 * Created by PhpStorm.
 * User: filatov
 * Date: 31.01.2018
 * Time: 16:45
 */

namespace console\helpers;

use toris\logger\helpers\Logger;
use Yii;

class ApplicationVersion
{
    public static function get()
    {
        $projectPath = Yii::getAlias('@root').DIRECTORY_SEPARATOR;
        $commitTag = trim(exec("git --git-dir=$projectPath.git --work-tree=$projectPath describe --tags --abbrev=0", $output));
//        Yii::info($output, __METHOD__);

//        $commitDate = new \DateTime(trim(exec('git log -n1 --pretty=%ci HEAD')));
//        $commitDate->setTimezone(new \DateTimeZone('Europe/Moscow'));

        return $commitTag;
//        return sprintf('v%s.%s.%s-dev.%s (%s)', self::MAJOR, self::MINOR, self::PATCH, $commitHash, $commitDate->format('d.m.Y H:m:s'));
    }
}
