<?php
/**
 * Created by PhpStorm.
 * User: filatov
 * Date: 18.12.2017
 * Time: 14:34
 */

namespace console\migrations\base;

use yii\db\Migration;

class RoleRuleBaseMigration extends Migration
{
    protected $arRule = [
//        0 => ['Компонент "Заявки"', 'administrateRequests', null],
//        1 => ['Список', 'administrateRequests:index', 0],
//        2 => ['Просмотр', 'administrateRequests:view', 0],
//        3 => ['Редактирование', 'administrateRequests:update', 0],
//        4 => ['Удаление', 'administrateRequests:delete', 0],

    ];

    protected $arRoleRuleLink = [
        // administrator
//        [1, 0],

        // operator
//        [2, 0],


        // ap:manager_catalog
//        [3, 20],

        // ap:manager_templates
//        [4, 20],

        // ap:control
//        [7, 6],

        // observer
//        [8, 1],
    ];

    protected $inserted = [];

    public function safeUp()
    {
        $this->safeDown();

        $parents = [];
        foreach ($this->arRule as $num => $rule) {
            $parentId = isset($parents[$rule[2]]) ? $parents[$rule[2]] : null;
            $this->insert('users_rules', [
                'name' => $rule[0],
                'name_inner' => $rule[1],
                'parent_id' => $parentId,
            ]);

            $lastId = $this->db->getLastInsertID('users_rules_id_seq');
            $this->inserted[$num] = $lastId;
            if (is_null($rule[2])) {
                $parents[$num] = $lastId;
            }
        }

        foreach ($this->arRoleRuleLink as $link) {
            $this->insert('users_roles_rules_link', [
                'role_id' => $link[0],
                'rule_id' => $this->inserted[$link[1]],
            ]);
        }
    }

    public function safeDown()
    {
        foreach ($this->arRule as $num => $rule) {
            $this->delete('users_rules', [
                'name' => $rule[0],
                'name_inner' => $rule[1],
            ]);
        }
    }
}
