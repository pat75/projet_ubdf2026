<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

pest()->extend(TestCase::class)->use(RefreshDatabase::class)->in('Feature/Database', 'Feature/Front', 'Feature/Espace', 'Feature/Admin', 'Feature/Jobs');
pest()->extend(TestCase::class)->in('Feature/Images', 'Feature/Legacy', 'Unit');
