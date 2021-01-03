<?php
namespace Haassie\Timeular;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\ClientException;
use Haassie\Timeular\Exceptions\MissingCredentialsException;

class Client
{
    protected static $apiUrl = 'https://api.timeular.com/api/v3/';
    protected static $timeFormat = 'Y-m-d\TH:i:s.v';

    /**
     * @var string
     */
    protected $apiKey;

    /**
     * @var string
     */
    private $apiSecret;

    /**
     * @var GuzzleClient
     */
    private $guzzleClient;

    public function __construct(string $apiKey, string $apiSecret, GuzzleClient $guzzleClient = null)
    {
        $this->apiKey = $apiKey;
        $this->apiSecret = $apiSecret;

        if ($guzzleClient instanceof GuzzleClient) {
            $this->guzzleClient = $guzzleClient;
        } else {
            $this->guzzleClient = new GuzzleClient();
        }

        if (empty($this->apiKey)) {
            throw new MissingCredentialsException('No API key given. You can retrieve your API key on https://profile.timeular.com/#/app/account');
        }
        if (empty($this->apiSecret)) {
            throw new MissingCredentialsException('No API secret given. You can retrieve your API secret on https://profile.timeular.com/#/app/account. The secret is only shown when creating a new API key.');
        }
    }

    public function getIntegrations(): array
    {
        $data = $this->getData('integrations');

        if (!is_array($data) || !array_key_exists('integrations', $data)) {
            return [];
        }
        return $data['integrations'];
    }

    public function getWebhookEvents(): array
    {
        $data = $this->getData('webhooks/event');

        if (!is_array($data) || !array_key_exists('events', $data)) {
            return [];
        }
        return $data['events'];
    }

    public function getWebhookSubscriptions(): array
    {
        $data = $this->getData('webhooks/subscription');

        if (!is_array($data) || !array_key_exists('subscriptions', $data)) {
            return [];
        }
        return $data['subscriptions'];
    }

    public function resetWebhookSubscriptions(): array
    {
        $data = $this->deleteData('webhooks/subscription');

        return $data;
    }

    public function addWebhookSubscription(string $event, string $url): array
    {
        return $this->postData(
            'webhooks/subscription',
            [
                'event' => $event,
                'target_url' => $url
            ]
        );
    }

    public function getTimeEntries(\DateTime $startDate, \DateTime $endDate): array
    {
        $path = 'time-entries/' . $startDate->format(self::$timeFormat) . '/' . $endDate->format(self::$timeFormat);

        $data = $this->getData($path);

        if (!is_array($data) || !array_key_exists('timeEntries', $data)) {
            return [];
        }
        return $data['timeEntries'];
    }

    public function getActivities(): array
    {
        $data = $this->getData('activities');

        if (!is_array($data) || !array_key_exists('activities', $data)) {
            return [];
        }
        return $data['activities'];
    }

    protected function getData(string $path, $method = 'GET'): array
    {
        $token = $this->getToken();
        $result = $this->guzzleClient->request(
            $method,
            self::$apiUrl . $path,
            [
                'headers' => [
                    'Accept'     => 'application/json',
                    'Authorization' => 'Bearer ' . $token
                ]
            ]
        );
        return json_decode((string)$result->getBody(), true);
    }

    protected function deleteData(string $path): array
    {
        $token = $this->getToken();
        $result = $this->guzzleClient->request(
            'DELETE',
            self::$apiUrl . $path,
            [
                'headers' => [
                    'Authorization' => 'Bearer ' . $token
                ]
            ]
        );
        return json_decode((string)$result->getBody(), true);
    }

    protected function postData(string $path, array $data): array
    {
        $token = $this->getToken();

        try {
            $result = $this->guzzleClient->request(
                'POST',
                self::$apiUrl . $path,
                [
                    'headers' => [
                        'Authorization' => 'Bearer ' . $token
                    ],
                    'json' => $data
                ]
            );
        } catch (ClientException $exception) {
            $responseBody = $exception->getResponse()->getBody()->getContents();
            return json_decode((string)$responseBody, true);
        }

        return json_decode((string)$result->getBody(), true);
    }

    protected function getToken(): string
    {
        $data = ['apiKey' => $this->apiKey, 'apiSecret' => $this->apiSecret];
        $result = $this->guzzleClient->request(
            'POST',
            self::$apiUrl . 'developer/sign-in',
            [
              'headers' => [
                  'Accept' => 'application/json'
              ],
              'json' => $data
          ]
        );
        $returnedData = json_decode((string)$result->getBody(), true);
        return (string)$returnedData['token'];
    }
}
