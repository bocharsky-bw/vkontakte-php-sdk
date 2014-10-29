<?php

namespace BW;

/**
 * The Vkontakte PHP SDK
 *
 * @author Bocharsky Victor
 */
class Vkontakte
{
    const VERSION = '5.25';

    /**
     * The application ID
     * @var string
     */
    private $appId;
    
    /**
     * The application secret key
     * @var string
     */
    private $secretKey;
    
    /**
     * The scope for login URL
     * @var array
     */
    private $scope = array();
    
    /**
     * The URL to which the user will be redirected
     * @var string
     */
    private $redirectUri;
    
    /**
     * The response type of login URL
     * @var string
     */
    private $responceType = 'code';
    
    /**
     * The current access token
     * @var \StdClass
     */
    private $accessToken;
    

    /**
     * The Vkontakte instance constructor for quick configuration
     * @param array $config
     */
    public function __construct(array $config = array())
    {
        if (isset($config['app_id'])) {
            $this->setAppId($config['app_id']);
        }
        if (isset($config['secret_key'])) {
            $this->setSecretKey($config['secret_key']);
        }
        if (isset($config['scopes'])) {
            $this->setScope($config['scopes']);
        }
        if (isset($config['redirect_uri'])) {
            $this->setRedirectUri($config['redirect_uri']);
        }
        if (isset($config['response_type'])) {
            $this->setResponceType($config['response_type']);
        }
    }
    
    
    /**
     * Get the user id of current access token
     * 
     * @return string
     */
    public function getUserId()
    {
        return $this->accessToken->user_id;
    }
    
    /**
     * Get the login URL for Vkontakte sign in
     * 
     * @return string
     */
    public function getLoginUrl()
    {
        return 'https://oauth.vk.com/authorize?' . http_build_query(array(
            'client_id'     => $this->getAppId(),
            'scope'         => implode(',', $this->getScope()),
            'redirect_uri'  => $this->getRedirectUri(),
            'response_type' => $this->getResponceType(),
            'v'             => self::VERSION,
        ));
    }
    
    /**
     * Authenticate user and get access token from server
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
            'client_id'     => $this->getAppId(),
            'client_secret' => $this->getSecretKey(),
            'code'          => $code,
            'redirect_uri'  => $this->getRedirectUri(),
        ));

        $token = $this->curl($url);
        $data = json_decode($token);
        $data->created = time(); // add access token created unix timestamp to object
        $token = json_encode($data);
        
        $this->setAccessToken($token);

        return $this;
    }
    
    /**
     * Make an API call to https://api.vk.com/method/
     * 
     * @return string The response, decoded from json format
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
        $query['access_token'] = $this->accessToken->access_token;
        $url = 'https://api.vk.com/method/' . $method . '?' . http_build_query($query);
        $result = json_decode($this->curl($url));
        
        if (isset($result->response)) {
            return $result->response;
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
        return time() > $this->accessToken->created + $this->accessToken->expires_in;
    }
    
    /**
     * Set the application id
     * @param string $appId
     * 
     * @return $this
     */
    public function setAppId($appId)
    {
        $this->appId = $appId;
        
        return $this;
    }
    
    /**
     * Get the application id
     * 
     * @return string
     */
    public function getAppId()
    {
        return $this->appId;
    }
    
    /**
     * Set the application secret key
     * @param string $secretKey
     * 
     * @return $this
     */
    public function setSecretKey($secretKey)
    {
        $this->secretKey = $secretKey;
        
        return $this;
    }
    
    /**
     * Get the application secret key
     * 
     * @return string
     */
    public function getSecretKey()
    {
        return $this->secretKey;
    }
    
    /**
     * Set the scope for login URL
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
     * Set the access token
     * @param string $token The access token in json format
     * 
     * @return $this
     */
    public function setAccessToken($token)
    {
        $this->accessToken = json_decode($token);
        
        return $this;
    }
    
    /**
     * Get the access token
     * @param string $code
     * 
     * @return string The access token in json format
     */
    public function getAccessToken()
    {
        return json_encode($this->accessToken);
    }
    
    /**
     * Make the curl request to specified url
     * @param string $url The url for curl() function
     * 
     * @return mixed The result of curl_exec() function
     * 
     * @throws \Exception
     */
    protected function curl($url)
    {
        // create curl resource
        $ch = curl_init();

        // set url
        curl_setopt($ch, CURLOPT_URL, $url);
        // return the transfer as a string
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        // disable SSL verifying
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

        // $output contains the output string
        $result = curl_exec($ch);
        
        if ( ! $result) {
            $errno = curl_errno($ch);
            $error = curl_error($ch);
        }
        
        // close curl resource to free up system resources
        curl_close($ch);
        
        if (isset($errno) && isset($error)) {
            throw new \Exception($error, $errno);
        }

        return $result;
    }
}
