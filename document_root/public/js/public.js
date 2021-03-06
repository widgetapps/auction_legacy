

function updateBoard() {
    $('#bid-board').load('/public/index/bidboard');
}

$(document).ready(function() {

    if ($('#itemlist')){
        $('#itemlist').DataTable();
    }

    if ($('.itemtitle')) {
        $("#itemlist").on("click", ".itemtitle", function(event){
            var id = $(event.target).attr('href').substring(1);
            console.log(event.target);
            var url = '/public/index/itemdetail/id/' + id;
            $('#itemcontent').load(url);
        });
    }

    if ($('#bid-board')) {
        setInterval('updateBoard()',5000);
    }

} );
