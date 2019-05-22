<?php

namespace UKFast\PHaaS;

use Exception;
use Psr\Http\Message\ResponseInterface;
use UKFast\Exception\ValidationException;
use UKFast\Page;
use UKFast\Client as BaseClient;
use UKFast\PHaaS\Entities\Domain;

class DomainClient extends BaseClient
{
    protected $basePath = 'phaas/';

    /**
     * Get a paginated list of domains
     *
     * @param int $page
     * @param int $perPage
     * @param array $filters
     * @return Page
     */
    public function getAll($page = 1, $perPage = 15, $filters = [])
    {
        $domains = $this->paginatedRequest('v1/domains', $page, $perPage, $filters);

        $domains->serializeWith(function ($item) {
            return new Domain($item);
        });

        return $domains;
    }

    /**
     * Add a single domain
     *
     * @param string $domain
     * @param string $verificationEmail
     * @return Domain
     * @throws Exception
     */
    public function addDomain($domain, $verificationEmail)
    {
        if (empty($domain)) {
            throw new ValidationException("A domain must be provided");
        }

        if (empty($verificationEmail)) {
            throw new ValidationException("A verification email address must be provided");
        }

        $data = json_encode([
            "domain"             => $domain,
            "verification_email" => $verificationEmail
        ]);


        $response = $this->request("POST", 'v1/domains', $data, ['Content-Type' => 'application/json']);

        $body = $this->decodeJson($response->getBody()->getContents());

        return new Domain($body->data);
    }

    /**
     * Resend validation email for a domain by domain id
     *
     * @param string $domainId
     * @return Domain
     */
    public function resendValidationEmail($domainId)
    {
        if (empty($domainId)) {
            throw new ValidationException("A domain id must be provided");
        }

        $response = $this->request("GET", "v1/domains/$domainId/resend-verification");

        $body = $this->decodeJson($response);

        return new Domain($body);
    }

    /**
     * Validate the domain hash
     *
     * @param string $hash
     * @return ResponseInterface
     */
    public function verifyDomainHash($hash)
    {
        if (empty($hash) || strlen($hash) !== 28) {
            throw new ValidationException("A valid domain verification hash must be provided");
        }

        return $this->request("GET", "v1/domains/verify/$hash");
    }
}
