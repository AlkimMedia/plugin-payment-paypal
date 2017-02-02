<?php //strict

namespace PayPal\Services\Database;

use Illuminate\Support\Facades\App;
use PayPal\Models\Database\Settings;
use Plenty\Modules\Plugin\DataBase\Contracts\DataBase;
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
    public function __construct(DataBase $dataBase)
    {
        parent::__construct($dataBase);
    }

    public function loadSetting($webstore)
    {
        $setting = $this->getValue(Settings::class, $webstore);
        if($setting instanceof Settings)
        {
            return $setting->value;
        }
        return null;
    }

    public function loadSettings($settingType)
    {
        $settings = array();
        $results = $this->getValues(Settings::class);
        if(is_array($results))
        {
            foreach ($results as $item)
            {
                if($item instanceof Settings && $item->name == $settingType)
                {
                    $settings[] = ['PID_'.$item->id => $item->value];
                }
            }
        }
        return $settings;
    }

    public function saveSettings($mode, $settings)
    {
        if($settings)
        {
            foreach ($settings as $setting)
            {
                foreach ($setting as $store => $values)
                {
                    $store = str_replace('PID_', '', $store);
                    /** @var Settings $settingModel */
                    $settingModel = pluginApp(Settings::class);
                    $settingModel->id = $store;
                    $settingModel->name = 'settings';
                    $settingModel->value = [$mode => $values];
                    $settingModel->updatedAt = date('Y-m-d H:i:s');

                    $this->setValue($settingModel);
                }
            }

            return true;
        }
    }

    public function saveSetting($setting)
    {

    }
}
