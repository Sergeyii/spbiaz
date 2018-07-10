<?php
/**
 * Created by PhpStorm.
 * User: filatov
 * Date: 29.06.2017
 * Time: 10:21
 */

namespace console;

use common\exceptions\CommonException;
use common\soap\exceptions\SoapException;
use common\soap\models\TargetProgram;
use common\soap\request\BaseRequest;
use common\soap\request\GetRegionalProgram;
use common\soap\request\GetTargetProgram;
use common\soap\request\GetTargetProgramOwnersFunds;
use common\soap\WebService;
use frontend\modules\addresses\models\Address;
use frontend\modules\directories\models\TypeAP;
use frontend\modules\regionals\models\Regional;
use frontend\modules\works\models\Work;
use toris\logger\helpers\Logger;
use Yii;
use yii\db\Query;

class Ap2Importer
{
    /** @var WebService */
    protected $service;

    public function __construct()
    {
        $this->service = Yii::$app->webservice;
    }

    public function importWorks()
    {
        $this->_import(GetTargetProgram::className());
    }

    public function importCurrentWorks()
    {
        $this->_import(GetTargetProgramOwnersFunds::className());
    }

    public function importRegionals()
    {
        $this->_import(GetRegionalProgram::className());
    }

    public function getAnswer()
    {
        return $this->service->answer->getAnswer();
    }

    public function deleteWorks()
    {
        $count = Address::deleteAll([
            'id' => (new Query())->from(['a' => Address::tableName()])->select('a.id')
                ->innerJoin(['typeap' => TypeAP::tableName()], 'typeap.id = a.dic_type_ap_id')
                ->andWhere(['typeap.code' => TargetProgram::V2_CODE])
        ]);
        $count += Work::deleteAll(['not', ['ap2_id' => null]]);

        $this->service->answer->addDeleted($count);
    }

    public function deleteCurrentWorks()
    {
        $this->deleteWorks();
    }

    public function deleteRegionals()
    {
        $count = Regional::deleteAll(['from_ap2' => true]);
        $this->service->answer->addDeleted($count);
    }

    protected function _import($requestClassName)
    {
        try {
            $start = microtime(true);

            /** @var BaseRequest $request */
            $request = new $requestClassName;
            if ($request->validate()) {
                if ($request instanceof GetRegionalProgram) {
                    $this->_doRegionalRequest($request);
                } else {
                    $this->_doRequest($request);
                }
            } else {
                throw new SoapException("validate $requestClassName false\n");
            }

            $time = microtime(true) - $start;
            printf('Скрипт выполнялся %.4F сек.', $time);
        } catch (CommonException $e) {
            $this->_catch($e);
        }
    }

    protected function _doRequest(BaseRequest $request)
    {
        $this->service->send($request);
        echo $this->service->answer->getAnswer()."\n";
    }

    protected function _doRegionalRequest(GetRegionalProgram $request)
    {
        $this->service->useCache = false;
        $firstResponse = $this->service->send($request);
        if ($firstResponse->pageCount > 1) {
            for($i = 1; $i <=$firstResponse->pageCount; ) {
                $nextPage = ++$i;
                $request = new GetRegionalProgram(['regionalProgramFilter' => [
                    'currentPage' => $nextPage
                ]]);

                if ($request->validate()) {
                    /** @var \common\soap\response\GetRegionalProgram $response */
                    $this->service->send($request);
                }
            }
        }
        echo $this->service->answer->getAnswer()."\n";
    }

    protected function _catch($e)
    {
        $log = new Logger(Logger::TYPE_ERROR, $e->getMessage(), time());
        $log->addData(['trace' => $e->getTraceAsString()]);
        $log->writeLog();
    }
}