<?php

namespace PayPal\Services\Database;

use Plenty\Modules\Plugin\DynamoDb\Contracts\DynamoDbRepositoryContract;

class DatabaseBaseService
{
    /**
     * @var DynamoDbRepositoryContract
     */
    public $dynamoDbRepositoryContract;

    public function __construct(DynamoDbRepositoryContract $dynamoDbRepositoryContract)
    {
        $this->dynamoDbRepositoryContract = $dynamoDbRepositoryContract;
    }

    /**
     * Set the settings value
     *
     * @param string $table
     * @param array $item
     *
     * @return bool
     */
    protected function setValue($table, $item)
    {
        return $this->dynamoDbRepositoryContract->putItem('PayPal', $table, $item);
    }

    /**
     * Get the settings value
     *
     * @param string $name
     * @return bool|mixed
     * @throws \Exception
     */
    protected function getValue($table)
    {
        /** @var Settings $settings */
        $settings = $this->dynamoDbRepositoryContract->getItem('PayPal', 'settings', true, ['Webstore'=>'1000']);

        if($settings)
        {
            return $settings;
        }
        return false;
    }
}