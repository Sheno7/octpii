<?php

namespace App\Traits;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

trait ApiCallerTrait
{

    public function getToken()
    {
        $url = env('API_URL') . '/oauth/token';
        $client = new Client();
        try {
            $response = $client->post($url, [
                'form_params' => [
                    'grant_type' => 'client_credentials',
                    'client_id' => env('API_CLIENT_ID'),
                    'client_secret' => env('API_CLIENT_SECRET'),
                    'scope' => '*',
                ],
            ]);

            if ($response->getStatusCode() === 200) {
                return json_decode($response->getBody(), true);
            } else {
                return null;
            }
        } catch (RequestException $e) {
            return $e->getResponse()->getBody()->getContents();
        }
    }
    public function callApiGet($url)
    {
        $client = new Client();
        try {
            $response = $client->get($url,
            [
                'headers' =>
                [
                    'Accept-Encoding' => 'gzip',
                ],
                'decode_content' => true,
            ]);

            if ($response->getStatusCode() === 200)
            {
                return json_decode($response->getBody(), true);
            }
            else
            {
                return null;
            }
        } catch (RequestException $e) {
            return $e->getResponse()->getBody()->getContents();
        }
    }

    public function callApiPost($url)
    {
        $client = new Client();

        try {
            $response = $client->post($url);

            if ($response->getStatusCode() === 200)
            {
                return json_decode($response->getBody(), true);
            } else
            {
                return null;
            }
        } catch (RequestException $e)
        {
            return $e->getResponse()->getBody()->getContents();
        }
    }
}
