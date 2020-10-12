<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class TagController extends Controller
{
    /**
     * Generates the gtag.js file
     * @return \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     */
    public function getGtagJs()
    {
        return response($this->getScript('https://www.googletagmanager.com/gtag/js', request()->query()))
            ->header('Content-Type', 'text/javascript');
    }

    /**
     * Generates the analytics.js file
     * @return \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     */
    public function getAnalyticsJs()
    {
        return response($this->getScript('https://www.google-analytics.com/analytics.js'))
            ->header('Content-Type', 'text/javascript');
    }

    /**
     * Downloads the required script from Google, and replace all relevant
     * informations with the proxy's own info
     * @param $src
     * @param array $query
     * @return string
     */
    protected function getScript($src, $query = [])
    {
        // Get raw script
        $rawJavascript = Http::get($src, $query)->body();

        return (string)Str::of($rawJavascript)
            // Replace the original URLs with the proxy ones
            ->replace('www.google-analytics.com', request()->getHost())
            ->replace('www.googletagmanager.com', request()->getHost())
            // Also replace the script names
            ->replace('analytics.js', env('GA_SCRIPT_NAME'))
            ->replace('gtag/js', env('GA_TAG_NAME'))
            // And the collection endpoints
            ->replace('"/j/collect', '"' . env('GA_COLLECT_ENDPOINT'))
            ->replace('"/r/collect', '"' . env('GA_COLLECT_ENDPOINT'))
            ->replace('"/collect', '"' . env('GA_COLLECT_ENDPOINT'));
    }
}
