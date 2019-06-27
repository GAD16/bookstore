$(document).ready(initPage);

function initPage () {

    $('.popup_bg').click(function(){	// Событие клика на затемненный фон
        $('.popup').fadeOut(800);	// Медленно убираем всплывающее окно
    });



    $('.delButton').click(function () {
        var id = $(this).parent().attr('data-id');
        console.log(id);
        $.ajax({
                type: "GET",
                url: "delButton",
                data: {id: id},
                success: function (data) {
                    if (data === '0') {
                        alert('Ошибка. Запись не была удалена');

                    } else {
                        $('.tableBlock').html(data);

                    }
                }
                });
    });
    $('.cell').click(function () {
        $('.popup').fadeIn(800);
        var row = $(this).parent().parent().children('.setID').attr('data-id');
       // var id = row.$('.setID').attr('data-id');
       // console.log(id);
        console.log(row);
    })

}