<?php

namespace App\Http\Requests\Api\V1\Article;

// Сторонние зависимости.
use App\Http\Requests\BaseFormRequest;
use Illuminate\Validation\Rule;

/**
 * @NB В связи с тем, что Записи идентифицируются по ID,
 * то нет необходимости в уникальности атрибутов `title` и `slug`.
 *
 * @NB Нежелательным является использование метода `route`,
 * так как пока непонятно, как это тестировать. И надо ли?
 */
class ArticleRequest extends BaseFormRequest
{
    /**
     * Общий массив допустимых значений для правила `in:список_значений`.
     * @var array
     */
    protected $allowedForInRule = [
        'date_at' => [
            'currdate',
            'customdate',

        ],

    ];

    /**
     * Подготовить данные для валидации.
     * @return void
     */
    protected function prepareForValidation(): void
    {
        // NB: if isset image_id then attach image.
        $input = $this->except([
            '_token',
            '_method',
            'submit',

        ]);

        $input['title'] = filter_var($this->input('title'), FILTER_SANITIZE_STRING);
        $input['slug'] = string_slug($input['slug'] ?? $input['title']);
        $input['teaser'] = html_clean($input['teaser'] ?? null);

        $input['content'] = preg_replace_callback("/<pre[^>]*?>(.+?)<\/pre>/is",
            function ($match) {
                return '<pre class="ql-syntax" spellcheck="false">' . html_secure($match[1]) . '</pre>';
            },
            $this->input('content', null)
        );
        $input['content'] = preg_replace("/\<script.*?\<\/script\>/", '', $input['content']);
        $input['content'] = $this->removeEmoji($input['content']);

        $input['description'] = teaser($input['description'] ?? null, 255);
        $input['keywords'] = teaser($input['keywords'] ?? null, 255);
        $input['tags'] = isset($input['tags'])
            ? array_map(
                function (string $tag) {
                    return string_slug($tag, setting('tags.delimiter', '-'), false, false);
                },
                preg_split('/,/', $input['tags'], -1, PREG_SPLIT_NO_EMPTY)
            ) : [];

        if (empty($input['date_at'])) {
            $input['updated_at'] =  date('Y-m-d H:i:s');
        } else {
            if ('currdate' === $input['date_at']) {
                $input['created_at'] = date('Y-m-d H:i:s');
            } else {
                $input['created_at'] = date_format(date_create($input['created_at']), 'Y-m-d H:i:s');
            }

            $input['updated_at'] =  null;
        }

        if (empty($input['categories']) or empty($input['state'])) {
            $input['state'] = 'unpublished';
        }

        $this->replace($input)
            ->merge([
                // default value to the checkbox
                'on_mainpage' => $this->input('on_mainpage', 0),
                'is_pinned' => $this->input('is_pinned', 0),
                'is_catpinned' => $this->input('is_catpinned', 0),
                'is_favorite' => $this->input('is_favorite', 0),
                'allow_com' => $this->input('allow_com', 2),

            ]);
    }

    /**
     * Получить пользовательские имена атрибутов
     * для формирования сообщений валидатора.
     * @return array
     */
    public function attributes(): array
    {
        return [
            'title' => trans('Title'),
            'slug' => trans('Slug'),
            'teaser' => trans('Teaser'),

        ];
    }

    /**
     * Получить массив пользовательских строк перевода
     * для формирования сообщений валидатора.
     * @return array
     */
    public function messages(): array
    {
        return [

        ];
    }

    /**
     * Получить массив правил валидации,
     * которые будут применены к запросу.
     * @return array
     */
    public function rules(): array
    {
        return [
            'user_id' => [
                'bail',
                'sometimes',
                'required',
                'integer',
                'exists:users,id',

            ],

            // Main content.
            'title' => [
                'bail',
                'required',
                'string',
                'max:255',

            ],

            'slug' => [
                'bail',
                'required',
                'string',
                'max:255',

            ],

            'teaser' => [
                'nullable',
                'string',
                'max:255',

            ],

            'content' => [
                'nullable',
                'string',

            ],

            'description' => [
                'nullable',
                'string',
                'max:255',

            ],

            'keywords' => [
                'nullable',
                'string',
                'max:255',

            ],

            // Flags ?
            'state' => [
                'required',
                'string',
                'in:published,unpublished,draft',

            ],

            'on_mainpage' => [
                'nullable',
                'boolean',

            ],

            'is_favorite' => [
                'nullable',
                'boolean',

            ],

            'is_pinned' => [
                'nullable',
                'boolean',

            ],

            'is_catpinned' => [
                'nullable',
                'boolean',

            ],

            // Extension.
            'allow_com' => [
                'required',
                'numeric',
                'in:0,1,2',

            ],

            'views' => [
                'nullable',
                'integer',

            ],

            'votes' => [
                'nullable',
                'integer',

            ],

            'rating' => [
                'nullable',
                'integer',

            ],

            // Relations types.
            'image_id' => [
                'nullable',
                'integer',
                'exists:files,id',

            ],

            'categories' => [
                'nullable',
                'array',

            ],

            'categories.*' => [
                'integer',
                'exists:categories,id',

            ],

            /*'files' => [
                'nullable',
                'array',
            ],

            'files.*' => [
                'integer',
                'exists:files,id',
            ],

            'images' => [
                'nullable',
                'array',
            ],

            'images.*' => [
                'integer',
                'exists:files,id',
            ],*/

            'tags' => [
                'nullable',
                'array',

            ],

            'tags.*' => [
                'string',
                'max:255',
                'regex:/^[\w-]+$/u',

            ],

            // Временные метки.
            'date_at' => [
                'nullable',
                'string',
                'in:'.$this->allowedForInRule('date_at'),

            ],

            'created_at' => [
                'nullable',
                'required_with:date_at',
                // 'date_format:"Y-m-d H:i:s"',

            ],

            'updated_at' => [
                'nullable',
                'required_without:date_at',
                // 'date_format:"Y-m-d H:i:s"',

            ],

        ];
    }

    /**
     * Remove Emoji Characters in PHP by Himphen Hui.
     * @source https://medium.com/coding-cheatsheet/remove-emoji-characters-in-php-236034946f51
     * @param  string  $string
     * @return string
     */
    protected function removeEmoji(string $string)
    {
        // Match Emoticons.
        $regex_emoticons = '/[\x{1F600}-\x{1F64F}]/u';
        $clear_string = preg_replace($regex_emoticons, '', $string);

        // Match Miscellaneous Symbols and Pictographs.
        $regex_symbols = '/[\x{1F300}-\x{1F5FF}]/u';
        $clear_string = preg_replace($regex_symbols, '', $clear_string);

        // Match Transport And Map Symbols.
        $regex_transport = '/[\x{1F680}-\x{1F6FF}]/u';
        $clear_string = preg_replace($regex_transport, '', $clear_string);

        // Match Miscellaneous Symbols.
        $regex_misc = '/[\x{2600}-\x{26FF}]/u';
        $clear_string = preg_replace($regex_misc, '', $clear_string);

        // Match Dingbats.
        $regex_dingbats = '/[\x{2700}-\x{27BF}]/u';
        $clear_string = preg_replace($regex_dingbats, '', $clear_string);

        return $clear_string;
    }
}
