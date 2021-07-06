<?php


namespace App\Service;


use App\Library\StripeClient;

class Connector
{
    public function __construct(private StripeClient $stripe){}

    public function test()
    {
        $this->stripe->cheeses->all();
    }
}