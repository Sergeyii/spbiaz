<?php
/**
 * Created by PhpStorm.
 * User: filatov
 * Date: 08.06.2018
 * Time: 13:16
 */

namespace frontend\modules\openData\curlApi;

use common\helpers\BaseCurlApi;
use frontend\modules\openData\models\OpenDataStructure;
//use toris\base\exceptions\SaveARException;
use yii\db\JsonExpression;
use yii\helpers\Json;
use yii\httpclient\Request;

class LoadStructureApi extends BaseCurlApi
{
    public $baseUrl = 'http://data.gov.spb.ru/api/v1/datasets/9/versions/latest/';

    protected $token = 'fc2407eca3fa1605a5760d4313e8605326166494';

    public function createRequest(): Request
    {
        $req = parent::createRequest();
        $req->addHeaders(['Authorization' => "Token $this->token"]);
        return $req;
    }

    public function save()
    {
        $odStrucureModel = new OpenDataStructure(array_merge($this->out->data, [
            'structure' => new JsonExpression($this->out->data['structure'])
        ]));
        if (!$odStrucureModel->save()) {
//            throw new SaveARException($odStrucureModel);
            throw new \Exception('Ошибка сохранения');
        }
    }
}
