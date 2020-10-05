<?php

namespace JorisvanW;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

abstract class AbstractLogger
{

    protected $logs = [];

    protected $models = [];

    public function __construct()
    {
        $this->boot();
    }
    /**
     * starting method just for cleaning code
     *
     * @return void
     */
    public function boot()
    {
        Event::listen('eloquent.*', function ($event, $models) {
            if (Str::contains($event, 'eloquent.retrieved')) {
                foreach (array_filter($models) as $model) {
                    $class                = get_class($model);
                    $this->models[$class] = ($this->models[$class] ?? 0) + 1;
                }
            }
        });
    }
    /**
     * logs into associative array
     *
     * @param                           $request
     * @param \Illuminate\Http\Response $response
     *
     * @return array
     */
    public function logData($request, $response)
    {
        $currentRouteAction = Route::currentRouteAction();

        // Initialiaze controller and action variable before use them
        $controller = "";
        $action     = "";

        /*
         * Some routes will not contain the `@` symbole (e.g. closures, or routes using a single action controller).
         */
        if ($currentRouteAction) {
            if (strpos($currentRouteAction, '@') !== false) {
                [$controller, $action] = explode('@', $currentRouteAction);
            } else {
                // If we get a string, just use that.
                if (is_string($currentRouteAction)) {
                    [$controller, $action] = ["", $currentRouteAction];
                } else {
                    // Otherwise force it to be some type of string using `json_encode`.
                    [$controller, $action] = ["", (string)json_encode($currentRouteAction)];
                }
            }
        }

        $endTime = microtime(true);

        $implode_models = $this->models;

        array_walk($implode_models, function (&$value, $key) {
            $value = "{$key} ({$value})";
        });

        $models                         = implode(', ', $implode_models);
        $this->logs['created_at']       = Carbon::now();
        $this->logs['method']           = $request->method();
        $this->logs['url']              = $request->path();
        $this->logs['headers']          = $this->headers($request);
        $this->logs['payload']          = $this->payload($request);
        $this->logs['response']         = $response->status();
        $this->logs['duration']         = number_format($endTime - LARAVEL_START, 3);
        $this->logs['controller']       = $controller;
        $this->logs['action']           = $action;
        $this->logs['models']           = $models;
        $this->logs['response_payload'] = !is_null($response->exception) ? $response->getContent() : null;
        $this->logs['exception']        = !is_null($response->exception) ? json_encode([
            'exception' => (string)(get_class($response->exception)),
            'code'      => $response->exception->getCode(),
            'message'   => $response->exception->getMessage(),
        ]) : null;
        $this->logs['ip']               = $request->ip();

        return $this->logs;
    }
    /**
     * Helper method for mapping array into models
     *
     * @param array $data
     *
     * @return ApiLog
     */
    public function mapArrayToModel(array $data)
    {
        $model                   = new ApiLog();
        $model->created_at       = Carbon::make($data[0]);
        $model->method           = $data[1];
        $model->url              = $data[2];
        $model->headers          = $data[3];
        $model->payload          = $data[4];
        $model->response         = $data[5];
        $model->duration         = $data[6];
        $model->controller       = $data[7];
        $model->action           = $data[8];
        $model->models           = $data[9];
        $model->response_payload = $data[10];
        $model->exception        = $data[11];
        $model->ip               = $data[12];
        return $model;
    }

    /**
     * Formats the request payload for logging
     *
     * @param $request
     *
     * @return string
     */
    protected function payload($request)
    {
        $allFields = $request->all();

        foreach (config('apilog.dont_log', []) as $key) {
            if (array_key_exists($key, $allFields)) {
                unset($allFields[$key]);
            }
        }

        return json_encode($allFields);
    }

    /**
     * Formats the request headers for logging
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return string
     */
    protected function headers($request)
    {
        $headers = [];

        foreach (config('apilog.headers_log', []) as $headerName) {
            if (!empty($header = $request->header($headerName))) {
                $headers[$headerName] = $header;
            }
        }

        return json_encode($headers);
    }
}
