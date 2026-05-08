$(function () {
  // Mobile menu
  $('#hamburger').on('click', function () {
    $('#navLinks').toggleClass('open');
  });

  // AJAX filter function
  function loadBlogs() {
    const cat  = $('[data-cat].active').data('cat') || '';
    const srch = $('#searchInput').val() || '';
    const date = $('#dateFilter').val() || '';

    $('#blogsGrid').html('<div class="loading-spinner">⏳</div>');

    $.ajax({
      url: 'ajax.php',
      method: 'GET',
      data: { category: cat, search: srch, date: date },
      success: function (res) {
        $('#blogsGrid').html(res.html);
        $('#postCount').text(res.count + ' posts');
      },
      error: function () {
        $('#blogsGrid').html('<div class="no-posts"><div class="no-posts-icon">❌</div><h3>Error loading posts</h3></div>');
      }
    });
  }

  // Category filter buttons
  $(document).on('click', '.filter-btn', function () {
    $('.filter-btn').removeClass('active');
    $(this).addClass('active');
    loadBlogs();
  });

  // Date filter
  $('#dateFilter').on('change', function () {
    loadBlogs();
  });

  // Search
  let searchTimer;
  $('#searchInput').on('input', function () {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(loadBlogs, 400);
  });

  $('#searchForm').on('submit', function (e) {
    e.preventDefault();
    loadBlogs();
  });

  // Sticky filter bar offset
  $(window).on('scroll', function () {
    if ($(this).scrollTop() > 100) {
      $('#filterBar').addClass('scrolled');
    } else {
      $('#filterBar').removeClass('scrolled');
    }
  });
});
