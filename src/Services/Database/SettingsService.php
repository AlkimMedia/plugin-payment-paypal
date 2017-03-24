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

    public function loadSetting($webstore, $mode)
    {
        $setting = $this->getValues(Settings::class, ['name', 'webstore'], [$mode, $webstore], ['=','=']);
        if(is_array($setting) && $setting[0] instanceof Settings)
        {
            return $setting[0]->value;
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
                    $settings[] = ['PID_'.$item->webstore => $item->value];
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
                    $id = 0;
                    $store = (int)str_replace('PID_', '', $store);

                    if($store > 0)
                    {
                        $existValue = $this->getValues(Settings::class, ['name', 'webstore'], [$mode, $store], ['=','=']);
                        if(isset($existValue) && is_array($existValue))
                        {
                            if($existValue[0] instanceof Settings)
                            {
                                $id = $existValue[0]->id;
                            }
                        }

                        /** @var Settings $settingModel */
                        $settingModel = pluginApp(Settings::class);
                        if($id > 0)
                        {
                            $settingModel->id = $id;
                        }
                        $settingModel->webstore = $store;
                        $settingModel->name = $mode;
                        $settingModel->value = $values;
                        $settingModel->updatedAt = date('Y-m-d H:i:s');

                        if($settingModel instanceof Settings)
                        {
                            $this->setValue($settingModel);
                        }
                    }
                }
            }
            return 1;
        }
    }

    public function saveSetting($setting)
    {

    }
}
