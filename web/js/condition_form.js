$(document).ready(function () {
    $('input[type=radio][name=ConditionForm\\[phone\\]]').change(function () {
        var phone_end = $("#conditionform-phone_end");
        phone_end.val('');
        phone_end.attr('disabled', this.value === '1' ? false : 'disabled');
    });
});