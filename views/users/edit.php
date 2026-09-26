<?php
$namePattern = '[A-Za-z؀-ۿ]+( [A-Za-z؀-ۿ]+)*';
?>
<section class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-10 col-lg-7">
            <div class="card auth-card shadow-sm">
                <div class="card-body p-4 p-md-5">
                    <h1 class="display-font h3 mb-4">Edit your profile</h1>
                    <form method="post" action="<?= e(url('/profile/update')) ?>" class="needs-validation" novalidate data-validate>
                        <?= csrfField() ?>
                        <div class="row g-3">
                            <div class="col-sm-6">
                                <label for="editFirstName" class="form-label">First name</label>
                                <input type="text" class="form-control<?= invalidClass('first_name') ?>" id="editFirstName" name="first_name" required maxlength="50"
                                       pattern="<?= e($namePattern) ?>" data-rule="name" value="<?= old('first_name', (string) $profile['first_name']) ?>">
                                <div class="invalid-feedback">Letters only, up to 50 characters.</div>
                                <?= fieldError('first_name') ?>
                            </div>
                            <div class="col-sm-6">
                                <label for="editLastName" class="form-label">Last name</label>
                                <input type="text" class="form-control<?= invalidClass('last_name') ?>" id="editLastName" name="last_name" required maxlength="50"
                                       pattern="<?= e($namePattern) ?>" data-rule="name" value="<?= old('last_name', (string) $profile['last_name']) ?>">
                                <div class="invalid-feedback">Letters only, up to 50 characters.</div>
                                <?= fieldError('last_name') ?>
                            </div>
                            <div class="col-sm-6">
                                <label for="editOccupation" class="form-label">Occupation</label>
                                <input type="text" class="form-control<?= invalidClass('occupation') ?>" id="editOccupation" name="occupation" maxlength="100"
                                       value="<?= old('occupation', (string) $profile['occupation']) ?>" placeholder="e.g. Photographer">
                                <?= fieldError('occupation') ?>
                            </div>
                            <div class="col-sm-6">
                                <label for="editLocation" class="form-label">Location</label>
                                <input type="text" class="form-control<?= invalidClass('location') ?>" id="editLocation" name="location" maxlength="100"
                                       value="<?= old('location', (string) $profile['location']) ?>" placeholder="e.g. Khartoum">
                                <?= fieldError('location') ?>
                            </div>
                            <div class="col-12">
                                <label for="editDescription" class="form-label">About you</label>
                                <textarea class="form-control<?= invalidClass('description') ?>" id="editDescription" name="description" rows="4" maxlength="1000"
                                          data-counter="#editDescriptionCounter"><?= old('description', (string) $profile['description']) ?></textarea>
                                <small class="text-body-secondary d-block text-end"><span id="editDescriptionCounter">0</span>/1000</small>
                                <?= fieldError('description') ?>
                            </div>
                        </div>
                        <div class="d-flex gap-2 mt-4">
                            <button type="submit" class="btn btn-accent"><i class="bi bi-check2"></i> Save changes</button>
                            <a href="<?= e(url('/user/' . $profile['id'])) ?>" class="btn btn-outline-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>
