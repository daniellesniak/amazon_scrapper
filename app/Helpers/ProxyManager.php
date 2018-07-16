<?php

namespace App\Helpers;

class ProxyManager
{
    protected $proxies = [];
    public $validateCount = 0;

    /**
     * ProxyManager constructor.
     * @param $fileLocation
     */
    public function __construct($fileLocation)
    {
        $this->load($fileLocation);
    }

    /**
     * @param $fileLocation
     * @return bool
     */
    private function load($fileLocation): bool
    {
        $proxies = file_get_contents($fileLocation);
        $this->proxies = explode(PHP_EOL, $proxies);

        return true;
    }

    public function getValidatedProxies()
    {
        $validatedProxies = $this->validate($this->proxies);
        $this->validateCount = count($validatedProxies);

        return $validatedProxies;
    }

    private function validate($proxies)
    {
        $successfullyValidated = [];
        foreach ($proxies as $proxy) {
            list($host, $port) = explode(':', $proxy);

            if (empty($host) || empty($port)) {
                continue;
            }

            if (!empty($host) && !empty($port) && $connection = @fsockopen($host, $port,$errorCode, $errorMessage, 5)) {
                $successfullyValidated[] = "{$host}:{$port}";
                fclose($connection);
            }
        }

        return $successfullyValidated;
    }
}