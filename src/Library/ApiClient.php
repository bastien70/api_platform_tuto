<?php


namespace App\Library;

use App\Library\Service\CheeseService;
use App\Library\Service\DailyStatsService;
use App\Library\Service\UserService;

/**
 * @property CheeseService $cheeses
 * @property UserService $users
 * @property DailyStatsService $dailyStats
 */
class ApiClient
{
    public function __construct(
        public CheeseService $cheeses,
        public UserService $users,
        public DailyStatsService $dailyStats
    ){}


}