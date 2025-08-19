<?php



?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>CineScope - Movie Landing</title>
  <style>
    /* Reset & base */
    * {
      box-sizing: border-box;
    }
    body {
      margin: 0;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      /* Removed overflow: hidden so footer can show */
      background-color: white;
      color: black;
      transition: background-color 0.3s, color 0.3s;
    }
    body.dark {
      background-color: black;
      color: white;
    }

    header {
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      padding: 1rem 2rem;
      display: flex;
      justify-content: space-between;
      align-items: center;
      z-index: 10;
    }
    header h2 {
      color: #e50914;
      font-weight: bold;
      font-size: 1.25rem;
      margin: 0;
    }

    button.toggle-btn {
      display: flex;
      align-items: center;
      gap: 0.5rem;
      background-color: #e50914;
      color: white;
      padding: 0.5rem 1rem;
      border: none;
      border-radius: 9999px;
      font-weight: 600;
      font-size: 0.875rem;
      cursor: pointer;
      transition: background-color 0.3s, transform 0.2s;
    }
    button.toggle-btn:hover {
      background-color: #b20710;
      transform: scale(1.05);
    }
    button.toggle-btn svg {
      width: 18px;
      height: 18px;
      fill: currentColor;
    }

    section.hero {
      position: relative;
      height: 100vh;
      background-image: url('https://images5.alphacoders.com/128/thumb-1920-1280284.jpg');
      background-position: center;
      background-size: cover;
      display: flex;
      align-items: center;
      justify-content: center;
      text-align: center;
    }
    section.hero::before {
      content: "";
      position: absolute;
      inset: 0;
      background-color: rgba(0,0,0,0.6);
      z-index: 0;
    }
    .hero-content {
      position: relative;
      z-index: 1;
      max-width: 700px;
      padding: 1rem;
      animation: fade-up 1.2s ease-out forwards;
    }
    .hero-content h1 {
      font-size: 2.5rem;
      font-weight: bold;
      color: #e50914;
      margin-bottom: 1rem;
    }
    .hero-content p {
      font-size: 1.125rem;
      margin-bottom: 1.5rem;
    }
    .hero-content a.cta-button {
      background-color: #e50914;
      color: white;
      padding: 0.75rem 2rem;
      border-radius: 0.5rem;
      font-weight: 700;
      font-size: 1rem;
      text-decoration: none;
      transition: background-color 0.3s;
      display: inline-block;
    }
    .hero-content a.cta-button:hover {
      background-color: #b20710;
    }

    footer {
      text-align: center;
      padding: 1.5rem 1rem;
      font-size: 0.875rem;
      color: rgb(236, 229, 229);
      background: #111;
    }

    footer .social-links {
      margin-top: 0.5rem;
      display: flex;
      justify-content: center;
      gap: 1rem;
    }

    footer .social-links a {
      color: rgb(236, 229, 229);
      font-size: 1.25rem;
      text-decoration: none;
      transition: color 0.3s;
    }

    footer .social-links a:hover {
      color: #e50914;
    }
    @keyframes fade-up {
      0% {
        opacity: 0;
        transform: translateY(20px);
      }
      100% {
        opacity: 1;
        transform: translateY(0);
      }
    }
  </style>
</head>
<body>

  <header>
    <h2>🎬 FilmNest</h2>
    <button class="toggle-btn" onclick="toggleDarkMode()" id="toggleBtn">
      <svg viewBox="0 0 24 24" id="modeIcon">
        <path d="M12 2a9.93 9.93 0 00-7.07 2.93A9.93 9.93 0 002 12c0 2.67 1.05 5.17 2.93 7.07A9.93 9.93 0 0012 22a10 10 0 000-20zm0 18c-1.85 0-3.55-.63-4.9-1.69a8.001 8.001 0 0110.4-10.4A8.002 8.002 0 0112 20z"/>
      </svg>
      <span id="mode-label">Dark Mode</span>
    </button>
  </header>

  <section class="hero">
    <div class="hero-content">
      <h1>Find. Watch. Love.</h1>
      <p>Explore the latest blockbusters, hidden gems, and build your own movie collection.</p>
      <a href="movie.php" class="cta-button">Get Started</a>
    </div>
  </section>

  <footer>
  <p>&copy; 2025 CineScope. All rights reserved.</p>
  <div class="social-links">
    <a href="#" aria-label="Twitter">🐦Twitter |</a>
    <a href="#" aria-label="Facebook">📘 Facebook |</a>
    <a href="#" aria-label="Instagram">📸 Instagram</a>
  </div>
</footer>


  <script>
    const toggleBtn = document.getElementById('toggleBtn');
    const modeLabel = document.getElementById('mode-label');
    const modeIcon = document.getElementById('modeIcon');

    const darkPath = "M12 2a9.93 9.93 0 00-7.07 2.93A9.93 9.93 0 002 12c0 2.67 1.05 5.17 2.93 7.07A9.93 9.93 0 0012 22a10 10 0 000-20zm0 18c-1.85 0-3.55-.63-4.9-1.69a8.001 8.001 0 0110.4-10.4A8.002 8.002 0 0112 20z";
    const lightPath = "M12 4.5a7.5 7.5 0 100 15 7.5 7.5 0 000-15zm0 13a5.5 5.5 0 110-11 5.5 5.5 0 010 11z"; // example sun icon path

    function updateButtonLabel() {
      if (document.body.classList.contains('dark')) {
        modeLabel.textContent = 'Light Mode';
        modeIcon.setAttribute('d', lightPath);
      } else {
        modeLabel.textContent = 'Dark Mode';
        modeIcon.setAttribute('d', darkPath);
      }
    }

    function toggleDarkMode() {
      document.body.classList.toggle('dark');
      updateButtonLabel();
    }

    // Initialize label on load
    updateButtonLabel();
  </script>
</body>
</html>
