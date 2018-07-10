<?php
/**
 * Created by PhpStorm.
 * User: filatov
 * Date: 15.12.2016
 * Time: 13:04
 */

namespace frontend\helpers;

use frontend\modules\requests\models\Request;
use Yii;

class HelperMenu
{
    private $arMenu;

    /**
     * @return array
     */
    public function getArMenu(): array
    {
        return $this->arMenu;
    }

    public function __construct()
    {
        $requestModel = new Request();
        $this->arMenu = [
            'options' => ['class' => 'nav metismenu', 'id' => 'side-menu'],
            'items' => [
                ['label' => 'Адресные программы Санкт-Петербурга', 'options' => [
                    'class' => 'nav-header'
                ]],
                ['label' => 'Рабочий стол', 'icon' => 'fa fa-diamond', 'url' => ['/site/index'], 'visible' => true],
                ['label' => 'Заявки', 'icon' => 'fa fa-paper-plane', 'url' => '#', 'visible' => Yii::$app->user->can('administrateRequests:index'), 'items' => [
                    ['label' => 'Все', 'url' => ['/requests/default/index'], 'visible' => Yii::$app->user->can('administrateRequests:index')],
                    ['label' => $requestModel->printStatus(Request::STATUS_FOR_APPROVE), 'url' => ['/requests/default/index', 'status' => Request::STATUS_FOR_APPROVE], 'visible' => Yii::$app->user->can('administrateRequests:index')],
                    ['label' => $requestModel->printStatus(Request::STATUS_INCLUDED_TO_PROJECT), 'url' => ['/requests/default/index', 'status' => Request::STATUS_INCLUDED_TO_PROJECT], 'visible' => Yii::$app->user->can('administrateRequests:index')],
                    ['label' => $requestModel->printStatus(Request::STATUS_INCLUDED_TO_AP), 'url' => ['/requests/default/index', 'status' => Request::STATUS_INCLUDED_TO_AP], 'visible' => Yii::$app->user->can('administrateRequests:index')],
                    ['label' => $requestModel->printStatus(Request::STATUS_ARCHIVED), 'url' => ['/requests/default/index', 'status' => Request::STATUS_ARCHIVED], 'visible' => Yii::$app->user->can('administrateRequests:index')],
                    ['label' => 'С "Наш Спб"', 'url' => ['/requests/default/index', 'from_pob' => true], 'visible' => Yii::$app->user->can('administrateRequests:index')],
                    ['label' => $requestModel->printStatus(Request::STATUS_CANCELED), 'url' => ['/requests/default/index', 'status' => Request::STATUS_CANCELED], 'visible' => Yii::$app->user->can('administrateRequests:index')],

                ]],
                ['label' => 'Работы', 'icon' => 'fa fa-briefcase', 'url' => ['/works/default/index'], 'visible' => Yii::$app->user->can('administrateWork:index')],
                ['label' => 'Адресные программы', 'icon' => 'fa fa-briefcase', 'url' => '#', 'visible' => Yii::$app->user->can('administrateAddress:index'), 'items' => [
                    ['label' => 'Все', 'icon' => 'fa fa-briefcase', 'url' => ['/addresses/all/index'], 'visible' => Yii::$app->user->can('administrateAddress:index')],
                    ['label' => 'Текущие', 'icon' => 'fa fa-briefcase', 'url' => ['/addresses/default/index'], 'visible' => Yii::$app->user->can('administrateAddress:index')],
                    ['label' => 'Утвержденные', 'icon' => 'fa fa-briefcase', 'url' => ['/addresses/success/index'], 'visible' => Yii::$app->user->can('administrateAddress:index')],
                    ['label' => 'Архивные', 'icon' => 'fa fa-briefcase', 'url' => ['/addresses/archive/index'], 'visible' => Yii::$app->user->can('administrateAddress:index')],
                    ['label' => 'Удаленные', 'icon' => 'fa fa-briefcase', 'url' => ['/addresses/deleted/index'], 'visible' => Yii::$app->user->can('administrateDeletedAddressProgramm:index')],
                    ['label' => 'Данные от ГАТИ', 'icon' => 'fa fa-briefcase', 'url' => ['/open-data/default/index'],
                        'visible' => Yii::$app->user->can('administrateDeletedAddressProgramm:index') && Yii::$app->user->can('administrateRole:index')
                    ],
                ]],
                ['label' => 'Региональная программа', 'icon' => 'fa fa-briefcase', 'url' => ['/regionals/default/index'], 'visible' => Yii::$app->user->can('administrateRegion:index')],
                ['label' => 'Конкурсные процедуры', 'icon' => 'fa fa-briefcase', 'url' => ['/tenders/default/index'], 'visible' => Yii::$app->user->can('administrateTender:index')],
                ['label' => 'Договоры', 'icon' => 'fa fa-briefcase', 'url' => ['/contracts/default/index'], 'visible' => true],
                ['label' => 'Шаблоны', 'icon' => 'fa fa-cogs', 'url' => '#', 'visible' => Yii::$app->user->can('administrateTemplate:index'), 'items' => [
                    ['label' => 'Типы шаблонов', 'url' => ['/templates_my/types/index'], 'visible' => Yii::$app->user->can('administrateTemplate:index')],
                    ['label' => 'Шаблоны', 'url' => ['/templates_my/default/index'], 'visible' => Yii::$app->user->can('administrateTemplate:index')],
                    ['label' => 'Поля', 'url' => ['/templates_my/fields/index'], 'visible' => Yii::$app->user->can('administrateDirectory:index')],
                ]],
                ['label' => 'Справочники', 'icon' => 'fa fa-book', 'url' => '#', 'visible' => Yii::$app->user->can('administrateDirectory:index'), 'items' => [
                    ['label' => Yii::t('directories', 'DIC_TYPE_WORK'), 'url' => ['/directories/type-work/index'], 'visible' => Yii::$app->user->can('administrateDirectory:index')],
                    ['label' => Yii::t('directories', 'DIC_METRIKA'), 'url' => ['/directories/metrics/index'], 'visible' => Yii::$app->user->can('administrateDirectory:index')],
                    ['label' => Yii::t('directories', 'DIC_AREAS'), 'url' => ['/directories/areas/index'], 'visible' => Yii::$app->user->can('administrateDirectory:index')],
                    ['label' => Yii::t('directories', 'DIC_TYPE_AP'), 'url' => ['/directories/type-ap/index'], 'visible' => Yii::$app->user->can('administrateDirectory:index')],
                    ['label' => Yii::t('directories', 'DIC_TYPE_OGS'), 'url' => ['/directories/type-ogs2/index'], 'visible' => Yii::$app->user->can('administrateDirectory:index')],
                    ['label' => Yii::t('directories', 'DIC_TARGET_ARTICLE'), 'url' => ['/directories/target-article/index'], 'visible' => Yii::$app->user->can('administrateDirectory:index')],
                    ['label' => Yii::t('directories', 'DIC_STATE_PROGRAM'), 'url' => ['/directories/state-program/index'], 'visible' => Yii::$app->user->can('administrateDirectory:index')],
                    ['label' => Yii::t('directories', 'DIC_TYPE_MKD'), 'url' => ['/directories/type-mkd/index'], 'visible' => Yii::$app->user->can('administrateDirectory:index')],
                    ['label' => Yii::t('directories', 'DIC_SOURCE_MONEY'), 'url' => ['/directories/source-money/index'], 'visible' => Yii::$app->user->can('administrateDirectory:index')],
                    ['label' => Yii::t('site', 'DIC_DIC'), 'url' => ['/directories_my/generated/index'], 'visible' => Yii::$app->user->can('administrateDirectory:index')],
                    ['label' => 'Константы', 'url' => ['/directories/constants/index'], 'visible' => Yii::$app->user->can('administrateDirectory:index')],
                    ['label' => Yii::t('directories', 'TYPE_AP_AGREED_TITLE'), 'url' => ['/directories/type-ap-agreed/index'], 'visible' => Yii::$app->user->can('administrateDirectory:index')],
                    ['label' => Yii::t('directories', 'APPROVE_BLOCK_TITLE'), 'url' => ['/directories/approve-block/index'], 'visible' => Yii::$app->user->can('administrateDirectory:index')],
                    ['label' => Yii::t('directories', 'HEADS_TITLE'), 'url' => ['/directories/heads/index'], 'visible' => Yii::$app->user->can('administrateDirectory:index')],
                ]],
                ['label' => 'Источники финансирования', 'icon' => 'fa fa-money', 'url' => ['/source_money/default/index'], 'visible' => Yii::$app->user->can('administrateFinanceSource:index')],
                ['label' => 'Роли', 'icon' => 'fa fa-briefcase', 'url' => ['/users/roles/index'], 'visible' => Yii::$app->user->can('administrateRole:index')],
                ['label' => 'Правила', 'icon' => 'fa fa-briefcase', 'url' => ['/users/rules/index'], 'visible' => Yii::$app->user->can('administrateRule:index')],
                ['label' => Yii::t('regionalWork', 'TITLE'), 'icon' => 'fa fa-search', 'url' => ['/regionalWork/default/index/'], 'visible' => Yii::$app->user->can('administrateRegion:index')],
                ['label' => Yii::t('generated', 'GENERATED_TITLE'), 'icon' => 'fa fa-file', 'url' => ['/generated/default/index/'], 'visible' => true],
//                ['label' => 'Администрирование', 'icon' => 'fa fa-lock', 'url' => ['/admin/index-secret'], 'visible' => Yii::$app->user->can('administrateRule:index')],
            ],
        ];
    }


}