// Système de notation des services - VERSION CORRIGÉE
document.addEventListener('DOMContentLoaded', function() {
    initRatingSystem();
});

function initRatingSystem() {
    const ratingWidgets = document.querySelectorAll('.service-card');
    
    ratingWidgets.forEach(widget => {
        const serviceId = widget.dataset.serviceId;
        if (!serviceId) return;
        
        let selectedRating = 0;
        
        // Créer le widget rating s'il n'existe pas
        let ratingContainer = widget.querySelector('.rating-container');
        if (!ratingContainer) {
            ratingContainer = document.createElement('div');
            ratingContainer.className = 'rating-container';
            ratingContainer.innerHTML = `
                <div class="rating-display">
                    <div class="stars-static" id="stars-static-${serviceId}">
                        <i class="far fa-star"></i><i class="far fa-star"></i><i class="far fa-star"></i><i class="far fa-star"></i><i class="far fa-star"></i>
                    </div>
                    <span class="rating-average">0/5</span>
                    <span class="rating-count">(0 avis)</span>
                </div>
                <div class="rating-stars-container">
                    <div class="stars-input" id="stars-input-${serviceId}">
                        <input type="radio" name="rating-${serviceId}" value="5" id="star5-${serviceId}"><label for="star5-${serviceId}">★</label>
                        <input type="radio" name="rating-${serviceId}" value="4" id="star4-${serviceId}"><label for="star4-${serviceId}">★</label>
                        <input type="radio" name="rating-${serviceId}" value="3" id="star3-${serviceId}"><label for="star3-${serviceId}">★</label>
                        <input type="radio" name="rating-${serviceId}" value="2" id="star2-${serviceId}"><label for="star2-${serviceId}">★</label>
                        <input type="radio" name="rating-${serviceId}" value="1" id="star1-${serviceId}"><label for="star1-${serviceId}">★</label>
                    </div>
                    <textarea class="rating-comment" id="comment-${serviceId}" placeholder="Votre commentaire (optionnel)" rows="2" style="display:none;"></textarea>
                    <button class="rating-submit" data-service-id="${serviceId}" style="display:none;">⭐ Envoyer ma note</button>
                    <div class="rating-message"></div>
                </div>
                <div class="rating-list" id="rating-list-${serviceId}"></div>
            `;
            
            // Ajouter après le service-meta
            const serviceMeta = widget.querySelector('.service-meta');
            if (serviceMeta) {
                serviceMeta.insertAdjacentElement('afterend', ratingContainer);
            } else {
                widget.appendChild(ratingContainer);
            }
        }
        
        const stars = widget.querySelectorAll(`#stars-input-${serviceId} label`);
        const commentInput = widget.querySelector(`#comment-${serviceId}`);
        const submitBtn = widget.querySelector('.rating-submit');
        const messageDiv = widget.querySelector('.rating-message');
        
        if (!stars.length) return;
        
        // Charger les notes existantes
        loadRatingData(serviceId, stars, commentInput, submitBtn, messageDiv);
        
        // Clic sur étoile
        stars.forEach(star => {
            star.addEventListener('click', function(e) {
                e.preventDefault();
                const radio = this.previousElementSibling;
                if (radio) {
                    radio.checked = true;
                    selectedRating = parseInt(radio.value);
                } else {
                    // Alternative si la structure est différente
                    const input = document.querySelector(`#stars-input-${serviceId} input:checked`);
                    selectedRating = input ? parseInt(input.value) : 0;
                }
                
                highlightStars(serviceId, selectedRating);
                if (commentInput) commentInput.style.display = 'block';
                if (submitBtn) submitBtn.style.display = 'flex';
            });
        });
        
        // Écouter les changements des radios
        const radios = widget.querySelectorAll(`#stars-input-${serviceId} input`);
        radios.forEach(radio => {
            radio.addEventListener('change', function() {
                selectedRating = parseInt(this.value);
                highlightStars(serviceId, selectedRating);
                if (commentInput) commentInput.style.display = 'block';
                if (submitBtn) submitBtn.style.display = 'flex';
            });
        });
        
        // Envoi
        if (submitBtn) {
            submitBtn.addEventListener('click', function() {
                if (selectedRating === 0) {
                    showMessage(messageDiv, 'Choisissez une note (1 à 5 étoiles)', 'error');
                    return;
                }
                
                const comment = commentInput ? commentInput.value : '';
                
                submitBtn.disabled = true;
                submitBtn.textContent = 'Envoi...';
                
                const formData = new FormData();
                formData.append('service_id', serviceId);
                formData.append('rating', selectedRating);
                formData.append('comment', comment);
                
                fetch('index.php?action=add_rating', {
                    method: 'POST',
                    body: formData
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        showMessage(messageDiv, '✓ ' + data.message, 'success');
                        // Mettre à jour l'affichage
                        updateRatingDisplay(serviceId, data.average, data.count);
                        // Désactiver le formulaire
                        if (commentInput) commentInput.style.display = 'none';
                        if (submitBtn) submitBtn.style.display = 'none';
                        // Afficher un message de confirmation
                        setTimeout(() => {
                            if (messageDiv) messageDiv.innerHTML = '';
                        }, 3000);
                    } else {
                        showMessage(messageDiv, '✗ ' + (data.message || 'Erreur inconnue'), 'error');
                        submitBtn.disabled = false;
                        submitBtn.textContent = '⭐ Envoyer ma note';
                    }
                })
                .catch(err => {
                    console.error('Erreur:', err);
                    showMessage(messageDiv, '✗ Erreur de connexion au serveur', 'error');
                    submitBtn.disabled = false;
                    submitBtn.textContent = '⭐ Envoyer ma note';
                });
            });
        }
    });
}

