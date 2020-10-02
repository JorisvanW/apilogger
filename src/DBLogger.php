<?php

namespace JorisvanW;

use JorisvanW\Contracts\ApiLoggerInterface;

class DBLogger extends AbstractLogger implements ApiLoggerInterface{

    /**
     * Model for saving logs
     *
     * @var [type]
     */
    protected $logger;

    public function __construct(ApiLog $logger)
    {
        parent::__construct();
        $this->logger = $logger;
    }
    /**
     * return all models
     */
    public function getLogs()
    {
        return $this->logger->all();
    }
    /**
     * return the models paginated
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getLogsPaginated($perPage = 15, $page = null, $options = [])
    {
        return $this->logger->orderByDesc('created_at')->paginate($perPage);
    }
    /**
     * save logs in database
     */
    public function saveLogs($request,$response)
    {
        $data = $this->logData($request,$response);

        $this->logger->fill($data);

        $this->logger->save();
    }
    /**
     * delete all logs
     */
    public function deleteLogs()
    {
        $this->logger->truncate();
    }
}
