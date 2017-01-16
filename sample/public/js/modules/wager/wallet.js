$(document).ready(function() {
    $('#ccredit').hide();

    //Initailize the radio buttons//
    $(":radio").each(function() {
        $(this).attr("id",$(this).attr("name")+$(this).attr("value"));
    });
    // DepositForm
    $("#paytype0").attr("checked","checked");
    $("#withtype0").attr("checked","checked");
    
    $(":radio").click(function(){
        if($("#paytype1").prop('checked') === true) {
            $('#ccredit').show();

        } else {
            $('#ccredit').hide();
        }
    });
    
    //--Make a Deposit --//
    $("#DepositForm").submit(function(event) {
        if($("#paytype1").prop('checked') === true ||
            $("#deposit-id").val() < 10) {
            event.preventDefault();  
        }
    });
        
    $("#submitDeposit").click(function(){
        
        if($("#deposit-id").val() < 10) {
            event.preventDefault(); 
            $('#messagewager').html("Deposit amount cannot be less than $10 USD.");
            $('#messageWager').popup("show");
            return false;
        }
        
        if($("#paytype1").prop('checked') === true) {
            $("#madeCCDeposit").popup("show");
        } 
    });
    
    $('#madedepositSubmit').click(function() {
        $("#madeCCDeposit").popup("hide");
        Makedeposit("/wager/wallet/depositcc/");
    }); 
    
    $('#madedepositCancel').click(function(){
        $("#madeCCDeposit").popup("hide");
    });
 
     //--Make a withdrawal --//
    $("#WithdrawForm").submit(function(event) {
        event.preventDefault();  
    });   
    
    $('#submitWithdraw').click(function() {
        var t = $("#withdraw-id").val();
        if($("#withdraw-id").val() === "") {
            alert("Please Enter an amount to withdraw.");
            return false;
        }
        Makewithdrawal("/wager/wallet/withdrawal/");
    }); 
});

function Makedeposit(url) {
    $('#messagewager').html("Please wait while we send your deposit...");
    $('#messageWager').popup("show");
    var d = $('#DepositForm').serialize();
    $.ajax({
            type: "POST",
            url: url,
            data: $('#DepositForm').serialize(),
            success: function(data)
            {
                  if(data.success) {
                    $("#mycredits").html('<h3>$' + data.credits + '</h3>');
                  } 
                  $('#messagewager').html(data.messages);
                  $('#messageWager').popup("show");
            }
    });
    document.getElementById("DepositForm").reset();
    $('#ccredit').hide();
}

function Makewithdrawal(url) {
    $('#messagewager').html("Please wait while we send your deposit...");
    $('#messageWager').popup("show");
    var d = $('#DepositForm').serialize();
    $.ajax({
            type: "POST",
            url: url,
            data: $('#WithdrawForm').serialize(),
            success: function(data)
            {
                  if(data.success) {
                    $("#mycredits").html('<h3>$' + data.credits + '</h3>');
                  } 
                  $('#messagewager').html(data.messages);
                  $('#messageWager').popup("show");
            }
    });
    document.getElementById("WithdrawForm").reset();
}

$("#withdraw-id").keyup(function() {
    var moneyVal = $(this)
    var withdrawAmount = moneyVal.val()
    var creditAvailable = parseInt($("#mycredits > h3").html().substring(1));
    if (isNaN(withdrawAmount)) {
        if ($("#withdraw-id").next().is('label')) {
            $("#withdraw-id").next('label').remove()
        }
        $("#withdraw-id").after('<label class="error">Only use numbers please</label>');
        $("#submitWithdraw").attr('disabled', true);
    }
    if (withdrawAmount > creditAvailable) {
        if ($("#withdraw-id").next().is('label')) {
            $("#withdraw-id").next('label').remove()
        }
        $("#withdraw-id").after('<label class="error">You do not have enough credits to withdraw this amount</label>');
        $("#submitWithdraw").attr('disabled', true);
    }
    if (withdrawAmount <= creditAvailable) {
         $("#withdraw-id").next('label').remove()
        $("#submitWithdraw").removeAttr("disabled")
    }


})


