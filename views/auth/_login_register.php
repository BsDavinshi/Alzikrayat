<?php
$activeTab = Session::old('first_name') !== '' ? 'register' : 'login';
?>
<div class="card auth-card shadow-sm login-register" id="login-register">
    <div class="card-header bg-transparent border-0 pt-4 px-4">
        <ul class="nav nav-pills nav-fill gap-2" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link <?= $activeTab === 'login' ? 'active' : '' ?>" data-bs-toggle="pill" data-bs-target="#tab-login" type="button" role="tab" aria-controls="tab-login">
                    <i class="bi bi-box-arrow-in-right"></i> Login
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link <?= $activeTab === 'register' ? 'active' : '' ?>" data-bs-toggle="pill" data-bs-target="#tab-register" type="button" role="tab" aria-controls="tab-register">
                    <i class="bi bi-person-plus"></i> Register
                </button>
            </li>
        </ul>
    </div>
    <div class="card-body p-4 tab-content">
        <div class="tab-pane fade <?= $activeTab === 'login' ? 'show active' : '' ?>" id="tab-login" role="tabpanel">
            <?= View::partial('auth/_login_form', ['idPrefix' => 'home-login', 'lastLogin' => $lastLogin ?? null]) ?>
        </div>
        <div class="tab-pane fade <?= $activeTab === 'register' ? 'show active' : '' ?>" id="tab-register" role="tabpanel">
            <?= View::partial('auth/_register_form', ['idPrefix' => 'home-register']) ?>
        </div>
    </div>
</div>
