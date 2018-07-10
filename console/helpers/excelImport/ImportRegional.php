<?php
/**
 * Created by PhpStorm.
 * User: filatov
 * Date: 16.04.2018
 * Time: 16:12
 */

namespace console\helpers\excelImport;

use Box\Spout\Common\Type;
use Box\Spout\Reader\ReaderFactory;
use Box\Spout\Writer\WriterFactory;
use common\helpers\EasAddressApi;
use frontend\modules\directories\models\Areas;
use frontend\modules\directories\models\TypeMKD;
use frontend\modules\directories\models\TypeWork;
use frontend\modules\regionals\models\Regional;
use frontend\modules\regionals\models\RegionalTypesRelation;
use toris\base\exceptions\SaveARException;
use Yii;
use yii\base\BaseObject;
use yii\base\ErrorException;
use yii\helpers\ArrayHelper;
use yii\queue\JobInterface;
use yii\queue\Queue;

class ImportRegional extends BaseObject implements JobInterface
{
    const ROOT_TYPEWORK_NAME = 'Виды работ для импорта региональных программ из excel';
    const INNER_HOUSE_TYPE_WORK_COL_NUM = 3;
    const DATA_COL_DISTRICT = 3;
    const DATA_COL_TYPE_MKD = 2;
    const DATA_COL_ADDRESS = 1;
    const DATA_COL_MKD_SQUARE = 5;
    const DATA_COL_MKD_LIVING_AND_NOT_SQUARE = 6;
    const DATA_COL_YEAR = 4;
    const DATA_COL_PRIVATIZATION_DATE = 7;
    const DATA_COL_ROWNUM = 0;

    const TW_COL_FOUNDATIONS_REPAIR_MKD = 8;
    const TW_COL_ROOF_REPAIR = 9;
    const TW_COL_FACADE_REPAIR = 10;
    const TW_COL_HEAT_SUPPLY = 11;
    const TW_COL_COLD_WATER_SUPPLY = 12;
    const TW_COL_WASTE_WATER = 13;
    const TW_COL_HEAT_WATER_SUPPLY = 14;
    const TW_COL_ELECTRICITY_SUPPLY = 15;
    const TW_COL_GAS_SUPPLY = 16;
    const TW_COL_BASEMENT_REPAIR = 17;
    const TW_COL_FIRE_PROTECTION = 18;
    const TW_COL_ELEVATOR_REPAIR = 19;
    const TW_COL_EMERGENCY_BUILDING_REPAIR = 20;
    const TW_COL_PROJECT_DOCUMENTATION_REPAIR = 21;

    const ROWNUM_TW_MAP = [
        6 => [
            9 => self::TW_COL_PROJECT_DOCUMENTATION_REPAIR,
        ],
        7 => [
            0 => self::TW_COL_FOUNDATIONS_REPAIR_MKD,
            1 => self::TW_COL_ROOF_REPAIR,
            2 => self::TW_COL_FACADE_REPAIR,

            4 => self::TW_COL_BASEMENT_REPAIR,
            5 => self::TW_COL_FIRE_PROTECTION,
            6 => self::TW_COL_ELEVATOR_REPAIR,
            7 => self::TW_COL_EMERGENCY_BUILDING_REPAIR,
        ],
        8 => [
            0 => self::TW_COL_HEAT_SUPPLY,
            1 => self::TW_COL_COLD_WATER_SUPPLY,
            2 => self::TW_COL_WASTE_WATER,
            3 => self::TW_COL_HEAT_WATER_SUPPLY,
            4 => self::TW_COL_ELECTRICITY_SUPPLY,
            5 => self::TW_COL_GAS_SUPPLY,
        ],
    ];

    const NULL_VALUES = ['-', '0', ''];
    const NULL_EAS = 'NO EAS';

    public $filePath = 'regionals19122017.ods';

    /** @var integer */
    protected $rowNum;

    /** @var array */
    protected $row;

