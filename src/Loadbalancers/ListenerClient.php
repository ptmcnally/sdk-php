<?php

namespace UKFast\SDK\Loadbalancers;

use UKFast\SDK\Entities\ClientEntityInterface;
use UKFast\SDK\Loadbalancers\Entities\AccessRule;
use UKFast\SDK\Loadbalancers\Entities\Bind;
use UKFast\SDK\Loadbalancers\Entities\Cert;
use UKFast\SDK\Loadbalancers\Entities\Listener;
use UKFast\SDK\SelfResponse;
use UKFast\SDK\Traits\PageItems;

class ListenerClient extends Client implements ClientEntityInterface
{
    use PageItems;

    const BIND_MAP = [
        'frontend_id' => 'frontendId',
        'vips_id' => 'vipsId',
    ];

    const CERT_MAP = [
        'frontend_id' => 'frontendId',
        'certs_name' => 'name',
        'certs_pem' => 'pem',
    ];

    const ACCESS_RULE_MAP = [
        'frontend_id' => 'frontendId',
        'whitelist' => 'whitelist',
    ];

    protected $collectionPath = 'v2/frontends';

    public function getEntityMap()
    {
        return [
            'vips_id' => 'vipsId',
            'config_id' => 'configId',
            'hsts_enabled' => 'hstsEnabled',
            'hsts_maxage' => 'hstsMaxAge',
            'redirect_https' => 'redirectHttps',
            'default_backend_id' => 'defaultBackendId',
        ];
    }

    /**
     * Gets a page of SSLs
     *
     * @param int $id
     * @param int $page
     * @param int $perPage
     * @param array $filters
     * @return \UKFast\SDK\Page
     */
    public function getSsls($id, $page = 1, $perPage = 15, $filters = [])
    {
        $filters = $this->friendlyToApi(SslClient::sslToApiFormat($filters), SslClient::getEntityMap());
        $page = $this->paginatedRequest("v2/frontends/$id/ssls", $page, $perPage, $filters);

        $page->serializeWith(function ($item) {
            return $this->ssls()->loadEntity((array) $item);
        });

        return $page;
    }

    /**
     * Gets a page of Binds
     *
     * @param int $id
     * @param int $page
     * @param int $perPage
     * @param array $filters
     * @return \UKFast\SDK\Page
     */
    public function getBinds($id, $page = 1, $perPage = 15, $filters = [])
    {
        $filters = $this->friendlyToApi($filters, self::BIND_MAP);
        $page = $this->paginatedRequest("v2/frontends/$id/binds", $page, $perPage, $filters);
        $page->serializeWith(function ($item) {
            return new Bind($this->apiToFriendly($item, self::BIND_MAP));
        });

        return $page;
    }
    
    /**
     * Gets a page of Certs
     *
     * @param int $id
     * @param int $page
     * @param int $perPage
     * @param array $filters
     * @return \UKFast\SDK\Page
     */
    public function getCerts($id, $page = 1, $perPage = 15, $filters = [])
    {
        $filters = $this->friendlyToApi($filters, self::CERT_MAP);
        $page = $this->paginatedRequest("v2/frontends/$id/certs", $page, $perPage, $filters);
        $page->serializeWith(function ($item) {
            return new Cert($this->apiToFriendly($item, self::CERT_MAP));
        });

        return $page;
    }

    /**
     * Gets a page of listener access rules
     *
     * @param int $id Listener ID
     * @param int $page
     * @param int $perPage
     * @param array $filters
     * @return \UKFast\SDK\Page
     */
    public function getAccessRulePage($id, $page = 1, $perPage = 15, $filters = [])
    {
        $filters = $this->friendlyToApi($filters, self::ACCESS_RULE_MAP);
        $page = $this->paginatedRequest("v2/frontends/$id/access", $page, $perPage, $filters);
        $page->serializeWith(function ($item) {
            return new AccessRule($this->apiToFriendly($item, self::ACCESS_RULE_MAP));
        });

        return $page;
    }

