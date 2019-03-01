<?php

namespace BBCMS\Http\Controllers\Admin;

use BBCMS\Models\Comment;
use BBCMS\Http\Requests\CommentUpdateRequest;
use BBCMS\Http\Requests\Admin\CommentsRequest;
use BBCMS\Http\Controllers\Admin\AdminController;

class CommentsController extends AdminController
{
    protected $model;
    protected $template = 'comments';

    public function __construct(Comment $model)
    {
        parent::__construct();
        $this->authorizeResource(Comment::class);

        $this->model = $model;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $this->authorize($this->model);
        
        $comments = $this->model
            ->with(['commentable', 'user'])
            ->orderBy('id', 'desc')
            ->paginate(25);

        return $this->makeResponse('index', compact('comments'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \BBCMS\Models\Comment  $comment
     * @return \Illuminate\Http\Response
     */
    public function edit(Comment $comment)
    {
        $this->authorize($comment);
        
        return $this->makeResponse('edit', compact('comment'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \BBCMS\Http\Requests\CommentUpdateRequest  $request
     * @param  \BBCMS\Models\Comment  $comment
     * @return \Illuminate\Http\Response
     */
    public function update(CommentUpdateRequest $request, Comment $comment)
    {
        $this->authorize($comment);
        
        $comment->update($request->only(['content']));
        
        return $this->makeRedirect(true, 'admin.comments.index', sprintf(
            __('msg.update'), $comment->url, route('admin.comments.edit', $comment)
        ));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \BBCMS\Models\Comment  $comment
     * @return \Illuminate\Http\Response
     */
    public function destroy(Comment $comment)
    {
        $this->authorize($comment);

        $comment->delete();
        
        return $this->makeRedirect(true, 'admin.comments.index', __('msg.destroy'));
    }

    /**
     * Mass updates to Comment.
     *
     * @param  \BBCMS\Http\Requests\Admin\CommentsRequest  $request
     * @param  \BBCMS\Models\Comment  $comment
     * @return \Illuminate\Http\Response
     */
    public function massUpdate(CommentsRequest $request)
    {
        $this->authorize('otherUpdate', $this->model);

        $comments = $this->model->whereIn('id', $request->comments);
        $messages = [];

        switch ($request->mass_action) {
            case 'published':
                if (! $comments->update(['is_approved' => true])) {
                    $messages[] = 'unable to published';
                }
                break;
            case 'unpublished':
                if (! $comments->update(['is_approved' => false])) {
                    $messages[] = 'unable to unpublished';
                }
                break;
            case 'delete':
                if (! $comments->get()->each->delete()) {
                    $messages[] = 'unable to delete';
                }
                break;
        }

        $message = empty($messages) ? 'msg.complete' : 'msg.complete_but_null';

        return $this->makeRedirect(true, 'admin.comments.index', $message);
    }
}
