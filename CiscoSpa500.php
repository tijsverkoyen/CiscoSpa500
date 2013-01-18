<?php

namespace TijsVerkoyen\CiscoSpa500;

/**
 * CiscoSpa500 class
 *
 * @author		Tijs Verkoyen <php-ciscospa500@verkoyen.eu>
 * @version		3.0.0
 * @copyright	Copyright (c) Tijs Verkoyen. All rights reserved.
 * @license		BSD License
 */
class CiscoSpa500
{
    // internal constant to enable/disable debugging
    const DEBUG = true;

    // current version
    const VERSION = '1.0.0';

	/**
	 * @var The ip of the phone
	 */
	private $ip;

    /**
     * The timeout
     *
     * @var int
     */
    private $timeOut = 60;

    /**
     * The user agent
     *
     * @var string
     */
    private $userAgent;

    // class methods
    /**
     * Create an instance
     *
     * @param string $token  The token to use.
     * @param string $apiUrl The url of the API.
     */
    public function __construct($ip)
    {
	    $this->setIp($ip);
    }

    /**
     * Make a call
     *
     * @param  string           $path       The method to be called.
     * @param  array[optional]  $parameters The parameters to pass.
     * @param  string[optional] $method     The method to use. Possible values are GET or POST.
     * @return mixed
     */
    private function doCall($path, array $parameters = null, $method = 'GET')
    {
        // redefine
        $path = (string) $path;
        $parameters = (array) $parameters;

        // init var
        $options = array();

        // build the url
        $url = 'http://' . $this->getIp() . '/' . $path;

        // HTTP method
        if ($method == 'POST') {
            $options[CURLOPT_POST] = true;
            $options[CURLOPT_POSTFIELDS] = http_build_query($parameters);
        } else {
            $options[CURLOPT_POST] = false;
            if (!empty($parameters)) {
                $url .= '&' . http_build_query($parameters);
            }
        }

        // set options
        $options[CURLOPT_URL] = $url;
        $options[CURLOPT_USERAGENT] = $this->getUserAgent();
        if (ini_get('open_basedir') == '' && ini_get('safe_mode' == 'Off')) {
            $options[CURLOPT_FOLLOWLOCATION] = true;
        }
        $options[CURLOPT_RETURNTRANSFER] = true;
        $options[CURLOPT_TIMEOUT] = (int) $this->getTimeOut();
        $options[CURLOPT_SSL_VERIFYPEER] = false;
        $options[CURLOPT_SSL_VERIFYHOST] = false;

        // init
        $curl = curl_init();

        // set options
        curl_setopt_array($curl, $options);

        // execute
        $response = curl_exec($curl);
        $headers = curl_getinfo($curl);

        // fetch errors
        $errorNumber = curl_errno($curl);
        $errorMessage = curl_error($curl);

        // close
        curl_close($curl);

        // return
        return $response;
    }

	/**
	 * Get the ip
	 *
	 * @return string
	 */
	public function getIp()
	{
		return $this->ip;
	}

    /**
     * Get the timeout that will be used
     *
     * @return int
     */
    public function getTimeOut()
    {
        return (int) $this->timeOut;
    }

    /**
     * Get the useragent that will be used.
     * Our version will be prepended to yours.
     * It will look like: "PHP CiscoSpa500/<version> <your-user-agent>"
     *
     * @return string
     */
    public function getUserAgent()
    {
        return (string) 'PHP CiscoSpa500/' . self::VERSION . ' ' . $this->userAgent;
    }

	/**
	 * Set the ip of the phone
	 *
	 * @param string $ip
	 */
	public function setIp($ip)
	{
		$this->ip = (string) $ip;
	}

    /**
     * Set the timeout
     * After this time the request will stop.
     * You should handle any errors triggered by this.
     *
     * @param $seconds int timeout in seconds.
     */
    public function setTimeOut($seconds)
    {
        $this->timeOut = (int) $seconds;
    }

    /**
     * Set the user-agent for you application
     * It will be appended to ours, the result will look like: "PHP
     * CiscoSpa500/<version> <your-user-agent>"
     *
     * @param $userAgent string user-agent, it should look like
     *        <app-name>/<app-version>.
     */
    public function setUserAgent($userAgent)
    {
        $this->userAgent = (string) $userAgent;
    }

	/**
	 * Get the call log
	 *
	 * @return array
	 */
	public function getCallLog()
	{
		$data = $this->doCall('calllog.htm');
		$return = array();

		$tabs = array();
		preg_match_all('|<div.*class="tab-page".*id="(.*)">.*<table.*class="stat".*>(.*)</div>|iUms', $data, $tabs);

		if(isset($tabs[1]))
		{
			// loop tabs
			foreach($tabs[1] as $i => $row)
			{
				$return[$row] = array();

				$lines = array();
				preg_match_all('|.*<td>&nbsp;(.*)(<td>)+|iUm', $tabs[2][$i], $lines);

				if(isset($lines[1]))
				{
					foreach($lines[1] as $line)
					{
						if(trim($line) == '') continue;
						$values = explode(',', $line);

						if(count($values) < 4)
						{
							array_unshift($values, null);
						}

						$keys = array('from', 'to', 'time', 'duration');

						$values = array_combine($keys, $values);

						foreach($values as $key => $value)
						{
							$values[$key] = trim($value);
						}

						$return[$row][] = $values;
					}
				}
			}
		}

		return $return;
	}
}
