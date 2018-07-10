<?php
/**
 * Created by PhpStorm.
 * User: filatov
 * Date: 03.10.2017
 * Time: 15:25
 */

namespace frontend\helpers;

use frontend\modules\works\dynamic\models\WorkDynamic;
use frontend\modules\works\models\Work;
use toris\opentbs\OpenTBS;
use yii\base\Object;
use kartik\mpdf\Pdf;
use yii\db\ActiveRecord;

/**
 * Class ExportHandler
 * @package frontend\helpers
 * @property ActiveRecord $model
 */
abstract class ExportHandler extends Object
{
    /** @var  OpenTBS */
    protected $openTbs;

    /** @var string Путь к шаблону docx */
    protected $template;

    public $model;

    /** @var string Имя для заголовка */
    public $name;

    public function init()
    {
        $this->openTbs = new OpenTBS();
        $this->name = empty($this->model->name) ? 'Не задано' : $this->model->name;
    }

    abstract public function getName(string $ext): string;

    public function pdf($html)
    {
        $pdf = new Pdf([
            // set to use core fonts only
            'mode' => Pdf::MODE_UTF8,
            // A4 paper format
            'format' => Pdf::FORMAT_A4,
            // portrait orientation
            'orientation' => Pdf::ORIENT_PORTRAIT,
            // stream to browser inline
            'destination' => Pdf::DEST_DOWNLOAD,
            'filename' => $this->getName('pdf'),
            // your html content input
            'content' => $html,
            // format content from your own css file if needed or use the
            // enhanced bootstrap css built by Krajee for mPDF formatting
            'cssFile' => '@vendor/kartik-v/yii2-mpdf/assets/kv-mpdf-bootstrap.css',
            // any css to be embedded if required
//            'cssInline' => '',
            // set mPDF properties on the fly
//            'options' => ['title' => 'Krajee Report Title'],
            // call mPDF methods on the fly
            'methods' => [
//                'SetHeader'=>['Krajee Report Header'],
                'SetFooter'=>['{PAGENO}'],
            ]
        ]);
        return $pdf->render();
    }

    protected function prepareWorkData(Work $work): array
    {
        $dynamicModel = new WorkDynamic([], ['mainModel' => $work]);
        $arPretty = $dynamicModel->prettyWithCode;
        return [
            'address' => $work->workAddr,
            'type_work' => isset($work->typeWork->name)
                ? $work->typeWork->name
                : "Неизвестный тип работы ({$work->type_work_name})",
            'type_ogs' => isset($arPretty['type_ogs']) ? $arPretty['type_ogs'] : null,
            'recipient' =>isset($arPretty['recipient']) ? $arPretty['recipient'] : null,
            'plan_volume' => $work->plan_volume,
            'plan_cost' => $work->plan_cost,
            'finance_source' => $this->printFinanceSources($work),
        ];
    }

    protected function printFinanceSources(Work $work): string
    {
        $str = '';
        foreach ($work->financeSource as $financeSource) {
            $amount = $financeSource->amount ? $financeSource->amount : '0';
            $str .= "$financeSource->name: $amount рублей\n";
        }
        return $str;
    }
}