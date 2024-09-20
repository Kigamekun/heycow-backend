<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <title>HeyCow</title>
  <meta name="description" content="">
  <meta name="keywords" content="">

  <!-- Favicons -->
  <link href="assets_lp/img/favicomatic/favicon-1.png" rel="icon">
  <link href="assets_lp/img/favicomatic/favicon-2.png" rel="icon">
  <link href="assets_lp/img/favicomatic/favicon-3.png" rel="icon">
  <link href="assets_lp/img/favicomatic/favicon-4.png" rel="icon">
  <link href="assets_lp/img/favicomatic/favicon-5.png" rel="icon">
  <link href="assets_lp/img/favicomatic/favicon-6.png" rel="icon">
  <link href="assets_lp/img/favicomatic/apple-touch-icon-1.png" rel="apple-touch-icon">
  <link href="assets_lp/img/favicomatic/apple-touch-icon-2.png" rel="apple-touch-icon">
  <link href="assets_lp/img/favicomatic/apple-touch-icon-3.png" rel="apple-touch-icon">
  <link href="assets_lp/img/favicomatic/apple-touch-icon-4.png" rel="apple-touch-icon">
  <link href="assets_lp/img/favicomatic/apple-touch-icon-5.png" rel="apple-touch-icon">
  <link href="assets_lp/img/favicomatic/apple-touch-icon-6.png" rel="apple-touch-icon">
  <link href="assets_lp/img/favicomatic/apple-touch-icon-7.png" rel="apple-touch-icon">
  <link href="assets_lp/img/favicomatic/apple-touch-icon-8.png" rel="apple-touch-icon">
  <link href="assets_lp/img/favicomatic/mstile-1.png" rel="apple-touch-icon">
  <link href="assets_lp/img/favicomatic/mstile-2.png" rel="apple-touch-icon">
  <link href="assets_lp/img/favicomatic/mstile-3.png" rel="apple-touch-icon">
  <link href="assets_lp/img/favicomatic/mstile-4.png" rel="apple-touch-icon">
  <link href="assets_lp/img/favicomatic/mstile-5.png" rel="apple-touch-icon">

  <!-- Fonts -->
  <link href="https://fonts.googleapis.com" rel="preconnect">
  <link href="https://fonts.gstatic.com" rel="preconnect" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Raleway:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">

  <!-- Vendor CSS Files -->
  <link href="assets_lp/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="assets_lp/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
  <link href="assets_lp/vendor/aos/aos.css" rel="stylesheet">
  <link href="assets_lp/vendor/swiper/swiper-bundle.min.css" rel="stylesheet">
  <link href="assets_lp/vendor/glightbox/css/glightbox.min.css" rel="stylesheet">

  <!-- Main CSS File -->
  <link href="{{ asset('assets_lp/css/main.css') }}" rel="stylesheet">

</head>

<body class="index-page">

  <header id="header" class="header d-flex align-items-center fixed-top">
    <div class="container-fluid container-xl position-relative d-flex align-items-center justify-content-between">

      <a href="index.html" class="logo d-flex align-items-center">
        <!-- Uncomment the line below if you also wish to use an image logo -->
        <img src="assets_lp/img/logosapi.png" alt="">
        <h1 class="sitename">HeyCow </h1>
      </a>

      <nav id="navmenu" class="navmenu">
        <ul>
          <li><a href="#hero" class="active">Home</a></li>
          <li><a href="#about">About</a></li>
          <li><a href="#contact">Contact</a></li>
        </ul>
        <i class="mobile-nav-toggle d-xl-none bi bi-list"></i>
      </nav>
    
      <a href="{{route('login')}}" class="btn btn-light">Login</a>

    </div>
  </header>

  <main class="main">

    <!-- Hero Section -->
    <section id="hero" class="hero section dark-background">

      <div class="container">
        <div class="row gy-4">
          <div class="col-lg-4 order-lg-last hero-img" data-aos="zoom-out">
            <img src="assets_lp/img/phone2.png" alt="Phone 1" class="phone-1">
            <img src="assets_lp/img/phone1.png" alt="Phone 2" class="phone-2">
          </div>
          <div class="col-lg-8 d-flex flex-column justify-content-center align-items text-center text-md-start" data-aos="fade-up">
            <h2>Ngangonkan Sapi Mu Sekarang Juga!</h2>
            <p>Aplikasi HeyCow berfungsi untuk memonitoring kesehatan, gejala, dan penyakit pada
              sapi secara real-time yang terintegrasi dengan IoT yang dapat mendeteksi suhu tubuh,
              dan parameterlainnya.
            </p>
            <div class="d-flex mt-4 justify-content-center justify-content-md-start">
              <a href="#" class="download-btn"><i class="bi bi-google-play"></i> <span>Google Play</span></a>
              <a href="#" class="download-btn"><i class="bi bi-apple"></i> <span>App Store</span></a>
            </div>
          </div>
        </div>
      </div>

    </section><!-- /Hero Section -->

    <!-- About Section -->