    /** @var TypeWork */
    protected $rootTypeWork;

    /** @var TypeWork */
    protected $innerHouseTypeWork;

    /** @var TypeWork[] */
    protected $typeWorks = [];

    /** @var array [id => mkd_type_name] */
    protected $mkdTypes = [];

    /** @var array [id => district_name] */
    protected $districts = [];

    /** @var Regional */
    protected $lastAddedRegional;

    /** @var array */
    protected $badAddresses = [];

    /** @var array */
    protected $resolvedAddresses = [];

    /** @var array */
    protected $resolvedAddressesVpr = [];

    public function init()
    {
        $this->districts = Areas::getModelArray();
        $this->mkdTypes = TypeMKD::getModelArray();
    }

    /**
     * @param $path
     * @return array
     */
    protected function loadResolvedAddresses($path)
    {
        $reader = ReaderFactory::create(Type::XLSX);
        $reader->open($path);
        $arRows = $out = [];
        foreach ($reader->getSheetIterator() as $sheet) {
            foreach ($sheet->getRowIterator() as $rowNum => $row) {
                if ($rowNum > 1) {
                    $arRows[] = $row;
                }
            }
        }
        $out = ArrayHelper::index($arRows, 0);
        $reader->close();
        return $out;
    }

    /**
     * @param Queue $queue which pushed and is handling the job
     * @throws \yii\db\Exception
     */
    public function execute($queue)
    {
        $pathResolved1 = Yii::getAlias('@statics') . '/files/regionalAddresses.xlsx';
        $this->resolvedAddresses = $this->loadResolvedAddresses($pathResolved1);
        $pathResolved2 = Yii::getAlias('@statics') . '/files/regionalAddresses2.xlsx';
        $this->resolvedAddressesVpr = $this->loadResolvedAddresses($pathResolved2);

        $this->checkRootTypeWork();
        $reader = ReaderFactory::create(Type::ODS);
        $path = Yii::getAlias('@statics') . '/files/' . $this->filePath;
        $reader->open($path);
        foreach ($reader->getSheetIterator() as $sheet) {
            foreach ($sheet->getRowIterator() as $this->rowNum => $this->row) {
                $this->checkTypeWork();
                $this->handleRow();
            }
        }

        if (YII_ENV_DEV) {
            $str = '';
            foreach ($this->badAddresses as $key => $address) {
                $str .= "$key;$address\n";
            }
            file_put_contents(Yii::getAlias('@statics') . '/files/bad_addresses.txt', $str);
        }
        $reader->close();
    }

    /**
     * Проверить наличие корневого вида работы, при отсутствии - создать
     */
    protected function checkRootTypeWork()
    {
        if (!$this->rootTypeWork = TypeWork::find()->where(['name' => self::ROOT_TYPEWORK_NAME])->one()) {
            $this->rootTypeWork = new TypeWork(['name' => self::ROOT_TYPEWORK_NAME]);
            $this->rootTypeWork->makeRoot();
        }
    }

    protected function checkTypeWork()
    {
        if ($this->rowNum == 6) {
            $this->handleTypeWork($this->rootTypeWork, 9);
        }
        if ($this->rowNum == 7) {
            for ($i = 0; $i <= 7; $i++) {
                $tw = $this->handleTypeWork($this->rootTypeWork, $i);
                if ($i == self::INNER_HOUSE_TYPE_WORK_COL_NUM) {
                    $this->innerHouseTypeWork = $tw;
                }
            }
        }
        if ($this->rowNum == 8) {
            for ($i = 0; $i <= 5; $i++) {
                $this->handleTypeWork($this->innerHouseTypeWork, $i);
            }
            ksort($this->typeWorks);
        }
    }

