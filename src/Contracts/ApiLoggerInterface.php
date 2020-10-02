<?php

namespace JorisvanW\Contracts;

interface ApiLoggerInterface{

    /**
     * saving methods in favourite driver
     *
     * @param [type] $request
     * @param [type] $response
     * @return void
     */
    public function saveLogs($request,$response);
    /**
     * return logs to use in the frontend
     *
     * @return void
     */
    public function getLogs();
    /**
     * return logs to use in the frontend
     * Works only for DB.
     *
     * @param int   $perPage
     * @param null  $page
     * @param array $options
     *
     * @return void
     */
    public function getLogsPaginated($perPage = 15, $page = null, $options = []);
    /**
     * provide method to delete all the logs
     *
     * @return void
     */
    public function deleteLogs();

}
