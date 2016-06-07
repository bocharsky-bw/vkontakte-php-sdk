<?php

namespace BW;

/**
 * The Vkontakte PHP SDK
 *
 * @author Victor Bocharsky <bocharsky.bw@gmail.com>
 */
class Vkontakte
{
    /**
     * The API version used in queries
     *
     * @link https://vk.com/dev/versions API version list
     */
    private $apiVersion = '5.37';

    /**
     * The client ID (app ID)
     *
     * @var string
     */
    private $clientId;

    /**
     * The client secret key
     *
     * @var string
     */
    private $clientSecret;

    /**
     * The scope for login URL
     *
     * @var array
     */
    private $scope = array();

    /**
     * The URL to which the user will be redirected
     *
     * @var string
     */
    private $redirectUri;

    /**
     * The response type of login URL
     *
     * @var string
     */
    private $responceType = 'code';

    /**
     * The current access token
     *
     * @var array
     */
    private $accessToken;

    /**
     * The type of connection
     *
     * @var boolean
     */
    private $persistentConnect = true;

    /**
     * The custom string which VK will return back.
     * 
     * @var string
    */
    private $state;

    /**
     * The connection
     *
     * @var resource
     */
    private static $connection;

    /**
     * @var bool
     */
    private $IPv6Disabled = false;

    /**
     * The Vkontakte instance constructor for quick configuration
     *
     * @param array $config
     */
    public function __construct(array $config = array())
    {
        if (isset($config['api_version'])) {
            $this->setApiVersion($config['api_version']);
        }
        if (isset($config['client_id'])) {
            $this->setClientId($config['client_id']);
        }
        if (isset($config['client_secret'])) {
            $this->setClientSecret($config['client_secret']);
        }
        if (isset($config['scope'])) {
            $this->setScope($config['scope']);
        }
        if (isset($config['redirect_uri'])) {
            $this->setRedirectUri($config['redirect_uri']);
        }
        if (isset($config['response_type'])) {
            $this->setResponceType($config['response_type']);
        }
        if (isset($config['persistent_connect'])) {
            $this->setPersistentConnect($config['persistent_connect']);
        }
        if (isset($config['state'])) {
            $this->setState($config['state']);
        }
        if (isset($config['ipv6_disabled'])) {
            $this->disableIPv6();
        }
    }

    /**
     * Destruct method
     */
    public function __destruct()
    {
        if (is_resource(static::$connection)) {
            curl_close(static::$connection);
        }
    }

    /**
     * Get the user id of current access token
     *
     * @return string|null
     */
    public function getUserId()
    {
        return isset($this->accessToken['user_id']) ? $this->accessToken['user_id'] : null;
    }

    /**
     * Get the user email of current access token. Email should be requested in scope first.
     *
     * @return string|null
     */
    public function getUserEmail()
    {
        return isset($this->accessToken['email']) ? $this->accessToken['email'] : null;
    }

    /**
     * Get the login URL for Vkontakte sign in
     *
     * @return string
     */
    public function getLoginUrl()
    {
        // required params
        $params = array(
            'client_id' => $this->getClientId(),
            'redirect_uri' => $this->getRedirectUri(),
        );
        // optional params
        if ($this->getScope()) {
            $params['scope'] = implode(',', $this->getScope());
        }
        if ($this->getResponceType()) {
            $params['response_type'] = $this->getResponceType();
        }
        if ($this->apiVersion) {
            $params['v'] = $this->apiVersion;
        }
        if ($this->state) {
            $params['state'] = $this->state;
        }

        return 'https://oauth.vk.com/authorize?' . http_build_query($params);
    }

    /**
     * Authenticate user and get access token from server
     *
     * @param string $code
     *
     * @return $this
     */
    public function authenticate($code = null)
    {
        if (null === $code) {
            if (isset($_GET['code'])) {
                $code = $_GET['code'];
            }
        }

        $url = 'https://oauth.vk.com/access_token?' . http_build_query(array(
                'client_id' => $this->getClientId(),
                'client_secret' => $this->getClientSecret(),
                'code' => $code,
                'redirect_uri' => $this->getRedirectUri(),
            ));

        $token = $this->curl($url);
        $decodedToken = json_decode($token, true);
        $decodedToken['created'] = time(); // add access token created unix timestamp to array

        $this->setAccessToken($decodedToken);

        return $this;
    }

    /**
     * Make an API call to https://api.vk.com/method/
     *
     * @param string $method API method name
     * @param array $query API method params
     *
     * @return mixed The response
     *
     * @throws \Exception
     */
    public function api($method, array $query = array())
    {
        /* Generate query string from array */
        foreach ($query as $param => $value) {
            if (is_array($value)) {
                // implode values of each nested array with comma
                $query[$param] = implode(',', $value);
            }
        }
        $query['access_token'] = isset($this->accessToken['access_token'])
            ? $this->accessToken['access_token']
            : '';
        if (empty($query['v'])) {
            $query['v'] = $this->getApiVersion();
        }
        $url = 'https://api.vk.com/method/' . $method . '?' . http_build_query($query);
        $result = json_decode($this->curl($url), true);

        if (isset($result['response'])) {
            return $result['response'];
        }

        return $result;
    }

