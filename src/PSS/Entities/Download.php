<?php

namespace UKFast\SDK\PSS\Entities;

class Download
{
    /**
     * Attachment constructor.
     * @param \GuzzleHttp\Psr7\Response $response
     */
    public function __construct($response)
    {
        $this->response = $response;
    }

    /**
     * @return \GuzzleHttp\Psr7\Stream
     */
    public function getStream()
    {
        return $this->response->getBody();
    }

    /**
     * @return string|boolean
     */
    public function getContentType()
    {
        $contentType = $this->response->getHeaders()['Content-Type'];
        if ($contentType) {
            return $contentType;
        }

        return false;
    }
}
