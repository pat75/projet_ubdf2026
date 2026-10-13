<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 | Module « Nous contacter » : un ticket par demande, son fil de messages,
 | et la base de connaissances (fiches question / reponse validees par un
 | administrateur) qui nourrit les suggestions de reponse IA.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('support_tickets', function (Blueprint $table) {
            $table->id();
            // Creatif connecte au depot ; sinon seule l'adresse est connue.
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('email')->nullable();
            $table->string('statut', 20)->default('nouveau')->index();
            $table->string('page_origine', 500)->nullable();
            $table->string('navigateur', 500)->nullable();
            $table->string('ip', 45)->nullable();
            $table->boolean('is_spam')->default(false);
            $table->timestamp('lu_admin_at')->nullable();
            $table->timestamp('last_message_at')->nullable()->index();
            $table->timestamps();
        });

        Schema::create('support_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('support_ticket_id')->constrained()->cascadeOnDelete();
            // `client` (creatif ou visiteur) ou `admin`.
            $table->string('auteur', 10);
            $table->foreignId('admin_id')->nullable()->constrained('admins')->nullOnDelete();
            $table->text('corps');
            $table->timestamps();
        });

        Schema::create('support_fiches', function (Blueprint $table) {
            $table->id();
            $table->text('question');
            $table->text('reponse');
            $table->boolean('actif')->default(false);
            $table->foreignId('support_ticket_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();

            // SQLite (tests) ne connait pas FULLTEXT : repli LIKE dans
            // App\Services\Support\BaseConnaissance.
            if (Schema::getConnection()->getDriverName() === 'mysql') {
                $table->fullText(['question', 'reponse']);
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('support_fiches');
        Schema::dropIfExists('support_messages');
        Schema::dropIfExists('support_tickets');
    }
};
