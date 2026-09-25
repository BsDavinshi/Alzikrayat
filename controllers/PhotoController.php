<?php
declare(strict_types=1);

class PhotoController extends Controller
{
    public function index(): void
    {
        $searchTerm = mb_substr((string) $this->request->query('q', ''), 0, 100);
        $pageNumber = max(1, (int) $this->request->query('page', 1));

        $pagination = (new Photo())->paginate($pageNumber, (int) Config::get('gallery.perPage', 12), $searchTerm);

        $this->render('photos/index', [
            'pageTitle' => $searchTerm !== '' ? 'Search: ' . $searchTerm : 'Gallery',
            'pagination' => $pagination,
            'searchTerm' => $searchTerm,
            'galleryStyle' => $this->resolveGalleryStyle(),
        ]);
    }

    public function show(int $photoId): void
    {
        $photoModel = new Photo();
        $photo = $photoModel->findWithDetails($photoId, Auth::id());

        if ($photo === null) {
            $this->abort(404, 'This memory does not exist or has been deleted.');
        }

        $this->render('photos/show', [
            'pageTitle' => $photo['title'],
            'photo' => $photo,
            'comments' => (new Comment())->forPhoto($photoId),
            'taggedUsers' => (new PhotoTag())->forPhoto($photoId),
            'neighbours' => $photoModel->neighbours($photoId, (string) $photo['date_time']),
            'isOwner' => Auth::id() === (int) $photo['user_id'],
            'pageScripts' => ['js/photo-show.js'],
        ]);
    }

    public function create(): void
    {
        $this->requireAuth();

        $this->render('photos/create', [
            'pageTitle' => 'Upload a memory',
            'albums' => (new Album())->forUser((int) Auth::id()),
            'selectedAlbumId' => (int) $this->request->query('album', Session::old('album_id', '0')),
            'filters' => Photo::ALLOWED_FILTERS,
            'maxBytes' => (int) Config::get('upload.maxBytes'),
            'pageScripts' => ['js/filters.js', 'js/tagging.js', 'js/upload.js'],
        ]);
    }

    public function store(): void
    {
        $this->requireAuth();
        $this->verifyCsrf();

        $currentUserId = (int) Auth::id();
        $input = [
            'title' => (string) $this->request->input('title', ''),
            'description' => (string) $this->request->input('description', ''),
            'album_id' => (string) $this->request->input('album_id', ''),
            'filter_name' => (string) $this->request->input('filter_name', 'none'),
        ];
        if ($input['album_id'] === '0') {
            $input['album_id'] = '';
        }

        $errors = Photo::validate($input);

        $uploadError = ImageUploader::validate($this->request->file('photo'));
        if ($uploadError !== null) {
            $errors['photo'] = $uploadError;
        }

        $albumModel = new Album();
        if (!isset($errors['album_id']) && $input['album_id'] !== '' && !$albumModel->belongsTo((int) $input['album_id'], $currentUserId)) {
            $errors['album_id'] = 'You can only add photos to your own albums.';
        }

        if ($errors !== []) {
            $this->backWithErrors('/photo/create', $errors, $input);
        }

        $rawTaggedIds = $this->request->input('tagged_users', []);
        $candidateIds = array_filter(array_map('intval', is_array($rawTaggedIds) ? $rawTaggedIds : []), static fn(int $id): bool => $id > 0);
        $taggedUserIds = (new User())->filterExistingIds(array_unique($candidateIds));

        $storedFileName = ImageUploader::store((array) $this->request->file('photo'));
        $photoModel = new Photo();

        try {
            $photoModel->beginTransaction();
            $newPhotoId = $photoModel->create([
                'user_id' => $currentUserId,
                'album_id' => $input['album_id'] !== '' ? (int) $input['album_id'] : null,
                'file_name' => $storedFileName,
                'title' => $input['title'],
                'description' => $input['description'],
                'filter_name' => $input['filter_name'],
            ]);
            (new PhotoTag())->tagUsers($newPhotoId, $taggedUserIds, $currentUserId);
            $photoModel->commit();
        } catch (Throwable $databaseError) {
            $photoModel->rollBack();
            ImageUploader::delete($storedFileName);
            throw $databaseError;
        }

        Session::flash('success', 'Your memory "' . $input['title'] . '" was uploaded.');
        $this->redirect('/photo/' . $newPhotoId);
    }

    public function delete(int $photoId): void
    {
        $this->requireAuth();
        $this->verifyCsrf();

        $photoModel = new Photo();
        $photo = $photoModel->findById($photoId);

        if ($photo === null) {
            $this->abort(404, 'This memory does not exist or has already been deleted.');
        }
        if ((int) $photo['user_id'] !== Auth::id()) {
            $this->abort(403, 'You can only delete photos that you uploaded.');
        }

        $photoModel->deleteById($photoId);
        ImageUploader::delete((string) $photo['file_name']);

        Session::flash('success', 'The photo "' . $photo['title'] . '" was deleted.');
        $this->redirect('/user/' . Auth::id());
    }

    public function toggleLike(int $photoId): void
    {
        $this->requireAuth();
        $this->verifyCsrf();

        if ((new Photo())->findById($photoId) === null) {
            $this->abort(404, 'Photo not found.');
        }

        $likeState = (new PhotoLike())->toggle($photoId, (int) Auth::id());

        if ($this->request->isAjax()) {
            $this->json(['success' => true] + $likeState);
        }
        $this->redirect('/photo/' . $photoId);
    }

    public function untag(int $photoId, int $taggedUserId): void
    {
        $this->requireAuth();
        $this->verifyCsrf();

        $photo = (new Photo())->findById($photoId);
        if ($photo === null) {
            $this->abort(404, 'Photo not found.');
        }

        $currentUserId = Auth::id();
        if ($currentUserId !== $taggedUserId && $currentUserId !== (int) $photo['user_id']) {
            $this->abort(403, 'Only the tagged person or the photo owner can remove this tag.');
        }

        (new PhotoTag())->untag($photoId, $taggedUserId);
        Session::flash('success', 'Tag removed.');
        $this->redirect('/photo/' . $photoId);
    }
}
