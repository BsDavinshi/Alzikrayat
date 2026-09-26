<?php
?>
<section class="container py-5">
    <div class="row g-5 align-items-start">
        <div class="col-lg-7">
            <span class="eyebrow">About Us</span>
            <h1 class="display-font display-5 mt-2">The story behind Alzikrayat</h1>
            <p class="lead mt-3">
                <em>Alzikrayat</em> (الذكريات) means <strong>“memories”</strong> in Arabic. The project started from a simple
                observation: our best photos are scattered across phones and chat groups, and the stories behind them
                fade quickly. Alzikrayat gives every photo a home, a title, a story and a place for friends to gather around it.
            </p>
            <p>
                It was built as <strong>Course Project 1</strong> of the <em>Advanced Web Technologies</em> course at the
                College of Computer Science and Information Technology, Sudan University of Science and Technology.
                The goal was to write a complete web application <strong>without any framework</strong>: a hand-written
                Model-View-Controller engine, a regular-expression router, raw parameterised SQL and a three-tier deployment.
            </p>

            <h2 class="h4 mt-5 mb-3">How it is built</h2>
            <div class="row g-3">
                <div class="col-sm-4">
                    <div class="tier-card h-100">
                        <span class="tier-label">Presentation tier</span>
                        <p class="small mb-0">PHP view templates, Bootstrap 5, vanilla JavaScript (validation, filters, AJAX).</p>
                    </div>
                </div>
                <div class="col-sm-4">
                    <div class="tier-card h-100">
                        <span class="tier-label">Application tier</span>
                        <p class="small mb-0">Front controller, regex Router, Controllers, Validator, Session &amp; Auth.</p>
                    </div>
                </div>
                <div class="col-sm-4">
                    <div class="tier-card h-100">
                        <span class="tier-label">Data tier</span>
                        <p class="small mb-0">MySQL with normalised tables, foreign keys with cascades, PDO singleton.</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card about-card">
                <div class="card-body p-4">
                    <h2 class="h5 mb-3">Alzikrayat in numbers</h2>
                    <ul class="list-unstyled about-numbers mb-4">
                        <li><span><?= e(number_format($statistics['members'])) ?></span> members</li>
                        <li><span><?= e(number_format($statistics['photos'])) ?></span> memories shared</li>
                        <li><span><?= e(number_format($statistics['comments'])) ?></span> comments written</li>
                        <li><span><?= e(number_format($statistics['albums'])) ?></span> albums curated</li>
                    </ul>
                    <h2 class="h6">Our values</h2>
                    <ul class="small mb-0">
                        <li><strong>Privacy first</strong> — bcrypt-hashed passwords, CSRF tokens, escaped output.</li>
                        <li><strong>Ownership</strong> — only you can delete your photos.</li>
                        <li><strong>Accessibility</strong> — responsive on phones, tablets and desktops, with a dark mode.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</section>
