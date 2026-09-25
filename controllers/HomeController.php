<?php
declare(strict_types=1);

class HomeController extends Controller
{
    public function index(): void
    {
        $photoModel = new Photo();

        $this->render('home/index', [
            'pageTitle' => 'Welcome',
            'statistics' => $photoModel->siteStatistics(),
            'latestPhotos' => $photoModel->latest(8),
            'heroPhotos' => $photoModel->mostLiked(5),
            'topContributors' => (new User())->topContributors(4),
            'lastLogin' => AuthController::readLastLoginCookie(),
        ]);
    }

    public function about(): void
    {
        $this->render('home/about', [
            'pageTitle' => 'About Us',
            'statistics' => (new Photo())->siteStatistics(),
        ]);
    }
}