    /**
     * Check is access token expired
     *
     * @return boolean
     */
    public function isAccessTokenExpired()
    {
        return time() > $this->accessToken['created'] + $this->accessToken['expires_in'];
    }

    /**
     * Set the API version
     *
     * @param string $apiVersion
     *
     * @return $this
     */
    public function setApiVersion($apiVersion)
    {
        $this->apiVersion = $apiVersion;

        return $this;
    }

    /**
     * Get the API version
     *
     * @return string
     */
    public function getApiVersion()
    {
        return $this->apiVersion;
    }

    /**
     * Set the client ID (app ID)
     *
     * @param string $clientId
     *
     * @return $this
     */
    public function setClientId($clientId)
    {
        $this->clientId = $clientId;

        return $this;
    }

    /**
     * Get the client ID (app ID)
     *
     * @return string
     */
    public function getClientId()
    {
        return $this->clientId;
    }

    /**
     * Set the client secret key
     *
     * @param string $clientSecret
     *
     * @return $this
     */
    public function setClientSecret($clientSecret)
    {
        $this->clientSecret = $clientSecret;

        return $this;
    }

    /**
     * Get the client secret key
     *
     * @return string
     */
    public function getClientSecret()
    {
        return $this->clientSecret;
    }

    /**
     * Set the scope for login URL
     *
     * @param array $scope
     *
     * @return $this
     */
    public function setScope(array $scope)
    {
        $this->scope = $scope;

        return $this;
    }

    /**
     * Get the scope for login URL
     *
     * @return array
     */
    public function getScope()
    {
        return $this->scope;
    }

    /**
     * Set the URL to which the user will be redirected
     *
     * @param string $redirectUri
     *
     * @return $this
     */
    public function setRedirectUri($redirectUri)
    {
        $this->redirectUri = $redirectUri;

        return $this;
    }

    /**
     * Get the URL to which the user will be redirected
     *
     * @return string
     */
    public function getRedirectUri()
    {
        return $this->redirectUri;
    }

    /**
     * Set the response type of login URL
     *
     * @param string $responceType
     *
     * @return $this
     */
    public function setResponceType($responceType)
    {
        $this->responceType = $responceType;

        return $this;
    }

    /**
     * Get the response type of login URL
     *
     * @return string
     */
    public function getResponceType()
    {
        return $this->responceType;
    }

    /**
     * Set option enable for persistent connection
     *
     * @param boolean $enable
     *
     * @return $this
     */
    public function setPersistentConnect($enable)
    {
        $this->persistentConnect = (boolean)$enable;

        return $this;
    }

    /**
     * Whether the status of type connection is persistent
     *
     * @return boolean
     */
    public function isPersistentConnect()
    {
        return $this->persistentConnect;
    }

    /**
     * Set the access token
     *
     * @param string|array $token The access token in json|array format
     *
     * @return $this
     */
    public function setAccessToken($token)
    {
        if (is_string($token)) {
            $this->accessToken = json_decode($token, true);
        } else {
            $this->accessToken = (array)$token;
        }

        return $this;
    }

    /**
     * Get the access token
     *
     * @return array|null The access token
     */
    public function getAccessToken()
    {
        return $this->accessToken;
    }

    /**
     * Set the state string 
     * 
     * @param string $state. Custom string for returning in response from Vkontakte.
     * 
     * @return $this
     */
    public function setState($state)
    {
        $this->state = $state;
        
        return $this;
    }
    
    /**
     * Get the state string
     *
     * @return string|null 
     */
    public function getState()
    {
        return $this->state;
    }

    public function disableIPv6()
    {
        $this->IPv6Disabled = true;
    }
    
    public function enableIPv6()
    {
        $this->IPv6Disabled = false;
    }

    /**
     * @return bool
     */
    public function isIPv6Disabled()
    {
        return $this->IPv6Disabled;
    }
    
    /**
     * Make the curl request to specified url
     *
     * @param string $url The url for curl() function
     *
     * @return mixed The result of curl_exec() function
     *
     * @throws \Exception
     */
    protected function curl($url)
    {
        // create curl resource
        if ($this->persistentConnect) {
            if (!is_resource(static::$connection)) {
                static::$connection = curl_init();
            }
            $ch = static::$connection;
        } else {
            $ch = curl_init();
        }

        // set url
        curl_setopt($ch, CURLOPT_URL, $url);
        // return the transfer as a string
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        // disable SSL verifying
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        
        if ($this->IPv6Disabled) {
            curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
        }

        // $output contains the output string
        $result = curl_exec($ch);

        if (!$result) {
            $errno = curl_errno($ch);
            $error = curl_error($ch);
        }

        if (!$this->persistentConnect) {
            // close curl resource to free up system resources
            curl_close($ch);
        }

        if (isset($errno) && isset($error)) {
            throw new \Exception($error, $errno);
        }

        return $result;
    }

}
