<?php

namespace PayPal\Migrations;

use Plenty\Modules\Plugin\DynamoDb\Contracts\DynamoDbRepositoryContract;

/**
 * Class CreateSettingsTable
 */
class CreateSettingsTable
{
    /**
     * @param DynamoDbRepositoryContract $dynamoDbRepositoryContract
     */
    public function run(DynamoDbRepositoryContract $dynamoDbRepositoryContract)
    {
        $dynamoDbRepositoryContract->createTable(
            'PayPal',
            'settings',
            [
                [
                    'AttributeName' => 'Webstore',
                    'AttributeType' => 'N',
                ]
            ],
            [
                [
                    'AttributeName' => 'Webstore',
                    'KeyType'       => 'HASH'
                ]
            ]
        );

        $dynamoDbRepositoryContract->createTable(
            'PayPal',
            'accounts',
            [
                [
                    'AttributeName' => 'Email',
                    'AttributeType' => 'S',
                ]
            ],
            [
                [
                    'AttributeName' => 'Email',
                    'KeyType'       => 'RANGE'
                ]
            ]
        );
    }
}