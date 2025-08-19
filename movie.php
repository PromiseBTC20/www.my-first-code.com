<?php
?>
<!DOCTYPE html>
<html lang="en" class="dark">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Movie Explorer</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    .movie-card:hover {
      transform: scale(1.03);
      transition: transform 0.2s;
    }

    /* Full-page overlay spinner */
    #loadingOverlay {
      position: fixed;
      inset: 0;
      background: rgba(0,0,0,0.55);
      display: flex;
      align-items: center;
      justify-content: center;
      z-index: 60;
      transition: opacity 0.2s ease;
    }
    #loadingOverlay.hidden { display: none; }

    .spinner {
      border: 6px solid rgba(255,255,255,0.15);
      border-top: 6px solid rgba(255,255,255,0.95);
      border-radius: 50%;
      width: 72px;
      height: 72px;
      animation: spin 1s linear infinite;
      box-shadow: 0 4px 20px rgba(0,0,0,0.5);
    }
    @keyframes spin {
      0% { transform: rotate(0deg); }
      100% { transform: rotate(360deg); }
    }

    /* nice card transition for when grid is replaced */
    #moviesGrid { min-height: 120px; }
  </style>
</head>
<body class="bg-gray-100 dark:bg-gray-900 text-gray-900 dark:text-white">
  <!-- Loading overlay -->
  <div id="loadingOverlay" class="hidden" aria-hidden="true">
    <div class="flex flex-col items-center gap-4">
      <div class="spinner" role="status" aria-label="Loading"></div>
      <div class="text-white text-sm opacity-90">Loading movies…</div>
    </div>
  </div>

  <div class="container mx-auto px-4 py-6">
    <header class="flex flex-col sm:flex-row items-center justify-between mb-6">
      <h1 class="text-3xl font-bold mb-4 sm:mb-0">Movie Explorer</h1>
      <div class="flex gap-3 items-center">
        <input id="searchInput" type="text" placeholder="Search movies..." class="px-4 py-2 rounded border dark:border-gray-700 dark:bg-gray-800" />
        <select id="genreFilter" class="px-4 py-2 rounded border dark:border-gray-700 dark:bg-gray-800">
          <option value="">All Genres</option>
          <!-- options will be populated dynamically -->
        </select>
        <button id="searchBtn" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded">Search</button>
        <button id="showFavoritesBtn" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded">Show Favorites</button>
        <button id="toggleThemeBtn" class="bg-gray-700 hover:bg-gray-600 text-white px-4 py-2 rounded">Toggle Dark Mode</button>
      </div>
    </header>

    <section id="featuredBanner" class="relative h-72 mb-8 rounded-xl overflow-hidden bg-cover bg-center" style="background-image: url('');">
      <div class="absolute inset-0 bg-black bg-opacity-50 flex flex-col justify-center p-6">
        <h2 id="bannerTitle" class="text-2xl font-bold mb-1"></h2>
        <p id="bannerMeta" class="mb-2"></p>
        <p id="bannerPlot" class="text-sm mb-4"></p>
        <button id="bannerFavBtn" class="hidden px-4 py-2 bg-yellow-400 hover:bg-yellow-500 text-black rounded w-max">Add to Favorite</button>
      </div>
    </section>

    <h3 id="featuredHeading" class="text-2xl font-semibold mb-4">Featured</h3>
    <div id="moviesGrid" class="grid grid-cols-2 md:grid-cols-4 gap-6 mb-10"></div>

    <div class="flex justify-between items-center mb-10">
      <button id="prevPageBtn" class="px-4 py-2 bg-gray-300 dark:bg-gray-700 rounded">Previous</button>
      <span id="pageInfo"></span>
      <button id="nextPageBtn" class="px-4 py-2 bg-gray-300 dark:bg-gray-700 rounded">Next</button>
    </div>

    <h3 class="text-2xl font-semibold mb-4">Trending</h3>
    <div id="trendingGrid" class="grid grid-cols-2 md:grid-cols-4 gap-6 mb-10"></div>

    <h3 class="text-2xl font-semibold mb-4 mt-10">More Trending Movies</h3>
    <div id="moreTrendingGrid" class="grid grid-cols-2 md:grid-cols-4 gap-6"></div>
  </div>

  <script>
    const API_KEY = 'f4eb89c4'; // keep your key here
    let currentPage = 1;
    let trendingMovies = [];
    let favorites = JSON.parse(localStorage.getItem('favorites') || '[]');
    let isShowingFavorites = false;
    let currentSearch = '';
    let currentGenre = '';

    const featuredBanner = document.getElementById('featuredBanner');
    const bannerTitle = document.getElementById('bannerTitle');
    const bannerMeta = document.getElementById('bannerMeta');
    const bannerPlot = document.getElementById('bannerPlot');
    const bannerFavBtn = document.getElementById('bannerFavBtn');

    const spinnerOverlay = document.getElementById('loadingOverlay');
    const genreSelect = document.getElementById('genreFilter');
    const moviesGrid = document.getElementById('moviesGrid');

    // Theme persistence
    if (localStorage.getItem('theme') === 'dark') {
      document.documentElement.classList.add('dark');
    }

    function showSpinner() {
      spinnerOverlay.classList.remove('hidden');
      spinnerOverlay.setAttribute('aria-hidden', 'false');
    }
    function hideSpinner() {
      spinnerOverlay.classList.add('hidden');
      spinnerOverlay.setAttribute('aria-hidden', 'true');
    }

    function shuffleArray(arr) {
      return arr.sort(() => Math.random() - 0.5);
    }

    function getRandomKeyword() {
      const keywords = ['star', 'love', 'war', 'game', 'night', 'life', 'man', 'hero', 'space', 'dark'];
      return keywords[Math.floor(Math.random() * keywords.length)];
    }

    async function fetchMovieDetails(id) {
      try {
        const res = await fetch(`https://www.omdbapi.com/?apikey=${API_KEY}&i=${id}&plot=short`);
        const json = await res.json();
        if (json && json.Response === "True") return json;
        return null;
      } catch (err) {
        console.warn('detail fetch failed for', id, err);
        return null;
      }
    }

    function movieCardHtml(movie) {
      return `
        <a href="movie-details.php?id=${movie.imdbID}" class="block movie-card rounded shadow overflow-hidden hover:shadow-lg transition">
          <img src="${movie.Poster && movie.Poster !== 'N/A' ? movie.Poster : 'https://via.placeholder.com/300x450?text=No+Image'}" alt="${escapeHtml(movie.Title)}" class="w-full h-64 object-cover" />
          <div class="p-2">
            <h4 class="font-semibold">${escapeHtml(movie.Title)}</h4>
            <p class="text-sm opacity-70">${escapeHtml(movie.Year || '')} ${movie.Genre ? '• ' + escapeHtml(movie.Genre) : ''}</p>
          </div>
        </a>
      `;
    }

    // small helper to avoid injecting any special chars in markup (basic)
    function escapeHtml(str) {
      if (!str) return '';
      return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
    }

    function updateGenreOptions(movies) {
      // collect unique genres from movie.Genre (which is a comma-separated string)
      const set = new Set();
      movies.forEach(m => {
        if (m && m.Genre) {
          m.Genre.split(',').map(g => g.trim()).forEach(g => { if (g) set.add(g); });
        }
      });

      const selected = currentGenre || genreSelect.value || '';

      // rebuild options, keeping the first "All" option
      genreSelect.innerHTML = `<option value="">All Genres</option>`;
      Array.from(set).sort().forEach(g => {
        const opt = document.createElement('option');
        opt.value = g;
        opt.textContent = g;
        genreSelect.appendChild(opt);
      });

      // restore previous selection if still available
      if (selected) {
        const exists = Array.from(genreSelect.options).some(o => o.value === selected);
        if (exists) {
          genreSelect.value = selected;
          currentGenre = selected;
        } else {
          // if previously selected genre no longer exists, clear selection
          genreSelect.value = '';
          currentGenre = '';
        }
      }
    }

    async function renderFavorites() {
      // apply genre filter to favorites
      const list = (currentGenre ? favorites.filter(m => m.Genre && m.Genre.includes(currentGenre)) : favorites);
      if (!list.length) {
        moviesGrid.innerHTML = `<p class="col-span-full text-center text-red-500">No favorites${currentGenre ? ' in this genre' : ''}</p>`;
        return;
      }
      moviesGrid.innerHTML = list.map(movieCardHtml).join('');
    }

    async function loadMovies() {
      // if favorites view is active, render from localStorage favorites
      if (isShowingFavorites) {
        renderFavorites();
        return;
      }

      showSpinner();
      moviesGrid.innerHTML = ""; // clear promptly so overlay is visible
      let query = currentSearch || getRandomKeyword();

      try {
        const res = await fetch(`https://www.omdbapi.com/?apikey=${API_KEY}&s=${encodeURIComponent(query)}&type=movie&page=${currentPage}`);
        const data = await res.json();

        if (data && data.Response === "True" && Array.isArray(data.Search)) {
          // Get detailed data for each search result (so we have Genre)
          const detailPromises = data.Search.map(m => fetchMovieDetails(m.imdbID));
          let detailed = await Promise.all(detailPromises);
          // remove nulls (failed detail fetches)
          detailed = detailed.filter(Boolean);

          // update genre select based on these detailed results
          updateGenreOptions(detailed);

          // Apply genre filter
          let filtered = detailed;
          if (currentGenre) {
            filtered = detailed.filter(m => m.Genre && m.Genre.includes(currentGenre));
          }

          if (filtered.length === 0) {
            moviesGrid.innerHTML = `<p class="col-span-full text-center text-red-500">No results found for this genre</p>`;
          } else {
            moviesGrid.innerHTML = filtered.map(movieCardHtml).join('');
          }
          document.getElementById('pageInfo').textContent = `Page ${currentPage}`;
        } else {
          moviesGrid.innerHTML = `<p class="col-span-full text-center text-red-500">No results found</p>`;
        }
      } catch (err) {
        console.error('search failed', err);
        moviesGrid.innerHTML = `<p class="col-span-full text-center text-red-500">An error occurred while loading movies</p>`;
      } finally {
        hideSpinner();
      }
    }

    async function loadTrendingMovies() {
      // separate spinner not required here — we keep a small inline load behavior
      const keyword = getRandomKeyword();
      try {
        const res = await fetch(`https://www.omdbapi.com/?apikey=${API_KEY}&s=${keyword}&type=movie&page=1`);
        const data = await res.json();
        if (data && data.Response === "True") {
          const detailPromises = data.Search.map(m => fetchMovieDetails(m.imdbID));
          const detailed = (await Promise.all(detailPromises)).filter(Boolean);
          trendingMovies = detailed;
          document.getElementById("trendingGrid").innerHTML = shuffleArray(detailed).map(movieCardHtml).join('');
        }

        // more trending (page 2)
        const moreKeyword = getRandomKeyword();
        const moreRes = await fetch(`https://www.omdbapi.com/?apikey=${API_KEY}&s=${moreKeyword}&type=movie&page=2`);
        const moreData = await moreRes.json();
        if (moreData && moreData.Response === "True") {
          const moreDetailed = (await Promise.all(moreData.Search.map(m => fetchMovieDetails(m.imdbID)))).filter(Boolean);
          document.getElementById("moreTrendingGrid").innerHTML = shuffleArray(moreDetailed).map(movieCardHtml).join('');
        }
      } catch (err) {
        console.warn('trending load failed', err);
      }
    }

    function rotateBanner() {
      if (trendingMovies.length > 0) {
        const movie = trendingMovies[Math.floor(Math.random() * trendingMovies.length)];
        featuredBanner.style.backgroundImage = `url(${movie.Poster && movie.Poster !== 'N/A' ? movie.Poster : 'https://via.placeholder.com/800x400'})`;
        bannerTitle.textContent = movie.Title || '';
        bannerMeta.textContent = `${movie.Year || ''} ${movie.Genre ? '• ' + movie.Genre : ''}`;
        bannerPlot.textContent = movie.Plot || '';
        bannerFavBtn.onclick = () => toggleFavorite(movie);
        bannerFavBtn.classList.remove('hidden');
      }
    }

    function toggleFavorite(movie) {
      const index = favorites.findIndex(f => f.imdbID === movie.imdbID);
      if (index > -1) {
        favorites.splice(index, 1);
      } else {
        favorites.push(movie);
      }
      localStorage.setItem('favorites', JSON.stringify(favorites));
      if (isShowingFavorites) {
        renderFavorites();
      }
    }

    // Theme toggle + persist
    document.getElementById('toggleThemeBtn').onclick = () => {
      document.documentElement.classList.toggle('dark');
      localStorage.setItem('theme', document.documentElement.classList.contains('dark') ? 'dark' : 'light');
    };

    const showFavoritesBtn = document.getElementById('showFavoritesBtn');
    showFavoritesBtn.onclick = () => {
      isShowingFavorites = !isShowingFavorites;
      if (isShowingFavorites) {
        showFavoritesBtn.textContent = "Hide Favorites";
        // When showing favorites, rebuild genre options from favorites too
        updateGenreOptions(favorites);
        renderFavorites();
      } else {
        showFavoritesBtn.textContent = "Show Favorites";
        loadMovies();
      }
    };

    // Pagination
    document.getElementById('prevPageBtn').onclick = () => {
      if (currentPage > 1 && !isShowingFavorites) {
        currentPage--;
        loadMovies();
      }
    };
    document.getElementById('nextPageBtn').onclick = () => {
      if (!isShowingFavorites) {
        currentPage++;
        loadMovies();
      }
    };

    // Search
    document.getElementById('searchBtn').onclick = () => {
      currentSearch = document.getElementById('searchInput').value.trim();
      currentPage = 1;
      isShowingFavorites = false;
      showFavoritesBtn.textContent = "Show Favorites";
      loadMovies();
    };

    // Genre change
    genreSelect.onchange = () => {
      currentGenre = genreSelect.value;
      // if showing favorites, re-render favorites; otherwise reload current page (keeps search)
      if (isShowingFavorites) {
        renderFavorites();
      } else {
        loadMovies();
      }
    };

    // Enter key triggers search
    document.getElementById('searchInput').addEventListener('keypress', (e) => {
      if (e.key === 'Enter') {
        currentSearch = e.target.value.trim();
        currentPage = 1;
        isShowingFavorites = false;
        showFavoritesBtn.textContent = "Show Favorites";
        loadMovies();
      }
    });

    // initial load
    (async function init() {
      showSpinner();
      try {
        // Load first page of movies (random keyword) and trending
        await loadMovies();
        await loadTrendingMovies();
      } finally {
        hideSpinner();
      }
      setInterval(rotateBanner, 15000); // rotate every 15s
    })();
  </script>
</body>
</html>
