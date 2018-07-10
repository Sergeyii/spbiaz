<?php
/**
 * Created by PhpStorm.
 * User: filatov
 * Date: 13.01.2017
 * Time: 14:54
 */

namespace frontend\helpers;

use common\helpers\HelperTorisRoles;
use toris\torisRbac\modules\rbac\Module;

class TorisRoleHelper
{
    /**
     * @return HelperTorisRoles
     */
    public function get()
    {
        /** @var Module $rbacModule */
        $rbacModule = \Yii::$app->getModule('rbac');
        $torisRoles = $rbacModule->getTorisHelper();
        return $torisRoles;
    }
}
