<?php
/**
 * Created by PhpStorm.
 * User: filatov
 * Date: 29.03.2017
 * Time: 13:46
 */

namespace common\helpers;


class HelperTorisRoles extends \toris\torisRbac\modules\rbac\widgets\safe\HelperTorisRoles
{
    /**
     * @brief Check user role
     * @return bool If it's "observer" return true.
     */
    public function isRoleObserver() {
        return $this->isRole(TorisRole::ROLE_OBSERVER);
    }

    /**
     * @brief Check user role
     * @return bool If it's "control" return true.
     */
    public function isRoleControl() {
        return $this->isRole(TorisRole::ROLE_CONTROL);
    }

    /**
     * @brief Check user role
     * @return bool If it's "administrator" return true.
     */
    public function isRoleAdmin() {
        return $this->isRole(TorisRole::ROLE_ADMIN);
    }

    /**
     * @brief Check user role
     * @return bool If it's "operator" return true.
     */
    public function isRoleOperator() {
        return $this->isRole(TorisRole::ROLE_OPERATOR);
    }
}