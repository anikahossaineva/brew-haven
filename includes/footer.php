<footer class="text-white py-5 mt-5" style="background:#241a15">
  <div class="container">
    <div class="row g-4 align-items-start">
      <div class="col-lg-5"><h2 class="h4">Brew Haven Coffee Shop</h2><p class="mb-0">Fresh coffee, good food and a little room to breathe.</p></div>
      <div class="col-lg-3"><h3 class="h6">Explore</h3><div class="d-flex flex-column gap-2"><a class="link-light" href="about.php">Our story</a><a class="link-light" href="menu.php">Menu</a><a class="link-light" href="reservation.php">Reservations</a><a class="link-light" href="contact.php">Contact</a></div></div>
      <div class="col-lg-4"><h3 class="h6">A good note, now and then</h3><form id="newsletter-form" class="d-flex gap-2"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><label class="visually-hidden" for="newsletter-email">Email address</label><input class="form-control" id="newsletter-email" name="email" type="email" maxlength="190" placeholder="Your email address" required><button class="btn btn-light" type="submit">Subscribe</button></form><p id="newsletter-result" class="small mt-2" role="status"></p></div>
    </div>
    <hr class="border-light opacity-25 mt-4"><div class="d-flex flex-wrap gap-3 justify-content-between small"><span>© <?= date('Y') ?> Brew Haven. All rights reserved.</span><span><a class="link-light" href="privacy.php">Privacy</a> · <a class="link-light" href="terms.php">Terms</a></span></div>
  </div>
</footer>
