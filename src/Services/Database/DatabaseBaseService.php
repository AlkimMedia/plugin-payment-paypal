<?php

namespace PayPal\Services\Database;

use Plenty\Modules\Plugin\DataBase\Contracts\DataBase;
use Plenty\Modules\Plugin\DataBase\Contracts\Model;

class DatabaseBaseService
{
    /**
     * @var DataBase
     */
    public $dataBase;

    public function __construct(DataBase $dataBase)
    {
        $this->dataBase = $dataBase;
    }

    /**
     * Set the settings value
     *
     * @param Model $model
     *
     * @return bool
     */
    protected function setValue($model)
    {
        if($model instanceof Model)
        {
            return $this->dataBase->save($model);
        }
        return false;
    }

    /**
     * Delete the give model from the database
     *
     * @param Model $model
     * @return bool
     */
    public function deleteValue($model)
    {
        if($model instanceof Model)
        {
            return $this->dataBase->delete($model);
        }
        return false;
    }

    /**
     * Get the settings value
     *
     * @param string $modelClassName
     * @param mixed $key
     * @return bool|mixed
     */
    protected function getValue($modelClassName, $key)
    {
        $result = $this->dataBase->find($modelClassName, $key);

        if($result)
        {
            return $result;
        }
        return false;
    }

    /**
     * @param string $modelClassName
     * @return bool|string
     */
    protected function getValues($modelClassName, $field='', $value = '', $operator='=')
    {
        $query = $this->dataBase->query($modelClassName);

        $results = $query->get();

        if($results)
        {
            return $results;
        }
        return false;
    }
}