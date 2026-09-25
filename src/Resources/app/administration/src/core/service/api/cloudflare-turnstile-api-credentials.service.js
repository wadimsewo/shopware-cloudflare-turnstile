const ApiService = Shopware.Classes.ApiService;

export default class CloudflareTurnstileApiCredentialsService extends ApiService {
    constructor(httpClient, loginService, apiEndpoint = 'cloudflare-turnstile') {
        super(httpClient, loginService, apiEndpoint);
    }

    /**
     * @param {string} siteKey
     * @param {string} secretKey
     * @returns {Promise}
     */
    validateApiCredentials(siteKey, secretKey) {
        const headers = this.getBasicHeaders();

        // POST keeps the secret key out of URLs and access logs
        return this.httpClient.post(
            `_action/${this.getApiBasePath()}/validate-api-credentials`,
            { siteKey, secretKey },
            { headers: headers },
        ).then((response) => {
            return ApiService.handleResponse(response);
        });
    }
}

