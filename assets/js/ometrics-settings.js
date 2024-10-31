jQuery(document).ready(function() {
  jQuery('#ometrics-connect-account').submit(function() {
    jQuery("#register-button").removeClass("pulseArea");
      
      //encode the password
      jQuery("#ometrics_password").val(btoa(jQuery("#ometrics_password").val()));
      
      jQuery.ajax({
        type: "POST",
        url: ajaxurl,
        data: jQuery('#ometrics-connect-account').serialize()
      }).done(function( data ) {
        if (data.error) {
          //display in error div
          jQuery('#saveMessage').empty();
          jQuery('#saveMessage').append("<p class='ometrics-error'>"+data.error+"</p>").show();
          jQuery('#ometrics_id').val('');
          jQuery('#ometrics_token').val('');
          jQuery('#ometrics_agent').val('');
        }
        else {
          jQuery('#ometrics_id').val(data.ometrics_id);
          jQuery('#ometrics_token').val(data.ometrics_token);
          jQuery('#ometrics_agent').val(data.ometrics_agent);
          jQuery('#bot-found').hide();
          if (data.ometrics_agent) {
            jQuery('#bot-found').show();
            jQuery('#ometrics-configure').show();
            jQuery('#ometrics-configure').attr('href', 'https://www.ometrics.com/user/redirect/ochatbot-builder?agentId='+data.ometrics_agent);
            if (data.agent_status == 1) {
              //set to "checked"
              jQuery("#agent_status").prop('checked', true);
              jQuery("#activate").hide("slow");
              jQuery("#deactivate").show("slow");
            }
            else {
              //uncheck
              jQuery("#agent_status").prop('checked', false);
              jQuery("#deactivate").hide("slow");
              jQuery("#activate").show("slow");
            }
          }
          jQuery('#saveMessage').empty();
          jQuery('#saveMessage').append("<p class='ometrics-success'>"+data.success+"</p>").show();
          jQuery("#disconnect").show();
          jQuery("#ometrics-connect-account").hide();
          jQuery(".ometrics-tab").hide();
          jQuery('.ometrics-tabcontent').addClass("ometrics-connected");
          jQuery("#forgot-password-section").hide();
          jQuery("#login").hide();
          jQuery("#register-account").hide();
          setTimeout(function(){ jQuery('#saveMessage').hide('slow');}, 10000);
        }
      })
      .fail(function(jqXHR, textStatus, errorThrown) {
          // If fail
          console.log(textStatus + ': ' + errorThrown);
          jQuery('#saveMessage').append("<p class='ometrics-error'>Error communicating with Ometrics (A01).  <a href='https://www.ometrics.com/user/support'>Please contact Ometrics technical support</a> or email at <a href='mailto:support@ometrics.com'>support@ometrics.com</a>.</p>").show();
      });

    return false;
  });

  jQuery('#registration_form').submit(function() {
    jQuery("#register-button").removeClass("pulseArea");
    //check valid registration info

    if (!validateRegistration()) {
      return;
    }
    //encode the password
    jQuery("#ometrics_password").val(btoa(jQuery("#ometrics_password").val()));
      
    //register form
    jQuery.ajax({
      type: "POST",
      url: ajaxurl,
      data: jQuery('#registration_form').serialize()
    }).done(function( data ) {
      if (data.error) {
        //display in error div
        jQuery('#saveMessage').append("<p class='ometrics-error'>"+data.error+"</p>").show();
        jQuery('#ometrics_id').val('');
        jQuery('#ometrics_token').val('');
        jQuery('#ometrics_agent').val('');
        jQuery('#bot-found').hide();
        jQuery("#agent_status").prop('checked', false);
        jQuery("#activate").hide("slow");
        jQuery("#deactivate").show("slow");
      }
      else {
        jQuery('#saveMessage').append("<p class='ometrics-success'>"+data.success+"</p>").show();
        jQuery('#bot-found').show();
        jQuery("#disconnect").show();
        jQuery("#ometrics-connect-account").hide();
        jQuery(".ometrics-tab").hide();
        jQuery('.ometrics-tabcontent').addClass("ometrics-connected");
        jQuery("#forgot-password-section").hide();
        jQuery("#register-account").hide();
        jQuery("#login").hide();
        setTimeout(function(){ jQuery('#saveMessage').hide('slow');}, 10000);
        jQuery('#bot-found').hide();
        jQuery('#ometrics_id').val(data.ometrics_id);
        jQuery('#ometrics_token').val(data.ometrics_token);
        jQuery('#ometrics_agent').val(data.ometrics_agent);
        if (data.ometrics_agent) {
          jQuery('#bot-found').show();
          jQuery('#ometrics-configure').show();
          jQuery('#ometrics-configure').attr('href', 'https://www.ometrics.com/user/redirect/ochatbot-builder?agentId='+data.ometrics_agent);
          if (data.agent_status == 1) {
            //set to "checked"
            jQuery("#agent_status").prop('checked', true);
            jQuery("#activate").hide("slow");
            jQuery("#deactivate").show("slow");
          }
          else {
            //uncheck
            jQuery("#agent_status").prop('checked', false);
            jQuery("#deactivate").hide("slow");
            jQuery("#activate").show("slow");
          }
        }
        else {
          jQuery('#bot-found').hide();
        }

      }
    })
    .fail(function(jqXHR, textStatus, errorThrown) {
        // If fail
        console.log(textStatus + ': ' + errorThrown);
        jQuery('#saveMessage').append("<p class='ometrics-error'>Error communicating with Ometrics (A02).  <a href='https://www.ometrics.com/user/support'>Please contact Ometrics technical support</a> or email at <a href='mailto:support@ometrics.com'>support@ometrics.com</a>.</p>").show();
    });
    return false;
  });

  jQuery('#ometrics-forgot-form').submit(function() {
    jQuery("#register-button").removeClass("pulseArea");
    jQuery.ajax({
      type: 'POST',
      url: ajaxurl,
      data: jQuery('#ometrics-forgot-form').serialize()
    }).done(function(data) {
        // If successful
        //check for error/warning messages
        if (data.no_account && data.no_account == 1) {
          jQuery("#register-button").addClass("pulseArea");
        }
        if (data.error) {
          //display in error div
          jQuery('#saveMessage').empty();
          jQuery('#saveMessage').append("<p class='ometrics-error'>"+data.error+"</p>").show();
        }
        else {
          jQuery('#saveMessage').empty();
          jQuery('#saveMessage').append("<p class='ometrics-success'>"+data.success+"</p>").show();
          setTimeout(function(){ jQuery('#saveMessage').hide('slow');}, 10000);
        }
    }).fail(function(jqXHR, textStatus, errorThrown) {
        // If fail
        console.log(textStatus + ': ' + errorThrown);
        jQuery('#saveMessage').append("<p class='ometrics-error'>Error communicating with Ometrics (A03).  <a href='https://www.ometrics.com/user/support'>Please contact Ometrics technical support</a> or email at <a href='mailto:support@ometrics.com'>support@ometrics.com</a>.</p>").show();
    });

    return false;
  });

  jQuery("#ometrics-forgot").click(function () {
    jQuery("#ometrics-forgot-form").toggle("slow");
  });

  jQuery('#ometrics-disconnect').click(function () {
    if (confirm("This action will disconnect your account and your Ometrics tools will no longer function.  Are you sure?")){
      jQuery("#register-button").removeClass("pulseArea");
      jQuery.ajax({
        type: 'POST',
        url: ajaxurl,
        data: jQuery('#ometrics-settings-connect-form').serialize()+"&type=disconnect"

      }).done(function(data) {
          // If successful
          //check for error/warning messages
          if (data.error) {
            //display in error div
            jQuery('#saveMessage').empty();
            jQuery('#saveMessage').append("<p class='ometrics-error'>"+data.error+"</p>").show();
            jQuery('#ometrics_id').val('');
            jQuery('#ometrics_token').val('');
            jQuery('#ometrics_agent').val('');
          }
          else {
            jQuery('#saveMessage').empty();
            jQuery('#saveMessage').append("<p class='ometrics-success'>"+data.success+"</p>").show();
            jQuery('#ometrics_id').val('');
            jQuery('#ometrics_token').val('');
            jQuery('#ometrics_agent').val('');
          }
          jQuery('#bot-found').hide();
          jQuery('#ometrics-configure').hide();
          jQuery('#ometrics-configure').attr('href', '');
          jQuery("#agent_status").prop('checked', false);
          //show login/connect fields
          jQuery("#disconnect").hide();
          jQuery("#ometrics-connect-account").show();
          jQuery(".ometrics-tab").show();
          jQuery('.ometrics-tabcontent').removeClass("ometrics-connected");
          jQuery("#forgot-password-section").show();
          //jQuery("#register-account").show();
          //jQuery("#login").show();
          openTab(0, 'login');
          setTimeout(function(){ jQuery('#saveMessage').hide('slow');}, 10000);
      }).fail(function(jqXHR, textStatus, errorThrown) {
          // If fail
          console.log(textStatus + ': ' + errorThrown);
          jQuery('#ometrics_id').val('');
          jQuery('#ometrics_token').val('');
          jQuery('#ometrics_agent').val('');
          jQuery("#disconnect").hide();
          jQuery('#bot-found').hide();
          jQuery('#ometrics-configure').hide();
          jQuery('#ometrics-configure').attr('href', '');
          jQuery("#agent_status").prop('checked', false);
          //show login/connect fields
          jQuery("#ometrics-connect-account").show();
          jQuery(".ometrics-tab").show();
          jQuery('.ometrics-tabcontent').removeClass("ometrics-connected");
          jQuery("#forgot-password-section").show();
          //jQuery("#register-account").show();
          //jQuery("#login").show();
          openTab(0, 'login');
          jQuery('#saveMessage').append("<p class='ometrics-error'>Error communicating with Ometrics (A04).  <a href='https://www.ometrics.com/user/support'>Please contact Ometrics technical support</a> or email at <a href='mailto:support@ometrics.com'>support@ometrics.com</a>.</p>").show();
      });
    }
  });

  jQuery('#agent_status').change(function() {
    var statusVal = 0;
    if (jQuery(this).is(':checked')) {
      statusVal = 1;
    }
    //ajax call to update Agent
    //check for valid id, token and agent
    jQuery.ajax({
      type: 'POST',
      url: ajaxurl,
      data: jQuery('#ometrics-settings-connect-form').serialize()+"&type=setStatus&status="+ statusVal
    }).done(function(data) {
      // If successful
      //check for error/warning messages
      if (data.no_account && data.no_account == 1) {
        jQuery("#register-button").addClass("pulseArea");
      }
      if (data.error) {
        //display in error div
        jQuery('#saveMessage').empty();
        jQuery('#saveMessage').append("<p class='ometrics-error'>"+data.error+"</p>").show();
        jQuery('#bot-found').hide();
      }
      else {
        jQuery('#saveMessage').empty();
        jQuery('#bot-found').show();
        if (data.agent_status == 1) {
          //set to "checked"
          jQuery("#agent_status").prop('checked', true);
          jQuery("#activate").hide("slow");
          jQuery("#deactivate").show("slow");
        }
        else {
          //uncheck
          jQuery("#deactivate").hide("slow");
          jQuery("#activate").show("slow");
          jQuery("#agent_status").prop('checked', false);
        }
      }
    }).fail(function(jqXHR, textStatus, errorThrown) {
        // If fail
        console.log(textStatus + ': ' + errorThrown);
        jQuery('#saveMessage').append("<p class='ometrics-error'>Error communicating with Ometrics (A05).  <a href='https://www.ometrics.com/user/support'>Please contact Ometrics technical support</a> or email at <a href='mailto:support@ometrics.com'>support@ometrics.com</a>.</p>").show();
    });

  });

  if (jQuery("#ometrics_agent").val() && jQuery("#ometrics_token").val() && jQuery("#ometrics_id").val()) {
    jQuery("#ometrics-configure").show();
    //get agent status on page load
    jQuery.ajax({
      type: 'POST',
      url: ajaxurl,
      data: jQuery('#ometrics-settings-connect-form').serialize()+"&type=getStatus"
    }).done(function(data) {
        // If successful
        //check for error/warning messages
        if (data.no_account && data.no_account == 1) {
          jQuery("#register-button").addClass("pulseArea");
        }
        if (data.error) {
          //display in error div
          jQuery('#saveMessage').empty();
          jQuery('#saveMessage').append("<p class='ometrics-error'>"+data.error+"</p>").show();
          jQuery('#bot-found').hide();
        }
        else {
          //check for new agent
          if (typeof(data.ometrics_agent) !== 'undefined' && data.ometrics_agent != jQuery("#ometrics_agent").val()) {
            jQuery("#ometrics_agent").val(data.ometrics_agent);

          }
          jQuery('#saveMessage').empty();
          jQuery('#bot-found').show();
          if (data.agent_status == 1) {
            //set to "checked"
            jQuery("#agent_status").prop('checked', true);
            jQuery("#activate").hide("slow");
            jQuery("#deactivate").show("slow");
          }
          else {
            //uncheck
            jQuery("#deactivate").hide("slow");
            jQuery("#activate").show("slow");
            jQuery("#agent_status").prop('checked', false);
          }
        }
    }).fail(function(jqXHR, textStatus, errorThrown) {
        // If fail
        console.log(textStatus + ': ' + errorThrown);
        jQuery('#saveMessage').append("<p class='ometrics-error'>Error communicating with Ometrics (A06).  <a href='https://www.ometrics.com/user/support'>Please contact Ometrics technical support</a> or email at <a href='mailto:support@ometrics.com'>support@ometrics.com</a>.</p>").show();
    });
  }
});

