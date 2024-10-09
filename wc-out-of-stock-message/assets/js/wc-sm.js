;
(function($){
  $(document).ready(function(){

    setTimeout(function() {
      $('._stock_status_field').show();
    }, 1000);

    $('.wc_input_stock').change(function(){
      if ( this.value < 1 ) {
        $('._out_of_stock_msg_field').addClass('visible');

        $('.'+ this.value+'_field').show();

        if( this.value == 'instock' ){
          $('.outofstock_field').hide();
          $('#_out_of_stock_msg').text('');
        }
      }

    });

    $('#_stock_status').change(function () {
      $('._out_of_stock_msg_field').addClass('visible');

      $('.'+ this.value+'_field').show();

      if( this.value == 'instock' ){
        $('.outofstock_field').hide();
        $('#_out_of_stock_msg').text('');
      }

    });

    /*Out of Stock is selected*/
    if( $('#_stock_status').val() == 'outofstock' ){
        $('._out_of_stock_msg_field, ._wc_sm_use_global_note_field').addClass('visible');
    }
  }); 

})(jQuery);
