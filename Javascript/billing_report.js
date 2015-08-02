$(document).ready(function() {
    $('.edit-mode').hide();
    $('.edit-mode-datepicker').hide();

    $('.editable-row').on('click', function() {
        if ($(this).siblings('.edit-mode-datepicker').length > 0) {
            $(this).siblings('.edit-mode-datepicker').datepicker({
                dateFormat: 'yy-mm-dd',
                onSelect: function() {
                    var readOnlyElement = $(this).siblings('.editable-row');
                    var editElement = $(this);
                    var valueCheck = editElement.val();
                    var transactionNo = $(this).data('transaction');
                    if (editElement.hasClass('status')) {
                        var status = editElement.val();
                    } else if (editElement.hasClass('adp-number')) {
                        var adpNumber = editElement.val();
                    } else if (editElement.hasClass('statement-date')) {
                        var statementDate = editElement.val();
                    }

                    if (status) {
                        valueCheck = editElement.children(':selected').text();
                    }

                    if ((valueCheck != readOnlyElement.html()) && valueCheck != "[none]" && valueCheck != '') {
                        editElement.parent().append('<img id="ajax_loader" src="../images/spinner.gif" />');
                        $.post(
                        '../api/ajax/hcfa_billing_report/save_insurance_status.php',
                        {
                          status: status,
                          transactionNo: transactionNo,
                          statementDate: statementDate,
                          adpNumber: adpNumber
                        }, function(data) {

                        }).done(function() {
                            if (status) {
                                $('#day_in_status_' + transactionNo).html('0');
                            }
                            readOnlyElement.html(valueCheck);
                            readOnlyElement.show();
                            editElement.hide();
                            $('#ajax_loader').remove();
                        });
                    } else {
                        readOnlyElement.show();
                        editElement.val(editElement.data('previous'));
                        editElement.hide();
                    }
                }
            });
            $(this).siblings('.edit-mode-datepicker').show();
            $(this).siblings('.edit-mode-datepicker').focus();
            $(this).hide();
        } else {
            $(this).siblings('.edit-mode').show();
            $(this).siblings('.edit-mode').focus();
            $(this).siblings('.edit-mode').data('previous', $(this).siblings('.edit-mode').val());
            $(this).hide();
        }
    });

    $('.edit-mode').on('focusout',  function() {
        var readOnlyElement = $(this).siblings('.editable-row');
        var editElement = $(this);
        var valueCheck = editElement.val();
        var transactionNo = $(this).data('transaction');
        if (editElement.hasClass('status')) {
            var status = editElement.val();
        } else if (editElement.hasClass('adp-number')) {
            var adpNumber = editElement.val();
        } else if (editElement.hasClass('statement-date')) {
            var statementDate = editElement.val();
        }

        if (status) {
            valueCheck = editElement.children(':selected').text();
        }

        if ((valueCheck != readOnlyElement.html()) && valueCheck != "[none]" && valueCheck != '') {
            editElement.parent().append('<img id="ajax_loader" src="../images/spinner.gif" />');
            $.post(
            '../api/ajax/hcfa_billing_report/save_insurance_status.php',
            {
              status: status,
              transactionNo: transactionNo,
              statementDate: statementDate,
              adpNumber: adpNumber
            }, function(data) {

            }).done(function() {
                if (status) {
                    $('#day_in_status_' + transactionNo).html('0');
                }
                readOnlyElement.html(valueCheck);
                readOnlyElement.show();
                editElement.hide();
                $('#ajax_loader').remove();
            });
        } else {
            readOnlyElement.show();
            editElement.val(editElement.data('previous'));
            editElement.hide();
        }
    });

    $('.edit-mode-datepicker').on('focusout',  function() {
        $(this).siblings('.editable-row').show();
        $(this).hide();
    });
});