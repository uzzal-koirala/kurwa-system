document.addEventListener('DOMContentLoaded', () => {
    const categoryButtons = document.querySelectorAll('.category-card');
    const gridContainer = document.querySelector('.caretaker-grid');
    const searchInput = document.getElementById('caretakerSearch');

    let caretakersData = [];

    // Search and Filter Logic
    function filterAndRender() {
        const searchTerm = searchInput?.value.toLowerCase().trim() || '';
        const filtered = caretakersData.filter(person =>
            person.full_name.toLowerCase().includes(searchTerm) ||
            person.specialization.toLowerCase().includes(searchTerm) ||
            (person.category && person.category.toLowerCase().includes(searchTerm))
        );
        renderGrid(filtered);
    }

    searchInput?.addEventListener('input', filterAndRender);

    // Initialize
    fetchCaretakers('All');

    // Category Click Listeners
    categoryButtons.forEach(btn => {
        btn.addEventListener('click', (e) => {
            categoryButtons.forEach(b => b.classList.remove('active'));
            const targetBtn = e.currentTarget;
            targetBtn.classList.add('active');

            const category = targetBtn.dataset.category;
            fetchCaretakers(category);
        });
    });

    // Fetch Caretakers from API
    function fetchCaretakers(category) {
        gridContainer.innerHTML = '<p style="text-align:center; grid-column: 1/-1;">Loading caretakers...</p>';

        fetch(`handlers/fetch_caretakers.php?category=${encodeURIComponent(category)}`)
            .then(async response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                const text = await response.text();
                try {
                    return JSON.parse(text);
                } catch (e) {
                    console.error("Failed to parse JSON. Raw response:", text);
                    throw new Error("Invalid JSON response from server");
                }
            })
            .then(res => {
                if (res && res.status === 'success') {
                    caretakersData = res.data || [];
                    filterAndRender(); // Apply search even after category fetch
                } else {
                    gridContainer.innerHTML = '<p style="text-align:center; color: red; grid-column: 1/-1;">Error: Check console for API response.</p>';
                }
            })
            .catch(error => {
                console.error("Fetch/Connection error:", error);
                gridContainer.innerHTML = `<p style="text-align:center; color: red; grid-column: 1/-1;">Connection error: ${error.message}</p>`;
            });
    }

    // Render Grid Cards
    function renderGrid(caretakers) {
        gridContainer.innerHTML = '';

        if (caretakers.length === 0) {
            gridContainer.innerHTML = '<p style="text-align:center; grid-column: 1/-1;">No caretakers match this category.</p>';
            return;
        }

        caretakers.forEach(person => {
            const card = document.createElement('div');
            card.className = 'caretaker-card';
            card.dataset.id = person.id;

            card.innerHTML = `
                <div class="caretaker-image">
                    <img src="${person.image_url || 'https://images.unsplash.com/photo-1594824476967-48c8b964273f?auto=format&fit=crop&w=600&q=80'}" alt="${person.full_name}">
                    <span class="ct-role-badge"><i class="ri-user-heart-line"></i> ${person.category || 'Caretaker'}</span>
                </div>
                <div class="caretaker-info">
                    <h3>${person.full_name}</h3>
                    <p>${person.specialization}</p>
                    <div class="caretaker-meta">
                        <span><i class="ri-star-fill"></i> ${person.rating}</span>
                        <span><i class="ri-briefcase-4-line"></i> ${person.experience_years} Yrs</span>
                        <span><i class="ri-user-heart-line"></i> ${person.patients_helped}+ patients</span>
                    </div>
                    <div class="caretaker-bottom">
                        <strong style="font-size: 14px;">Rs. ${parseFloat(person.price_per_day).toLocaleString()}/day</strong>
                        <button class="fav-icon" onclick="toggleFavorite(event, ${person.id})">
                            <i class="${person.is_favorite ? 'ri-heart-3-fill' : 'ri-heart-3-line'}"></i>
                        </button>
                    </div>
                </div>
            `;

            card.addEventListener('click', () => {
                window.location.href = `caretaker_profile.php?id=${person.id}`;
            });

            gridContainer.appendChild(card);
        });
    }

    window.toggleFavorite = function(event, caretakerId) {
        event.stopPropagation();
        const btn = event.currentTarget;
        const icon = btn.querySelector('i');
        
        const formData = new FormData();
        formData.append('caretaker_id', caretakerId);

        fetch('handlers/toggle_favorite.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                if (data.is_favorite) {
                    icon.className = 'ri-heart-3-fill';
                    btn.classList.add('active');
                } else {
                    icon.className = 'ri-heart-3-line';
                    btn.classList.remove('active');
                }
            }
        })
        .catch(err => console.error('Error toggling favorite:', err));
    };
});
