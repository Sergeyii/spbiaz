<?php
/**
 * Created by PhpStorm.
 * User: nikita.klesov
 * Date: 06.04.2018
 * Time: 11:38
 */

namespace console\helpers\excelImport;

use Box\Spout\Common\Type;
use Box\Spout\Reader\ReaderFactory;
use common\helpers\EasAddressApi;
use frontend\helpers\HelperStatuses;
use frontend\modules\addresses\models\Address;
use frontend\modules\directories\models\TypeAP;
use frontend\modules\directories\models\TypeWork;
use frontend\modules\source_money\models\ApFinanceSources;
use frontend\modules\templates\models\sub\TemplateSubField;
use frontend\modules\templates\models\Template;
use frontend\modules\works\dynamic\models\WorkDynamic;
use frontend\modules\works\models\Work;
use toris\base\exceptions\SaveARException;
use toris\templates\models\TemplateType;
use Yii;
use yii\base\BaseObject;
use yii\base\ErrorException;
use yii\helpers\ArrayHelper;
use yii\queue\JobInterface;
use yii\queue\Queue;

class ImportWorksFromExcelHelper extends BaseObject implements JobInterface
{
    const TEMPLATE_CODE = 'overhaul_mkd';
    const TEMPLATE_NAME = 'Адресная программа текущего ремонта общего имущества многоквартирных домов';
    const SUB_FIELD_CODE = 'stairway_num';
    const TEMPLATE_TYPE_CODE = 'for_import_from_excel';
    const TEMPLATE_TYPE_NAME = 'Тип шабона для импорта из excel';
    const FILENAME_AP_MAP = [
        '3614-1.xlsx' => 'Адресная программа Текущего ремонта по ООО ЖКС №1 Петроградского района на 2018 год',
        '3614-2.xlsx' => 'Адресная программа Текущего ремонта по ООО ЖКС №1 Петроградского района на 2018 год',
        '3616-1.xlsx' => 'Адресная программа Текущего ремонта по ООО ЖКС №2 Петроградского района на 2018 год',
        '3616-2.xlsx' => 'Адресная программа Текущего ремонта по ООО ЖКС №2 Петроградского района на 2018 год',
    ];
    const STAIRS_HEADER = 'л.кл';
    const ADDRESS_COL_INDEX = 2;

    public $filePath;

    /** @var Template */
    protected $template;

    /** @var TemplateType */
    protected $templateType;

    /** @var WorkTypeData[] */
    protected $workTypeMap = [];

    /** @var Address */
    protected $ap;

    /** @var integer Номер текущей перебираемой колонки */
    protected $colNum;

    /** @var string Значение текущей перебираемой ячейки */
    protected $cell;

    /** @var integer Номер текущей перебираемой строки */
    protected $rowNum;

    /** @var array Массив значений текущей перебираемой строки */
    protected $row;

    /** @var TemplateSubField */
    protected $field;

    /** @var TypeWork[] */
    protected $workTypes;

    /** @var string */
    protected $address;

    /** @var string */
    protected $easCode;

    /** @var ApFinanceSources[] */
    protected $financeSource = [];

    /**
     * @param string $address
     */
    public function setAddress(string $address)
    {
        $getter = new EasAddressApi(['inputAddress' => $address]);
        $out = $getter->send();
        if ($out->status && !empty($out->data)) {
            $getter->selectCurrentEas();
        }
        $this->address = $getter->inputAddress;
    }

    /**
     * @param $val
     * @return null|TypeWork
     */
    protected function findWorkType($val)
    {
        $out = null;
        if (empty($this->workTypes)) {
            $this->workTypes = TypeWork::find()->all();
        }
        foreach ($this->workTypes as $workType) {
            if ($val == $workType->code) {
                $out = $workType;
                break;
            }
        }
        return $out;
    }

