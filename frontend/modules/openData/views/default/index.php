<?php
/**
 * Created by PhpStorm.
 * User: filatov
 * Date: 08.06.2018
 * Time: 15:31
 */

use yii\helpers\Html;
use kartik\grid\GridView;
//use toris\widgets\Box;
use yii\helpers\Url;

/**
 * @var \yii\web\View $this
 * @var \yii\data\ArrayDataProvider $dataProvider
 * @var \frontend\modules\openData\models\search\OpenDataSearch $searchModel
 */

$this->title = Yii::t('openData', 'TITLE');
$this->params['subtitle'] = Yii::t('openData', 'TITLE_INDEX');
$this->params['breadcrumbs'] = [
    $this->title
];
$boxButtons = [];
if (Yii::$app->user->can('administrateAddress:update')) {
    \frontend\modules\openData\OpenDataAsset::register($this);
    $this->registerJs("var loadOpenData = new LoadOpenData();");
    $boxButtons[] = Html::a(
        '<i class="fa fa-download"></i>',
        '#',
        ['id' => 'import-from-open-data', 'class' => 'btn btn-sm btn-primary']
    );
}
$boxButtons = !empty($boxButtons) ? implode(' ', $boxButtons) : null;
?>
<div class="wrapper wrapper-content animated fadeInRight">
    <div class="row">
        <div class="col-xs-12">
            <?php /* $box = Box::begin(
                [
                    'title' => $this->params['subtitle'],
                    'renderBody' => false,
                    'options' => [
                        'class' => 'box-primary'
                    ],
                    'buttonsTemplate' => $boxButtons,
                    'bodyOptions' => [
                        'class' => ''
                    ],
                ]
            ); */?>
            <?= Html::errorSummary($searchModel, ['class' => 'text-danger']);?>
            <?php
            $gridColumns = array_merge(array_map('strtolower', $searchModel::COLUMNS_FOR_INDEX_PAGE), [
                [
                    'class' => \yii\grid\ActionColumn::class,
                    'template' => '{view}',
                    'buttons' => [
                        'view' => function ($url, $model) {
                            return Html::a(
                                '<i class="glyphicon glyphicon-eye-open"></i>',
                                Url::to([
                                    '/openData/default/view',
                                    'id' => $model['num_id'],
                                ]),
                                [
                                    'class' => 'btn btn-default btn-sm',
                                    'title' => Yii::t('yii', 'View'),
                                    'aria-label' => Yii::t('yii', 'View'),
                                    'data-pjax' => '0',
                                    'update-action' => true,
                                ]
                            );
                        },
                    ],
                ],
            ]);
            echo GridView::widget([
                'dataProvider' => $dataProvider,
                'filterModel' => $searchModel,
                'columns' => $gridColumns
            ]); ?>
        </div>
    </div>
</div>

