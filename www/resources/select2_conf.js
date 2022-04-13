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

    return $(
        '<div class="row" style="height: 50px">' +
            '<div class="col-10" style="margin: auto">' +
                '<span>' + repo.text + '</span>' +
            '</div>' +
            '<div class="col-2">' +
                '<span><img src="' + repo.image + '" style="height: 50px; width: 50px; object-fit: cover; object-position: left"/></span>' +
            '</div>' +
        '</div>'
    );
}
