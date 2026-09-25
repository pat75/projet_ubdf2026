<?php

namespace App\Filament\Resources\NewsletterMails\Pages;

use App\Filament\Resources\NewsletterMails\NewsletterMailResource;
use Filament\Resources\Pages\ListRecords;

class ListNewsletterMails extends ListRecords
{
    protected static string $resource = NewsletterMailResource::class;
}
