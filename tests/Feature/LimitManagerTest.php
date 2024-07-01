<?php

namespace NabilHassen\LaravelUsageLimiter\Tests\Feature;

use Illuminate\Contracts\Cache\Store;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;
use NabilHassen\LaravelUsageLimiter\Contracts\Limit;
use NabilHassen\LaravelUsageLimiter\Enum\FrequencyEnum;
use NabilHassen\LaravelUsageLimiter\Exceptions\InvalidLimitResetFrequencyValue;
use NabilHassen\LaravelUsageLimiter\LimitManager;
use NabilHassen\LaravelUsageLimiter\Tests\TestCase;

class LimitManagerTest extends TestCase
{
    protected $limitManagerClass;

    protected function setUp(): void
    {
        parent::setup();

        $this->limitManagerClass = app(LimitManager::class);
    }

    public function test_cache_is_initialized(): void
    {
        $this->assertInstanceOf(Store::class, $this->limitManagerClass->getCacheStore());
    }

    public function test_get_limits_return_limits_collection(): void
    {
        $this->createLimit();

        $this->assertCount(1, $this->limitManagerClass->getLimits());
    }

    public function test_get_limit_throws_exception_if_id_or_name_is_not_provided(): void
    {
        $limit = $this->createLimit();

        $this->assertException(
            fn () => $this->limitManagerClass->getLimit([]),
            InvalidArgumentException::class
        );

        $this->assertException(
            fn () => $this->limitManagerClass->getLimit([
                'plan' => $limit->plan,
            ]),
            InvalidArgumentException::class
        );
    }

    public function test_get_limit_return_a_limit_by_id(): void
    {
        $limit = $this->createLimit();

        $this->assertEquals($limit->name, $this->limitManagerClass->getLimit([
            'id' => $limit->id,
        ])->name);
    }

    public function test_get_limit_return_a_limit_by_name_and_plan(): void
    {
        $limit = $this->createLimit();
        $nullPlanLimit = $this->createLimit(plan: null);

        $this->assertEquals($limit->name, $this->limitManagerClass->getLimit([
            'name' => $limit->name,
            'plan' => $limit->plan,
        ])->name);

        $this->assertEquals($nullPlanLimit->name, $this->limitManagerClass->getLimit([
            'name' => $nullPlanLimit->name,
        ])->name);
    }

    public function test_limits_cache_is_flushed(): void
    {
        $this->createLimit();
        $this->createLimit(plan: null);

        DB::flushQueryLog();

        $this->limitManagerClass->getLimits();

        $this->assertQueriesExecuted(1);

        DB::flushQueryLog();

        $this->limitManagerClass->getLimits();

        $this->assertQueriesExecuted(0);

        DB::flushQueryLog();

        $this->limitManagerClass->flushCache();

        $this->limitManagerClass->getLimits();

        $this->assertQueriesExecuted(1);
    }

    public function test_exception_is_thrown_if_invalid_reset_frequency_is_passed_to_get_next_reset(): void
    {
        $this->assertException(
            fn () => $this->limitManagerClass->getNextReset(Str::random(), now()),
            InvalidLimitResetFrequencyValue::class
        );
    }

    public function test_get_next_reset_returns_carbon_date(): void
    {
        $limit_frequency = FrequencyEnum::from(app(Limit::class)->getResetFrequencyOptions()->random());

        $date = $this->limitManagerClass->getNextReset($limit_frequency, now());

        $this->assertInstanceOf(Carbon::class, $date);
    }
}
