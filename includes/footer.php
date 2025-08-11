<?php
/**
 * Render common modals used across the site
 */
function renderCommonModals() {
    ?>
    <!-- Login Modal -->
    <div class="modal fade" id="loginModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Login to SocialHub</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="username" class="form-label">Username or Email</label>
                            <input type="text" class="form-control" name="username" required>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" name="password" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="login" class="btn btn-primary">Login</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Logout Confirmation Modal -->
    <div class="modal fade" id="logoutModal" tabindex="-1">
        <div class="modal-dialog modal-sm">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirm Logout</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    Are you sure you want to logout?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <a href="index.php?logout=1" class="btn btn-primary">Logout</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Search Results Modal -->
    <div class="modal fade" id="searchModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Search Results</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="searchResults">
                    <!-- Search results will be loaded here -->
                </div>
            </div>
        </div>
    </div>
    <?php
}

/**
 * Render the website footer
 * @param array $additionalJS - Array of additional JS files to include
 */
function renderFooter($additionalJS = []) {
    ?>
    </div> <!-- Close main-content -->

    <!-- Footer -->
    <footer class="bg-body-tertiary border-top py-4 mt-5">
        <div class="container">
            <div class="row">
                <div class="col-md-4">
                    <h5><i class="fas fa-share-alt"></i> SocialHub</h5>
                    <p class="text-body-secondary">Connect, share, and explore with friends from around the world.</p>
                    <div class="social-links">
                        <a href="#" class="text-body-secondary me-3"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="text-body-secondary me-3"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="text-body-secondary me-3"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="text-body-secondary"><i class="fab fa-linkedin-in"></i></a>
                    </div>
                </div>
                <div class="col-md-2">
                    <h6>Company</h6>
                    <ul class="list-unstyled">
                        <li><a href="#" class="text-body-secondary text-decoration-none">About Us</a></li>
                        <li><a href="#" class="text-body-secondary text-decoration-none">Careers</a></li>
                        <li><a href="#" class="text-body-secondary text-decoration-none">Contact</a></li>
                        <li><a href="#" class="text-body-secondary text-decoration-none">Blog</a></li>
                    </ul>
                </div>
                <div class="col-md-2">
                    <h6>Community</h6>
                    <ul class="list-unstyled">
                        <li><a href="#" class="text-body-secondary text-decoration-none">Guidelines</a></li>
                        <li><a href="#" class="text-body-secondary text-decoration-none">Help Center</a></li>
                        <li><a href="search.php" class="text-body-secondary text-decoration-none">Discover</a></li>
                        <li><a href="#" class="text-body-secondary text-decoration-none">Features</a></li>
                    </ul>
                </div>
                <div class="col-md-2">
                    <h6>Legal</h6>
                    <ul class="list-unstyled">
                        <li><a href="#" class="text-body-secondary text-decoration-none">Privacy Policy</a></li>
                        <li><a href="#" class="text-body-secondary text-decoration-none">Terms of Service</a></li>
                        <li><a href="#" class="text-body-secondary text-decoration-none">Cookie Policy</a></li>
                        <li><a href="#" class="text-body-secondary text-decoration-none">DMCA</a></li>
                    </ul>
                </div>
                <div class="col-md-2">
                    <h6>Support</h6>
                    <ul class="list-unstyled">
                        <li><a href="#" class="text-body-secondary text-decoration-none">Help</a></li>
                        <li><a href="#" class="text-body-secondary text-decoration-none">Report Bug</a></li>
                        <li><a href="#" class="text-body-secondary text-decoration-none">Feedback</a></li>
                        <li><a href="#" class="text-body-secondary text-decoration-none">Status</a></li>
                    </ul>
                </div>
            </div>
            <hr class="my-4">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <p class="text-body-secondary mb-0">&copy; <?php echo date('Y'); ?> SocialHub. All rights reserved.</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <div class="btn-group btn-group-sm" role="group">
                        <button type="button" class="btn btn-outline-secondary" onclick="toggleTheme()">
                            <i class="fas fa-palette"></i> Theme
                        </button>
                        <a href="#" class="btn btn-outline-secondary" onclick="window.scrollTo({top: 0, behavior: 'smooth'})">
                            <i class="fas fa-arrow-up"></i> Top
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <!-- Alert Container -->
    <div id="alertContainer" class="position-fixed top-0 end-0 p-3" style="z-index: 1100;"></div>

    <!-- Core JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/main.js"></script>

    <!-- Additional JavaScript -->
    <?php foreach ($additionalJS as $js): ?>
        <script src="<?php echo htmlspecialchars($js); ?>"></script>
    <?php endforeach; ?>

    <!-- TODO: Remove this - Performance and Analytics (Optional) -->
    <script>
        // Performance monitoring
        if ('performance' in window) {
            window.addEventListener('load', function() {
                setTimeout(function() {
                    const perfData = performance.getEntriesByType('navigation')[0];
                    if (perfData && console) {
                        console.log('Page load time:', Math.round(perfData.loadEventEnd - perfData.fetchStart), 'ms');
                    }
                }, 0);
            });
        }
    </script>
    </body>
    </html>
    <?php
}

/**
 * Quick helper function to include both header and handle session messages
 */
function startPage($pageTitle = 'SocialHub', $currentPage = 'home', $additionalCSS = [], $additionalJS = []) {
    renderHeader($pageTitle, $currentPage, $additionalCSS, $additionalJS);
    echo '<div class="container-fluid mt-5 pt-4">';
    renderSessionMessages();
}

/**
 * Quick helper function to end page with footer and modals
 */
function endPage($additionalJS = []) {
    echo '</div>'; // Close container-fluid
    renderCommonModals();
    renderFooter($additionalJS);
}
?>