    /**
     * Get an access rule for a listener by ID
     * @param $id
     * @param $accessRuleId
     * @return AccessRule
     */
    public function getAccessRuleById($id, $accessRuleId)
    {
        $response = $this->request("GET", "v2/frontends/$id/access/$accessRuleId");
        $body = $this->decodeJson($response->getBody()->getContents());
        return new AccessRule($this->apiToFriendly($body->data, self::ACCESS_RULE_MAP));
    }

    /**
     * Creates a new listener
     * @param Listener $listener
     * @return \UKFast\SDK\SelfResponse
     */
    public function create($listener)
    {
        $json = json_encode($this->friendlyToApi($listener, self::MAP));
        $response = $this->post("v2/frontends", $json);
        $response = $this->decodeJson($response->getBody()->getContents());
        
        return (new SelfResponse($response))
            ->setClient($this)
            ->serializeWith(function ($response) {
                return new Listener($this->apiToFriendly($response->data, self::MAP));
            });
    }

    /**
     * Creates a new SSL
     * @param \UKFast\SDK\Loadbalancers\Entities\Ssl $ssl
     * @return \UKFast\SDK\SelfResponse
     */
    public function addSsl($id, $ssl)
    {
        $json = json_encode($this->friendlyToApi(SslClient::sslToApiFormat($ssl), SslClient::getEntityMap()));
        $response = $this->post("v2/frontends/$id/ssls", $json);
        $response = $this->decodeJson($response->getBody()->getContents());
        
        return (new SelfResponse($response))
            ->setClient($this)
            ->serializeWith(function ($response) {
                return $this->ssls()->loadEntity((array) $response->data);
            });
    }

    /**
     * Creates a new Binding
     * @param \UKFast\SDK\Loadbalancers\Entities\Bind $ssl
     * @return \UKFast\SDK\SelfResponse
     */
    public function addBind($id, $bind)
    {
        $json = json_encode($this->friendlyToApi($bind, self::BIND_MAP));
        $response = $this->post("v2/frontends/$id/binds", $json);
        $response = $this->decodeJson($response->getBody()->getContents());
        
        return (new SelfResponse($response))
            ->setClient($this)
            ->serializeWith(function ($response) {
                return new Bind($this->apiToFriendly($response->data, self::BIND_MAP));
            });
    }

    /**
     * Creates a new listener access rule
     * @param $id Listener ID
     * @return \UKFast\SDK\SelfResponse
     */
    public function addAccessRule($id)
    {
        $response = $this->post("v2/frontends/$id/access", null);
        $response = $this->decodeJson($response->getBody()->getContents());
        
        return (new SelfResponse($response))
            ->setClient($this)
            ->serializeWith(function ($response) {
                return new AccessRule($this->apiToFriendly($response->data, self::ACCESS_RULE_MAP));
            });
    }

    /**
     * Update access rule for a listener
     * @param $id
     * @param AccessRule $accessRule
     * @return SelfResponse
     */
    public function updateAccessRule($id, AccessRule $accessRule)
    {
        $response = $this->patch(
            "v2/frontends/$id/access/$accessRule->id",
            json_encode($this->friendlyToApi($accessRule, self::ACCESS_RULE_MAP))
        );
        $response = $this->decodeJson($response->getBody()->getContents());

        return (new SelfResponse($response))
            ->setClient($this)
            ->serializeWith(function ($response) {
                return new AccessRule($this->apiToFriendly($response->data, self::ACCESS_RULE_MAP));
            });
    }

    /**
     * @param $id
     * @param string $accessRuleId
     * @return bool
     */
    public function deleteAccessRule($id, $accessRuleId)
    {
        $response = $this->delete("v2/frontends/$id/access/$accessRuleId");

        return $response->getStatusCode() == 204;
    }

    /**
     * @param $data
     * @return mixed|Listener
     */
    public function loadEntity($data)
    {
        return new Listener($this->apiToFriendly($data, $this->getEntityMap()));
    }
}
