<?php

namespace BBCMS\Http\Controllers\Admin;

use BBCMS\Models\Category;
use BBCMS\Http\Requests\Admin\CategoryRequest;
use BBCMS\Http\Controllers\Admin\AdminController;

use Illuminate\Http\Request;

class CategoriesController extends AdminController
{
    protected $model;
    protected $x_fields;
    protected $template = 'categories';

    public function __construct(Category $model)
    {
        parent::__construct();
        $this->authorizeResource(Category::class);

        $this->model = $model;
        $this->x_fields = $model->x_fields;
    }

    public function index()
    {
        $this->authorize(Category::class);

        $categories = $this->model
            ->orderByRaw('ISNULL(`position`), `position` ASC')
            ->withCount(['articles'])
            ->get()
            ->nested();

        return $this->makeResponse('index', compact('categories'));
    }

    public function create()
    {
        return $this->makeResponse('create', [
            'template_list' => select_dir('custom_views', true),
            'category' => [],
            'x_fields' => $this->x_fields,
        ]);
    }

    public function store(CategoryRequest $request)
    {
        $category = $this->model->fill($request->all());

        foreach ($this->x_fields->pluck('name') as $x_field) {
            $category->{$x_field} = $request->{$x_field};
        }

        $category->save();

        // Image.
        if ($request->image_id) {
            // Get related Model.
            $image = $category->files()->getRelated();
            // Create new acosiation. associate($request->image_id)
            $image->whereId($request->image_id)->update([
                'attachment_type' => $category->getMorphClass(),
                'attachment_id' => $category->id
            ]);
        }
        
        return $this->makeRedirect(true, 'admin.categories.index', sprintf(
            __('msg.store'), $category->url, route('admin.categories.edit', $category)
        ));
    }

    public function edit(Category $category)
    {
        $category->when($category->image_id, function ($query) {
            $query->with(['image']);
        });

        return $this->makeResponse('edit', [
            'template_list' => select_dir('custom_views', true),
            'category' => $category,
            'x_fields' => $this->x_fields,
        ]);
    }

    public function update(CategoryRequest $request, Category $category)
    {
        foreach ($this->x_fields->pluck('name') as $x_field) {
            $category->{$x_field} = $request->{$x_field};
        }

        $category->update($request->all());

        // Image. Only if empty image_id, then keep previos image_id.
        if ($request->image_id) {
            // Get related Model.
            $image = $category->files()->getRelated();
            // Delete acosiation with old. dissociate($request->image_id)
            if ($category->image_id) {
                $image->whereId($category->image_id)->update([
                    'attachment_type' => null,
                    'attachment_id' => null
                ]);
            }
            // Create new acosiation. associate($request->image_id)
            $image->whereId($request->image_id)->update([
                'attachment_type' => $category->getMorphClass(),
                'attachment_id' => $category->id
            ]);
        }

        return $this->makeRedirect(true, 'admin.categories.index', sprintf(
            __('msg.update'), $category->url, route('admin.categories.edit', $category)
        ));
    }

    public function destroy(Category $category)
    {
        if ($this->model->where('parent_id', $category->id)->count() or $category->articles->count()) {
            return $this->makeRedirect(false, 'admin.categories.index', __('msg.not_empty'));
        }

        $category->delete();
        
        return $this->makeRedirect(true, 'admin.categories.index', __('msg.destroy'));
    }

    public function positionReset(Request $request)
    {
        $this->authorize('otherUpdate', Category::class);
        $this->model->positionReset();
        
        return $this->makeRedirect(true, 'admin.categories.index', __('msg.position_reset'));
    }

    public function positionUpdate(Request $request)
    {
        $this->authorize('otherUpdate', Category::class);
        $request->validate([
            'list' => 'required|array',
        ]);

        if ($this->model->positionUpdate($request)) {
            return response()->json(['status' => true, 'message' => __('msg.position_update')], 200);
        }

        return response()->json(['status' => false, 'message' => __('msg.not_complete')], 200);
    }
}