<section id="about" class="about section">

  <div class="container" data-aos="fade-up" data-aos-delay="100">
    <div class="row align-items-center gy-5">

      <!-- Image on the Left -->
      <div class="col-xl-6">
        <img src="assets_lp/img/sapi_3.jpg" alt="Cow Image" class="img-fluid rounded">
      </div>

      <!-- Content on the Right -->
      <div class="col-xl-6 content">
        <!-- <h3>About Us</h3> -->
        <h2>About HeyCow.com</h2>
        <p>Aplikasi HeyCow! menyediakan layanan manajemen ternak sapi yang mencakup pengelolaan ternak, integrasi perangkat IoT, dan pemantauan kesehatan ternak. Aplikasi ini dirancang untuk para peternak yang meminjamkan sapi mereka kepada HeyCow! untuk digembalakan dan dirawat. 
        Dengan bantuan teknologi, peternak dapat memantau kondisi kesehatan sapi dan mendapatkan solusi manajemen ternak yang lebih efektif.</p>
        <a href="#" class="read-more"><span>Read More</span><i class="bi bi-arrow-right"></i></a>
      </div>

    </div>
  </div>

</section>


    <!-- Featured Section -->
    <section id="featured" class="featured section">

      <!-- Section Title -->
      <div class="container section-title" data-aos="fade-up">
        <h2>OUR SERVICES</h2>
        <p>Kami menyediakan layanan yang dirancang khusus untuk memenuhi kebutuhan Anda</p>
      </div><!-- End Section Title -->

      <div class="container">

        <div class="row gy-4" data-aos="fade-up" data-aos-delay="100">

          <div class="col-md-4">
            <div class="card">
              <div class="img">
                <img src="assets_lp/img/cattle-vector 1.svg" alt="" class="img-fluid">
              </div>
              <h2 class="title">Ngangon - Management Livestock Cattle</h2>
              <p>
                Manage your cattle, so you can manage and monitor them remotely.
              </p>
            </div>
          </div><!-- End Card Item -->

          <div class="col-md-4" data-aos="fade-up" data-aos-delay="200">
            <div class="card">
              <div class="img">
                <img src="assets_lp/img/Mobile - monitor 1.svg" alt="" class="img-fluid">
              </div>
              <h2 class="title">Health Monitoring</h2>
              <p>
                Providing a feature to monitor your cattle’s health through the mobile app.
              </p>
            </div>
          </div><!-- End Card Item -->

          <div class="col-md-4" data-aos="fade-up" data-aos-delay="300">
            <div class="card">
              <div class="img">
                <img src="assets_lp/img/community 2.svg" alt="" class="img-fluid">
              </div>
              <h2 class="title">Community</h2>
              <p>
                A community forum for those who want to ask questions.
              </p>
            </div>
          </div><!-- End Card Item -->

        </div>

      </div>

    </section><!-- /Featured Section -->

  <!-- Featured Section -->
  <section id="featured" class="featured section">

    <!-- Section Title -->
    <div class="container section-title" data-aos="fade-up">
      <h2>OUR PRODUCT</h2>
      <p>Kami menyediakan produk berbasis teknologi canggih yang dirancang untuk meningkatkan efisiensi dan produktivitas Anda</p>
    </div><!-- End Section Title -->

    <div class="container">

      <div class="row gy-4" data-aos="fade-up" data-aos-delay="100">

        <div class="col-md-4">
          <div class="card">
            <div class="img">
              <img src="assets_lp/img/iot 1.svg" alt="" class="img-fluid">
            </div>
            <h2 class="title">Internet Of Things | Ear Tag</h2>
            <p>
              Helping farmers obtain real-time and concrete data from their cows.
            </p>
          </div>
        </div><!-- End Card Item -->

        <div class="col-md-4" data-aos="fade-up" data-aos-delay="200">
          <div class="card">
            <div class="img">
              <img src="assets_lp/img/mobile-app 1.svg" alt="" class="img-fluid">
            </div>
            <h2 class="title">Mobile Application HeyCow</h2>
            <p>
              A mobile app provided for cattle farmers with flexible functionality.
            </p>
          </div>
        </div><!-- End Card Item -->

        <div class="col-md-4" data-aos="fade-up" data-aos-delay="300">
          <div class="card">
            <div class="img">
              <img src="assets_lp/img/desktop.svg" alt="" class="img-fluid">
            </div>
            <h2 class="title">Desktop | Website</h2>
            <p>
              A desktop and website platform for administrators to manage the database or storage of cattle.
            </p>
          </div>
        </div><!-- End Card Item -->

      </div>

    </div>

  </section><!-- /Featured Section -->
  
  <!-- /Banner Section -->
  <div class="banner">
    <div class="text-section">
        <h1>HeyCow</h1>
        <p>A cattle farm management app using Internet Of Things for real-time data updates, available on Google Playstore.</p>
        <a href="#" class="download-button">
            <img src="https://upload.wikimedia.org/wikipedia/commons/7/78/Google_Play_Store_badge_EN.svg" alt="Download on Google Play">
            Download Now!
        </a>
    </div>
    <div class="image-section">
        <img src="assets_lp/img/banner-img.png" alt="HeyCow App Screenshots">
    </div>
