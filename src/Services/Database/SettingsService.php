<?php //strict

namespace PayPal\Services\Database;

use PayPal\Models\Database\Settings;
use Plenty\Modules\Plugin\DynamoDb\Contracts\DynamoDbRepositoryContract;

/**
 * Class SettingsService
 * @package PayPal\Services
 */
class SettingsService extends DatabaseBaseService
{
    protected $tableName = 'settings';

    /**
     * SettingsService constructor.
     * @param DynamoDbRepositoryContract $dynamoDbRepositoryContract
     */
    public function __construct(DynamoDbRepositoryContract $dynamoDbRepositoryContract)
    {
        parent::__construct($dynamoDbRepositoryContract);
    }

    public function loadSettings()
    {
        return $this->getValue($this->tableName);
    }

    public function saveSettings($settings)
    {
        if($settings)
        {
            $settings = [
                'Webstore'      => ['N' => '1000'],
                'Value'         => ['S' => $settings]
            ];

            return $this->setValue($this->tableName, $settings);
        }
    }
}
