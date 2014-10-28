<?php

namespace TH\OAuth2\Storage\Memory;

use OAuth2\Storage\ClientInterface;

class ClientMemoryStorage implements ClientInterface
{
    private $clients;

    public function __construct(Array $clients)
    {
        $this->clients = $clients;
    }

    /**
     * @inherit
     */
    public function getClientDetails($client_id)
    {
        if (!array_key_exists($client_id, $this->clients)) {
            return null;
        }
        $default_client_details = [
            'client_id'     => $client_id,
            'client_secret' => null,
            'redirect_uri'  => null,
            'scope'         => null,
        ];
        return array_intersect_key(
            array_merge($default_client_details, $this->clients[$client_id]),
            $default_client_details
        );
    }

    /**
     * @inherit
     */
    public function getClientScope($client_id)
    {
        $clientDetails = $this->getClientDetails($client_id);
        if ($clientDetails === null) {
            return null;
        }
        return $clientDetails['scope'];
    }

    /**
     * @inherit
     */
    public function checkRestrictedGrantType($client_id, $grant_type)
    {
        if (!array_key_exists($client_id, $this->clients)) {
            return null;
        }
        if (array_key_exists('grant_types', $this->clients[$client_id])) {
            return in_array($grant_type, $this->clients[$client_id]);
        }
        return true;
    }
}
