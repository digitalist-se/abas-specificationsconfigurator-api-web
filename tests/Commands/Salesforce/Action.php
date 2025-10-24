<?php

namespace Tests\Commands\Salesforce;

enum Action: string
{
    case Get = 'get';
    case Search = 'search';
    case Create = 'create';
    case Update = 'update';
}
