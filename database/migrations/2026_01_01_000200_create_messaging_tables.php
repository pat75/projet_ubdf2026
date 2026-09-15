<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Messagerie intermediee (ub2_intermediate_form) et formulaire de
        // contact simple (ub2_contact_form), unifies en conversations.
        Schema::create('conversations', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('legacy_id')->nullable()->unique();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            $table->string('channel', 20)->default('contact')->index();  // contact|intermediate
            $table->string('subject')->nullable();
            $table->string('request_detail')->nullable();

            // Emetteur externe (non inscrit).
            $table->string('sender_name')->nullable();
            $table->string('sender_company')->nullable();
            $table->string('sender_email')->nullable();
            $table->string('sender_phone', 40)->nullable();

            // Acces par lien signe (token + selector du systeme 2019).
            $table->string('token')->nullable()->index();
            $table->string('selector', 64)->nullable()->index();

            $table->string('book_image')->nullable();
            $table->timestamp('last_message_at')->nullable()->index();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'channel']);
        });

        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('legacy_id')->nullable()->unique();
            $table->foreignId('conversation_id')->constrained()->cascadeOnDelete();

            // true = ecrit par le creatif, false = ecrit par le visiteur.
            $table->boolean('from_owner')->default(false);
            $table->text('body');
            $table->string('ip', 45)->nullable();
            $table->timestamp('read_at')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['conversation_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('messages');
        Schema::dropIfExists('conversations');
    }
};
