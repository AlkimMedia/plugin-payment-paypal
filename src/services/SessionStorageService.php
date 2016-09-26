<?php //strict

namespace PayPal\Services;

use Plenty\Modules\Frontend\Session\Storage\Contracts\FrontendSessionStorageFactoryContract;

/**
 * Class SessionStorageService
 * @package PayPal\Services
 */
class SessionStorageService
{
    /**
     * @var FrontendSessionStorageFactoryContract
     */
    private $sessionStorage;

    /**
     * SessionStorageService constructor.
     * @param FrontendSessionStorageFactoryContract $sessionStorage
     */
    public function __construct(FrontendSessionStorageFactoryContract $sessionStorage)
    {
        $this->sessionStorage = $sessionStorage;
    }

    /**
     * @param string $name
     * @param mixed $value
     */
    public function setSessionValue(string $name, mixed $value)
    {
        $this->sessionStorage->getPlugin()->setValue($name, $value);
    }

    /**
     * @param string $name
     * @return mixed
     */
    public function getSessionValue(string $name):mixed
    {
        return $this->sessionStorage->getPlugin()->getValue($name);
    }
}
