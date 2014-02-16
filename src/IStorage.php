<?php

interface IStorage {

    public function isAvailable();

    public function saveJob($arr);

    public function getError();

    public function getJobs();

    public function getJobsForTube($tube);

    public function load($key);

    public function delete($key);
}
