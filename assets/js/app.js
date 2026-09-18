(function () {
  var date = document.querySelector('input[type="date"]');
  if (date && !date.min) {
    var today = new Date();
    var y = today.getFullYear();
    var m = String(today.getMonth() + 1).padStart(2, '0');
    var d = String(today.getDate()).padStart(2, '0');
    date.min = y + '-' + m + '-' + d;
  }

  var bookmark = document.getElementById('bookmarkBtn');
  if (bookmark) {
    bookmark.addEventListener('click', function () {
      bookmark.classList.toggle('active');
      bookmark.setAttribute('aria-pressed', bookmark.classList.contains('active'));
    });
  }
})();