</div>  <!-- /End Banner Section -->

   

   <!-- Docum Section -->
<section id="docum" class="docum section">

  <!-- Section Title -->
  <div class="container section-title" data-aos="fade-up">
    <h2>Documentation</h2>
    <p>Ini adalah dokumentasi resmi dari HeyCow.</p>
  </div><!-- End Section Title -->

  <div class="container" data-aos="zoom-in" data-aos-delay="100">

    <div class="row g-4">

      <!-- Docum Image 1 -->
      <div class="col-lg-4">
        <div class="docum-item">
          <div class="icon">
            <img src="assets_lp/img/sapi_1.jpg" alt="Free Plan Image">
          </div>
        </div>
      </div><!-- End Docum Image -->

      <!-- Docum Image 2 -->
      <div class="col-lg-4">
        <div class="docum-item featured">
          <div class="icon">
            <img src="assets_lp/img/sapi_2.jpg" alt="Business Plan Image">
          </div>
        </div>
      </div><!-- End Docum Image -->

      <!-- Docum Image 3 -->
      <div class="col-lg-4">
        <div class="docum-item">
          <div class="icon">
            <img src="assets_lp/img/sapi_3.jpg" alt="Developer Plan Image">
          </div>
        </div>
      </div><!-- End Docum Image -->

    </div>

  </div>

</section><!-- /Docum Section -->


    <!-- Faq Section -->
    <section id="faq" class="faq section">

      <!-- Section Title -->
      <div class="container section-title" data-aos="fade-up">
        <h2>Frequently Asked Questions</h2>
        <p>Pertanyaan yang Sering Kami Dengar.</p>
      </div><!-- End Section Title -->

      <div class="container">

        <div class="row justify-content-center">

          <div class="col-lg-10" data-aos="fade-up" data-aos-delay="100">

            <div class="faq-container">

              <div class="faq-item faq-active">
                <h3>Sistem aplikasinya seperti apa sihh?</h3>
                <div class="faq-content">
                  <p>Lorem ipsum dolor, sit amet consectetur adipisicing elit. Unde omnis molestiae voluptatem ad, culpa veritatis amet suscipit eos eum, nesciunt minus ipsum! Maxime blanditiis possimus sapiente iste eveniet. Natus, earum!</p>
                </div>
                <i class="faq-toggle bi bi-chevron-right"></i>
              </div><!-- End Faq item-->

              <div class="faq-item">
                <h3>Kenapa aplikasi ini perlu saya gunakan?</h3>
                <div class="faq-content">
                  <p>Lorem ipsum dolor, sit amet consectetur adipisicing elit. Veritatis dolore repellat dolorum rerum, architecto ab, vero nulla id adipisci perferendis sit atque reprehenderit eaque mollitia cupiditate quo repellendus labore cum?</p>
                </div>
                <i class="faq-toggle bi bi-chevron-right"></i>
              </div><!-- End Faq item-->

              <div class="faq-item">
                <h3>Apakah aplikasi ini memiliki keunggulan?</h3>
                <div class="faq-content">
                  <p>Lorem ipsum, dolor sit amet consectetur adipisicing elit. Sapiente rem dolore provident vel quibusdam soluta saepe odio dolores explicabo, maxime placeat nostrum quos excepturi ducimus modi velit natus nihil ad.</p>
                </div>
                <i class="faq-toggle bi bi-chevron-right"></i>
              </div><!-- End Faq item-->


            </div>

          </div><!-- End Faq Column-->

        </div>

      </div>

    </section><!-- /Faq Section -->

