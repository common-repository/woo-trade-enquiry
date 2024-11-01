    jQuery(function($) {

     dialog = $( "#contact-form" ).dialog({
       autoOpen: false,
       height: 'auto',
       maxHeight: 570,
       width: 'auto',
       dialogClass: "modal-enquiry-form",
       // maxWidth: $(window).width() - 180,
       modal: true,
       fluid: true,
       resizable: false,
     //draggable: false,
   //opacity:0.75,
   show: function() {$(this).fadeIn(300);},
   hide:function() {$(this).fadeOut(300);},
 });
     $( "#enquiry input.contact" ).click(function() {
      dialog.dialog( "open" );
	//  $("#contact-form").closest(".ui-dialog").css("position","fixed");
});
     $( "#cancel" ).click(function() {
       dialog.dialog( "close" );
     });

     $('#send-btn').click(function() {
        //e.preventDefault();

        $('#enquiry-form').validate(
        {
          errorElement: "div",
        //onkeyup: false,
       // onfocusout: false,
       rules:
       {
         solo_customer_name:{
          required: true,
        },

        solo_customer_email:{
         required:true,
         email:true
       },
       solo_enquiry:{
         required: true,
         minlength:10,
       },
       agree:"required"
     },

     messages:{

      solo_customer_name:object_name.solo_customer_name,
      solo_customer_email:object_name.solo_customer_email,
      solo_enquiry:object_name.solo_enquiry,
    },

    errorPlacement: function(error, element) {
      error.appendTo("div#errors");
    },
    submitHandler:function(form)
    {

      var name=$("[name='solo_customer_name']").val();
      var emailid=$("[name='solo_customer_email']").val();
      var subject=$("[name='solo_subject']").val();
      var enquiry=$("[name='solo_enquiry']").val();
      var cc=$("[name='cc']").is(':checked') ? 1 : 0;
      var product_name=$("#solo_product_name").html();
      var product_url=window.location.href;
      var security=$("[name='product_enquiry']").val();
      var authoremail = jQuery('#author_email').val();
      var product_id = $("[name='solo_product_id']").val()
      dialog.dialog( "close" );
      $( "#loading" ).dialog({
        create: function( event, ui ) {
         var dialog = $(this).closest(".ui-dialog");
                                             /*dialog.find(".ui-dialog-titlebar-close").appendTo(dialog)
                                                   .css({
                                                     position: "absolute",
                                                     top: 0,
                                                     right: 0,
                                                     margin: "3px"
                                                   });*/
      dialog.find(".ui-dialog-titlebar").remove();},
      resizable: false,
      width:'auto',
      height:'auto',
      modal: true,
                              //draggable: false
                            });
      $.ajax({
       url: object_name.ajaxurl,
       type:'POST',
       data: {action:'solo_send',security:security,solo_name:name,solo_emailid:emailid,solo_subject:subject,solo_enquiry:enquiry,solo_cc:cc,solo_product_name:product_name,solo_product_url:product_url,uemail:authoremail, solo_product_id: product_id},
       success: function(response) {
         $( "#send_mail" ).hide();
         $( "#loading" ).text(response);
         $( "#loading" ).dialog( "option", "buttons", {"OK": function() { $(this).dialog("close"); } });
       }
     });

      form.reset();
    }
  });

  });

    $(".ui-dialog").addClass("wdm-enquiry-modal");


  });
