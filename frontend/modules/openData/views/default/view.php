<?php
/**
 * @var \frontend\modules\openData\models\OpenData $model
 * @var \yii\web\View $this
 */

$this->title = Yii::t('openData', 'TITLE');
$this->params['subtitle'] = Yii::t('contracts', 'VIEW');
$this->params['breadcrumbs'] = [
    [
        'label' => $this->title,
        'url' => ['index'],
    ],
    $this->params['subtitle']
];

?>
<div class="row animated fadeInRight">
    <div class="col-lg-12">
        <div class="wrapper wrapper-content" style="padding-bottom: 0;">
            <div class="ibox">
                <div class="ibox-content">
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="m-b-md">
                                <h2>
                                     <?=Yii::t('openData', 'VIEW');?>
                                </h2>
                            </div>
                        </div>
                    </div>
                    <hr>
                    <div class="row item-view">
                        <div class="col-lg-12">
                            <?php foreach ($model->structure->columns as $column): ?>
                                <div class="row m-b-md">
                                    <div class="col-lg-3">
                                        <i class="fa fa-building"></i>
                                        <?= $model->getAttributeLabel($column->title); ?>
                                    </div>
                                    <div class="col-lg-9">
                                        <? if ($model->jsonField($column->title)): ?>
                                            <?= $model->jsonField($column->title); ?>
                                        <? else: ?>
                                            <span class="text-danger">(Не задано)</span>
                                        <? endif; ?>
                                    </div>
                                </div>
                            <?php endforeach;?>

                            <hr>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
