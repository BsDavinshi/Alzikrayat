<?php
declare(strict_types=1);

class CommentController extends Controller
{
    public function store(int $photoId): void
    {
        $this->requireAuth();
        $this->verifyCsrf();

        $photo = (new Photo())->findById($photoId);
        if ($photo === null) {
            $this->abort(404, 'This memory no longer exists.');
        }

        $commentText = (string) $this->request->input('comment', '');
        $errors = Comment::validate(['comment' => $commentText]);

        if ($errors !== []) {
            if ($this->request->isAjax()) {
                $this->json(['success' => false, 'errors' => $errors, 'message' => reset($errors)], 422);
            }
            $this->backWithErrors('/photo/' . $photoId . '#comments', $errors, ['comment' => $commentText]);
        }

        $commentModel = new Comment();
        $newCommentId = $commentModel->create($photoId, (int) Auth::id(), $commentText);

        if ($this->request->isAjax()) {
            $savedComment = $commentModel->findWithContext($newCommentId);
            $this->json([
                'success' => true,
                'html' => View::partial('photos/_comment', ['comment' => $savedComment, 'photoOwnerId' => (int) $photo['user_id']]),
                'count' => $commentModel->countForPhoto($photoId),
            ], 201);
        }

        Session::flash('success', 'Comment added.');
        $this->redirect('/photo/' . $photoId . '#comment-' . $newCommentId);
    }

    public function delete(int $commentId): void
    {
        $this->requireAuth();
        $this->verifyCsrf();

        $commentModel = new Comment();
        $comment = $commentModel->findWithContext($commentId);
        if ($comment === null) {
            $this->abort(404, 'Comment not found.');
        }

        $currentUserId = Auth::id();
        if ($currentUserId !== (int) $comment['user_id'] && $currentUserId !== (int) $comment['photo_owner_id']) {
            $this->abort(403, 'You can only delete your own comments or comments on your photos.');
        }

        $commentModel->deleteById($commentId);

        if ($this->request->isAjax()) {
            $this->json(['success' => true, 'count' => $commentModel->countForPhoto((int) $comment['photo_id'])]);
        }
        Session::flash('success', 'Comment deleted.');
        $this->redirect('/photo/' . $comment['photo_id'] . '#comments');
    }
}
