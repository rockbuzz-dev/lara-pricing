<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePricingTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $tables = $this->getConfigTables();

        Schema::create($tables['pricing_plans'], function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name')->unique();
            $table->text('description')->nullable();
            $table->smallInteger('price')->default(0);
            $table->string('interval')->default('month');
            $table->smallInteger('period')->default(1);
            $table->smallInteger('trial_period_days')->default(0);
            $table->smallInteger('sort_order')->default(1);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create($tables['pricing_features'], function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name')->unique();
            $table->string('slug')->unique();
            $table->smallInteger('sort_order')->default(1);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create($tables['pricing_feature_plan'], function (Blueprint $table) {
            $table->uuid('feature_id')->index();
            $table->foreign('feature_id')
                ->references('id')
                ->on('pricing_features')
                ->onDelete('cascade');
            $table->uuid('plan_id')->index();
            $table->foreign('plan_id')
                ->references('id')
                ->on('pricing_plans')
                ->onDelete('cascade');
            $table->string('value');
            $table->unique(['feature_id', 'plan_id']);
        });

        Schema::create($tables['pricing_subscriptions'], function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name')->unique();
            $table->dateTime('start_at')->useCurrent();
            $table->dateTime('finish_at')->nullable();
            $table->dateTime('canceled_at')->nullable();
            $table->date('due_date')->useCurrent();
            $table->uuid('subscribable_id');
            $table->string('subscribable_type');
            $table->uuid('plan_id')->index();
            $table->foreign('plan_id')
                ->references('id')
                ->on('pricing_plans')
                ->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes();
            $table->index(['subscribable_id', 'subscribable_type', 'plan_id']);
        });

        Schema::create($tables['pricing_subscription_usages'], function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->smallInteger('used');
            $table->uuid('feature_id')->index();
            $table->foreign('feature_id')
                ->references('id')
                ->on('pricing_features')
                ->onDelete('cascade');
            $table->uuid('subscription_id')->index();
            $table->foreign('subscription_id')
                ->references('id')
                ->on('pricing_subscriptions')
                ->onDelete('cascade');
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['feature_id', 'subscription_id']);
            $table->index(['feature_id', 'subscription_id']);
        });

        Schema::create($tables['pricing_activities'], function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('description');
            $table->json('changes')->nullable();
            $table->uuid('activityable_id')->index();
            $table->string('activityable_type');
            $table->uuid('causeable_id')->index();
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
        $tables = $this->getConfigTables();

        Schema::dropIfExists($tables['pricing_activities']);
        Schema::dropIfExists($tables['pricing_subscription_usages']);
        Schema::dropIfExists($tables['pricing_subscriptions']);
        Schema::dropIfExists($tables['pricing_feature_plan']);
        Schema::dropIfExists($tables['pricing_features']);
        Schema::dropIfExists($tables['pricing_plans']);
    }

    private function getConfigTables(): array
    {
        return config('pricing.tables');
    }
}
