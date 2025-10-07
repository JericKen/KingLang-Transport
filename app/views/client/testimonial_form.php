<?php 
require_client_auth(); 
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Share Your Experience</title>
    <link rel="icon" href="../../../public/images/main-logo-icon.png" type="">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../public/css/testimonial_form.css">
</head>
<body>
    <?php include_once __DIR__ . "/../assets/sidebar.php"; ?> 

    <div class="content collapsed" id="content">
        <div class="container-fluid py-3 px-3 px-xl-4">
            <div class="container-fluid d-flex justify-content-between align-items-center flex-wrap p-0 m-0 mb-3">
                <div class="p-0">
                    <h3 class="mb-1"><i class="bi bi-chat-square-quote me-2 text-success"></i>Share Your Experience</h3>
                    <p class="text-muted mb-0 small">Help other travelers by sharing your experience with KingLang Transport</p>
                </div>
                <?php include_once __DIR__ . "/../assets/user_profile.php"; ?>
            </div>
            <hr class="my-3">

            <?php if (!empty($eligibleBookings)): ?>
                <section class="testimonial-form-section">
                    <h2 class="mb-3">Submit a Review</h2>
                    <form id="testimonialForm" class="testimonial-form">
                        <div class="form-group">
                            <label for="booking_id">Select Trip</label>
                            <select id="booking_id" name="booking_id" required>
                                <option value="">Select a completed trip to review</option>
                                <?php foreach ($eligibleBookings as $booking): ?>
                                    <option value="<?php echo $booking['booking_id']; ?>">
                                        <?php echo htmlspecialchars($booking['destination']); ?> 
                                        (<?php echo convertToManilaTime($booking['date_of_tour'], 'M d, Y'); ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="rating">Rating</label>
                            <div class="rating-input">
                                <input type="radio" id="star5" name="rating" value="5" required>
                                <label for="star5" class="star"><i class="fas fa-star"></i></label>
                                <input type="radio" id="star4" name="rating" value="4" required>
                                <label for="star4" class="star"><i class="fas fa-star"></i></label>
                                <input type="radio" id="star3" name="rating" value="3" required>
                                <label for="star3" class="star"><i class="fas fa-star"></i></label>
                                <input type="radio" id="star2" name="rating" value="2" required>
                                <label for="star2" class="star"><i class="fas fa-star"></i></label>
                                <input type="radio" id="star1" name="rating" value="1" required>
                                <label for="star1" class="star"><i class="fas fa-star"></i></label>
                            </div>
                            <span class="rating-text"></span>
                        </div>

                        <div class="form-group">
                            <label for="title">Review Title</label>
                            <input type="text" id="title" name="title" maxlength="100" required 
                                   placeholder="Give your review a title">
                            <small class="char-count">0 / 100</small>
                        </div>

                        <div class="form-group">
                            <label for="content">Your Review</label>
                            <textarea id="contentText" name="content" maxlength="1000" rows="5" required 
                                      placeholder="Share your experience with KingLang Transport..."></textarea>
                            <small class="char-count">0 / 1000</small>
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-paper-plane"></i> Submit Review
                        </button>
                    </form>
                </section>
                <?php else: ?>
                <section class="no-eligible-trips">
                    <div class="no-trips-message">
                        <i class="fas fa-calendar-times"></i>
                        <h3 class="mb-2">No Completed Trips</h3>
                        <p class="mb-3">You don't have any completed trips to review yet. Complete a trip with us first!</p>
                        <a href="/home/book" class="btn btn-primary">Book a Trip</a>
                    </div>
                </section>
                <?php endif; ?>

                <!-- User's Previous Testimonials -->
                <?php if (!empty($userTestimonials)): ?>
                <section class="user-testimonials-section">
                    <h2 class="mb-3">Your Reviews</h2>
                    <div class="testimonials-grid">
                        <?php foreach ($userTestimonials as $testimonial): ?>
                        <div class="testimonial-card">
                            <div class="testimonial-header">
                                <div class="rating">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <i class="fas fa-star <?php echo $i <= $testimonial['rating'] ? 'filled' : 'empty'; ?>"></i>
                                    <?php endfor; ?>
                                </div>
                                <div class="status">
                                    <?php if ($testimonial['is_approved']): ?>
                                        <span class="status-approved"><i class="fas fa-check-circle"></i> Approved</span>
                                    <?php else: ?>
                                        <span class="status-pending"><i class="fas fa-clock"></i> Pending Approval</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <h3><?php echo htmlspecialchars($testimonial['title']); ?></h3>
                            <p class="trip-info">
                                <i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($testimonial['destination']); ?>
                                <span class="date"><?php echo convertToManilaTime($testimonial['date_of_tour'], 'M d, Y'); ?></span>
                            </p>
                            <p class="testimonial-content"><?php echo htmlspecialchars($testimonial['content']); ?></p>
                            <div class="testimonial-footer">
                                <small class="submission-date">
                                    Submitted on <?php echo convertToManilaTime($testimonial['created_at'], 'M d, Y'); ?>
                                </small>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </section>
                <?php endif; ?>
        </div>
    </div>

    <!-- Success/Error Modal -->
    <div id="messageModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <div class="modal-body">
                <div id="modalIcon"></div>
                <h3 id="modalTitle"></h3>
                <p id="modalMessage"></p>
            </div>
        </div>
    </div>

    <?php include_once __DIR__ . '/chat_widget_core.php'; ?>

    <script>
        // Set user login status for chat widget
        var userLoggedIn = <?php echo isset($_SESSION['user_id']) ? 'true' : 'false'; ?>;
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js" integrity="sha384-ndDqU0Gzau9qJ1lfW4pNLlhNTkCfHzAVBReH9diLvGRem5+R9g2FzA8ZGN954O5Q" crossorigin="anonymous"></script>
    <script src="../public/js/testimonial_form.js"></script>
    <script src="/../../../public/js/assets/sidebar.js"></script>
</body>
</html>