function loadRatingData(serviceId, stars, commentInput, submitBtn, messageDiv) {
    fetch(`index.php?action=get_ratings&service_id=${serviceId}`)
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                // Mettre à jour l'affichage
                updateRatingDisplay(serviceId, data.average, data.count);
                
                // Si l'utilisateur a déjà noté
                if (data.user_rating) {
                    const radio = document.querySelector(`#stars-input-${serviceId} input[value="${data.user_rating}"]`);
                    if (radio) {
                        radio.checked = true;
                        highlightStars(serviceId, data.user_rating);
                    }
                    if (commentInput) commentInput.style.display = 'none';
                    if (submitBtn) submitBtn.style.display = 'none';
                    
                    // Afficher "vous avez noté"
                    if (messageDiv) {
                        const userMsg = document.createElement('div');
                        userMsg.className = 'user-rating-info';
                        userMsg.innerHTML = `<span>⭐ Vous avez noté ${data.user_rating}/5</span>`;
                        messageDiv.parentNode.insertBefore(userMsg, messageDiv);
                    }
                } else {
                    // Afficher le formulaire
                    if (commentInput) commentInput.style.display = 'none';
                    if (submitBtn) submitBtn.style.display = 'none';
                }
                
                // Charger la liste des avis
                loadRatingList(serviceId, data.ratings);
            }
        })
        .catch(err => console.error('Erreur chargement rating:', err));
}

function updateRatingDisplay(serviceId, average, count) {
    const starsContainer = document.getElementById(`stars-static-${serviceId}`);
    if (starsContainer) {
        const fullStars = Math.floor(average);
        const hasHalf = (average - fullStars) >= 0.5;
        let starsHtml = '';
        
        for (let i = 1; i <= 5; i++) {
            if (i <= fullStars) {
                starsHtml += '<i class="fas fa-star"></i>';
            } else if (i === fullStars + 1 && hasHalf) {
                starsHtml += '<i class="fas fa-star-half-alt"></i>';
            } else {
                starsHtml += '<i class="far fa-star"></i>';
            }
        }
        starsContainer.innerHTML = starsHtml;
    }
    
    const avgSpan = document.querySelector(`#rating-${serviceId} .rating-average`);
    const countSpan = document.querySelector(`#rating-${serviceId} .rating-count`);
    if (avgSpan) avgSpan.textContent = (average || 0) + '/5';
    if (countSpan) countSpan.textContent = '(' + (count || 0) + ' avis)';
}

function highlightStars(serviceId, rating) {
    const labels = document.querySelectorAll(`#stars-input-${serviceId} label`);
    labels.forEach((label, index) => {
        const starValue = 5 - index;
        if (starValue <= rating) {
            label.style.color = '#fbbf24';
        } else {
            label.style.color = '#cbd5e1';
        }
    });
}

function loadRatingList(serviceId, ratings) {
    const listContainer = document.getElementById(`rating-list-${serviceId}`);
    if (!listContainer) return;
    
    if (ratings && ratings.length > 0) {
        listContainer.innerHTML = ratings.map(r => `
            <div class="rating-item">
                <div>
                    <span class="rating-user">${escapeHtml(r.user_name || 'Utilisateur')}</span>
                    <span class="rating-stars-small">
                        ${generateSmallStars(r.rating)}
                    </span>
                    <span class="rating-date">${formatDateShort(r.created_at)}</span>
                </div>
                ${r.comment ? `<div class="rating-comment-text">${escapeHtml(r.comment)}</div>` : ''}
            </div>
        `).join('');
        listContainer.style.display = 'block';
    } else {
        listContainer.innerHTML = '<div class="loading-rating">Aucun avis pour le moment</div>';
        listContainer.style.display = 'block';
    }
}

function generateSmallStars(rating) {
    let stars = '';
    for (let i = 1; i <= 5; i++) {
        if (i <= rating) {
            stars += '<i class="fas fa-star" style="color:#fbbf24; font-size:10px;"></i>';
        } else {
            stars += '<i class="far fa-star" style="color:#cbd5e1; font-size:10px;"></i>';
        }
    }
    return stars;
}

function formatDateShort(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('fr-FR');
}

function escapeHtml(str) {
    if (!str) return '';
    return str
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
}

function showMessage(div, text, type) {
    if (!div) return;
    div.innerHTML = text;
    div.style.padding = '10px';
    div.style.borderRadius = '10px';
    div.style.marginTop = '10px';
    div.style.textAlign = 'center';
    div.style.background = type === 'success' ? '#dcfce7' : '#fee2e2';
    div.style.color = type === 'success' ? '#166534' : '#dc2626';
    div.style.fontSize = '13px';
    setTimeout(() => {
        if (div) div.innerHTML = '';
    }, 4000);
}