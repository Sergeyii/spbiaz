<?php
/**
 * Created by PhpStorm.
 * User: filatov
 * Date: 29.03.2017
 * Time: 13:43
 */

namespace common\helpers;


class TorisRole extends \toris\torisRbac\modules\rbac\helpers\TorisRole
{
    const ROLE_ADMIN = '[urn:eis:toris:ap3]urn:role:ap:administrator';
    const ROLE_OPERATOR = '[urn:eis:toris:ap3]urn:role:ap:operator';
    const ROLE_MANAGER_CATALOG = '[urn:eis:toris:ap3]urn:role:ap:manager_catalog';
    const ROLE_MANAGER_TEMPLATES = '[urn:eis:toris:ap3]urn:role:ap:manager_templates';
    const ROLE_PORTLET = '[urn:eis:toris:ap3]urn:role:ap:portlet';
    const ROLE_PROPERTY_OBJECT = '[urn:eis:toris:ap3]urn:role:ap:property_object';
    const ROLE_OBSERVER = '[urn:eis:toris:ap3]urn:role:ap:observer';
    const ROLE_CONTROL = '[urn:eis:toris:ap3]urn:role:ap:control';
}