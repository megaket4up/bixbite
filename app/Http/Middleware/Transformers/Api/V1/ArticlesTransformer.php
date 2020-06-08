<?php

namespace App\Http\Middleware\Transformers\Api\V1;

// Сторонние зависимости.
use App\Support\Contracts\ResourceRequestTransformer;
use Illuminate\Http\Request;

/**
 * Преобразователь данных Запроса для Записей.
 */
class ArticlesTransformer implements ResourceRequestTransformer
{
    /**
     * Запрос для текущего ресурса.
     * @var Request
     */
    protected $request;

    /**
     * Создать новый экземпляр Преобразователя данных.
     * @param  Request  $request
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * Получить массив данных, используемых по умолчанию.
     * @return array
     */
    public function default(): array
    {
        return $this->request->all();
    }

    /**
     * Получить массив данных для сохранения сущности.
     * @return array
     */
    public function store(): array
    {
        $inputs = [];

        return $inputs;
    }

    /**
     * Получить массив данных для обновления сущности.
     * @return array
     */
    public function update(): array
    {
        $inputs = [];

        $inputs['title'] = strtoupper($this->request->input('title'));

        return $inputs;
    }

    /**
     * Получить массив данных для массовго обновления сущностей.
     * @return array
     */
    public function massUpdate(): array
    {
        $inputs = [];

        return $inputs;
    }
}
