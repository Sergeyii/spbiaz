<?php
/**
 * Created by PhpStorm.
 * User: filatov
 * Date: 08.06.2018
 * Time: 13:36
 */

namespace frontend\modules\openData\controllers;

use frontend\controllers\AppController;
use frontend\modules\openData\Importer;
use frontend\modules\openData\models\OpenData;
use frontend\modules\openData\models\search\OpenDataSearch;
//use toris\torisRbac\modules\rbac\helpers\AccessFactory;
use Yii;

class DefaultController extends AppController
{
    /**
     * @inheritdoc
     */
//    public function behaviors()
//    {
//        return (new AccessFactory('administrateAddress'))
//            ->updateIndexActions(['view'])
//            ->updateUpdateActions(['import'])
//            ->generate();
//    }

    public function actionIndex()
    {
        $searchModel = new OpenDataSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->get());

        return $this->render('index', [
            'dataProvider'  => $dataProvider,
            'searchModel'   => $searchModel,
        ]);
    }

    public function actionView($id)
    {
        /** @var OpenData $model */
        $model = OpenData::findOne($id);

        return $this->render('view', [
            'model' => $model,
        ]);
    }

    /**
     * Импорт с открытых данных
     * @return \yii\web\Response
     */
    public function actionImport()
    {
        Yii::$app->queue->push(new Importer());
        return $this->asJson(['status' => 1, 'msg' => 'Задача на обновление поставлена в очередь. Примерное время ожидания 1 мин.']);
    }
}