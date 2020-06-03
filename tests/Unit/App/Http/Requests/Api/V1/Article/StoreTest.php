<?php

namespace Tests\Unit\App\Http\Requests\Api\V1\Article;

// Тестируемый класс.
use App\Http\Requests\Api\V1\Article\Store;

// Сторонние зависимости.
use App\Models\User;
use Faker\Generator as Faker;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

/**
 * @coversDefaultClass \App\Http\Requests\Api\V1\Article\Store
 *
 * @cmd phpunit tests/Unit/App/Http/Requests/Api/V1/Article
 */
class StoreTest extends TestCase
{
    use WithFaker;

    /**
     * [$ownerUser description]
     * @var User
     */
    private $ownerUser;

    /**
     * [$requestingInputs description]
     * @var array
     */
    private $requestingInputs = [];

    /**
     * [$faker description]
     * @var Faker
     */
    protected $faker;

    /**
     * [setUp description]
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->ownerUser = factory(User::class)
            ->states('owner')
            ->create();

        $this->actingAs($this->ownerUser);

        $this->app->resolving(Store::class, function (Store $resolved) {
            $resolved->replace($this->requestingInputs);
        });
    }

    /**
     * @test
     * @dataProvider additionFailsDataProvider
     * @dataProvider additionPassesDataProvider
     *
     * Описание теста.
     * @param  bool  $shouldPass
     * @param  callable  $generator
     * @return void
     */
    public function testExample(bool $shouldPass, callable $generator): void
    {
        // Не доверяя пользователю,
        // выбираем его идентификатор
        // из фасада аутентификации.
        Auth::shouldReceive('id')
            ->once()
            ->andReturn($this->ownerUser->id);

        try {
            $request = $this->resolveRequestForTesting(
                $generator($this->faker)
            );

            $this->assertTrue($shouldPass);
        } catch (ValidationException $e) {
            $this->assertFalse($shouldPass);
        }
    }

    /**
     * [resolveRequestForTesting description]
     * @param  array  $inputs
     * @return Store
     */
    protected function resolveRequestForTesting(array $inputs): Store
    {
        $this->requestingInputs = $inputs;

        return $this->app->make(Store::class);
    }

    /**
     * [additionWithFailsDataProvider description]
     * @return array
     */
    public function additionFailsDataProvider(): array
    {
        return [
            'отклонить запрос из-за отсутствия данных' => [
                false,
                function () {
                    return [];
                }
            ],

            'отклонить запрос из-за превышения длины заголовка' => [
                false,
                function (Faker $faker) {
                    return [
                        'title' => Str::random(256),
                    ];
                }
            ],

        ];
    }

    public function additionPassesDataProvider()
    {
        return [
            'пропустить запрос с минимальным набором данных' => [
                true,
                function (Faker $faker) {
                    return [
                        'title' => $faker->sentence(mt_rand(4, 12))
                    ];
                },
            ],

        ];
    }
}