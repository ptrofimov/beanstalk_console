<?php

interface IStorage {

    public function isAvailable();

    public function saveJob($arr);

    public function getError();

    public function getJobsForTube($tube);

    public function load($key);
}
