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
        var row = $(this).parent().parent();
        id = $(row).children('.setID').attr('data-id');
        row = $(row).children();
        var inputs = $('input');
        var data = $(row).eq(0).text();
        $(inputs).eq(0).attr({'placeholder' : data});
        data = $(row).eq(1).text();
        $(inputs).eq(1).attr({'placeholder' : data});
        data = $(row).eq(2).text();
        $(inputs).eq(2).attr({'placeholder' : data});
        data = $(row).eq(3).text();
        $(inputs).eq(3).attr({'placeholder' : data});

        $('.updateButton').click(function () {
        var result = $('form').serializeArray();

        result[4] = {'name' : 'id', 'value' : id};
        console.log(result);
        var encoded = $.toJSON(result);
        console.log(encoded);
        console.log(id);
        });

    });

}