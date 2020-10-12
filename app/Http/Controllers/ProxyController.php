<?php

namespace App\Http\Controllers;

use GuzzleHttp\Client;
use Laminas\Diactoros\ServerRequest;
use Laminas\Diactoros\StreamFactory;
use Laminas\HttpHandlerRunner\Emitter\SapiEmitter;
use Proxy\Proxy;
use Proxy\Adapter\Guzzle\GuzzleAdapter;
use Laminas\Diactoros\ServerRequestFactory;
use function request;

class ProxyController extends Controller
{
    /**
     * Proxies a request to the Google Analytics collection URI
     */
    public function proxy()
    {
        // Create a PSR7 request based on the current browser request.
        $request = ServerRequestFactory::fromGlobals();

        // Create a guzzle client
        $guzzle = new Client();

        // Create the proxy instance
        $proxy = new Proxy(new GuzzleAdapter($guzzle));

        // Apply filters
        $proxy->filter([$this, 'appendIP']);
        $proxy->filter([$this, 'updateUri']);

        // Forward the request and get the response.
        $response = $proxy->forward($request)
            ->to(env('GA_UPSTREAM_ENDPOINT'));

        // Output response to the browser.
        (new SapiEmitter())->emit($response);
    }

    /**
     * Append the client IP to the request
     * @param ServerRequest $request
     * @param $response
     * @param $next
     * @return mixed
     */
    public function appendIP(ServerRequest $request, $response, $next)
    {
        // If the client used GET, append their IP to the query params
        if ($request->getMethod() === 'GET') {
            $queryParams = $request->getQueryParams();
            $queryParams['uip'] = request()->ip();
            $request = $request->withQueryParams($queryParams);

            // If the client used POST, append their IP to the body data
        } elseif ($request->getMethod() === 'POST') {
            $payloadData = $request->getBody()->getContents();
            $payloadData .= '&uip=' . urlencode(request()->ip());
            $request = $request->withBody((new StreamFactory())->createStream($payloadData));
        }

        // Call the next item in the middleware.
        $response = $next($request, $response);
        return $response;
    }

    /**
     * Change the randomly generated URI to the Google's /collect
     * @param ServerRequest $request
     * @param $response
     * @param $next
     * @return mixed
     */
    public function updateUri(ServerRequest $request, $response, $next)
    {
        // Replace the URI with collect
        $request = $request->withUri($request->getUri()->withPath('/collect'));

        // Call the next item in the middleware.
        $response = $next($request, $response);
        return $response;
    }
}
