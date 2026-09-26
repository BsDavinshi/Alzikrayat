(function () {
    'use strict';

    var picker = document.getElementById('tagPicker');
    if (!picker) { return; }

    var searchInput = document.getElementById('tagSearch');
    var suggestionList = document.getElementById('tagSuggestions');
    var chipContainer = document.getElementById('tagChips');
    var searchUrl = picker.getAttribute('data-search-url');
    var debounceTimer = null;
    var activeIndex = -1;
    var currentSuggestions = [];

    function selectedIds() {
        return Array.prototype.map.call(chipContainer.querySelectorAll('input[name="tagged_users[]"]'), function (hiddenInput) {
            return parseInt(hiddenInput.value, 10);
        });
    }

    function closeSuggestions() {
        suggestionList.classList.add('d-none');
        searchInput.setAttribute('aria-expanded', 'false');
        activeIndex = -1;
    }

    function buildAvatar(user) {
        var avatar = document.createElement('span');
        avatar.className = 'avatar avatar-xs';
        avatar.style.background = user.color;
        avatar.textContent = user.initials;
        return avatar;
    }

    function renderSuggestions(users) {
        var alreadyTagged = selectedIds();
        currentSuggestions = users.filter(function (user) { return alreadyTagged.indexOf(user.id) === -1; });
        suggestionList.innerHTML = '';

        if (!currentSuggestions.length) {
            var emptyItem = document.createElement('li');
            emptyItem.className = 'list-group-item small text-body-secondary';
            emptyItem.textContent = 'No members found';
            suggestionList.appendChild(emptyItem);
        }

        currentSuggestions.forEach(function (user, index) {
            var item = document.createElement('li');
            item.className = 'list-group-item';
            item.setAttribute('role', 'option');
            item.id = 'tag-option-' + user.id;
            item.appendChild(buildAvatar(user));
            item.appendChild(document.createTextNode(user.name));
            item.addEventListener('mousedown', function (mouseEvent) {
                mouseEvent.preventDefault();
                addTag(user);
            });
            item.addEventListener('mouseenter', function () { highlight(index); });
            suggestionList.appendChild(item);
        });

        suggestionList.classList.remove('d-none');
        searchInput.setAttribute('aria-expanded', 'true');
        activeIndex = -1;
    }

    function highlight(index) {
        var items = suggestionList.querySelectorAll('[role=option]');
        items.forEach(function (item, itemIndex) { item.classList.toggle('active', itemIndex === index); });
        activeIndex = index;
        if (items[index]) { searchInput.setAttribute('aria-activedescendant', items[index].id); }
    }

    function addTag(user) {
        if (selectedIds().indexOf(user.id) !== -1) { return; }

        var chip = document.createElement('span');
        chip.className = 'tag-chip';
        chip.appendChild(buildAvatar(user));
        chip.appendChild(document.createTextNode(' ' + user.name));

        var hiddenInput = document.createElement('input');
        hiddenInput.type = 'hidden';
        hiddenInput.name = 'tagged_users[]';
        hiddenInput.value = String(user.id);
        chip.appendChild(hiddenInput);

        var removeButton = document.createElement('button');
        removeButton.type = 'button';
        removeButton.className = 'tag-chip-remove';
        removeButton.setAttribute('aria-label', 'Remove ' + user.name);
        removeButton.innerHTML = '<i class="bi bi-x"></i>';
        removeButton.addEventListener('click', function () { chip.remove(); });
        chip.appendChild(removeButton);

        chipContainer.appendChild(chip);
        searchInput.value = '';
        closeSuggestions();
        searchInput.focus();
    }

    function search(term) {
        fetch(searchUrl + '?q=' + encodeURIComponent(term), {
            credentials: 'same-origin',
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        })
            .then(function (response) { return response.ok ? response.json() : { users: [] }; })
            .then(function (payload) {
                if (searchInput.value.trim() === term) { renderSuggestions(payload.users || []); }
            })
            .catch(closeSuggestions);
    }

    searchInput.addEventListener('input', function () {
        var term = searchInput.value.trim();
        clearTimeout(debounceTimer);
        if (!term) { closeSuggestions(); return; }
        debounceTimer = setTimeout(function () { search(term); }, 220);
    });

    searchInput.addEventListener('keydown', function (keyEvent) {
        var optionCount = suggestionList.querySelectorAll('[role=option]').length;

        if (keyEvent.key === 'ArrowDown' && optionCount) {
            keyEvent.preventDefault();
            highlight((activeIndex + 1) % optionCount);
        } else if (keyEvent.key === 'ArrowUp' && optionCount) {
            keyEvent.preventDefault();
            highlight((activeIndex - 1 + optionCount) % optionCount);
        } else if (keyEvent.key === 'Enter') {
            keyEvent.preventDefault();
            if (activeIndex >= 0 && currentSuggestions[activeIndex]) { addTag(currentSuggestions[activeIndex]); }
        } else if (keyEvent.key === 'Escape') {
            closeSuggestions();
        }
    });

    searchInput.addEventListener('blur', function () { setTimeout(closeSuggestions, 120); });
})();
