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
    }

    /**
     * Get the settings value
     *
     * @param Model $model
     * @param mixed $key
     * @return bool|mixed
     */
    protected function getValue($model, $key)
    {
        $result = $this->dataBase->find($model, $key);

        if($result)
        {
            return $result;
        }
        return false;
    }

    /**
     * @param Model $model
     * @return bool|string
     */
    protected function getValues($model, $field='', $value = '', $operator='=')
    {
        $query = $this->dataBase->query($model);

        $results = $query->get();

        if($results)
        {
            return $results;
        }
        return false;
    }
}