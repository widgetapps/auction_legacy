
$(document).ready(function() {

    if ($('#itemlist')){
        $('#itemlist').DataTable();
    }

    if ($('.itemtitle')) {
        $('.itemtitle').click(function(event) {
            var id = $(event.target).attr('href').substring(1);
            console.log(event.target);
            var url = '/public/index/itemdetail/id/' + id;
            $('#itemcontent').load(url);
        });
    }

} );
