<?php

namespace PayPal\Models\Database;

use Plenty\Modules\Plugin\DataBase\Contracts\Model;

/**
 * Class Account
 *
 * @property int $id
 * @property string $name
 * @property array $value
 * @property string $createdAt
 * @property string $updatedAt
 */
class Account extends Model
{
    public $id = 0;
    public $name = '';
    public $value = [];
    public $createdAt = '';
    public $updatedAt = '';

    /**
     * @return string
     */
    public function getTableName():string
    {
        return 'PayPal::accounts';
    }
}