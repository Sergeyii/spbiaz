<?php
/**
 * Created by PhpStorm.
 * User: filatov
 * Date: 08.06.2018
 * Time: 13:19
 */

namespace frontend\modules\openData\curlApi;

use frontend\modules\openData\models\OpenData;
//use toris\base\exceptions\SaveARException;
use yii\db\JsonExpression;

class LoadDataApi extends LoadStructureApi
{
    public $baseUrl = 'http://data.gov.spb.ru/api/v1/datasets/9/versions/latest/data/';
    public $perPage = 100;
    public $page = 1;

    public function getRequestData()
    {
        return [
            'per_page' => $this->perPage,
            'page' => $this->page,
        ];
    }

    /**
     * Recursive load to db via pagination
     */
    public function saveAll()
    {
        if (isset($this->out->data['detail']) && strpos($this->out->data['detail'], 'Эта страница не содержит данных')) {
            // end
        } else {
            foreach ($this->out->data as $row) {
                $odModel = new OpenData(array_merge($row, [
                    'row' => new JsonExpression($row['row'])
                ]));
                if (!$odModel->save()) {
                    //throw new SaveARException($odModel);
                    throw new \Exception('Ошибка сохранения');
                }
            }
            $this->page++;
            $this->send();
            $this->saveAll();
        }
    }
}