<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePricingTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('plans', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->uuid('uuid');
            $table->string('name')->unique();
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->smallInteger('price')->default(0);
            $table->string('interval')->default('month');
            $table->smallInteger('period')->default(1);
            $table->smallInteger('trial_period_days')->default(0);
            $table->smallInteger('order_column')->default(1);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('features', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->uuid('uuid');
            $table->string('name')->unique();
            $table->string('slug')->unique();
            $table->smallInteger('order_column')->default(1);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('feature_plan', function (Blueprint $table) {
            $table->unsignedBigInteger('feature_id')->index();
            $table->foreign('feature_id')
                ->references('id')
                ->on('features')
                ->onDelete('cascade');
            $table->unsignedBigInteger('plan_id')->index();
            $table->foreign('plan_id')
                ->references('id')
                ->on('plans')
                ->onDelete('cascade');
            $table->string('value');
            $table->unique(['feature_id', 'plan_id']);
        });

        Schema::create('subscriptions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->uuid('uuid');
            $table->string('name');
            $table->string('slug');
            $table->dateTime('start_at')->useCurrent();
            $table->dateTime('finish_at')->nullable();
            $table->dateTime('canceled_at')->nullable();
            $table->string('due_day')->nullable();
            $table->uuid('subscribable_id');
            $table->string('subscribable_type');
            $table->unsignedBigInteger('plan_id')->index();
            $table->foreign('plan_id')
                ->references('id')
                ->on('plans')
                ->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes();
            $table->index(['subscribable_id', 'subscribable_type', 'plan_id'], 'subscribable_plan');
        });

        Schema::create('subscription_usages', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->uuid('uuid');
            $table->smallInteger('used');
            $table->unsignedBigInteger('feature_id')->index();
            $table->foreign('feature_id')
                ->references('id')
                ->on('features')
                ->onDelete('cascade');
            $table->unsignedBigInteger('subscription_id')->index();
            $table->foreign('subscription_id')
                ->references('id')
                ->on('subscriptions')
                ->onDelete('cascade');
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['feature_id', 'subscription_id']);
            $table->index(['feature_id', 'subscription_id']);
        });

        Schema::create('pricing_activities', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('description');
            $table->json('changes')->nullable();
            $table->unsignedBigInteger('activityable_id')->index();
            $table->string('activityable_type');
            $table->unsignedBigInteger('causeable_id')->index();
            $table->string('causeable_type');
            $table->dateTime('created_at')->useCurrent();
            $table->index(
                ['activityable_id', 'activityable_type', 'causeable_id', 'causeable_type'],
                'activityable_causeable'
            );
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pricing_activities');
        Schema::dropIfExists('subscription_usages');
        Schema::dropIfExists('subscriptions');
        Schema::dropIfExists('feature_plan');
        Schema::dropIfExists('features');
        Schema::dropIfExists('plans');
    }
}
