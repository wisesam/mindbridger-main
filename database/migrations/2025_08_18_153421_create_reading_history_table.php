<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        try {
            Schema::create('reading_history', function (Blueprint $table) {
                $table->id();
            
                $table->integer('inst');
                $table->integer('book_id');
                $table->char('user_id', 20);
                
                $table->timestamp('start_time')->nullable();
                $table->timestamp('end_time')->nullable();
            
                $table->enum('status', ['none', 'in_progress', 'completed', 'suspended'])
                    ->default('none');
            
                $table->json('historyData')->nullable();
                $table->json('historyDataBackup')->nullable();
                $table->json('evaluationData')->nullable();
                $table->json('evaluationDataBackup')->nullable();
            
                $table->timestamps();
            
                $table->foreign(['inst', 'book_id'])
                    ->references(['inst', 'id'])
                    ->on('book')
                    ->onDelete('cascade');
            
                $table->foreign(['inst', 'user_id'])
                    ->references(['inst', 'id'])
                    ->on('users') 
                    ->onDelete('cascade');
            });
        } catch (\Throwable $e) {
            // if something fails, drop the table
            Schema::dropIfExists('reading_history');
            throw $e; // rethrow so migration still fails visibly
        }
    }    

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('reading_history');
    }
};