    /**
     * @param TypeWork $parent
     * @param int $colIndex
     * @return TypeWork
     */
    protected function handleTypeWork(TypeWork $parent, int $colIndex): TypeWork
    {
        if (!$tw = TypeWork::find()->where(['name' => $this->row[$colIndex]])->one()) {
            $tw = new TypeWork(['name' => $this->row[$colIndex]]);
            $tw->appendTo($parent);
        }
        if (isset(self::ROWNUM_TW_MAP[$this->rowNum][$colIndex]) && self::ROWNUM_TW_MAP[$this->rowNum][$colIndex]) {
            $this->typeWorks[self::ROWNUM_TW_MAP[$this->rowNum][$colIndex]] = $tw;
        }
        return $tw;
    }

    /**
     * Обработка строки с данными
     * @throws SaveARException
     */
    protected function handleRow()
    {
        if ($this->rowNum >= 10) {
//            if ($this->rowNum < 617) {return;}
            if (YII_ENV_DEV) {
                echo "Строка $this->rowNum\n";
            }
            if (!$this->row[self::DATA_COL_ROWNUM]) {
                if ($this->lastAddedRegional) {
                    $this->handleElevatorRow();
                }
            } else {
                $this->handleCommonDataRow();
            }
        }
    }

    /**
     * Обработка строки с обычными данными
     * @throws SaveARException
     */
    protected function handleCommonDataRow()
    {
        $params = $this->prepareParams();
        if (empty($params)) {
            return;
        }
        if (!Regional::find()->filterWhere($params)->andWhere(['or', 'is_history != TRUE', 'is_history IS NULL'])->one()) {
            $params['regional_type'] = [];
            foreach ($this->typeWorks as $index => $typeWork) {
                if (!in_array($this->row[$index], self::NULL_VALUES)) {
                    if ($arYears = $this->parseTypeWorkYears($this->row[$index])) {
                        $regionalWorkTypeData = array_merge($arYears, ['dic_type_work_id' => $typeWork->id]);
                        $params['regional_type'][] = $regionalWorkTypeData;
                    }
                }
            }
            $this->lastAddedRegional = new Regional($params);
            $this->lastAddedRegional->setScenario('admin-create');
            if (!$this->lastAddedRegional->save()) {
                throw new SaveARException($this->lastAddedRegional);
            }
        } else {
            $this->lastAddedRegional = null;
        }
    }

    /**
     * Обработка строки с данными по работам по лифтам
     * @throws SaveARException
     */
    protected function handleElevatorRow()
    {
        foreach ($this->typeWorks as $index => $typeWork) {
            if ($arYears = $this->parseTypeWorkYears($this->row[$index])) {
                $regionalWorkTypeData = array_merge($arYears, ['dic_type_work_id' => $typeWork->id]);
                $model = new RegionalTypesRelation($regionalWorkTypeData);
                $model->regional_prog_id = $this->lastAddedRegional->id;
                if (!$model->save()) {
                    throw new SaveARException($model);
                }
            }
        }
    }

    /**
     * Подготовка параметров для регионалки
     * @return array
     */
    protected function prepareParams(): array
    {
        $out = [];
        if ($eas = $this->setAddress($this->row[self::DATA_COL_ADDRESS] ?? null)) {
            $commisioningYear = isset($this->row[self::DATA_COL_YEAR])
                ? substr(trim($this->row[self::DATA_COL_YEAR]), 0, 4)
                : null;
            $privatizationDate = '';
            if (isset($this->row[self::DATA_COL_PRIVATIZATION_DATE])) {
                if (is_string($this->row[self::DATA_COL_PRIVATIZATION_DATE])) {
                    $privatizationDate = trim(preg_replace('/\s+/', '', $this->row[self::DATA_COL_PRIVATIZATION_DATE]));
                    if (in_array($privatizationDate, self::NULL_VALUES)) {
                        $privatizationDate = '';
                    }
                } else {
                    $privatizationDate = $this->row[self::DATA_COL_PRIVATIZATION_DATE];
                }
                if ($privatizationDate) {
                    $privatizationDate = Yii::$app->formatter->asDate($privatizationDate);
                }
            }
            if ($this->row[self::DATA_COL_DISTRICT] && $this->row[self::DATA_COL_TYPE_MKD]) {
                $out = [
                    'dic_areas_id' => $this->findDistrictId($this->row[self::DATA_COL_DISTRICT] ?? null),
                    'dic_type_mkd_id' => $this->findTypeMkdId($this->row[self::DATA_COL_TYPE_MKD] ?? null),
                    'address' => $eas['pAddress'],
                    'eas_code' => $eas['easCode'],
                    'mkd_square' => $this->row[self::DATA_COL_MKD_SQUARE] ?? null,
                    'residential_not_residential_square' => $this->row[self::DATA_COL_MKD_LIVING_AND_NOT_SQUARE] ?? null,
                    'commisioning_year' => $commisioningYear,
                    'first_res_privatization' => $privatizationDate,
                ];
            }
        }

        return $out;
    }

