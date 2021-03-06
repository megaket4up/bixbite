<?php

namespace Tests\Unit\App\Http\Middleware;

// Исключения.
use App\Exceptions\UnsupportedMiddlewareForRouteException;

// Тестируемый класс.
use App\Http\Middleware\TransformApiData;

// Сторонние зависимости.
use App\Support\Contracts\ResourceRequestTransformer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Библиотеки тестирования.
use Mockery as m;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \App\Http\Middleware\TransformApiData
 *
 * @cmd phpunit tests\Unit\App\Http\Middleware\TransformApiDataTest.php
 */
class TransformApiDataTest extends TestCase
{
    protected $mocks = [];

    protected function tearDown(): void
    {
        m::close();

        $this->mocks = [];
    }

    /**
     * @test
     * @covers ::__construct
     *
     * Успешная инициализация посредника.
     * @return void
     */
    public function testSuccessfullyInitiated(): void
    {
        $middleware = $this->createMiddleware('api.someResource.someMethod');

        $this->assertSame('api.someResource.someMethod', $middleware->currentRouteName());
        $this->assertSame('api', $middleware->group());
        $this->assertSame('someResource', $middleware->resource());
        $this->assertSame('someMethod', $middleware->action());
    }

    /**
     * @test
     * @covers ::__construct
     *
     * Посредник применяется только для группы `api`.
     * @return void
     */
    public function testMiddlewareAppliesOnlyForApiGroup(): void
    {
        $this->expectException(UnsupportedMiddlewareForRouteException::class);
        $middleware = $this->createMiddleware('some-route');
    }

    /**
     * @test
     * @covers ::hasTransformerForCurrentRoute
     *
     * Запрос текущего маршрута имеет преобразователь данных.
     * @return void
     */
    public function testRequestForCurrentRouteHasDataTransformer(): void
    {
        $group = 'api';

        $transformers = TransformApiData::AVAILABLE_TRANSFORMERS;
        $resources = array_keys($transformers);
        $resource = array_pop($resources);

        $actions = TransformApiData::ALLOWED_ACTIONS;
        $action = array_pop($actions);

        $middleware = $this->createMiddleware("{$group}.{$resource}.{$action}");

        $this->assertTrue($middleware->hasTransformerForCurrentRoute());
        $this->assertSame($group, $middleware->group());
        $this->assertSame($resource, $middleware->resource());
        $this->assertSame($action, $middleware->action());
    }

    /**
     * @test
     * @covers ::hasTransformerForCurrentRoute
     *
     * Запрос текущего маршрута не имеет преобразователя данных.
     * @return void
     */
    public function testRequestForCurrentRouteDoesNotHaveDataTransformer(): void
    {
        $middleware = $this->createMiddleware('api.someResource.someMethod');

        $this->assertFalse($middleware->hasTransformerForCurrentRoute());
    }

    /**
     * [createRequestWithCustomData description]
     * @param  array  $requestingInputs
     * @return Request
     */
    protected function createRequestWithCustomData(array $requestingInputs): Request
    {
        $request = new Request;

        $request->merge($requestingInputs);

        return $request;
    }

    /**
     * [createMiddleware description]
     * @param  string  $routeName
     * @return TransformApiData
     */
    protected function createMiddleware(string $routeName): TransformApiData
    {
        Route::shouldReceive('currentRouteName')
            ->once()
            ->andReturn($routeName);

        return new TransformApiData;
    }
}
