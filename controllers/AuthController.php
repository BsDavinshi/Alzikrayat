<?php
declare(strict_types=1);

class AuthController extends Controller
{
    public function showLogin(): void
    {
        if (Auth::check()) {
            $this->redirect('/photos');
        }

        $this->render('auth/login', [
            'pageTitle' => 'Login',
            'lastLogin' => self::readLastLoginCookie(),
        ]);
    }

    public function login(): void
    {
        $this->verifyCsrf();

        $email = mb_strtolower((string) $this->request->input('email', ''));
        $password = $this->request->raw('password');

        $lockedSeconds = $this->secondsUntilUnlock();
        if ($lockedSeconds > 0) {
            Session::flashInput(['email' => $email]);
            Session::flash('error', "Too many failed attempts. Please wait {$lockedSeconds} seconds and try again.");
            $this->redirect('/login');
        }

        $errors = User::validateLogin(['email' => $email, 'password' => $password]);
        if ($errors !== []) {
            $this->backWithErrors('/login', $errors, ['email' => $email]);
        }

        $userModel = new User();
        $user = $userModel->findByEmail($email);

        if ($user === null || !password_verify($password, (string) $user['password'])) {
            $this->recordFailedAttempt();
            Session::flashInput(['email' => $email]);
            Session::flash('error', 'Invalid email or password.');
            $this->redirect('/login');
        }

        if (password_needs_rehash((string) $user['password'], PASSWORD_BCRYPT)) {
            $userModel->rehashPassword((int) $user['id'], $password);
        }

        Session::remove('login_attempts');
        $this->completeLogin($user, 'Welcome back, ' . $user['first_name'] . '!');
    }

    public function showRegister(): void
    {
        if (Auth::check()) {
            $this->redirect('/photos');
        }

        $this->render('auth/register', ['pageTitle' => 'Create an account']);
    }

    public function register(): void
    {
        $this->verifyCsrf();

        $input = [
            'first_name' => (string) $this->request->input('first_name', ''),
            'last_name' => (string) $this->request->input('last_name', ''),
            'email' => mb_strtolower((string) $this->request->input('email', '')),
            'password' => $this->request->raw('password'),
            'password_confirm' => $this->request->raw('password_confirm'),
        ];
        $stickyInput = ['first_name' => $input['first_name'], 'last_name' => $input['last_name'], 'email' => $input['email']];

        $errors = User::validateRegistration($input);
        $userModel = new User();

        if (!isset($errors['email']) && $userModel->emailExists($input['email'])) {
            $errors['email'] = 'An account with this email already exists.';
        }
        if ($errors !== []) {
            $this->backWithErrors('/register', $errors, $stickyInput);
        }

        $newUserId = $userModel->create($input);
        $this->completeLogin($userModel->findById($newUserId) ?? [], 'Welcome to Alzikrayat, ' . $input['first_name'] . '! Share your first memory.');
    }

    public function logout(): void
    {
        $this->verifyCsrf();
        Auth::logout();
        Session::flash('success', 'You have been logged out. See you soon!');
        $this->redirect('/');
    }

    public static function readLastLoginCookie(): ?string
    {
        $cookieValue = $_COOKIE[(string) Config::get('auth.lastLoginCookie')] ?? null;
        if (!is_string($cookieValue)) {
            return null;
        }

        $loginTime = DateTimeImmutable::createFromFormat(DateTimeInterface::ATOM, $cookieValue);
        return $loginTime ? $loginTime->format('l, F j, Y \a\t g:i:s A') : null;
    }

    private function completeLogin(array $user, string $welcomeMessage): never
    {
        Auth::login($user);
        $this->writeLastLoginCookie();
        Session::flash('success', $welcomeMessage);

        $intendedPath = (string) (Session::pull('intended_url') ?? '/photos');
        if (!str_starts_with($intendedPath, '/') || str_starts_with($intendedPath, '//')) {
            $intendedPath = '/photos';
        }
        $this->redirect($intendedPath);
    }

    private function writeLastLoginCookie(): void
    {
        $lifetimeDays = (int) Config::get('auth.lastLoginDays', 7);

        setcookie((string) Config::get('auth.lastLoginCookie'), date(DATE_ATOM), [
            'expires' => time() + $lifetimeDays * 86400,
            'path' => '/',
            'secure' => Session::isHttps(),
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
    }

    private function recordFailedAttempt(): void
    {
        $attempts = Session::get('login_attempts', ['count' => 0, 'lockedUntil' => 0]);
        $attempts['count']++;

        if ($attempts['count'] >= (int) Config::get('auth.maxLoginAttempts', 5)) {
            $attempts = ['count' => 0, 'lockedUntil' => time() + (int) Config::get('auth.lockoutSeconds', 60)];
        }
        Session::set('login_attempts', $attempts);
    }

    private function secondsUntilUnlock(): int
    {
        $attempts = Session::get('login_attempts', ['lockedUntil' => 0]);
        return max(0, (int) $attempts['lockedUntil'] - time());
    }
}
