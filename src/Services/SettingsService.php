<?php //strict

namespace PayPal\Services;

use PayPal\Models\Settings;
use Plenty\Modules\Plugin\DataBase\Contracts\DataBase;

/**
 * Class SettingsService
 * @package PayPal\Services
 */
class SettingsService
{
    const WEB_PROFILE = "webProfile";
    const SETTINGS = "settings";
    const ACCOUNTS = "accounts";


    /**
     * @var array
     */
    private $settingsID = [
        self::WEB_PROFILE   =>  1,
        self::SETTINGS      =>  2,
        self::ACCOUNTS      =>  3];

    /**
     * @var DataBase
     */
    private $dataBase;

    /**
     * SettingsService constructor.
     * @param DataBase $dataBase
     */
    public function __construct(DataBase $dataBase)
    {
        $this->dataBase = $dataBase;
    }

    /**
     * Set the settings value
     *
     * @param string $name
     * @param $value
     * @throws \Exception
     */
    public function setSettingsValue(string $name, $value)
    {
        if(!array_key_exists($name, $this->settingsID))
        {
            throw new \Exception('The given settings name is not defined!');
        }

        $settings = pluginApp(Settings::class);

        if($settings instanceof Settings)
        {
            $settings->id        = $this->settingsID[$name];
            $settings->name      = $name;
            $settings->value     = (string) json_encode($value);
            $settings->createdAt = date('Y-m-d H:i:s');
            $settings->updatedAt = date('Y-m-d H:i:s');

            $this->dataBase->save($settings);
        }
    }

    /**
     * Get the settings value
     *
     * @param string $name
     * @return bool|mixed
     * @throws \Exception
     */
    public function getSettingsValue(string $name)
    {
        if(!array_key_exists($name, $this->settingsID))
        {
            throw new \Exception('The given settings name is not defined!');
        }

        /** @var Settings $settings */
        $settings = $this->dataBase->find(Settings::class, $this->settingsID[$name]);

        if($settings instanceof Settings)
        {
            return $settings->value;
        }

        return false;
    }
}
