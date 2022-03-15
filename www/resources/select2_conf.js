$(".mySelect").select2({
    ajax: {
        url: 'https://ip-147-251-124-162.flt.cloud.muni.cz/campus-idp/module.php/campusMultiauth/idpSearch.php',
        dataType: 'json',
        delay: 250,
        processResults: function (data) {
            return {
                results: data.items,
            };
        },
        cache: true
    },

    placeholder: "Select an institution",
    minimumInputLength: 3,
    templateResult: formatRepo,
});
$('.mySelect')
    .val(null).trigger('change')
    .on('select2:select', function (e) {
        const data = e.params.data;
        $('#idpentityid').val(data.idpentityid);
        $('#idps_form').submit();
    });

function formatRepo(repo)
{
    if (repo.loading) {
        return repo.text;
    }

    return $('<span>' + repo.text + ' ' + '<img src="' + repo.image + '" height="19px" width="19px" /></span>');
}
