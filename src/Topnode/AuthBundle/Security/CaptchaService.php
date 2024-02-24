<?php

namespace App\Topnode\AuthBundle\Security;

class CaptchaService
{
    private $siteKey;
    private $secretKey;
    private $url;
    private $env;

    public function __construct(string $env)
    {
        $this->env = $env;
    }

    /**
     * Called on App\Topnode\BaseBundle\DependencyInjection\TopnodeBaseExtension
     * to parse all the configuration from config.yml and inject here.
     */
    public function setConfig(array $config)
    {
        $this->siteKey = $config['recaptcha']['sitekey'];
        $this->secretKey = $config['recaptcha']['secretkey'];
        $this->url = $config['recaptcha']['url'];

        if (0 === strlen($this->secretKey) || 0 === strlen($this->secretKey)) {
            throw new \Exception('Both the sitekey and the secretkey must be setted and not empty on the configuration files.');
        }
    }

    public function validate($captchaResponse)
    {
        if ('dev' == $this->env) {
            return true;
        }

        $data = [
            'secret' => $this->secretKey,
            'response' => $captchaResponse,
        ];

        $options = [
            'http' => [
                'header' => "Content-type: application/x-www-form-urlencoded\r\n",
                'method' => 'POST',
                'content' => http_build_query($data),
            ],
        ];

        $url = $this->url;
        $context = stream_context_create($options);

        $result = json_decode(file_get_contents($url, false, $context));

        return $result->success;
    }

    public function getSiteKey()
    {
        return $this->siteKey;
    }
}
