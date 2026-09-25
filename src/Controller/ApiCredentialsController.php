<?php

declare(strict_types=1);

namespace Ssq\CloudflareTurnstile\Controller;

use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Exception\GuzzleException;
use Ssq\CloudflareTurnstile\Api\ClientInterface;
use Shopware\Core\Framework\Routing\Exception\InvalidRequestParameterException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class ApiCredentialsController extends AbstractController
{
    private ClientInterface $client;

    public function __construct(ClientInterface $client)
    {
        $this->client = $client;
    }

    /**
     * @throws \JsonException
     */
    public function validateApiCredentials(Request $request): JsonResponse
    {
        $secretKey = $request->request->get('secretKey');

        if (!\is_string($secretKey)) {
            throw new InvalidRequestParameterException('secretKey');
        }

        $siteKey = $request->request->get('siteKey');

        if (!\is_string($siteKey)) {
            throw new InvalidRequestParameterException('siteKey');
        }

        $credentialsValid = $this->isValidCredentials($secretKey, $siteKey);

        return new JsonResponse([
            'credentialsValid' => $credentialsValid,
        ]);
    }

    /**
     * @throws \JsonException
     */
    protected function isValidCredentials(string $secretKey, string $siteKey): bool
    {
        try {
            // we send a dummy response token – we only care about credential validity
            $response = $this->client->getValidationResponse('dummy-token', $secretKey);
        } catch (BadResponseException $exception) {
            $response = $this->client->decodeResponseBody($exception->getResponse());
        } catch (GuzzleException $exception) {
            return false;
        }

        $errorCodes = $response['error-codes'] ?? [];

        // If Cloudflare reports "invalid-input-secret", the secret key is wrong.
        // Any other error (like "invalid-input-response") means the credentials themselves are fine.
        return !\in_array('invalid-input-secret', $errorCodes, true);
    }
}

