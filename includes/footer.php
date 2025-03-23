<?php
$categories = getCategories($conn);
?>
<footer class="main-footer">
    <div class="footer-top">
        <div class="container">
            <div class="footer-grid">
                <div class="footer-column">
                    <h3>About Us</h3>
                    <p>Your trusted online shopping destination for quality products at competitive prices.</p>
                    <div class="social-links">
                        <a href="#"><i class="fab fa-facebook"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                        <a href="#"><i class="fab fa-pinterest"></i></a>
                    </div>
                </div>

                <div class="footer-column">
                    <h3>Categories</h3>
                    <ul>
                        <?php
                        foreach (array_slice($categories, 0, 6) as $category) {
                            echo '<li><a href="category.php?id=' . $category['id'] . '">' . $category['name'] . '</a></li>';
                        }
                        ?>
                    </ul>
                </div>

                <div class="footer-column">
                    <h3>Customer Service</h3>
                    <ul>
                        <li><a href="contact.php">Contact Us</a></li>
                        <li><a href="shipping.php">Shipping Information</a></li>
                        <li><a href="returns.php">Returns & Exchanges</a></li>
                        <li><a href="faq.php">FAQ</a></li>
                        <li><a href="size-guide.php">Size Guide</a></li>
                    </ul>
                </div>

                <div class="footer-column">
                    <h3>My Account</h3>
                    <ul>
                        <li><a href="account.php">My Profile</a></li>
                        <li><a href="orders.php">Order History</a></li>
                        <li><a href="wishlist.php">Wishlist</a></li>
                        <li><a href="newsletter.php">Newsletter</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <div class="footer-bottom">
        <div class="container">
            <div class="footer-info">
                <div class="payment-methods">
                    <img src="assets/images/payment-methods.png" alt="Accepted Payment Methods">
                </div>
                <div class="copyright">
                    <p>&copy; <?php echo date('Y'); ?> MOHD SAHIL. All rights reserved.</p>
                </div>
            </div>
        </div>
    </div>
</footer>

<!-- Newsletter Popup -->
<div id="newsletter-popup" class="popup">
    <div class="popup-content">
        <button class="close-popup"><i class="fas fa-times"></i></button>
        <h3>Subscribe to Our Newsletter</h3>
        <p>Get the latest updates on new products and upcoming sales</p>
        <form action="subscribe.php" method="POST" class="newsletter-form">
            <input type="email" name="email" placeholder="Your email address" required>
            <button type="submit">Subscribe</button>
        </form>
    </div>
</div>