<!-- Contact Section -->
<section id="contact" class="contact section">

  <!-- Section Title -->
  <div class="container section-title">
    <h2>Contact Us</h2>
  </div>

  <div class="container">
    <div class="row gy-4">
      <div class="col-lg-6">
        <form action="forms/contact.php" method="post" class="php-email-form">
          <div class="row gy-4">
            <div class="col-md-12">
              <input type="text" name="name" class="form-control" placeholder="Nama" required="">
            </div>

            <div class="col-md-12">
              <input type="email" class="form-control" name="email" placeholder="Email" required="">
            </div>

            <div class="col-md-12">
              <input type="text" class="form-control" name="phone" placeholder="No. Telepon" required="">
            </div>

            <div class="col-md-12">
              <textarea class="form-control" name="message" rows="6" placeholder="Komentar" required=""></textarea>
            </div>

            <div class="col-md-12 text-center">
              <button type="submit" class="btn-submit">Submit</button>
            </div>
          </div>
        </form>
      </div>

      <div class="col-lg-6">
        <div class="map">
          <iframe 
            src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d15853.851300654514!2d106.78809035541991!3d-6.589250499999998!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e69c5d2e602b501%3A0x25a12f0f97fac4ee!2sSchool%20of%20Vocational%20Studies%20-%20IPB%20University!5e0!3m2!1sen!2sid!4v1726549637387!5m2!1sen!2sid"
            class="iframe-map"
            style="border:0;"
            allowfullscreen=""
            loading="lazy"
            referrerpolicy="no-referrer-when-downgrade">
          </iframe>
        </div>
      </div>

    </div>
  </div>

</section>




  </main>

  <!-- <footer id="footer" class="footer dark-background">
    <div class="container">
      <h3 class="sitename">HeyCow.com</h3>
      <p>Kami menyediakan jasa pengangonan</p> <p>sapi ternak anda. Kontak kami untuk</p> <p>tahu lebih dalam!</p>
      <div class="social-links d-flex justify-content-start">
        <a href=""><i class="bi bi-instagram"></i></a>
        <a href=""><i class="bi bi-linkedin"></i></a>
      </div>
      <hr>
      <div class="container">
        <div class="copyright">
          <strong class="px-1 sitename">© 2024  HeyCow | Bogor | Jawa Barat | Indonesia</strong> 
        </div>
      </div>
    </div>
  </footer> -->

  <footer id="footer" class="footer dark-background">
    <div class="container">
      <div class="footer-columns">
        <div class="footer-column">
          <h3 class="sitename">HeyCow.com</h3>
          <p>Kami menyediakan jasa pengangonan sapi ternak anda. Kontak kami untuk tahu lebih dalam!</p>
          <div class="social-links">
            <a href=""><i class="bi bi-instagram"></i></a>
            <a href=""><i class="bi bi-google-play"></i></a>
          </div>
        </div>
        <div class="footer-column">
          <h3>Our Services</h3>
          <ul>
            <li>Ngangon - Livestock Cattle</li>
            <li>Health Monitor Cattle</li>
            <li>Cattle Management</li>
            <li>Best Services</li>
          </ul>
        </div>
        <div class="footer-column">
          <h3>Got Question?</h3>
          <p>+628126436745</p>
          <p>STAKUP</p>
          <p>Jl. Kumbang No.13, Baranangsiang, IPB University - Kota Bogor - Bogor, Jawa Barat</p>
        </div>
      </div>
      <hr>
      <div class="container">
        <div class="copyright">
          <strong class="px-1 sitename">© 2024  HeyCow | Bogor | Jawa Barat | Indonesia</strong> 
        </div>
      </div>
    </div>
  </footer>
  

  <!-- Scroll Top -->
  <a href="#" id="scroll-top" class="scroll-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>

  <!-- Preloader -->
  <div id="preloader"></div>

  <!-- Vendor JS Files -->
  <script src="assets_lp/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="assets_lp/vendor/php-email-form/validate.js"></script>
  <script src="assets_lp/vendor/aos/aos.js"></script>
  <script src="assets_lp/vendor/swiper/swiper-bundle.min.js"></script>
  <script src="assets_lp/vendor/glightbox/js/glightbox.min.js"></script>

  <!-- Main JS File -->
  <script src="assets_lp/js/main.js"></script>

</body>

</html>