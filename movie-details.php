<?php



?>
<!DOCTYPE html>
<html lang="en" class="dark">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Movie Details</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 dark:bg-gray-900 text-gray-900 dark:text-white">
  <div class="container mx-auto px-4 py-6">
    <button onclick="history.back()" class="mb-4 px-4 py-2 bg-blue-500 text-white rounded">Back</button>
    <div id="movieDetails" class="flex flex-col md:flex-row gap-6 mb-10"></div>

    <div id="trailerSection" class="mb-10 hidden">
      <h2 class="text-2xl font-semibold mb-4">Trailer</h2>
      <div id="trailerContainer" class="aspect-video w-full max-w-3xl"></div>
    </div>

    <div class="mb-10">
      <h2 class="text-2xl font-semibold mb-4">Related Movies</h2>
      <div id="relatedMovies" class="grid grid-cols-2 md:grid-cols-4 gap-6"></div>
    </div>

    <div class="mb-10">
      <h2 class="text-2xl font-semibold mb-4">Your Favorite Movies</h2>
      <div id="favoriteMovies" class="grid grid-cols-2 md:grid-cols-4 gap-6"></div>
    </div>
  </div>

  <script>
    const OMDB_API_KEY = 'f4eb89c4';
    const YOUTUBE_API_KEY = 'AIzaSyC2-oRQIa6beulSJC2h8TqufwvtiKXTeMQ';
    const movieId = new URLSearchParams(window.location.search).get('id');

    function isFavorite(id) {
      const favorites = JSON.parse(localStorage.getItem('favorites') || '[]');
      return favorites.includes(id);
    }

    function toggleFavorite(id, title) {
      let favorites = JSON.parse(localStorage.getItem('favorites') || '[]');
      if (favorites.includes(id)) {
        favorites = favorites.filter(fav => fav !== id);
      } else {
        favorites.push(id);
      }
      localStorage.setItem('favorites', JSON.stringify(favorites));
      alert(`${title} has been ${favorites.includes(id) ? 'added to' : 'removed from'} favorites.`);
      showFavoriteMovies();
    }

    async function fetchMovieDetails(id) {
      const res = await fetch(`https://www.omdbapi.com/?apikey=${OMDB_API_KEY}&i=${id}&plot=full`);
      return await res.json();
    }

    function displayMovieDetails(movie) {
      document.title = movie.Title;
      const html = `
        <img src="${movie.Poster !== 'N/A' ? movie.Poster : 'https://via.placeholder.com/300x450'}" alt="${movie.Title}" class="w-full md:w-1/3 rounded shadow" />
        <div>
          <h1 class="text-3xl font-bold mb-2">${movie.Title}</h1>
          <p class="text-sm mb-2">${movie.Year} • ${movie.Genre} • ${movie.Runtime}</p>
          <p class="mb-4">${movie.Plot}</p>
          <a href="https://archive.org/search.php?query=${encodeURIComponent(movie.Title + ' movie')}" target="_blank" class="inline-block mb-4 px-4 py-2 bg-green-600 text-white rounded">Download</a>
          <button onclick="toggleFavorite('${movie.imdbID}', '${movie.Title}')" class="mb-4 ml-2 px-4 py-2 bg-yellow-500 text-white rounded">${isFavorite(movie.imdbID) ? 'Remove from' : 'Add to'} Favorites</button>
          <p class="text-sm mb-1"><strong>Director:</strong> ${movie.Director}</p>
          <p class="text-sm mb-1"><strong>Actors:</strong> ${movie.Actors}</p>
          <p class="text-sm mb-1"><strong>IMDB Rating:</strong> ${movie.imdbRating}</p>
        </div>
      `;
      document.getElementById('movieDetails').innerHTML = html;
      showRelatedMovies(movie.Title);
      fetchTrailer(movie.Title);
    }

    async function fetchTrailer(title) {
      const query = encodeURIComponent(`${title} official trailer`);
      const url = `https://www.googleapis.com/youtube/v3/search?part=snippet&q=${query}&type=video&key=${YOUTUBE_API_KEY}&maxResults=1`;

      try {
        const res = await fetch(url);
        const data = await res.json();
        if (data.items && data.items.length > 0) {
          const videoId = data.items[0].id.videoId;
          const embedUrl = `https://www.youtube.com/embed/${videoId}`;
          document.getElementById('trailerContainer').innerHTML = `
            <iframe width="100%" height="100%" src="${embedUrl}" frameborder="0" allowfullscreen></iframe>
          `;
          document.getElementById('trailerSection').classList.remove('hidden');
        } else {
          document.getElementById('trailerSection').classList.add('hidden');
        }
      } catch (error) {
        console.error('Error fetching trailer:', error);
        document.getElementById('trailerSection').classList.add('hidden');
      }
    }

    async function showRelatedMovies(title) {
      const keyword = title.split(' ')[0];
      const res = await fetch(`https://www.omdbapi.com/?apikey=${OMDB_API_KEY}&s=${keyword}&type=movie&page=1`);
      const data = await res.json();
      if (data.Response === "True") {
        const related = data.Search.filter(m => m.imdbID !== movieId).slice(0, 8);
        const html = related.map(m => `
          <div class="block">
            <a href="movie-details.html?id=${m.imdbID}">
              <img src="${m.Poster !== 'N/A' ? m.Poster : 'https://via.placeholder.com/300x450'}" alt="${m.Title}" class="rounded shadow mb-2">
              <p class="text-sm text-center">${m.Title}</p>
            </a>
            <button onclick="toggleFavorite('${m.imdbID}', '${m.Title}')" class="mt-1 px-2 py-1 text-xs bg-yellow-500 text-white rounded w-full">${isFavorite(m.imdbID) ? '★ Favorite' : '☆ Add to Favorites'}</button>
          </div>
        `).join('');
        document.getElementById('relatedMovies').innerHTML = html;
      }
    }

    async function showFavoriteMovies() {
      const favorites = JSON.parse(localStorage.getItem('favorites') || '[]');
      const movies = await Promise.all(
        favorites.map(id => fetch(`https://www.omdbapi.com/?apikey=${OMDB_API_KEY}&i=${id}`).then(res => res.json()))
      );
      const html = movies.map(m => `
        <div class="block">
          <a href="movie-details.html?id=${m.imdbID}">
            <img src="${m.Poster !== 'N/A' ? m.Poster : 'https://via.placeholder.com/300x450'}" alt="${m.Title}" class="rounded shadow mb-2">
            <p class="text-sm text-center">${m.Title}</p>
          </a>
          <button onclick="toggleFavorite('${m.imdbID}', '${m.Title}')" class="mt-1 px-2 py-1 text-xs bg-yellow-500 text-white rounded w-full">${isFavorite(m.imdbID) ? '★ Favorite' : '☆ Add to Favorites'}</button>
        </div>
      `).join('');
      document.getElementById('favoriteMovies').innerHTML = html;
    }

    fetchMovieDetails(movieId).then(displayMovieDetails);
    showFavoriteMovies();
  </script>
</body>
</html>
