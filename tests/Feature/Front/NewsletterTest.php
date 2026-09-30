<?php

use App\Models\Admin;
use App\Models\NewsletterMail;

it('enregistre une adresse une seule fois', function () {
    $this->postJson(route('newsletter.inscription'), ['mail' => ' Lea@Example.test '])
        ->assertOk()->assertJson(['error' => false]);
    $this->postJson(route('newsletter.inscription'), ['mail' => 'lea@example.test'])->assertOk();

    expect(NewsletterMail::pluck('email')->all())->toBe(['lea@example.test']);
});

it('refuse une adresse invalide', function () {
    $this->postJson(route('newsletter.inscription'), ['mail' => 'pas-un-mail'])
        ->assertStatus(422)->assertJson(['error' => true]);

    expect(NewsletterMail::count())->toBe(0);
});

it('liste les adresses dans l\'admin', function () {
    NewsletterMail::create(['email' => 'lea@example.test']);
    $admin = Admin::create(['name' => 'Pat', 'email' => 'admin@example.test', 'password' => 'mot-de-passe-long']);

    $this->actingAs($admin, 'admin')->get('/admin/newsletter-mails')
        ->assertOk()->assertSee('Inscrits')->assertSee('lea@example.test');
});
