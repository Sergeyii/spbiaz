<?php
/**
 * Created by PhpStorm.
 * User: filatov
 * Date: 20.06.2017
 * Time: 9:54
 */

namespace console\controllers;

use common\soap\exceptions\SoapException;
use console\Ap2Importer;
use yii\console\Controller;

/**
 * data import from ap2
 * АП - работы - вид
 * getTargetProgram - getTaskDetails - простые
 * getTargetProgramOwnersFunds - getOwnersWorksWithTP - текущие
 *
 * Class Ap2ImportController
 * @package console\controllers
 */
class Ap2ImportController extends Controller
{
    public $defaultAction = 'regionals';

    /**
     * imports common ap-works
     * @throws SoapException
     */
    public function actionWorks()
    {
        $importer = new Ap2Importer();
        $importer->importWorks();
    }

    /**
     * imports current ap-works
     * @throws SoapException
     */
    public function actionCurrentWorks()
    {
        $importer = new Ap2Importer();
        $importer->importCurrentWorks();
    }

    /**
     * imports regional programs
     * @throws SoapException
     */
    public function actionRegionals()
    {
        $importer = new Ap2Importer();
        $importer->importRegionals();
    }
}