<?php
declare(strict_types=1);

class UserController extends Controller
{
    public function profile(int $userId): void
    {
        $profile = (new User())->findProfile($userId);
        if ($profile === null) {
            $this->abort(404, 'This member does not exist.');
        }

        $photoModel = new Photo();
        $pagination = $photoModel->paginate(1, 60, '', $userId);

        $this->render('users/profile', [
            'pageTitle' => $profile['first_name'] . ' ' . $profile['last_name'],
            'profile' => $profile,
            'photos' => $pagination['items'],
            'taggedPhotos' => $photoModel->taggedWith($userId),
            'albums' => (new Album())->forUser($userId),
            'isOwnProfile' => Auth::id() === $userId,
        ]);
    }

    public function edit(): void
    {
        $this->requireAuth();

        $this->render('users/edit', [
            'pageTitle' => 'Edit profile',
            'profile' => (new User())->findProfile((int) Auth::id()),
        ]);
    }

    public function update(): void
    {
        $this->requireAuth();
        $this->verifyCsrf();

        $input = [];
        foreach (['first_name', 'last_name', 'location', 'occupation', 'description'] as $field) {
            $input[$field] = (string) $this->request->input($field, '');
        }

        $errors = User::validateProfile($input);
        if ($errors !== []) {
            $this->backWithErrors('/profile/edit', $errors, $input);
        }

        $userModel = new User();
        $userModel->updateProfile((int) Auth::id(), $input);
        Auth::refresh($userModel->findById((int) Auth::id()) ?? []);

        Session::flash('success', 'Profile updated.');
        $this->redirect('/user/' . Auth::id());
    }

    public function search(): void
    {
        $this->requireAuth();

        $searchTerm = mb_substr((string) $this->request->query('q', ''), 0, 50);
        if (mb_strlen($searchTerm) < 1) {
            $this->json(['users' => []]);
        }

        $matches = (new User())->searchByName($searchTerm, (int) Auth::id());
        $suggestions = array_map(static fn(array $user): array => [
            'id' => (int) $user['id'],
            'name' => $user['first_name'] . ' ' . $user['last_name'],
            'initials' => initials($user['first_name'], $user['last_name']),
            'color' => avatarColor((int) $user['id']),
        ], $matches);

        $this->json(['users' => $suggestions]);
    }
}
