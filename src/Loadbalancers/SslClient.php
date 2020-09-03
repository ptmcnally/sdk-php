<?php

namespace UKFast\SDK\Loadbalancers;

use UKFast\SDK\Entities\ClientEntityInterface;
use UKFast\SDK\Entity;
use UKFast\SDK\Loadbalancers\Entities\Ssl;
use UKFast\SDK\Traits\PageItems;

class SslClient extends Client implements ClientEntityInterface
{
    use PageItems {
        createEntity as create;
    }

    protected $collectionPath = 'v2/ssls';

    public static function getEntityMap()
    {
        return [
            'binds_id' => 'bindsId',
            'disable_http2' => 'disableHttp2',
            'http2_only' => 'onlyHttp2',
            'custom_ciphers' => 'customCiphers',
            'custom_tls13_ciphers' => 'customTls13Ciphers',
        ];
    }

//    /**
//     * @param Ssl $ssl
//     * @return \UKFast\SDK\SelfResponse
//     */
//    public function createEntity(Ssl $ssl)
//    {
//        return $this->create(static::sslToApiFormat($ssl));
//    }


    /**
     * @param $ssl
     * @return array
     */
    public static function sslToApiFormat($ssl)
    {
        $apiFormat = $ssl;
        if ($ssl instanceof Entity) {
            $apiFormat = $ssl->toArray();
        }

        if (isset($apiFormat['allowTls'])) {
            $apiFormat['allow_tlsv1'] = in_array('1.0', $ssl->allowTls);
            $apiFormat['allow_tlsv11'] = in_array('1.1', $ssl->allowTls);
            unset($apiFormat['allowTls']);
        }

        return $apiFormat;
    }

    /**
     * @param $data
     * @return Ssl
     */
    public function loadEntity($data)
    {
        $allowTls = [];
        if ($data['allow_tlsv1']) {
            $allowTls[] = '1.0';
        }

        if ($data['allow_tlsv11']) {
            $allowTls[] = '1.1';
        }

        unset($data['allow_tlsv11']);
        unset($data['allow_tlsv1']);

        $ssl = new Ssl($this->apiToFriendly($data, $this->getEntityMap()));
        $ssl->allowTls = $allowTls;
        return $ssl;
    }
}
