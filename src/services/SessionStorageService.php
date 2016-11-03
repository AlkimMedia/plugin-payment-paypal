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
     * Set the session value
     *
     * @param string $name
     * @param array $value
     */
    public function setSessionValue(string $name, array $value)
    {
        $this->sessionStorage->getPlugin()->setValue($name, $value);
    }

    /**
     * Get the session value
     *
     * @param string $name
     * @return array
     */
    public function getSessionValue(string $name):mixed
    {
        return $this->sessionStorage->getPlugin()->getValue($name);
    }
}
