<?php
// Récupérer les données
$service_id = isset($service_id) ? $service_id : 0;
$stats = isset($stats) ? $stats : ['moyenne' => 0, 'total' => 0];
$reviews = isset($reviews) ? $reviews : [];
$has_rated = isset($has_rated) ? $has_rated : false;
?>

<div class="rating-box" data-service="<?php echo $service_id; ?>">
    <div class="rating-header">
        <div class="rating-stats">
            <div class="stars">
                <?php for($i = 1; $i <= 5; $i++): ?>
                    <i class="fas fa-star <?php echo $i <= round($stats['moyenne']) ? 'gold' : ''; ?>"></i>
                <?php endfor; ?>
            </div>
            <span class="rating-value"><?php echo $stats['moyenne']; ?>/5</span>
            <span class="rating-count">(<?php echo $stats['total']; ?> avis)</span>
        </div>
    </div>
    
    <?php if(!$has_rated): ?>
    <div class="rating-form">
        <div class="rating-stars-input">
            <?php for($i = 1; $i <= 5; $i++): ?>
                <i class="far fa-star star-rating" data-value="<?php echo $i; ?>"></i>
            <?php endfor; ?>
        </div>
        <textarea class="rating-comment" placeholder="Votre commentaire (optionnel)" rows="2"></textarea>
        <button class="btn-submit-rating">Envoyer mon avis</button>
        <div class="rating-message"></div>
    </div>
    <?php else: ?>
    <div class="rating-already">
        <i class="fas fa-check-circle"></i> Vous avez déjà donné votre avis
    </div>
    <?php endif; ?>
    
    <?php if(!empty($reviews)): ?>
    <div class="rating-reviews">
        <h4>Derniers avis</h4>
        <?php foreach($reviews as $review): ?>
        <div class="review-item">
            <div class="review-stars">
                <?php for($i = 1; $i <= 5; $i++): ?>
                    <i class="fas fa-star <?php echo $i <= $review['rating'] ? 'gold' : ''; ?>"></i>
                <?php endfor; ?>
            </div>
            <?php if(!empty($review['comment'])): ?>
            <div class="review-comment"><?php echo htmlspecialchars($review['comment']); ?></div>
            <?php endif; ?>
            <div class="review-date"><?php echo date('d/m/Y', strtotime($review['created_at'])); ?></div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>

<style>
.rating-box {
    background: #fff;
    border-radius: 16px;
    padding: 20px;
    margin-top: 20px;
    border: 1px solid #e2e8f0;
}
body.dark-mode .rating-box {
    background: #1e293b;
    border-color: #334155;
}
.rating-header {
    margin-bottom: 15px;
    padding-bottom: 15px;
    border-bottom: 1px solid #e2e8f0;
}
.rating-stats {
    display: flex;
    align-items: center;
    gap: 10px;
}
.stars i {
    font-size: 16px;
    color: #cbd5e1;
}
.stars i.gold {
    color: #fbbf24;
}
.rating-value {
    font-weight: bold;
    font-size: 18px;
}
.rating-count {
    font-size: 12px;
    color: #64748b;
}
.rating-stars-input {
    display: flex;
    gap: 8px;
    margin-bottom: 15px;
    justify-content: center;
}
.star-rating {
    font-size: 30px;
    cursor: pointer;
    color: #cbd5e1;
    transition: all 0.2s;
}
.star-rating:hover,
.star-rating.active {
    color: #fbbf24;
    transform: scale(1.1);
}
.rating-comment {
    width: 100%;
    padding: 10px;
    border: 2px solid #e2e8f0;
    border-radius: 12px;
    margin-bottom: 15px;
    resize: vertical;
}
.btn-submit-rating {
    background: linear-gradient(135deg, #052E16, #0a4a22);
    color: white;
    border: none;
    padding: 10px 20px;
    border-radius: 30px;
    cursor: pointer;
    width: 100%;
    font-weight: bold;
}
.btn-submit-rating:hover {
    opacity: 0.9;
    transform: translateY(-2px);
}
.rating-already {
    background: #dcfce7;
    padding: 10px;
    border-radius: 12px;
    text-align: center;
    color: #166534;
}
.rating-reviews {
    margin-top: 20px;
}
.rating-reviews h4 {
    font-size: 14px;
    margin-bottom: 10px;
}
.review-item {
    border-bottom: 1px solid #e2e8f0;
    padding: 10px 0;
}
.review-stars i {
    font-size: 12px;
    color: #cbd5e1;
}
.review-stars i.gold {
    color: #fbbf24;
}
.review-comment {
    font-size: 13px;
    margin: 5px 0;
    color: #334155;
}
.review-date {
    font-size: 11px;
    color: #94a3b8;
}
.rating-message {
    margin-top: 10px;
    font-size: 12px;
    text-align: center;
}
</style>