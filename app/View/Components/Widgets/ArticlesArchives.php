<?php

namespace BBCMS\View\Components\Widgets;

// Сторонние зависимости.
use BBCMS\Models\Article;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\View\Component;

/**
 * Компонент виджета архива записей.
 */
class ArticlesArchives extends Component
{
    /**
     * Заголовок виджета.
     * @var string
     */
    public $title;

    /**
     * Активность виджета.
     * @var boolean
     */
    public $isActive = false;

    /**
     * Шаблон виджета.
     * @var string
     */
    public $template = 'components.widgets.articles-archives';

    /**
     * Время кэширования виджета.
     * @var string
     */
    public $cacheTime = 24 * 60 *60;

    /**
     * Создать экземпляр компонента.
     */
    public function __construct(
        array $parameters = []
    ) {
        $this->configure($parameters);
    }

    /**
     * Конфигурирование компонента.
     * @param  array  $parameters
     * @return void
     */
    protected function configure(array $parameters): void
    {

    }

    /**
     * Получить шаблон / содержимое, представляющее компонент.
     * @return Renderable
     */
    public function render(): Renderable
    {
        return view($this->template);
    }

    public function months(): Collection
    {
        return Article::without('categories')
            ->selectRaw('
                YEAR(created_at) AS year,
                MONTHNAME(created_at) AS month,
                count(*) AS count
            ')
            ->distinct()
            ->published()
            ->groupBy('year', 'month')
            ->orderBy('created_at', 'desc')
            ->limit($this->parameters['limit'] ?? 12)
            ->get();
    }
}
