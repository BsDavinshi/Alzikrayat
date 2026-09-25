<?php
declare(strict_types=1);

class AlbumController extends Controller
{
    public function index(): void
    {
        $this->render('albums/index', [
            'pageTitle' => 'Albums',
            'albums' => (new Album())->allWithCovers(),
        ]);
    }

    public function show(int $albumId): void
    {
        $album = (new Album())->findWithOwner($albumId);
        if ($album === null) {
            $this->abort(404, 'This album does not exist.');
        }

        $pageNumber = max(1, (int) $this->request->query('page', 1));
        $pagination = (new Photo())->paginate($pageNumber, (int) Config::get('gallery.perPage', 12), '', null, $albumId);

        $this->render('albums/show', [
            'pageTitle' => $album['title'],
            'album' => $album,
            'pagination' => $pagination,
            'galleryStyle' => $this->resolveGalleryStyle(),
            'isOwner' => Auth::id() === (int) $album['user_id'],
        ]);
    }

    public function create(): void
    {
        $this->requireAuth();
        $this->render('albums/create', ['pageTitle' => 'New album']);
    }

    public function store(): void
    {
        $this->requireAuth();
        $this->verifyCsrf();

        $input = [
            'title' => (string) $this->request->input('title', ''),
            'description' => (string) $this->request->input('description', ''),
        ];

        $errors = Album::validate($input);
        if ($errors !== []) {
            $this->backWithErrors('/album/create', $errors, $input);
        }

        $newAlbumId = (new Album())->create((int) Auth::id(), $input['title'], $input['description']);
        Session::flash('success', 'Album created. Now add some memories to it!');
        $this->redirect('/album/' . $newAlbumId);
    }

    public function delete(int $albumId): void
    {
        $this->requireAuth();
        $this->verifyCsrf();

        $albumModel = new Album();
        if (!$albumModel->belongsTo($albumId, (int) Auth::id())) {
            $this->abort(403, 'You can only delete your own albums.');
        }

        $albumModel->deleteById($albumId);
        Session::flash('success', 'Album deleted. Its photos are still in your gallery.');
        $this->redirect('/user/' . Auth::id());
    }
}
