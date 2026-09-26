document.addEventListener('DOMContentLoaded', function () {
    'use strict';

    var commentForm = document.getElementById('commentForm');
    var commentList = document.getElementById('commentList');
    var commentCountBadge = document.querySelector('[data-comment-count]');
    var noCommentsMessage = document.getElementById('noCommentsMessage');

    function updateCommentCount(count) {
        if (commentCountBadge) { commentCountBadge.textContent = String(count); }
        if (noCommentsMessage) { noCommentsMessage.classList.toggle('d-none', count > 0); }
    }

    if (commentForm) {
        var commentTextarea = document.getElementById('commentText');

        commentForm.addEventListener('submit', function (submitEvent) {
            submitEvent.preventDefault();
            var submitButton = commentForm.querySelector('button[type=submit]');
            submitButton.disabled = true;

            window.Alz.postForm(commentForm.action, new FormData(commentForm))
                .then(function (result) {
                    if (!result.ok) {
                        window.Alz.toast(result.data.message || 'Your comment could not be saved.', 'danger');
                        return;
                    }
                    commentList.insertAdjacentHTML('beforeend', result.data.html);
                    var newComment = commentList.lastElementChild;
                    newComment.classList.add('is-new');
                    commentList.scrollTop = commentList.scrollHeight;

                    updateCommentCount(result.data.count);
                    commentForm.reset();
                    commentForm.classList.remove('was-submitted');
                    commentTextarea.classList.remove('is-valid', 'is-invalid');
                    commentTextarea.dispatchEvent(new Event('input'));
                })
                .catch(function () { window.Alz.toast('Network error. Please try again.', 'danger'); })
                .finally(function () { submitButton.disabled = false; });
        });

        commentTextarea.addEventListener('keydown', function (keyEvent) {
            if (keyEvent.key === 'Enter' && (keyEvent.ctrlKey || keyEvent.metaKey)) {
                keyEvent.preventDefault();
                commentForm.requestSubmit();
            }
        });
    }

    if (commentList) {
        commentList.addEventListener('submit', function (submitEvent) {
            var deleteForm = submitEvent.target.closest('.comment-delete-form');
            if (!deleteForm) { return; }
            submitEvent.preventDefault();

            window.Alz.postForm(deleteForm.action, new FormData(deleteForm)).then(function (result) {
                if (!result.ok) {
                    window.Alz.toast(result.data.message || 'The comment could not be deleted.', 'danger');
                    return;
                }
                deleteForm.closest('.comment').remove();
                updateCommentCount(result.data.count);
                window.Alz.toast('Comment deleted.');
            });
        });
    }

    var likeButton = document.querySelector('[data-like-url]');
    if (likeButton) {
        likeButton.addEventListener('click', function () {
            likeButton.disabled = true;
            window.Alz.postForm(likeButton.getAttribute('data-like-url'), new FormData())
                .then(function (result) {
                    if (!result.ok) {
                        window.Alz.toast(result.data.message || 'Could not update your like.', 'danger');
                        return;
                    }
                    likeButton.classList.toggle('is-liked', result.data.liked);
                    likeButton.setAttribute('aria-pressed', result.data.liked ? 'true' : 'false');
                    likeButton.querySelector('i').className = 'bi ' + (result.data.liked ? 'bi-heart-fill' : 'bi-heart');
                    likeButton.querySelector('[data-like-count]').textContent = String(result.data.count);
                    likeButton.classList.remove('pop');
                    void likeButton.offsetWidth;
                    likeButton.classList.add('pop');
                })
                .finally(function () { likeButton.disabled = false; });
        });
    }

    var shareMenu = document.querySelector('.share-menu');
    if (shareMenu) {
        var pageUrl = window.location.href.split('#')[0];
        var shareTitle = shareMenu.getAttribute('data-share-title');
        var shareText = '"' + shareTitle + '" - a memory on Alzikrayat';
        var intentUrls = {
            twitter: 'https://twitter.com/intent/tweet?text=' + encodeURIComponent(shareText) + '&url=' + encodeURIComponent(pageUrl),
            facebook: 'https://www.facebook.com/sharer/sharer.php?u=' + encodeURIComponent(pageUrl),
            whatsapp: 'https://wa.me/?text=' + encodeURIComponent(shareText + ' ' + pageUrl)
        };

        shareMenu.querySelectorAll('a[data-share]').forEach(function (link) {
            link.href = intentUrls[link.getAttribute('data-share')];
        });

        var nativeButton = shareMenu.querySelector('[data-share=native]');
        if (!navigator.share) {
            nativeButton.closest('li').remove();
        } else {
            nativeButton.addEventListener('click', function () {
                navigator.share({ title: shareTitle, text: shareText, url: pageUrl }).catch(function () { });
            });
        }

        shareMenu.querySelector('[data-share=copy]').addEventListener('click', function () {
            if (!navigator.clipboard) { window.prompt('Copy this link:', pageUrl); return; }
            navigator.clipboard.writeText(pageUrl).then(function () { window.Alz.toast('Link copied to clipboard.'); });
        });
    }

    document.addEventListener('keydown', function (keyEvent) {
        var tagName = (document.activeElement && document.activeElement.tagName) || '';
        if (/INPUT|TEXTAREA|SELECT/.test(tagName) || keyEvent.altKey || keyEvent.ctrlKey || keyEvent.metaKey) { return; }

        var targetLink = null;
        if (keyEvent.key === 'ArrowLeft') { targetLink = document.getElementById('previousPhotoLink'); }
        if (keyEvent.key === 'ArrowRight') { targetLink = document.getElementById('nextPhotoLink'); }
        if (targetLink) { window.location.href = targetLink.href; }
    });

    var lightboxImage = document.getElementById('lightboxImage');
    if (lightboxImage) {
        var zoomLevel = 1;
        var applyZoom = function () {
            lightboxImage.style.transform = 'scale(' + zoomLevel + ')';
            lightboxImage.style.cursor = zoomLevel > 1 ? 'zoom-out' : 'zoom-in';
        };

        lightboxImage.addEventListener('wheel', function (wheelEvent) {
            wheelEvent.preventDefault();
            zoomLevel = Math.min(4, Math.max(1, zoomLevel + (wheelEvent.deltaY < 0 ? 0.25 : -0.25)));
            applyZoom();
        }, { passive: false });

        lightboxImage.addEventListener('dblclick', function () {
            zoomLevel = zoomLevel > 1 ? 1 : 2;
            applyZoom();
        });

        document.getElementById('lightbox').addEventListener('hidden.bs.modal', function () {
            zoomLevel = 1;
            applyZoom();
        });
    }
});
