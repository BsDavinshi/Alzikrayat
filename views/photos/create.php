<?php
$maxMegabytes = round($maxBytes / 1048576, 1);
?>
<section class="container py-4 py-md-5">
    <div class="mb-4">
        <span class="eyebrow">New memory</span>
        <h1 class="display-font h1 mt-1 mb-1">Upload a photo</h1>
        <p class="text-body-secondary mb-0">Pick an image, style it with a filter, tell its story and tag the people in it.</p>
    </div>

    <form method="post" action="<?= e(url('/photo/store')) ?>" enctype="multipart/form-data" id="uploadForm"
          class="needs-validation" novalidate data-validate>
        <?= csrfField() ?>
        <input type="hidden" name="MAX_FILE_SIZE" value="<?= e($maxBytes) ?>">
        <input type="hidden" name="filter_name" id="filterName" value="none">

        <div class="row g-4">
            <div class="col-lg-7">
                <div class="card upload-card h-100">
                    <div class="card-body p-3 p-md-4">
                        <label for="photoInput" class="dropzone<?= invalidClass('photo') ?>" id="dropzone">
                            <input type="file" class="visually-hidden" id="photoInput" name="photo" required
                                   accept="image/jpeg,image/png,image/gif,image/webp" data-rule="image" data-max-bytes="<?= e($maxBytes) ?>">
                            <span class="dropzone-empty" id="dropzoneEmpty">
                                <i class="bi bi-cloud-arrow-up"></i>
                                <strong>Drop an image here or click to browse</strong>
                                <small class="text-body-secondary">JPG, PNG, GIF or WEBP · up to <?= e($maxMegabytes) ?> MB</small>
                            </span>
                            <canvas id="previewCanvas" class="preview-canvas d-none" aria-label="Filtered preview"></canvas>
                        </label>
                        <div class="invalid-feedback" id="photoFeedback">Please choose a JPG, PNG, GIF or WEBP image under <?= e($maxMegabytes) ?> MB.</div>
                        <?= fieldError('photo') ?>
                        <?= fieldError('filter_name') ?>

                        <div class="filter-studio mt-3 d-none" id="filterStudio">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="small fw-semibold"><i class="bi bi-magic"></i> Filter studio</span>
                                <button type="button" class="btn btn-link btn-sm p-0" id="resetFilters">Reset</button>
                            </div>
                            <div class="filter-strip" id="filterStrip" role="radiogroup" aria-label="Filters">
                                <?php foreach ($filters as $filterName): ?>
                                    <button type="button" class="filter-chip <?= $filterName === 'none' ? 'active' : '' ?>" data-filter="<?= e($filterName) ?>" role="radio" aria-checked="<?= $filterName === 'none' ? 'true' : 'false' ?>">
                                        <canvas width="72" height="72" class="filter-thumb" aria-hidden="true"></canvas>
                                        <span><?= e($filterName === 'none' ? 'Original' : filterLabel($filterName)) ?></span>
                                    </button>
                                <?php endforeach; ?>
                            </div>
                            <div class="row g-3 mt-1">
                                <div class="col-sm-6">
                                    <label for="brightnessRange" class="form-label small mb-0">Brightness <span id="brightnessValue">0</span></label>
                                    <input type="range" class="form-range" id="brightnessRange" min="-100" max="100" value="0">
                                </div>
                                <div class="col-sm-6">
                                    <label for="contrastRange" class="form-label small mb-0">Contrast <span id="contrastValue">0</span></label>
                                    <input type="range" class="form-range" id="contrastRange" min="-100" max="100" value="0">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-5">
                <div class="card upload-card">
                    <div class="card-body p-3 p-md-4">
                        <div class="mb-3">
                            <label for="titleInput" class="form-label">Title <span class="text-danger">*</span></label>
                            <input type="text" class="form-control<?= invalidClass('title') ?>" id="titleInput" name="title" required maxlength="200"
                                   value="<?= old('title') ?>" placeholder="e.g. Sunset over the Nile" data-counter="#titleCounter">
                            <div class="d-flex justify-content-between">
                                <div class="invalid-feedback">A title is required (max 200 characters).</div>
                                <small class="text-body-secondary ms-auto"><span id="titleCounter">0</span>/200</small>
                            </div>
                            <?= fieldError('title') ?>
                        </div>

                        <div class="mb-3">
                            <label for="descriptionInput" class="form-label">Story <small class="text-body-secondary">(optional)</small></label>
                            <textarea class="form-control<?= invalidClass('description') ?>" id="descriptionInput" name="description" rows="4" maxlength="2000"
                                      placeholder="What happened in this moment?" data-counter="#descriptionCounter"><?= old('description') ?></textarea>
                            <small class="text-body-secondary d-block text-end"><span id="descriptionCounter">0</span>/2000</small>
                            <?= fieldError('description') ?>
                        </div>

                        <div class="mb-3">
                            <label for="albumSelect" class="form-label">Album</label>
                            <div class="input-group">
                                <select class="form-select<?= invalidClass('album_id') ?>" id="albumSelect" name="album_id">
                                    <option value="0">No album</option>
                                    <?php foreach ($albums as $album): ?>
                                        <option value="<?= e($album['id']) ?>" <?= (int) $album['id'] === $selectedAlbumId ? 'selected' : '' ?>><?= e($album['title']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <a class="btn btn-outline-secondary" href="<?= e(url('/album/create')) ?>" title="Create album"><i class="bi bi-plus-lg"></i></a>
                            </div>
                            <?= fieldError('album_id') ?>
                        </div>

                        <div class="mb-4">
                            <label for="tagSearch" class="form-label">Tag people <small class="text-body-secondary">(registered members)</small></label>
                            <div class="tag-picker" id="tagPicker" data-search-url="<?= e(url('/api/users/search')) ?>">
                                <div class="tag-picker-chips" id="tagChips"></div>
                                <input type="text" class="form-control" id="tagSearch" placeholder="Type a name…" autocomplete="off"
                                       role="combobox" aria-expanded="false" aria-controls="tagSuggestions" aria-autocomplete="list">
                                <ul class="list-group tag-suggestions d-none" id="tagSuggestions" role="listbox"></ul>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-accent w-100 py-2" id="uploadButton">
                            <span class="spinner-border spinner-border-sm d-none" aria-hidden="true"></span>
                            <i class="bi bi-cloud-arrow-up"></i> Upload memory
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</section>