    /**
     * @param string $years
     * @return array|null
     * @throws ErrorException
     */
    protected function parseTypeWorkYears(string $years)
    {
        $out = null;
        if (in_array($years, self::NULL_VALUES)) {
            return $out;
        }
        if (!preg_match('/(\d{4})\-(\d{4})\s?гг/', $years, $matches)) {
            if (!preg_match('/(\d{4})\s?г/', $years, $matches)) {
                throw new ErrorException("Не удалось распарсить года для вида работы. Номер строки: $this->rowNum. Года: $years");
            } else {
                $out = [
                    'period_start' => $matches[1],
                    'period_end' => $matches[1],
                ];
            }
        } else {
            $out = [
                'period_start' => $matches[1],
                'period_end' => $matches[2],
            ];
        }
        return $out;
    }

    /**
     * Найти идентификатор района
     * @param string $districtName
     * @return int
     * @throws ErrorException
     */
    protected function findDistrictId(string $districtName): int
    {
        $districtName = str_replace('-', '', $districtName);
        if ($p = strpos($districtName, ' район')) {
            $districtName = substr($districtName, 0, $p);
        }
        if (!$id = array_search($districtName, $this->districts)) {
            throw new ErrorException("Не удалось найти район $districtName");
        }
        return $id;
    }

    /**
     * Найти идентификатор типа МКД
     * @param string $mkdName
     * @return int
     * @throws SaveARException
     */
    protected function findTypeMkdId(string $mkdName): int
    {
        if (!$id = array_search($mkdName, $this->mkdTypes)) {
            $model = new TypeMKD(['name' => $mkdName]);
            $model->setScenario('admin-create');
            if (!$model->save()) {
                throw new SaveARException($model);
            } else {
                $id = $model->id;
                $this->mkdTypes[$id] = $model->name;
            }
        }
        return $id;
    }

    /**
     * @param string $address
     * @return array|null
     * @throws ErrorException
     */
    public function setAddress(string $address)
    {
        $out = ['pAddress' => null, 'easCode' => null];
        if (in_array($address, ['Исключен'])) {
            return null;
        }
        if (isset($this->resolvedAddressesVpr[$this->rowNum][2]) && $this->resolvedAddressesVpr[$this->rowNum][2] != self::NULL_EAS) {
            $out['pAddress'] = $this->resolvedAddressesVpr[$this->rowNum][1];
            $out['easCode'] = $this->resolvedAddressesVpr[$this->rowNum][2];
        } else {
            if (isset($this->resolvedAddresses[$this->rowNum])) {
                $address = $this->resolvedAddresses[$this->rowNum][1];
            }
            $getter = new EasAddressApi(['inputAddress' => $address, 'enableCache' => true]);
            $msg = $getter->send();
            if ($msg->status && !empty($msg->data)) {
                $getter->selectCurrentEas();
                $out['pAddress'] = $getter->pAddress;
                $out['easCode'] = $getter->easCode;
            } else {
                $this->badAddresses[$this->rowNum] = $address;
                return null;
            }
        }
        return $out;
    }
}