function openTab(evt, tabName) {
  // Declare all variables
  var i, tabcontent, tablinks;

  // Get all elements with class="ometrics-tabcontent" and hide them
  tabcontent = document.getElementsByClassName("ometrics-tabcontent");
  for (i = 0; i < tabcontent.length; i++) {
    tabcontent[i].style.display = "none";
  }

  // Get all elements with class="ometrics-tablinks" and remove the class "active"
  tablinks = document.getElementsByClassName("ometrics-tablinks");
  for (i = 0; i < tablinks.length; i++) {
    tablinks[i].className = tablinks[i].className.replace(" active", "");
  }

  // Show the current tab, and add an "active" class to the button that opened the tab
  document.getElementById(tabName).style.display = "block";
  var newTab = document.getElementById(tabName+'-button');
  newTab.className += " active";
}
  function checkemail(email) {
    jQuery.ajax({
      type: 'POST',
      url: ajaxurl,
      data: jQuery('#emailForm').serialize()+"&data="+encodeURIComponent(email)
    }).done(function(data) {
      data = jQuery.trim(data.result);
      if (data == 0) {
          jQuery("#reg_user_email_err").text('');
          jQuery("#user_email_check_err").text('');
          jQuery("#emailcheckId").attr('value', '1');
      }
      if (data == 1) {
          jQuery("#reg_user_email_err").text('');
          jQuery("#user_email_check_err").html('Email is already in use.  <a onclick="openTab(event, \'login\')" style="cursor: pointer;">Click here to log in.</a>');
          jQuery("#reg_user_email").attr('value', '');
          jQuery("#emailcheckId").attr('value', '0');

      }
    }).fail(function(jqXHR, textStatus, errorThrown) {
        // If fail
        console.log(textStatus + ': ' + errorThrown);
        jQuery('#saveMessage').append("<p class='ometrics-error'>Error communicating with Ometrics (A07).  <a href='https://www.ometrics.com/user/support'>Please contact Ometrics technical support</a> or email at <a href='mailto:support@ometrics.com'>support@ometrics.com</a>.</p>").show();
    });

  }
  function validateRegistration() {
        jQuery('#first_name_err').text('');
        var first_name=jQuery('#first_name').val();
        if(first_name=="") {
          jQuery('#first_name_err').text('Please enter first name.');
          return false;
        }

        if(checkName(first_name)==false) {
          jQuery('#first_name_err').text('Only alphabets are allowed.');
          return false;
        }

        jQuery('#last_name_err').text('');
        var last_name=jQuery('#last_name').val();
        if(last_name=="") {
          jQuery('#last_name_err').text('Please enter last name.');
          return false;
        }

        if(checkName(last_name)==false) {
          jQuery('#last_name_err').text('Only alphabets are allowed.');
          return false;
        }

        jQuery('#reg_user_email_err').text('');
        var user_email=jQuery('#reg_user_email').val();
        if(user_email=="") {
          jQuery('#reg_user_email_err').text('Please enter email address.');
          return false;
        }

        if(emailValidate(user_email)==false) {
          jQuery('#reg_user_email_err').text('');
          jQuery('#reg_user_email_err').text('Please enter valid email address.');
          return false;
        }

        var emailcheckId=jQuery('#emailcheckId').val();

        if(emailcheckId=="0")	{
          jQuery('#user_email_check_err').text('');
          jQuery('#reg_user_email_err').text('');
          jQuery("#reg_user_email_err").html('Email is already in use.  <a onclick="openTab(event, \'login\')" style="cursor: pointer;">Click here to log in.</a>');
          return false;
        }

        jQuery('#reg_user_password_err').text('');
        var user_password=jQuery('#reg_user_password').val();
        if(user_password=="") {
          jQuery('#reg_user_password_err').text('Please enter password.');
          return false;
        }


        if(!testPassword(user_password)) {
          jQuery('#reg_user_password_err').text('');
          jQuery('#reg_user_password_err').text('Password must have 8 to 15 characters,one number & at least one capital letter. ');
          return false;
        }
      return true;
  }

  function emailValidate(email) {
     var reg = /^([A-Za-z0-9_\-\.\+])+\@([A-Za-z0-9_\-\.])+\.([A-Za-z]{2,10})$/;
     return reg.test(email);
  }

  function testPassword(passwd) {
      var intScore   = 0;
      var strVerdict = "weak";
      var strLog     = "";

      // PASSWORD LENGTH
      if (passwd.length<5)                         // length 4 or less
      {
        intScore = (intScore+3);
        strLog   = strLog + "3 points for length (" + passwd.length + ")\n";
      }
      else if (passwd.length>4 && passwd.length<8) // length between 5 and 7
      {
        intScore = (intScore+6);
        strLog   = strLog + "6 points for length (" + passwd.length + ")\n";
      }
      else if (passwd.length>7 && passwd.length<16)// length between 8 and 15
      {
        intScore = (intScore+12);
        strLog   = strLog + "12 points for length (" + passwd.length + ")\n";
      }
      else if (passwd.length>15)                    // length 16 or more
      {
        intScore = (intScore+18);
        strLog   = strLog + "18 point for length (" + passwd.length + ")\n";
      }


      // LETTERS 
      if (passwd.match(/[a-z]/))                              // [verified] at least one lower case letter
      {
        intScore = (intScore+1);
        strLog   = strLog + "1 point for at least one lower case char\n";
      }

      if (passwd.match(/[A-Z]/))                              // [verified] at least one upper case letter
      {
        intScore = (intScore+5);
        strLog   = strLog + "5 points for at least one upper case char\n";
      }

      // NUMBERS
      if (passwd.match(/\d+/))                                 // [verified] at least one number
      {
        intScore = (intScore+5);
        strLog   = strLog + "5 points for at least one number\n";
      }

      if (passwd.match(/(.*[0-9].*[0-9].*[0-9])/))             // [verified] at least three numbers
      {
        intScore = (intScore+5);
        strLog   = strLog + "5 points for at least three numbers\n";
      }


      // SPECIAL CHAR
      if (passwd.match(/.[!,@,#,$,%,^,&,*,?,_,~]/))            // [verified] at least one special character
      {
        intScore = (intScore+5);
        strLog   = strLog + "5 points for at least one special char\n";
      }

                     // [verified] at least two special characters
      if (passwd.match(/(.*[!,@,#,$,%,^,&,*,?,_,~].*[!,@,#,$,%,^,&,*,?,_,~])/))
      {
        intScore = (intScore+5);
        strLog   = strLog + "5 points for at least two special chars\n";
      }


      // COMBOS
      if (passwd.match(/([a-z].*[A-Z])|([A-Z].*[a-z])/))        // [verified] both upper and lower case
      {
        intScore = (intScore+2);
        strLog   = strLog + "2 combo points for upper and lower letters\n";
      }

      if (passwd.match(/([a-zA-Z])/) && passwd.match(/([0-9])/)) // [verified] both letters and numbers
      {
        intScore = (intScore+2);
        strLog   = strLog + "2 combo points for letters and numbers\n";
      }

                    // [verified] letters, numbers, and special characters
      if (passwd.match(/([a-zA-Z0-9].*[!,@,#,$,%,^,&,*,?,_,~])|([!,@,#,$,%,^,&,*,?,_,~].*[a-zA-Z0-9])/))
      {
        intScore = (intScore+2);
        strLog   = strLog + "2 combo points for letters, numbers and special chars\n";
      }


      if(intScore < 16)
      {
         strVerdict = "very weak";
      }
      else if (intScore > 15 && intScore < 25)
      {
         strVerdict = "weak";
      }
      else if (intScore > 24 && intScore < 35)
      {
         strVerdict = "mediocre";
      }
      else if (intScore > 34 && intScore < 45)
      {
         strVerdict = "strong";
      }
      else
      {
         strVerdict = "stronger";;

      }
    if(intScore<27){
      return false;
    }else{
      return true;
    }


  }
  function checkName(val)
  {
    re = /^[A-Za-z\s]+$/;
    if(re.test(val))
    {
      return true;
    }
    else
    {
      return false;
    }
  }
