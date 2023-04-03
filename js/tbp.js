jQuery(document).ready(function($) {
    $('#post-filter-submit').click(function(e) {
      
      e.preventDefault();
  
      var destination = $('#destination').val();
      var theme = $('#theme').val();
      var category = $('#category').val();
      var tag = $('#tag').val();
  
      $.ajax({
        url: myAjax.ajaxurl,
        type: 'post',
        data: {
          action: 'post_filter_ajax_handler',
          destination: destination,
          theme: theme,
          category: category,
          tag: tag,
        },
        beforeSend: function() {
          $('#post-filter-results').html('Loading...');
        },
        success: function(result) {
          $('#post-filter-results').html(result);
        },
        error: function() {
          alert('Error!');
        },
      });
    });
  });
  