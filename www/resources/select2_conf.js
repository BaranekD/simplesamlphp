$(".mySelect").select2({
    data: data,
    placeholder: "Select an institution",
    allowClear: false,
    minimumInputLength: 3
});
$('.mySelect')
    .val(null).trigger('change')
    .on('select2:select', function (e) {
        const data = e.params.data;
        $('#idpentityid').val(data.idpentityid);
        $('#idps_form').submit();
    });

// .on('select2:open', function () {
    //     $('.select2-container').css('margin-bottom', $('.select2-dropdown').css('height'));
    //     // document.getElementBy('idps_form').style.marginBottom = '250px';
    // })
    // .on('change', function () {
    //     $('.select2-container').css('margin-bottom', $('.select2-dropdown').css('height'));
    //     // document.getElementBy('idps_form').style.marginBottom = '250px';
    // })
    // .on('select2:close', function () {
    //     $('.select2-container').css('margin-bottom', '0px');
    //     // document.getElementById('idps_form').style.marginBottom = '0px';
    // });
