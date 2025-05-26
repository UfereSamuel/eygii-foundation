    </main>
    
    <!-- Footer -->
    <footer style="background: var(--gradient-primary); color: white;">
        <div class="container">
            <!-- Main Footer Content -->
            <div class="row py-5">
                <div class="col-lg-4 mb-4 mb-lg-0">
                    <div class="d-flex align-items-center mb-4">
                        <div class="logo-wrapper me-3" style="width: 60px; height: 60px;">
                            <img src="assets/images/logos/eygii_logo.png" alt="EYGII Logo" class="logo-img">
                        </div>
                        <div>
                            <h4 class="text-white mb-1">EYGII</h4>
                            <p class="mb-0 text-white-50">Eloquent Youth & Global Integrity</p>
                        </div>
                    </div>
                    <p class="text-accent mb-3 fst-italic">"Reviving world integrity and moral values"</p>
                    <p class="text-white-50 mb-4">
                        Empowering young people to become agents of positive change in their communities 
                        while promoting integrity, moral values, and sustainable development.
                    </p>
                    
                    <!-- Social Media Links -->
                    <div>
                        <h6 class="text-white mb-3">Connect With Us</h6>
                        <div class="d-flex gap-3">
                            <a href="#" class="social-link" title="Facebook">
                                <i class="fab fa-facebook-f"></i>
                            </a>
                            <a href="#" class="social-link" title="Twitter">
                                <i class="fab fa-twitter"></i>
                            </a>
                            <a href="#" class="social-link" title="Instagram">
                                <i class="fab fa-instagram"></i>
                            </a>
                            <a href="#" class="social-link" title="LinkedIn">
                                <i class="fab fa-linkedin-in"></i>
                            </a>
                            <a href="#" class="social-link" title="YouTube">
                                <i class="fab fa-youtube"></i>
                            </a>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-3 col-md-6 mb-4 mb-lg-0">
                    <h6 class="text-white mb-4">
                        <i class="fas fa-link me-2"></i>Quick Links
                    </h6>
                    <ul class="list-unstyled footer-links">
                        <li><a href="index.php">Home</a></li>
                        <li><a href="about.php">About Us</a></li>
                        <li><a href="programs.php">Our Programs</a></li>
                        <li><a href="get-involved.php">Get Involved</a></li>
                        <li><a href="news.php">News & Updates</a></li>
                        <li><a href="contact.php">Contact Us</a></li>
                        <li><a href="donate.php">Donate</a></li>
                    </ul>
                </div>
                
                <div class="col-lg-3 col-md-6 mb-4 mb-lg-0">
                    <h6 class="text-white mb-4">
                        <i class="fas fa-map-marker-alt me-2"></i>Contact Info
                    </h6>
                    <div class="contact-info">
                        <div class="contact-item mb-3">
                            <i class="fas fa-map-marker-alt me-3"></i>
                            <span>K19, Joke Plaza, Bodija, Ibadan</span>
                        </div>
                        <div class="contact-item mb-3">
                            <i class="fas fa-envelope me-3"></i>
                            <a href="mailto:eygii2017@gmail.com">eygii2017@gmail.com</a>
                        </div>
                        <div class="contact-item mb-3">
                            <i class="fab fa-whatsapp me-3"></i>
                            <a href="https://wa.me/2348136613616">+234 813 661 3616</a>
                        </div>
                        <div class="contact-item">
                            <i class="fab fa-whatsapp me-3"></i>
                            <a href="https://wa.me/2348054824514">+234 805 482 4514</a>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-2 col-md-6">
                    <h6 class="text-white mb-4">
                        <i class="fas fa-heart me-2"></i>Support
                    </h6>
                    <div class="support-section">
                        <p class="text-white-50 mb-3 small">Help us make a difference in young lives</p>
                        <a href="donate.php" class="btn btn-warning btn-sm mb-3 w-100">
                            <i class="fas fa-heart me-2"></i>Donate Now
                        </a>
                        <a href="get-involved.php" class="btn btn-outline-light btn-sm w-100">
                            <i class="fas fa-hands-helping me-2"></i>Volunteer
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- Footer Bottom -->
            <div class="border-top border-white-25 py-4">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <p class="mb-0 text-white-50">
                            &copy; <?php echo date('Y'); ?> Eloquent Youth & Global Integrity. All rights reserved.
                        </p>
                    </div>
                    <div class="col-md-6 text-md-end">
                        <p class="mb-0 text-white-50">
                            <i class="fas fa-heart text-accent me-2"></i>
                            Built with passion for positive change
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </footer>
    
    <!-- Back to Top Button -->
    <button class="back-to-top" id="backToTop" onclick="scrollToTop()">
        <i class="fas fa-arrow-up"></i>
    </button>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JavaScript -->
    <script>
        // Back to top functionality
        window.addEventListener('scroll', function() {
            const backToTop = document.getElementById('backToTop');
            if (window.scrollY > 300) {
                backToTop.classList.add('show');
            } else {
                backToTop.classList.remove('show');
            }
        });
        
        function scrollToTop() {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        }
        
        // Smooth scrolling for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });
    </script>
    
    <style>
        /* Footer Styles */
        .footer-links li {
            margin-bottom: 0.75rem;
        }
        
        .footer-links a {
            color: rgba(255, 255, 255, 0.7);
            text-decoration: none;
            transition: all var(--transition-fast);
            font-weight: 500;
        }
        
        .footer-links a:hover {
            color: var(--accent-400);
            transform: translateX(4px);
        }
        
        .contact-item {
            display: flex;
            align-items: flex-start;
            color: rgba(255, 255, 255, 0.7);
            font-weight: 500;
        }
        
        .contact-item i {
            color: var(--accent-400);
            margin-top: 2px;
            width: 20px;
        }
        
        .contact-item a {
            color: rgba(255, 255, 255, 0.7);
            text-decoration: none;
            transition: all var(--transition-fast);
        }
        
        .contact-item a:hover {
            color: var(--accent-400);
        }
        
        .social-link {
            width: 45px;
            height: 45px;
            background: rgba(255, 255, 255, 0.1);
            color: white;
            border-radius: var(--radius-full);
            display: flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            transition: all var(--transition-normal);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .social-link:hover {
            background: var(--accent-500);
            color: white;
            transform: translateY(-3px);
            box-shadow: var(--shadow-lg);
        }
        
        .border-white-25 {
            border-color: rgba(255, 255, 255, 0.25) !important;
        }
        
        .text-white-50 {
            color: rgba(255, 255, 255, 0.7) !important;
        }
        
        /* Back to Top Button */
        .back-to-top {
            position: fixed;
            bottom: 30px;
            right: 30px;
            width: 50px;
            height: 50px;
            background: var(--gradient-accent);
            color: white;
            border: none;
            border-radius: var(--radius-full);
            display: none;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            cursor: pointer;
            transition: all var(--transition-normal);
            z-index: 1000;
            box-shadow: var(--shadow-lg);
        }
        
        .back-to-top:hover {
            background: var(--gradient-primary);
            transform: translateY(-3px);
            box-shadow: var(--shadow-xl);
        }
        
        .back-to-top.show {
            display: flex;
        }
        
        /* Support Section */
        .support-section .btn {
            font-weight: 600;
            border-radius: var(--radius-full);
            transition: all var(--transition-normal);
        }
        
        .support-section .btn:hover {
            transform: translateY(-2px);
        }
    </style>
</body>
</html> 