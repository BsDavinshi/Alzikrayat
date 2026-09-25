<?php
declare(strict_types=1);

abstract class Controller
{
    protected Request $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    protected function render(string $viewName, array $viewData = []): void
    {
        echo View::render($viewName, $viewData);
    }

    protected function json(array $payload, int $statusCode = 200): never
    {
        Response::json($payload, $statusCode);
    }

    protected function redirect(string $path): never
    {
        Response::redirect(url($path), 303);
    }

    protected function abort(int $statusCode, string $message): never
    {
        if ($this->request->isAjax()) {
            $this->json(['success' => false, 'message' => $message], $statusCode);
        }
        (new ErrorController($this->request))->show($statusCode, $message);
        exit;
    }

    protected function requireAuth(): void
    {
        if (Auth::check()) {
            return;
        }
        if ($this->request->isAjax()) {
            $this->json(['success' => false, 'message' => 'Please login first.'], 401);
        }
        if ($this->request->getMethod() === 'GET') {
            Session::set('intended_url', $this->request->getPathWithQuery());
        }
        Session::flash('info', 'Please login to continue.');
        $this->redirect('/login');
    }

    protected function verifyCsrf(): void
    {
        $submittedToken = $_POST['_csrf'] ?? $this->request->header('X-CSRF-Token');
        if (!Session::verifyCsrf(is_string($submittedToken) ? $submittedToken : null)) {
            $this->abort(419, 'Your session expired or the form is invalid. Please refresh the page and try again.');
        }
    }

    protected function backWithErrors(string $path, array $errors, array $input = []): never
    {
        Session::flashErrors($errors);
        Session::flashInput($input);
        Session::flash('error', 'Please fix the highlighted fields.');
        $this->redirect($path);
    }

    protected function resolveGalleryStyle(): string
    {
        $availableStyles = Config::get('gallery.styles', []);
        $cookieName = (string) Config::get('gallery.styleCookie');
        $requestedStyle = (string) $this->request->query('style', '');

        if (isset($availableStyles[$requestedStyle])) {
            setcookie($cookieName, $requestedStyle, [
                'expires' => time() + 365 * 86400,
                'path' => '/',
                'secure' => Session::isHttps(),
                'httponly' => true,
                'samesite' => 'Lax',
            ]);
            return $requestedStyle;
        }

        $savedStyle = (string) ($_COOKIE[$cookieName] ?? '');
        return isset($availableStyles[$savedStyle]) ? $savedStyle : 'grid3';
    }
}
