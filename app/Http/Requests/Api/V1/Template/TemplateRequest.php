<?php

namespace BBCMS\Http\Requests\Api\V1\Template;

use BBCMS\Models\Template;
use BBCMS\Http\Requests\Request;

class TemplateRequest extends Request
{
    public function authorize()
    {
        return true;
    }

    /**
     * Get data to be validated from the request.
     *
     * @return array
     */
    protected function validationData()
    {
        $filename = $this->get('filename');
        $content = $this->get('content', null);

        $template = new Template([
            'filename' => $filename,
        ]);

        $template->get();

        return $this->replace([
                'filename' => $filename,
                'content' => $content,
            ])
            ->merge([
                'template' => $template,
                'path' => $template->path,
                'exists' => $template->exists,
            ])
            ->all();
    }

    public function attributes()
    {
        return [
            'filename' => __('Template'),
            'content' => __('Content'),
        ];
    }

    public function messages()
    {
        return [
            // 'filename.required' => __('msg.filename.required'),
            'content.required' => __('msg.content.required'),
        ];
    }
}
