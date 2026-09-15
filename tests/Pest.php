<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

pest()->extend(TestCase::class)->use(RefreshDatabase::class)->in('Feature/Database');
pest()->extend(TestCase::class)->in('Feature/Legacy', 'Unit');
