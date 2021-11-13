<?php

namespace sample\dev\giiant\generators\model;

/**
 * Extension of the Giiant model Generator for customization and bug fixes
 */
class Generator extends \schmunk42\giiant\generators\model\Generator
{
    public $ignoreRelations = ['User'];
    public $ignoreRules = ['/\'exist\'/']; // ['/\'user_id\'.*?\'exist\'/'];
    public $queryInterfaceNs = 'rhertogh\\Yii2Oauth2Server\\interfaces\\models\\queries';

    /**
     * @inheritDoc
     */
    protected function generateRelations()
    {
        $tables = parent::generateRelations();

        foreach ($tables as $tableName => &$relations) {
            foreach ($relations as $relationName => $relationConfig) {
                if (in_array($relationConfig[1], $this->ignoreRelations)) {
                    unset($relations[$relationName]);
                }

                // hardcoded fix for https://github.com/yiisoft/yii2-gii/issues/55
                if ($tableName === 'oauth2_user_client_scope' && $relationName === 'User' && $relationConfig[1] === 'Oauth2UserClient') {
                    $relations['UserClient'] = $relations['User'];
                    unset($relations['User']);
                }
                if ($tableName === 'oauth2_scope' && $relationName === 'Users' && $relationConfig[1] === 'Oauth2UserClient') {
                    $relations['UserClients'] = $relations['Users'];
                    unset($relations['Users']);
                }
            }
        }

        return $tables;
    }

    /**
     * @inheritDoc
     */
    public function generateRules($table)
    {
        $rules =  parent::generateRules($table);

        foreach ($rules as $key => $rule) {
            foreach ($this->ignoreRules as $ignoreRule) {
                if (preg_match($ignoreRule, $rule)) {
                    unset($rules[$key]);
                    continue 2;
                }
            }
        }

        return array_values($rules);
    }

    /**
     * @inheritDoc
     */
    public function generateRelationName($relations, $table, $key, $multiple)
    {
        $name = parent::generateRelationName($relations, $table, $key, $multiple);
        if (strpos($name, 'Oauth2') === 0) {
            $name = substr($name, strlen('Oauth2'));
        }
        return $name;
    }
}