    protected function checkFinanceSource()
    {
        /** @var ApFinanceSources $fs */
        if ($fs = ApFinanceSources::find()->where([
            'name' => 'Плата населения  (работы, выполняемые  управляющими организациями)'
        ])->one()) {
            $this->financeSource[] = $fs->id;
        }
    }

    /**
     * Построить структуру вида $this->workTypeMap = [
     *      %start_col_index% => WorkTypeData
     * ]
     */
    protected function buildWorkTypeMap()
    {
        $currentKey = 0;
        if ($this->rowNum == 1) {
            foreach ($this->row as $key => $val) {
                if ($val == '') {
                    if ($currentKey > 0) {
                        $this->workTypeMap[$currentKey]->endColIndex = $key;
                    }
                } else {
                    $typeWork = $this->findWorkType($val);
                    if ($typeWork) {
                        $this->workTypeMap[$key] = new WorkTypeData([
                            'wt' => $typeWork,
                            'startColIndex' => $key,
                            'endColIndex' => $key,
                            'dynamicWork' => $this->prepareWorkPrototype(),
                            'template' => $this->template,
                        ]);
                        $currentKey = $key;
                    } else {
                        $currentKey = 0;
                    }
                }
            }
            if (empty($this->workTypeMap)) {
                throw new ErrorException('Не удалось построить карту видов работ');
            }
        }
    }

    /**
     * Прочитать данные о единицах измерения
     */
    protected function enrichWorkTypeMapWithMetrics()
    {
        if ($this->rowNum == 2) {
            foreach ($this->row as $this->colNum => $this->cell) {
                if ($wtData = $this->getTypeWork()) {
                    $wtData->setMetric($this->colNum, $this->cell);
                }
            }
        }
    }

    /**
     * @return TemplateSubField
     * @throws SaveARException
     */
    protected function createField()
    {
        $params = [
            'name' => '№ лестничной клетки',
            'type' => TemplateSubField::TYPE_STRING,
            'code' => 'stairway_num',
            'description' => 'Поле для импорта работ из xml',
        ];
        if (!$this->field = TemplateSubField::find()->where($params)->one()) {
            $this->field = new TemplateSubField($params);
            $this->field->setScenario('admin-create');
            if (!$this->field->save()) {
                throw new SaveARException($this->field);
            }
        }
        return $this->field;
    }

    /**
     * Проверка наличия шаблона и наличия в нем поля "№ лестничной клетки"
     * @return bool
     */
    protected function checkTemplate()
    {
        if ($this->template = Template::find()->where(['code' => self::TEMPLATE_CODE])->one()) {
            foreach ($this->template->fieldsAdditional as $subField) {
                if ($subField->visible && $subField->code == self::SUB_FIELD_CODE) {
                    return true;
                }
            }
//            $this->template->delete();
        }
        return false;
    }

    /**
     * Подготовка модели работы
     * @return WorkDynamic
     */
    protected function prepareWorkPrototype()
    {
        $workModel = new Work(['scenario' => Work::SCENARIO_ADMIN_CREATE]);
        $workDynamicProto = new WorkDynamic([], ['mainModel' => $workModel]);
        $workDynamicProto->addr = $this->ap->id;
        $workDynamicProto->user_id = $this->ap->user_id;
        $workDynamicProto->template_id = $this->template->id;
        $workDynamicProto->linkWorkFinanceSource = $this->financeSource;
        return $workDynamicProto;
    }

    /**
     * Создать шаблон для импорта
     * @throws SaveARException
     */
    protected function buildTemplate()
    {
        $this->createTemplateType();
        $tsf = $this->createField();
        $templateSubFields[$tsf->id] = [
            'required' => true,
            'multiple' => false,
        ];
        $subFields = json_encode($templateSubFields);
        $this->template = new Template([
            'code' => static::TEMPLATE_CODE,
            'name' => static::TEMPLATE_NAME,
            'template_type_id' => $this->templateType->id,
            'sub_fields' => $subFields,
            'scenario' => 'admin-create',
            'dic_types_ap' => ArrayHelper::getColumn(TypeAP::find()->select('id')->asArray()->all(), 'id'),
        ]);
        if (!$this->template->save()) {
            throw new SaveARException($this->template);
        }
        $this->template->refresh();
    }

