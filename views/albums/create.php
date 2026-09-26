<?php
?>
<section class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="card auth-card shadow-sm">
                <div class="card-body p-4 p-md-5">
                    <h1 class="display-font h3 mb-1">New album</h1>
                    <p class="text-body-secondary small mb-4">Group related memories together.</p>

                    <form method="post" action="<?= e(url('/album/store')) ?>" class="needs-validation" novalidate data-validate>
                        <?= csrfField() ?>
                        <div class="mb-3">
                            <label for="albumTitle" class="form-label">Album title <span class="text-danger">*</span></label>
                            <input type="text" class="form-control<?= invalidClass('title') ?>" id="albumTitle" name="title" required maxlength="150"
                                   value="<?= old('title') ?>" placeholder="e.g. Graduation 2026">
                            <div class="invalid-feedback">A title is required (max 150 characters).</div>
                            <?= fieldError('title') ?>
                        </div>
                        <div class="mb-4">
                            <label for="albumDescription" class="form-label">Description</label>
                            <textarea class="form-control<?= invalidClass('description') ?>" id="albumDescription" name="description" rows="3" maxlength="1000"
                                      data-counter="#albumDescriptionCounter"><?= old('description') ?></textarea>
                            <small class="text-body-secondary d-block text-end"><span id="albumDescriptionCounter">0</span>/1000</small>
                            <?= fieldError('description') ?>
                        </div>
                        <button type="submit" class="btn btn-accent w-100"><i class="bi bi-folder-plus"></i> Create album</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>