    /**
     * Поиск подходящего templateType - без полей, либо его создание в случае отсутствия
     * @throws SaveARException
     */
    protected function createTemplateType()
    {
        $templateTypes = TemplateType::find()->all();
        foreach ($templateTypes as $templateType) {
            if (empty($templateType->fields)) {
                $this->templateType = $templateType;
                return;
            }
        }

        $this->templateType = new TemplateType([
            'code' => static::TEMPLATE_TYPE_CODE,
            'name' => static::TEMPLATE_TYPE_NAME,
            'scenario' => 'admin-create',
        ]);
        if (!$this->templateType->save()) {
            throw new SaveARException($this->templateType);
        }
    }

    /**
     * Найти АП для текущего файла, в которую будут добавляться работы
     * @throws ErrorException
     */
    protected function checkAP()
    {
        if ($apName = self::FILENAME_AP_MAP[$this->filePath]) {
            if (!$this->ap = Address::find()->where([
                'name' => $apName,
                'status' => HelperStatuses::STATUS_ACTIVE,
                'main_id' => null
            ])->one()) {
                throw new ErrorException("Не найдена АП с именем \"$apName\"");
            }
        }
    }

    /**
     * @param Queue $queue which pushed and is handling the job
     * @throws \yii\db\Exception
     */
    public function execute($queue)
    {
        if (!$this->checkTemplate()) {
            $this->buildTemplate();
        }
        $this->checkAP();
        $this->checkFinanceSource();
        (new MetricsHandler())->createMetrics();

        $reader = ReaderFactory::create(Type::XLSX);
        $path = Yii::getAlias('@statics') . '/files/' . $this->filePath;
        $reader->open($path);
        foreach ($reader->getSheetIterator() as $sheet) {
            foreach ($sheet->getRowIterator() as $this->rowNum => $this->row) {
                $this->buildWorkTypeMap();
                $this->enrichWorkTypeMapWithMetrics();
                $this->handleRow();
            }
        }

        $reader->close();
    }

    /**
     * Обработать строку
     */
    protected function handleRow()
    {
        if ($this->rowNum > 2) {
            $this->updateWorkDynamicModels();
            foreach ($this->row as $this->colNum => $this->cell) {
                if ($this->colNum == self::ADDRESS_COL_INDEX) {
                    $this->setAddress($this->cell);
                }
                if ($wtData = $this->getTypeWork()) {
                    $wtData->handleDataCell($this->colNum, $this->cell);
                }
            }
        }
    }

    /**
     * Если прошло сохранение работы. Для сохранения последующих нужны новые модели
     */
    protected function updateWorkDynamicModels()
    {
        foreach ($this->workTypeMap as $workTypeData) {
            if ($workTypeData->dynamicWork->mainModel->id) {
                $workTypeData->dynamicWork = $this->prepareWorkPrototype();
            }
        }
    }

    /**
     * Найти вид работы по текущему номеру колонки
     * @return WorkTypeData|null
     */
    protected function getTypeWork()
    {
        $out = null;
        if (isset($this->workTypeMap[$this->colNum])) {
            $out = $this->workTypeMap[$this->colNum];
        } else {
            foreach ($this->workTypeMap as $wtData) {
                if ($wtData->in($this->colNum)) {
                    $out = $wtData;
                    break;
                }
            }
        }

        if ($out) {
            if ($out->dynamicWork->name != $this->address) {
                $out->dynamicWork->name = $this->address;
                $out->dynamicWork->workAddr = $this->address;
                $out->dynamicWork->eas_code = $this->easCode;
            }
        }
        return $out;
    }
}
